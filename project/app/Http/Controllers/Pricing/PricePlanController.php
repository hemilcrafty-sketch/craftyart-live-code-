<?php

namespace App\Http\Controllers\Pricing;

use App\Enums\PlanFeatureSlug;
use App\Http\Controllers\HelperController;
use Exception;
use App\Models\Pricing\Plan;
use App\Models\Pricing\SubPlan;
use Illuminate\Http\Request;
use App\Models\Pricing\PlanDuration;
use Illuminate\Http\JsonResponse;
use App\Models\Pricing\PlanCategoryFeature;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Contracts\Foundation\Application;

class PricePlanController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'description', "value" => 'Description']
        ];

        $query = Plan::query()
            ->orderByRaw('sequence_number')
            ->orderByDesc('created_at');

        $plans = $this->applyFiltersAndPagination($request, $query, $searchableFields);
        $hasFreePlan = Plan::where('is_free_type', 1)->exists();

        return view("pricing.price_plans.index", compact('plans', 'searchableFields', 'hasFreePlan'));
    }

    // public function create(Request $request)
    // {
    //     $planId = $request->query('id');

    //     $isFreePlan = $request->query('type') === 'free';

    //     $plan = new Plan();
    //     $subPlan = collect();
    //     $appearanceByFeatureId = collect();
    //     $planData = new Plan();
    //     $alreadyAddedIds = $subPlan->pluck('duration')->toArray();
    //     $categoryFeatures = PlanCategoryFeature::with('Planfeatures')->get();
    //     $planCategory = $isFreePlan ? collect() : PlanDuration::all();
    //     $slugOptions = PlanFeatureSlug::values();

    //     if ($planId) {
    //         $plan = Plan::findOrFail($planId);
    //         $planData = $plan;
    //         $isFreePlan = $plan->is_free_type == 1;

    //         $subPlan = SubPlan::with('category')
    //             ->where('plan_id', $plan->string_id)
    //             ->where('deleted', 0)
    //             ->get();

    //         $appearance = $plan->appearance ?? [];

    //         $appearanceList = is_string($appearance)
    //             ? json_decode($appearance, true)
    //             : $appearance;

    //         foreach ($appearanceList as &$item) {
    //             $feature = $categoryFeatures
    //                 ->flatMap->planfeatures
    //                 ->firstWhere('id', $item['features_id']);

    //             if ($feature) {
    //                 $item['slug'] = $feature->slug;
    //             }
    //         }

    //         $appearanceByFeatureId = collect($appearanceList)->keyBy('features_id');
    //     }

    //     return view("pricing.price_plans.create", compact(
    //         'plan',
    //         'planData',
    //         'subPlan',
    //         'appearanceByFeatureId',
    //         'categoryFeatures',
    //         'planCategory',
    //         'alreadyAddedIds',
    //         'isFreePlan',
    //         'slugOptions',
    //     ));
    // }

    public function create(Request $request)
    {
        $planId = $request->query('id');
        $isFreePlan = $request->query('type') === 'free';

        $plan = new Plan();
        $subPlan = collect();
        $appearanceByFeatureId = collect();
        $planData = new Plan();
        $alreadyAddedIds = $subPlan->pluck('duration')->toArray();
        $categoryFeatures = PlanCategoryFeature::with('Planfeatures')->get();
        $planCategory = $isFreePlan ? collect() : PlanDuration::all();
        $slugOptions = PlanFeatureSlug::values();

        if ($planId) {
            $plan = Plan::findOrFail($planId);
            $planData = $plan;
            $isFreePlan = $plan->is_free_type == 1;

            $subPlan = SubPlan::with('category')
                ->where('plan_id', $plan->string_id)
                ->where('deleted', 0)
                ->get();

            $appearance = $plan->appearance ?? [];
            $appearanceList = is_string($appearance)
                ? json_decode($appearance, true)
                : $appearance;

            // Store the order from database for sorting
            $featureOrderFromDb = collect($appearanceList)
                ->pluck('features_id')
                ->flip()
                ->toArray(); // This gives us feature_id => position_in_db

            // Map appearance data by feature ID for easy access
            $appearanceByFeatureId = collect($appearanceList)->keyBy('features_id');

            // Get all categories and their features
            $categoryFeatures = PlanCategoryFeature::with('Planfeatures')->get();

            // Sort features within each category based on database order
            foreach ($categoryFeatures as $category) {
                $sortedFeatures = $category->Planfeatures->sortBy(function ($feature) use ($featureOrderFromDb) {
                    // Return position from DB if exists, otherwise a large number to push to end
                    return $featureOrderFromDb[$feature->id] ?? 9999;
                })->values();

                $category->setRelation('Planfeatures', $sortedFeatures);
            }

            // Sort categories based on the first feature's position in the database
            $categoryFeatures = $categoryFeatures->sortBy(function ($category) use ($featureOrderFromDb) {
                // Find the minimum position of features in this category from the database
                $featureIds = $category->Planfeatures->pluck('id')->toArray();
                $positions = array_filter(array_intersect_key($featureOrderFromDb, array_flip($featureIds)));

                return !empty($positions) ? min($positions) : 9999;
            })->values();

            // Add slug to appearance items for reference
            foreach ($appearanceList as &$item) {
                $feature = $categoryFeatures
                    ->flatMap->planfeatures
                    ->firstWhere('id', $item['features_id']);

                if ($feature) {
                    $item['slug'] = $feature->slug;
                }
            }
        }

        return view("pricing.price_plans.create", compact(
            'plan',
            'planData',
            'subPlan',
            'appearanceByFeatureId',
            'categoryFeatures',
            'planCategory',
            'alreadyAddedIds',
            'isFreePlan',
            'slugOptions',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $isUpdate = !empty($request->id);
        $plan = $isUpdate ? Plan::findOrFail($request->id) : new Plan();
        $stringId = $isUpdate ? $plan->string_id : HelperController::generateStringIds(10, '', Plan::class);

        $features = is_array($request->features) ? $request->features : [];

        $errors = []; // collect validation errors

        $appearance = collect($request->meta_data ?? [])
            ->filter(fn($_, $key) => in_array($key, $features))
            ->map(function ($value, $key) use ($request, &$errors) {
                $metaValue = is_array($value) ? ($value['meta_value'] ?? 0) : 0;

                $metaFeatureValue = $request->feature_meta_value[$key]['meta_feature_value'] ?? ['', ''];
                $subName = $request->feature_sub_name[$key] ?? '';

                // ✅ Condition: if sub_name OR any meta_feature_value is filled
                if ((!empty($metaFeatureValue[0]) || !empty($metaFeatureValue[1]))) {
                    if (empty($metaFeatureValue[0]) || empty($metaFeatureValue[1])) {
                        $errors[] = "Feature ID {$key}: Feature Value and Feature Duration Limit both fields are required together.";
                    }
                }

                return [
                    'features_id' => $key,
                    'meta_value' => (int) $metaValue,
                    'slug' => $request->slug[$key]['slug'] ?? '',
                    'meta_appearance' => $request->meta_data_appearance[$key]['meta_appearance'] ?? '',
                    'meta_feature_value' => $metaFeatureValue,
                    'sub_name' => $subName,
                    'is_feature_visible' => isset($request->feature_visible[$key]) ? 1 : 0,
                ];
            })->values()->all();

        if (!empty($errors)) {
            return response()->json([
                'status' => false,
                'errors' => $errors
            ], 422);
        }

        $validationError = ContentManager::validateBase64Images([
            ['img' => $request->icon, 'name' => "icon", 'required' => !$isUpdate]
        ]);

        if ($validationError) {
            return response()->json(['error' => $validationError], 422);
        }

        $savedImagePath = ContentManager::saveImageToPath(
            $request->icon,
            'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
        );

        $planData = [
            'name' => $request->name,
            'sub_title' => $request->sub_title,
            'btn_name' => $request->btn_name,
            'is_recommended' => (int) $request->input('is_recommended', 0),
            'description' => $request->description ?? '',
            'icon' => $savedImagePath ?? $plan->icon,
            'sequence_number' => $request->sequence_number,
            'appearance' => json_encode($appearance),
            'status' => (int) $request->input('status', 1),
            'is_free_type' => $request->is_free_type ?? 0,
        ];

        if ($isUpdate) {
            $plan->update($planData);
        } else {
            $plan = Plan::create(array_merge($planData, ['string_id' => $stringId]));
        }

        $submittedIds = [];
        foreach ($request->subplans ?? [] as $index => $subplan) {
            PlanDuration::find($subplan['duration_id']);

            // Process subscription IDs for this subplan
            $subscriptionIds = [];
            if (isset($subplan['subscription_ids'])) {
                foreach ($subplan['subscription_ids'] as $gatewayKey => $gatewayId) {
                    if (!empty($gatewayId)) {
                        $subscriptionIds[$gatewayKey] = $gatewayId;
                    }
                }
            }

            $subplanData = [
                'additional_duration' => isset($subplan['duration_value']) ? (int) $subplan['duration_value'] : null,

                // INR
                'inr_price'        => isset($subplan['inr_price']) ? (float) $subplan['inr_price'] : null,
                'inr_offer_price'  => isset($subplan['inr_offer_price']) ? (float) $subplan['inr_offer_price'] : null,
                'inr_discount'     => (isset($subplan['inr_price'], $subplan['inr_offer_price']) && (float)$subplan['inr_offer_price'] > 0)
                    ? round((($subplan['inr_price'] - $subplan['inr_offer_price']) / $subplan['inr_price']) * 100) . '%'
                    : null,

                // USD
                'usd_price'        => isset($subplan['usd_price']) ? (float) $subplan['usd_price'] : null,
                'usd_offer_price'  => isset($subplan['usd_offer_price']) ? (float) $subplan['usd_offer_price'] : null,
                'usd_discount'     => (isset($subplan['usd_price'], $subplan['usd_offer_price']) && (float)$subplan['usd_offer_price'] > 0)
                    ? round((($subplan['usd_price'] - $subplan['usd_offer_price']) / $subplan['usd_price']) * 100) . '%'
                    : null,
            ];

            if (!empty($subplan['id'])) {
                SubPlan::where('id', $subplan['id'])
                    ->where('plan_id', $stringId)
                    ->update([
                        'duration_id' => $subplan['duration_id'] ?? null,
                        'plan_details' => json_encode($subplanData),
                        'subscription_ids' => !empty($subscriptionIds) ? json_encode($subscriptionIds) : null,
                        'deleted' => 0
                    ]);
                $submittedIds[] = $subplan['id'];
            } else {
                $newSub = SubPlan::create([
                    'string_id' => HelperController::generateStringIds(10, '', SubPlan::class, 'string_id'),
                    'plan_id' => $stringId,
                    'duration_id' => $subplan['duration_id'] ?? null,
                    'plan_details' => json_encode($subplanData),
                    'subscription_ids' => !empty($subscriptionIds) ? json_encode($subscriptionIds) : null,
                    'deleted' => 0
                ]);
                $submittedIds[] = $newSub->id;
            }
        }

        if ($isUpdate) {
            SubPlan::where('plan_id', $stringId)
                ->whereNotIn('id', $submittedIds)
                ->update(['deleted' => 1]);
        }

        return response()->json([
            'status' => true,
            'success' => $isUpdate ? "Plan updated successfully." : "Plan created successfully."
        ]);
    }


    /*public function store(Request $request): JsonResponse
    {
        $isUpdate = !empty($request->id);
        $plan = $isUpdate ? Plan::findOrFail($request->id) : new Plan();
        $stringId = $isUpdate ? $plan->string_id : HelperController::generateStringIds(10, '', Plan::class);

        $features = is_array($request->features) ? $request->features : [];

        $errors = []; // collect validation errors

        $appearance = collect($request->meta_data ?? [])
            ->filter(fn($_, $key) => in_array($key, $features))
            ->map(function ($value, $key) use ($request, &$errors) {
                $metaValue = is_array($value) ? ($value['meta_value'] ?? 0) : 0;

                $metaFeatureValue = $request->feature_meta_value[$key]['meta_feature_value'] ?? ['', ''];
                $subName = $request->feature_sub_name[$key] ?? '';

                // ✅ Condition: if sub_name OR any meta_feature_value is filled
                if (!empty($subName) || (!empty($metaFeatureValue[0]) || !empty($metaFeatureValue[1]))) {
                    if (empty($subName) || empty($metaFeatureValue[0]) || empty($metaFeatureValue[1])) {
                        $errors[] = "Feature ID {$key}: sub_name and both meta_feature_value fields are required together.";
                    }
                }

                return [
                    'features_id' => $key,
                    'meta_value' => (int) $metaValue,
                    'slug' => $request->slug[$key]['slug'] ?? '',
                    'meta_appearance' => $request->meta_data_appearance[$key]['meta_appearance'] ?? '',
                    'meta_feature_value' => $metaFeatureValue,
                    'sub_name' => $subName,
                    'is_feature_visible' => isset($request->feature_visible[$key]) ? 1 : 0,
                ];
            })->values()->all();

        if (!empty($errors)) {
            return response()->json([
                'status' => false,
                'errors' => $errors
            ], 422);
        }

        $validationError = ContentManager::validateBase64Images([
            ['img' => $request->icon, 'name' => "icon", 'required' => !$isUpdate]
        ]);

        if ($validationError) {
            return response()->json(['error' => $validationError], 422);
        }

        $savedImagePath = ContentManager::saveImageToPath(
            $request->icon,
            'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
        );

        $subscriptionId = [];
        if ($request->has('subscription_id')) {
            if (is_string($request->subscription_id)) {
                $subscriptionId = json_decode($request->subscription_id, true) ?? [];
            }
            elseif (is_array($request->subscription_id)) {
                $subscriptionId = array_filter($request->subscription_id);
            }
        }

        $planData = [
            'name' => $request->name,
            'sub_title' => $request->sub_title,
            'btn_name' => $request->btn_name,
            'is_recommended' => (int) $request->input('is_recommended', 0),
            'description' => $request->description ?? '',
            'icon' => $savedImagePath ?? $plan->icon,
            'sequence_number' => $request->sequence_number,
            'subscription_id' => !empty($subscriptionId) ? json_encode($subscriptionId) : null,
            'appearance' => json_encode($appearance),
            'status' => (int) $request->input('status', 1),
            'is_free_type' => $request->is_free_type ?? 0,
        ];

        if ($isUpdate) {
            $plan->update($planData);
        } else {
            $plan = Plan::create(array_merge($planData, ['string_id' => $stringId]));
        }

        $submittedIds = [];
        foreach ($request->subplans ?? [] as $subplan) {
            PlanDuration::find($subplan['duration_id']);
            $subplanData = [
                'additional_duration' => isset($subplan['duration_value']) ? (int) $subplan['duration_value'] : null,

                // INR
                'inr_price'        => isset($subplan['inr_price']) ? (int) $subplan['inr_price'] : null,
                'inr_offer_price'  => isset($subplan['inr_offer_price']) ? (int) $subplan['inr_offer_price'] : null,
                'inr_discount'     => (isset($subplan['inr_price'], $subplan['inr_offer_price']) && (int)$subplan['inr_offer_price'] > 0)
                    ? round((($subplan['inr_price'] - $subplan['inr_offer_price']) / $subplan['inr_price']) * 100) . '%'
                    : null,

                // USD
                'usd_price'        => isset($subplan['usd_price']) ? (int) $subplan['usd_price'] : null,
                'usd_offer_price'  => isset($subplan['usd_offer_price']) ? (int) $subplan['usd_offer_price'] : null,
                'usd_discount'     => (isset($subplan['usd_price'], $subplan['usd_offer_price']) && (int)$subplan['usd_offer_price'] > 0)
                    ? round((($subplan['usd_price'] - $subplan['usd_offer_price']) / $subplan['usd_price']) * 100) . '%'
                    : null,
            ];

            if (!empty($subplan['id'])) {
                SubPlan::where('id', $subplan['id'])
                    ->where('plan_id', $stringId)
                    ->update([
                        'duration_id' => $subplan['duration_id'] ?? null,
                        'plan_details' => json_encode($subplanData),
                        'deleted' => 0
                    ]);
                $submittedIds[] = $subplan['id'];
            } else {
                $newSub = SubPlan::create([
                    'string_id' => HelperController::generateStringIds(10, '', SubPlan::class, 'string_id'),
                    'plan_id' => $stringId,
                    'duration_id' => $subplan['duration_id'] ?? null,
                    'plan_details' => json_encode($subplanData),
                    'deleted' => 0
                ]);
                $submittedIds[] = $newSub->id;
            }
        }

        if ($isUpdate) {
            SubPlan::where('plan_id', $stringId)
                ->whereNotIn('id', $submittedIds)
                ->update(['deleted' => 1]);
        }

        return response()->json([
            'status' => true,
            'success' => $isUpdate ? "Plan updated successfully." : "Plan created successfully."
        ]);
    }*/


    function destroy(Plan $plan): JsonResponse
    {
        try {
            $plan->update(['status' => 0]);

            return response()->json([
                'status' => true,
                'success' => "Plan status has been updated to inactive.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

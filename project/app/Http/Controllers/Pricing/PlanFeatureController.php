<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\AppBaseController;
use App\Models\Pricing\PlanCategoryFeature;
use App\Models\Pricing\PlanFeature;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanFeatureController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $categoryFeatures = PlanCategoryFeature::all();
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'slug', "value" => 'slug']];
        $features = $this->applyFiltersAndPagination($request, PlanFeature::query(), $searchableFields);
        return view("pricing.plan_feature.index", [
            'features' => $features,
            'categoryFeatures' => $categoryFeatures,
            'searchableFields' => $searchableFields
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $inputs = $request->except('_token');

        try {
            $isUpdate = !empty($inputs['feature_id']);
            $slugIncoming = isset($inputs['slug']) ? trim((string) $inputs['slug']) : '';

            if ($isUpdate) {
                $feature = PlanFeature::findOrFail($inputs['feature_id']);
                $currentSlug = trim((string) $feature->slug);

                if ($currentSlug !== '') {
                    unset($inputs['slug']);
                } else {
                    if ($slugIncoming === '') {
                        return response()->json([
                            'status' => false,
                            'message' => 'Slug is required.',
                        ]);
                    }
                    if (!preg_match('/^[a-z0-9_]+$/', $slugIncoming)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Slug may only contain lowercase letters, numbers, and underscores.',
                        ]);
                    }
                    $inputs['slug'] = $slugIncoming;
                    if (PlanFeature::where('slug', $slugIncoming)->where('id', '!=', $feature->id)->exists()) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Slug already exists. Please use a different slug.',
                        ]);
                    }
                }

                $feature->update($inputs);
            } else {
                if ($slugIncoming === '') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Slug is required.',
                    ]);
                }
                if (!preg_match('/^[a-z0-9_]+$/', $slugIncoming)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Slug may only contain lowercase letters, numbers, and underscores.',
                    ]);
                }
                $inputs['slug'] = $slugIncoming;
                if (PlanFeature::where('slug', $slugIncoming)->exists()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Slug already exists. Please use a different slug.',
                    ]);
                }
                PlanFeature::create($inputs);
            }

            return response()->json([
                'status' => true,
                'message' => 'Feature has been ' . ($isUpdate ? 'updated' : 'added') . ' successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(PlanFeature $feature): JsonResponse
    {
        return response()->json([
            'feature' => $feature,
        ]);
    }

    public function destroy(PlanFeature $feature): JsonResponse
    {
        try {
            $feature->delete();
            return response()->json([
                'status' => true,
                'message' => 'Feature has been deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Models\Pricing\PlanDuration;
use App\Models\Pricing\PlanUserDiscount;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanDurationController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {

        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'duration', "value" => 'Duration Type']];
        $getPlanCategories = $this->applyFiltersAndPagination($request, PlanDuration::query(), $searchableFields);
        $discounts = PlanUserDiscount::all();

        return view("pricing.plan_duration.index", compact('getPlanCategories', 'searchableFields', 'discounts'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $name = $request->name;
            $durationType = $request->duration;
            $id = $request->id;
            $isAnnual = $request->has('is_annual') && $request->is_annual == 1 ? 1 : 0;

            // Check duplicate name
            $exists = PlanDuration::where('name', $name)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'errors' => ['name' => ['This name already exists.']]
                ], 422);
            }

            if (!empty($id)) {
                $planCategory = PlanDuration::findOrFail($id);
                $planCategory->update([
                    'name' => $name,
                    'duration' => $durationType,
                    'is_annual' => $isAnnual,
                ]);
            } else {
                PlanDuration::create([
                    'name' => $name,
                    'string_id' => HelperController::generateID(), // pass $name to fix argument error
                    'duration' => $durationType,
                    'is_annual' => $isAnnual,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Plan Duration has been ' . ($id ? 'updated' : 'added') . ' successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id): JsonResponse
    {
        $planCategory = PlanDuration::findOrFail($id);
        return response()->json(['planCategory' => $planCategory]);
    }

}
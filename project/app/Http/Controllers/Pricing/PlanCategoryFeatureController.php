<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\AppBaseController;
use App\Models\Pricing\PlanCategoryFeature;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanCategoryFeatureController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name']];
        $categoryFeatures = $this->applyFiltersAndPagination($request, PlanCategoryFeature::query(), $searchableFields);
        return view("pricing.plan_category_feature.index", compact('searchableFields'))->with('categoryFeatures', $categoryFeatures);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check for duplicate name
        $duplicate = PlanCategoryFeature::where('name', $request->name)
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', '!=', $request->id);
            })
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'message' => 'The name is already in use. Please choose a another one.'
            ], 422);
        }

        if ($request->id) {
            $feature = PlanCategoryFeature::findOrFail($request->id);
            $feature->update(['name' => $request->name]);
        } else {
            PlanCategoryFeature::create(['name' => $request->name]);
        }

        return response()->json([
            'status' => true,
            'message' => "Category Feature " . ($request->id ? "updated" : "created") . " successfully.",
        ]);
    }

}
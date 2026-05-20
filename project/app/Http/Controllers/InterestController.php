<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\NewCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestController extends AppBaseController
{

    // Show interest list
    public function showInterest(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $interestArray = $this->applyFiltersAndPagination(
            $request,
            Interest::query(),
            $searchableFields,
            [
                'parent_query' => NewCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'new_category_id',
            ]
        );
        $allNewCategories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();

        return view('filters.interests', compact('interestArray', 'allNewCategories', 'searchableFields'));
    }


    // Store or update interest
    public function storeOrUpdate(Request $request): JsonResponse
    {
        $user = auth()->user();

        $newCategoryIds = $request->input('new_category_ids', []);
        if (!is_array($newCategoryIds)) {
            $newCategoryIds = explode(',', $newCategoryIds); // fallback
        }

        if ($request->id) {
            $res = Interest::find($request->id);
            if (!$res) {
                return response()->json(['error' => 'Interest not found.']);
            }

            // Optional role check
            $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id);
            if ($accessCheck) {
                return response()->json(['error' => $accessCheck]);
            }
        } else {
            if (Interest::where("name", $request->name)->exists()) {
                return response()->json(['error' => 'Interest already exists.']);
            }

            $res = new Interest();
            $res->emp_id = $user->id;
        }

        // Save data
        $res->name = $request->name;
        $res->id_name = $request->id_name;
        $res->new_category_id = json_encode($newCategoryIds);
        $res->status = $request->status;
        $res->save();

        $msg = $request->id ? 'Interest updated successfully.' : 'Interest added successfully.';
        return response()->json(['success' => $msg]);
    }

    // Delete interest
    public function deleteInterest(Request $request): JsonResponse
    {
        $deleted = Interest::destroy($request->id);

        return response()->json([
            'success' => $deleted ? true : false
        ]);
    }

    public function getInterestList(Request $request): JsonResponse
    {
        try {
            $category = NewCategory::find($request->cateId);

            if (!$category) {
                throw new Exception('Category not found.');
            }

            $rootParentId = $category->getRootParentId() ?: $request->cateId;

            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);

            $interests = Interest::whereJsonContains('new_category_id', $catId)
                ->where('status', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $interests,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

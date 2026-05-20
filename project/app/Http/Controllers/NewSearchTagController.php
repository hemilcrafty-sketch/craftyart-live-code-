<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\User;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewSearchTagController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $allNewCategories = NewCategory::getAllCategoriesWithSubcategories();
        $newSearchTags = $this->applyFiltersAndPagination(
            $request,
            NewSearchTag::with('assignedSeo'),
            $searchableFields,
            [
                'parent_query' => NewCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'new_category_id',
            ]
        );

        $assignSubCat = User::where('user_type', 5)->get();
        return view('filters.new_search_tag', compact('allNewCategories', 'newSearchTags', 'searchableFields', 'assignSubCat'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $accessCheck = $this->isAccessByRole("seo_all");
            if ($accessCheck) {
                return response()->json([
                    'error' => $accessCheck,
                ]);
            }

            $inputs = [
                "name" => $request->name,
                "id_name" => $request->id_name,
                "new_category_id" => json_encode(array_filter(explode(",", str_replace(' ', '', $request->new_category_id)))),
                "seo_emp_id" => $request->seo_emp_id ?? 0,
                "status" => $request->status,
                "emp_id" => auth()->user()->id,
            ];

            NewSearchTag::create($inputs);

            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been added successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function update(Request $request, NewSearchTag $newSearchTag): JsonResponse
    {
        try {
            $user = auth()->user();

            $accessCheck = $this->isAccessByRole("seo_all", $newSearchTag->id, $newSearchTag->emp_id, [$newSearchTag['seo_emp_id']]);
            if (!RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
                if ($accessCheck) {
                    return response()->json([
                        'error' => $accessCheck,
                    ]);
                }
            }

            $rawIds = str_replace(' ', '', $request->new_category_id);
            $categoryIds = $rawIds === "0" || empty($rawIds)
                ? []
                : array_filter(explode(",", $rawIds));


            $data = $newSearchTag->update([
                'new_category_id' => json_encode($categoryIds),
                'seo_emp_id' => $request->seo_emp_id ?? $newSearchTag['seo_emp_id'],
                'name' => $request->name,
                'id_name' => $request->id_name,
                'status' => $request->status,
            ]);

            // dd($data);
            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been updated successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function edit(NewSearchTag $newSearchTag)
    {
        $ids = json_decode($newSearchTag->new_category_id, true);
        $ids = is_array($ids) ? $ids : [];

        $assignSubCatId = $newSearchTag->seo_emp_id; // single subcategory ID

        $catNames = [];
        foreach ($ids as $id) {
            $catNames[] = HelperController::getNewCatName($id);
        }

        return response()->json([
            "status" => true,
            "data" => [
                "id" => $newSearchTag->id,
                "name" => $newSearchTag->name,
                "id_name" => $newSearchTag->id_name,
                "status" => $newSearchTag->status,
                "new_category_id" => $ids,
                "seo_emp_id" => $assignSubCatId, // single value
                "catNames" => $catNames,
            ]
        ]);
    }





    public function destroy(NewSearchTag $newSearchTag)
    {
        try {
            $newSearchTag->delete();
            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

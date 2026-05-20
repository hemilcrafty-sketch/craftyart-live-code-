<?php

namespace App\Http\Controllers;

use App\Models\NewCategory;
use App\Models\Theme;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends AppBaseController
{
    public function show_theme(Request $request, Theme $theme): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $themeArray = $this->applyFiltersAndPagination(
            $request,
            Theme::query(),
            $searchableFields,
            [
                'parent_query' => NewCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'new_category_id',
            ]
        );
        $allCategories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();

        return view('filters.themes', compact('themeArray', 'allCategories', 'searchableFields'));
    }


    public function submitTheme(Request $request): JsonResponse
    {
        $user = auth()->user();

        $newCategoryIdsInput = $request->input('new_category_ids');
        if (is_array($newCategoryIdsInput)) {
            $newCategoryIds = $newCategoryIdsInput;
        } else if (is_string($newCategoryIdsInput)) {
            $newCategoryIds = explode(',', $newCategoryIdsInput);
        } else {
            $newCategoryIds = [];
        }

        // Edit logic (accept `id` from JS append or `theme_id` from form hidden)
        $themeId = $request->input('id') ?: $request->input('theme_id');
        if ($themeId) {
            $res = Theme::find($themeId);
            if (!$res) {
                return response()->json(['error' => 'Theme not found.']);
            }


        } else {
            // Create logic
            $existing = Theme::where("name", $request->input('name'))->first();
            if ($existing) {
                return response()->json(['error' => 'Theme already exists.']);
            }

            $res = new Theme();
            $res->emp_id = $user->id;
        }

        $accessCheck = $this->isAccessByRole('seo_all', $themeId ?: null, $res->emp_id);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->new_category_id = json_encode($newCategoryIds);
        $res->status = $request->input('status');
        $res->save();

        $msg = $themeId ? 'Data updated successfully.' : 'Data added successfully.';

        return response()->json(['success' => $msg]);
    }

    public function deleteTheme(Request $request, Theme $theme): JsonResponse
    {
        Theme::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    public function getThemeList(Request $request): JsonResponse
    {
        try {
            $category = NewCategory::find($request->cateId);
            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: $request->cateId;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $themes = Theme::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
            return response()->json([
                'success' => true,
                'data' => $themes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

}

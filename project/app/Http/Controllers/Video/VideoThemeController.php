<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Models\Video\VideoTheme;
use App\Models\Video\VideoCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoThemeController extends AppBaseController
{
    public function show_video_theme(Request $request)
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'id_name', "value" => 'ID Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $filteredThemes = $this->applyFiltersAndPagination(
            $request,
            VideoTheme::query(),
            $searchableFields,
            [
                'parent_query' => VideoCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'category_id',
            ]
        );

        $allCategories = VideoCategory::where('status', 1)->get();

        // Get grouped categories with parent-child structure for dropdown
        // Include both parent and child categories as selectable options
        $parentCategories = VideoCategory::where('parent_category_id', 0)->where('status', 1)->get();
        $groupedVideoCategories = [];
        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->where('status', 1)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children,
                'hasChildren' => $children->isNotEmpty()
            ];
        }


        return view('videos.filters.themes')->with([
            'themeArray' => $filteredThemes,
            'allCategories' => $allCategories,
            'groupedVideoCategories' => $groupedVideoCategories,
            'searchableFields' => $searchableFields
        ]);
    }

    public function submitTheme(Request $request): JsonResponse
    {
        $user = auth()->user();

        $categoryIdsInput = $request->input('category_ids');
        if (is_array($categoryIdsInput)) {
            $categoryIds = $categoryIdsInput;
        } else if (is_string($categoryIdsInput)) {
            $categoryIds = explode(',', $categoryIdsInput);
        } else {
            $categoryIds = [];
        }

        if ($request->has('id') && $request->id) {
            $res = VideoTheme::find($request->id);
            if (!$res) {
                return response()->json(['error' => 'Video Theme not found.']);
            }
        } else {
            $existing = VideoTheme::where("name", $request->input('name'))->first();
            if ($existing) {
                return response()->json(['error' => 'Video Theme already exists.']);
            }

            $res = new VideoTheme();
            $res->emp_id = $user->id;
        }

        $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id);
        if ($accessCheck) {
            return response()->json(['error' => $accessCheck]);
        }

        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->id_name = $request->input('id_name');
        $res->category_id = json_encode($categoryIds);
        $res->save();

        $msg = $request->has('id') ? 'Data updated successfully.' : 'Data added successfully.';
        return response()->json(['success' => $msg]);
    }

    public function deleteTheme(Request $request): JsonResponse
    {
        VideoTheme::destroy($request->id);
        return response()->json(['success' => $request->id]);
    }
}

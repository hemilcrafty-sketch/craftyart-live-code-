<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Models\Video\VideoInterest;
use App\Models\Video\VideoCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoInterestController extends AppBaseController
{
  public function showInterest(Request $request)
  {
    $searchableFields = [
      ["id" => 'id', "value" => 'Id'],
      ["id" => 'name', "value" => 'Name'],
      ["id" => 'id_name', "value" => 'ID Name'],
      ["id" => 'status', "value" => 'Status']
    ];

    $filteredInterests = $this->applyFiltersAndPagination(
      $request,
      VideoInterest::query(),
      $searchableFields,
      [
        'parent_query' => VideoCategory::query(),
        'related_column' => 'category_name',
        'column_value' => 'category_id',
      ]
    );

    $allCategories = VideoCategory::where('parent_category_id', 0)->where('status', 1)->get();

    return view('videos.filters.interests')->with([
      'interestArray' => $filteredInterests,
      'allCategories' => $allCategories,
      'searchableFields' => $searchableFields
    ]);
  }

  public function storeOrUpdateInterest(Request $request): JsonResponse
{
    $user = auth()->user();

    $newCategoryIdsInput = $request->input('category_ids');
    if (is_array($newCategoryIdsInput)) {
      $newCategoryIds = $newCategoryIdsInput;
    } else if (is_string($newCategoryIdsInput)) {
      $newCategoryIds = explode(',', $newCategoryIdsInput);
    } else {
      $newCategoryIds = [];
    }

    if ($request->has('id') && $request->id) {
      $res = VideoInterest::find($request->id);
      if (!$res) {
        return response()->json(['error' => 'Video Interest not found.']);
      }
    } else {
      $existing = VideoInterest::where("name", $request->input('name'))->first();
      if ($existing) {
        return response()->json(['error' => 'Video Interest already exists.']);
      }

      $res = new VideoInterest();
      $res->emp_id = $user->id;
    }

    $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id);
    if ($accessCheck) {
      return response()->json(['error' => $accessCheck]);
    }

    $res->name = $request->input('name');
    $res->status = $request->input('status');
    $res->id_name = $request->input('id_name');
    $res->category_id = json_encode($newCategoryIds);
    $res->save();

    $msg = $request->has('id') ? 'Data updated successfully.' : 'Data added successfully.';
    return response()->json(['success' => $msg]);
  }

  public function deleteInterest(Request $request): JsonResponse
  {
    VideoInterest::destroy($request->id);
    return response()->json(['success' => $request->id]);
  }
}

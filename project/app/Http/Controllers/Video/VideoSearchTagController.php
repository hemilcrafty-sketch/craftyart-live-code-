<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Models\Video\VideoSearchTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoSearchTagController extends AppBaseController
{
  public function show_video_search_tag(Request $request)
  {
    $searchableFields = [
      ["id" => 'id', "value" => 'Id'],
      ["id" => 'name', "value" => 'Name'],
      ["id" => 'id_name', "value" => 'ID Name'],
      ["id" => 'status', "value" => 'Status']
    ];

    $filteredTags = $this->applyFiltersAndPagination($request, VideoSearchTag::query(), $searchableFields);
    $assignSubCat = User::where('user_type', 5)->get();

    return view('videos.filters.search_tags')->with([
      'searchTagArray' => $filteredTags,
      'searchableFields' => $searchableFields,
      'assignSubCat' => $assignSubCat
    ]);
  }

  public function submitVideoSearchTag(Request $request): JsonResponse
  {
    $user = auth()->user();

    if ($request->has('id') && $request->id) {
      $res = VideoSearchTag::find($request->id);
      if (!$res) {
        return response()->json(['error' => 'Video Search Tag not found.']);
      }
    } else {
      $existing = VideoSearchTag::where("name", $request->input('name'))->first();
      if ($existing) {
        return response()->json(['error' => 'Video Search Tag already exists.']);
      }

      $res = new VideoSearchTag();
      $res->emp_id = $user->id;
    }

    $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id);
    if ($accessCheck) {
      return response()->json(['error' => $accessCheck]);
    }

    $res->name = $request->input('name');
    $res->status = $request->input('status');
    $res->id_name = $request->input('id_name');
    $res->seo_emp_id = $request->input('seo_emp_id');
    $res->save();

    $msg = $request->has('id') ? 'Data updated successfully.' : 'Data added successfully.';
    return response()->json(['success' => $msg]);
  }

  public function deleteVideoSearchTag(Request $request): JsonResponse
  {
    VideoSearchTag::destroy($request->id);
    return response()->json(['success' => $request->id]);
  }
}

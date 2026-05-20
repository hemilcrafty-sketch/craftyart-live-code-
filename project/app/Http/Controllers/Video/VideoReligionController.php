<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Models\Video\VideoReligion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoReligionController extends AppBaseController
{
  public function index(Request $request)
  {
    $searchableFields = [
      ["id" => 'id', "value" => 'Id'],
      ["id" => 'name', "value" => 'Name'],
      ["id" => 'id_name', "value" => 'ID Name'],
      ["id" => 'status', "value" => 'Status']
    ];

    $filteredReligions = $this->applyFiltersAndPagination($request, VideoReligion::query(), $searchableFields);

    return view('videos.filters.religions')->with([
      'religionArray' => $filteredReligions,
      'searchableFields' => $searchableFields
    ]);
  }

  public function submit(Request $request): JsonResponse
  {
    $user = auth()->user();

    if ($request->has('id') && $request->id) {
      $res = VideoReligion::find($request->id);
      if (!$res) {
        return response()->json(['error' => 'Video Religion not found.']);
      }
    } else {
      $existing = VideoReligion::where("name", $request->input('name'))->first();
      if ($existing) {
        return response()->json(['error' => 'Video Religion already exists.']);
      }

      $res = new VideoReligion();
      $res->emp_id = $user->id;
    }

    $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id);
    if ($accessCheck) {
      return response()->json(['error' => $accessCheck]);
    }

    $res->name = $request->input('name');
    $res->status = $request->input('status');
    $res->id_name = $request->input('id_name');
    $res->save();

    $msg = $request->has('id') ? 'Data updated successfully.' : 'Data added successfully.';
    return response()->json(['success' => $msg]);
  }

  public function destroy($id): JsonResponse
  {
    VideoReligion::destroy($id);
    return response()->json(['success' => $id]);
  }
}

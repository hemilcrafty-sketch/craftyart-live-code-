<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Models\Video\VideoStyle;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class VideoStyleController extends AppBaseController
{
  public function show_video_style(Request $request): Factory|View|Application
  {
    $searchableFields = [
      ["id" => 'id', "value" => 'Id'],
      ["id" => 'name', "value" => 'Name'],
      ["id" => 'id_name', "value" => 'ID Name'],
      ["id" => 'status', "value" => 'Status']
    ];

    $filteredStyles = $this->applyFiltersAndPagination($request, VideoStyle::query(), $searchableFields);

    return view('videos.filters.styles')->with([
      'styleArray' => $filteredStyles,
      'searchableFields' => $searchableFields
    ]);
  }

  public function submitStyle(Request $request): JsonResponse
  {
    $user = auth()->user();

    if ($request->has('id') && $request->id) {
      $res = VideoStyle::find($request->id);
      if (!$res) {
        return response()->json(['error' => 'Video Style not found.']);
      }
    } else {
      $existing = VideoStyle::where("name", $request->input('name'))->first();
      if ($existing) {
        return response()->json(['error' => 'Video Style already exists.']);
      }

      $res = new VideoStyle();
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

  public function deleteStyle(Request $request): JsonResponse
  {
    VideoStyle::destroy($request->id);
    return response()->json(['success' => $request->id]);
  }
}

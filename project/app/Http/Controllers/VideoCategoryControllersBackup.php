<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\VideoCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Contracts\Foundation\Application;

class VideoCategoryControllersBackup extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'Status']];
        $allCategories = $this->applyFiltersAndPagination($request, VideoCategory::query(), $searchableFields);
        return view("video_cat.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $inputs = $request->all();

        if ($id) {
            $data = VideoCategory::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found'
                ]);
            }
        }

        $base64Images = [['img' => $inputs['thumb'], 'name' => "Thumb", 'required' => true]];

        if ($error = ContentManager::validateBase64Images($base64Images)) {
            return response()->json(['status' => false, 'error' => $error]);
        }

        try {
            $inputs['thumb'] = ContentManager::saveImageToPath(
                $inputs['thumb'],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $inputs['emp_id'] = Auth::id();

            $id ? VideoCategory::find($id)->update($inputs) : VideoCategory::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'VideoCat Category ' . ($id ? 'Updated' : 'Added')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            $VideoCat = VideoCategory::findOrFail($id);
            return response()->json([
                'status' => true,
                'data' => $VideoCat
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'VideoCat Not Found.'
            ], 404);
        }
    }

    public function destroy(VideoCategory $videoCat): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $videoCat->delete();
            } else {
                $videoCat['status'] = 0;
                $videoCat->save();
            }
            return response()->json([
                'status' => true,
                'success' => "Video Category Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\AudioVideoManager;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\VideoCategory;
use App\Models\VideoItem;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoItemControllersBackup extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $VideoCategory = VideoCategory::all();

        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'name', "value" => 'Name'], ["id" => 'is_premium', "value" => 'Is Premium'], ["id" => 'status', "value" => 'Status']];
        $videoItems = $this->applyFiltersAndPagination($request, VideoItem::with('VideoCategory'), $searchableFields);
        return view("video_item.index", compact('videoItems', 'VideoCategory', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            $inputs = $request->all();
            if ($id) {
                $data = VideoItem::find($id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data Not Found'
                    ]);
                }
            }

            if(!$id && !$request->hasFile('file')){
                return response()->json([
                    'success' => false,
                    'message' => 'Please select video file'
                ]);
            }

            $base64Images = [['img' => $inputs['thumb'], 'name' => "thumb", 'required' => true]];

            if ($error = ContentManager::validateBase64Images($base64Images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $inputs['thumb'] = ContentManager::saveImageToPath(
                $inputs['thumb'],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            unset($inputs['_token']);

            $inputs['emp_id'] = Auth::id();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                // Get temp file path before saving
                $tempVideoPath = $file->getRealPath();
                // Get file size
                $inputs['size'] = round(filesize($tempVideoPath) / 1024, 2);
                $meta = AudioVideoManager::getVideoMeta($tempVideoPath,true,'uploadedFiles/video_file/' . 'compress_' . uniqid() . '.mp4');
                $inputs['compress_vdo'] =$meta['compress_vdo'] ?? null;
                $inputs['duration'] = $meta['duration'] ?? 0;
                $inputs['width'] = $meta['width'] ?? null;
                $inputs['height'] = $meta['height'] ?? null;
                $fileName = 'video_' . uniqid() . '.' . $file->getClientOriginalExtension();
                StorageUtils::storeAs($file, 'uploadedFiles/video_file/', $fileName);
                $inputs['file'] = 'uploadedFiles/video_file/' . $fileName;
            }

            $id ? VideoItem::find($id)->update($inputs) : VideoItem::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'VideoItem Category ' . ($id ? 'Updated' : 'Added')
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            $VideoItem = VideoItem::find($id);
            return response()->json([
                'status' => true,
                'data' => $VideoItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'VideoItem Not Found.'
            ], 404);
        }
    }

    public function updateVideoItemPremium(Request $request): JsonResponse
    {
        $res = VideoItem::find($request->id);
        if ($res->is_premium == 1) {
            $res->is_premium = 0;
        } else {
            $res->is_premium = 1;
        }
        $res->save();
        return response()->json([
            'success' => "done"
        ]);
    }

    public function destroy(VideoItem $VideoItem): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $VideoItem->delete();
            } else {
                $VideoItem['status'] = 0;
                $VideoItem->save();
            }
            return response()->json([
                'status' => true,
                'success' => "VideoItem Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
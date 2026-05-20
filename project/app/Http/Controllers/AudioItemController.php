<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\AudioVideoManager;
use App\Http\Controllers\Utils\RoleManager;
use Exception;
use App\Models\AudioItem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AudioCategory;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Support\Facades\Auth;

class AudioItemController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $audioCategory = AudioCategory::all();

        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'name', "value" => 'name'], ["id" => 'is_premium', "value" => 'is_premium'], ["id" => 'status', "value" => 'status']];
        $audioItems = $this->applyFiltersAndPagination(
            $request,
            AudioItem::query(),
            $searchableFields
            ,
            [
                'parent_query' => AudioCategory::query(),
                'related_column' => 'name',
                'column_value' => 'audio_category_id',
            ]
        );
        return view("audio_items.index", compact('audioItems', 'audioCategory', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            $inputs = $request->all();

            if ($id) {
                $data = AudioItem::find($id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data Not Found'
                    ]);
                }
            }

            $accessCheck = $this->isAccessByRole("design", $id, $data->emp_id ?? null);
            if ($accessCheck) {
                return response()->json([
                    'status' => false,
                    'error' => $accessCheck,
                ]);
            }

            if (!$id && !$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select audio file'
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

            $inputs['emp_id'] = Auth::user()->id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $tempFilePath = $file->getRealPath();
                $fileSizeInBytes = filesize($tempFilePath);
                $inputs['size'] = round($fileSizeInBytes / 1024, 2);
                $inputs['duration'] = AudioVideoManager::getDuration($tempFilePath);
                $fileName = 'audio_' . uniqid() . '.' . $file->getClientOriginalExtension();
                StorageUtils::storeAs($file, 'uploadedFiles/audio_file/', $fileName);
                $inputs['file'] = 'uploadedFiles/audio_file/' . $fileName;
            }

            $id ? AudioItem::find($id)->update($inputs) : AudioItem::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'AudioItem Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            $audioItem = AudioItem::find($id);
            return response()->json([
                'status' => true,
                'data' => $audioItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'AudioItem Not Found.'
            ], 404);
        }
    }


    public function updateAudioItemPremium(Request $request): JsonResponse
    {
        $res = AudioItem::find($request->id);
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

    public function destroy(AudioItem $audioItem): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(Auth::user()->user_type)) {
                $audioItem->delete();
            } else {
                $audioItem['status'] = 0;
                $audioItem->save();
            }
            return response()->json([
                'status' => true,
                'success' => "AudioItem Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
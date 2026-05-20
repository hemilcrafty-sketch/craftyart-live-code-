<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AudioCategory;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Auth;

class AudioCategoryControllerBackup extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'Status']];
        $allCategories = $this->applyFiltersAndPagination($request, AudioCategory::query(), $searchableFields);
        return view("audio_cat.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $inputs = $request->all();

        if ($id) {
            $data = AudioCategory::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found'
                ]);
            }
        }

        $base64Images = [['img' => $inputs['thumb'], 'name' => "thumb", 'required' => true]];

        if ($error = ContentManager::validateBase64Images($base64Images)) {
            return response()->json(['status' => false, 'error' => $error]);
        }

        try {
            $inputs['thumb'] = ContentManager::saveImageToPath(
                $inputs['thumb'],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $inputs['emp_id'] = Auth::id();

            $id ? AudioCategory::find($id)->update($inputs) : AudioCategory::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'AudioCategory Category ' . ($id ? 'Updated' : 'Added')
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
            $audioCategory = AudioCategory::findOrFail($id);
            return response()->json([
                'status' => true,
                'data' => $audioCategory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'AudioCategory Not Found.'
            ], 404);
        }
    }

    public function destroy(AudioCategory $audioCat): JsonResponse
    {
        try {
            if(RoleManager::isAdmin(Auth::user()->user_type)){
                $audioCat->delete();
            } else {
                $audioCat['status'] = 0;
                $audioCat->save();
            }
            return response()->json([
                'status' => true,
                'success' => "Audio Item Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
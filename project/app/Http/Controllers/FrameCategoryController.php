<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\FrameCategory;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Support\Facades\Auth;

class FrameCategoryController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'Status']];
        $allCategories = $this->applyFiltersAndPagination($request, FrameCategory::query(), $searchableFields);
        return view("frame_cat.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $req): JsonResponse
    {
        $id = $req->input('id');

        if ($id) {
            $data = FrameCategory::find($id);
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

        $images = [
            ['img' => $req->thumb, 'name' => 'Thumb', 'required' => true]
        ];

        if ($error = ContentManager::validateBase64Images($images)) {
            return response()->json(['status' => false, 'error' => $error]);
        }



        try {
            $data = $req->all();
            $data['emp_id'] = Auth::user()->id;

            $data['thumb'] = ContentManager::saveImageToPath(
                $data['thumb'],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $id ? FrameCategory::find($id)->update($data) : FrameCategory::create($data);
            return response()->json([
                'status' => true,
                'success' => 'Frame Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function edit($id): JsonResponse
    {
        $category = FrameCategory::findOrFail($id);
        return response()->json($category);
    }

    public function destroy(FrameCategory $frameCategory): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(Auth::user()->user_type)) {
                $frameCategory->delete();
            } else {
                $frameCategory['status'] = 0;
                $frameCategory->save();
            }

            return response()->json([
                'status' => true,
                'success' => "Frame Category Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

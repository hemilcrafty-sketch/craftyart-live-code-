<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\GifCategory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GifCategoryControllersBackup extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'Status']];
        $allCategories = $this->applyFiltersAndPagination($request, GifCategory::query(), $searchableFields);
        return view("gif_categories.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $inputs = $request->all();

        if ($id) {
            $data = GifCategory::find($id);
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

            $id ? GifCategory::find($id)->update($inputs) : GifCategory::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'GifCategory Category ' . ($id ? 'Updated' : 'Added')
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
            $GifCat = GifCategory::findOrFail($id);
            return response()->json($GifCat);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Gif Category Not Found.'
            ], 404);
        }
    }

    public function destroy(GifCategory $gifCategory): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $gifCategory->delete();
            } else {
                $gifCategory['status'] = 0;
                $gifCategory->save();
            }
            return response()->json([
                'status' => true,
                'success' => "Gif Item Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
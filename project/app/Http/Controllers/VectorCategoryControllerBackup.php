<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\VectorCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;

class VectorCategoryControllerBackup extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'Status']];
        $allCategories = $this->applyFiltersAndPagination($request, VectorCategory::query(), $searchableFields);

        return view("vector_categories.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $inputs = $request->all();

        if ($id) {
            $data = VectorCategory::find($id);
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

            $id ? VectorCategory::find($id)->update($inputs) : VectorCategory::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'VectorCategory Category ' . ($id ? 'Updated' : 'Added')
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
            $svgCategory = VectorCategory::findOrFail($id);

            return response()->json($svgCategory);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'SVG Category Not Found.'
            ], 404);
        }
    }

    public function destroy(VectorCategory $vectorCategory): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $vectorCategory->delete();
            } else {
                $vectorCategory['status'] = 0;
                $vectorCategory->save();
            }
            return response()->json([
                'status' => true,
                'success' => "Frame Item Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
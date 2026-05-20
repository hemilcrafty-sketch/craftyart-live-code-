<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\BgCategory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BgCatController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'bg_category_name', "value" => 'Name'], ["id" => 'sequence_number', "value" => 'Sequence Number'], ["id" => 'status', "value" => 'status']];

        $allCategories = $this->applyFiltersAndPagination($request, BgCategory::query(), $searchableFields);
        return view("bg_cat.index", compact('allCategories', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $inputs = $request->all();
        if ($id) {
            $data = BgCategory::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found'
                ]);
            }
        }

        $accessCheck = $this->isAccessByRole("design",$id,$data->emp_id ?? null);
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $base64Images = [['img' => $inputs['bg_category_thumb'], 'name' => "thumb", 'required' => true]];

        if ($error = ContentManager::validateBase64Images($base64Images)) {
            return response()->json(['status' => false, 'error' => $error]);
        }

        try {
            $inputs['bg_category_thumb'] = ContentManager::saveImageToPath(
                $inputs['bg_category_thumb'],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $inputs['emp_id'] = Auth::id();

            $id ? BgCategory::find($id)->update($inputs) : BgCategory::create($inputs);
            return response()->json([
                'status' => true,
                'success' => 'BgCategory Category ' . ($id ? 'Updated' : 'Added')
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
            $BgCategory = BgCategory::findOrFail($id);
            return response()->json([
                'status' => true,
                'data' => $BgCategory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'BgCategory Not Found.'
            ], 404);
        }
    }

    public function destroy(BgCategory $showBgCat): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(Auth::user()->user_type)) {
                $showBgCat->delete();
            } else {
                $showBgCat['status'] = 0;
                $showBgCat->save();
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
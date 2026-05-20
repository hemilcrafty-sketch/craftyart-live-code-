<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\StickerCategory;
use Illuminate\Support\Facades\Auth;

class StickerCatController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'stk_category_name', "value" => 'Category Name'], ["id" => 'sequence_number', "value" => 'Sequence'], ["id" => 'status', "value" => 'Status']];
        $stickerCatArray = $this->applyFiltersAndPagination($request, StickerCategory::query(), $searchableFields);
        return view('sticker_cat.index', compact('stickerCatArray', 'searchableFields'));
    }

    public function edit($id): JsonResponse
    {
        try {
            $category = StickerCategory::findOrFail($id);
            return response()->json($category);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Sticker Category Not Found.'
            ], 404);
        }
    }

    public function store(Request $req): JsonResponse
    {
        $id = $req->input('id');

        if ($id) {
            $data = StickerCategory::find($id);
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
        $images = [
            ['img' => $req["stk_category_thumb"], 'name' => 'Category Thumb', 'required' => true],
        ];

        if ($error = ContentManager::validateBase64Images($images)) {
            return response()->json(['status' => false, 'error' => $error]);
        }

        try {
            $data = $req->all();
            $data['emp_id'] = Auth::user()->id;

            if ($req->has('stk_category_thumb')) {
                $data['stk_category_thumb'] = ContentManager::saveImageToPath(
                    $req->stk_category_thumb,
                    'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
                );
            }

            $id ? StickerCategory::find($id)->update($data) : StickerCategory::create($data);
            return response()->json([
                'status' => true,
                'success' => 'Sticker Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy(StickerCategory $showStickerCat): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(Auth::user()->user_type)) {
                $showStickerCat->delete();
            } else {
                $showStickerCat['status'] = 0;
                $showStickerCat->save();
            }
            return response()->json([
                'status' => true,
                'success' => "Frame Item Delete Successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\VectorItem;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\VectorCategory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class VectorItemControllerBackup extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'name', "value" => 'name'], ["id" => 'status', "value" => 'status']];
        $allCategories = $this->applyFiltersAndPagination($request, VectorItem::query(), $searchableFields);
        $perentCategory = VectorCategory::all();
        return view("vector_items.index", compact('allCategories', 'perentCategory', 'searchableFields'));
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $id = $req->input('svg_item_id');
            $SvgItem = $req->all();

            if ($id) {
                $data = VectorItem::find($id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data Not Found'
                    ]);
                }
            }

            $images = [
                ['img' => $SvgItem["thumb"], 'name' => 'thumb', 'required' => true],
                ['img' => $SvgItem["file"], 'name' => 'file', 'required' => true],
            ];

            if ($error = ContentManager::validateBase64Images($images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $SvgItem["thumb"] = ContentManager::saveImageToPath(
                $SvgItem["thumb"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $SvgItem["file"] = ContentManager::saveImageToPath(
                $SvgItem["file"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $dim = ContentManager::getImageSizeFromUrl(ContentManager::getStorageLink($SvgItem["file"]));
            $SvgItem["width"] = $dim['width'] ?? null;
            $SvgItem["height"] = $dim['height'] ?? null;
            $SvgItem['emp_id'] = auth()->id();

            $id ? VectorItem::find($id)->update($SvgItem) : VectorItem::create($SvgItem);
            return response()->json([
                'status' => true,
                'success' => 'VectorItem Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }



    public function edit($id): JsonResponse
    {
        try {
            $svgItem = VectorItem::findOrFail($id);
            return response()->json($svgItem);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'VectorItem Not Found.'
            ], 404);
        }
    }


    public function updateSvgItemPremium(Request $request): JsonResponse
    {
        $res = VectorItem::findorfail($request->id);
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

    public function destroy(VectorItem $vectorItem): JsonResponse
    {
        try {
            if(HelperController::isAdmin(FacadesAuth::user()->user_type)){
                $vectorItem->delete();
            } else {
                $vectorItem['status'] = 0;
                $vectorItem->save();
            }

            return response()->json([
                'status' => true,
                'success' => "svgItem Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
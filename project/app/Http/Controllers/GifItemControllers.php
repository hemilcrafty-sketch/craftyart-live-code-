<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\GifCategory;
use App\Models\GifItem;
use App\Http\Controllers\Utils\ContentManager;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;

class GifItemControllers extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'name', "value" => 'name'], ["id" => 'status', "value" => 'status']];
        $allCategories = $this->applyFiltersAndPagination(
            $request,
            GifItem::query(),
            $searchableFields
            ,
            [
                'parent_query' => GifCategory::query(),
                'related_column' => 'name',
                'column_value' => 'gif_category_id',
            ]
        );
        $perentCategory = GifCategory::all();
        return view("gif_items.index", compact('allCategories', 'perentCategory', 'searchableFields'));
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $id = $req->input('gif_item_id');
            $GifItem = $req->all();

            if ($id) {
                $data = GifItem::find($id);
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
                ['img' => $GifItem["thumb"], 'name' => 'thumb', 'required' => true],
                ['img' => $GifItem["file"], 'name' => 'file', 'required' => true],
            ];

            if ($error = ContentManager::validateBase64Images($images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $GifItem["thumb"] = ContentManager::saveImageToPath(
                $GifItem["thumb"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $GifItem["file"] = ContentManager::saveImageToPath(
                $GifItem["file"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $dim = ContentManager::getImageSizeFromUrl(ContentManager::getStorageLink($GifItem["file"]));
            $GifItem["width"] = $dim['width'] ?? null;
            $GifItem["height"] = $dim['height'] ?? null;
            $GifItem['emp_id'] = auth()->id();

            $id ? GifItem::find($id)->update($GifItem) : GifItem::create($GifItem);
            return response()->json([
                'status' => true,
                'success' => 'GifItem Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }



    public function edit($id): JsonResponse
    {
        try {
            $GifItem = GifItem::findOrFail($id);
            return response()->json($GifItem);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'GifItem Not Found.'
            ], 404);
        }
    }


    public function updateGifItemPremium(Request $request): JsonResponse
    {
        $res = GifItem::findorfail($request->id);
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

    public function destroy(GifItem $GifItem): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(FacadesAuth::user()->user_type)) {
                $GifItem->delete();
            } else {
                $GifItem['status'] = 0;
                $GifItem->save();
            }

            return response()->json([
                'status' => true,
                'success' => "GifItem Delete Successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
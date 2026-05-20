<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\StickerItem;
use App\Models\StickerCategory;
use App\Models\StickerMode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StickerItemControllerBackup extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'Id'], ["id" => 'sticker_name', "value" => 'Sticker Name'],  ["id" => 'status', "value" => 'Status']];
        $stickerItems = $this->applyFiltersAndPagination($request, StickerItem::query(), $searchableFields,[
            'parent_query' =>  StickerCategory::query(),
            'related_column' => 'stk_category_name',
            'column_value' => 'stk_cat_id',
        ]);
        $dataArray = [
            'sticker_mode' => StickerMode::all(),
            'stkCatArray' => StickerCategory::orderBy('id', 'desc')->get(),
        ];
        return view('sticker_item.index', compact('stickerItems', 'dataArray', 'searchableFields'));
    }

    public function edit($id): JsonResponse
    {
        try {
            $sticker = StickerItem::findOrFail($id);
            return response()->json($sticker);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Sticker Item Not Found.'
            ], 404);
        }

    }

    public function store(Request $req): JsonResponse
    {
        try {
            $id = $req->input('id');
            $frameItem = $req->all();

            $existingThumb = null;
            $existingImage = null;
            if ($id) {
                $data = StickerItem::find($id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data Not Found'
                    ]);
                }
                $existingThumb = $data->sticker_thumb;
                $existingImage = $data->sticker_image;
            }
            
            $images = [
                ['img' => $frameItem["sticker_thumb"], 'name' => 'Sticker Thumb', 'required' => true,'existingImg' => $existingThumb],
                ['img' => $frameItem["sticker_image"], 'name' => 'Sticker Image', 'required' => true,'existingImg' => $existingImage],
            ];

            if ($error = ContentManager::validateBase64Images($images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $frameItem["sticker_thumb"] = ContentManager::saveImageToPath(
                $frameItem["sticker_thumb"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $frameItem["sticker_image"] = ContentManager::saveImageToPath(
                $frameItem["sticker_image"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $dim = ContentManager::getImageSizeFromUrl(ContentManager::getStorageLink($frameItem["sticker_image"]));
            $frameItem["width"] = $dim['width'] ?? null;
            $frameItem["height"] = $dim['height'] ?? null;

            $frameItem['emp_id'] = auth()->id();

            $id ? StickerItem::find($id)->update($frameItem) : StickerItem::create($frameItem);
            return response()->json([
                'status' => true,
                'success' => 'Sticker Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function status_update(Request $request): JsonResponse
    {
        $res = StickerItem::findorfail($request->id);
        if ($res->status == 1) {
            $res->status = 0;
        } else {
            $res->status = 1;
        }
        $res->save();
        return response()->json([
            'success' => "done"
        ]);
    }

    public function premium_update(Request $request): JsonResponse
    {
        $res = StickerItem::findorfail($request->id);
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

    public function destroy(StickerItem $stickerItem): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $stickerItem->delete();
            } else {
                $stickerItem['status'] = 0;
                $stickerItem->save();
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
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\BgItem;
use App\Models\BgCategory;
use App\Models\BgMode;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class BgItemControllerBackup extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'id'],
            ["id" => 'bg_name', "value" => 'name'],
            ["id" => 'status', "value" => 'status']
        ];

        $allCategories = $this->applyFiltersAndPagination($request, BgItem::with(['BgCategory', 'BgMode']), $searchableFields);
        $perentCategory = BgCategory::all();
        $bgMode = BgMode::all();
        return view("bg_item.index", compact('allCategories', 'bgMode', 'perentCategory', 'searchableFields'));
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $id = $req->input('id');
            $BgItem = $req->all();
            if ($id) {
                $data = BgItem::find($id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data Not Found'
                    ]);
                }
            }
            $images = [
                ['img' => $BgItem["bg_thumb"], 'name' => 'Background Thumb', 'required' => true],
                ['img' => $BgItem["bg_image"], 'name' => 'Background Image', 'required' => true],
            ];

            if ($error = ContentManager::validateBase64Images($images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $BgItem["bg_thumb"] = ContentManager::saveImageToPath(
                $BgItem["bg_thumb"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $BgItem["bg_image"] = ContentManager::saveImageToPath(
                $BgItem["bg_image"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $dim = ContentManager::getImageSizeFromUrl(ContentManager::getStorageLink($BgItem["bg_image"]));
            $BgItem["width"] = $dim['width'] ?? null;
            $BgItem["height"] = $dim['height'] ?? null;


            $BgItem['emp_id'] = auth()->id();

            $id ? BgItem::find($id)->update($BgItem) : BgItem::create($BgItem);
            return response()->json([
                'status' => true,
                'success' => 'BgItem Category ' . ($id ? 'Updated' : 'Added')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            $BgItem = BgItem::findOrFail($id);
            return response()->json($BgItem);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'BgItem Not Found.'
            ], 404);
        }
    }

    public function updatebackgroundItemPremium(Request $request): JsonResponse
    {
        $res = BgItem::find($request->id);
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

    public function destroy(BgItem $showBgItem): JsonResponse
    {
        try {
            if (HelperController::isAdmin(Auth::user()->user_type)) {
                $showBgItem->delete();
            } else {
                $showBgItem['status'] = 0;
                $showBgItem->save();
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
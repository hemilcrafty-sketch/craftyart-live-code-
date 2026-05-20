<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use Exception;
use App\Models\FrameItem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\FrameCategory;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Support\Facades\Auth;

class FrameItemController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'id'], ["id" => 'name', "value" => 'Name'], ["id" => 'is_premium', "value" => 'Premium'], ["id" => 'status', "value" => 'Status']];
        $framesItems = $this->applyFiltersAndPagination(
            $request,
            FrameItem::query(),
            $searchableFields
            ,
            [
                'parent_query' => FrameCategory::query(),
                'related_column' => 'name',
                'column_value' => 'frame_category_id',
            ]
        );
        $allCategories = FrameCategory::all();

        return view("frame_items.index", compact('framesItems', 'allCategories', 'searchableFields'));
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $id = $req->input('frame_item_id');
            // dd($id);
            $frameItem = $req->all();

            if ($id) {
                $data = FrameItem::find($id);
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
                ['img' => $frameItem["thumb"], 'name' => 'Thumb', 'required' => true],
                ['img' => $frameItem["file"], 'name' => 'File', 'required' => true],
            ];

            if ($error = ContentManager::validateBase64Images($images)) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $frameItem["thumb"] = ContentManager::saveImageToPath(
                $frameItem["thumb"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $frameItem["file"] = ContentManager::saveImageToPath(
                $frameItem["file"],
                'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
            );

            $dim = ContentManager::getImageSizeFromUrl(ContentManager::getStorageLink($frameItem["file"]));
            $frameItem["width"] = $dim['width'] ?? null;
            $frameItem["height"] = $dim['height'] ?? null;
            $frameItem['emp_id'] = Auth::user()->id;

            $id ? FrameItem::find($id)->update($frameItem) : FrameItem::create($frameItem);
            return response()->json(['status' => true, 'success' => "Frame Item ($id ? 'Updated' : 'Added')"]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function edit($id): JsonResponse
    {
        $category = FrameItem::findOrFail($id);
        return response()->json($category);
    }

    public function updateFrameItemPremium(Request $request): JsonResponse
    {
        $res = FrameItem::find($request->id);
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

    public function destroy(FrameItem $frameItem): JsonResponse
    {
        try {
            if (RoleManager::isAdmin(Auth::user()->user_type)) {
                $frameItem->delete();
            } else {
                $frameItem['status'] = 0;
                $frameItem->save();
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

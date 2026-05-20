<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AudioCategory;
use App\Models\AudioItem;
use App\Models\BgCategory;
use App\Models\BgItem;
use App\Models\BgMode;
use App\Models\FrameCategory;
use App\Models\FrameItem;
use App\Models\GifCategory;
use App\Models\GifItem;
use App\Models\RawDatas;
use App\Models\StickerCategory;
use App\Models\StickerItem;
use App\Models\StickerMode;
use App\Models\VectorCategory;
use App\Models\VectorItem;
use App\Models\VideoCategory;
use App\Models\VideoItem;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RawDatasController extends AppBaseController
{
  public function index(Request $request): Factory|View|Application
  {
    $searchableFields = [
      ['id' => 'id', 'value' => "id"],
      ['id' => 'name', 'value' => "Name"],
      ['id' => 'asset_type', 'value' => "Asset Type"]
    ];

    $query = RawDatas::query()->where('status', 1);
    $rawDataItems = $this->applyFiltersAndPagination($request, $query, $searchableFields);

    $data['bgCatArray'] = BgCategory::all();
    $data['stkCatArray'] = StickerCategory::all();
    $data['frameCatArray'] = FrameCategory::all();
    $data['svgCatArray'] = VectorCategory::all();
    $data['gifCatArray'] = GifCategory::all();
    $data['videoCatArray'] = VideoCategory::all();

    $data['audioCatArray'] = AudioCategory::all();
    $data['assetTypes'] = RawDatas::ASSET_TYPES;
    $data['rawTypes'] = RawDatas::RAW_TYPES;
    $data['bg_mode'] = BgMode::all();
    $data['sticker_mode'] = StickerMode::all();

    return view("raw_datas.index", compact('searchableFields', 'rawDataItems', 'data'));
  }

  public function edit($id): JsonResponse
  {
    $rawItem = RawDatas::find($id);

    if (!$rawItem) {
      return response()->json([
        "status" => false,
        "message" => "Raw data item not found"
      ], 404);
    }
    return response()->json([
      "status" => true,
      'data' => $rawItem
    ]);
  }

  public function update(Request $request): JsonResponse
  {
    try {
      $data = $request->all();

      $commonInputs = [
        "emp_id" => Auth::user()->id,
        "name" => $data['name'],
        "width" => $data['width'] ?? null,
        "height" => $data['height'] ?? null,
        "is_premium" => $data['is_premium'],
        "status" => $data['status'],
        "thumb" => $data['thumbnail'] ?? null,
        "file" => $data['image'],
      ];

      if ($data['parent_category_id'] == 0) {
        $res = new StickerItem($commonInputs);
        $res->stk_cat_id = $data['sub_category_id'];
        $res->sticker_type = $request->item_type;
        $res->sticker_name = $data['name'];
        $res->sticker_thumb = $data['thumbnail'];
        $res->sticker_image = $data['image'];
        $res->save();

      } else if ($data['parent_category_id'] == 1) {
        $res = new BgItem($commonInputs);
        $res->bg_cat_id = $data['sub_category_id'];
        $res->bg_type = $request->item_type;
        $res->bg_name = $data['name'];
        $res->bg_thumb = $data['thumbnail'];
        $res->bg_image = $data['image'];
        $res->save();

      } else if ($data['parent_category_id'] == 2) {
        $commonInputs["frame_category_id"] = $data['sub_category_id'];
        FrameItem::create($commonInputs);

      } else if ($data['parent_category_id'] == 3) {
        $commonInputs["svg_category_id"] = $data['sub_category_id'];
        VectorItem::create($commonInputs);

      } else if ($data['parent_category_id'] == 4) {
        $commonInputs["audio_category_id"] = $data['sub_category_id'];
        $commonInputs["duration"] = $data['duration'];
        $commonInputs["size"] = $data['asset_size'];
        AudioItem::create($commonInputs);

      } else if ($data['parent_category_id'] == 5) {
        $commonInputs["gif_category_id"] = $data['sub_category_id'];
        GifItem::create($commonInputs);

      } else if ($data['parent_category_id'] == 6) {
        $commonInputs["video_category_id"] = $data['sub_category_id'];
        $commonInputs["duration"] = $data['duration'];
        $commonInputs["size"] = $data['asset_size'];
        $commonInputs["compress_vdo"] = $data['compress_vdo'];
        VideoItem::create($commonInputs);
      }


      // Mark original raw data record as processed
//      RawDatas::where('id', $data['rawDataItemId'])->update(['status' => 0]);

      return response()->json([
        'status' => true,
        'success' => "Updated successfully.",
        'data' => $data
      ]);

    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'error' => $e->getMessage(),
      ]);
    }
  }


  /*public function destroy($id): JsonResponse
  {
    try {
      $svgItem = VectorItem::find($id);
      if (!$svgItem) {
        return response()->json([
          'status' => false,
          'error' => 'SVG item not found.',
        ]);
      }

      if (!Str::contains($svgItem->thumb, 'no_image')) {
        StorageUtils::delete($svgItem->thumb);
      }

      $svgItem->delete();

      return response()->json([
        'status' => true,
        'success' => 'SVG item deleted successfully.',
      ]);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'error' => $e->getMessage(),
      ]);
    }
  }*/
}
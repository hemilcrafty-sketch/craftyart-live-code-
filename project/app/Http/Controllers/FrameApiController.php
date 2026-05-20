<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\FrameCategory;
use App\Models\FrameItem;
use Illuminate\Http\Request;

class FrameApiController extends ApiController
{
    public function getFrameData(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->input('page', 1);

        $limit = HelperController::getPaginationLimit(size: 30);
        $totalCount = FrameCategory::with('frameItem')
            ->where('status', 1)
            ->orderBy('created_at', 'DESC')->count();
        $total_pages = ceil($totalCount / $limit);

        $resFrameCat = FrameCategory::with('frameItem')
            ->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $frameResult = $resFrameCat->map(function ($item) {
            $rowArr = [];
            $rowArr['category_id'] = $item->id;
            $rowArr['category_name'] = $item->name;
            $rowArr['category_thumb'] = HelperController::$mediaUrl . $item->thumb;
            $frameItems = (isset($item->frameItem) && $item->frameItem != null) ? $item->frameItem : [];
            $rowArr['datas'] = [];

            foreach ($frameItems as $frameItem) {
                $frameData = [
                    'category_id' => $frameItem->frame_category_id,
                    'id' => $frameItem->id,
                    'name' => $frameItem->name,
                    'thumb' => HelperController::$mediaUrl . $frameItem->thumb,
                    'file' => HelperController::$mediaUrl . $frameItem->file,
                    'width' => $frameItem->width,
                    'height' => $frameItem->height,
                    'type' => $frameItem->type,
                    'latest' => 0,
                    'is_premium' => $frameItem->is_premium,
                ];
                $rowArr['datas'][] = $frameData;
            }
            return !empty($rowArr['datas']) ? $rowArr : null;
        })->filter()->values();


        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "",
            [
                'current_page' => $page,
                'isLastPage' => $page >= $total_pages,
                'datas' => $frameResult,
            ]
        ));
    }

    public function getCatFrameData(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $category_id = $request->get('id');
        $page = $request->has('page') ? $request->get('page') : 1;
        $hasPagination = $request->has('page');

        if ($hasPagination) {
            $limit = HelperController::getPaginationLimit(size: 50);
        } else {
            $limit = HelperController::getPaginationLimit(size: 1000);
        }

        $totalCount = FrameItem::where("status", '1')->where("frame_category_id", $category_id)->count();
        $total_pages = ceil($totalCount / $limit);

        $itemData = FrameItem::where("status", '1')
            ->where("frame_category_id", $category_id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $item_rows = array();

        if ($itemData != null && $itemData->count() != 0) {
            foreach ($itemData as $item) {
                $item_rows[] = array(
                    'category_id' => $item->frame_category_id,
                    'id' => $item->id,
                    'name' => $item->name,
                    'thumb' => HelperController::$mediaUrl . $item->thumb,
                    'file' => HelperController::$mediaUrl . $item->file,
                    'width' => $item->width,
                    'height' => $item->height,
                    'type' => $item->type,
                    'latest' => 0,
                    'is_premium' => $item->is_premium,
                );
            }
            $msg = 'Loading Success!';
        } else {
            $msg = 'Data not found!';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'current_page' => $page,
                'isLastPage' => $page >= $total_pages,
                'datas' => $item_rows,
            ]
        ));
    }
}

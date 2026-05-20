<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use Illuminate\Http\Request;
use App\Models\StickerCategory;
use App\Models\StickerItem;

class StickerController extends ApiController
{

    function getStickers(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $cat_rows = array();

        $page = $request->has('page') ? $request->get('page') : 1;
        $hasPagination = $request->has('page');

        if ($hasPagination) {
            $limit = HelperController::getPaginationLimit(size: 50);
        } else {
            $limit = HelperController::getPaginationLimit(size: 1000);
        }
        $totalCount = StickerCategory::where("status", '1')->count();
        $total_pages = ceil($totalCount / $limit);

        $catData = StickerCategory::where("status", '1')
            ->orderBy('sequence_number', 'ASC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        if ($catData != null && $catData->count() != 0) {
            foreach ($catData as $row) {
                $itemData = StickerItem::where("status", '1')
                    ->where("stk_cat_id", $row->id)
                    ->orderBy('created_at', 'DESC')
                    ->take(9)
                    ->get();
                if ($itemData->count() != 0) {
                    $item_rows = array();
                    foreach ($itemData as $item) {
                        $item_rows[] = array(
                            'category_id' => $item->stk_cat_id,
                            'id' => $item->id,
                            'name' => $item->sticker_name,
                            'thumb' => HelperController::$mediaUrl . $item->sticker_thumb,
                            'file' => HelperController::$mediaUrl . $item->sticker_image,
                            'type' => $item->sticker_type,
                            'width' => $item->width,
                            'height' => $item->height,
                            'latest' => 0,
                            'is_premium' => $item->is_premium,
                        );
                    }

                    $cat_rows[] = array(
                        'category_id' => $row->id,
                        'category_name' => $row->stk_category_name,
                        'category_thumb' => HelperController::$mediaUrl . $row->stk_category_thumb,
                        'datas' => $item_rows,
                    );
                }
            }
            $msg = 'Loading Success!';
        } else {
            $msg = 'Data not found!';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'current_page' => $page,
                'isLastPage' => $page >= $total_pages,
                'datas' => $cat_rows,
            ]
        ));
    }

    function getCategoryStickers(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $item_rows = array();

        $category_id = $request->get('id');
        $page = $request->has('page') ? $request->get('page') : 1;
        $hasPagination = $request->has('page');

        if ($hasPagination) {
            $limit = HelperController::getPaginationLimit(size: 50);
        } else {
            $limit = HelperController::getPaginationLimit(size: 1000);
        }
        $totalCount = StickerItem::where("status", '1')->where("stk_cat_id", $category_id)->count();
        $total_pages = ceil($totalCount / $limit);

        $itemData = StickerItem::where("status", '1')
            ->where("stk_cat_id", $category_id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        if ($itemData != null && $itemData->count() != 0) {
            foreach ($itemData as $item) {
                $item_rows[] = array(
                    'category_id' => $item->stk_cat_id,
                    'id' => $item->id,
                    'name' => $item->sticker_name,
                    'thumb' => HelperController::$mediaUrl . $item->sticker_thumb,
                    'file' => HelperController::$mediaUrl . $item->sticker_image,
                    'type' => $item->sticker_type,
                    'width' => $item->width,
                    'height' => $item->height,
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


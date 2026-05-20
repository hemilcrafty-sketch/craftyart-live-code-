<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\UserData;
use Illuminate\Http\Request;
use App\Models\BgCategory;
use App\Models\BgItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BgController extends ApiController
{

    function getBgs(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $cat_rows = array();

        $page = $request->has('page') ? $request->get('page') : 1;
        $hasPagination = $request->has('page');

        if ($hasPagination) {
            $limit = 50;
        } else {
            $limit = 1000;
        }

        $totalCount = BgCategory::where("status", '1')->count();
        $total_pages = ceil($totalCount / $limit);

        $catData = BgCategory::where("status", '1')
            ->orderBy('sequence_number', 'ASC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        if ($catData != null && $catData->count() != 0) {
            foreach ($catData as $row) {
                $itemData = BgItem::where("status", '1')->where("bg_cat_id", $row->id)->orderBy('created_at', 'DESC')->take(9)->get();
                if ($itemData->count() != 0) {
                    $item_rows = array();
                    foreach ($itemData as $item) {
                        $item_rows[] = array(
                            'category_id' => $item->bg_cat_id,
                            'id' => $item->id,
                            'name' => $item->bg_name,
                            'thumb' => HelperController::$mediaUrl . $item->bg_thumb,
                            'file' => HelperController::$mediaUrl . $item->bg_image,
                            'type' => $item->bg_type,
                            'width' => $item->width,
                            'height' => $item->height,
                            'latest' => 0,
                            'is_premium' => $item->is_premium,
                        );
                    }

                    $cat_rows[] = array(
                        'category_id' => $row->id,
                        'category_name' => $row->bg_category_name,
                        'category_thumb' => HelperController::$mediaUrl . $row->bg_category_thumb,
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

    function getCategoryBgs(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $item_rows = array();

        $category_id = $request->get('id');
        $page = $request->has('page') ? $request->get('page') : 1;
        $hasPagination = $request->has('page');

        if ($hasPagination) {
            $limit = 50;
        } else {
            $limit = 1000;
        }
        $totalCount = BgItem::where("status", '1')->where("bg_cat_id", $category_id)->count();
        $total_pages = ceil($totalCount / $limit);

        $itemData = BgItem::where("status", '1')
            ->where("bg_cat_id", $category_id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        if ($itemData != null && $itemData->count() != 0) {
            foreach ($itemData as $item) {
                $item_rows[] = array(
                    'category_id' => $item->bg_cat_id,
                    'id' => $item->id,
                    'name' => $item->bg_name,
                    'thumb' => HelperController::$mediaUrl . $item->bg_thumb,
                    'file' => HelperController::$mediaUrl . $item->bg_image,
                    'type' => $item->bg_type,
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

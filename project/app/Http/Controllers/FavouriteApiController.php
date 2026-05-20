<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Draft;
use App\Models\LikedProduct;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Size;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\WebTemplateViewHistory;
use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use App\Models\UserData;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FavouriteApiController extends ApiController
{

    function favourite(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get("id");

        if (!Design::where("string_id", $id)->where("status", 1)->exists()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }

        $isExists = LikedProduct::where("user_id", $this->uid)->where("product_id", $id)->exists();

        if ($isExists) {
            $msg = "Unstarred";
            $isSuccess = LikedProduct::where("user_id", $this->uid)->where("product_id", $id)->delete();
        } else {
            $msg = "Starred";
            $isSuccess = LikedProduct::insert(['user_id' => $this->uid, 'product_id' => $id]);
        }

        if ($isSuccess) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }
    }

    function getAllFavourite(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = HelperController::getPaginationLimit();

        $likedProducts = LikedProduct::where("user_id", $this->uid)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['product_id'], 'page', $page);

        $productIds = $likedProducts->getCollection()->pluck('product_id')->unique()->toArray();

        $designs = Design::whereIn('string_id', $productIds)
            ->where('status', 1)
            ->get()
            ->keyBy('string_id');

        $categoryIds = $designs->pluck('new_category_id')->unique()->toArray();
        $categories = NewCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        $item_rows = [];

        $rates = RateController::getRates();

        foreach ($likedProducts as $likedItem) {
            $item = $designs[$likedItem->product_id] ?? null;
            if ($item) {
                $catRow = $categories[$item->new_category_id] ?? null;
                $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
                if ($catRow != null) {
                    $catLink = $catRow->cat_link;
                }

                $item_rows[] = HelperController::getItemData(
                    uid: $this->uid,
                    catRow: $catRow,
                    item: $item,
                    thumbArray: json_decode($item->thumb_array),
                    catLink: $catLink,
                    rates: $rates
                );
            }
        }

        $msg = 'Loading Success!';

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'isLastPage' => count($item_rows) < $limit,
                'datas' => $item_rows
            ]
        ));

    }
}

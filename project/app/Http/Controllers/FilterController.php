<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Color;
use App\Models\Design;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\Religion;
use App\Models\Size;
use App\Models\Style;
use App\Models\Theme;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends ApiController
{

    function getFilters(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        if (!$request->id) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Params missing"));

        $callback = function () use ($request) {

            $category = NewCategory::findId(isStatus: 1, id: $request->id);
            if (!$category) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Data not found"));

            $rootParentId = $category->parent;
            $rootParentId = $rootParentId ? $rootParentId['id'] : $request->id;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);

            $colors = Color::where('status', 1)->orderBy("id", "desc")->get();
            $interests = Interest::select(['id', 'id_name', 'name'])->whereJsonContains('new_category_id', $catId)->where('status', 1)->orderBy("id", "desc")->get();
            $sizes = Size::select(["id", "size_name as name", "width_ration as p_width", "height_ration as p_height", "width as l_width", "height as l_height", "id_name"])->whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
            $styles = Style::select(['id', 'id_name', 'name'])->where('status', 1)->orderBy("id", "desc")->get();

            $languages = $this->getCounts(
                Language::class,
                ['id', 'id_name', 'name'],
                'lang_id'
            );

            $religions = $this->getCounts(
                Religion::class,
                ['id', 'religion_name as name', 'id_name'],
                'religion_id'
            );
            $themes = $this->getCounts(
                Theme::class,
                ['id', 'id_name', 'name'],
                'style_id',
                'new_category_id',
                $catId
            );

            $response['colors'] = $colors;
            $response['interests'] = $interests;
            $response['languages'] = $languages;
            $response['religions'] = $religions;
            $response['sizes'] = $sizes;
            $response['styles'] = $styles;
            $response['themes'] = $themes;

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loading Success!", [
                'datas' => $response
            ]));

        };

        if (HelperController::$cacheEnabled) $response = Cache::tags(["filter_api"])->remember("filter_api", HelperController::$cacheTimeOut, $callback);
        else $response = $callback();

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

    function getSizes(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $callback = function () {

            $url = HelperController::$mediaUrl;

            $sizes = Size::select(
                ["id",
                    "size_name as name",
                    DB::raw("CONCAT('$url', thumb) as thumb"),
                    "width_ration as p_width",
                    "height_ration as p_height",
                    "width as l_width",
                    "height as l_height",
                    "id_name"]
            )->where('status', 1)->get();

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loading Success!", [
                'datas' => $sizes
            ]));

        };

        if (HelperController::$cacheEnabled) $response = Cache::tags(["sizes_api"])->remember("sizes_api", HelperController::$cacheTimeOut, $callback);
        else $response = $callback();

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

    function getCounts($model, $selectFields, $jsonField, $filterColumn = null, $filterValue = null, $orderColumn = "id", $orderDirection = "desc"): array
    {
        $query = $model::select($selectFields)->where('status', 1)->orderBy($orderColumn, $orderDirection);
        if ($filterColumn && $filterValue) {
            $query->whereJsonContains($filterColumn, strval($filterValue));
        }
        $records = $query->get();
        $counts = [];
        foreach ($records as $row) {
            $count = Design::whereJsonContains($jsonField, strval($row->id))->count();
            $counts[] = [
                'data' => $row,
                'count' => $count,
            ];
        }
        // Sort by count in descending order
        usort($counts, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $response = [];
        foreach ($counts as $data) {
            $response[] = $data['data'];
        }
        return $response;
    }
}

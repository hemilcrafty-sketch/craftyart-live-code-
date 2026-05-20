<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\PageSlugHistory;
use Illuminate\Http\Request;

class PageSlugHistoryController extends ApiController
{
    public function getData(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $response['page_slug_history'] = PageSlugHistoryController::get($request->type);
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));
    }


    //"0" => K Page
    //"1" => Special Page
    //"2" => Blog Page
    //"3" => New Category Page
    //"4" => Old Category Page
    //"5" => Product Page

    public static function get($type): array
    {
//        return PageSlugHistory::select('old_slug', 'new_slug')->where('type', $type)->get();
        return [];
    }

    public static function findOne($id, $type)
    {
        $data = PageSlugHistory::where('old_slug', $id)->where('type', $type)->first();
        if ($data) return $data->new_slug;
        return null;
    }
}

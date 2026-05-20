<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Draft;
use App\Models\LikedProduct;
use App\Models\NewCategory;
use App\Models\SpecialKeyword;
use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use App\Models\UserData;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TemplateApiController extends ApiController
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
        $limit = 20;

        $datas = LikedProduct::where("user_id", $this->uid)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $item_rows = [];
        foreach ($datas as $likedItem) {
            $item = Design::where("string_id", $likedItem->product_id)->where("status", 1)->first();
            if ($item) {
                $catRow = Category::find($item->category_id);
                if ($catRow != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
                }
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

    function getAllFab(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $refWidth = $request->get('w', 1);
        $refHeight = $request->get('h', 1);
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = 20;

        $tempRatio = $refWidth / $refHeight;
        $tempRatio = round($tempRatio, 2);

        $itemData = Design::where("status", 1)
            ->where("ratio", $tempRatio)
            ->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();

        $item_rows = [];
        if ($itemData != null) {
            foreach ($itemData as $item) {
                $catRow = Category::find($item->category_id);
                if ($catRow != null) {
                    $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
                }
            }

            $msg = 'Loading Success!';
        } else {
            $msg = 'Data not found!';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'isLastPage' => count($item_rows) < $limit,
                'datas' => $item_rows
            ]
        ));

    }

    function getTemplates(Request $request): array|string
    {
//        if ($this->isFakeRequest($request)) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
//        }

        $response = [];

        // only cat section
        $categories = NewCategory::getAllCategoriesWithSubcategories(1);
        $catDatas = [];
        foreach ($categories as $key => $category) {

            $catDatas[$key]['category_id'] = $category->id;
            $catDatas[$key]['id_name'] = $category->id_name;
            $catDatas[$key]['category_name'] = $category->category_name;
            $catDatas[$key]['category_thumb'] = HelperController::$mediaUrl . $category->category_thumb;

            $allCateIds = CategoryTemplatesApiController::getAllIds($category->toArray());

            $query = Design::whereIn('new_category_id', $allCateIds)->where('status', 1)->count();

            if ($query <= 0) {
                unset($catDatas[$key]);
            }
        }
        $response['catlist'] = $catDatas;
        // end of only cat section

        // inspired section
        if ($this->uid) {
            $draft = Draft::where('user_id', $this->uid)->whereNotNull('template_id')->latest()->first();
            if ($draft) {
                $item = Design::where('string_id', $draft->template_id)->where('status', 1)->first();
                if ($item) {
                    $SearchApi = new SearchApiController($request);
                    $searchData = $SearchApi->searchTemplates(json_decode($item->related_tags)[0], 1, null, 20, $item->id_name);
                    $response['inspired'] = $searchData['datas'];
                }
            }
        }
        // end of inspired section

        // trending section
        $limit = 20;
        $itemData = Design::where("trending_views", ">", 0)->where("status", 1)->whereHas('parent', function ($query) {
            $query->where('status', 1);
        })->orderBy('trending_views', 'DESC')->paginate($limit, ['*'], 'page', 1);
        $item_rows = array();
        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $response['trending'] = $item_rows;
        // end trending section

        // cats section
        $response['cats'] = CategoryTemplatesApiController::getAllNewCategories($this->uid, true);
        // end cats section

        // upcoming event section
        $upcomingEvents = Design::whereRaw("STR_TO_DATE(end_date, '%m/%d/%Y') > ?", [now()])->orderByRaw("STR_TO_DATE(end_date, '%m/%d/%Y') ASC")->get();

        $response['upcomingEvents'] = $upcomingEvents;
        // end of upcoming event section

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, 'Loaded!', $response));
    }

    function getKeyTemplates(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized", ["page_slug_history" => []]));
        }

        $keyName = $request->get('id');
        $filter = isset($request->filter) ? $request->filter : [];
        $page = $request->has('page') ? $request->get('page') : 1;

        $keyData = SpecialKeyword::where('name', $keyName)->where('status', '1')->first();

        if (!$keyData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found", ["page_slug_history" => PageSlugHistoryController::get(1)]));
        }

        $keyDataId = "\"" . $keyData->id . "\"";

        $limit = 20;

        $templatesQuery = Design::where("status", 1)
            ->where('special_keywords', 'like', '%' . $keyDataId . '%')
            ->whereHas('parent', function ($query) {
                $query->where('status', 1);
            });

        if (!empty($filter)) {
            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        $itemData = $templatesQuery->orderBy('created_at', 'DESC')->paginate($limit, ['*'], 'page', $page)->onEachSide(-1);

        $item_rows = [];

        foreach ($itemData->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $msg = 'Loading Success!';

        $response['pagination'] = PaginationController::getPagination($itemData);
        $response['total_page'] = $itemData->lastPage();
        $response['category_id'] = $keyData->cat_id;
        $response['sub_category'] = array_values(CategoryTemplatesApiController::getSubCategories($keyData->cat_id));
        $response['new_related_tags'] = array_values(CategoryTemplatesApiController::getSubCategoriesTags($keyData->cat_id));
        $response['datas'] = $item_rows;
        $response['title'] = $keyData->title;
        $response['string_id'] = $keyData->string_id;
        $response['h2_tag'] = $keyData->h2_tag;
        $response['meta_title'] = $keyData->meta_title;
        $response['meta_desc'] = $keyData->meta_desc;
        $response['short_desc'] = $keyData->short_desc;
        $response['long_desc'] = $keyData->long_desc;
        $response['top_keywords'] = isset($keyData->top_keywords) ? HelperController::getTopKeywords(json_decode($keyData->top_keywords)) : [];
        $response['page_slug_history'] = PageSlugHistoryController::get(0);
        $response['canonical_link'] = $keyData->canonical_link;

        $data = PReviewController::getPReviews($this->uid, 3, $keyData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
    }

    function getSpecialTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $relatedTags = $request->get('id');
        $page = $request->has('page') ? $request->get('page') : 1;
        $onlyVideo = $request->has('v') && $request->get('v');
        $filter = isset($request->filter) ? $request->filter : [];

        if (!$relatedTags) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Parameters missing"));
        }

        $description = str_replace(', ', ',', $relatedTags);
        $desc_array = explode(',', $description);

        $limit = 20;
        $templatesQuery = Design::query();
        if ($onlyVideo) {
            $templatesQuery->where('animation', 1);
        }

        $templatesQuery->whereStatus(1)->where(function ($q) use ($desc_array) {
            foreach ($desc_array as $kw) {
                $q->orWhere('related_tags', 'LIKE', '%"' . $kw . '"%');
            }
        });

        $sql = "CASE ";

        foreach ($desc_array as $index => $keyword) {
            $sql .= "WHEN related_tags LIKE '%\"" . $keyword . "\"%' THEN " . ($index + 1) . " ";
        }

        $sql .= "ELSE 0 END";

        $templatesQuery->orderByRaw($sql);

        $templatesQuery->whereHas('parent', function ($query) {
            $query->where('status', 1);
        });

        if (!empty($filter)) {
            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        $templates = $templatesQuery->orderBy('created_at', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $item_rows = [];

        foreach ($templates->items() as $item) {
            $catRow = Category::find($item->category_id);
            if ($catRow != null) {
                $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array));
            }
        }
        $msg = 'Loading Success!';

        $response['pagination'] = PaginationController::getPagination($templates);
        $response['isLastPage'] = $templates->lastPage() == $templates->currentPage();
        $response['datas'] = $item_rows;
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
    }

    function getPosterPage(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id_name = $request->get('id');

        if (!$id_name) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Parameters missing"));
        }

        $hasShowAll = false;
        $user_data = UserData::where("uid", $this->uid)->first();

        $status_condition = "=";
        $status = "1";
        if ($user_data && ($user_data->can_update == 1 || $user_data->can_update == '1' || $user_data->web_update == 1 || $user_data->web_update == '1')) {
            $status_condition = "!=";
            $status = "-1";
        }

        $string_id = explode('-', $id_name)[0];
        $itemData = Design::where("string_id", $string_id)->where('status', $status_condition, $status)->first();

        if (!$itemData) {
            if (is_numeric($id_name)) {
                $itemData = Design::where("id", $id_name)->where('status', $status_condition, $status)->first();
            }
            if (!$itemData) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found"));
            }
        }

        DB::table('template_view_history')->insert([
            'user_id' => $this->uid,
            'product_id' => $itemData->string_id,
            'ip_address' => $request->ip() == '89.116.134.215' ? null : $request->ip()
        ]);

        $last_24_hour_views = DB::table('template_view_history')->where('product_id', $itemData->string_id)->where('created_at', '>=', now()->subDay())->count();

        $catRow = Category::find($itemData->category_id);

        $item_rows = HelperController::getItemData($this->uid, $catRow, $itemData, json_decode($itemData->thumb_array), false);

        $item_rows['url'] = HelperController::$mediaUrl;
        $item_rows['category_id_name'] = $catRow->id_name;
        $item_rows['ratio'] = $itemData->ratio;
        $item_rows['h2_tag'] = $itemData->h2_tag;
        $item_rows['description'] = $itemData->description;
        $item_rows['meta_description'] = $itemData->meta_description;
        $item_rows['status'] = $itemData->status;
        $item_rows['last_24_hour_views'] = $last_24_hour_views;

        try {
            $SearchApi = new SearchApiController($request);
            $searchData = $SearchApi->searchTemplates($item_rows['related_tags'][0], 1, null, 20, $itemData->id_name);
        } catch (QueryException $e) {
            $searchData['datas'] = [];
        } catch (Exception $e) {
            $searchData['datas'] = [];
        }


        $response['data'] = $item_rows;
        $response['suggested'] = $searchData['datas'];

        $data = PReviewController::getPReviews($this->uid, 0, $itemData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));

    }

    function getPosterDetail(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $template_id = $request->get('id');

        if ($template_id == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        $fieldName = is_numeric($template_id) ? "id" : "id_name";
        $itemData = Design::where($fieldName, $template_id)->first();

        if (!$itemData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found"));
        }

        $res = Design::find($itemData->id);
        $res->web_views = $res->web_views + 1;
        $res->trending_views = $res->trending_views + 1;
        $res->save();


        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded",
            [
                'data' => FabricJsController::getPosterDetail($request, true)
            ]
        ));
    }
}

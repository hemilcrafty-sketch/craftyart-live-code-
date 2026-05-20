<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Size;
use App\Models\WebTemplateViewHistory;
use Exception;
use Illuminate\Http\Request;
use App\Models\Design;
use App\Models\UserData;
use Illuminate\Database\QueryException;

class TemplateApiController extends ApiController
{

    function getAllFab(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $refWidth = $request->get('w', 1);
        $refHeight = $request->get('h', 1);
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = HelperController::getPaginationLimit();

        $tempRatio = $refWidth / $refHeight;
        $tempRatio = round($tempRatio, 2);

        $itemData = Design::where("status", 1)
            ->where("ratio", $tempRatio)
            ->whereHas('parent', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);

        $item_rows = [];

        $rates = RateController::getRates();

        $allCategoryIds = $itemData->getCollection()->pluck('new_category_id')->unique();
        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        foreach ($itemData->items() as $item) {
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

        $msg = 'Loading Success!';

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg,
            [
                'isLastPage' => $itemData->currentPage() === $itemData->lastPage(),
                'datas' => $item_rows
            ]
        ));

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
        }

        if (!$itemData) return $this->failed(msg: "Parameters missing");

        if ($this->isTester()) {
            WebTemplateViewHistory::where('created_at', '<=', now()->subDay())->delete();
        }

        $ipData = HelperController::getIpAndCountry($request);
        $userIp = $ipData['ip'];

        WebTemplateViewHistory::create([
            'user_id' => $this->uid,
            'product_id' => $itemData->string_id,
            'ip_address' => $userIp == '89.116.134.215' ? null : $userIp,
            'country' => $ipData['cn'],
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
            'gclid' => $request->cookie('_gclid'),
            'gcl_au' => $request->cookie('_gcl_au'),
            'ga' => $request->cookie('_ga'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'type' => 'template',
        ]);

        $last_24_hour_views = WebTemplateViewHistory::where('product_id', $itemData->string_id)->where('type', 'template')->where('created_at', '>=', now()->subDay())->count();
        $rates = RateController::getRates();
        $catRow = NewCategory::findId(select: null, isStatus: 1, id: $itemData->new_category_id);
        $item_rows = HelperController::getItemData(
            uid: $this->uid,
            catRow: $catRow,
            item: $itemData,
            thumbArray: json_decode($itemData->thumb_array),
            swap: false,
            rates: $rates
        );

        $newTags = [];
//        if ($catRow) {
//            $parentCat = NewCategory::find($catRow->parent_category_id);
//            if ($parentCat && isset($itemData->new_related_tags)) {
//                $value = $itemData->new_related_tags;
//                $dataRes = NewSearchTag::whereIn('id', $value)->where('status', 1)->get();
//
//                if ($dataRes) {
//                    $newTags = collect($dataRes)->map(function ($newSearchTag) use ($parentCat, $catRow) {
//                        return [
//                            'id' => $newSearchTag['id'],
//                            'id_name' => $newSearchTag['id_name'],
//                            'link' => '/templates/' . $parentCat->id_name . '/' . $catRow->id_name . '?query=' . $newSearchTag['id_name'],
//                            'name' => $newSearchTag['name']
//                        ];
//                    })->toArray();
//                }
//            }
//        }

        $subCatArray = CategoryTemplatesApiController::getSubCategories($catRow);

        $pageUrl = $item_rows['template_link'];

        if ($catRow && $catRow->parent) {
            $item_rows['category_name'] = $catRow->category_name . ' ' .$catRow->parent['category_name'];
        }

        $item_rows['category_size'] = $itemData->width . ' X ' .$itemData->height . ' px';
        $item_rows['pre_breadcrumb'] = CategoryTemplatesApiController::getCategoryBreadcrumbs($catRow, $itemData->post_name, $pageUrl);
        $item_rows['page_link'] = $pageUrl;
        $item_rows['url'] = HelperController::$mediaUrl;
        $item_rows['category_id_name'] = $catRow?->id_name;
        $item_rows['ratio'] = $itemData->ratio;
        $item_rows['h2_tag'] = $itemData->h2_tag;
        $item_rows['meta_title'] = $itemData->meta_title ?? $itemData->post_name;
        $item_rows['description'] = $itemData->description;
        $item_rows['meta_description'] = $itemData->meta_description;
        $item_rows['status'] = $itemData->status;
        $item_rows['last_24_hour_views'] = $last_24_hour_views;
        $item_rows['parent_cats'] = $subCatArray['parentTags'];
        $item_rows['sub_category'] = $subCatArray['subCats'];
        $item_rows['new_related_tags'] = $subCatArray['subCatTags'];
        $item_rows['new_tags'] = $newTags;
        $item_rows['currency'] = $ipData['cur'];
        $size = Size::find($itemData->template_size);
        if ($size) {
            $item_rows['paper_size'] = $size->paper_size;
        }

        try {
            $SearchApi = new SearchApiController($request);
            $searchData = $SearchApi->exactKeywordTemplates($rates, $item_rows['related_tags'][0], 50, $itemData->string_id);
        } catch (QueryException|Exception $e) {
            $searchData['datas'] = [];
        }

        $response['data'] = $item_rows;
        $response['suggested'] = $searchData['datas'];
        $response['cta'] = isset($itemData->cta) ? HelperController::getCTA($itemData->cta) : null;
        $response['canonical_link'] = PaginationController::buildCanonicalLink($itemData->canonical_link, $pageUrl, 1);
        $response['contact_no'] = is_null($user_data) ? null : $user_data->contact_no;
        $data = PReviewController::getPReviews($this->uid, 0, $itemData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $pageUrl);

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

        $data = FabricJsController::getPosterDetail($request, true);
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded",
            [
                'data' => JSONUtils::applyPageStringId($data, $itemData->string_id)
            ]
        ));
    }
}

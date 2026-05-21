<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CacheHelper;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\DomainChecker;
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
use Cache;
use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use App\Models\UserData;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KPageApiController extends ApiController
{

    function getKeyTemplates(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized", ["page_slug_history" => []]));
        }

        $oldSlug = $request->id;
        if (!$oldSlug)
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"), true, true);

        $hasPageInRequest = $request->has('page');
        $data = HelperController::extractAndRemoveTrailingNumber($oldSlug);
        $page = $hasPageInRequest ? $request->input('page', 1) : $data['number'] ?? 1;
        $keyName = $data['string'] ?? 1;

        if ($this->uid && empty(DomainChecker::getDomainName($request)))
            $page = 1;

        $filter = isset($request->filter) ? $request->filter : [];

        $cacheTag = "kp_$keyName";
        $contentCacheKey = 'kp_content_' . $keyName;
        $faqCacheKey = 'kp_faq_' . $keyName;

        $cacheKey = 'kp_' . $keyName . md5(json_encode([
            'filter' => json_encode($filter),
            'page' => $page,
        ]));

        $page_link = HelperController::$webPageUrl . 'k/' . $keyName;

        $callback = function ($doCache = true) use ($cacheTag, $contentCacheKey, $faqCacheKey, $request, $keyName, $filter, $page, $page_link) {
            $keyData = SpecialKeyword::where('name', $keyName)->where('status', '1')->first();

            if (!$keyData) {
                return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, "Data not found", ["page_slug_history" => PageSlugHistoryController::get(0)]));
            }

            $keyDataId = "\"" . $keyData->id . "\"";

            $limit = HelperController::getPaginationLimit();

            $templatesQuery = Design::where("status", 1)
                ->whereHas('parent', function ($query) {
                    $query->where('status', 1);
                });

            $cat = NewCategory::findId(select: null, isStatus: 1, id: $keyData->cat_id);

            if (empty($filter)) {
                $templatesQuery = $templatesQuery->where('special_keywords', 'like', '%' . $keyDataId . '%');
            } else {
                if (!$cat || $cat->parent_category_id != 0) {
                    $templatesQuery = $templatesQuery->where('new_category_id', $keyData->cat_id);
                } else {
                    $ids = NewCategory::where("parent_category_id", $keyData->cat_id)->pluck('id')->unique();
                    $templatesQuery = $templatesQuery->whereIn('new_category_id', $ids);
                }

                $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
            }

            $itemData = $templatesQuery->orderByRaw('pinned DESC, id DESC')->paginate($limit, ['*'], 'page', $page);
            //            $itemData = $templatesQuery->orderByRaw('pinned DESC, web_views DESC, id DESC')->paginate($limit, ['*'], 'page', $page);

            $rates = RateController::getRates();

            $allCategoryIds = $itemData->getCollection()->pluck('new_category_id')->unique();
            $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

            $item_rows = [];

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

            $subCatArray = CacheHelper::getSubCategories($keyData->cat_id);

            $response['pagination'] = PaginationController::getPagination($itemData, $filter, $page_link);
            $response['page_link'] = $page_link;
            $response['filter_link'] = $page_link;
            $response['total_page'] = $itemData->lastPage();
            $response['isLastPage'] = $itemData->currentPage() >= $itemData->lastPage();
            $response['category_id'] = $keyData->cat_id;
            $response['pre_breadcrumb'] = CategoryTemplatesApiController::getCategoryBreadcrumbs($cat, ucwords($keyName), $page_link);
            $response['templateCount'] = HelperController::getTemplateCount($itemData->total(), $keyData->primary_keyword);
            $response['parent_cats'] = $subCatArray['parentTags'];
            $response['sub_category'] = $subCatArray['subCats'];
            $response['new_related_tags'] = $subCatArray['subCatTags'];
            $response['datas'] = $item_rows;
            $response['title'] = $keyData->title;
            $response['string_id'] = $keyData->string_id;
            $response['meta_title'] = $keyData->meta_title;
            $response['meta_desc'] = $keyData->meta_desc;
            $response['short_desc'] = $keyData->short_desc;
            // if ($page == 1) {
            $response['h2_tag'] = $keyData->h2_tag;
            $response['long_desc'] = $keyData->long_desc;
            $response['contents'] = isset($keyData->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($keyData->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: $doCache) : [];
            // }
            $response['top_keywords'] = isset($keyData->top_keywords) ? HelperController::getTopKeywords(json_decode($keyData->top_keywords)) : [];
            $response['page_slug_history'] = PageSlugHistoryController::get(0);
            $response['canonical_link'] = PaginationController::buildCanonicalLink($keyData->canonical_link, $page_link, $page);

            $faqsResponse = ContentManager::faqsResponse(faqs: $keyData->faqs, premiumKeyword: $keyData->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: $doCache);
            $response['faqs'] = $faqsResponse['faqs'];
            $response['faqs_title'] = $faqsResponse['faqs_title'];

            $data = PReviewController::getPReviews($this->uid, 3, $keyData->string_id, 1);
            if ($data['success']) {
                $response['reviews'] = $data['data'];
            }

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, $msg, $response));
        };

        if (HelperController::$cacheEnabled) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback(false);
        }

        if (!$response['success'])
            $response = $callback(false);

        if (isset($response['success']) && $response['success']) {
            $user_data = UserData::where("uid", $this->uid)->first();
            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $page_link);
        }

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

}

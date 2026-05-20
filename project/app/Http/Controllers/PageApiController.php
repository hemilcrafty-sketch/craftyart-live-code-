<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CacheHelper;
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
use App\Models\SpecialPage;
use App\Models\UserData;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PageApiController extends ApiController
{

    public function getPage(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $data = HelperController::extractAndRemoveTrailingNumber($request->slug);
        $pageNo = $data['number'] ?? 1;
        $slug = $data['string'] ?? 1;
        $filter = $request->get('filter', []);

        if ($this->uid) $pageNo = 1;

        $cacheTag = "sp_$slug";
        $contentCacheKey = 'sp_content_' . $slug;
        $faqCacheKey = 'sp_faq_' . $slug;
        $cacheKey = 'sp_' . $slug . md5(json_encode([
                'filter' => json_encode($filter),
                'page' => $pageNo,
            ]));

        $page_link = HelperController::$webPageUrl . $slug;

        $callback = function ($doCache = true) use ($cacheTag, $contentCacheKey, $faqCacheKey, $request, $slug, $filter, $pageNo, $page_link) {

            if ($request->has('showForce') && $request->input('showForce')) {
                $page = SpecialPage::wherePageSlug($slug)->first();
            } else {
                $page = SpecialPage::wherePageSlug($slug)->whereStatus(1)->first();
            }

            if (!$page) {
                return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, "Data not found", ["page_slug_history" => PageSlugHistoryController::get(1), "gdg" => $slug]));
            }

            $page->image = isset($page->image) ? env('CLOUDFLARE_R2_URL') . $page->image : '';

            $cat = NewCategory::findId(select: null, isStatus: 1, id: $page->cat_id);
            $page->pre_breadcrumb = CategoryTemplatesApiController::getCategoryBreadcrumbs($cat, $page->breadcrumb, $page_link);

//            if ($request->has("version") && $request->version == "2") {
//                $page->pre_breadcrumb = CategoryTemplatesApiController::getCategoryBreadcrumbs(NewCategory::find($page->cat_id), $page->breadcrumb, $page_link);
//            } else {
//                if (isset($page->pre_breadcrumb) && $page->pre_breadcrumb != null && $page->pre_breadcrumb != "") {
//                    $page->pre_breadcrumb = json_decode($page->pre_breadcrumb);
//                } else {
//                    $page->pre_breadcrumb = [
//                        'value' => "Templates",
//                        "link" => "https://www.craftyartapp.com/templates",
//                        "openinnewtab" => 0,
//                        "nofollow" => 0
//                    ];
//                }
//            }

//            if ($this->isTester()) {
//                return json_decode(StorageUtils::get($page->contents));
//            }

            $rates = RateController::getRates();
            $contents = json_decode($page->contents);
            if ($page->contents && !$contents) {
                $contents = ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($page->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: $doCache);
            }

            $page->contents = $contents;

            if (is_object($page->contents)) {
                $page->contents = (array)$page->contents;
                $page->contents = array_values($page->contents);
            }

            $faqsResponse = ContentManager::faqsResponse(faqs: $page->faqs, premiumKeyword: $page->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: $doCache);
            $page->faqs = $faqsResponse['faqs'];
            $page->faqs_title = $faqsResponse['faqs_title'];

            $page->hero_background_image = isset($page->hero_background_image) ? env('CLOUDFLARE_R2_URL') . $page->hero_background_image : '';
            $page->body_background_image = isset($page->body_background_image) ? env('CLOUDFLARE_R2_URL') . $page->body_background_image : '';

            if (isset($page->banner) && $page->banner != null && $page->banner != "") {
                $page->banner = env('CLOUDFLARE_R2_URL') . $page->banner;
            } else {
                unset($page['banner']);
            }

            $data = PReviewController::getPReviews($this->uid, 2, $page->string_id, 1);
            if ($data['success']) {
                $page->reviews = $data['data'];
            }

            $page->category_id = $page->cat_id;

            $keyword = null;
            $onlyVideo = false;
            foreach ($page->contents as $content) {
                if (isset($content->type) && $content->type === "api" && isset($content->value)) {
                    $value = $content->value;
                    $keyword = $value->keyword;
                    $onlyVideo = isset($value->only_video) && ($value->only_video == 1 || $value->only_video === "1");
                }
            }

            $templateDatas = $this->fetchSpecialTemplatesData(
                uid: $this->uid,
                rates: $rates,
                relatedTags: ltrim(rtrim($keyword)),
                onlyVideo: $onlyVideo,
                slug: $slug,
                filter: $filter,
                page_link: $page_link,
                page: $pageNo,
            );

            if (empty($filter) && $templateDatas['count'] == 0) {
                return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, "Data not found", ["templateDatas" => $keyword]));
            }

            $page->datas = $templateDatas['datas'];
            $page->page_link = $page_link;
            $page->filter_link = $page_link;
            $page->pagination = $templateDatas['pagination'];
            $page->isLastPage = $templateDatas['isLastPage'];

            $page->canonical_link = PaginationController::buildCanonicalLink($page->canonical_link, $page_link, $pageNo);

            $page->templateCount = HelperController::getTemplateCount($templateDatas['count'], $page->primary_keyword);

            $subCatArray = CacheHelper::getSubCategories($page->cat_id);
            $page->parent_cats = $subCatArray['parentTags'];
            $page->sub_category = $subCatArray['subCats'];
            $page->new_related_tags = $subCatArray['subCatTags'];

            $page->top_keywords = isset($page->top_keywords) ? HelperController::getTopKeywords(json_decode($page->top_keywords)) : [];

            $page->cta = isset($page->cta) ? HelperController::getCTA($page->cta) : null;

            $page->page_slug_history = PageSlugHistoryController::get(1);
            $page->only_video = $onlyVideo;

            unset($page['cat_id']);

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "loaded", $page->toArray()));
        };

        if (HelperController::$cacheEnabled) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback(false);
        }

        if (!$response['success']) $response = $callback(false);

        if (isset($response['success']) && $response['success']) {
            $user_data = UserData::where("uid", $this->uid)->first();
            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $page_link);
        }

        return ResponseHandler::sendEncryptedResponse($request, $response);
    }

    public function getSpecialTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        if (!$request->get('id')) return $this->failed(msg: "Parameters missing");

        $page = $request->get('page', 1);
        $relatedTags = $request->get('id') ?? "";
        $onlyVideo = $request->has('v') && $request->get('v');
        $filter = $request->get('filter', []);

        $data = HelperController::extractAndRemoveTrailingNumber($request->slug);
        $slug = $data['string'] ?? 1;

        $page_link = HelperController::$webPageUrl . $slug;

        $responseData = $this->fetchSpecialTemplatesData(
            uid: $this->uid,
            rates: RateController::getRates(),
            relatedTags: ltrim(rtrim($relatedTags)),
            onlyVideo: $onlyVideo,
            slug: $slug,
            filter: $filter,
            page_link: $page_link,
            page: $page);

        return $this->successed(datas: $responseData);
    }

    public static function fetchSpecialTemplatesData(?string $uid, Collection $rates, string $relatedTags, bool $onlyVideo, string $slug, array $filter, string $page_link, int $page = 1): array
    {
        $limit = HelperController::getPaginationLimit();
        $description = str_replace(', ', ',', $relatedTags);
        $desc_array = explode(',', $description);

        $templatesQuery = Design::query();

        if ($onlyVideo) {
            $templatesQuery->where('animation', 1);
        }

        $templatesQuery->whereHas('parent', function ($query) {
            $query->where('status', 1);
        });

        if (empty($filter)) {
            $templatesQuery->where(function ($q) use ($desc_array) {
                foreach ($desc_array as $kw) {
                    $q->orWhere('related_tags', 'LIKE', '%"' . $kw . '"%');
                }
            });
        } else {
            $pageObj = SpecialPage::wherePageSlug($slug)->first();
            if ($pageObj) {
                $cat = NewCategory::select(['parent_category_id'])->find($pageObj->cat_id);
                if (!$cat || $cat->parent_category_id != 0) {
                    $templatesQuery->where('new_category_id', $pageObj->cat_id);
                } else {
                    $ids = NewCategory::select(['id'])->where("parent_category_id", $pageObj->cat_id)->pluck('id')->unique();
                    $templatesQuery->whereIn('new_category_id', $ids);
                }
            }

            $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);
        }

        $data = [];

//        $templates = $templatesQuery->whereStatus(1)->orderByRaw('pinned DESC, web_views DESC, id DESC')->paginate($limit, ['*'], 'page', $page);
        $templates = $templatesQuery->whereStatus(1)->orderByRaw('pinned DESC, id DESC')->paginate($limit, ['*'], 'page', $page);

        $item_rows = [];

        $allCategoryIds = $templates->getCollection()->pluck('new_category_id')->unique();
        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        foreach ($templates->items() as $item) {
            $catRow = $categories[$item->new_category_id] ?? null;
            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
            if ($catRow != null) $catLink = $catRow->cat_link;
            $item_rows[] = HelperController::getItemData(
                uid: $uid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array),
                catLink: $catLink,
                rates: $rates
            );
        }

        $data['count'] = $templates->total();
        $data['page_link'] = $page_link;
        $data['filter_link'] = $page_link;
        $data['pagination'] = PaginationController::getPagination($templates, $filter, $page_link);
        $data['isLastPage'] = $templates->lastPage() === $templates->currentPage();
        $data['datas'] = $item_rows;

        return $data;
    }
}


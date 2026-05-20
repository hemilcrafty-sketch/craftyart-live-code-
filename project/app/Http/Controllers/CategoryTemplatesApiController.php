<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Religion;
use App\Models\Size;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\Style;
use App\Models\Theme;
use App\Models\UserData;
use App\Models\VirtualCategory;
use Cache;
use Illuminate\Http\Request;
use App\Models\Design;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryTemplatesApiController extends ApiController
{

    public static string $defaultTagLine = 'Beautiful and elegant designs with customizable templates - perfect for any special celebrations!';
    public static array $latestSeo = [
        "short_desc" => "Stay ahead with our collection of latest templates. From cutting-edge designs to contemporary trends, find the perfect fit for your next events.",
        "h1_tag" => "Latest Templates",
        "h2_tag" => "Dive into Creativity: Latest Template Designs",
        "meta_title" => "Latest Template Collection for Every Need | Get Started Now!",
        "meta_desc" => "Enhance your next events with the latest templates. Discover a wide range of Latest Templates for various needs. Get started today!",
        "long_desc" => "In today's fast-paced digital landscape, staying up-to-date with the latest trends and tools is crucial for any creative endeavor. Whether you're a designer, marketer, blogger, or business owner, having access to the most recent templates can significantly boost your projects' impact and efficiency. This is where the world of Latest Templates comes into play.\n\nThe term Latest Templates encompasses a wide array of design resources that span various industries and purposes. These templates can range from website designs, graphic assets, presentation layouts, email designs, social media graphics, and much more. They serve as pre-designed frameworks that can be customized to suit your specific needs, allowing you to save valuable time while maintaining a professional and polished appearance.\n\nThe beauty of these latest templates lies in their adaptability. No matter the nature of your project, whether it's a cutting-edge tech startup pitch, a cozy corner cafe's promotional materials, or a fashion blog's Instagram posts, there are templates available that align with your vision. With a multitude of styles, color schemes, typography choices, and layouts to choose from, you have the freedom to make each template your own.\n\nOne of the key advantages of utilizing the latest templates is the speed they bring to your workflow. Traditional design processes can be time-consuming, often requiring you to start from scratch. With templates, the foundation is already set, and you're simply adding your unique touch. This expedites the design process, allowing you to meet tight deadlines without compromising quality.\n\nMoreover, these templates often come crafted by experienced designers who understand the principles of aesthetics, visual hierarchy, and user experience. This means you're starting with a design that's not only visually appealing but also strategically effective. Even if you're not a design expert yourself, these templates empower you to create materials that resonate with your audience.\n\nLet's delve into some of the most popular categories where the latest templates prove to be game-changers:\n\nWeb Design Templates: In the digital age, your website is often the first point of contact with potential customers. Utilizing the latest web design templates ensures your site is modern, user-friendly, and responsive across devices.\n\nGraphic Design Templates: From business cards to brochures, these templates cover a wide range of print and digital materials. They help you maintain a consistent brand identity across all touch points.\n\nPresentation Templates: Whether for business pitches or educational purposes, presentation templates make your content engaging and impactful. Creative slides and visually appealing graphics keep your audience attentive.\n\nSocial Media Templates: Consistency is key on social platforms. With these templates, you can maintain a cohesive brand presence and share eye-catching content that stops users from scrolling.\n\nEmail Marketing Templates: Crafting effective emails can be challenging. The latest email templates ensure your messages are well-designed and optimized for better open and click-through rates.\n\nE-commerce Templates: If you're an online retailer, e-commerce templates help showcase your products in the best light, leading to higher conversion rates and increased sales.\n\nIn conclusion, the world of Latest Templates opens doors to a universe of creativity and efficiency. Whether you're a design professional or a novice, these templates offer a shortcut to stunning visuals and effective communication. By harnessing the power of the latest templates, you're not just keeping up with trends; you're setting new standards for your projects and leaving a lasting impression on your audience. Explore, customize, and elevate your creations with the ever-evolving realm of the latest templates.",
    ];

    /**
     * @param Collection $rates
     * @param NewCategory[]|LengthAwarePaginator|null $categories
     * @param string|null $uid
     * @param int|null $isImp
     * @param int|null $page
     * @return array{isLastPage:bool, datas: array, catlist: array}
     */

    public static function getAllNewCategories(Collection $rates, array|LengthAwarePaginator|null $categories = null, ?string $uid = null, ?int $isImp = null, ?int $page = 1): array
    {

        if (!$categories) {
//            $categories = NewCategory::getAllCatsWithChilds(isImp: $isImp, page: $page);
            $categories = NewCategory::getAllCatsWithChilds(filters: ['imp' => $isImp], limit: 15, page: $page);
        }

        $isLastPage = $categories->lastPage() === $categories->currentPage();

        $cats = [];
        $datas = [];

        foreach ($categories->items() as $category) {
            $categoryId = $category->id;
            $allIds = array_merge([$categoryId], $category->child_cat_ids ?? []);
            $templates = Design::whereIn('new_category_id', $allIds)->where('status', 1)->latest()->take(12)->get();

            if ($templates->isEmpty()) {
                continue;
            }

            $allCategoryMap = [];

            if ($uid) {
                $allCategoryMap[$categoryId] = $category;
                if (!empty($category->subcategories)) {
                    foreach ($category->subcategories as $sub) {
                        $allCategoryMap[$sub->id] = $sub;
                    }
                }
            }

            $commonData = [
                'category_id' => $categoryId,
                'id_name' => $category->id_name,
                'category_name' => $category->category_name,
                'category_thumb' => HelperController::$mediaUrl . $category->category_thumb,
                'category_mockup' => $category->mockup ? HelperController::$mediaUrl . $category->mockup : null,
            ];

            $cats[] = $commonData;

            if ($isImp && $category->imp !== $isImp) {
                continue;
            }

            $processedTemplates = $templates->map(function ($template) use ($uid, $allCategoryMap, &$usedCategoryMap, $rates) {
                $cateRow = $allCategoryMap[$template->new_category_id] ?? null;
                $catLink = null;
                if ($cateRow) {
                    $catLink = $cateRow->cat_link;
                }

                return HelperController::getItemData(
                    uid: $uid,
                    catRow: $cateRow,
                    item: $template,
                    thumbArray: json_decode($template->thumb_array),
                    catLink: $catLink,
                    rates: $rates
                );
            });

            $datas[] = array_merge($commonData, [
                'template_model' => $processedTemplates,
            ]);
        }

        return ["isLastPage" => $isLastPage, "datas" => $datas, "catlist" => $cats];
    }

    function getDashboardDatas(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = (int)$request->has('page') ? $request->get('page') : 1;
        $data = $this->getAllNewCategories(rates: RateController::getRates(), uid: $this->uid, page: $page);
        $response['datas'] = $data['datas'];
        $response['isLastPage'] = $data['isLastPage'];
        $response['oldDatas'] = [];
        $response['seo'] = self::$latestSeo;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', $response));

    }

    private function getVirtualCategory($request, $slug, $page, $limit, $hasPageInRequest): array|string
    {
        $item_rows = array();
        $catRow = VirtualCategory::where('id_name', $slug)->where('status', 1)->first();
        if ($catRow == null) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Virtual Data not found"));

        $cacheTag = "category_$slug";
        $contentCacheKey = "category_content_$slug";
        $faqCacheKey = "category_faq_$slug";
        $cacheKey = "categories_$slug" . md5(json_encode(['page' => $page]));

        $query = Design::query();
        $query->whereStatus(1);
        $limit = QueryManager::applyConditionToQuery($query, explode(' && ', $catRow->virtual_query), $limit);

        $itemData = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $itemData->currentPage() >= $itemData->lastPage();

        $rates = RateController::getRates();

        foreach ($itemData->items() as $item) {
            $item_rows[] = HelperController::getItemData($this->uid, $catRow, $item, json_decode($item->thumb_array), catLink: HelperController::$webPageUrl . "templates/p/$item->id_name", rates: $rates);
        }

        $catLink = HelperController::$webPageUrl . "templates/" . $slug;

        $responsePayload = [
            "new_api" => false,
            "page_link" => $catLink,
            "filter_link" => $catLink,
            "templateCount" => HelperController::getTemplateCount($itemData->total(), $catRow->primary_keyword),
            "isLastPage" => $isLastPage,
            "category_id" => $catRow->id,
            "datas" => $item_rows,
            "pagination" => PaginationController::getPagination($itemData, [], $catLink),
        ];

//        if (!$hasPageInRequest) {

        $seo['meta_title'] = $catRow->meta_title;
        $seo['meta_desc'] = $catRow->meta_desc;
        $seo['h1_tag'] = $catRow->h1_tag;
        $seo['h2_tag'] = $catRow->h2_tag;
        $seo['short_desc'] = $catRow->short_desc;
        $seo['long_desc'] = $catRow->long_desc;
        $seo['tag_line'] = $catRow->tag_line ?? CategoryTemplatesApiController::$defaultTagLine;

        $responsePayload['seo'] = $seo;
        $responsePayload['top_keywords'] = (isset($catRow->top_keywords)) ? HelperController::getTopKeywords(json_decode($catRow->top_keywords)) : [];
        $responsePayload['contents'] = (isset($catRow->contents)) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($catRow->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: true) : [];

        $responsePayload['pre_breadcrumb'] = self::getCategoryBreadcrumbs(null, $catRow->category_name);
        $faqsResponse = ContentManager::faqsResponse(faqs: $catRow->faqs, premiumKeyword: $catRow->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: true);
        $responsePayload['faqs'] = $faqsResponse['faqs'];
        $responsePayload['faqs_title'] = $faqsResponse['faqs_title'];
        $responsePayload['canonical_link'] = PaginationController::buildCanonicalLink($catRow->canonical_link, $catLink, $page);

        $responsePayload['string_id'] = $catRow->string_id;
        $responsePayload['banner'] = $catRow->banner ? HelperController::$mediaUrl . $catRow->banner : null;

        $data = PReviewController::getPReviews($this->uid, 4, $catRow->string_id, 1);
        if ($data['success']) $responsePayload['reviews'] = $data['data'];
//        }

        $responsePayload['query'] = [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ];

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loading Success!", $responsePayload));
    }

    public function getCategories(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $oldSlug = $request->slug;
        if (!$oldSlug) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));

        $hasPageInRequest = $request->has('page');
        $data = HelperController::extractAndRemoveTrailingNumber($oldSlug);
        $page = $hasPageInRequest ? $request->input('page', 1) : $data['number'] ?? 1;
        $slug = $data['string'] ?? 1;

        if ($this->uid && !$hasPageInRequest) $page = 1;

        $limit = HelperController::getPaginationLimit();

        $filter = isset($request->filter) ? $request->filter : [];

        $cacheTag = "category_$slug";
        $contentCacheKey = 'category_content_' . $slug;
        $faqCacheKey = 'category_faq_' . $slug;

        $cacheKey = 'categories_' . $slug . md5(json_encode(['filter' => json_encode($filter), 'page' => $page]));

        $checkCat = NewCategory::findCatLink(isStatus: 1, id: $slug);

        if (!$checkCat) return $this->getVirtualCategory($request, $slug, $page, $limit, $hasPageInRequest);

        $callback = function ($doCache = true) use ($cacheTag, $contentCacheKey, $faqCacheKey, $filter, $checkCat, $limit, $page, $hasPageInRequest) {

            $usedCategoryMap = array_merge([$checkCat->id], $checkCat->child_cat_ids ?? []);

            $templatesQuery = Design::whereIn('new_category_id', $usedCategoryMap)->where('status', 1);

            if (!empty($filter)) $templatesQuery = CategoryTemplatesApiController::getFilterQuery($templatesQuery, $filter);

            $templates = $templatesQuery->orderByRaw('id DESC')->paginate($limit, ['*'], 'page', $page);

            $allCategoryMap = [];

            if ($this->uid) {
                $allCategoryMap[$checkCat->id] = $checkCat;
                foreach ($checkCat->subcategories ?? [] as $sub) {
                    $allCategoryMap[$sub->id] = $sub;
                }
            }

            $rates = RateController::getRates();

            $templateDatas = [];
            foreach ($templates->items() as $template) {
                $cateRow = $allCategoryMap[$template->new_category_id] ?? null;
                $catLink = HelperController::$webPageUrl . "templates/p/" . $template->id_name;
                if ($cateRow != null && $cateRow->cat_link !== $checkCat->cat_link) $catLink = $cateRow->cat_link;
                $templateDatas[] = HelperController::getItemData(uid: $this->uid, catRow: $cateRow, item: $template, thumbArray: json_decode($template->thumb_array, true, 512, JSON_UNESCAPED_SLASHES), catLink: $catLink, rates: $rates);
            }

            $response = [
                "new_api" => true,
                "page_link" => $checkCat->cat_link,
                "filter_link" => $checkCat->cat_link,
                "templateCount" => HelperController::getTemplateCount($templates->total(), $checkCat->primary_keyword),
                "isLastPage" => $templates->currentPage() >= $templates->lastPage(),
                "category_id" => $checkCat->id,
                "banner" => $checkCat->banner ? HelperController::$mediaUrl . $checkCat->banner : null,
                "string_id" => $checkCat->string_id,
                "datas" => $templateDatas,
                "pagination" => PaginationController::getPagination($templates, $filter, $checkCat->cat_link)
            ];

//            if (!$hasPageInRequest) {
            $subCatArray = CategoryTemplatesApiController::getSubCategories($checkCat);

            $response['parent_cats'] = $subCatArray['parentTags'];
            $response['sub_category'] = $subCatArray['subCats'];
            $response['new_related_tags'] = $subCatArray['subCatTags'];

            $seoDatas = collect($checkCat)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
            $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CategoryTemplatesApiController::$defaultTagLine;

            $response['seo'] = $seoDatas;
            $response['top_keywords'] = (isset($checkCat->top_keywords)) ? HelperController::getTopKeywords(json_decode($checkCat->top_keywords)) : [];
            $response['contents'] = isset($checkCat->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($checkCat->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey, doCache: $doCache) : [];

            $faqsResponse = ContentManager::faqsResponse(faqs: $checkCat->faqs, premiumKeyword: $checkCat->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey, doCache: $doCache);
            $response['faqs'] = $faqsResponse['faqs'];
            $response['faqs_title'] = $faqsResponse['faqs_title'];
            $response['canonical_link'] = PaginationController::buildCanonicalLink($checkCat->canonical_link, $checkCat->cat_link, $page);
            $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($checkCat);

            $data = PReviewController::getPReviews($this->uid, 1, $checkCat->string_id, 1);
            if ($data['success']) $response['reviews'] = $data['data'];
//            }

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loading Success!", $response));
        };

        if (HelperController::$cacheEnabled) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback(false);
        }

        if (!$response['success'] || count($response['datas']) == 0) $response = $callback(false);

        if (isset($response['success']) && $response['success']) {
            $user_data = UserData::where("uid", $this->uid)->first();
            $url = $checkCat->cat_link;
            FbPixel::trackEvent(FacebookEvent::VIEW_CONTENT, $request, $user_data?->name, $user_data?->email, null, $url);
        }

        return ResponseHandler::sendEncryptedResponse($request, $response);

    }

    public static function getFilterQuery($templatesQuery, $filter)
    {
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'language':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $languages = $matches[1];
                        $templatesQuery->where(function ($query) use ($languages) {
                            foreach ($languages as $language) {
                                $language = Language::where('id_name', $language)->first();
                                $langId = $language->id ?? "";
                                $query->orWhereJsonContains('lang_id', json_encode($langId));
                            }
                        });
                        break;
                    case 'style':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $styles = $matches[1];
                        $templatesQuery->where(function ($query) use ($styles) {
                            foreach ($styles as $style) {
                                $style = Style::where('id_name', $style)->first();
                                $styleId = $style->id ?? "";
                                $query->orWhereJsonContains('style_id', json_encode($styleId));
                            }
                        });
                        break;
                    case 'size':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $values = $matches[1];
                        $templatesQuery->where(function ($query) use ($values) {
                            foreach ($values as $value) {
                                $tempSize = Size::where('id_name', $value)->first();
                                $templateSize = $tempSize->id ?? "";
                                if ($templateSize) {
                                    $query->orWhere('template_size', $templateSize);
                                }
                            }
                        });
                        break;
                    case 'tags':
                        $value = HelperController::checkStringFormat($value, true);
                        $newSearchTag = NewSearchTag::where('id_name', $value)->first();
                        $newSearchTagName = $newSearchTag->id ?? "";
                        $templatesQuery->whereJsonContains('new_related_tags', $newSearchTagName);
                        break;
                    case 'is_premium':
                        if ($value === "true") {
                            $templatesQuery->where(function ($query) {
                                $query->where('is_premium', 1)->orWhere('is_freemium', 1);
                            });
                        } else {
                            $templatesQuery->where('is_premium', 0)->where('is_freemium', 0);
                        }
                        break;

                    case 'interest':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $interests = $matches[1];
                        $templatesQuery->where(function ($query) use ($interests) {
                            foreach ($interests as $interest) {
                                $interest = Interest::where('id_name', $interest)->first();
                                $interestId = $interest->id ?? "";
                                if ($interestId) {
                                    $query->orWhereJsonContains('interest_id', json_encode($interestId));
                                }
                            }
                        });
                        break;

                    case 'color':
                        $lowerValue = strtolower($value);
                        $templatesQuery->whereJsonContains('color_id', $lowerValue);
                        break;

                    case 'religion':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $religions = $matches[1];
                        $templatesQuery->where(function ($query) use ($religions) {
                            foreach ($religions as $religion) {
                                $religion = Religion::where('id_name', $religion)->first();
                                $religionId = $religion->id ?? "";
                                if ($religionId) {
                                    $query->orWhereJsonContains('religion_id', json_encode($religionId));
                                }
                            }
                        });
                        break;
                    case 'orientation':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $orientations = $matches[1];
                        $templatesQuery->where(function ($query) use ($orientations) {
                            foreach ($orientations as $orientation) {
                                if ($orientation) {
                                    $query->orWhere('orientation', $orientation);
                                }
                            }
                        });
                        break;
                    case 'animation':
                        $value = ($value === "true") ? 1 : 0;
                        $templatesQuery->where('animation', $value);
                        break;
                    case 'theme':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $themes = $matches[1];
                        $templatesQuery->where(function ($query) use ($themes) {
                            foreach ($themes as $theme) {
                                $theme = Theme::where('id_name', $theme)->first();
                                $themName = $theme->id ?? "";
                                if ($themName) {
                                    $query->orWhereJsonContains('theme_id', json_encode($themName));
                                }
                            }
                        });
                        break;
                }
            }
        }
        return $templatesQuery;
    }

    public static function getSubCategories(NewCategory|int|null $category): array
    {
        if (is_int($category)) $category = NewCategory::findId(select: null, isStatus: 1, id: $category);

        $parents = NewCategory::query()->select(['id', 'id_name', 'category_name', 'category_thumb', 'cat_link'])->whereParentCategoryId(0)->where('total_templates', '>', 0)->whereStatus(1)->get();
        $parentTags = [];
        foreach ($parents as $parent) {
            $parentTags[] = [
                'id' => $parent->id,
                'category_name' => $parent->category_name,
                'category_thumb' => HelperController::$mediaUrl . $parent->category_thumb,
                'url' => $parent->cat_link,
                'link' => $parent->cat_link,
                'id_name' => $parent->id_name,
                'status' => 1,
            ];
        }

        if ($category) {

            $sPages = SpecialPage::Query()->select(['id', 'page_slug', 'title', 'primary_keyword', 'breadcrumb'])->whereCatId($category->id)->whereNoIndex(0)->whereCanonicalLink(null)->whereStatus(1)->get();
            $kPages = SpecialKeyword::Query()->select(['id', 'name', 'title', 'primary_keyword'])->whereCatId($category->id)->whereNoIndex(0)->whereCanonicalLink(null)->whereStatus(1)->get();

            $pages = [];

            foreach ($sPages as $page) {
                $pages[] = [
                    'id' => $page->id,
                    'url' => HelperController::$webPageUrl . $page->page_slug,
                    'link' => HelperController::$webPageUrl . $page->page_slug,
                    'id_name' => $page->page_slug,
                    'name' => $page->primary_keyword ?? $page->breadcrumb ?? $page->title,
                ];
            }

            foreach ($kPages as $page) {
                $pages[] = [
                    'id' => $page->id,
                    'url' => HelperController::$webPageUrl . "k/" . $page->name,
                    'link' => HelperController::$webPageUrl . "k/" . $page->name,
                    'id_name' => $page->name,
                    'name' => $page->primary_keyword ?? $page->title,
                ];
            }

            if ($category->parent) {
                $parentCat = NewCategory::findId(select: null, isStatus: 1, id: $category->parent['id']);

                if (count($pages) === 0) {
//                    $newSearchTags = NewSearchTag::select(['id', 'id_name', 'name'])->where('new_category_id', 'LIKE', "%\"" . $category->id . "\"%")->where('total_templates', '>', 0)->where('status', 1)->get();
//                    $pages = $newSearchTags->reduce(function ($carry, $tag) use ($category) {
//                        $carry[] = [
//                            'id' => $tag->id,
//                            'url' => $category->cat_link . '/?query=' . $tag->id_name,
//                            'link' => $category->cat_link . '/?query=' . $tag->id_name,
//                            'id_name' => $tag->id_name,
//                            'name' => $tag->name,
//                        ];
//                        return $carry;
//                    }, collect());
                }

                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($parentCat), "subCatTags" => $pages];
            } else {
                return ["parentTags" => array_values($parentTags), "subCats" => self::getChilds($category), "subCatTags" => $pages];
            }
        }

        return ["parentTags" => array_values($parentTags), "subCats" => [], "subCatTags" => []];
    }

    private static function getChilds(NewCategory|null $category): array
    {
        $childs = [];
        if ($category && !$category->parent) {
            foreach ($category->subcategories as $subcategory) {
                $childs[] = [
                    'id' => $subcategory->id,
                    'category_name' => $subcategory->category_name,
                    'category_thumb' => HelperController::$mediaUrl . $subcategory->category_thumb,
                    'url' => $subcategory->cat_link,
                    'link' => $subcategory->cat_link,
                    'id_name' => $subcategory->id_name,
                    'status' => $subcategory->status,
                ];
            }
        }

        return array_values($childs);
    }

    public static function getCategoryBreadcrumbs(NewCategory|CaricatureCategory $cat = null, $last = null, $link = null): array
    {

        $pre_breadcrumb[] = [
            'value' => "Crafty Art",
            "link" => "https://www.craftyartapp.com",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        $pre_breadcrumb[] = [
            'value' => "Templates",
            "link" => "https://www.craftyartapp.com/templates",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        if ($cat) {
            if ($cat->parent) {
                $pre_breadcrumb[] = [
                    'value' => $cat->parent['category_name'],
                    "link" => $cat->parent['cat_link'],
                    "openinnewtab" => 0,
                    "nofollow" => 0
                ];
            }
            $pre_breadcrumb[] = [
                'value' => $cat->category_name,
                "link" => $cat->cat_link,
                "openinnewtab" => 0,
                "nofollow" => 0
            ];

//            if ($cat->parent_category_id != 0) {
//                $parentCat = NewCategory::find($cat->parent_category_id);
//                if ($parentCat) {
//                    $pre_breadcrumb[] = [
//                        'value' => $parentCat->category_name,
//                        "link" => $parentCat->cat_link,
//                        "openinnewtab" => 0,
//                        "nofollow" => 0
//                    ];
//                    $pre_breadcrumb[] = [
//                        'value' => $cat->category_name,
//                        "link" => $cat->cat_link,
//                        "openinnewtab" => 0,
//                        "nofollow" => 0
//                    ];
//                }
//            } else {
//                $pre_breadcrumb[] = [
//                    'value' => $cat->category_name,
//                    "link" => $cat->cat_link,
//                    "openinnewtab" => 0,
//                    "nofollow" => 0
//                ];
//            }
        }

        if (is_null($last) && !empty($pre_breadcrumb)) {
//            $lastIndex = count($pre_breadcrumb) - 1;
//            unset($pre_breadcrumb[$lastIndex]['link']);
//            unset($pre_breadcrumb[$lastIndex]['openinnewtab']);
//            unset($pre_breadcrumb[$lastIndex]['nofollow']);
        } else {
            if ($last) $pre_breadcrumb[] = ['value' => $last, "link" => $link];
        }

        return $pre_breadcrumb;
    }

}

<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\CategoryTemplatesApiController;
use App\Http\Controllers\PReviewController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\UserData;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoInterest;
use App\Models\Video\VideoLanguage;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoReligion;
use App\Models\Video\VideoSize;
use App\Models\Video\VideoSlugHistory;
use App\Models\Video\VideoStyle;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoTheme;
use App\Models\Video\VideoVirtualCategory;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class LottieApiController extends ApiController
{

    public static array $templateSeo = [
        "h1_tag" => "Memorable Invitation Video Maker for Your Special Events",
        "h2_tag" => "Explore Popular Invitation Video Categories",
        "meta_title" => "Online Invitation Video Maker | Customize & Download instant",
        "meta_desc" => "Use our Video Invitation Maker to create invitation videos online. Customize templates, add photos, text, and design engaging invites quickly and easily.",
        "short_desc" => "Use our invitation video maker to create stunning digital invites in 2 minutes. Explore templates for weddings, birthdays & special occasions—fast & easy.",
        "long_desc" => "Create stunning digital invites in 2 minutes with our invitation video maker. Choose templates, customize easily, and share instantly for any occasion.",
    ];

    function getCategories(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $page = (int)$request->has('page') ? $request->get('page') : 1;

        $limit = 12;

        $rates = RateController::getRates(true);

        $datas = VideoCategory::query()
            ->whereStatus(1)
            ->where('parent_category_id', '>', 0)
            ->where(function ($q) {
                $q->whereHas('videoTemplates', fn($t) => $t->whereStatus(1)->whereDoFrontLottie(1)->whereIsDeleted(0));
            })
            ->with([
                'videoTemplates' => fn($t) => $t->whereStatus(1)->whereDoFrontLottie(1)->whereIsDeleted(0)->with(['videoCat', 'virtualCat']),
            ])->orderBy('id')
            ->paginate($limit, ['*'], 'page', $page);

        $datas->getCollection()->transform(function ($category) {
            /** @var VideoCategory $category */
            // Parent templates
            $parentTemplates = $category->videoTemplates;

            // Subcategory templates (flattened)
            $subTemplates = $category->subcategories->flatMap(fn($sub) => $sub->videoTemplates);

            // Merge + remove duplicates (by id)
            $category->templates = $parentTemplates
                ->merge($subTemplates)
                ->unique('id')
                ->sortBy('id')
                ->take(12)
                ->values();

            $category->makeHidden([
                'videoTemplates',
                'subcategories'
            ]);

            return $category;
        });

        $categories = [];

        foreach ($datas->items() as $data) {
            /** @var VideoCategory $data */

            $templateDatas = [];
            foreach ($data->templates as $row) {
                /** @var VideoTemplate $row */
                $templateDatas[] = HelperController::getVideoItemData(item: $row, rates: $rates);
            }

            $categories[] = [
                'category_id' => $data->id,
                'category_name' => $data->category_name,
                'category_title' => $data->category_title,
                'category_thumb' => HelperController::$mediaUrl . $data->category_thumb,
                'category_mockup' => null,
                'link' => $data->slug,
                'total_templates' => $data->total_templates,
                'templates' => $templateDatas
            ];
        }

        $response = [
            'category_hierarchy' => self::getSubCategories(null, true),
            'data' => $categories,
            "pagination" => PaginationController::getPagination($datas),
            "seo" => self::$templateSeo,
        ];

        $data = PReviewController::getPReviews("", 6, "15vODGdC", 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }

        $response['faqs'] = [
            "title" => "Frequently Asked Questions (FAQs)",
            "data" => [
                [
                    "question" => "1. What is a Video Invitation?",
                    "answer" => "A video invitation is a digital way to invite people to an event, using a video format rather than traditional paper invitations. It allows you to add creativity, sound, and visual elements, making your invitation more personal and engaging."
                ],
                [
                    "question" => "2. How do I Create a Video Invitation?",
                    "answer" => "You can easily create a video invitation using our website. Simply choose your preferred design, add event details, and customize the video with images, text, and music. Once you're satisfied, download the video and share it!"
                ],
                [
                    "question" => "3. What are the benefits of creating invitation videos?",
                    "answer" => "Creating invitation videos gives you a modern, creative, and customizable way to invite guests to your event. You can add music, photos, text, and personal touches to make your invitation stand out."
                ],
                [
                    "question" => "4. How do I share my invitation video?",
                    "answer" => "Once you’ve created your digital invite video, you can share it across multiple platforms like WhatsApp, email, or social media. It’s as simple as downloading the video and sending it to your guests!"
                ],
                [
                    "question" => "5. Can I create a video invitation for an event?",
                    "answer" => "Yes, you can create a video invitation for any type of event, from weddings to birthdays, corporate gatherings and more. We have a wide range of templates to match the theme and tone of your event."
                ],
                [
                    "question" => "6. What file format will my invitation video be in?",
                    "answer" => "Typically, the invitation video will be in a common video format such as MP4, which is compatible with most devices and platforms. You can easily share your video via WhatsApp, social media, or email."
                ]
            ],
        ];

        return $this->successed(datas: $response);
    }

    function getPage(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $oldSlug = $request->input('slug');
        if (empty($oldSlug)) return $this->failed(msg: "Parameters missing!");

        $hasPageInRequest = $request->has('page');
        $data = HelperController::extractAndRemoveTrailingNumber($oldSlug);
        $page = $hasPageInRequest ? $request->input('page', 1) : $data['number'] ?? 1;
        $slug = $data['string'] ?? 1;

        $slugData = VideoSlugHistory::whereSlug($slug)->first();
        if (empty($slugData)) return $this->failed(msg: "Invalid data");

        if ($slugData->reference_type === 'category') {
            return $this->getCategory($request, $slug, $page);
        } else if ($slugData->reference_type === 'virtual_page') {
            return $this->getVirtualPage($request, $slug, $page);
        } else if ($slugData->reference_type === 'templates') {
            return $this->getTemplate($request, $slug);
        }

        return $this->failed(msg: "Invalid data");
    }

    function getCategory(Request $request, $slug, $page): array|string
    {

        $category = VideoCategory::whereSlug($slug)->first();
        if (empty($category)) return $this->failed(msg: "Invalid data");

        $filter = isset($request->filter) ? $request->filter : [];

        $limit = 20;
        $cacheTag = "vi_category_$slug";
        $contentCacheKey = 'vi_category_content_' . $slug;
        $faqCacheKey = 'vi_category_faq_' . $slug;

        $templatesQuery = VideoTemplate::with(['videoCat', 'virtualCat'])->whereCategoryId($category->id)->whereDoFrontLottie(1)->whereIsDeleted(0)->whereStatus(1);

        if (!empty($filter)) $templatesQuery = self::getFilterQuery($templatesQuery, $filter);

        $datas = $templatesQuery->paginate($limit, ['*'], 'page', $page);
        $rates = RateController::getRates(true);

        $templateDatas = [];
        foreach ($datas->items() as $row) {
            /** @var VideoTemplate $row */
            $templateDatas[] = HelperController::getVideoItemData(item: $row, rates: $rates);
        }

        $response = [
            'type' => 'category',
            'string_id' => $category->string_id,
            'data' => [
                'category_id' => $category->id,
                'string_id' => $category->string_id,
                'category_name' => $category->category_name,
                'category_title' => $category->category_title,
                'category_thumb' => HelperController::$mediaUrl . $category->category_thumb,
                'category_mockup' => null,
                'cat_link' => $category->slug,
                'templates' => $templateDatas
            ],
            "page_link" => $category->slug,
            "filter_link" => $category->slug,
            "pagination" => PaginationController::getPagination($datas, $filter, $category->slug)
        ];

        $response['category_hierarchy'] = self::getSubCategories($category);

        $seoDatas = collect($category)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
        $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CategoryTemplatesApiController::$defaultTagLine;

        $response['seo'] = $seoDatas;
        $response['top_keywords'] = (isset($category->top_keywords)) ? HelperController::getTopKeywords(json_decode($category->top_keywords)) : [];

        if ($page == 1) {
            $response['contents'] = isset($category->contents) ? ContentManager::getContentsPath(rates: $rates, contents: json_decode(StorageUtils::get($category->contents)), uid: $this->uid, cacheTag: $cacheTag, cacheKey: $contentCacheKey) : [];
        } else {
            $response['contents'] = [];
        }

        $faqsResponse = ContentManager::faqsResponse(faqs: $category->faqs, premiumKeyword: $category->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey);
        $response['faqs'] = [
            "title" => $faqsResponse['faqs_title'],
            "data" => $faqsResponse['faqs'],
        ];
        $response['canonical_link'] = PaginationController::buildCanonicalLink($category->canonical_link, $category->full_slug, $page);
        $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($category);

        $data = PReviewController::getPReviews($this->uid, 6, $category->string_id, 1);
        if ($data['success']) $response['reviews'] = $data['data'];

        return $this->successed(datas: $response, noIndex: $category->no_index);
    }

    function getVirtualPage(Request $request, $slug, $page): array|string
    {

        $virtualPage = VideoVirtualCategory::whereSlug($slug)->first();
        if (empty($virtualPage)) return $this->failed(msg: "Invalid data");

        $filter = isset($request->filter) ? $request->filter : [];

        $cacheTag = "vi_vp_category_$slug";
        $faqCacheKey = 'vi_vp_category_faq_' . $slug;

        $cacheKey = $cacheTag . md5(json_encode([
                'filter' => json_encode($filter),
                'page' => $page,
            ]));

        $rates = RateController::getRates(true);

        $response = [
            'type' => 'virtual_page',
            'string_id' => $virtualPage->string_id,
            "page_link" => $virtualPage->slug,
            "filter_link" => $virtualPage->slug,
        ];

        $videoCat = VideoCategory::find($virtualPage->parent_category_id);

        $response['category_hierarchy'] = self::getSubCategories($videoCat);

        $seoDatas = collect($virtualPage)->only(['h1_tag', 'h2_tag', 'meta_title', 'meta_desc', 'short_desc', 'long_desc', 'tag_line']);
        $seoDatas['tag_line'] = $seoDatas->get('tag_line') ?? CategoryTemplatesApiController::$defaultTagLine;

        $response['seo'] = $seoDatas;
        $response['top_keywords'] = (isset($virtualPage->top_keywords)) ? HelperController::getTopKeywords(json_decode($virtualPage->top_keywords)) : [];

        $response['contents'] = isset($virtualPage->contents) ? ContentManager::getVideoContentsPath(
            rates: $rates,
            contents: json_decode(StorageUtils::get($virtualPage->contents)),
            uid: $this->uid,
            page: $page,
            filter: $filter,
            slug: $virtualPage->slug,
            cacheTag: $cacheTag,
            cacheKey: $cacheKey,
        ) : [];

        $faqsResponse = ContentManager::faqsResponse(faqs: $virtualPage->faqs, premiumKeyword: $virtualPage->primary_keyword, cacheTag: $cacheTag, cacheKey: $faqCacheKey);
        $response['faqs'] = [
            "title" => $faqsResponse['faqs_title'],
            "data" => $faqsResponse['faqs'],
        ];
        $response['canonical_link'] = PaginationController::buildCanonicalLink($virtualPage->canonical_link, $virtualPage->full_slug, $page);
        $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($videoCat, $virtualPage->category_name, $virtualPage->slug);

        $data = PReviewController::getPReviews($this->uid, 7, $virtualPage->string_id, 1);
        if ($data['success']) $response['reviews'] = $data['data'];

        return $this->successed(datas: $response, noIndex: $virtualPage->no_index);
    }

    function getTemplate(Request $request, $slug = null): array|string
    {
        if (empty($slug) && $this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $slug = $request->input('slug', $slug);
        $forApp = $request->input('forApp', false);
        if (empty($slug)) return $this->failed(msg: "Parameters missing!");

        $itemData = VideoTemplate::with(['videoCat', 'virtualCat'])
            ->where(function ($query) use ($slug) {
                $query->where('string_id', $slug)
                    ->orWhere('slug', $slug);
            })
            ->whereStatus(1)
            ->whereIsDeleted(0)
            ->first();

        $user_data = UserData::whereUid($this->uid)->first();

        $rates = RateController::getRates(true);
        $data = HelperController::getVideoItemData(item: $itemData, rates: $rates);
        $data = array(
            ...$data,
            'zip_url' => HelperController::$mediaUrl . $itemData->video_zip_url,
            'editable_image' => json_decode($itemData->editable_image),
            'change_text' => $itemData->change_text,
            'editable_text' => json_decode($itemData->editable_text),
            'change_music' => $itemData->change_music,
            'watermark_height' => $itemData->watermark_height,
            'template_type' => $itemData->template_type,
            'do_front_lottie' => $itemData->do_front_lottie,
            'encrypted' => $itemData->encrypted,
            'encryption_key' => $itemData->encryption_key,
            'keyword' => $itemData->keywordNames(),
            'upload_date' => $itemData->created_at->format('d/m/Y H:i:s'),
            'date_published' => $itemData->created_at->format('d/m/Y H:i:s'),
        );

        try {
            $SearchApi = new VideoSearchApiController($request);
            $searchData = $SearchApi->exactKeywordTemplates($rates, $itemData->keyword, 15, $itemData->string_id);
        } catch (QueryException|Exception $e) {
            $searchData['datas'] = [];
        }

        $response['type'] = 'product_page';
        $response['string_id'] = $itemData->string_id;
        $response['data'] = $data;
        $response['suggested'] = $searchData['datas'];

        $response['category_hierarchy'] = self::getSubCategories($itemData->videoCat);
        $response['pre_breadcrumb'] = self::getCategoryBreadcrumbs($itemData->videoCat, $itemData->video_name, $itemData->slug);
        $response['cta'] = isset($itemData->cta) ? HelperController::getCTA($itemData->cta) : null;
        $response['seo'] = [
            'h2_tag' => $itemData->h2_tag,
            'meta_title' => $itemData->meta_title ?? $itemData->video_name,
            'description' => $itemData->description,
            'meta_description' => $itemData->meta_description
        ];

        $response['canonical_link'] = PaginationController::buildCanonicalLink($itemData->canonical_link, $itemData->full_slug, 1);

        $data = PReviewController::getPReviews($this->uid, 8, $itemData->string_id, 1);
        if ($data['success']) {
            $response['reviews'] = $data['data'];
        }
        $response['needToPurchase'] = !VideoPurchaseHistory::where('product_id', $itemData->string_id)->where('user_id', $this->uid)->exists();

        $ipData = HelperController::getIpAndCountry($request);
        $response['currency'] = $ipData['cur'];
        $response['contact_no'] = is_null($user_data) ? null : $user_data->contact_no;

        return $this->successed(datas: [
            'type' => 'product_page',
            'data' => $response
        ], noIndex: $itemData->no_index);
    }

    function getPurchases(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $page = $request->has('page') ? $request->get('page') : 1;

        $limit = HelperController::getPaginationLimit(size: 10);

        $purHistory = array();

        $purDatas = MasterPurchaseHistory::whereUserId($this->uid)->wherePaymentStatus('paid')->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $allCategoryIds = $purDatas->getCollection()->pluck('product_id')->unique();
        $designs = VideoTemplate::whereIn('string_id', $allCategoryIds)->get()->keyBy('string_id');

        foreach ($purDatas->items() as $row) {
            /** @var MasterPurchaseHistory $row */
            /** @var VideoTemplate $subRow */

            $subRow = $designs->get($row->product_id);
            $currency_code = "$";
            if ($row->currency_code === "INR") {
                $currency_code = "₹";
            }

            $amount = $currency_code . $row->amount;

            $purHistory[] = array(
                'id' => $row->product_id,
                'type' => $row->product_type,
                'name' => $subRow->video_name,
                'image' => HelperController::$mediaUrl . $subRow->video_thumb,
                'width' => $subRow->width,
                'height' => $subRow->height,
                'transaction_id' => $row->payment_id,
                'amount' => $amount,
                'purchase_date' => $row->created_at->format('d/m/Y H:i:s'),
                'status' => HelperController::checkSubsStatus($row->status),
                'color' => HelperController::getSubsColor($row->status),
            );
        }

        $msg = 'Data loaded';
        if (($page == 1 || $page == '1') && sizeof($purHistory) == 0) {
            $msg = 'No History exist.';
        }

        $response['isLastPage'] = $purDatas->currentPage() >= $purDatas->lastPage();
        $response['datas'] = $purHistory;

        return $this->successed(msg: $msg, datas: $response);
    }

    public static function getCategoryBreadcrumbs(VideoCategory $cat = null, $last = null, $link = null): array
    {

        $pre_breadcrumb[] = [
            'value' => "My Video Invites",
            "link" => "/",
            "openinnewtab" => 0,
            "nofollow" => 0
        ];

        if ($cat) {
//            if ($cat->parentCategory) {
//                $pre_breadcrumb[] = [
//                    'value' => $cat->parentCategory->category_name,
//                    "link" => $cat->parentCategory->slug,
//                    "openinnewtab" => 0,
//                    "nofollow" => 0
//                ];
//            }
            $pre_breadcrumb[] = [
                'value' => $cat->category_name,
                "link" => $cat->slug,
                "openinnewtab" => 0,
                "nofollow" => 0
            ];
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

    public static function getSubCategories(VideoCategory|int|null $category, $allTags = false): array
    {
        if (is_int($category)) $category = VideoCategory::findId(select: null, isStatus: 1, id: $category);

        $parents = VideoCategory::query()->select(['id', 'category_name', 'category_thumb', 'slug'])->where('parent_category_id', '!=', 0)->where('total_templates', '>', 0)->whereStatus(1)->get();
        $parentTags = [];
        foreach ($parents as $parent) {
            $parentTags[] = [
                'id' => $parent->id,
                'category_name' => $parent->category_name,
                'category_title' => $parent->category_title,
                'category_thumb' => HelperController::$mediaUrl . $parent->category_thumb,
                'url' => $parent->slug,
                'link' => $parent->slug,
            ];
        }

        if ($allTags) {
            $parentCat = VideoVirtualCategory::all();
            return ["categories" => array_values($parentTags), "tags" => self::getChilds($parentCat)];
        }

        if ($category) {
            $parentCat = VideoVirtualCategory::whereParentCategoryId($category->id)->whereStatus(1)->get();
            return ["categories" => array_values($parentTags), "tags" => self::getChilds($parentCat)];
        }

        return ["categories" => array_values($parentTags), "tags" => []];
    }

    /**
     * Get child categories for a given parent category
     *
     * @param VideoVirtualCategory[] $categories
     * @return array
     */
    private static function getChilds(mixed $categories): array
    {
        $childs = [];
        foreach ($categories as $subcategory) {
            $childs[] = [
                'id' => $subcategory->id,
                'category_name' => $subcategory->category_name,
                'category_title' => $subcategory->category_name,
                'category_thumb' => HelperController::$mediaUrl . $subcategory->category_thumb,
                'url' => $subcategory->slug,
                'link' => $subcategory->slug,
            ];
        }

        return array_values($childs);
    }

    public static function getFilterQuery($templatesQuery, $filter)
    {
        $sort_by = "latest";
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'language':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $languages = $matches[1];
                        $templatesQuery->where(function ($query) use ($languages) {
                            foreach ($languages as $language) {
                                $language = VideoLanguage::where('id_name', $language)->first();
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
                                $style = VideoStyle::where('id_name', $style)->first();
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
                                $tempSize = VideoSize::where('id_name', $value)->first();
                                $templateSize = $tempSize->id ?? "";
                                if ($templateSize) {
                                    $query->orWhere('template_size', $templateSize);
                                }
                            }
                        });
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
                                $interest = VideoInterest::where('id_name', $interest)->first();
                                $interestId = $interest->id ?? "";
                                if ($interestId) {
                                    $query->orWhereJsonContains('interest_id', json_encode($interestId));
                                }
                            }
                        });
                        break;

                    case 'color':
                        $lowerValue = strtolower($value);
                        $templatesQuery->where('color_ids', $lowerValue);
                        break;

                    case 'religion':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $religions = $matches[1];
                        $templatesQuery->where(function ($query) use ($religions) {
                            foreach ($religions as $religion) {
                                $religion = VideoReligion::where('id_name', $religion)->first();
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
                    case 'theme':
                        preg_match_all("/'([^']+)'/", $value, $matches);
                        $themes = $matches[1];
                        $templatesQuery->where(function ($query) use ($themes) {
                            foreach ($themes as $theme) {
                                $theme = VideoTheme::where('id_name', $theme)->first();
                                $themName = $theme->id ?? "";
                                if ($themName) {
                                    $query->orWhereJsonContains('theme_id', json_encode($themName));
                                }
                            }
                        });
                        break;
                    case 'sort_by':
                        $templatesQuery->reOrder();
                        $sort_by = $value;
                        break;
                }
            }
        }

        $lowerValue = strtolower($sort_by);
        if ($lowerValue === "oldest") {
            $templatesQuery->orderBy('id', 'asc');
        } else if ($lowerValue === "popular") {
            $templatesQuery->orderBy('creation', 'desc');
        } else if ($lowerValue === "low_to_high") {
            $templatesQuery->orderBy('pages', 'asc');
        } else if ($lowerValue === "high_to_low") {
            $templatesQuery->orderBy('pages', 'desc');
        } else {
            $templatesQuery->orderBy('id', 'desc');
        }

        return $templatesQuery;
    }

}

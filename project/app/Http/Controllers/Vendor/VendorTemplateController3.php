<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\DomainChecker;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Models\Color;
use App\Models\Design;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Religion;
use App\Models\Size;
use App\Models\Style;
use App\Models\Theme;
use App\Models\UserData;
use App\Models\Vendor\Tenants;
use App\Models\Vendor\VendorDetails;
use App\Models\Vendor\VendorTemplate;
use App\Models\WebFbSelling;
use Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Utils\StorageUtils;

class VendorTemplateController extends ApiController
{
    public function addOrUpdateVendorDetails(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $error = self::isValidVendor();
        if ($error)
            return $this->failed(msg: $error);

        $vendorDetails = VendorDetails::whereUserId($this->uid)->first() ?? new VendorDetails();
        $vendorDetails->user_id = $this->uid;

        $vendorDetails->title = $request->input('title');
        $vendorDetails->brand_name = $request->input('brand_name');
        $vendorDetails->contact_no = $request->input('contact_no');
        $vendorDetails->description = $request->input('description');
        $vendorDetails->owner_name = $request->input('owner_name');
        $vendorDetails->email = $request->input('email');
        $vendorDetails->instagram_url = $request->input('instagram_url');
        $vendorDetails->facebook_url = $request->input('facebook_url');
        $vendorDetails->website_url = $request->input('website_url');

        // BUSINESS LOGO
        if ($request->hasFile('business_logo')) {
            $file = $request->file('business_logo');
            $name = $this->uid . '-logo-' . time() . '.' . $file->getClientOriginalExtension();
            StorageUtils::storeAs($file, 'uploadedFiles/vendor', $name);
            $vendorDetails->business_logo = 'uploadedFiles/vendor/' . $name;

        } elseif ($request->boolean('remove_business_logo')) {
            $vendorDetails->business_logo = null;
        }


        // OWNER PROFILE PHOTO
        if ($request->hasFile('owner_profile_photo')) {
            $file = $request->file('owner_profile_photo');
            $name = $this->uid . '-owner-' . time() . '.' . $file->getClientOriginalExtension();
            StorageUtils::storeAs($file, 'uploadedFiles/vendor', $name);
            $vendorDetails->owner_profile_photo = 'uploadedFiles/vendor/' . $name;

        } elseif ($request->boolean('remove_owner_profile_photo')) {
            $vendorDetails->owner_profile_photo = null;
        }

        $vendorDetails->save();

        return $this->successed(msg: "Vendor Details Updated Successfully", datas: ["data" => $vendorDetails]);
    }

    // ─── Public API Methods ──────────────────────────────────────────────────────
    public function getVendorHome(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $limit = HelperController::getPaginationLimit(size: 50);
        $page = (int) $request->input('page', 1);

        $error = self::isValidVendor();
        if ($error)
            return $this->failed(msg: $error);

        $query = Design::query()->where('status', 1);

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('h2_tag', 'like', "%{$search}%")
                    ->orWhere('id_name', 'like', "%{$search}%");
            });
        }

        // ⚡ FILTERS
        self::getFilterQuery($query, $request->input('filter'));

        $query->orderByDesc('id');

        // 📦 PAGINATION
        $templates = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $templates->currentPage() >= $templates->lastPage();

        // 📂 CATEGORY FETCH (ONLY REQUIRED IDS)
        $categoryIds = $templates->getCollection()
            ->pluck('new_category_id')
            ->filter()
            ->unique()
            ->values();

        $categories = NewCategory::whereIn('id', $categoryIds)
            ->select('id', 'cat_link', 'category_name', 'size')
            ->get()
            ->keyBy('id');

        // ⚡ FAST LOOKUP
        $vendorTemplateIDs = VendorTemplate::where('user_id', $this->uid)
            ->pluck('template_id')
            ->flip();

        $rates = RateController::getRates();

        // 🎯 DATA TRANSFORM
        $item_rows = collect($templates->items())->map(function ($item) use ($categories, $rates, $vendorTemplateIDs) {

            $catRow = $categories[$item->new_category_id] ?? null;

            $catLink = $catRow->cat_link ?? (HelperController::$webPageUrl . "templates/p/" . $item->id_name);

            $itemData = HelperController::getItemData(
                uid: $this->uid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array, true) ?? [],
                catLink: $catLink,
                rates: $rates
            );

            // ⚡ O(1)
            $itemData['is_vendor_added'] = isset($vendorTemplateIDs[$itemData['string_id']]);

            return $itemData;
        })->filter()->values();

        // 🧾 Vendor Details
        $vendorDetails = VendorDetails::whereUserId($this->uid)->first();

        // ⚡ FILTERS + CATEGORIES ONLY PAGE 1
        $filters = [];
        $categoriesData = [];

        if ($page === 1) {

            $subCategories = NewCategory::where('total_templates', '!=', 0)
                ->where('parent_category_id', '!=', 0)
                ->where('status', 1)
                ->select('id', 'parent_category_id', 'category_name', 'id_name')
                ->get();

            $data = $this->buildFiltersAndCategories($subCategories);

            $filters = collect($data)->only([
                'colors',
                'interests',
                'sizes',
                'styles',
                'languages',
                'religions',
                'themes'
            ]);

            $categoriesData = [
                'parent_categories' => $data['parent_categories'],
                'child_categories' => $data['child_categories'],
            ];
        }

        return $this->successed(msg: 'Loading Success!', datas: [
            "is_vendor_details_added" => (bool) $vendorDetails,
            "vendor_details" => $vendorDetails,
            'isLastPage' => $isLastPage,
            'page' => $page,
            'datas' => $item_rows,
            'filters' => $filters,
            'categories' => $categoriesData,
        ]);
    }

    public function getVendorTemplate(Request $request): array|string
    {
        $limit = HelperController::getPaginationLimit(size: 50);
        $page = $request->input('page', 1);

        $resolved = $this->resolveTenantUid($request);
        if (is_string($resolved))
            return $this->failed(msg: $resolved);
        ['uid' => $targetUid, 'user' => $targetUser] = $resolved;
        $query = VendorTemplate::where('user_id', $targetUid);

        $filter = $request->input('filter');
        $searchTerm = $request->input('search');

        $matchingDesignIds = [];
        if ($searchTerm || !empty($filter)) {
            $designQuery = Design::where('status', 1);
            if ($searchTerm) {
                $designQuery->where(function ($q) use ($searchTerm) {
                    $q->where('h2_tag', 'like', '%' . $searchTerm . '%')
                        ->orWhere('id_name', 'like', '%' . $searchTerm . '%');
                });
            }

            if (!empty($filter)) {
                self::getFilterQuery($designQuery, $filter);
            }

            // Optimization: Only care about IDs that are in the vendor's list
            $allVendorTemplateIds = VendorTemplate::where('user_id', $targetUid)->pluck('template_id');
            $matchingDesignIds = $designQuery->whereIn('string_id', $allVendorTemplateIds)->pluck('string_id');

            $query->whereIn('template_id', $matchingDesignIds);
        }

        $vendorTemplates = $query->paginate($limit, ['*'], 'page', $page);
        $templateIds = $vendorTemplates->getCollection()->pluck('template_id');
        $designs = Design::whereIn('string_id', $templateIds)->get()->keyBy('string_id');


        $rates = RateController::getRates();
        $categoriesMap = NewCategory::whereIn('id', $designs->pluck('new_category_id')->unique())->get()->keyBy('id');

        $datas = $vendorTemplates->getCollection()->map(function ($vt) use ($designs, $categoriesMap, $rates, $targetUid) {
            $item = $designs->get($vt->template_id);
            if (!$item)
                return null;

            $catRow = $categoriesMap[$item->new_category_id] ?? null;
            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
            if ($catRow)
                $catLink = $catRow->cat_link;

            $itemData = HelperController::getItemData(
                uid: $targetUid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array, true) ?? [],
                catLink: $catLink,
                rates: $rates
            );

            if ($itemData) {
                $itemData['vendor_price'] = (float) $vt->price;
                $itemData['vendor_template_id'] = $vt->id;
                $itemData['is_vendor_added'] = true;
            }

            return $itemData;
        })->filter()->values();

        $transformedFilters = [];
        $transformedCategories = [];
        $templateCounts = [];

        if ($page == 1) {
            // 1. Get ALL categories assigned to this vendor (ignoring current filters for sidebar)
            $allVendorIds = VendorTemplate::where('user_id', $targetUid)->pluck('template_id');
            $allDesignsForCats = Design::whereIn('string_id', $allVendorIds)
                ->where('status', 1)
                ->select('new_category_id')
                ->get();
            $categoryIds = $allDesignsForCats->pluck('new_category_id')->unique()->filter()->values();

            $subCategories = NewCategory::whereIn('id', $categoryIds)
                ->where('parent_category_id', '!=', 0)
                ->where('total_templates', '!=', 0)
                ->where('status', 1)
                ->select('id', 'parent_category_id', 'category_name', 'id_name')
                ->get();

            $data = $this->buildFiltersAndCategories($subCategories);

            // 2. Get counts based on ACTIVE filters
            $filteredQuery = VendorTemplate::where('user_id', $targetUid);
            if ($searchTerm || !empty($filter)) {
                $filteredQuery->whereIn('template_id', $matchingDesignIds);
            }
            $filteredIds = $filteredQuery->pluck('template_id');

            $designsForCounts = Design::whereIn('string_id', $filteredIds)
                ->where('status', 1)
                ->select('is_premium', 'is_freemium')
                ->get();

            $templateCounts = [
                'total' => $designsForCounts->count(),
                'free' => $designsForCounts->where('is_premium', 0)->where('is_freemium', '!=', 1)->count(),
                'freemium' => $designsForCounts->where('is_freemium', 1)->count(),
                'premium' => $designsForCounts->where('is_premium', 1)->where('is_freemium', '!=', 1)->count(),
            ];

            $transformedFilters = collect($data)->only([
                'colors',
                'interests',
                'sizes',
                'styles',
                'languages',
                'religions',
                'themes'
            ]);

            $transformedCategories = [
                'parent_categories' => $data['parent_categories'],
                'child_categories' => $data['child_categories'],
            ];
        }


        $vendorDetails = VendorDetails::whereUserId($targetUid)->first();

        //    $userRes = [
        //        'name'        => $targetUser->name,
        //        'user_name'   => $targetUser->user_name,
        //        'unique_name' => '@' . $targetUser->user_name,
        //        'bio'         => $targetUser->bio,
        //        'photo_uri'   => str_contains($targetUser->photo_uri ?? '', 'uploadedFiles/')
        //            ? HelperController::$mediaUrl . $targetUser->photo_uri
        //            : $targetUser->photo_uri,
        //    ];

        return $this->successed(msg: 'Loading Success!', datas: [
            "is_vendor_details_added" => (bool) $vendorDetails,
            "vendor_details" => $vendorDetails,
            "template_count" => $templateCounts ? [
                "all" => $templateCounts['total'],
                "free" => (int) $templateCounts['free'],
                "premium" => (int) $templateCounts['premium'] + (int) $templateCounts['freemium'],
            ] : [],
            'isLastPage' => $vendorTemplates->currentPage() >= $vendorTemplates->lastPage(),
            'page' => $page,
            'datas' => $datas,
            'filters' => $transformedFilters,
            'categories' => $transformedCategories,
        ]);
    }


    public function toggleVendorTemplate(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }
        $validationError = self::isValidVendor();
        if ($validationError)
            return $this->failed(msg: $validationError);

        $templateId = $request->input('template_id');
        $price = $request->input('price', 0);
        $currency = $request->input('currency', "INR");

        if (!$templateId) {
            return $this->failed(msg: "template_id is required");
        }

        $existing = VendorTemplate::where('user_id', $this->uid)
            ->where('template_id', $templateId)
            ->first();

        if ($existing) {
            $existing->delete();
            return $this->successed(msg: 'Template removed from your account');
        }

        $template = Design::whereStringId($templateId)->first();
        if (!$template)
            return $this->failed(msg: "Template Not Found");

        $vendorTemplate = new VendorTemplate();
        $vendorTemplate->user_id = $this->uid;
        $vendorTemplate->template_id = $templateId;
        $vendorTemplate->price = $price;
        $vendorTemplate->currency = $currency;
        $vendorTemplate->save();

        return $this->successed(msg: 'Template added to your account');
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
                    // case 'is_premium':
                    //     if ($value === "true") {
                    //         $templatesQuery->where(function ($query) {
                    //             $query->where('is_premium', 1)->orWhere('is_freemium', 1);
                    //         });
                    //     } else {
                    //         $templatesQuery->where('is_premium', 0)->where('is_freemium', 0);
                    //     }
                    //     break;

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

                    case 'category':
                        // $value: int id — auto-detect parent vs child
                        $catId = (int) $value;
                        $category = NewCategory::find($catId);
                        if ($category) {
                            if ($category->parent_category_id != 0) {
                                // It's a child category — filter directly
                                $templatesQuery->where('new_category_id', $catId);
                            } else {
                                // It's a parent — expand to all child category ids
                                $childIds = NewCategory::where('parent_category_id', $catId)->pluck('id')->toArray();
                                $templatesQuery->whereIn('new_category_id', $childIds);
                            }
                        }
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

    private function buildFiltersAndCategories($subCategories): array
    {
        return Cache::remember(
            'filters_categories_' . md5(json_encode($subCategories->pluck('id'))),
            600,
            function () use ($subCategories) {

                // ✅ Parent Categories
                $parentIds = $subCategories->pluck('parent_category_id')
                    ->filter()
                    ->unique()
                    ->values();

                $parentCats = NewCategory::whereIn('id', $parentIds)
                    ->where('status', 1)
                    ->select('id', 'category_name', 'id_name', 'parent_category_id')
                    ->get();

                // ✅ Root IDs
                $rootIds = $parentCats->pluck('id')->values();

                // ✅ Colors
                $colors = Color::where('status', 1)
                    ->select('id', 'code')
                    ->orderByDesc('id')
                    ->get();

                // 🔥 COMMON JSON FILTER BUILDER
                $applyJsonFilter = function ($query, $column) use ($rootIds) {
                    $query->where(function ($q) use ($rootIds, $column) {
                        foreach ($rootIds as $id) {
                            $q->orWhereJsonContains($column, $id);
                        }
                    });
                };

                // ✅ Interests (DB FILTER ONLY)
                $interests = Interest::where('status', 1)
                    ->select('id', 'id_name', 'name')
                    ->where(fn($q) => $applyJsonFilter($q, 'new_category_id'))
                    ->orderByDesc('id')
                    ->get();

                // ✅ Sizes (DB FILTER ONLY)
                $sizes = Size::where('status', 1)
                    ->select(
                        'id',
                        'size_name as name',
                        'width_ration as p_width',
                        'height_ration as p_height',
                        'width as l_width',
                        'height as l_height',
                        'id_name'
                    )
                    ->where(fn($q) => $applyJsonFilter($q, 'new_category_id'))
                    ->get();

                // ✅ Styles
                $styles = Style::where('status', 1)
                    ->select('id', 'id_name', 'name')
                    ->orderByDesc('id')
                    ->get();

                // ✅ Languages / Religions
//                $languages = $this->getCounts(Language::class, ['id', 'id_name', 'name'], 'lang_id');
//                $religions = $this->getCounts(Religion::class, ['id', 'religion_name as name', 'id_name'], 'religion_id');
    
                $languages = Language::where('status', 1)
                    ->select('id', 'id_name', 'name')
                    ->orderByDesc('id')
                    ->get();

                $religions = Religion::where('status', 1)
                    ->select('id', 'religion_name as name', 'id_name')
                    ->orderByDesc('id')
                    ->get();

                // ✅ Themes (NO LOOP NOW 🚀)
                $themes = Theme::where('status', 1)
                    ->select('id', 'id_name', 'name')
                    ->where(fn($q) => $applyJsonFilter($q, 'new_category_id'))
                    ->get();

                return [
                    'colors' => $colors,
                    'interests' => $interests,
                    'sizes' => $sizes,
                    'styles' => $styles,
                    'languages' => $languages,
                    'religions' => $religions,
                    'themes' => $themes,
                    'parent_categories' => $parentCats->values(),
                    'child_categories' => $subCategories->values(),
                ];
            }
        );
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

    private function resolveTenantUid(Request $request): array|string
    {
        $requestDomain = DomainChecker::getDomainName($request) ?? $request->header('Ref-Host');

        $craftyDomains = ['www.craftyartapp.com', 'craftyartapp.com', 'beta.craftyartapp.com', 'localhost', '192.168.29.23', '192.168.29.179'];
        if (empty($requestDomain) || in_array($requestDomain, $craftyDomains)) {
            if ($this->isFakeRequestAndUser($request))
                return "UnAuthorize";
            $error = self::isValidVendor();
            if ($error)
                return $error;
            return ['uid' => $this->uid, 'user' => UserData::whereUid($this->uid)->first()];
        }

        $tenant = Tenants::where('domain', $requestDomain)->where('status', 'active')->first();
        if (!$tenant)
            return $requestDomain;

        $error = self::isValidVendor(uid: $tenant->user_id);
        if ($error)
            return $error;

        return ['uid' => $tenant->user_id, 'user' => UserData::whereUid($tenant->user_id)->first()];
    }

    public function isValidVendor($uid = null, $userName = null): ?string
    {
        if ($userName)
            $userData = UserData::with('latestTransactionLog')->whereUserName($userName)->first();
        else
            $userData = UserData::with('latestTransactionLog')->whereUid($uid ?? $this->uid)->first();

        if (!$userData)
            return ($uid || $userName) ? "User Not Found" : "UnAuthorize";

        if (!$userData->latestTransactionLog)
            return "Subscription Not Found";

        if ($userData->latestTransactionLog->expired_at < now())
            return "Subscription is Expired";

        $planLimits = $userData->latestTransactionLog->plan_limit;
        $isVendor = collect($planLimits)->firstWhere('slug', 'is_vendor');

        if (!$isVendor || empty($isVendor['meta_value'])) {
            return "User does not have vendor access.";
        }

        return null;
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Font;
use App\Models\Design;
use App\Models\Category;
use App\Models\AppCategory;
use App\Models\StickerCategory;
use App\Models\StickerItem;
use App\Models\BgCategory;
use App\Models\BgItem;
use App\Models\Color;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Religion;
use App\Models\SearchTag;
use App\Models\FrameCategory;
use App\Models\FrameItem;
use App\Models\Size;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Caricature\Attire;
use App\Models\Theme;
use App\Models\EditorVisit;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\PendingTask;
use App\Models\Subscription;
use App\Models\Revenue\BusinessSupportPurchaseHistory;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\AIPurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends AppBaseController
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($isManager = null)
    {

        $currentuserid = Auth::user()->user_type;

        if(RoleManager::isSalesManager($currentuserid) || RoleManager::isSalesEmployee($currentuserid)){
            return redirect()->route('order_user.index');
        }
        // added 28-04-26 5:19pm
        if (RoleManager::isHr($currentuserid)) {
            return redirect()->route('job_applications.index');
        }

        $idAdmin = RoleManager::isAdmin(Auth::user()->user_type);

        $isSeoExecutive = RoleManager::isSeoExecutive(Auth::user()->user_type);
        $condition = "=";
        if ($idAdmin) {
            $condition = "!=";
            $currentuserid = -1;
        } else {
            $currentuserid = Auth::user()->id;
        }

        $datas['app'] = AppCategory::count();
        $datas['app_live'] = AppCategory::where('status', '1')->count();
        $datas['app_unlive'] = AppCategory::where('status', '0')->count();

        if ($isSeoExecutive) {
            $datas['pending_task'] = PendingTask::where('emp_id', Auth::user()->id)->where('status', 2)->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['fonts'] = Font::count();
            $datas['fonts_live'] = Font::where('status', '1')->count();
            $datas['fonts_unlive'] = Font::where('status', '0')->count();
        } else {
            $datas['fonts'] = Font::where('emp_id', $condition, $currentuserid)->count();
            $datas['fonts_live'] = Font::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['fonts_unlive'] = Font::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['cat'] = Category::count();
            $datas['cat_live'] = Category::where('status', '1')->count();
            $datas['cat_unlive'] = Category::where('status', '0')->count();
        } else {
            $datas['cat'] = Category::where('emp_id', $condition, $currentuserid)->count();
            $datas['cat_live'] = Category::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['cat_unlive'] = Category::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['item'] = Design::count();
            $datas['item_live'] = Design::where('status', '1')->count();
            $datas['item_unlive'] = Design::where('status', '0')->count();
        } else {
            $datas['item'] = Design::where('emp_id', $condition, $currentuserid)->count();
            $datas['item_live'] = Design::where('status', '1')->where('emp_id', $condition, $currentuserid)->count();
            $datas['item_unlive'] = Design::where('status', '0')->where('emp_id', $condition, $currentuserid)->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['stk_cat'] = StickerCategory::count();
            $datas['stk_cat_live'] = StickerCategory::where('status', '1')->count();
            $datas['stk_cat_unlive'] = StickerCategory::where('status', '0')->count();
        } else {
            $datas['stk_cat'] = StickerCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['stk_cat_live'] = StickerCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['stk_cat_unlive'] = StickerCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['stk_item'] = StickerItem::count();
            $datas['stk_item_live'] = StickerItem::where('status', '1')->count();
            $datas['stk_item_unlive'] = StickerItem::where('status', '0')->count();
        } else {
            $datas['stk_item'] = StickerItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['stk_item_live'] = StickerItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['stk_item_unlive'] = StickerItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['bg_cat'] = BgCategory::count();
            $datas['bg_cat_live'] = BgCategory::where('status', '1')->count();
            $datas['bg_cat_unlive'] = BgCategory::where('status', '0')->count();
        } else {
            $datas['bg_cat'] = BgCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['bg_cat_live'] = BgCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['bg_cat_unlive'] = BgCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        if (isset($isManager) && $isManager == 1) {
            $datas['bg_item'] = BgItem::count();
            $datas['bg_item_live'] = BgItem::where('status', '1')->count();
            $datas['bg_item_unlive'] = BgItem::where('status', '0')->count();
        } else {
            $datas['bg_item'] = BgItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['bg_item_live'] = BgItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['bg_item_unlive'] = BgItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Color
        if (isset($isManager) && $isManager == 1) {
            $datas['color_item'] = Color::count();
            $datas['color_item_live'] = Color::where('status', '1')->count();
            $datas['color_item_unlive'] = Color::where('status', '0')->count();
        } else {
            $datas['color_item'] = Color::where('emp_id', $condition, $currentuserid)->count();
            $datas['color_item_live'] = Color::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['color_item_unlive'] = Color::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }
        // size
        if (isset($isManager) && $isManager == 1) {
            $datas['size_item'] = Size::count();
            $datas['size_item_live'] = Size::where('status', '1')->count();
            $datas['size_item_unlive'] = Size::where('status', '0')->count();
        } else {
            $datas['size_item'] = Size::where('emp_id', $condition, $currentuserid)->count();
            $datas['size_item_live'] = Size::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['size_item_unlive'] = Size::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        // Relegion
        if (isset($isManager) && $isManager == 1) {
            $datas['religion_item'] = Religion::count();
            $datas['religion_item_live'] = Religion::where('status', '1')->count();
            $datas['religion_item_unlive'] = Religion::where('status', '0')->count();
        } else {
            $datas['religion_item'] = Religion::where('emp_id', $condition, $currentuserid)->count();
            $datas['religion_item_live'] = Religion::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['religion_item_unlive'] = Religion::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // New Category
        if (isset($isManager) && $isManager == 1) {
            $datas['new_categories_item'] = NewCategory::count();
            $datas['new_categories_item_live'] = NewCategory::where('status', '1')->count();
            $datas['new_categories_item_unlive'] = NewCategory::where('status', '0')->count();
        } else {

            $datas['new_categories_item'] = NewCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['new_categories_item_live'] = NewCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['new_categories_item_unlive'] = NewCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Language
        if (isset($isManager) && $isManager == 1) {
            $datas['language_item'] = Language::count();
            $datas['language_item_live'] = Language::where('status', '1')->count();
            $datas['language_item_unlive'] = Language::where('status', '0')->count();
        } else {
            $datas['language_item'] = Language::where('emp_id', $condition, $currentuserid)->count();
            $datas['language_item_live'] = Language::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['language_item_unlive'] = Language::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Theme
        if (isset($isManager) && $isManager == 1) {
            $datas['theme_item'] = Theme::count();
            $datas['theme_item_live'] = Theme::where('status', '1')->count();
            $datas['theme_item_unlive'] = Theme::where('status', '0')->count();
        } else {
            $datas['theme_item'] = Theme::where('emp_id', $condition, $currentuserid)->count();
            $datas['theme_item_live'] = Theme::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['theme_item_unlive'] = Theme::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // SpecialKeyword
        if (isset($isManager) && $isManager == 1) {
            $datas['keyword_item'] = SpecialKeyword::count();
            $datas['keyword_item_live'] = SpecialKeyword::where('status', '1')->count();
            $datas['keyword_item_unlive'] = SpecialKeyword::where('status', '0')->count();
        } else {
            $datas['keyword_item'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->count();
            $datas['keyword_item_live'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['keyword_item_unlive'] = SpecialKeyword::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Search Tags
        if (isset($isManager) && $isManager == 1) {
            $datas['search_tag_item'] = SearchTag::count();
            $datas['search_tag_item_live'] = SearchTag::where('status', '1')->count();
            $datas['search_tag_item_unlive'] = SearchTag::where('status', '0')->count();
        } else {
            $datas['search_tag_item'] = SearchTag::where('emp_id', $condition, $currentuserid)->count();
            $datas['search_tag_item_live'] = SearchTag::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['search_tag_item_unlive'] = SearchTag::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        // Interest
        if (isset($isManager) && $isManager == 1) {
            $datas['interest_item'] = Interest::count();
            $datas['interest_item_live'] = Interest::where('status', '1')->count();
            $datas['interest_item_unlive'] = Interest::where('status', '0')->count();
        } else {
            $datas['interest_item'] = Interest::where('emp_id', $condition, $currentuserid)->count();
            $datas['interest_item_live'] = Interest::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['interest_item_unlive'] = Interest::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }


        if (isset($isManager) && $isManager == 1) {
            $datas['frame_cat_item'] = FrameCategory::count();
            $datas['frame_cat_item_live'] = FrameCategory::where('status', '1')->count();
            $datas['frame_cat_item_unlive'] = FrameCategory::where('status', '0')->count();
        } else {
            $datas['frame_cat_item'] = FrameCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['frame_cat_item_live'] = FrameCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['frame_cat_item_unlive'] = FrameCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['frame_item'] = FrameItem::count();
            $datas['frame_item_live'] = FrameItem::where('status', '1')->count();
            $datas['frame_item_unlive'] = FrameItem::where('status', '0')->count();
        } else {
            $datas['frame_item'] = FrameItem::where('emp_id', $condition, $currentuserid)->count();
            $datas['frame_item_live'] = FrameItem::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['frame_item_unlive'] = FrameItem::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['video_cat_item'] = VideoCategory::count();
            $datas['video_cat_item_live'] = VideoCategory::where('status', '1')->count();
            $datas['video_cat_item_unlive'] = VideoCategory::where('status', '0')->count();
        } else {
            $datas['video_cat_item'] = VideoCategory::where('emp_id', $condition, $currentuserid)->count();
            $datas['video_cat_item_live'] = VideoCategory::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['video_cat_item_unlive'] = VideoCategory::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (isset($isManager) && $isManager == 1) {
            $datas['video_template_item'] = VideoTemplate::count();
            $datas['video_template_item_live'] = VideoTemplate::where('status', '1')->count();
            $datas['video_template_item_unlive'] = VideoTemplate::where('status', '0')->count();
        } else {
            $datas['video_template_item'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->count();
            $datas['video_template_item_live'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->where('status', '1')->count();
            $datas['video_template_item_unlive'] = VideoTemplate::where('emp_id', $condition, $currentuserid)->where('status', '0')->count();
        }

        if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {

            // Indexed pages WITH valid category
            $datas['index_special_page'] = SpecialPage::where('no_index', 0)
                ->where('status', 1)
                ->whereNotNull('cat_id')
                ->where('cat_id', '!=', 0)
                ->count();

            $datas['index_keyword_page'] = SpecialKeyword::where('no_index', 0)
                ->where('status', 1)
                ->whereNotNull('cat_id')
                ->where('cat_id', '!=', 0)
                ->count();

            $datas['index_product_page'] = Design::where('no_index', 0)
                ->where('status', 1)
                ->whereNotNull('new_category_id')
                ->where('new_category_id', '!=', 0)
                ->count();

            $datas['index_category_page']  = NewCategory::where('no_index', 0)->where('status', 1)->count();
            $datas['index_caricature_cat'] = CaricatureCategory::where('no_index', 0)->where('status', 1)->count();
            $datas['index_caricature_attire'] = Attire::where('no_index', 0)->where('status', 1)->count();

            $orphanSpecialPage = SpecialPage::where(function ($q) {
                    $q->whereNull('cat_id')
                    ->orWhere('cat_id', 0);
                })
                ->count();

            $orphanKeywordPage = SpecialKeyword::where(function ($q) {
                    $q->whereNull('cat_id')
                    ->orWhere('cat_id', 0);
                })
                ->count();

            $datas['index_orphan_page'] = $orphanSpecialPage + $orphanKeywordPage;
        }

        if ($idAdmin) {

        // $sum1 = PurchaseHistory::whereBetween('created_at', [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()])->sum('net_amount');
        // $sum2 = VideoPurchaseHistory::whereBetween('created_at', [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()])->sum('net_amount');
        // $sum3 = AIPurchaseHistory::whereBetween('created_at', [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()])->sum('net_amount');
        // $sum4 = CaricaturePurchaseHistory::whereBetween('created_at', [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()])->sum('net_amount');
        // $sum5 = TransactionLog::whereBetween('created_at', [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()])->sum('net_amount');

        // return $sum1 + $sum2 + $sum3 + $sum4 + $sum5;

            //------------------------------------------------------------//------------------------------------------------------------//------------------------------------------------------------

            $now = Carbon::now();

            
            $thisMonth = Carbon::now()->copy();
            $lastMonth = Carbon::now()->copy()->subMonth();

            $periods = [
                'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
                'day_before_yesterday' => [Carbon::now()->subDays(2)->startOfDay(), Carbon::now()->subDays(2)->endOfDay()],
                'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
                'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
                'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
                'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
                'total' => [],
            ];
            
            $sum = function ($model, $field, $currency, $range, $filters = []) {
                $query = $model::query()->where('currency_code', $currency);

                if ($range) $query->whereBetween('created_at', $range);

                foreach ($filters as $type => $condition) {

                    switch ($type) {

                        case 'where':
                            foreach ($condition as $col => $val) $query->where($col, $val);
                            break;

                        case 'orWhere':
                            foreach ($condition as $col => $val) $query->orWhere($col, $val);
                            break;

                        case 'whereNull':
                            foreach ($condition as $col) $query->whereNull($col);
                            break;

                        case 'whereNotNull':
                            foreach ($condition as $col) $query->whereNotNull($col);
                            break;

                        case 'whereIn':
                            foreach ($condition as $col => $vals) $query->whereIn($col, $vals);
                            break;

                        case 'whereNotIn':
                            foreach ($condition as $col => $vals) $query->whereNotIn($col, $vals);
                            break;

                        case 'between':
                            foreach ($condition as $col => $vals) $query->whereBetween($col, $vals);
                            break;

                        case 'like':
                            foreach ($condition as $col => $val) $query->where($col, 'LIKE', "%{$val}%");
                            break;

                        case 'notLike':
                            foreach ($condition as $col => $val) $query->where($col, 'NOT LIKE', "%{$val}%");
                            break;

                    }
                }
        
                $total = (int)$query->sum($field);
                $count = (int)$query->count();
                return ['total' => $total, 'count' => $count];

            };

            $planIds = Subscription::where('is_meta', 1)->pluck('id')->toArray();

            // Filters
            $withFbc = ['whereNotNull' => ['fbc']];                               // for templates, caricature, caricature_credit
            $offerFilterWithEMandate = ['whereIn' => ['plan_id' => $planIds], 'where' => ['is_e_mandate' => '1']];
            $offerFilterNoEMandate = ['whereIn' => ['plan_id' => $planIds], 'where' => ['is_e_mandate' => '0']];

            $baseLink = "https://panel.craftyartapp.com/new_template";
            // $models = [
            //     'e_mandate' => ["link" => "$baseLink/transcation_logs", "model" => TransactionLog::class, 'filter' => ['where' => ['is_e_mandate' => '1']]],
            //     'subs' => ["link" => "$baseLink/transcation_logs", "model" => TransactionLog::class, 'filter' => ['where' => ['is_e_mandate' => '0']]],
            //     'templates' => ["link" => "$baseLink/purchases", "model" => PurchaseHistory::class, 'filter' => []],
            //     'caricature' => ["link" => "$baseLink/cari_purchases", "model" => CaricaturePurchaseHistory::class, 'filter' => []],
            //     'caricature_credit' => ["link" => "$baseLink/ai_credit_purchases", "model" => AIPurchaseHistory::class, 'filter' => []],
            //     'video' => ["link" => "$baseLink/video_transcation_logs", "model" => VideoPurchaseHistory::class, 'filter' => []],
            // ];

            $models = [
                'total_sub' => [
                    "link" => "$baseLink/transcation_logs",
                    "model" => TransactionLog::class,
                    "filter" => [],
                    // "ignore" => true
                ],
//                 'e_mandate' => [
//                     "link" => "$baseLink/transcation_logs?type=e_mandate",
//                     "model" => TransactionLog::class,
//                     "filter" => ['where' => ['is_e_mandate' => '1']]
//                 ],
//                 'sales_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=sales_team",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => '0', 'by_sales_team' => 1]]
// //                    "filter" => ['whereNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => '0', 'by_sales_team' => 1], 'whereNotIn' => ['plan_id' => $planIds]]
//                 ],
//                 'seo_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=seo",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => '0', 'by_sales_team' => 0]]
// //                    "filter" => ['whereNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => '0', 'by_sales_team' => 0], 'whereNotIn' => ['plan_id' => $planIds]]
//                 ],
//                 'meta_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=meta",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNotNull' => ['fbc'], 'whereNull' => ['gclid'], 'where' => ['is_e_mandate' => 0]],
//                     // "filter" => ['whereNotNull' => ['fbc'], 'whereNull' => ['gclid'], 'where' => ['is_e_mandate' => 0], 'whereIn' => ['plan_id' => $planIds]]
//                 ],
//                 'meta_page_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=meta_page",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNotNull' => ['url'], 'where' => ['is_e_mandate' => 0], 'like' => ['url' => '/wedding-video-bundle']],
//                     "ignore" => true
//                     // "filter" => ['whereNotNull' => ['fbc'], 'whereNull' => ['gclid'], 'where' => ['is_e_mandate' => 0], 'whereIn' => ['plan_id' => $planIds]]
//                 ],
//                 'google_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=google",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNotNull' => ['gclid'], 'whereNull' => ['fbc'], 'where' => ['is_e_mandate' => 0]]
//                     // "filter" => ['whereNotNull' => ['gclid'], 'whereNull' => ['fbc'], 'where' => ['is_e_mandate' => 0], 'whereIn' => ['plan_id' => $planIds]]
//                 ],
//                 'google_page_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=google_page",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNotNull' => ['url'], 'where' => ['is_e_mandate' => 0], 'like' => ['url' => '/video-bundle']],
//                     "ignore" => true
//                     // "filter" => ['whereNotNull' => ['fbc'], 'whereNull' => ['gclid'], 'where' => ['is_e_mandate' => 0], 'whereIn' => ['plan_id' => $planIds]]
//                 ],
//                 'meta_google_subs' => [
//                     "link" => "$baseLink/transcation_logs?type=meta-google",
//                     "model" => TransactionLog::class,
//                     "filter" => ['whereNotNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => 0]]
//                     // "filter" => ['whereNotNull' => ['fbc', 'gclid'], 'where' => ['is_e_mandate' => 0], 'whereIn' => ['plan_id' => $planIds]]
//                 ],
                'business_support' => [
                    "link" => "$baseLink/business_support",
                    "model" => BusinessSupportPurchaseHistory::class,
                    "filter" => []
                ],
                'templates' => [
                    "link" => "$baseLink/purchases",
                    "model" => PurchaseHistory::class,
                    "filter" => []
                ],
                
                'caricature' => [
                    "link" => "$baseLink/cari_purchases",
                    "model" => CaricaturePurchaseHistory::class,
                    "filter" => []
                ],
                'caricature_credit' => [
                    "link" => "$baseLink/ai_credit_purchases",
                    "model" => AIPurchaseHistory::class,
                    "filter" => []
                ],
                'video' => [
                    "link" => "$baseLink/video_transcation_logs",
                    "model" => VideoPurchaseHistory::class,
                    "filter" => []
                ],
            ];

            $labelTitles = [
                'today' => "Today's",
                'yesterday' => "Yesterday's",
                'day_before_yesterday' => "2 Days Ago's",
                'this_month' => "This Month's",
                'last_month' => "Last Month's",
                'this_year' => "This Year's",
                'last_year' => "Last Year's",
                'total' => "Total",
            ];

            $symbols = ['INR' => 'Rs', 'USD' => '$'];

            // Internal numeric stores
            $raw = [];     // $raw[period][key]['INR'|'USD'] = amount
            $fbc = [];     // $fbc[period][key]['INR'|'USD'] = amount (only for specific keys)
            $offer = [];   // $offer[period]['subs']['INR'|'USD'] = amount

            $revenueData = [];

            foreach ($periods as $label => $range) {

                // Collect raw, fbc, offer for each model & currency
                foreach ($symbols as $currency => $symbol) {
                    foreach ($models as $key => $config) {
                        $model = $config['model'];

                        $normal = $sum($model, 'net_amount', $currency, $range, $config['filter'])['total'];
                        $raw[$label][$key][$currency] = $normal;

                        // FBC breakdown for selected models
                        if (in_array($key, ['templates', 'caricature', 'caricature_credit'], true)) {
                            $fbc[$label][$key][$currency] = $sum($model, 'net_amount', $currency, $range, $withFbc)['total'];
                        }

                        // Offer breakdown for subs

                        if ($key === 'e_mandate') {
                            $offer[$label]['e_mandate'][$currency] = $sum(TransactionLog::class, 'net_amount', $currency, $range, $offerFilterWithEMandate)['total'];
                        }

                        if ($key === 'subs') {
                            $offer[$label]['subs'][$currency] = $sum(TransactionLog::class, 'net_amount', $currency, $range, $offerFilterNoEMandate)['total'];
                        }
                    }
                }

                // Build ordered rows for this period
                $rows = [];

                foreach ($models as $key => $config) {

                    $titleBase = $labelTitles[$label] . ' ' . ucwords(str_replace('_', ' ', $key));

                    // INR row piece
                    $inrNormal = $raw[$label][$key]['INR'] ?? 0.0;
                    $inrText = "Rs {$inrNormal}";

                    if (isset($fbc[$label][$key]['INR'])) {
                        $inrText .= " - ({$fbc[$label][$key]['INR']})";
                    }

                    if ($key === 'e_mandate' && isset($offer[$label][$key]['INR'])) {
                        $inrText .= " - ({$offer[$label][$key]['INR']})";
                    }

                    if ($key === 'subs' && isset($offer[$label][$key]['INR'])) {
                        $inrText .= " - ({$offer[$label][$key]['INR']})";
                    }

                    // USD row piece
                    $usdNormal = $raw[$label][$key]['USD'] ?? 0.0;
                    $usdText = "$ {$usdNormal}";

                    if (isset($fbc[$label][$key]['USD'])) {
                        $usdText .= " - ({$fbc[$label][$key]['USD']})";
                    }

                    if ($key === 'e_mandate' && isset($offer[$label][$key]['USD'])) {
                        $usdText .= " - ({$offer[$label][$key]['USD']})";
                    }

                    if ($key === 'subs' && isset($offer[$label][$key]['USD'])) {
                        $usdText .= " - ({$offer[$label][$key]['USD']})";
                    }

                    $rows[] = [
                        "title" => $titleBase,
                        "inr" => $inrText,
                        "usd" => $usdText,
                        "link" => $config['link'],
                        "ignore" => $config['ignore'] ?? false,
                    ];
                }

                // Totals (numeric)

                $inrTotal = array_sum(
                    array_column(
                        array_filter(
                            $raw[$label],
                            fn ($_, $key) => !isset($models[$key]['ignore']) || $models[$key]['ignore'] !== true,
                            ARRAY_FILTER_USE_BOTH
                        ),
                        'INR'
                    )
                );


                $usdTotal = array_sum(
                    array_column(
                        array_filter(
                            $raw[$label],
                            fn ($_, $key) => !isset($models[$key]['ignore']) || $models[$key]['ignore'] !== true,
                            ARRAY_FILTER_USE_BOTH
                        ),
                        'USD'
                    )
                );
                // $inrTotal = array_sum(array_column($raw[$label], 'INR'));
                // $usdTotal = array_sum(array_column($raw[$label], 'USD'));
                $final = $inrTotal + $usdTotal; // NOTE: no FX conversion, just a raw add

                // "Total" row (no link)
                $rows[] = [
                    "title" => $labelTitles[$label] . " Total",
                    "inr" => "Rs {$inrTotal}",
                    "usd" => "$ {$usdTotal}",
                ];

                // "Final" row (no link)
                $rows[] = [
                    "title" => $labelTitles[$label] . " Final",
                    "final" => "Rs {$final}",
                ];

                $revenueData[$label] = $rows;
            }

            $datas['revenue_data'] = $revenueData;

            $razorpaySum = function($date) use ($sum) {
                $models = [
                    [PurchaseHistory::class, ['payment_method' => 'Razorpay']],
                    [VideoPurchaseHistory::class, ['payment_method' => 'Razorpay']],
                    [AIPurchaseHistory::class, ['payment_method' => 'Razorpay']],
                    [CaricaturePurchaseHistory::class, ['payment_method' => 'Razorpay']],
                    [TransactionLog::class, ['payment_method' => 'Razorpay', 'is_e_mandate' => '0']],
                ];

                $finalTotal = 0;
                $finalCount = 0;

                foreach ($models as [$model, $filter]) {
                    $result = $sum($model, 'net_amount', 'INR', $date, ['where' => $filter]);

                    $finalTotal += $result['total'];
                    $finalCount += $result['count'];
                }

                return "$finalTotal ($finalCount)";
            };

            $extra1 = [
                [
                    "title" => 'Total Razorpay Inr',
                    "value" => "Rs " . $razorpaySum([Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                ],
                [
                    "title" => 'Yesterday Razorpay Inr',
                    "value" => "Rs " . $razorpaySum([Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
                ],
                [
                    "title" => "Today's EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?expired_at=" . Carbon::today()->format('Y-m-d')
                ],
                [
                    "title" => "Tomorrow's EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [Carbon::tomorrow()->startOfDay(), Carbon::tomorrow()->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?expired_at=" . Carbon::tomorrow()->format('Y-m-d')
                ],
                [
                    "title" => "After 2 Day's EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [Carbon::now()->addDays(2)->startOfDay(), Carbon::now()->addDays(2)->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?expired_at=" . Carbon::now()->addDays(2)->format('Y-m-d')
                ],
                [
                    "title" => "After 3 Day's EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [Carbon::now()->addDays(3)->startOfDay(), Carbon::now()->addDays(3)->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?expired_at=" . Carbon::now()->addDays(3)->format('Y-m-d')
                ],
                [
                    "title" => "Today's Pending EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'pending')->whereBetween('expired_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?status=pending&expired_at=" . Carbon::today()->format('Y-m-d')
                ],
                [
                    "title" => "Today's Cancelled EMandate",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_status', 'cancelled')->whereBetween('expired_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count(),
                    "link" => "$baseLink/upcoming_mandates?status=cancelled&expired_at=" . Carbon::today()->format('Y-m-d')
                ],
                [
                    "title" => "Active Subs",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->count()
                ],
                [
                    "title" => "Paused Subs",
                    "value" => TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'paused')->count()
                ],
    //            [
    //                "title" => 'Editor Visits',
    //                "value" => EditorVisit::whereDate('created_at', Carbon::today())->count()
    //            ],
                [
                    "title" => 'Drafts',
                    "value" => Draft::whereDate('created_at', Carbon::today())->count()
                ],
                [
                    "title" => 'Exports',
                    "value" => ExportTable::where('watermark', 0)->whereDate('created_at', Carbon::today())->count()
                ],
                [
                    "title" => 'Watermark Exports',
                    "value" => ExportTable::where('watermark', 1)->whereDate('created_at', Carbon::today())->count(),
                    "link" => "$baseLink/free_exports"
                ],
            ];

            $extra2 = [
                [
                    "title" => 'AI Credit Transcation',
                    "link" => "$baseLink/credit_transaction_logs",
                    "target" => "_blank"
                ],
                [
                    "title" => 'Refresh Transcation',
                    "link" => "$baseLink/refreshTanscation",
                    "target" => "_blank"
                ],
                [
                    "title" => 'Get Users Excel',
                    "link" => "$baseLink/export-users",
                    "target" => "_blank"
                ],
                [
                    "title" => 'Get Purchase Excel',
                    "link" => "$baseLink/export-datas",
                    "target" => "_blank"
                ],
                [
                    "title" => 'Get Subs Excel',
                    "link" => "$baseLink/export-sub-datas",
                    "target" => "_blank"
                ],
                [
                    "title" => 'Get Meta Subs Excel',
                    "link" => "$baseLink/export-meta-sub-datas",
                    "target" => "_blank"
                ],
            ];

            $datas['extras'] = [$extra1, $extra2];

            $datas['today_razorpay_inr'] = "Rs " . $razorpaySum([Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
            $datas['yesterday_razorpay_inr'] = "Rs " . $razorpaySum([Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()]);

            //            $visits = EditorVisit::whereDate('created_at', Carbon::today())->count();
            $drafts = Draft::whereDate('created_at', Carbon::today())->count();
            $exports = ExportTable::whereDate('created_at', Carbon::today())->count();

            $today_e_mandate = TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->where('is_trial', 1)->whereBetween('expired_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count();
            $tomorrow_e_mandate = TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->where('is_trial', 1)->whereBetween('expired_at', [Carbon::tomorrow()->startOfDay(), Carbon::tomorrow()->endOfDay()])->count();
            $active_subs = TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->where('is_trial', 1)->count();
            $paused_subs = TransactionLog::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'paused')->where('is_trial', 1)->count();

            $datas['editor_history'] = "Today's EMandate: $today_e_mandate, Tomorrow's EMandate: $tomorrow_e_mandate, Active Subs: $active_subs, Paused Subs: $paused_subs, Drafts: $drafts, Exports: $exports";
            // $datas['editor_history'] = "Drafts: $drafts, Exports: $exports";

        }

        $datas['cache'] = env('CACHE_VER', '1');

        return view('dashboard')->with('datas', $datas);
    }

    public function refreshTanscation(Request $request) {
        
    }

    public function update_cache_ver(Request $request)
    {
        $cache_ver = $request->input('cache_ver');

        $this->setEnv('CACHE_VER', $cache_ver);

        return response()->json([
            'success' => 'Cache update successfully.'
        ]);
    }

    private function setEnv($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . env($key, '1'),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}

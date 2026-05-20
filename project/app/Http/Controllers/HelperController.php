<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\CaricatureController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\CaricatureCategory;
use App\Models\PendingTask;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoVirtualCategory;
use App\Models\AppCategory;
use App\Models\Category;
use App\Models\StickerCategory;
use App\Models\StickerMode;
use App\Models\BgCategory;
use App\Models\Design;
use App\Models\UserData;
use App\Models\InAppType;
use App\Models\FontFamily;
use App\Models\NewCategory;
use App\Models\PurchaseHistory;
use App\Models\TransactionLog;
use App\Models\TemplateRate;
use App\Models\NewSearchTag;
use App\Models\VirtualCategory;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HelperController extends Controller
{

    public static string $webPageUrl = "https://www.craftyartapp.com/";
    public static string $frontendUrl = "https://www.craftyartapp.com/";
    public static string $videoFrontendUrl = "https://beta.myvideoinvites.com/";
    public static array $oldMediaUrl = ["https://panel.craftyartapp.com/templates/", "https://storage.googleapis.com/media.craftyartapp.com/"];
    public static string $mediaUrl = "https://media.craftyartapp.com/";


    public static $USD_AMOUNT = 4.99;
    public static $USD_EXTRA_PAGE_AMOUNT = 2;
    public static $USD_MAX_AMOUNT = 8.99;
    public static $INR_AMOUNT = 249;
    public static $INR_EXTRA_PAGE_AMOUNT = 150;
    public static $INR_MAX_AMOUNT = 999;

    public static $VIDEO_USD_AMOUNT = 4.99;
    public static $VIDEO_USD_EXTRA_PAGE_AMOUNT = 1;
    public static $VIDEO_USD_MAX_AMOUNT = 9.99;
    public static $VIDEO_INR_AMOUNT = 249;
    public static $VIDEO_INR_EXTRA_PAGE_AMOUNT = 100;
    public static $VIDEO_INR_MAX_AMOUNT = 749;

    public static function getItemData($catRow, $item, $thumbArray, $swap = true)
    {
        if ($swap) {
            $thumbArray = HelperController::swapThumbs($thumbArray, $item->default_thumb_pos);
        }

        foreach ($thumbArray as $key => $value) {
            $thumbArray[$key] = HelperController::$mediaUrl . $value;
        }

        $size = sizeof($thumbArray);
        $payment = HelperController::getTemplateRates($size, $item->is_premium == 1, $item->is_freemium == 1, $item->animation == 1, $item->editor_choice == 1);

        $newTags = [];

        if (isset($item->new_related_tags)) {
            $new_related_tags = implode(",", json_decode($item->new_related_tags, true));
            foreach (explode(',', $new_related_tags) as $value) {
                $dataRes = NewSearchTag::where('id', $value)->first();
                if ($dataRes) {
                    $newTags[] = $dataRes->name;
                }
            }
        }

        $videoUrl = $item->video_thumb != null ? HelperController::$mediaUrl . $item->video_thumb : null;

        $data = array(
            'category_id' => $catRow->id,
            'category_id_name' => $catRow->id_name,
            'category_name' => $catRow->category_name,
            'category_size' => $catRow->size,
            'template_id' => $item->id,
            'string_id' => $item->string_id,
            'id_name' => $item->id_name,
            'template_name' => $item->post_name,
            'template_thumb' => HelperController::$mediaUrl . $item->post_thumb,
            'thumbArray' => $thumbArray,
            'video' => $videoUrl,
            'width' => $item->width,
            'height' => $item->height,
            'pages' => sizeof($thumbArray),
            'tags' => json_decode($item->related_tags),
            'related_tags' => json_decode($item->related_tags),
            'new_tags' => $newTags,
            'latest' => false,
            'is_premium' => true /*$item->is_premium == 1*/ ,
            'auto_create' => $item->auto_create == 1 ? true : false,
            'created_at' => $item->created_at,
            'payment' => $payment,
            'inrVal' => $payment['inrVal'],
            'usdVal' => $payment['usdVal'],
            'inrAmount' => $payment['inrAmount'],
            'usdAmount' => $payment['usdAmount'],
        );

        return $data;
    }

    public static function getTemplateRates($size, Design|stdClass $item, $extraDisc = 0): array
    {

        $data = match (true) {
            is_array($item->caricature_ids) => $item->caricature_ids,
            is_string($item->caricature_ids) => json_decode($item->caricature_ids, true) ?? [],
            default => [],
        };

        $isPremium = $item->is_premium == 1;
        $isFreemium = $item->is_freemium == 1;
        $hasAnimation = $item->animation == 1;
        $isEditorChoice = $item->editor_choice == 1;
        $totalCaricatures = count($data);

        if ($isPremium) {
            $value = TemplateRate::getRates("premium_template");
        } else if ($isFreemium) {
            $value = TemplateRate::getRates("freemium_template");
        } else {
            $value = TemplateRate::getRates("remove_watermark");
        }

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        if ($hasAnimation) {
            $inrAmount = $inrAmount + $value->inr->animation;
            $usdAmount = $usdAmount + $value->usd->animation;
        }

        if ($isEditorChoice) {
            $inrAmount = $inrAmount + $value->inr->editor_choice;
            $usdAmount = $usdAmount + $value->usd->editor_choice;
        }

        $inrAmount = $inrAmount + ($value->inr->caricature * $totalCaricatures);
        $usdAmount = $usdAmount + ($value->usd->caricature * $totalCaricatures);

        if ($extraDisc > 0) {
            $inrDiscount = round($inrAmount * $extraDisc / 100);
            $usdDiscount = $usdAmount * $extraDisc / 100;

            $inrAmount = $inrAmount - $inrDiscount;
            $usdAmount = $usdAmount - $usdDiscount;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }


    // public static function getTemplateRates($size, $isPremium = true)
    // {
    //     $res = TemplateRate::find(1);
    //     if ($size == 0) {
    //         $size = 1;
    //     }

    //     $extraPage = $size - 1;

    //     if ($isPremium) {
    //         $inrAmount = $res->tmp_base_inr + ($res->tmp_page_inr * $extraPage);
    //         $usdAmount = $res->tmp_base_usd + ($res->tmp_page_usd * $extraPage);
    //         if ($inrAmount > $res->tmp_max_inr) {
    //             $inrAmount = $res->tmp_max_inr;
    //         }
    //         if ($usdAmount > $res->tmp_max_usd) {
    //             $usdAmount = $res->tmp_max_usd;
    //         }
    //     } else {
    //         $inrAmount = $res->free_tmp_base_inr + ($res->free_tmp_page_inr * $extraPage);
    //         $usdAmount = $res->free_tmp_base_usd + ($res->free_tmp_page_usd * $extraPage);
    //         if ($inrAmount > $res->free_tmp_max_inr) {
    //             $inrAmount = $res->free_tmp_max_inr;
    //         }
    //         if ($usdAmount > $res->free_tmp_max_usd) {
    //             $usdAmount = $res->free_tmp_max_usd;
    //         }
    //     }

    //     $payment['inrVal'] = $inrAmount;
    //     $payment['usdVal'] = $usdAmount;
    //     $payment['inrAmount'] = '₹' . $inrAmount;
    //     $payment['usdAmount'] = '$' . $usdAmount;

    //     return $payment;
    // }

    public static function getVideoRates($size): array
    {
        $value = TemplateRate::getRates("mobile_video");

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;
        $inrAmount = $value['inr']['base_price'] + ($value['inr']['page_price'] * $extraPage);
        $usdAmount = $value['usd']['base_price'] + ($value['usd']['page_price'] * $extraPage);
        $inrAmount = min($inrAmount, $value['inr']['max_price']);
        $usdAmount = min($usdAmount, $value['usd']['max_price']);

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function getCaricatureRates($size): array
    {
        $value = TemplateRate::getRates("caricature");

        if ($size == 0) {
            $size = 1;
        }

        $value = json_decode(json_encode($value), true);

        $extraPage = $size - 1;
        $inrAmount = $value['inr']['base_price'] + ($value['inr']['page_price'] * $extraPage);
        $usdAmount = $value['usd']['base_price'] + ($value['usd']['page_price'] * $extraPage);
        $inrAmount = min($inrAmount, $value['inr']['max_price']);
        $usdAmount = min($usdAmount, $value['usd']['max_price']);

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    // public static function getVideoRates($size)
    // {
    //     $res = TemplateRate::find(1);
    //     if ($size == 0) {
    //         $size = 1;
    //     }

    //     if ($size > 2) {
    //         $extraPage = $size - 2;
    //         $inrAmount = min($res->vid_max_inr, $res->vid_base_inr + ($res->vid_page_inr * $extraPage));
    //         $usdAmount = min($res->vid_max_usd, $res->vid_base_usd + ($res->vid_page_usd * $extraPage));
    //     } else {
    //         $inrAmount = $res->vid_base_inr;
    //         $usdAmount = $res->vid_base_usd;
    //     }

    //     $payment['inrVal'] = $inrAmount;
    //     $payment['usdVal'] = $usdAmount;
    //     $payment['inrAmount'] = '₹' . $inrAmount;
    //     $payment['usdAmount'] = '$' . $usdAmount;

    //     return $payment;
    // }

    public static function calculateTemplateRate($size, $currency, $isPremium = true)
    {
        $payment = HelperController::getTemplateRates($size, $isPremium);
        return $currency == 'INR' ? $payment['inrVal'] : $payment['usdVal'];
    }

    public static function calculateVideoRate($size, $currency)
    {
        $payment = HelperController::getVideoRates($size);
        return $currency == 'INR' ? $payment['inrVal'] : $payment['usdVal'];
    }

    public static function templateSize($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public static function getFontFamilyName($id)
    {
        $res = FontFamily::find($id);
        if ($res != null) {
            return $res->fontFamily;
        }
        return "";
    }

    public static function getCatName($id)
    {
        // dd($id);
        $res = Category::find($id);
        // dd($res);
        if ($res != null) {
            // dd($res->category_name);
            return $res->category_name;
        }
        return "NA";
    }

    public static function getNewCatName($id)
    {
        $res = NewCategory::find($id);
        if ($res != null) {
            return $res->category_name;
        }
        return "NA";
    }

    public static function getNewCatNames($id, $isJoin = false)
    {


        $res = NewCategory::whereIn('id', $id)->pluck('category_name');
        if ($isJoin == true && $res != null) {
            return $res->implode(',');
        } else if ($res != null) {
            return $res->implode(',');
        }
        return "NA";
    }

    /**
     * @param  array<int|string>|int|string|null  $id
     */
    public static function getVideoMainCatNames($id): string
    {
        if ($id === null || $id === '') {
            return 'NA';
        }
        $ids = is_array($id) ? $id : [$id];
        $ids = array_filter($ids, static fn($v) => $v !== '' && $v !== null);
        if ($ids === []) {
            return 'NA';
        }
        $res = VideoCategory::whereIn('id', $ids)->pluck('category_name');

        return $res->isNotEmpty() ? $res->implode(',') : 'NA';
    }

    public static function getParentNewCatName($id, $isFromNewCategory = false)
    {

        $res = NewCategory::with('parentCategory')->where('id', $id)->first();
        if ($res) {
            if ($isFromNewCategory && $id != 0) {
                return $res->category_name;
            }
            if (isset($res->parentCategory->category_name)) {
                return $res->parentCategory->category_name;
            }
        }
        return "NA";
    }

    public static function getParentVideoCatName($id, $isFromCategoryegory = false)
    {
        return self::getParentCategoryName($id, $isFromCategoryegory);
    }

    public static function getParentCategoryName($id, $isFromCategoryegory = false)
    {
        $res = VideoCategory::with('parentCategory')->where('id', $id)->first();
        if ($res) {
            if ($isFromCategoryegory && $id != 0) {
                return $res->category_name;
            }
            if (isset($res->parentCategory->category_name)) {
                return $res->parentCategory->category_name;
            }
        }
        return "NA";
    }

    public static function getCaricatureCatName($id, $isJoin = false)
    {
        $res = CaricatureCategory::where('id', $id)->pluck('category_name')->first();
        if ($res != null) {
            return $res;
        }
        return "NA";
    }

    public static function getParentCaricatureCatName($id, $isFromNewCategory = false)
    {

        $res = CaricatureCategory::with('parentCategory')->where('id', $id)->first();
        if ($res) {
            if ($isFromNewCategory && $id != 0) {
                return $res->category_name;
            }
            if (isset($res->parentCategory->category_name)) {
                return $res->parentCategory->category_name;
            }
        }
        return "NA";
    }

    public static function checkStringFormat($string, $convert = false)
    {

        if (preg_match("/^\['(.+)'\]$/", $string, $matches)) {
            if ($convert) {
                return $matches[1];
            }
            return $string;
        }
        return $string;
    }

    public static function getVCatName($id)
    {
        $res = VideoCategory::find($id);
        if ($res != null) {
            return $res->category_name;
        }
        return "";
    }

    public static function getStickerCatName($id)
    {
        $res = StickerCategory::find($id);
        if ($res != null) {
            return $res->stk_category_name;
        }
        return "";
    }

    public static function getStickerMode($value)
    {
        $res = StickerMode::where('value', $value)->first();
        return $res->type;
    }

    public static function getBgCatName($id)
    {
        $res = BgCategory::find($id);
        return $res->bg_category_name;
    }

    public static function getUserName($id)
    {
        $res = DB::table('user_data')->where("uid", $id)->first();
        if ($res != null) {
            return $res->name;
        } else {
            return 'NONE';
        }
    }

    public static function getAppName($id)
    {
        $res = AppCategory::find($id);
        if (isset($res->app_name) && $res->app_name != null) {
            return $res->app_name;
        } else {
            return 'NONE';
        }
    }

    public static function getInAppType($type)
    {
        $res = InAppType::where('type', $type)->first();
        return $res->value;
    }

    public static function checkPlan($features, $rowFeatureId)
    {
        $isFlag = false;
        foreach ($features as $key => $feature) {
            if ($feature->id == $rowFeatureId) {
                $isFlag = true;
            }
        }
        if ($isFlag) {
            return true;
        }
        return false;
    }

    public static function stringContain($mainString, $containString)
    {
        if ($mainString == null) {
            return false;
        }

        $jsonArray = json_decode($mainString, true);

        if (is_array($jsonArray)) {
            foreach ($jsonArray as $json) {
                if ($containString == $json) {
                    return true;
                }
            }
        }
        return false;
        // if (str_contains($mainString, $containString)) {
        //     return true;
        // }
        // return false;
    }

    public static function randomNameGenerator()
    {
        return \Carbon\Carbon::now()->timestamp;
    }

    public static function getOrientations(): array
    {
        return [
            "portrait",
            "landscape",
            "square"
        ];
    }

    public static function getPrice(): array
    {
        return [
            "Free",
            "Premium",
        ];
    }

    public static function getAnimation(): array
    {
        return [
            "true" => 1,
            "false" => 0,
        ];
    }


    public static function filterArrayOrder($selectedValuesString, $mainArray, $column, $tg = false): ?array
    {
        if (is_string($selectedValuesString)) {
            $selectionArray = json_decode($selectedValuesString);
            $themesMap = [];
            foreach ($mainArray as $theme) {
                $themesMap[$theme->{$column}] = $theme;
            }
            $reorderedThemes = [];
            if (is_array($selectionArray)) {
                foreach ($selectionArray as $themeName) {
                    if (isset($themesMap[$themeName])) {
                        $reorderedThemes[] = $themesMap[$themeName];
                    }
                }
                foreach ($mainArray as $theme) {
                    if (!in_array($theme->{$column}, $selectionArray)) {
                        $reorderedThemes[] = $theme;
                    }
                }
            } else {
                $reorderedThemes = $mainArray;
            }
            return $reorderedThemes;
        }
        return null;
    }

    public static function redirectToLogin()
    {
        return view('home');
    }

    public static function dateRange($startDate, $endDate)
    {
        if ($startDate != '') {
            return $startDate . ' - ' . $endDate;
        } else {
            return "None";
        }
    }

    public static function isBanner($isBanner)
    {
        if ($isBanner == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function canCancle($canCancle)
    {
        if ($canCancle == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function isPremium($isPremium)
    {
        if ($isPremium == '1') {
            return "TRUE";
        } else {
            return "FALSE";
        }
    }

    public static function checkStatus($status)
    {
        if ($status == '1') {
            return "LIVE";
        } else {
            return "NOT LIVE";
        }
    }

    public static function checkSubsStatus($status)
    {
        if ($status == '1') {
            return "Active";
        } else {
            return "Expired";
        }
    }

    public static function getSubsColor($status)
    {
        if ($status == '1') {
            return "#2EC4B6";
        } else {
            return "#FF0000";
        }
    }

    public static function getMailOrNumber($user)
    {
        if ($user->email == '' || $user->email == null) {
            return $user->country_code . ' ' . $user->number;
        } else {
            return $user->email;
        }
    }


    public static function getUserClass($userId)
    {
        return UserData::where('uid', $userId)->first();
    }

    public static function getOfferWithPrice($actual_price, $price)
    {

        $returnString = "(" . $actual_price . ") (" . $price . ") ";

        $offer_msg = "No offer";
        try {
            if ($actual_price != $price) {
                $disc = (($actual_price - $price) / $actual_price) * 100;
                $discount = (int) ($disc);
                $offer_msg = "(" . $discount . "% off)";
            }
        } catch (\Exception $e) {
            $offer_msg = "No offer";
        }

        return $returnString . $offer_msg;
    }

    public static function checkRequestFields($request, $fieldArray)
    {
        $missingArray = '';
        foreach ($fieldArray as $field) {
            if (!$request->has($field)) {
                if ($missingArray == '') {
                    $missingArray = $field;
                } else {
                    $missingArray = $missingArray . ', ' . $field;
                }
            }
        }

        $response['success'] = 1;

        if ($missingArray != '') {
            $response['success'] = 0;
            $response['message'] = 'Missing parameters';
            $response['params'] = $missingArray;
        }

        return $response;
    }



    public static function getTemplateName($tempId)
    {
        $design = Design::where('id', $tempId)->first();
        return ($design) ? $design->post_name : '';
    }

    public static function getVideoTemplateName($tempId)
    {
        $videoTemplate = VideoTemplate::where('id', $tempId)->first();
        return ($videoTemplate) ? $videoTemplate->video_name : '';
    }

    public static function getAllActiveSubscribers($request)
    {
        $currentDate = Carbon::now();
        $currentDate = now();
        $temp_data_count = DB::table('user_data')
            ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        return $temp_data_count;
    }

    public static function getAllExpiredSubscribers($request)
    {
        $currentDate = Carbon::now();
        $currentDate = now();
        $temp_data_count = DB::table('user_data')
            ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        return $temp_data_count;
    }

    public static function getAllUpCommingExpiredSubscribers($request)
    {
        $temp_data = [];
        $temp_data_count = 0;
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);
        $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();
        return $temp_data_count;
    }

    public static function getParentCatName($parentCategoryId, $model, $fieldName = "")
    {
        $parentData = $model->where('id', $parentCategoryId)->first();
        if (!$parentData) {
            return '';
        }
        if (!empty($fieldName) && isset($parentData->$fieldName)) {
            return $parentData->$fieldName;
        }

        return isset($parentData->category_name) ? $parentData->category_name : '';
    }

    public static function generateStars($rate)
    {
        $fullStar = '<span class="fa fa-star checked"></span>';
        $halfStar = '<span class="fa fa-star-half-alt checked"></span>';
        $emptyStar = '<span class="fa fa-star"></span>';
        $ratingHtml = '';
        $wholeStars = floor($rate);
        $hasHalfStar = ($rate - $wholeStars) >= 0.5;
        for ($i = 0; $i < $wholeStars; $i++) {
            $ratingHtml .= $fullStar;
        }
        if ($hasHalfStar) {
            $ratingHtml .= $halfStar;
        }

        for ($i = $wholeStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
            $ratingHtml .= $emptyStar;
        }
        return $ratingHtml;
    }

    public static function getUserSubscriptionCount($userID)
    {
        $countSubscription = TransactionLog::where('user_id', $userID)->count();
        return $countSubscription;
    }

    public static function getUserTemplateCount($userID)
    {
        $countTemplate = PurchaseHistory::where('user_id', $userID)->count();
        return $countTemplate;
    }

    public static function getUserVideoCount($userID)
    {
        $countTemplate = VideoPurchaseHistory::where('user_id', $userID)->count();
        return $countTemplate;
    }

    public static function getPurchaseTemplateCount($product_id)
    {
        return PurchaseHistory::where('product_id', $product_id)->count();
    }

    public static function getVPurchaseTemplateCount($product_id)
    {
        return VideoPurchaseHistory::where('product_id', $product_id)->count();
    }


    public static function swapThumbs($thumbArray, $j)
    {
        if ($j < 0 || $j >= count($thumbArray)) {
            return $thumbArray;
        }

        $element = $thumbArray[$j];
        unset($thumbArray[$j]);

        $thumbArray = array_values($thumbArray);

        array_unshift($thumbArray, $element);

        return $thumbArray;
    }


    public static function processCTA($request)
    {
        $cta = [];
        $ctaDatas = $request->input('cta_data');
        if ($ctaDatas) {
            foreach ($ctaDatas as $ctaDataJson) {
                $ctaData = json_decode(base64_decode($ctaDataJson), true);
                foreach ($ctaData as $key => $value) {
                    $ctavar = [];
                    $ctavar = $value;
                    if ($key == "cta_convert" || $key == "cta_hero" || $key == "cta_process") {
                        $ctavar['image']['src'] = HelperController::processBase64Image($value['image']['src']);
                    } else if ($key == "cta_ads") {
                        $ctavar['image']['src'] = HelperController::processBase64Image($value['image']['src']);
                        $ctavar['button']['src'] = HelperController::processBase64Image($value['button']['src']);
                    } else if ($key == "cta_scrollable") {
                        if (isset($value['images']) && is_array($value['images'])) {
                            $processedSteps = array_map(function ($step) {
                                $step['src'] = HelperController::processBase64Image($step['src']);
                                return $step;
                            }, $value['images']);
                            $ctavar['images'] = $processedSteps;
                        }
                    } else if ($key == "cta_how_to_make") {
                        if (isset($value['stepsection']) && is_array($value['stepsection'])) {
                            $processedSteps = array_map(function ($step) {
                                $step['image']['src'] = HelperController::processBase64Image($step['image']['src']);
                                return $step;
                            }, $value['stepsection']);
                            $ctavar['stepsection'] = $processedSteps;
                        }
                    }
                    $cta[$key] = $ctavar;
                }
            }
        }
        return $cta;
    }

    public static function processBase64Image($base64String, $directory = 'uploadedFiles/cta_images/')
    {
        if (preg_match('/^data:image\/([a-zA-Z0-9]+);base64,/', $base64String, $matches)) {
            $extension = $matches[1];
            $imageData = substr($base64String, strpos($base64String, ',') + 1);
            $imageData = base64_decode($imageData);
            $imageName = Str::random(40) . '.' . $extension;
            $imagePath = $directory . $imageName;
            StorageUtils::put($imagePath, $imageData);
            return $imagePath;
        }
        return $base64String;
    }

    public static function generatePublicUrl($path): string
    {
        //        return HelperController::$mediaUrl. $path;
        if (str_starts_with($path, "https://assets.craftyart.in") || str_starts_with($path, config('filesystems.storage_url')) || str_starts_with($path, HelperController::$mediaUrl) || str_contains($path, "googleusercontent")) {
            return $path;
        }
        $path = str_replace(HelperController::$oldMediaUrl, '', $path);
        $clean = ltrim($path, '/');
        if (Storage::disk('public')->exists($clean)) {
            $base = config('filesystems.storage_url');
            if (is_string($base) && $base !== '') {
                return rtrim($base, '/') . '/' . $clean;
            }

            return Storage::disk('public')->url($clean);
        }
        if (StorageUtils::exists($path)) {
            return config('filesystems.storage_url') . $path;
            //            return HelperController::$mediaUrl. $path;
        }
        //        $oldPath = "https://assets.craftyart.in/" . $path;
//        $imageContent = file_get_contents($oldPath);
//        if ($imageContent) {
//            return $oldPath;
//        }
        return $path;
    }


    public static function generateFolderID($id, $length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function checkCategoryAvail($id, $categoryName, $idName, $parentCatID = 0): bool
    {
        $data = Category::where('id', "!=", $id)->where("category_name", $categoryName)->first();
        $data2 = Category::where('id', "!=", $id)->where("id_name", $idName)->first();
        $data3 = NewCategory::where('id', "!=", $id)->where("category_name", $categoryName)->where('parent_category_id', $parentCatID)->first();
        $data4 = NewCategory::where('id', "!=", $id)->where("id_name", $idName)->where('parent_category_id', $parentCatID)->first();
        $data5 = VirtualCategory::where('id', "!=", $id)->where("category_name", $categoryName)->first();
        $data6 = VirtualCategory::where('id', "!=", $id)->where("id_name", $idName)->first();
        if ($data || $data2 || $data3 || $data4 || $data5 || $data6) {
            return true;
        }
        return false;
    }

    public static function checkCaricatureCategoryAvail($id, $categoryName, $idName, $parentCatID = 0): bool
    {
        $data = CaricatureCategory::where('id', "!=", $id)->where("category_name", $categoryName)->where('parent_category_id', $parentCatID)->first();
        $data2 = CaricatureCategory::where('id', "!=", $id)->where("id_name", $idName)->where('parent_category_id', $parentCatID)->first();
        if ($data || $data2) {
            return true;
        }
        return false;
    }

    public static function isCategoryInPending($idName): bool
    {
        $data = PendingTask::where('id_name', $idName)->where('page_type', 1)->where('action', 'add')->where('status', 0)->first();
        if ($data) {
            return true;
        }
        return false;
    }

    public static function applyFilters($query, $filters, $values)
    {
        if ($values !== '') {
            foreach ($filters as $column) {
                if (strpos($column, '.') !== false) {
                    // Split the column to get the relationship and the column name
                    [$relation, $relationColumn] = explode('.', $column);

                    // Apply the filter to the relation
                    $query->orWhereHas($relation, function ($subQuery) use ($relationColumn, $values) {
                        $subQuery->where($relationColumn, 'like', "%$values%");
                    });
                } else {
                    // Apply the filter to the main query
                    $query->orWhere($column, 'like', "%$values%");
                }
            }
        }
        return $query;
    }

    public static function getUserInfo($review): array
    {
        $url = self::$mediaUrl;
        $user = [];
        if ($review->user_id == null) {
            $user['id'] = 'anonymous';
            $user['name'] = $review->name;
            $user['email'] = $review->email;
            $user['profile_photo'] = ContentManager::getStorageLink($review->photo_uri);
        } else {
            $user['id'] = $review->user->uid;
            $user['name'] = $review->user->name;
            $user['email'] = $review->user->email;
            if (str_contains($review->user->photo_uri, 'uploadedFiles/')) {
                $profile_photo = $url . $review->user->photo_uri;
            }
            $user['profile_photo'] = $profile_photo;
        }

        return [
            'id' => $review->id,
            'feedback' => $review->feedback,
            'rate' => $review->rate,
            'date' => $review->created_at->format('Y/m/d'),
            'user' => $user,
        ];
    }

    public static function getReviewUserPic($review)
    {
        $url = self::$mediaUrl;
        if ($review->user_id == null) {
            return ContentManager::getStorageLink($review->photo_uri);
        } else {
            $profile_photo = $review->user->photo_uri;
            if (str_contains($review->user->photo_uri, 'uploadedFiles/')) {
                $profile_photo = $url . $review->user->photo_uri;
            }
            $user['profile_photo'] = $profile_photo;
            return $profile_photo;
        }
    }

    /**
     * Base URL for the video invitations product (separate from Crafty Art templates frontend).
     */
    public static function getVideoFrontendBaseUrl(): string
    {
        $url = trim((string) config('app.video_frontend_url', 'https://beta.myvideoinvites.com/'));
        if ($url === '') {
            $url = 'https://beta.myvideoinvites.com/';
        }

        return rtrim($url, '/') . '/';
    }

    /**
     * Build public URL on the video site for p_type 6 (video category), 7 (video virtual category), 8 (video template).
     * Uses VIDEO_FRONTEND_URL — not www.craftyartapp.com.
     */
    public static function getVideoFrontendPageUrlById(int $type, $id): string
    {
        $base = self::getVideoFrontendBaseUrl();

        $urlFromCanonical = static function (?string $canonical) use ($base): ?string {
            if (empty($canonical) || !str_starts_with($canonical, 'http')) {
                return null;
            }
            $path = parse_url($canonical, PHP_URL_PATH);
            if ($path === null || $path === '' || $path === '/') {
                return null;
            }
            $query = parse_url($canonical, PHP_URL_QUERY);
            $built = rtrim($base, '/') . $path;
            if (!empty($query)) {
                $built .= '?' . $query;
            }

            return $built;
        };

        if ($type === 6) {
            $data = VideoCategory::query()
                ->where(function ($q) use ($id) {
                    $q->where('string_id', $id);
                    if ($id !== '' && $id !== null && ctype_digit((string) $id)) {
                        $q->orWhere('id', (int) $id);
                    }
                })
                ->first();
            if (!$data) {
                return $base;
            }
            $fromCanon = $urlFromCanonical($data->canonical_link ?? null);
            if ($fromCanon !== null) {
                return $fromCanon;
            }
            $path = $data->slug;

            return rtrim($base, '/') . '/' . ltrim((string) $path, '/');
        }

        if ($type === 7) {
            $data = VideoVirtualCategory::query()
                ->where(function ($q) use ($id) {
                    $q->where('string_id', $id);
                    if ($id !== '' && $id !== null && ctype_digit((string) $id)) {
                        $q->orWhere('id', (int) $id);
                    }
                })
                ->first();
            if (!$data) {
                return $base;
            }
            $fromCanon = $urlFromCanonical($data->canonical_link ?? null);
            if ($fromCanon !== null) {
                return $fromCanon;
            }
            $path = $data->slug;

            return rtrim($base, '/') . '/' . ltrim((string) $path, '/');
        }

        if ($type === 8) {
            // Same path pattern as Crafty product pages: /templates/p/{slug}
            $data = VideoTemplate::query()
                ->where(function ($q) use ($id) {
                    $q->where('string_id', $id);
                    if ($id !== '' && $id !== null && ctype_digit((string) $id)) {
                        $q->orWhere('id', (int) $id);
                    }
                })
                ->first();
            if (!$data) {
                return $base;
            }
            $slug = $data->slug;
            if ($slug === null || $slug === '') {
                return $base;
            }
            $slug = ltrim((string) $slug, '/');

            if (!empty($data->canonical_link) && str_starts_with($data->canonical_link, 'http')) {
                $path = parse_url($data->canonical_link, PHP_URL_PATH);
                if (is_string($path) && str_starts_with($path, '/templates/p/')) {
                    $fromCanon = $urlFromCanonical($data->canonical_link);
                    if ($fromCanon !== null) {
                        return $fromCanon;
                    }
                }
            }

            return rtrim($base, '/') . '/templates/p/' . $slug;
        }

        return $base;
    }

    public static function getFrontendPageUrlById($type, $id): string
    {
        $type = (int) $type;
        if (in_array($type, [6, 7, 8], true)) {
            return self::getVideoFrontendPageUrlById($type, $id);
        }
        if ($type === 0) {
            $data = Design::where('string_id', $id)->first(); // Match by string
            return $data ? self::$frontendUrl . 'templates/p/' . $data->id_name : self::$frontendUrl;
        }
        if ($type === 1) {
            $data = NewCategory::where('string_id', $id)->first();
            if ($data) {
                $parentCategory = $data->parent_category_id != 0 ? NewCategory::find($data->parent_category_id) : null;
                return self::$frontendUrl . 'templates/' . ($parentCategory ? $parentCategory->id_name . '/' : '') . $data->id_name;
            }

            return self::$frontendUrl;
        }
        if ($type === 2) {
            $data = SpecialPage::where('string_id', $id)->first();
            return $data ? self::$frontendUrl . $data->page_slug : self::$frontendUrl;
        }
        if ($type === 3) {
            $data = SpecialKeyword::where('string_id', $id)->first();
            return $data ? self::$frontendUrl . 'k/' . $data->name : self::$frontendUrl;
        }
        if ($type === 4) {
            $data = Category::where('string_id', $id)->first();
            return $data ? self::$frontendUrl . 'templates/' . $data->id_name : self::$frontendUrl;
        }
        if ($type === 5) {
            $data = VirtualCategory::where('string_id', $id)->first();
            return $data ? self::$frontendUrl . 'templates/' . $data->id_name : self::$frontendUrl;
        }

        return self::$frontendUrl;
    }

    public static function getPageValueByStringId($type, $stringID): string
    {
        $type = (int) $type;

        return match ($type) {
            0 => Design::where('string_id', $stringID)->value('post_name') ?? 'Not Found',
            1 => NewCategory::where('string_id', $stringID)->value('category_name') ?? 'Not Found',
            2 => SpecialPage::where('string_id', $stringID)->value('page_slug') ?? 'Not Found',
            3 => SpecialKeyword::where('string_id', $stringID)->value('name') ?? 'Not Found',
            4 => Category::where('string_id', $stringID)->value('category_name') ?? 'Not Found',
            5 => VirtualCategory::where('string_id', $stringID)->value('category_name') ?? 'Not Found',
            6 => VideoCategory::query()
                ->where(function ($q) use ($stringID) {
                        $q->where('slug', $stringID)->orWhere('string_id', $stringID);
                        if ($stringID !== '' && $stringID !== null && ctype_digit((string) $stringID)) {
                            $q->orWhere('id', (int) $stringID);
                        }
                    })
                ->value('category_name') ?? 'Not Found',
            7 => VideoVirtualCategory::query()
                ->where(function ($q) use ($stringID) {
                        $q->where('slug', $stringID)->orWhere('string_id', $stringID);
                        if ($stringID !== '' && $stringID !== null && ctype_digit((string) $stringID)) {
                            $q->orWhere('id', (int) $stringID);
                        }
                    })
                ->value('category_name') ?? 'Not Found',
            8 => VideoTemplate::query()
                ->where(function ($q) use ($stringID) {
                        $q->where('string_id', $stringID)->orWhere('slug', $stringID);
                        if ($stringID !== '' && $stringID !== null && ctype_digit((string) $stringID)) {
                            $q->orWhere('id', (int) $stringID);
                        }
                    })
                ->value('video_name') ?? 'Not Found',
            default => 'Not Found',
        };
    }

    public static function getFrontendPageUrl($type, $slug): string
    {
        if ($type == 0) {
            return self::$frontendUrl . 'templates/p/' . $slug;
        } else if ($type == 1 || $type == 4) {
            return self::$frontendUrl . 'templates/' . $slug;
        } else if ($type == 2) {
            return self::$frontendUrl . $slug;
        } else if ($type == 3) {
            return self::$frontendUrl . 'k/' . $slug;
        }
        return self::$frontendUrl;
    }

    public static function newCatChildUpdatedAt($parentCatId): string
    {
        $time = now()->toDateTimeString();
        if (isset($parentCatId)) {
            $parentCat = NewCategory::find($parentCatId);
            if ($parentCat) {
                $parentCat->child_updated_at = $time;
                $parentCat->save();
                if (isset($parentCat->parent_category_id) && $parentCat->parent_category_id !== 0) {
                    $grandParentCat = NewCategory::find($parentCat->parent_category_id);
                    if ($grandParentCat) {
                        $grandParentCat->child_updated_at = $time;
                        $grandParentCat->save();
                    }
                }
            }
        }
        return $time;
    }

    public static function caricatureChildUpdatedAt($parentCatId): string
    {
        $time = now()->toDateTimeString();
        if (isset($parentCatId)) {
            $parentCat = CaricatureCategory::find($parentCatId);
            if ($parentCat) {
                $parentCat->child_updated_at = $time;
                $parentCat->save();
                if (isset($parentCat->parent_category_id) && $parentCat->parent_category_id !== 0) {
                    $grandParentCat = CaricatureCategory::find($parentCat->parent_category_id);
                    if ($grandParentCat) {
                        $grandParentCat->child_updated_at = $time;
                        $grandParentCat->save();
                    }
                }
            }
        }
        return $time;
    }

        public static function checkStatusCondition($catId, $status, $type = 1, $parentCat = 0): ?string
    {
        $child_cat_ids = $parentCat == 0 ? NewCategory::where('parent_category_id', $catId)->pluck('id')->toArray() : [];
        if ($status == 0) {
            if ($type == 1) {
                $specialPages = SpecialPage::where('cat_id', $catId)->pluck('page_slug')->toArray();
                $specialKeywords = SpecialKeyword::where('cat_id', $catId)->pluck('name')->toArray();
                $childPages = !empty($child_cat_ids)
                    ? SpecialPage::whereIn('cat_id', $child_cat_ids)->pluck('page_slug')->toArray()
                    : [];
                $childKeywords = !empty($child_cat_ids)
                    ? SpecialKeyword::whereIn('cat_id', $child_cat_ids)->pluck('name')->toArray()
                    : [];
                $allPages = array_merge($specialPages, $childPages);
                $allKeywords = array_merge($specialKeywords, $childKeywords);
                if (!empty($allPages) || !empty($allKeywords)) {
                    return implode('<br><br>', array_filter([
                            $allPages ? '<strong>Special Pages :</strong><br>' . implode('<br>', array_unique($allPages)) : null,
                            $allKeywords ? '<strong>Special Keywords :</strong><br>' . implode('<br>', array_unique($allKeywords)) : null,
                        ])) . '<br><br><strong>You cannot unlive this page.</strong>';
                }
                if ($parentCat == 0 && !empty($child_cat_ids)) {
                    $liveChildExists = NewCategory::whereIn('id', $child_cat_ids)
                        ->where('status', 1)
                        ->exists();
                    if ($liveChildExists) {
                        return "Subcategory is live. You cannot unlive this page.";
                    }
                }
            }
            return null;
        }
        $template = NewCategory::where('id', $catId)
            ->select('total_templates')
            ->first();

        $templateCount = $template->total_templates ?? 0;

        if ($type == 1) {
            if ($templateCount > 0) {
                if ($parentCat != 0) {
                    $parentStatus = NewCategory::where('id', $parentCat)->value('status');
                    if ($parentStatus != 1) {
                        return "Parent category is not live. You cannot live this page.";
                    }
                }
                return null;
            }
            return "Template is not available in this category. You cannot live this page.";
        }
        if ($templateCount > 0) {
            $categoryStatus = NewCategory::where('id', $catId)->value('status');
            if ($categoryStatus == 1) {
                return null;
            }
            return "Assigned category is not live. You cannot live this page.";
        }
        return "Template is not available in the assigned category. You cannot live this page.";
    }

    public static function generateStringIds($length = 10, $prefix = '', $source = null, $column = 'string_id'): string
    {
        $stringId = Str::lower(Str::random($length));
        $finalId = $prefix ? $prefix . '_' . $stringId : $stringId;
        if ($source && class_exists($source)) {
            $modelClass = $source;
            $modelInstance = new $modelClass;
            $table = $modelInstance->getTable();
            // Check if the column exists
            if (Schema::hasColumn($table, $column)) {
                do {
                    $stringId = Str::lower(Str::random($length));
                    $finalId = $prefix ? $prefix . '_' . $stringId : $stringId;
                    $exists = $modelClass::where($column, $finalId)->exists();
                } while ($exists);
            }
        }
        return $finalId;
    }

    public static function generateID($id = "", $length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

}

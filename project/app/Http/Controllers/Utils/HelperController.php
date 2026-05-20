<?php

namespace App\Http\Controllers\Utils;

use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\LikedProduct;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoTemplate;
use App\Models\VirtualCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Random\RandomException;
use stdClass;

class HelperController extends Controller
{

    public static bool $cacheEnabled = true;
    public static int $paginationLimit = 20;
    public static int $cacheTimeOut = 3600;
    public static string $webPageUrl = "https://www.craftyartapp.com/";
//    public static string $webPageUrl = "http://localhost:3000/";
    public static array $oldMediaUrl = ["https://panel.craftyartapp.com/templates/", "https://storage.googleapis.com/media.craftyartapp.com/"];
    public static string $mediaUrl = "https://media.craftyartapp.com/";

    public static function getPaginationLimit(?int $size = null): int
    {
        if (is_int($size)) return $size;
        return HelperController::$paginationLimit;
    }

    public static function getItemData(
        string|null                               $uid,
        Category|NewCategory|VirtualCategory|null $catRow,
        Design|stdClass                           $item,
        array                                     $thumbArray,
        bool                                      $swap = true,
        string|null                               $catLink = null,
        bool                                      $fromEditor = false,
        Collection                                $rates = null): array
    {
        if ($swap) {
            $thumbArray = HelperController::swapThumbs($thumbArray, $item->default_thumb_pos);
        }

        foreach ($thumbArray as $key => $value) {
            $thumbArray[$key] = HelperController::$mediaUrl . $value;
        }

        $size = sizeof($thumbArray);
        $payment = RateController::getTemplateRates($rates, $size, $item);

        if ($item->additional_thumb && !$fromEditor) {
            array_unshift($thumbArray, HelperController::$mediaUrl . $item->additional_thumb);
        }

        $newSrcSet = [];
        foreach ($thumbArray as $value) {
            $srcSet = $value;
//            $srcSet = $value . "?width=" . 1143 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 1143) . " 1143w, ";
//            $srcSet = $srcSet . $value . "?width=" . 571 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 571) . " 571w, ";
//            $srcSet = $srcSet . $value . "?width=" . 286 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 286) . " 286w, ";
//            $srcSet = $srcSet . $value . "?width=" . 252 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 252) . " 252w, ";
//            $srcSet = $srcSet . $value . "?width=" . 150 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 150) . " 150w, ";
//            $srcSet = $srcSet . $value . "?width=" . 100 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 100) . " 100w, ";
//            $srcSet = $srcSet . $value . "?width=" . 71 . "&height=" . HelperController::calculateHeight($item->width, $item->height, 71) . " 71w";

            $data = [];
            $data['img'] = $value;
            $data['srcSet'] = $srcSet;
            $data['sizes'] = "(max-width: 400px) 80vw, (max-width: 600px) 50vw, (max-width: 900px) 33vw, (max-width: 1200px) 25vw, (max-width: 1500px) 20vw, 17vw";
            $newSrcSet[] = $data;
        }

        $isStarred = false;
        if ($uid) $isStarred = LikedProduct::where("user_id", $uid)->where("product_id", $item->string_id)->exists();

        $isFreemium = $item->is_premium == 0 && $item->is_freemium == 1;

        $totalCaricatures = count(match (true) {
            is_array($item->caricature_ids) => $item->caricature_ids,
            is_string($item->caricature_ids) => json_decode($item->caricature_ids, true) ?? [],
            default => [],
        });

        return array(
            'category_id' => $catRow?->id,
            'category_name' => $catRow?->category_name,
            'category_size' => $catRow?->size,
            'template_id' => $item->id,
            'string_id' => $item->string_id,
            'id_name' => $item->id_name ?? $item->string_id,
            'template_name' => $item->post_name,
            'meta_description' => $item->meta_description,
            'template_thumb' => HelperController::$mediaUrl . $item->post_thumb,
            'mockup' => $item->additional_thumb ? HelperController::$mediaUrl . $item->additional_thumb : null,
            'thumbArray' => $thumbArray,
            'thumbSrcSets' => $newSrcSet,
            'color' => "#efefef",
            'video' => $item->video_thumb != null ? HelperController::$mediaUrl . $item->video_thumb : null,
            'width' => $item->width,
            'height' => $item->height,
            'pages' => sizeof($thumbArray),
            'tags' => json_decode($item->related_tags),
            'related_tags' => json_decode($item->related_tags),
//            'new_tags' => $newTags,
            'latest' => false,
            'isStarred' => $isStarred,
            'ai_magic' => $totalCaricatures > 0,
            'caricatures' => $totalCaricatures,
            'ai_credits' => $totalCaricatures * 249,
//            'is_premium' => true /*$item->is_premium == 1*/,
            'is_premium' => ($item->is_premium == 1 || $item->is_freemium == 1),
            'is_freemium' => $isFreemium,
            'editor_choice' => $item->editor_choice == 1,
            'auto_create' => $item->auto_create == 1,
            'created_at' => Carbon::parse($item->created_at)->toAtomString(),
            'views' => $item->web_views,
            'payment' => $payment,
            'inrVal' => $payment['inrVal'],
            'usdVal' => $payment['usdVal'],
            'inrAmount' => $payment['inrAmount'],
            'usdAmount' => $payment['usdAmount'],
            'template_link' => HelperController::$webPageUrl . "templates/p/$item->id_name",
            'cat_link' => $catLink,
        );
    }

    public static function getCaricatureData(
        CaricatureCategory|null $catRow,
        Attire                  $item,
        Collection              $rates = null): array
    {

        $payment = RateController::getCaricatureRates($rates, $item->head_count, false, $item->editor_choice == 1);

        $isFreemium = $item->is_premium == 0 && $item->is_freemium == 1;

        return array(
            'id_name' => $item->id_name ?? $item->string_id,
            'string_id' => $item->string_id,
            'post_name' => $item->post_name,
            'meta_title' => $item->meta_title,
            'meta_description' => $item->meta_description,
            'h2_tag' => $item->h2_tag,
            'long_desc' => $item->long_desc,
            'width' => $item->width,
            'height' => $item->height,
            'thumb' => $item->thumbnail_url,
            'editor_choice' => $item->editor_choice == 1,
            'is_premium' => ($item->is_premium == 1 || $item->is_freemium == 1),
            'is_freemium' => $isFreemium,
            'views' => $item->trending_views,
            'payment' => $payment,
            'page_link' => HelperController::$webPageUrl . "caricature/p/$item->id_name",
            'template_thumbs' => $item->template_thumbs,
        );
    }

    public static function getVideoItemData(
        VideoTemplate|stdClass $item,
        Collection             $rates = null): array
    {

        $payment = RateController::getVideoRates($rates, $item->pages);
        $category = $item->virtualCat ?? $item->videoCat;

        return array(
            'category_id' => $item->category_id,
            'category_name' => $category?->category_name,
            'category_title' => $category?->category_name ?? $category?->category_title,
            'video_id' => $item->id,
            'string_id' => $item->string_id,
            'video_name' => $item->video_name,
            'file_name' => $item->folder_name,
            'video_thumb' => HelperController::$mediaUrl . $item->video_thumb,
            'video_url' => HelperController::$mediaUrl . $item->video_url,
            'width' => $item->width,
            'height' => $item->height,
            'keyword' => $item->keyword,
            'views' => $item->views,
            'creation' => $item->creation,
            'is_premium' => $item->is_premium,
            'payment' => $payment,
            'template_link' => "$item->slug",
            'cat_link' => $category?->slug ?? "/templates/p/$item->string_id",
        );
    }

    public static function checkStringFormat($string, $convert = false): string
    {

        if (preg_match("/^\['(.+)'\]$/", $string, $matches)) {
            if ($convert) {
                return $matches[1];
            }
            return $string;
        }
        return $string;
    }

    public static function checkSubsStatus($status): string
    {
        if ($status == '1') {
            return "Active";
        } else {
            return "Expired";
        }
    }

    public static function getSubsColor($status): string
    {
        if ($status == '1') {
            return "#2EC4B6";
        } else {
            return "#FF0000";
        }
    }

    public static function swapThumbs($thumbArray, $j): array
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

    // public static function generateID($prefix = '', $length = 10, bool $appendNumber = true, $stringType = "noraml"): string
    // {
    //     if ($appendNumber) $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //     else $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    //     $random = '';
    //     for ($i = 0; $i < $length; $i++) {
    //         try {
    //             $index = random_int(0, strlen($pool) - 1);
    //         } catch (\Random\RandomException $e) {
    //             $index = mt_rand(0, strlen($pool) - 1);
    //         }
    //         $random .= $pool[$index];
    //     }

    //     $finalString = $prefix ? $prefix . '_' . $random : $random;
    //     return match ($stringType) {
    //         'upper' => strtoupper($finalString),
    //         'lower' => strtolower($finalString),
    //         default => $finalString,
    //     };
    // }

    // public static function generateRandomId($length = 10, $prefix = '', ?string $modelSource = null, $column = 'string_id', bool $appendNumber = false, $stringType = "normal"): string
    // {

    //     if ($modelSource && class_exists($modelSource)) {
    //         $modelClass = $modelSource;
    //         $modelInstance = new $modelClass;
    //         $table = $modelInstance->getTable();
    //         // Check if the column exists
    //         if (Schema::hasColumn($table, $column)) {
    //             do {
    //                 $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType);
    //                 $exists = $modelClass::where($column, $finalId)->exists();
    //             } while ($exists);
    //         } else {
    //             $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType);
    //         }
    //     } else {
    //         $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType);
    //     }
    //     return $finalId;
    // }
    public static function generateID($prefix = '', $length = 10, bool $appendNumber = true, $stringType = "normal" , $postfix = ''): string
    {
        if ($appendNumber) $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        else $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $random = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $index = random_int(0, strlen($pool) - 1);
            } catch (\Random\RandomException $e) {
                $index = mt_rand(0, strlen($pool) - 1);
            }
            $random .= $pool[$index];
        }

        $finalString = $prefix ? $prefix . '_' . $random : $random;

        if($postfix) {
            $finalString .= $postfix;
        }

        return match ($stringType) {
            'upper' => strtoupper($finalString),
            'lower' => strtolower($finalString),
            default => $finalString,
        };
    }

    public static function generateRandomId($length = 10, $prefix = '', ?string $modelSource = null, $column = 'string_id', bool $appendNumber = false, $stringType = "normal", $postfix = ''): string
    {

        if ($modelSource && class_exists($modelSource)) {
            $modelClass = $modelSource;
            $modelInstance = new $modelClass;
            $table = $modelInstance->getTable();
            // Check if the column exists
            if (Schema::hasColumn($table, $column)) {
                do {
                    $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType, postfix: $postfix );
                    $exists = $modelClass::where($column, $finalId)->exists();
                } while ($exists);
            } else {
                $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType, postfix: $postfix );
            }
        } else {
            $finalId = self::generateID(prefix: $prefix, length: $length, appendNumber: $appendNumber, stringType: $stringType, postfix: $postfix );
        }
        return $finalId;
    }


    public static function getIpAndCountry(Request $request, $ip = null): array
    {
        $ipAddress = $ip ?? ApiController::findIp($request);
        return self::getCountryByIp($ipAddress);
    }

    public static function getCountryByIp($ipAddress): array
    {
        // Get the location details from the IP address
        $location = GeoIP($ipAddress);

        // Extract the country code and country name
        $countryCode = $location['iso_code'];
        $countryName = $location['country'];
        $currency = $location['currency'];

//        if ($request->isTester) {
//            return ['ip' => $ipAddress, 'cc' => "US", 'cn' => "US", 'cur' => "USD"];
//        }

        return [
            'ip' => $ipAddress,
            'cc' => strtoupper($countryCode),
            'cn' => $countryName,
            'cur' => strtoupper($currency),
            'symbol' => strtoupper($currency) === "INR" ? "₹" : "$",
        ];
    }

    public static function getTopKeywords($topKeywords): array
    {
        if (!empty($topKeywords)) {
            foreach ($topKeywords as &$keyword) {
                if (isset($keyword->openinnewtab)) {
                    $keyword->openinnewtab = (int)$keyword->openinnewtab;
                }
                if (isset($keyword->nofollow)) {
                    $keyword->nofollow = (int)$keyword->nofollow;
                }
            }
            unset($keyword);
        }

        return $topKeywords;
    }

    public static function generatePublicUrl($path): string
    {
        if (str_starts_with($path, "https://assets.craftyart.in") || str_starts_with($path, HelperController::$mediaUrl)) {
            return $path;
        }
        $path = str_replace(HelperController::$oldMediaUrl, '', $path);
        return HelperController::$mediaUrl . $path;
    }

    public static function getCTA($cta)
    {
        if (!$cta) {
            return null;
        }

        $ctaData = json_decode($cta, true);
        // Process the image paths
        foreach ($ctaData as $key => &$ctaItem) {
            // Handle single image objects
            if (isset($ctaItem['image']['src'])) {
                $ctaItem['image']['src'] = HelperController::generatePublicUrl($ctaItem['image']['src']);
            }
            // Handle arrays of images
            if (isset($ctaItem['images']) && is_array($ctaItem['images'])) {
                foreach ($ctaItem['images'] as &$imageItem) {
                    if (isset($imageItem['src'])) {
                        $imageItem['src'] = HelperController::generatePublicUrl($imageItem['src']);
                    }
                }
            }
            // Handle step section images
            if (isset($ctaItem['stepsection']) && is_array($ctaItem['stepsection'])) {
                foreach ($ctaItem['stepsection'] as &$step) {
                    if (isset($step['image']['src'])) {
                        $step['image']['src'] = HelperController::generatePublicUrl($step['image']['src']);
                    }
                }
            }
        }

        return $ctaData;
    }

    public static function getTemplateCount($count, $primaryKeyword): array
    {
        if ($primaryKeyword) {
            if ($count > 20) $final = $count . "+ " . $primaryKeyword;
            else $final = $count . " " . $primaryKeyword;
        } else {
            if ($count > 20) $final = $count . "+  Templates";
            else {
                if ($count <= 1) $final = $count . " Template";
                else $final = $count . " Templates";
            }
        }

        return ["count" => $count, "msg" => $final];
    }

    public static function extractAndRemoveTrailingNumber($string): array
    {
        if (preg_match('/\/(\d+)$/', $string, $matches)) {
            $number = intval($matches[1]);
            $cleaned = preg_replace('/\/\d+$/', '', $string);
            return [
                'number' => $number,
                'string' => $cleaned
            ];
        }
        return [
            'number' => null,
            'string' => $string
        ];
    }

    public static function getExtensionFromMimeType(string $mimeType): string
    {
        $map = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        return $map[$mimeType] ?? 'png';
    }

}

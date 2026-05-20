<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Video\LottieApiController;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoVirtualCategory;
use App\Models\VirtualCategory;
use Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SimpleXMLElement;

class ContentManager
{
    public static function getContents($contents, $fldrStr, $availableImage = [], $availableVideo = []): false|string
    {
        $contents = json_decode($contents);
        $oldImageInData = [];
        $oldVideoInData = [];
        if ($contents == null) {
            return "";
        }
        foreach ($contents as $contentKey => $items) {
            if ($items !== null && isset($items->type)) {

                if ($items->type == 'content') {
                    foreach ($items->value as $key => $item) {
                        if ($key === 'images') {
                            if (str_starts_with($item->link, 'data:image/')) {
                                $imageFolderPath = public_path('assets/images/');
                                if (!file_exists($imageFolderPath)) {
                                    mkdir($imageFolderPath, 0777, true);
                                }
                                $image_parts = explode(";base64,", $item->link);
                                $image_type_aux = explode("image/", $image_parts[0]);
                                $image_type = $image_type_aux[1];
                                $image_base64 = base64_decode($image_parts[1]);
                                $uniqid = uniqid();

                                $directory = '';
                                $file = 'p/' . $fldrStr . '/I/' . $uniqid . '.' . $image_type;
                                StorageUtils::put($file, $image_base64);
                                // $item->link = env('CLOUDFLARE_R2_URL') . $file;
                                $item->link = $file;
                            } else {
                                $oldImageInData[] = $item->link;
                            }
                        }
                        if ($key === 'video') {
                            if (str_starts_with($item->link, 'data:video/')) {
                                $videoFolderPath = public_path('assets/video/');
                                if (!file_exists($videoFolderPath)) {
                                    mkdir($videoFolderPath, 0777, true);
                                }
                                $video_parts = explode(";base64,", $item->link);
                                $video_type_aux = explode("video/", $video_parts[0]);
                                $video_type = $video_type_aux[1];
                                $video_base64 = base64_decode($video_parts[1]);
                                $uniqid = uniqid();
                                $file = 'p/' . $fldrStr . '/V/' . $uniqid . '.' . $video_type;
                                StorageUtils::put($file, $video_base64);
                                // $item->link = env('CLOUDFLARE_R2_URL') . $file;
                                $item->link = $file;

                            } else {
                                $oldVideoInData[] = $item->link;
                            }
                        }
                    }
                } else if (Str::startsWith($items->type, 'cta')) {
                    if ($items->type == "cta_convert" || $items->type == "cta_hero" || $items->type == "cta_feature") {
                        if (isset($items->value->image->src) && !Str::startsWith($items->value->image->src, "uploadedFiles")) {
                            $imageData = ContentManager::processBase64Image($items->value->image->src);
                            $items->value->image->src = $imageData['src'];
                            $items->value->image->width = $imageData['width'];
                            $items->value->image->height = $imageData['height'];
                        }
                    } else if ($items->type == "cta_ads") {
                        if (isset($items->value->image->src) && !Str::startsWith($items->value->image->src, "uploadedFiles")) {
                            $imageData = ContentManager::processBase64Image($items->value->image->src);
                            $items->value->image->src = $imageData['src'];
                            $items->value->image->width = $imageData['width'];
                            $items->value->image->height = $imageData['height'];
                        }

                        if (isset($items->value->button->src) && !Str::startsWith($items->value->button->src, "uploadedFiles")) {
                            $buttonData = ContentManager::processBase64Image($items->value->button->src);
                            $items->value->button->src = $buttonData['src'];
                            $items->value->button->width = $buttonData['width'];
                            $items->value->button->height = $buttonData['height'];
                        }
                    } else if ($items->type == "cta_scrollable") {
                        if (isset($items->value->images) && is_array($items->value->images)) {
                            $processedSteps = array_map(function ($step) {
                                if (isset($step->src) && !Str::startsWith($step->src, "uploadedFiles")) {
                                    $imageData = ContentManager::processBase64Image($step->src);
                                    $step->src = $imageData['src'];
                                    $step->width = $imageData['width'];
                                    $step->height = $imageData['height'];
                                }
                                return $step;
                            }, $items->value->images);
                            $items->value->images = $processedSteps;
                        }
                    } else if ($items->type == "cta_how_to_make" || $items->type == "cta_process" || $items->type == "cta_multiplebtn") {
                        if (isset($items->value->stepsection) && is_array($items->value->stepsection)) {
                            $processedSteps = array_map(function ($step) {
                                if (isset($step->image->src) && !Str::startsWith($step->image->src, "uploadedFiles")) {
                                    $imageData = ContentManager::processBase64Image($step->image->src);
                                    $step->image->src = $imageData['src'];
                                    $step->image->width = $imageData['width'];
                                    $step->image->height = $imageData['height'];
                                }
                                return $step;
                            }, $items->value->stepsection);
                            $items->value->stepsection = $processedSteps;
                        }
                    }

                    if (isset($items->value->bg->src)) {
                        if (isset($items->value->bg->src) && !Str::startsWith($items->value->bg->src, "uploadedFiles")) {
                            $bgData = ContentManager::processBase64Image($items->value->bg->src);
                            $items->value->bg->src = $bgData['src'];
                            $items->value->bg->width = $bgData['width'];
                            $items->value->bg->height = $bgData['height'];
                        }
                    }

                } else if ($items->type == 'ads') {
                    if (isset($items->value->image)) {
                        if (str_starts_with($items->value->image, 'data:image/')) {
                            $folderPath = public_path('assets/images/');
                            if (!file_exists($folderPath)) {
                                mkdir($folderPath, 0777, true);
                            }
                            $image_parts = explode(";base64,", $items->value->image);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];
                            $image_base64 = base64_decode($image_parts[1]);
                            $uniqid = uniqid();
                            $file = 'p/' . $fldrStr . '/I/' . $uniqid . '.' . $image_type;
                            StorageUtils::put($file, $image_base64);
                            $items->value->image = env('CLOUDFLARE_R2_URL') . $file;
                        } else {
                            $oldImageInData[] = $items->value->image;
                        }
                    }
                }
            } else {
                unset($contents[$contentKey]);
            }
        }

        foreach ($availableImage as $imageFromDb) {
            if (!in_array($imageFromDb, $oldImageInData)) {
                $imagePath = basename($imageFromDb);
                StorageUtils::delete($imagePath);
            }
        }

        foreach ($availableVideo as $videoFromDb) {
            if (!in_array($videoFromDb, $oldVideoInData)) {
                $videoPath = basename($videoFromDb);
                StorageUtils::delete($videoPath);
            }
        }
        return json_encode($contents);
    }

    public static function getBase64Contents($contents)
    {
        $contents = json_decode($contents);
        $base64Images = [];

        if ($contents == null) {
            return $base64Images;
        }

        foreach ($contents as $items) {
            if ($items !== null && isset($items->type)) {
                if ($items->type == 'content') {
                    foreach ($items->value as $key => $item) {
                        if ($key === 'images' && isset($item->link) && strpos($item->link, 'data:image/') === 0) {
                            $base64Images[] = $item->link;
                        }
                    }
                } elseif (Str::startsWith($items->type, 'cta')) {
                    if (isset($items->value->image->src) && !Str::startsWith($items->value->image->src, "uploadedFiles")) {
                        $base64Images[] = $items->value->image->src;
                    }

                    if ($items->type == "cta_ads" && isset($items->value->button->src) && !Str::startsWith($items->value->button->src, "uploadedFiles")) {
                        $base64Images[] = $items->value->button->src;
                    }

                    if ($items->type == "cta_scrollable" && isset($items->value->images) && is_array($items->value->images)) {
                        foreach ($items->value->images as $step) {
                            if (isset($step->src) && !Str::startsWith($step->src, "uploadedFiles")) {
                                $base64Images[] = $step->src;
                            }
                        }
                    }

                    if (in_array($items->type, ["cta_how_to_make", "cta_process", "cta_multiplebtn"]) && isset($items->value->stepsection) && is_array($items->value->stepsection)) {
                        foreach ($items->value->stepsection as $step) {
                            if (isset($step->image->src) && !Str::startsWith($step->image->src, "uploadedFiles")) {
                                $base64Images[] = $step->image->src;
                            }
                        }
                    }

                    if (isset($items->value->bg->src) && !Str::startsWith($items->value->bg->src, "uploadedFiles")) {
                        $base64Images[] = $items->value->bg->src;
                    }
                }
            }
        }

        return $base64Images;
    }

    public static function validateBase64Images(array $base64Images)
    {
        $validTypes = ['jpg', 'jpeg', 'svg', 'webp'];
        foreach ($base64Images as $key => $base64Image) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $imageType = strtolower($matches[1]);
                $base64String = substr($base64Image, strpos($base64Image, ',') + 1);
                $decodedImage = base64_decode($base64String, true);

                if ($decodedImage === false) {
                    return "Invalid Base64 encoding at index $key.";
                }

                if (!in_array($imageType, $validTypes)) {
                    return "Unsupported format '$imageType' at index $key.";
                }

                $imageSize = strlen($decodedImage); // Get the size of the decoded image

                if ($imageType == 'webp' && $imageSize > 200 * 1024) {
                    return "Webp images must be less than 200KB at index $key.";
                }

                if (in_array($imageType, ['jpg', 'jpeg', 'svg']) && $imageSize > 50 * 1024) {
                    return "Jpg, jpeg, and svg images must be less than 50KB at index $key.";
                }
            } else {
                return "Invalid Base64 format at index $key.";
            }
        }

        return null;
    }

    public static function processBase64Image($base64String, $directory = 'uploadedFiles/cta_images/'): array
    {
        try {
            // Validate base64 string format
            if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64String, $matches)) {
                $extension = explode('+', $matches[1])[0]; // Handle cases like 'svg+xml'
                $imageData = base64_decode(substr($base64String, strpos($base64String, ',') + 1));
                if ($imageData === false) {
                    return ['src' => null, 'width' => null, 'height' => null];
                }
                $width = null;
                $height = null;
                // Get image dimensions for non-SVG images
                if ($extension !== 'svg') {
                    $imageSize = getimagesizefromstring($imageData);
                    $width = $imageSize[0] ?? null;
                    $height = $imageSize[1] ?? null;
                } else {
                    // Extract width and height from SVG
                    [$width, $height] = self::extractSvgDimensions($imageData);
                }
                $imageName = Str::random(40) . '.' . $extension;
                $imagePath = $directory . $imageName;
                StorageUtils::put($imagePath, $imageData);
                return [
                    'src' => $imagePath,
                    'width' => $width,
                    'height' => $height
                ];
            } else {
                $imagePath = ContentManager::getStorageLink($base64String);
                $dimensions = self::getImageSizeFromUrl($imagePath);
                return [
                    'src' => str_replace(HelperController::$mediaUrl, '', $base64String),
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height']
                ];
            }
        } catch (\Exception $e) {
            return [
                'src' => str_replace(HelperController::$mediaUrl, '', $base64String),
                'width' => null,
                'height' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    public static function getImageSizeFromUrl($url): ?array
    {
        $size = @getimagesize($url);
        if ($size) {
            return [
                'width' => $size[0],
                'height' => $size[1]
            ];
        }
        return null;
    }

    /**
     * Extract width and height from an SVG image string
     */
    private static function extractSvgDimensions($svgContent)
    {
        try {
            $xml = new SimpleXMLElement($svgContent);
            $attributes = $xml->attributes();

            $width = isset($attributes->width) ? (string)$attributes->width : null;
            $height = isset($attributes->height) ? (string)$attributes->height : null;

            // If width/height are missing, check viewBox attribute
            if (!$width || !$height) {
                if (isset($attributes->viewBox)) {
                    $viewBox = explode(' ', (string)$attributes->viewBox);
                    if (count($viewBox) === 4) {
                        $width = $viewBox[2];
                        $height = $viewBox[3];
                    }
                }
            }

            return [$width, $height];
        } catch (\Exception $e) {
            return [null, null];
        }
    }

    public static function getStorageLink($src)
    {
        if (!$src)
            return '';

        if (strpos($src, 'data:image') === 0) {
            $base64String = explode(',', $src, 2)[1] ?? '';
            if (preg_match('/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/', $base64String)) {
                return $src;
            }
        }

        return HelperController::generatePublicUrl($src);
    }

    public static function validateMultipleImageFiles(array $imageFields)
    {
        foreach ($imageFields as $image) {
            if (!$image['file'] && $image['required']) {
                return $image['name'] . ' is required';
            }

            if (!$image['file']) {
                continue;
            }

            $imageType = $image['file']->getClientOriginalExtension();
            $imageSize = $image['file']->getSize();
            $validTypes = ['jpg', 'jpeg', 'svg', 'webp'];
            if (!in_array(strtolower($imageType), $validTypes)) {
                return 'All images must be jpg, jpeg, svg, or webp files.';
            }
            if ($imageType == 'webp' && $imageSize > 200 * 1024) {
                return 'Webp images must be less than 200KB.';
            }

            if (in_array($imageType, ['jpg', 'jpeg', 'svg']) && $imageSize > 50 * 1024) {
                return 'Jpg, jpeg, and svg images must be less than 50KB.';
            }
        }
        return null;
    }

    public static function getContentsPath(Collection $rates, $contents, $uid, string $cacheTag, string $cacheKey, bool $doCache = false)
    {
        $callback = function () use ($rates, $contents, $uid) {
            if (!isset($contents))
                return [];
            foreach ($contents as $content) {
                if ($content->type == 'content') {
                    foreach ($content->value as $key => $item) { // Get key and item
                        if ($key === 'images' || $key === 'video') {
                            if (isset($content->value->$key->link)) { // Ensure 'link' exists
                                $content->value->$key->link = ContentManager::getStorageLink($content->value->$key->link);
                            }
                        }
                    }
                }
                if (Str::startsWith($content->type, 'cta')) {
                    $ctaItem = $content->value;
                    if (isset($ctaItem->image->src)) {
                        $ctaItem->image->src = HelperController::generatePublicUrl($ctaItem->image->src);
                    }
                    if (isset($ctaItem->button->src)) {
                        $ctaItem->button->src = HelperController::generatePublicUrl($ctaItem->button->src);
                    }

                    if (isset($ctaItem->bg->src)) {
                        $ctaItem->bg->src = HelperController::generatePublicUrl($ctaItem->bg->src);
                    }

                    // Handle arrays of images
                    if (isset($ctaItem->images) && is_array($ctaItem->images)) {
                        foreach ($ctaItem->images as &$imageItem) {
                            if (isset($imageItem->src)) {
                                $imageItem->src = HelperController::generatePublicUrl($imageItem->src);
                            }
                        }
                    }
                    // Handle step section images
                    if (isset($ctaItem->stepsection) && is_array($ctaItem->stepsection)) {
                        foreach ($ctaItem->stepsection as &$step) {
                            if (isset($step->image->src)) {
                                $step->image->src = HelperController::generatePublicUrl($step->image->src);
                            }
                        }
                    }
                    if ($content->type === 'cta_more_template') {
                        if ($ctaItem->virtualType == 'url') {
                            $virtualCat = VirtualCategory::where('id_name', $ctaItem->slug)->where('status', 1)->first();
                            if ($virtualCat == null) {
                                $ctaItem->data = [];
                                continue;
                            }
                            $ctaItem->title = $virtualCat->category_name;
                            $ctaItem->desc = $virtualCat->short_desc;
                            $conditionQuery = $virtualCat->virtual_query;

                        } else {
                            $conditionQuery = $ctaItem->query;
                        }
                        $query = Design::query();
                        $conditions = explode(' && ', $conditionQuery);
                        $limit = QueryManager::applyConditionToQuery($query, $conditions);
                        $datas = $query->where('status', 1)->limit($limit)->get();
                        $ctaItem->data = [];

                        $allCategoryIds = $datas->pluck('new_category_id')->unique();
                        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

                        foreach ($datas as $item) {
                            $catRow = $categories[$item->new_category_id] ?? null;
                            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
                            if ($catRow != null) {
                                $catLink = $catRow->cat_link;
                            }
                            $ctaItem->data[] = HelperController::getItemData(uid: $uid, catRow: $catRow, item: $item, thumbArray: json_decode($item->thumb_array), catLink: $catLink, rates: $rates);
                        }
                    }
                }
            }
            return $contents;
        };

        if (HelperController::$cacheEnabled && $doCache) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback();
        }

        return $response;
    }

    public static function getVideoContentsPath(Collection $rates, $contents, $uid, $page, $filter, $slug, string $cacheTag, string $cacheKey, bool $doCache = false)
    {
        $callback = function () use ($rates, $contents, $uid, $page, $filter, $slug) {
            if (!isset($contents)) return [];
            foreach ($contents as $content) {

                if ($content->type == 'api_virtual') {
                    $ctaItem = $content->value;

                    $query = VideoTemplate::query()->with(['videoCat', 'virtualCat'])->whereDoFrontLottie(1)->whereIsDeleted(0)->whereStatus(1);

                    $conditions = explode(' && ', $ctaItem->query);
                    $limit = QueryManager::applyConditionToQuery($query, $conditions, HelperController::getPaginationLimit(), "videovirtualcolumns");
                    if (!empty($filter)) $query = LottieApiController::getFilterQuery($query, $filter);
                    $datas = $query->paginate($limit, ['*'], 'page', $page);
                    $ctaItem->data = [];

                    foreach ($datas->items() as $item) {
                        $ctaItem->data[] = HelperController::getVideoItemData(item: $item, rates: $rates);
                    }

                    $ctaItem->pagination = PaginationController::getPagination($datas, $filter, $slug);
                }

                if ($page > 1) continue;

                if ($content->type == 'content') {
                    foreach ($content->value as $key => $item) { // Get key and item
                        if ($key === 'images' || $key === 'video') {
                            if (isset($content->value->$key->link)) { // Ensure 'link' exists
                                $content->value->$key->link = ContentManager::getStorageLink($content->value->$key->link);
                            }
                        }
                    }
                }

                if (Str::startsWith($content->type, 'cta')) {
                    $ctaItem = $content->value;
                    if (isset($ctaItem->image->src)) {
                        $ctaItem->image->src = HelperController::generatePublicUrl($ctaItem->image->src);
                    }

                    if (isset($ctaItem->button->src)) {
                        $ctaItem->button->src = HelperController::generatePublicUrl($ctaItem->button->src);
                    }

                    if (isset($ctaItem->bg->src)) {
                        $ctaItem->bg->src = HelperController::generatePublicUrl($ctaItem->bg->src);
                    }

                    // Handle arrays of images
                    if (isset($ctaItem->images) && is_array($ctaItem->images)) {
                        foreach ($ctaItem->images as &$imageItem) {
                            if (isset($imageItem->src)) {
                                $imageItem->src = HelperController::generatePublicUrl($imageItem->src);
                            }
                        }
                    }

                    // Handle step section images
                    if (isset($ctaItem->stepsection) && is_array($ctaItem->stepsection)) {
                        foreach ($ctaItem->stepsection as &$step) {
                            if (isset($step->image->src)) {
                                $step->image->src = HelperController::generatePublicUrl($step->image->src);
                            }
                        }
                    }

//                    if ($content->type === 'cta_more_template') {
//                        if ($ctaItem->virtualType == 'url') {
//                            $virtualCat = VideoVirtualCategory::whereSlug($ctaItem->slug)->where('status', 1)->first();
//                            if ($virtualCat == null) {
//                                $ctaItem->data = [];
//                                continue;
//                            }
//                            $ctaItem->title = $virtualCat->category_name;
//                            $ctaItem->desc = $virtualCat->short_desc;
//                            $conditionQuery = $virtualCat->virtual_query;
//
//                        } else {
//                            $conditionQuery = $ctaItem->query;
//                        }
//                        $query = Design::query();
//                        $conditions = explode(' && ', $conditionQuery);
//                        $limit = QueryManager::applyConditionToQuery($query, $conditions);
//                        $datas = $query->where('status', 1)->limit($limit)->get();
//                        $ctaItem->data = [];
//
//                        $allCategoryIds = $datas->pluck('new_category_id')->unique();
//                        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');
//
//                        foreach ($datas as $item) {
//                            $catRow = $categories[$item->new_category_id] ?? null;
//                            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
//                            if ($catRow != null) {
//                                $catLink = $catRow->cat_link;
//                            }
//                            $ctaItem->data[] = HelperController::getItemData(uid: $uid, catRow: $catRow, item: $item, thumbArray: json_decode($item->thumb_array), catLink: $catLink, rates: $rates);
//                        }
//                    }

                }
            }
            return $contents;
        };

        if (HelperController::$cacheEnabled && $doCache) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback();
        }

        return $response;
    }

    public static function faqsResponse($faqs, mixed $premiumKeyword, string $cacheTag, string $cacheKey, bool $doCache = false): array
    {
        if (!$premiumKeyword) $premiumKeyword = "";
        $callback = function () use ($faqs, $premiumKeyword) {
            try {
                $rawFaqData = isset($faqs) ? StorageUtils::get($faqs) : null;
                if ($rawFaqData) {
                    $decoded = json_decode($rawFaqData, true);
                    if (isset($decoded['faqs'])) {
                        $response['faqs_title'] = $decoded['title'] ?? '';
                        $response['faqs'] = $decoded['faqs'];
                    } else {
                        $response['faqs'] = $decoded;
                        $response['faqs_title'] = $premiumKeyword ? "Faqs for " . $premiumKeyword : "Faqs for Templates";
                    }
                } else {
                    $response['faqs'] = [];
                    $response['faqs_title'] = '';
                }
            } catch (\Exception $e) {
                $response['faqs'] = [];
                $response['faqs_title'] = '';
            }
            return $response;
        };

        if (HelperController::$cacheEnabled && $doCache) {
            $response = Cache::tags([$cacheTag])->remember($cacheKey, HelperController::$cacheTimeOut, $callback);
        } else {
            $response = $callback();
        }

        return $response;
    }
}

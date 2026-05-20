<?php
namespace App\Http\Controllers\Utils;

use App\Http\Controllers\HelperController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Auth;

class ContentManager extends Controller
{

    public static function validateContent($contents,$longDesc,$h2Tag): ?string
    {
        if (!isset($contents) && !isset($longDesc)) {
           return "Please Add Contents or Long description";
        }
        if (isset($longDesc) && !isset($h2Tag)){
            return "H2 tag is required if Long description Available";
        }
        if (isset($contents) && !isset($longDesc) && isset($h2Tag)){
            return "Remove H2 Tag From H2 Tag field becauze long desc is null and content is available";
        }
        return null;
    }

    public static function getContents($contents, $fldrStr, $availableImage = [], $availableVideo = []): bool|string
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
                            if (strpos($item->link, 'data:image/') === 0) {
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
                            if (strpos($item->link, 'data:video/') === 0) {
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
                    if ($items->type == "cta_convert" || $items->type == "cta_hero" || $items->type == "cta_feature" || $items->type == "cta_ads") {
                        if (isset($items->value->image->src)) {
                            $imageData = ContentManager::processBase64Image($items->value->image->src);
                            $items->value->image->src = $imageData['src'];
                            $items->value->image->width = $imageData['width'];
                            $items->value->image->height = $imageData['height'];
                        }
                        if ($items->type == "cta_ads" && isset($items->value->button->src)) {
                            $buttonData = ContentManager::processBase64Image($items->value->button->src);
                            $items->value->button->src = $buttonData['src'];
                            $items->value->button->width = $buttonData['width'];
                            $items->value->button->height = $buttonData['height'];
                        }
                    } else if ($items->type == "cta_scrollable") {
                        if (isset($items->value->images) && is_array($items->value->images)) {
                            $processedSteps = array_map(function ($step) {
                                if (isset($step->src)) {
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
                                if (isset($step->image->src)) {
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
                        $bgData = ContentManager::processBase64Image($items->value->bg->src);
                        $items->value->bg->src = $bgData['src'];
                        $items->value->bg->width = $bgData['width'];
                        $items->value->bg->height = $bgData['height'];
                    }

                } else if ($items->type == 'ads') {
                    if (isset($items->value->image)) {
                        if (strpos($items->value->image, 'data:image/') === 0) {
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
                // }
            }
        }
        return json_encode($contents);
    }

    public static function getContentsPath($contents, $uid): array
    {
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
                    $query = DB::table('designs');
                    $conditions = explode(' && ', $conditionQuery);
                    QueryManager::applyConditionToQuery($query, $conditions);
                    $datas = $query->get();
                    $ctaItem->data = [];
                    foreach ($datas as $item) {
                        $catRow = Category::find($item->category_id);
                        if ($catRow != null) {
                            $ctaItem->data[] = HelperController::getItemData($uid, $catRow, $item, json_decode($item->thumb_array), true, false, true);
                        }
//                else {
//                    $ctaItem->data[] = $item;
//                }
                    }
                }

            }
        }
        return $contents;
    }

    public static function getBase64Contents($contents): array
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
                        if ($key === 'images' && isset($item->link)) {
                            $base64Images[] = ['img' => $item->link, 'name' => "Content Image", 'required' => false];
                        }
                    }
                } elseif (Str::startsWith($items->type, 'cta')) {
                    if (isset($items->value->image->src)) {
                        $base64Images[] = ['img' => $items->value->image->src, 'name' => $items->value->name, 'required' => false];
                    }

                    if ($items->type == "cta_ads" && isset($items->value->button->src)) {
                        $base64Images[] = ['img' => $items->value->button->src, 'name' => $items->value->name, 'required' => false];
                    }

                    if ($items->type == "cta_scrollable" && isset($items->value->images) && is_array($items->value->images)) {
                        foreach ($items->value->images as $index => $step) {
                            $base64Images[] = ['img' => $step->src, 'name' => $items->value->name . " Image " . $index + 1, 'required' => false];
                        }
                    }

                    if (in_array($items->type, ["cta_how_to_make", "cta_process", "cta_multiplebtn"]) && isset($items->value->stepsection) && is_array($items->value->stepsection)) {
                        foreach ($items->value->stepsection as $index => $step) {
                            $base64Images[] = ['img' => $step->image->src, 'name' => $items->value->name . " Image " . $index + 1, 'required' => false];
                        }
                    }

                    if (isset($items->value->bg->src) && self::isBase64($items->value->bg->src)) {
                        $base64Images[] = ['img' => $items->value->bg->src, 'name' => $items->value->name . " Background Image", 'required' => false];
                    }
                }
            }
        }

        return $base64Images;
    }

    public static function isBase64($base64String): bool
    {
        if (isset($base64String) && preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64String, $matches)) {
            return true;
        }
        return false;
    }

    public static function validateBase64Images(array $base64Images, array $maxSizes = []): ?string
    {
        // Default max sizes (in KB)
        $defaultSizes = [
            'webp' => 200,
            'gif' => 300,
            'jpg' => 50,
            'png' => 50,
            'jpeg' => 50,
            'svg' => 50,
        ];

        // Merge defaults with user-specified limits
        $maxSizes = array_merge($defaultSizes, $maxSizes);

        $validTypes = ['jpg', 'jpeg', 'svg', 'svg+xml', 'webp', 'gif',"png"];

        foreach ($base64Images as $key => $base64Obj) {
            $base64Image = $base64Obj['img'];
            $required = $base64Obj['required'];
            $imageName = $base64Obj['name'];
            $existingImg = $base64Obj['existingImg'] ?? null;

            // Required check
            if (!isset($base64Image) && $required) {
                return "$imageName File is Required";
            }
            if (!isset($base64Image)) {
                continue;
            }

            // Base64 validation
            if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64Image, $matches)) {
                $imageType = strtolower($matches[1]);
                if ($imageType === 'svg+xml') {
                    $imageType = 'svg';
                }

                $base64String = substr($base64Image, strpos($base64Image, ',') + 1);
                $decodedImage = base64_decode($base64String, true);
                if ($decodedImage === false) {
                    return "Invalid Base64 encoding at index $key.";
                }

                if (!in_array($imageType, $validTypes)) {
                    return "Unsupported format '$imageType' at index $key.";
                }

                $imageSizeKB = strlen($decodedImage) / 1024;

                // Use dynamic max sizes if provided
                $maxAllowed = $maxSizes[$imageType] ?? 50;

                if ($imageSizeKB > $maxAllowed) {
                    return "$imageName Error: $imageType image must be less than {$maxAllowed}KB at index $key.";
                }

            } elseif (isset($existingImg) && str_ends_with($base64Image, $existingImg)) {
                continue;
            } else {
                // Remote image or stored path
                if (
                    str_starts_with($base64Image, "https://assets.craftyart.in") ||
                    str_starts_with($base64Image, HelperController::$mediaUrl)
                ) {
                    continue;
                }

                $imagePath = str_replace(config('filesystems.storage_url'), '', $base64Image);

                if (StorageUtils::exists($imagePath)) {
                    try {
                        $headers = get_headers(HelperController::$mediaUrl . $base64Image, 1);
                        $mimeType = is_array($headers['Content-Type'] ?? null)
                            ? end($headers['Content-Type'])
                            : ($headers['Content-Type'] ?? 'application/octet-stream');

                        $imageSizeKB = (is_array($headers['Content-Length'] ?? null)
                                ? end($headers['Content-Length'])
                                : ($headers['Content-Length'] ?? 0)) / 1024;

                    } catch (\Exception $e) {
                        return "$imageName Error: Could not fetch remote image.";
                    }

                    // Determine extension from MIME
                    $ext = match ($mimeType) {
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                        'image/png' => 'png',
                        'image/jpeg', 'image/jpg' => 'jpg',
                        'image/svg+xml', 'image/svg' => 'svg',
                        default => 'unknown',
                    };

                    $maxAllowed = $maxSizes[$ext] ?? 50;
                    if ($imageSizeKB > $maxAllowed) {
                        return "$imageName Error: $ext image size must be less than {$maxAllowed} KB.";
                    }
                }
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
                    'src' => str_replace(HelperController::$mediaUrl,'',$base64String),
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height']
                ];
            }
        } catch (\Exception $e) {
            throw new \Exception("Image processing failed: " . $e->getMessage());
        }
    }

    public static function getImageSizeFromUrl($url): ?array
    {
        if (Str::endsWith($url, '.svg')) {
            $imageUrl = self::getStorageLink($url);
            $svgContent = file_get_contents($imageUrl);
            [$width, $height] = self::extractSvgDimensions($svgContent);
            return [
                'width' => $width,
                'height' => $height
            ];
        } else {
            $size = @getimagesize($url);
            if ($size) {
                return [
                    'width' => $size[0],
                    'height' => $size[1]
                ];
            }
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


    // public static function processBase64Image($base64String, $directory = 'uploadedFiles/cta_images/')
    // {
    //   try {
    //     // Validate base64 string format
    //     if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64String, $matches)) {
    //       $extension = explode('+', $matches[1])[0]; // Handle cases like 'svg+xml'
    //       $imageData = base64_decode(substr($base64String, strpos($base64String, ',') + 1));

    //       if ($imageData === false) {
    //         return ['src' => null, 'width' => null, 'height' => null];
    //       }

    //       // Get image dimensions
    //       $imageSize = getimagesizefromstring($imageData);
    //       $width = $imageSize[0] ?? null;
    //       $height = $imageSize[1] ?? null;

    //       // Generate unique image name
    //       $imageName = Str::random(40) . '.' . $extension;
    //       $imagePath = $directory . $imageName;
    //       StorageUtils::put($imagePath, $imageData);
    //       return [
    //         'src' => $imagePath, // Generate public URL
    //         'width' => $width,
    //         'height' => $height
    //       ];
    //     } else {
    //       echo $base64String;
    //       $imagePath = asset($base64String);
    //       if (!file_exists($imagePath)) {
    //         return ['src' => $base64String, 'width' => null, 'height' => null];
    //       }
    //       list($width, $height) = getimagesize($imagePath);
    //       return [
    //         'src' => $base64String, // Generate public URL
    //         'width' => $width,
    //         'height' => $height
    //       ];
    //     }
    //   } catch (\Exception $e) {
    //     return [
    //       'src' => $base64String,
    //       'width' => null,
    //       'height' => null,
    //       'error' => $e->getMessage()
    //     ];
    //   }
    // }

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


    public static function saveImageToPath($image, $pathName): string|null
    {
        if (!isset($image)) return null;

        if (preg_match('/^data:image\/([a-zA-Z0-9+]+);base64,/', $image, $matches)) {
            $mimeType = strtolower($matches[1]);
            $extension = match ($mimeType) {
                'jpeg' => 'jpg',
                'svg+xml' => 'svg',
                default => $mimeType
            };
            $imageData = base64_decode(substr($image, strpos($image, ',') + 1));
            $filePath = "{$pathName}.{$extension}";
            StorageUtils::put($filePath, $imageData);
            return $filePath;
        } else {
            return str_replace(env('STORAGE_URL'), '', $image);
        }
    }

    public static function validateCanonicalLink($canonicalLink,$type,$idName): ?string
    {
       if(isset($canonicalLink)){
            if(!RoleManager::isAdminOrSeoManager(Auth::user()->user_type)){
                return "You have no access to modify Canonical link";
            }
            if(!str_starts_with($canonicalLink, HelperController::$frontendUrl)) {
                return  "Canonical link must be start with ".HelperController::$frontendUrl;
            }
            if(self::getFrontendPageUrl($type,$idName) == rtrim($canonicalLink, '/')) {
                return "Canonical link cannot be same as page url";
            }
       }
       return null;
    }

    public static function getFrontendPageUrl($type, $slug): string
    {
        if ($type == 0) {
            return HelperController::$frontendUrl . 'templates/p/' . $slug;
        } else if ($type == 1) {
            return HelperController::$frontendUrl . 'templates/' . $slug;
        } else if ($type == 2) {
            return HelperController::$frontendUrl.$slug;
        } else if ($type == 3) {
            return HelperController::$frontendUrl . 'k/' . $slug;
        }
        return HelperController::$frontendUrl;
    }

    public static function extractBase64Dimensions($base64String): ?array
    {
        try {
            if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64String, $matches)) {
                $extension = explode('+', $matches[1])[0];
                $imageData = base64_decode(substr($base64String, strpos($base64String, ',') + 1));

                if ($imageData === false) {
                    return null;
                }

                if ($extension === 'svg') {
                    [$width, $height] = self::extractSvgDimensions($imageData);
                } else {
                    $imageSize = getimagesizefromstring($imageData);
                    $width = $imageSize[0] ?? null;
                    $height = $imageSize[1] ?? null;
                }

                return [
                    'width' => $width,
                    'height' => $height,
                    'extension' => $extension
                ];
            } else {
                // Non-base64 (existing image URL)
                $dimensions = self::getImageSizeFromUrl($base64String);
                return [
                    'width' => $dimensions['width'] ?? null,
                    'height' => $dimensions['height'] ?? null
                ];
            }
        } catch (\Exception $e) {
            throw new \Exception("Dimension extraction failed: " . $e->getMessage());
        }
    }

}

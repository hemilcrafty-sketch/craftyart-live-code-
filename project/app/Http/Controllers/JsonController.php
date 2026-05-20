<?php

namespace App\Http\Controllers;

use App\Models\BgMode;
use App\Models\ResizeMode;
use App\Models\StickerMode;
use App\Models\Template;
use App\Models\Design;
use App\Models\Category;
use App\Models\AppCategory;
use App\Models\TextAlignment;
use Exception;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use App\Http\Controllers\Utils\StorageUtils;
use DB;
use Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class JsonController extends AppBaseController
{

    public $st_images = array();

    public function index()
    {

    }

    public function create(Request $request)
    {
        $datas['cat'] = Category::all();
        $datas['apps'] = AppCategory::all();
        return view('json/create_json')->with('datas', $datas);
    }

    public function store(Request $request)
    {

        $currentuserid = Auth::user()->id;
        set_time_limit(240);

        $messages = ["attachments.max" => "file can't be more than 3."];

        $this->validate($request, [
            'st_image.*' => 'required|image|mimes:jpg,png,gif|max:200048',
            'st_image' => 'max:200',
        ], $messages);

        $this->validate($request, ['json_file' => 'required']);

        $json_file = $request->file('json_file');
        if ($json_file->getClientOriginalExtension() != "json") {
            return response()->json([
                'error' => 'Json extension is invalid.'
            ]);
        }

        $this->st_images = $request->file('st_image');
        $st_images2 = $request->file('st_image');
        $json_object = json_decode($json_file->getContent(), true);

        $res = new Design();

        $res->string_id = $this->generateId();
        $res->emp_id = $currentuserid;

        $mainWidth = $json_object['width'];
        $mainHeight = $json_object['height'];

        $res->app_id = $request->input('app_id');
        $res->post_name = pathinfo($json_file->getClientOriginalName(), PATHINFO_FILENAME);

        $tempRatio = $mainWidth / $mainHeight;
        $tempRatio = round($tempRatio, 2);

        $res->ratio = $tempRatio;

        $res->width = $mainWidth;
        $res->height = $mainHeight;
        $res->status = 0;
        $res->is_premium = 0;

        $thumbArray = [];

        $thumb_path = $this->getThumbPath($json_object);
        if ($thumb_path == null) {
            return response()->json([
                'error' => 'Thumb file is invalid.'
            ]);
        }
        $thumbArray[] = $thumb_path;

        $res->post_thumb = $thumb_path;
        $res->thumb_array = json_encode($thumbArray);
        $i = 0;

        $layers = $json_object['layers'];
        $layer_count = count($layers);
        $sizeOfTemp = 0;

        foreach ($st_images2 as $st_image) {
            if (round($st_image->getSize() / 1024, 2) > 6242880) {
                return response()->json([
                    'error' => $st_image->getClientOriginalName() + " File is size is grater then 6 KB"
                ]);
            }
            $sizeOfTemp = $sizeOfTemp + $st_image->getSize();

        }
        \Log::info("Total Template Size : " . $sizeOfTemp);
        if ($sizeOfTemp > 5242880) {
            return response()->json([
                'error' => "Template size must be below 5MB"
            ]);
        }

        $bgDataAdjust['black_point'] = 1.0;
        $bgDataAdjust['brightness'] = 1.0;
        $bgDataAdjust['brilliance'] = 1.0;
        $bgDataAdjust['contrast'] = 1.0;
        $bgDataAdjust['exposure'] = 1.0;
        $bgDataAdjust['highlight'] = 1.0;
        $bgDataAdjust['saturation'] = 1.0;
        $bgDataAdjust['shadow'] = 1.0;
        $bgDataAdjust['sharpness'] = 1.0;
        $bgDataAdjust['tint'] = 1.0;
        $bgDataAdjust['vibrance'] = 1.0;
        $bgDataAdjust['blur'] = 0;
        $bgDataFilter['intensity'] = 1.0;
        $bgDataFilter['type'] = 0;

        $bgDataCrop['top'] = 0;
        $bgDataCrop['bottom'] = 0;
        $bgDataCrop['left'] = 0;
        $bgDataCrop['right'] = 0;

        $bgDataFlip['h'] = false;
        $bgDataFlip['v'] = false;

        $bgData = [];
        $designData = array();
        $layersData = array();
        $errorMessage = [];
        foreach ($layers as $layer) {
            $type = $layer['type'];
            if ($i == 0) {

                $bgData['layerType'] = 0;
                $bgData['thumb'] = $thumb_path;
                $image = $this->getBgPath($layer, true);
                if ($image == null) {
                    return response()->json([
                        'error' => 'bg file is invalid.'
                    ]);
                }
                $bgData['image'] = $image;
                $bgData['color'] = null;
                $bgData['width'] = 100;
                $bgData['height'] = 100;
                $bgData['type'] = 0;
                $bgData['gradAngle'] = 0;
                $bgData['gradRatio'] = 0;
                $bgData['animation'] = 0;
                $bgData['videoStartTime'] = 0;
                $bgData['videoEndTime'] = 0;
                $bgData['adjustment'] = $bgDataAdjust;
                $bgData['filter'] = $bgDataFilter;
                $bgData['crop'] = $bgDataCrop;
                $bgData['flip'] = $bgDataFlip;

            } else {
                if ($type == 'LayerKind.TEXT') {
                    $dd = $this->getTextData($layer, $mainWidth, $mainHeight);
                    if (isset($dd['layerType'])) {
                        $layersData[] = $dd;
                    }
                } else {
                    $dd = $this->getStickerData($layer, $mainWidth, $mainHeight, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip);
                    if (isset($dd['layerType'])) {
                        $layersData[] = $dd;
                    }
                }
            }
            $i++;
        }

        $bgData['layers'] = $layersData;
        $designData[] = $bgData;

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.json';
        $filePath = 'uploadedFiles/fab_jsons/' . $new_name;
        StorageUtils::put($filePath, json_encode($designData));
        $res->designs = $filePath;
        $res->size = $sizeOfTemp;

        if ($mainWidth == $mainHeight) {
            $res->orientation = "square";
        } else if ($mainWidth < $mainHeight) {
            $res->orientation = "portrait";
        } else {
            $res->orientation = "landscape";
        }

        // if( isset($errorMessage) && !empty($errorMessage) ){
        //     return  response()->json([
        //         'error' => $errorMessage['message']
        //     ]);
        // }

        try{
            $res->save();
        }catch (Exception $e){
            return response()->json([
                "error"=> $e
            ]);
        }


        $dddd = count($st_images2);
        return response()->json([
            'success' => $dddd
        ]);
    }

    public function getThumbPath($layer): ?string
    {

        $bgPath = $layer['templatePreview'];
        $thumbFile = new \SplFileInfo($bgPath);

        if ($thumbFile->getExtension() == 'png') {
            return null;
        }

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $thumbFile->getExtension();
        foreach ($this->st_images as $key => $file) {
            $fileName = $file->getClientOriginalName();
            if ($fileName == $thumbFile->getFilename()) {
                StorageUtils::storeAs($file, 'uploadedFiles/thumb_file', $new_name);
                array_shift($this->st_images);
                return 'uploadedFiles/thumb_file/' . $new_name;
            }
        }

        return null;
    }

    public function getBgPath($layer): ?string
    {

        $bgPath = $layer['src'];
        $bgFile = new \SplFileInfo($bgPath);

        if ($bgFile->getExtension() == 'png') {
            return null;
        }

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $bgFile->getExtension();

        foreach ($this->st_images as $key => $file) {
            $fileName = $file->getClientOriginalName();
            if ($fileName == $bgFile->getFilename()) {
                StorageUtils::storeAs($file, 'uploadedFiles/bg_file', $new_name);
                array_shift($this->st_images);
                return 'uploadedFiles/bg_file/' . $new_name;
            }
        }
        return null;
    }

    public function getTextData($layer, $mainWidth, $mainHeight)
    {

        $layerText['layerType'] = 2;
        $layerText['isVideo'] = false;
        $layerText['text'] = $layer['text'];
        $layerText['font'] = $layer['fontName'];
        $layerText['width'] = $this->getPrSize($mainWidth, $layer['width']);
        $layerText['height'] = $this->getPrSize($mainHeight, $layer['height']);
        $layerText['left'] = $this->getPrSize($mainWidth, $layer['x']);
        $layerText['top'] = $this->getPrSize($mainHeight, $layer['y']);
        $layerText['originX'] = 'left';
        $layerText['originY'] = 'top';
        $layerText['size'] = $layer['size'];
        $layerText['color'] = $layer['fontColor'];
        $layerText['opacity'] = 100;
        $layerText['rotation'] = $layer['rotation'];
        $layerText['isEditable'] = 0;
        $layerText['isUrl'] = 0;
        $layerText['editableTitle'] = "";
        $layerText['editableId'] = null;
        $layerText['curve'] = $layer['warpBend'];
        $layerText['update'] = true;

        if ($layer['justification'] == 'Justification.LEFT') {
            $alignment = '2';
        } else if ($layer['justification'] == 'Justification.CENTER') {
            $alignment = '4';
        } else {
            $alignment = '3';
        }
        $textAlignment = TextAlignment::where('value', $alignment)->first();
        $layerTextFormat['alignment'] = $alignment;

        $layerTextFormat['textAlign'] = ($textAlignment) ? $textAlignment->stringVal : '';
        $layerTextFormat['bold'] = false;
        $layerTextFormat['italic'] = false;
        $layerTextFormat['capital'] = false;
        $layerTextFormat['underline'] = false;
        $layerTextFormat['bulletSpan'] = false;
        $layerTextFormat['numericSpan'] = false;
        $layerText['format'] = $layerTextFormat;

        $layerTextSpacing['anchor'] = '1';
        $layerTextSpacing['letter'] = $layer['wordSpace'];
        $layerTextSpacing['line'] = $layer['heightSpace'] == "Auto" ? 0 : $layer['heightSpace'];
        try {
            $layerTextSpacing['lineMultiplier'] = $layer['lineSpaceMultiplier'];
        } catch (\Exception $e) {
            $layerTextSpacing['lineMultiplier'] = 1;
        }
        $layerText['spacing'] = $layerTextSpacing;

        if ($layer['hasLayerEffect']) {
            $layerText['effects'] = json_encode($layer['layerEffects']);
        } else {
            $layerText['effects'] = null;
        }
        return $layerText;
    }

    public function getStickerData($layer, $mainWidth, $mainHeight, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip)
    {

        $bgPath = $layer['src'];
        $stickerFile = new \SplFileInfo($bgPath);

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $stickerFile->getExtension();

        $stickerLayer = array();
        foreach ($this->st_images as $file) {
            $fileName = $file->getClientOriginalName();
            if ($fileName == $stickerFile->getFilename()) {
                StorageUtils::storeAs($file, 'uploadedFiles/sticker_file', $new_name);
                $toPath = 'uploadedFiles/sticker_file/' . $new_name;
                array_shift($this->st_images);
                $stickerLayer['layerType'] = 1;
                $stickerLayer['isVideo'] = false;
                $stickerLayer['image'] = $toPath;
                $stickerLayer['left'] = $this->getPrSize($mainWidth, $layer['x']);
                $stickerLayer['top'] = $this->getPrSize($mainHeight, $layer['y']);
                $stickerLayer['originX'] = 'left';
                $stickerLayer['originY'] = 'top';
                $stickerLayer['width'] = $this->getPrSize($mainWidth, $layer['width']);
                $stickerLayer['height'] = $this->getPrSize($mainHeight, $layer['height']);
                $stickerLayer['isEditable'] = 0;
                $stickerLayer['isUrl'] = 0;
                $stickerLayer['editableTitle'] = "";
                $stickerLayer['editableId'] = null;
                $stickerLayer['rotation'] = 0;
                $stickerLayer['opacity'] = 100;
                $stickerLayer['type'] = 0;
                $stickerLayer['color'] = null;
                $stickerLayer['resizeType'] = 0;
                $stickerLayer['lockType'] = 0;
                $stickerLayer['animation'] = 0;
                $stickerLayer['adjustment'] = $bgDataAdjust;
                $stickerLayer['filter'] = $bgDataFilter;
                $stickerLayer['crop'] = $bgDataCrop;
                $stickerLayer['flip'] = $bgDataFlip;
                $stickerLayer['videoStartTime'] = 0;
                $stickerLayer['videoEndTime'] = 0;
                break;
            }
        }
        return $stickerLayer;
    }


    public function getPrSize($mainSize, $objectSize)
    {
        return (($objectSize * 100) / $mainSize);
    }

    public function gcd($width, $height)
    {
        if ($height == 0)
            return $width;
        else
            return $this->gcd($height, $width % $height);
    }

    public function ratio($width, $height): string
    {
        $gcd = $this->gcd($width, $height);
        //        if ($width < $height) {
        return $this->showAnswer($width / $gcd, $height / $gcd);
        //        } else {
//            return $this->showAnswer($height / $gcd, $width / $gcd);
//        }
    }

    public function showAnswer($a, $b): string
    {
        return $a . ':' . $b;
    }

    public static function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (Design::where('string_id', $string_id)->exists());
        return $string_id;
    }
}
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
use DB;
use Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Auth;

class JsonPageController extends AppBaseController
{

    public $st_images = array();

    public function index()
    {

    }

    public function import_page(Request $request)
    {
        set_time_limit(240);
        
        $messages = ["attachments.max" => "file can't be more than 3." ];

        $this->validate($request, [
                'st_image.*' => 'required|image|mimes:jpg,png,gif|max:200048',
                'st_image' => 'max:200',
            ],$messages);

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

        $mainWidth = $json_object['width'];
        $mainHeight = $json_object['height'];

        $i = 0;

        $layers = $json_object['layers'];

        $layer_count = count($layers);

        $sizeOfTemp = 0;

        foreach ($st_images2 as $st_image) {
            $sizeOfTemp = $sizeOfTemp + $st_image->getSize();
        }

        if($sizeOfTemp > 5242880) {
            return response()->json([
                'error' => "Template size must be below 5MB"
            ]);
        }

        $thumb_path = $this->getThumbPath($json_object);
        if ($thumb_path == null) {
            return response()->json([
                'error' => 'Thumb file is invalid.'
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
        foreach ($layers as $layer) {
            $type = $layer['type'];
            if ($i == 0) {
                $bgData['layerType'] = 0;
                $bgData['thumb'] = $thumb_path;
                $image = $this->getBgPath($layer);
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

        return response()->json([
            'success' => $bgData
        ]);
    }

    public function getThumbPath($layer): ?string
    {
        $bgPath = $layer['templatePreview'];
        $thumbFile = new  \SplFileInfo($bgPath);

        if ($thumbFile->getExtension() == 'png') {
            return null;
        }

        return $thumbFile->getFilename();
    }

    public function getBgPath($layer): ?string
    {
        $bgPath = $layer['src'];
        $bgFile = new  \SplFileInfo($bgPath);

        if ($bgFile->getExtension() == 'png') {
            return null;
        }

        return $bgFile->getFilename();
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
            $alignment = 2;
        } else if ($layer['justification'] == 'Justification.CENTER') {
            $alignment = 4;
        } else {
            $alignment = 3;
        }
        $layerTextFormat['alignment'] = $alignment;
        $layerTextFormat['textAlign'] = TextAlignment::where('value', $alignment)->first()->stringVal;
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

        if($layer['hasLayerEffect']) {
            $layerText['Effects'] = json_encode($layer['layerEffects']);
        } else {
            $layerText['Effects'] = "";
        }
        return $layerText;
    }

    public function getStickerData($layer, $mainWidth, $mainHeight, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip)
    {

        $bgPath = $layer['src'];
        $stickerFile = new  \SplFileInfo($bgPath);

        $stickerLayer = array();
        $stickerLayer['layerType'] = 1;
        $stickerLayer['isVideo'] = false;
        $stickerLayer['image'] = $stickerFile->getFilename();
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
        $stickerLayer['color'] = "";
        $stickerLayer['resizeType'] = 0;
        $stickerLayer['lockType'] =0;
        $stickerLayer['animation'] = 0;
        $stickerLayer['adjustment'] = $bgDataAdjust;
        $stickerLayer['filter'] = $bgDataFilter;
        $stickerLayer['crop'] = $bgDataCrop;
        $stickerLayer['flip'] = $bgDataFlip;
        $stickerLayer['videoStartTime'] = 0;
        $stickerLayer['videoEndTime'] = 0;

        return $stickerLayer;
    }

    public function getPrSize($mainSize, $objectSize)
    {
        return (($objectSize * 100) / $mainSize);
    }

    public function gcd($width, $height)
    {
        if ($height == 0) return $width;
        else return $this->gcd($height, $width % $height);
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
}

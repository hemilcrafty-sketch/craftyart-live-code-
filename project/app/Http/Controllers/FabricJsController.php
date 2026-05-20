<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Design;


class FabricJsController extends Controller
{

    public static function getPosterDetail(Request $request, $forceV3 = false, $showAll = false, $forceReset___ = false)
    {

    	$fromFabricV3 = $request->has('fromFabricV3');
    	if (!$fromFabricV3) {
    		$fromFabricV3 = $forceV3;
    	}
    	$template_id = $request->get('template_id');
    	if (!$template_id) {
    		$template_id = $request->get('i');
    		if (!$template_id) {
	    		$template_id = $request->get('id');
	    	}
    	}

    	$forceReset = false;
    	if (Str::contains($template_id, '?force=1') && $forceReset___) {
    		$forceReset = true;
    		$template_id = Str::replace('?force=1', '', $template_id);
    	}

        $expression = "=";
	    $condition = "1";
	    if($showAll) {
	    	$expression = "!=";
	    	$condition = "-1";
	    }

        $fieldName = is_numeric($template_id) ? "id" : "id_name";
        $itemData = Design::where($fieldName, $template_id)->where('status', $expression, $condition)->first();

        if ($itemData == null && !is_numeric($template_id)) {
        	$itemData = Design::where('string_id', $template_id)->where('status', $expression, $condition)->first();
        	if ($itemData == null && !is_numeric($template_id)) {
	        	$parts = explode("-", $template_id);
				$firstWord = $parts[0];
				$itemData = Design::where('string_id', $firstWord)->where('status', $expression, $condition)->first();
	        }
        }

        if ($itemData != null) {

        	if (!$forceReset && $fromFabricV3 && $itemData->fab_designs != null) {

        		$jsonData = json_decode($itemData->fab_designs);
				if (!$jsonData) {
					$jsonData = json_decode(StorageUtils::get($itemData->fab_designs));
				}

				if ($showAll) {
					$response['id'] = $itemData->id;
					$response['data'] = $jsonData;
					$response['string_id'] = $itemData->string_id;
					return $response;
				}
				return $jsonData;
        	} else {
        		$jsonData = json_decode($itemData->designs);
				if (!$jsonData) {
					$jsonData = json_decode(StorageUtils::get($itemData->designs));
				}

	            $fabData["name"] = $itemData->post_name;
	            $fabData["frame"] = array('width' => $itemData->width, 'height' => $itemData->height);

				$pagesData = array();
				foreach ($jsonData as $index => $design) {
					$pagesData[] = FabricJsController::getPage($index, $design, $itemData, $fromFabricV3);
				}

				$fabData["pages"] = $pagesData;

				if ($showAll) {
					$response['id'] = $itemData->id;
					$response['data'] = $fabData;
                    $response['string_id'] = $itemData->string_id;
					return $response;
				} else {
					return $fabData;
				}
        	}
        } else {
            return null;
        }
    }

    public static function getPage($index, $design, $itemData, $fromFabricV3): array
    {

    	$data["id"] = HelperController::generateID($index);
		$data["name"] = "Page ".$index + 1;
		$objectsData = array();

		$objectsData[] = FabricJsController::getBgLayer($design, $itemData);

		foreach ($design->layers as $index_ => $singleLayer) {
			if ($singleLayer->layerType == 1) {
				$objectsData[] = FabricJsController::getStickerLayer($index_, $singleLayer, $itemData);
			} else {
				$objectsData[] = FabricJsController::getTextLayer($index_, $singleLayer, $itemData, $fromFabricV3);
			}
		}

		$data["objects"] = $objectsData;
    	return $data;
    }

    public static function getBgLayer($design, $itemData): array
    {

    	$layer["id"] = HelperController::generateID();
		$layer["angle"] = 0;
		$layer["stroke"] = null;
		$layer["strokeWidth"] = 0;
		$layer["left"] = 0;
		$layer["top"] = 0;
		$layer["width"] = $itemData->width;
		$layer["height"] = $itemData->height;
		$layer["opacity"] = 1;
		$layer["originX"] = "left";
		$layer["originY"] = "top";
		$layer["scaleX"] = 1;
		$layer["scaleY"] = 1;
		$layer["type"] = "Background";
		$layer["flipX"] = $design->flip->v;
		$layer["flipY"] = $design->flip->h;
		$layer["skewX"] = 0;
		$layer["skewY"] = 0;
		$layer["visible"] = true;

		try {
            $layer["fill"] = $design->color == null ? "#ffffff" : $design->color;
        } catch (\Exception $e) {
        	$layer["fill"] = "#ffffff";
        }

        try {
            $layer["src"] = $design->image != null ? HelperController::$mediaUrl . $design->image : "";
        } catch (\Exception $e) {
        	$layer["src"] = "";
        }

		$layer["metadata"] = json_decode("{}");
    	return $layer;
    }

    public static function getStickerLayer($index, $singleLayer, $itemData): array
    {

    	$layer["id"] = HelperController::generateID($index);
		$layer["angle"] = (float) $singleLayer->rotation;
		$layer["stroke"] = null;
		$layer["strokeWidth"] = 0;
		$layer["left"] = FabricJsController::getPrSize($itemData->width, $singleLayer->left);
		$layer["top"] = FabricJsController::getPrSize($itemData->height, $singleLayer->top);
		$layer["width"] = FabricJsController::getPrSize($itemData->width, $singleLayer->width);
		$layer["height"] = FabricJsController::getPrSize($itemData->height, $singleLayer->height);
		$layer["opacity"] = $singleLayer->opacity / 100;
		$layer["originX"] = "left";
		$layer["originY"] = "top";
		$layer["scaleX"] = 1;
		$layer["scaleY"] = 1;
		$layer["type"] = "StaticImage";
		$layer["flipX"] = $singleLayer->flip->v;
		$layer["flipY"] = $singleLayer->flip->h;
		$layer["skewX"] = 0;
		$layer["skewY"] = 0;
		$layer["visible"] = true;
		$layer["shadow"] = null;
		$layer["src"] =HelperController::$mediaUrl . $singleLayer->image;
		$layer["cropX"] = 0;
		$layer["cropY"] = 0;
		$layer["metadata"] = json_decode("{}");
		$layer["layerType"] = $singleLayer->type;
    	return $layer;
    }

    public static function getTextLayer($index, $singleLayer, $itemData, $fromFabricV3): array
    {

    	$familyName = null;

		$fontData = DB::table('fonts')->where('path', "uploadedFiles/font_file/" . $singleLayer->font)->first();
		if (!$fontData) {
			$fontData = DB::table('fonts')->where('name', FabricJsController::getFileNameWithoutExtension(basename($singleLayer->font)))->first();
		}
		if ($fontData) {
			$familyName = $fontData->fontFamily;
		}

    	$layer["id"] = HelperController::generateID($index);
		$layer["angle"] = (float) $singleLayer->rotation;
		$layer["stroke"] = null;
		$layer["strokeWidth"] = 0;
		$layer["left"] = FabricJsController::getPrSize($itemData->width, $singleLayer->left);
		$layer["top"] = FabricJsController::getPrSize($itemData->height, $singleLayer->top);
		$layer["width"] = FabricJsController::getPrSize($itemData->width, $singleLayer->width);
		$layer["height"] = FabricJsController::getPrSize($itemData->height, $singleLayer->height);
		$layer["opacity"] = $singleLayer->opacity / 100;
		$layer["originX"] = "left";
		$layer["originY"] = "top";
		$layer["scaleX"] = 1;
		$layer["scaleY"] = 1;
		$layer["type"] = "StaticText";
		$layer["flipX"] = false;
		$layer["flipY"] = false;
		$layer["skewX"] = 0;
		$layer["skewY"] = 0;
		$layer["visible"] = true;
		$layer["shadow"] = null;
		$layer["charSpacing"] = $singleLayer->spacing->letter;
		$layer["fill"] = $singleLayer->color;
		$layer["fontName"] = $singleLayer->font;
		if ($fromFabricV3 && $familyName) {
			$layer["fontFamily"] = trim($familyName);
		}
		$layer["fontSize"] = $singleLayer->size;
		$layer["fontWeight"] = "normal";
		$layer["lineHeight"] = 1.16;
		$layer["shapeType"] ="Normal";
		$layer["shapeValue"] = 0;
		$layer["text"] = $singleLayer->text;
		if (isset($singleLayer->format->textAlign)) {
			$layer["textAlign"] = $singleLayer->format->textAlign;
		}
		$layer["fontURL"] = HelperController::$mediaUrl . 'uploadedFiles/font_file/' . $singleLayer->font;

		$font['name'] = $familyName;
		$font['url'] = HelperController::$mediaUrl . 'uploadedFiles/font_file/' . $singleLayer->font;
		$layer["fontDatas"] = $font;
		$layer["metadata"] = json_decode("{}");
		$layer["calFirstTime"] = true;

    	return $layer;
    }

    public static function getPrSize($mainSize, $objectSize): float|int
    {
        return (($objectSize / 100) * $mainSize);
    }

    private static function getFileNameWithoutExtension($filename) {
        return substr($filename, 0, strrpos($filename, '.'));
    }

}


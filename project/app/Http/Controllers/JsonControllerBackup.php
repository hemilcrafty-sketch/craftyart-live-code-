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

class JsonControllerBackup extends Controller
{

    public $st_images = array();

    public function __construct()
    {
        $this->middleware('auth');
    }

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

        // if($currentuserid == 1) {
        //     return $this->store2($request);
        // }

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

        $res = new Template();

        $res->emp_id = $currentuserid;

        $mainWidth = $json_object['width'];
        $mainHeight = $json_object['height'];

        $res->app_id = $request->input('app_id');
        // $res->category_id = $request->input('category_id');
        $res->bg_id = 0;
        $res->post_name = pathinfo($json_file->getClientOriginalName(), PATHINFO_FILENAME);

        $res->ratio = $this->ratio($mainWidth, $mainHeight);
        $res->width = $mainWidth;
        $res->height = $mainHeight;
        $res->status = $request->input('status');
        $res->is_premium = $request->input('is_premium');

        $res->post_thumb = $this->getThumbPath($json_object);

        $i = 0;

        $sticker_data = array();
        $text_data = array();

        $layers = $json_object['layers'];

        $layer_count = count($layers);

        $sizeOfTemp = 0;

        foreach ($st_images2 as $st_image) {
            $sizeOfTemp = $sizeOfTemp + $st_image->getSize();
        }

        if ($sizeOfTemp > 3145728) {
            return response()->json([
                'error' => "Template size must be below 3MB"
            ]);
        }



        foreach ($layers as $layer) {
            $type = $layer['type'];
            if ($i == 0) {
                $res->back_image = $this->getBgPath($layer);
                $res->back_image_type = '0';
                $res->grad_angle = '0';
                $res->grad_ratio = '0';
                $res->back_color = null;
            } else {
                if ($type == 'LayerKind.TEXT') {
                    $text_data[] = $this->getTextData($layer, $i, $mainWidth, $mainHeight);
                } else {
                    $sticker_data[] = $this->getStickerData($layer, $i, $mainWidth, $mainHeight);
                }
            }

            $i++;
        }

        $res->component_info = json_encode($sticker_data);
        $res->text_info = json_encode($text_data);

        $res->size = $sizeOfTemp;

        $res->save();
        // array_shift($this->st_images);

        $dddd = count($st_images2);
        return response()->json([
            'success' => $dddd
        ]);

        //post_thumb

    }

    public function store2(Request $request)
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

        $images = $request->file('st_image');

        $sizeOfTemp = 0;

        foreach ($images as $st_image) {
            $sizeOfTemp = $sizeOfTemp + $st_image->getSize();
        }

        if ($sizeOfTemp > 3145728) {
            return response()->json([
                'error' => "Template size must be below 3MB"
            ]);
        }

        $json_object = json_decode($json_file->getContent(), true);

        $res = new Design();

        $res->emp_id = $currentuserid;

        $mainWidth = $json_object['width'];
        $mainHeight = $json_object['height'];

        $res->app_id = $request->input('app_id');
        $res->bg_id = 0;
        $res->post_name = pathinfo($json_file->getClientOriginalName(), PATHINFO_FILENAME);
        $post_thumb_path = $this->getThumbPath($json_object);
        $res->post_thumb = $post_thumb_path;

        $res->ratio = $this->ratio($mainWidth, $mainHeight);
        $res->width = $mainWidth;
        $res->height = $mainHeight;
        $res->status = $request->input('status');
        $res->is_premium = $request->input('is_premium');
        $res->size = $sizeOfTemp;

        $layers = $json_object['layers'];

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

        $designData = array();
        $i = 0;
        foreach ($layers as $layer) {
            $bgData['layerType'] = 0;
            if ($i == 0) {
                $bgData['thumb'] = $post_thumb_path;
            } else {
                $bgData['thumb'] = $this->getThumbPath($layer);
            }
            $bgData['image'] = $this->getBgPath($layer);
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

            $layerSet = $layer['layersSet'];
            $layersData = array();
            foreach ($layerSet as $subLayer) {
                $type = $subLayer['type'];
                if ($type == 'LayerKind.TEXT') {
                    $layersData[] = $this->getTextData2($subLayer, $mainWidth, $mainHeight);
                } else {
                    $layersData[] = $this->getStickerData2($subLayer, $mainWidth, $mainHeight, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip);
                }
            }
            $bgData['layers'] = $layersData;
            $designData[] = $bgData;
            $i++;
        }

        return response()->json([
            'success' => json_encode($designData)
        ]);

    }

    public function getStickerData($layer, $i, $mainWidth, $mainHeight)
    {


        $bgPath = $layer['src'];
        $stickerFile = new \SplFileInfo($bgPath);

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $stickerFile->getExtension();

        $st_data = array();
        $key = 0;
        foreach ($this->st_images as $file) {
            $fileName = $file->getClientOriginalName();
            if ($fileName == $stickerFile->getFilename()) {
                StorageUtils::storeAs($file, 'uploadedFiles/sticker_file', $new_name);
                $toPath = 'uploadedFiles/sticker_file/' . $new_name;
                array_shift($this->st_images);
                $st_data['st_image'] = $toPath;
                $st_data['st_x_pos'] = $this->getPrSize($mainWidth, $layer['x']);
                $st_data['st_y_pos'] = $this->getPrSize($mainHeight, $layer['y']);
                $st_data['st_width'] = $this->getPrSize($mainWidth, $layer['width']);
                $st_data['st_height'] = $this->getPrSize($mainHeight, $layer['height']);
                $st_data['st_rotation'] = '0';
                $st_data['st_opacity'] = '100';
                $st_data['st_type'] = '0';
                $st_data['st_color'] = null;
                $st_data['st_resize'] = '0';
                $st_data['st_cat'] = '0';
                $st_data['st_order'] = $i;
                break;
            }
            $key++;
        }
        return $st_data;
    }

    public function getStickerData2($layer, $mainWidth, $mainHeight, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip)
    {

        $bgPath = $layer['src'];
        $stickerFile = new \SplFileInfo($bgPath);

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $stickerFile->getExtension();

        $stickerLayer = array();
        foreach ($this->st_images as $file) {
            $fileName = $file->getClientOriginalName();
            if ($fileName == $stickerFile->getFilename()) {
                // $file->storeAs('uploadedFiles/sticker_file', $new_name, 'public');
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

    public function getTextData($layer, $i, $mainWidth, $mainHeight)
    {

        $txt_data = array();
        $txt_data['text'] = $layer['text'];
        $txt_data['font_family'] = $layer['fontName'];

        if ($layer['justification'] == 'Justification.LEFT') {
            $txt_align = '2';
        } else if ($layer['justification'] == 'Justification.CENTER') {
            $txt_align = '4';
        } else {
            $txt_align = '3';
        }

        $txt_data['txt_align'] = $txt_align;
        $txt_data['is_editable'] = '0';

        $txt_data['txt_size'] = $layer['size'];
        $txt_data['txt_color'] = $layer['fontColor'];
        $txt_data['txt_color_alpha'] = '100';

        $txt_data['txt_width'] = $this->getPrSize($mainWidth, $layer['width']);
        $txt_data['txt_height'] = $this->getPrSize($mainHeight, $layer['height']);
        $txt_data['txt_x_pos'] = $this->getPrSize($mainWidth, $layer['x']);
        $txt_data['txt_y_pos'] = $this->getPrSize($mainHeight, $layer['y']);
        $txt_data['word_spacing'] = $layer['wordSpace'];
        $txt_data['editable_title'] = "";
        $txt_data['line_spacing'] = $layer['heightSpace'] == "Auto" ? 0 : $layer['heightSpace'];
        try {
            $txt_data['lineSpaceMultiplier'] = $layer['heightSpaceMultiplier'];
        } catch (\Exception $e) {
            try {
                $txt_data['lineSpaceMultiplier'] = $layer['lineSpaceMultiplier'];
            } catch (\Exception $e) {
                $txt_data['lineSpaceMultiplier'] = 1;
            }
        }

        $txt_data['txt_curve'] = $layer['warpBend'];
        $txt_data['txt_rotation'] = $layer['rotation'];
        $txt_data['txt_opacity'] = '100';

        if ($layer['hasLayerEffect']) {
            try {
                $txt_data['layer_effects'] = json_encode($layer['layerEffects']);
            } catch (\Exception $e) {

            }

        }

        $txt_data['txt_order'] = $i;

        return $txt_data;
    }

    public function getTextData2($layer, $mainWidth, $mainHeight)
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
        $layerText['editableTitle'] = "";
        $layerText['curve'] = $layer['warpBend'];

        if ($layer['justification'] == 'Justification.LEFT') {
            $alignment = '2';
        } else if ($layer['justification'] == 'Justification.CENTER') {
            $alignment = '4';
        } else {
            $alignment = '3';
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
            $txt_data['lineMultiplier'] = $layer['lineSpaceMultiplier'];
        } catch (\Exception $e) {
            $txt_data['lineMultiplier'] = 1;
        }
        $layerText['spacing'] = $layerTextSpacing;

        if ($layer['hasLayerEffect']) {
            $layerText['Effects'] = json_encode($layer['layerEffects']);
        } else {
            $layerText['Effects'] = null;
        }
        return $layerText;
    }

    public function getBgPath($layer): ?string
    {
        $bgPath = $layer['src'];
        $bgFile = new \SplFileInfo($bgPath);

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

        //do {
        //} while (!File::exists('uploadedFiles/bg_file/' . $new_name));

        return null;
    }

    public function getThumbPath($layer): ?string
    {
        $bgPath = $layer['templatePreview'];
        $thumbFile = new \SplFileInfo($bgPath);

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

        //do {
        //} while (!File::exists('uploadedFiles/thumb_file/' . $new_name));

        return null;
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
}

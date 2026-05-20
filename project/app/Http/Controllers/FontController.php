<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FontFamily;
use App\Models\FontList;
use App\Models\Font;

class FontController extends ApiController
{
    function getFonts(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

//        if ($this->uid == $this->testingUid) {
//
////            $fontDatas = Font::whereNull('web_path')->get();
////            foreach ($fontDatas as $font) {
////                $filePath = "uploadedFiles/fonts/" . pathinfo($font->path, PATHINFO_FILENAME) . ".woff";
////                if (StorageUtils::exists($filePath)) {
////                    Font::where('id', $font->id)->update(['web_path' => $filePath]);
////                }
////            }
//
//            $fontDatas = FontList::whereNull('web_path')->get();
//            foreach ($fontDatas as $font) {
//                $filePath = "uploadedFiles/fonts/" . pathinfo($font->fontUrl, PATHINFO_FILENAME) . ".woff";
//                if (StorageUtils::exists($filePath)) {
//                    FontList::where('id', $font->id)->update(['web_path' => $filePath]);
//                }
//            }
//            return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, "converted"));
//        }

        if ($request->get('fonts')) return $this->getEditorFont_($request);

        $url = HelperController::$mediaUrl;

        $fontFamilies = FontFamily::where('status', 1)->orderBy('id', 'ASC')->limit(1000)->get();

        $fontFamilyRows = array();
        foreach ($fontFamilies as $fontFamily) {

            $fontLists = FontList::where("fontFamilyId", $fontFamily->id)->where("status", 1)->get();

            if ($fontLists->count() != 0) {
                $fontListRows = array();

                foreach ($fontLists as $font) {
                    $fontPath = $this->getFontPath($font->web_path, $font->fontUrl);
                    $fontListRows[] = array(
                        'familyId' => $font->fontFamilyId,
                        'fontName' => $font->fontName,
                        'fontType' => $font->fontType,
                        'fontUrl' => $url . $fontPath,
                        'fontWeight' => $font->fontWeight,
                        'supportBold' => $font->support_bold == 1,
                        'supportItalic' => $font->support_italic == 1,
                    );
                }

                $fontFamilyRows[] = array(
                    'familyId' => $fontFamily->id,
                    'fontFamily' => $fontFamily->fontFamily,
                    'fontThumb' => $url . $fontFamily->fontThumb,
                    'uniname' => $fontFamily->uniname,
                    'supportType' => $fontFamily->supportType,
                    'isPremium' => $fontFamily->is_premium,
                    'fontList' => $fontListRows,
                    'supportBold' => $fontFamily->support_bold == 1,
                    'supportItalic' => $fontFamily->support_italic == 1,
                );
            }

        }
        if (sizeof($fontFamilyRows) == 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found"));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Data loaded", ['datas' => $fontFamilyRows]));
        }
    }

    private function getEditorFont_(Request $request): array|string
    {

        $url = HelperController::$mediaUrl;

        $fonts = json_decode($request->get('fonts'));

        $fontListRows = array();

        foreach ($fonts as $fontFamily) {

            $fontFamilies = FontFamily::where('fontFamily', trim($fontFamily))->first();
            if (!$fontFamilies) {
                $fontFamilies = FontFamily::where('fontFamily', $fontFamily)->first();
            }
            if ($fontFamilies) {
                $fontLists = FontList::where("fontFamilyId", trim($fontFamilies->id))->where("status", 1)->get();
                if (sizeof($fontLists) > 0) {
                    foreach ($fontLists as $font) {
                        $fontPath = $this->getFontPath($font->web_path, $font->fontUrl);
                        $fontListRows[] = array(
                            'fontName' => trim($fontFamilies->fontFamily),
                            'uniname' => $fontFamilies->uniname,
                            'fontUrl' => $url . $fontPath,
                            'fontWeight' => $font->fontWeight,
                            'supportBold' => $font->support_bold == 1,
                            'supportItalic' => $font->support_italic == 1,
                        );
                    }
                } else {
                    $fontDatas = DB::table('fonts')->where('fontFamily', trim($fontFamily))->first();
                    if (!$fontDatas) {
                        $fontDatas = DB::table('fonts')->where('fontFamily', $fontFamily)->first();
                    }
                    if ($fontDatas) {
                        $fontPath = $this->getFontPath($fontDatas->web_path, $fontDatas->path);
                        $fontListRows[] = array(
                            'fontName' => trim($fontDatas->fontFamily),
                            'fontUrl' => $url . $fontPath,
                            'uniname' => $fontDatas->uniname,
                            'fontWeight' => $fontDatas->fontWeight,
                        );
                    }
                }
            } else {
                $fontDatas = DB::table('fonts')->where('fontFamily', trim($fontFamily))->first();
                if (!$fontDatas) {
                    $fontDatas = DB::table('fonts')->where('fontFamily', $fontFamily)->first();
                }
                if ($fontDatas) {
                    $fontPath = $this->getFontPath($fontDatas->web_path, $fontDatas->path);
                    $fontListRows[] = array(
                        'fontName' => trim($fontDatas->fontFamily),
                        'fontUrl' => $url . $fontPath,
                        'uniname' => $fontDatas->uniname,
                        'fontWeight' => $fontDatas->fontWeight,
                    );
                }
            }
        }

        if (sizeof($fontListRows) == 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Data not found"));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Data loaded", ['datas' => $fontListRows]));
        }
    }

    function checkUnicode(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $fontFamilyName = $request->get('ff');
        if ($fontFamilyName == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        $uniname = "none";

        $fontFamily = FontFamily::where('fontFamily', $fontFamilyName)->first();
        if ($fontFamily) {
            $uniname = $fontFamily->uniname;
        } else {
            $fontFamily = Font::where('fontFamily', $fontFamilyName)->first();
            if ($fontFamily) {
                $uniname = $fontFamily->uniname;
            }
        }

        $allowLang = ["kap", "krutidev"];

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded",
            [
                'isUnicode' => in_array($uniname, $allowLang),
                'uniname' => $uniname
            ]
        ));
    }

    private function getFontPath($webPath, $path)
    {
//        if ($this->uid == $this->testingUid) {
        return $webPath ?? $path;
//        } else {
//            return $path;
//        }
    }
}


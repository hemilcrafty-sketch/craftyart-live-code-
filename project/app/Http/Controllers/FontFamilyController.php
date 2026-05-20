<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\FontFamily;
use App\Models\FontList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FontFamilyController extends AppBaseController
{

    public $fontFamilyDatas = [];
    public $familyCount = 0;

    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'fontFamily', 'value' => 'Font Family'],
            ['id' => 'supportType', 'value' => 'Support Type'],
            ['id' => 'uniname', 'value' => 'Uniname'],
            ['id' => 'status', 'value' => 'Status'],
            ['id' => 'is_premium', 'value' => 'Is Premium'],
        ];

        $fontFamilies = $this->applyFiltersAndPagination($request, FontFamily::query(), $searchableFields);

        return view('fonts.font_families', compact('fontFamilies', 'searchableFields'));
    }


    private function fontFamilyDownload($json_object, $index, $count)
    {

        if ($index === $count) {
            return;
        }

        if ($this->familyCount === 500) {
            return;
        }

        $fontFamily = $json_object[$index];

        if (FontFamily::where('fontFamily', $fontFamily['familyNameEn'])->exists()) {
            $this->fontFamilyDownload($json_object, $index + 1, $count);
            return;
        }

        $url = $fontFamily['familySvgEn'];
        $client = new Client();
        $response = $client->get($url);

        if ($response->getStatusCode() === 200) {

            $this->familyCount = $this->familyCount + 1;

            $fileContents = $response->getBody();

            $bytes = random_bytes(20);
            $filename = bin2hex($bytes) . Carbon::now()->timestamp . '.' . pathinfo($url, PATHINFO_EXTENSION);

            $storagePath = 'uploadedFiles/font_thumb/' . $filename;
            StorageUtils::put($storagePath, $fileContents);

            $res = new FontFamily();

            $res->fontFamily = $fontFamily['familyNameEn'];
            $res->fontThumb = $storagePath;
            $res->supportType = implode(',', $fontFamily['supportType']);
            $res->is_premium = 0;
            $res->status = 1;

            $res->save();

            $this->fontDownload($res->id, $fontFamily['tFontV2List'], 0, sizeof($fontFamily['tFontV2List']), $json_object, $index + 1, $count);
        } else {
            $this->fontFamilyDatas[] = $fontFamily;
            $this->fontDownload($res->id, $fontFamily['tFontV2List'], 0, sizeof($fontFamily['tFontV2List']), $json_object, $index + 1, $count);
        }

    }

    private function fontDownload($familyId, $fontList, $index, $count, $json_object, $json_index, $json_count)
    {
        if ($index === $count) {
            $this->fontFamilyDownload($json_object, $json_index, $json_count);
            return;
        }

        $fontFamily = $fontList[$index];

        if (FontList::where('fontName', $fontFamily['fontName'])->exists()) {
            $this->fontDownload($familyId, $fontList, $index + 1, $count, $json_object, $json_index, $json_count);
            return;
        }

        $url = $fontFamily['fontUrl'];
        $client = new Client();
        $response = $client->get($url);

        if ($response->getStatusCode() === 200) {

            $fileContents = $response->getBody();

            $bytes = random_bytes(20);
            $filename = bin2hex($bytes) . Carbon::now()->timestamp . '.' . pathinfo($url, PATHINFO_EXTENSION);

            $storagePath = 'uploadedFiles/font_file/' . $filename;
            StorageUtils::put($storagePath, $fileContents);
            $res = new FontList();
            $res->fontFamilyId = $familyId;
            $res->fontName = $fontFamily['fontName'];
            $res->fontType = $fontFamily['fontType'];
            $res->fontUrl = $storagePath;
            $res->status = 1;
            $res->save();

            $this->fontDownload($familyId, $fontList, $index + 1, $count, $json_object, $json_index, $json_count);

        } else {
            $this->fontFamilyDatas[] = $fontFamily;
            $this->fontDownload($res->id, $fontFamily['tFontV2List'], 0, sizeof($fontFamily['tFontV2List']), $json_object, $index + 1, $count);
        }
    }

    public function add(Request $request)
    {

        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $this->validate($request, ['fontThumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        $fontFamily = $request->input('fontFamily');
        $data = FontFamily::where("fontFamily", $fontFamily)->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Font Family Already exist.'
            ]);
        }

        $res = new FontFamily();
        $res->emp_id = auth()->user()->id;
        $res->fontFamily = $fontFamily;
        $fontThumb = $request->file('fontThumb');
        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $fontThumb->getClientOriginalExtension();
        StorageUtils::storeAs($fontThumb, 'uploadedFiles/font_thumb', $new_name);
        $res->fontThumb = 'uploadedFiles/font_thumb/' . $new_name;

        // $supportTypes = $request->input('supportType');
        // $supportField = "";
        // foreach ($supportTypes as $supportType) {
        //     $supportField = $supportField . $supportType .",";
        // }
        // $res->supportType = substr($supportField, 0, -1);

        $res->support_bold = $request->input('support_bold');
        $res->support_italic = $request->input('support_italic');

        $res->uniname = $request->input('uniname');
        $res->is_premium = $request->input('is_premium');
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function get(Request $request)
    {

        $res = FontFamily::find($request->id);
        if ($res) {
            return response()->json([
                'success' => $res
            ]);
        } else {
            return response()->json([
                'error' => 'No data found.'
            ]);
        }

    }

    public function update(Request $request)
    {

        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdmin(Auth::user()->user_type);

        $res = FontFamily::find($request->id);

        $accessCheck = $this->isAccessByRole("design", $res->id, $res->emp_id ?? null);
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $fontThumb = $request->file('fontThumb');
        if ($fontThumb != null) {
            $this->validate($request, ['fontThumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        }

        $fontFamily = $request->input('fontFamily');

        $data = FontFamily::where("id", '!=', $request->id)->where("fontFamily", $fontFamily)->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Font Family Already exist.'
            ]);
        }

        $res->emp_id = auth()->user()->id;

        $res->fontFamily = $fontFamily;

        if ($fontThumb != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $fontThumb->getClientOriginalExtension();
            StorageUtils::storeAs($fontThumb, 'uploadedFiles/font_thumb', $new_name);
            $res->fontThumb = 'uploadedFiles/font_thumb/' . $new_name;
        }

        if (!$idAdmin) {
            $res->uniname = $request->input('uniname');
            $res->save();
            return response()->json([
                'success' => 'Done.'
            ]);
        }

        // $supportTypes = $request->input('supportType');
        // $supportField = "";
        // foreach ($supportTypes as $supportType) {
        //     $supportField = $supportField . $supportType .",";
        // }
        // $res->supportType = substr($supportField, 0, -1);

        $res->support_bold = $request->input('support_bold');
        $res->support_italic = $request->input('support_italic');

        $res->uniname = $request->input('uniname');
        $res->is_premium = $request->input('is_premium');
        $res->status = $request->input('status');
        $res->save();

        if ($fontThumb != null) {
            StorageUtils::delete($request->input('thumb_path'));
        }

        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function delete(Request $request)
    {
        $res = FontFamily::find($request->id);
        // $thumb = $res->fontThumb;
        // $path = $res->path;
        // try {
        //     unlink(storage_path("app/public/".$thumb));
        // } catch (\Exception $e) {
        // }

        // $datas = FontList::where('fontFamilyId', $request->id)->get();

        // foreach ($datas as $data) {
        //     try {
        //         unlink(storage_path("app/public/".$data->fontUrl));
        //     } catch (\Exception $e) {
        //     }
        //     FontList::destroy(array('id', $data->id));
        // }

        // FontFamily::destroy(array('id', $request->id));

        return response()->json([
            'success' => $request->id
        ]);
        // $resDatas = FontFamily::all();

        // foreach ($resDatas as $res) {
        //     $thumb = $res->fontThumb;
        //     $path = $res->path;
        //     try {
        //         unlink(storage_path("app/public/".$thumb));
        //     } catch (\Exception $e) {
        //     }

        //     $datas = FontList::where('fontFamilyId', $res->id)->get();

        //     foreach ($datas as $data) {
        //         try {
        //             unlink(storage_path("app/public/".$data->fontUrl));
        //         } catch (\Exception $e) {
        //         }
        //         FontList::destroy(array('id', $data->id));
        //     }

        //     FontFamily::destroy(array('id', $res->id));
        // }

        // return response()->json([
        //     'success' => $request->id
        // ]);
    }

}

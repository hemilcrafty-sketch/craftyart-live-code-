<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\FontFamily;
use App\Models\FontList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use FontLib\Font as FontLib;
use Illuminate\Support\Facades\Auth;

class FontListController extends AppBaseController
{

    const supported_fonts = ['woff'];


    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'fontName', 'value' => 'Font Name'],
            ['id' => 'fontType', 'value' => 'Font Type'],
            ['id' => 'fontWeight', 'value' => 'Font Weight'],
            ['id' => 'support_bold', 'value' => 'Support Bold'],
            ['id' => 'support_italic', 'value' => 'Support Italic'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $query = FontList::query();

        $filterBy = $request->input('filter_by');
        $filterValue = $request->input('filter_value');

        if ($filterBy && $filterValue !== null) {
            $query->where($filterBy, 'like', '%' . $filterValue . '%');
        }

        $datas['fontLists'] = $query->orderBy('id', 'desc')->paginate(10);
        $datas['fontFamilies'] = FontFamily::all();
        $datas['searchableFields'] = $searchableFields;

        return view('fonts.font_lists')->with([
            'datas' => $datas,
            'searchableFields' => $searchableFields
        ]);
    }

    public function add(Request $request)
    {
        $font_file = $request->file('fontFile');
        $this->validate($request, ['fontFile' => 'required']);

        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        if (!in_array($font_file->getClientOriginalExtension(), self::supported_fonts)) {
            return response()->json([
                'error' => 'Font extension is invalid. Supported fonts are woff'
            ]);
        }
        // if ($font_file->getClientOriginalExtension() != "ttf" && $font_file->getClientOriginalExtension() != "TTF" && $font_file->getClientOriginalExtension() != "otf" && $font_file->getClientOriginalExtension() != "OTF") {
        //     return response()->json([
        //         'error' => 'Font extension is invalid.'
        //     ]);
        // }

        $fontName = $request->input('fontName');

        $data = FontList::where("fontName", $fontName)->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Font Name Already exist.'
            ]);
        }

        $data = FontFamily::where("fontFamily", $request->input('fontFamilyId'))->first();
        if ($data == null) {
            return response()->json([
                'error' => 'Font Family Not exist.'
            ]);
        }

        $res = new FontList();
        $res->fontFamilyId = $data->id;
        $res->fontName = $request->input('fontName');
        // $res->fontType = $request->input('fontType');
        $res->support_bold = $request->input('support_bold');
        $res->support_italic = $request->input('support_italic');
        $res->status = $request->input('status');

        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $font_file->getClientOriginalExtension();
        StorageUtils::storeAs($font_file, 'uploadedFiles/fonts', $new_name);
        $res->fontUrl = 'uploadedFiles/fonts/' . $new_name;
        $res->emp_id = auth()->user()->id;

        $font = FontLib::load($font_file);
        if ($font != null) {
            try {
                $font->parse();
                $res->fontWeight = $font->getFontWeight();
            } catch (\Exception $e) {
            }
        }

        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function get(Request $request)
    {
        $res = FontList::find($request->id);
        if ($res) {
            $res->fontFamilyId = FontFamily::find($res->fontFamilyId)->fontFamily;
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
        $res = FontList::find($request->id);
        $accessCheck = $this->isAccessByRole("design", $res->id,  null);
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }
        if ($idAdmin) {

            $font_file = $request->file('fontFile');
            if ($font_file != null) {
                $this->validate($request, ['fontFile' => 'required']);
                if (!in_array($font_file->getClientOriginalExtension(), self::supported_fonts)) {
                    return response()->json([
                        'error' => 'Font extension is invalid. Supported fonts are woff'
                    ]);
                }
                // if ($font_file->getClientOriginalExtension() != "ttf" && $font_file->getClientOriginalExtension() != "TTF" && $font_file->getClientOriginalExtension() != "otf" && $font_file->getClientOriginalExtension() != "OTF") {
                //     return response()->json([
                //         'error' => 'Font extension is invalid.'
                //     ]);
                // }
            }

            $fontName = $request->input('fontName');

            $data = FontList::where("id", '!=', $request->id)->where("fontName", $fontName)->first();
            if ($data != null) {
                return response()->json([
                    'error' => 'Font Name Already exist.'
                ]);
            }

            $data = FontFamily::where("fontFamily", $request->input('fontFamilyId'))->first();
            if ($data == null) {
                return response()->json([
                    'error' => 'Font Family Not exist.'
                ]);
            }


            $res->fontFamilyId = $data->id;
            $res->fontName = $request->input('fontName');
            // $res->fontType = $request->input('fontType');
            $res->support_bold = $request->input('support_bold');
            $res->support_italic = $request->input('support_italic');
            $res->status = $request->input('status');

            if ($font_file != null) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $font_file->getClientOriginalExtension();
                StorageUtils::storeAs($font_file, 'uploadedFiles/fonts', $new_name);
                $res->fontUrl = 'uploadedFiles/fonts/' . $new_name;

                $font = FontLib::load($font_file);
                if ($font != null) {
                    try {
                        $font->parse();
                        $res->fontWeight = $font->getFontWeight();
                    } catch (\Exception $e) {
                        $res->fontWeight = 400;
                    }
                }
            }
            $res->save();

            return response()->json([
                'success' => 'Data Updated successfully.'
            ]);

        }

        return response()->json([
            'error' => 'Error'
        ]);
    }

    public function delete(Request $request)
    {
        $res = FontList::find($request->id);
        // $fontUrl = $res->fontUrl;
        // try {
        //     unlink(storage_path("app/public/".$fontUrl));
        // } catch (\Exception $e) {
        // }

        // FontList::destroy(array('id', $request->id));

        return response()->json([
            'success' => $request->id
        ]);
    }

}

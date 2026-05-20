<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Font;
use Illuminate\Http\Request;
use FontLib\Font as FontLib;
use Illuminate\Support\Facades\Auth;

class FontController extends AppBaseController
{

    public function create()
    {
        return view('fonts/create_font');
    }

    public function store(Request $request)
    {
        $res = new Font();


        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $image = $request->file('font_thumb');
        $this->validate($request, ['font_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);

        $font_file = $request->file('font_file');
        $this->validate($request, ['font_file' => 'required']);

        if ($font_file->getClientOriginalExtension() != "ttf" && $font_file->getClientOriginalExtension() != "TTF" && $font_file->getClientOriginalExtension() != "otf" && $font_file->getClientOriginalExtension() != "OTF") {
            return response()->json([
                'error' => 'Font extension is invalid.'
            ]);
        } else {
            $font_name = $font_file->getClientOriginalName();

            if (Font::where('name', pathinfo($font_name, PATHINFO_FILENAME))->exists()) {
                return response()->json([
                    'error' => 'Font already exists.'
                ]);
            }

            $new_name = rand() . HelperController::randomNameGenerator() . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/font_thumb', $new_name);
            $res->thumb = 'uploadedFiles/font_thumb/' . $new_name;

            StorageUtils::delete('uploadedFiles/font_file/' . $font_name);

            StorageUtils::storeAs($font_file, 'uploadedFiles/font_file', $font_name);
            $res->path = 'uploadedFiles/font_file/' . $font_name;


            // $fontPath = storage_path("app/public/" . 'uploadedFiles/font_file/' . $font_name);
            // $font = FontLib::load($fontPath);
            // $font = FontLib::load(StorageUtils::get('uploadedFiles/font_file/' . $font_name));
            $font = FontLib::load($font_file);
            if ($font != null && method_exists($font, "getFontName")) {
                $res->fontFamily = $font->getFontName();
                $res->postScriptName = $font->getFontPostscriptName();
            }

            if ($font != null) {
                try {
                    $font->parse();
                    $res->fontWeight = $font->getFontWeight();
                } catch (\Exception $e) {
                }

            }

            $res->name = pathinfo($font_name, PATHINFO_FILENAME);
            $res->extension = $font_file->getClientOriginalExtension();
            $res->uniname = $request->input('uniname');
            $res->status = $request->input('status');
            $res->emp_id = auth()->user()->id;
            $res->save();
            return response()->json([
                'success' => 'Data Added successfully.'
            ]);
        }

    }

    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'Id'],
            ['id' => 'name', 'value' => 'Name'],
            ['id' => 'extension', 'value' => 'Extension'],
            ['id' => 'uniname', 'value' => 'Uniname'],
            ['id' => 'fontFamily', 'value' => 'Font Family'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $appArray = $this->applyFiltersAndPagination($request, Font::query(), $searchableFields);

        return view('fonts.show_font', compact('appArray', 'searchableFields'));
    }

    public function edit(Font $font, $id)
    {
        return view('fonts/edit_font')->with('appArray', Font::find($id));

    }

    /** @noinspection PhpUndefinedFieldInspection */
    public function update(Request $request, Font $font)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdmin(Auth::user()->user_type);


        $res = Font::find($request->id);

        $accessCheck = $this->isAccessByRole("design", $res->id, null);
        if ($accessCheck) {
            return response()->json([
                'status' => false,
                'error' => $accessCheck,
            ]);
        }

        $res->name = $request->input('font_name');

        $image = $request->file('font_thumb');
        if ($image != null) {
            $this->validate($request, ['font_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        }


        if (!$idAdmin) {

            if ($image != null) {
                $this->validate($request, ['font_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
                $new_name = rand() . HelperController::randomNameGenerator() . '.' . $image->getClientOriginalExtension();
                StorageUtils::storeAs($image, 'uploadedFiles/font_thumb', $new_name);
                $res->thumb = 'uploadedFiles/font_thumb/' . $new_name;
            }

            $res->uniname = $request->input('uniname');
            $res->save();
            return response()->json([
                'success' => 'Done'
            ]);
        }

        $font_file = $request->file('font_file');
        if ($font_file != null) {
            if ($font_file->getClientOriginalExtension() != "ttf" && $font_file->getClientOriginalExtension() != "otf") {
                return response()->json([
                    'error' => 'Font extension is invalid.'
                ]);
            }
        }

        if ($font_file != null) {
            $font_name = $font_file->getClientOriginalName();

            StorageUtils::delete('uploadedFiles/font_file/' . $font_name);

            StorageUtils::storeAs($font_file, 'uploadedFiles/font_file', $font_name);
            $res->path = 'uploadedFiles/font_file/' . $font_name;

            $res->extension = $font_file->getClientOriginalExtension();

            $font = FontLib::load($font_file);

            if ($font != null && method_exists($font, "getFontName")) {
                $res->fontFamily = $font->getFontName();
                $res->postScriptName = $font->getFontPostscriptName();
            }

            if ($font != null) {
                try {
                    $font->parse();
                    $res->fontWeight = $font->getFontWeight();
                } catch (\Exception $e) {
                    $res->fontWeight = 400;
                }
            }
        }

        $res->uniname = $request->input('uniname');
        $res->status = $request->input('status');
        $res->save();

        if ($image != null) {
            StorageUtils::delete($request->input('thumb_path'));
        }
        return response()->json([
            'success' => 'Updated Successfully'
        ]);
        //        return redirect('show_fonts');
    }

    public function destroy(Font $font, $id)
    {
        // $res=Font::find($id);

        // $thumb = $res->thumb;
        // $path = $res->path;

        // try {
        // 	unlink(storage_path("app/public/".$thumb));
        // } catch (\Exception $e) {
        // }

        // try {
        // 	unlink(storage_path("app/public/".$path));
        // } catch (\Exception $e) {
        // }


        // Font::destroy(array('id', $id));
        return redirect('show_fonts');
    }
}

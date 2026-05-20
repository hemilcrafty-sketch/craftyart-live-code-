<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppCategoryController extends AppBaseController
{

    public function index()
    {
        //
    }


    public function create()
    {
        return view('app_cat/create_app');
    }


    public function store(Request $request)
    {
        $res = new AppCategory;
        $res->app_name = $request->input('app_name');

        $image = $request->file('app_thumb');
        $this->validate($request, ['app_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        $res->app_thumb = 'uploadedFiles/thumb_file/' . $new_name;

        $res->status = $request->input('status');
        $res->save();
        $request->session()->flash('msg', 'App Added');
        return redirect('show_app');
    }

    public function show(AppCategory $appCategory)
    {
        return view('app_cat/show_app')->with('appArray', AppCategory::all());
    }

    public function edit(AppCategory $appCategory, $id)
    {
        return view('app_cat/edit_app')->with('appArray', AppCategory::find($id));

    }

    /** @noinspection PhpUndefinedFieldInspection */
    public function update(Request $request, AppCategory $appCategory)
    {
        $res = AppCategory::find($request->id);
        $res->app_name = $request->input('app_name');

        $image = $request->file('app_thumb');
        if ($image != null) {
            $this->validate($request, ['app_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
            $res->app_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        }

        $res->status = $request->input('status');
        $res->save();

        if ($image != null) {
            try {
                unlink(storage_path("app/public/" . $request->input('app_thumb_path')));
            } catch (\Exception $e) {
            }
        }

        return response()->json([
            'success' => "done"
        ]);

        //        return redirect('show_app');
    }

    public function destroy(AppCategory $appCategory, $id)
    {
        //  $res=AppCategory::find($id);

        //  $app_thumb = $res->app_thumb;


        //  try {
        //      unlink(storage_path("app/public/".$app_thumb));
        // } catch (\Exception $e) {
        // }

        //  AppCategory::destroy(array('id', $id));
        return redirect('show_cat');
    }
}

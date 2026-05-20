<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubCategoryController extends AppBaseController
{


    public function index()
    {

    }
   
    public function show_sub_cat(SubCategory $subCategory)
    {
        return view('filters/sub_cat')->with('subCatArray', SubCategory::all());
    }

    public function addSubCat(Request $request)
    {   

        $data = SubCategory::where("name", $request->input('name'))->first();
        if($data != null) {
            return response()->json([
                'error' => 'Sub Category Already exist.'
            ]);
        }

        $res = new SubCategory;
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updateSubCat(Request $request, SubCategory $subCategory)
    {

        $res = SubCategory::find($request->id);
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteSubCat(Request $request, SubCategory $subCategory)
    {
        SubCategory::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

}

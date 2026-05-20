<?php

namespace App\Http\Controllers;

use App\Models\NewCategory;
use App\Models\Theme;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ThemeControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    }

    public function show_theme(Theme $heme)
    {
        $allCategories = NewCategory::where('parent_category_id',0)->where('status',1)->get();
        $themeArray = Theme::all();
        return view('filters/themes',compact('themeArray','allCategories'));
    }

    public function addTheme(Request $request)
    {
    	$data = Theme::where("name", $request->input('name'))->first();
    	if($data != null) {
    		return response()->json([
            	'error' => 'Theme Already exist.'
        	]);
    	}

        $newCategoryIdsInput = $request->input('new_category_ids');
        if (is_array($newCategoryIdsInput)) {
            $newCategoryIds = $newCategoryIdsInput;
        } else if (is_string($newCategoryIdsInput)) {
            $newCategoryIds = explode(',', $newCategoryIdsInput);
        } else {
            $newCategoryIds = [];
        }

        $res = new Theme;
        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->new_category_id = json_encode($newCategoryIds);
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function themeEdit($id)
    {
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $dataArray['item'] = Theme::find($id);
        $dataArray['allCategories'] = $allCategories;
        return response()->json([
                "status" => true,
                "view" => view("filters.edit_theme",compact('dataArray'))->render()
            ]
        );
    }

    public function updateTheme(Request $request, Theme $theme)
    {

        $newCategoryIdsInput = $request->input('new_category_ids');
        if (is_array($newCategoryIdsInput)) {
            $newCategoryIds = $newCategoryIdsInput;
        } else if (is_string($newCategoryIdsInput)) {
            $newCategoryIds = explode(',', $newCategoryIdsInput);
        } else {
            $newCategoryIds = [];
        }

        $res = Theme::find($request->id);
        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->new_category_id = json_encode($newCategoryIds);
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteTheme(Request $request, Theme $theme)
    {
        Theme::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    public function getThemeList(Request $request)
    {
        try {
            $category = NewCategory::find($request->cateId);
            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: $request->cateId;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $themes = Theme::whereJsonContains('new_category_id',$catId)->where('status',1)->get();
            return response()->json([
                'success' => true,
                'data' => $themes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

}

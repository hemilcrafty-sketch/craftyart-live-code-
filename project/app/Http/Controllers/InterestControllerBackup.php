<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\NewCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InterestControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function showInterest(Interest $interest)
    {
        $interestArray = Interest::all();
        $allNewCategories = NewCategory::where('parent_category_id',0)->where('status',1)->get();
        return view('filters/interests',compact('interestArray','allNewCategories'));
    }

    public function addInterest(Request $request)
    {

    	$data = Interest::where("name", $request->input('name'))->first();
    	if($data != null) {
    		return response()->json([
            	'error' => 'Interest Already exist.'
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

        $res = new Interest;
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

    public function editIntrest($id)
    {
        // $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $allCategories = NewCategory::where('parent_category_id',0)->where('status',1)->get();
        $dataArray['item'] = Interest::find($id);
        $dataArray['allCategories'] = $allCategories;
        return response()->json([
                "status" => true,
                "view" => view("filters.edit_interest",compact('dataArray'))->render()
            ]
        );
    }

    public function updateInterest(Request $request, Interest $interest)
    {
        $newCategoryIdsInput = $request->input('new_category_ids');
        if (is_array($newCategoryIdsInput)) {
            $newCategoryIds = $newCategoryIdsInput;
        } else if (is_string($newCategoryIdsInput)) {
            $newCategoryIds = explode(',', $newCategoryIdsInput);
        } else {
            $newCategoryIds = [];
        }

        $res = Interest::find($request->id);
        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->new_category_id = json_encode($newCategoryIds);
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteInterest(Request $request, Interest $interest)
    {
        Interest::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    public function getInterestList( Request $request )
    {
        try {
            $category = NewCategory::find($request->cateId);
            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: $request->cateId;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $interestes = Interest::whereJsonContains('new_category_id',$catId)->where('status',1)->get();
            return response()->json([
                'success' => true,
                'data' => $interestes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

}

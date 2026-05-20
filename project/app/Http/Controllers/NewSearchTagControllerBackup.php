<?php

namespace App\Http\Controllers;

use App\Models\NewCategory;
use App\Models\NewSearchTag;
use Exception;
use Illuminate\Http\Request;

class NewSearchTagControllerBackup extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /** @return \Illuminate\Http\Response */
    public function index()
    {
        $allNewCategories = NewCategory::getAllCategoriesWithSubcategories();
        // $newSearchTags = NewSearchTag::with('newCategories')->orderBy('id', 'desc')->get();
        $newSearchTags = NewSearchTag::all();
        return view('filters/new_search_tag', compact('allNewCategories', 'newSearchTags'));
    }

    /** Store a newly created resource in storage.*/
    public function store(Request $request)
    {
        try {
            $inputs = [
                "name" => $request->name,
                "id_name" => $request->id_name,
                "new_category_id" => json_encode(array_filter(explode(",", str_replace(' ','', $request->new_category_id)))),
                "status" => $request->status,
                "emp_id" => auth()->user()->id,
            ];

            NewSearchTag::create($inputs);
            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been added successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /** Show the form for editing the specified resource.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(NewSearchTag $newSearchTag)
    {
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $dataArray['item'] = $newSearchTag;
        $dataArray['allCategories'] = $allCategories;

        $ids = json_decode($newSearchTag->new_category_id);

        $newIds = "";
        $names = "";
        $namesArray = [];

        for($i = 0; $i < count($ids); $i++) {
            $cn = HelperController::getNewCatName($ids[$i]);
            if ($i == count($ids) - 1) {
                $newIds = $newIds . $ids[$i];
                $names = $names . $cn;
            } else {
                $newIds = $newIds . $ids[$i] . ", ";
                $names = $names . $cn . ", ";
            }
            $namesArray[] = $cn;
        }

        $dataArray['item']['catNames'] = $names;
        $dataArray['item']['newIds'] = $newIds;

        return response()->json([
                "status" => true,
                "id" => $ids,
                "name" => $namesArray,
                "view" => view("filters.edit_search_tag",compact('dataArray'))->render()
            ]
        );
    }

    /** Update the specified resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewSearchTag $newSearchTag)
    {
        try {
            $newSearchTag->update([
                "new_category_id" => json_encode(array_filter(explode(",", str_replace(' ','', $request->new_category_id)))),
                'name' => $request->name,
                "id_name" => $request->id_name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been updated successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewSearchTag $newSearchTag)
    {
        try {
            $newSearchTag->delete();
            return response()->json([
                'status' => true,
                'success' => "New Search Tag has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

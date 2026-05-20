<?php

namespace App\Http\Controllers;

use App\Models\SearchTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchTagControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    }

    public function show_search_tag(SearchTag $searchTag)
    {
        return view('filters/search_tag')->with('searchTagArray', SearchTag::all());
    }

    public function addSearchTag(Request $request)
    {

        $data = SearchTag::where("name", $request->input('name'))->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Tag Already exist.'
            ]);
        }

        $res = new SearchTag;
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updateSearchTag(Request $request, SearchTag $searchTag)
    {

        $res = SearchTag::find($request->id);
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteSearchTag(Request $request, SearchTag $searchTag)
    {
        SearchTag::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }
}

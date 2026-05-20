<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\PageSlugHistory;
use Mail;
use Carbon\Carbon;
use Auth;

class PageSlugHistoryController extends AppBaseController
{

    public static array $types =[
        "0" => "K Page",
        "1" => "Special Page",
        "2" => "Blog Page",
        "3" => "New Category Page",
        "4" => "Old Category Page",
        "5" => "Product Page",
    ];

    public function show(PageSlugHistory $pageSlugHistory)
    {
        $data['types'] = PageSlugHistoryController::$types;

        $slugs = PageSlugHistory::all();
        foreach ($slugs as $slug) {
            $slug->typeName = PageSlugHistoryController::$types[$slug->type] ?? 'Unknown';
        }

        $data['lists'] = $slugs;
        return view('filters/page_slugs')->with('slugs', $data);
    }

    public function add(Request $request)
    {

    	$currentuserid = auth()->user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);

        if (!$idAdmin) {
            return response()->json([
	            'error' => 'Ask admin or fenil for changes'
	        ]);
        }

    	$old_slug =  $request->old_slug;
    	$new_slug =  $request->new_slug;
    	$type =  $request->type;

        $data = PageSlugHistory::where("old_slug", $old_slug)->where('type', $type)->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Old slug Already exist.'
            ]);
        }

        if (!str_starts_with($new_slug, "https://www.craftyartapp.com")) {
            return response()->json([
                'error' => 'New slug should start from "https://www.craftyartapp.com"'
            ]);
        }

        $res = new PageSlugHistory;
        $res->old_slug = $old_slug;
        $res->new_slug = $new_slug;
        $res->type = $type;
        $res->emp_id = auth()->user()->id;
        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function update(Request $request, PageSlugHistory $pageSlugHistory)
    {
    	$currentuserid = auth()->user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);

        if (!$idAdmin) {
            return response()->json([
	            'error' => 'Ask admin or fenil for changes'
	        ]);
        }
        
    	$old_slug =  $request->old_slug;
    	$new_slug =  $request->new_slug;
    	$type =  $request->type;

    	$data = PageSlugHistory::where("old_slug", $old_slug)->where('type', $type)->where("id", "!=", $request->id)->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Old slug Already exist.'
            ]);
        }

        if (!str_starts_with($new_slug, "https://www.craftyartapp.com")) {
            return response()->json([
                'error' => 'New slug should start from "https://www.craftyartapp.com"'
            ]);
        }

        $res = PageSlugHistory::find($request->id);
        $res->old_slug = $old_slug;
        $res->new_slug = $new_slug;
        $res->type = $type;
        $res->emp_id = auth()->user()->id;
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }
  
}


<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LangControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    }

    public function showLang(Language $language)
    {
        return view('filters/languages')->with('langArray', Language::all());
    }

    public function addLang(Request $request)
    {

    	$data = Language::where("name", $request->input('name'))->first();
    	if($data != null) {
    		return response()->json([
            	'error' => 'Language Already exist.'
        	]);
    	}

        $res = new Language;
        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updateLang(Request $request, Language $language)
    {

        $res = Language::find($request->id);
        $res->name = $request->input('name');
        $res->id_name = $request->input('id_name');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteLang(Request $request, Language $language)
    {
        Language::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

}

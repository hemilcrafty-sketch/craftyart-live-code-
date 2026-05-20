<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StyleControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    }

    public function show_style(Style $style)
    {
        return view('filters/styles')->with('styleArray', Style::all());
    }

    public function addStyle(Request $request)
    {

        $data = Style::where("name", $request->input('name'))->first();
        if($data != null) {
            return response()->json([
                'error' => 'Style Already exist.'
            ]);
        }

        $res = new Style;
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->id_name = $request->input('id_name');
        $res->emp_id = auth()->user()->id;
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updateStyle(Request $request, Style $style)
    {

        $res = Style::find($request->id);
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->id_name = $request->input('id_name');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deleteStyle(Request $request, Style $style)
    {
        Style::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

}

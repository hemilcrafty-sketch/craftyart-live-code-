<?php

namespace App\Http\Controllers;

use App\Models\BgMode;
use App\Models\EditableMode;
use App\Models\ResizeMode;
use App\Models\StickerMode;
use App\Models\Template;
use App\Models\Category;
use App\Models\AppCategory;
use App\Models\TextAlignment;
use Exception;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EditableModesController extends AppBaseController
{

    public function index()
    {

    }

    public function create(Request $request)
    {

        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Filter and paginate results
        $fontFamilies = EditableMode::where('name', 'like', '%' . $query . '%')
                        ->orWhere('brand_id', 'like', '%' . $query . '%')
                        ->orderBy($sortBy, $sortOrder)
                        ->paginate($perPage);

        return view('filters/editable_mode')->with('titleArray', $fontFamilies);
    }

    public function store(Request $request)
    {
        $data = EditableMode::where("name", $request->input('name'))->first();
        if($data != null) {
            return response()->json([
                'error' => 'Title Already exist.'
            ]);
        }

        $res = new EditableMode();
        $res->name = $request->input('name');
        $res->brand_id = $request->input('brand_id');
        $res->save();
        return response()->json([
            'success' => 'Done'
        ]);
    }

    public function update(Request $request, EditableMode $editableMode)
    {

        $data = EditableMode::where("name", $request->input('name'))->first();
        if($data != null) {
            if($data->id != $request->id) {
                return response()->json([
                    'error' => 'Title Already exist.'
                ]);
            }
        }

        $res = EditableMode::find($request->id);
        $res->name = $request->input('name');
        $res->brand_id = $request->input('brand_id');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function delete(Request $request, EditableMode $editableMode)
    {
        EditableMode::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends AppBaseController
{
    /** Display a listing of the resource.*/
    public function index(Request $request)
    {
        $filters = ["code"];
        $query = Color::query();
        if( isset($request->query) && $request->input('query') != '' ){
            $query = $this->applyFilters($query, $filters,$request->input('query'));
        }
        $colors = $query->orderBy('id','desc')->paginate(10);
        return view("color.index")->with('colors',$colors);
    }

    /*** Store a newly created resource in storage.*/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            Color::create([
                'code' => $request->code,
                'emp_id' => auth()->user()->id
            ]);
            return $this->sendSuccessResponse("Color has been added successfully.");
        } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    /*** Update the specified resource in storage.*/
    public function update(Request $request, Color $color)
    {
        try {
            $color->update($request->only(['code']));
            return $this->sendSuccessResponse("Color has been updated successfully.");
       } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    /*** Remove the specified resource from storage.*/
    public function destroy(Color $color)
    {
        try {
            $color->delete();
            return $this->sendSuccessResponse("Color has been deleted successfully.");
        } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }
    }
}

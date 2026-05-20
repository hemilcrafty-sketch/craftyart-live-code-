<?php

namespace App\Http\Controllers;

use App\Models\Relegion;
use App\Models\Religion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RelegionControllerBackup extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = ["religion_name"];
        $query = Religion::query();
        if( isset($request->query) && $request->input('query') != '' ){
            $query = $this->applyFilters($query, $filters,$request->input('query'));
        }
        $religions = $query->orderBy('id','desc')->paginate(10);
        return view("relegion.index")->with('religions',$religions);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'religion_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            Religion::create([
                'religion_name' => $request->religion_name,
                'id_name' => $request->id_name,
                'emp_id' => auth()->user()->id,
                'status' => $request->status
            ]);
            return response()->json([
                'status' => true,
                'success' => "Religion has been added successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Religion $religion)
    {
        try {
            $religion->update($request->only(['religion_name','id_name','status']));
            return response()->json([
                'status' => true,
                'success' => "Religion has been updated successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Remove the specified resource from storage.*/
    public function destroy(Religion $religion)
    {
        try {
            $religion->delete();
            return response()->json([
                'status' => true,
                'success' => "Religion has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

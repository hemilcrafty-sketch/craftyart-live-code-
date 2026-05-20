<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanMetaDetail;
use Exception;
use Illuminate\Http\Request;

class PlanMetaDetailsController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = ["meta_feature_key"];
        $query = PlanMetaDetail::query();
        if( isset($request->query) && $request->input('query') != '' ){
            $query = $this->applyFilters($query, $filters,$request->input('query'));
        }
        $planMetaDetails = $query->paginate(10);
        $plans = Plan::all();
        $features = Feature::all();
        return view("plan_meta_details.index",compact("planMetaDetails","plans","features"));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = $request->all();
        try {
            if( isset($inputs["plan_id"]) && !empty($inputs["plan_id"]) && isset($inputs["feature_id"]) && !empty($inputs["feature_id"])){
                PlanMetaDetail::create([
                    "meta_feature_key" =>  $inputs["meta_feature_key"],
                    "meta_feature_value" =>  $inputs["meta_feature_value"],
                    "plan_id" =>  json_encode( $inputs["plan_id"] ),
                    "feature_id" =>  json_encode( $inputs["feature_id"] ),
                ]);
            }
            return $this->sendSuccessResponse("Plan Meta Details has been inserted successfully.");
        } catch (Exception $e) {
             return $this->sendErrorResponse($e->getMessage());
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

    /*** Show the form for editing the specified resource. */
    public function edit($id)
    {
        $plans = Plan::all();
        $features = Feature::all();
        $planMetaDetail = PlanMetaDetail::where('id',$id)->first();

        return response()->json([
            'view'=> view('plan_meta_details.edit',compact('planMetaDetail','plans','features'))->render()
        ]);
    }

    /** Update the specified resource in storage.*/
    public function update(Request $request, $id)
    {
        $inputs = $request->except('_token');
        try {
            if( isset($inputs["plan_id"]) && !empty($inputs["plan_id"]) && isset($inputs["feature_id"]) && !empty($inputs["feature_id"])){

                PlanMetaDetail::whereId($id)->update([
                    "meta_feature_key" =>  $inputs["meta_feature_key"],
                    "meta_feature_value" =>  $inputs["meta_feature_value"],
                    "plan_id" =>  json_encode($inputs["plan_id"]),
                    "feature_id" =>  json_encode($inputs["feature_id"]),
                ]);
            }

            return $this->sendSuccessResponse("Plan Meta Details has been updated successfully.");
       } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            PlanMetaDetail::where($id)->delete();
            return $this->sendSuccessResponse("Plan Meta Details has been deleted successfully.");
        } catch (Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }
    }
}

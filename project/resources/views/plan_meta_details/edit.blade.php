<form method="post" id="edit_plan_meta_details_form">
 @csrf
  <input class="form-control" type="textname" name="plan_meta_detail_id" value="{{$planMetaDetail->id}}" style="display: none"/>
    <div class="form-group">
        <h7>Meta Feature Key</h7>
        <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Enter Meta Feature Key" name="exist_meta_feature_key" value="{{ $planMetaDetail->meta_feature_key }}" required=""  />
        </div>
    </div>
    <div class="form-group">
        <h7>Meta Feature Value</h7>
        <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Enter Meta Feature Value" name="exist_meta_feature_value" value="{{ $planMetaDetail->meta_feature_value }}" required="" />
        </div>
    </div>
    <div class="form-group">
        <h7>Plan</h7>
        <div class="input-group custom">
            <select name="exist_plan_id[]" id="selectedPlanId"
            class="custom-select2 form-control selected-plan-id"
            multiple="multiple"
            data-style="btn-outline-primary">
                @foreach ($plans as $plan)
                    {{-- <option value="{{ $plan->id }}"  {{ ($plan->id == $planMetaDetail->plan_id) ? "selected" : "" }} >{{ $plan->name }}</option> --}}
                    <option value="{{ $plan->id }}" {{ ( in_array( $plan->id,json_decode($planMetaDetail->plan_id,true) )) ? "selected" : "" }} >{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group">
        <h7>Feature</h7>
        <div class="input-group custom">
            <select class="custom-select2 form-control selected-feature-id"
                    multiple="multiple"
                    data-style="btn-outline-primary"
                    name="exist_feature_ids[]" id="selectedFeatureId">
                    @foreach ($features as $feature)
                        <option value="{{ $feature->id }}" {{ ( in_array( $feature->id,json_decode($planMetaDetail->feature_id,true) )) ? "selected" : "" }} >{{ $feature->name }}</option>
                    @endforeach
            </select>
        </div>
    </div>
 <div class="row">
     <div class="col-sm-12">
         <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button>
     </div>
 </div>
</form>

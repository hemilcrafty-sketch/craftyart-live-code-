@inject('customOrderApiController', 'App\Http\Controllers\CustomOrder\CustomOrderApiController')
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
        <div class="main-container">
            <div id="main_loading_screen" style="display: none;">
                <div id="loader-wrapper">
                    <div id="loader"></div>
                    <div class="loader-section section-left"></div>
                    <div class="loader-section section-right"></div>
                </div>
            </div>
            <div class="pd-ltr-20 xs-pd-20-10">
                <div class="min-height-200px">
                    <div class="card-box mb-30">
                        <div class="pb-20">

                            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">                

                                <div class="row">

                                	<div class="col-sm-12 col-md-3">
                                        <div class="pd-20">
                                        	<form action="{{ route('show_orders') }}" method="GET">
	                                            <div class="form-group">
			                                        <h6>Sorting By</h6>
			                                        <select class="selectpicker form-control" data-style="btn-outline-primary" name="sorting_by" onchange="this.form.submit()">	
			                                        	@foreach($orderArray['sortingFields'] as $key => $value)
		                                                
			                                                @if($key == $orderArray['sortingField'])
			                                                	<option value="{{ $key }}" selected="">{{ $value }}</option>
			                                                @else
			                                                	<option value="{{ $key }}">{{ $value }}</option>
			                                                @endif

		                                                @endforeach
			                                        </select>
		                                        </div>
	                                    	</form>
                                        </div>
                                    </div>

                                    <div class="col-sm-12 col-md-9">
                                        <div class="pt-20">
                                            <form action="{{ route('show_orders') }}" method="GET">
                                                <div class="form-group">
                                                    <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                       <input type="text" class="form-control" name="query" placeholder="Search here....." value="{{ request()->input('query') }}"> <button type="submit" class="btn btn-primary">Search</button>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                        </div>
                                    </div>
                                </div>

                                <form id="create_item_action" action="create_item" method="POST" enctype="multipart/form-data" style="display: none;">
                                    <input type="text" id="passingAppId" name="passingAppId">
                                       @csrf
                                </form>

                                <div class="col-sm-12 table-responsive">   
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Order Id</th>
                                                <th>Name</th>
                                                <th>Number</th>
                                                <th>Email</th>
                                                <th>Amount</th>
                                                <th>Coin</th>
                                                <th>Discount</th>
                                                <th>status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="item_table">
                                            @foreach ($orderArray['item'] as $item)
                                            <tr style="background-color: #efefef;">
                                                <td>{{$item->id}}</td>
                                                <td>{{$item->order_id}}</td>
                                                <td>{{$item->name}}</td>
                                                <td>{{$item->country_code}} {{$item->number}}</td>
                                                <td>{{$item->email}}</td>
                                                <td>{{$item->currency}} {{$item->paid_amount}} ({{$item->amount}})</td>
                                                <td>{{$item->coins}}</td>
                                                <td>{{$item->discount}}</td>
                                                <td>{{$customOrderApiController::geStatusMsg2($item->status)}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">{{ $orderArray['count_str'] }}</div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                            <ul class="pagination">
                                                {{ $orderArray['item']->appends(request()->input())->links('pagination::bootstrap-4') }}
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@include('layouts.masterscript')
    </body>
</html>

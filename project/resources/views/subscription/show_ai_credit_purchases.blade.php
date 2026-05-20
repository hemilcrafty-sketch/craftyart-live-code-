 
 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <h3 style="font-size: larger;color:black;">AI Credits Purchases</h3>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route(Route::currentRouteName()) }}" method="GET">
                                        <div class="form-group">
                                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                <label>Search:<input type="text" class="form-control" name="query" placeholder="Search here....." value="{{ request()->input('query') }}"></label> <button type="submit" class="btn btn-primary">Search</button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>***</th>
                                        <th>User ID</th>
                                        <th>User Name</th>
                                        <th>Credits</th>
                                        <th>Payment ID</th>
                                        <th>Transcation ID</th>
                                        <th>Platform</th>
                                        <th>Amount</th>
                                        <th>Payment Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas['transcationArray'] as $transcation)
                                        <tr>
                                            <td class="table-plus">{{ $transcation->id }}</td>

                                            <td>
                                                <a target="_blank"
                                                    href="user_detail/{{ $transcation->userData?->email }}">{{ $transcation?->userData?->name ?? '--' }} <br> {{ $transcation?->userData?->email ?? '--' }} <br> {{ $transcation?->userData?->contact_no ?? $transcation?->contact_no ?? '--' }} </a>
                                            </td>

                                            <td>{{ $helperController::getUserName($transcation->user_id) }}</td>

                                            <td><a target="_blank"
                                                    href="{{ $transcation->page_link }}">{{ $transcation->product_id }}</a>
                                            </td>

                                            <td>
                                                @if ($transcation->payment_id)
                                                    <a href="{{ url('caricature_history/payment/' . $transcation->payment_id) }}"
                                                        target="_blank">

                                                        {{ $transcation->payment_id }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td>{{ $transcation->transaction_id }}</td>

                                            <td>{{ $transcation->from_where }}</td>

                                            <td>{{ $transcation->currency_code }} {{ $transcation->amount }}</td>

                                            <td>{{ $transcation->created_at }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                    aria-live="polite">{{ $datas['count_str'] }}</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        {{ $datas['transcationArray']->appends(request()->input())->links('pagination::bootstrap-4') }}
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

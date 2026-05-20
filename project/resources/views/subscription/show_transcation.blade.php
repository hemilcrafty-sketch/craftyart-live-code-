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

                        <div class="pd-20">
                            <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                                data-target="#add_transaction_model" type="button">
                                Add Transaction </a>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <h3 style="font-size: larger;color:black;">Subscriptions Transaction Logs</h3>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route('transcation_logs') }}" method="GET">
                                        <div class="form-group">
                                            <div class="dataTables_filter">
                                                <label>
                                                    Search:
                                                    <input type="text"
                                                           class="form-control"
                                                           name="query"
                                                           placeholder="Search here....."
                                                           value="{{ request('query') }}">
                                                </label>

                                                @if(request()->has('type'))
                                                    <input type="hidden" name="type" value="{{ request('type') }}">
                                                @endif

                                                <button type="submit" class="btn btn-primary">Search</button>
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
                                        <th>User</th>
                                        <th>Plan</th>
                                        <th>Transcation ID</th>
                                        <th>Platform</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Payment Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas['transcationArray'] as $transcation)
                                        <tr>
                                            <td class="table-plus">{{ $transcation->id }}</td>

                                            <td>
                                                <a target="_blank"
                                                    href="user_detail/{{ $transcation->user_id }}">{{ $transcation->user_id }} <br> {{ $transcation?->userData?->name ?? '--' }} <br> {{ $transcation?->userData?->email ?? '--' }} <br> {{ $transcation?->userData?->contact_no ?? $transcation?->contact_no ?? '--' }} </a>
                                            </td>

                                            <td>({{ $transcation->subscription?->id }}) <br> {{ $transcation->subscription?->package_name }}</td>

                                            <td>{{ $transcation->transaction_id }}  ({{ $transcation->subscription_id ?? '--' }}) </td>

                                            <td>{{ $transcation->from_where }}</td>

                                            <td>{{ $transcation->currency_code }} {{ $transcation->price_amount }}</td>

                                            <td>{{ $transcation->currency_code }} {{ $transcation->paid_amount }}</td>

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

<div class="modal fade" id="add_transaction_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Transaction</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_transaction_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>User ID</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="user_id" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Plan ID</h6>
                        <div class="input-group custom">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" name="plan_id">
                                @foreach ($datas['packageArray'] as $package)
                                    <option value="{{ $package->id }}">{{ $package->package_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Contact No</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="contact" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Method</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" list="method_list" autocomplete="on"
                                style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                name="method" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Transaction ID</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="transaction_id" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Currency</h6>
                        <div class="input-group custom">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="currency_code">
                                <option value="INR">INR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Price Amount</h6>
                        <div class="input-group custom">
                            <input type="number" class="form-control" name="price_amount" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Paid Amount</h6>
                        <div class="input-group custom">
                            <input type="number" class="form-control" name="paid_amount" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>From Wallet</h6>
                        <select class="selectpicker form-control" data-style="btn-outline-primary" name="fromWallet">
                            <option value="0">False</option>
                            <option value="1">True</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>From Where</h6>
                        <select class="selectpicker form-control" data-style="btn-outline-primary" name="fromWhere">
                            <option value="Mobile">Mobile</option>
                            <option value="Web">Web</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Coins</h6>
                        <div class="input-group custom">
                            <input type="number" class="form-control" name="coins" value="0"
                                required="" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" type="submit" name="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<datalist id="method_list">
    <option value="Razorpay"></option>
    <option value="Stripe"></option>
</datalist>

@include('layouts.masterscript')

</body>

</html>

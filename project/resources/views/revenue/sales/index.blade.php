@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
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
                  <h3 style="font-size: larger;color:black;">Sales Revenue</h3>
                </div>
              </div>

              <div class="col-sm-12 col-md-9">
                <div class="pt-20">
                  <form action="{{ route(Route::currentRouteName()) }}" method="GET">
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <input type="text" class="form-control" name="query" placeholder="Search..."
                            value="{{ request()->input('query') }}">
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="success" {{ request()->status == 'success' ? 'selected' : '' }}>Success
                            </option>
                            <option value="pending" {{ request()->status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="failed" {{ request()->status == 'failed' ? 'selected' : '' }}>Failed</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <input type="date" class="form-control" name="from_date"
                            value="{{ request()->input('from_date') }}">
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <input type="date" class="form-control" name="to_date"
                            value="{{ request()->input('to_date') }}">
                        </div>
                      </div>
                      <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
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
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Reference ID</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($datas['sales'] as $sale)
                    <tr>
                      <td>{{ $sale->id }}</td>
                      <td>
                        <strong>{{ $sale->user_name }}</strong><br>
                        {{ $sale->email }}<br>
                        {{ $sale->contact_no }}
                      </td>
                      <td>
                        Plan ID: {{ $sale->plan_id }}<br>
                        Type: {{ $sale->plan_type }}<br>
                        @if($sale->usage_type)
                          Usage: {{ ucfirst($sale->usage_type) }}
                        @endif
                      </td>
                      <td>₹{{ number_format($sale->amount, 2) }}</td>
                      <td>{{ ucfirst($sale->payment_method) }}</td>
                      <td>
                        {{ $sale->reference_id }}<br>
                        @if($sale->phonepe_order_id)
                          <small>PhonePe: {{ $sale->phonepe_order_id }}</small>
                        @endif
                      </td>
                      <td>
                        @if($sale->status == 'success')
                          <span class="badge badge-success">Success</span>
                        @elseif($sale->status == 'pending')
                          <span class="badge badge-warning">Pending</span>
                        @else
                          <span class="badge badge-danger">Failed</span>
                        @endif
                      </td>
                      <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                      <td>
                        <a href="{{ route('revenue.sales.show', $sale->id) }}" class="btn btn-sm btn-info"
                          title="View Details">
                          <i class="fa fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="row">
              <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">
                  {{ $datas['count_str'] }}</div>
              </div>
              <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                  <ul class="pagination">
                    {{ $datas['sales']->appends(request()->input())->links('pagination::bootstrap-4') }}
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
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')
<div class="main-container">

  <div class="pd-ltr-20 xs-pd-20-10">
    <div class="min-height-200px">
      <div class="card-box mb-30">
        <div class="pd-20">
          <div class="row mb-3">
            <div class="col-md-6">
              <h3 style="font-size: larger;color:black;">Sale Details #{{ $sale->id }}</h3>
            </div>
            <div class="col-md-6 text-right">
              <a href="{{ route('revenue.sales.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Sales
              </a>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h5>Customer Information</h5>
                </div>
                <div class="card-body">
                  <table class="table table-borderless">
                    <tr>
                      <th width="40%">Name:</th>
                      <td>{{ $sale->user_name }}</td>
                    </tr>
                    <tr>
                      <th>Email:</th>
                      <td>{{ $sale->email }}</td>
                    </tr>
                    <tr>
                      <th>Contact:</th>
                      <td>{{ $sale->contact_no }}</td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h5>Payment Information</h5>
                </div>
                <div class="card-body">
                  <table class="table table-borderless">
                    <tr>
                      <th width="40%">Amount:</th>
                      <td><strong>₹{{ number_format($sale->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                      <th>Payment Method:</th>
                      <td>{{ ucfirst($sale->payment_method) }}</td>
                    </tr>
                    <tr>
                      <th>Status:</th>
                      <td>
                        <span
                          class="badge badge-{{ $sale->status == 'paid' ? 'success' : ($sale->status == 'pending' ? 'warning' : 'danger') }}">
                          {{ ucfirst($sale->status) }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <th>Reference ID:</th>
                      <td>{{ $sale->reference_id }}</td>
                    </tr>
                    @if($sale->phonepe_order_id)
                      <tr>
                        <th>PhonePe Order ID:</th>
                        <td>{{ $sale->phonepe_order_id }}</td>
                      </tr>
                    @endif
                    @if($sale->payment_link_id)
                      <tr>
                        <th>Payment Link ID:</th>
                        <td>{{ $sale->payment_link_id }}</td>
                      </tr>
                    @endif
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h5>Plan Details</h5>
                </div>
                <div class="card-body">
                  <table class="table table-borderless">
                    <tr>
                      <th width="40%">Plan ID:</th>
                      <td>{{ $sale->plan_id }}</td>
                    </tr>
                    <tr>
                      <th>Subscription Type:</th>
                      <td>{{ ucfirst($sale->subscription_type) }}</td>
                    </tr>
                    <tr>
                      <th>Plan Type:</th>
                      <td>{{ ucfirst($sale->plan_type) }}</td>
                    </tr>
                    @if($sale->usage_type)
                      <tr>
                        <th>Usage Type:</th>
                        <td>{{ ucfirst($sale->usage_type) }}</td>
                      </tr>
                    @endif
                    @if($sale->caricature)
                      <tr>
                        <th>Caricature:</th>
                        <td>{{ $sale->caricature }}</td>
                      </tr>
                    @endif
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h5>Additional Information</h5>
                </div>
                <div class="card-body">
                  <table class="table table-borderless">
                    @if($sale->order_id)
                      <tr>
                        <th width="40%">Order ID:</th>
                        <td>{{ $sale->order_id }}</td>
                      </tr>
                    @endif
                    @if($sale->salesPerson)
                      <tr>
                        <th>Sales Person:</th>
                        <td>{{ $sale->salesPerson->name ?? 'N/A' }}</td>
                      </tr>
                    @endif
                    <tr>
                      <th>Created At:</th>
                      <td>{{ $sale->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    @if($sale->paid_at)
                      <tr>
                        <th>Paid At:</th>
                        <td>{{ $sale->paid_at->format('d M Y H:i:s') }}</td>
                      </tr>
                    @endif
                    @if($sale->payment_link_url)
                      <tr>
                        <th>Payment Link:</th>
                        <td><a href="{{ $sale->payment_link_url }}" target="_blank">View Link</a></td>
                      </tr>
                    @endif
                    @if($sale->short_url)
                      <tr>
                        <th>Short URL:</th>
                        <td><a href="{{ $sale->short_url }}" target="_blank">{{ $sale->short_url }}</a></td>
                      </tr>
                    @endif>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

@include('layouts.footer')
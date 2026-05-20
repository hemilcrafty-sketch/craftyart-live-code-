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
                  <h3 style="font-size: larger;color:black;">Business Support Purchases</h3>
                </div>
              </div>

              <div class="col-sm-12 col-md-9">
                <div class="pt-20">
                  <form action="{{ route(Route::currentRouteName()) }}" method="GET">
                    <div class="form-group">
                      <div id="DataTables_Table_0_filter" class="dataTables_filter">
                        <label>Search:<input type="text" class="form-control" name="query"
                            placeholder="Search here....." value="{{ request()->input('query') }}"></label> <button
                          type="submit" class="btn btn-primary">Search</button>
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
                    <th>Followup</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($datas['transcationArray'] as $transcation)
                    <tr>
                      <td class="table-plus">{{ $transcation->id }}</td>

                      <td>
                        <a target="_blank"
                          href="user_detail/{{ $transcation->userData?->email }}">{{ $transcation?->userData?->name ?? '--' }}
                          <br> {{ $transcation?->userData?->email ?? '--' }} <br>
                          {{ $transcation?->userData?->contact_no ?? $transcation?->contact_no ?? '--' }}
                        </a>
                      </td>

                      <td>{{ $helperController::getUserName($transcation->user_id) }}</td>

                      <td><a target="_blank" href="{{ $transcation->page_link }}">{{ $transcation->product_id }}</a>
                      </td>

                      <td>
                        @if ($transcation->payment_id)
                          <a href="{{ url('caricature_history/payment/' . $transcation->payment_id) }}" target="_blank">

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

                      <td class="text-center">
                        <button class="btn btn-sm btn-primary followup-btn" data-id="{{ $transcation->id }}"
                          data-description="{{ $transcation->description ?? '' }}" title="Add Followup Note">
                          <i class="fa fa-edit"></i>
                        </button>
                        @if(!empty($transcation->description))
                          <i class="fa-solid fa-circle-info info-icon" data-id="{{ $transcation->id }}"
                            data-description="{{ $transcation->description }}"
                            style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"
                            title="View Followup Details"></i>
                        @endif
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

<!-- Followup Modal -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content" style="max-height: 90vh;">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa fa-edit"></i> Add Followup Note
        </h5>
        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="followupForm">
        @csrf
        <input type="hidden" name="id" id="followup_id">
        <div class="modal-body" style="overflow-y: auto;">
          <div class="form-group">
            <label for="followup_description">Note</label>
            <textarea name="description" class="form-control" id="followup_description" rows="6"
              placeholder="Enter followup note..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Follow Up Details Modal (Info) -->
<div class="modal fade" id="followupInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content" style="max-height: 90vh;">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-circle-info"></i> Follow Up Details
        </h5>
        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="overflow-y: auto; max-height: 70vh;">
        <div class="info-section">
          <h6 style="color: #667eea; font-weight: 600; margin-bottom: 10px;">
            <i class="fa fa-sticky-note"></i> Note
          </h6>
          <p id="followupInfoDescription"
            style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea; margin: 0; white-space: pre-wrap; word-wrap: break-word;">
            -
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    // Open followup modal
    $('.followup-btn').on('click', function () {
      const id = $(this).data('id');
      const description = $(this).data('description');

      $('#followup_id').val(id);
      $('#followup_description').val(description);
      $('#followupModal').modal('show');
    });

    // Submit followup form
    $('#followupForm').on('submit', function (e) {
      e.preventDefault();

      const formData = $(this).serialize();

      $.ajax({
        url: "{{ route('business_support.followup') }}",
        type: "POST",
        data: formData,
        success: function (response) {
          if (response.success) {
            $('#followupModal').modal('hide');
            location.reload(); // Reload to show updated data
          } else {
            alert('Error: ' + (response.message || 'Unknown error'));
          }
        },
        error: function (xhr) {
          alert('Error: ' + (xhr.responseJSON?.message || 'Failed to update followup'));
        }
      });
    });

    // Open info modal
    $('.info-icon').on('click', function () {
      const description = $(this).data('description');

      $('#followupInfoDescription').text(description || '-');
      $('#followupInfoModal').modal('show');
    });
  });
</script>

</body>

</html>
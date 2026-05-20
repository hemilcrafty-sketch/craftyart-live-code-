@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">

                <!-- ================= USER INFO ================= -->
                <h5 class="mb-3">User Information</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Picture</th>
                                <th>Email</th>
                                <th>Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $userData['user']['name'] }}</td>

                                <td>
                                    @if(!empty($userData['user']['profile_pic']))
                                        <img src="{{ $userData['user']['profile_pic'] }}"
                                             style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div style="
                                            width:100px;
                                            height:100px;
                                            border-radius:50%;
                                            background:#2EC4B6;
                                            color:white;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            font-size:40px;
                                            font-weight:bold;">
                                            {{ strtoupper(substr($userData['user']['name'],0,1)) }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $userData['user']['email'] }}</td>
                                <td>{{ $userData['user']['contact_no'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <!-- ================= TABS SECTION ================= -->
                <div class="mt-5">

                    <ul class="nav nav-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="subs-tab" data-toggle="tab"
                               href="#subs" role="tab">Subscription History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="drafts-tab" data-toggle="tab"
                               href="#drafts" role="tab">Drafts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="exports-tab" data-toggle="tab"
                               href="#exports" role="tab">Export History</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">

                        <!-- ================= SUBSCRIPTIONS ================= -->
                        <div class="tab-pane fade show active" id="subs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Type</th>
                                            <th>Subscription ID</th>
                                            <th>Transaction ID</th>
                                            <th>Recurred</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Purchase</th>
                                            <th>Billing</th>
                                            <th>Validity</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($userData['subsHistory']))
                                            @foreach ($userData['subsHistory'] as $sub)
                                                <tr>
                                                    <td>{{ $sub['package_name'] }}</td>
                                                    <td>{{ $sub['type'] }}</td>
                                                    <td>{{ $sub['subscription_id'] }}</td>
                                                    <td>{{ $sub['transaction_id'] }}</td>
                                                    <td>{{ $sub['recurred'] }}</td>
                                                    <td>{{ $sub['amount'] }}</td>
                                                    <td>{{ $sub['method'] }}</td>
                                                    <td>{{ $sub['purchase_date'] }}</td>
                                                    <td>{{ $sub['billing_date'] }}</td>
                                                    <td>{{ $sub['validity'] }}</td>
                                                    <td>
                                                        <span class="badge"
                                                              style="background:{{ $sub['color']; }}; color: white">
                                                            {{ $sub['status'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if(!empty($sub['show_refund_btn']))
                                                            <button class="btn btn-outline-danger btn-sm open-refund-modal"
                                                                style="font-size: 12px;"
                                                                data-id="{{ $sub['id'] }}"
                                                                data-amount="{{ $sub['paid_amount'] }}"
                                                                data-currency="{{ $sub['currency_code'] }}"
                                                                data-max-amount="{{ $sub['paid_amount'] }}">
                                                            <i class="fa-solid fa-money-bill-wave"></i> Refund
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    No Subscription History Found
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ================= DRAFTS ================= -->
                        <div class="tab-pane fade" id="drafts" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Thumbnail</th>
                                            <th>Draft ID</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($userData['drafts']))
                                            @foreach($userData['drafts'] as $index => $draft)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>

                                                    <td>
                                                        <img src="{{ $draft['thumb'] }}"
                                                             style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                                                    </td>

                                                    <td>{{ $draft['id'] }}</td>

                                                    <td>{{ $draft['created_at'] }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    No Drafts Found
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <!-- ================= EXPORT HISTORY ================= -->
                        <div class="tab-pane fade" id="exports" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Draft ID</th>
                                            <th>Draft Thumb</th>
                                            <th>Export Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($userData['export']))
                                            @foreach($userData['export'] as $index => $export)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $export['draft_id'] }}</td>
                                                    <td>
                                                        <img src="{{ $export['thumb'] }}"
                                                             style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                                                    </td>
                                                    <td>{{ $export['created_at'] }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    No Export History Found
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="post" id="refundForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="refund_id" id="refund_id">

                    <div class="form-group">
                        <h6>Refund Amount</h6>
                        <div class="input-group">
                            <input type="number" class="form-control" name="refund_amount"
                                   id="refund_amount"
                                   step="0.01"
                                   min="0.01"
                                   required
                                   placeholder="Enter refund amount">
                            <div class="input-group-append">
                                <span class="input-group-text" id="currency_symbol"></span>
                            </div>
                        </div>
                        <small class="text-muted" id="max_amount_info">Maximum refundable: <span id="max_amount_display"></span></small>
                        <div class="invalid-feedback" id="amount_error"></div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                   name="refund_instantly"
                                   id="refund_instantly"
                                   value="1">
                            <label class="custom-control-label" for="refund_instantly">
                                <strong>Refund Instantly</strong>
                            </label>
                        </div>
                        <small class="text-muted">If checked, refund will be processed immediately</small>
                    </div>

                    <div class="form-group">
                        <h6>Refund Reason</h6>
                        <select class="form-control" name="refund_reason" id="refund_reason" required>
                            <option value="">Select reason</option>
                            <option value="duplicate">Duplicate</option>
                            <option value="fraudulent">Fraudulent</option>
                            <option value="requested_by_customer">Requested by Customer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Note <span class="text-danger">*</span></h6>
                        <textarea class="form-control" name="refund_note" id="refund_note"
                                  rows="3"
                                  placeholder="Please provide details..."
                                  required></textarea>
                        <small class="text-muted">Required for all refunds</small>
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-12">
                            <button class="btn btn-danger btn-block" type="submit" id="submit_refund">
                                Process Refund
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>

    let currentRefundData = null;

    // Open refund modal
    $(document).on('click', '.open-refund-modal', function() {
        const refundId = $(this).data('id');
        const amount = $(this).data('amount');
        const currency = $(this).data('currency');
        const maxAmount = $(this).data('max-amount');

        currentRefundData = {
            amount: amount,
            currency: currency,
            maxAmount: maxAmount
        };

        // Set hidden values
        $('#refund_id').val(refundId);

        // Set amount with max limit
        $('#refund_amount').val(amount);
        $('#refund_amount').attr('max', maxAmount);
        $('#max_amount_display').text(currency + ' ' + maxAmount);

        // Update currency symbol
        updateCurrencySymbol(currency);

        // Reset form (except hidden fields)
        $('#refund_reason').val('');
        $('#refund_note').val('');
        $('#refund_instantly').prop('checked', false);
        $('#amount_error').hide();
        $('#refund_amount').removeClass('is-invalid');

        // Show modal
        $('#refundModal').modal('show');
    });

    function updateCurrencySymbol(currency) {
        const symbol = currency === 'USD' ? '$' : (currency === 'INR' ? '₹' : '');
        $('#currency_symbol').text(symbol);
    }

    // Validate amount input
    $('#refund_amount').on('input', function() {
        const inputAmount = parseFloat($(this).val());
        const maxAmount = parseFloat($(this).attr('max'));
        const $errorDiv = $('#amount_error');


        if (isNaN(inputAmount)) {
            $(this).addClass('is-invalid');
            $errorDiv.text('Please enter a valid amount').show();
            return;
        }

        if (inputAmount <= 0) {
            $(this).addClass('is-invalid');
            $errorDiv.text('Amount must be greater than 0').show();
            return;
        }

        if (inputAmount > maxAmount) {
            $(this).addClass('is-invalid');
            $errorDiv.text(`Amount cannot exceed ${maxAmount}`).show();
            return;
        }

        $(this).removeClass('is-invalid');
        $errorDiv.hide();
    });

    // Validate note field
    $('#refund_note').on('input', function() {
        if ($(this).val().trim().length === 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Handle refund form submission
    $('#refundForm').on('submit', function(event) {
        event.preventDefault();

        // Validate amount
        const inputAmount = parseFloat($('#refund_amount').val());
        const maxAmount = parseFloat($('#refund_amount').attr('max'));

        if (isNaN(inputAmount)) {
            $('#amount_error').text('Please enter a valid amount').show();
            $('#refund_amount').addClass('is-invalid');
            $('#refund_amount').focus();
            return false;
        }

        if (inputAmount <= 0) {
            $('#amount_error').text('Amount must be greater than 0').show();
            $('#refund_amount').addClass('is-invalid');
            $('#refund_amount').focus();
            return false;
        }

        if (inputAmount > maxAmount) {
            $('#amount_error').text(`Amount cannot exceed ${currency} ${maxAmount}`).show();
            $('#refund_amount').addClass('is-invalid');
            $('#refund_amount').focus();
            return false;
        }

        // Validate reason
        const reason = $('#refund_reason').val();
        if (!reason) {
            alert('Please select a refund reason');
            $('#refund_reason').focus();
            return false;
        }

        // Validate note
        const note = $('#refund_note').val().trim();
        if (!note) {
            alert('Please provide a note');
            $('#refund_note').focus();
            return false;
        }

        if (!confirm('Are you sure you want to process this refund?')) {
            return false;
        }

        // Show loading state
        const $submitBtn = $('#submit_refund');
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('transactions.refund') }}",
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data.success) {
                    alert(data.msg);
                    location.reload();
                } else {
                    alert(data.msg || 'Refund failed');
                    $submitBtn.prop('disabled', false).text('Process Refund');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while processing refund';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                alert(errorMessage);
                $submitBtn.prop('disabled', false).text('Process Refund');
            }
        });
    });

    // Reset form when modal is closed
    $('#refundModal').on('hidden.bs.modal', function() {
        $('#refundForm')[0].reset();
        $('#amount_error').hide();
        $('#refund_amount').removeClass('is-invalid');
        $('#refund_note').removeClass('is-invalid');
        currentRefundData = null;
    });

    $('#add_transaction_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('custom.transcation') }}",
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#add_transaction_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    location.reload();
                }

            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });
</script>
</body>
</html>

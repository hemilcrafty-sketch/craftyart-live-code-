@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')

<style>
    .subscription-badges .badge {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: help;
    }
    .modal-dialog { max-height: 85vh; display: flex; flex-direction: column; }
    .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
    .modal-header { flex-shrink: 0; }
    .modal-body { overflow-y: auto; overflow-x: hidden; flex: 1 1 auto; }
    .modal-footer { flex-shrink: 0; }
    .discount-badge { font-size: 0.75em; color: #28a745; font-weight: 600; }
</style>

<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                <div class="row justify-content-between">
                    <div class="col-md-3">
                        @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                            <button type="button" class="btn btn-primary m-1" id="addNewbackgroundItemBtn">
                                + Add Offer Package
                            </button>
                        @endif
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                    style="max-height: calc(110vh - 220px) !important">
                    <table id="temp_table" style="table-layout: fixed; width: 100%;"
                        class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Package Name</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>INR Details</th>
                                <th>USD Details</th>
                                <th>Add. Duration</th>
                                <th>Subscription IDs</th>
                                <th>Status</th>
                                <th class="datatable-nosort" style="width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($OfferPackage as $row)
                                @php $pd = $row->plan_details; @endphp
                                <tr id="row_{{ $row->id }}">
                                    <td>{{ $row->id }}</td>
                                    <td><strong>{{ $row->package_name }}</strong></td>
                                    <td>{{ $row->plan->name ?? '-' }}</td>
                                    <td>{{ $row->duration->name ?? '-' }}</td>
                                    <td>
                                        <div><strong>Price:</strong> ₹{{ number_format($pd['inr_price'], 2) }}</div>
                                        <div><strong>Offer:</strong> ₹{{ number_format($pd['inr_offer_price'], 2) }}
                                            <span class="discount-badge">({{ $pd['inr_discount'] }} off)</span>
                                        </div>
                                        @if($pd['inr_trial_days'] > 0)
                                            <div><small>Trial: {{ $pd['inr_trial_days'] }} days @ ₹{{ number_format($pd['inr_trial_price'], 2) }}</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div><strong>Price:</strong> ${{ number_format($pd['usd_price'], 2) }}</div>
                                        <div><strong>Offer:</strong> ${{ number_format($pd['usd_offer_price'], 2) }}
                                        </div>
                                        <span class="discount-badge">({{ $pd['usd_discount'] }} off)</span>
                                        @if($pd['usd_trial_days'] > 0)
                                            <div><small>Trial: {{ $pd['usd_trial_days'] }} days @ ${{ number_format($pd['usd_trial_price'], 2) }}</small></div>
                                        @endif
                                    </td>
                                    <td>{{ $pd['additional_duration'] }}</td>
                                    <td>
                                        @if(!empty($row->subscription_ids) && is_array($row->subscription_ids))
                                            <div class="subscription-badges">
                                                @foreach($row->subscription_ids as $key => $value)
                                                    <span class="badge badge-secondary mr-1 mb-1" title="{{ $value }}">
                                                        {{ $key }}: {{ \Str::limit($value, 10) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="dropdown-item edit-bonus" data-id="{{ $row->id }}">
                                            <i class="dw dw-edit2"></i> Edit
                                        </button>
                                        <button class="dropdown-item delete-bonus" data-id="{{ $row->id }}">
                                            <i class="dw dw-delete-3"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="my-1">
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="bounce_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Offer Package</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="bounce_form" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="bounce_id">
                    <input type="hidden" name="subscription_ids_json" id="subscription_ids_json">

                    <div class="form-group mb-3">
                        <label>Select Plan</label>
                        <select name="plan_id" id="plan_id" class="form-control" required>
                            <option value="">-- Select Plan --</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->string_id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Select Duration</label>
                        <select name="duration_id" id="duration_id" class="form-control" required>
                            <option value="">-- Select Duration --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Package Name</label>
                        <input type="text" name="package_name" id="package_name" class="form-control"
                            placeholder="e.g., Premium Offer, Special Deal" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Additional Duration (days)</label>
                        <input type="number" step="1" min="0" name="additional_duration" id="additional_duration"
                            class="form-control" value="0">
                    </div>

                    <!-- INR Section -->
                    <h6 class="mt-3 mb-2 text-primary">INR Pricing</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>INR Price (Original)</label>
                                <input type="number" step="any" min="0" name="inr_price" id="inr_price"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>INR Offer Price</label>
                                <input type="number" step="any" min="0" name="inr_offer_price" id="inr_offer_price"
                                    class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>INR Trial Days</label>
                                <input type="number" step="1" min="0" name="inr_trial_days" id="inr_trial_days"
                                    class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>INR Trial Price</label>
                                <input type="number" step="any" min="0" name="inr_trial_price" id="inr_trial_price"
                                    class="form-control" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- USD Section -->
                    <h6 class="mt-3 mb-2 text-primary">USD Pricing</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>USD Price (Original)</label>
                                <input type="number" step="any" min="0" name="usd_price" id="usd_price"
                                    class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>USD Offer Price</label>
                                <input type="number" step="any" min="0" name="usd_offer_price" id="usd_offer_price"
                                    class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>USD Trial Days</label>
                                <input type="number" step="1" min="0" name="usd_trial_days" id="usd_trial_days"
                                    class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>USD Trial Price</label>
                                <input type="number" step="any" min="0" name="usd_trial_price" id="usd_trial_price"
                                    class="form-control" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Subscription IDs -->
                    <div class="row mt-3" id="subscriptionIdsSection">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Subscription IDs (Payment Gateway IDs)</h6>
                                        <small class="text-muted">Add gateway keys and their subscription IDs</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSubscriptionFields()">
                                        <i class="fa fa-plus"></i> Add Gateway
                                    </button>
                                </div>
                                <div class="card-body" id="subscriptionFieldsContainer" style="display: none;">
                                    <div id="subscriptionFields"></div>
                                    <div class="text-right mt-3">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addSubscriptionField()">
                                            <i class="fa fa-plus"></i> Add Another Gateway
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3 mt-3">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0" selected>Inactive</option>
                        </select>
                    </div>

                    <div class="align-content-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    let subscriptionFieldsVisible = false;

    // Plan change
    $(document).on("change", "#plan_id", function () {
        let planId = $(this).val();
        $("#duration_id").html('<option value="">-- Select Duration --</option>');
        if (planId) {
            $.ajax({
                url: "{{ route('offer-package.getDurations', '') }}/" + planId,
                type: "GET",
                success: function (res) {
                    $.each(res, function (index, item) {
                        $("#duration_id").append(
                            `<option value="${item.duration_id}" data-duration="${item.duration_id}">${item.duration_name}</option>`
                        );
                    });
                }
            });
        }
    });

    $(document).on("change", "#duration_id", function () {
        $("#duration_id").val($(this).find(":selected").data("duration"));
    });

    // Add New
    $(document).on("click", "#addNewbackgroundItemBtn", function () {
        $("#bounce_form")[0].reset();
        $("#bounce_id").val("");
        $("#duration_id").html('<option value="">-- Select Sub Plan & Duration --</option>');
        clearSubscriptionFields();
        $("#modal_title").text("Add Offer Package");
        $("#bounce_modal").modal("show");
    });

    // Edit
    $(document).on("click", ".edit-bonus", function () {
        let id = $(this).data("id");
        $.get("{{ url('offer-package') }}/" + id + "/edit", function (res) {
            const pd = res.plan_details || {};
            $("#bounce_id").val(res.id);
            $("#plan_id").val(res.plan_id).trigger("change");

            setTimeout(() => {
                $("#duration_id").val(res.duration_id).trigger("change");
                $("#duration_id").val(res.duration_id);
            }, 500);

            $("#package_name").val(res.package_name);
            $("#additional_duration").val(pd.additional_duration || 0);

            $("#inr_price").val(pd.inr_price || 0);
            $("#inr_offer_price").val(pd.inr_offer_price || 0);
            $("#inr_trial_days").val(pd.inr_trial_days || 0);
            $("#inr_trial_price").val(pd.inr_trial_price || 0);

            $("#usd_price").val(pd.usd_price || 0);
            $("#usd_offer_price").val(pd.usd_offer_price || 0);
            $("#usd_trial_days").val(pd.usd_trial_days || 0);
            $("#usd_trial_price").val(pd.usd_trial_price || 0);

            if (res.subscription_ids && Object.keys(res.subscription_ids).length > 0) {
                loadSubscriptionFields(res.subscription_ids);
            } else {
                clearSubscriptionFields();
            }

            $("#status").val(res.status);
            $("#modal_title").text("Edit Offer Package");
            $("#bounce_modal").modal("show");
        });
    });

    // Save
    $("#bounce_form").on("submit", function (e) {
        e.preventDefault();
        const subscriptionIds = getSubscriptionIds();
        $("#subscription_ids_json").val(JSON.stringify(subscriptionIds));

        $.post("{{ route('offer-package.store') }}", $(this).serialize(), function (res) {
            if (res.status) {
                $("#bounce_modal").modal("hide");
                window.location.reload();
            } else {
                alert(res.message);
            }
        });
    });

    // Delete
    $(document).on("click", ".delete-bonus", function () {
        if (!confirm("Are you sure?")) return;
        let id = $(this).data("id");
        $.ajax({
            url: "{{ url('offer-package') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function (res) {
                if (res.status) $("#row_" + id).remove();
            }
        });
    });

    /* Subscription helpers */
    function toggleSubscriptionFields() {
        const container = document.getElementById('subscriptionFieldsContainer');
        const btn = document.querySelector('#subscriptionIdsSection .card-header button');
        if (!subscriptionFieldsVisible) {
            container.style.display = 'block';
            btn.innerHTML = '<i class="fa fa-minus"></i> Hide Gateway Fields';
            btn.classList.replace('btn-outline-primary', 'btn-outline-secondary');
            if (!document.querySelectorAll('#subscriptionFields .subscription-field-row').length) addSubscriptionField();
        } else {
            container.style.display = 'none';
            btn.innerHTML = '<i class="fa fa-plus"></i> Add Gateway';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        }
        subscriptionFieldsVisible = !subscriptionFieldsVisible;
    }

    function addSubscriptionField(key = '', value = '') {
        const row = document.createElement('div');
        row.className = 'row subscription-field-row mb-2';
        row.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control subscription-key" placeholder="Gateway Key (e.g., razorpay)" value="${key}">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control subscription-value" placeholder="Subscription ID" value="${value}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.subscription-field-row').remove()">
                    <i class="fa fa-trash"></i>
                </button>
            </div>`;
        document.getElementById('subscriptionFields').appendChild(row);
    }

    function getSubscriptionIds() {
        const data = {};
        document.querySelectorAll('#subscriptionFields .subscription-field-row').forEach(row => {
            const k = row.querySelector('.subscription-key').value.trim();
            const v = row.querySelector('.subscription-value').value.trim();
            if (k && v) data[k] = v;
        });
        return data;
    }

    function clearSubscriptionFields() {
        document.getElementById('subscriptionFields').innerHTML = '';
        subscriptionFieldsVisible = false;
        document.getElementById('subscriptionFieldsContainer').style.display = 'none';
        const btn = document.querySelector('#subscriptionIdsSection .card-header button');
        btn.innerHTML = '<i class="fa fa-plus"></i> Add Gateway';
        btn.classList.replace('btn-outline-secondary', 'btn-outline-primary');
    }

    function loadSubscriptionFields(subscriptionIds) {
        clearSubscriptionFields();
        if (subscriptionIds && Object.keys(subscriptionIds).length > 0) {
            document.getElementById('subscriptionFieldsContainer').style.display = 'block';
            const btn = document.querySelector('#subscriptionIdsSection .card-header button');
            btn.innerHTML = '<i class="fa fa-minus"></i> Hide Gateway Fields';
            btn.classList.replace('btn-outline-primary', 'btn-outline-secondary');
            subscriptionFieldsVisible = true;
            for (const [k, v] of Object.entries(subscriptionIds)) addSubscriptionField(k, v);
        }
    }
</script>

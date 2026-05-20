@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')

<style>
.subscription-badges .badge{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:help}
#bounce_modal .modal-dialog,#bounce_modal .modal-content{max-height:calc(100vh - 2rem);display:flex;flex-direction:column}
#bounce_modal .modal-dialog{max-width:min(900px,100vw - 1.5rem);margin:1rem auto}
#bounce_modal .modal-body{overflow-y:auto;flex:1 1 auto}
.discount-badge{font-size:.75em;color:#28a745;font-weight:600}
#bounce_val_summary ul{max-height:12rem;overflow-y:auto}
#bounce_val_summary strong{display:block;margin-bottom:.35rem}
</style>

@php
    $offerPriceCol = function ($sym, $pd, $pfx) {
        $g = function ($k, $def = null) use ($pd, $pfx) {
            return $pd[$pfx . '_' . $k] ?? $def;
        };
        $m = function ($k) use ($g) {
            return number_format((float) $g($k, 0), 2);
        };
        $h = '<div><strong>Price:</strong> ' . $sym . $m('price') . '</div>'
            . '<div><strong>Offer:</strong> ' . $sym . $m('offer_price') . ' <span class="discount-badge">(' . e($g('discount', '')) . ' off)</span></div>';
        if ((int) $g('trial_days', 0) > 0) {
            $h .= '<div><small>Trial: ' . e($g('trial_days')) . ' days @ ' . $sym . $m('trial_price') . '</small></div>';
        }
        return $h;
    };
@endphp

<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display:flex;flex-direction:column;height:90vh;overflow:hidden">
                <div class="row justify-content-between">
                    <div class="col-md-3">
                        @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                            <button type="button" class="btn btn-primary m-1" id="addOfferPackageBtn">+ Add Offer Package</button>
                        @endif
                    </div>
                </div>
                <div class="scroll-wrapper table-responsive tableFixHead" style="max-height:calc(110vh - 220px)!important">
                    <table id="temp_table" class="table table-striped table-bordered mb-0" style="table-layout:fixed;width:100%">
                        <thead>
                            <tr>
                                <th>Id</th><th>Package Name</th><th>Plan</th><th>Duration</th>
                                <th>INR Details</th><th>USD Details</th><th>Add. Duration</th><th>Subscription IDs</th><th>Status</th>
                                <th class="datatable-nosort" style="width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($OfferPackage as $row)
                                @php
                                    $pd = is_array($row->plan_details ?? null) ? $row->plan_details : [];
                                    $dur = ($row->duration_id ?? '') === 'custom'
                                        ? ($row->custom_days ? 'Custom (' . $row->custom_days . ' days)' : 'Custom')
                                        : ($row->duration->name ?? '-');
                                @endphp
                                <tr id="row_{{ $row->id }}">
                                    <td>{{ $row->id }}</td>
                                    <td><strong>{{ $row->package_name }}</strong></td>
                                    <td>{{ $row->plan->name ?? '-' }}</td>
                                    <td>{{ $dur }}</td>
                                    <td>{!! $offerPriceCol('₹', $pd, 'inr') !!}</td>
                                    <td>{!! $offerPriceCol('$', $pd, 'usd') !!}</td>
                                    <td>{{ $pd['additional_duration'] ?? '' }}</td>
                                    <td>
                                        @if (is_array($row->subscription_ids) && $row->subscription_ids !== [])
                                            <div class="subscription-badges">
                                                @foreach ($row->subscription_ids as $key => $value)
                                                    <span class="badge badge-secondary mr-1 mb-1" title="{{ $value }}">{{ $key }}: {{ \Str::limit($value, 10) }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-{{ $row->status ? 'success' : 'danger' }}">{{ $row->status ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-nowrap">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary edit-offer-package" data-id="{{ $row->id }}"><i class="dw dw-edit2"></i> Edit</button>
                                            <button type="button" class="btn btn-outline-danger delete-offer-package" data-id="{{ $row->id }}"><i class="dw dw-delete-3"></i> Delete</button>
                                        </div>
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

<div class="modal fade" id="bounce_modal" tabindex="-1" role="dialog" aria-labelledby="modal_title">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Offer Package</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="bounce_form" method="POST">
                    <div id="bounce_val_summary" class="alert alert-danger small mb-3" style="display:none" role="alert"></div>
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
                        <select name="duration_id" id="duration_id_select" class="form-control" required><option value="">-- Select Duration --</option></select>
                    </div>
                    <div class="form-group mb-3" id="custom_days_group" style="display:none">
                        <label>Custom days</label>
                        <input type="number" name="custom_days" id="custom_days" class="form-control" min="1" step="1" placeholder="Number of days">
                    </div>
                    <div class="form-group mb-3">
                        <label>Package name</label>
                        <input type="text" name="package_name" id="package_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Additional duration (days)</label>
                        <input type="number" step="1" min="0" name="additional_duration" id="additional_duration" class="form-control" value="0">
                    </div>

                    @foreach (['inr' => 'INR', 'usd' => 'USD'] as $code => $label)
                        <h6 class="text-primary mb-2">{{ $label }}</h6>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group mb-3">
                                <label>{{ $label }} price (original)</label>
                                <input type="number" step="any" min="0" name="{{ $code }}_price" id="{{ $code }}_price" class="form-control" required>
                            </div></div>
                            <div class="col-md-6"><div class="form-group mb-3">
                                <label>{{ $label }} offer price</label>
                                <input type="number" step="any" min="0" name="{{ $code }}_offer_price" id="{{ $code }}_offer_price" class="form-control" required>
                            </div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group mb-3">
                                <label>{{ $label }} trial days</label>
                                <input type="number" step="1" min="0" name="{{ $code }}_trial_days" id="{{ $code }}_trial_days" class="form-control" value="0">
                            </div></div>
                            <div class="col-md-6"><div class="form-group mb-3">
                                <label>{{ $label }} trial price</label>
                                <input type="number" step="any" min="0" name="{{ $code }}_trial_price" id="{{ $code }}_trial_price" class="form-control" value="0">
                            </div></div>
                        </div>
                    @endforeach
                    <div class="card mb-3" id="subscriptionIdsSection">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                            <span class="font-weight-bold mb-0">Subscription IDs</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleSubscriptionFieldsBtn" onclick="toggleSubscriptionFields()"><i class="fa fa-plus"></i> Add gateway</button>
                        </div>
                        <div class="card-body py-2" id="subscriptionFieldsContainer" style="display:none">
                            <div id="subscriptionFields"></div>
                            <button type="button" class="btn btn-success btn-sm mt-2" onclick="addSubscriptionField()"><i class="fa fa-plus"></i> Add another</button>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0" selected>Inactive</option>
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
(function () {
    var CUSTOM = @json(\App\Http\Controllers\Pricing\OfferPackageController::SUB_PLAN_CUSTOM),
        DUR = '<option value="">-- Select Duration --</option>',
        API_D = @json(rtrim(route('offer-package.getDurations', ''), '/')),
        API_O = @json(rtrim(url('offer-package'), '/')),
        $b = $("#duration_id_select"),
        subVis = !1, pend = null;

    function syncDur() {
        var v = $b.val();
        if (v === CUSTOM) {
            $("#custom_days_group").show();
            $("#custom_days").prop("required", !0);
        } else {
            $("#custom_days_group").hide();
            $("#custom_days").prop("required", !1).val("");
        }
    }
    function addCustomOpt() {
        if (!$b.find('option[value="' + CUSTOM + '"]').length)
            $b.append('<option value="' + CUSTOM + '" data-duration="custom" data-is-annual="0" data-duration-days="">Custom days</option>');
    }
    function applyPend(pid) {
        var p = pend;
        if (!p || String(p.planId) !== String(pid)) return;
        pend = null;
        if (p.isCustom) {
            addCustomOpt();
            $b.val(CUSTOM).trigger("change");
            $("#custom_days").val(p.customDays != null && p.customDays !== "" ? String(p.customDays) : "");
        } else {
            $b.val(p.durationId != null ? String(p.durationId) : "").trigger("change");
        }
    }
    function resetF() {
        clearVal();
        pend = null; $("#bounce_form")[0].reset(); $("#bounce_id").val(""); $b.html(DUR);
        $("#custom_days_group").hide(); $("#custom_days").prop("required", !1);
    }
    function flatErr(j) {
        if (!j || !j.errors || typeof j.errors !== "object") return j && j.message ? String(j.message) : "Save failed.";
        var o = [], k, v, m, keys = Object.keys(j.errors);
        for (k = 0; k < keys.length; k++) {
            v = j.errors[keys[k]];
            if (Array.isArray(v)) { for (m = 0; m < v.length; m++) if (v[m]) o.push(String(v[m])); }
            else if (v) o.push(String(v));
        }
        return o.length ? o.join("\n") : (j.message || "Save failed.");
    }
    function errSel(k) {
        var b = String(k).split(".")[0];
        if (b === "duration_id") return "#duration_id_select";
        return document.getElementById(b) ? "#" + b : null;
    }
    function clearVal() {
        $("#bounce_val_summary").hide().empty();
        $("#bounce_form .is-invalid").removeClass("is-invalid");
        $("#bounce_form .field-err-msg").remove();
    }
    function errLabel(k) {
        return k.replace(/_/g, " ");
    }
    function showVal(errors) {
        clearVal();
        if (!errors || typeof errors !== "object") return;
        var $ul = $("<ul class=\"mb-0 pl-3\"></ul>"), k, v, i, s, m, $row, $inp, msg, $fb;
        for (k in errors) {
            if (!Object.prototype.hasOwnProperty.call(errors, k)) continue;
            v = errors[k];
            if (!Array.isArray(v)) v = [v];
            msg = v.filter(Boolean).join(" ");
            for (i = 0; i < v.length; i++) if (v[i]) $ul.append($("<li></li>").text(errLabel(k) + " — " + v[i]));
            s = errSel(k);
            if (s) $(s).addClass("is-invalid");
        }
        if ($ul.children().length) {
            $("#bounce_val_summary").append($("<strong></strong>").text("Please fix the errors below:"), $ul).show();
            $("#bounce_modal .modal-body").scrollTop(0);
        }
    }

    $(document).on("input change", "#bounce_form .form-control", function () {
        $(this).removeClass("is-invalid");
        $(this).siblings(".field-err-msg").remove();
    });
    $(document).on("change", "#plan_id", function () {
        var pid = $(this).val();
        if (pend && String(pend.planId) !== String(pid)) pend = null;
        $b.html(DUR); $("#duration_id").val("");
        if (!pid) return;
        $.get(API_D + "/" + encodeURIComponent(pid), function (res) {
            $.each(res, function (_, it) {
                $b.append($("<option></option>").val(it.duration_id != null ? String(it.duration_id) : "")
                    .attr("data-is-annual", parseInt(it.is_annual, 10) === 1 ? "1" : "0")
                    .attr("data-duration-days", it.duration_days != null && it.duration_days !== "" ? String(it.duration_days) : "")
                    .text(it.duration_name));
            });
            addCustomOpt(); applyPend(pid);
        });
    });
    $(document).on("change", "#duration_id_select", function () { syncDur(); });
    $(document).on("input change", "#custom_days", function () { });
    $(document).on("click", "#addOfferPackageBtn", function () { resetF(); clrSub(); $("#modal_title").text("Add Offer Package"); $("#bounce_modal").modal("show"); });
    $(document).on("click", ".edit-offer-package", function () {
        var id = $(this).data("id");
        $.get(API_O + "/" + encodeURIComponent(id) + "/edit", function (res) {
            var pd = res.plan_details || {}, k, map = { additional_duration: "#additional_duration", inr_price: "#inr_price", inr_offer_price: "#inr_offer_price", inr_trial_days: "#inr_trial_days", inr_trial_price: "#inr_trial_price", usd_price: "#usd_price", usd_offer_price: "#usd_offer_price", usd_trial_days: "#usd_trial_days", usd_trial_price: "#usd_trial_price" };
            resetF(); $("#bounce_id").val(res.id);
            pend = res.plan_id != null && String(res.plan_id).trim() !== "" ? { planId: String(res.plan_id), isCustom: res.duration_id === "custom", customDays: res.custom_days, durationId: res.duration_id } : null;
            $("#plan_id").val(res.plan_id).trigger("change"); $("#package_name").val(res.package_name);
            for (k in map) if (Object.prototype.hasOwnProperty.call(map, k)) $(map[k]).val(pd[k] || 0);
            if (res.subscription_ids && Object.keys(res.subscription_ids).length) loadSub(res.subscription_ids); else clrSub();
            $("#status").val(res.status); $("#modal_title").text("Edit Offer Package"); $("#bounce_modal").modal("show");
        });
    });
    $("#bounce_form").on("submit", function (e) {
        e.preventDefault();
        var $form = $(this), $btn = $form.find('button[type="submit"]'), originalBtnHtml = $btn.html();
        $("#subscription_ids_json").val(JSON.stringify(getSub()));
        
        $btn.prop("disabled", !0).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        $.ajax({
            url: "{{ route('offer-package.store') }}",
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
            success: function (res) {
                $btn.prop("disabled", !1).html(originalBtnHtml);
                if (res.errors && typeof res.errors === "object" && Object.keys(res.errors).length) {
                    showVal(res.errors);
                    return;
                }
                if (res.status) {
                    clearVal();
                    $("#bounce_modal").modal("hide");
                    location.reload();
                    return;
                }
                alert(res.message || "Could not save.");
            },
            error: function (xhr) {
                $btn.prop("disabled", !1).html(originalBtnHtml);
                if (xhr.status === 419) {
                    alert("Session expired. Refresh the page.");
                    return;
                }
                var j = xhr.responseJSON || {};
                if (xhr.status === 422 && j.errors) {
                    showVal(j.errors);
                    return;
                }
                alert(flatErr(j));
            }
        });
    });
    $(document).on("click", ".delete-offer-package", function () {
        if (!confirm("Are you sure?")) return;
        var id = $(this).data("id");
        $.ajax({ url: API_O + "/" + encodeURIComponent(id), type: "DELETE", data: { _token: "{{ csrf_token() }}" }, success: function (res) { if (res.status) $("#row_" + id).remove(); } });
    });

    function subSync() {
        var btn = document.getElementById("toggleSubscriptionFieldsBtn"); if (!btn) return;
        if (subVis) { btn.innerHTML = '<i class="fa fa-minus"></i> Hide'; btn.classList.replace("btn-outline-primary", "btn-outline-secondary"); }
        else { btn.innerHTML = '<i class="fa fa-plus"></i> Add gateway'; btn.classList.replace("btn-outline-secondary", "btn-outline-primary"); }
    }
    window.toggleSubscriptionFields = function () {
        var box = document.getElementById("subscriptionFieldsContainer"); if (!box) return;
        subVis = !subVis; box.style.display = subVis ? "block" : "none"; subSync();
        if (subVis && document.querySelectorAll("#subscriptionFields .subscription-field-row").length === 0) addSubscriptionField();
    };
    window.addSubscriptionField = function (key, value) {
        var row = document.createElement("div"); row.className = "row subscription-field-row mb-2";
        row.innerHTML = '<div class="col-md-5"><input type="text" class="form-control subscription-key" placeholder="Gateway key"></div><div class="col-md-5"><input type="text" class="form-control subscription-value" placeholder="Subscription ID"></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'.subscription-field-row\').remove()"><i class="fa fa-trash"></i></button></div>';
        row.querySelector(".subscription-key").value = key != null ? key : ""; row.querySelector(".subscription-value").value = value != null ? value : "";
        document.getElementById("subscriptionFields").appendChild(row);
    };
    function getSub() {
        var data = {};
        document.querySelectorAll("#subscriptionFields .subscription-field-row").forEach(function (row) {
            var k = row.querySelector(".subscription-key").value.trim(), v = row.querySelector(".subscription-value").value.trim();
            if (k && v) data[k] = v;
        });
        return data;
    }
    function clrSub() {
        subVis = !1; document.getElementById("subscriptionFields").innerHTML = "";
        var box = document.getElementById("subscriptionFieldsContainer"); if (box) box.style.display = "none"; subSync();
    }
    function loadSub(ids) {
        clrSub();
        if (ids && Object.keys(ids).length) {
            subVis = !0; document.getElementById("subscriptionFieldsContainer").style.display = "block"; subSync();
            Object.keys(ids).forEach(function (k) { addSubscriptionField(k, ids[k]); });
        }
    }
})();
</script>

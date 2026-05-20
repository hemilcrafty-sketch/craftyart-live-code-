@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')

<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display:flex;flex-direction:column;height:90vh;overflow:hidden">
                <div class="row justify-content-between p-3">
                    <div class="col-md-3">
                        @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                            <button type="button" class="btn btn-primary" id="addOfferPageBtn">+ Add Offer Page</button>
                        @endif
                    </div>
                </div>
                <div class="table-responsive tableFixHead" style="flex:1;overflow-y:auto">
                    <table id="offer_page_table" class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px">Id</th>
                                <th>Package Name</th>
                                <th>Slug</th>
                                <th>Instructions Status</th>
                                <th>Instructions</th>
                                <th>Show Addon</th>
                                <th class="datatable-nosort" style="width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offerPages as $row)
                                <tr id="row_{{ $row->id }}">
                                    <td>{{ $row->id }}</td>
                                    <td><strong>{{ $row->offerPackage->package_name ?? '-' }}</strong></td>
                                    <td><code>{{ $row->slug }}</code></td>
                                    <td>
                                        <span class="badge badge-{{ $row->enable_instructions ? 'success' : 'secondary' }}">
                                            {{ $row->enable_instructions ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($row->enable_instructions)
                                            <span class="text-muted d-inline-block " style="max-width: 150px;" title="{{ $row->instructions }}">
                                                {{ $row->instructions }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $row->is_show_addon ? 'info' : 'secondary' }}">
                                            {{ $row->is_show_addon ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary edit-offer-page" data-id="{{ $row->id }}"><i class="dw dw-edit2"></i> Edit</button>
                                            <button type="button" class="btn btn-outline-danger delete-offer-page" data-id="{{ $row->id }}"><i class="dw dw-delete-3"></i> Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="offer_page_modal" tabindex="-1" role="dialog" aria-labelledby="modal_title">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Offer Page</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="offer_page_form" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="offer_page_id">
                    
                    <div class="form-group mb-3">
                        <label>Select Offer Package</label>
                        <select name="offer_package_id" id="offer_package_id" class="form-control" required>
                            <option value="">-- Select Package --</option>
                            @foreach ($offerPackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->package_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" required placeholder="e.g. wedding-offer">
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enable_instructions" name="enable_instructions" value="1">
                        <label class="form-check-label" for="enable_instructions">Enable instructions</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_show_addon" name="is_show_addon" value="1">
                        <label class="form-check-label" for="is_show_addon">Show Addon</label>
                    </div>

                    <div id="instructions_section" class="mb-3" style="display:none">
                        <label for="instructions" class="small font-weight-bold">Instruction text</label>
                        <textarea class="form-control" name="instructions" id="instructions" rows="4" placeholder="Next: @{{next_payment_date}}..."></textarea>
                        <div class="mt-2 small text-muted">
                            <strong>Supported Tokens:</strong> 
                            <code class="cursor-pointer" title="Click to insert" onclick="insertToken('@{{next_payment_date}}')">@{{next_payment_date}}</code>, 
                            <code class="cursor-pointer" title="Click to insert" onclick="insertToken('@{{trial_days}}')">@{{trial_days}}</code>, 
                            <code class="cursor-pointer" title="Click to insert" onclick="insertToken('@{{trial_amount}}')">@{{trial_amount}}</code>
                        </div>
                        <div id="instruction_preview_box" class="mt-2 p-2 border rounded bg-light small" style="display:none">
                            <strong>Preview:</strong> <span id="instruction_preview_text"></span>
                        </div>
                    </div>

                    <div class="text-right mt-4">
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
window.insertToken = function(token) {
    const el = document.getElementById('instructions');
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const text = el.value;
    el.value = text.substring(0, start) + token + text.substring(end);
    el.focus();
    el.selectionStart = el.selectionEnd = start + token.length;
    $(el).trigger('input');
};

(function () {
    const $modal = $("#offer_page_modal");
    const $form = $("#offer_page_form");
    const $idInput = $("#offer_page_id");
    const $instrSection = $("#instructions_section");
    const $instrInput = $("#instructions");
    const $pkgSelect = $("#offer_package_id");
    const $previewBox = $("#instruction_preview_box");
    const $previewText = $("#instruction_preview_text");
    
    const PACKAGES = @json($offerPackages);
    const API_BASE = "{{ url('offer-page') }}";

    function getSelectedPackage() {
        const id = $pkgSelect.val();
        return PACKAGES.find(p => String(p.id) === String(id));
    }

    function formatDate(date) {
        return date.toLocaleDateString("en-IN", { day: "numeric", month: "long", year: "numeric" });
    }

    function calculateNextPaymentDate(pkg) {
        let d = new Date();
        d.setHours(0, 0, 0, 0);

        const pd = pkg.plan_details || {};
        const trialDays = parseInt(pd.inr_trial_days) || 0;
        const trialPrice = parseFloat(pd.inr_trial_price) || 0;

        if (trialDays > 0 && trialPrice > 0) {
            d.setDate(d.getDate() + trialDays);
            return formatDate(d);
        }

        if (pkg.duration_id === 'custom') {
            const days = parseInt(pkg.custom_days) || 30;
            d.setDate(d.getDate() + days);
        } else if (pkg.duration) {
            if (parseInt(pkg.duration.is_annual) === 1) {
                d.setFullYear(d.getFullYear() + 1);
            } else {
                const durStr = String(pkg.duration.duration || '');
                const match = durStr.match(/\d+/);
                const days = match ? parseInt(match[0]) : 30;
                d.setDate(d.getDate() + days);
            }
        } else {
            d.setDate(d.getDate() + 30);
        }

        return formatDate(d);
    }

    function updatePreview() {
        const text = $instrInput.val().trim();
        const pkg = getSelectedPackage();

        if (!text || !pkg || !$("#enable_instructions").is(":checked")) {
            $previewBox.hide();
            return;
        }

        const pd = pkg.plan_details || {};
        const trialDays = String(pd.inr_trial_days || 0);
        const trialAmount = "₹" + (parseFloat(pd.inr_trial_price) || 0).toLocaleString("en-IN");
        const nextDate = calculateNextPaymentDate(pkg);

        let preview = text
            .replace(/@{{next_payment_date}}/g, nextDate)
            .replace(/@{{trial_days}}/g, trialDays)
            .replace(/@{{trail_days}}/g, trialDays)
            .replace(/@{{trial_amount}}/g, trialAmount)
            .replace(/@{{trail_amount}}/g, trialAmount);

        $previewText.text(preview);
        $previewBox.show();
    }

    function resetForm() {
        $form[0].reset();
        $idInput.val("");
        $instrSection.hide();
        $previewBox.hide();
        $("#is_show_addon").prop("checked", false);
        $("#modal_title").text("Add Offer Page");
    }

    $("#enable_instructions").on("change", function() {
        $instrSection.toggle(this.checked);
        updatePreview();
    });

    $instrInput.on("input", updatePreview);
    $pkgSelect.on("change", updatePreview);

    $("#addOfferPageBtn").on("click", function () {
        resetForm();
        $modal.modal("show");
    });

    $(document).on("click", ".edit-offer-page", function () {
        const id = $(this).data("id");
        $.get(`${API_BASE}/${id}/edit`, function (res) {
            resetForm();
            $("#modal_title").text("Edit Offer Page");
            $idInput.val(res.id);
            $pkgSelect.val(res.offer_package_id);
            $("#slug").val(res.slug);
            $("#enable_instructions").prop("checked", !!res.enable_instructions).trigger("change");
            $("#is_show_addon").prop("checked", !!res.is_show_addon);
            $instrInput.val(res.instructions);
            updatePreview();
            $modal.modal("show");
        });
    });

    $form.on("submit", function (e) {
        e.preventDefault();
        const $btn = $form.find('button[type="submit"]');
        const originalHtml = $btn.html();
        
        $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: "{{ route('offer-page.store') }}",
            type: "POST",
            data: $form.serialize(),
            success: function (res) {
                if (res.status) {
                    location.reload();
                } else {
                    alert(res.message || "Error saving data");
                    $btn.prop("disabled", false).html(originalHtml);
                }
            },
            error: function (xhr) {
                $btn.prop("disabled", false).html(originalHtml);
                const err = xhr.responseJSON;
                if (err && err.errors) {
                    alert(Object.values(err.errors).flat().join("\n"));
                } else {
                    alert("Something went wrong!");
                }
            }
        });
    });

    $(document).on("click", ".delete-offer-page", function () {
        if (!confirm("Are you sure you want to delete this page?")) return;
        const id = $(this).data("id");
        $.ajax({
            url: `${API_BASE}/${id}`,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function (res) {
                if (res.status) {
                    $(`#row_${id}`).remove();
                }
            }
        });
    });
})();
</script>


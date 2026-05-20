@include('layouts.masterhead')
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
<div class="main-container">
    <div class="pd-ltr-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    @if (isset($error))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Table -->
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: 100vh !important; overflow-y: auto;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0"
                            style="table-layout: fixed; width: 100%;">


                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Email / Phone</th>
                                    <th>Amount</th>
                                    <th>Product Qty</th>
                                    <th>Product Title</th>
                                    <th>Refund</th>
                                    <th>Status</th>
                                    <th>FollowUp</th>
                                    <th>Follow By</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>

                            <tbody id="tableBody">
                                @if ($transactions->count() > 0)
                                    @foreach ($transactions as $index => $transaction)
                                        <tr class="lead-row" data-transaction-id="{{ $transaction['_id'] ?? '' }}">
                                            <td style="white-space: nowrap; text-align: center;">
                                                <span style="color: #007bff; font-weight: 600;">
                                                    {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $index + 1 }}
                                                </span>
                                            </td>

                                            <td style="word-break: break-word;">
                                                <strong>{{ $transaction['buyerDetails']['email'] ?? '-' }}</strong>
                                                <br>
                                                <small style="color: #666;">{{ $transaction['buyerDetails']['countryCode'] ?? '' }} {{ $transaction['buyerDetails']['phone'] ?? '-' }}</small>
                                            </td>

                                            <td style="white-space: nowrap; text-align: right;">
                                                <strong>{{ $transaction['amountPaid'] ?? 0 }} {{ $transaction['currency'] ?? 'INR' }}</strong>
                                            </td>

                                            <td style="white-space: nowrap; text-align: center;">
                                                @if (!empty($transaction['productQuantity']))
                                                    {{ implode(', ', $transaction['productQuantity']) }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td style="word-break: break-word;">
                                                {{ $transaction['productTitle'] ?? '-' }}
                                            </td>

                                            <td style="white-space: nowrap; text-align: center;">
                                                <span class="badge {{ $transaction['refund'] === 'none' ? 'badge-success' : 'badge-warning' }}">
                                                    {{ ucfirst($transaction['refund'] ?? 'none') }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                @php
                                                    $status = $transaction['status'] ?? '';
                                                    $badgeClass = 'badge-success';
                                                    if ($status === 'pending') {
                                                        $badgeClass = 'badge-warning';
                                                    } elseif ($status === 'failed') {
                                                        $badgeClass = 'badge-danger';
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('payment', '', $status)) }}</span>
                                            </td>

                                            <td style="position: relative; text-align:center;">
                                                @php
                                                    $canEditFollowup = false;
                                                    $currentUserId = auth()->user()->id;
                                                    $isSalesUser = $roleManager::isSalesEmployee(auth()->user()->user_type);
                                                    $isAdminOrManager = $roleManager::isAdmin(auth()->user()->user_type) ||
                                                                       $roleManager::isManager(auth()->user()->user_type) ||
                                                                       $roleManager::isSalesManager(auth()->user()->user_type);

                                                    if ($isAdminOrManager) {
                                                        $canEditFollowup = true;
                                                    } elseif ($isSalesUser) {
                                                        $empId = $transaction['emp_id'] ?? 0;
                                                        if (empty($empId) || $empId == 0 || $empId == $currentUserId) {
                                                            $canEditFollowup = true;
                                                        }
                                                    }
                                                @endphp

                                                <input type="checkbox"
                                                    class="followup-switch"
                                                    data-cosmofeed-id="{{ $transaction['_id'] ?? '' }}"
                                                    data-emp-id="{{ $transaction['emp_id'] ?? 0 }}"
                                                    @if (($transaction['followup_call'] ?? 0) == 1) checked @endif
                                                    @if (!$canEditFollowup) disabled @endif />

                                                @if (($transaction['followup_call'] ?? 0) == 1 && (!empty($transaction['followup_note']) || !empty($transaction['followup_label'])))
                                                    <i class="fa-solid fa-circle-info info-icon"
                                                        data-cosmofeed-id="{{ $transaction['_id'] ?? '' }}"
                                                        data-note="{{ $transaction['followup_note'] ?? '' }}"
                                                        data-label="{{ $transaction['followup_label'] ?? '' }}"
                                                        data-label-display="{{ $followupLabels[$transaction['followup_label'] ?? ''] ?? $transaction['followup_label'] ?? '' }}"
                                                        data-can-edit="{{ $canEditFollowup ? '1' : '0' }}"
                                                        style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>
                                                @endif
                                            </td>

                                            <td style="white-space:nowrap;">
                                                {{ $roleManager::getUploaderName($transaction['emp_id'] ?? 0) }}
                                            </td>

                                            <td style="white-space: nowrap;">
                                                {{ \Carbon\Carbon::parse($transaction['date'] ?? '')->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center" style="padding: 40px;">
                                            <p style="color: #999; margin: 0;">No leads found</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $transactions])
            </div>
        </div>
    </div>
</div>

<!-- Followup Modal -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="followupForm">
            @csrf
            <input type="hidden" name="cosmofeed_transaction_id" id="followup_cosmofeed_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow Up Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="followup_label">Followup Type</label>
                        <select name="followup_label" class="form-control" id="followup_label" required>
                            <option value="">Select label</option>
                            @foreach ($followupLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="followup_note">Note</label>
                        <textarea name="followup_note" class="form-control" id="followup_note" rows="4"
                            placeholder="Enter note"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Followup Info Modal -->
<div class="modal fade" id="followupInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 20px 25px; border-radius: 16px 16px 0 0;">
                <div style="display: flex; align-items: center; width: 100%;">
                    <div
                        style="background: rgba(255,255,255,0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fa-solid fa-circle-info" style="font-size: 20px; color: #fff;"></i>
                    </div>
                    <h5 class="modal-title" style="color: #ffffff; font-weight: 700; font-size: 18px; margin: 0;">
                        Follow Up Details
                    </h5>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal"
                    style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 24px; font-weight: 300; margin: 0; padding: 0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <i class="fa-solid fa-tag" style="color: #667eea; margin-right: 10px; font-size: 16px;"></i>
                        <strong
                            style="color: #495057; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Label</strong>
                    </div>
                    <div id="followupInfoLabel"
                        style="background: #f8f9fa; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #667eea; font-size: 14px; color: #212529; font-weight: 500;">
                        -
                    </div>
                </div>
                <div>
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <i class="fa-solid fa-comment-dots"
                            style="color: #764ba2; margin-right: 10px; font-size: 16px;"></i>
                        <strong
                            style="color: #495057; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Note</strong>
                    </div>
                    <div id="followupInfoNote"
                        style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #764ba2; font-size: 14px; color: #495057; line-height: 1.6; min-height: 60px; white-space: pre-wrap;">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer"
                style="border-top: 1px solid #e9ecef; padding: 15px 25px; background: #f8f9fa; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-primary" id="editFollowupBtn"
                    style="border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fa-solid fa-edit"></i> Edit
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #333;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .alert {
        margin-bottom: 15px;
    }
</style>

@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        // Initialize Switchery for followup switches
        var elems = Array.prototype.slice.call(document.querySelectorAll('.followup-switch'));
        elems.forEach(function(html) {
            if (!html.switchery) {
                var switchery = new Switchery(html, { size: 'small' });
                html.switchery = switchery;
            }
        });

        // Followup Switch Change Handler
        $(document).on("change", ".followup-switch", function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            let cosmofeedId = $(this).data("cosmofeed-id");
            let isChecked = $(this).is(":checked");
            let $checkbox = $(this);

            if (isChecked) {
                // Open modal to add followup
                $("#followup_cosmofeed_id").val(cosmofeedId);
                $("#followup_note").val('');
                $("#followup_label").val('');
                $("#followupModal").modal("show");
            } else {
                // Uncheck followup
                if (confirm("Are you sure you want to uncheck this follow-up?")) {
                    $.ajax({
                        url: "{{ route('custom_leads.followupUpdate') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            cosmofeed_transaction_id: cosmofeedId,
                            followup_call: 0
                        },
                        success: function(res) {
                            console.log('✅ Followup unchecked successfully');
                            // Remove info icon
                            var $row = $checkbox.closest('tr.lead-row');
                            if ($row.length) {
                                var $followupCell = $row.find('td').eq(7);
                                $followupCell.find('.info-icon').remove();
                            }
                        },
                        error: function(err) {
                            let errorMsg = err.responseJSON?.message || "Error updating followup";
                            alert("Error: " + errorMsg);
                            $checkbox.prop("checked", true);
                            if ($checkbox[0].switchery) {
                                $checkbox[0].switchery.setPosition(true);
                            }
                        }
                    });
                } else {
                    $checkbox.prop("checked", true);
                    if ($checkbox[0].switchery) {
                        $checkbox[0].switchery.setPosition(true);
                    }
                }
            }
        });

        // Followup Form Submit
        $("#followupForm").on("submit", function(e) {
            e.preventDefault();

            let cosmofeedId = $("#followup_cosmofeed_id").val();
            let followupLabel = $("#followup_label").val();
            let followupNote = $("#followup_note").val();

            if (!followupLabel) {
                alert("Please select a followup type");
                return;
            }

            $.ajax({
                url: "{{ route('custom_leads.followupUpdate') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    cosmofeed_transaction_id: cosmofeedId,
                    followup_call: 1,
                    followup_label: followupLabel,
                    followup_note: followupNote
                },
                success: function(res) {
                    $("#followupModal").modal("hide");

                    // Update UI - add or update info icon
                    let $row = $('tr.lead-row[data-transaction-id="' + cosmofeedId + '"]');
                    if ($row.length) {
                        let $followupCell = $row.find('td').eq(7);
                        let $existingIcon = $followupCell.find('.info-icon');

                        let labelDisplay = $("#followup_label option:selected").text();

                        if ($existingIcon.length) {
                            // Update existing icon
                            $existingIcon.attr('data-note', followupNote);
                            $existingIcon.attr('data-label', followupLabel);
                            $existingIcon.attr('data-label-display', labelDisplay);
                        } else {
                            // Add new icon
                            let iconHtml = '<i class="fa-solid fa-circle-info info-icon" ' +
                                'data-cosmofeed-id="' + cosmofeedId + '" ' +
                                'data-note="' + followupNote + '" ' +
                                'data-label="' + followupLabel + '" ' +
                                'data-label-display="' + labelDisplay + '" ' +
                                'data-can-edit="1" ' +
                                'style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>';
                            $followupCell.append(iconHtml);
                        }
                    }

                    alert("Followup updated successfully");
                },
                error: function(err) {
                    let errorMsg = err.responseJSON?.message || "Error updating followup";
                    alert("Error: " + errorMsg);
                }
            });
        });

        // Info Icon Click - Show followup details
        $(document).on("click", ".info-icon", function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            let cosmofeedId = $(this).attr("data-cosmofeed-id");
            let note = $(this).attr("data-note");
            let label = $(this).attr("data-label");
            let labelDisplay = $(this).attr("data-label-display");
            let canEdit = $(this).attr("data-can-edit") === '1';

            // Store data for Edit button
            $("#followupInfoModal").data("cosmofeed-id", cosmofeedId);
            $("#followupInfoModal").data("note", note);
            $("#followupInfoModal").data("label", label);
            $("#followupInfoModal").data("can-edit", canEdit);

            $("#followupInfoLabel").text(labelDisplay || '-');
            $("#followupInfoNote").text(note || '-');

            // Show/hide edit button based on permissions
            if (canEdit) {
                $("#editFollowupBtn").show();
            } else {
                $("#editFollowupBtn").hide();
            }

            $("#followupInfoModal").modal("show");
        });

        // Edit button in Follow Up Details modal
        $("#editFollowupBtn").on("click", function() {
            let cosmofeedId = $("#followupInfoModal").data("cosmofeed-id");
            let note = $("#followupInfoModal").data("note");
            let label = $("#followupInfoModal").data("label");

            $("#followupInfoModal").modal("hide");

            setTimeout(function() {
                $("#followup_cosmofeed_id").val(cosmofeedId);
                $("#followup_note").val(note || '');
                $("#followup_label").val(label || '');
                $("#followupModal").modal("show");
            }, 300);
        });

        // Close modal when clicking close button
        $(".close, [data-bs-dismiss='modal']").on("click", function() {
            $(this).closest(".modal").modal("hide");
        });
    });
</script>

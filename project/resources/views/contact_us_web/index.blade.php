@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    /* Info Icon Styling - Display inline with checkbox */
    .info-icon {
        cursor: pointer;
        color: #667eea;
        font-size: 16px;
        margin-left: 8px;
        vertical-align: middle;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .info-icon:hover {
        color: #764ba2;
        transform: scale(1.1);
    }
    
    /* Ensure followup cell content stays inline */
    td .followup-switch,
    td .info-icon,
    td .switchery {
        display: inline-block;
        vertical-align: middle;
    }
    
    /* Prevent followup cell from wrapping */
    table td:has(.followup-switch) {
        white-space: nowrap;
    }
    
    /* Fallback for browsers that don't support :has() */
    table tbody tr td:nth-child(7) {
        white-space: nowrap;
    }
    
    /* Ensure Switchery doesn't break layout */
    .switchery {
        margin-right: 0 !important;
    }
</style>

<div class="main-container designer-access-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 89vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                            <h5 class="m-2">Contact Us Web</h5>
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('contact_us_web'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 0px;">Id</th>
                                    <th style="width: 20px;">Name</th>
                                    <th style="width: 60px;">Email</th>
                                    <th style="width: 20px;">Contact No</th>
                                    <th style="width: 20px;">Message</th>
                                    {{-- <th>Ip Address</th>
                                    <th>User Agent</th> --}}
                                    <th style="width: 70px;">System Info</th>
                                    <th style="width: 30px;">FollowUp</th>
                                    <th style="width: 30px;">Follow By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ContactUses as $ContactUse)
                                    <tr>
                                        <td>{{ $ContactUse->id }}</td>
                                        <td>{{ $ContactUse->name }}</td>
                                        <td>{{ $ContactUse->email }}</td>
                                        <td>{{ $ContactUse->contact_no ?? '—' }}</td>
                                        <td>{{ $ContactUse->message }}</td>
                                        {{-- <td>{{ $ContactUse->ip_address }}</td>
                                        <td>{{ $ContactUse->user_agent }}</td> --}}
                                        <td>
                                            @php
                                                $systemInfo = json_decode($ContactUse->system_info, true);
                                            @endphp

                                            @if ($systemInfo)
                                                <ul style="padding-left: 15px; margin: 0;">
                                                    <div class=" d-flex justify-content-around">
                                                        <div class="">
                                                            <li><strong>IP : </strong> {{ $systemInfo['ip'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Mobile : </strong>
                                                                {{ $systemInfo['mobile'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Tablet : </strong>
                                                                {{ $systemInfo['tablet'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Desktop : </strong>
                                                                {{ $systemInfo['desktop'] ?? '-' }}
                                                            </li>
                                                            <li><strong>CPU Cores : </strong>
                                                                {{ $systemInfo['cpuCores'] ?? '-' }}</li>
                                                            <li><strong>Device : </strong>
                                                                {{ $systemInfo['device'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Browser : </strong>
                                                                {{ $systemInfo['browser'] ?? '-' }}
                                                            </li>
                                                        </div>
                                                        <div class="">
                                                            <li><strong>Memory (GB) : </strong>
                                                                {{ $systemInfo['deviceMemory'] ?? '-' }}</li>


                                                            <li><strong>Platform : </strong>
                                                                {{ $systemInfo['platform'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Language : </strong>
                                                                {{ $systemInfo['language'] ?? '-' }}
                                                            </li>

                                                            <li><strong>Resolution : </strong>
                                                                {{ $systemInfo['screenResolution'] ?? '-' }}</li>
                                                            <li><strong>Timezone : </strong>
                                                                {{ $systemInfo['timezone'] ?? '-' }}
                                                            </li>

                                                            <li><strong>Online:</strong>
                                                                @if (isset($systemInfo['online']))
                                                                    {{ $systemInfo['online'] ? 'Yes' : 'No' }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </li>
                                                        </div>
                                                    </div>

                                                </ul>
                                            @else
                                                <span class="text-muted">No Info</span>
                                            @endif
                                        </td>
                                        <td style="position: relative;white-space: nowrap;">
                                            <input type="checkbox" class="followup-switch switch-btn me-3"
                                                   data-id="{{ $ContactUse->id }}" data-size="small"
                                                   @if ($ContactUse->followup_call == 1) checked @endif />
                                            @if (!empty($ContactUse->followup_note) || !empty($ContactUse->followup_label))
                                                <i class="fa-solid fa-circle-info info-icon"
                                                   data-id="{{ $ContactUse->id }}"
                                                   data-note="{{ $ContactUse->followup_note }}"
                                                   data-label="{{ $ContactUse->followup_label }}"
                                                   data-label-display="{{ $followupLabels[$ContactUse->followup_label] ?? $ContactUse->followup_label }}"></i>
                                            @endif
                                        </td>
                                        <td style="word-break: keep-all">{{ \App\Http\Controllers\Utils\RoleManager::getUploaderName($ContactUse->emp_id) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $ContactUses])
            </div>
        </div>
    </div>
</div>

{{-- Followup Info Modal --}}
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

{{-- Modal for note --}}
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="followupForm">
            @csrf
            <input type="hidden" name="id" id="followup_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow Up Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="followup_label">Follow-up type</label>
                        <select name="followup_label" class="form-control" id="followup_label" required>
                            <option value="">Select follow-up type</option>
                            @foreach ($followupLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="followup_note">Note</label>
                        <textarea name="followup_note" class="form-control" id="followup_note" rows="4" placeholder="Enter note"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('layouts.masterscript')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
// Ensure modal close works regardless of Bootstrap version
$(document).on('click', '[data-dismiss="modal"]', function () {
    $(this).closest('.modal').modal('hide');
});

$(document).ready(function() {
    let currentContactId = null;

    // Checkbox change - Use event delegation
    $(document).off("change", ".followup-switch").on("change", ".followup-switch", function() {
        let id = $(this).data("id");
        let isChecked = $(this).is(":checked");
        let $checkbox = $(this);

        if (isChecked) {
            currentContactId = id;
            $("#followup_contact_id").val(id);
            $("#followup_note").val('');
            $("#followup_label").val('');

            // Store checkbox reference for modal cancel
            $("#followupModal").data('checkbox', $checkbox);

            $("#followupModal").modal("show");
        } else {
            if (confirm("Are you sure you want to uncheck this follow-up?")) {
                $.ajax({
                    url: "{{ route('contact_us_web.followupUpdate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        followup_call: 0
                    },
                    success: function(res) {
                        console.log('✅ Followup unchecked successfully');
                        
                        // Update UI immediately
                        const $row = $checkbox.closest('tr');
                        const $followupCell = $row.find('td').eq(5); // 6th column (index 5)
                        $followupCell.find('.info-icon').remove();
                        
                        // Update Follow By column
                        const $followByCell = $row.find('td').eq(6); // 7th column (index 6)
                        $followByCell.text('—');
                    },
                    error: function(err) {
                        console.error('❌ Error unchecking followup:', err);
                        alert("Error: " + (err.responseJSON?.message || err.responseText));
                        $checkbox.prop("checked", true);
                    }
                });
            } else {
                $checkbox.prop("checked", true);
            }
        }
    });

    // Handle modal close without saving - uncheck the checkbox
    $('#followupModal').on('hidden.bs.modal', function (e) {
        const $checkbox = $(this).data('checkbox');
        const wasSubmitted = $(this).data('submitted');
        
        // If modal was closed without submitting, uncheck the checkbox
        if ($checkbox && !wasSubmitted) {
            $checkbox.prop('checked', false);
            
            // Reinitialize Switchery if it exists
            if (typeof Switchery !== 'undefined') {
                try {
                    const switcheryInstance = $checkbox.data('switchery');
                    if (switcheryInstance) {
                        switcheryInstance.destroy();
                        new Switchery($checkbox[0], { size: 'small' });
                    }
                } catch (e) {
                    console.warn('Switchery update failed:', e);
                }
            }
        }
        
        // Reset submitted flag
        $(this).data('submitted', false);
        $(this).data('checkbox', null);
    });

    // Info Icon Click - Show modal with followup details
    $(document).off("click", ".info-icon").on("click", ".info-icon", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let id = $(this).attr("data-id");
        let note = $(this).attr("data-note");
        let label = $(this).attr("data-label");
        let labelDisplay = $(this).attr("data-label-display");

        // Store data for edit button
        $("#followupInfoModal").data("contact-id", id);
        $("#followupInfoModal").data("note", note);
        $("#followupInfoModal").data("label", label);

        // Populate modal
        $("#followupInfoLabel").text(labelDisplay || '-');
        $("#followupInfoNote").text(note || '-');

        // Show modal
        $("#followupInfoModal").modal("show");
    });

    // Edit button in info modal - Open edit form
    $("#editFollowupBtn").off("click").on("click", function() {
        // Get stored data
        let contactId = $("#followupInfoModal").data("contact-id");
        let note = $("#followupInfoModal").data("note");
        let label = $("#followupInfoModal").data("label");

        // Close info modal
        $("#followupInfoModal").modal("hide");

        // Wait for info modal to close, then open edit modal
        setTimeout(function() {
            // Populate edit form
            $("#followup_contact_id").val(contactId);
            $("#followup_note").val(note || '');
            $("#followup_label").val(label || '');

            // Open edit modal
            $("#followupModal").modal("show");
        }, 300);
    });

    // Modal submit
    $("#followupForm").on("submit", function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        let contactId = $("#followup_contact_id").val();
        let followupNote = $("#followup_note").val();
        let followupLabel = $("#followup_label").val();

        $.ajax({
            url: "{{ route('contact_us_web.followupUpdate') }}",
            type: "POST",
            data: formData,
            success: function(res) {
                console.log('✅ Followup saved successfully', res);
                
                // Mark as submitted so modal close handler doesn't uncheck
                $("#followupModal").data('submitted', true);
                
                $("#followupModal").modal("hide");
                
                // Update UI immediately
                const followupLabelDisplay = $("#followup_label option:selected").text().trim();
                
                // Find the row and update it
                const $row = $('input.followup-switch[data-id="' + contactId + '"]').closest('tr');
                
                if ($row.length > 0) {
                    const $followupCell = $row.find('td').eq(5); // 6th column (index 5)
                    let $infoIcon = $followupCell.find('.info-icon');
                    
                    if ($infoIcon.length > 0) {
                        // Info icon exists - update it
                        $infoIcon.attr('data-note', followupNote);
                        $infoIcon.attr('data-label', followupLabel);
                        $infoIcon.attr('data-label-display', followupLabelDisplay);
                    } else {
                        // Info icon doesn't exist - create it
                        const infoIconHtml = `
                            <i class="fa-solid fa-circle-info info-icon"
                               data-id="${contactId}"
                               data-note="${followupNote}"
                               data-label="${followupLabel}"
                               data-label-display="${followupLabelDisplay}"></i>
                        `;
                        $followupCell.append(infoIconHtml);
                    }
                    
                    // Update Follow By column
                    const $followByCell = $row.find('td').eq(6); // 7th column (index 6)
                    $followByCell.text('{{ auth()->user()->name ?? "Admin" }}');
                    
                    // Highlight the row to show it was updated
                    $row.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $row.css('background-color', '');
                    }, 2000);
                }
            },
            error: function(err) {
                console.error('❌ Error saving followup:', err);
                alert("Error: " + (err.responseJSON?.message || err.responseText));
            }
        });
    });
});
</script>

</body>

</html>

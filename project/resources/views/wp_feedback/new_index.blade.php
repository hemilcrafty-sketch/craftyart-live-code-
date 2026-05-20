@include('layouts.masterhead')
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')

<style>
    .feedback-dashboard {
        background: #f5f7fb;
        min-height: 100vh;
        padding: 24px;
    }

    .feedback-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .feedback-stat-card,
    .feedback-panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        border: 1px solid #e8edf5;
    }

    .feedback-stat-card {
        padding: 18px 20px;
    }

    .feedback-stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }

    .feedback-stat-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 600;
    }

    .feedback-panel {
        padding: 20px;
    }

    .feedback-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .feedback-toolbar form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
    }

    .feedback-field {
        min-width: 160px;
        flex: 1 1 160px;
    }

    .feedback-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .feedback-table {
        width: 100%;
        margin-bottom: 0;
    }

    .feedback-table thead th {
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        white-space: nowrap;
    }

    .feedback-table td {
        vertical-align: middle;
        font-size: 13px;
        color: #334155;
        border-top: 1px solid #eef2f7;
    }

    .feedback-user-name {
        font-weight: 700;
        color: #0f172a;
    }

    .feedback-user-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-pill.pending {
        background: #fff7db;
        color: #a16207;
    }

    .status-pill.completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-pill.expired {
        background: #fee2e2;
        color: #b91c1c;
    }

    .view-btn {
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 12px;
    }

    .empty-state {
        padding: 48px 20px;
        text-align: center;
        color: #64748b;
    }

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
    table tbody tr td:nth-child(9) {
        white-space: nowrap;
    }
    
    /* Ensure Switchery doesn't break layout */
    .switchery {
        margin-right: 0 !important;
    }
</style>

<div class="feedback-dashboard">
    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="feedback-stats">
                @foreach([
                    ['label' => 'Total Sent', 'value' => $stats['total_sent'], 'color' => '#4f46e5'],
                    ['label' => 'Pending', 'value' => $stats['pending'], 'color' => '#d97706'],
                    ['label' => 'Completed', 'value' => $stats['completed'], 'color' => '#16a34a'],
                    ['label' => 'Expired', 'value' => $stats['expired'], 'color' => '#dc2626'],
                    ['label' => 'Avg Rating', 'value' => $stats['avg_rating'] . '★', 'color' => '#ea580c'],
                    ['label' => 'Response Rate', 'value' => $stats['response_rate'] . '%', 'color' => '#0284c7'],
                ] as $card)
                    <div class="feedback-stat-card">
                        <div class="feedback-stat-value" style="color: {{ $card['color'] }};">{{ $card['value'] }}</div>
                        <div class="feedback-stat-label">{{ $card['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="feedback-panel">
                <div class="feedback-toolbar">
                    <form method="GET" action="{{ route('wp_feedback.new_index') }}">
                        <div class="feedback-field">
                            <label class="feedback-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name / Email / Phone"
                                value="{{ request('search') }}">
                        </div>
                        <div class="feedback-field">
                            <label class="feedback-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                @foreach (['pending', 'completed', 'expired'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="feedback-field">
                            <label class="feedback-label">Rating</label>
                            <select name="rating" class="form-control">
                                <option value="">All Ratings</option>
                                @for ($r = 1; $r <= 5; $r++)
                                    <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>
                                        {{ $r }} ★
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="feedback-field">
                            <label class="feedback-label">Sort</label>
                            @php $currentSort = request('sort', 'newest'); @endphp
                            <select name="sort" class="form-control">
                                <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="rating_desc" {{ $currentSort === 'rating_desc' ? 'selected' : '' }}>Rating: High to Low</option>
                                <option value="rating_asc" {{ $currentSort === 'rating_asc' ? 'selected' : '' }}>Rating: Low to High</option>
                                <option value="submitted_desc" {{ $currentSort === 'submitted_desc' ? 'selected' : '' }}>Submitted: Newest</option>
                                <option value="submitted_asc" {{ $currentSort === 'submitted_asc' ? 'selected' : '' }}>Submitted: Oldest</option>
                            </select>
                        </div>
                        <div class="feedback-field" style="flex: 0 0 auto; min-width: 0;">
                            <label class="feedback-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('wp_feedback.new_index') }}" class="btn btn-light border">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table feedback-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Phone</th>
                                <th>Purchase ID</th>
                                <th>Status</th>
                                <th>Rating</th>
                                <th>Sent At</th>
                                <th>Submitted At</th>
                                <th>FollowUp Call</th>
                                <th>Follow By</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($feedbackRequests as $i => $req)
                                @php
                                    $user = $req->userData;
                                    $support = $req->unifiedSupport;
                                    $wpRes = $req->response;
                                @endphp
                                <tr>
                                    <td>{{ $feedbackRequests->firstItem() + $i }}</td>
                                    <td>
                                        <span class="feedback-user-name">{{ $user?->name ?? 'Unknown User' }}</span>
                                        <span class="feedback-user-meta">{{ $user?->email ?? $req->user_id }}</span>
                                    </td>
                                    <td>{{ $user?->number ?? $req->contact_no ?? '—' }}</td>
                                    <td>{{ $req->purchase_id }}</td>
                                    <td>
                                        @if ($wpRes)
                                            <span class="status-pill completed">Completed</span>
                                        @elseif ($req->isExpired())
                                            <span class="status-pill expired">Expired</span>
                                        @else
                                            <span class="status-pill pending">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($wpRes)
                                            @for ($s = 1; $s <= 5; $s++)
                                                <span style="color: {{ $s <= $wpRes->rating ? '#f59e0b' : '#cbd5e1' }}; font-size: 15px;">★</span>
                                            @endfor
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap;">{{ $req->sent_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $wpRes?->submitted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td style="position: relative;white-space: nowrap;">
                                        <input type="checkbox" class="followup-switch switch-btn me-3"
                                               data-id="{{ $req->id }}" data-size="small"
                                               @if ($support && $support->wp_feedback_followup_call == 1) checked @endif />
                                        @if ($support && (!empty($support->wp_feedback_followup_note) || !empty($support->wp_feedback_followup_label)))
                                            <i class="fa-solid fa-circle-info info-icon"
                                               data-id="{{ $req->id }}"
                                               data-note="{{ $support->wp_feedback_followup_note }}"
                                               data-label="{{ $support->wp_feedback_followup_label }}"
                                               data-label-display="{{ $followupLabels[$support->wp_feedback_followup_label] ?? $support->wp_feedback_followup_label }}"></i>
                                        @endif
                                    </td>
                                    <td style="word-break: keep-all">{{ \App\Http\Controllers\Utils\RoleManager::getUploaderName($support?->emp_id ?? 0) }}</td>
                                    <td>
                                        @if ($wpRes)
                                            <button class="btn btn-outline-primary view-btn view-feedback-btn"
                                                data-rating="{{ $wpRes->rating }}"
                                                data-feedback="{{ e($wpRes->feedback_text ?? '—') }}"
                                                data-suggestions="{{ e($wpRes->suggestions ?? '—') }}"
                                                data-user="{{ e($user?->name ?? 'Unknown User') }}"
                                                data-submitted="{{ $wpRes->submitted_at?->format('d/m/Y H:i') ?? '—' }}">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">No response yet</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="empty-state">No feedback requests found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr class="my-3">
                @include('partials.pagination', ['items' => $feedbackRequests])
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
            <input type="hidden" name="id" id="followup_request_id">
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

{{-- Feedback Detail Modal --}}
<div class="modal fade" id="feedbackDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Feedback Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>User:</strong> <span id="modal-user"></span></p>
                <p><strong>Submitted At:</strong> <span id="modal-submitted"></span></p>
                <hr>
                <p><strong>Rating:</strong></p>
                <div id="modal-stars" style="font-size:22px; margin-bottom:8px;"></div>
                <p><strong>Feedback:</strong></p>
                <div id="modal-feedback" class="p-2 bg-light rounded" style="min-height:50px;"></div>
                <p class="mt-3"><strong>Suggestions:</strong></p>
                <div id="modal-suggestions" class="p-2 bg-light rounded" style="min-height:50px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
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
    let currentRequestId = null;

    // Checkbox change - Use event delegation
    $(document).off("change", ".followup-switch").on("change", ".followup-switch", function() {
        let id = $(this).data("id");
        let isChecked = $(this).is(":checked");
        let $checkbox = $(this);

        if (isChecked) {
            currentRequestId = id;
            $("#followup_request_id").val(id);
            $("#followup_note").val('');
            $("#followup_label").val('');

            // Store checkbox reference for modal cancel
            $("#followupModal").data('checkbox', $checkbox);

            $("#followupModal").modal("show");
        } else {
            if (confirm("Are you sure you want to uncheck this follow-up?")) {
                $.ajax({
                    url: "{{ route('wp_feedback.new_followupUpdate') }}",
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
                        const $followupCell = $row.find('td').eq(8); // 9th column (index 8)
                        $followupCell.find('.info-icon').remove();
                        
                        // Update Follow By column
                        const $followByCell = $row.find('td').eq(9); // 10th column (index 9)
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

    // Info Icon Click - Show modal with followup details (single click for info modal)
    $(document).off("click", ".info-icon").on("click", ".info-icon", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let id = $(this).attr("data-id");
        let note = $(this).attr("data-note");
        let label = $(this).attr("data-label");
        let labelDisplay = $(this).attr("data-label-display");

        // Store data for edit button
        $("#followupInfoModal").data("request-id", id);
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
        let requestId = $("#followupInfoModal").data("request-id");
        let note = $("#followupInfoModal").data("note");
        let label = $("#followupInfoModal").data("label");

        // Close info modal
        $("#followupInfoModal").modal("hide");

        // Wait for info modal to close, then open edit modal
        setTimeout(function() {
            // Populate edit form
            $("#followup_request_id").val(requestId);
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
        let requestId = $("#followup_request_id").val();
        let followupNote = $("#followup_note").val();
        let followupLabel = $("#followup_label").val();

        $.ajax({
            url: "{{ route('wp_feedback.new_followupUpdate') }}",
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
                const $row = $('input.followup-switch[data-id="' + requestId + '"]').closest('tr');
                
                if ($row.length > 0) {
                    const $followupCell = $row.find('td').eq(8); // 9th column (index 8)
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
                               data-id="${requestId}"
                               data-note="${followupNote}"
                               data-label="${followupLabel}"
                               data-label-display="${followupLabelDisplay}"></i>
                        `;
                        $followupCell.append(infoIconHtml);
                    }
                    
                    // Update Follow By column
                    const $followByCell = $row.find('td').eq(9); // 10th column (index 9)
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

// View feedback modal
$(document).on('click', '.view-feedback-btn', function () {
    const rating      = parseInt($(this).data('rating'));
    const feedback    = $(this).data('feedback') || '—';
    const suggestions = $(this).data('suggestions') || '—';
    const user        = $(this).data('user');
    const submitted   = $(this).data('submitted');

    $('#modal-user').text(user);
    $('#modal-submitted').text(submitted);

    // Render stars
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += `<span style="color:${i <= rating ? '#ffc107' : '#ddd'};">★</span>`;
    }
    stars += ` <small style="color:#666;">(${rating}/5)</small>`;
    $('#modal-stars').html(stars);

    $('#modal-feedback').text(feedback);
    $('#modal-suggestions').text(suggestions);

    $('#feedbackDetailModal').modal('show');
});
</script>

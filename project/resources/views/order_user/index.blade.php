@include('layouts.masterhead')
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
<div class="main-container">
    <div class="pd-ltr-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                    <div class="header-row">
                        <div class="filters-section">
                            <div class="meta-filter-container">
                                <!-- First Row: Filters -->
                                <div class="meta-filter-row"
                                    style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                    <!-- Records Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Records</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $filterType == 'all' ? 'active' : '' }}"
                                            data-filter="filter_type" data-value="all">All</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $filterType == 'remove_duplicate' ? 'active' : '' }}"
                                            data-filter="filter_type" data-value="remove_duplicate">No Dupes</button>
                                    </div>

                                    <!-- Status Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Status</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'all' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="all">All</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'active' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="active">Active</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'pending' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="pending">Pending</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $statusFilter == 'failed' ? 'active' : '' }}"
                                            data-filter="status_filter" data-value="failed">Failed</button>
                                    </div>

                                    <!-- Amount Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Amount</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $amountSort == 'desc' ? 'active' : '' }}"
                                            data-filter="amount_sort" data-value="desc">High</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $amountSort == 'asc' ? 'active' : '' }}"
                                            data-filter="amount_sort" data-value="asc">Low</button>
                                    </div>

                                    <!-- WhatsApp Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">WhatsApp</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $whatsappFilter == 'sent' ? 'active' : '' }}"
                                            data-filter="whatsapp_filter" data-value="sent">Sent</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $whatsappFilter == 'not_sent' ? 'active' : '' }}"
                                            data-filter="whatsapp_filter" data-value="not_sent">Not Sent</button>
                                    </div>

                                    <!-- Email Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Email</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $emailFilter == 'sent' ? 'active' : '' }}"
                                            data-filter="email_filter" data-value="sent">Sent</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $emailFilter == 'not_sent' ? 'active' : '' }}"
                                            data-filter="email_filter" data-value="not_sent">Not Sent</button>
                                    </div>

                                    <!-- Followup Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $followupFilter == 'called' ? 'active' : '' }}"
                                            data-filter="followup_filter" data-value="called">Called</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $followupFilter == 'not_called' ? 'active' : '' }}"
                                            data-filter="followup_filter" data-value="not_called">Not Called</button>
                                    </div>

                                    <!-- Followup Label Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup label</span>
                                        @foreach ($followupLabels as $key => $label)
                                            <button type="button"
                                                class="meta-filter-btn {{ $followupLabelFilter == $key ? 'active' : '' }}"
                                                data-filter="followup_label_filter" data-value="{{ $key }}">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Type Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Type</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'old_sub' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="old_sub">Old Sub</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'new_sub' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="new_sub">New Sub</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'template' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="template">Template</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'video' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="video">Video</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $typeFilter == 'caricature' ? 'active' : '' }}"
                                            data-filter="type_filter" data-value="caricature">Caricature</button>
                                    </div>

                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">From Where</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $fromWhere == 'seo' ? 'active' : '' }}"
                                            data-filter="from_where" data-value="seo">Seo</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $fromWhere == 'meta' ? 'active' : '' }}"
                                            data-filter="from_where" data-value="meta">Meta</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $fromWhere == 'google' ? 'active' : '' }}"
                                            data-filter="from_where" data-value="google">Google</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $fromWhere == 'meta_google' ? 'active' : '' }}"
                                            data-filter="from_where" data-value="meta_google">Meta-Google</button>
                                    </div>

                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Offer Pages</span>
                                        <select class="form-control d-inline-block w-auto" id="offerPageFilter" style="height: 30px; padding: 2px 10px; font-size: 13px; border-radius: 6px; border: 1px solid #ddd;">
                                            <option value="">All Slugs</option>
                                            @foreach($activeOfferSlugs as $slug)
                                                <option value="{{ $slug }}" {{ $fromWhere == $slug ? 'selected' : '' }}>{{ $slug }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Usage Type Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Usage Type</span>
                                        <button type="button"
                                            class="meta-filter-btn {{ $usageTypeFilter == 'personal' ? 'active' : '' }}"
                                            data-filter="usage_type_filter" data-value="personal">Personal</button>
                                        <button type="button"
                                            class="meta-filter-btn {{ $usageTypeFilter == 'professional' ? 'active' : '' }}"
                                            data-filter="usage_type_filter"
                                            data-value="professional">Professional</button>
                                    </div>
                                </div>

                                <!-- Second Row: Search Box and Create Payment Link Button -->
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                                    <!-- Search Box -->
                                    <div class="search-container" style="flex: 1;">
                                        <form method="GET" action="{{ route('order_user.index') }}" id="searchForm">
                                            <div class="search-box">
                                                <i class="fas fa-search search-icon"></i>
                                                <input type="text" class="search-input" id="globalSearch" name="search"
                                                    value="{{ $searchTerm ?? '' }}" placeholder="Search all records...">
                                                @if (isset($searchTerm) && !empty($searchTerm))
                                                    <button type="button" class="clear-search" id="clearSearch">×</button>
                                                @endif
                                            </div>
                                            <!-- Hidden fields to preserve filters -->
                                            <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                            <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                                            <input type="hidden" name="type_filter" value="{{ $typeFilter }}">
                                            <input type="hidden" name="amount_sort" value="{{ $amountSort }}">
                                            <input type="hidden" name="whatsapp_filter" value="{{ $whatsappFilter }}">
                                            <input type="hidden" name="email_filter" value="{{ $emailFilter }}">
                                            <input type="hidden" name="followup_filter" value="{{ $followupFilter }}">
                                            <input type="hidden" name="followup_label_filter"
                                                value="{{ $followupLabelFilter }}">
                                            <input type="hidden" name="usage_type_filter"
                                                value="{{ $usageTypeFilter }}">
                                            <input type="hidden" name="from_where" value="{{ $fromWhere }}">
                                        </form>
                                    </div>

                                    <!-- Create Payment Link Button -->
                                    <div style="flex-shrink: 0;">
                                        <button type="button" class="btn btn-primary" id="createPaymentLinkBtn" style="padding: 10px 24px; font-size: 14px; font-weight: 600; white-space: nowrap;
                                                       border-radius: 6px; box-shadow: 0 2px 4px rgba(0,123,255,0.3);
                                                       transition: all 0.3s ease;">
                                            <i class="fa fa-link"></i> Create Payment Link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 200px) !important;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0"
                            style="table-layout: fixed; width: 100%;">

                            {{-- Column Width Control --}}
                            <colgroup>
                                <col style="width:80px">
                                <col style="width:140px">
                                <col style="width:240px">
                                <col style="width:90px">
                                <col style="width:90px">
                                <col style="width:100px">
                                <col style="width:70px">
                                <col style="width:110px">
                                <col style="width:110px">
                                <col style="width:70px">
                                <col style="width:70px">
                                <col style="width:120px">
                                <col style="width:110px">
                                <col style="width:90px">
                                <col style="width:90px">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email / Contact No</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Link</th>
                                    <th>Status</th>
                                    <th>Subscription</th>
                                    <th style="word-break: keep-all">Date</th>
                                    <th>Email</th>
                                    <th>WP</th>
                                    <th>From Where</th>
                                    <th>FollowUp</th>
                                    <th>Follow By</th>
                                    <th>Txn</th>
                                </tr>
                            </thead>

                            <tbody id="tableBody">
                                @if ($OrderUsers->count() > 0)
                                    @foreach ($OrderUsers as $OrderUser)
                                        <tr class="searchable-row order-row" data-order-id="{{ $OrderUser->id }}"
                                            data-user-id="{{ $OrderUser->user_id }}">
                                            <td style="white-space: nowrap;">
                                                <span class="order-id-text" style="color: #007bff; font-weight: 600;">
                                                    {{ $OrderUser->id ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $OrderUser->user?->name ?? '-' }}
                                                @if ($OrderUser->userDeleted)
                                                    <br><span style="color:red;">(User Deleted)</span>
                                                @endif
                                            </td>

                                            <td style="word-break: break-all;">
                                                {{ $OrderUser->user?->email ?? '-' }}
                                                <br><br>
                                                {{ $OrderUser->contact_no ?? $OrderUser->user?->contact_no ?? '-' }}
                                            </td>

                                            <td style="white-space:nowrap;">
                                                {{ $OrderUser->amount_with_symbol }}
                                            </td>

                                            <td>{{ $OrderUser->type ?? '' }}</td>

                                            <td>
                                                @foreach ($OrderUser->plan_items as $item)
                                                    @if ($OrderUser->shouldShowLink())
                                                        <a href="{{ $OrderUser->getItemLink($item) }}" target="_blank"
                                                            class="text-primary text-decoration-none">

                                                            {{ $OrderUser->getItemDisplayText($item) }}
                                                        </a><br>
                                                    @else
                                                        {{ $OrderUser->getItemDisplayText($item) }}
                                                    @endif
                                                @endforeach
                                            </td>

                                            <td class="text-center">
                                                @if (isset($OrderUser->status))
                                                    @if ($OrderUser->status == 'pending')
                                                        <span class="badge badge-warning">{{ $OrderUser->status }}</span>
                                                    @elseif($OrderUser->status == 'failed')
                                                        <span class="badge badge-danger">{{ $OrderUser->status }}</span>
                                                    @elseif($OrderUser->status == 'success' || $OrderUser->status == 'paid')
                                                        <span class="badge badge-success">{{ $OrderUser->status }}</span>
                                                    @else
                                                        {{ $OrderUser->status }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $OrderUser->isSubscriptionActive() ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $OrderUser->isSubscriptionActive() ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>

                                            <td style="word-break: keep-all">
                                                {{ $OrderUser->created_at }}
                                            </td>

                                            <td class="text-center">
                                                @if ($OrderUser->email_template_count > 0)
                                                    <span style="color: green; font-weight: bold;">✔
                                                        {{ $OrderUser->email_template_count }}</span>
                                                @else
                                                    <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if ($OrderUser->whatsapp_template_count > 0)
                                                    <span style="color: green; font-weight: bold;">✔
                                                        {{ $OrderUser->whatsapp_template_count }}</span>
                                                @else
                                                    <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                {{ $OrderUser->from_where }}
                                            </td>

                                            <td style="position: relative; text-align:center;">
                                                @php
                                                    $canEditFollowup = false;
                                                    $currentUserId = auth()->user()->id;
                                                    $isSalesUser = $roleManager::isSalesEmployee(auth()->user()->user_type);
                                                    $isAdminOrManager = $roleManager::isAdmin(auth()->user()->user_type) ||
                                                        $roleManager::isManager(auth()->user()->user_type) ||
                                                        $roleManager::isSalesManager(auth()->user()->user_type);

                                                    // Admin, Manager, and Sales Manager can always edit followup
                                                    if ($isAdminOrManager) {
                                                        $canEditFollowup = true;
                                                    }
                                                    // Sales user can edit if:
                                                    // 1. Order is not assigned to anyone (emp_id = 0 or null)
                                                    // 2. Order is assigned to them (emp_id = current user id)
                                                    elseif ($isSalesUser) {
                                                        if (empty($OrderUser->emp_id) || $OrderUser->emp_id == 0 || $OrderUser->emp_id == $currentUserId) {
                                                            $canEditFollowup = true;
                                                        }
                                                    }
                                                @endphp

                                                <input type="checkbox" class="followup-switch switch-btn me-3"
                                                    data-id="{{ $OrderUser->id }}" data-size="small"
                                                    data-emp-id="{{ $OrderUser->emp_id ?? 0 }}" @if ($OrderUser->followup_call == 1) checked @endif @if (!$canEditFollowup)
                                                    disabled @endif />

                                                @if (!empty($OrderUser->followup_note) || !empty($OrderUser->followup_label))
                                                    <i class="fa-solid fa-circle-info info-icon" data-id="{{ $OrderUser->id }}"
                                                        data-note="{{ $OrderUser->followup_note }}"
                                                        data-label="{{ $OrderUser->followup_label }}"
                                                        data-label-display="{{ $followupLabels[$OrderUser->followup_label] ?? $OrderUser->followup_label }}"
                                                        data-can-edit="{{ $canEditFollowup ? '1' : '0' }}"
                                                        style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>
                                                @endif
                                            </td>

                                            <td style="white-space:nowrap;">
                                                {{ $roleManager::getUploaderName($OrderUser->emp_id) }}
                                            </td>

                                            <td class="text-center">
                                                <button class="btn btn-outline-primary open-transaction-modal"
                                                    style="font-size:12px;"
                                                    data-email="{{ $OrderUser->email ?? $OrderUser->user?->email ?? '' }}"
                                                    data-contact="{{ $OrderUser->contact_no ?? '' }}">
                                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Purchase History Collapse Row -->
                                        <tr class="collapse-row" id="history-row-{{ $OrderUser->id }}" style="display: none;">
                                            <td colspan="15" style="padding: 0; background-color: #ffffff; border:none;">
                                                <div class="purchase-history-container">
                                                    <div class="text-center" style="padding:30px;">
                                                        <i class="fa fa-spinner fa-spin"
                                                            style="font-size: 24px; color:#007bff;"></i>
                                                        <p style="margin-top:10px; color:#6c757d;">Loading purchase history...
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="14" class="text-center">No records found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $OrderUsers])
            </div>
        </div>
    </div>
</div>

<!-- Followup Modal -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="followupForm">
            @csrf
            <input type="hidden" name="id" id="followup_order_id">
            <input type="hidden" name="user_id" id="followup_user_id">
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

<!-- Create Payment Link Modal -->
<div class="modal fade" id="paymentLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form id="paymentLinkForm">
            @csrf
            <div class="modal-content"
                style="border-radius: 15px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 90vh;">
                <!-- Modern Header with Gradient -->
                <div class="modal-header" id="paymentLinkModalHeader"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 25px 30px; position: relative;">
                    <div style="display: flex; align-items: center; width: 100%;">
                        <div
                            style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fa fa-link" style="font-size: 24px; color: #fff;"></i>
                        </div>
                        <div style="flex: 1;">
                            <h5 class="modal-title" id="paymentLinkModalTitle"
                                style="color: #ffffff; font-weight: 700; font-size: 22px; margin: 0; letter-spacing: 0.5px;">
                                Create Payment Link
                            </h5>
                            <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0; font-size: 13px;">
                                Generate secure payment link for your customer
                            </p>
                        </div>
                        <button type="button" class="close" data-bs-dismiss="modal"
                            style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 28px; font-weight: 300; margin: 0; padding: 0; width: 35px; height: 35px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="modal-body"
                    style="padding: 30px; background: #f8f9fa; max-height: calc(90vh - 180px); overflow-y: auto;">
                    <!-- Customer Information Section -->
                    <div
                        style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <h6
                            style="color: #667eea; font-weight: 700; margin-bottom: 20px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fa fa-user"></i> Customer Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_name"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Customer Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="user_name" class="form-control" id="user_name" required
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;"
                                        placeholder="Enter customer name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" style="font-weight: 600; color: #495057; font-size: 13px;">Email
                                        <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" id="email" required
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;"
                                        placeholder="customer@example.com">
                                    <small class="form-text" id="email_validation_msg" style="display:none;"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_no"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Contact Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="contact_no" class="form-control" id="contact_no" required
                                        maxlength="15"
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;"
                                        placeholder="9876543210">
                                    <small class="form-text text-muted" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Enter valid 10-digit mobile number
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Payment Method <span
                                            class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" id="payment_method" required
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;">
                                        <option value="razorpay" selected>Razorpay</option>
                                        <option value="phonepe">PhonePe</option>
                                    </select>
                                    <small class="form-text text-muted" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Select payment gateway
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Details Section -->
                    <div
                        style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <h6
                            style="color: #667eea; font-weight: 700; margin-bottom: 20px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fa fa-shopping-cart"></i> Plan Details
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 14px; margin-bottom: 12px; display: block;">
                                        Subscription Type <span class="text-danger">*</span>
                                    </label>
                                    <div style="margin-top: 8px;">
                                        <div class="form-check form-check-inline" style="margin-right: 30px;">
                                            <input class="form-check-input" type="radio" name="subscription_type"
                                                id="old_sub" value="old_sub" required
                                                style="width: 18px; height: 18px; margin-top: 2px; cursor: pointer;">
                                            <label class="form-check-label" for="old_sub"
                                                style="font-weight: 500; color: #495057; font-size: 14px; margin-left: 8px; cursor: pointer;">
                                                Old Subscription
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="subscription_type"
                                                id="new_sub" value="new_sub" checked required
                                                style="width: 18px; height: 18px; margin-top: 2px; cursor: pointer;">
                                            <label class="form-check-label" for="new_sub"
                                                style="font-weight: 500; color: #495057; font-size: 14px; margin-left: 8px; cursor: pointer;">
                                                New Subscription
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plan_id"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Select Plan <span
                                            class="text-danger">*</span></label>
                                    <select name="plan_id" class="form-control" id="plan_id" required
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;">
                                        <option value="">-- Select Plan --</option>
                                    </select>
                                    <small class="form-text text-muted" style="font-size: 11px;">Select subscription
                                        type first</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="amount"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Amount (₹) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" id="amount" required min="1"
                                        step="0.01"
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="plan_type"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Usage Purpose <span
                                            class="text-danger">*</span></label>
                                    <select name="plan_type" class="form-control" id="plan_type" required
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;">
                                        <option value="">Select Usage Purpose</option>
                                        <option value="personal">Personal Use</option>
                                        <option value="professional">Professional Use</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="caricature"
                                        style="font-weight: 600; color: #495057; font-size: 13px;">Caricature
                                        Count</label>
                                    <input type="number" name="caricature" class="form-control" id="caricature" min="0"
                                        max="100" value="0"
                                        style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;"
                                        placeholder="0-100">
                                    <small class="form-text text-muted" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Max 100 allowed
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="paymentLinkResult" style="display:none; margin-top:20px;">
                        <!-- Success Header -->
                        <div
                            style="text-align:center; padding:20px; background:linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius:12px 12px 0 0; box-shadow:0 4px 12px rgba(40,167,69,0.3);">
                            <div style="font-size:72px; color:#fff; margin-bottom:10px; animation: scaleIn 0.5s ease;">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <h4
                                style="color:#fff; font-weight:700; margin:0; font-size:24px; text-shadow:0 2px 4px rgba(0,0,0,0.2);">
                                Payment Link Created Successfully!
                            </h4>
                            <p style="color:rgba(255,255,255,0.9); margin:10px 0 0 0; font-size:14px;">
                                Share this link with your customer to receive payment
                            </p>
                        </div>

                        <!-- Details Section -->
                        <div
                            style="background:#fff; padding:25px; border-radius:0 0 12px 12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                            <!-- Reference ID and Amount -->
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                                <div
                                    style="background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding:15px; border-radius:8px; border-left:4px solid #28a745;">
                                    <div
                                        style="color:#6c757d; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:5px;">
                                        Reference ID
                                    </div>
                                    <div id="result_reference_id"
                                        style="color:#212529; font-weight:700; font-size:16px; font-family:monospace; word-break:break-all;">
                                    </div>
                                </div>
                                <div
                                    style="background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding:15px; border-radius:8px; border-left:4px solid #28a745;">
                                    <div
                                        style="color:#6c757d; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:5px;">
                                        Amount
                                    </div>
                                    <div style="color:#28a745; font-weight:700; font-size:24px;">
                                        ₹<span id="result_amount"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Link Section -->
                            <div
                                style="background:linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding:20px; border-radius:10px; border:2px dashed #2196f3;">
                                <div
                                    style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                    <div
                                        style="color:#1565c0; font-weight:700; font-size:14px; text-transform:uppercase; letter-spacing:0.5px;">
                                        <i class="fa fa-link"></i> Payment Link
                                    </div>
                                    <div
                                        style="background:#fff; padding:4px 12px; border-radius:20px; font-size:11px; color:#1565c0; font-weight:600;">
                                        Ready to Share
                                    </div>
                                </div>

                                <!-- Link Display -->
                                <div
                                    style="background:#fff; padding:12px; border-radius:6px; margin-bottom:15px; border:1px solid #90caf9;">
                                    <input type="text" class="form-control" id="result_payment_link"
                                        style="border:none; background:transparent; font-family:monospace; font-size:13px; color:#1565c0; font-weight:600; padding:0; box-shadow:none; cursor:text;"
                                        onclick="this.select();">
                                </div>

                                <!-- Alternative: Clickable Link -->
                                <div
                                    style="background:#e3f2fd; padding:12px; border-radius:6px; margin-bottom:15px; border:1px solid #90caf9; text-align:center;">
                                    <small style="color:#666; display:block; margin-bottom:5px; font-size:11px;">Or
                                        right-click this link and select "Copy link address":</small>
                                    <a href="#" id="clickable_payment_link" target="_blank"
                                        style="color:#1565c0; font-weight:700; font-size:14px; text-decoration:underline; word-break:break-all;"
                                        onclick="return false;">
                                        Click here to see link
                                    </a>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                                    <button type="button" class="btn btn-info btn-lg" id="openLinkBtn"
                                        style="font-weight:700; border-radius:8px; padding:12px; box-shadow:0 4px 8px rgba(23,162,184,0.3); transition:all 0.3s;">
                                        <i class="fa fa-external-link"></i> Open Link
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg" id="copyLinkBtn"
                                        style="font-weight:700; border-radius:8px; padding:12px; box-shadow:0 4px 8px rgba(40,167,69,0.3); transition:all 0.3s;">
                                        <i class="fa fa-copy"></i> Copy Link
                                    </button>
                                    <button type="button" class="btn btn-warning btn-lg" id="showLinkBtn"
                                        style="font-weight:700; border-radius:8px; padding:12px; box-shadow:0 4px 8px rgba(255,193,7,0.3); transition:all 0.3s;">
                                        <i class="fa fa-eye"></i> Show Link
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg" id="shareWhatsAppBtn"
                                        style="font-weight:700; border-radius:8px; padding:12px; box-shadow:0 4px 8px rgba(37,211,102,0.3); background:#25d366; border:none; transition:all 0.3s;">
                                        <i class="fa fa-whatsapp"></i> WhatsApp
                                    </button>
                                </div>

                                <!-- Manual Copy Option -->
                                <div style="text-align:center; margin-bottom:15px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selectLinkBtn"
                                        style="font-size:12px; padding:6px 15px; border-radius:6px;">
                                        <i class="fa fa-hand-pointer-o"></i> Or Click Here to Select & Copy Manually
                                        (Ctrl+C)
                                    </button>
                                </div>

                                <!-- Helpful Note -->
                                <div
                                    style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:10px; margin-bottom:15px; text-align:center;">
                                    <small style="color:#856404; font-size:11px;">
                                        <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> If copy doesn't work,
                                        use <strong>"Show Link"</strong> button to see the link in a popup (you can
                                        select and copy from there), or use <strong>"Open Link"</strong> to open payment
                                        page directly
                                    </small>
                                </div>

                                <!-- Help Text -->
                                <div
                                    style="margin-top:15px; padding:12px; background:rgba(255,255,255,0.7); border-radius:6px; text-align:center;">
                                    <small style="color:#1565c0; font-size:12px; font-weight:600;">
                                        <i class="fa fa-info-circle"></i> Customer can pay using UPI, Cards, Net
                                        Banking, or Wallets
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        @keyframes scaleIn {
                            from {
                                transform: scale(0);
                                opacity: 0;
                            }

                            to {
                                transform: scale(1);
                                opacity: 1;
                            }
                        }

                        #copyLinkBtn:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4) !important;
                        }

                        #shareWhatsAppBtn:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 12px rgba(37, 211, 102, 0.4) !important;
                        }
                    </style>
                </div>
                <div class="modal-footer"
                    style="background: #f8f9fa; border-top: 2px solid #e9ecef; padding: 20px 30px; border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="padding: 12px 30px; border-radius: 8px; font-weight: 600; font-size: 14px; border: 2px solid #6c757d; background: transparent; color: #6c757d; transition: all 0.3s;">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary" id="createLinkBtn"
                        style="padding: 12px 30px; border-radius: 8px; font-weight: 600; font-size: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 4px 12px rgba(102,126,234,0.4); transition: all 0.3s;">
                        <i class="fa fa-link"></i> Create Link
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Payment Link Modal Enhancements */
    #paymentLinkModal .form-control:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
    }

    #paymentLinkModal .modal-header .close:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        transform: rotate(90deg);
    }

    #paymentLinkModal .btn-secondary:hover {
        background: #6c757d !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    #paymentLinkModal #createLinkBtn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5) !important;
    }

    #paymentLinkModal .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>

<!-- Custom Notification Modal -->
<div class="modal" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true"
    style="z-index:9999; display:none;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"
            style="border:none; border-radius:12px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <div id="notificationHeader" class="modal-header" style="border:none; padding:30px 30px 20px;">
                <div style="width:100%; text-align:center;">
                    <div id="notificationIcon" style="font-size:64px; margin-bottom:15px;"></div>
                    <h4 id="notificationTitle" class="modal-title" style="font-weight:700; margin:0;"></h4>
                </div>
            </div>
            <div class="modal-body" style="padding:0 30px 30px; text-align:center;">
                <p id="notificationMessage" style="font-size:15px; color:#6c757d; margin:0; line-height:1.6;"></p>
            </div>
            <div class="modal-footer" style="border:none; padding:0 30px 30px; justify-content:center;">
                <button type="button" class="btn btn-primary" id="closeNotificationBtn"
                    style="padding:10px 40px; font-weight:600; border-radius:6px;">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop" id="notificationBackdrop" style="display:none; z-index:9998; background:rgba(0,0,0,0.5);">
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="add_transaction_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content"
            style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 90vh;">
            <!-- Modern Header -->
            <div class="modal-header"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 20px 25px; border-radius: 12px 12px 0 0;">
                <div style="display: flex; align-items: center;">
                    <div
                        style="background: rgba(255,255,255,0.2); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fa fa-money-bill-transfer" style="font-size: 20px; color: #fff;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: #ffffff; font-weight: 700; font-size: 20px; margin: 0;">
                            Add Transaction</h5>
                        <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 12px;">Manually add subscription
                            transaction</p>
                    </div>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"
                    style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 24px; font-weight: 300;">×</button>
            </div>

            <div class="modal-body"
                style="padding: 25px; background: #f8f9fa; max-height: calc(90vh - 180px); overflow-y: auto;">
                <form method="post" id="add_transaction_form" enctype="multipart/form-data">
                    @csrf

                    <!-- Customer Info Section -->
                    <div
                        style="background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6
                            style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-user"></i> Customer Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Email
                                        <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="txn_email" name="email" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="customer@example.com" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Contact
                                        No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="txn_contact" name="contact" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="9876543210" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details Section -->
                    <div
                        style="background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6
                            style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-receipt"></i> Transaction Details
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Method
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="method" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="Razorpay, PhonePe, etc." />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Transaction
                                        ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="transaction_id" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="TXN123456789" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Currency</label>
                                    <select class="form-control" name="currency_code"
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="INR">INR (₹)</option>
                                        <option value="USD">USD ($)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Price
                                        Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price_amount" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="0.00" step="0.01" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Paid
                                        Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="paid_amount" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="0.00" step="0.01" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan & Additional Info Section -->
                    <div
                        style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6
                            style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-box"></i> Plan & Additional Info
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <label for="txn_subscription_type"
                                           style="font-weight: 600; color: #495057; font-size: 13px;">Transaction type</label>
                                    <select name="subscription_type" id="txn_subscription_type" class="form-control" required
                                            style="border-radius: 8px; border: 2px solid #e9ecef; padding: 10px 12px; font-size: 14px; max-width: 100%;">
                                        <option value="new_sub" selected>New Subscription</option>
                                        <option value="old_sub">Old Subscription</option>
                                        <option value="vendor_panel">Vendor Panel Access</option>
                                        <option value="ai_tools_credit">AI Tools Credit</option>
                                        <option value="addon_other">Other</option>
                                    </select>
                                </div>
                                <small id="txn_subscription_type_help" class="text-muted d-block mt-1" style="font-size: 11px;">Subscriptions require a plan and usage details. Add-on types only need payment details above; fields below adjust automatically.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="plan_id"
                                           style="font-weight: 600; color: #495057; font-size: 13px;">Select Plan <span
                                            class="text-danger">*</span></label>
                                    <select name="plan_id" class="form-control" id="plan_id" required
                                            style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px; transition: all 0.3s;">
                                        <option value="">-- Select Plan --</option>
                                    </select>
                                    <small class="form-text text-muted" style="font-size: 11px;">Select subscription
                                        type first</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Additional Days</label>
                                    <input type="number" class="form-control" name="additional_days" value="0" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="0" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Usage
                                        Purpose <span class="text-danger">*</span></label>
                                    <select name="usage_purpose" class="form-control" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="">Select Purpose</option>
                                        <option value="personal">Personal Use</option>
                                        <option value="professional">Professional Use</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">From
                                        Wallet</label>
                                    <select class="form-control" name="fromWallet"
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">From
                                        Where</label>
                                    <select class="form-control" name="fromWhere"
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="Mobile">Mobile</option>
                                        <option value="Web">Web</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label
                                        style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Caricatures</label>
                                    <input type="number" class="form-control" name="caricatures" value="0" required
                                        style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;"
                                        placeholder="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer"
                style="background: #f8f9fa; border-top: 2px solid #e9ecef; padding: 15px 25px; border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    style="padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 13px; border: 2px solid #6c757d; background: transparent; color: #6c757d;">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="submit" form="add_transaction_form" class="btn btn-primary"
                    style="padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 13px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 4px 12px rgba(102,126,234,0.4);">
                    <i class="fa fa-check"></i> Submit Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Purchase History Row Styling */
    .order-row {
        transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .order-row:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.001);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Only ID column is clickable */
    .order-row td:first-child {
        cursor: pointer;
    }

    .order-row td:first-child:hover {
        background-color: #e7f3ff !important;
    }

    .order-row.row-expanded {
        background-color: #e7f3ff !important;
        border-left: 4px solid #007bff;
    }

    .order-id-text {
        display: inline-block;
        align-items: center;
        white-space: nowrap;
        cursor: pointer;
    }

    .order-id-text::after {
        content: '\f107';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-left: 8px;
        font-size: 12px;
        color: #6c757d;
        transition: transform 0.3s ease;
    }

    .row-expanded .order-id-text::after {
        transform: rotate(180deg);
    }

    .collapse-row {
        background-color: #ffffff;
    }

    .collapse-row td {
        border-top: none !important;
    }

    .purchase-history-container {
        animation: slideDown 0.3s ease;
        max-height: 600px;
        overflow-y: auto;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Scrollbar styling for purchase history */
    .purchase-history-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .purchase-history-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .purchase-history-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .purchase-history-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Add Transaction Modal Styling */
    #add_transaction_model .form-control:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15) !important;
    }

    #add_transaction_model .modal-header .close:hover {
        opacity: 0.8;
    }

    #add_transaction_model .btn-secondary:hover {
        background: #6c757d !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    #add_transaction_model .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5) !important;
    }

    /* Mobile Responsive Styles for Transaction Modal */
    @media (max-width: 768px) {
        #add_transaction_model .modal-dialog {
            margin: 10px;
            max-width: calc(100% - 20px);
        }

        #add_transaction_model .modal-body {
            padding: 15px !important;
        }

        #add_transaction_model .modal-header {
            padding: 15px !important;
            flex-direction: column;
            align-items: flex-start !important;
        }

        #add_transaction_model .modal-header>div {
            width: 100%;
        }

        #add_transaction_model .modal-header .close {
            position: absolute;
            right: 15px;
            top: 15px;
        }

        #add_transaction_model .row {
            margin-left: 0;
            margin-right: 0;
        }

        #add_transaction_model .col-md-6,
        #add_transaction_model .col-md-4 {
            padding-left: 0;
            padding-right: 0;
            margin-bottom: 10px;
        }

        #add_transaction_model .form-group {
            margin-bottom: 15px !important;
        }

        #add_transaction_model label {
            font-size: 11px !important;
            margin-bottom: 4px !important;
        }

        #add_transaction_model .form-control,
        #add_transaction_model select {
            font-size: 12px !important;
            padding: 6px 10px !important;
        }

        #add_transaction_model h6 {
            font-size: 12px !important;
            margin-bottom: 12px !important;
        }

        #add_transaction_model .modal-footer {
            padding: 12px 15px !important;
            flex-direction: column;
        }

        #add_transaction_model .modal-footer .btn {
            width: 100%;
            margin-bottom: 8px;
            padding: 10px !important;
            font-size: 13px !important;
        }

        #add_transaction_model .modal-footer .btn:last-child {
            margin-bottom: 0;
        }

        /* Adjust section padding for mobile */
        #add_transaction_model .modal-body>div {
            padding: 15px !important;
            margin-bottom: 12px !important;
        }
    }

    /* Tablet Responsive */
    @media (min-width: 769px) and (max-width: 991px) {
        #add_transaction_model .modal-dialog {
            max-width: 90%;
        }

        #add_transaction_model .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        #add_transaction_model .col-md-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
    }
</style>

@include('layouts.masterscript')

<!-- Pusher JS for WebSocket Real-Time Updates -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script>
    // User role check
    const isAdminOrManager = {{ $roleManager::isAdmin(auth()->user()->user_type) || $roleManager::isManager(auth()->user()->user_type) ? 'true' : 'false' }};
    const isSalesUser = {{ $roleManager::isSalesEmployee(auth()->user()->user_type) ? 'true' : 'false' }};
    const currentUserId = {{ auth()->user()->id }};
    const currentUserName = {!! json_encode(auth()->user()->name ?? '') !!};

    function removeFilter(paramName) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete(paramName);
        window.location.href = currentUrl.toString();
    }

    $(document).ready(function () {
        let currentOrderId = null;

        // Search on Enter key press only
        $('#globalSearch').on('keypress', function (e) {
            if (e.which === 13) { // Enter key
                $('#searchForm').submit();
            }
        });

        // Clear search
        $('#clearSearch').on('click', function () {
            $('#globalSearch').val('');
            $('#searchForm').submit();
        });

        // Show/hide clear button based on search input
        $('#globalSearch').on('input', function () {
            const $clearBtn = $('#clearSearch');
            if ($(this).val().length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
        });

        // Initialize Event Handlers
        function initializeEventHandlers() {
            // Followup Switch - Use event delegation to avoid duplicate handlers
            $(document).off("change", ".followup-switch").on("change", ".followup-switch", function (e) {
                // Prevent event bubbling
                e.stopPropagation();
                e.stopImmediatePropagation();

                let empId = parseInt($(this).data("emp-id")) || 0;
                let id = $(this).data("id");
                let isChecked = $(this).is(":checked");
                let $checkbox = $(this);

                // Check if user can edit this followup
                let canEdit = false;
                if (isAdminOrManager) {
                    // Admin/Manager/Sales Manager can always edit
                    canEdit = true;
                } else if (isSalesUser) {
                    // Sales user can edit if order is not assigned or assigned to them
                    if (empId === 0 || empId === currentUserId) {
                        canEdit = true;
                    } else {
                        alert("This order is assigned to another Sales user. Only the assigned user can update followup.");
                        $(this).prop("checked", !isChecked); // Revert
                        return false;
                    }
                }

                if (!canEdit) {
                    $(this).prop("checked", !isChecked); // Revert
                    return false;
                }

                if (isChecked) {
                    currentOrderId = id;
                    $("#followup_order_id").val(id);
                    $("#followup_note").val('');
                    $("#followup_label").val('');

                    // Fetch user_id for followup
                    $.ajax({
                        url: "{{ route('order_user.get_user_usage') }}",
                        type: "GET",
                        data: { order_id: id },
                        success: function (response) {
                            if (response.success && response.user_id) {
                                $("#followup_user_id").val(response.user_id);
                            }
                        },
                        error: function () {
                            console.log('Could not fetch user info');
                        }
                    });

                    $("#followupModal").modal("show");
                } else {
                    if (confirm("Are you sure you want to uncheck this follow-up?")) {
                        $.ajax({
                            url: "{{ route('order_user.followupUpdate') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                followup_call: 0
                            },
                            success: function (res) {
                                console.log('✅ Followup unchecked successfully');
                                // Update same tab immediately: remove info icon so UI matches (no refresh needed)
                                var $row = $checkbox.closest('tr.order-row');
                                if ($row.length) {
                                    var $followupCell = $row.find('td').eq(12);
                                    $followupCell.find('.info-icon').remove();
                                }
                            },
                            error: function (err) {
                                alert("Error: " + err.responseText);
                                $checkbox.prop("checked", true);
                            }
                        });
                    } else {
                        $checkbox.prop("checked", true);
                    }
                }
            });

            // Info Icon Click - Show modal with followup details (same as recent_expire)
            $(document).off("click", ".info-icon").on("click", ".info-icon", function (e) {
                e.stopPropagation();
                e.stopImmediatePropagation();

                let id = $(this).attr("data-id");
                let note = $(this).attr("data-note");
                let label = $(this).attr("data-label");
                let labelDisplay = $(this).attr("data-label-display");
                let userId = $(this).closest("tr.order-row").attr("data-user-id") || $(this).closest("tr").data("user-id") || "";

                // Store data for Edit button
                $("#followupInfoModal").data("order-id", id);
                $("#followupInfoModal").data("user-id", userId);
                $("#followupInfoModal").data("note", note);
                $("#followupInfoModal").data("label", label);

                $("#followupInfoLabel").text(labelDisplay || '-');
                $("#followupInfoNote").text(note || '-');

                $("#followupInfoModal").modal("show");
            });

            // Edit button in Follow Up Details modal - open edit form (same as recent_expire)
            $("#editFollowupBtn").off("click").on("click", function () {
                let orderId = $("#followupInfoModal").data("order-id");
                let userId = $("#followupInfoModal").data("user-id");
                let note = $("#followupInfoModal").data("note");
                let label = $("#followupInfoModal").data("label");

                $("#followupInfoModal").modal("hide");

                setTimeout(function () {
                    $("#followup_order_id").val(orderId);
                    $("#followup_user_id").val(userId || "");
                    $("#followup_note").val(note || '');
                    $("#followup_label").val(label || '');
                    $("#followupModal").modal("show");
                }, 300);
            });

            // Transaction Modal - Use event delegation
            $(document).off("click", ".open-transaction-modal").on("click", ".open-transaction-modal", function (e) {
                e.stopPropagation();
                e.stopImmediatePropagation();

                let email = $(this).data('email');
                let contact = $(this).data('contact');

                console.log('Opening transaction modal with:', { email, contact });

                $('#add_transaction_model').modal('show');

                // Use setTimeout to ensure modal is fully rendered before setting values
                setTimeout(function () {
                    $('#txn_email').val(email);
                    $('#txn_contact').val(contact);
                    console.log('Set values:', {
                        email: $('#txn_email').val(),
                        contact: $('#txn_contact').val()
                    });
                }, 100);
            });

        }

        // Show notification function
        function showNotification(title, message) {
            // Don't request notification permission automatically - this can cause popups
            // Only show in-page notification
            const notification = $('<div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
                '<strong>' + title + '</strong><br>' + message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>');

            $('body').append(notification);

            setTimeout(function () {
                notification.fadeOut(function () {
                    $(this).remove();
                });
            }, 5000);

            // Only use browser notifications if already granted (don't request)
            if ("Notification" in window && Notification.permission === "granted") {
                try {
                    new Notification(title, {
                        body: message,
                        icon: '{{ asset('assets/logo.png') }}'
                    });
                } catch (e) {
                    console.log('Browser notification failed:', e);
                }
            }
        }

        // Filter Toggle
        $('.meta-filter-btn').on('click', function () {
            const filterName = $(this).data('filter');
            const filterValue = $(this).data('value');
            const currentUrl = new URL(window.location.href);

            const currentValue = currentUrl.searchParams.get(filterName);

            if (currentValue === filterValue) {
                currentUrl.searchParams.delete(filterName);
            } else {
                currentUrl.searchParams.set(filterName, filterValue);
            }

            if (filterName === 'filter_type' && filterValue === 'all') {
                const paramsToRemove = [
                    'filter_type', 'status_filter', 'type_filter', 'amount_sort',
                    'whatsapp_filter', 'email_filter', 'followup_filter', 'followup_label_filter',
                    'from_where'
                ];
                paramsToRemove.forEach(param => currentUrl.searchParams.delete(param));
            }

            window.location.href = currentUrl.toString();
        }); 

        $('#offerPageFilter').on('change', function () {
            const filterValue = $(this).val();
            const currentUrl = new URL(window.location.href);

            if (filterValue) {
                currentUrl.searchParams.set('from_where', filterValue);
            } else {
                currentUrl.searchParams.delete('from_where');
            }

            window.location.href = currentUrl.toString();
        });

        // Transaction Form Submission
        $('#add_transaction_form').on('submit', function (event) {
            event.preventDefault();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(this);

            // Show loading state
            const $submitBtn = $('#add_transaction_form').closest('.modal').find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: "{{ route('manage_subscription.submit') }}",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    if (data.status) {
                        alert(data.success || 'Subscription added successfully!');
                        location.reload();
                    } else {
                        alert(data.success || data.error || 'Something went wrong!');
                    }
                },
                error: function (error) {
                    $submitBtn.prop('disabled', false).html(originalText);

                    let errorMsg = 'Error adding transaction';
                    try {
                        const response = JSON.parse(error.responseText);
                        errorMsg = response.message || errorMsg;

                        // Show validation errors if present
                        if (response.errors) {
                            let errorList = '';
                            Object.keys(response.errors).forEach(key => {
                                errorList += '- ' + response.errors[key].join('\n- ') + '\n';
                            });
                            errorMsg += ':\n\n' + errorList;
                        }
                    } catch (e) {
                        errorMsg = error.responseText || errorMsg;
                    }

                    alert(errorMsg);
                }
            });
        });

        // Followup Form Submission
        $("#followupForm").on("submit", function (e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let orderId = $("#followup_order_id").val();
            let followupNote = $("#followup_note").val();
            let followupLabel = $("#followup_label").val();
            let followupLabelDisplay = $("#followup_label option:selected").text().trim();

            $.ajax({
                url: "{{ route('order_user.followupUpdate') }}",
                type: "POST",
                data: formData,
                success: function (res) {
                    // Properly hide modal and remove backdrop
                    $("#followupModal").modal("hide");

                    // Update the row's info icon so Follow Up Details modal shows new data when opened again
                    const $row = $('#tableBody tr.order-row').filter(function () {
                        return $(this).attr('data-order-id') === orderId || parseInt($(this).find('td:first').text()) === parseInt(orderId);
                    }).first();
                    if ($row.length) {
                        const $followupCell = $row.find('td').eq(12);
                        let $infoIcon = $followupCell.find('.info-icon');
                        const noteEscaped = (followupNote || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        const labelEscaped = (followupLabel || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        const labelDisplayEscaped = (followupLabelDisplay || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        if ($infoIcon.length) {
                            $infoIcon.attr('data-note', followupNote || '');
                            $infoIcon.attr('data-label', followupLabel || '');
                            $infoIcon.attr('data-label-display', followupLabelDisplay || '');
                        } else {
                            const iconHtml = `<i class="fa-solid fa-circle-info info-icon" data-id="${orderId}" data-note="${noteEscaped}" data-label="${labelEscaped}" data-label-display="${labelDisplayEscaped}" data-can-edit="1" style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>`;
                            $followupCell.find('.switchery').length ? $followupCell.find('.switchery').after(iconHtml) : $followupCell.append(iconHtml);
                        }
                        // Update "Follow By" column immediately (saved by current user - no refresh needed)
                        $row.find('td').eq(13).text(currentUserName || '-');
                    }

                    // If Follow Up Details modal is open for this order, refresh its content so it shows new data in real time
                    if ($("#followupInfoModal").hasClass('show') || $("#followupInfoModal").is(':visible')) {
                        var modalOrderId = $("#followupInfoModal").data("order-id");
                        if (modalOrderId == orderId || modalOrderId === orderId) {
                            $("#followupInfoModal").data("note", followupNote);
                            $("#followupInfoModal").data("label", followupLabel);
                            $("#followupInfoLabel").text(followupLabelDisplay || '-');
                            $("#followupInfoNote").text(followupNote || '-');
                        }
                    }

                    setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('padding-right', '');
                        $('body').css('overflow', '');
                    }, 300);

                },
                error: function (err) {
                    let errorMsg = "Error updating followup";
                    try {
                        const response = JSON.parse(err.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        errorMsg = err.responseText || errorMsg;
                    }
                    alert("Error: " + errorMsg);
                }
            });
        });

        // Function to add new order to table without refresh
        function addOrderToTable(order) {
            const newRow = `
                <tr class="searchable-row order-row" data-order-id="${order.id}" data-user-id="${order.user_id}" style="cursor: pointer;">
                    <td>
                        <span class="order-id-text" style="color: #007bff; font-weight: 600;">
                            ${order.id}
                        </span>
                    </td>
                    <td>${order.user_name || '-'}</td>
                    <td style="word-break: break-all;">
                        ${order.email || '-'}<br><br>
                        ${order.contact_no || '-'}
                    </td>
                    <td style="white-space:nowrap;">${order.amount_with_symbol || '-'}</td>
                    <td>${order.type || ''}</td>
                    <td>${order.plan_items || '-'}</td>
                    <td class="text-center" style="white-space:nowrap;">
                        <span class="badge ${order.status === 'pending' ? 'badge-warning' : order.status === 'failed' ? 'badge-danger' : 'badge-success'}" style="display:inline-block; white-space:nowrap;">${order.status}</span>
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        <span class="badge ${order.is_subscription_active ? 'badge-success' : 'badge-danger'}" style="display:inline-block; white-space:nowrap;">
                            ${order.is_subscription_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td style="word-break: keep-all">${order.created_at}</td>
                    <td class="text-center">
                        <span style="color: ${order.email_template_count > 0 ? 'green' : 'red'}; font-weight: bold;">
                            ${order.email_template_count > 0 ? '✔ ' + order.email_template_count : '✗'}
                        </span>
                    </td>
                    <td class="text-center">
                        <span style="color: ${order.whatsapp_template_count > 0 ? 'green' : 'red'}; font-weight: bold;">
                            ${order.whatsapp_template_count > 0 ? '✔ ' + order.whatsapp_template_count : '✗'}
                        </span>
                    </td>
                    <td class="text-center">${order.from_where || '-'}</td>
                    <td style="position: relative; text-align:center;">
                        <input type="checkbox" class="followup-switch switch-btn me-3"
                               data-id="${order.id}"
                               data-emp-id="${order.emp_id || 0}"
                               data-size="small" />
                    </td>
                    <td style="white-space:nowrap;">${order.follow_by || '-'}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary open-transaction-modal" style="font-size:12px;"
                                data-email="${order.email || ''}" data-contact="${order.contact_no || ''}">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </button>
                    </td>
                </tr>
                <!-- Purchase History Collapse Row -->
                <tr class="collapse-row" id="history-row-${order.id}" style="display: none;">
                    <td colspan="15" style="padding: 0; background-color: #f8f9fa;">
                        <div class="purchase-history-container" style="padding: 20px;">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin" style="font-size: 24px;"></i>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            // Add to top of table
            $('#tableBody').prepend(newRow);

            // Use setTimeout to ensure DOM is fully ready before initializing Switchery
            setTimeout(function () {
                // Initialize Switchery for the new checkbox with proper disabled state
                const $newRow = $('#tableBody tr:first');
                const newCheckbox = $newRow.find('.switch-btn')[0];

                if (newCheckbox && typeof Switchery !== 'undefined') {
                    // Calculate if checkbox should be disabled
                    const empId = parseInt(order.emp_id) || 0;
                    let shouldDisable = false;

                    if (isAdminOrManager) {
                        shouldDisable = true;
                    } else if (isSalesUser) {
                        if (empId !== 0 && empId !== currentUserId) {
                            shouldDisable = true;
                        }
                    }

                    console.log('🔧 Initializing Switchery for new order:', {
                        order_id: order.id,
                        emp_id: empId,
                        isAdminOrManager: isAdminOrManager,
                        isSalesUser: isSalesUser,
                        currentUserId: currentUserId,
                        shouldDisable: shouldDisable,
                        checkboxExists: !!newCheckbox
                    });

                    // Set disabled property before initializing Switchery
                    $(newCheckbox).prop('disabled', shouldDisable);

                    // Initialize Switchery
                    try {
                        new Switchery(newCheckbox, {
                            size: 'small',
                            disabled: shouldDisable
                        });
                        console.log('✅ Switchery initialized successfully for order #' + order.id);

                        // Clean up any modal backdrops that might appear during Switchery init
                        setTimeout(function () {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('padding-right', '');
                            $('body').css('overflow', '');
                        }, 50);
                    } catch (e) {
                        console.error('❌ Error initializing Switchery:', e);
                    }
                } else {
                    console.error('❌ Cannot initialize Switchery:', {
                        checkboxExists: !!newCheckbox,
                        switcheryDefined: typeof Switchery !== 'undefined'
                    });
                }

                // No need to re-initialize event handlers since we're using event delegation
                // initializeEventHandlers(); // REMOVED - causes duplicate handlers
            }, 100); // Small delay to ensure DOM is ready
        }

        // Initial Setup
        initializeEventHandlers();

        // WebSocket System for Real-Time Updates
        let lastOrderId = {{ $OrderUsers->first()->id ?? 0 }};
        let useWebSocket = false;
        let pusher = null;


        function initializeWebSocket() {
            try {

                // Enable Pusher logging for debugging
                Pusher.logToConsole = true;

                // Initialize Pusher (same config as recent_expire for Laravel WebSockets on localhost)
                pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
                    wsHost: window.location.hostname,
                    wsPort: 443,
                    wssPort: 443,
                    forceTLS: true,
                    encrypted: true,
                    disableStats: true,
                    enabledTransports: ['ws', 'wss'],
                    cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                });

                // Connection state logging
                pusher.connection.bind('connecting', function () {
                    console.log('🔄 WebSocket connecting...');
                });

                pusher.connection.bind('connected', function () {
                    useWebSocket = true;
                    // Don't show notification on page load - only log to console
                    // showNotification('✅ Real-Time Mode', 'WebSocket connected - instant updates enabled!');
                });

                pusher.connection.bind('disconnected', function () {
                    useWebSocket = false;
                    // Don't show notification on disconnect - only log to console
                    // showNotification('⚠️ Connection Lost', 'WebSocket disconnected. Please refresh the page.');
                });

                pusher.connection.bind('failed', function () {
                    useWebSocket = false;
                    // Don't show notification on connection failure - only log to console
                    // showNotification('⚠️ Connection Failed', 'WebSocket connection failed. Please refresh the page.');
                });

                // Subscribe to orders channel
                const channel = pusher.subscribe('orders');

                channel.bind('pusher:subscription_succeeded', function () {
                });

                channel.bind('pusher:subscription_error', function (error) {
                    console.error('❌ Subscription error:', error);
                });

                // Listen for new order events
                channel.bind('new-order-created', function (data) {

                    // IMMEDIATELY remove any modal backdrops before processing
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                    $('body').css('overflow', '');

                    if (data && data.order) {
                        console.log('⚡ WebSocket: New order received INSTANTLY!', data.order);

                        // Format order data for table
                        const order = {
                            id: data.order.id,
                            user_name: data.order.user_name || '-',
                            email: data.order.email || '-',
                            contact_no: data.order.contact_no || '-',
                            amount_with_symbol: data.order.amount_with_symbol || '-',
                            type: data.order.type || '',
                            plan_items: data.order.plan_items || '-',
                            status: data.order.status,
                            is_subscription_active: data.order.is_subscription_active || false,
                            created_at: data.order.created_at,
                            email_template_count: data.order.email_template_count || 0,
                            whatsapp_template_count: data.order.whatsapp_template_count || 0,
                            from_where: data.order.from_where || '-',
                            followup_call: data.order.followup_call || 0,
                            follow_by: data.order.follow_by || '-'
                        };

                        addOrderToTable(order);
                        lastOrderId = Math.max(lastOrderId, order.id);

                        // AGGRESSIVE cleanup - run multiple times
                        for (let i = 0; i < 5; i++) {
                            setTimeout(function () {
                                $('.modal-backdrop').remove();
                                $('.modal').modal('hide');
                                $('body').removeClass('modal-open');
                                $('body').css('padding-right', '');
                                $('body').css('overflow', '');
                            }, i * 50);
                        }
                    }
                });

                // Listen for order status change events
                channel.bind('order-status-changed', function (data) {
                    if (data && data.order_id && data.should_remove) {
                        removeOrderFromTable(data.order_id);
                    }
                });

                // Listen for order followup change events
                channel.bind('order-followup-changed', function (data) {
                    if (data && data.order_id) {
                        updateOrderFollowUp(data);
                    }
                });

            } catch (error) {
                useWebSocket = false;
            }
        }

        function updateOrderFollowUp(data) {
            console.log('🔍 updateOrderFollowUp called with data:', data);

            // Find the row with this order ID
            $('#tableBody tr.searchable-row').each(function () {
                let rowOrderId = parseInt($(this).find('td:first').text());
                if (rowOrderId === data.order_id) {
                    const $row = $(this);

                    // Update the followup checkbox (13th column - index 12)
                    const $followupCell = $row.find('td').eq(12);

                    // Try multiple selectors to find the checkbox
                    let $checkbox = $followupCell.find('.followup-switch');
                    if ($checkbox.length === 0) {
                        $checkbox = $followupCell.find('input[type="checkbox"]');
                    }
                    if ($checkbox.length === 0) {
                        $checkbox = $followupCell.find('.switch-btn');
                    }
                    // If no checkbox found, create the entire cell content from scratch
                    if ($checkbox.length === 0) {

                        const isChecked = data.followup_call == 1;
                        const empId = parseInt(data.emp_id) || 0;

                        // Calculate disabled state
                        const shouldDisable = (function () {
                            if (isAdminOrManager) {
                                return true;
                            } else if (isSalesUser) {
                                if (empId !== 0 && empId !== currentUserId) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                            return false;
                        })();

                        // Remove ALL Switchery elements and checkboxes completely
                        $followupCell.find('.switchery').remove();
                        $followupCell.find('input[type="checkbox"]').remove();
                        $followupCell.find('.followup-switch').remove();
                        $followupCell.find('.switch-btn').remove();

                        // Now clear the cell completely
                        $followupCell.empty();

                        // Create checkbox HTML
                        const disabledAttr = shouldDisable ? 'disabled' : '';
                        const checkedAttr = isChecked ? 'checked' : '';
                        const newCheckboxHtml = `
                            <input type="checkbox"
                                   class="followup-switch switch-btn me-3"
                                   data-id="${data.order_id}"
                                   data-emp-id="${empId}"
                                   data-size="small"
                                   ${checkedAttr}
                                   ${disabledAttr} />
                        `;

                        $followupCell.append(newCheckboxHtml);

                        // Initialize Switchery
                        setTimeout(function () {
                            const $newCheckbox = $followupCell.find('.followup-switch');

                            if ($newCheckbox.length > 0) {

                                if (typeof Switchery !== 'undefined') {
                                    try {
                                        // Check if already initialized
                                        if ($newCheckbox[0].switchery) {
                                            console.log('⚠️ Checkbox already has Switchery, destroying first');
                                            $newCheckbox[0].switchery.destroy();
                                        }

                                        $newCheckbox[0].checked = isChecked;

                                        const switcheryInstance = new Switchery($newCheckbox[0], {
                                            size: 'small',
                                            disabled: shouldDisable
                                        });
                                    } catch (e) {
                                        console.error('❌ Error creating Switchery:', e);
                                        console.error('❌ Error stack:', e.stack);
                                    }
                                } else {
                                    console.error('❌ Switchery library not defined!');
                                }
                            } else {
                                console.error('❌ Checkbox not found after adding HTML!');
                            }
                        }, 100);

                        // Add info icon if needed
                        if (isChecked && (data.followup_note || data.followup_label)) {
                            const infoIconHtml = `
                                <i class="fa-solid fa-circle-info info-icon"
                                   data-id="${data.order_id}"
                                   data-note="${data.followup_note || ''}"
                                   data-label="${data.followup_label || ''}"
                                   data-label-display="${data.followup_label_display || ''}"
                                   data-can-edit="1"
                                   style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>
                            `;
                            $followupCell.append(infoIconHtml);
                        }

                        // Update "Follow By" column (14th column - index 13)
                        if (data.follow_by) {
                            const $followByCell = $row.find('td').eq(13);
                            $followByCell.text(data.follow_by);
                        }

                        // Highlight row
                        $row.css('background-color', '#d4edda');
                        setTimeout(function () {
                            $row.css('background-color', '');
                        }, 2000);

                        // Check if row should be hidden based on current filters
                        const currentUrl = new URL(window.location.href);
                        const followupFilter = currentUrl.searchParams.get('followup_filter');

                        if (followupFilter) {
                            // If "Called" filter is active and followup is unchecked, hide row
                            if (followupFilter === 'called' && !isChecked) {
                                $row.fadeOut(300, function () {
                                    $(this).remove();
                                });
                                // Also remove the collapse row
                                $('#history-row-' + data.order_id).remove();
                            }
                            // If "Not Called" filter is active and followup is checked, hide row
                            else if (followupFilter === 'not_called' && isChecked) {
                                $row.fadeOut(300, function () {
                                    $(this).remove();
                                });
                                // Also remove the collapse row
                                $('#history-row-' + data.order_id).remove();
                            }
                        }

                        // No need to re-initialize event handlers since we're using event delegation
                        // initializeEventHandlers(); // REMOVED - causes duplicate handlers

                        return; // Exit early since we created everything from scratch
                    }

                    // Update checkbox state (existing checkbox found)
                    if ($checkbox.length > 0) {
                        const isChecked = data.followup_call == 1;
                        const empId = parseInt(data.emp_id) || 0;


                        // Calculate disabled state
                        const shouldDisable = (function () {
                            if (isAdminOrManager) {
                                return true;
                            } else if (isSalesUser) {
                                if (empId !== 0 && empId !== currentUserId) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                            return false;
                        })();


                        // Destroy existing Switchery and remove all related elements
                        if ($checkbox[0].switchery) {
                            try {
                                $checkbox[0].switchery.destroy();
                            } catch (e) {
                                console.error('Error destroying Switchery:', e);
                            }
                        }

                        // Remove any leftover Switchery elements
                        $followupCell.find('.switchery').remove();

                        // Remove the old checkbox
                        $checkbox.remove();

                        // Create new checkbox HTML
                        const disabledAttr = shouldDisable ? 'disabled' : '';
                        const checkedAttr = isChecked ? 'checked' : '';
                        const newCheckboxHtml = `
                            <input type="checkbox"
                                   class="followup-switch switch-btn me-3"
                                   data-id="${data.order_id}"
                                   data-emp-id="${empId}"
                                   data-size="small"
                                   ${checkedAttr}
                                   ${disabledAttr} />
                        `;

                        // Insert new checkbox at the beginning of the cell
                        $followupCell.prepend(newCheckboxHtml);

                        // Capture variables for setTimeout closure
                        const _shouldDisable = shouldDisable;
                        const _isChecked = isChecked;
                        const _orderId = data.order_id;

                        // Use setTimeout to ensure DOM is ready before initializing Switchery
                        setTimeout(function () {
                            // Initialize Switchery on the new checkbox
                            const $newCheckbox = $followupCell.find('.followup-switch');
                            if ($newCheckbox.length > 0 && typeof Switchery !== 'undefined') {
                                try {
                                    // Set the checked property on the DOM element before Switchery init
                                    $newCheckbox[0].checked = _isChecked;

                                    new Switchery($newCheckbox[0], {
                                        size: 'small',
                                        disabled: _shouldDisable
                                    });
                                } catch (e) {
                                    console.error('❌ Error creating Switchery:', e);
                                }
                            }
                        }, 100); // Small delay to ensure DOM is ready

                        // Update or remove info icon (moved inside checkbox block to access shouldDisable)
                        const $infoIcon = $followupCell.find('.info-icon');
                        const $hoverNote = $followupCell.find('.hover-note');

                        // Calculate canEdit (opposite of shouldDisable)
                        const canEdit = !shouldDisable;

                        if (data.followup_call == 1 && (data.followup_note || data.followup_label)) {
                            // Update or create info icon
                            if ($infoIcon.length === 0) {
                                const cursorStyle = canEdit ? 'cursor: pointer;' : '';
                                const infoIconHtml = `
                                    <i class="fa-solid fa-circle-info info-icon"
                                       data-id="${data.order_id}"
                                       data-note="${data.followup_note || ''}"
                                       data-label="${data.followup_label || ''}"
                                       data-label-display="${data.followup_label_display || ''}"
                                       data-can-edit="1"
                                       style="cursor: pointer; color: #667eea; font-size: 18px; margin-left: 8px;"></i>
                                `;
                                $followupCell.append(infoIconHtml);
                            } else {
                                // Update existing info icon
                                $infoIcon.attr('data-note', data.followup_note || '');
                                $infoIcon.attr('data-label', data.followup_label || '');
                                $infoIcon.attr('data-label-display', data.followup_label_display || '');
                                $infoIcon.attr('data-can-edit', '1');
                                $infoIcon.css('cursor', 'pointer');
                                $infoIcon.css('color', '#667eea');
                                $infoIcon.css('font-size', '18px');
                            }
                        } else {
                            // Remove info icon if followup is unchecked
                            $infoIcon.remove();
                        }
                    }

                    // Update "Follow By" column (14th column - index 13) - same as recent_expire
                    if (data.follow_by) {
                        const $followByCell = $row.find('td').eq(13);
                        $followByCell.text(data.follow_by);
                    }

                    // Highlight the row briefly to show it was updated
                    $row.css('background-color', '#d4edda'); // Light green
                    setTimeout(function () {
                        $row.css('background-color', '');
                    }, 2000);

                    // Check if row should be hidden based on current filters
                    const currentUrl = new URL(window.location.href);
                    const followupFilter = currentUrl.searchParams.get('followup_filter');

                    if (followupFilter) {
                        const isFollowupChecked = data.followup_call == 1;

                        // If "Called" filter is active and followup is unchecked, hide row
                        if (followupFilter === 'called' && !isFollowupChecked) {
                            $row.fadeOut(300, function () {
                                $(this).remove();
                            });
                            // Also remove the collapse row
                            $('#history-row-' + data.order_id).remove();
                        }
                        // If "Not Called" filter is active and followup is checked, hide row
                        else if (followupFilter === 'not_called' && isFollowupChecked) {
                            $row.fadeOut(300, function () {
                                $(this).remove();
                            });
                            // Also remove the collapse row
                            $('#history-row-' + data.order_id).remove();
                        }
                    }

                    // No need to re-initialize event handlers since we're using event delegation
                    // initializeEventHandlers(); // REMOVED - causes duplicate handlers
                }
            });
        }

        function removeOrderFromTable(orderId) {
            let found = false;
            // Find the row with this order ID
            $('#tableBody tr.searchable-row').each(function () {
                let rowOrderId = parseInt($(this).find('td:first').text());

                if (rowOrderId === orderId) {
                    found = true;

                    // Animate removal
                    $(this).css('background-color', '#f8d7da'); // Light red
                    $(this).fadeOut(1000, function () {
                        $(this).remove();

                        // Check if table is empty
                        if ($('#tableBody tr.searchable-row').length === 0) {
                            $('#tableBody').html('<tr><td colspan="14" class="text-center">No records found</td></tr>');
                        }
                    });
                }
            });

        }

        // Start WebSocket connection after 2 seconds
        setTimeout(initializeWebSocket, 2000);

        // ============================================
        // PURCHASE HISTORY FEATURE
        // ============================================

        // Click handler ONLY for Order ID column (first column)
        $(document).on('click', '.order-row td:first-child, .order-id-text', function (e) {
            // Stop event from bubbling up to prevent double trigger
            e.stopPropagation();

            // Get the row element
            const $row = $(this).hasClass('order-row') ? $(this) : $(this).closest('.order-row');

            if (!$row.length) {
                return;
            }

            // Don't trigger if clicking on checkbox, button, or other interactive elements
            const $target = $(e.target);

            // Check if clicking on interactive elements or their children
            if ($target.is('.followup-switch, .switchery, button, a, input, .info-icon, .hover-note') ||
                $target.closest('.followup-switch, .switchery, button, a, input, .info-icon, .hover-note').length > 0) {
                return;
            }

            const orderId = $row.data('order-id');
            const userId = $row.data('user-id');
            const $collapseRow = $('#history-row-' + orderId);

            console.log('📦 Order row clicked:', {
                orderId: orderId,
                userId: userId,
                collapseRowExists: $collapseRow.length > 0,
                isVisible: $collapseRow.is(':visible')
            });

            if ($collapseRow.length === 0) {
                console.error('❌ Collapse row not found for order #' + orderId);
                return;
            }

            // Toggle this row
            if ($collapseRow.is(':visible')) {
                $collapseRow.slideUp(300);
                $row.removeClass('row-expanded');
                return;
            }


            // Close all other rows
            $('.collapse-row').slideUp(300);
            $('.order-row').removeClass('row-expanded');

            // Open this row
            $collapseRow.slideDown(300);
            $row.addClass('row-expanded');

            fetchPurchaseHistory(userId, orderId);
        });

        function fetchPurchaseHistory(userId, orderId) {
            const $container = $('#history-row-' + orderId + ' .purchase-history-container');
            $container.html('<div class="text-center" style="padding:20px;"><i class="fa fa-spinner fa-spin" style="font-size:24px;"></i><p>Loading...</p></div>');

            $.ajax({
                url: '{{ url("/order-user/purchase-history") }}/' + userId,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        displayPurchaseHistory(response, orderId);
                    } else {
                        $container.html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Purchase history error:', xhr.responseText);
                    $container.html('<div class="alert alert-danger">Failed to load purchase history. Error: ' + error + '</div>');
                }
            });
        }

        function displayPurchaseHistory(data, orderId) {
            const $container = $('#history-row-' + orderId + ' .purchase-history-container');

            // Get user info from first order (all orders belong to same user)
            const firstOrder = data.orders[0] || data.all_orders[0];
            const userName = firstOrder ? (firstOrder.user_name || 'Unknown User') : 'Unknown User';
            const userEmail = firstOrder ? (firstOrder.email || '') : '';
            const userContact = firstOrder ? (firstOrder.contact_no || '') : '';

            // Check if no orders found
            if (data.orders.length === 0) {
                let html = '<div style="padding:0; background:transparent;">';

                // Compact header with close button
                html += '<div class="purchase-history-header" style="position:sticky; top:0; z-index:100; margin-bottom:0; padding:10px 15px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:6px 6px 0 0; box-shadow:0 2px 6px rgba(102,126,234,0.2); display:flex; justify-content:space-between; align-items:center;">';
                html += '<div style="color:#ffffff; font-size:14px; font-weight:600;"><i class="fa fa-history" style="margin-right:6px;"></i>PURCHASE HISTORY</div>';
                html += '<button class="btn btn-sm close-history" data-order-id="' + orderId + '" style="background:rgba(255,255,255,0.2); border:none; color:#fff; padding:5px 12px; border-radius:4px; font-weight:600; font-size:12px; transition:all 0.3s;" onmouseover="this.style.background=\'rgba(255,255,255,0.3)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.2)\'">';
                html += '<i class="fa fa-times"></i></button></div>';

                // Compact No History Message
                html += '<div style="background:#fff; border-radius:0 0 6px 6px; padding:20px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.06);">';
                html += '<div style="width:50px; height:50px; background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">';
                html += '<i class="fa fa-shopping-cart" style="font-size:24px; color:#adb5bd;"></i></div>';
                html += '<p style="margin:0; color:#6c757d; font-size:14px; font-weight:500;">No successful purchase history</p>';
                html += '</div></div>';

                $container.html(html);
                return;
            }

            // Store all orders for load more functionality
            $container.data('all-orders', data.all_orders);
            $container.data('showing-count', data.showing_count);

            let html = '<div style="padding:0; background:transparent;">';

            // Simple header with just close button
            html += '<div class="purchase-history-header" style="position:sticky; top:0; z-index:100; margin-bottom:0; padding:12px 15px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:8px 8px 0 0; box-shadow:0 2px 8px rgba(102,126,234,0.3); display:flex; justify-content:space-between; align-items:center;">';
            html += '<div style="color:#ffffff; font-size:16px; font-weight:600; letter-spacing:0.5px;"><i class="fa fa-history" style="margin-right:8px;"></i>PURCHASE HISTORY</div>';
            html += '<button class="btn btn-sm close-history" data-order-id="' + orderId + '" style="background:rgba(255,255,255,0.2); border:none; color:#fff; padding:8px 16px; border-radius:6px; font-weight:600; font-size:13px; transition:all 0.3s;" onmouseover="this.style.background=\'rgba(255,255,255,0.3)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.2)\'">';
            html += '<i class="fa fa-times" style="margin-right:5px;"></i>Close</button>';
            html += '</div>';

            // Table Section
            html += '<div style="background:white; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.08); overflow:hidden;">';
            html += '<div style="overflow-x:auto;">';
            html += '<table class="table mb-0" id="purchase-history-table-' + orderId + '" style="margin:0; min-width:1200px;">';

            // Table Header - White text
            html += '<thead>';
            html += '<tr style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);">';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Order</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Amount</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Status</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Type</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2); min-width:150px;">Plan</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Payment</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2); min-width:120px;">Transaction</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Date</th>';
            html += '<th style="padding:10px 8px; font-weight:600; font-size:11px; color:#ffffff; text-transform:uppercase; border-bottom:2px solid rgba(255,255,255,0.2);">Sub</th>';
            html += '</tr></thead>';

            // Table Body
            html += '<tbody>';

            data.orders.forEach(function (order, index) {
                const statusColors = {
                    'success': { bg: '#d4edda', color: '#155724', badge: 'badge-success' },
                    'paid': { bg: '#d4edda', color: '#155724', badge: 'badge-success' }
                };

                const statusColor = statusColors[order.status] || statusColors['success'];
                const subBadge = order.is_subscription_active ? 'badge-success' : 'badge-secondary';
                const isCurrentOrder = order.id == orderId;
                const rowBg = isCurrentOrder ? 'background:linear-gradient(to right, #fff9e6, #fffbf0);' : (index % 2 === 0 ? 'background:#ffffff;' : 'background:#fafbfc;');
                const borderLeft = isCurrentOrder ? 'border-left:3px solid #ffc107;' : '';

                html += '<tr style="' + rowBg + borderLeft + ' transition:all 0.2s;" onmouseover="this.style.background=\'#f0f4ff\'" onmouseout="this.style.background=\'' + (isCurrentOrder ? 'linear-gradient(to right, #fff9e6, #fffbf0)' : (index % 2 === 0 ? '#ffffff' : '#fafbfc')) + '\'">';

                // Order ID - More compact
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; white-space:nowrap;">';
                if (isCurrentOrder) {
                    html += '<span style="font-weight:700; color:#667eea; font-size:13px;">';
                    html += '<i class="fa fa-arrow-right" style="color:#ffc107; margin-right:4px; font-size:10px;"></i>#' + order.id;
                    html += '</span>';
                } else {
                    html += '<span style="font-weight:500; color:#495057; font-size:12px;">#' + order.id + '</span>';
                }
                html += '</td>';

                // Amount
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="font-weight:700; color:#2c3e50; font-size:13px;">' + order.amount + '</span>';
                html += '</td>';

                // Status - More compact
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="background:' + statusColor.bg + '; color:' + statusColor.color + '; padding:4px 10px; border-radius:12px; font-size:10px; font-weight:600; text-transform:uppercase;">';
                html += '<i class="fa fa-check-circle" style="margin-right:3px;"></i>' + order.status;
                html += '</span></td>';

                // Type
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="background:#e3f2fd; color:#1976d2; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:600;">' + order.type + '</span>';
                html += '</td>';

                // Plan
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0; max-width:250px;">';
                html += '<div style="font-size:12px; color:#495057; line-height:1.4; word-wrap:break-word;">' + order.plan_items + '</div>';
                html += '</td>';

                // Payment Method
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<div style="display:flex; align-items:center;">';
                if (order.payment_method === 'Razorpay') {
                    html += '<i class="fa fa-credit-card" style="color:#3395ff; margin-right:6px;"></i>';
                } else if (order.payment_method === 'Stripe') {
                    html += '<i class="fab fa-stripe" style="color:#635bff; margin-right:6px;"></i>';
                } else if (order.payment_method === 'PhonePe') {
                    html += '<i class="fa fa-mobile-alt" style="color:#5f259f; margin-right:6px;"></i>';
                } else {
                    html += '<i class="fa fa-check-circle" style="color:#28a745; margin-right:6px;"></i>';
                }
                html += '<span style="font-size:11px; color:#6c757d; font-weight:500;">' + order.payment_method + '</span>';
                html += '</div></td>';

                // Transaction ID
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<code style="background:#f8f9fa; padding:4px 8px; border-radius:4px; font-size:10px; color:#495057; border:1px solid #e9ecef; display:inline-block; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + order.transaction_id + '">';
                html += order.transaction_id;
                html += '</code></td>';

                // Date
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<div style="font-size:11px; color:#6c757d;">';
                html += '<i class="fa fa-calendar" style="margin-right:5px; color:#adb5bd;"></i>';
                html += order.created_at.split(' ')[0] + '<br>';
                html += '<i class="fa fa-clock" style="margin-right:5px; color:#adb5bd;"></i>';
                html += order.created_at.split(' ')[1];
                html += '</div></td>';

                // Subscription
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span class="badge ' + subBadge + '" style="font-size:10px; padding:6px 10px; border-radius:12px;">';
                html += order.is_subscription_active ? '<i class="fa fa-check-circle"></i> Active' : '<i class="fa fa-times-circle"></i> Inactive';
                html += '</span></td>';

                html += '</tr>';
            });

            html += '</tbody></table></div>';

            // Load More Button - More compact
            if (data.remaining_count > 0) {
                html += '<div style="padding:12px; text-align:center; background:#f8f9fa; border-top:1px solid #dee2e6;">';
                html += '<button class="btn btn-primary load-more-history" data-order-id="' + orderId + '" style="padding:8px 20px; font-weight:600; font-size:12px; border-radius:6px; box-shadow:0 2px 6px rgba(102,126,234,0.3);">';
                html += '<i class="fa fa-chevron-down" style="margin-right:6px;"></i> Load More (' + data.remaining_count + ' more)';
                html += '</button>';
                html += '</div>';
            }

            html += '</div></div>';
            $container.html(html);
        }

        // Load More button handler
        $(document).on('click', '.load-more-history', function (e) {
            e.stopPropagation();
            const orderId = $(this).data('order-id');
            const $container = $('#history-row-' + orderId + ' .purchase-history-container');
            const allOrders = $container.data('all-orders');
            const currentCount = $container.data('showing-count');

            // Get table tbody
            const $tbody = $('#purchase-history-table-' + orderId + ' tbody');

            // Get remaining orders
            const remainingOrders = allOrders.slice(currentCount);

            // Render remaining orders
            let html = '';
            remainingOrders.forEach(function (order, index) {
                const actualIndex = currentCount + index;
                const statusColors = {
                    'success': { bg: '#d4edda', color: '#155724', badge: 'badge-success' },
                    'paid': { bg: '#d4edda', color: '#155724', badge: 'badge-success' }
                };

                const statusColor = statusColors[order.status] || statusColors['success'];
                const subBadge = order.is_subscription_active ? 'badge-success' : 'badge-secondary';
                const isCurrentOrder = order.id == orderId;
                const rowBg = isCurrentOrder ? 'background:linear-gradient(to right, #fff9e6, #fffbf0);' : (actualIndex % 2 === 0 ? 'background:#ffffff;' : 'background:#fafbfc;');
                const borderLeft = isCurrentOrder ? 'border-left:3px solid #ffc107;' : '';

                html += '<tr style="' + rowBg + borderLeft + ' transition:all 0.2s;" onmouseover="this.style.background=\'#f0f4ff\'" onmouseout="this.style.background=\'' + (isCurrentOrder ? 'linear-gradient(to right, #fff9e6, #fffbf0)' : (actualIndex % 2 === 0 ? '#ffffff' : '#fafbfc')) + '\'">';

                // Order ID - Compact
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; white-space:nowrap;">';
                if (isCurrentOrder) {
                    html += '<span style="font-weight:700; color:#667eea; font-size:13px;">';
                    html += '<i class="fa fa-arrow-right" style="color:#ffc107; margin-right:4px; font-size:10px;"></i>#' + order.id;
                    html += '</span>';
                } else {
                    html += '<span style="font-weight:500; color:#495057; font-size:12px;">#' + order.id + '</span>';
                }
                html += '</td>';

                // Amount
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="font-weight:700; color:#2c3e50; font-size:13px;">' + order.amount + '</span>';
                html += '</td>';

                // Status - Compact
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="background:' + statusColor.bg + '; color:' + statusColor.color + '; padding:4px 10px; border-radius:12px; font-size:10px; font-weight:600; text-transform:uppercase;">';
                html += '<i class="fa fa-check-circle" style="margin-right:3px;"></i>' + order.status;
                html += '</span></td>';

                // Type
                html += '<td style="padding:8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span style="background:#e3f2fd; color:#1976d2; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:600;">' + order.type + '</span>';
                html += '</td>';

                // Plan
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0; max-width:250px;">';
                html += '<div style="font-size:12px; color:#495057; line-height:1.4; word-wrap:break-word;">' + order.plan_items + '</div>';
                html += '</td>';

                // Payment Method
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<div style="display:flex; align-items:center;">';
                if (order.payment_method === 'Razorpay') {
                    html += '<i class="fa fa-credit-card" style="color:#3395ff; margin-right:6px;"></i>';
                } else if (order.payment_method === 'Stripe') {
                    html += '<i class="fab fa-stripe" style="color:#635bff; margin-right:6px;"></i>';
                } else if (order.payment_method === 'PhonePe') {
                    html += '<i class="fa fa-mobile-alt" style="color:#5f259f; margin-right:6px;"></i>';
                } else {
                    html += '<i class="fa fa-check-circle" style="color:#28a745; margin-right:6px;"></i>';
                }
                html += '<span style="font-size:11px; color:#6c757d; font-weight:500;">' + order.payment_method + '</span>';
                html += '</div></td>';

                // Transaction ID
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<code style="background:#f8f9fa; padding:4px 8px; border-radius:4px; font-size:10px; color:#495057; border:1px solid #e9ecef; display:inline-block; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + order.transaction_id + '">';
                html += order.transaction_id;
                html += '</code></td>';

                // Date
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<div style="font-size:11px; color:#6c757d;">';
                html += '<i class="fa fa-calendar" style="margin-right:5px; color:#adb5bd;"></i>';
                html += order.created_at.split(' ')[0] + '<br>';
                html += '<i class="fa fa-clock" style="margin-right:5px; color:#adb5bd;"></i>';
                html += order.created_at.split(' ')[1];
                html += '</div></td>';

                // Subscription
                html += '<td style="padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0;">';
                html += '<span class="badge ' + subBadge + '" style="font-size:10px; padding:6px 10px; border-radius:12px;">';
                html += order.is_subscription_active ? '<i class="fa fa-check-circle"></i> Active' : '<i class="fa fa-times-circle"></i> Inactive';
                html += '</span></td>';

                html += '</tr>';
            });

            // Append to tbody
            $tbody.append(html);

            // Update showing count
            $container.data('showing-count', allOrders.length);

            // Remove load more button
            $(this).parent().remove();
        });

        // Close button handler
        $(document).on('click', '.close-history', function (e) {
            e.stopPropagation();
            const orderId = $(this).data('order-id');
            $('#history-row-' + orderId).slideUp(300);
            $('[data-order-id="' + orderId + '"].order-row').removeClass('row-expanded');
        });

        // ============================================
        // CREATE PAYMENT LINK FEATURE
        // ============================================

        // Custom notification function for payment link
        function showNotification(type, title, message) {
            const $modal = $('#notificationModal');
            const $backdrop = $('#notificationBackdrop');
            const $header = $('#notificationHeader');
            const $icon = $('#notificationIcon');
            const $title = $('#notificationTitle');
            const $message = $('#notificationMessage');

            console.log('showNotification called:', { type, title, message });

            if (type === 'success') {
                $header.css('background', 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)');
                $icon.html('<i class="fa fa-check-circle" style="color:#28a745;"></i>');
                $title.css('color', '#155724').text(title);
            } else if (type === 'error') {
                $header.css('background', 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)');
                $icon.html('<i class="fa fa-times-circle" style="color:#dc3545;"></i>');
                $title.css('color', '#721c24').text(title);
            } else if (type === 'info') {
                $header.css('background', 'linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%)');
                $icon.html('<i class="fa fa-info-circle" style="color:#0c5460;"></i>');
                $title.css('color', '#0c5460').text(title);
            }

            $message.html(message);

            // Show backdrop and modal
            $backdrop.fadeIn(300);
            $modal.fadeIn(300).css('display', 'block').addClass('show');
            $('body').addClass('modal-open');
        }

        // Close notification modal
        $('#closeNotificationBtn, #notificationBackdrop').on('click', function () {
            const $modal = $('#notificationModal');
            const $backdrop = $('#notificationBackdrop');

            $modal.fadeOut(300).removeClass('show');
            $backdrop.fadeOut(300);
            $('body').removeClass('modal-open');
        });

        // Validate mobile number
        function validateMobileNumber(number) {
            // Remove spaces and special characters
            const cleaned = number.replace(/[\s\-\(\)]/g, '');

            // Check if it's 10 digits
            if (!/^\d{10}$/.test(cleaned)) {
                return {
                    valid: false,
                    message: 'Mobile number must be exactly 10 digits'
                };
            }

            // Check for repeating digits (like 9999999999)
            if (/^(\d)\1{9}$/.test(cleaned)) {
                return {
                    valid: false,
                    message: 'Mobile number cannot have all same digits (e.g., 9999999999)'
                };
            }

            // Check for sequential digits (like 1234567890)
            const sequential = '01234567890123456789';
            if (sequential.includes(cleaned) || sequential.split('').reverse().join('').includes(cleaned)) {
                return {
                    valid: false,
                    message: 'Mobile number cannot be sequential (e.g., 1234567890)'
                };
            }

            // Check if starts with valid digit (6-9 for Indian numbers)
            if (!/^[6-9]/.test(cleaned)) {
                return {
                    valid: false,
                    message: 'Mobile number must start with 6, 7, 8, or 9'
                };
            }

            return {
                valid: true,
                message: 'Valid mobile number'
            };
        }

        // Open payment link modal
        $('#createPaymentLinkBtn').on('click', function () {
            $('#paymentLinkForm')[0].reset();
            $('#paymentLinkResult').hide();
            $('#createLinkBtn').prop('disabled', false).html('<i class="fa fa-link"></i> Create Link');
            $('#paymentLinkModal').modal('show');

            // Reset email validation
            $('#email').removeClass('is-valid is-invalid');
            $('#email_validation_msg').hide();

            // Load plans for default subscription type (new_sub)
            loadPlans('new_sub', $('#paymentLinkModal #plan_id'));
        });

        // Email validation on blur
        $('#email').on('blur', function () {
            const email = $(this).val().trim();

            if (!email) {
                return;
            }

            // Show loading state
            $('#email_validation_msg').removeClass('text-success text-danger')
                .addClass('text-info')
                .html('<i class="fa fa-spinner fa-spin"></i> Checking email...')
                .show();

            // Check if email exists in user_data table
            $.ajax({
                url: '{{ route("order_user.validate_email") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email
                },
                success: function (response) {
                    if (response.success) {
                        if (response.exists && response.is_active) {
                            // Email exists and user is active
                            $('#email').removeClass('is-invalid').addClass('is-valid');
                            $('#email_validation_msg').removeClass('text-danger text-info')
                                .addClass('text-success')
                                .html('<i class="fa fa-check-circle"></i> Email verified - User is active')
                                .show();
                        } else if (response.exists && !response.is_active) {
                            // Email exists but user is inactive
                            $('#email').removeClass('is-valid').addClass('is-invalid');
                            $('#email_validation_msg').removeClass('text-success text-info')
                                .addClass('text-danger')
                                .html('<i class="fa fa-times-circle"></i> User account is inactive. Please activate the account first.')
                                .show();
                        } else {
                            // Email does not exist
                            $('#email').removeClass('is-valid').addClass('is-invalid');
                            $('#email_validation_msg').removeClass('text-success text-info')
                                .addClass('text-danger')
                                .html('<i class="fa fa-times-circle"></i> Email not found in system. Please register first.')
                                .show();
                        }
                    } else {
                        $('#email_validation_msg').removeClass('text-success text-info')
                            .addClass('text-danger')
                            .html('<i class="fa fa-exclamation-triangle"></i> Error validating email')
                            .show();
                    }
                },
                error: function () {
                    $('#email_validation_msg').removeClass('text-success text-info')
                        .addClass('text-danger')
                        .html('<i class="fa fa-exclamation-triangle"></i> Error validating email')
                        .show();
                }
            });
        });

        function syncAddTransactionPlanSectionUi() {
            const $modal = $('#add_transaction_model');
            const type = $modal.find('select[name="subscription_type"]').val() || 'new_sub';
            const $planSelect = $modal.find('#plan_id');
            const $planRowFields = $modal.find('[name="additional_days"], [name="usage_purpose"]');
            const $walletWhere = $modal.find('[name="fromWallet"], [name="fromWhere"]');
            const $caricatures = $modal.find('[name="caricatures"]');
            const $help = $('#txn_subscription_type_help');

            const helpTexts = {
                new_sub: 'New subscription: choose plan, usage purpose, and optional caricature credits.',
                old_sub: 'Old subscription: choose legacy plan and related fields.',
                vendor_panel: 'Vendor Panel Access: records payment only; plan and usage fields below are not used.',
                ai_tools_credit: 'AI Tools Credit: enter caricature count only below; credits are applied from template rates.',
                addon_other: 'Other: records payment only; all plan and extra fields below are disabled.'
            };
            $help.text(helpTexts[type] || helpTexts.new_sub);

            if (type === 'new_sub' || type === 'old_sub') {
                $planSelect.prop('disabled', false).prop('required', true);
                $planRowFields.prop('disabled', false);
                $walletWhere.prop('disabled', false);
                $caricatures.prop('disabled', false);
                $modal.find('[name="usage_purpose"]').prop('required', true);
                $modal.find('[name="additional_days"], [name="caricatures"]').prop('required', true);
                return;
            }

            $planSelect.prop('disabled', true).prop('required', false).html('<option value="">— Not applicable —</option>');
            $planRowFields.prop('disabled', true).prop('required', false);
            $walletWhere.prop('disabled', true).prop('required', false);

            if (type === 'ai_tools_credit') {
                $caricatures.prop('disabled', false).prop('required', true);
            } else {
                $caricatures.prop('disabled', true).prop('required', false);
            }
        }

        // Load plans when the Add Transaction modal opens
        $('#add_transaction_model').on('show.bs.modal', function () {
            const subscriptionType = $(this).find('select[name="subscription_type"]').val() || 'new_sub';
            syncAddTransactionPlanSectionUi();
            if (subscriptionType === 'new_sub' || subscriptionType === 'old_sub') {
                loadPlans(subscriptionType, $(this).find('#plan_id'));
            }
        });

        // Load plans when subscription type changes (scoped per modal)
        $('#add_transaction_model').on('change', 'select[name="subscription_type"]', function () {
            const v = $(this).val();
            syncAddTransactionPlanSectionUi();
            if (v === 'new_sub' || v === 'old_sub') {
                loadPlans(v, $('#add_transaction_model #plan_id'));
            }
        });

        $('#paymentLinkModal').on('change', 'input[name="subscription_type"]', function () {
            loadPlans($(this).val(), $('#paymentLinkModal #plan_id'));
        });

        // Function to load plans based on subscription type into a specific select element
        function loadPlans(subscriptionType, $planSelect) {
            if (!$planSelect || !$planSelect.length) return;
            $planSelect.html('<option value="">Loading plans...</option>').prop('disabled', true);

            $.ajax({
                url: '{{ url("/order-user/get-plans") }}',
                method: 'GET',
                data: { subscription_type: subscriptionType },
                success: function (response) {
                    if (response.success && response.plans && response.plans.length) {
                        let options = '<option value="">-- Select Plan --</option>';
                        response.plans.forEach(function (plan) {
                            options += `<option value="${plan.id}">${plan.name} - ₹${plan.price}</option>`;
                        });
                        $planSelect.html(options).prop('disabled', false);
                    } else {
                        $planSelect.html('<option value="">No plans available</option>').prop('disabled', true);
                    }
                },
                error: function () {
                    $planSelect.html('<option value="">Error loading plans</option>').prop('disabled', true);
                }
            });
        }

        // Handle payment link form submission
        $('#paymentLinkForm').on('submit', function (e) {
            e.preventDefault();

            // Check if email is validated
            if ($('#email').hasClass('is-invalid')) {
                showNotification('error', 'Invalid Email', 'Please enter a valid email that exists in the system and has an active account.');
                return false;
            }

            if (!$('#email').hasClass('is-valid')) {
                showNotification('error', 'Email Not Validated', 'Please wait for email validation to complete.');
                return false;
            }

            // Validate mobile number
            const contactNo = $('#contact_no').val();
            const validation = validateMobileNumber(contactNo);

            if (!validation.valid) {
                showNotification('error', 'Invalid Mobile Number', validation.message + '<br><br><strong>Example of valid number:</strong> 9876543210');
                return false;
            }

            // Validate caricature count
            const caricatureCount = parseInt($('#caricature').val()) || 0;
            if (caricatureCount < 0) {
                showNotification('error', 'Invalid Caricature Count', 'Caricature count cannot be negative.');
                return false;
            }
            if (caricatureCount > 100) {
                showNotification('error', 'Invalid Caricature Count', 'Caricature count cannot exceed 100.');
                return false;
            }

            const $btn = $('#createLinkBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating...');

            const formData = $(this).serialize();

            $.ajax({
                url: '{{ route("order_user.create_payment_link") }}',
                method: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        // Show success message with payment link
                        $('#result_reference_id').text(response.data.reference_id);
                        $('#result_amount').text(response.data.amount);
                        $('#result_payment_link').val(response.data.payment_link);

                        // Also set the clickable link
                        $('#clickable_payment_link')
                            .attr('href', response.data.payment_link)
                            .text(response.data.payment_link);

                        // Verify the value was set correctly
                        const setLink = $('#result_payment_link').val();
                        // Disable form fields
                        $('#paymentLinkForm input, #paymentLinkForm select, #paymentLinkForm input[type="radio"]').prop('disabled', true);
                        $btn.hide();

                        // Update modal header to show success
                        $('#paymentLinkModalHeader').removeClass('bg-primary').addClass('bg-success');
                        $('#paymentLinkModalTitle').html('<i class="fa fa-check-circle"></i> Payment Link Created Successfully!');

                        // Show payment link result with animation
                        $('#paymentLinkResult').slideDown(400, function () {
                            // Scroll to the result
                            $('#paymentLinkResult')[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                            // Show success notification
                            showNotification('success', 'Payment Link Created!',
                                'Payment link has been created successfully. You can now copy and share it with the customer.');
                        });

                        // Reload the table in background to show the new sale (without closing modal)
                        setTimeout(function () {
                            console.log('Reloading table to show new sale...');
                            if (typeof table !== 'undefined' && table.ajax) {
                                table.ajax.reload(null, false); // false = stay on current page
                            }
                            // Don't reload page - keep modal open so user can copy link
                        }, 2000);

                        // Don't show notification modal - user can see the result directly
                    } else {
                        showNotification('error', 'Failed to Create Link', response.message || 'An error occurred while creating the payment link');
                        $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Create Link');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', { xhr, status, error });
                    console.error('Response:', xhr.responseText);

                    let errorMsg = 'Failed to create payment link';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch (e) {
                            console.error('Failed to parse error response');
                        }
                    }

                    // Show user-friendly error message
                    if (errorMsg.includes('Recurring digits')) {
                        errorMsg = 'The contact number has repeating digits which is not allowed by the payment gateway.<br><br><strong>Please use a valid mobile number.</strong>';
                    }

                    showNotification('error', 'Error Creating Payment Link', errorMsg);
                    $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Create Link');
                }
            });
        });

        // Copy payment link - COMPLETELY NEW ROBUST METHOD
        $('#copyLinkBtn').on('click', function () {
            const link = $('#result_payment_link').val();

            if (!link || link.trim() === '') {
                alert('No payment link found to copy!');
                return;
            }

            // Method 1: Try modern Clipboard API first (most reliable)
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(function () {
                    // Success
                    showCopySuccess();
                }).catch(function (err) {
                    // If Clipboard API fails, try fallback
                    copyUsingFallback(link);
                });
            } else {
                // Browser doesn't support Clipboard API, use fallback
                copyUsingFallback(link);
            }

            function copyUsingFallback(text) {
                // Method 2: Use input field (more reliable than textarea)
                const $input = $('<input>')
                    .val(text)
                    .css({
                        position: 'absolute',
                        left: '-9999px',
                        top: '0'
                    })
                    .appendTo('body');

                // Select the text
                $input[0].select();
                $input[0].setSelectionRange(0, 99999); // For mobile devices

                // Try to copy
                let success = false;
                try {
                    success = document.execCommand('copy');
                } catch (err) {
                    console.error('execCommand error:', err);
                }

                // Remove the input
                $input.remove();

                if (success) {
                    showCopySuccess();
                } else {
                    // Last resort: Show the link in an alert so user can manually copy
                    alert('Automatic copy failed. Please copy this link manually:\n\n' + text);
                }
            }

            function showCopySuccess() {
                const btn = document.getElementById('copyLinkBtn');
                const originalHTML = btn.innerHTML;
                const originalBg = btn.style.background;

                btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                btn.style.background = '#28a745';

                // Show toast notification
                const $toast = $('<div>')
                    .css({
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        background: '#28a745',
                        color: 'white',
                        padding: '15px 25px',
                        borderRadius: '8px',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
                        zIndex: 99999,
                        fontSize: '14px',
                        fontWeight: '600'
                    })
                    .html('<i class="fa fa-check-circle"></i> Payment Link Copied! You can now paste it.')
                    .appendTo('body');

                setTimeout(function () {
                    btn.innerHTML = originalHTML;
                    btn.style.background = originalBg;
                    $toast.fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 2500);
            }
        });

        // Open payment link in new tab
        $('#openLinkBtn').on('click', function () {
            const link = $('#result_payment_link').val();

            if (link && link.trim() !== '') {
                window.open(link, '_blank');

                // Show toast
                const $toast = $('<div>')
                    .css({
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        background: '#17a2b8',
                        color: 'white',
                        padding: '15px 25px',
                        borderRadius: '8px',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
                        zIndex: 99999,
                        fontSize: '14px',
                        fontWeight: '600'
                    })
                    .html('<i class="fa fa-external-link"></i> Link Opened in New Tab!')
                    .appendTo('body');

                setTimeout(() => {
                    $toast.fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 2000);
            }
        });

        // Show Link in Alert (for manual copy)
        $('#showLinkBtn').on('click', function () {
            const link = $('#result_payment_link').val();

            if (link && link.trim() !== '') {
                // Show in a prompt so user can select and copy
                prompt('Payment Link (Select All and Copy):', link);

                // Also show toast
                const $toast = $('<div>')
                    .css({
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        background: '#ffc107',
                        color: '#000',
                        padding: '15px 25px',
                        borderRadius: '8px',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
                        zIndex: 99999,
                        fontSize: '14px',
                        fontWeight: '600'
                    })
                    .html('<i class="fa fa-info-circle"></i> Select the link and press Ctrl+C to copy')
                    .appendTo('body');

                setTimeout(() => {
                    $toast.fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 3000);
            }
        });

        // Share on WhatsApp
        $(document).on('click', '#shareWhatsAppBtn', function () {
            const link = $('#result_payment_link').val();
            const amount = $('#result_amount').text();
            const refId = $('#result_reference_id').text();

            const message = `Hello! 👋\n\nPlease complete your payment of ₹${amount}\n\nReference ID: ${refId}\n\nPayment Link: ${link}\n\nYou can pay using UPI, Cards, Net Banking, or Wallets.\n\nThank you!`;

            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        });

        // Select link for manual copy
        $('#selectLinkBtn').on('click', function () {
            const $input = $('#result_payment_link');
            const link = $input.val();

            // Focus and select the input
            $input[0].focus();
            $input[0].select();
            $input[0].setSelectionRange(0, 99999);

            // Change button text
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.html('<i class="fa fa-check"></i> Text Selected! Now Press Ctrl+C')
                .removeClass('btn-outline-secondary')
                .addClass('btn-success');

            setTimeout(() => {
                $btn.html(originalHtml)
                    .removeClass('btn-success')
                    .addClass('btn-outline-secondary');
            }, 3000);

            // Show toast
            const $toast = $('<div>')
                .css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    background: '#007bff',
                    color: 'white',
                    padding: '15px 25px',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
                    zIndex: 99999,
                    fontSize: '14px',
                    fontWeight: '600'
                })
                .html('<i class="fa fa-info-circle"></i> Link Selected! Press Ctrl+C to Copy')
                .appendTo('body');

            setTimeout(() => {
                $toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        });

        // Reset modal when closed
        $('#paymentLinkModal').on('hidden.bs.modal', function () {
            $('#paymentLinkForm')[0].reset();
            $('#paymentLinkForm input, #paymentLinkForm select, #paymentLinkForm input[type="radio"]').prop('disabled', false);
            $('#paymentLinkResult').hide();
            $('#createLinkBtn').show().prop('disabled', false).html('<i class="fa fa-link"></i> Create Link');

            // Reset modal header
            $('#paymentLinkModalHeader').removeClass('bg-success').addClass('bg-primary');
            $('#paymentLinkModalTitle').html('<i class="fa fa-link"></i> Create Payment Link');
        });

        // Clean up followup modal when closed
        $('#followupModal').on('hidden.bs.modal', function () {

            // Force remove any lingering backdrops
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');

            // Reset form
            $('#followupForm')[0].reset();
            $('#followup_order_id').val('');
            $('#followup_user_id').val('');

        });

        // Global cleanup for any lingering modal backdrops
        // This prevents blank popups from appearing
        setInterval(function () {
            // Check if there are any modal backdrops but no visible modals
            const $backdrops = $('.modal-backdrop');
            const $visibleModals = $('.modal.show, .modal.in');

            if ($backdrops.length > 0 && $visibleModals.length === 0) {
                $backdrops.remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
                $('body').css('overflow', '');
            }

            // Also check for body.modal-open without any visible modals
            if ($('body').hasClass('modal-open') && $visibleModals.length === 0) {
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
                $('body').css('overflow', '');
            }
        }, 500); // Check every 500ms for faster cleanup

        // Auto-open Purchase History if coming from payment success page
        $(document).ready(function () {
            const purchaseHistoryData = sessionStorage.getItem('openPurchaseHistory');
            if (purchaseHistoryData) {
                try {
                    const data = JSON.parse(purchaseHistoryData);

                    // Clear the flag
                    sessionStorage.removeItem('openPurchaseHistory');

                    // Find the order row and trigger click
                    const orderRow = $(`.order-row[data-order-id="${data.orderId}"]`);
                    if (orderRow.length > 0) {
                        // Scroll to the order
                        orderRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Highlight the row briefly
                        orderRow.css('background-color', '#ffffcc');
                        setTimeout(() => {
                            orderRow.css('background-color', '');
                        }, 2000);

                        // Click to open purchase history
                        setTimeout(() => {
                            orderRow.trigger('click');
                        }, 500);
                    } else {
                        // Order not in current view (might be success status)
                        // Show a toast notification
                        if (typeof toastr !== 'undefined') {
                            toastr.success(`Payment successful for ${data.userName}! Order ID: ${data.orderId}`, 'Success', {
                                timeOut: 5000,
                                closeButton: true,
                                progressBar: true
                            });
                        } else {
                            alert(`Payment successful! Order ID: ${data.orderId}\n\nNote: Success orders are shown in Purchase History only.`);
                        }
                    }
                } catch (e) {
                    console.error('Error parsing purchase history data:', e);
                }
            }
        });
    });
</script>
</body>

</html>

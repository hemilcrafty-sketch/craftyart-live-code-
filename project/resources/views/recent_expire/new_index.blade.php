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
                                <div class="meta-filter-row">
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Records</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $filterType == 'all' ? 'active' : '' }}"
                                                data-filter="filter_type" data-value="all"> All
                                        </button>
                                    </div>
                                    <!-- Amount -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Amount</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $amountSort == 'desc' ? 'active' : '' }}"
                                                data-filter="amount_sort" data-value="desc">High
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $amountSort == 'asc' ? 'active' : '' }}"
                                                data-filter="amount_sort" data-value="asc">Low
                                        </button>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">WhatsApp</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $whatsappFilter == 'sent' ? 'active' : '' }}"
                                                data-filter="whatsapp_filter" data-value="sent">Sent
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $whatsappFilter == 'not_sent' ? 'active' : '' }}"
                                                data-filter="whatsapp_filter" data-value="not_sent">Not Sent
                                        </button>
                                    </div>

                                    <!-- Email -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Email</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $emailFilter == 'sent' ? 'active' : '' }}"
                                                data-filter="email_filter" data-value="sent">Sent
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $emailFilter == 'not_sent' ? 'active' : '' }}"
                                                data-filter="email_filter" data-value="not_sent">Not Sent
                                        </button>
                                    </div>

                                    <!-- Followup -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Followup</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $followupFilter == 'called' ? 'active' : '' }}"
                                                data-filter="followup_filter" data-value="called">Called
                                        </button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $followupFilter == 'not_called' ? 'active' : '' }}"
                                                data-filter="followup_filter" data-value="not_called">Not Called
                                        </button>
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

                                    <!-- Usage Type Filter -->
                                    <div class="meta-filter-group">
                                        <span class="meta-filter-label">Usage Type</span>
                                        <button type="button"
                                                class="meta-filter-btn {{ $usageTypeFilter == 'personal' ? 'active' : '' }}"
                                                data-filter="usage_type_filter" data-value="personal">Personal</button>
                                        <button type="button"
                                                class="meta-filter-btn {{ $usageTypeFilter == 'professional' ? 'active' : '' }}"
                                                data-filter="usage_type_filter" data-value="professional">Professional</button>
                                    </div>

                                    <div class="search-container">
                                        <form method="GET" action="{{ route('recent_expire.new_index') }}"
                                              id="searchForm">
                                            <div class="search-box">
                                                <i class="fas fa-search search-icon"></i>
                                                <input type="text" class="search-input" name="search"
                                                       id="globalSearch" value="{{ request('search') }}"
                                                       placeholder="Search all records...">

                                                <!-- Preserve all existing parameters -->
                                                @foreach (request()->all() as $key => $value)
                                                @if ($key !== 'search' && $key !== 'page')
                                                @if (is_array($value))
                                                @foreach ($value as $arrayValue)
                                                <input type="hidden" name="{{ $key }}[]"
                                                       value="{{ $arrayValue }}">
                                                @endforeach
                                                @else
                                                <input type="hidden" name="{{ $key }}"
                                                       value="{{ $value }}">
                                                @endif
                                                @endif
                                                @endforeach

                                                <button type="submit" style="display:none;"></button>
                                                <button type="button" class="clear-search" id="clearSearch">×</button>
                                            </div>
                                            <input type="hidden" name="followup_label_filter"
                                                   value="{{ $followupLabelFilter }}">
                                            <input type="hidden" name="usage_type_filter"
                                                   value="{{ $usageTypeFilter }}">
                                        </form>
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
                            <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>name</th>
                                <th>email</th>
                                <th>Contact No</th>
                                <th>Plan</th>
                                <th>paid amount</th>
                                <th>expire date</th>
                                <th>Email Send</th>
                                <th>Whatsapp Send</th>
                                <th>FollowUp Call</th>
                                <th>Follow By</th>
                                <th>Transaction</th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            @foreach ($recentExpires as $recentExpire)
                            <tr class="searchable-row">
                                <td>{{ $recentExpire->id ?? '-' }}</td>
                                <td>{{ $recentExpire->name ?? ($recentExpire->userData?->name ?? '-') }}</td>
                                <td>{{ $recentExpire->email ?? ($recentExpire->userData?->email ?? '-') }}</td>
                                <td>{{ $recentExpire->contact_no ?? ($recentExpire->userData?->number ?? '-') }}</td>
                                <td>
                                    @php
                                    $related = $recentExpire->related_plan;
                                    @endphp

                                    @if ($recentExpire->type == 0)
                                    {{ $related->package_name ?? '-' }}
                                    @elseif($recentExpire->type == 1)
                                    {{ $related->plan->name ?? ($related->name ?? '-') }}
                                    @elseif($recentExpire->type == 2)
                                    {{ $related->plan->name ?? ($related->subPlan->name ?? '-') }}
                                    @endif
                                </td>

                                <td>{{ $recentExpire->amount_with_symbol }}</td>

                                </td>
                                <td>{{ $recentExpire->expired_at }}</td>
                                <td style="font-size: 15px;">
                                    @if ($recentExpire->email_sent > 0)
                                    <span style="color: green; font-weight: bold;">✔
                                                    {{ $recentExpire->email_sent }}</span>
                                    @else
                                    <span style="color: red; font-weight: bold;">✗</span>
                                    @endif
                                </td>
                                <td style="font-size: 15px;">
                                    @if ($recentExpire->wp_sent > 0)
                                    <span style="color: green; font-weight: bold;">✔
                                                    {{ $recentExpire->wp_sent }}</span>
                                    @else
                                    <span style="color: red; font-weight: bold;">✗</span>
                                    @endif
                                </td>
                                <td style="position: relative; white-space: nowrap;">
                                    <input type="checkbox" class="followup-switch switch-btn me-3"
                                           data-id="{{ $recentExpire->id }}" data-size="small"
                                           @if ($recentExpire->expire_followup_call == 1) checked @endif />
                                     @if (!empty($recentExpire->expire_followup_note) || !empty($recentExpire->expire_followup_label))
                                    <i class="fa-solid fa-circle-info info-icon"
                                       data-id="{{ $recentExpire->id }}"
                                       data-note="{{ $recentExpire->expire_followup_note }}"
                                       data-label="{{ $recentExpire->expire_followup_label }}"
                                       data-label-display="{{ $followupLabels[$recentExpire->expire_followup_label] ?? $recentExpire->expire_followup_label }}"></i>
                                    @endif
                                </td>
                                <td style="word-break: keep-all">{{ $roleManager::getUploaderName($recentExpire->support_emp_id) }}</td>
                                <td>
                                    <button class="btn btn-outline-primary open-transaction-modal"
                                            style="font-size: 12px;"
                                            data-email="{{ $recentExpire->email ?? ($recentExpire->userData?->email ?? '-') }}"
                                            data-contact="{{ $recentExpire->contact_no ?? ($recentExpire->userData?->number ?? '-') }}">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $recentExpires])
            </div>
        </div>
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
                style="border-top: 1px solid #e9ecef; padding: 15px 25px; background: #f8f9fa; border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for note -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="followupForm">
            @csrf
            <input type="hidden" name="id" id="followup_order_id">
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

<!-- Transaction Modal -->
<div class="modal fade" id="add_transaction_model" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 90vh;">
            <!-- Modern Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 20px 25px; border-radius: 12px 12px 0 0;">
                <div style="display: flex; align-items: center;">
                    <div style="background: rgba(255,255,255,0.2); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fa fa-money-bill-transfer" style="font-size: 20px; color: #fff;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: #ffffff; font-weight: 700; font-size: 20px; margin: 0;">Add Transaction</h5>
                        <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 12px;">Manually add subscription transaction</p>
                    </div>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true" style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 24px; font-weight: 300;">×</button>
            </div>
            
            <div class="modal-body" style="padding: 25px; background: #f8f9fa; max-height: calc(90vh - 180px); overflow-y: auto;">
                <form method="post" id="add_transaction_form" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Customer Info Section -->
                    <div style="background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6 style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-user"></i> Customer Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="txn_email" name="email" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="customer@example.com" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Contact No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="txn_contact" name="contact" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="9876543210" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transaction Details Section -->
                    <div style="background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6 style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-receipt"></i> Transaction Details
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Method <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="method" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="Razorpay, PhonePe, etc." />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Transaction ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="transaction_id" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="TXN123456789" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Currency</label>
                                    <select class="form-control" name="currency_code" 
                                            style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="INR">INR (₹)</option>
                                        <option value="USD">USD ($)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Price Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price_amount" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="0.00" step="0.01" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="paid_amount" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="0.00" step="0.01" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Plan & Additional Info Section -->
                    <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <h6 style="color: #667eea; font-weight: 700; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-box"></i> Plan & Additional Info
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Plan <span class="text-danger">*</span></label>
                                    <select class="form-control" name="plan_id" 
                                            style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        @foreach ($datas['packageArray'] as $package)
                                            <option value="{{ $package->id }}">{{ $package->package_name }}</option>
                                        @endforeach
                                    </select>
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
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Usage Purpose <span class="text-danger">*</span></label>
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
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">From Wallet</label>
                                    <select class="form-control" name="fromWallet" 
                                            style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">From Where</label>
                                    <select class="form-control" name="fromWhere" 
                                            style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;">
                                        <option value="Mobile">Mobile</option>
                                        <option value="Web">Web</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 6px;">Caricatures</label>
                                    <input type="number" class="form-control" name="caricatures" value="0" required 
                                           style="border-radius: 6px; border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 13px;" 
                                           placeholder="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e9ecef; padding: 15px 25px; border-radius: 0 0 12px 12px;">
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
    table tbody tr td:nth-child(10) {
        white-space: nowrap;
    }
    
    /* Ensure Switchery doesn't break layout */
    .switchery {
        margin-right: 0 !important;
    }
    
    /* Add Transaction Modal Styling */
    #add_transaction_model .form-control:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.15rem rgba(102,126,234,0.15) !important;
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
        box-shadow: 0 6px 16px rgba(102,126,234,0.5) !important;
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
        
        #add_transaction_model .modal-header > div {
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
        #add_transaction_model .modal-body > div {
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
    function removeFilter(paramName) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete(paramName);
        window.location.href = currentUrl.toString();
    }

    $(document).ready(function() {
        let currentOrderId = null;

        // Clear search functionality
        $('#clearSearch').on('click', function() {
            $('#globalSearch').val('');

            // Remove search parameter and submit
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            currentUrl.searchParams.delete('page'); // Go to first page after clear

            window.location.href = currentUrl.toString();
        });

        // Enter key press to submit form
        $('#globalSearch').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();

                // Remove page parameter to start from first page when searching
                const form = $('#searchForm');
                const pageInput = form.find('input[name="page"]');
                if (pageInput.length) {
                    pageInput.remove();
                }

                form.submit();
            }
        });

        // Checkbox change - Use event delegation
        $(document).off("change", ".followup-switch").on("change", ".followup-switch", function() {
            let id = $(this).data("id");
            let isChecked = $(this).is(":checked");
            let $checkbox = $(this);

            if (isChecked) {
                currentOrderId = id;
                $("#followup_order_id").val(id);
                $("#followup_note").val('');
                $("#followup_label").val('');

                // Store checkbox reference for modal cancel
                $("#followupModal").data('checkbox', $checkbox);

                $("#followupModal").modal("show");
            } else {
                if (confirm("Are you sure you want to uncheck this follow-up?")) {
                    $.ajax({
                        url: "{{ route('recent_expire.followupUpdate') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            followup_call: 0
                        },
                        success: function(res) {
                            console.log('✅ Followup unchecked successfully');
                            // Don't reload - let WebSocket update the UI
                            // Remove info icon if exists
                            $checkbox.closest('td').find('.info-icon').remove();
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
                console.log('⚠️ Modal closed without saving, unchecking checkbox');
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

        // Info icon click → open modal with existing note
        $(".info-icon").on("dblclick", function() {
            let id = $(this).data("id");
            let note = $(this).data("note");
            let label = $(this).data("label");


            currentOrderId = id;
            $("#followup_order_id").val(id);
            $("#followup_note").val(note || '');
            $("#followup_label").val(label || '');
            $("#followupModal").modal("show");
        });

        // Info Icon Click - Show modal with followup details (single click for info modal)
        $(document).off("click", ".info-icon").on("click", ".info-icon", function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            let id = $(this).data("id");
            let note = $(this).data("note");
            let label = $(this).data("label");
            let labelDisplay = $(this).data("label-display");

            // Populate modal
            $("#followupInfoLabel").text(labelDisplay || '-');
            $("#followupInfoNote").text(note || '-');

            // Show modal
            $("#followupInfoModal").modal("show");
        });

        // Modal submit
        $("#followupForm").on("submit", function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('recent_expire.new_followupUpdate') }}",
                type: "POST",
                data: formData,
                success: function(res) {
                    console.log('✅ Followup saved successfully');
                    
                    // Mark as submitted so modal close handler doesn't uncheck
                    $("#followupModal").data('submitted', true);
                    
                    $("#followupModal").modal("hide");
                    // Don't reload - let WebSocket update the UI
                },
                error: function(err) {
                    console.error('❌ Error saving followup:', err);
                    alert("Error: " + (err.responseJSON?.message || err.responseText));
                }
            });
        });

        // Toggle filter functionality
        $('.meta-filter-btn').on('click', function() {
            const filterName = $(this).data('filter');
            const filterValue = $(this).data('value');
            const currentUrl = new URL(window.location.href);

            const currentValue = currentUrl.searchParams.get(filterName);

            if (currentValue === filterValue) {
                currentUrl.searchParams.delete(filterName);
            } else {
                currentUrl.searchParams.set(filterName, filterValue);
            }

            // Remove page parameter when changing filters
            currentUrl.searchParams.delete('page');

            if (filterName === 'filter_type' && filterValue === 'all') {
                const paramsToRemove = [
                    'filter_type', 'status_filter', 'amount_sort',
                    'whatsapp_filter', 'email_filter', 'followup_filter', 'followup_label_filter'
                ];

                paramsToRemove.forEach(param => {
                    currentUrl.searchParams.delete(param);
                });
            }

            window.location.href = currentUrl.toString();
        });

        // Transaction Modal
        $(".open-transaction-modal").on("click", function() {
            let email = $(this).data('email');
            let contact = $(this).data('contact');

            console.log('Opening transaction modal with:', { email, contact });

            $('#add_transaction_model').modal('show');
            
            // Use setTimeout to ensure modal is fully rendered before setting values
            setTimeout(function() {
                $('#txn_email').val(email);
                $('#txn_contact').val(contact);
                console.log('Set values:', { 
                    email: $('#txn_email').val(), 
                    contact: $('#txn_contact').val() 
                });
            }, 100);
        });

        // AJAX submit for transaction
        $('#add_transaction_form').on('submit', function(event) {
            event.preventDefault();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(this);

            $.ajax({
                url: "{{ route('manage_subscription.submit') }}",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    console.log("Submitting transaction...");
                },
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        alert(data.success || 'Subscription added successfully!');
                        location.reload();
                    } else {
                        alert(data.success || data.error || 'Something went wrong!');
                    }
                },
                error: function(error) {
                    console.error(error);
                    alert('AJAX Error: ' + error.responseText);
                }
            });
        });
    });

    // ============================================
    // 🔥 WEBSOCKET REAL-TIME UPDATES (PUSHER)
    // ============================================
    let useWebSocket = false;
    const currentUserId = {{ auth()->user()->id ?? 0 }};

    // Initialize WebSocket connection
    if (typeof Pusher !== 'undefined') {
        try {
            console.log('🚀 Initializing WebSocket connection...');

            const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                wsHost: window.location.hostname,
                wsPort: 443,
                wssPort: 443,
                forceTLS: true,
                encrypted: true,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
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
                console.log('✅ WebSocket CONNECTED! Real-time updates enabled');
                useWebSocket = true;
            });

            pusher.connection.bind('disconnected', function () {
                console.log('❌ WebSocket disconnected');
                useWebSocket = false;
            });

            pusher.connection.bind('failed', function () {
                console.log('❌ WebSocket connection failed');
                useWebSocket = false;
            });

            // Subscribe to the orders channel
            const channel = pusher.subscribe('orders');

            console.log('📡 Subscribed to orders channel');

            // Listen for transaction followup change events
            channel.bind('transaction-followup-changed', function (data) {
                console.log('📞 TRANSACTION FOLLOWUP CHANGED:', data);

                if (data && data.transaction_id) {
                    console.log(`⚡ WebSocket: Updating followup for transaction #${data.transaction_id}`);
                    updateTransactionFollowUp(data);

                    if (data.emp_id && data.emp_id != currentUserId) {
                        console.log('📞 Followup updated by another user for transaction #' + data.transaction_id);
                    }
                }
            });

        } catch (error) {
            console.error('❌ WebSocket initialization failed:', error);
            useWebSocket = false;
        }
    } else {
        console.warn('⚠️ Pusher library not loaded. WebSocket disabled.');
    }

    function updateTransactionFollowUp(data) {
        console.log('🔍 updateTransactionFollowUp called with data:', data);

        // Find the row with this transaction ID
        $('#tableBody tr.searchable-row').each(function () {
            let rowTransactionId = parseInt($(this).find('td:first').text());
            if (rowTransactionId === data.transaction_id) {
                const $row = $(this);

                console.log('✅ Found row for transaction #' + data.transaction_id);

                // Update the followup checkbox (10th column - index 9)
                const $followupCell = $row.find('td').eq(9);

                // Find the checkbox
                let $checkbox = $followupCell.find('.followup-switch');
                if ($checkbox.length === 0) {
                    $checkbox = $followupCell.find('input[type="checkbox"]');
                }
                if ($checkbox.length === 0) {
                    $checkbox = $followupCell.find('.switch-btn');
                }

                console.log('📋 Checkbox found:', $checkbox.length > 0);

                if ($checkbox.length > 0) {
                    // Update checkbox state
                    const isChecked = data.followup_call == 1;
                    
                    // Reinitialize Switchery if it exists
                    if (typeof Switchery !== 'undefined') {
                        try {
                            // Destroy existing Switchery instance
                            const switcheryInstance = $checkbox.data('switchery');
                            if (switcheryInstance) {
                                switcheryInstance.destroy();
                            }

                            // Update checkbox state
                            $checkbox.prop('checked', isChecked);

                            // Create new Switchery instance
                            new Switchery($checkbox[0], { size: 'small' });
                        } catch (e) {
                            console.warn('Switchery update failed:', e);
                            // Fallback: just update checkbox without Switchery
                            $checkbox.prop('checked', isChecked);
                        }
                    } else {
                        // No Switchery, just update checkbox
                        $checkbox.prop('checked', isChecked);
                    }

                    // Update or add/remove info icon
                    let $infoIcon = $followupCell.find('.info-icon');
                    
                    if (isChecked && (data.followup_note || data.followup_label)) {
                        const labelDisplay = data.followup_label_display || data.followup_label || '';
                        const noteEscaped = (data.followup_note || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        const labelEscaped = (data.followup_label || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        const labelDisplayEscaped = labelDisplay.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        
                        if ($infoIcon.length === 0) {
                            // Add info icon after Switchery wrapper or checkbox
                            const iconHtml = `<i class="fa-solid fa-circle-info info-icon" data-id="${data.transaction_id}" data-note="${noteEscaped}" data-label="${labelEscaped}" data-label-display="${labelDisplayEscaped}"></i>`;
                            
                            // Find Switchery wrapper if it exists
                            const $switcheryWrapper = $followupCell.find('.switchery');
                            if ($switcheryWrapper.length > 0) {
                                $switcheryWrapper.after(iconHtml);
                            } else {
                                $checkbox.after(iconHtml);
                            }
                        } else {
                            // Update existing info icon data attributes
                            $infoIcon.attr('data-note', data.followup_note || '');
                            $infoIcon.attr('data-label', data.followup_label || '');
                            $infoIcon.attr('data-label-display', labelDisplay);
                        }
                    } else if (!isChecked) {
                        // Remove info icon if unchecked
                        $infoIcon.remove();
                    }

                    console.log('✅ Transaction followup updated successfully');
                } else {
                    console.warn('⚠️ Checkbox not found for transaction #' + data.transaction_id);
                }

                // Update "Follow By" column (11th column - index 10)
                if (data.emp_name) {
                    const $followByCell = $row.find('td').eq(10);
                    $followByCell.text(data.emp_name);
                }
            }
        });
    }
</script>

</body>

</html>
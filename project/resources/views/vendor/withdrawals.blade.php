@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<style>
    .vw-page {
        --vw-slate: #1e293b;
        --vw-muted: #64748b;
        --vw-border: #e2e8f0;
        --vw-surface: #f8fafc;
        --vw-indigo: #4f46e5;
    }
    .vw-page-top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--vw-border);
    }
    .vw-page-top h1 {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--vw-slate);
        margin: 0 0 0.25rem 0;
        letter-spacing: -0.02em;
    }
    .vw-page-top p { margin: 0; font-size: 0.875rem; color: var(--vw-muted); max-width: 40rem; line-height: 1.45; }
    .vw-page-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);
    }
    .vw-stat-row { margin-bottom: 1.25rem; }
    .vw-stat-card {
        border-radius: 10px;
        border: 1px solid var(--vw-border);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        height: 100%;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .vw-stat-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }
    .vw-stat-card .vw-stat-inner {
        padding: 0.95rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
    }
    .vw-stat-card .vw-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--vw-slate);
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
    }
    .vw-stat-card .vw-stat-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--vw-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 0.15rem;
    }
    .vw-stat-card .vw-stat-ico {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1rem; flex-shrink: 0;
    }
    .vw-stat-card.vw-s-total .vw-stat-ico { background: linear-gradient(135deg, #475569, #64748b); }
    .vw-stat-card.vw-s-pending .vw-stat-ico { background: linear-gradient(135deg, #c2410c, #ea580c); }
    .vw-stat-card.vw-s-processing .vw-stat-ico { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
    .vw-stat-card.vw-s-done .vw-stat-ico { background: linear-gradient(135deg, #047857, #10b981); }
    .vw-stat-card.vw-s-reject .vw-stat-ico { background: linear-gradient(135deg, #b91c1c, #ef4444); }
    .vw-stat-card.vw-s-amount .vw-stat-ico { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }
    .vw-stat-card.vw-s-amount .vw-stat-value { color: #047857; }
    .vw-filter-card {
        background: var(--vw-surface);
        border: 1px solid var(--vw-border);
        border-radius: 10px;
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
    }
    .vw-filter-card .form-label { font-weight: 600; color: var(--vw-slate); font-size: 0.8rem; }
    .vw-table-card {
        border: 1px solid var(--vw-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .vw-table-card .vw-table-head {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--vw-border);
        background: var(--vw-surface);
    }
    .vw-table-card .vw-table-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--vw-slate);
    }
    .vw-table-card .vw-table-head p { margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: var(--vw-muted); }
    .vw-table-wrap { padding: 0 0 1rem; }
    .vw-table-wrap .table { margin-bottom: 0; font-size: 0.875rem; }
    .vw-table-wrap .table thead th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--vw-muted);
        font-weight: 700;
        white-space: nowrap;
        background: var(--vw-surface);
        border-bottom: 1px solid var(--vw-border);
    }
    .vw-table-wrap .table tbody td { vertical-align: middle; }
    /* Actions: always one horizontal row (override theme .btn width:100%) */
    .vw-th-actions,
    .vw-td-actions {
        min-width: 17.5rem;
        width: 1%;
        white-space: nowrap;
        vertical-align: middle !important;
    }
    .vw-actions {
        display: inline-flex !important;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.35rem;
    }
    .vw-actions .btn {
        white-space: nowrap;
        flex: 0 0 auto;
        width: auto !important;
        max-width: none;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    .vw-user-cell { max-width: 15rem; line-height: 1.35; }
    .vw-user-cell .vw-user-meta { font-size: 0.8rem; color: var(--vw-muted); }
    .vw-user-cell code { font-size: 0.75rem; }
    .vw-empty { padding: 3rem 1.5rem; text-align: center; color: var(--vw-muted); }
    .vw-empty i { font-size: 2.5rem; opacity: 0.35; display: block; margin-bottom: 0.75rem; }
    .vw-modal-h { background: var(--vw-surface); border-bottom: 1px solid var(--vw-border); }
    @media (max-width: 767px) {
        .vw-th-actions, .vw-td-actions { min-width: 0; white-space: normal; }
        .vw-actions {
            flex-wrap: wrap;
            justify-content: flex-start;
        }
    }
    .vw-page .gap-3 { gap: 1rem; }
</style>
<div class="main-container vw-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pb-20">
                <div class="vw-page-top">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="vw-page-icon" aria-hidden="true"><i class="fa fa-money-bill-wave"></i></div>
                        <div>
                            <h1>Withdrawal requests</h1>
                            <p>Review vendor and freelancer payouts. Filter by status, search by UID or payout reference, then open a row to accept, reject, or mark complete.</p>
                        </div>
                    </div>
                </div>

                <div class="row vw-stat-row">
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-total height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">{{ number_format($totalWithdrawals) }}</div>
                                    <div class="vw-stat-label">Total</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-list-ul"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-pending height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">{{ number_format($pendingWithdrawals) }}</div>
                                    <div class="vw-stat-label">Pending</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-clock"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-processing height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">{{ number_format($processingWithdrawals) }}</div>
                                    <div class="vw-stat-label">Processing</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-spinner"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-done height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">{{ number_format($completedWithdrawals) }}</div>
                                    <div class="vw-stat-label">Completed</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-reject height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">{{ number_format($rejectedWithdrawals) }}</div>
                                    <div class="vw-stat-label">Rejected</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-times-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-20">
                        <div class="card-box vw-stat-card vw-s-amount height-100-p">
                            <div class="vw-stat-inner">
                                <div>
                                    <div class="vw-stat-value">₹{{ number_format($totalPendingAmount, 2) }}</div>
                                    <div class="vw-stat-label">Pending amount</div>
                                </div>
                                <div class="vw-stat-ico"><i class="fa fa-rupee-sign"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vw-filter-card">
                    <form method="GET" action="{{ route('vendor.withdrawals') }}" class="row g-2 g-md-3 align-items-end">
                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small mb-1" for="vw-q">Search</label>
                            <input type="text" id="vw-q" name="q" class="form-control"
                                placeholder="UID, payout ID, UTR…" value="{{ $searchQuery }}" autocomplete="off">
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <label class="form-label small mb-1" for="vw-status">Status</label>
                            <select id="vw-status" name="status" class="form-control">
                                <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All statuses</option>
                                <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $statusFilter == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ $statusFilter == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="failed" {{ $statusFilter == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <label class="form-label small mb-1" for="vw-vtype">Vendor type</label>
                            <select id="vw-vtype" name="vendor_type" class="form-control">
                                <option value="all" {{ $vendorTypeFilter == 'all' ? 'selected' : '' }}>All types</option>
                                <option value="affiliate" {{ $vendorTypeFilter == 'affiliate' ? 'selected' : '' }}>Affiliate</option>
                                <option value="freelancer" {{ $vendorTypeFilter == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <label class="form-label small mb-1" for="vw-per">Page size</label>
                            <select id="vw-per" name="per_page" class="form-control">
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / page</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 / page</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 col-12">
                            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search me-1"></i>Search</button>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <a href="{{ route('vendor.withdrawals') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="vw-table-card mb-30">
                    <div class="vw-table-head">
                        <h2>All requests</h2>
                        <p>Pending rows show Accept / Reject. Other statuses are view-only for audit.</p>
                    </div>
                    <div class="vw-table-wrap px-2 px-md-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Bank / UPI</th>
                                        <th>Status</th>
                                        <th>Payout ID</th>
                                        <th>UTR</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($withdrawals as $index => $withdrawal)
                                        <tr>
                                            <td class="text-muted">{{ $withdrawals->firstItem() + $index }}</td>
                                            <td class="vw-user-cell">
                                                @if($withdrawal->userData)
                                                    <div><strong>{{ $withdrawal->userData->name }}</strong></div>
                                                    <div class="vw-user-meta text-truncate" title="{{ $withdrawal->userData->email }}">{{ $withdrawal->userData->email }}</div>
                                                    <div class="small mt-1">
                                                        <code>{{ $withdrawal->user_id }}</code>
                                                        @if(isset($userCoins[$withdrawal->user_id]))
                                                            <span class="text-muted"> ·
                                                                @if($withdrawal->vendor_type === 'affiliate')
                                                                    Bal. ₹{{ number_format($userCoins[$withdrawal->user_id]['affiliate'], 2) }}
                                                                @else
                                                                    Bal. ₹{{ number_format($userCoins[$withdrawal->user_id]['freelancer'], 2) }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <code class="small">{{ $withdrawal->user_id }}</code>
                                                    @if(isset($userCoins[$withdrawal->user_id]))
                                                        <span class="text-muted small"> · Bal. ₹{{ number_format($withdrawal->vendor_type === 'affiliate' ? $userCoins[$withdrawal->user_id]['affiliate'] : $userCoins[$withdrawal->user_id]['freelancer'], 2) }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($withdrawal->vendor_type === 'affiliate')
                                                    <span class="badge badge-primary">Affiliate</span>
                                                @else
                                                    <span class="badge badge-info">Freelancer</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-dark">₹{{ number_format($withdrawal->amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($withdrawal->bankDetails)
                                                    @if($withdrawal->bankDetails->withdraw_type == 0)
                                                        <strong>{{ $withdrawal->bankDetails->bank_name }}</strong><br>
                                                        <small class="text-muted">{{ $withdrawal->bankDetails->bank_holder_name }}</small><br>
                                                        @php $acct = $withdrawal->bankDetails->bank_account_number; @endphp
                                                        @if($acct && strlen((string) $acct) >= 4)
                                                            <code class="small">****{{ substr((string) $acct, -4) }}</code>
                                                        @elseif($acct)
                                                            <code class="small">{{ $acct }}</code>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endif
                                                        @if(empty($withdrawal->bankDetails->razorpay_bank_account_id) && (optional($walletSettings)->payment_type ?? 'manual') === 'manual')
                                                            <br><span class="badge badge-light text-dark border mt-1" style="font-size: 0.65rem;">Manual payout</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-light text-dark border">UPI</span>
                                                        <span class="small">{{ $withdrawal->bankDetails->upi }}</span>
                                                        @if(empty($withdrawal->bankDetails->razorpay_bank_account_id) && (optional($walletSettings)->payment_type ?? 'manual') === 'manual')
                                                            <br><span class="badge badge-light text-dark border mt-1" style="font-size: 0.65rem;">Manual payout</span>
                                                        @endif
                                                    @endif
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($withdrawal->status === 'completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @elseif($withdrawal->status === 'pending')
                                                    <span class="badge badge-warning text-dark">Pending</span>
                                                @elseif($withdrawal->status === 'processing')
                                                    <span class="badge badge-info">Processing</span>
                                                @elseif($withdrawal->status === 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @elseif($withdrawal->status === 'failed')
                                                    <span class="badge badge-danger">Failed</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($withdrawal->status ?? 'N/A') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($withdrawal->razorpay_payout_id)
                                                    <code class="small text-primary" title="{{ $withdrawal->razorpay_payout_id }}">{{ \Illuminate\Support\Str::limit($withdrawal->razorpay_payout_id, 22) }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $withdrawal->utr ?? '—' }}</small></td>
                                            <td><small class="text-muted">{{ $withdrawal->created_at ? $withdrawal->created_at->format('M j, Y H:i') : '—' }}</small></td>
                                            <td class="text-end vw-td-actions">
                                                <div class="vw-actions">
                                                    @if($withdrawal->status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal{{ $withdrawal->id }}">
                                                            <i class="fa fa-check"></i> Accept
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $withdrawal->id }}">
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('vendor.withdrawal.show', $withdrawal->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10">   
                                                <div class="vw-empty">
                                                    <i class="fa fa-inbox"></i>
                                                    <div class="font-weight-600 text-dark mb-1">No withdrawals match</div>
                                                    <div class="small">Try clearing filters or widening your search.</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @foreach($withdrawals as $withdrawal)
                            @if($withdrawal->status === 'pending')
                                <div class="modal fade" id="acceptModal{{ $withdrawal->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('vendor.withdrawal.accept', $withdrawal->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header vw-modal-h">
                                                    <h5 class="modal-title font-weight-bold">Accept withdrawal</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2"><strong>User:</strong> {{ $withdrawal->userData->name ?? $withdrawal->user_id }}</p>
                                                    <p class="mb-2"><strong>Amount:</strong> ₹{{ number_format($withdrawal->amount, 2) }}</p>
                                                    <p class="mb-3"><strong>Type:</strong> {{ ucfirst($withdrawal->vendor_type) }}</p>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-600">Admin notes <span class="text-muted font-weight-normal">(optional)</span></label>
                                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Internal notes…"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Accept &amp; process</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="rejectModal{{ $withdrawal->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('vendor.withdrawal.reject', $withdrawal->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header vw-modal-h">
                                                    <h5 class="modal-title font-weight-bold text-danger">Reject withdrawal</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2"><strong>User:</strong> {{ $withdrawal->userData->name ?? $withdrawal->user_id }}</p>
                                                    <p class="mb-2"><strong>Amount:</strong> ₹{{ number_format($withdrawal->amount, 2) }}</p>
                                                    <p class="mb-3"><strong>Type:</strong> {{ ucfirst($withdrawal->vendor_type) }}</p>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-600">Reason <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Visible to the user…" required></textarea>
                                                        <small class="form-text text-muted">This message may be shown to the vendor.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject &amp; refund</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if($withdrawals->hasPages())
                            <div class="px-2 py-3 border-top">{{ $withdrawals->appends(request()->query())->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')

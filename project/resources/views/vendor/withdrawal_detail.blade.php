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
    .vw-page-top p { margin: 0; font-size: 0.875rem; color: var(--vw-muted); max-width: 36rem; line-height: 1.45; }
    .vw-page-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);
    }
    .vw-back {
        border-radius: 8px;
        font-weight: 600;
    }
    .vw-detail-card {
        border: 1px solid var(--vw-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .vw-detail-card .vw-detail-head {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--vw-border);
        background: var(--vw-surface);
    }
    .vw-detail-card .vw-detail-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--vw-slate);
    }
    .vw-detail-card .vw-detail-head p { margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: var(--vw-muted); }
    .vw-detail-body { padding: 1rem 1.15rem 1.25rem; }
    .vw-hero-amount {
        font-size: 1.75rem;
        font-weight: 800;
        color: #047857;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
    }
    .vw-dl { margin: 0; }
    .vw-dl > div {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 1rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: baseline;
    }
    .vw-dl > div:last-child { border-bottom: 0; padding-bottom: 0; }
    .vw-dl dt {
        flex: 0 0 10.5rem;
        max-width: 100%;
        margin: 0;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--vw-muted);
    }
    .vw-dl dd {
        margin: 0;
        flex: 1 1 12rem;
        min-width: 0;
        font-size: 0.9rem;
        color: var(--vw-slate);
    }
    .vw-side-actions .btn { font-weight: 600; border-radius: 8px; }
    .vw-side-actions .btn + .btn { margin-top: 0.5rem; }
    .vw-alert-soft {
        border-radius: 8px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        color: #0c4a6e;
        font-size: 0.875rem;
        padding: 0.85rem 1rem;
    }
    .vw-modal-h { background: var(--vw-surface); border-bottom: 1px solid var(--vw-border); }
</style>
<div class="main-container vw-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px pb-20">
            <div class="vw-page-top">
                <div class="d-flex gap-3 align-items-start flex-grow-1">
                    <div class="vw-page-icon" aria-hidden="true"><i class="fa fa-file-invoice-dollar"></i></div>
                    <div>
                        <h1>Withdrawal detail</h1>
                        <p>Request <code class="small">{{ $withdrawal->string_id ?? ('#' . $withdrawal->id) }}</code>
                            · Internal ID {{ $withdrawal->id }}</p>
                    </div>
                </div>
                <a href="{{ route('vendor.withdrawals') }}" class="btn btn-outline-secondary vw-back">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
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

            <div class="row">
                <div class="col-lg-8">
                    <div class="vw-detail-card">
                        <div class="vw-detail-head">
                            <h2>Request summary</h2>
                            <p>Amount, status, and payout references for this withdrawal.</p>
                        </div>
                        <div class="vw-detail-body">
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="small font-weight-bold text-uppercase text-muted mb-1" style="letter-spacing: 0.04em;">Amount</div>
                                <div class="vw-hero-amount">₹{{ number_format($withdrawal->amount, 2) }}</div>
                                <div class="mt-2">
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
                                    @if($withdrawal->vendor_type === 'affiliate')
                                        <span class="badge badge-primary ml-1">Affiliate</span>
                                    @else
                                        <span class="badge badge-info ml-1">Freelancer</span>
                                    @endif
                                </div>
                            </div>
                            <dl class="vw-dl">
                                <div>
                                    <dt>User</dt>
                                    <dd>
                                        @if($withdrawal->userData)
                                            <strong>{{ $withdrawal->userData->name }}</strong><br>
                                            <span class="text-muted small">{{ $withdrawal->userData->email }}</span><br>
                                            <code class="small">{{ $withdrawal->user_id }}</code>
                                        @else
                                            <code>{{ $withdrawal->user_id }}</code>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt>Currency</dt>
                                    <dd>{{ $withdrawal->currency ?? 'INR' }}</dd>
                                </div>
                                <div>
                                    <dt>Razorpay payout ID</dt>
                                    <dd>
                                        @if($withdrawal->razorpay_payout_id)
                                            <code class="text-primary small" title="{{ $withdrawal->razorpay_payout_id }}">{{ $withdrawal->razorpay_payout_id }}</code>
                                        @else
                                            <span class="text-muted">Not generated yet</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt>UTR</dt>
                                    <dd>{{ $withdrawal->utr ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Created</dt>
                                    <dd class="text-muted small">{{ $withdrawal->created_at ? $withdrawal->created_at->format('M j, Y \a\t g:i A') : '—' }}</dd>
                                </div>
                                @if($withdrawal->completed_at)
                                    <div>
                                        <dt>Completed</dt>
                                        <dd class="text-muted small">{{ $withdrawal->completed_at->format('M j, Y \a\t g:i A') }}</dd>
                                    </div>
                                @endif
                                @if($withdrawal->failure_reason)
                                    <div>
                                        <dt>Failure / rejection</dt>
                                        <dd><span class="text-danger">{{ $withdrawal->failure_reason }}</span></dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="vw-detail-card">
                        <div class="vw-detail-head">
                            <h2>Bank / UPI</h2>
                            <p>Destination for this payout.</p>
                        </div>
                        <div class="vw-detail-body">
                            @if($withdrawal->bankDetails)
                                @if($withdrawal->bankDetails->withdraw_type == 0)
                                    <dl class="vw-dl">
                                        <div>
                                            <dt>Bank</dt>
                                            <dd><strong>{{ $withdrawal->bankDetails->bank_name }}</strong></dd>
                                        </div>
                                        <div>
                                            <dt>Account holder</dt>
                                            <dd>{{ $withdrawal->bankDetails->bank_holder_name }}</dd>
                                        </div>
                                        <div>
                                            <dt>Account number</dt>
                                            <dd>
                                                @php $acct = $withdrawal->bankDetails->bank_account_number; @endphp
                                                @if($acct && strlen((string) $acct) >= 4)
                                                    <code class="small">****{{ substr((string) $acct, -4) }}</code>
                                                @elseif($acct)
                                                    <code class="small">{{ $acct }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                                <span class="text-muted small d-block mt-1">Full number is shown when marking complete (processing).</span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt>IFSC</dt>
                                            <dd><code>{{ $withdrawal->bankDetails->ifsc_code }}</code></dd>
                                        </div>
                                        <div>
                                            <dt>Verification</dt>
                                            <dd>
                                                @if($withdrawal->bankDetails->status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @elseif($withdrawal->bankDetails->status === 'processing')
                                                    <span class="badge badge-warning text-dark">Processing</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($withdrawal->bankDetails->status ?? 'N/A') }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                @else
                                    <dl class="vw-dl">
                                        <div>
                                            <dt>UPI ID</dt>
                                            <dd><strong>{{ $withdrawal->bankDetails->upi }}</strong></dd>
                                        </div>
                                        <div>
                                            <dt>Verification</dt>
                                            <dd>
                                                @if($withdrawal->bankDetails->status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($withdrawal->bankDetails->status ?? 'N/A') }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                @endif
                            @else
                                <p class="text-muted mb-0 small">No bank details linked to this request.</p>
                            @endif
                        </div>
                    </div>

                    @if($withdrawal->status === 'pending')
                        <div class="vw-detail-card vw-side-actions">
                            <div class="vw-detail-head">
                                <h2>Actions</h2>
                                <p>Approve to deduct balance and queue payout, or reject to refund.</p>
                            </div>
                            <div class="vw-detail-body">
                                <button type="button" class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#acceptModal">
                                    <i class="fa fa-check"></i> Accept &amp; process
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-block" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="fa fa-times"></i> Reject &amp; refund
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($withdrawal->status === 'processing')
                        <div class="vw-detail-card vw-side-actions">
                            <div class="vw-detail-head">
                                <h2>Complete payout</h2>
                                <p>After you send funds manually, record the UTR here.</p>
                            </div>
                            <div class="vw-detail-body">
                                <div class="vw-alert-soft mb-3">
                                    <i class="fa fa-info-circle"></i> Balance is already deducted. Send money to the user, then mark completed with bank UTR.
                                </div>
                                <button type="button" class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#completeModal">
                                    <i class="fa fa-check-circle"></i> Mark as completed
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($withdrawal->status === 'pending')
<div class="modal fade" id="acceptModal" tabindex="-1" role="dialog" aria-hidden="true">
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

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <small class="form-text text-muted">Amount will be refunded to the user balance.</small>
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

@if($withdrawal->status === 'processing')
<div class="modal fade" id="completeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('vendor.withdrawal.complete', $withdrawal->id) }}" method="POST">
                @csrf
                <div class="modal-header vw-modal-h">
                    <h5 class="modal-title font-weight-bold">Mark withdrawal completed</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning border-0" style="border-radius: 8px;">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Confirm transfer first.</strong> Only submit after payment has reached the user’s bank or UPI.
                    </div>
                    <p class="mb-2"><strong>User:</strong> {{ $withdrawal->userData->name ?? $withdrawal->user_id }}</p>
                    <p class="mb-3"><strong>Amount:</strong> ₹{{ number_format($withdrawal->amount, 2) }} · <strong>Type:</strong> {{ ucfirst($withdrawal->vendor_type) }}</p>

                    @if($withdrawal->bankDetails)
                        <div class="border rounded p-3 mb-3 bg-light small">
                            <div class="font-weight-bold text-uppercase text-muted mb-2" style="font-size: 0.7rem; letter-spacing: 0.04em;">Payout destination</div>
                            @if($withdrawal->bankDetails->withdraw_type == 0)
                                <ul class="mb-0 pl-3">
                                    <li>Bank: {{ $withdrawal->bankDetails->bank_name }}</li>
                                    <li>Account: {{ $withdrawal->bankDetails->bank_account_number }}</li>
                                    <li>IFSC: {{ $withdrawal->bankDetails->ifsc_code }}</li>
                                    <li>Holder: {{ $withdrawal->bankDetails->bank_holder_name }}</li>
                                </ul>
                            @else
                                <ul class="mb-0 pl-3">
                                    <li>UPI: {{ $withdrawal->bankDetails->upi }}</li>
                                </ul>
                            @endif
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-600">UTR / reference <span class="text-danger">*</span></label>
                        <input type="text" name="utr" class="form-control" placeholder="Bank UTR or transaction reference" required>
                        <small class="form-text text-muted">From your outward payment to the user.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-600">Completion notes <span class="text-muted font-weight-normal">(optional)</span></label>
                        <textarea name="completion_notes" class="form-control" rows="2" placeholder="Internal note…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> Mark as completed</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@include('layouts.masterscript')

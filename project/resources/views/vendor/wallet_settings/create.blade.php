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
    .vw-form-card {
        border: 1px solid var(--vw-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .vw-form-card .vw-form-head {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--vw-border);
        background: var(--vw-surface);
    }
    .vw-form-card .vw-form-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--vw-slate);
    }
    .vw-form-card .vw-form-head p { margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: var(--vw-muted); }
    .vw-form-body { padding: 1.15rem 1.15rem 1.25rem; }
    .vw-form-body .form-label, .vw-form-body label { font-weight: 600; color: var(--vw-slate); font-size: 0.875rem; }
    .vw-section-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--vw-muted);
        margin: 1rem 0 0.75rem 0;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .vw-section-label:first-child { margin-top: 0; }
    .vw-page .gap-3 { gap: 1rem; }
    .vw-page .gap-2 { gap: 0.5rem; }
</style>
<div class="main-container vw-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px pb-20">
            <div class="vw-page-top">
                <div class="d-flex gap-3 align-items-start flex-grow-1">
                    <div class="vw-page-icon" aria-hidden="true"><i class="fa fa-plus-circle"></i></div>
                    <div>
                        <h1>Create wallet setting</h1>
                        <p>Add a named profile (e.g. <code class="small">default</code>) with limits and commission splits.</p>
                    </div>
                </div>
                <a href="{{ route('vendor.wallet_settings') }}" class="btn btn-outline-secondary font-weight-600" style="border-radius: 8px;">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('vendor.wallet_settings.store') }}" method="POST">
                @csrf
                <div class="vw-form-card">
                    <div class="vw-form-head">
                        <h2>Configuration</h2>
                        <p>Keys must be unique. Most installs use a single <strong>default</strong> row.</p>
                    </div>
                    <div class="vw-form-body">
                        <div class="vw-section-label">Identity</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Setting key <span class="text-danger">*</span></label>
                                    <input type="text" name="setting_key" class="form-control" value="{{ old('setting_key') }}" required placeholder="e.g. default">
                                    <small class="form-text text-muted">Unique slug used by the app (letters, numbers, underscore).</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Display name</label>
                                    <input type="text" name="setting_name" class="form-control" value="{{ old('setting_name') }}" placeholder="Optional label">
                                    <small class="form-text text-muted">Shown only in this admin UI.</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes for your team…">{{ old('description') }}</textarea>
                        </div>

                        <div class="vw-section-label">Withdrawal limits (₹)</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Minimum <span class="text-danger">*</span></label>
                                    <input type="number" name="min_withdrawal_threshold" class="form-control" value="{{ old('min_withdrawal_threshold', 500) }}" step="0.01" min="0" required>
                                    <small class="form-text text-muted">Lowest amount a vendor can request per withdrawal.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Maximum</label>
                                    <input type="number" name="max_withdrawal_limit" class="form-control" value="{{ old('max_withdrawal_limit') }}" step="0.01" min="0" placeholder="Leave empty = no cap">
                                    <small class="form-text text-muted">Optional per-request ceiling.</small>
                                </div>
                            </div>
                        </div>

                        <div class="vw-section-label">Commissions (%)</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Freelancer / designer <span class="text-danger">*</span></label>
                                    <input type="number" name="freelancer_commission_rate" class="form-control" value="{{ old('freelancer_commission_rate', 30) }}" step="0.01" min="0" max="100" required>
                                    <small class="form-text text-muted">Platform share on freelancer earnings.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label>Referral / affiliate <span class="text-danger">*</span></label>
                                    <input type="number" name="referral_commission_rate" class="form-control" value="{{ old('referral_commission_rate', 10) }}" step="0.01" min="0" max="100" required>
                                    <small class="form-text text-muted">Rate applied to referral-driven revenue.</small>
                                </div>
                            </div>
                        </div>

                        <div class="vw-section-label">Payout channel</div>
                        <div class="form-group mb-0">
                            <label>Payment type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-control" required>
                                <option value="manual" {{ old('payment_type', 'manual') === 'manual' ? 'selected' : '' }}>Manual — you mark payouts complete in the panel</option>
                                <option value="razorpay" {{ old('payment_type') === 'razorpay' ? 'selected' : '' }}>Razorpay — automated payout API</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary font-weight-600" style="border-radius: 8px;">
                        <i class="fa fa-save"></i> Create setting
                    </button>
                    <a href="{{ route('vendor.wallet_settings') }}" class="btn btn-outline-secondary font-weight-600" style="border-radius: 8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')

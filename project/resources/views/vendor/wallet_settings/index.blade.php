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
    .vw-page-top p { margin: 0; font-size: 0.875rem; color: var(--vw-muted); max-width: 42rem; line-height: 1.45; }
    .vw-page-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);
    }
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
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .vw-table-card .vw-table-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--vw-slate);
    }
    .vw-table-card .vw-table-head p { margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: var(--vw-muted); }
    .vw-table-wrap { padding: 0 0 0.5rem; }
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
    .vw-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
    .vw-actions form { display: inline; margin: 0; }
    .vw-empty { padding: 2.5rem 1.5rem; text-align: center; color: var(--vw-muted); }
    .vw-empty i { font-size: 2.25rem; opacity: 0.35; display: block; margin-bottom: 0.65rem; }
    .vw-setting-title { font-weight: 600; color: var(--vw-slate); }
    .vw-page .gap-3 { gap: 1rem; }
</style>
<div class="main-container vw-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px pb-20">
            <div class="vw-page-top">
                <div class="d-flex gap-3 align-items-start">
                    <div class="vw-page-icon" aria-hidden="true"><i class="fa fa-wallet"></i></div>
                    <div>
                        <h1>Wallet settings</h1>
                        <p>Control min/max withdrawal, freelancer and referral commission rates, and whether payouts run manually or via Razorpay. Keep one clear <strong>default</strong> profile for production.</p>
                    </div>
                </div>
                <a href="{{ route('vendor.wallet_settings.create') }}" class="btn btn-primary font-weight-600" style="border-radius: 8px;">
                    <i class="fa fa-plus"></i> Add setting
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="vw-table-card mb-30">
                <div class="vw-table-head">
                    <div>
                        <h2>All profiles</h2>
                        <p>Toggle active sets which row apps may read; edit values anytime.</p>
                    </div>
                </div>
                <div class="vw-table-wrap px-2 px-md-3 pb-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Setting</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Freelancer %</th>
                                    <th>Referral %</th>
                                    <th>Payout</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settings as $index => $setting)
                                    <tr>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td style="min-width: 11rem;">
                                            <div class="vw-setting-title">{{ $setting->setting_name ?? $setting->setting_key }}</div>
                                            @if($setting->setting_key)
                                                <small class="text-muted d-block">Key: <code class="small">{{ $setting->setting_key }}</code></small>
                                            @endif
                                            @if($setting->description)
                                                <small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($setting->description, 80) }}</small>
                                            @endif
                                        </td>
                                        <td><strong>₹{{ number_format($setting->min_withdrawal_threshold, 2) }}</strong></td>
                                        <td>
                                            @if($setting->max_withdrawal_limit)
                                                <strong>₹{{ number_format($setting->max_withdrawal_limit, 2) }}</strong>
                                            @else
                                                <span class="text-muted small">No cap</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $setting->freelancer_commission_rate ?? $setting->platform_commission_rate }}%</span>
                                        </td>
                                        <td>
                                            @if($setting->referral_commission_rate !== null && $setting->referral_commission_rate !== '')
                                                <span class="badge badge-primary">{{ $setting->referral_commission_rate }}%</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($setting->payment_type === 'razorpay')
                                                <span class="badge badge-success">Razorpay</span>
                                            @else
                                                <span class="badge badge-secondary">Manual</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($setting->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-light text-dark border">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="vw-actions justify-content-end">
                                                <a href="{{ route('vendor.wallet_settings.edit', $setting->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('vendor.wallet_settings.toggle', $setting->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Flip active / inactive">
                                                        <i class="fa fa-toggle-on"></i> Toggle
                                                    </button>
                                                </form>
                                                @if($setting->setting_key !== 'default')
                                                    <form action="{{ route('vendor.wallet_settings.destroy', $setting->id) }}" method="POST"
                                                        onsubmit="return confirm('Delete this wallet profile? This cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">
                                            <div class="vw-empty">
                                                <i class="fa fa-sliders-h"></i>
                                                <div class="font-weight-600 text-dark mb-1">No wallet settings yet</div>
                                                <div class="small mb-2">Create a default profile to enable withdrawals.</div>
                                                <a href="{{ route('vendor.wallet_settings.create') }}" class="btn btn-primary btn-sm">Create setting</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')

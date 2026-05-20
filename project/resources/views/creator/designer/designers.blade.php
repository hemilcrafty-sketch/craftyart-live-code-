@include('layouts.masterhead')

<style>
    .designer-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .filter-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .designer-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        background: #d4edda;
        color: #155724;
    }

    .stats-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 11px;
        background: #f8f9fa;
        color: #495057;
        margin: 2px;
    }
</style>

<div class="main-container">
    <div class="xs-pd-10-10">
        <div class="min-height-200px">
            <div class="designer-card">
                <div class="filter-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="h4 mb-0">Approved Designers</h4>
                            <small class="text-muted">All onboarded designers with approved applications</small>
                        </div>
                        <div>
                            <span class="text-muted">Total: {{ $paginator->total() }}</span>
                        </div>
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                    style="max-height: calc(110vh - 220px) !important;">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Display Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Employment Type</th>
                                <th>Commission</th>
                                <th>Stats</th>
                                <th>Status</th>
                                <th style="width: 140px;">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paginator as $designer)
                                <tr>
                                    <td><strong>#{{ $designer->id }}</strong></td>
                                    <td>{{ $designer->name ?? ($designer->application->name ?? '—') }}</td>
                                    <td>{{ $designer->email ?? ($designer->application->email ?? '—') }}</td>
                                    <td>{{ $designer->application->phone ?? '—' }}</td>
                                    <td>
                                        <span class="designer-badge">
                                            {{ ucfirst($designer->designer_employment_type ?? 'freelancer') }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($designer->commission_rate ?? 30, 2) }}%</td>
                                    <td>
                                        <span class="stats-badge" title="Total Designs">
                                            📊 {{ $designer->total_designs ?? 0 }}
                                        </span>
                                        <span class="stats-badge" title="Live Designs">
                                            ✅ {{ $designer->live_designs ?? 0 }}
                                        </span>
                                        <span class="stats-badge" title="Total Earnings">
                                            💰 ₹{{ number_format($designer->total_earnings ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($designer->is_active ?? true)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $designer->application->reviewed_at ? \Carbon\Carbon::parse($designer->application->reviewed_at)->format('d M Y') : ($designer->updated_at ? $designer->updated_at->format('d M Y') : '—') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fa fa-users" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3">No approved designers found.</p>
                                        <small>Designers will appear here after their applications are approved.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($paginator->hasPages())
                    <div class="pagination-footer p-2 border-top bg-white">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span class="text-muted">
                                Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of
                                {{ $paginator->total() }} entries
                            </span>
                            <div>
                                {{ $paginator->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
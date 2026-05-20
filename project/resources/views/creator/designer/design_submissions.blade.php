@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@include('layouts.masterhead')

<style>
    .designer-system-container .preview-thumb {
        width: 100px;
        height: 80px;
        object-fit: contain;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .designer-system-container .designer-info-cell {
        line-height: 1.4;
        min-width: 120px;
    }

    .designer-system-container .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .designer-system-container .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .designer-system-container .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .designer-system-container .status-live {
        background: #cce5ff;
        color: #004085;
    }

    .designer-system-container .status-seo {
        background: #d1ecf1;
        color: #0c5460;
    }

    .designer-system-container .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .designer-system-container .btn-action {
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        margin: 2px;
        display: inline-block;
        white-space: nowrap;
        text-decoration: none;
    }

    .designer-system-container .btn-info-action {
        background: #17a2b8;
        color: white;
    }

    .designer-system-container .btn-edit {
        background: #ffc107;
        color: #212529;
    }

    .designer-system-container .btn-edit:hover {
        background: #e0a800;
        color: #212529;
    }

    .designer-system-container .btn-success-action {
        background: #28a745;
        color: white;
    }

    .designer-system-container .btn-danger-action {
        background: #dc3545;
        color: white;
    }

    .designer-system-container .actions-cell {
        white-space: nowrap;
        min-width: 180px;
    }

    .designer-system-container .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    @keyframes dsHighlightFade {
        0% { background-color: rgba(40, 167, 69, 0.28); }
        100% { background-color: transparent; }
    }

    .designer-system-container tr.new-design-submission-highlight td {
        animation: dsHighlightFade 3s ease-in-out;
    }
</style>

<div class="main-container">
    <div class="xs-pd-10-10 designer-system-container">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 95vh; overflow: hidden;">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-2" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-2" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Filter & Search (show_item style) --}}
                    <div class="filter-header">
                        <div class="row gx-2 gy-1 align-items-center justify-content-between flex-wrap">
                            <div class="col-md-auto col-12">
                                <h5 class="mb-0">Design Submissions (Freelancer)</h5>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('designer_system.design_submissions') }}"
                                    id="form-status">
                                    <input type="hidden" name="freelancer" value="{{ $freelancerOnly ? '1' : '0' }}">
                                    <input type="hidden" name="query" value="{{ $searchQuery ?? '' }}">
                                    <select name="status" class="form-control item-form-input w-100"
                                        onchange="this.form.submit()">
                                        <option value="all" {{ ($status ?? '') == 'all' ? 'selected' : '' }}>All</option>
                                        <option value="pending_designer_head" {{ ($status ?? '') == 'pending_designer_head' ? 'selected' : '' }}>Pending</option>
                                        <option value="pending_seo" {{ ($status ?? '') == 'pending_seo' ? 'selected' : '' }}>Sent to SEO</option>
                                        <option value="rejected_by_designer_head" {{ ($status ?? '') == 'rejected_by_designer_head' ? 'selected' : '' }}>Rejected (DH)</option>
                                        <option value="rejected_by_seo" {{ ($status ?? '') == 'rejected_by_seo' ? 'selected' : '' }}>Rejected (SEO)</option>
                                        <option value="live" {{ ($status ?? '') == 'live' ? 'selected' : '' }}>Live
                                        </option>
                                    </select>
                                </form>
                            </div>

                            {{-- Search --}}
                            <div class="col-md-auto col-12 mt-1">
                                <form action="{{ route('designer_system.design_submissions') }}" method="GET"
                                    class="d-flex">
                                    <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
                                    <input type="hidden" name="freelancer" value="{{ $freelancerOnly ? '1' : '0' }}">
                                    <input type="text" class="form-control item-form-input" name="query"
                                        placeholder="Title, email, string_id, template_id, uid…"
                                        value="{{ $searchQuery ?? '' }}">
                                    <button type="submit" class="btn btn-primary item-form-input">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Table (show_item style) --}}
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important;">
                        <table class="table table-striped table-bordered mb-0 ds-design-submissions-table"
                            style="min-width: 1100px;">
                            <thead>
                                <tr>
                                    <th style="width: 108px;">Id / Draft</th>
                                    <th style="width: 168px;">Designer</th>
                                    <th style="width: 160px;">Category</th>
                                    <th style="width: 96px;">W×H / R</th>
                                    <th style="min-width: 160px;">Title / SEO name</th>
                                    <th style="width: 120px;">Thumb</th>
                                    <th style="width: 108px;">Status</th>
                                    <th style="width: 84px;">crafty_db</th> 
                                    <th style="width: 92px;">Submitted</th>
                                    <th class="datatable-nosort" style="width: 200px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($designs as $design)
                                    <tr data-design-id="{{ $design->id }}">
                                        <td class="table-plus">
                                            <strong>#{{ $design->id }}</strong>
                                            @if($design->string_id)
                                                <br><small class="text-muted text-break"
                                                    title="API string_id">{{ Str::limit($design->string_id, 22) }}</small>
                                            @endif
                                            @if($design->template_id)
                                                <br><small class="text-muted" title="template_id">tpl
                                                    {{ Str::limit($design->template_id, 14) }}</small>
                                            @endif
                                        </td>
                                        <td class="designer-info-cell">
                                            <strong>{{ optional($design->designer)->name ?? '—' }}</strong>
                                            @if(optional($design->designer)->email)
                                                <br><small class="text-muted">{{ $design->designer->email }}</small>
                                            @endif
                                            @if($design->designer_id && !optional($design->designer)->id)
                                                <br><small class="text-warning" title="No matching user_data row">designer_id
                                                    {{ $design->designer_id }}</small>
                                            @endif
                                            @if($design->app_user_uid)
                                                <br><small class="text-muted font-monospace" title="Stored from API submit">uid
                                                    {{ Str::limit($design->app_user_uid, 14) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $seo = $design->seoDetails;
                                                $catId = optional($seo)->primary_category_id ?? $design->category_id ?? null;
                                                $catName = $catId && isset($newCategories[$catId]) ? $newCategories[$catId] : ucfirst($design->category ?? '—');
                                            @endphp
                                            <span title="new_categories / slug">{{ Str::limit($catName, 24) }}</span>
                                            @if($design->category_id && !isset($newCategories[$design->category_id]))
                                                <br><small class="text-warning" title="category_id not in new_categories">id
                                                    {{ $design->category_id }}</small>
                                            @endif
                                            @if($design->video)
                                                <br><span class="badge bg-info" style="font-size:10px;"
                                                    title="{{ Str::limit($design->video, 80) }}"><i
                                                        class="fa fa-video-camera"></i> video</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $cw = optional($seo)->width ?? $design->width;
                                                $ch = optional($seo)->height ?? $design->height;
                                                $r = $design->ratio;
                                            @endphp
                                            @if($cw || $ch)
                                                <span class="text-nowrap">{{ $cw ?? '—' }}×{{ $ch ?? '—' }}</span>
                                            @elseif($r !== null && $r !== '')
                                                <span class="text-muted" title="ratio">r
                                                    {{ is_numeric($r) ? round((float) $r, 3) : $r }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ optional($seo)->post_name ?? $design->title }}</span>
                                            @if($seo)
                                                <span class="badge bg-info ms-1" title="design_seo_details row">SEO</span>
                                            @endif
                                            @if($design->tags && is_array($design->tags) && count($design->tags) > 0)
                                                <div class="mt-1">
                                                    @foreach(array_slice($design->tags, 0, 4) as $tag)
                                                        <span class="badge bg-light text-dark border me-1 mb-1"
                                                            style="font-weight:500;font-size:10px;">{{ Str::limit($tag, 16) }}</span>
                                                    @endforeach
                                                    @if(count($design->tags) > 4)
                                                        <span class="text-muted small">+{{ count($design->tags) - 4 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($design->msg)
                                                <div class="small text-muted mt-1" title="{{ e($design->msg) }}"><i
                                                        class="fa fa-comment"></i> {{ Str::limit($design->msg, 42) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $thumbUrl = $design->previewImageUrl();
                                            @endphp
                                            @if($thumbUrl)
                                                <img src="{{ $thumbUrl }}" class="preview-thumb" alt="Design preview" loading="lazy"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                <span class="text-muted small" style="display:none;">Error loading</span>
                                            @else
                                                <span class="text-muted small">No image</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($design->status == 'pending_designer_head')
                                                <span class="status-badge status-pending">Pending</span>
                                            @elseif($design->status == 'approved_by_designer_head')
                                                <span class="status-badge status-approved">OK (DH)</span>
                                            @elseif($design->status == 'pending_seo')
                                                <span class="status-badge status-approved">SEO queue</span>
                                            @elseif($design->status == 'approved_by_seo')
                                                <span class="status-badge status-seo">OK (SEO)</span>
                                            @elseif($design->status == 'rejected_by_designer_head')
                                                <span class="status-badge status-rejected">Rej. DH</span>
                                            @elseif($design->status == 'rejected_by_seo')
                                                <span class="status-badge status-rejected">Rej. SEO</span>
                                            @elseif($design->status == 'live')
                                                <span class="status-badge status-live">Live</span>
                                            @else
                                                <span class="status-badge"
                                                    style="background:#e3f2fd;color:#1565c0;">{{ Str::limit($design->status, 14) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($design->crafty_design_id))
                                                <span class="status-badge status-live"
                                                    title="crafty_db.designs.id">#{{ $design->crafty_design_id }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $design->created_at->format('d M Y') }}</td>
                                        <td class="actions-cell">
                                            @if($design->status === 'pending_seo' || $design->status === 'rejected_by_seo')
                                                <a href="{{ route('designer_system.design.edit', $design->id) }}"
                                                    class="btn-action btn-edit" title="Edit draft / technical fields"><i
                                                        class="fa fa-edit"></i> Edit</a>
                                                <a href="{{ route('designer_system.design.seo', $design->id) }}"
                                                    class="btn-action btn-info-action"
                                                    title="SEO manager: review, approve (publish to designs), or reject"><i
                                                        class="fa fa-search"></i> Edit SEO</a>
                                            @elseif($design->status === 'live')
                                                <a href="{{ route('designer_system.design.seo', $design->id) }}"
                                                    class="btn-action btn-info-action" title="Edit SEO / view published row"><i
                                                        class="fa fa-search"></i> Edit SEO</a>
                                            @else
                                                <a href="{{ route('designer_system.design.edit', $design->id) }}"
                                                    class="btn-action btn-edit"
                                                    title="Design manager: review, approve, or reject; then Edit SEO appears"><i
                                                        class="fa fa-edit"></i> Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">
                                            <div class="empty-state"><i class="fa fa-paint-brush"></i>
                                                <p>No design submissions found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination (show_item style) --}}
                    <div class="pagination-footer p-2 border-top bg-white">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span class="text-muted">{{ $countStr ?? 'Showing 0-0 of 0 entries' }}</span>
                            @if($designs->hasPages())
                                <div>{{ $designs->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var listStatus = @json($status ?? 'all');
        var freelancerOnly = @json($freelancerOnly ?? true);
        var searchQuery = @json($searchQuery ?? '');
        var listCurrentPage = @json((int) ($designs->currentPage() ?? 1));
        var key = @json(config('broadcasting.connections.pusher.key', env('PUSHER_APP_KEY')));
        if (!key) {
            return;
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) {
                return '';
            }
            var d = document.createElement('div');
            d.textContent = String(text);
            return d.innerHTML;
        }

        function rowMatchesListFilters(data) {
            if (freelancerOnly && !data.is_freelancer) {
                return false;
            }
            if (listStatus !== 'all' && listStatus !== data.status) {
                return false;
            }
            return true;
        }

        function statusBadgeHtml(status) {
            var map = {
                pending_designer_head: { c: 'status-pending', t: 'Pending' },
                approved_by_designer_head: { c: 'status-approved', t: 'OK (DH)' },
                pending_seo: { c: 'status-approved', t: 'SEO queue' },
                approved_by_seo: { c: 'status-seo', t: 'OK (SEO)' },
                rejected_by_designer_head: { c: 'status-rejected', t: 'Rej. DH' },
                rejected_by_seo: { c: 'status-rejected', t: 'Rej. SEO' },
                live: { c: 'status-live', t: 'Live' }
            };
            var m = map[status];
            if (!m) {
                return '<span class="status-badge" style="background:#e3f2fd;color:#1565c0;">' +
                    escapeHtml(String(status).substring(0, 14)) + '</span>';
            }
            return '<span class="status-badge ' + m.c + '">' + m.t + '</span>';
        }

        function actionsHtml(status, editUrl, seoUrl) {
            if (status === 'pending_seo' || status === 'rejected_by_seo') {
                return '<a href="' + editUrl + '" class="btn-action btn-edit" title="Edit draft / technical fields">' +
                    '<i class="fa fa-edit"></i> Edit</a> <a href="' + seoUrl + '" class="btn-action btn-info-action" ' +
                    'title="SEO manager"><i class="fa fa-search"></i> Edit SEO</a>';
            }
            if (status === 'live') {
                return '<a href="' + seoUrl + '" class="btn-action btn-info-action" title="Edit SEO">' +
                    '<i class="fa fa-search"></i> Edit SEO</a>';
            }
            return '<a href="' + editUrl + '" class="btn-action btn-edit" title="Design manager">' +
                '<i class="fa fa-edit"></i> Edit</a>';
        }

        function buildDesignSubmissionTdsHtml(data) {
            var draft = '<strong>#' + data.id + '</strong>';
            if (data.string_id) {
                draft += '<br><small class="text-muted text-break" title="API string_id">' +
                    escapeHtml(data.string_id.substring(0, 22)) + '</small>';
            }
            if (data.template_id) {
                draft += '<br><small class="text-muted" title="template_id">tpl ' +
                    escapeHtml(data.template_id.substring(0, 14)) + '</small>';
            }

            var designer = '<strong>' + escapeHtml(data.designer_name || '—') + '</strong>';
            if (data.designer_email) {
                designer += '<br><small class="text-muted">' + escapeHtml(data.designer_email) + '</small>';
            }
            if (data.designer_row_missing) {
                designer += '<br><small class="text-warning" title="No matching user_data row">designer_id ' +
                    escapeHtml(String(data.designer_id)) + '</small>';
            }
            if (data.app_uid) {
                designer += '<br><small class="text-muted font-monospace" title="Stored from API submit">uid ' +
                    escapeHtml(String(data.app_uid).substring(0, 14)) + '</small>';
            }

            var cat = '<span title="new_categories / slug">' + escapeHtml(data.category_name || '—') + '</span>';
            if (data.category_id_warning && data.category_id) {
                cat += '<br><small class="text-warning" title="category_id not in new_categories">id ' +
                    escapeHtml(String(data.category_id)) + '</small>';
            }
            if (data.video) {
                cat += '<br><span class="badge bg-info" style="font-size:10px;" title="' +
                    escapeHtml(data.video.substring(0, 80)) + '"><i class="fa fa-video-camera"></i> video</span>';
            }

            var wh = '<span class="text-nowrap">' + escapeHtml(data.wh_display || '—') + '</span>';

            var title = '<span class="fw-medium">' + escapeHtml(data.title_display || '') + '</span>';
            if (data.has_seo) {
                title += ' <span class="badge bg-info ms-1" title="design_seo_details row">SEO</span>';
            }

            var thumb;
            if (data.thumb_url) {
                thumb = '<img src="' + escapeHtml(data.thumb_url) + '" class="preview-thumb" alt="" loading="lazy" ' +
                    'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline\';">' +
                    '<span class="text-muted small" style="display:none;">Error loading</span>';
            } else {
                thumb = '<span class="text-muted small">No image</span>';
            }

            var crafty = (data.crafty_design_id)
                ? '<span class="status-badge status-live" title="crafty_db.designs.id">#' +
                escapeHtml(String(data.crafty_design_id)) + '</span>'
                : '—';

            var submitted = escapeHtml(data.created_display || '');

            return '<td class="table-plus">' + draft + '</td>' +
                '<td class="designer-info-cell">' + designer + '</td>' +
                '<td>' + cat + '</td>' +
                '<td>' + wh + '</td>' +
                '<td>' + title + '</td>' +
                '<td>' + thumb + '</td>' +
                '<td>' + statusBadgeHtml(data.status) + '</td>' +
                '<td>' + crafty + '</td>' +
                '<td>' + submitted + '</td>' +
                '<td class="actions-cell">' + actionsHtml(data.status, data.edit_url, data.seo_url) + '</td>';
        }

        function ensureEmptyState(tbody) {
            var n = tbody.querySelectorAll('tr[data-design-id]').length;
            if (n > 0) {
                return;
            }
            var emptyTr = tbody.querySelector('tr .empty-state');
            if (emptyTr) {
                return;
            }
            tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><i class="fa fa-paint-brush"></i>' +
                '<p>No design submissions found</p></div></td></tr>';
        }

        function showDsNotification(title, message) {
            var el = document.createElement('div');
            el.className = 'alert alert-info alert-dismissible fade show notification-alert-ds';
            el.setAttribute('role', 'alert');
            el.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;min-width:280px;';
            el.innerHTML = '<strong>' + escapeHtml(title) + ':</strong> ' + escapeHtml(message) +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            document.body.appendChild(el);
            setTimeout(function () {
                if (el.parentNode) {
                    el.remove();
                }
            }, 5000);
        }

        function addOrUpdateDesignSubmissionRow(data) {
            var tbody = document.querySelector('.ds-design-submissions-table tbody');
            if (!tbody || !data || !data.id) {
                return;
            }
            var emptyWrap = tbody.querySelector('.empty-state');
            if (emptyWrap) {
                emptyWrap.closest('tr').remove();
            }
            var existing = tbody.querySelector('tr[data-design-id="' + data.id + '"]');
            if (existing) {
                existing.innerHTML = buildDesignSubmissionTdsHtml(data);
                existing.classList.add('new-design-submission-highlight');
                setTimeout(function () {
                    existing.classList.remove('new-design-submission-highlight');
                }, 3000);
                return;
            }
            var tr = document.createElement('tr');
            tr.setAttribute('data-design-id', data.id);
            tr.className = 'new-design-submission-highlight';
            tr.innerHTML = buildDesignSubmissionTdsHtml(data);
            tbody.insertBefore(tr, tbody.firstChild);
            setTimeout(function () {
                tr.classList.remove('new-design-submission-highlight');
            }, 3000);
        }

        function onNewSubmission(data) {
            if (!data || !data.id) {
                return;
            }
            if (searchQuery) {
                window.location.reload();
                return;
            }
            if (listCurrentPage > 1) {
                showDsNotification('New design', '#' + data.id + ' — switch to page 1 or refresh to see it in the list.');
                return;
            }
            if (!rowMatchesListFilters(data)) {
                showDsNotification('New design', '#' + data.id + ' — not shown for current filters.');
                return;
            }
            addOrUpdateDesignSubmissionRow(data);
            showDsNotification('New design', '#' + data.id + ' ' + (data.title_display || '').substring(0, 40));
        }

        function onStatusChanged(data) {
            if (!data || !data.id) {
                return;
            }
            if (searchQuery) {
                window.location.reload();
                return;
            }
            var tbody = document.querySelector('.ds-design-submissions-table tbody');
            if (!tbody) {
                return;
            }
            var row = tbody.querySelector('tr[data-design-id="' + data.id + '"]');
            if (!row) {
                return;
            }
            if (!rowMatchesListFilters(data)) {
                row.remove();
                ensureEmptyState(tbody);
                showDsNotification('Design updated', '#' + data.id + ' removed from this view (status filter).');
                return;
            }
            row.innerHTML = buildDesignSubmissionTdsHtml(data);
            row.classList.add('new-design-submission-highlight');
            setTimeout(function () {
                row.classList.remove('new-design-submission-highlight');
            }, 3000);
        }

        function initWs() {
            try {
                var pusher = new Pusher(key, {
                    wsHost: @json(config('broadcasting.connections.pusher.options.host', env('PUSHER_HOST', '127.0.0.1'))),
                    wsPort: {{ (int) config('broadcasting.connections.pusher.options.port', env('PUSHER_PORT', 6001)) }},
                    wssPort: {{ (int) config('broadcasting.connections.pusher.options.port', env('PUSHER_PORT', 6001)) }},
                    forceTLS: false,
                    encrypted: false,
                    disableStats: true,
                    enabledTransports: ['ws', 'wss'],
                    cluster: @json(config('broadcasting.connections.pusher.options.cluster', env('PUSHER_APP_CLUSTER', 'mt1'))),
                    auth: {
                        headers: { 'X-CSRF-TOKEN': @json(csrf_token()) }
                    }
                });
                var rtAnnounced = false;
                pusher.connection.bind('connected', function () {
                    if (!rtAnnounced) {
                        rtAnnounced = true;
                        showDsNotification('Real-time', 'New designs appear in the table without refresh.');
                    }
                });
                var channel = pusher.subscribe('design-submissions');
                channel.bind('new-submission', onNewSubmission);
                channel.bind('submission-status-changed', onStatusChanged);
            } catch (e) {
                console.warn('Design submissions Pusher:', e);
            }
        }

        setTimeout(initWs, 1500);
    });
</script>
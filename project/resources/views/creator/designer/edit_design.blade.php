@include('layouts.masterhead')

<style>
    .ds-page {
        background: linear-gradient(180deg, #f0f4f8 0%, #e8eef5 100%);
        min-height: calc(100vh - 60px);
        padding: 1.25rem;
    }

    .ds-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3d7ab5 100%);
        color: #fff;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 24px rgba(30, 58, 95, 0.25);
    }

    .ds-hero h1 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 0.35rem 0;
        color: #fff;
    }

    .ds-hero .ds-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .ds-hero .ds-breadcrumb a:hover {
        color: #fff;
        text-decoration: underline;
    }

    .ds-hero .ds-meta {
        font-size: 0.8125rem;
        opacity: 0.9;
    }

    .ds-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.06);
        overflow: hidden;
        height: 100%;
    }

    .ds-card-h {
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #eef2f6;
        font-weight: 600;
        font-size: 0.9375rem;
        color: #1a2b3c;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ds-card-h i {
        color: #3d7ab5;
    }

    .ds-card-b {
        padding: 1.25rem;
    }

    .ds-preview-wrap {
        background: linear-gradient(145deg, #f8fafc 0%, #eef2f7 100%);
        border-radius: 10px;
        border: 1px dashed #cfd8e3;
        padding: 1rem;
        text-align: center;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ds-preview-wrap img {
        max-width: 100%;
        max-height: 220px;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .ds-stat {
        font-size: 0.8125rem;
        color: #5c6b7a;
        margin-bottom: 0.5rem;
    }

    .ds-stat strong {
        color: #1a2b3c;
        font-weight: 600;
    }

    .ds-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .ds-badge-pending {
        background: #fff3cd;
        color: #856404;
    }

    .ds-badge-seo {
        background: #cce5ff;
        color: #004085;
    }

    .ds-badge-live {
        background: #d4edda;
        color: #155724;
    }

    .ds-badge-reject {
        background: #f8d7da;
        color: #721c24;
    }

    .ds-badge-default {
        background: #e3f2fd;
        color: #1565c0;
    }

    .ds-form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #334155;
        margin-bottom: 0.35rem;
    }

    .ds-form-hint {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    .ds-actions-bar {
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 1px solid #eef2f6;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .ds-media-alert {
        display: none;
        padding: 10px;
        background: #f8d7da;
        border-radius: 6px;
        color: #721c24;
        margin-top: 8px;
    }

    .ds-current-file {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
        margin-bottom: 0;
    }

    .ds-thumb-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .ds-thumb-card {
        width: 178px;
        flex: 0 0 auto;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.65rem;
        text-align: center;
        position: relative;
    }

    .ds-thumb-card-h {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.35rem;
        margin-bottom: 0.35rem;
        text-align: left;
    }

    .ds-thumb-card-h .ds-thumb-key {
        margin-bottom: 0;
        flex: 1;
        min-width: 0;
    }

    .ds-thumb-remove {
        flex-shrink: 0;
        line-height: 1;
        padding: 0.15rem 0.45rem;
        font-size: 1rem;
    }

    .ds-thumb-card img {
        max-width: 100%;
        max-height: 120px;
        object-fit: contain;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

    .ds-thumb-card .ds-thumb-key {
        font-size: 0.7rem;
        color: #64748b;
        margin-bottom: 0.35rem;
        word-break: break-all;
    }

    .ds-video-preview video,
    .ds-video-preview img {
        max-width: 100%;
        max-height: 220px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

</style>

<div class="main-container">
    <div class="ds-page">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="ds-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <nav class="ds-breadcrumb mb-2">
                    <a href="{{ route('designer_system.design_submissions') }}"><i
                            class="fa fa-arrow-left me-1"></i>Design submissions</a>
                    <span class="text-white-50 mx-2">/</span>
                    <span class="text-white-50">Edit design</span>
                </nav>
                <h1><i class="fa fa-pencil-alt me-2 opacity-90"></i>Edit design #{{ $design->id }}</h1>
                <p class="ds-meta mb-0">{{ Str::limit($design->title, 80) }}</p>
                <p class="ds-meta mb-0 mt-1 small opacity-95">
                    @if($design->string_id)<span class="me-2">Draft
                    <code>{{ Str::limit($design->string_id, 40) }}</code></span>@endif
                    @if($design->template_id)<span class="me-2">tpl
                    <code>{{ Str::limit($design->template_id, 20) }}</code></span>@endif
                    @if($design->width || $design->height)<span
                    class="me-2">{{ $design->width ?? '—' }}×{{ $design->height ?? '—' }} px</span>@endif
                    @if($design->ratio !== null && $design->ratio !== '')<span>r
                    {{ is_numeric($design->ratio) ? round((float) $design->ratio, 4) : $design->ratio }}</span>@endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @php
                    $st = $design->status;
                    $badgeClass = match (true) {
                        $st === 'pending_designer_head' => 'ds-badge-pending',
                        $st === 'pending_seo' => 'ds-badge-seo',
                        $st === 'live' => 'ds-badge-live',
                        str_contains((string) $st, 'rejected') => 'ds-badge-reject',
                        default => 'ds-badge-default',
                    };
                    $badgeLabel = match (true) {
                        $st === 'pending_designer_head' => 'Step 1: Pending design manager',
                        $st === 'pending_seo' => 'Step 2: Pending SEO (approve → designs table)',
                        $st === 'live' => 'Live in designs table',
                        default => str_replace('_', ' ', $st),
                    };
                @endphp
                <span class="ds-badge {{ $badgeClass }}"><i class="fa fa-flag"></i>{{ $badgeLabel }}</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                @php
                    $thumbsArr = [];
                    if (old('thumbs') !== null) {
                        $decodedThumbs = json_decode(old('thumbs'), true);
                        if (is_array($decodedThumbs)) {
                            $thumbsArr = $decodedThumbs;
                        }
                    } elseif (is_array($design->thumbs)) {
                        $thumbsArr = $design->thumbs;
                    }
                    $thumbsJson = old('thumbs');
                    if ($thumbsJson === null) {
                        $thumbsJson = $thumbsArr !== []
                            ? json_encode($thumbsArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : '';
                    }
                    $sortedThumbs = collect($thumbsArr)->sortKeysUsing(function ($a, $b) {
                        return strnatcasecmp((string) $a, (string) $b);
                    });
                    $sortedThumbsExisting = $sortedThumbs->filter(function ($tpath) {
                        $p = is_string($tpath) ? $tpath : (is_scalar($tpath) ? (string) $tpath : '');

                        return \App\Models\Creator\Designer\DesignSubmission::panelMediaSourceExists($p);
                    });

                    $caricatureJson = old('caricature_ids');
                    if ($caricatureJson === null) {
                        $caricatureJson = $design->caricature_ids ? json_encode($design->caricature_ids, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
                    }

                    $videoRaw = trim((string) old('video', $design->video ?? ''));
                    $videoShowPreview = $videoRaw !== ''
                        && \App\Models\Creator\Designer\DesignSubmission::panelMediaSourceExists($videoRaw);
                    $videoPreviewUrl = $videoShowPreview
                        ? \App\Models\Creator\Designer\DesignSubmission::panelMediaDisplayUrl($videoRaw)
                        : null;
                    $videoPathPart = $videoRaw !== '' ? parse_url($videoRaw, PHP_URL_PATH) : '';
                    $videoExt = $videoRaw !== ''
                        ? strtolower(pathinfo(is_string($videoPathPart) && $videoPathPart !== '' ? $videoPathPart : $videoRaw, PATHINFO_EXTENSION))
                        : '';
                    $videoImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $designsJson = old('designs');
                    if ($designsJson === null && $design->designs) {
                        $decodedDesigns = json_decode($design->designs, true);
                        $designsJson = $decodedDesigns !== null
                            ? json_encode($decodedDesigns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : $design->designs;
                    }
                    if ($designsJson === null) {
                        $designsJson = '';
                    }
                    $ilSel = old('is_live', $design->is_live);
                    $draftFieldsLocked = !in_array($design->status, ['pending_designer_head', 'rejected_by_designer_head'], true);
                @endphp

                <div class="ds-card mb-3">
                    <div class="ds-card-b">
                        <form method="POST" action="{{ route('designer_system.design.update', $design->id) }}"
                            id="form-design-draft" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @if($draftFieldsLocked)
                                <div class="alert alert-secondary border mb-3" role="status">
                                    <i class="fa fa-lock me-2"></i>
                                    <strong>Read-only.</strong> This design was approved by the design manager. These draft
                                    fields cannot be changed here — use <strong>SEO &amp; Publish</strong> for listing
                                    content.
                                </div>
                            @endif
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="ds-form-label" for="string_id">String ID</label>
                                    <input type="text" id="string_id" name="string_id"
                                        class="form-control font-monospace small {{ $draftFieldsLocked ? 'bg-light' : '' }}"
                                        value="{{ old('string_id', $design->string_id) }}" maxlength="128"
                                        placeholder="string id" @if($draftFieldsLocked) readonly @endif>
                                </div>
                                <div class="col-md-6">
                                    <label class="ds-form-label" for="template_id">Template ID</label>
                                    <input type="text" id="template_id" name="template_id"
                                        class="form-control font-monospace small {{ $draftFieldsLocked ? 'bg-light' : '' }}"
                                        value="{{ old('template_id', $design->template_id) }}" maxlength="255"
                                        placeholder="tpl_…" @if($draftFieldsLocked) readonly @endif>
                                </div>
                                <div class="col-md-4">
                                    <label class="ds-form-label" for="width">Width</label>
                                    <input type="number" id="width" name="width"
                                        class="form-control {{ $draftFieldsLocked ? 'bg-light' : '' }}"
                                        value="{{ old('width', $design->width) }}" min="0" placeholder="px"
                                        @if($draftFieldsLocked) readonly @endif>
                                </div>
                                <div class="col-md-4">
                                    <label class="ds-form-label" for="height">Height</label>
                                    <input type="number" id="height" name="height"
                                        class="form-control {{ $draftFieldsLocked ? 'bg-light' : '' }}"
                                        value="{{ old('height', $design->height) }}" min="0" placeholder="px"
                                        @if($draftFieldsLocked) readonly @endif>
                                </div>
                                <div class="col-md-4">
                                    <label class="ds-form-label" for="ratio">Ratio</label>
                                    <input type="text" id="ratio" name="ratio"
                                        class="form-control {{ $draftFieldsLocked ? 'bg-light' : '' }}"
                                        value="{{ old('ratio', $design->ratio) }}" placeholder="e.g. 1.77"
                                        @if($draftFieldsLocked) readonly @endif>
                                </div>
                                <div class="col-md-6">
                                    <label class="ds-form-label">User Id <small
                                            class="text-muted">(read-only)</small></label>
                                    <input type="text" class="form-control font-monospace small bg-light" readonly
                                        value="{{ $design->user_id ?? '—' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="ds-form-label" for="msg">Message <small
                                        class="text-muted">(<code>msg</code>)</small></label>
                                <textarea id="msg" name="msg"
                                    class="form-control {{ $draftFieldsLocked ? 'bg-light' : '' }}" rows="2"
                                    placeholder="Freelancer note" @if($draftFieldsLocked) readonly
                                    @endif>{{ old('msg', $design->msg) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <input type="hidden" name="video" id="video" value="{{ old('video', $design->video) }}">
                                <label class="ds-form-label" for="video_file">Video file</label>
                                <input type="file" id="video_file" name="video_file"
                                    class="form-control form-control-sm height-auto"
                                    accept="video/mp4,video/webm,video/quicktime,.mov" @if($draftFieldsLocked) disabled
                                    @endif>
                                @if($videoShowPreview && $videoPreviewUrl !== '')
                                    <div class="ds-video-preview mt-3 p-2 bg-light rounded border">
                                        @if(in_array($videoExt, $videoImageExts, true))
                                            <img src="{{ $videoPreviewUrl }}" alt="Video cover"
                                                class="img-thumbnail rounded border"
                                                style="max-height:220px;object-fit:contain;">
                                        @else
                                            <video class="rounded border" width="320" height="180" controls preload="metadata"
                                                playsinline>
                                                <source src="{{ $videoPreviewUrl }}" type="video/{{ $videoExt ?: 'mp4' }}">
                                            </video>
                                        @endif
                                        <p class="ds-current-file mb-0 mt-2">Current:
                                            {{ basename(is_string($videoPathPart) && $videoPathPart !== '' ? $videoPathPart : $videoRaw) }}
                                        </p>
                                    </div>
                                @elseif($videoRaw !== '')
                                    <p class="ds-form-hint mb-0 mt-2"><i class="fa fa-info-circle text-muted"></i> Video
                                        path is set but the file was not found on storage — fix the path or upload again.
                                    </p>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="ds-form-label">Thumbs</label>
                                <h6 class="small text-muted mb-1">Images</h6>
                                <input type="file" class="form-control form-control-sm height-auto" id="thumb_images"
                                    name="thumb_images[]" multiple accept="image/*" @if($draftFieldsLocked) disabled
                                    @endif>
                                <div id="ds-thumb-grid"
                                    class="ds-thumb-grid mt-3 @if($sortedThumbsExisting->isEmpty()) d-none @endif">
                                    @foreach($sortedThumbsExisting as $tkey => $tpath)
                                        @php
                                            $tpath = is_string($tpath) ? $tpath : (is_scalar($tpath) ? (string) $tpath : '');
                                            $turl = $tpath !== ''
                                                ? (\App\Models\Creator\Designer\DesignSubmission::panelMediaDisplayUrl($tpath) ?? '')
                                                : '';
                                            $tPathPart = $tpath !== '' ? parse_url($tpath, PHP_URL_PATH) : '';
                                            $tBasename = $tpath !== ''
                                                ? basename(is_string($tPathPart) && $tPathPart !== '' ? $tPathPart : $tpath)
                                                : '';
                                        @endphp
                                        <div class="ds-thumb-card" data-thumb-key="{{ $tkey }}">
                                            <div class="ds-thumb-card-h">
                                                <div class="ds-thumb-key">Key <code>{{ $tkey }}</code></div>
                                                <button type="button" class="btn btn-sm btn-outline-danger ds-thumb-remove"
                                                    data-thumb-key="{{ $tkey }}" title="Remove this thumb"
                                                    @if($draftFieldsLocked) disabled @endif>×</button>
                                            </div>
                                            @if($turl !== '')
                                                <img src="{{ $turl }}" alt="" class="img-thumbnail rounded border"
                                                    style="max-height:120px;object-fit:contain;">
                                            @endif
                                            @if($tpath !== '')
                                                <p class="ds-current-file mb-0 mt-1">Current: {{ $tBasename }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <textarea id="thumbs" name="thumbs" class="d-none" tabindex="-1" aria-hidden="true"
                                    autocomplete="off" @if($draftFieldsLocked) readonly
                                    @endif>{{ $thumbsJson }}</textarea>
                            </div>
                            <textarea id="caricature_ids" name="caricature_ids" class="d-none" tabindex="-1"
                                aria-hidden="true" autocomplete="off" @if($draftFieldsLocked) readonly
                                @endif>{{ $caricatureJson }}</textarea>
                            <textarea id="designs" name="designs" class="d-none" tabindex="-1" aria-hidden="true"
                                autocomplete="off" @if($draftFieldsLocked) readonly @endif>{{ $designsJson }}</textarea>

                            <div class="ds-actions-bar">
                                @if(!$draftFieldsLocked)
                                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i>Save
                                        all changes</button>
                                @endif
                                @php
                                    $previewSidForLink = trim((string) old('string_id', $design->string_id ?? ''));
                                @endphp
                                @if($previewSidForLink !== '')
                                    <a id="ds-preview-design-link"
                                        href="https://designer.craftyartapp.com/{{ rawurlencode($previewSidForLink) }}"
                                        target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary px-4"
                                        data-string-id="{{ $previewSidForLink }}">
                                        <i class="fa fa-external-link-alt me-1"></i>Preview design
                                    </a>
                                @else
                                    <span class="btn btn-outline-secondary px-4 disabled" tabindex="-1"
                                        title="Set String ID above to open the designer preview"
                                        style="pointer-events: none; opacity: 0.65;">
                                        <i class="fa fa-external-link-alt me-1"></i>Preview design
                                    </span>
                                @endif
                                <a href="{{ route('designer_system.design_submissions') }}"
                                    class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>

                        {{-- Design Approval Section --}}
                        @if($design->status == 'pending_designer_head')
                            <div class="mt-4 pt-4 border-top">
                                <div class="ds-card-h border-0 px-0 pt-0 mb-3"><i class="fa fa-check-circle"></i> Design
                                    Review</div>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <strong>Review Required:</strong> This design is pending your approval. Review the
                                    design details above and approve to send to SEO team, or reject with feedback.
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <form method="POST"
                                            action="{{ route('designer_system.design.approve', $design->id) }}"
                                            onsubmit="return confirm('Approve this design and send to SEO team?');">
                                            @csrf
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <h6 class="text-success mb-3"><i
                                                            class="fa fa-check-circle me-2"></i>Approve Design</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes (optional)</label>
                                                        <textarea name="notes" class="form-control" rows="3"
                                                            placeholder="Add any notes for the SEO team..."></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fa fa-check me-2"></i>Approve & Send to SEO
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form method="POST"
                                            action="{{ route('designer_system.design.reject', $design->id) }}"
                                            onsubmit="return confirm('Are you sure you want to reject this design?');">
                                            @csrf
                                            <div class="card border-danger">
                                                <div class="card-body">
                                                    <h6 class="text-danger mb-3"><i
                                                            class="fa fa-times-circle me-2"></i>Reject Design</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Rejection Reason <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="notes" class="form-control" rows="3" required
                                                            minlength="10"
                                                            placeholder="Explain why this design is being rejected..."></textarea>
                                                        <small class="text-muted">Minimum 10 characters required</small>
                                                    </div>
                                                    <button type="submit" class="btn btn-danger w-100">
                                                        <i class="fa fa-times me-2"></i>Reject Design
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SEO Button (shown after approval) --}}
                        @if(in_array($design->status, ['pending_seo', 'approved_by_seo', 'live']))
                            <div class="mt-4 pt-4 border-top">
                                <div class="ds-card-h border-0 px-0 pt-0 mb-3"><i class="fa fa-search"></i> SEO & Publishing
                                </div>
                                @if($design->status == 'pending_seo')
                                    <div class="alert alert-success">
                                        <i class="fa fa-check-circle me-2"></i>
                                        <strong>Design Approved!</strong> This design has been approved and is ready for SEO
                                        optimization.
                                    </div>
                                @elseif($design->status == 'live')
                                    <div class="alert alert-success">
                                        <i class="fa fa-check-circle me-2"></i>
                                        <strong>Published!</strong> This design is live and available in the designs table.
                                        @if($design->crafty_design_id)
                                            <a href="{{ url('edit_seo_item/' . $design->crafty_design_id) }}" target="_blank"
                                                class="alert-link">View in designs table →</a>
                                        @endif
                                    </div>
                                @endif
                                <a href="{{ route('designer_system.design.seo', $design->id) }}"
                                    class="btn btn-info btn-lg w-100">
                                    <i class="fa fa-search me-2"></i>Edit SEO & Publish to Designs Table
                                </a>
                            </div>
                        @endif

                        {{-- Rejection Info --}}
                        @if(str_contains($design->status, 'rejected'))
                            <div class="mt-4 pt-4 border-top">
                                <div class="alert alert-danger">
                                    <h6 class="alert-heading"><i class="fa fa-times-circle me-2"></i>Design Rejected</h6>
                                    <p class="mb-0">
                                        <strong>Status:</strong> {{ str_replace('_', ' ', ucfirst($design->status)) }}<br>
                                        @if($design->designer_head_notes)
                                            <strong>Designer Head Notes:</strong> {{ $design->designer_head_notes }}<br>
                                        @endif
                                        @if($design->seo_head_notes)
                                            <strong>SEO Head Notes:</strong> {{ $design->seo_head_notes }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var grid = document.getElementById('ds-thumb-grid');
        var ta = document.getElementById('thumbs');
        if (!grid || !ta) return;
        if (ta.readOnly) return;

        function parseThumbsJson() {
            var raw = (ta.value || '').trim();
            if (raw === '') return {};
            try {
                var o = JSON.parse(raw);
                if (o !== null && typeof o === 'object' && !Array.isArray(o)) return o;
                if (Array.isArray(o)) {
                    var out = {};
                    o.forEach(function (v, i) { out[String(i)] = v; });
                    return out;
                }
            } catch (e) { }
            return null;
        }

        function writeThumbsJson(obj) {
            var keys = Object.keys(obj);
            if (keys.length === 0) {
                ta.value = '';
                return;
            }
            ta.value = JSON.stringify(obj, null, 4);
        }

        grid.addEventListener('click', function (e) {
            var btn = e.target.closest('.ds-thumb-remove');
            if (!btn) return;
            e.preventDefault();
            var key = btn.getAttribute('data-thumb-key');
            if (key === null || key === '') return;
            if (!confirm('Remove thumb "' + key + '" from this design? Click Save to apply.')) return;

            var obj = parseThumbsJson();
            if (obj === null) {
                alert('Thumbs JSON is invalid. Fix the JSON in the textarea first.');
                return;
            }
            delete obj[key];
            writeThumbsJson(obj);

            var card = btn.closest('.ds-thumb-card');
            if (card) card.remove();
            if (grid.querySelectorAll('.ds-thumb-card').length === 0) {
                grid.classList.add('d-none');
            }
        });
    })();

    (function () {
        var el = document.getElementById('ds-preview-design-link');
        var sidInput = document.getElementById('string_id');
        if (!el) return;

        var previewOrigin = 'https://designer.craftyartapp.com';

        function setPreviewHref() {
            var sid = sidInput && !sidInput.readOnly
                ? (sidInput.value || '').trim()
                : (el.getAttribute('data-string-id') || '').trim();
            if (!sid) {
                el.href = '#';
                el.classList.add('disabled');
                el.style.pointerEvents = 'none';
                el.setAttribute('title', 'Set String ID above to open the designer preview');
                return;
            }
            el.classList.remove('disabled');
            el.style.pointerEvents = '';
            el.removeAttribute('title');
            el.href = previewOrigin.replace(/\/$/, '') + '/' + encodeURIComponent(sid);
        }

        setPreviewHref();
        if (sidInput && !sidInput.readOnly) {
            sidInput.addEventListener('input', setPreviewHref);
            sidInput.addEventListener('change', setPreviewHref);
        }
    })();
</script>
@include('layouts.masterscript')
@include('layouts.masterhead')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')

<style>
    .career-page-section {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .career-page-section:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);
    }
    .career-page-section .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
    }
    .section-inactive {
        opacity: 0.5;
        pointer-events: none;
        filter: grayscale(0.4);
    }
    .repeatable-row {
        position: relative;
        background: #fff;
        transition: background 0.2s;
    }
    .repeatable-row:hover {
        background: #fcfcfc;
    }
    .btn-remove-item {
        color: #ef4444;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 4px 8px;
        background: transparent;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .btn-remove-item:hover {
        color: #b91c1c;
        background: #fef2f2;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-remove-item i {
        font-size: 0.95rem;
    }
    .btn-remove-item {
        color: #ef4444;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 4px 8px;
        background: transparent;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .btn-remove-item:hover {
        color: #b91c1c;
        background: #fef2f2;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-remove-item i {
        font-size: 0.95rem;
    }
    .btn-remove-row {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 5;
    }
    .sticky-save-bar {
        position: sticky;
        bottom: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        margin-top: 2rem;
    }
    .step-badge {
        width: 32px;
        height: 32px;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4 class="text-blue h4">Career Page Sections</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Career Page</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-30">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="text-muted small mb-0">Customize each section of your careers page. Toggle visibility as needed.</p>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle mr-2"></i> Please fix the errors in the highlighted sections below.
                        </div>
                    @endif

                    <form method="post" action="{{ route('career_page.update') }}" enctype="multipart/form-data" id="careerPageForm">
                        @csrf
                        @php
                            /** UI order only; backend `$sectionKeys` unchanged. */
                            $sectionDisplayOrder = [
                                'hero',
                                'tags',
                                'stats',
                                'our_culture',
                                'why_join',
                                'hiring_steps',
                                'open_roles',
                                'hr_details', // Added HR Details
                                'general_cta',
                            ];
                            $sectionTitles = [
                                'hero' => ['title' => 'Hero Section', 'icon' => 'dw dw-rocket'],
                                'tags' => ['title' => 'Search Tags', 'icon' => 'dw dw-price-tag'],
                                'stats' => ['title' => 'Company Stats', 'icon' => 'dw dw-analytics-11'],
                                'our_culture' => ['title' => 'Our Culture', 'icon' => 'dw dw-map'],
                                'why_join' => ['title' => 'Why Join Us', 'icon' => 'dw dw-star'],
                                'hiring_steps' => ['title' => 'Hiring Process', 'icon' => 'dw dw-list'],
                                'open_roles' => ['title' => 'Job Openings Heading', 'icon' => 'dw dw-briefcase'],
                                'hr_details' => ['title' => 'HR Details', 'icon' => 'dw dw-user1'],
                                'general_cta' => ['title' => 'General CTA', 'icon' => 'dw dw-megaphone'],
                            ];

                            $__careerSectionOrder = [];
                            foreach ($sectionDisplayOrder as $k) {
                                if (in_array($k, $sectionKeys, true)) { $__careerSectionOrder[] = $k; }
                            }
                            foreach ($sectionKeys as $k) {
                                if (!in_array($k, $__careerSectionOrder, true)) { $__careerSectionOrder[] = $k; }
                            }
                        @endphp

                        @foreach ($__careerSectionOrder as $key)
                            @php
                                $row = $sectionsByKey->get($key);
                                $v = ($row && is_array($row->value)) ? $row->value : [];
                                $isActive = old("sections.$key.is_active", (!$row || $row->is_active) ? '1' : '0') === '1';
                                $titleInfo = $sectionTitles[$key] ?? ['title' => ucwords(str_replace('_', ' ', $key)), 'icon' => 'dw dw-edit'];
                            @endphp

                            <div class="card career-page-section" id="section_{{ $key }}">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 font-weight-bold text-dark"><i class="{{ $titleInfo['icon'] }} mr-2 text-blue"></i> {{ $titleInfo['title'] }}</h5>
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="sections[{{ $key }}][is_active]" value="0">
                                            <input type="checkbox" class="custom-control-input section-visibility-toggle" 
                                                id="active_{{ $key }}" 
                                                name="sections[{{ $key }}][is_active]" 
                                                value="1" 
                                                data-section="{{ $key }}"
                                                {{ $isActive ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="active_{{ $key }}">Enabled</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4 {{ !$isActive ? 'section-inactive' : '' }}" data-content-for="{{ $key }}">
                                    
                                    @if ($key === 'hero')
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error("sections.hero.title") is-invalid @enderror" 
                                                        name="sections[hero][title]" maxlength="255" required
                                                        value="{{ old('sections.hero.title', $v['title'] ?? '') }}">
                                                    @error("sections.hero.title") <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label>Subtitle</label>
                                                    <textarea class="form-control" name="sections[hero][subtitle]" rows="3" maxlength="1000">{{ old('sections.hero.subtitle', $v['subtitle'] ?? '') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="font-weight-bold small text-muted">Hero Background Media</label>
                                                <input type="file" class="form-control height-auto dynamic-file" 
                                                    data-imgstore-id="sections[hero][image]"
                                                    data-value="{{ $contentManager::getStorageLink($v['image'] ?? null) }}"
                                                    data-nameset="true" data-validate="false"
                                                    data-accept=".jpg,.jpeg,.webp,.png,.svg">
                                                <small class="form-text text-muted">WebP/SVG recommended.</small>
                                            </div>
                                        </div>

                                    @elseif($key === 'stats')
                                        <div class="repeatable-container" data-name-base="sections[stats][items]">
                                            @php $items = old('sections.stats.items', $v['items'] ?? [['label' => '', 'value' => '']]); @endphp
                                            @foreach ($items as $index => $item)
                                                <div class="repeatable-row border rounded p-3 mb-3">
                                                    <button type="button" class="btn-remove-item btn-remove-row" title="Remove Stat">
                                                        <i class="fa fa-trash-o mr-1"></i> Remove
                                                    </button>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="small text-muted">Label (e.g. Total Users)</label>
                                                                <input type="text" name="sections[stats][items][{{$index}}][label]" 
                                                                    class="form-control @error("sections.stats.items.$index.label") is-invalid @enderror" 
                                                                    value="{{ $item['label'] ?? '' }}" maxlength="100" required>
                                                                @error("sections.stats.items.$index.label") <small class="text-danger">{{ $message }}</small> @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="small text-muted">Value (e.g. 50k+)</label>
                                                                <input type="text" name="sections[stats][items][{{$index}}][value]" 
                                                                    class="form-control @error("sections.stats.items.$index.value") is-invalid @enderror" 
                                                                    value="{{ $item['value'] ?? '' }}" maxlength="50" required>
                                                                @error("sections.stats.items.$index.value") <small class="text-danger">{{ $message }}</small> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus mr-1"></i> Add Stat</button>
                                        </div>

                                    @elseif($key === 'tags')
                                        <div class="repeatable-container d-flex flex-wrap" data-name-base="sections[tags][items]">
                                            @php $items = old('sections.tags.items', $v['items'] ?? [['label' => '']]); @endphp
                                            @foreach ($items as $index => $item)
                                                <div class="repeatable-row border rounded p-2 mr-2 mb-2 d-flex align-items-center" style="min-width: 200px;">
                                                    <input type="text" name="sections[tags][items][{{$index}}][label]" 
                                                        class="form-control form-control-sm mr-2" 
                                                        placeholder="Tag Label" value="{{ $item['label'] ?? '' }}" maxlength="50" required>
                                                    <button type="button" class="btn-remove-item btn-remove-row p-1" style="position:static" title="Remove Tag">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus"></i></button>
                                            @error("sections.tags.items.*.label") <small class="text-danger d-block w-100 mt-1">{{ $message }}</small> @enderror
                                        </div>

                                    @elseif($key === 'our_culture')
                                        <div class="row">
                                            <div class="col-md-12 mb-4">
                                                <div class="form-group">
                                                    <label>Section Title</label>
                                                    <input type="text" class="form-control" name="sections[our_culture][title]" 
                                                        value="{{ old('sections.our_culture.title', $v['title'] ?? '') }}" maxlength="255">
                                                </div>
                                                <div class="form-group">
                                                    <label>Introduction</label>
                                                    <textarea class="form-control" name="sections[our_culture][intro]" rows="3">{{ old('sections.our_culture.intro', $v['intro'] ?? '') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6 border-right">
                                                <h6 class="mb-3 font-weight-bold">Highlights</h6>
                                                <div class="repeatable-container" data-name-base="sections[our_culture][highlights]">
                                                    @php $hls = old('sections.our_culture.highlights', $v['highlights'] ?? [['title' => '', 'icon' => '']]); @endphp
                                                    @foreach ($hls as $index => $hl)
                                                        <div class="repeatable-row border rounded p-3 mb-3">
                                                            <button type="button" class="btn-remove-item btn-remove-row" title="Remove Highlight">
                                                                <i class="fa fa-trash-o mr-1"></i> Remove
                                                            </button>
                                                            <div class="form-group">
                                                                <label class="small font-weight-bold">Icon (SVG/PNG)</label>
                                                                <input type="file" class="form-control height-auto dynamic-file" 
                                                                    data-imgstore-id="sections[our_culture][highlights][{{$index}}][icon]"
                                                                    data-value="{{ $contentManager::getStorageLink($hl['icon'] ?? null) }}"
                                                                    data-nameset="true" data-validate="false"
                                                                    accept="image/*">
                                                            </div>
                                                            <input type="text" name="sections[our_culture][highlights][{{$index}}][title]" 
                                                                class="form-control mb-2" placeholder="Title" value="{{ $hl['title'] ?? '' }}" maxlength="100">
                                                            <textarea name="sections[our_culture][highlights][{{$index}}][description]" 
                                                                class="form-control" placeholder="Description" rows="2">{{ $hl['description'] ?? '' }}</textarea>
                                                        </div>
                                                    @endforeach
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row">Add Highlight</button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-3 font-weight-bold">Culture Media</h6>
                                                @php $ocMedia = $v['media'] ?? []; @endphp
                                                <div class="form-group">
                                                    <label>Type</label>
                                                    <select name="sections[our_culture][media][type]" class="form-control">
                                                        <option value="image" {{ (old('sections.our_culture.media.type', $ocMedia['type'] ?? '') == 'image') ? 'selected' : '' }}>Image</option>
                                                        <option value="video" {{ (old('sections.our_culture.media.type', $ocMedia['type'] ?? '') == 'video') ? 'selected' : '' }}>Video</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Media File</label>
                                                    <input type="file" name="sections[our_culture][media][file]" class="form-control-file border p-1 rounded" accept="image/*,video/mp4,video/webm">
                                                    @if($ocMedia['file'] ?? null)
                                                        <div class="mt-1 small text-muted">Active: <code>{{ $ocMedia['file'] }}</code></div>
                                                    @endif
                                                </div>
                                                <div class="form-group">
                                                    <label>Video Poster (Thumbnail)</label>
                                                    <input type="file" class="form-control height-auto dynamic-file" 
                                                        data-imgstore-id="sections[our_culture][media][poster]"
                                                        data-value="{{ $contentManager::getStorageLink($ocMedia['poster'] ?? null) }}"
                                                        data-nameset="true" data-validate="false"
                                                        accept="image/*">
                                                </div>
                                                <div class="form-group">
                                                    <label>Media Caption</label>
                                                    <textarea name="sections[our_culture][media][caption]" class="form-control" rows="2" placeholder="Tell a story about this media...">{{ old('sections.our_culture.media.caption', $ocMedia['caption'] ?? '') }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tags (Comma separated or repeatable)</label>
                                                    <div class="repeatable-container" data-name-base="sections[our_culture][media][hashtags]" data-repeatable-simple="true">
                                                        @php $ocTags = old('sections.our_culture.media.hashtags', $ocMedia['hashtags'] ?? ['']); @endphp
                                                        @foreach ($ocTags as $index => $tag)
                                                            <div class="repeatable-row mb-1 d-flex">
                                                                <input type="text" name="sections[our_culture][media][hashtags][]" class="form-control form-control-sm" placeholder="#culture" value="{{ $tag }}">
                                                                <button type="button" class="btn-remove-item btn-remove-row ml-1 p-1" style="position:static" title="Remove Hashtag">
                                                                    <i class="fa fa-trash-o"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                        <button type="button" class="btn btn-sm btn-link p-0 btn-add-row">+ Add Hashtag</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($key === 'why_join')
                                        <div class="form-group">
                                            <label>Reasoning Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('sections.why_join.title') is-invalid @enderror" 
                                                name="sections[why_join][title]" value="{{ old('sections.why_join.title', $v['title'] ?? '') }}" 
                                                maxlength="255" required>
                                        </div>
                                        <div class="row repeatable-container" data-name-base="sections[why_join][items]">
                                            @php $wjItems = old('sections.why_join.items', $v['items'] ?? [['title' => '']]); @endphp
                                            @foreach ($wjItems as $index => $item)
                                                <div class="col-xl-4 col-lg-6 col-md-6 repeatable-row mb-4">
                                                    <div class="card h-100 border rounded shadow-sm bg-light position-relative">
                                                        <button type="button" class="btn-remove-item btn-remove-row d-flex align-items-center justify-content-center" 
                                                            style="position: absolute; top: -5px; right: -5px; border-radius: 6px; padding: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" 
                                                            title="Remove">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                        
                                                        <div class="card-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="small font-weight-bold text-muted">Icon (SVG/PNG)</label>
                                                                <input type="file" class="form-control-file form-control height-auto dynamic-file" 
                                                                    data-imgstore-id="sections[why_join][items][{{$index}}][icon]"
                                                                    data-value="{{ $contentManager::getStorageLink($item['icon'] ?? null) }}"
                                                                    data-nameset="true" data-validate="false"
                                                                    accept="image/*">
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="small font-weight-bold text-muted">Title <span class="text-danger">*</span></label>
                                                                <input type="text" name="sections[why_join][items][{{$index}}][title]" 
                                                                    class="form-control @error("sections.why_join.items.$index.title") is-invalid @enderror" 
                                                                    placeholder="e.g. Work-life balance" value="{{ $item['title'] ?? '' }}" 
                                                                    maxlength="100" required>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small font-weight-bold text-muted">Description</label>
                                                                <textarea name="sections[why_join][items][{{$index}}][description]" 
                                                                    class="form-control" placeholder="Short description..." 
                                                                    rows="3">{{ $item['description'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="col-12 mt-2 add-action-row">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus mr-1"></i> Add Reason</button>
                                            </div>
                                        </div>

                                    @elseif($key === 'hiring_steps')
                                        <div class="form-group">
                                            <label>Section Title</label>
                                            <input type="text" class="form-control" name="sections[hiring_steps][title]" value="{{ old('sections.hiring_steps.title', $v['title'] ?? '') }}" maxlength="255">
                                        </div>
                                        <div class="repeatable-container" data-name-base="sections[hiring_steps][steps]" data-is-stepper="true">
                                            @php $steps = old('sections.hiring_steps.steps', $v['steps'] ?? [['title' => '']]); @endphp
                                            @foreach ($steps as $index => $step)
                                                <div class="repeatable-row border rounded p-3 mb-3 d-flex align-items-start">
                                                    <div class="step-badge mr-3 bg-blue text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                                        <span class="step-number">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="flex-grow-1 mr-4">
                                                        <input type="text" name="sections[hiring_steps][steps][{{$index}}][title]" 
                                                            class="form-control mb-2" placeholder="Step Title" value="{{ $step['title'] ?? '' }}" maxlength="100" required>
                                                        <textarea name="sections[hiring_steps][steps][{{$index}}][description]" 
                                                            class="form-control" placeholder="Description" rows="2">{{ $step['description'] ?? '' }}</textarea>
                                                    </div>
                                                    <button type="button" class="btn-remove-item btn-remove-row" style="position:static" title="Remove Step">
                                                        <i class="fa fa-trash-o mr-1"></i> Remove Step
                                                    </button>
                                                </div>
                                            @endforeach
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus"></i> Add Next Step</button>
                                        </div>

                                    @elseif($key === 'open_roles')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Section Heading <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('sections.open_roles.title') is-invalid @enderror" 
                                                        name="sections[open_roles][title]" value="{{ old('sections.open_roles.title', $v['title'] ?? '') }}" 
                                                        maxlength="255" required>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label>Section Subtitle</label>
                                                    <textarea class="form-control" name="sections[open_roles][subtitle]" rows="2">{{ old('sections.open_roles.subtitle', $v['subtitle'] ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($key === 'hr_details')
                                        <div class="row">
                                            <div class="col-md-6 border-right">
                                                <div class="form-group">
                                                    <label>HR Phone Number <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control @error('sections.hr_details.phone') is-invalid @enderror" 
                                                        name="sections[hr_details][phone]" placeholder="+1 234 567 890" 
                                                        value="{{ old('sections.hr_details.phone', $v['phone'] ?? '') }}" required>
                                                    @error('sections.hr_details.phone') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold small text-muted">HR Email <span class="text-danger">*</span></label>
                                                    <input type="email" name="sections[hr_details][email]" 
                                                        class="form-control @error("sections.hr_details.email") is-invalid @enderror" 
                                                        placeholder="hr@example.com" value="{{ old('sections.hr_details.email', $v['email'] ?? '') }}" required>
                                                    @error("sections.hr_details.email") <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($key === 'general_cta')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>CTA Heading</label>
                                                    <input type="text" class="form-control" name="sections[general_cta][title]" value="{{ old('sections.general_cta.title', $v['title'] ?? '') }}" maxlength="255">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label>CTA Description</label>
                                                    <textarea class="form-control" name="sections[general_cta][subtitle]" rows="2">{{ old('sections.general_cta.subtitle', $v['subtitle'] ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach

                        <div class="card career-page-section mb-3">
                            <div class="card-header">
                                <h5 class="mb-0 font-weight-bold text-dark"><i class="dw dw-list mr-2 text-blue"></i> Job Listings</h5>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small">Actual job cards on the careers page are managed separately in the Job Openings module.</p>
                                <a href="{{ route('job_openings.index') }}" class="btn btn-sm btn-outline-primary">Manage Job Openings <i class="fa fa-arrow-right ml-1"></i></a>
                            </div>
                        </div>

                        <div class="sticky-save-bar d-flex justify-content-between align-items-center">
                            <span class="text-muted small">All changes must be saved to apply to the live site.</span>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow" id="submitBtn">
                                <i class="fa fa-save mr-2"></i> Save Section Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
(function() {
    'use strict';

    // UI Configuration
    const CONFIG = {
        sectionToggle: '.section-visibility-toggle',
        repeatableContainer: '.repeatable-container',
        repeatableRow: '.repeatable-row',
        btnAdd: '.btn-add-row',
        btnRemove: '.btn-remove-row',
        inactiveClass: 'section-inactive'
    };

    /**
     * Toggles section visual state (Opacity filter)
     * Keeps inputs enabled to prevent data loss on submit
     */
    function syncSectionVisibility(checkbox) {
        const key = checkbox.dataset.section;
        const target = document.querySelector(`[data-content-for="${key}"]`);
        if (!target) return;

        const isChecked = checkbox.checked;
        if (isChecked) {
            target.classList.remove(CONFIG.inactiveClass);
        } else {
            target.classList.add(CONFIG.inactiveClass);
        }

        // Toggle disabled state for all inputs/buttons to truly prevent interaction and look the part
        target.querySelectorAll('input, select, textarea, button').forEach(el => {
            el.disabled = !isChecked;
        });
    }

    /**
     * Updates name indices and step numbers
     */
    function reindex(container) {
        const base = container.dataset.nameBase;
        const isStepper = container.dataset.isStepper === 'true';
        const isSimple = container.dataset.repeatableSimple === 'true';
        
        const rows = container.querySelectorAll(CONFIG.repeatableRow);
        rows.forEach((row, i) => {
            // Re-index inputs
            row.querySelectorAll('input, textarea, select').forEach(input => {
                // Update dynamic-file attributes if present (even if no name attribute exists yet)
                if (input.dataset.imgstoreId) {
                    input.dataset.imgstoreId = input.dataset.imgstoreId.replace(/\[\d+\]/, `[${i}]`);
                }

                if (!input.name) return;
                
                if (isSimple && input.name.endsWith('[]')) return;

                // Handle standard indexed names like sections[hero][items][0][title]
                input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
            });

            // Update step badges
            if (isStepper) {
                const badge = row.querySelector('.step-number');
                if (badge) badge.textContent = i + 1;
            }
        });
    }

    // Initialize Visibility
    document.querySelectorAll(CONFIG.sectionToggle).forEach(cb => {
        cb.addEventListener('change', () => syncSectionVisibility(cb));
        syncSectionVisibility(cb);
    });

    // Event Delegation for Dynamic Rows
    document.body.addEventListener('click', function(e) {
        // ADD ROW
        const addBtn = e.target.closest(CONFIG.btnAdd);
        if (addBtn) {
            e.preventDefault();
            const container = addBtn.closest(CONFIG.repeatableContainer);
            const rows = container.querySelectorAll(CONFIG.repeatableRow);
            if (rows.length === 0) return;

            const clone = rows[rows.length - 1].cloneNode(true);

            // Clean clone state
            clone.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            clone.querySelectorAll('.text-danger').forEach(el => el.remove());
            clone.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea').forEach(el => el.value = '');
            clone.querySelectorAll('.dynamic-file-preview, .img-thumbnail').forEach(el => el.remove());
            clone.querySelectorAll('.dynamic-file').forEach(el => {
                el.dataset.value = '';
                el.style.display = 'block'; // Reset display style as it might have been hidden in the cloned row
            });

            // Use the button's wrapper or the button itself as the insertion point
            let referenceNode = addBtn.closest('.add-action-row') || addBtn;
            
            // Ensure referenceNode is a direct child of container
            if (referenceNode.parentNode !== container) {
                referenceNode = addBtn;
            }

            try {
                container.insertBefore(clone, referenceNode);
            } catch (err) {
                container.appendChild(clone);
            }

            const isSectionInactive = addBtn.closest(`.${CONFIG.inactiveClass}`) !== null;
            if (isSectionInactive) {
                clone.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
            }

            reindex(container);

            // Localized enhancement to avoid calling the destructive global dynamicFileCmp()
            const localEnhance = (input) => {
                if (!input || input.dataset.enhanced) return;
                
                const isFieldRequired = input.dataset.required == null ? true : input.dataset.required;
                input.dataset.enhanced = "true";
                
                const wrapper = document.createElement("div");
                wrapper.classList.add("dynamic-file-input", "mb-3");
                wrapper.style.width = "100%";

                const controlsRow = document.createElement("div");
                controlsRow.style.display = "flex";
                controlsRow.style.gap = "10px";
                controlsRow.style.alignItems = "center";
                controlsRow.style.marginBottom = "10px";

                const dropdown = document.createElement("select");
                dropdown.classList.add("form-select");
                dropdown.style.width = "120px";
                dropdown.innerHTML = `<option value="file" selected>File</option><option value="url">URL</option>`;

                const urlInput = document.createElement("input");
                urlInput.type = "text";
                urlInput.classList.add("form-control", "url-input");
                urlInput.placeholder = "Enter Image URL";
                urlInput.style.display = "none";
                urlInput.style.flex = "1";

                const previewContainer = document.createElement("div");
                previewContainer.style.cssText = "margin-top: 10px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px; background-color: #f8f9fa; display: none;";

                const previewImg = document.createElement("img");
                previewImg.classList.add("img-thumbnail", "image-preview");
                previewImg.style.cssText = "max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain; display: block;";

                const base64Input = document.createElement("input");
                base64Input.type = "hidden";

                if (input.dataset.imgstoreId) {
                    previewImg.id = input.dataset.imgstoreId;
                    base64Input.name = input.dataset.imgstoreId;
                }
                
                input.style.flex = "1";
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(controlsRow);
                controlsRow.appendChild(dropdown);
                controlsRow.appendChild(input);
                controlsRow.appendChild(urlInput);
                previewContainer.appendChild(previewImg);
                wrapper.appendChild(previewContainer);
                wrapper.appendChild(base64Input);

                if (input.dataset.accept) input.setAttribute("accept", input.dataset.accept);
                
                if (input.dataset.value) {
                    urlInput.value = input.dataset.value;
                    previewImg.src = input.dataset.value;
                    base64Input.value = input.dataset.value;
                    previewContainer.style.display = "block";
                    if (input.dataset.value.startsWith("data:")) {
                        dropdown.value = "file";
                        input.style.display = "block";
                        urlInput.style.display = "none";
                    } else {
                        dropdown.value = "url";
                        input.style.display = "none";
                        urlInput.style.display = "block";
                    }
                }

                dropdown.addEventListener("change", function() {
                    const isFile = this.value === "file";
                    input.style.display = isFile ? "block" : "none";
                    urlInput.style.display = isFile ? "none" : "block";
                });

                input.addEventListener("change", function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            previewImg.src = e.target.result;
                            base64Input.value = e.target.result;
                            previewContainer.style.display = "block";
                        };
                        reader.readAsDataURL(file);
                    }
                });

                urlInput.addEventListener("input", function() {
                    previewImg.src = this.value;
                    base64Input.value = this.value;
                    previewContainer.style.display = this.value ? "block" : "none";
                });
            };

            // Re-initialize dynamic file inputs for the new row
            clone.querySelectorAll('.dynamic-file').forEach(el => {
                delete el.dataset.enhanced;
                const wrapper = el.closest(".dynamic-file-input");
                if (wrapper) {
                    wrapper.parentNode.insertBefore(el, wrapper);
                    wrapper.remove();
                }
                localEnhance(el);
            });
            
            // If the section was inactive, the newly added dynamic file inputs might need to be disabled too
            if (isSectionInactive) {
                clone.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
            }
        }

        // REMOVE ROW
        const rmBtn = e.target.closest(CONFIG.btnRemove);
        if (rmBtn) {
            e.preventDefault();
            const container = rmBtn.closest(CONFIG.repeatableContainer);
            const rows = container.querySelectorAll(CONFIG.repeatableRow);
            
            if (rows.length > 1) {
                rmBtn.closest(CONFIG.repeatableRow).remove();
                reindex(container);
            } else {
                alert('At least one item is required.');
            }
        }
    });

    // Submit Loading State
    const form = document.getElementById('careerPageForm');
    if (form) {
        form.addEventListener('submit', () => {
            // Re-enable all inputs before sending so we don't lose data for inactive sections
            form.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = false);

            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Saving...';
            btn.classList.add('disabled');
        });
    }
    // Initialize dynamic file inputs on page load
    document.querySelectorAll('.dynamic-file').forEach(el => localEnhance(el));
})();
</script>
</body>
</html>

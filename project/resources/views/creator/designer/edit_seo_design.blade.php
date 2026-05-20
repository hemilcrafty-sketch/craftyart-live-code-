@include('layouts.masterhead')
@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
{{-- Layout aligned with item/edit_seo_raw (edit_seo_item) --}}
<style>
    .designer-seo-page .seo-main-panel {
        background-color: #eaeaea;
    }

    .designer-seo-page .seo-main-panel h6 {
        font-weight: 600;
        margin-top: 0;
    }

    /* Select2: do not load select2.min.css after this page — if you do, default gray wins. These reinforce theme chips. */
    .designer-seo-page .select2-container--default .select2-selection--multiple {
        min-height: 45px;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem;
    }

    .designer-seo-page .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1b00ff !important;
        color: #fff !important;
        border: 1px solid #1300cc !important;
        border-radius: 5px;
        padding: 5px 10px;
        margin: 2px;
    }

    .designer-seo-page .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
        margin-right: 6px;
    }

    .designer-seo-page .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f0f0f0 !important;
    }

    .designer-seo-page .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #1b00ff !important;
        box-shadow: 0 0 0 0.15rem rgba(27, 0, 255, 0.15);
    }

    /* Color hex chips: full width; text color set in JS from contrast */
    .designer-seo-page .color_tags .bootstrap-tagsinput {
        width: 100% !important;
        max-width: 100%;
        min-height: 45px;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    .designer-seo-page .color_tags .bootstrap-tagsinput .tag {
        border: 1px solid rgba(0, 0, 0, 0.12);
    }

    .designer-seo-page .color_tags .sp-replacer {
        margin-top: 8px;
        vertical-align: middle;
    }

    /* Same pattern as edit_seo_item (new category required popup) */
    .designer-seo-page .popup-container {
        display: none;
        position: fixed;
        z-index: 10050;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border: 1px solid #dc3545;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
        padding: 16px 20px;
        border-radius: 6px;
        max-width: 90%;
    }

    .designer-seo-page .popup-container .required-icon {
        display: inline-block;
        width: 22px;
        height: 22px;
        line-height: 22px;
        text-align: center;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        font-weight: bold;
        margin-right: 8px;
    }

    /* Select New Category: match edit_seo_item — panel must not clip absolute dropdown (see custom.css .parent-category-input.show) */
    .designer-seo-page .seo-main-panel {
        overflow: visible !important;
    }

    .designer-seo-page .min-height-200px {
        overflow: visible !important;
    }

    .designer-seo-page .form-group.category-dropbox-wrap {
        position: relative;
        z-index: 1;
    }

    .designer-seo-page .form-group.category-dropbox-wrap.category-dropdown-open {
        z-index: 10060;
    }

    .designer-seo-page .custom-dropdown.parent-category-input.show {
        z-index: 10061 !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .designer-seo-page .input-subcategory-dropbox {
        cursor: pointer;
    }
</style>

<div class="main-container seo-access-container designer-seo-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <i class="fa fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @php
            $st = $design->status;
            $statusBadgeClass = match (true) {
                $st === 'pending_seo' => 'warning',
                $st === 'live' => 'success',
                $st === 'rejected_by_seo' => 'danger',
                default => 'info',
            };
            $statusBadgeText = match (true) {
                $st === 'pending_seo' => 'Pending SEO review',
                $st === 'live' => 'Live',
                $st === 'rejected_by_seo' => 'Rejected (SEO)',
                default => str_replace('_', ' ', $st),
            };
        @endphp
    </div>

    <div class="min-height-200px">
        <div class="pd-20 card-box mb-30 seo-main-panel" style="background-color: #eaeaea;">
            @php
                $seo = $design->seoDetails;
                $kwDefault = ($seo && $seo->keywords && is_array($seo->keywords)) ? implode(', ', $seo->keywords) : '';

                // Get current design data from crafty_db if published (same source as edit_seo_item)
                $publishedDesign = null;
                if ($design->crafty_design_id) {
                    $publishedDesign = \App\Models\Design::find($design->crafty_design_id);
                }

                $fil = ($seo && is_array($seo->filters)) ? $seo->filters : [];
                $jsonIds = function ($v) {
                    if ($v === null || $v === '') {
                        return [];
                    }
                    $a = is_array($v) ? $v : json_decode((string) $v, true);

                    return is_array($a) ? array_map('intval', $a) : [];
                };

                $langSelected = old('lang_id', $fil['language_ids'] ?? $jsonIds($publishedDesign?->lang_id));
                $langSelected = is_array($langSelected) ? $langSelected : [];
                $themeSelected = old('theme_id', $fil['theme_ids'] ?? $jsonIds($publishedDesign?->theme_id));
                $themeSelected = is_array($themeSelected) ? $themeSelected : [];
                $stylesSelected = old('styles', $fil['style_ids'] ?? $jsonIds($publishedDesign?->style_id));
                $stylesSelected = is_array($stylesSelected) ? $stylesSelected : [];
                $religionSelected = old('religion_id', $fil['religion_ids'] ?? $jsonIds($publishedDesign?->religion_id));
                $religionSelected = is_array($religionSelected) ? $religionSelected : [];
                $interestSelected = old('interest_id', $fil['interest_ids'] ?? $jsonIds($publishedDesign?->interest_id));
                $interestSelected = is_array($interestSelected) ? $interestSelected : [];

                $orientationVal = old('orientation', $fil['orientation'] ?? ($publishedDesign?->orientation ?? 'portrait'));
                $templateSizeVal = old('template_size', $fil['size_id'] ?? $publishedDesign?->template_size);

                $newKwFromFil = '';
                if (!empty($fil['new_related_tag_ids']) && is_array($fil['new_related_tag_ids'])) {
                    $newKwFromFil = \App\Models\NewSearchTag::whereIn('id', $fil['new_related_tag_ids'])->pluck('name')->implode(',');
                }
                $publishedTagNames = '';
                if ($publishedDesign && $publishedDesign->new_related_tags) {
                    $tagIdsPub = json_decode($publishedDesign->new_related_tags, true);
                    if (is_array($tagIdsPub) && $tagIdsPub !== []) {
                        $publishedTagNames = \App\Models\NewSearchTag::whereIn('id', $tagIdsPub)->pluck('name')->implode(',');
                    }
                }
                $newKeywordsValue = old('new_keywords', $newKwFromFil !== '' ? $newKwFromFil : $publishedTagNames);

                $colorFromFil = '';
                if (!empty($fil['colors']) && is_array($fil['colors'])) {
                    $colorFromFil = implode(',', $fil['colors']);
                }
                $publishedColors = '';
                if ($publishedDesign && $publishedDesign->color_id) {
                    $publishedColors = implode(',', array_filter(json_decode($publishedDesign->color_id, true) ?: []));
                }
                $colorIdsValue = old('color_ids', $colorFromFil !== '' ? $colorFromFil : $publishedColors);
                $colorIdsForJs = array_values(array_filter(array_map(static function ($s) {
                    $s = trim((string) $s);
                    if ($s === '') {
                        return null;
                    }
                    return Str::startsWith($s, '#') ? $s : (preg_match('/^[0-9a-fA-F]{3,8}$/', $s) ? '#' . $s : $s);
                }, preg_split('/\s*,\s*/', (string) $colorIdsValue, -1, PREG_SPLIT_NO_EMPTY))));

                $dateRangeValue = old('date_range', '');
                if ($dateRangeValue === null || $dateRangeValue === '') {
                    if (!empty($fil['start_date']) && !empty($fil['end_date'])) {
                        $dateRangeValue = $fil['start_date'] . ' - ' . $fil['end_date'];
                    } elseif ($publishedDesign && $publishedDesign->start_date && $publishedDesign->end_date) {
                        $dateRangeValue = $publishedDesign->start_date . ' - ' . $publishedDesign->end_date;
                    }
                }

                $ratioDefault = old('ratio_field', $fil['ratio_field'] ?? ($publishedDesign?->ratio ?? $design->ratio ?? ''));

                $statusLiveDefault = old('status_field');
                if ($statusLiveDefault === null) {
                    if (array_key_exists('publish_status_live', $fil)) {
                        $statusLiveDefault = $fil['publish_status_live'] ? '1' : '0';
                    } else {
                        $statusLiveDefault = $publishedDesign ? (string) (int) $publishedDesign->status : '1';
                    }
                }

                $isPremiumSel = old('is_premium');
                if ($isPremiumSel === null) {
                    if (array_key_exists('is_premium', $fil)) {
                        $isPremiumSel = $fil['is_premium'] ? '1' : '0';
                    } else {
                        $isPremiumSel = $publishedDesign && ((int) $publishedDesign->is_premium === 1) ? '1' : '0';
                    }
                }
                $isFreemiumSel = old('is_freemium');
                if ($isFreemiumSel === null) {
                    if (array_key_exists('is_freemium', $fil)) {
                        $isFreemiumSel = $fil['is_freemium'] ? '1' : '0';
                    } else {
                        $isFreemiumSel = $publishedDesign && ((int) $publishedDesign->is_freemium === 1) ? '1' : '0';
                    }
                }

                $specialKwSelected = old('special_keywords');
                if ($specialKwSelected === null) {
                    if (!empty($fil['special_keyword_ids']) && is_array($fil['special_keyword_ids'])) {
                        $specialKwSelected = array_map('intval', $fil['special_keyword_ids']);
                    } elseif ($publishedDesign && $publishedDesign->special_keywords) {
                        $dec = json_decode($publishedDesign->special_keywords, true);
                        $specialKwSelected = is_array($dec) ? array_map('intval', $dec) : [];
                    } else {
                        $specialKwSelected = [];
                    }
                }
                $specialKwSelected = is_array($specialKwSelected) ? $specialKwSelected : [];

                $legacyCategoryDefault = old('legacy_category_id', $fil['legacy_category_id'] ?? $publishedDesign?->category_id ?? '');
                $canonicalLinkDefault = old('canonical_link', $fil['canonical_link'] ?? $publishedDesign?->canonical_link ?? '');

                $formStrId = $designerSeoStringId;
                $idNameStripPrefixes = $designerSeoIdNameStripPrefixes;
                $dbSegRaw = (string) ($seo?->id_name ?? '');
                $idSegFromDb = $dbSegRaw;
                foreach ($idNameStripPrefixes as $pfx) {
                    if ($pfx !== '' && Str::startsWith($idSegFromDb, $pfx . '-')) {
                        $idSegFromDb = substr($idSegFromDb, strlen($pfx) + 1);
                        break;
                    }
                }
                $hasPersistedIdSegment = $idSegFromDb !== '';
                $idSegForField = old('id_name') !== null ? (string) old('id_name') : $dbSegRaw;
                foreach ($idNameStripPrefixes as $pfx) {
                    if ($pfx !== '' && Str::startsWith($idSegForField, $pfx . '-')) {
                        $idSegForField = substr($idSegForField, strlen($pfx) + 1);
                        break;
                    }
                }
                $idNameFieldValue = $idSegForField !== '' ? ($formStrId . '-' . $idSegForField) : '';
                $seoUser = auth()->user();
                $canEditIdName = $seoUser && ($roleManager::isAdminOrSeoManager($seoUser->user_type) || $roleManager::isSeoExecutive($seoUser->user_type));

                $thumbDisplayPost = $seo?->post_thumb ?? $publishedDesign?->post_thumb ?? null;
                $thumbDisplayAdditional = $seo?->additional_thumb ?? $publishedDesign?->additional_thumb ?? null;
                $storageBase = rtrim((string) config('filesystems.storage_url', ''), '/');

                $newCategoryIdValue = (int) old('new_category_id', $seo?->primary_category_id ?? $design->category_id ?? 0);
                $dataArray = ['item' => ['new_category_id' => $newCategoryIdValue]];
                $datas = ['cat' => (object) ['parent_category_id' => $selectCategory?->parent_category_id]];
            @endphp

            @if($design->status === 'rejected_by_seo')
                <div class="alert alert-warning mb-20">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Rejected by SEO.</strong> Fix fields, <strong>Save SEO</strong>, then <strong>Reapply</strong>.
                </div>
            @endif
            <form id="designer_seo_form" method="POST" enctype="multipart/form-data"
                action="{{ route('designer_system.design.seo.update', $design->id) }}">
                @csrf
                @method('PUT')

                <div class="row mb-20">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Post Thumb</h6>
                            <input type="file" class="form-control-file form-control post-thumb-input" name="post_thumb"
                                id="post_thumb" accept="image/*" onchange="designerSeoPreviewPostThumb(event)">
                            <br>
                            @if(!empty($thumbDisplayPost))
                                <img id="postThumbPreview" src="{{ $storageBase }}/{{ ltrim($thumbDisplayPost, '/') }}?v={{ time() }}"
                                    style="max-width:120px;max-height:120px;border:1px solid #ddd;padding:2px;border-radius:4px;"
                                    onerror="this.style.display='none';">
                                <p class="small text-muted mb-0">Saved: {{ basename($thumbDisplayPost) }}</p>
                            @else
                                <img id="postThumbPreview"
                                    style="max-width:120px;max-height:120px;border:1px solid #ddd;padding:2px;border-radius:4px;display:none;">
                                <p id="noPostThumbMsg" class="small text-muted mb-0">No post thumb yet — upload or use publish
                                    defaults.</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Additional Thumbnail</h6>
                            <input type="file" class="form-control-file form-control" name="additional_thumb"
                                id="additional_thumb" accept="image/*" onchange="designerSeoPreviewAdditionalThumb(event)">
                            <input type="hidden" name="remove_additional_thumb" id="remove_additional_thumb" value="0">
                            <br>
                            @if(!empty($thumbDisplayAdditional))
                                <img id="additionalThumbPreview"
                                    src="{{ $storageBase }}/{{ ltrim($thumbDisplayAdditional, '/') }}?v={{ time() }}"
                                    style="max-width:120px;max-height:120px;border:1px solid #ddd;padding:2px;border-radius:4px;"
                                    onerror="this.style.display='none';">
                                <p class="small text-muted mb-0">Saved: {{ basename($thumbDisplayAdditional) }}</p>
                                <button type="button" class="btn btn-danger btn-sm mt-5" onclick="designerSeoRemoveAdditionalThumb()">Remove
                                    Additional Thumb</button>
                            @else
                                <img id="additionalThumbPreview"
                                    style="max-width:120px;max-height:120px;border:1px solid #ddd;padding:2px;border-radius:4px;display:none;">
                                <p id="noAdditionalThumbMsg" class="small text-muted mb-0">No additional thumbnail.</p>
                                <button type="button" class="btn btn-danger btn-sm mt-5" style="display:none;"
                                    id="removeAdditionalThumbBtn" onclick="designerSeoRemoveAdditionalThumb()">Remove Additional
                                    Thumb</button>
                            @endif
                        </div>
                    </div>
                </div>

                <h6>SEO Row</h6>
                <hr>
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <h6>Post Name</h6>
                            <input type="text" class="form-control" id="post_name" name="post_name" maxlength="60"
                                value="{{ old('post_name', $seo?->post_name ?? '') }}"
                                data-strid="{{ $formStrId }}"
                                placeholder="Display post name"
                                autocomplete="off"
                                oninput="window.designerSeoSyncPostNameAndIdName && window.designerSeoSyncPostNameAndIdName()"
                                onchange="window.designerSeoSyncPostNameAndIdName && window.designerSeoSyncPostNameAndIdName()"
                                onpaste="setTimeout(function(){ window.designerSeoSyncPostNameAndIdName && window.designerSeoSyncPostNameAndIdName(); }, 0)">
                            <small id="postNameCounter" class="text-muted">60 remaining of 60 letters</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <h6>ID Name</h6>
                            @if ($canEditIdName)
                                <input type="text" class="form-control" id="id_name" name="id_name" maxlength="280"
                                    value="{{ $idNameFieldValue }}"
                                    placeholder="{{ $formStrId }}-your-slug">
                            @else
                                @if ($hasPersistedIdSegment)
                                    <input type="text" class="form-control" id="id_name" name="id_name" maxlength="280"
                                        value="{{ $idNameFieldValue }}" readonly>
                                @else
                                    <input type="text" class="form-control" id="id_name" name="id_name" maxlength="280"
                                        value="{{ $idNameFieldValue }}"
                                        placeholder="{{ $formStrId }}-your-slug">
                                @endif
                            @endif
            
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Special Keywords</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="special_keywords[]" id="special_keywords">
                                    @foreach($specialKeywords as $keyword)
                                        <option value="{{ $keyword->id }}" {{ in_array((int) $keyword->id, $specialKwSelected, true) ? 'selected' : '' }}>
                                            {{ $keyword->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                           
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>H2 tag</h6>
                            <input type="text" class="form-control" id="h2_tag" name="h2_tag" maxlength="255"
                                value="{{ old('h2_tag', $seo?->h2_tag ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Canonical link</h6>
                            <input type="text" class="form-control canonical_link" id="canonical_link" name="canonical_link"
                                maxlength="2048" value="{{ $canonicalLinkDefault }}"
                                placeholder="Optional — default on publish if empty">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Meta title</h6>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="255"
                                value="{{ old('meta_title', $seo?->meta_title ?? $design->title) }}"
                                placeholder="Required before publish">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Description</h6>
                            <textarea class="form-control" id="description" name="description" rows="8"
                                style="min-height:120px;">{{ old('description', $seo?->description ?? $design->description ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Meta description</h6>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="8"
                                maxlength="2000" style="min-height:120px;"
                                placeholder="Required before publish">{{ old('meta_description', $seo?->meta_description ?? '') }}</textarea>
                            <small class="text-muted">Up to 2000 characters</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <h6>Categories &amp; Related Tag Row</h6>
                        <hr>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Categories</h6>
                            <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                name="legacy_category_id" id="legacy_category_id">
                                <option value="">— none —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) $legacyCategoryDefault === (string) $cat->id ? 'selected' : '' }}>
                                        {{ $cat->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Search Tags</h6>
                            <div class="col-sm-20" id="relatedKeyword">
                                <input type="text" data-role="tagsinput" class="form-control" id="keywords"
                                    name="keywords" placeholder="Add tags" value="{{ old('keywords', $kwDefault) }}"
                                    autocomplete="on">
                            </div>
                        </div>
                    </div>

                    @php
                        $isRestrictedCat = auth()->check() && $roleManager::isSeoExecutiveOrIntern(auth()->user()->user_type);
                    @endphp
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group category-dropbox-wrap">
                            <h6>Select New Category</h6>
                            {{-- Same structure as item/edit_seo_raw (edit_seo_item) --}}
                            <div class="input-subcategory-dropbox unset-bottom-border {{ $isRestrictedCat ? 'disabled-category' : '' }}"
                                id="parentCategoryInput"
                                @if($isRestrictedCat) style="pointer-events: none; opacity: 0.6;" @endif>
                                <span>
                                    @if(!empty($selectCategory) && isset($selectCategory->category_name))
                                        {{ $selectCategory->category_name }}
                                    @else
                                        {{ '== none ==' }}
                                    @endif
                                </span>
                                <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                            </div>
                            <div class="custom-dropdown parent-category-input"
                                @if($isRestrictedCat) style="pointer-events: none; opacity: 0.6;" @endif>
                                <input type="text" id="categoryFilter" class="form-control-file form-control"
                                    placeholder="Search categories">
                                <ul class="dropdown-menu-ul filter-wrap-list">
                                    <li class="category none-option">== none ==</li>
                                    @foreach($allCategories as $category)
                                        @php
                                            $classBold = !empty($category->subcategories) && $category->subcategories->count() > 0
                                                ? 'has-children'
                                                : 'has-parent';
                                            $selected = (int) ($dataArray['item']['new_category_id'] ?? 0) === (int) $category->id ? 'selected' : '';
                                        @endphp
                                        <li class="category {{ $classBold }} {{ $selected }}" data-id="{{ $category->id }}"
                                            data-catname="{{ $category->category_name }}">
                                            <span>{{ $category->category_name }}</span>
                                            @if(!empty($category->subcategories) && $category->subcategories->count() > 0)
                                                <ul class="subcategories">
                                                    @foreach($category->subcategories as $subcategory)
                                                        @include('partials.subcategory-optgroup', [
                                                            'subcategory' => $subcategory,
                                                            'sub_category_id' => $subcategory->id,
                                                            'sub_category_name' => $subcategory->category_name,
                                                        ])
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <input type="hidden" name="new_category_id" class="new_cat_id_item"
                                value="{{ $newCategoryIdValue > 0 ? $newCategoryIdValue : '0' }}"
                                @if($isRestrictedCat) readonly @endif>
                            <div class="popup-container" id="newCategoryRequiredPopup">
                                <p><span class="required-icon">!</span>Please select a new category.</p>
                            </div>
                            @error('new_category_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @if($isRestrictedCat)
                                <small class="text-danger">You are not allowed to change the category (SEO
                                    Executive/Intern).</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Sub Category Tags</h6>
                            <div class="col-sm-20" id="newKeywordsCols">
                                <input type="text" data-role="tagsinput" class="form-control new_key_words @error('new_keywords') is-invalid @enderror"
                                    id="newKeywords" name="new_keywords" placeholder="Add Sub Category tags"
                                    autocomplete="on" list="keywordsList" required=""
                                    value="{{ $newKeywordsValue }}">
                                <datalist id="keywordsList"></datalist>
                            </div>
                            @error('new_keywords')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Tags are saved under the selected primary category. Select a
                                <strong>child</strong> category first.</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <br>
                        <h6>Filter Row</h6>
                        <hr>
                    </div>

                    {{-- Languages --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Languages</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    name="lang_id[]" id="lang_id" multiple required>
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->id }}" {{ in_array((int) $lang->id, $langSelected, true) ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Theme --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Theme</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" id="theme_id" name="theme_id[]" required>
                                    @foreach($themes as $theme)
                                        <option value="{{ $theme->id }}" {{ in_array((int) $theme->id, $themeSelected, true) ? 'selected' : '' }}>
                                            {{ $theme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Style --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Style</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="styles[]" id="styles" required>
                                    @foreach($styles as $style)
                                        <option value="{{ $style->id }}" {{ in_array((int) $style->id, $stylesSelected, true) ? 'selected' : '' }}>
                                            {{ $style->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Orientation --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Orientation</h6>
                            <div class="col-sm-20">
                                <select class="form-control" data-style="btn-outline-primary" id="orientation"
                                    name="orientation" required>
                                    <option value="portrait" {{ $orientationVal === 'portrait' ? 'selected' : '' }}>
                                        Portrait</option>
                                    <option value="landscape" {{ $orientationVal === 'landscape' ? 'selected' : '' }}>
                                        Landscape</option>
                                    <option value="square" {{ $orientationVal === 'square' ? 'selected' : '' }}>Square
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Size --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Size</h6>
                            <select name="template_size" id="template_size" class="form-control">
                                <option value="">== none ==</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}" {{ (string) ($templateSizeVal ?? '') === (string) $size->id ? 'selected' : '' }}>
                                        {{ $size->size_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Religion --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Religion</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" name="religion_id[]" id="religion_id">
                                    @foreach($religions as $religion)
                                        <option value="{{ $religion->id }}" {{ in_array((int) $religion->id, $religionSelected, true) ? 'selected' : '' }}>
                                            {{ $religion->religion_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Interest --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Interest</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" id="interest_id" name="interest_id[]">
                                    @foreach($interests as $interest)
                                        <option value="{{ $interest->id }}" {{ in_array((int) $interest->id, $interestSelected, true) ? 'selected' : '' }}>
                                            {{ $interest->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Is Premium --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Is Premium</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                    name="is_premium" id="is_premium">
                                    <option value="1" {{ $isPremiumSel === '1' ? 'selected' : '' }}>TRUE</option>
                                    <option value="0" {{ $isPremiumSel !== '1' ? 'selected' : '' }}>FALSE</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Is Freemium --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Is Freemium</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                    name="is_freemium" id="is_freemium">
                                    <option value="1" {{ $isFreemiumSel === '1' ? 'selected' : '' }}>TRUE</option>
                                    <option value="0" {{ $isFreemiumSel !== '1' ? 'selected' : '' }}>FALSE</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Select Date Range</h6>
                            <input class="form-control datetimepicker-range" placeholder="Select Date" type="text"
                                name="date_range" id="date_range" value="{{ $dateRangeValue }}" readonly>
                        </div>
                    </div>

                    {{-- Colors (same ids/markup as item/edit_seo_raw) --}}
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Colors</h6>
                            <div class="col-sm-20 color_tags">
                                <input type="text" id="colorTags" class="form-control" name="color_ids" value="">
                                <input type="text" id="colorPicker" class="form-control mt-3" aria-label="Color picker">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <br>
                        <h6>Others Row</h6>
                        <hr>
                    </div>

                    {{-- Others row: ratio, width, height, status (edit_seo_item layout) --}}
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Ratio</h6>
                            <input class="form-control" id="ratio_field" type="text" name="ratio_field"
                                value="{{ $ratioDefault }}" required>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Width</h6>
                            <input class="form-control" id="width" type="number" name="width" min="0"
                                value="{{ old('width', $seo?->width ?? $design->width ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Height</h6>
                            <input class="form-control" id="height" type="number" name="height" min="0"
                                value="{{ old('height', $seo?->height ?? $design->height ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Status</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                    name="status_field" id="status_field">
                                    <option value="1" {{ (string) $statusLiveDefault === '1' ? 'selected' : '' }}>LIVE
                                    </option>
                                    <option value="0" {{ (string) $statusLiveDefault === '0' ? 'selected' : '' }}>NOT LIVE
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <input class="btn btn-primary" type="submit" name="submit" value="Save SEO">
                        <span class="text-muted font-14 ml-2">Creates <code>design_seo_details</code> if
                            missing.</span>
                        @if($design->status === 'pending_seo')
                            <span class="text-muted font-14 ml-2">Then
                                <a href="#seo-final-decision">Approve / Reject →</a></span>
                        @endif
                    </div>
                </div>
            </form>

            @if($design->status == 'live' && $design->crafty_design_id)
                <br>
                <h6>Published</h6>
                <hr>
                <p class="mb-15">This design is live in <strong>crafty_db.designs</strong>.</p>
                <a href="{{ url('edit_seo_item/' . $design->crafty_design_id) }}" class="btn btn-primary mb-20"
                    target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open template SEO editor
                    (edit_seo_item)</a>
            @endif

            @if($design->status == 'pending_seo')
                <br>
                <h6 id="seo-final-decision" style="scroll-margin-top: 5rem;">SEO manager — approve or reject</h6>
                <hr>
                <div class="alert alert-info small mb-20">
                    <i class="fa fa-lightbulb-o"></i> Save SEO above first (meta title &amp; meta description required).
                    <strong>Approve &amp; publish</strong> writes <code>crafty_db.designs</code> and sets status
                    <strong>Live</strong>.
                </div>
                <div class="row">
                    <div class="col-lg-6 col-sm-12 mb-20">
                        <div class="form-group">
                            <h6 class="text-success"><i class="fa fa-check-circle"></i> Approve (SEO)</h6>
                            <form method="POST" action="{{ route('designer_system.design.publish', $design->id) }}"
                                onsubmit="return confirm('Approve and create/update the crafty_db.designs row?');">
                                @csrf
                                <div class="form-group">
                                    <h6>Internal notes <small class="text-muted">(optional)</small></h6>
                                    <textarea id="notes_pub" name="notes" class="form-control" rows="2"
                                        placeholder="Optional audit notes">{{ old('notes') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-check"></i>
                                    Approve &amp; publish to designs table</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 mb-20">
                        <div class="form-group">
                            <h6 class="text-danger"><i class="fa fa-times-circle"></i> Reject (SEO)</h6>
                            <form method="POST" action="{{ route('designer_system.design.reject_seo', $design->id) }}"
                                onsubmit="return confirm('Reject this design for SEO?');">
                                @csrf
                                <div class="form-group">
                                    <h6>Reason <span class="text-danger">*</span></h6>
                                    <textarea id="reject_notes" name="notes" class="form-control" rows="4" required
                                        minlength="10"
                                        placeholder="Min. 10 characters — what should the freelancer fix?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-block"><i class="fa fa-ban"></i> Reject
                                    submission</button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($design->status == 'rejected_by_seo')
                @if($design->seo_head_notes)
                    <br>
                    <h6>Rejection reason</h6>
                    <hr>
                    <div class="alert alert-danger mb-20">
                        <strong>Reason:</strong> {{ $design->seo_head_notes }}
                    </div>
                @endif

                <br>
                <h6>Reapply for SEO approval</h6>
                <hr>
                <div class="alert alert-info small mb-20">
                    <i class="fa fa-lightbulb-o"></i> Fix issues, <strong>Save SEO</strong>, then reapply below.
                </div>
                <form method="POST" action="{{ route('designer_system.design.reapply_seo', $design->id) }}"
                    onsubmit="return confirm('Reapply this design for SEO approval?');">
                    @csrf
                    <div class="form-group">
                        <h6>Changes made <small class="text-muted">(optional)</small></h6>
                        <textarea id="reapply_notes" name="notes" class="form-control" rows="3"
                            placeholder="What you fixed...">{{ old('notes') }}</textarea>
                    </div>
                    <input class="btn btn-primary" type="submit" name="reapply" value="Reapply for SEO approval">
                </form>
            @elseif($design->status != 'live' && $design->status != 'pending_seo')
                <br>
                <div class="alert alert-secondary mb-0">
                    <i class="fa fa-clock-o"></i> SEO approve/reject is available when status is
                    <strong>pending_seo</strong>. Current: <code>{{ $design->status }}</code>.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Select2 base styles: core.css + style.css + masterhead (do not add select2.min.css here — it overrides chips to
gray). --}}
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@include('layouts.masterscript')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>

<script>
    (function () {
        function designerSeoSyncPostNameAndIdName() {
            var post = document.getElementById('post_name');
            var idName = document.getElementById('id_name');
            var counter = document.getElementById('postNameCounter');
            if (!post) {
                return;
            }
            var max = parseInt(post.getAttribute('maxlength'), 10) || 60;
            var len = post.value ? post.value.length : 0;
            if (counter) {
                counter.textContent = (max - len) + ' remaining of ' + max + ' letters';
            }
            if (!idName) {
                return;
            }
            var strId = post.getAttribute('data-strid');
            if (strId === null || strId === '') {
                return;
            }
            var seg = String(post.value || '').toLowerCase().replace(/\s+/g, '-');
            idName.value = String(strId) + '-' + seg;
        }

        window.designerSeoSyncPostNameAndIdName = designerSeoSyncPostNameAndIdName;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', designerSeoSyncPostNameAndIdName);
        } else {
            designerSeoSyncPostNameAndIdName();
        }
        window.addEventListener('load', designerSeoSyncPostNameAndIdName);
    })();

    function designerSeoPreviewPostThumb(event) {
        var input = event.target;
        var preview = document.getElementById('postThumbPreview');
        var noMsg = document.getElementById('noPostThumbMsg');
        if (input.files && input.files[0] && preview) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.style.display = 'block';
            if (noMsg) {
                noMsg.style.display = 'none';
            }
        }
    }

    function designerSeoPreviewAdditionalThumb(event) {
        var input = event.target;
        var preview = document.getElementById('additionalThumbPreview');
        var noMsg = document.getElementById('noAdditionalThumbMsg');
        var btn = document.getElementById('removeAdditionalThumbBtn');
        if (input.files && input.files[0] && preview) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.style.display = 'block';
            if (noMsg) {
                noMsg.style.display = 'none';
            }
            if (btn) {
                btn.style.display = 'inline-block';
            }
            var h = document.getElementById('remove_additional_thumb');
            if (h) {
                h.value = '0';
            }
        }
    }

    function designerSeoRemoveAdditionalThumb() {
        var input = document.getElementById('additional_thumb');
        var preview = document.getElementById('additionalThumbPreview');
        var btn = document.getElementById('removeAdditionalThumbBtn');
        var h = document.getElementById('remove_additional_thumb');
        if (input) {
            input.value = '';
        }
        if (preview) {
            preview.style.display = 'none';
            preview.removeAttribute('src');
        }
        if (btn) {
            btn.style.display = 'none';
        }
        if (h) {
            h.value = '1';
        }
    }

    jQuery(function ($) {
        $('select.custom-select2').select2({
            placeholder: 'Select options',
            allowClear: true,
            width: '100%'
        });

        (function designerSeoInitCategoryDropboxEarly() {
            function designerSeoAjaxLoading(on) {
                var el = document.getElementById('main_loading_screen');
                if (el) {
                    el.style.display = on ? 'block' : 'none';
                }
            }

            function designerSeoReloadSelect2($sel) {
                if ($sel.data('select2')) {
                    $sel.select2('destroy');
                }
                $sel.select2({ placeholder: 'Select options', allowClear: true, width: '100%' });
            }

            var loadNewSearchKeywords = function (newCatId) {
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });
                $.ajax({
                    url: "{{ route('getNewSearchTag') }}",
                    type: 'POST',
                    data: { cateId: newCatId },
                    beforeSend: function () {
                        designerSeoAjaxLoading(true);
                    },
                    success: function (data) {
                        designerSeoAjaxLoading(false);
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else if (data.success) {
                            $('#keywordsList').empty();
                            data.success.forEach(function (tag) {
                                $('#keywordsList').append($('<option>').attr('value', tag.name));
                            });
                        }
                    },
                    error: function (err) {
                        designerSeoAjaxLoading(false);
                        window.alert(err.responseText || 'Request failed');
                    }
                });
            };

            var loadDesignerSizeList = function (newCatId) {
                var $sel = $('#template_size');
                $sel.html('<option value="">== none ==</option>');
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });
                $.ajax({
                    url: "{{ route('getSizeList') }}",
                    type: 'POST',
                    data: { cateId: newCatId },
                    beforeSend: function () {
                        designerSeoAjaxLoading(true);
                    },
                    success: function (data) {
                        designerSeoAjaxLoading(false);
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else if (data.success && data.data) {
                            data.data.forEach(function (size) {
                                $sel.append('<option value="' + size.id + '">' + size.size_name + '</option>');
                            });
                        }
                    },
                    error: function (err) {
                        designerSeoAjaxLoading(false);
                        window.alert(err.responseText || 'Request failed');
                    }
                });
            };

            var loadDesignerThemeList = function (newCatId) {
                var $sel = $('#theme_id');
                $sel.html('');
                $.ajax({
                    url: "{{ route('getThemeList') }}",
                    type: 'POST',
                    data: { cateId: newCatId },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    beforeSend: function () {
                        designerSeoAjaxLoading(true);
                    },
                    success: function (data) {
                        designerSeoAjaxLoading(false);
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else if (data.success && data.data) {
                            data.data.forEach(function (theme) {
                                $sel.append('<option value="' + theme.id + '">' + theme.name + '</option>');
                            });
                            designerSeoReloadSelect2($sel);
                        }
                    },
                    error: function (err) {
                        designerSeoAjaxLoading(false);
                        window.alert(err.responseText || 'Request failed');
                    }
                });
            };

            var loadDesignerInterestList = function (newCatId) {
                var $sel = $('#interest_id');
                $sel.html('');
                $.ajax({
                    url: "{{ route('getInterestList') }}",
                    type: 'POST',
                    data: { cateId: newCatId },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    beforeSend: function () {
                        designerSeoAjaxLoading(true);
                    },
                    success: function (data) {
                        designerSeoAjaxLoading(false);
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else if (data.success && data.data) {
                            data.data.forEach(function (interest) {
                                $sel.append('<option value="' + interest.id + '">' + interest.name + '</option>');
                            });
                            designerSeoReloadSelect2($sel);
                        }
                    },
                    error: function (err) {
                        designerSeoAjaxLoading(false);
                        window.alert(err.responseText || 'Request failed');
                    }
                });
            };

            var $catWrap = $('.form-group.category-dropbox-wrap');
            var ncVal = $("input[name='new_category_id']").val();
            if (ncVal && ncVal !== '0') {
                loadNewSearchKeywords(ncVal);
            }

            $('#designer_seo_form').on('submit', function (e) {
                var v = $("input[name='new_category_id']").val();
                if (!v || v === '0') {
                    e.preventDefault();
                    $('#newCategoryRequiredPopup').show();
                    return false;
                }
                $('#newCategoryRequiredPopup').hide();
            });

            $(document).on('click', '#parentCategoryInput', function () {
                $('#newCategoryRequiredPopup').hide();
                var $dd = $catWrap.find('.parent-category-input');
                if ($dd.hasClass('show')) {
                    $dd.removeClass('show');
                    $catWrap.removeClass('category-dropdown-open');
                } else {
                    $dd.addClass('show');
                    $catWrap.addClass('category-dropdown-open');
                }
            });

            $(document).on('click', '.form-group.category-dropbox-wrap .category', function (event) {
                if ($(event.target).closest('li.subcategory').length) {
                    return;
                }
                event.stopPropagation();
                $catWrap.find('.category').removeClass('selected');
                $catWrap.find('.subcategory').removeClass('selected');
                var id = $(this).data('id');
                $("input[name='new_category_id']").val(id);
                $('#parentCategoryInput span').html($(this).data('catname'));
                $catWrap.find('.parent-category-input').removeClass('show');
                $catWrap.removeClass('category-dropdown-open');
                $(this).addClass('selected');

                $('#keywordsList').empty();
                if (id) {
                    loadNewSearchKeywords(id);
                    loadDesignerSizeList(id);
                    loadDesignerThemeList(id);
                    loadDesignerInterestList(id);
                }
            });

            $(document).on('click', '.form-group.category-dropbox-wrap .subcategory', function (event) {
                event.stopPropagation();
                $catWrap.find('.category').removeClass('selected');
                $catWrap.find('.subcategory').removeClass('selected');
                var id = $(this).data('id');
                $("input[name='new_category_id']").val(id);
                $catWrap.find('.parent-category-input').removeClass('show');
                $catWrap.removeClass('category-dropdown-open');
                $('#parentCategoryInput span').html($(this).data('catname'));
                $(this).addClass('selected');

                $('#keywordsList').empty();
                if (id) {
                    loadNewSearchKeywords(id);
                    loadDesignerSizeList(id);
                    loadDesignerThemeList(id);
                    loadDesignerInterestList(id);
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
                    $catWrap.find('.parent-category-input.show').removeClass('show');
                    $catWrap.removeClass('category-dropdown-open');
                }
            });

            $(document).on('click', '.form-group.category-dropbox-wrap li.category.none-option', function () {
                $("input[name='new_category_id']").val('0');
                $catWrap.find('.parent-category-input').removeClass('show');
                $catWrap.removeClass('category-dropdown-open');
                $('#parentCategoryInput span').html('== none ==');
                $('#keywordsList').empty();
            });

            $catWrap.find('#categoryFilter').on('input', function () {
                var filterValue = $(this).val().toLowerCase();
                $catWrap.find('.category, .subcategory').each(function () {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(filterValue) > -1);
                });
            });

            $(document).on('click', '#newKeywordsCols input[type="text"]', function () {
                $('.error-message.new-cat-hint').remove();
                if ($("input[name='new_category_id']").val() === '0' || $("input[name='new_category_id']").val() === '') {
                    $('#newKeywords').after(
                        '<span class="error-message new-cat-hint" style="color:red;">Please select a new category.</span>');
                }
            });
        })();

        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('.datetimepicker-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });
            $('.datetimepicker-range').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });
            $('.datetimepicker-range').on('cancel.daterangepicker', function () {
                $(this).val('');
            });
            var drVal = $('#date_range').val();
            if (drVal && drVal.indexOf(' - ') !== -1) {
                var p = drVal.split(' - ');
                var dr = $('#date_range').data('daterangepicker');
                if (dr && p.length === 2) {
                    var m0 = moment(p[0].trim(), ['MM/DD/YYYY', 'YYYY-MM-DD', moment.ISO_8601], true);
                    var m1 = moment(p[1].trim(), ['MM/DD/YYYY', 'YYYY-MM-DD', moment.ISO_8601], true);
                    if (m0.isValid() && m1.isValid()) {
                        dr.setStartDate(m0);
                        dr.setEndDate(m1);
                    }
                }
            }
        }

        var colorIds = @json($colorIdsForJs);

        function parseHexRgb(hex) {
            if (!hex || typeof hex !== 'string') {
                return null;
            }
            var h = hex.trim();
            if (h.indexOf('#') === 0) {
                h = h.slice(1);
            }
            if (h.length === 3) {
                h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
            }
            if (h.length !== 6 && h.length !== 8) {
                return null;
            }
            var n = parseInt(h.slice(0, 6), 16);
            if (isNaN(n)) {
                return null;
            }
            return {
                r: (n >> 16) & 255,
                g: (n >> 8) & 255,
                b: n & 255
            };
        }

        function textColorForBg(hex) {
            var rgb = parseHexRgb(hex);
            if (!rgb) {
                return '#111';
            }
            var yiq = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;

            return yiq >= 186 ? '#111111' : '#ffffff';
        }

        function styleColorTagEl(el, hex) {
            if (!el || !hex) {
                return;
            }
            var $el = $(el);
            $el.css({
                'background-color': hex,
                color: textColorForBg(hex)
            });
            $el.find('[data-role="remove"]').css('color', textColorForBg(hex));
        }

        function setTagBackgroundColor(color) {
            var tagElements = $('.color_tags .bootstrap-tagsinput .tag');
            var lastTagElement = tagElements[tagElements.length - 1];
            if (lastTagElement) {
                styleColorTagEl(lastTagElement, color);
            }
        }

        function syncColorIdsFromTags() {
            var colorsCode = [];
            $('.color_tags .bootstrap-tagsinput .tag').each(function () {
                var t = $(this).clone().children().remove().end().text().trim();
                if (t) {
                    colorsCode.push(t);
                }
            });
            $("input[name='color_ids']").val(colorsCode.join(','));
        }

        var currentTag = null;
        var currentColor = null;

        $('#colorTags').tagsinput({
            confirmKeys: [13, 32, 188]
        });

        colorIds.forEach(function (hex) {
            if (hex) {
                $('#colorTags').tagsinput('add', hex);
                setTagBackgroundColor(hex);
            }
        });

        $('.color_tags .bootstrap-tagsinput input[type="text"]').prop('readonly', true).css('min-width', '417px').attr('size', '40');
        $('.color_tags .bootstrap-tagsinput input[type="text"]').on('keydown keyup keypress', function (event) {
            event.preventDefault();
            $(this).attr('size', '40');
        });

        $("#colorPicker").spectrum({
            color: '#f00',
            showInput: true,
            showPalette: false,
            showAlpha: true,
            change: function (color) {
                var colorHex = color.toHexString();
                if (currentTag) {
                    var $tag = $(currentTag);
                    $tag.contents().filter(function () {
                        return this.nodeType === 3;
                    }).remove();
                    $tag.prepend(document.createTextNode(colorHex));
                    styleColorTagEl(currentTag, colorHex);
                    syncColorIdsFromTags();
                } else {
                    $('#colorTags').tagsinput('add', colorHex);
                    setTagBackgroundColor(colorHex);
                }
                currentTag = null;
            }
        });

        $('#colorTags').on('itemAdded', function (event) {
            var hex = typeof event.item === 'string' ? event.item : String(event.item || '');
            if (hex) {
                setTagBackgroundColor(hex);
            }
            syncColorIdsFromTags();
        });
        $('#colorTags').on('itemRemoved', function () {
            syncColorIdsFromTags();
        });

        $('.color_tags .bootstrap-tagsinput .tag').each(function () {
            var t = $(this).clone().children().remove().end().text().trim();
            if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(t)) {
                styleColorTagEl(this, t);
            }
        });

        $(document).on('click', '.color_tags .bootstrap-tagsinput .tag', function () {
            currentTag = this;
            currentColor = $(this).css('background-color');
            $('#colorPicker').spectrum('set', currentColor);
            $('#colorPicker').spectrum('show');
        });

        $('.bootstrap-tagsinput').sortable({
            items: '> .tag',
            axis: 'x',
            containment: 'parent',
            tolerance: 'pointer',
            cursor: 'move',
            distance: 5,
            helper: 'clone',
            start: function (event, ui) {
                $(this).find('input').prop('disabled', true);
                ui.helper.addClass('sorting');
            },
            stop: function (event, ui) {
                $(this).find('input').prop('disabled', false);
                $(ui.item).removeClass('sorting');
                $(this).sortable('refreshPositions');
                syncColorIdsFromTags();
            },
            sort: function (event, ui) {
                ui.helper.css('transform', 'scale(1.1)');
            }
        });

        $('.select2-selection__rendered').sortable({
            placeholder: 'ui-state-highlight',
            stop: function () {
                var selectionContainer = $(this);
                var newOrder = [];
                selectionContainer.find('.select2-selection__choice').each(function () {
                    newOrder.push($(this).attr('title'));
                });
                var selectElement = selectionContainer.closest('.select2-container').prevAll('select').first();
                if (selectElement.length) {
                    selectElement.val(newOrder).trigger('change');
                }
            }
        }).disableSelection();

        if ($('#keywords').length && $('#relatedKeyword .bootstrap-tagsinput').length === 0) {
            $('#keywords').tagsinput({
                trimValue: true,
                confirmKeys: [13, 44]
            });
        }

        $('#newKeywords').tagsinput({
            trimValue: true,
            confirmKeys: [13, 44]
        });

        $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr('list', 'keywordsList');
        $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr('style',
            'width: 100%; height: 45px; border: 1px solid rgb(0, 0, 0); border-radius: 5px; margin-top: 5px;'
        );

        @if($design->width && $design->height)
            if (!$('#width').val()) {
                $('#width').val('{{ $design->width }}');
            }
            if (!$('#height').val()) {
                $('#height').val('{{ $design->height }}');
            }
        @endif
    });
</script>
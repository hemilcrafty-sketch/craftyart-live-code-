@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<style>
    /* Modern Card Design */
    .seo-edit-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 25px;
    }

    /* Section Headers */
    .section-header {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
    }

    .section-header::before {
        content: '';
        width: 4px;
        height: 24px;
        background: #0059b2;
        margin-right: 12px;
        border-radius: 2px;
    }

    /* Form Labels */
    .form-group h6 {
        font-size: 14px;
        font-weight: 600;
        color: #4a4a4a;
        margin-bottom: 8px;
    }

    /* Form Controls */
    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #0059b2;
        box-shadow: 0 0 0 3px rgba(0, 89, 178, 0.1);
        outline: none;
    }

    /* Buttons */
    .btn-primary {
        background: #0059b2;
        border: none;
        border-radius: 6px;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #004a94;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 89, 178, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 6px;
        padding: 12px 28px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    /* Tags Input */
    .bootstrap-tagsinput {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 12px;
        min-height: 45px;
        width: 100%;
        display: block;
    }

    .bootstrap-tagsinput .tag {
        background: #0059b2;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        margin-right: 6px;
        margin-bottom: 6px;
        font-size: 13px;
        display: inline-block;
    }

    .bootstrap-tagsinput input {
        border: none;
        box-shadow: none;
        outline: none;
        background-color: transparent;
        padding: 6px;
        margin: 0;
        width: auto;
        max-width: inherit;
        min-width: 150px;
    }

    .bootstrap-tagsinput .tag [data-role="remove"] {
        margin-left: 8px;
        cursor: pointer;
        opacity: 0.8;
    }

    .bootstrap-tagsinput .tag [data-role="remove"]:hover {
        opacity: 1;
    }

    /* Autocomplete Dropdown Styling */
    .custom-autocomplete-dropdown {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 250px;
        overflow-y: auto;
        margin-top: 4px;
        width: 100%;
    }

    .autocomplete-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.2s ease;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover {
        background: #f8f9fa;
        color: #0059b2;
    }

    /* Form Group for Tags */
    .form-group {
        position: relative;
        margin-bottom: 20px;
    }

    .form-group small.text-muted {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #6c757d;
    }

    /* Select Boxes */
    .selectpicker,
    .custom-select2 {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
    }

    /* Textarea */
    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* Row Spacing */
    .row {
        margin-bottom: 15px;
    }

    /* HR Styling */
    hr {
        border: 0;
        height: 1px;
        background: #f0f0f0;
        margin: 25px 0;
    }

    /* Action Buttons Container */
    .action-buttons {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 12px;
    }

    /* Color Picker Container */
    .color_tags {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #colorPicker {
        width: 50px;
        height: 45px;
        padding: 4px;
        cursor: pointer;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    #colorPicker:hover {
        border-color: #0059b2;
        box-shadow: 0 0 0 3px rgba(0, 89, 178, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .seo-edit-card {
            padding: 20px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
        }
    }
</style>
<div class="main-container seo-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="seo-edit-card flex-column">

                <div class="mb-10"><video src="{{$contentManager::getStorageLink($dataArray['item']->video_url)}}" width="300" controls /></div>

                <form id="editVideoSeoForm" enctype="multipart/form-data">
                    @csrf
                    @php
                        $isSeoManagerAccess = $roleManager::isAdminOrSeoManager(Auth::user()->user_type);
                        $restrictSeoExecInternBasicSeo = in_array((int) Auth::user()->user_type, [
                            \App\Enums\UserRole::SEO_EXECUTIVE->id(),
                            \App\Enums\UserRole::SEO_INTERN->id(),
                        ], true);
                    @endphp
                    <input type="hidden" name="id" value="{{ $dataArray['item']->id }}">

                    <div class="section-header">Basic Information</div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Video Name</h6>
                                <input class="form-control" type="text" value="{{ $dataArray['item']->video_name }}"
                                    id="video_name" name="video_name" 
                                    data-template-id="{{ $dataArray['item']->id }}" 
                                    maxlength="60" required>
                                <small id="videoNameCounter" class="text-muted">
                                    <span id="videoNameCount">{{ strlen($dataArray['item']->video_name) }}</span>/60 characters
                                </small>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Category</h6>
                                @if ($restrictSeoExecInternBasicSeo)
                                    <input type="hidden" name="category_id" value="{{ $dataArray['item']->category_id }}">
                                @endif
                                <select class="form-control category form-control-sm" id="videoCategorySelect" name="{{ $restrictSeoExecInternBasicSeo ? 'category_id_display' : 'category_id' }}"
                                    @if ($restrictSeoExecInternBasicSeo) disabled @else required @endif>
                                    <option value="">Select Category</option>
                                    @foreach ($dataArray['groupedVideoCategories'] as $group)
                                    <optgroup label="{{ $group['parent']->category_name }}">
                                        @foreach ($group['children'] as $child)
                                        <option value="{{ $child->id }}"
                                            style="color: black !important;"
                                            {{ $dataArray['item']->category_id == $child->id ? 'selected' : '' }}>
                                            {{ $child->category_name }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Virtual Category</h6>
                                <select class="form-control category form-control-sm" id="virtualCategorySelect" name="virtual_category_id">
                                    <option value="">Select Virtual Category</option>
                                    @foreach ($dataArray['virtualCategory'] as $child)
                                    <option value="{{ $child->id }}"
                                            style="color: black !important;"
                                            {{ $dataArray['item']->virtual_category_id == $child->id ? 'selected' : '' }}>
                                    {{ $child->category_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>String id</h6>
                                <input class="form-control" type="text" value="{{ $dataArray['item']->string_id }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Keyword or Tag</h6>
                                <div class="col-sm-20">
                                    <select class="custom-select2 form-control" multiple="multiple"
                                            data-style="btn-outline-primary" name="keywords[]">
                                        @foreach ($dataArray['keywordArray'] as $keyword)
                                        @if ($helperController::stringContain($dataArray['item']->keyword, $keyword->id))
                                        <option value="{{ $keyword->id }}" selected="">
                                            {{ $keyword->name }}
                                        </option>
                                        @else
                                        <option value="{{ $keyword->id }}">{{ $keyword->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Premium Item</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="is_premium">
                                    @if ($dataArray['item']->is_premium == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Status</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="status">
                                    @if ($dataArray['item']->status == '1')
                                        <option value="1" selected>LIVE</option>
                                        <option value="0">NOT LIVE</option>
                                    @else
                                        <option value="1">LIVE</option>
                                        <option value="0" selected>NOT LIVE</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>No Index</h6>
                                @if ($restrictSeoExecInternBasicSeo)
                                    <input type="hidden" name="no_index" value="{{ $dataArray['item']->no_index ?? 1 }}">
                                @endif
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="{{ $restrictSeoExecInternBasicSeo ? 'no_index_display' : 'no_index' }}"
                                    @if ($restrictSeoExecInternBasicSeo) disabled @endif>
                                    @if (($dataArray['item']->no_index ?? 1) == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                                <small class="text-muted">TRUE = noindex (not indexed by search engines)</small>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="section-header">SEO Fields</div>
                    <div class="row">

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Slug</h6>
                                <input class="form-control" type="text" name="slug" id="slug"
                                       value="{{ $dataArray['item']->slug ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>H2 Tag</h6>
                                <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                    value="{{ $dataArray['item']->h2_tag ?? '' }}" />
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <input type="text" class="form-control" name="canonical_link"
                                    value="{{ $dataArray['item']->canonical_link ?? '' }}"
                                    @if ($restrictSeoExecInternBasicSeo) readonly style="background-color:#e9ecef;cursor:not-allowed" @endif />
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Meta Title</h6>
                                <input type="text" class="form-control" name="meta_title" id="meta_title"
                                    maxlength="60" value="{{ $dataArray['item']->meta_title ?? '' }}" />
                                <small id="metaCounter" class="text-muted">60 characters max</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Description</h6>
                                <textarea style="height: 150px" class="form-control" id="description" name="description">{{ $dataArray['item']->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Meta Description</h6>
                                <textarea style="height: 150px" class="form-control" id="meta_description" name="meta_description" maxlength="160">{{ $dataArray['item']->meta_description ?? '' }}</textarea>
                                <small class="text-muted">160 characters max</small>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="section-header">Filter Row</div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Languages</h6>
                                @php
                                    if (isset($dataArray['item']->lang_id) && $dataArray['item']->lang_id != '') {
                                        $dataArray['item']->lang_id = is_array(json_decode($dataArray['item']->lang_id))
                                            ? $dataArray['item']->lang_id
                                            : json_encode([$dataArray['item']->lang_id]);
                                        $dataArray['langArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->lang_id,
                                            $dataArray['langArray'],
                                            'id',
                                            1,
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    name="lang_id[]" multiple>
                                    @foreach ($dataArray['langArray'] as $lang)
                                        @if ($helperController::stringContain($dataArray['item']->lang_id ?? '', $lang->id))
                                            <option value="{{ $lang->id }}" selected="">
                                                {{ $lang->name }}
                                            </option>
                                        @else
                                            <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Theme</h6>
                                @php
                                    if (isset($dataArray['item']->theme_id) && $dataArray['item']->theme_id != '') {
                                        $dataArray['item']->theme_id = is_array(json_decode($dataArray['item']->theme_id))
                                            ? $dataArray['item']->theme_id
                                            : json_encode([$dataArray['item']->theme_id]);
                                        $dataArray['themeArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->theme_id,
                                            $dataArray['themeArray'],
                                            'id',
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" name="theme_id[]">
                                    @foreach ($dataArray['themeArray'] as $theme)
                                        @if ($helperController::stringContain($dataArray['item']->theme_id ?? '', $theme->id))
                                            <option value="{{ $theme->id }}" selected="">
                                                {{ $theme->name }}
                                            </option>
                                        @else
                                            <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Orientation</h6>
                                <select class="form-control" data-style="btn-outline-primary" id="orientation"
                                        name="orientation">
                                    @foreach ($helperController::getOrientations() as $orientation)
                                    <option value="{{ $orientation }}"
                                            {{ $orientation == ($dataArray['item']->orientation ?? '') ? 'selected' : '' }}>
                                    {{ \Str::title($orientation) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Size</h6>
                                <select name="template_size" id="sizeInput" class="form-control">
                                    <option value="">== none ==</option>
                                    @foreach ($dataArray['sizes'] as $size)
                                    @php
                                    $orientation = $dataArray['item']->orientation ?? 'portrait';
                                    $currentOrientation = 'portrait';
                                    if ($size->width_ration == $size->height_ration) {
                                    $currentOrientation = 'square';
                                    } elseif ($size->width_ration > $size->height_ration) {
                                    $currentOrientation = 'landscape';
                                    }
                                    @endphp

                                    @if (isset($dataArray['item']->template_size))
                                    @if ($dataArray['item']->template_size == $size->id)
                                    <option value="{{ $size->id }}" selected>
                                        {{ $size->size_name }}
                                    </option>
                                    @else
                                    <option value="{{ $size->id }}">
                                        {{ $size->size_name }}</option>
                                    @endif
                                    @else
                                    <option value="{{ $size->id }}">
                                        {{ $size->size_name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Religion</h6>
                                @php
                                if (
                                isset($dataArray['item']->religion_id) &&
                                $dataArray['item']->religion_id != ''
                                ) {
                                $dataArray['item']->religion_id = is_array(json_decode($dataArray['item']->religion_id))
                                ? $dataArray['item']->religion_id
                                : json_encode([$dataArray['item']->religion_id]);
                                $dataArray['religions'] = $helperController::filterArrayOrder(
                                $dataArray['item']->religion_id,
                                $dataArray['religions'],
                                'id',
                                );
                                }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                        multiple="multiple" name="religion_id[]">
                                    @foreach ($dataArray['religions'] as $religion)
                                    @if ($helperController::stringContain($dataArray['item']->religion_id ?? '', $religion->id))
                                    <option value="{{ $religion->id }}" selected="">
                                        {{ $religion->name }}
                                    </option>
                                    @else
                                    <option value="{{ $religion->id }}">{{ $religion->name }}
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Interest</h6>
                                @php
                                if (isset($dataArray['item']->interest_id) && $dataArray['item']->interest_id != '') {
                                $dataArray['interestArray'] = $helperController::filterArrayOrder(
                                $dataArray['item']->interest_id,
                                $dataArray['interestArray'],
                                'id',
                                );
                                }
                                @endphp
                                <select class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="interest_id[]">
                                    @foreach ($dataArray['interestArray'] as $interest)
                                    @if ($helperController::stringContain($dataArray['item']->interest_id ?? '', $interest->id))
                                    <option value="{{ $interest->id }}" selected="">
                                        {{ $interest->name }}</option>
                                    @else
                                    <option value="{{ $interest->id }}">{{ $interest->name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Is Freemium</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary"
                                        name="is_freemium">
                                    @if (($dataArray['item']->is_freemium ?? '0') == '1')
                                    <option value="1" selected>TRUE</option>
                                    <option value="0">FALSE</option>
                                    @else
                                    <option value="1">TRUE</option>
                                    <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Date Range</h6>
                                @if (($dataArray['item']->start_date ?? null) != null && $dataArray['item']->start_date != '')
                                @php
                                try {
                                $startDate = \Carbon\Carbon::parse($dataArray['item']->start_date)->format('m/d/Y');
                                $endDate = \Carbon\Carbon::parse($dataArray['item']->end_date)->format('m/d/Y');
                                $dateRangeValue = $startDate . ' - ' . $endDate;
                                } catch (\Exception $e) {
                                $dateRangeValue = $dataArray['item']->start_date . ' - ' . $dataArray['item']->end_date;
                                }
                                @endphp
                                <input class="form-control datetimepicker-range" placeholder="Select Date"
                                       value="{{ $dateRangeValue }}"
                                       type="text" name="date_range" readonly>
                                @else
                                <input class="form-control datetimepicker-range" placeholder="Select Date"
                                       type="text" name="date_range" readonly>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Colors</h6>
                                <div class="col-sm-20 color_tags">
                                    <input type="text" id="colorTags" class="form-control" data-role="tagsinput"
                                           name="color_ids">
                                    <input type="text" id="colorPicker" class="form-control mt-3">
                                </div>
                            </div>
                        </div>

                        {{-- <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Style</h6>
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="styles[]">
                                    @foreach ($dataArray['styleArray'] as $style)
                                        @if ($helperController::stringContain($dataArray['item']->style_id ?? '', $style->id))
                                            <option value="{{ $style->id }}" selected="">
                                                {{ $style->name }}
                                            </option>
                                        @else
                                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                    </div>


                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fa fa-save"></i> Update SEO
                        </button>
                        <a href="{{ route('show_v_item') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<datalist id="related_tag_list">
    @foreach ($dataArray['searchTagArray'] as $searchTag)
        <option value="{{ $searchTag->name }}"></option>
    @endforeach
</datalist>

@include('layouts.masterscript')

{{-- Spectrum Color Picker Library --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css" />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>

<style>
    /* Style for color tags */
    .bootstrap-tagsinput .tag {
        margin-right: 5px !important;
        margin-bottom: 5px !important;
        padding: 8px 12px !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        display: inline-block !important;
        color: white !important;
        border: none !important;
        font-size: 13px !important;
    }

    .bootstrap-tagsinput .tag[data-role="remove"] {
        margin-left: 8px !important;
        cursor: pointer !important;
        opacity: 0.8 !important;
    }

    .bootstrap-tagsinput .tag[data-role="remove"]:hover {
        opacity: 1 !important;
    }

    .bootstrap-tagsinput {
        width: 100% !important;
        min-height: 45px !important;
        padding: 8px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
    }

    .bootstrap-tagsinput input {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        background-color: transparent !important;
        padding: 0 6px !important;
        margin: 0 !important;
        width: auto !important;
        max-width: inherit !important;
    }
</style>
<script>
    function loadVideoSizeAndTheme(catId, keepValue, keepTheme) {
        var $sizeSel = $("#sizeInput");
        var $themeSel = $("select[name='theme_id[]']");
        var prev = keepValue !== undefined && keepValue !== null && keepValue !== ''
            ? String(keepValue)
            : '';
        var prevTheme = keepTheme !== undefined && keepTheme !== null ? keepTheme : [];
        $sizeSel.html('<option value="">== none ==</option>');
        $themeSel.empty();
        if ($themeSel.hasClass('select2-hidden-accessible')) {
            $themeSel.trigger('change');
        }

        if (!catId || catId === '0') {
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('loadVideoSizeAndTheme') }}",
            type: 'POST',
            data: {
                cateId: catId
            },
            success: function(data) {
                if (data.status) {
                    if (data.sizes && data.sizes.length) {
                        data.sizes.forEach(function(size) {
                            $sizeSel.append($('<option></option>').attr('value', size.id).text(size.size_name));
                        });
                    }
                    if (data.themes && data.themes.length) {
                        data.themes.forEach(function(theme) {
                            $themeSel.append($('<option></option>').attr('value', theme.id).text(theme.name));
                        });
                    }
                }
                if (prev) {
                    $sizeSel.val(prev);
                }
                if (prevTheme && prevTheme.length) {
                    $themeSel.val(prevTheme);
                }
                if ($themeSel.hasClass('select2-hidden-accessible')) {
                    $themeSel.trigger('change');
                }
            }
        });
    }

    $(document).ready(function() {

        // Load size & theme when video category changes (not virtual category)
        $('#videoCategorySelect').on('change', function() {
            var catId = $(this).val();
            loadVideoSizeAndTheme(catId ? String(catId) : '0', '');
        });

        // Pre-load size & theme for the currently selected video category
        var initialCatId = $('#videoCategorySelect').val();
        if (initialCatId) {
            var initialTheme = @json(is_array(json_decode($dataArray['item']->theme_id ?? '[]')) ? json_decode($dataArray['item']->theme_id) : []);
            loadVideoSizeAndTheme(String(initialCatId), '{{ $dataArray['item']->template_size ?? '' }}', initialTheme);
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            var form = $('#editVideoSeoForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('v_item_seo.update', [$dataArray['item']->id]) }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                beforeSend: function() {
                    // Show loader
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    if (main_loading_screen) {
                        main_loading_screen.style.display = "block";
                    }
                    // Disable submit button to prevent double submission
                    $('#submitBtn').prop('disabled', true).text('Updating...');
                },
                success: function(response) {
                    // Hide loader
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    if (main_loading_screen) {
                        main_loading_screen.style.display = "none";
                    }
                    // Re-enable submit button
                    $('#submitBtn').prop('disabled', false).text('Update');
                    
                    if (response.error) {
                        alert('Error: ' + response.error);
                    } else {
                        alert('SEO data updated successfully!');
                        // Optionally reload or redirect
                        // window.location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loader
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    if (main_loading_screen) {
                        main_loading_screen.style.display = "none";
                    }
                    // Re-enable submit button
                    $('#submitBtn').prop('disabled', false).text('Update');
                    
                    // Detailed error handling
                    var errorMessage = 'An error occurred while updating SEO data.';
                    
                    if (xhr.status === 419) {
                        errorMessage = "Session expired. Please refresh the page and try again.";
                    } else if (xhr.status === 422) {
                        // Validation errors
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            errorMessage = "Validation errors:\n";
                            for (var field in errors) {
                                errorMessage += "- " + errors[field].join(', ') + "\n";
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                    } else if (xhr.status === 500) {
                        errorMessage = "Server error occurred. Please try again or contact support.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage += "\nDetails: " + xhr.responseJSON.message;
                        }
                    } else if (xhr.status === 0) {
                        errorMessage = "Network error. Please check your internet connection.";
                    } else {
                        errorMessage = "Error " + xhr.status + ": " + (xhr.statusText || 'Unknown error');
                    }
                    
                    alert(errorMessage);
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }
            });
        });

        $(".select2-selection__rendered").sortable({
            placeholder: "ui-state-highlight",
            stop: function(event, ui) {
                var selectionContainer = $(this);
                var selectedOptions = selectionContainer.find(".select2-selection__choice");
                var newOrder = [];
                selectedOptions.each(function() {
                    var optionValue = $(this).attr("title");
                    newOrder.push(optionValue);
                });
                var selectElement = selectionContainer.closest(".custom-select2").find("select");
                selectElement.val(newOrder).trigger("change");
            }
        }).disableSelection();

        // Character counter for meta title
        $('#meta_title').on('input', function() {
            var remaining = 60 - $(this).val().length;
            $('#metaCounter').text(remaining + ' characters remaining');
        });

        // Initialize counter
        var metaTitleLength = $('#meta_title').val().length;
        $('#metaCounter').text((60 - metaTitleLength) + ' characters remaining');

        // Color picker functionality - same as regular items
        $('.col-sm-20.color_tags input[type="text"]').on('click', function(event) {
            event.preventDefault();
            $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        });
        $('.col-sm-20.color_tags input[type="text"]').on('keypress', function(event) {
            event.preventDefault();
            $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        });

        var colorData = @json($dataArray['item']->color_ids ?? '');
        const colorIds = colorData ? colorData.split(",") : [];

        for (const colorId of colorIds) {
            if (colorId.trim()) {
                $('#colorTags').tagsinput('add', colorId.trim());
                setTagBackgroundColor(colorId.trim());
            }
        }

        var currentTag = null; // To keep track of the current tag being edited
        var currentColor = null;
        
        // Initialize Spectrum color picker
        $("#colorPicker").spectrum({
            color: "#f00",
            showInput: true,
            showPalette: false,
            showAlpha: true,
            change: function(color) {
                var colorHex = color.toHexString();
                $(currentTag).css('background-color', colorHex);
                $(currentTag).text(colorHex);

                if (currentTag == null) {
                    $('#colorTags').tagsinput('remove', $(currentTag).text());
                    $('#colorTags').tagsinput('add', colorHex);
                } else {
                    var currentColorHex = rgbToHex(currentColor);
                    updateColorCodeValue(currentColorHex)
                }
                currentTag = null;
            }
        });

        function rgbToHex(rgb) {
            var result = rgb.match(/\d+/g);
            if (result) {
                var r = parseInt(result[0]).toString(16).padStart(2, '0');
                var g = parseInt(result[1]).toString(16).padStart(2, '0');
                var b = parseInt(result[2]).toString(16).padStart(2, '0');
                return '#' + r + g + b;
            }
            return null;
        }

        function updateColorCodeValue(currentColorHex) {
            var colorsCode = [];
            $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
                colorsCode.push($(this).text())
            });
            var colorsCodeString = colorsCode.join(',');
            $("input[name='color_ids']").val(colorsCodeString);
        }

        // Function to set the background color of the last tag
        function setTagBackgroundColor(color) {
            var tagElements = $('.bootstrap-tagsinput .tag');
            var lastTagElement = tagElements[tagElements.length - 1];
            $(lastTagElement).css('background-color', color);
        }

        // Initialize tags input
        $('#colorTags').tagsinput({
            confirmKeys: [13, 32, 188]
        });

        // Set background color when a new tag is added
        $('#colorTags').on('itemAdded', function(event) {
            setTagBackgroundColor(event.item);
        });

        // Set initial background colors for tags
        $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
            $(this).css("background-color", $(this).text());
        });

        // Event handler for tag click to open color picker
        $(document).on('click', '.color_tags .bootstrap-tagsinput .tag', function() {
            currentTag = this;
            currentColor = $(this).css('background-color');
            $("#colorPicker").spectrum("set", currentColor);
            $("#colorPicker").spectrum("show");
        });

        // Category dropdown functionality
        $(document).on('click', '#parentCategoryInput', function(e) {
            e.stopPropagation();
            if ($('.parent-category-input').hasClass('show')) {
                $('.parent-category-input').removeClass('show');
                $(this).removeClass('dropdown-open');
            } else {
                $('.parent-category-input').addClass('show');
                $(this).addClass('dropdown-open');
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
                $('.custom-dropdown.parent-category-input.show').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');
            }
        });

        // Category search filter
        $('#categoryFilter').on('input', function(e) {
            e.stopPropagation();
            var filterValue = $(this).val().toLowerCase();
            $('.category, .subcategory').each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(filterValue) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Prevent dropdown from closing when clicking on search input
        $('#categoryFilter').on('click', function(e) {
            e.stopPropagation();
        });

        // Category selection
        $(document).on('click', '.filter-wrap-list .category', function(e) {
            e.stopPropagation();
            $('.category').removeClass('selected');
            $('.subcategory').removeClass('selected');

            var catId = $(this).data('id');
            var catName = $(this).data('catname');

            if (catId && catName) {
                // Update hidden input
                $('.new_cat_id_item').val(catId);

                // Update display
                $('#parentCategoryInput span').html(catName);

                // Add selected class to clicked item
                $(this).addClass('selected');

                // Close dropdown
                $('.parent-category-input').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');

                loadVideoSizeAndTheme(String(catId), '');
            }
        });

        // Subcategory selection
        $(document).on('click', '.filter-wrap-list .subcategory', function(e) {
            e.stopPropagation();
            $('.category').removeClass('selected');
            $('.subcategory').removeClass('selected');

            var catId = $(this).data('id');
            var catName = $(this).data('catname');

            if (catId && catName) {
                // Update hidden input
                $('.new_cat_id_item').val(catId);

                // Update display
                $('#parentCategoryInput span').html(catName);

                // Add selected class to clicked item
                $(this).addClass('selected');

                // Close dropdown
                $('.parent-category-input').removeClass('show');
                $('#parentCategoryInput').removeClass('dropdown-open');

                loadVideoSizeAndTheme(String(catId), '');
            }
        });

        // Handle none option
        $(document).on('click', 'li.category.none-option', function(e) {
            e.stopPropagation();
            $('.new_cat_id_item').val('0');
            $('#parentCategoryInput span').html('== none ==');
            $('.filter-wrap-list .category, .filter-wrap-list .subcategory').removeClass('selected');
            $('.parent-category-input').removeClass('show');
            $('#parentCategoryInput').removeClass('dropdown-open');

            loadVideoSizeAndTheme('0', '');
        });

        // Tags input autocomplete with custom dropdown
        window.addEventListener("load", function() {
            // Style the tags input
            var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
            if (tagsInputContainer) {
                var tagsInput = tagsInputContainer.querySelector('input[type="text"]');
                if (tagsInput) {
                    tagsInput.setAttribute('autocomplete', 'off');
                    tagsInput.style.border = 'none';
                    tagsInput.style.outline = 'none';
                    tagsInput.style.boxShadow = 'none';

                    // Create custom autocomplete dropdown
                    var dropdown = document.createElement('div');
                    dropdown.className = 'custom-autocomplete-dropdown';
                    dropdown.style.display = 'none';
                    tagsInputContainer.parentElement.appendChild(dropdown);

                    // Get all available tags
                    var availableTags = [];
                    var datalist = document.getElementById('related_tag_list');
                    if (datalist) {
                        var options = datalist.querySelectorAll('option');
                        options.forEach(function(option) {
                            if (option.value) {
                                availableTags.push(option.value);
                            }
                        });
                    }

                    // Handle input for autocomplete
                    // tagsInput.addEventListener('input', function() {
                    //     var value = this.value.toLowerCase();
                    //     if (value.length < 2) {
                    //         dropdown.style.display = 'none';
                    //         return;
                    //     }
                    //
                    //     var matches = availableTags.filter(function(tag) {
                    //         return tag.toLowerCase().indexOf(value) > -1;
                    //     }).slice(0, 10); // Limit to 10 suggestions
                    //
                    //     if (matches.length > 0) {
                    //         dropdown.innerHTML = matches.map(function(tag) {
                    //             return '<div class="autocomplete-item">' + tag + '</div>';
                    //         }).join('');
                    //         dropdown.style.display = 'block';
                    //
                    //         // Add click handlers
                    //         dropdown.querySelectorAll('.autocomplete-item').forEach(function(item) {
                    //             item.addEventListener('click', function() {
                    //                 $('#keywords').tagsinput('add', this.textContent);
                    //                 tagsInput.value = '';
                    //                 dropdown.style.display = 'none';
                    //             });
                    //         });
                    //     } else {
                    //         dropdown.style.display = 'none';
                    //     }
                    // });

                    // Hide dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!tagsInputContainer.contains(e.target) && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                }
            }

            // Ensure tags input is properly styled
            // $('#keywords').on('itemAdded itemRemoved', function() {
            //     var input = $('.bootstrap-tagsinput input[type="text"]');
            //     if (input.length) {
            //         input.css({
            //             'border': 'none',
            //             'outline': 'none',
            //             'box-shadow': 'none',
            //             'min-width': '150px'
            //         });
            //     }
            // });
        });

        // Initialize date range picker
        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('.datetimepicker-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });

            $('.datetimepicker-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $('.datetimepicker-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
        
        // Auto-fill ID Name from Video Name
        const toKebabCase = str => str.toLowerCase().replace(/\s+/g, '-');

        function maybeAutofillEmptySlugOnLoad() {
            var $slug = $("#slug");
            if (($slug.val() || "").trim() !== "") {
                return;
            }
            var $videoName = $("#video_name");
            const templateId = $videoName.data("template-id");
            const videoName = ($videoName.val() || "").trim();
            if (templateId && videoName) {
                $slug.val(`${toKebabCase(videoName)}-${templateId}`);
            }
        }

        function attachVideoNameAutoFill() {
            $("#video_name").off("input").on("input", function() {
                const templateId = $(this).data("template-id");
                const videoName = $(this).val();
                
                // Update character counter
                const length = videoName.length;
                $('#videoNameCount').text(length);
                if (length >= 55) {
                    $('#videoNameCounter').addClass('text-warning').removeClass('text-muted');
                } else {
                    $('#videoNameCounter').removeClass('text-warning').addClass('text-muted');
                }
                if (length >= 60) {
                    $('#videoNameCounter').addClass('text-danger').removeClass('text-warning text-muted');
                } else {
                    $('#videoNameCounter').removeClass('text-danger');
                }
                
                // Update slug
                if (templateId && videoName) {
                    const idName = `${toKebabCase(videoName)}-${templateId}`;
                    $("#slug").val(idName);
                }
            });
        }
        
        // Attach immediately
        maybeAutofillEmptySlugOnLoad();
        attachVideoNameAutoFill();
        
        // Re-attach after a delay to ensure it works after role_access.js
        setTimeout(function () {
            maybeAutofillEmptySlugOnLoad();
            attachVideoNameAutoFill();
        }, 500);
        setTimeout(function () {
            maybeAutofillEmptySlugOnLoad();
            attachVideoNameAutoFill();
        }, 1000);
    });

</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

@if ($restrictSeoExecInternBasicSeo)
<script>
    $(function () {
        function lockExecInternVideoSeoFields() {
            $('select[name="category_id_display"]').prop('disabled', true).css({
                'pointer-events': 'none',
                'opacity': '0.85',
                'cursor': 'not-allowed'
            });
            var $ni = $('select[name="no_index_display"]');
            $ni.prop('disabled', true).css({
                'pointer-events': 'none',
                'opacity': '0.85',
                'cursor': 'not-allowed'
            });
            if ($.fn.selectpicker) {
                $ni.selectpicker('refresh');
            }
            $('input[name="canonical_link"]').prop('readonly', true).css({
                'background-color': '#e9ecef',
                'cursor': 'not-allowed'
            });
        }
        lockExecInternVideoSeoFields();
        setTimeout(lockExecInternVideoSeoFields, 150);
        setTimeout(lockExecInternVideoSeoFields, 600);
        window.addEventListener('load', function () {
            setTimeout(lockExecInternVideoSeoFields, 100);
        });
    });
</script>
@endif

@if($isSeoManagerAccess)
<script>
    // Enable specific fields for SEO Manager - runs AFTER role_access.js
    
    // Function to enable SEO Manager fields
    function enableSeoManagerFields() {
        console.log('Enabling SEO Manager fields...');
        
        // Enable Video Name
        $('#video_name').prop('disabled', false).prop('readonly', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'text'
        });
        
        // Enable Category Select
        $('select[name="category_id"]').prop('disabled', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'pointer'
        });
        
        // Enable Virtual Category Select
        $('select[name="virtual_category_id"]').prop('disabled', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'pointer'
        });
        
        // Enable No Index
        $('select[name="no_index"]').prop('disabled', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'pointer'
        });
        
        // Enable ID Name
        $('input[name="slug"]').prop('disabled', false).prop('readonly', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'text'
        });
        
        // Enable Slug
        $('input[name="slug"]').prop('disabled', false).prop('readonly', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'text'
        });
        
        // Enable Canonical Link
        $('input[name="canonical_link"]').prop('disabled', false).prop('readonly', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'text'
        });
        
        // Enable Status
        $('select[name="status"]').prop('disabled', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'pointer'
        });
        
        // Enable Update SEO Button
        $('#submitBtn').prop('disabled', false).removeAttr('disabled').css({
            'pointer-events': 'auto',
            'opacity': '1',
            'cursor': 'pointer'
        }).removeClass('disabled');
        
        // Refresh selectpicker if used
        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
        
        // Re-attach auto-fill for Video Name -> ID Name
        const toKebabCase = str => str.toLowerCase().replace(/\s+/g, '-');
        $("#video_name").off("input").on("input", function() {
            const templateId = $(this).data("template-id");
            const videoName = $(this).val();
            
            // Update character counter
            const length = videoName.length;
            $('#videoNameCount').text(length);
            if (length >= 55) {
                $('#videoNameCounter').addClass('text-warning').removeClass('text-muted');
            } else {
                $('#videoNameCounter').removeClass('text-warning').addClass('text-muted');
            }
            if (length >= 60) {
                $('#videoNameCounter').addClass('text-danger').removeClass('text-warning text-muted');
            } else {
                $('#videoNameCounter').removeClass('text-danger');
            }
            
            // Update slug
            if (templateId && videoName) {
                const idName = `${toKebabCase(videoName)}-${templateId}`;
                $("#slug").val(idName);
            }
        });
        
        console.log('SEO Manager fields enabled successfully');
    }
    
    // Run after document ready
    $(document).ready(function() {
        enableSeoManagerFields();
    });
    
    // Run after window load (after all scripts including role_access.js)
    window.addEventListener('load', function() {
        setTimeout(enableSeoManagerFields, 100);
        setTimeout(enableSeoManagerFields, 500);
        setTimeout(enableSeoManagerFields, 1000);
    });
</script>
@endif

</body>
</html>

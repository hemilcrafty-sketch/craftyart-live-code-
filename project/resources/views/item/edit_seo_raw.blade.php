@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-access-container">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="row pd-20 card-box mb-30">
            <div class="col-md-12 col-sm-12 text-right">
                <ul class="nav nav-tabs customtab" id="page_list" role="tablist">
                    @for ($i = 0; $i < count($dataArray['thumbs']); $i++)
                        @php
                            $isActive = $dataArray['item']->default_thumb_pos == $i ? 'active' : '';
                        @endphp
                        <li class="nav-item li_page_class" id="li_page_{{ $i }}"
                            data-page="{{ $i }}">
                            <a class="nav-link {{ $isActive }}" id="a_link_{{ $i }}" data-toggle="tab"
                                href="#page_{{ $i }}" role="tab">Page
                                {{ $i + 1 }}</a>
                        </li>
                    @endfor

                </ul>
            </div>
        </div>
    </div>

    <div class="min-height-200px">
        <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
            <form method="post" id="dynamic_form" enctype="multipart/form-data">
                <span id="result"></span>
                @csrf
                <input type="hidden" name="id" value="{{ $dataArray['item']->id }}" />
                <div id="page_number_container" style="display: none;">
                    <input class="form-control" type="textname" id="default_thumb_pos" name="default_thumb_pos"
                        value="{{ $dataArray['item']->default_thumb_pos }}" />
                    @for ($i = 0; $i < count($dataArray['thumbs']); $i++)
                        <input class="form-control" type="textname" id="page_number_{{ $i }}"
                            name="design_page_number[]" value="{{ $i }}" style="display: none;">
                    @endfor
                </div>
                <div class="tab-content" id="page_container">
                    @for ($i = 0; $i < count($dataArray['thumbs']); $i++)
                        @php
                            $design = $dataArray['thumbs'][$i];
                            $isActive = $dataArray['item']->default_thumb_pos == $i ? 'show active' : '';

                            $btnCss = $dataArray['item']->default_thumb_pos == $i ? 'btn-secondary' : 'btn-primary';
                            $btnTitle =
                                $dataArray['item']->default_thumb_pos == $i ? 'Defaulted Thumb' : 'Set Default Thumb';
                        @endphp
                        <div class="tab-pane fade {{ $isActive }}" id="page_{{ $i }}" role="tabpanel">
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <img src="{{ config('filesystems.storage_url') }}{{ $design }}" />
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" id="default_btn_{{ $i }}"
                                        class="default_btns btn {{ $btnCss }} form-control-file"
                                        onclick="setDefaultThumb('{{ $i }}')">{{ $btnTitle }}</button>
                                </div>

                            </div>
                        </div>
                    @endfor
                </div>

                <br />

                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Post Thumb</h6>
                            <input 
                                type="file" 
                                class="form-control-file form-control post-thumb-input" 
                                name="post_thumb" 
                                id="post_thumb"
                                accept="image/*"
                                onchange="previewPostThumb(event)"
                                style="background-color: white !important; cursor: pointer !important; pointer-events: auto !important;">
                            <br>
                            @if(!empty($dataArray['item']->post_thumb))
                                <img 
                                    id="postThumbPreview"
                                    src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->post_thumb }}?v={{ time() }}"
                                    style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px; border-radius: 4px;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; padding: 5px; background: #f8d7da; border-radius: 4px; color: #721c24; font-size: 11px;">
                                    <i class="fa fa-exclamation-triangle"></i> Image not found
                                </div>
                                <p style="font-size: 11px; color: #6c757d; margin-top: 5px;">
                                    Current: {{ basename($dataArray['item']->post_thumb) }}
                                </p>
                            @else
                                <img 
                                    id="postThumbPreview"
                                    style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px; border-radius: 4px; display: none;">
                                <div id="noPostThumbMsg" style="padding: 5px; background: #f8d7da; border-radius: 4px; color: #721c24; font-size: 11px;">
                                    <i class="fa fa-exclamation-triangle"></i> Image not found
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Additional Thumbnail</h6>
                            <input 
                                type="file" 
                                class="form-control-file form-control additional-thumb-input" 
                                name="additional_thumb" 
                                id="additional_thumb"
                                accept="image/*"
                                onchange="previewAdditionalThumb(event)"
                                style="background-color: white !important; cursor: pointer !important; pointer-events: auto !important;">
                            <br>
                            @if(!empty($dataArray['item']->additional_thumb))
                                <img 
                                    id="additionalThumbPreview"
                                    src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->additional_thumb }}?v={{ time() }}"
                                    style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px; border-radius: 4px;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; padding: 5px; background: #f8d7da; border-radius: 4px; color: #721c24; font-size: 11px;">
                                    <i class="fa fa-exclamation-triangle"></i> Image not found
                                </div>
                                <p style="font-size: 11px; color: #6c757d; margin-top: 5px;">
                                    Current: {{ basename($dataArray['item']->additional_thumb) }}
                                </p>
                                <br>
                                <button 
                                    type="button" 
                                    id="removeAdditionalThumbBtn"
                                    class="btn btn-danger btn-sm filter-access"
                                    onclick="removeAdditionalThumb()">
                                    Remove Additional Thumb
                                </button>
                            @else
                                <img 
                                    id="additionalThumbPreview"
                                    style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 2px; border-radius: 4px; display: none;">
                                <div id="noAdditionalThumbMsg" style="padding: 5px; background: #f8d7da; border-radius: 4px; color: #721c24; font-size: 11px;">
                                    <i class="fa fa-exclamation-triangle"></i> Image not found
                                </div>
                                <br>
                                <button 
                                    type="button" 
                                    id="removeAdditionalThumbBtn"
                                    class="btn btn-danger btn-sm"
                                    style="display: none;"
                                    onclick="removeAdditionalThumb()">
                                    Remove Additional Thumb
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <br>
                <h6>SEO Row</h6>
                <hr>
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <h6>Post Name</h6>
                            <input id="post_name" class="form-control" type="text" name="post_name" maxlength="60"
                                value="{{ $dataArray['item']->post_name }}"
                                data-strid="{{ $dataArray['item']->string_id }}" required>
                            <small id="postNameCounter" class="text-muted">60 remaining of 60 letters</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <h6>ID Name</h6>
                            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) || $roleManager::isSeoExecutive(Auth::user()->user_type))
                                <input id="id_name" class="form-control-file form-control" name="id_name"
                                    value="{{ $dataArray['item']->id_name }}" required>
                            @else
                                @if ($dataArray['item']->id_name)
                                    <input id="id_name" class="form-control-file form-control" name="id_name"
                                        value="{{ $dataArray['item']->id_name }}" readonly>
                                @else
                                    <input id="id_name" class="form-control-file form-control" name="id_name"
                                        value="{{ $dataArray['item']->id_name }}" required>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Special Keywords</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="special_keywords[]">
                                    @foreach ($dataArray['specialKeywords'] as $keyword)
                                        @if ($helperController::stringContain($dataArray['item']->special_keywords, $keyword->id))
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
                </div>
                <div class="row">

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>H2 Tag</h6>
                            <div class="col-sm-20">
                                <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                    value="{{ $dataArray['item']->h2_tag }}" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Canonical Link</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control canonical_link" name="canonical_link"
                                    value="{{ $dataArray['item']->canonical_link }}" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Meta Title</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control" name="meta_title" id="meta_title"
                                    maxlength="60" oninput="updateCount(this, 'metaCounter')" required
                                    value="{{ $dataArray['item']->meta_title }}" />
                            </div>
                            <small id="metaCounter" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Description</h6>
                            <div class="col-sm-20">
                                <textarea style="height: 250px" class="form-control" id="description" name="description">{{ $dataArray['item']->description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Meta Description</h6>
                            <div class="col-sm-20">
                                <textarea style="height: 250px" class="form-control" id="meta_description" name="meta_description" maxlength="160">{{ $dataArray['item']->meta_description }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <br>
                <h6>Categories & Related Tag Row</h6>
                <hr>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Categories</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    name="category_id" required>
                                    <option value="" selected="" disabled="">Categories</option>
                                    @foreach ($dataArray['cat'] as $cat)
                                        @if ($dataArray['item']->category_id == $cat->id)
                                            <option value="{{ $cat->id }}" selected="">
                                                {{ $cat->category_name }}</option>
                                        @else
                                            <option value="{{ $cat->id }}">{{ $cat->category_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Search Tags</h6>
                            <div class="col-sm-20" id="relatedKeyword">
                                <input type="text" data-role="tagsinput" class="form-control" id="keywords"
                                    name="keywords" placeholder="Add tags"
                                    value="{{ $dataArray['item']->related_tags }}" autocomplete="on"
                                    required="" />
                                <div id="suggestionsContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @php
                        $isRestricted = $roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type);
                    @endphp
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group category-dropbox-wrap">
                            <h6>Select New Category</h6>
                            {{-- Main dropdown button --}}
                            <div class="input-subcategory-dropbox unset-bottom-border {{ $isRestricted ? 'disabled-category' : '' }}"
                                id="parentCategoryInput"
                                @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                                <span>
                                    @if (!empty($dataArray['select_category']) && isset($dataArray['select_category']['category_name']))
                                        {{ $dataArray['select_category']['category_name'] }}
                                    @else
                                        {{ '== none ==' }}
                                    @endif
                                </span>
                                <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                            </div>
                            {{-- Dropdown list --}}
                            <div class="custom-dropdown parent-category-input"
                                @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                                <input type="text" id="categoryFilter" class="form-control-file form-control"
                                    placeholder="Search categories">
                                <ul class="dropdown-menu-ul filter-wrap-list">
                                    <li class="category none-option">== none ==</li>
                                    @foreach ($dataArray['allCategories'] as $category)
                                        @php
                                            $classBold =
                                                !empty($category['subcategories']) &&
                                                isset($category['subcategories'][0])
                                                    ? 'has-children'
                                                    : 'has-parent';
                                            $selected =
                                                $dataArray['item']['new_category_id'] == $category['id']
                                                    ? 'selected'
                                                    : '';
                                        @endphp
                                        <li class="category {{ $classBold }} {{ $selected }}"
                                            data-id="{{ $category['id'] }}"
                                            data-catname="{{ $category['category_name'] }}">
                                            <span>{{ $category['category_name'] }}</span>
                                            @if (!empty($category['subcategories']))
                                                <ul class="subcategories">
                                                    @foreach ($category['subcategories'] as $subcategory)
                                                        @include('partials.subcategory-optgroup', [
                                                            'subcategory' => $subcategory,
                                                            'sub_category_id' => $subcategory['id'],
                                                            'sub_category_name' => $subcategory['category_name'],
                                                        ])
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            {{-- Hidden field --}}
                            <input type="hidden" name="new_category_id" class="new_cat_id_item"
                                value="{{ !empty($dataArray['select_category']) && isset($dataArray['select_category']['id']) ? $dataArray['select_category']['id'] : '0' }}"
                                {{ $isRestricted ? 'readonly' : '' }}>
                            {{-- Validation popup --}}
                            <div class="popup-container" id="newCategoryRequiredPopup">
                                <p><span class="required-icon">!</span>Please select a new category.</p>
                            </div>
                            {{-- Optional notice --}}
                            @if ($isRestricted)
                                <small class="text-danger">You are not allowed to change the category (SEO
                                    Executive/Intern).</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Sub Category Tags</h6>
                            <div class="col-sm-20" id="newKeywordsCols">
                                <input type="text" data-role="tagsinput" class="form-control new_key_words"
                                    id="newKeywords" name="new_keywords" placeholder="Add Sub Category tags"
                                    autocomplete="on" list="keywordsList" required=""
                                    value="{{ $dataArray['item']->new_related_tags }}" />
                                <datalist id="keywordsList"></datalist>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <h6>Filter Row</h6>
                <hr>
                {{-- Start New Rows --}}

                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Languages</h6>
                            <div class="col-sm-20">
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
                                    name="lang_id[]" multiple required>
                                    @foreach ($dataArray['langArray'] as $lang)
                                        @if ($helperController::stringContain($dataArray['item']->lang_id, $lang->id))
                                            <option value="{{ $lang->id }}" selected="">
                                                {{ $lang->name }}
                                            </option>
                                        @else
                                            <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                        @endif
                                    @endforeach

                                </select>
                                <!-- <input type="hidden" id="selectedLanguages" name="lang_id">-->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Theme</h6>
                            <div class="col-sm-20">
                                @php
                                    if (isset($dataArray['item']->theme_id) && $dataArray['item']->theme_id != '') {
                                        $dataArray['themeArray'] = $helperController::filterArrayOrder(
                                            $dataArray['item']->theme_id,
                                            $dataArray['themeArray'],
                                            'name',
                                        );
                                    }
                                @endphp
                                <select class="custom-select2 form-control" data-style="btn-outline-primary"
                                    multiple="multiple" id="themeIds" name="theme_id[]"
                                    data-selection-opt='{{ $dataArray['item']->theme_id }}' required>
                                    @foreach ($dataArray['themeArray'] as $theme)
                                        @if ($helperController::stringContain($dataArray['item']->theme_id, $theme->id))
                                            <option value="{{ $theme->id }}" selected="">
                                                {{ $theme->name }}
                                            </option>
                                        @else
                                            <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" id="selectedThemes" name="themes_ids">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Style</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="styles[]" required>
                                    @foreach ($dataArray['styleArray'] as $style)
                                        @if ($helperController::stringContain($dataArray['item']->style_id, $style->id))
                                            <option value="{{ $style->id }}" selected="">
                                                {{ $style->name }}
                                            </option>
                                        @else
                                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Orientation</h6>
                            <div class="col-sm-20">
                                <select class="form-control" data-style="btn-outline-primary" id="orientation"
                                    name="orientation" required>
                                    @foreach ($helperController::getOrientations() as $orientation)
                                        <option value="{{ $orientation }}"
                                            {{ $orientation == $dataArray['item']->orientation ? 'selected' : '' }}>
                                            {{ \Str::title($orientation) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Size</h6>
                            <select name="template_size" id="sizeInput" class="form-control">
                                <option value="">== none ==</option>
                                @foreach ($dataArray['sizes'] as $size)
                                    @php
                                        $orientation = $dataArray['item']->orientation;
                                        $currentOrientation = 'portrait';
                                        if ($size->width_ration == $size->height_ration) {
                                            $currentOrientation = 'square';
                                        } elseif ($size->width_ration > $size->height_ration) {
                                            $currentOrientation = 'landscape';
                                        }

                                        $disabled = $orientation != $currentOrientation ? '' : '';
                                    @endphp

                                    @if (isset($dataArray['item']->template_size))
                                        @if ($dataArray['item']->template_size == $size->id)
                                            <option value="{{ $size->id }}" selected {{ $disabled }}>
                                                {{ $size->size_name }}
                                            </option>
                                        @else
                                            <option value="{{ $size->id }}" {{ $disabled }}>
                                                {{ $size->size_name }}</option>
                                        @endif
                                    @else
                                        <option value="{{ $size->id }}" {{ $disabled }}>
                                            {{ $size->size_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Relegion</h6>
                            <div class="col-sm-20">
                                @php
                                    if (
                                        isset($dataArray['item']->religion_id) &&
                                        $dataArray['item']->religion_id != ''
                                    ) {
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
                                        @if ($helperController::stringContain($dataArray['item']->religion_id, $religion->id))
                                            <option value="{{ $religion->id }}" selected="">
                                                {{ $religion->religion_name }}
                                            </option>
                                        @else
                                            <option value="{{ $religion->id }}">{{ $religion->religion_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                {{-- <input type="hidden" id="selectedReligions" name="religion_ids"> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
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
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" id="interestIds" name="interest_id[]">

                                    @foreach ($dataArray['interestArray'] as $interest)
                                        @if ($helperController::stringContain($dataArray['item']->interest_id, $interest->id))
                                            <option value="{{ $interest->id }}" selected="">
                                                {{ $interest->name }}</option>
                                        @else
                                            <option value="{{ $interest->id }}">{{ $interest->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            {{-- <input type="hidden" id="selectedInterests" name="interest_ids"> --}}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Is Premium</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
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
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Is Freemium</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                    name="is_freemium">
                                    @if ($dataArray['item']->is_freemium == '1')
                                        <option value="1" selected>TRUE</option>
                                        <option value="0">FALSE</option>
                                    @else
                                        <option value="1">TRUE</option>
                                        <option value="0" selected>FALSE</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6 style="max-">Select Date Range</h6>
                            @if ($dataArray['item']->start_date != null || $dataArray['item']->start_date != '')
                                <input class="form-control datetimepicker-range" placeholder="Select Date"
                                    value="{{ $dataArray['item']->start_date }} - {{ $dataArray['item']->end_date }}"
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
                </div>
                <br>

                <br>
                <h6>Others Row</h6>
                <hr>
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Ratio</h6>
                            <input class="form-control" id="ratio" type="textname" name="ratio"
                                value="{{ $dataArray['item']->ratio }}" required>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Width</h6>
                            <input class="form-control" id="width" type="textname" name="width"
                                value="{{ $dataArray['item']->width }}" required>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Height</h6>
                            <input class="form-control" id="height" type="textname" name="height"
                                value="{{ $dataArray['item']->height }}" required>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Status</h6>
                            <div class="col-sm-20">
                                <select class="selectpicker status form-control" data-style="btn-outline-primary"
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
                    </div>
                </div>

                <!-- <div class="row"> -->
                @include('partials.cta_section', ['ctaSection' => $dataArray['item']->cta])
                <!-- </div> -->

                @if($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <h6>Priority</h6>
                            <input type="number" class="form-control priority-field" id="priority" name="priority"
                                placeholder="0.90" step="0.01" min="0" max="1"
                                value="{{ $dataArray['item']->priority ?? 0.90 }}">
                            <small class="text-muted">Value between 0.00 and 1.00</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <h6>Frequency</h6>
                            <select id="frequency" class="form-control frequency-field" name="frequency">
                                @php
                                    $frequency = $dataArray['item']->frequency ?? 'daily';
                                @endphp
                                <option value="daily" {{ $frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <div>
                    <input class="btn btn-primary submit-btn" type="submit" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>
<datalist id="font_list">
    @foreach ($dataArray['fonts'] as $font)
        <option value="{{ $font->name }}.{{ $font->extension }}"></option>
    @endforeach
</datalist>

<datalist id="related_tag_list">
    @foreach ($dataArray['searchTagArray'] as $searchTag)
        <option value="{{ $searchTag->name }}"></option>
    @endforeach
</datalist>
</div>
<script src="{{ asset('assets/vendors/scripts/core.js') }}"></script>
<script src="{{ asset('assets/vendors/scripts/script.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>
<script src="{{ asset('assets/js/role_access.js') }}"></script>
{{-- @include('layouts.masterscript') --}}
<script>
    function setDefaultThumb(id) {
        $('#default_thumb_pos').val(id);
        $('.default_btns').removeClass('btn-secondary');
        $('.default_btns').removeClass('btn-primary');
        $('.default_btns').addClass('btn-primary');
        $('#default_btn_' + id).addClass('btn-secondary');
        $('.default_btns').html('Set Default Thumb');
        $('#default_btn_' + id).html('Defaulted Thumb');
    }

    // Post Thumb preview function
    function previewPostThumb(event) {
        const input = event.target;
        const preview = document.getElementById('postThumbPreview');
        const noMsg = document.getElementById('noPostThumbMsg');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF, or WEBP)');
                input.value = '';
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2048 * 1024) {
                alert('Image size must be less than 2MB');
                input.value = '';
                return;
            }
            
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            if (noMsg) noMsg.style.display = 'none';
        }
    }

    // Additional Thumb preview and remove functions
    function previewAdditionalThumb(event) {
        const input = event.target;
        const preview = document.getElementById('additionalThumbPreview');
        const removeBtn = document.getElementById('removeAdditionalThumbBtn');
        const noMsg = document.getElementById('noAdditionalThumbMsg');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF, or WEBP)');
                input.value = '';
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2048 * 1024) {
                alert('Image size must be less than 2MB');
                input.value = '';
                return;
            }
            
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            removeBtn.style.display = 'inline-block';
            if (noMsg) noMsg.style.display = 'none';
        }
    }

    function removeAdditionalThumb() {
        if (!confirm('Are you sure you want to remove the Additional Thumb image?')) {
            return;
        }

        const designId = document.querySelector('input[name="id"]').value;
        
        // Send AJAX request to remove from database
        $.ajax({
            url: '{{ url("remove-additional-thumb") }}/' + designId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Clear the input and preview
                    const input = document.getElementById('additional_thumb');
                    const preview = document.getElementById('additionalThumbPreview');
                    const removeBtn = document.getElementById('removeAdditionalThumbBtn');
                    const noMsg = document.getElementById('noAdditionalThumbMsg');
                    const currentFileText = preview.nextElementSibling?.nextElementSibling;

                    input.value = null;
                    preview.src = '';
                    preview.style.display = 'none';
                    removeBtn.style.display = 'none';
                    if (noMsg) noMsg.style.display = 'block';
                    if (currentFileText) currentFileText.style.display = 'none';

                    alert('Additional Thumb removed successfully!');
                } else {
                    alert('Error: ' + (response.error || 'Failed to remove image'));
                }
            },
            error: function(xhr) {
                console.error('Remove error:', xhr);
                alert('Failed to remove Additional Thumb. Please try again.');
            }
        });
    }

    window.addEventListener("load", function() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
        var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
        var tagsInput = tagsInputContainer.querySelector('input[type="text"]');

        if (tagsInput) {
            tagsInput.setAttribute('list', 'related_tag_list');
            tagsInput.setAttribute('autocomplete', 'on');
            tagsInput.style.width = '100%';
            tagsInput.style.height = '45px';
            tagsInput.style.border = '1px solid #000000';
            tagsInput.style.borderRadius = '5px';
            tagsInput.style.marginTop = '5px';
        }

        // $('#colorTags').tagsinput('remove', $(currentTag).text());
        // $('#colorTags').tagsinput('add', colorHex);
    });

    total_pages = {{ count($dataArray['thumbs']) }};
    current_page = 0;

    var sizeArray;

    $('#page_list').on('click', 'a[data-toggle="tab"]', function(e) {
        var activeTab = $(e.target);
        var pageId = activeTab.attr('href');
        current_page = pageId.replaceAll("#page_", "");
    });

    function selectChangeFunc(id) {
        if ($('#bg_type_id_' + id).val() === '0' || $('#bg_type_id_' + id).val() === '1') {
            $('#back_image_' + id).attr('required', '');
            var x = document.getElementById("back_image_field_" + id);
            x.style.display = "block";

            $('#color_code_' + id).removeAttr('required');
            var x1 = document.getElementById("color_code_field_" + id);
            x1.style.display = "none";

        } else {
            $('#color_code_' + id).attr('required', '');
            var x = document.getElementById("color_code_field_" + id);
            x.style.display = "block";

            $('#back_image_' + id).removeAttr('required');
            var x1 = document.getElementById("back_image_field_" + id);
            x1.style.display = "none";
        }
    }

    /* Filter and selected reordering options and value are set on inputed field when the form submit */

    const initialSetFilteredData = () => {
        var keywords = "";
        $("#relatedKeyword span.tag.label.label-info").each(function(index, value) {
            var keywordVal = $(this).text();
            if (keywords == "") {
                keywords = keywordVal;
            } else {
                keywords = keywords + ',' + keywordVal;
            }
        });

        $("#keywords").val(keywords);

        var selectedValues = "";
        $("select[name='religion_id[]']").next().find(
            ".select2-selection--multiple .select2-selection__rendered li").each(function(index, value) {
            var text = $(this).text().trim();
            text = text.replace('×', '');

            var optionValue = $("select[name='religion_id[]'] option").filter(function() {
                return $(this).text().trim() === text;
            }).val();

            if (selectedValues == "") {
                selectedValues = optionValue;
            } else {
                selectedValues = selectedValues + ',' + optionValue;
            }
        });

        selectedValues && (selectedValues = removeUndefineValue(selectedValues));

        $("#selectedReligions").val(selectedValues);

        var selectedColorIds = "";
        $("select[name='color_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
            .each(function(index, value) {
                var text = $(this).text().trim();
                text = text.replace('×', '');

                var optionValue = $("select[name='color_id[]'] option").filter(function() {
                    return $(this).text().trim() === text;
                }).val();

                if (selectedColorIds == "") {
                    selectedColorIds = optionValue;
                } else {
                    selectedColorIds = selectedColorIds + ',' + optionValue;
                }
            });
        selectedColorIds = removeUndefineValue(selectedColorIds);
        $("#selectedColors").val(selectedColorIds);

        var selectedThemes = "";
        $("select[name='theme_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
            .each(function(index, value) {
                var val = $(this).text().trim();
                val = val.replace('×', '');

                if (selectedThemes == "") {
                    selectedThemes = val;
                } else {
                    selectedThemes = selectedThemes + ',' + val;
                }
            });
        $("#selectedThemes").val(selectedThemes);

        var selectedIntrests = "";
        $("select[name='interest_id[]']").next().find(
            ".select2-selection--multiple .select2-selection__rendered li").each(function(index, value) {
            var text = $(this).text().trim();
            text = text.replace('×', '');

            var optionValue = $("select[name='interest_id[]'] option").filter(function() {
                return $(this).text().trim() === text;
            }).val();

            if (selectedIntrests == "") {
                selectedIntrests = optionValue;
            } else {
                selectedIntrests = selectedIntrests + ',' + optionValue;
            }
        });

        selectedIntrests && (selectedIntrests = removeUndefineValue(selectedIntrests));
        $("#selectedInterests").val(selectedIntrests);


        var selectedLanguageIds = "";
        $("select[name='lang_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
            .each(function(index, value) {
                var text = $(this).text().trim();
                text = text.replace('×', '');
                var optionValue = $("select[name='lang_id[]'] option").filter(function() {
                    return $(this).text().trim() === text;
                }).val();

                if (selectedLanguageIds == "") {
                    selectedLanguageIds = optionValue;
                } else {
                    selectedLanguageIds = selectedLanguageIds + ',' + optionValue;
                }
            });
        selectedLanguageIds = removeUndefineValue(selectedLanguageIds);
        $("#selectedLanguages").val(selectedLanguageIds);
    }

    const removeUndefineValue = (string) => {
        if (!string || typeof string !== 'string') return '';

        var values = string.split(',');
        values = values.filter(function(value) {
            return value !== 'undefined';
        });
        return values.join(',');
    }


    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        count = $("#page_list").children().length;

        if ($("input[name='new_category_id']") && $("input[name='new_category_id']").val() == "0" || $(
                "input[name='new_category_id']").val() == "") {
            $("#newCategoryRequiredPopup").show();
            event.preventDefault();
            return false;
        }

        if (count == 0) {
            alert('Add atleast 1 page.');
            return;
        }

        initialSetFilteredData()

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        document.querySelectorAll('.txt_update_class').forEach(function(select) {
            select.disabled = false;
        });

        var formData = new FormData(this);
        
        // Debug: Log file inputs
        var postThumbFile = document.getElementById('post_thumb').files[0];
        var additionalThumbFile = document.getElementById('additional_thumb').files[0];
        
        console.log('Post Thumb file:', postThumbFile ? postThumbFile.name : 'No file selected');
        console.log('Additional Thumb file:', additionalThumbFile ? additionalThumbFile.name : 'No file selected');
        
        // Ensure files are in FormData
        if (postThumbFile) {
            formData.set('post_thumb', postThumbFile);
            console.log('Post Thumb added to FormData');
        }
        if (additionalThumbFile) {
            formData.set('additional_thumb', additionalThumbFile);
            console.log('Additional Thumb added to FormData');
        }
        
        var url = "{{ route('item.update_seo', [$dataArray['item']->id]) }}";
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,  // Don't process the files
            contentType: false,  // Set content type to false as jQuery will tell the server its a query string request
            cache: false,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                document.querySelectorAll('.txt_update_class').forEach(function(select) {
                    select.disabled = true;
                });

                hideFields();
                if (data.error) {
                    console.error('Update error:', data.error);
                    window.alert('Error: ' + data.error);
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    console.log('Update success:', data.success);
                    window.alert('Success: ' + data.success + '\n\nPage will reload to show updated images.');
                    $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                    
                    // Reload page after 1.5 seconds to show updated images
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }

                setTimeout(function() {
                    $('#result').html('');
                }, 3000);

            },
            error: function(error) {
                document.querySelectorAll('.txt_update_class').forEach(function(select) {
                    select.disabled = true;
                });
                hideFields();
                
                console.error('AJAX error:', error);
                var errorMsg = 'An error occurred while updating.';
                
                if (error.responseJSON && error.responseJSON.message) {
                    errorMsg = error.responseJSON.message;
                } else if (error.responseJSON && error.responseJSON.error) {
                    errorMsg = error.responseJSON.error;
                } else if (error.responseText) {
                    errorMsg = error.responseText;
                }
                
                window.alert('Error: ' + errorMsg);
                $('#result').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    $(document).on('click', '#remove_layer', function() {
        $(this).closest(".row").remove();
    });

    function remove_page(id) {
        count = $("#page_list").children().length;
        if (count <= 1) {
            alert('Need at least 1 page');
            return;
        }

        $('#li_page_' + id).remove();
        $('#page_' + id).remove();
        $('#page_number_' + id).remove();
        count = 1;
        pageId = 0;
        $('#page_list li a').each(function(e) {
            var li = $(this);
            if (count === 1) {
                pageId = li.attr('href');
                pageId = pageId.replaceAll("#page_", "");
            }
            li.text('Page ' + count);
            count++;
        });
        current_page = pageId;
        $('#a_link_' + pageId).click();
    }

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

    $(document).ready(function() {
        $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr("list", "keywordsList");
        $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr("style",
            "width: 100%; height: 45px; border: 1px solid rgb(0, 0, 0); border-radius: 5px; margin-top: 5px;"
        );

        if ($("input[name='new_category_id']").val() != "" && $("input[name='new_category_id']").val() != "0") {
            loadNewSearchKeywords($("input[name='new_category_id']").val())
        }

        // Ensure Post Thumb and Additional Thumb fields are enabled
        $('#post_thumb').prop('disabled', false).css({
            'background-color': 'white',
            'cursor': 'pointer',
            'pointer-events': 'auto'
        });
        
        $('#additional_thumb').prop('disabled', false).css({
            'background-color': 'white',
            'cursor': 'pointer',
            'pointer-events': 'auto'
        });

        // Ensure Priority and Frequency fields are enabled for Admin and SEO Manager
        $('.priority-field, .frequency-field').prop('disabled', false).css({
            'background-color': 'white',
            'cursor': 'pointer',
            'pointer-events': 'auto',
            'opacity': '1'
        });

        console.log('Post Thumb and Additional Thumb fields enabled');
        console.log('Priority and Frequency fields enabled');
    });

    const loadNewSearchKeywords = (newCatId) => {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: "{{ route('getNewSearchTag') }}",
            type: 'POST',
            data: {
                cateId: newCatId
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert('error==>' + data.error);
                } else {
                    if (data.success) {
                        $("#related_new_tag_list").html("");
                        var newSearchTags = data.success;
                        $("#keywordsList").empty();
                        newSearchTags.forEach(tag => {
                            $("#keywordsList").append(`<option value="${tag.name}"></option>`);
                        });

                    }
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
        })
    }

    document.getElementById('orientation').addEventListener('change', function() {
        var orientation = this.value;

        if (sizeArray) {
            $("#sizeInput").html("");
            sizeArray.forEach(size => {
                var currentOrientation = "portrait";
                if (size.width_ration == size.height_ration) {
                    currentOrientation = "square";
                } else if (size.width_ration > size.height_ration) {
                    currentOrientation = "landscape";
                }
                // $("#sizeInput").append(`<option value="${size.id}" ${orientation != currentOrientation && 'disabled'}>${size.size_name}</option>`);
                $("#sizeInput").append(`<option value="${size.id}">${size.size_name}</option>`);
            });
        } else {
            var id = $("input[name='new_category_id']").val();

            if (id != "0") {
                loadSize(id).val();
            }

        }

    });

    const loadSize = (newCatId) => {
        $("#sizeInput").html("");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: "{{ route('getSizeList') }}",
            type: 'POST',
            data: {
                cateId: newCatId
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert('error==>' + data.error);
                } else {
                    if (data.success) {
                        sizeArray = data.data;
                        var orientation = $("#orientation").val();
                        sizeArray.forEach(size => {
                            var currentOrientation = "portrait";
                            if (size.width_ration == size.height_ration) {
                                currentOrientation = "square";
                            } else if (size.width_ration > size.height_ration) {
                                currentOrientation = "landscape";
                            }
                            // $("#sizeInput").append(`<option value="${size.id}" ${orientation != currentOrientation && 'disabled'}>${size.size_name}</option>`);
                            $("#sizeInput").append(
                                `<option value="${size.id}">${size.size_name}</option>`);
                        });

                    }
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
        })
    }

    const loadTheme = (newCatId) => {
        $("#themeIds").html("");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: "{{ route('getThemeList') }}",
            type: 'POST',
            data: {
                cateId: newCatId
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert('error==>' + data.error);
                } else {
                    console.log("data -> data :::: ");
                    console.log(data.data);
                    if (data.success) {
                        var themeArray = data.data;
                        console.log("themeArray :::: ");
                        console.log(themeArray);
                        themeArray.forEach(theme => {
                            $("#themeIds").append(
                                `<option value="${theme.id}">${theme.name}</option>`);
                        });

                    }
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
        })
    }

    const loadInterest = (newCatId) => {
        $("#interestIds").html("");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: "{{ route('getInterestList') }}",
            type: 'POST',
            data: {
                cateId: newCatId
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert('error==>' + data.error);
                } else {
                    if (data.success) {
                        var interestArray = data.data;
                        interestArray.forEach(interest => {
                            $("#interestIds").append(
                                `<option value="${interest.id}">${interest.name}</option>`);
                        });

                    }
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
        })
    }

    $(document).on('click', '#newKeywordsCols input[type="text"]', function() {
        $(".error-message").remove();
        if ($("input[name='new_category_id']").val() == "0") {
            $('#newKeywords').after(
                '<span class="error-message" style="color:red;">Please select a new category.</span>');
        }
    });

    $(document).on('click', '#parentCategoryInput', function() {
        $("#newCategoryRequiredPopup").hide();
        if ($('.parent-category-input').hasClass('show')) {
            $('.parent-category-input').removeClass('show');
        } else {
            $(".parent-category-input").addClass('show');
        }
    });

    $(document).on("click", ".category", function(event) {
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        $("input[name='new_category_id']").val(id);
        $("#parentCategoryInput span").html($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");

        $("#keywordsList").empty();
        $("#sizeInput").html("");
        $("#themeIds").html("");
        $("#interestIds").html("");
        sizeArray = null;

        if (id) {
            loadNewSearchKeywords(id);
            loadSize(id);
            loadTheme(id);
            loadInterest(id);
        }


    });

    $(document).on("click", ".subcategory", function(event) {
        event.stopPropagation();
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        var parentId = $(this).data('pid');
        $("input[name='new_category_id']").val(id);
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html($(this).data('catname'));
        $(this).addClass("selected");

        $("#keywordsList").empty();
        $("#sizeInput").html("");
        $("#themeIds").html("");
        $("#interestIds").html("");

        sizeArray = null;

        if (id) {
            loadNewSearchKeywords(id);
            loadSize(id);
            loadTheme(id);
            loadInterest(id);
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    $(document).on("click", "li.category.none-option", function() {
        $("input[name='new_category_id']").val("0");
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html('== none ==');
    });

    $('#categoryFilter').on('input', function() {
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
    jQuery.noConflict();

    jQuery(document).ready(function($) {
        $('.col-sm-20.color_tags input[type="text"]').prop('readonly', true);
        $('.col-sm-20.color_tags input[type="text"]').css('min-width', '417px !important');
        $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        $('.col-sm-20.color_tags input[type="text"]').on('keydown', function(event) {
            event.preventDefault();
            $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        });
        $('.col-sm-20.color_tags input[type="text"]').on('keyup', function(event) {
            event.preventDefault();
            $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        });
        $('.col-sm-20.color_tags input[type="text"]').on('keypress', function(event) {
            event.preventDefault();
            $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
        });



        var colorData = @json($dataArray['item']->color_id);
        const colorIds = colorData ? colorData.split(",") : "";

        for (const colorId of colorIds) {
            $('#colorTags').tagsinput('add', colorId);
            setTagBackgroundColor(colorId);
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
                // if( currentColor != null){
                //     var currentColorHex = rgbToHex(currentColor);
                // }
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
                console.log($(this).text());
                colorsCode.push($(this).text())
            });
            // Mapping Color Codes
            var existColorCodeVal = $("input[name='color_ids']").val();
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

        // Restore page index from session storage
        // let pageIndex = sessionStorage.getItem("pageIndex");
        // if (pageIndex) {
        //     $(".li_page_class .nav-link").removeClass("active");
        //     $("#a_link_" + pageIndex).addClass("active");
        // }

        // Make tags sortable
        // $(".bootstrap-tagsinput").sortable({
        //     items: "> .tag",
        //     axis: "x",
        //     containment: "parent",
        //     tolerance: "pointer",
        //     cursor: "move",
        //     start: function(event, ui) {
        //         $(this).find('input').prop('disabled', true);
        //     },
        //     stop: function(event, ui) {
        //         $(this).find('input').prop('disabled', false);
        //     }
        // });


        $(".bootstrap-tagsinput").sortable({
            items: "> .tag",
            axis: "x",
            containment: "parent",
            tolerance: "pointer",
            cursor: "move",
            distance: 5,
            helper: "clone",
            start: function(event, ui) {
                $(this).find('input').prop('disabled', true);
                ui.helper.addClass('sorting');
            },
            stop: function(event, ui) {
                $(this).find('input').prop('disabled', false);
                $(ui.item).removeClass('sorting');
                $(this).sortable("refreshPositions");
            },
            sort: function(event, ui) {
                ui.helper.css('transform', 'scale(1.1)'); // Optional: scale up while dragging
            }
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

        const toKebabCase = str => str.toLowerCase().replace(/\s+/g, '-');

        $("#post_name").on("input", function() {
            const strId = $(this).data("strid");
            const postName = $(this).val(); // No title case applied here
            const idName = `${strId}-${toKebabCase(postName)}`;

            $("#id_name").val(idName);
        });

    });

    // $(document).on("click",".li_page_class",function(){
    //     let page = $(this).data("page");
    //     sessionStorage.setItem("pageIndex", page);
    // });

    $('#arrowIcon').on('click', function() {
        if ($("#suggestionsNewContainer").css('display') == "none") {
            $("#suggestionsNewContainer").show();
        } else {
            $("#suggestionsNewContainer").hide();
        }
    });

    $(document).on("click", "div#suggestionsNewContainer ul li", function() {
        var $input = $('#newKeywordsCols input[type="text"]');
        var newWord = $(this).text().trim();
        var currentKeywords = $("#newKeywords").val().trim();
        var keywordsArray = currentKeywords ? currentKeywords.split(',').map(function(keyword) {
            return keyword.trim();
        }) : [];

        if (!keywordsArray.includes(newWord)) {
            keywordsArray.push(newWord);
        }
        var updatedKeywords = keywordsArray.join(', ');
        $("#newKeywords").val(updatedKeywords);
        $input.val(updatedKeywords);
        $("div#suggestionsNewContainer").hide();

    });

    function updateCount(input, counterId) {
        const max = 60;
        const remaining = max - input.value.length;
        document.getElementById(counterId).textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    // set count for pre-filled values
    document.addEventListener("DOMContentLoaded", function() {
        updateCount(document.getElementById('meta_title'), 'metaCounter');
    });

    function updateCount(input, counterId) {
        const max = parseInt(input.getAttribute('maxlength')) || 50;
        const remaining = max - input.value.length;
        document.getElementById(counterId).textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('post_name');
        updateCount(input, 'postNameCounter'); // initial count
        input.addEventListener('input', function() {
            updateCount(input, 'postNameCounter');
        });
    });
</script>

</body>

</html>
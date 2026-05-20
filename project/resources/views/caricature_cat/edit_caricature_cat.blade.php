@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div
    class="main-container {{ !empty($isDisableAll) && $isDisableAll ? 'disable-container' : 'seo-access-container' }} {{ !empty($previewMode) && $previewMode && $roleManager::isAdminOrSeoManager(Auth::user()->user_type) ? 'preview-mode' : '' }}">
    @include('partials.density_checker', [
        'title' => 'NewCategory Page',
        'slug' => $datas['cat']->id_name,
        'type' => 4,
        'primary_keyword' => $datas['cat']->primary_keyword,
        'changeLog' => $changeLog ?? [],
    ])

    <div class="">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <h6>Category Name</h6>
                                <input class="form-control" type="textname" name="category_name"
                                    value="{{ $datas['cat']->category_name }}" id="categoryName" required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <h6>ID Name</h6>
                                <input class="form-control" type="textname" name="id_name" id="categoryIDName"
                                    value="{{ $datas['cat']->id_name }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <div class="input-group custom mb-0">
                                    <input type="text" class="form-control canonical_link" name="canonical_link"
                                        value="{{ $datas['cat']->canonical_link }}" />
                                </div>
                            </div>
                        </div>
                        @php
                            $assignedSeoId = $datas['cat']->seo_emp_id ?? '';
                        @endphp
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Assign SEO</h6>
                                <div class="input-group custom mb-0">
                                    <select name="seo_emp_id" class="form-control custom-select2 assign-seo">
                                        <option value="">-- Select SEO --</option>
                                        @foreach ($userRole as $user)
                                            <option value="{{ $user->id }}"
                                                {{ $user->id == $assignedSeoId ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Title</h6>
                                <input class="form-control" type="text" name="meta_title" maxlength="60"
                                    oninput="updateMetaCount(this)" value="{{ $datas['cat']->meta_title }}" required>
                                <small id="metaCounter" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Primary Keyword</h6>
                                <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                    name="primary_keyword" placeholder="Enter Primary Keyword" required
                                    value="{{ $datas['cat']->primary_keyword ?? '' }}">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>H1 Tag</h6>
                                <input class="form-control" type="text" name="h1_tag" id="h1_tag" maxlength="60"
                                    oninput="updateCount(this, 'h1Counter')" value="{{ $datas['cat']->h1_tag }}"
                                    required>
                                <small id="h1Counter" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Tag Line</h6>
                                <input class="form-control" type="textname" name="tag_line"
                                    value="{{ $datas['cat']->tag_line }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="meta_desc">{{ $datas['cat']->meta_desc }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Short Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="short_desc">{{ $datas['cat']->short_desc }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>H2 Tag</h6>
                                <input class="form-control" type="textname" name="h2_tag"
                                    value="{{ $datas['cat']->h2_tag }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Long Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="long_desc">{{ $datas['cat']->long_desc }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>

                        <div class="input-subcategory-dropbox 
                            {{ !empty($previewMode) && $previewMode && $roleManager::isAdminOrSeoManager(Auth::user()->user_type) ? 'disabled-category' : '' }}"
                            id="parentCategoryInput"
                            @if (!empty($previewMode) && $previewMode && $roleManager::isAdminOrSeoManager(Auth::user()->user_type)) style="pointer-events: none; opacity: 0.6;" @endif>
                            <span>
                                @if (!empty($datas['parent_category']) && isset($datas['parent_category']->category_name))
                                    {{ $datas['parent_category']->category_name }}
                                @else
                                    == none ==
                                @endif
                            </span>
                            <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                        </div>

                        <div class="custom-dropdown parent-category-input"
                            @if (!empty($previewMode) && $previewMode && $roleManager::isAdminOrSeoManager(Auth::user()->user_type)) style="pointer-events: none; opacity: 0.6;" @endif>
                            <ul class="dropdown-menu-ul">
                                <li class="category none-option" data-id="0" data-catname="== none ==">== none ==
                                </li>

                                @foreach ($datas['allCategories'] as $category)
                                    @php
                                        $classBold =
                                            !empty($category['subcategories']) && isset($category['subcategories'][0])
                                                ? 'has-children'
                                                : 'has-parent';

                                        $selected =
                                            isset($datas['parent_category']->id) &&
                                            $datas['parent_category']->id == $category['id']
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
                    </div>

                    <input type="hidden" name="parent_category_id"
                        value="{{ isset($datas['cat']->parent_category_id) ? $datas['cat']->parent_category_id : '0' }}"
                        class="parent_cat_access"
                        {{ !empty($previewMode) && $previewMode && $roleManager::isAdminOrSeoManager(Auth::user()->user_type) ? 'readonly' : '' }}>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Category Thumb</h6>
                                <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                    class="form-control-file form-control height-auto dynamic-file"
                                    data-value="{{ $contentManager::getStorageLink($datas['cat']->category_thumb) }}"
                                    data-imgstore-id="category_thumb" data-nameset="true" />
                                <br />
                                <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->category_thumb }}" width="100" />
                                <input class="form-control" type="textname" id="cat_thumb_path" name="cat_thumb_path"
                                  value="{{ $datas['cat']->category_thumb }}" style="display: none"> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Mockup</h6>
                                <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                    class="form-control-file form-control height-auto dynamic-file"
                                    data-imgstore-id="mockup"
                                    data-value="{{ $contentManager::getStorageLink($datas['cat']->mockup) }}"
                                    data-nameset="true" />
                                <br />
                                <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->mockup }}" width="100" />
                                <input class="form-control" type="textname" id="mockup_path" name="mockup_path"
                                  value="{{ $datas['cat']->mockup }}" style="display: none"> -->
                            </div>

                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Banner</h6>
                        <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                            class="form-control-file form-control height-auto dynamic-file" data-imgstore-id="banner"
                            data-required="false"
                            data-value="{{ $contentManager::getStorageLink($datas['cat']->banner) }}"
                            data-nameset="true"><br />
                        <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->banner }}" width="100" />
                        <input class="form-control" type="textname" id="banner_path" name="banner_path"
                          value="{{ $datas['cat']->banner }}" style="display: none"> -->
                    </div>

                    @include('partials.content_section', [
                        'contents' => $datas['cat']->contents ?? old('contents'),
                        'ctaSection' => $page->cta ?? [],
                    ])

                    <div style="margin-bottom: 10px;">
                        @include('partials.faqs_section', ['faqs' => $datas['cat']->faqs ?? ''])
                    </div>
                    @include('partials.top_template_categories', [
                        'top_keywords' => $datas['cat']->top_keywords,
                    ])


                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="textname" name="sequence_number"
                            value="{{ $datas['cat']->sequence_number }}" required>
                    </div>
                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control status" data-style="btn-outline-primary"
                                name="status">
                                @if ($datas['cat']->status == '1')
                                    <option value="1" selected>LIVE</option>
                                    <option value="0">NOT LIVE</option>
                                @else
                                    <option value="1">LIVE</option>
                                    <option value="0" selected>NOT LIVE</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    @if($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                    <div class="row seo-all-container">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Priority</h6>
                                <input type="number" class="form-control seo-all-container" id="priority" name="priority"
                                    placeholder="0.90" step="0.01" min="0" max="1"
                                    value="{{ $datas['cat']->priority ?? 0.90 }}">
                                <small class="text-muted">Value between 0.00 and 1.00</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Frequency</h6>
                                <select id="frequency" class="form-control seo-all-container" name="frequency">
                                    @php
                                        $frequency = $datas['cat']->frequency ?? 'daily';
                                    @endphp
                                    <option value="daily" {{ $frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <div>
                            <input
                                class="btn btn-primary {{ empty($previewMode) || !$previewMode ? 'seo-submit' : '' }} mr-2"
                                type="submit" name="submit">
                        </div>
                        <div class="d-flex">
                            @if (!empty($previewMode) && $previewMode)
                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) && $previewStatus == 0)
                                    @include('partials.pending_task', ['id' => $previewId])
                                @endif
                                <input name="isPreview" value="false" type="hidden" />
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('partials.error_dialog')

@include('layouts.masterscript')

<script>
    //    if (!isPreviewMode) {
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        const parentDiv = document.querySelector('#sortable');
        if (parentDiv.children.length == 0) {
            showAlertModal('Please add top keywords.');
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        var url = "{{ route('update_cari_cat', [$previewId ?? $datas['cat']->id]) }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                document.getElementById("main_loading_screen").style.display = "block";
            },
            success: function(data) {
                hideFields();
                if (data.error) {
                    showAlertModal(data.error);
                } else {
                    alert("done");
                }
                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(xhr) {
                hideFields();
                let message = xhr.responseText || 'Something went wrong.';
                alert(message);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

    $(document).on('click', '#parentCategoryInput', function() {
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
        $("input[name='parent_category_id']").val(id);
        $("#parentCategoryInput span").html($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");
    });

    $(document).on("click", ".subcategory", function(event) {
        event.stopPropagation();
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        var parentId = $(this).data('pid');
        $("input[name='parent_category_id']").val(id);
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html($(this).data('catname'));
        $(this).addClass("selected");
    });

    $(document).on("click", "li.category.none-option", function() {
        $("input[name='parent_category_id']").val("0");
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html('== none ==');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1)
        .toLowerCase());

    $("#categoryName").on("input", function() {
        const titleString = toTitleCase($(this).val());
        $("#categoryIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });

    function updateMetaCount(input) {
        const max = 60;
        const remaining = max - input.value.length;
        document.getElementById('metaCounter').textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    // Run once when the page loads to set initial count
    document.addEventListener("DOMContentLoaded", function() {
        const metaTitleInput = document.querySelector('input[name="meta_title"]');
        if (metaTitleInput) {
            updateMetaCount(metaTitleInput);
        }
    });

    function updateCount(input, counterId) {
        const max = 60;
        const remaining = max - input.value.length;
        document.getElementById(counterId).textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    // Initialize on page load
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('h1_tag');
        if (input) {
            updateCount(input, 'h1Counter');
        }

    });
</script>

</body>

</html>

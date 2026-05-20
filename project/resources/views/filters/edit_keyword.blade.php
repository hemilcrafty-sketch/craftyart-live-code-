@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-access-container">



    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            @include('partials.density_checker', [
                'title' => 'Keyword Page',
                'slug' => $datas['data']->name,
                'type' => 3,
                'primary_keyword' => $datas['data']->primary_keyword,
            ])

            <div class="pd-20 card-box mb-30">
                <form method="post" id="edit_keyword_form" enctype="multipart/form-data">
                    @csrf
                    <input class="form-control" type="textname" id="keyword_id" name="keyword_id" style="display: none"
                        value="{{ $datas['data']->id }}" />

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Keyword</h6>
                                <div class="input-group custom">
                                    <input type="text" class="form-control" id="keyword" name="name"
                                        required="" value="{{ $datas['data']->name }}" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <div class="input-group custom mb-0">
                                    <input type="text" class="form-control canonical_link" name="canonical_link"
                                        value="{{ $datas['data']->canonical_link }}" />
                                </div>
                                <p class="text-end" style="font-size: 12px;">Only admin or fenil can modify canonical
                                    link</p>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <h6>Title</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control" id="title" name="title" maxlength="60"
                                    oninput="updateCount(this, 'titleCounter')" required
                                    value="{{ $datas['data']->title }}" />
                            </div>
                            <small id="titleCounter" class="text-muted"></small>
                        </div>

                        <div class="form-group col-md-6">
                            <h6>Primary Keyword</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                    name="primary_keyword" placeholder="Enter Primary Keyword" required
                                    value="{{ $datas['data']->primary_keyword ?? '' }}">
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <h6>H2 Tag</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                value="{{ $datas['data']->h2_tag }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Meta Title</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="60"
                                oninput="updateMetaCount(this)" required value="{{ $datas['data']->meta_title }}" />
                        </div>
                        <small id="metaCounter" class="text-muted"></small>
                    </div>

                    <div class="form-group">
                        <h6>Meta Desc</h6>
                        <div class="input-group custom">
                            <textarea class="form-control txtarea" id="meta_desc" name="meta_desc" oninput="autoResize(this)">{{ $datas['data']->meta_desc }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Short Desc</h6>
                        <div class="input-group custom">
                            <textarea class="form-control txtarea" oninput="autoResize(this)" id="short_desc" name="short_desc">{{ $datas['data']->short_desc }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Long Desc</h6>
                        <div class="input-group custom">
                            <textarea class="form-control txtarea" id="long_desc" oninput="autoResize(this)" name="long_desc">{{ $datas['data']->long_desc }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Banner</h6>
                        <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                            data-value="{{ $datas['data']->banner }}"
                            class="form-control-file dynamic-file form-control height-auto" data-imgstore-id="banner"
                            data-required=false data-nameset="true">
                        <br />
                        <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['data']->banner }}" width="100" /> -->
                        <!-- <input class="form-control" type="textname" id="banner_path" name="banner_path"
                            value="{{ $datas['data']->banner }}" style="display: none"> -->
                    </div>

                    @php
                        // Restrict users who are NOT Admin, SEO Manager, or SEO Executive
                        $isRestricted = !$roleManager::isAdminOrSeoManager(Auth::user()->user_type);
                    @endphp

                    <div class="form-group category-dropbox-wrap">
                        <h6>Category</h6>

                        {{-- Main dropdown --}}
                        <div class="input-subcategory-dropbox {{ $isRestricted ? 'disabled-category' : '' }}"
                            id="parentCategoryInput"
                            @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                            <span>
                                @if (isset($datas['cat']->category_name) && $datas['cat']->category_name != '')
                                    {{ $datas['cat']->category_name }}
                                @else
                                    {{ '== none ==' }}
                                @endif
                            </span>
                            <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                        </div>

                        {{-- Dropdown --}}
                        <div class="custom-dropdown parent-category-input"
                            @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                            <ul class="dropdown-menu-ul">
                                <li class="category none-option">== none ==</li>
                                @foreach ($datas['allCategories'] as $category)
                                    @php
                                        $classBold =
                                            !empty($category['subcategories']) && isset($category['subcategories'][0])
                                                ? 'has-children'
                                                : 'has-parent';
                                        $selected =
                                            isset($datas['data']->cat_id) && $datas['data']->cat_id == $category['id']
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

                    {{-- Hidden field for category_id --}}
                    <input type="hidden" name="category_id" class="new_cat_id_item"
                        value="{{ isset($datas['data']->cat_id) ? $datas['data']->cat_id : '0' }}"
                        {{ $isRestricted ? 'readonly' : '' }}>

                    {{-- Hidden field for id --}}
                    <div class="form-group">
                        <input class="form-control keyword-id" type="hidden" name="id"
                            value="{{ $datas['data']->id }}">
                    </div>

                    {{-- Optional notice for restricted users --}}
                    @if ($isRestricted)
                        <small class="text-danger">
                            You are not allowed to change the category (Only Admin, SEO Manager
                            can).
                        </small>
                    @endif


                    @include('partials.content_section', [
                        'contents' => $datas['data']->contents ?? old('contents'),
                        'ctaSection' => [],
                    ])
                    <div style="margin-bottom: 10px;">
                        @include('partials.faqs_section', ['faqs' => $datas['data']->faqs ?? ''])
                    </div>
                    @include('partials.top_template_categories', [
                        'top_keywords' => $datas['data']->top_keywords,
                    ])

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select id="status" class="selectpicker status form-control"
                                data-style="btn-outline-primary" name="status">
                                <option value="1" {{ $datas['data']->status == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ $datas['data']->status == 0 ? 'selected' : '' }}>
                                    Disable
                                </option>
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
                                    value="{{ $datas['data']->priority ?? 0.90 }}">
                                <small class="text-muted">Value between 0.00 and 1.00</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Frequency</h6>
                                <select id="frequency" class="form-control seo-all-container" name="frequency">
                                    @php
                                        $frequency = $datas['data']->frequency ?? 'daily';
                                    @endphp
                                    <option value="daily" {{ $frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-primary btn-block submit-btn" id="update_click">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@include('layouts.masterscript')
<script>
    function autoResize(textarea) {
        textarea.style.height = 'auto'; // Reset the height to auto
        textarea.style.height = textarea.scrollHeight + 'px'; // Set the height to match the content
    }

    window.addEventListener('DOMContentLoaded', function() {
        var textareas = document.querySelectorAll('textarea');
        textareas.forEach(function(textarea) {
            autoResize(textarea);
        });
    });
    $('.bg_type_id').change(function() {
        if ($(this).val() === '0' || $(this).val() === '1') {
            $('.back_image').attr('required', '');
            var x = document.getElementById("back_image_field");
            x.style.display = "block";

            $('.color_code').removeAttr('required');
            var x1 = document.getElementById("color_code_field");
            x1.style.display = "none";

        } else {
            $('.color_code').attr('required', '');
            var x = document.getElementById("color_code_field");
            x.style.display = "block";

            $('.back_image').removeAttr('required');
            var x1 = document.getElementById("back_image_field");
            x1.style.display = "none";
        }
    });


    $(document).on('click', '#update_click', function() {
        event.preventDefault();

        const parentDiv = document.querySelector('#sortable');
        const isSeoManager = {{ $roleManager::isAdminOrSeoManager(Auth::user()->user_type) ? 'true' : 'false' }};
        
        if (parentDiv.children.length == 0 && !isSeoManager) {
            window.alert('add top keywords');
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("edit_keyword_form"));
        var status = formData.get("keyword_id");


        var url = "{{ route('keyword.update', ':status') }}";
        url = url.replace(":status", status);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#edit_keyword_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    location.reload();
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });
    $(document).on('click', '#remove_bg_info', function() {
        $(this).closest(".row").remove();
    });

    $(document).on('click', '#add_text_info', function() {
        dynamic_text_field();
    });

    $(document).on('click', '#remove_text_info', function() {
        $(this).closest(".row").remove();
    });

    $(document).on('click', '#add_component_info', function() {
        dynamic_component_field();
    });

    $(document).on('click', '#remove_component_info', function() {
        $(this).closest(".row").remove();
    });

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
        $("input[name='category_id']").val(id);
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
        $("input[name='category_id']").val(id);
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html($(this).data('catname'));
        $(this).addClass("selected");
    });

    $(document).on("click", "li.category.none-option", function() {
        $("input[name='category_id']").val("0");
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html('== none ==');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    function hideFields() {
        $('#createSizeForm')[0].reset();
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

    function updateMetaCount(input) {
        const max = 60;
        const remaining = max - input.value.length;
        document.getElementById('metaCounter').textContent =
            remaining + ' remaining of ' + max + ' letters';
    }
metaCounter
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('meta_title');
        if (input) {
            updateMetaCount(input);
        }
    });

    function updateCount(input, counterId) {
        const max = parseInt(input.getAttribute('maxlength')) || 60;
        const remaining = max - input.value.length;
        document.getElementById(counterId).textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('title');
        updateCount(input, 'titleCounter');
        input.addEventListener('input', () => updateCount(input, 'titleCounter'));
    });
</script>

</body>

</html>

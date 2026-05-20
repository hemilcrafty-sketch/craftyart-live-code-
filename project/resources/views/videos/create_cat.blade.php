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

    /* Action Buttons Container */
    .action-buttons {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 12px;
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

    /* Page Container */
    .video-category-create-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
</style>

<div class="main-container seo-all-container">
    <div class="video-category-create-container">
        <div class="min-height-200px">
            <div class="seo-edit-card">
                <div class="section-header">Create Video Category</div>
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>
                    @csrf

                    <div class="section-header">Basic Information</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <h6>Category Name</h6>
                                <input class="form-control" type="text" name="category_name" id="categoryName"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <h6>Slug</h6>
                                <input class="form-control" type="text" name="slug" id="slug"
                                       placeholder="Please Enter Slug"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <div class="input-group custom mb-0">
                                    <input type="text" class="form-control canonical_link"
                                        name="canonical_link" />
                                </div>
                                <p class="text-end" style="font-size: 12px;">Only admin or fenil can modify
                                    canonical link</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Assign SEO</h6>
                                <div class="input-group custom mb-0">
                                    <select name="seo_emp_id" class="form-control custom-select2 assign-seo">
                                        <option value="">-- Select SEO --</option>
                                        @foreach ($userRole as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="section-header">SEO Fields</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Title</h6>
                                <input class="form-control" type="text" name="meta_title" maxlength="60"
                                    oninput="updateMetaCount(this)" required>
                                <small id="metaCounter" class="text-muted">0 / 60</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Primary Keyword</h6>
                                <input type="text" class="form-control" id="primary_keyword"
                                    name="primary_keyword" placeholder="Enter Primary Keyword" required>
                            </div>
                        </div>



                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>H1 Tag</h6>
                                <input class="form-control" type="text" name="h1_tag" maxlength="60"
                                    oninput="updateCount(this, 'h1Counter')" required>
                                <small id="h1Counter" class="text-muted">60 remaining of 60 letters</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Tag Line</h6>
                                <input class="form-control" type="text" name="tag_line" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Category Title</h6>
                                <input class="form-control" type="text" name="category_title" placeholder="e.g., Wedding Invitation Videos">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="meta_desc" maxlength="160" oninput="updateCount(this, 'metaDescCounter')"></textarea>
                                <small id="metaDescCounter" class="text-muted">160 remaining of 160 characters</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Short Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="short_desc" maxlength="350" oninput="updateCount(this, 'shortDescCounter')"></textarea>
                                <small id="shortDescCounter" class="text-muted">350 remaining of 350 characters</small>
                            </div>
                        </div>


                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <h6>H2 Tag</h6>
                                <input class="form-control" type="text" name="h2_tag">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Long Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="long_desc"></textarea>
                            </div>

                        </div> --}}
                    </div>

                    <br>
                    <div class="section-header">Images</div>
                    <div class="row">
                        <h6>Parent Category</h6>

                        <div class="input-subcategory-dropbox" id="parentCategoryInput"><span>== none ==</span> <i
                                style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i></div>
                        <div class="custom-dropdown parent-category-input">
                            <ul class="dropdown-menu-ul">
                                <li class="category none-option">== none ==</li>
                                @foreach ($allCategories as $category)
                                    @php
                                        $classBold =
                                            !empty($category['subcategories']) &&
                                            isset($category['subcategories'][0])
                                                ? 'has-children'
                                                : 'has-parent';
                                    @endphp
                                    <li class="category {{ $classBold }}" data-id="{{ $category['id'] }}"
                                        data-catname="{{ $category['category_name'] }}">
                                        {{ $category['category_name'] }}
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
                    <input type="hidden" name="parent_category_id" value="0">

                    <div class="form-group">
                        <h6>Banner</h6>
                        <input type="file" accept=".jpg, .jpeg, .webp, .svg"
                            class="form-control-file form-control height-auto dynamic-file"
                            data-imgstore-id="banner" data-nameset="true">
                    </div>

                    <div class="form-group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Category Thumb</h6>
                                <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                    class="form-control-file form-control height-auto dynamic-file"
                                    data-imgstore-id="category_thumb" data-nameset="true">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Mockup</h6>
                                <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                    class="form-control-file form-control height-auto dynamic-file"
                                    data-imgstore-id="mockup" data-nameset="true">
                            </div>

                        </div>
                    </div>

                    <br>
                    <div class="section-header">Category Settings</div>
                    {{-- <div class="form-group category-dropbox-wrap">
                        <h6>Application</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                id="app_id" name="app_id" required>
                                <option value="" selected="true" disabled>== Select Application ==</option>
                                @foreach ($appArray as $app)
                                    <option value="{{ $app->id }}">{{ $app->app_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}

                    <br>
                    <div class="section-header">Content & SEO</div>
                    @include('partials.content_section', [
                        'contents' => old('contents'),
                        'ctaSection' => [],
                    ])
                    <div style="margin-bottom: 10px;">
                        @include('partials.faqs_section', ['faqs' => ''])
                    </div>

                    <br>
                    <div class="section-header">Additional Settings</div>
                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="number" id="sequence_number" name="sequence_number"
                            required>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                id="status" name="status">
                                <option value="1">LIVE</option>
                                <option value="0">NOT LIVE</option>
                            </select>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <input class="btn btn-primary" type="submit" name="submit" value="Create Category">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        const categoryThumbInput = document.querySelector("[name='category_thumb']");
        const mockupInput = document.querySelector("[name='mockup']");
        if (!categoryThumbInput.value || !mockupInput.value) {
            alert("Please select category thumb and mockup files.");
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: '{{ route("v_cat.store") }}',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "block";
                }
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }

                if (data.error) {
                    window.alert(data.error);
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                    window.location.href = "{{ route('show_v_cat') }}";
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
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

    // Category Name to Slug auto-generation
    $(document).ready(function() {
        const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

        $("#categoryName").off("input").on("input", function() {
            const titleString = toTitleCase($(this).val());
            $("#slug").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
            $(this).val(titleString);
        });
    });

    function updateMetaCount(input) {
        const max = 60;
        const remaining = max - input.value.length;
        document.getElementById('metaCounter').textContent =
            remaining + ' remaining of ' + max + ' letters';
    }

    function updateCount(input, counterId) {
        let max = 60; // default
        if (counterId === 'metaCounter') max = 60;
        if (counterId === 'h1Counter') max = 60;
        if (counterId === 'metaDescCounter') max = 160;
        if (counterId === 'shortDescCounter') max = 350;

        const remaining = max - input.value.length;
        const counterElement = document.getElementById(counterId);
        if (counterElement) {
            counterElement.textContent = remaining + ' remaining of ' + max + ' characters';
        }
    }
</script>
</body>

</html>

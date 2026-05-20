@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            @include('partials.density_checker', [
                'title' => 'Video Virtual Category Page',
                'slug' => $datas['cat']->slug,
                'type' => 1,
                'primary_keyword' => $datas['cat']->primary_keyword,
            ])
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <h6>Category Name</h6>
                                <input class="form-control" type="textname" name="category_name"
                                    value="{{ $datas['cat']->category_name }}" id="categoryName" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group ">
                                <h6>Slug</h6>
                                <input class="form-control" type="text" name="slug" id="slug"
                                       placeholder="Please Enter Slug"
                                       value="{{ $datas['cat']->slug }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Canonical Link</h6>
                                <div class="input-group custom mb-0">
                                    <input type="text" class="form-control canonical_link" name="canonical_link"
                                        value="{{ $datas['cat']->canonical_link }}" />
                                </div>
                                <p class="text-end" style="font-size: 12px;">Only admin or fenil can modify canonical
                                    link</p>
                            </div>
                        </div>
                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <h6>Assign Sub Categories Tag</h6>
                                    <select class="form-control" id="assignSubCatSelect" name="seo_emp_id">
                                        <option disabled {{ empty($datas['cat']->seo_emp_id) ? 'selected' : '' }}>
                                            Select
                                        </option>
                                        @foreach ($assignSubCat as $subcat)
                                            <option value="{{ $subcat->id }}"
                                                {{ isset($datas['cat']->seo_emp_id) && $datas['cat']->seo_emp_id == $subcat->id ? 'selected' : '' }}>
                                                {{ $subcat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                    </div>
                    @include('videos.partials.sitemap_seo_fields', [
                        'priority' => $datas['cat']->priority ?? 0.90,
                        'frequency' => $datas['cat']->frequency ?? 'daily',
                    ])
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Title</h6>
                                <input class="form-control" type="text" name="meta_title" id="meta_title"
                                    maxlength="60" oninput="updateCount(this, 'metaCounter')"
                                    value="{{ $datas['cat']->meta_title }}" required>
                                <small id="metaCounter" class="text-muted">60 remaining of 60 characters</small>
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
                                <small id="h1Counter" class="text-muted">60 remaining of 60 characters</small>
                            </div>
                        </div>

                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <h6>H2 Tag</h6>
                                <input class="form-control" type="textname" name="h2_tag"
                                    value="{{ $datas['cat']->h2_tag }}">
                            </div>
                        </div> --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Meta Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="meta_desc" maxlength="160" oninput="updateCount(this, 'metaDescCounter')">{{ $datas['cat']->meta_desc }}</textarea>
                                <small id="metaDescCounter" class="text-muted">160 remaining of 160 characters</small>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Short Desc</h6>
                                <textarea style="height: 120px" class="form-control" name="short_desc" maxlength="350" oninput="updateCount(this, 'shortDescCounter')">{{ $datas['cat']->short_desc }}</textarea>
                                <small id="shortDescCounter" class="text-muted">350 remaining of 350 characters</small>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>Tag Line</h6>
                                <input class="form-control" type="textname" name="tag_line"
                                    value="{{ $datas['cat']->tag_line }}" required>
                            </div>
                        </div>

                    </div>

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
                            class="form-control-file form-control height-auto dynamic-file " data-imgstore-id="banner"
                            data-value="{{ $contentManager::getStorageLink($datas['cat']->banner) }}"
                            data-required="false" data-nameset="true"><br />
                        <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->banner }}" width="100" />
                        <input class="form-control" type="textname" id="banner_path" name="banner_path"
                          value="{{ $datas['cat']->banner }}" style="display: none"> -->
                    </div>

                    <div class="form-group">
                        <h6>Parent Category</h6>
                        <select class="form-control form-control-sm video-category-select seo-all-container" name="parent_category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($datas['groupedVideoCategories'] as $group)
                            <optgroup label="{{ $group['parent']->category_name }}">
                                @foreach ($group['children'] as $child)
                                <option value="{{ $child->id }}"
                                    @if ($datas['cat']->parent_category_id == $child->id) selected @endif>
                                    {{ $child->category_name }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>

                    @include('partials.content_section', [
                        'contents' =>  $datas['cat']->contents ?? old('contents'),
                        'ctaSection' => [],
                    ])
                    <div style="margin-bottom: 10px;">
                        @include('partials.faqs_section', ['faqs' => $datas['cat']->faqs ?? ''])
                    </div>

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

                    {{-- Add hidden field for generatedQuery with empty value for video virtual categories --}}
                    <input type="hidden" name="generatedQuery" value="">

                    <div>
                        <input class="btn btn-primary submit-btn" type="submit" name="submit">
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.masterscript')
    <script>
        // Initialize Quill editor for More Template CTA modal
        let ctaMoreTemplateQuill = null;

        $('#api_virtual_modal').on('shown.bs.modal', function () {
            if (!ctaMoreTemplateQuill) {
                const ctaMoreTemplateDesc = document.getElementById('ctaMoreTemplateDesc');
                if (ctaMoreTemplateDesc) {
                    ctaMoreTemplateQuill = new Quill('#ctaMoreTemplateDesc', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });
                }
            }
        });

        // Only attach virtualcontainer event listeners if it exists
        const virtualcontainer = document.getElementById("virtualcontainer");
        if (virtualcontainer) {
            document.addEventListener("DOMContentLoaded", function() {
                const virtualInputElem = virtualcontainer.querySelector("#virtualConditionQuery");
                const virtualCondition = JSON.parse(decodeHTMLEntities(virtualInputElem.value));
                virtualCondition.forEach((condition) => {
                    setValueInTable(
                        condition.column,
                        condition.columnName,
                        condition.operator,
                        condition.value,
                        condition.secondValue,
                        null,
                        virtualcontainer
                    );
                });
            });

            virtualcontainer.querySelector(".save-condition")?.addEventListener("click", function(event) {
                saveCondition(event, virtualcontainer);
            });

            virtualcontainer.querySelector(".add-sorting")?.addEventListener("click", function(event) {
                addSorting(event, virtualcontainer);
            });
        }

        // Form submission handler moved to bottom of page for cache-busting
        // See script tag after video_virtual.js include

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

        // Set counts on page load for prefilled values
        document.addEventListener("DOMContentLoaded", function() {
            // Small delay to ensure DOM is fully loaded
            setTimeout(function() {
                const h1TagInput = document.getElementById('h1_tag');
                if (h1TagInput) {
                    updateCount(h1TagInput, 'h1Counter');
                }

                const metaTitleInput = document.getElementById('meta_title');
                if (metaTitleInput) {
                    updateCount(metaTitleInput, 'metaCounter');
                }

                const metaDescTextarea = document.querySelector('textarea[name="meta_desc"]');
                if (metaDescTextarea) {
                    updateCount(metaDescTextarea, 'metaDescCounter');
                }

                const shortDescTextarea = document.querySelector('textarea[name="short_desc"]');
                if (shortDescTextarea) {
                    updateCount(shortDescTextarea, 'shortDescCounter');
                }
            }, 100);
        });

        // Backup initialization with jQuery
        $(document).ready(function() {
            setTimeout(function() {
                const h1TagInput = document.getElementById('h1_tag');
                if (h1TagInput) {
                    updateCount(h1TagInput, 'h1Counter');
                }

                const metaTitleInput = document.getElementById('meta_title');
                if (metaTitleInput) {
                    updateCount(metaTitleInput, 'metaCounter');
                }

                const metaDescTextarea = document.querySelector('textarea[name="meta_desc"]');
                if (metaDescTextarea) {
                    updateCount(metaDescTextarea, 'metaDescCounter');
                }

                const shortDescTextarea = document.querySelector('textarea[name="short_desc"]');
                if (shortDescTextarea) {
                    updateCount(shortDescTextarea, 'shortDescCounter');
                }
            }, 200);
        });
    </script>


    </body>

    </html>

<script>
// Define columns and sorting for video virtual categories - MUST be before video_virtual.js
const columns = @json(config('videovirtualcolumns.columns', []));
const sorting = @json(config('videovirtualcolumns.sorting', []));
console.log('Columns and sorting defined:', { columns, sorting });
</script>

<script src="{{ asset('assets/js/video_virtual.js') }}?v={{ time() }}"></script>

<script>
// Form submission handler - Version {{ time() }}
(function() {
    // Remove any existing handlers
    $('#dynamic_form').off('submit');

    // Attach new handler
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        formData.append('id', "{{ $datas['cat']->id }}");

        // Use the correct update route
        var url = "{{ url('update_video_virtual_cat', $datas['cat']->id) }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "block";
                }
            },
            success: function(data) {
                hideFields();
                if (data.error) {
                    window.alert(data.error);
                } else {
                    window.alert(data.success);
                }
                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        if (main_loading_screen) {
            main_loading_screen.style.display = "none";
        }
    }
})();

// Category Name to Slug auto-generation - MUST be after video_virtual.js
(function() {
    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    
    // Remove any existing handlers and attach new one
    $("#categoryName").off("input").on("input", function() {
        const titleString = toTitleCase($(this).val());
        const slugBase = titleString.toLowerCase().replace(/\s+/g, '-');
        $("#slug").val(slugBase); // Remove ID appending completely
        $(this).val(titleString);
    });
    
    console.log('Category Name to Slug handler attached - ID appending removed');
})();
</script>
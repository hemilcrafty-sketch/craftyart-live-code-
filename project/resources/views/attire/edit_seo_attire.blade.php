@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">

    <div class="min-height-200px">
        <div class="pd-20 card-box mb-30">
            <form method="post" id="dynamic_form" enctype="multipart/form-data">
                <span id="result"></span>
                @csrf
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
                            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) ||
                            $roleManager::isSeoExecutive(Auth::user()->user_type))
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
                </div>
                <div class="row">

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>H2 Tag</h6>
                            <div class="col-sm-20">
                                <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                       value="{{ $dataArray['item']->h2_tag }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Canonical Link</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control canonical_link" name="canonical_link"
                                       value="{{ $dataArray['item']->canonical_link }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Meta Title</h6>
                            <div class="input-group custom">
                                <input type="text" class="form-control" name="meta_title" id="meta_title"
                                       maxlength="60" oninput="updateCount(this, 'metaCounter')" required
                                       value="{{ $dataArray['item']->meta_title }}"/>
                            </div>
                            <small id="metaCounter" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Description</h6>
                            <div class="col-sm-20">
                                <textarea style="height: 250px" class="form-control" id="long_desc" name="long_desc">{{ $dataArray['item']->long_desc }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <h6>Meta Description</h6>
                            <div class="col-sm-20">
                                <textarea style="height: 250px" class="form-control" id="meta_description"
                                          name="meta_description" maxlength="160">{{ $dataArray['item']->meta_description }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <br>
                <div class="row">
                    @php
                    $isRestricted = false;
                    @endphp
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group category-dropbox-wrap">
                            <h6>Select Category</h6>
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
                                    $dataArray['item']['category_id'] == $category['id']
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
                            <input type="hidden" name="category_id" class="new_cat_id_item"
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
                </div>
                <br>
                <h6>Filter Row</h6>
                <hr>
                {{-- Start New Rows --}}

                <div class="row">

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <h6>Style</h6>
                            <div class="col-sm-20">
                                <select class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="styles[]">
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
                                    @if ($helperController::stringContain($dataArray['item']->religion_id,
                                    $religion->id))
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

                </div>
                <br>
                @include('partials.content_section', [
                'contents' => $dataArray['item']->contents ?? old('contents'),
                'ctaSection' => [],
                ])
                <div style="margin-bottom: 10px;">
                    @include('partials.faqs_section', ['faqs' => $dataArray['item']->faqs ?? ''])
                </div>
                <br>
                <h6>Others Row</h6>
                <hr>
                <div class="row">

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
                            <input type="number" class="form-control" id="priority" name="priority"
                                placeholder="0.90" step="0.01" min="0" max="1"
                                value="{{ $dataArray['item']->priority ?? 0.90 }}">
                            <small class="text-muted">Value between 0.00 and 1.00</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <h6>Frequency</h6>
                            <select id="frequency" class="form-control" name="frequency">
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

</div>
@include('layouts.masterscript')
<script>

    $('#dynamic_form').on('submit', function (event) {
        event.preventDefault();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        document.querySelectorAll('.txt_update_class').forEach(function (select) {
            select.disabled = false;
        });

        var formData = new FormData(this);
        var url = "{{ route('update_seo_attire', [$dataArray['item']->id]) }}";
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                document.querySelectorAll('.txt_update_class').forEach(function (select) {
                    select.disabled = true;
                });
                hideFields();
                if (data.error) {
                    window.alert(data.error);
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    window.alert(data.success);
                    // window.location.href = "{{ route('show_item') }}";
                }
                setTimeout(function () {
                    $('#result').html('');
                }, 3000);

            },
            error: function (error) {
                document.querySelectorAll('.txt_update_class').forEach(function (select) {
                    select.disabled = true;
                });
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

    $(document).on('click', '#parentCategoryInput', function () {
        $("#newCategoryRequiredPopup").hide();
        if ($('.parent-category-input').hasClass('show')) {
            $('.parent-category-input').removeClass('show');
        } else {
            $(".parent-category-input").addClass('show');
        }
    });

    $(document).on("click", ".category", function (event) {
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
        var id = $(this).data('id');
        $("input[name='category_id']").val(id);
        $("#parentCategoryInput span").html($(this).data('catname'));
        $('.parent-category-input').removeClass('show');
        $(this).addClass("selected");
    });

    $(document).on("click", ".subcategory", function (event) {
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

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    $(document).on("click", "li.category.none-option", function () {
        $("input[name='category_id']").val("0");
        $('.parent-category-input').removeClass('show');
        $("#parentCategoryInput span").html('== none ==');
    });

    $('#categoryFilter').on('input', function () {
        var filterValue = $(this).val().toLowerCase();
        $('.category, .subcategory').each(function () {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(filterValue) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // jQuery.noConflict();
    //
    // jQuery(document).ready(function($) {
    //     $('.col-sm-20.color_tags input[type="text"]').prop('readonly', true);
    //     $('.col-sm-20.color_tags input[type="text"]').css('min-width', '417px !important');
    //     $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
    //     $('.col-sm-20.color_tags input[type="text"]').on('keydown', function(event) {
    //         event.preventDefault();
    //         $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
    //     });
    //     $('.col-sm-20.color_tags input[type="text"]').on('keyup', function(event) {
    //         event.preventDefault();
    //         $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
    //     });
    //     $('.col-sm-20.color_tags input[type="text"]').on('keypress', function(event) {
    //         event.preventDefault();
    //         $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
    //     });
    //
    //
    //
    //     var colorData = @json($dataArray['item']->color_id);
    //     const colorIds = colorData ? colorData.split(",") : "";
    //
    //     for (const colorId of colorIds) {
    //         $('#colorTags').tagsinput('add', colorId);
    //         setTagBackgroundColor(colorId);
    //     }
    //
    //     var currentTag = null; // To keep track of the current tag being edited
    //     var currentColor = null;
    //     // Initialize Spectrum color picker
    //     $("#colorPicker").spectrum({
    //         color: "#f00",
    //         showInput: true,
    //         showPalette: false,
    //         showAlpha: true,
    //         change: function(color) {
    //             var colorHex = color.toHexString();
    //             // if( currentColor != null){
    //             //     var currentColorHex = rgbToHex(currentColor);
    //             // }
    //             $(currentTag).css('background-color', colorHex);
    //             $(currentTag).text(colorHex);
    //
    //             if (currentTag == null) {
    //                 $('#colorTags').tagsinput('remove', $(currentTag).text());
    //                 $('#colorTags').tagsinput('add', colorHex);
    //             } else {
    //                 var currentColorHex = rgbToHex(currentColor);
    //                 updateColorCodeValue(currentColorHex)
    //             }
    //             currentTag = null;
    //         }
    //     });
    //
    //     function rgbToHex(rgb) {
    //
    //         var result = rgb.match(/\d+/g);
    //         if (result) {
    //             var r = parseInt(result[0]).toString(16).padStart(2, '0');
    //             var g = parseInt(result[1]).toString(16).padStart(2, '0');
    //             var b = parseInt(result[2]).toString(16).padStart(2, '0');
    //             return '#' + r + g + b;
    //         }
    //         return null;
    //     }
    //
    //     function updateColorCodeValue(currentColorHex) {
    //         var colorsCode = [];
    //         $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
    //             console.log($(this).text());
    //             colorsCode.push($(this).text())
    //         });
    //         // Mapping Color Codes
    //         var existColorCodeVal = $("input[name='color_ids']").val();
    //         var colorsCodeString = colorsCode.join(',');
    //         $("input[name='color_ids']").val(colorsCodeString);
    //
    //     }
    //     // Function to set the background color of the last tag
    //     function setTagBackgroundColor(color) {
    //         var tagElements = $('.bootstrap-tagsinput .tag');
    //         var lastTagElement = tagElements[tagElements.length - 1];
    //         $(lastTagElement).css('background-color', color);
    //     }
    //
    //     // Initialize tags input
    //     $('#colorTags').tagsinput({
    //         confirmKeys: [13, 32, 188]
    //     });
    //
    //     // Set background color when a new tag is added
    //     $('#colorTags').on('itemAdded', function(event) {
    //         setTagBackgroundColor(event.item);
    //     });
    //
    //     // Set initial background colors for tags
    //     $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
    //         $(this).css("background-color", $(this).text());
    //     });
    //
    //     // Event handler for tag click to open color picker
    //     $(document).on('click', '.color_tags .bootstrap-tagsinput .tag', function() {
    //         currentTag = this;
    //         currentColor = $(this).css('background-color');
    //         $("#colorPicker").spectrum("set", currentColor);
    //         $("#colorPicker").spectrum("show");
    //     });
    //
    //     // Restore page index from session storage
    //     // let pageIndex = sessionStorage.getItem("pageIndex");
    //     // if (pageIndex) {
    //     //     $(".li_page_class .nav-link").removeClass("active");
    //     //     $("#a_link_" + pageIndex).addClass("active");
    //     // }
    //
    //     // Make tags sortable
    //     // $(".bootstrap-tagsinput").sortable({
    //     //     items: "> .tag",
    //     //     axis: "x",
    //     //     containment: "parent",
    //     //     tolerance: "pointer",
    //     //     cursor: "move",
    //     //     start: function(event, ui) {
    //     //         $(this).find('input').prop('disabled', true);
    //     //     },
    //     //     stop: function(event, ui) {
    //     //         $(this).find('input').prop('disabled', false);
    //     //     }
    //     // });
    //
    //
    //     $(".bootstrap-tagsinput").sortable({
    //         items: "> .tag",
    //         axis: "x",
    //         containment: "parent",
    //         tolerance: "pointer",
    //         cursor: "move",
    //         distance: 5,
    //         helper: "clone",
    //         start: function(event, ui) {
    //             $(this).find('input').prop('disabled', true);
    //             ui.helper.addClass('sorting');
    //         },
    //         stop: function(event, ui) {
    //             $(this).find('input').prop('disabled', false);
    //             $(ui.item).removeClass('sorting');
    //             $(this).sortable("refreshPositions");
    //         },
    //         sort: function(event, ui) {
    //             ui.helper.css('transform', 'scale(1.1)'); // Optional: scale up while dragging
    //         }
    //     });
    //
    //     $(".select2-selection__rendered").sortable({
    //         placeholder: "ui-state-highlight",
    //         stop: function(event, ui) {
    //             var selectionContainer = $(this);
    //             var selectedOptions = selectionContainer.find(".select2-selection__choice");
    //             var newOrder = [];
    //             selectedOptions.each(function() {
    //                 var optionValue = $(this).attr("title");
    //                 newOrder.push(optionValue);
    //             });
    //             var selectElement = selectionContainer.closest(".custom-select2").find("select");
    //             selectElement.val(newOrder).trigger("change");
    //         }
    //     }).disableSelection();
    //
    //     const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1)
    //         .toLowerCase());
    //     $("#post_name").on("input", function() {
    //         const strId = $(this).data("strid");
    //         const postNameString = toTitleCase($(this).val());
    //         $("#id_name").val(`${strId}-${postNameString.toLowerCase().replace(/\s+/g, '-')}`);
    //         $(this).val(postNameString);
    //     });
    // });

    $("#post_name").on("input", function () {
        const strId = $(this).data("strid");
        const postNameString =$(this).val();
        $("#id_name").val(`${strId}-${postNameString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(postNameString);
    });

    $('#arrowIcon').on('click', function () {
        if ($("#suggestionsNewContainer").css('display') == "none") {
            $("#suggestionsNewContainer").show();
        } else {
            $("#suggestionsNewContainer").hide();
        }
    });

    $(document).on("click", "div#suggestionsNewContainer ul li", function () {
        var $input = $('#newKeywordsCols input[type="text"]');
        var newWord = $(this).text().trim();
        var currentKeywords = $("#newKeywords").val().trim();
        var keywordsArray = currentKeywords ? currentKeywords.split(',').map(function (keyword) {
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

</script>

</body>

</html>

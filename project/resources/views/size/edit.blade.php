 
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container seo-all-container">

  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            Size Thumb
                        </div>
                    </div>
                </div>
            </div>

            <div class="pd-20 card-box mb-30">
                <form method="post" id="editSizeForm" enctype="multipart/form-data">
                    @csrf
                    <span id="result"></span>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Size Name</h6>
                                <input id="sizeName" class="form-control-file form-control" name="size_name"
                                    value="{{ $dataArray['item']->size_name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>ID Name</h6>
                                <input id="idName" class="form-control-file form-control" name="id_name"
                                    value="{{ $dataArray['item']->id_name }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Thumb</h6>
                                <input type="file" class="form-control-file form-control height-auto" id="thumb"
                                    name="thumb"><br />
                                <img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->thumb }}"
                                    width="100" />
                                <input class="form-control" type="text" id="thumb_path" name="thumb_path"
                                    value="{{ $dataArray['item']->thumb }}" style="display: none">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Recomanded Paper Size</h6>
                                <input type="text" class="form-control-file form-control" id="paperSize"
                                    name="paperSize" value="{{ $dataArray['item']->paper_size }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width Ration</h6>
                                <input class="form-control" id="widthRation" type="text" name="width_ration"
                                    required="" value="{{ $dataArray['item']->width_ration }}" step="any">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height Ration</h6>
                                <input class="form-control" id="heightRation" type="text" name="height_ration"
                                    required="" value="{{ $dataArray['item']->height_ration }}" step="any">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width 2</h6>
                                <input class="form-control" id="width" type="text" name="width" required=""
                                    value="{{ $dataArray['item']->width }}" step="any">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height 2</h6>
                                <input class="form-control" id="height" type="text" name="height" required=""
                                    value="{{ $dataArray['item']->height }}" step="any">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <h6>New Category</h6>
                            <select class="custom-select2 form-control" multiple="multiple"
                                data-style="btn-outline-primary" name="new_category_ids[]" id="newCategoryIds" required>
                                @foreach ($dataArray['allCategories'] as $newCategory)
                                    @if ($helperController::stringContain($dataArray['item']->new_category_id, $newCategory->id))
                                        <option value="{{ $newCategory->id }}" selected="">
                                            {{ $newCategory->category_name }}
                                        </option>
                                    @else
                                        <option value="{{ $newCategory->id }}">{{ $newCategory->category_name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Status</h6>
                                <select id="status" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="status">
                                    <option value="1"
                                        {{ $dataArray['item']->status == '1' ? 'selected="selected"' : '' }}>Active
                                    </option>
                                    <option value="0"
                                        {{ $dataArray['item']->status == '0' ? 'selected="selected"' : '' }}>
                                        Disable
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-6 col-sm-12">
                            <input type="hidden" name="id" value="{{ $dataArray['item']->id }}">
                            <input class="btn btn-primary" type="submit" name="submit">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@include('layouts.masterscript')
<script>
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


    $('#editSizeForm').on('submit', function(event) {
        event.preventDefault();
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        formData.append('_method', 'PUT');
        let id = $("input[name='id']").val();
        var url = "{{ route('sizes.update', ['size' => ':id']) }}".replace(':id', id);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                if (data.error) {
                    // $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                    alert(data.error);
                } else {
                    // $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                    alert(data.success);
                    window.location.href = "{{ route('sizes.index') }}";
                }
                hideFields();
                window.location.href = "{{ route('sizes.index') }}";

            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            }
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

    function hideFields() {
        // $('#editSizeForm')[0].reset();
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

    /* New Category */

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
    // jQuery.noConflict();
    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    $(document).on("keypress", "#sizeName", function() {
        const titleString = toTitleCase($(this).val());
        $("#idName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });
</script>

</body>

</html>

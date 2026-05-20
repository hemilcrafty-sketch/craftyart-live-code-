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
                <form method="post" id="createSizeForm">
                    <span id="result"></span>
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Size Name</h6>
                                <input id="sizeName" class="form-control-file form-control" name="size_name" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>ID Name</h6>
                                <input id="idName" class="form-control-file form-control" name="id_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Thumb</h6>
                                <input type="file" class="form-control-file form-control height-auto" id="thumb"
                                    name="thumb" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Recomanded Paper Size</h6>
                                <input type="text" class="form-control-file form-control" id="paperSize"
                                    name="paperSize" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width Ration</h6>
                                <input class="form-control" id="widthRation" type="number" name="width_ration"
                                    required="" step="any">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height Ration</h6>
                                <input class="form-control" id="heightRation" type="number" name="height_ration"
                                    required="" step="any">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width 2</h6>
                                <input class="form-control" id="width" type="number" name="width" required=""
                                    step="any">
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height 2</h6>
                                <input class="form-control" id="height" type="number" name="height" required=""
                                    step="any">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <h6>New Category</h6>
                            <select class="custom-select2 form-control" multiple="multiple"
                                data-style="btn-outline-primary" name="new_category_ids[]" required>
                                @foreach ($allNewCategories as $newCategory)
                                    <option value="{{ $newCategory->id }}">{{ $newCategory->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Status</h6>
                                <select id="status" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
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


    $('#createSizeForm').on('submit', function(event) {
        event.preventDefault();
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('sizes.store') }}",
            type: 'POST',
            dataType: 'json',
            data: formData,
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
        $('#createSizeForm')[0].reset();
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
        event.stopPropagation();
        var id = $(this).data('id');
        var catName = $(this).data('catname');
        var input = $("input[name='new_category_ids']");
        var selectedCategories = input.val() ? input.val().split(',') : [];
        var selectedCatNames = $("#selectedCategories").text().trim() === "== none ==" ? [] : $(
                "#selectedCategories")
            .text().split(', ');

        if ($(this).hasClass("selected")) {
            selectedCategories = selectedCategories.filter(catId => catId != id);
            selectedCatNames = selectedCatNames.filter(cat => cat != catName);
            $(this).removeClass("selected");
        } else {
            selectedCategories.push(id);
            selectedCatNames.push(catName);
            $(this).addClass("selected");
        }

        input.val(selectedCategories.join(','));
        $("#selectedCategories").html(selectedCatNames.length ? selectedCatNames.join(', ') : '== none ==');
    });

    $(document).on("click", ".subcategory", function(event) {
        event.stopPropagation();
        var id = $(this).data('id');
        var catName = $(this).data('catname');
        var input = $("input[name='new_category_ids']");
        var selectedCategories = input.val() ? input.val().split(',') : [];
        var selectedCatNames = $("#selectedCategories").text().trim() === "== none ==" ? [] : $(
                "#selectedCategories")
            .text().split(', ');

        if ($(this).hasClass("selected")) {
            selectedCategories = selectedCategories.filter(catId => catId != id);
            selectedCatNames = selectedCatNames.filter(cat => cat != catName);
            $(this).removeClass("selected");
        } else {
            selectedCategories.push(id);
            selectedCatNames.push(catName);
            $(this).addClass("selected");
        }

        input.val(selectedCategories.join(','));
        $("#selectedCategories").html(selectedCatNames.length ? selectedCatNames.join(', ') : '== none ==');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    $(document).on("click", "li.category.none-option", function() {
        $("input[name='new_category_ids']").val("");
        $('.parent-category-input').removeClass('show');
        $("#selectedCategories").html('== none ==');
        $(".category").removeClass("selected");
        $(".subcategory").removeClass("selected");
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


    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

    $(document).on("keypress", "#sizeName", function() {
        const titleString = toTitleCase($(this).val());
        $("#idName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });
</script>

</body>

</html>

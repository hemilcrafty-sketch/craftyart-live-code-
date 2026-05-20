 
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">

  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>{{ $helperController::getAppName($datas['appId']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">
                    <span id="result"></span>
                    @csrf
                    <div class="form-group" style="display: none;">
                        <input class="form-control" type="textname" name="app_id" value="{{ $datas['appId'] }}"
                            required>
                        <input id="count" class="form-control" type="textname" name="count" value="0"
                            required>
                    </div>

                    <div class="row">

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Post Name</h6>
                                <input id="post_name" class="form-control-file form-control" name="post_name" required>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Post Thumb</h6>
                                <input type="file" id="post_thumb" class="form-control-file form-control"
                                    name="post_thumb" required>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Ratio</h6>
                                <input class="form-control" id="ratio" type="textname" name="ratio" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="width" type="textname" name="width" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="height" type="textname" name="height" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Select BG Type</h6>
                                <div class="col-sm-20">
                                    <select class="selectpicker form-control bg_type_id"
                                        data-style="btn-outline-primary" name="bg_type_id[]" required>
                                        <option value="" disabled="true" selected="true">== Select BG Type ==
                                        </option>
                                        @foreach ($datas['bg_mode'] as $bg)
                                            <option value="{{ $bg->value }}">{{ $bg->type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-3 col-sm-12">
                            <div class="form-group" id="back_image_field" style="display: none;">
                                <h6>Back Image</h6>
                                <input type="file" id="back_image" class="form-control-file form-control"
                                    name="back_image[]">
                            </div>

                            <div class="form-group" id="color_code_field" style="display: none;">
                                <h6>Color Code</h6>
                                <input class="form-control" type="textname" id="color_code" name="color_code[]">
                            </div>
                        </div>


                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Gradient Angle</h6>
                                <input class="form-control" id="grad_angle" type="textname" name="grad_angle[]"
                                    value="0" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Gradient Ratio</h6>
                                <input class="form-control" id="grad_ratio" type="textname" name="grad_ratio[]"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Category Name</h6>
                                <select id="bg_cat" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="bg_cat" required>

                                    <option value="0">No Category</option>
                                    @foreach ($datas['bgCat'] as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->bg_category_name }}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <br />
                        <h6>Component Info</h6>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Sticker Image</h6>
                                <input type="file" class="form-control" type="textname" id="st_image"
                                    name="st_image[]" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="st_width" type="textname" name="st_width[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="st_height" type="textname" name="st_height[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>X Pos</h6>
                                <input class="form-control" id="st_x_pos" type="textname" name="st_x_pos[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Y Pos</h6>
                                <input class="form-control" id="st_y_pos" type="textname" name="st_y_pos[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Rotation</h6>
                                <input class="form-control" id="st_rotation" type="textname" name="st_rotation[]"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Opacity</h6>
                                <input class="form-control" id="st_opacity" type="textname" name="st_opacity[]"
                                    value="100" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Type</h6>
                                <select id="st_type" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="st_type[]" required>
                                    @foreach ($datas['sticker_mode'] as $sticker)
                                        <option value="{{ $sticker->value }}">{{ $sticker->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Color</h6>
                                <input class="form-control" id="st_color" type="textname" name="st_color[]">
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Resize</h6>
                                <select id="st_resize" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="st_resize[]" required>
                                    @foreach ($datas['resize_mode'] as $resize)
                                        <option value="{{ $resize->value }}">{{ $resize->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Lock Type</h6>
                                <select id="st_lock_type" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="st_lock_type[]" required>
                                    @foreach ($datas['lock_type'] as $type)
                                        <option value="{{ $type->value }}">{{ $type->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Order</h6>
                                <input class="form-control" id="st_order" type="textname" name="st_order[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Category Name</h6>
                                <select id="st_cat" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="st_cat[]" required>

                                    <option value="0">No Category</option>
                                    @foreach ($datas['stkCat'] as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->stk_category_name }}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6 style="opacity: 0;">.</h6>
                                <button type="button" name="add_component_info" id="add_component_info"
                                    class="btn btn-primary form-control-file">Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="dynamic_component_field"></div>

                    <div class="form-group">
                        <br />
                        <h6>Text Info</h6>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Text</h6>
                                <textarea style="height: 80px" class="form-control" id="text" name="text[]"></textarea>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Effects</h6>
                                <textarea style="height: 80px" class="form-control" id="txt_effect" name="txt_effect[]"></textarea>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Font Family</h6>
                                <input class="form-control" list="font_list" id="font_family" type="text"
                                    name="font_family[]" autocomplete="on"
                                    style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Editable</h6>
                                <div class="col-sm-20">
                                    <select id="is_editable" class="selectpicker form-control"
                                        data-style="btn-outline-primary" name="is_editable[]">
                                        <option value="0">FALSE</option>
                                        <option value="1">TRUE</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Editable Title</h6>
                                <select id="editable_title" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="editable_title[]">
                                    <option value="null" selected="">=Select Editable Title=</option>
                                    @foreach ($datas['editable_mode'] as $editable_mode)
                                        <option value="{{ $editable_mode->name }}">{{ $editable_mode->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Alignment</h6>
                                <select id="txt_align" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="txt_align[]" required>
                                    @foreach ($datas['txt_align'] as $txt)
                                        <option value="{{ $txt->value }}">{{ $txt->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Size</h6>
                                <input class="form-control" id="txt_size" type="textname" name="txt_size[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Color</h6>
                                <input class="form-control" id="txt_color" type="textname" name="txt_color[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Color Alpha</h6>
                                <input class="form-control" id="txt_color_alpha" type="textname"
                                    name="txt_color_alpha[]" value="100" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="txt_width" type="textname" name="txt_width[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="txt_height" type="textname" name="txt_height[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>X Pos</h6>
                                <input class="form-control" id="txt_x_pos" type="textname" name="txt_x_pos[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Y Pos</h6>
                                <input class="form-control" id="txt_y_pos" type="textname" name="txt_y_pos[]"
                                    required>
                            </div>
                        </div>


                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Line Space</h6>
                                <input class="form-control" id="line_spacing" type="textname" name="line_spacing[]"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Line Space Multiplier</h6>
                                <input class="form-control" id="lineSpaceMultiplier" type="textname"
                                    name="lineSpaceMultiplier[]" value="1" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Word Space</h6>
                                <input class="form-control" id="word_spacing" type="textname " name="word_spacing[]"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Curve</h6>
                                <input class="form-control" id="txt_curve" type="textname" name="txt_curve[]"
                                    value="0" required>
                            </div>
                        </div>


                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Rotation</h6>
                                <input class="form-control" id="txt_rotation" type="textname" name="txt_rotation[]"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Opacity</h6>
                                <input class="form-control" id="txt_opacity" type="textname" name="txt_opacity[]"
                                    value="100" required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6>Order</h6>
                                <input class="form-control" id="txt_order" type="textname" name="txt_order[]"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-12">
                            <div class="form-group">
                                <h6 style="opacity: 0;">.</h6>
                                <button type="button" name="add_text_info" id="add_text_info"
                                    class="btn btn-primary form-control-file">Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="dynamic_text_field"></div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Search keywords</h6>
                                <div class="col-sm-20">
                                    <select id="keywords" class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="keywords[]" required>
                                        <option value="" disabled="true">== Select Tags ==
                                        </option>
                                        @foreach ($datas['searchTagArray'] as $searchTag)
                                            <option value="{{ $searchTag->name }}">{{ $searchTag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Style</h6>
                                <div class="col-sm-20">
                                    <select id="styles" class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="styles[]" required>
                                        <option value="" disabled="true">== Select Tags ==
                                        </option>
                                        @foreach ($datas['styleArray'] as $style)
                                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Category</h6>
                                <div class="col-sm-20">
                                    <select id="category_id" class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="category_id[]" required>
                                        <option value="" disabled="true">== Select Category ==
                                        </option>
                                        @foreach ($datas['cat'] as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Sub Category</h6>
                                <div class="col-sm-20">
                                    <select id="sub_category_id" class="custom-select2 form-control"
                                        multiple="multiple" data-style="btn-outline-primary" name="sub_category_id[]"
                                        required>
                                        <option value="" disabled="true">== Select Sub Category ==
                                        </option>
                                        @foreach ($datas['subCatArray'] as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Theme</h6>
                                <div class="col-sm-20">
                                    <select id="theme_id" class="custom-select2 form-control" multiple="multiple"
                                        data-style="btn-outline-primary" name="theme_id[]" required>
                                        <option value="" disabled="true">== Select Theme ==
                                        </option>
                                        @foreach ($datas['themeArray'] as $theme)
                                            <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Date Range</h6>
                                <input class="form-control datetimepicker-range" placeholder="Select Date"
                                    type="text" name="date_range" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Premium Item</h6>
                                <div class="col-sm-20">
                                    <select id="is_premium" class="selectpicker form-control"
                                        data-style="btn-outline-primary" name="is_premium">
                                        <option value="0">FALSE</option>
                                        <option value="1">TRUE</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Status</h6>
                                <div class="col-sm-20">
                                    <select id="status" class="selectpicker form-control"
                                        data-style="btn-outline-primary" name="status">
                                        <option value="1">LIVE</option>
                                        <option value="0">NOT LIVE</option>
                                    </select>
                                </div>
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
    <datalist id="font_list">
        @foreach ($datas['fonts'] as $font)
            <option value="{{ $font->name }}.{{ $font->extension }}"></option>
        @endforeach
    </datalist>
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


    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        $.ajax({
            url: 'submit_item',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                hideFields();
                if (data.error) {
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                    '</div>');
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

    //@formatter:off
    function dynamic_text_field() {
        html =
            '<div class="row"> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Text</h6> <textarea style="height: 80px" class="form-control" id="text" name="text[]"></textarea> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Effects</h6> <textarea style="height: 80px" class="form-control" id="txt_effect" name="txt_effect[]"></textarea> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Font Family</h6> <input class="form-control" list="font_list" id="font_family" type="text" name="font_family[]" autocomplete="on" style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Editable</h6> <div class="col-sm-20"> <select id="is_editable" class="selectpicker form-control" data-style="btn-outline-primary" name="is_editable[]"> <option value="0">FALSE</option> <option value="1">TRUE</option> </select> </div> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Editable Title</h6> <select id="editable_title" class="selectpicker form-control" data-style="btn-outline-primary" name="editable_title[]"> <option value="null" selected="">=Select Editable Title=</option> @foreach ($datas['editable_mode'] as $editable_mode) <option value="{{ $editable_mode->name }}">{{ $editable_mode->name }}</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Alignment</h6> <select id="txt_align" class="selectpicker form-control" data-style="btn-outline-primary" name="txt_align[]" required> @foreach ($datas['txt_align'] as $txt) <option value="{{ $txt->value }}">{{ $txt->type }}</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Size</h6> <input class="form-control" id="txt_size" type="textname" name="txt_size[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Color</h6> <input class="form-control" id="txt_color" type="textname" name="txt_color[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Color Alpha</h6> <input class="form-control" id="txt_color_alpha" type="textname" name="txt_color_alpha[]" value="100" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Width</h6> <input class="form-control" id="txt_width" type="textname" name="txt_width[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Height</h6> <input class="form-control" id="txt_height" type="textname" name="txt_height[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>X Pos</h6> <input class="form-control" id="txt_x_pos" type="textname" name="txt_x_pos[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Y Pos</h6> <input class="form-control" id="txt_y_pos" type="textname" name="txt_y_pos[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Line Space</h6> <input class="form-control" id="line_spacing" type="textname" name="line_spacing[]" value="0" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Line Space Multiplier</h6> <input class="form-control" id="lineSpaceMultiplier" type="textname" name="lineSpaceMultiplier[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Word Space</h6> <input class="form-control" id="word_spacing" type="textname " name="word_spacing[]" value="0" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Curve</h6> <input class="form-control" id="txt_curve" type="textname" name="txt_curve[]" value="0" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Rotation</h6> <input class="form-control" id="txt_rotation" type="textname" name="txt_rotation[]" value="0" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Opacity</h6> <input class="form-control" id="txt_opacity" type="textname" name="txt_opacity[]" value="100" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Order</h6> <input class="form-control" id="txt_order" type="textname" name="txt_order[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6 style="opacity: 0;">.</h6> <button type="button" name="remove_text_info" id="remove_text_info" class="btn btn-danger form-control-file">Remove </button> </div> </div> </div>';
        $('#dynamic_text_field').append(html);
    }

    function dynamic_component_field() {
        html =
            '<div class="row">                        <div class="col-md-2 col-sm-12">                            <div class="form-group">                                <h6>Sticker Image</h6>                                <input type="file" class="form-control" type="textname" id="st_image" name="st_image[]"                                       required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Width</h6>                                <input class="form-control" id="st_width" type="textname" name="st_width[]" required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Height</h6>                                <input class="form-control" id="st_height" type="textname" name="st_height[]" required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>X Pos</h6>                                <input class="form-control" id="height" type="st_x_pos" name="st_x_pos[]" required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Y Pos</h6>                                <input class="form-control" id="height" type="st_y_pos" name="st_y_pos[]" required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Rotation</h6>                                <input class="form-control" id="st_rotation" type="textname" name="st_rotation[]"   value="0"                                    required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Opacity</h6>                                <input class="form-control" id="st_opacity" type="textname" name="st_opacity[]" value="100"                                      required>                            </div>                        </div>                        <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Type</h6>                                <select id="st_type" class="selectpicker form-control"                                        data-style="btn-outline-primary"                                        name="st_type[]" required>                                    @foreach ($datas['sticker_mode'] as $sticker)                                    <option value="{{ $sticker->value }}">{{ $sticker->type }}</option>                                    @endforeach                                </select>                            </div>                        </div>    <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Color</h6>                                <input class="form-control" id="st_color" type="textname" name="st_color[]"                                       >                            </div>                        </div>                    <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Resize</h6>                                <select id="st_resize" class="selectpicker form-control"                                        data-style="btn-outline-primary"                                        name="st_resize[]" required>                                    @foreach ($datas['resize_mode'] as $resize)                                    <option value="{{ $resize->value }}">{{ $resize->type }}</option>                                    @endforeach                                </select>                            </div>                        </div>        <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Lock Type</h6> <select id="st_lock_type" class="selectpicker form-control" data-style="btn-outline-primary" name="st_lock_type[]" required> @foreach ($datas['lock_type'] as $type) <option value="{{ $type->value }}">{{ $type->type }}</option> @endforeach </select> </div> </div>                <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6>Order</h6>                                <input class="form-control" id="st_order" type="textname" name="st_order[]"                                       required>                            </div>                        </div>        <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Category Name</h6> <select id="st_cat" class="selectpicker form-control" data-style="btn-outline-primary" name="st_cat[]" required> <option value="0">No Category</option> @foreach ($datas['stkCat'] as $cat) <option value="{{ $cat->id }}">{{ $cat->stk_category_name }}</option> @endforeach </select> </div> </div>                <div class="col-md-1 col-sm-12">                            <div class="form-group">                                <h6 style="opacity: 0;">.</h6>                                <button type="button" name="remove_component_info" id="remove_component_info"                                        class="btn btn-danger form-control-file">Remove                                </button>                            </div>                        </div>                    </div>';
        $('#dynamic_component_field').append(html);
    }
    //@formatter:on

    function hideFields() {

        $("#post_name").val('');
        $("#post_thumb").val('');
        $(".bg_type_id").val('');
        $("#back_image").val('');
        $("#color_code").val('');
        $("#grad_angle").val('');
        $("#grad_ratio").val('');
        $("#ratio").val('');
        $("#width").val('');
        $("#height").val('');

        $("#keywords").val('');
        $("#category_id").val('');
        $("#sub_category_id").val('');
        $("#styles").val('');
        $("#theme_id").val('');
        $("#is_premium").val('0');
        $("#status").val('1');

        $("#st_image").val('');
        $("#st_width").val('');
        $("#st_height").val('');
        $("#st_x_pos").val('');
        $("#st_y_pos").val('');
        $("#st_rotation").val('');
        $("#st_opacity").val('');
        $("#st_type").val('0');
        $("#st_color").val('0');
        $("#st_resize").val('0');
        $("#st_lock_type").val('0');
        $("#st_order").val('');
        $('#dynamic_component_field').empty();

        $("#text").val('')
        $("#font_family").val('')
        $("#is_editable").val('')
        $("#editable_title").val('')
        $("#txt_size").val('')
        $("#txt_color").val('')
        $("#txt_color_alpha").val('100')
        $("#txt_width").val('')
        $("#txt_height").val('')
        $("#txt_x_pos").val('')
        $("#txt_y_pos").val('')
        $("#line_spacing").val('0')
        $("#lineSpaceMultiplier").val('1')
        $("#word_spacing").val('0')
        $("#txt_curve").val('0')
        $("#txt_rotation").val('0')
        $("#txt_opacity").val('100')
        $("#txt_effect").val('')
        $("#txt_order").val('')

        $('#dynamic_text_field').empty();

        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

    }
</script>

</body>

</html>

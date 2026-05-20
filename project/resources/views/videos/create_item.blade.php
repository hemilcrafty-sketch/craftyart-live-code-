@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container  designer-access-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">
                    <span id="result"></span>
                    @csrf
                    <div class="form-group" style="display: none;">
                        <h6>Body Name</h6>
                        <input id="count" class="form-control" type="textname" name="count" value="0" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Video Name</h6>
                                <input class="form-control" type="textname" id="video_name" name="video_name" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Template id</h6>
                                <input class="form-control" type="textname" id="relation_id" name="relation_id"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Video Thumb</h6>
                        <input type="file" id="video_thumb" class="form-control-file form-control height-auto"
                            name="video_thumb" accept=".webp" required>
                        <small class="text-muted">Only WebP format, max 100 KB</small>
                    </div>

                    <div class="form-group">
                        <h6>Video File</h6>
                        <input type="file" id="video_file" class="form-control-file form-control height-auto"
                            name="video_file" accept=".mp4" required>
                        <small class="text-muted">Only MP4 format, max 10 MB</small>
                    </div>

                    <div class="form-group">
                        <h6>Zip File</h6>
                        <input type="file" id="zip_file" class="form-control-file form-control height-auto"
                            name="zip_file" required>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Pages</h6>
                                <input class="form-control" id="pages" type="number" name="pages" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="width" type="number" name="width" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="height" type="number" name="height" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Watermark Height</h6>
                                <input class="form-control" id="watermark_height" type="number" name="watermark_height"
                                    value="0" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Video Type</h6>
                                <select id="template_type" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="template_type" required>
                                    <option value="" disabled="true" selected="true">== Select Template Type ==
                                    </option>
                                    @foreach ($datas['templateType'] as $templateType)
                                        <option value="{{ $templateType->value }}">{{ $templateType->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Show Text Front</h6>
                                <select id="do_front_lottie" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="do_front_lottie" required>
                                    <option value="1">True</option>
                                    <option value="0" selected>False</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
                        <div class="form-group">
                            <br />
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <h6>Images</h6>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" name="add_img_id" id="add_img_id"
                                        class="btn btn-primary form-control-file">Add</button>
                                </div>
                            </div>

                        </div>

                        <div id="dynamic_img_field">
                        </div>

                    </div>

                    <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
                        <div class="form-group">
                            <br />
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <h6>Text</h6>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" name="add_text_id" id="add_text_id"
                                        class="btn btn-primary form-control-file">Add</button>
                                </div>
                            </div>

                        </div>

                        <div id="dynamic_text_field">
                        </div>

                    </div>

                    <div class="form-group">
                        <h6>Encrypted</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" id="encrypted"
                                name="encrypted">
                                <option value="0">FALSE</option>
                                <option value="1">TRUE</option>
                            </select>
                        </div>
                    </div>

                    <div id="encryption_field" style="display: none;">
                        <div class="form-group">
                            <h6>Encryption Key</h6>
                            <input class="form-control" type="textname" id="encryption_key" name="encryption_key">
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Change Music</h6>
                        <div class="col-sm-20">
                            <select id="change_music" class="selectpicker form-control" data-style="btn-outline-primary"
                                name="change_music">
                                <option value="0">FALSE</option>
                                <option value="1">TRUE</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Select Main Category</h6>
                        <select class="form-control form-control-sm" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($datas['groupedVideoCategories'] as $group)
                                <optgroup label="{{ $group['parent']->category_name }}">
                                    @foreach ($group['children'] as $child)
                                        <option value="{{ $child->id }}" style="color: black !important;">
                                            {{ $child->category_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Keyword or Tags</h6>
                        <div class="col-sm-20 keyword-input">
                            <input type="text" data-role="tagsinput" class="form-control" id="keywords" name="keywords"
                                placeholder="Add tags" autocomplete="on" required="" />
                            <div id="suggestionsContainer"></div>
                        </div>
                    </div>

                    {{ csrf_field() }}

                    <div class="form-group">
                        <h6>Premium Item</h6>
                        <div class="col-sm-20">
                            <select id="is_premium" class="selectpicker form-control" data-style="btn-outline-primary"
                                name="is_premium">
                                <option value="0">FALSE</option>
                                <option value="1">TRUE</option>
                            </select>
                        </div>
                    </div>

                    @include('videos.partials.sitemap_seo_fields')

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select id="status" class="selectpicker form-control" data-style="btn-outline-primary"
                                name="status">
                                <option value="1">LIVE</option>
                                <option value="0">NOT LIVE</option>
                            </select>
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

<!-- Success Modal -->
<div class="modal fade" id="templateCreatedModal" tabindex="-1" role="dialog"
    aria-labelledby="templateCreatedModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="templateCreatedModalLabel">
                    <i class="fa fa-check-circle mr-2"></i>Template Created Successfully!
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="modalTemplateThumbnail" src="" alt="Template Thumbnail"
                        style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #ddd;">
                </div>
                <div class="template-details">
                    <p><strong>Template Name:</strong> <span id="modalTemplateName"></span></p>
                    <p><strong>Template ID:</strong> <span id="modalTemplateRelationId"></span></p>
                    <p><strong>String ID:</strong> <span id="modalTemplateStringId"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="createAnother()">
                    <i class="fa fa-plus mr-1"></i>Create Another
                </button>
                <a href="{{ route('show_v_item') }}" class="btn btn-primary">
                    <i class="fa fa-list mr-1"></i>View All Templates
                </a>
            </div>
        </div>
    </div>
</div>

<datalist id="related_tag_list">
    @foreach ($datas['searchTagArray'] as $searchTag)
        <option value="{{ $searchTag->name }}"></option>
    @endforeach
</datalist>
@include('layouts.masterscript')
<script>
    function showConfirmation() {
        return confirm("Are you sure you want to reload this page?");
    }
    window.addEventListener("popstate", function (event) {
        if (showConfirmation()) {
            window.location.reload(true);
        } else {
            history.pushState(null, null, window.location.href);
            event.preventDefault();
        }
    });
    window.addEventListener("beforeunload", function (event) {
        if (showConfirmation()) {
            window.location.reload(true);
        } else {
            event.preventDefault();
        }
    });
</script>
<script>
    window.addEventListener("beforeunload", function (event) {
        if (showConfirmation()) {
            window.location.reload(true);
        } else {
            event.preventDefault();
        }
    });
    window.addEventListener("load", function () {
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

    });

    var textCount = 1;

    $('#encrypted').change(function () {
        if ($(this).val() == '1') {
            $('#encryption_key').attr('required', '');
            var x = document.getElementById("encryption_field");
            x.style.display = "block";

        } else {
            $('#encryption_key').removeAttr('required');
            var x = document.getElementById("encryption_field");
            x.style.display = "none";
        }
    });

    // Video Thumb validation (WebP, max 100 KB)
    $('#video_thumb').change(function() {
        const file = this.files[0];
        if (file) {
            const fileType = file.type;
            const fileName = file.name.toLowerCase();

            if (fileType !== 'image/webp' && !fileName.endsWith('.webp')) {
                alert('Only WebP format is allowed for Video Thumb!');
                $(this).val('');
                return;
            }

            const maxSize = 100 * 1024;
            if (file.size > maxSize) {
                alert('Video Thumb size must be less than 100 KB! Current size: ' + (file.size / 1024).toFixed(2) + ' KB');
                $(this).val('');
                return;
            }
        }
    });

    // Video File validation (MP4, max 10 MB)
    $('#video_file').change(function() {
        const file = this.files[0];
        if (file) {
            const fileType = file.type;
            const fileName = file.name.toLowerCase();

            if (fileType !== 'video/mp4' && !fileName.endsWith('.mp4')) {
                alert('Only MP4 format is allowed for Video File!');
                $(this).val('');
                return;
            }

            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Video File size must be less than 10 MB! Current size: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB');
                $(this).val('');
                return;
            }
        }
    });

    $('#dynamic_form').on('submit', function (event) {
        event.preventDefault();

        const videoThumb = $('#video_thumb')[0].files[0];
        const videoFile = $('#video_file')[0].files[0];

        if (videoThumb) {
            const thumbType = videoThumb.type;
            const thumbName = videoThumb.name.toLowerCase();
            if (thumbType !== 'image/webp' && !thumbName.endsWith('.webp')) {
                alert('Only WebP format is allowed for Video Thumb!');
                return;
            }
            if (videoThumb.size > 100 * 1024) {
                alert('Video Thumb size must be less than 100 KB!');
                return;
            }
        }

        if (videoFile) {
            const vfType = videoFile.type;
            const vfName = videoFile.name.toLowerCase();
            if (vfType !== 'video/mp4' && !vfName.endsWith('.mp4')) {
                alert('Only MP4 format is allowed for Video File!');
                return;
            }
            if (videoFile.size > 10 * 1024 * 1024) {
                alert('Video File size must be less than 10 MB!');
                return;
            }
        }

        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        $.ajax({
            url: 'submit_v_item',
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";

                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    // Show success modal with template details
                    if (data.template) {
                        const storageUrl = "{{ config('filesystems.storage_url') }}";
                        $('#modalTemplateName').text(data.template.video_name);
                        $('#modalTemplateRelationId').text(data.template.relation_id);
                        $('#modalTemplateStringId').text(data.template.string_id);
                        $('#modalTemplateThumbnail').attr('src', storageUrl + data.template.video_thumb);
                        $('#templateCreatedModal').modal('show');
                    } else {
                        // Fallback if template data not available
                        hideFields();
                        $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                        setTimeout(function () {
                            window.location.href = "{{ route('show_v_item') }}";
                        }, 2000);
                    }
                }

                setTimeout(function () {
                    $('#result').html('');
                }, 3000);

            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    $(document).on('click', '#add_text_id', function () {
        dynamic_text_field();
    });

    $(document).on('click', '#remove_text_id', function () {
        $(this).closest(".row").remove();
    });

    function dynamic_text_field() {
        var lastInputValue = $("input[name='key[]']:last").val();
        if (typeof lastInputValue !== 'undefined') {
            var parts = lastInputValue.split('_');
            var numericValue = parseInt(parts[parts.length - 1]);
            textCount = numericValue + 1;
        } else {
            textCount++;
        }
        html =
            '<div class="row"><hr size="8" width="100%" color="black"><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Key</h6><input type="text" class="form-control" placeholder="key" id="key" name="key[]" value="editable_text_' +
            textCount +
            '"  required></div></div><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Title</h6><input type="text" class="form-control" placeholder="Title" id="title" name="title[]" required></div></div><div class="col-md-2 col-sm-12"><div class="form-group"><h6>Font Family</h6><input type="text" class="form-control" placeholder="Font Family" id="font_family" name="font_family[]" required></div></div><div class="col-md-4 col-sm-12"><div class="form-group"> <h6>Value</h6><textarea style="height: 80px" class="form-control" id="editable_text_id" name="editable_text_id[]" required></textarea></div></div><div class="col-md-2 pd-19"><div class="form-group"><button type="button" name="remove_text_id" id="remove_text_id" class="btn btn-danger form-control-file">Remove</button></div></div></div>';
        $('#dynamic_text_field').append(html);
    }

    $(document).on('click', '#add_img_id', function () {
        dynamic_img_field();
    });

    $(document).on('click', '#remove_img_id', function () {
        $(this).closest(".row").remove();
    });

    function dynamic_img_field() {
        html =
            '<div class="row"><hr size="8" width="100%" color="black"><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Id</h6><input type="text" class="form-control" placeholder="key" id="key" name="img_key[]" required></div></div><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Is Shape</h6><select class="selectpicker form-control" data-style="btn-outline-primary" name="img_shape[]" required><option value="0">False</option><option value="1">True</option></select></div></div><div class="col-md-4 pd-19"><div class="form-group"><h6 style="opacity: 0;">.</h6><button type="button" name="remove_img_id" id="remove_img_id" class="btn btn-danger form-control-file">Remove</button></div></div></div>';
        $('#dynamic_img_field').append(html);
    }

    function hideFields() {
        $("#category_id").val('');
        $("#relation_id").val('');
        $("#pages").val('');
        $("#video_name").val('');
        $("#video_thumb").val('');
        $("#video_file").val('');
        $("#zip_file").val('');
        $("#width").val('');
        $("#height").val('');
        $("#editable_image_id").val('');
        $("#keywords").val('');
        $("#edit_text").val('0');
        $("#encrypted").val('0');
        $("#encryption_key").val('');
        $("#change_music").val('0');
        $("#is_premium").val('0');
        $("#status").val('0');
        $('#template_type').val('');
        $("#keywords").val();

        $('#dynamic_text_field').empty();

        var x = document.getElementById("editable_text_field");
        x.style.display = "none";
        var x1 = document.getElementById("dynamic_text_field");
        x1.style.display = "none";

        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

    }

    function createAnother() {
        $('#templateCreatedModal').modal('hide');
        window.location.reload();
    }
</script>
</body>

</html>
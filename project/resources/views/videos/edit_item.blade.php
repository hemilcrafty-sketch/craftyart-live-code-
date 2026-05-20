   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<style>
    /* Modern Card Design */
    .video-edit-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 25px;
    }

    /* Section Headers */
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 25px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 28px;
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

    .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    /* File Upload */
    .form-control-file {
        padding: 8px;
        border: 2px dashed #e0e0e0;
        border-radius: 6px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .form-control-file:hover {
        border-color: #0059b2;
        background: #f0f7ff;
    }

    /* Image Preview */
    .form-group img {
        margin-top: 10px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 4px;
        background: white;
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

    /* Dynamic Fields */
    .dynamic-field-row {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }

    .dynamic-field-row:hover {
        border-color: #0059b2;
        background: #f0f7ff;
    }

    /* Action Buttons */
    .action-buttons {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Remove Buttons */
    .btn-danger {
        background: #dc3545 !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 10px 20px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        color: #ffffff !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
        height: auto !important;
    }

    .btn-danger:hover {
        background: #c82333 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important;
        color: #ffffff !important;
    }

    .btn-danger:active {
        transform: translateY(0) !important;
    }

    /* Fix for form-control-file class on remove buttons */
    button.btn-danger.form-control-file {
        border: none !important;
        background: #dc3545 !important;
        color: #ffffff !important;
        padding: 10px 20px !important;
    }

    button.btn-danger.form-control-file:hover {
        background: #c82333 !important;
        color: #ffffff !important;
    }

    /* Row Spacing */
    .row {
        margin-bottom: 15px;
    }

    /* Select Boxes */
    .selectpicker {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
    }

    /* Labels */
    label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 8px;
        display: block;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .video-edit-card {
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
</style>
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="video-edit-card">
                <div class="section-title">Edit Video Template</div>
                <form id="editVideoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $dataArray['item']->id }}">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Template id</h6>
                                <input class="form-control" type="textname"
                                    value="{{ $dataArray['item']->relation_id }}" id="relation_id" name="relation_id"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>String id (Read Only)</h6>
                                <input class="form-control" type="textname" value="{{ $dataArray['item']->string_id }}"
                                    readonly>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <h6>Video Thumb</h6>
                        <input type="file" id="video_thumb" class="form-control-file form-control height-auto"
                            name="video_thumb" accept=".webp">
                        <small class="text-muted">Only WebP format, max 100 KB</small>
                        @if($dataArray['item']->video_thumb)
                            <div style="margin-top: 10px;">
                                <img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->video_thumb }}"
                                    style="max-width: 200px; max-height: 200px; border-radius: 6px; border: 1px solid #e0e0e0;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                                <div style="display: none; padding: 10px; background: #f8d7da; border-radius: 6px; color: #721c24;">
                                    <i class="fa fa-exclamation-triangle"></i> Image not found
                                </div>
                                <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">Current: {{ basename($dataArray['item']->video_thumb) }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <h6>Video File</h6>
                        <input type="file" id="video_file" class="form-control-file form-control height-auto"
                            name="video_file" accept=".mp4">
                        <small class="text-muted">Only MP4 format, max 10 MB</small>
                        @if($dataArray['item']->video_url)
                            <div style="margin-top: 10px;">
                                @php
                                    $extension = pathinfo($dataArray['item']->video_url, PATHINFO_EXTENSION);
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                @endphp
                                @if(in_array(strtolower($extension), $imageExtensions))
                                    <img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->video_url }}"
                                        style="max-width: 200px; max-height: 200px; border-radius: 6px; border: 1px solid #e0e0e0;"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                                    <div style="display: none; padding: 10px; background: #f8d7da; border-radius: 6px; color: #721c24;">
                                        <i class="fa fa-exclamation-triangle"></i> Image not found
                                    </div>
                                @else
                                    <video width="200" height="150" controls style="border-radius: 6px; border: 1px solid #e0e0e0;">
                                        <source src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->video_url }}" type="video/{{ $extension }}">
                                        Your browser does not support the video tag.
                                    </video>
                                @endif
                                <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">Current: {{ basename($dataArray['item']->video_url) }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <h6>Zip File</h6>
                        <input type="file" id="zip_file" class="form-control-file form-control height-auto"
                            name="zip_file" accept=".zip">
                        @if($dataArray['item']->video_zip_url)
                            <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e0e0e0;">
                                <i class="fa fa-file-archive-o" style="color: #0059b2; margin-right: 8px;"></i>
                                <span style="font-size: 13px; color: #4a4a4a;">{{ basename($dataArray['item']->video_zip_url) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Pages</h6>
                                <input class="form-control" id="pages" type="number"
                                    value="{{ $dataArray['item']->pages }}" name="pages" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="width" type="number"
                                    value="{{ $dataArray['item']->width }}" name="width" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="height" type="number"
                                    value="{{ $dataArray['item']->height }}" name="height" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Watermark Height</h6>
                                <input class="form-control" id="watermark_height" type="number"
                                    value="{{ $dataArray['item']->watermark_height }}" name="watermark_height"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Video Type</h6>
                                <select id="template_type" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="template_type" required>
                                    @foreach ($dataArray['templateType'] as $templateType)
                                        @if ($dataArray['item']->template_type == $templateType->value)
                                            <option value="{{ $templateType->value }}" selected="">
                                                {{ $templateType->type }}
                                            </option>
                                        @else
                                            <option value="{{ $templateType->value }}">{{ $templateType->type }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <h6>Show Text Front</h6>
                                <select id="do_front_lottie" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="do_front_lottie" required>
                                    @if ($dataArray['item']->do_front_lottie == 1 || $dataArray['item']->do_front_lottie == '1')
                                        <option value="1" selected>True</option>
                                        <option value="0">False</option>
                                    @else
                                        <option value="1">True</option>
                                        <option value="0" selected>False</option>
                                    @endif

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
                            @for ($i = 0; $i < count($dataArray['editable_image']); $i++)
                                <div class="row">
                                    <hr size="8" width="100%" color="black">
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Id</h6>
                                            <input type="text" class="form-control" placeholder="key"
                                                id="img_key" name="img_key[]"
                                                value="{{ $dataArray['editable_image'][$i]['key'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Is Shape</h6>
                                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                                name="img_shape[]" required>
                                                @if ($dataArray['editable_image'][$i]['isShape'] == 0)
                                                    <option value="0" selected="true">False</option>
                                                    <option value="1">True</option>
                                                @else
                                                    <option value="0">False</option>
                                                    <option value="1" selected="true">True</option>
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pd-19">
                                        <div class="form-group">
                                            <h6 style="opacity: 0;">.</h6>
                                            <button type="button" name="remove_img_id" id="remove_img_id"
                                                class="btn btn-danger form-control-file">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endfor

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
                            @for ($i = 0; $i < count($dataArray['editable_text']); $i++)
                                <div class="row">
                                    <hr size="8" width="100%" color="black">
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Key</h6>
                                            <input type="text" class="form-control" placeholder="key"
                                                id="key" name="key[]"
                                                value="{{ $dataArray['editable_text'][$i]['key'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Title</h6>
                                            <input type="text" class="form-control" placeholder="Title"
                                                id="title" name="title[]"
                                                value="{{ $dataArray['editable_text'][$i]['title'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <h6>Font Family</h6>
                                            <input type="text" class="form-control" placeholder="Font Family"
                                                id="font_family" name="font_family[]"
                                                value="{{ $dataArray['editable_text'][$i]['font_family'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <h6>Value</h6>
                                            <textarea style="height: 80px" class="form-control" id="editable_text_id" name="editable_text_id[]" required>{{ $dataArray['editable_text'][$i]['value'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-2 pd-19">
                                        <div class="form-group">
                                            <h6 style="opacity: 0;">.</h6>
                                            <button type="button" name="remove_text_id" id="remove_text_id"
                                                class="btn btn-danger form-control-file">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            @endfor

                        </div>

                    </div>

                    <div class="form-group">
                        <h6>Encrypted</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="encrypted" id="encrypted">
                                @if ($dataArray['item']->encrypted == '1')
                                    <option value="0">FALSE</option>
                                    <option value="1" selected="">TRUE</option>
                                @else
                                    <option value="0" selected="">FALSE</option>
                                    <option value="1">TRUE</option>
                                @endif
                            </select>
                        </div>
                    </div>


                    @if ($dataArray['item']->encrypted == '1')
                        <div id="encryption_field">
                            <div class="form-group">
                                <h6>Encryption Key</h6>
                                <input class="form-control" type="textname" id="encryption_key"
                                    name="encryption_key" value="{{ $dataArray['item']->encryption_key }}" required>
                            </div>
                        </div>
                    @else
                        <div id="encryption_field" style="display: none;">
                            <div class="form-group">
                                <h6>Encryption Key</h6>
                                <input class="form-control" type="textname" id="encryption_key"
                                    name="encryption_key">
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <h6>Change Music</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="change_music">
                                @if ($dataArray['item']->change_music == '1')
                                    <option value="0">FALSE</option>
                                    <option value="1" selected="">TRUE</option>
                                @else
                                    <option value="0" selected="">FALSE</option>
                                    <option value="1">TRUE</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="submitBtn" name="submit">
                            <i class="fa fa-save"></i> Update Video
                        </button>
                        <a href="{{ route('show_v_item') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<datalist id="related_tag_list">
    @foreach ($dataArray['searchTagArray'] as $searchTag)
        <option value="{{ $searchTag->name }}"></option>
    @endforeach
</datalist>
@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        // Video Thumb validation and preview (WebP, max 100 KB)
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

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = '<div style="margin-top: 10px;"><img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; border-radius: 6px; border: 1px solid #e0e0e0;"><p style="font-size: 12px; color: #6c757d; margin-top: 5px;">New: ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)</p></div>';
                    $('#video_thumb').parent().find('div').remove();
                    $('#video_thumb').after(preview);
                }
                reader.readAsDataURL(file);
            }
        });

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

                const preview = '<div style="margin-top: 10px; padding: 10px; background: #f0f7ff; border-radius: 6px; border: 1px solid #0059b2;"><i class="fa fa-video-camera" style="color: #0059b2; margin-right: 8px;"></i><span style="font-size: 13px; color: #4a4a4a;">New: ' + file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)</span></div>';
                $('#video_file').parent().find('div').remove();
                $('#video_file').after(preview);
            }
        });

        $('#zip_file').change(function() {
            const file = this.files[0];
            if (file) {
                const preview = '<div style="margin-top: 10px; padding: 10px; background: #f0f7ff; border-radius: 6px; border: 1px solid #0059b2;"><i class="fa fa-file-archive-o" style="color: #0059b2; margin-right: 8px;"></i><span style="font-size: 13px; color: #4a4a4a;">New: ' + file.name + '</span></div>';
                $('#zip_file').parent().find('div').remove();
                $('#zip_file').after(preview);
            }
        });

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            // Disable submit button to prevent double submission
            $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            var form = $('#editVideoForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('v_item.update', [$dataArray['item']->id]) }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.success || response.status === true) {
                        alert(response.message || 'Video template updated successfully!');
                        window.location.href = "{{ route('show_v_item') }}";
                    } else {
                        alert(response.error || response.message || 'Failed to update video template.');
                        $('#submitBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Update Video');
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Update Video');
                    
                    if (xhr.status === 419) {
                        alert("Session expired. Please refresh the page and try again.");
                    } else if (xhr.status === 422) {
                        var json = xhr.responseJSON || {};
                        if (json.error) {
                            alert(json.error);
                            return;
                        }
                        var errors = json.errors;
                        if (!errors) {
                            alert(json.message || 'Validation failed.');
                            return;
                        }
                        var errorMsg = "Validation errors:\n";
                        for (var key in errors) {
                            errorMsg += "- " + errors[key][0] + "\n";
                        }
                        alert(errorMsg);
                    } else if (xhr.status === 500) {
                        alert("Server error. Please check the console for details.");
                        console.error(xhr.responseText);
                    } else {
                        alert("An error occurred. Please try again.");
                        console.error(xhr.responseText);
                    }
                }
            });
        });
    });

    window.addEventListener("load", function() {
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

    $(document).ready(function() {
        var textCount = $("#dynamic_text_field").children().length + 1;

        $('#encrypted').change(function() {
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


        $(document).on('click', '#add_text_id', function() {
            dynamic_text_field();
        });

        $(document).on('click', '#remove_text_id', function() {
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


        $(document).on('click', '#add_img_id', function() {
            dynamic_img_field();
        });

        $(document).on('click', '#remove_img_id', function() {
            $(this).closest(".row").remove();
        });

        function dynamic_img_field() {
            html =
                '<div class="row"><hr size="8" width="100%" color="black"><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Id</h6><input type="text" class="form-control" placeholder="key" id="key" name="img_key[]" required></div></div><div class="col-md-4 col-sm-12"><div class="form-group"><h6>Is Shape</h6><select class="selectpicker form-control" data-style="btn-outline-primary" name="img_shape[]" required><option value="0">False</option><option value="1">True</option></select></div></div><div class="col-md-4 pd-19"><div class="form-group"><h6 style="opacity: 0;">.</h6><button type="button" name="remove_img_id" id="remove_img_id" class="btn btn-danger form-control-file">Remove</button></div></div></div>';
            $('#dynamic_img_field').append(html);
        }
    });
</script>
</body>

</html>
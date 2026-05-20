@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            Video Size (thumb)
                        </div>
                    </div>
                </div>
            </div>

            <div class="pd-20 card-box mb-30">
                <form method="post" id="createVideoSizeForm" enctype="multipart/form-data">
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
                                <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                       class="form-control-file form-control height-auto dynamic-file"
                                       data-imgstore-id="thumb" data-nameset="true">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h6>Recommended Paper Size</h6>
                                <input type="text" class="form-control-file form-control" id="paperSize"
                                    name="paperSize" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width Ratio</h6>
                                <input class="form-control" id="widthRation" type="number" name="width_ration"
                                    required step="any">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height Ratio</h6>
                                <input class="form-control" id="heightRation" type="number" name="height_ration"
                                    required step="any">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Width</h6>
                                <input class="form-control" id="width" type="number" name="width" required step="any">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Height</h6>
                                <input class="form-control" id="height" type="number" name="height" required step="any">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <h6>Categories</h6>
                            <select class="custom-select2 form-control" multiple="multiple"
                                data-style="btn-outline-primary" name="category_ids[]" required>
                                @foreach ($allVideoCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
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
    $('#createVideoSizeForm').on('submit', function(event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('video_sizes.store') }}",
            type: 'POST',
            dataType: 'json',
            data: formData,
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.success);
                    window.location.href = "{{ route('video_sizes.index') }}";
                }
            },
            error: function(xhr) {
                $('#main_loading_screen').hide();
                var msg = xhr.responseText || 'Request failed';
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j.errors && typeof j.errors === 'object') {
                        var firstKey = Object.keys(j.errors)[0];
                        if (firstKey && j.errors[firstKey] && j.errors[firstKey][0]) {
                            msg = j.errors[firstKey][0];
                        }
                    } else if (j.error) {
                        msg = j.error;
                    } else if (j.message) {
                        msg = j.message;
                    }
                } catch (e) {
                }
                alert(msg);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    $(document).on('keypress', '#sizeName', function() {
        const titleString = toTitleCase($(this).val());
        $('#idName').val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });
</script>
</body>

</html>

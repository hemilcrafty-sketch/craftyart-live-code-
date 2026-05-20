@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container designer-access-container">

    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <!-- Left: Add Button -->
                        <div class="col-md-3">
                            @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewStickerItemBtn">
                                    + Add Sticker Item
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('sticker_item.index'),
                            ])
                        </div>
                    </div>

                    {{-- <div class="col-sm-12 table-responsive"> --}}
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Sticker Id</th>
                                    <th>User</th>
                                    <th>Sticker Name</th>
                                    <th>Cateogry Name</th>
                                    <th class="datatable-nosort">Sticker thumb</th>
                                    <th class="datatable-nosort">Sticker Image</th>
                                    <th>Sticker Type</th>
                                    <th>Is Premium</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort" style="width:150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stickerItems as $sticker)
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $sticker->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($sticker->emp_id) }}
                                        </td>
                                        <td>{{ $sticker->sticker_name }}</td>
                                        <td>{{ \App\Http\Controllers\HelperController::getStickerCatName($sticker->stk_cat_id) }}
                                        </td>

                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($sticker->sticker_thumb) }}"
                                                style="max-width: 100px; max-height: 100px;" />
                                        </td>
                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($sticker->sticker_image) }}"
                                                style="max-width: 100px; max-height: 100px;" />
                                        </td>

                                        <td>{{ \App\Http\Controllers\HelperController::getStickerMode($sticker->sticker_type) }}
                                        </td>

                                        <td>
                                            <label id="premium_label_{{ $sticker->id }}" style="display: none;">
                                                {{ $sticker->is_premium == '1' ? 'TRUE' : 'FALSE' }}
                                            </label>
                                            <button style="border: none" onclick="premium_click('{{ $sticker->id }}')">
                                                <input type="checkbox" class="switch-btn" data-size="small"
                                                    data-color="#0059b2"
                                                    {{ $sticker->is_premium == '1' ? 'checked' : '' }} />
                                            </button>
                                        </td>

                                        <td>
                                            <label id="status_label_{{ $sticker->id }}" style="display: none;">
                                                {{ $sticker->status == '1' ? 'Live' : 'Not Live' }}
                                            </label>
                                            <button style="border: none" onclick="status_click('{{ $sticker->id }}')">
                                                <input type="checkbox" class="switch-btn" data-size="small"
                                                    data-color="#0059b2"
                                                    {{ $sticker->status == '1' ? 'checked' : '' }} />
                                            </button>
                                        </td>

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-sticker-item-btn"
                                                    data-id="{{ $sticker->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <button type="button" class="dropdown-item delete-sticker-item"
                                                        data-id="{{ $sticker->id }}">
                                                        <i class="dw dw-delete-3"></i> Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $stickerItems])
            </div>
        </div>
    </div>
</div>
</div>
</div>

<div class="modal fade designer-access-container" id="add_sticker_item_model" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Sticker Item</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form method="POST" id="sticker_cate_form" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="sticker_category_id">

                    <div class="form-group">
                        <h6>Sticker Item Name</h6>
                        <input class="form-control" type="text" id="stk_category_name" name="sticker_name" required>
                    </div>

                    <div class="form-group">
                        <h6>Select Category</h6>
                        <select id="category_id" class="form-control" name="stk_cat_id" required>
                            <option value="" disabled selected>== Select Category ==</option>
                            @foreach ($dataArray['stkCatArray'] as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->stk_category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Sticker Item Thumb</h6>
                        <input type="file" class="form-control dynamic-file" data-nameset="true"
                            data-imgstore-id="sticker_thumb" id="sticker_thumbs" accept=".jpg, .jpeg, .webp, .svg">
                    </div>

                    <div class="form-group">
                        <h6>Sticker Item File</h6>
                        <input type="file" class="form-control dynamic-file" data-nameset="true"
                            data-imgstore-id="sticker_image" id="sticker_images" accept=".svg">
                    </div>

                    <div class="form-group">
                        <h6>Sticker Type</h6>
                        <select id="sticker_type" class="form-control" name="sticker_type">
                            @foreach ($dataArray['sticker_mode'] as $sticker)
                                <option value="{{ $sticker->value }}"
                                    {{ isset($dataArray['stickeritem']) && $dataArray['stickeritem']->sticker_type == $sticker->value ? 'selected' : '' }}>
                                    {{ $sticker->type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Premium Item</h6>
                        <select id="is_premium" class="form-control" name="is_premium">
                            <option value="0"
                                {{ isset($dataArray['stickeritem']) && $dataArray['stickeritem']->is_premium == 0 ? 'selected' : '' }}>
                                FALSE</option>
                            <option value="1"
                                {{ isset($dataArray['stickeritem']) && $dataArray['stickeritem']->is_premium == 1 ? 'selected' : '' }}>
                                TRUE</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <select class="form-control" id="status" name="status">
                            <option value="1">LIVE</option>
                            <option value="0">NOT LIVE</option>
                        </select>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary" id="sticker_form_submit">Submit</button>
                    </div>
                </form>
            </div>
            <meta name="csrf-token" content="{{ csrf_token() }}">
        </div>
    </div>
</div>

<script>
    const STORAGE_URL = "{{ env('STORAGE_URL') }}";
    const storageUrl = "{{ config('filesystems.storage_url') }}";
</script>
@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        $('#addNewStickerItemBtn').on('click', function() {
            resetStickerForm();
            $('#add_sticker_item_model').modal('show');
        });

        $(document).on('click', '.edit-sticker-item-btn', function() {
            const id = $(this).data('id');
            $.get(`{{ url('sticker_item') }}/${id}/edit`, function(data) {
                console.log(data);

                $('#sticker_category_id').val(data.id);
                $('#stk_category_name').val(data.sticker_name);
                $('#category_id').val(data.stk_cat_id);
                $('#status').val(data.status);

                const imageUrl = getStorageLink(data.sticker_thumb)
                $('#sticker_thumbs').attr('data-value', imageUrl);

                const imageUrlFile = getStorageLink(data.sticker_image)
                $('#sticker_images').attr('data-value', imageUrlFile)

                dynamicFileCmp();
                $('#result').html('');
                $('#add_sticker_item_model').modal('show');
            });
        });

        $('#sticker_cate_form').on('submit', function(e) {
            e.preventDefault();
            const formElement = $('#sticker_cate_form')[0];
            const formData = new FormData(formElement);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `{{ route('sticker_item.store') }}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error saving sticker');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        });

        $('.delete-sticker-item').click(function() {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this sticker category?')) return;
            $.ajax({
                url: "{{ route('sticker_item.destroy', ':id') }}".replace(":id", id),
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload(); // or dynamically remove the row from the table
                },
                error: function(xhr) {
                    alert('Something went wrong. Try again.');
                    console.error(xhr.responseText);
                }
            });
        });

        function resetStickerForm() {
            $('#sticker_cate_form')[0].reset();
            $('#sticker_category_id').val('');
            resetDynamicFileValue("sticker_thumbs")
            resetDynamicFileValue("sticker_images")
        }
    });

    function premium_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;

        var url = "{{ route('stk.premium', ': status ') }}";
        url = url.replace(":status", status);

        var formData = new FormData();
        formData.append('id', $id);


        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    var x = document.getElementById("premium_label_" + $id);
                    if (x.innerHTML === "TRUE") {
                        x.innerHTML = "FALSE";
                    } else {
                        x.innerHTML = "TRUE";
                    }
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
    }

    function status_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;


        var url = "{{ route('stk.status', ': status ') }}";
        url = url.replace(":status", status);

        var formData = new FormData();
        formData.append('id', $id);


        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    var x = document.getElementById("status_label_" + $id);
                    if (x.innerHTML === "Live") {
                        x.innerHTML = "Not Live";
                    } else {
                        x.innerHTML = "Live";
                    }
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
    }
</script>
</body>

</html>

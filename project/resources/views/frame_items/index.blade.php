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
                        <div class="col-md-3">
                            @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                                <button class="btn btn-primary m-1 item-form-input" role="button" data-backdrop="static"
                                    id="addNewFrameItemBtn" type="button">
                                    + Add New Frame Item
                                </button>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('frame_items.index'),
                            ])
                        </div>
                    </div>


                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th class="datatable-nosort">Name</th>
                                    <th>Frame Category Name</th>
                                    <th class="datatable-nosort">Thumb</th>
                                    <th class="datatable-nosort">File</th>
                                    <th>Is Premium</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($framesItems as $framesItem)
                                    <tr>
                                        <td class="table-plus">{{ $framesItem->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($framesItem->emp_id) }}
                                        <td id="name">{{ $framesItem->name }}</td>
                                        <td>{{ optional($framesItem->frameCategory)->name ?? '' }}</td>

                                        <td><img src="{{ $contentManager::getStorageLink($framesItem->thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td><img src="{{ $contentManager::getStorageLink($framesItem->file) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>

                                        @if ($framesItem->is_premium == '1')
                                            <td>
                                                <label id="premium_label_{{ $framesItem->id }}"
                                                    style="display: none;">TRUE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" checked class="hidden-checkbox"
                                                        onclick="premium_click('{{ $framesItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @else
                                            <td>
                                                <label id="premium_label_{{ $framesItem->id }}"
                                                    style="display: none;">FALSE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" class="hidden-checkbox"
                                                        onclick="premium_click('{{ $framesItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @endif

                                        @if ($framesItem->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-frame-item-btn"
                                                    data-id="{{ $framesItem->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deleteFrameItem({{ $framesItem['id'] }})">
                                                        <i class="dw dw-delete-3"></i> Delete
                                                    </a>
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

                @include('partials.pagination', ['items' => $framesItems])
            </div>
        </div>
    </div>
</div>

<div class="modal fade designer-access-container" id="add_frame_item_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add New Frame Item</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="result"></div>
            <div class="modal-body">
                <form method="post" id="add_frame_item_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="frame_item_id" id="frame_item_id" value="">

                    <div class="form-group">
                        <h6>Name</h6>
                        <input type="text" class="form-control" name="name" placeholder="Name" id="itemName">
                    </div>

                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>
                        <select class="form-control" name="frame_category_id" id="frame_category_id">
                            <option value="0">== none ==</option>
                            @foreach ($allCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Frame Items Thumb</h6>
                        <input type="file" class="form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .svg" id="thumbs" data-imgstore-id="thumb"
                            data-nameset="true">
                    </div>

                    <div class="form-group">
                        <h6>Frame Items File</h6>
                        <input type="file" class="form-control dynamic-file height-auto" data-accept=".svg"
                            data-imgstore-id="file" id="files" data-nameset="true" accept="image/*">
                    </div>

                    <div class="form-group">
                        <h6>Is Premium</h6>
                        <select id="is_premium" class="form-control" data-style="btn-outline-primary"
                            name="is_premium" accept="image/*">
                            <option value="0">FALSE</option>
                            <option value="1">TRUE</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <select class=" form-control" data-style="btn-outline-primary" id="status"
                            name="status">
                            <option value="1">LIVE</option>
                            <option value="0">NOT LIVE</option>
                        </select>
                    </div>

                    <div>
                        <input class="btn btn-primary" type="submit" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const STORAGE_URL = "{{ env('STORAGE_URL') }}";
    // *Debug
    const storageUrl = "{{ config('filesystems.storage_url') }}";
</script>
@include('layouts.masterscript')
<script>
    function toggleCheckbox(button, parameter) {
        var checkbox = button.querySelector('input[ type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        premium_click(parameter);
    }

    $(document).ready(function() {

        $('#addNewFrameItemBtn').on('click', function() {
            resetValue();
            $('#add_frame_item_model').modal('show');
            // $('.selectpicker').selectpicker('refresh');
        });

        $('#add_frame_item_form').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let frame_item_id = $('#frame_item_id').val();
            if (frame_item_id) {
                formData.append('frame_item_id', frame_item_id);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('frame_items.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        alert(response.error || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    alert("Error: " + xhr.responseText);
                }
            });
        });

        $(document).on('click', '.edit-frame-item-btn', function() {
            const id = $(this).data('id');

            $.get("{{ url('frame_items') }}/" + id + "/edit", function(data) {

                $('#add_frame_item_model').modal('show');
                $('#modal_title').text("Edit Frame Item");

                const imageUrl = getStorageLink(data.thumb)
                $('#thumbs').attr('data-value', imageUrl);
                const imageUrlFils = getStorageLink(data.file)
                $('#files').attr('data-value', imageUrlFils);
                dynamicFileCmp();

                $('#frame_item_id').val(data.id);
                $('#itemName').val(data.name);

                $('#frame_category_id').val(data.frame_category_id).change();

            });
        });

        $('#add_frame_item_model').on('hidden.bs.modal', function() {
            resetValue();
        });

        function resetValue() {
            $('#modal_title').text("Add New Frame Item");
            $('#add_frame_item_form')[0].reset();
            $('#frame_item_id').val('');
            $('#result').html('');
            $('#parentCategoryInput span').text('== none ==');
            resetDynamicFileValue("thumbs")
            resetDynamicFileValue("files")
        }

    });

    function deleteFrameItem(id) {
        event.preventDefault()
        if (confirm('Are you sure you want to delete this frame item?')) {
            $.ajax({
                url: "{{ route('frame_items.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr) {}
            });
        }
    }

    function premium_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var status = $id;
        var url = "{{ route('frameItem.premium', ': status ') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(data) {
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
</script>
</body>

</html>

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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewgifItemBtn">
                                    + Add gif Item
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('gif_items.index'),
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
                                    <th>Name</th>
                                    <th>Category Name</th>
                                    <th class="datatable-nosort">Thumb</th>
                                    <th>File</th>
                                    <th>Is Premium</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allCategories as $gifItem)
                                    <tr>
                                        <td class="table-plus">{{ $gifItem->id }}</td>
                                        <td class="table-plus">{{ $roleManager::getUploaderName($gifItem->emp_id) }}
                                        </td>
                                        <td>{{ $gifItem->name }}</td>
                                        <td>{{ optional($gifItem->gifCategory)->name ?? '' }}</td>
                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($gifItem->thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($gifItem->file) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        @if ($gifItem->is_premium == '1')
                                            <td>
                                                <label id="premium_label_{{ $gifItem->id }}"
                                                    style="display: none;">TRUE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" checked class="hidden-checkbox"
                                                        onclick="premium_click('{{ $gifItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @else
                                            <td>
                                                <label id="premium_label_{{ $gifItem->id }}"
                                                    style="display: none;">FALSE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" class="hidden-checkbox"
                                                        onclick="premium_click('{{ $gifItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @endif

                                        @if ($gifItem->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">

                                                <button class="dropdown-item edit-gif-item-btn"
                                                    data-id="{{ $gifItem->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deletegifCategory({{ $gifItem['id'] }})">
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

                @include('partials.pagination', ['items' => $allCategories])

            </div>
        </div>
    </div>
</div>

<div class="modal fade designer-access-container" id="add_gif_item_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Gif </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_gif_item_form" enctype="multipart/form-data">
                    <input type="hidden" name="gif_item_id" id="gif_item_id">
                    <div class="form-group">
                        <h6>Gif Name</h6>
                        <input class="form-control" type="textname" id="gifCategoryName" name="name" required>
                    </div>
                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>

                        <select id="gif_category_id" class="form-control" name="gif_category_id" required>
                            <option value="" disabled selected>== Select Category ==</option>
                            @foreach ($perentCategory as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <input type="hidden" name="parent_category_id" value="0"> --}}
                    <div class="form-group">
                        <h6>gif Category Thumb</h6>
                        <input type="file" class="form-control-file form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .gif" data-imgstore-id="thumb" id="thumbs"
                            data-nameset="true">
                    </div>
                    <div class="form-group">
                        <h6>Frame Items File</h6>
                        <input type="file" class="form-control-file form-control dynamic-file height-auto"
                            data-accept=".gif" data-imgstore-id="file" id="files" data-nameset="true">
                    </div>

                    <div class="form-group">
                        <h6>Is Premium</h6>
                        <select id="is_premium" class="form-control" data-style="btn-outline-primary" name="is_premium"
                            accept="image/*">
                            <option value="0">FALSE</option>
                            <option value="1">TRUE</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" id="status"
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

    function premium_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var status = $id;
        var url = "{{ route('gifItem.premium', ': status ') }}";
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

    function deletegifCategory(id) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this gif Item?')) {
            $.ajax({
                url: "{{ route('gif_items.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        window.location.reload();
                    } else {
                        alert(response.error || 'Failed to delete gif item.');
                    }
                },
                error: function(xhr) {
                    alert('Failed to delete gif item. Please try again later.');
                }
            });
        }
    }

    $(document).ready(function() {
        $('#addNewgifItemBtn').on('click', function() {
            resetgifItemForm();
            $('#add_gif_item_model .modal-title').text('Add gif Item');
            $('#add_gif_item_model').modal('show');
        });

        $(document).on('click', '.edit-gif-item-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ url('gif_items') }}/${id}/edit`, function(data) {
                if (data) {
                    $('#add_gif_item_model').modal('show');
                    $('#add_gif_item_model .modal-title').text('Edit gif Item');
                    $('#gif_item_id').val(data.id);
                    $('#gifCategoryName').val(data.name);
                    $('#gif_category_id').val(data.gif_category_id);
                    $('#status').val(data.status);

                    const thumbUrl = getStorageLink(data.thumb);
                    $('#thumbs').attr('data-value', thumbUrl);

                    const thumbUrlFiles = getStorageLink(data.file);
                    $('#files').attr('data-value', thumbUrlFiles);
                    dynamicFileCmp();
                }
            });
        });

        $('#add_gif_item_form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = $('#gif_item_id').val();
            if (id) {
                formData.append('id', id);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `{{ route('gif_items.store') }}`,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        });

        function resetgifItemForm() {
            $('#add_gif_item_form')[0].reset();
            $('#gif_item_id').val('');
            resetDynamicFileValue("files")
            resetDynamicFileValue("thumbs")
        }
    });
</script>
</body>

</html>

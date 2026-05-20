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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewbackgroundItemBtn">
                                    + Add background Item
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('show_bg_item.index'),
                            ])
                        </div>
                    </div>
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Name</th>
                                    <th>Category Name</th>
                                    <th class="datatable-nosort">Thumb</th>
                                    <th>Image</th>
                                    <th>Type</th>
                                    <th>Is Premium</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort" style="width:150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allCategories as $backgroundItem)
                                    <tr>
                                        <td class="table-plus">{{ $backgroundItem->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($backgroundItem->emp_id) }}
                                        <td>{{ $backgroundItem->bg_name }}</td>
                                        <td>{{ optional($backgroundItem->BgCategory)->bg_category_name ?? '' }}</td>

                                        <td><img src="{{ $contentManager::getStorageLink($backgroundItem->bg_thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td><img src="{{ $contentManager::getStorageLink($backgroundItem->bg_image) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ optional($backgroundItem->BgMode)->type ?? '' }}</td>
                                        @if ($backgroundItem->is_premium == '1')
                                            <td>
                                                <label id="premium_label_{{ $backgroundItem->id }}"
                                                    style="display: none;">TRUE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" checked class="hidden-checkbox"
                                                        onclick="premium_click('{{ $backgroundItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @else
                                            <td>
                                                <label id="premium_label_{{ $backgroundItem->id }}"
                                                    style="display: none;">FALSE</label>
                                                <label class="switch-new">
                                                    <input type="checkbox" class="hidden-checkbox"
                                                        onclick="premium_click('{{ $backgroundItem->id }}')">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        @endif

                                        @if ($backgroundItem->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-background-item-btn"
                                                    data-id="{{ $backgroundItem->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deletebackgroundCategory({{ $backgroundItem['id'] }})">
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

<div class="modal fade designer-access-container" id="add_background_item_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add background Item</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_background_item_form" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="background_item_id">
                    <div class="form-group">
                        <h6>Background Name</h6>
                        <input class="form-control" type="textname" id="backgroundCategoryName" name="bg_name" required>
                    </div>
                    <div class="form-group category-dropbox-wrap">
                        <h6>Background Category</h6>
                        <select id="show_bg_category_id" class="form-control" name="bg_cat_id" required>
                            <option value="" disabled selected>== Select Category ==</option>
                            @foreach ($perentCategory as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->bg_category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Background Type</h6>
                        <div class="col-sm-20">
                            <select id="bg_type" class="selectpicker form-control" data-style="btn-outline-primary"
                                name="bg_type">
                                @foreach ($bgMode as $bg)
                                    <option value="{{ $bg->value }}">{{ $bg->type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- <input type="hidden" name="parent_category_id" value="0"> --}}
                    <div class="form-group">
                        <h6>background Category Thumb</h6>
                        <input type="file"
                            class="form-control-file form-select form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .background" data-imgstore-id="bg_thumb" id="bg_thumb"
                            data-nameset="true">
                    </div>
                    <div class="form-group">
                        <h6>Frame Items File</h6>
                        <input type="file" class="form-control-file form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .background" data-imgstore-id="bg_image" id="bg_image"
                            data-nameset="true">
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
        var url = "{{ route('backgroundItem.premium', ': status ') }}";
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

    function deletebackgroundCategory(id) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this background Item?')) {
            $.ajax({
                url: "{{ route('show_bg_item.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        window.location.reload();
                    } else {
                        alert(response.error || 'Failed to delete background item.');
                    }
                },
                error: function(xhr) {
                    alert('Failed to delete background item. Please try again later.');
                }
            });
        }
    }

    $(document).ready(function() {
        $('#addNewbackgroundItemBtn').on('click', function() {
            resetbackgroundItemForm();
            $('#add_background_item_model .modal-title').text('Add background Item');
            $('#add_background_item_model').modal('show');
        });

        $(document).on('click', '.edit-background-item-btn', function() {
            const id = $(this).data('id');
            // alert(id);
            $.get(`{{ url('show_bg_item') }}/${id}/edit`, function(data) {
                if (data) {
                    $('#add_background_item_model').modal('show');
                    $('#add_background_item_model .modal-title').text('Edit background Item');
                    $('#background_item_id').val(data.id);
                    $('#backgroundCategoryName').val(data.bg_name);
                    $('#show_bg_category_id').val(data.bg_cat_id);
                    $('#bg_type').val(data.bg_type);
                    $('#is_premium').val(data.is_premium);
                    $('#status').val(data.status);

                    const thumbUrl = getStorageLink(data.bg_thumb);
                    $('#bg_thumb').attr('data-value', thumbUrl);

                    const thumbUrlFiles = getStorageLink(data.bg_image);
                    $('#bg_image').attr('data-value', thumbUrlFiles);
                    dynamicFileCmp();
                }
            });
        });

        $('#add_background_item_form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = $('#background_item_id').val();
            if (id) {
                formData.append('id', id);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `{{ route('show_bg_item.store') }}`,
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

        function resetbackgroundItemForm() {
            $('#add_background_item_form')[0].reset();
            $('#background_item_id').val('');
            resetDynamicFileValue("files")
            resetDynamicFileValue("thumbs")
        }
    });
</script>
</body>

</html>

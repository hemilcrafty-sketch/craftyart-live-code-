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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewbackgroundCatBtn">
                                    + Add Background Category
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('show_bg_cat.index'),
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
                                    <th class="datatable-nosort">Thumb</th>
                                    <th>Sequence Number</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allCategories as $backgroundCat)
                                    <tr>
                                        <td class="table-plus">{{ $backgroundCat->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($backgroundCat->emp_id) }}
                                        <td>{{ $backgroundCat->bg_category_name }}</td>
                                        <td><img src="{{ $contentManager::getStorageLink($backgroundCat->bg_category_thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ $backgroundCat->sequence_number }}</td>
                                        @if ($backgroundCat->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif
                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-background-category-btn"
                                                    data-id="{{ $backgroundCat->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deleteBackgroundCategory({{ $backgroundCat['id'] }})">
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
</div>

<div class="modal fade designer-access-container" id="add_show_bg_category_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Background Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="show_bg_cate_form" enctype="multipart/form-data">
                    <div class="form-group">
                        <h6>Background Category Name</h6>
                        <input type="hidden" id="show_bg_category_id" name="id" value="">

                        <input class="form-control" type="textname" id="show_bg_category_name" name="bg_category_name"
                            required>
                    </div>
                    {{-- <input type="hidden" name="parent_category_id" value="0"> --}}
                    <div class="form-group">
                        <h6>Background Category Thumb</h6>
                        <input type="file" class="form-control-file form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .svg" data-imgstore-id="bg_category_thumb"
                            id="bg_category_thumb" data-nameset="true">
                    </div>
                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="number" id="sequence_number" name="sequence_number" required>
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
    function deleteBackgroundCategory(id) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this Background Category?')) {
            $.ajax({
                url: "{{ route('show_bg_cat.destroy', ':id') }}".replace(':id', id),
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
                error: function(xhr) {

                }
            });
        }
    }


    $(document).ready(function() {
        $('#addNewbackgroundCatBtn').on('click', function() {
            resetForm();
            $('#add_show_bg_category_model').modal('show');
        });

        $(document).on('click', '.edit-background-category-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ url('show_bg_cat') }}/${id}/edit`, function(res) {
                if (res.status) {
                    const data = res.data;
                    $('#add_show_bg_category_model').modal('show');
                    $('#show_bg_category_id').val(data.id);
                    $('#show_bg_category_name').val(data.bg_category_name);
                    $('#parent_category_id').val(data.parent_category_id);
                    $('#sequence_number').val(data.sequence_number);
                    $('#status').val(data.status);

                    if (data.bg_category_thumb) {
                        const imageUrl = getStorageLink(data.bg_category_thumb);
                        $('#bg_category_thumb').attr('data-value', imageUrl);
                        dynamicFileCmp();
                    }
                }
            });
        });

        $('#show_bg_cate_form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `{{ route('show_bg_cat.store') }}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error occurred!');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        });

        function resetForm() {
            $('#show_bg_cate_form')[0].reset();
            $('#show_bg_category_id').val('');
            resetDynamicFileValue('thumb_files')
            $('#result').html('');
        }
    });
</script>
</body>

</html>

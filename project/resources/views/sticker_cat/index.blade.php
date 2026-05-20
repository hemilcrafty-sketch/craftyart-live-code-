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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewStickerCatBtn">
                                    + Add Sticker Category
                                </button>
                            @endif
                        </div>
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('show_sticker_cat.index'),
                            ])
                        </div>
                    </div>
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Name</th>
                                    <th class="datatable-nosort">Category Thumb</th>
                                    <th>Sequence Number</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stickerCatArray as $stickerCat)
                                    <tr>
                                        <td class="table-plus">{{ $stickerCat->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($stickerCat->emp_id) }}
                                        <td>{{ $stickerCat->stk_category_name }}</td>
                                        <td><img src="{{ $contentManager::getStorageLink($stickerCat->stk_category_thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ $stickerCat->sequence_number }}</td>
                                        @if ($stickerCat->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-sticker-category-btn"
                                                    data-id="{{ $stickerCat->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <button type="button" class="dropdown-item" id="delete-sticker-cat"
                                                        data-id="{{ $stickerCat->id }}">
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
                @include('partials.pagination', ['items' => $stickerCatArray])
            </div>
        </div>
    </div>
</div>

<div class="modal fade designer-access-container" id="add_sticker_category_model" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Sticker Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form method="POST" id="sticker_cate_form" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="sticker_category_id">

                    <div class="form-group">
                        <h6>Sticker Category Name</h6>
                        <input class="form-control" type="text" id="stk_category_name" name="stk_category_name"
                            required>
                    </div>

                    <div class="form-group">
                        <h6>Sticker Category Thumb</h6>
                        <input type="file" class="form-control-file form-control dynamic-file" id="stkCategoryThumb"
                            data-accept=".jpg, .jpeg, .webp, .svg" accept="image/*"
                            data-imgstore-id="stk_category_thumb" data-nameset="true">
                    </div>

                    <div class="form-group">
                        <h6>Sequence Number</h6>
                        <input class="form-control" type="text" id="sequence_number" name="sequence_number" required>
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
    // *Debug
    const storageUrl = "{{ config('filesystems.storage_url') }}";
</script>
@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        $('#addNewStickerCatBtn').on('click', function() {
            resetStickerCategoryForm();
            $('#add_sticker_category_model').modal('show');
        });

        $(document).on('click', '.edit-sticker-category-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ url('show_sticker_cat') }}/${id}/edit`, function(data) {
                $('#add_sticker_category_model').modal('show');

                $('#sticker_category_id').val(data.id);
                $('#stk_category_name').val(data.stk_category_name);
                $('#sequence_number').val(data.sequence_number);
                $('#status').val(data.status);

                const imageUrl = getStorageLink(data.stk_category_thumb)
                $('#stkCategoryThumb').attr('data-value', imageUrl);
                dynamicFileCmp();
                $('#result').html('');

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
                url: `{{ route('show_sticker_cat.store') }}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert(data.error);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        });

        $(document).on('click', '#delete-sticker-cat', function() {
            event.preventDefault();
            const id = $(this).data('id');
            // alert(id);
            if (!confirm('Are you sure you want to delete this sticker category?')) return;
            $.ajax({
                url: "{{ route('show_sticker_cat.destroy', ':id') }}".replace(":id", id),
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


        function resetStickerCategoryForm() {
            $('#sticker_cate_form')[0].reset();
            $('#sticker_category_id').val('');
            resetDynamicFileValue("stkCategoryThumb")
        }
    });
</script>
</body>

</html>

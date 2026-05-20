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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewFrameCatBtn">
                                    + Add Frame Category
                                </button>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('frame_categories.index'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Name</th>
                                    <th>Category Thumb</th>
                                    <th>Sequence Number</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allCategories as $framesCat)
                                    <tr>
                                        <td class="table-plus">{{ $framesCat->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($framesCat->emp_id) }}
                                        <td>{{ $framesCat->name }}</td>
                                        <td><img src="{{ $contentManager::getStorageLink($framesCat->thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ $framesCat->sequence_number }}</td>
                                        @if ($framesCat->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-frame-category-btn"
                                                    data-id="{{ $framesCat->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deleteFrameCategory({{ $framesCat['id'] }})">
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

<div class="modal fade designer-access-container" id="add_frame_category_model" tabindex="-1" role="dialog"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Frame Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form method="POST" id="frame_cate_form" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="frame_category_id">

                    <div class="form-group">
                        <h6>Frame Category Name</h6>
                        <input class="form-control" type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <h6>Parent Category</h6>
                        <select class="form-control" name="parent_category_id" id="parent_category_id">
                            <option value="0">== none ==</option>
                            @foreach ($allCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @if (!empty($category->subcategories))
                                    @foreach ($category->subcategories as $sub)
                                        <option value="{{ $sub->id }}">-- {{ $sub->name }}</option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Frame Category Thumb</h6>
                        <input type="file" class="form-control-file form-control dynamic-file" id="thumb_file"
                            data-accept=".jpg, .jpeg, .webp, .svg" accept="image/*" data-imgstore-id="thumb"
                            data-nameset="true">
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
                        <button type="submit" class="btn btn-primary" id="frame_form_submit">Submit</button>
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
    function deleteFrameCategory(id) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this frame category?')) {
            $.ajax({
                url: "{{ route('frame_categories.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Failed to delete frame category. Please try again later.');
                }
            });
        }
    }

    $(document).ready(function() {
        $('#addNewFrameCatBtn').on('click', function() {
            resetFrameCategoryForm();
            $('#add_frame_category_model').modal('show');
        });

        $(document).on('click', '.edit-frame-category-btn', function() {
            const id = $(this).data('id');
            $.get("{{ url('frame_categories') }}/" + id + "/edit")
                .done(function(data) {
                    $('#frame_category_id').val(data.id);
                    $('#name').val(data.name);
                    $('#parent_category_id').val(data.parent_category_id);
                    $('#sequence_number').val(data.sequence_number);
                    $('#status').val(data.status);

                    const imageUrl = getStorageLink(data.thumb);
                    $('#thumb_file').attr('data-value', imageUrl);
                    dynamicFileCmp();
                    $('#result').html('');

                    $('#add_frame_category_model').modal('show');
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    alert("Failed to load frame category. Please try again.");
                });
        });

        $('#frame_cate_form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: `{{ route('frame_categories.store') }}`,
                data: formData,
                contentType: false,
                processData: false,
                success: res => res.status ? location.reload() : alert(res.error),
                error: xhr => alert(xhr.responseText)
            });
        });


        function resetFrameCategoryForm() {
            $('#frame_cate_form')[0].reset();
            resetDynamicFileValue("thumb_file")
            $('#frame_category_id').val('');
        }
    });
</script>
</body>

</html>

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
                                <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewAudioCatBtn">
                                    + Add Audio Category
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('audio_cat.index'),
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
                                @foreach ($allCategories as $audioCat)
                                    <tr>
                                        <td class="table-plus">{{ $audioCat->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($audioCat->emp_id) }}
                                        <td>{{ $audioCat->name }}</td>
                                        <td><img src="{{ $contentManager::getStorageLink($audioCat->thumb) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ $audioCat->sequence_number }}</td>
                                        @if ($audioCat->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item edit-frame-category-btn"
                                                    data-id="{{ $audioCat->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="deleteAudioCategory({{ $audioCat['id'] }})">
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

<div class="modal fade designer-access-container" id="add_audio_category_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Audio Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="audio_cate_form" enctype="multipart/form-data">
                    <div class="form-group">
                        <h6>Audio Category Name</h6>
                        <input type="hidden" id="audio_category_id" name="id" value="">

                        <input class="form-control" type="textname" id="audio_category_name" name="name" required>
                    </div>
                    <div class="form-group category-dropbox-wrap">
                        <h6>Parent Category</h6>

                        <select id="parent_category_id" class="form-control" name="parent_category_id">
                            <option value="" disabled selected>== Select Category ==</option>
                            @foreach ($allCategories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <input type="hidden" name="parent_category_id" value="0"> --}}
                    <div class="form-group">
                        <h6>Audio Category Thumb</h6>
                        <input type="file" class="form-control-file form-control dynamic-file height-auto"
                            data-accept=".jpg, .jpeg, .webp, .svg" data-imgstore-id="thumb" id="thumb_files"
                            data-nameset="true">
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
    function deleteAudioCategory(id) {
        if (confirm('Are you sure you want to delete this audio category?')) {
            $.ajax({
                url: "{{ route('audio_cat.destroy', ':id') }}".replace(":id", id),
                type: 'DELETE',
                data: {
                    id: id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Failed to delete audio category. Please try again later.');
                }
            });
        }
    }

    $(document).ready(function() {
        $('#addNewAudioCatBtn').on('click', function() {
            resetForm();
            $('#add_audio_category_model').modal('show');
        });

        $(document).on('click', '.edit-frame-category-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ url('audio_cat') }}/${id}/edit`, function(res) {
                if (res.status) {
                    const data = res.data;
                    $('#add_audio_category_model').modal('show');
                    $('#audio_category_id').val(data.id);
                    $('#audio_category_name').val(data.name);
                    $('#parent_category_id').val(data.parent_category_id);
                    $('#sequence_number').val(data.sequence_number);
                    $('#status').val(data.status);

                    if (data.thumb) {
                        const imageUrl = getStorageLink(data.thumb);
                        $('#thumb_files').attr('data-value', imageUrl);
                        dynamicFileCmp();
                    }
                }
            });
        });

        $('#audio_cate_form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `{{ route('audio_cat.store') }}`,
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
            $('#audio_cate_form')[0].reset();
            $('#audio_category_id').val('');
            $('#thumb_files').removeAttr('data-value');
            $('#result').html('');
            resetDynamicFileValue("thumb_files")
        }
    });
</script>
</body>

</html>

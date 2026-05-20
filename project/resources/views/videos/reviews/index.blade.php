@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                            @if (!$roleManager::isSeoManager(Auth::user()->user_type) || $roleManager::isDesignerManager(Auth::user()->user_type))
                                <a href="#" class="btn btn-primary m-1 item-form-input" data-toggle="modal"
                                    data-target="#add_video_review" id="openAddModal">
                                    Add Video Review
                                </a>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('video_reviews.index'),
                            ])
                        </div>
                    </div>
                    
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Index</th>
                                    <th style="width: 120px;">User ID</th>
                                    <th style="width: 120px;">Name</th>
                                    <th style="width: 120px;">Email</th>
                                    <th style="width: 150px;">Profile Image</th>
                                    <th style="width: 400px;">Message</th>
                                    <th>Rating</th>
                                    <th>Is Approve</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($videoReviews as $videoReview)
                                    <tr>
                                        <td class="table-plus">{{ $videoReview->id }}</td>
                                        <td>{{ $videoReview->user_id }}</td>
                                        <td>{{ $videoReview->name ?? $videoReview->user->name }}</td>
                                        <td>{{ $videoReview->email ?? $videoReview->user->email }}</td>
                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($videoReview->photo_uri ?? ($videoReview->user?->photo_uri ?? '')) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td><label for="">{{ $videoReview->feedback }}</label></td>
                                        <td>{{ $videoReview->rate }}
                                            {!! \App\Http\Controllers\HelperController::generateStars($videoReview->rate) !!}
                                        </td>
                                        <td>
                                            <label id="premium_label_{{ $videoReview->id }}" style="display: none;">
                                                {{ $videoReview->is_approve == '1' ? 'TRUE' : 'FALSE' }}
                                            </label>
                                            <label class="switch-new">
                                                <input type="checkbox" class="hidden-checkbox"
                                                    {{ $videoReview->is_approve == '1' ? 'checked' : '' }}
                                                    onclick="onReviewStatus('{{ $videoReview->id }}')">
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            @php
                                                $isSeoManager =
                                                    $roleManager::isSeoManager(Auth::user()->user_type) ||
                                                    $roleManager::isDesignerManager(Auth::user()->user_type);
                                            @endphp

                                            <button class="dropdown-item edit-review-btn"
                                                data-id="{{ $videoReview->id }}"
                                                @if ($videoReview->user_id != null || $isSeoManager) disabled @endif>
                                                <i class="dw dw-edit2"></i> Edit
                                            </button>

                                            @php
                                                $isAdminOrSeoManager =
                                                    $roleManager::isAdmin(Auth::user()->user_type) ||
                                                    $roleManager::isSeoManager(Auth::user()->user_type);
                                            @endphp

                                            <button class="dropdown-item delete-review-btn"
                                                data-id="{{ $videoReview->id }}"
                                                @if (!$isAdminOrSeoManager) disabled @endif
                                                @if ($videoReview->user_id != null) disabled @endif>
                                                <i class="dw dw-delete-3"></i> Delete
                                            </button>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $videoReviews])
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_video_review" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered custom-modal-width">
        <div class="modal-content">
            <form id="addForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="review_id">

                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Video Review</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" id="closeButton" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="name"><strong>Name</strong></label>
                                <input class="form-control" placeholder="Name" id="name" name="name"
                                    type="text">
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="email"><strong>Email</strong></label>
                                <input class="form-control" placeholder="Email" id="email" name="email"
                                    type="email">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label><strong>Profile Pic</strong></label>
                                <input type="file" class="form-control-file form-control dynamic-file height-auto"
                                    data-imgstore-id="photo_uri" data-nameset="true" id="profile_pic_input"
                                    data-accept=".jpg, .jpeg, .webp, .svg">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label><strong>Rate</strong></label>
                                <input class="form-control" placeholder="Rate" name="rate" id="rate"
                                    type="number" min="1" max="5">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label><strong>Feedback</strong></label>
                                <textarea class="form-control" name="feedback" cols="15" id="feedback" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div id="result" class="mr-auto text-success"></div>
                    <button type="submit" class="btn btn-primary" id="btnSubmitForm">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const STORAGE_URL = "{{ env('STORAGE_URL') }}";
    const storageUrl = "{{ config('filesystems.storage_url') }}";
</script>
@include('layouts.masterscript')
<script>
    let videoReviews = @json($videoReviews);
    $(document).ready(function() {
        $('#openAddModal').click(function() {
            resetValue();
        });

        $('#closeButton').click(function() {
            resetValue();
        });

        function resetValue() {
            $('#addForm')[0].reset();
            $('#profile_pic_input').removeAttr('data-value');
            $('#photo_uri').attr('src', '');
            $('#review_id').val('');
            $('#result').html('');
        }
        
        $('#addForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('video_reviews.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        $('#result').html(
                            '<span class="text-success">Saved successfully!</span>');
                        $('#addForm')[0].reset();
                        $('#review_id').val('');
                        $('#add_video_review').modal('hide');
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || xhr.responseJSON || {};
                    let msg = errors.error || 'Something went wrong.';
                    $('#result').html('<span class="text-danger">' + msg + '</span>');
                }
            });
        });
    });

    $(document).on('click', '.edit-review-btn', function() {
        const data = $(this).data();
        const reviewId = videoReviews.data.find(p => p.id === data.id);
        $('#review_id').val(reviewId.id);
        $('#name').val(reviewId.name);
        $('#email').val(reviewId.email);
        $('#rate').val(reviewId.rate);
        $('#feedback').val(reviewId.feedback);
        const imageUrl = getStorageLink(reviewId.photo_uri)
        $('#profile_pic_input').attr('data-value', imageUrl);
        $('#result').html('');

        $('#add_video_review').modal('show');
        dynamicFileCmp();
    });

    const onReviewStatus = (id) => {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var formData = new FormData();
        formData.append('id', id);
        $.ajax({
            url: "{{ route('video_reviews.reviewStatus') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data.error) {
                    window.alert(data.error);
                } else {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(error) {
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

    $(document).ready(function() {
        $('.delete-review-btn').click(function() {
            var reviewId = $(this).data('id');

            if (confirm("Are you sure you want to delete this review?")) {
                $.ajax({
                    url: `{{ route('video_reviews.destroy', ':id') }}`.replace(':id', reviewId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while deleting the review');
                    }
                });
            }
        });
    });
</script>
</body>

</html>

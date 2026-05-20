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
                                <a class="btn btn-primary text-white m-1 item-form-input" data-toggle="modal"
                                    data-target="#add_video_page_review" id="openAddModal">
                                    Add Page Review
                                </a>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('video_page_reviews.index'),
                            ])
                        </div>
                    </div>
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Index</th>
                                    <th style="width: 120px;">User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="width:150px">Profile Image</th>
                                    <th>Page Type</th>
                                    <th>Page Value</th>
                                    <th style="width: 300px;">Message</th>
                                    <th>Rating</th>
                                    <th>Is Approve</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($videoPageReviews as $videoPageReview)
                                    <tr>
                                        <td class="table-plus">{{ $videoPageReview->id }}</td>
                                        <td>{{ $videoPageReview->user_id }}</td>
                                        <td>{{ $videoPageReview->name ?? ($videoPageReview->user?->name ?? 'N/A') }}</td>
                                        <td>{{ $videoPageReview->email ?? ($videoPageReview->user?->email ?? 'N/A') }}</td>
                                        <td>
                                            <img src="{{ $contentManager::getStorageLink($videoPageReview->photo_uri ?? ($videoPageReview->user?->photo_uri ?? '')) }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>
                                        <td>{{ $videoPageReview->video_type_name }}</td>
                                        <td><a href="{{ \App\Http\Controllers\HelperController::getVideoFrontendPageUrlById((int) $videoPageReview->p_type, $videoPageReview->p_id) }}"
                                                class="text-primary"
                                                target="_blank">{{ \App\Http\Controllers\HelperController::getPageValueByStringId($videoPageReview->p_type, $videoPageReview->p_id) }}</a>
                                        </td>
                                        <td><label>{{ $videoPageReview->feedback }}</label></td>
                                        <td>
                                            {{ $videoPageReview->rate }}
                                            {!! \App\Http\Controllers\HelperController::generateStars($videoPageReview->rate) !!}
                                        </td>
                                        <td>
                                            <label id="premium_label_{{ $videoPageReview->id }}" style="display: none;">
                                                {{ $videoPageReview->is_approve == '1' ? 'TRUE' : 'FALSE' }}
                                            </label>
                                            <label class="switch-new">
                                                <input type="checkbox" class="hidden-checkbox"
                                                    {{ $videoPageReview->is_approve == '1' ? 'checked' : '' }}
                                                    onclick="onReviewStatus('{{ $videoPageReview->id }}')">
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
                                                data-id="{{ $videoPageReview->id }}"
                                                @if ($videoPageReview->user_id != null || $isSeoManager) disabled @endif>
                                                <i class="dw dw-edit2"></i> Edit
                                            </button>

                                            @php
                                                $isAdminOrSeoManager = $roleManager::isAdminOrSeoManager(
                                                    Auth::user()->user_type,
                                                );
                                            @endphp

                                            <button class="dropdown-item delete-review-btn"
                                                data-id="{{ $videoPageReview->id }}"
                                                @if (!$isAdminOrSeoManager) disabled @endif
                                                @if ($videoPageReview->user_id != null) disabled @endif>
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
                @include('partials.pagination', ['items' => $videoPageReviews])
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="add_video_page_review" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered custom-modal-width">
        <div class="modal-content">
            <form id="addForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="review_id">

                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Page Review</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" id="closeButton">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="name"><strong>Name</strong></label>
                                <input class="form-control" placeholder="Name" name="name" type="text">
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="email"><strong>Email</strong></label>
                                <input class="form-control" placeholder="Email" name="email" type="email">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <label><strong>Profile Pic</strong></label>
                            <input type="file" class="form-control dynamic-file height-auto" id="profile_pic_input"
                                data-imgstore-id="photo_uri" data-nameset="true" accept=".jpg,.jpeg,.webp,.svg">
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label><strong>Page Type</strong></label>
                                <select name="p_type" id="p_type" class="form-control" required>
                                    <option value="" disabled selected>Select</option>
                                    <option value="6">New Category</option>
                                    <option value="7">Virtual Category</option>
                                    <option value="8">Product Page</option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label><strong>Page ID</strong></label>
                                <select name="p_id" id="p_id" class="form-control" required
                                    style="width: 100%">
                                    <option value="" disabled selected>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label><strong>Rate</strong></label>
                                <input class="form-control" placeholder="Rate" name="rate" type="number"
                                    min="1" max="5">
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label><strong>Feedback</strong></label>
                                <textarea class="form-control" name="feedback" cols="15" rows="5"></textarea>
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
    $(document).ready(function() {
        let videoPageReviews = @json($videoPageReviews);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#add_video_page_review').on('shown.bs.modal', function() {
            // Initialize p_type select2 only once
            if (!$('#p_type').hasClass('select2-hidden-accessible')) {
                $('#p_type').select2({
                    placeholder: 'Select an option',
                    width: '100%',
                    dropdownParent: $('#add_video_page_review')
                });
            }

            // Reset and disable p_id
            $('#p_id').prop('disabled', true);
            if ($('#p_id').hasClass('select2-hidden-accessible')) {
                $('#p_id').select2('destroy');
            }
            $('#p_id').empty().append('<option value="" disabled selected>Select</option>');

            // Handle p_type change
            $('#p_type').off('change.videoPageReview').on('change.videoPageReview', function() {
                const typeVal = $(this).val();
                console.log('Page type changed to:', typeVal);
                
                // Reset p_id
                if ($('#p_id').hasClass('select2-hidden-accessible')) {
                    $('#p_id').select2('destroy');
                }
                
                $('#p_id').empty().append('<option value="" disabled selected>Select</option>');
                
                if (typeVal) {
                    $('#p_id').prop('disabled', false);
                    
                    // Initialize select2 with AJAX
                    $('#p_id').select2({
                        placeholder: 'Type to search...',
                        width: '100%',
                        dropdownParent: $('#add_video_page_review'),
                        minimumInputLength: 1,
                        allowClear: true,
                        ajax: {
                            url: "{{ route('get_selected_video_page_data') }}",
                            dataType: 'json',
                            delay: 300,
                            data: function(params) {
                                console.log('AJAX request params:', {
                                    q: params.term,
                                    type: typeVal
                                });
                                return {
                                    q: params.term || '',
                                    type: typeVal
                                };
                            },
                            processResults: function(data) {
                                console.log('AJAX Response:', data);
                                if (!Array.isArray(data)) {
                                    console.error('Invalid response format:', data);
                                    return { results: [] };
                                }
                                return {
                                    results: data.map(function(item) {
                                        return {
                                            id: item.id,
                                            text: item.label
                                        };
                                    })
                                };
                            },
                            cache: false,
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                            }
                        }
                    });
                } else {
                    $('#p_id').prop('disabled', true);
                }
            });
        });

        function resetValue() {
            $('#addForm')[0].reset();
            $('#profile_pic_input').removeAttr('data-value');
            $('#photo_uri').attr('src', '');
            $('#review_id').val('');
            
            // Properly reset select2 elements
            if ($('#p_type').hasClass('select2-hidden-accessible')) {
                $('#p_type').val('').trigger('change');
            }
            if ($('#p_id').hasClass('select2-hidden-accessible')) {
                $('#p_id').select2('destroy');
            }
            $('#p_id').prop('disabled', true);
            
            $('#result').html('');
        }

        $('#openAddModal').click(function() {
            resetValue();
        });

        $('#closeButton').click(function() {
            resetValue();
            $('#add_video_page_review').modal('hide');
        });

        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('video_page_reviews.store') }}",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.success) {
                        $('#result').html(
                            '<span class="text-success">Saved Successfully!</span>');
                        $('#addForm')[0].reset();
                        $('#review_id').val('');
                        $('#add_video_page_review').modal('hide');
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

        $(document).on('click', '.edit-review-btn', function() {
            const data = $(this).data();
            const videoPageReviewId = videoPageReviews.data.find(p => p.id == data.id);
            if (!videoPageReviewId) {
                console.error('Review not found for ID:', data.id);
                return;
            }
            $('#review_id').val(videoPageReviewId.id);
            $('[name="name"]').val(videoPageReviewId.name);
            $('[name="email"]').val(videoPageReviewId.email);
            $('[name="rate"]').val(videoPageReviewId.rate);
            $('[name="feedback"]').val(videoPageReviewId.feedback);
            $('#p_type').val(videoPageReviewId.p_type).trigger('change');
            $('#p_id').empty().val(null).trigger('change');
            
            setTimeout(() => {
                $.ajax({
                    url: "{{ route('get_selected_video_page_title') }}",
                    method: "GET",
                    data: {
                        type: videoPageReviewId.p_type,
                        v_id: videoPageReviewId.p_id
                    },
                    success: function(response) {
                        const selectedOption = new Option(response.label, videoPageReviewId
                            .p_id, true, true);
                        $('#p_id').append(selectedOption).trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Error fetching label:', xhr.responseText);
                    }
                });
            }, 250);
            
            const imageUrl = getStorageLink(videoPageReviewId.photo_uri);
            $('#profile_pic_input').attr('data-value', imageUrl);
            $('#photo_uri').attr('src', imageUrl);
            $('#result').html('');
            dynamicFileCmp();
            $('#add_video_page_review').modal('show');
        });
    });

    const onReviewStatus = (id) => {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        let formData = new FormData();
        formData.append('id', id);
        $.ajax({
            url: "{{ route('video_page_reviews.reviewStatus') }}",
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
                    url: `{{ route('video_page_reviews.destroy', ':id') }}`.replace(':id', reviewId),
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

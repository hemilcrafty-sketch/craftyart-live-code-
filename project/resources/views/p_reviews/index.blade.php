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
                                     data-target="#add_preview" id="openAddModal">
                                     Add Page Review
                                 </a>
                             @endif

                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form', [
                                 'action' => route('p_reviews.index'),
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
                                 @foreach ($pReviews as $pReview)
                                     <tr>
                                         <td class="table-plus">{{ $pReview->id }}</td>
                                         <td>{{ $pReview->user_id }}</td>
                                         <td>{{ $pReview->name ?? ($pReview->user?->name ?? 'N/A') }}</td>
                                         <td>{{ $pReview->email ?? ($pReview->user?->email ?? 'N/A') }}</td>
                                         <td>
                                             <img src="{{ $contentManager::getStorageLink($pReview->photo_uri ?? ($pReview->user?->photo_uri ?? '')) }}"
                                                 style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                         </td>
                                         <td>{{ $pReview->page_type_name }}</td>
                                         <td><a href="{{ \App\Http\Controllers\HelperController::getFrontendPageUrlById($pReview->p_type, $pReview->p_id) }}"
                                                 class="text-primary"
                                                 target="_blank">{{ \App\Http\Controllers\HelperController::getPageValueByStringId($pReview->p_type, $pReview->p_id) }}</a>
                                         </td>
                                         <td><label>{{ $pReview->feedback }}</label></td>
                                         <td>
                                             {{ $pReview->rate }}
                                             {!! \App\Http\Controllers\HelperController::generateStars($pReview->rate) !!}
                                         </td>
                                         <td>
                                             <label id="premium_label_{{ $pReview->id }}" style="display: none;">
                                                 {{ $pReview->is_approve == '1' ? 'TRUE' : 'FALSE' }}
                                             </label>
                                             <label class="switch-new">
                                                 <input type="checkbox" class="hidden-checkbox"
                                                     {{ $pReview->is_approve == '1' ? 'checked' : '' }}
                                                     onclick="onReviewStatus('{{ $pReview->id }}')">
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
                                                 data-id="{{ $pReview->id }}"
                                                 @if ($pReview->user_id != null || $isSeoManager) disabled @endif>
                                                 <i class="dw dw-edit2"></i> Edit
                                             </button>

                                             @php
                                                 $isAdminOrSeoManager = $roleManager::isAdminOrSeoManager(
                                                     Auth::user()->user_type,
                                                 );
                                             @endphp

                                             <button class="dropdown-item delete-review-btn"
                                                 data-id="{{ $pReview->id }}"
                                                 @if (!$isAdminOrSeoManager) disabled @endif
                                                 @if ($pReview->user_id != null) disabled @endif>
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
                 @include('partials.pagination', ['items' => $pReviews])
             </div>
         </div>
     </div>
 </div>
 </div>

 <div class="modal fade" id="add_preview" tabindex="-1" role="dialog">
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
                                     <option value="" disabled {{ isset($pReview) ? '' : 'selected' }}>Select
                                     </option>
                                     @foreach ([0 => 'Product Page', 1 => 'New Category', 2 => 'Special Page', 3 => 'Special Keyword', 4 => 'Category', 5 => 'Virtual Category'] as $key => $label)
                                         <option value="{{ $key }}"
                                             {{ isset($pReview) && $pReview->p_type == $key ? 'selected' : '' }}>
                                             {{ $label }}
                                         </option>
                                     @endforeach
                                 </select>
                             </div>
                         </div>

                         <div class="col-md-6 col-sm-12">
                             <div class="form-group">
                                 <label><strong>Page ID</strong></label>
                                 <select name="p_id" id="p_id" class="form-control" required
                                     style="width: 100%">
                                     @if (isset($pReview))
                                         <option value="{{ $pReview->p_id }}" selected>
                                             {{ $pReview->page_label ?? 'Selected Page' }}
                                         </option>
                                     @else
                                         <option value="" disabled selected>Select</option>
                                     @endif
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
     // *Debug
     const storageUrl = "{{ config('filesystems.storage_url') }}";
 </script>
 @include('layouts.masterscript')
 <script>
     $(document).ready(function() {
         let pReviews = @json($pReviews);
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
             }
         });
         $('#add_preview').on('shown.bs.modal', function() {

             $('#p_type').select2({
                 placeholder: 'Select an option',
                 width: '100%',
                 dropdownParent: $('#add_preview')
             });

             $('#p_id').prop('disabled', true).val(null).trigger('change');

             $('#p_type').on('change', function() {
                 const typeVal = $(this).val();
                 $('#p_id').val(null).trigger('change');
                 $('#p_id').prop('disabled', false);
                 $('#p_id').select2({
                     placeholder: 'Search Page...',
                     width: '100%',
                     dropdownParent: $('#add_preview'),
                     minimumInputLength: 2,
                     ajax: {
                         url: "{{ route('get_selected_page_data') }}",
                         dataType: 'json',
                         delay: 250,
                         data: function(params) {
                             return {
                                 q: params.term,
                                 type: $('#p_type').val()
                             };
                         },
                         processResults: function(data) {
                             return {
                                 results: data.map(function(item) {
                                     return {
                                         id: item.id,
                                         text: item.label
                                     };
                                 })
                             };
                         },
                         cache: true
                     }
                 });
             });
             if ($('#p_type').val()) {
                 $('#p_type').trigger('change');
             }
         });

         function resetValue() {
             $('#addForm')[0].reset();
             $('#profile_pic_input').removeAttr('data-value');
             $('#photo_uri').attr('src', '');
             $('#review_id').val('');
             $('#p_type').val('').trigger('change');
             $('#p_id').val(null).trigger('change').prop('disabled', true);
             $('#result').html('');
         }
         $('#openAddModal').click(function() {
             resetValue();
         });
         $('#closeButton').click(function() {
             resetValue();
             $('#add_preview').modal('hide');
         });

         $('#addForm').on('submit', function(e) {
             e.preventDefault();
             let formData = new FormData(this);
             $.ajax({
                 url: "{{ route('p_reviews.store') }}",
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
                         $('#add_preview').modal('hide');
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
             const PreviewId = pReviews.data.find(p => p.id == data.id);
             if (!PreviewId) {
                 console.error('Review not found for ID:', data.id);
                 return;
             }
             $('#review_id').val(PreviewId.id);
             $('[name="name"]').val(PreviewId.name);
             $('[name="email"]').val(PreviewId.email);
             $('[name="rate"]').val(PreviewId.rate);
             $('[name="feedback"]').val(PreviewId.feedback);
             $('#p_type').val(PreviewId.p_type).trigger('change');
             $('#p_id').empty().val(null).trigger('change');
             setTimeout(() => {
                 $.ajax({
                     url: "{{ route('get_selected_page_title') }}",
                     method: "GET",
                     data: {
                         type: PreviewId.p_type,
                         p_id: PreviewId.p_id
                     },
                     success: function(response) {
                         const selectedOption = new Option(response.label, PreviewId
                             .p_id, true, true);
                         $('#p_id').append(selectedOption).trigger('change');
                     },
                     error: function(xhr) {
                         console.error('Error fetching label:', xhr.responseText);
                     }
                 });
             }, 250);
             const imageUrl = getStorageLink(PreviewId.photo_uri);
             $('#profile_pic_input').attr('data-value', imageUrl);
             $('#photo_uri').attr('src', imageUrl);
             $('#result').html('');
             dynamicFileCmp();
             $('#add_preview').modal('show');
         });
     });

     const onReviewStatus = (id) => {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         let status = id;
         let formData = new FormData();
         formData.append('id', id);
         $.ajax({
             url: "{{ route('p_reviews.reviewStatus') }}",
             type: 'POST',
             data: formData,
             success: function(data) {
                 if (data.error) {
                     window.alert(data.error);
                 } else {
                     if (data.is_approve == 1) {
                         $(`#btnReviewStatus-${id}`).removeClass("btn-primary");
                         $(`#btnReviewStatus-${id}`).addClass("btn-secondary");
                         $(`#btnReviewStatus-${id}`).text("Not Approved");
                     } else {
                         $(`#btnReviewStatus-${id}`).removeClass("btn-secondary");
                         $(`#btnReviewStatus-${id}`).addClass("btn-primary");
                         $(`#btnReviewStatus-${id}`).text("Approved");
                     }
                     setTimeout(() => {
                         location.reload();
                     }, 2000);
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

     $(document).ready(function() {
         $('.delete-review-btn').click(function() {
             var reviewId = $(this).data('id');

             if (confirm("Are you sure you want to delete this review?")) {
                 $.ajax({
                     url: `{{ route('p_reviews.destroy', ':id') }}`.replace(':id', reviewId),
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

 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

 <div class="main-container">
     <div class="">
         <div class="min-height-200px">
             <div class="card-box d-flex flex-column" style="height: 94vh; overflow: hidden;">

                 <div class="row justify-content-between">
                     <div class="col-md-2 m-1">
                         <a href="#" class="btn btn-primary item-form-input" data-backdrop="static" data-toggle="modal"
                             data-target="#category_feature_modal" type="button">
                             Add Category Feature </a>
                     </div>

                     <div class="col-md-7">
                         @include('partials.filter_form ', [
                             'action' => route('categoryFeatures.index'),
                         ])
                     </div>
                 </div>
                 <form id="create_relegion_action" action="{{ route('categoryFeatures.create') }}" method="GET"
                     style="display: none;">
                     <input type="text" id="passingAppId" name="passingAppId">
                     @csrf
                 </form>

                 <div class="flex-grow-1 overflow-auto">
                     <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;" >
                         <table id="temp_table" class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th>Id</th>
                                     <th>Name</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody id="relegion_table">
                                 @foreach ($categoryFeatures as $categoryFeatue)
                                     <tr style="background-color: #efefef;">
                                         <td class="table-plus">{{ $categoryFeatue->id }}</td>
                                         <td class="table-plus">{{ $categoryFeatue->name }}</td>
                                         <td>
                                             <Button class="dropdown-item btn-edits" data-id="{{ $categoryFeatue->id }}"
                                                 data-catfeature="{{ $categoryFeatue->name }}" data-backdrop="static"
                                                 data-toggle="modal" data-target="#edit_category_feature_model"><i
                                                     class="dw dw-edit2"></i> Edit
                                             </Button>
                                         </td>
                                     </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
                 <hr class="my-1">
                 @include('partials.pagination', ['items' => $categoryFeatures])
             </div>
         </div>
     </div>
     {{-- @include('color.create'); --}}
     <!-- Single Modal -->
     <div class="modal fade" id="category_feature_modal" tabindex="-1" role="dialog"
         aria-labelledby="categoryFeatureModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title modal-title-text">Add Category Feature</h5>
                     <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                 </div>
                 <span id="result"></span>
                 <div class="modal-body">
                     <form id="category_feature_form">
                         @csrf
                         <input type="hidden" name="id" id="feature_id">
                         <div class="form-group">
                             <label>Name</label>
                             <input type="text" class="form-control" placeholder="Enter Category Feature"
                                 name="name" id="feature_name" required>
                         </div>
                         <div class="row mt-3">
                             <div class="col-sm-12">
                                 <button type="submit" class="btn btn-primary btn-block" id="submit_btn">Save</button>
                             </div>
                         </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>

     @include('layouts.masterscript')

     <script>
         $(document).ready(function() {
             // Open modal for Add
             $(document).on("click", "#openAddModal", function() {
                 $("#category_feature_form")[0].reset();
                 $("#feature_id").val('');
                 $(".modal-title-text").text("Add Category Feature");
                 $("#category_feature_modal").modal("show");
             });

             // Open modal for Edit
             $(document).on("click", ".btn-edits", function() {
                 let id = $(this).data('id');
                 let name = $(this).data('catfeature');

                 $("#feature_id").val(id);
                 $("#feature_name").val(name);
                 $(".modal-title-text").text("Edit Category Feature");
                 $("#category_feature_modal").modal("show");
             });

             // Submit Form (Add or Update)
             $('#category_feature_form').on('submit', function(e) {
                 e.preventDefault();

                 const formData = new FormData();
                 formData.append('id', $('#feature_id').val());
                 formData.append('name', $('#feature_name').val());

                 $.ajaxSetup({
                     headers: {
                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                     }
                 });

                 $.ajax({
                     url: `{{ route('categoryFeatures.store') }}`,
                     type: 'POST',
                     data: formData,
                     contentType: false, // very important
                     processData: false, // very important
                     beforeSend: function() {
                         $("#loading_screen").show();
                     },
                     success: function(data) {
                         let msg = data.message || 'Success';
                         $('#result').html(`<div class="alert alert-success">${msg}</div>`);
                         $("#category_feature_modal").modal("hide");
                         setTimeout(() => location.reload(), 1000);
                     },
                     error: function(xhr) {
                         const response = xhr.responseJSON;
                         let msg = response?.message || 'Something went wrong.';
                         $('#result').html(`<div class="alert alert-danger">${msg}</div>`);
                     },
                     complete: function() {
                         $("#loading_screen").hide();
                     }
                 });
             });

         });
     </script>
     </body>

     </html>

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
                                   <button type="button" class="btn btn-primary m-1 item-form-input" id="addNewSvgCatBtn">
                                       + Add Svg Category
                                   </button>
                               @endif
                           </div>

                           <div class="col-md-7">
                               @include('partials.filter_form', [
                                   'action' => route('vector_categories.index'),
                               ])
                           </div>
                       </div>
                       <div class="scroll-wrapper table-responsive tableFixHead"
                           style="max-height: calc(110vh - 220px) !important">
                           <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">

                               <thead>
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
                                   @foreach ($allCategories as $svgCat)
                                       <tr>
                                           <td class="table-plus">{{ $svgCat->id }}</td>
                                           <td class="table-plus">{{ $roleManager::getUploaderName($svgCat->emp_id) }}
                                           </td>
                                           <td>{{ $svgCat->name }}</td>
                                           <td><img src="{{ $contentManager::getStorageLink($svgCat->thumb) }}"
                                                   style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                           </td>
                                           <td>{{ $svgCat->sequence_number }}</td>
                                           @if ($svgCat->status == '1')
                                               <td>LIVE</td>
                                           @else
                                               <td>NOT LIVE</td>
                                           @endif

                                           <td>
                                               <div class="d-flex">
                                                   <button class="dropdown-item edit-svg-category-btn"
                                                       data-id="{{ $svgCat->id }}">
                                                       <i class="dw dw-edit2"></i> Edit
                                                   </button>
                                                   @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                       <a class="dropdown-item" href="#"
                                                           onclick="deleteSvgCategory({{ $svgCat['id'] }})">
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

   <div class="modal fade designer-access-container" id="add_svg_category_model" tabindex="-1" role="dialog"
       aria-labelledby="myLargeModalLabel" aria-hidden="false">
       <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="myLargeModalLabel">Add Svg Category</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>

               <div class="modal-body">
                   <form method="post" id="add_svg_cate_form" enctype="multipart/form-data">
                       <input type="hidden" id="svg_category_id" name="id" value="">

                       <div class="form-group">
                           <h6>Svg Category Name</h6>
                           <input class="form-control" type="textname" id="svg_category_name" name="name" required>
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
                       <div class="form-group">
                           <h6>Svg Category Thumb</h6>
                           <input type="file" class="form-control-file form-control dynamic-file height-auto"
                               data-accept=".jpg, .jpeg, .webp, .svg" data-imgstore-id="thumb" id="thumbs"
                               data-nameset="true">
                       </div>

                       <div class="form-group">
                           <h6>Sequence Number</h6>
                           <input class="form-control" type="number" id="sequence_number" name="sequence_number"
                               required>
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
       $(document).ready(function() {
           $('#addNewSvgCatBtn').on('click', function() {
               resetSvgCategoryForm();
               $('#add_svg_category_model .modal-title').text('Add Svg Category');
               $('#add_svg_category_model').modal('show');
           });

           $(document).on('click', '.edit-svg-category-btn', function() {
               const id = $(this).data('id');
               $.get(`{{ url('vector_categories') }}/${id}/edit`, function(data) {
                   if (data) {
                       $('#add_svg_category_model').modal('show');
                       $('#add_svg_category_model .modal-title').text('Edit Svg Category');
                       $('#svg_category_id').val(data.id);
                       $('#svg_category_name').val(data.name);
                       $('#parent_category_id').val(data.parent_category_id);
                       $('#sequence_number').val(data.sequence_number);
                       $('#status').val(data.status);
                       const thumbUrl = getStorageLink(data.thumb);
                       $('#thumbs').attr('data-value', thumbUrl);
                       dynamicFileCmp();
                   }
               });
           });

           $('#add_svg_cate_form').on('submit', function(e) {
               e.preventDefault();
               const formData = new FormData(this);
               const id = $('#svg_category_id').val();
               if (id) {
                   formData.append('id', id);
               }

               $.ajaxSetup({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   }
               });

               $.ajax({
                   url: `{{ route('vector_categories.store') }}`,
                   method: 'POST',
                   data: formData,
                   contentType: false,
                   processData: false,
                   success: function(response) {
                       if (response.status) {
                           location.reload();
                       } else {
                           alert(response.error || 'Something went wrong.');
                       }
                   },
                   error: function(xhr) {
                       alert(xhr.responseText || 'Server error.');
                   }
               });
           });

           function resetSvgCategoryForm() {
               $('#add_svg_cate_form')[0].reset();
               $('#svg_category_id').val('');
               resetDynamicFileValue("thumb")
               $('#add_svg_category_model .modal-title').text('Add Svg Category');
               $('#result').html('');
           }
       });

       function deleteSvgCategory(id) {
           event.preventDefault();
           if (confirm('Are you sure you want to delete this svg category?')) {
               $.ajax({
                   url: "{{ route('vector_categories.destroy', ':id') }}".replace(':id', id),
                   type: 'DELETE',
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                           'content')
                   },
                   success: function(response) {
                       $('#svg-category-' + id)
                       location.reload();
                   },
                   error: function(xhr) {
                       alert('Failed to delete SVG category. Please try again later.');
                   }
               });
           }
       }
   </script>
   </body>

   </html>

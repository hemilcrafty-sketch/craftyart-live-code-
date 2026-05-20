 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container seo-all-container">


     <div class="pd-ltr-10 xs-pd-20-10">
         <div class="min-height-200px">
             <div class="card-box mb-30">
                 <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                     <div class="row justify-content-between">
                         <div class="col-md-3 m-1">
                             @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                                 <a href="#" class="btn btn-primary item-form-input" onclick="openAddModal()">Add Interest</a>
                             @endif

                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form ', [
                                 'action' => route('show_interest'),
                             ])
                         </div>
                     </div>

                     <div class="scroll-wrapper table-responsive tableFixHead">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th>Id</th>
                                     <th>Name</th>
                                     <th>New Category</th>
                                     <th>Status</th>
                                     <th>User</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($interestArray as $interest)
                                     @php
                                         $newCategoryIds =
                                             isset($interest->new_category_id) && $interest->new_category_id != null
                                                 ? json_decode($interest->new_category_id, true)
                                                 : [];
                                         if (!is_array($newCategoryIds)) {
                                             $newCategoryIds = [$newCategoryIds];
                                         }
                                     @endphp
                                     <tr>
                                         <td class="table-plus">{{ $interest->id }}</td>
                                         <td class="table-plus">{{ $interest->name }}</td>
                                         <td class="table-plus">
                                             {{ $helperController::getNewCatNames($newCategoryIds, true) }}
                                         </td>

                                         @if ($interest->status == '1')
                                             <td>Active</td>
                                         @else
                                             <td>Disabled</td>
                                         @endif
                                         <td>{{ $roleManager::getUploaderName($interest->emp_id) }}
                                         </td>
                                         <td>
                                             <div class="d-flex">

                                                 <button class="dropdown-item"
                                                     onclick="edit_click(
    '{{ $interest->id }}',
    '{{ addslashes($interest->name) }}',
    '{{ $interest->status }}',
    '{{ addslashes($interest->id_name) }}',
    '{{ json_encode(json_decode($interest->new_category_id, true)) }}'
)">
                                                     Edit
                                                 </button>


                                                 @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                     <Button class="dropdown-item"
                                                         onclick="delete_click('{{ $interest->id }}')"><i
                                                             class="dw dw-delete-3"></i> Delete</Button>
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
                 @include('partials.pagination', ['items' => $interestArray])
             </div>
         </div>
     </div>
 </div>

 <div class="modal fade seo-all-container" id="interest_modal" tabindex="-1" role="dialog"
     aria-labelledby="interest_modal_title" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">

             <div class="modal-header">
                 <h5 class="modal-title" id="interest_modal_title">Add Interest</h5>
                 <button type="button" class="close" data-bs-dismiss="modal">×</button>
             </div>

             <div class="modal-body">
                 <form id="add_interest_form" method="POST" enctype="multipart/form-data">
                     @csrf
                     <input type="hidden" name="id" id="interest_id" />

                     <div class="form-group">
                         <label>Name</label>
                         <input type="text" class="form-control" placeholder="Interest" id="interestName"
                             name="name" required />
                     </div>

                     <div class="form-group">
                         <label>ID Name</label>
                         <input type="text" class="form-control" placeholder="ID Name" id="interestIDName"
                             name="id_name" required />
                     </div>

                     <div class="form-group">
                         <h7>New Category</h7>
                         <div class="col-sm-20" id="newCategory">
                             <select class="custom-select2 form-control" multiple="multiple" name="new_category_ids[]"
                                 id="newEditCategoryIds" required>
                                 @foreach ($allNewCategories as $newCategory)
                                     <option value="{{ $newCategory->id }}">{{ $newCategory->category_name }}
                                     </option>
                                 @endforeach
                             </select>
                         </div>
                     </div>

                     <div class="form-group">
                         <label>Status</label>
                         <select id="status" class="form-control" name="status">
                             <option value="1">Active</option>
                             <option value="0">Disable</option>
                         </select>
                     </div>

                     <div class="form-group">
                         <button type="submit" id="interest_submit_btn" class="btn btn-primary btn-block">Save</button>
                     </div>
                 </form>
             </div>

         </div>
     </div>
 </div>
 @include('layouts.masterscript')
 <script>
     $(document).ready(function() {
         $('#newEditCategoryIds').select2({
             width: '100%',
             placeholder: "Select Categories",
             allowClear: true
         });
     });

     // Open Add Modal
     function openAddModal() {
         $('#interest_modal_title').text("Add Interest");
         $('#interest_submit_btn').text("Save");

         $('#interest_id').val('');
         $('#interestName').val('');
         $('#interestIDName').val('');
         $('#status').val('1');
         $('#newEditCategoryIds').val([]).trigger('change');

         $('#interest_modal').modal('show');
     }

     // Open Edit Modal
     function edit_click(id, name, status, idName, newCategoryIdJson) {
         $('#interest_modal_title').text("Edit Interest");
         $('#interest_submit_btn').text("Update");

         $('#interest_id').val(id);
         $('#interestName').val(name);
         $('#interestIDName').val(idName);
         $('#status').val(status);

         let categoryIds = [];
         try {
             categoryIds = JSON.parse(newCategoryIdJson) || [];
             categoryIds = categoryIds.map(String);
         } catch (e) {
             categoryIds = [];
         }

         $('#newEditCategoryIds').val(categoryIds).trigger('change');
         $('#interest_modal').modal('show');
     }

     // Submit Form
     $('#add_interest_form').on('submit', function(event) {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         const formData = new FormData(this);

         $.ajax({
             url: '{{ url('interest_store_or_update') }}',
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $("#main_loading_screen").show();
             },
             success: function(response) {
                 $("#main_loading_screen").hide();
                 if (response.error) {
                     alert('Error: ' + response.error);
                 } else {
                     alert(response.success || "Saved successfully.");
                     location.reload();
                 }
             },
             error: function(error) {
                 $("#main_loading_screen").hide();
                 alert("Something went wrong:\n" + error.responseText);
             },
             cache: false,
             contentType: false,
             processData: false
         });
     });

     // Reset on close
     $('#interest_modal').on('hidden.bs.modal', function() {
         $('#add_interest_form')[0].reset();
         $('#newEditCategoryIds').val([]).trigger('change');
         $('#interest_id').val('');
     });

     // Auto-generate ID name
     const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

     $(document).on("input", "#interestName", function() {
         const titleString = toTitleCase($(this).val());
         $("#interestIDName").val(titleString.toLowerCase().replace(/\s+/g, '-'));
         $(this).val(titleString);
     });


     function delete_click(id) {

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var url = "{{ route('interest.delete', ':id') }}";
         url = url.replace(":id", id);

         $.ajax({
             url: url,
             type: 'POST',
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert(data.error);
                 } else {
                     location.reload();
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
 </script>

 </body>

 </html>

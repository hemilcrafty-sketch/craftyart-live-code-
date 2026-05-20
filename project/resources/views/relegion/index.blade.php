   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')

   <div class="main-container">
       <div class="pd-ltr-10 xs-pd-20-10">
           <div class="min-height-200px">
               <div class="card-box mb-30">
                   <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                       <div class="row justify-content-between">
                           <div class="col-md-3 m-1">
                               @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                                   <a href="javascript:void(0)" class="btn btn-primary item-form-input" onclick="openAddModal()">Add
                                       Religion</a>
                               @endif
                           </div>
                           <div class="col-md-7">
                               @include('partials.filter_form ', [
                                   'action' => route('religions.index'),
                               ])
                           </div>
                       </div>


                       <div class="scroll-wrapper table-responsive tableFixHead">
                           <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">
                               <thead>
                                   <tr>
                                       <th>Id</th>
                                       <th>Relegion</th>
                                       <th>User</th>
                                       <th>Status</th>
                                       <th class="datatable-nosort">Action</th>
                                   </tr>
                               </thead>
                               <tbody id="relegion_table">
                                   @foreach ($religions as $religion)
                                       <tr style="background-color: #efefef;">
                                           <td class="table-plus">{{ $religion->id }}</td>
                                           <td class="table-plus">{{ $religion->religion_name }}</td>
                                           <td class="table-plus">
                                               {{ $roleManager::getUploaderName($religion->emp_id) }}
                                           </td>
                                           <td class="table-plus">
                                               {{ $religion->status ? 'Active' : 'UnActive' }}</td>
                                           <td>
                                               <div class="d-flex">

                                                   <button class="dropdown-item btn-edits" data-id="{{ $religion->id }}"
                                                       data-religion="{{ $religion->religion_name }}"
                                                       data-id-name="{{ $religion->id_name }}"
                                                       data-status="{{ $religion->status }}">
                                                       <i class="dw dw-edit2"></i> Edit
                                                   </button>
                                                   @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                       <button class="dropdown-item"
                                                           onclick="delete_click('{{ $religion->id }}')"><i
                                                               class="dw dw-delete-3"></i> Delete</button>
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
                   @include('partials.pagination', ['items' => $religions])
               </div>
           </div>
       </div>
   </div>
   </div>
   <div class="modal fade" id="delete_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
       aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <input type="text" id="delete_id" name="delete_id" style="display: none;">
               <div class="modal-header">
                   <h4 class="modal-title" id="myLargeModalLabel">Delete</h4>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>
               <div class="modal-body">
                   <p> Are you sure you want to delete? </p>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                   <button type="button" class="btn btn-primary" onclick="delete_click()">Yes, Delete</button>
               </div>
           </div>
       </div>
   </div>

   <div class="modal fade seo-all-container" id="religion_modal" tabindex="-1" role="dialog" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">

       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="religion_modal_title">Add Religion</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>
               <div class="modal-body">
                   <form id="religionForm">
                       @csrf
                       <input type="hidden" name="id" id="religion_id">

                       <div class="form-group">
                           <label>Religion Name</label>
                           <input type="text" class="form-control" name="religion_name" id="religion_name" required>
                       </div>

                       <div class="form-group">
                           <label>ID Name</label>
                           <input type="text" class="form-control" name="id_name" id="id_name" required>
                       </div>

                       <div class="form-group">
                           <label>Status</label>
                           <select class="form-control" name="status" id="status">
                               <option value="1">Active</option>
                               <option value="0">Disabled</option>
                           </select>
                       </div>

                       <div id="result"></div>
                       <button type="button" class="btn btn-primary btn-block" id="submitReligionForm">Submit</button>
                   </form>
               </div>
           </div>
       </div>
   </div>

   @include('layouts.masterscript')

   <script>
       const toTitleCase = str =>
           str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

       const toSnakeCase = str =>
           str.trim().toLowerCase().replace(/\s+/g, '-');

       // Auto-generate ID name when typing
       $(document).on("input", "#religion_name", function() {
           const title = toTitleCase($(this).val());
           const idName = toSnakeCase(title);
           $(this).val(title);
           $("#id_name").val(idName);
       });

       function openAddModal() {
           $('#religion_modal_title').text("Add Religion");
           $('#submitReligionForm').text("Save");
           $('#religionForm')[0].reset();
           $('#religion_id').val('');
           $('#result').html('');
           $('#religion_modal').modal('show');
       }

       $(document).on("click", "#relegion_table .btn-edits", function(e) {
           e.preventDefault();
           $('#religion_modal_title').text("Edit Religion");
           $('#submitReligionForm').text("Update");
           $('#result').html('');

           const $btn = $(this);
           $('#religion_id').val($btn.attr('data-id'));
           $('#religion_name').val($btn.attr('data-religion'));
           $('#id_name').val($btn.attr('data-id-name'));
           $('#status').val(String($btn.attr('data-status') ?? '1'));

           $('#religion_modal').modal('show');
       });

       $(document).on("click", "#submitReligionForm", function() {
           const id = $("#religion_id").val();
           const formData = {
               religion_name: $("#religion_name").val(),
               id_name: $("#id_name").val(),
               status: $("#status").val(),
               religion_id: id,
               _token: $('meta[name="csrf-token"]').attr("content")
           };

           $.ajax({
               url: "{{ route('religions.submit') }}",
               type: "POST",
               data: formData,
               success: function(data) {
                   if (data.status) {

                       setTimeout(() => {
                           $('#religion_modal').modal('hide');
                           location.reload();
                       }, 1000);
                   } else {
                       alert(data.error);

                   }
               },
               error: function(xhr) {
                   let message = 'Something went wrong.';
                   if (xhr.responseJSON && xhr.responseJSON.message) {
                       message = xhr.responseJSON.message;
                   } else if (xhr.responseText) {
                       message = xhr.responseText;
                   }
                   alert(message);

               }
           });
       });

       function set_delete_id($id) {
           $("#delete_id").val($id);
       }

       function delete_click(id) {

           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           var url = "{{ route('religions.destroy', ':id') }}";
           url = url.replace(":id", id);

           $.ajax({
               url: url,
               type: 'DELETE',
               beforeSend: function() {
                   // $('#delete_model').modal('toggle');
                   var main_loading_screen = document.getElementById("main_loading_screen");
                   main_loading_screen.style.display = "block";
               },
               success: function(data) {
                   var main_loading_screen = document.getElementById("main_loading_screen");
                   main_loading_screen.style.display = "none";
                   if (data.error) {
                       window.alert('error==>' + data.error);
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

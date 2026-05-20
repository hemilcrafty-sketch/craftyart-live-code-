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
                                 <a class="btn btn-primary item-form-input item-form-input" href="{{ route('sizes.create') }}">Add New
                                     Size</a>
                             @endif

                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form ', [
                                 'action' => route('sizes.index'),
                             ])
                         </div>
                     </div>

                     <div class="scroll-wrapper table-responsive tableFixHead"
                         style="max-height: calc(108vh - 220px) !important;">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th style="width:50px">Id</th>
                                     <th>Size Name</th>
                                     <th style="width:170px">New Category</th>
                                     <th>User</th>
                                     <th>Width Ration</th>
                                     <th>Height Ration</th>
                                     <th style="width:80px">Paper Size</th>
                                     <th>Units</th>
                                     <th style="width:80px">Status</th>
                                     <th style="width:200px" class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody id="size_table">
                                 @foreach ($sizes as $size)
                                     @php
                                         $newCategoryIds =
                                             isset($size->new_category_id) && $size->new_category_id != null
                                                 ? json_decode($size->new_category_id, true)
                                                 : [];
                                         if (!is_array($newCategoryIds)) {
                                             $newCategoryIds = [$newCategoryIds];
                                         }
                                     @endphp
                                     <tr style="background-color: #efefef;">
                                         <td class="table-plus">{{ $size->id }}</td>
                                         <td class="table-plus">{{ $size->size_name }}</td>
                                         <td class="table-plus">
                                             {{ \App\Http\Controllers\HelperController::getNewCatNames($newCategoryIds, true) }}
                                         </td>
                                         <td class="table-plus">
                                             {{ $roleManager::getUploaderName($size->emp_id) }}
                                         </td>
                                         <td class="table-plus">{{ $size->width_ration }},
                                             {{ $size->width }}</td>
                                         <td class="table-plus">{{ $size->height_ration }},
                                             {{ $size->height }}</td>
                                         <td class="table-plus">{{ $size->paper_size }}</td>
                                         <td class="table-plus">{{ \Str::title($size->unit) }}</td>
                                         <td class="table-plus">
                                             {{ $size->status ? 'Active' : 'UnActive' }}</td>
                                         <td>
                                             <div class="d-flex">

                                                 <a class="dropdown-item"
                                                     href="{{ route('sizes.edit', $size->id) }}"><i
                                                         class="dw dw-edit2"></i>
                                                     Edit</a>
                                                 @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                     <button class="dropdown-item"
                                                         onclick="delete_click('{{ $size->id }}')"><i
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
                 @include('partials.pagination', ['items' => $sizes])
             </div>
         </div>
     </div>
 </div>
 <div class="modal fade" id="send_notification_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="myLargeModalLabel">Send Notification</h5>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>

             <div class="modal-body" id="notification_model">

                 <form method="post" id="notification_form" enctype="multipart/form-data">
                     @csrf
                     <input id="temp_id" class="form-control" type="textname" name="temp_id" style="display: none;" />
                     <div class="form-group">
                         <h7>Title</h7>
                         <div class="input-group custom">
                             <input type="text" class="form-control" placeholder="Title" name="title"
                                 required="" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h7>Description</h7>
                         <div class="input-group custom">
                             <input type="text" class="form-control" placeholder="Description" name="description"
                                 required="" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h7>Large Icon</h7>
                         <div class="input-group custom">
                             <input type="file" class="form-control" name="large_icon" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h7>Big Picture</h7>
                         <div class="input-group custom">
                             <input type="file" class="form-control" name="big_picture" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h7>Schedule</h7>
                         <div class="input-group custom">
                             <input class="form-control datetimepicker" placeholder="Select Date & Time" type="text"
                                 name="schedule" readonly>
                         </div>
                     </div>

                     <div class="row">
                         <div class="col-sm-12">
                             <button type="button" class="btn btn-primary btn-block"
                                 id="send_notification_click">Send</button>
                         </div>
                     </div>
                 </form>

             </div>

         </div>
     </div>
 </div>

 <div class="modal fade" id="reset_date_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title" id="myLargeModalLabel">Reset Date</h4>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you reset the date?</p>
             </div>

             <input class="form-control" type="textname" id="reset_temp_id" name="reset_temp_id"
                 style="display: none">

             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                 <button type="button" id="reset_date_click" class="btn btn-primary">Yes, Reset</button>
             </div>
         </div>
     </div>
 </div>

 <div class="modal fade" id="reset_creation_model" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title" id="myLargeModalLabel">Reset Creation Date</h4>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you reset the creation date?</p>
             </div>

             <input class="form-control" type="textname" id="reset_creation_id" name="reset_creation_id"
                 style="display: none">

             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                 <button type="button" id="reset_creation_click" class="btn btn-primary">Yes, Reset</button>
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
 @include('layouts.masterscript')

 <script>
     function notification_click($id) {
         $("#temp_id").val($id);
     }

     function reset_click($id) {
         $("#reset_temp_id").val($id);
     }

     function reset_creation($id) {
         $("#reset_creation_id").val($id);
     }


     function status_click($id) {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var status = $id;
         var url = "{{ route('temp.status', ': status ') }}";
         url = url.replace(":status", status);
         var formData = new FormData();
         formData.append('id', $id);

         $.ajax({
             url: url,
             type: 'POST',
             data: formData,
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
                     var x = document.getElementById("status_label_" + $id);
                     if (x.innerHTML === "Live") {
                         x.innerHTML = "Not Live";
                     } else {
                         x.innerHTML = "Live";
                     }
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

     $(document).on('click', '#send_notification_click', function() {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var formData = new FormData(document.getElementById("notification_form"));
         var status = formData.get("temp_id");


         var url = "{{ route('poster.notification', ': status ') }}";
         url = url.replace(":status", status);

         $.ajax({
             url: url,
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#send_notification_model').modal('toggle');
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert(data.error);
                 } else {
                     window.alert(data.success);
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
     });

     $(document).on('click', '#reset_date_click', function() {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var status = $("#reset_temp_id").val();
         var url = "{{ route('reset.date', ': status ') }}";
         url = url.replace(":status", status);

         var formData = new FormData();
         formData.append('id', status);


         $.ajax({
             url: url,
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#reset_date_model').modal('toggle');
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert(data.error);
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
     });

     $(document).on('click', '#reset_creation_click', function() {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var status = $("#reset_creation_id").val();


         var url = "{{ route('reset.creation', ': status ') }}";
         url = url.replace(":status", status);

         var formData = new FormData();
         formData.append('id', status);

         $.ajax({
             url: url,
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#reset_creation_model').modal('toggle');
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert(data.error);
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
     });

     $(document).ready(function() {
         $('#application_id').change(function() {

             $.ajaxSetup({
                 headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                 }
             });

             var value = $(this).val();

             $.ajax({
                 url: "{{ route('item.custom_data') }}",
                 method: "POST",
                 data: {
                     value: value,
                     _token: '{{ csrf_token() }}'
                 },
                 success: function(result) {
                     $('#item_table').html(result);
                 },
                 error: function(result) {
                     window.alert(result.responseText);
                 }
             })
         });
     });

     function appSelection() {
         // $('#passingAppId').val('1');
         $('#create_size_action').submit();
     }

     function set_delete_id($id) {
         $("#delete_id").val($id);
     }

     function delete_click(id) {

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var url = "{{ route('sizes.destroy', ': id ') }}";
         url = url.replace(":id", id);

         $.ajax({
             url: url,
             type: 'DELETE',
             beforeSend: function() {
                 $('#delete_model').modal('toggle');
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

     const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
     $(document).on("keypress", "#themeName", function() {
         const titleString = toTitleCase($(this).val());
         $("#themeIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
         $(this).val(titleString);
     });

     $(document).on("keypress", "#editThemeName", function() {
         const titleString = toTitleCase($(this).val());
         $("#editThemeIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
         $(this).val(titleString);
     });
 </script>
 </body>

 </html>

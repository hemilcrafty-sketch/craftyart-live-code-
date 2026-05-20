 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container">


     <div class="">
         <div class="min-height-200px">
             <div class="card-box">
                 <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                     <div class="row justify-content-between">
                         <div class="col-md-3 m-1">
                             <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                                 data-target="#add_font_model" type="button">Add Font </a>

                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form ', [
                                 'action' => route('font_list'),
                             ])
                         </div>
                     </div>

                     <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: calc(110vh - 220px) !important">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th>Id</th>
                                     <th>Font Family</th>
                                     <th>Font Name</th>
                                     <th>Font Type</th>
                                     <th>Support Bold</th>
                                     <th>Support Italic</th>
                                     <th>Font Weight</th>
                                     <th>Status</th>
                                     <th>User</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($datas['fontLists'] as $fontList)
                                     <tr>
                                         <td class="table-plus">{{ $fontList->id }}</td>
                                         <td class="table-plus">
                                             {{ $helperController::getFontFamilyName($fontList->fontFamilyId) }}
                                         </td>
                                         <td class="table-plus">{{ $fontList->fontName }}</td>
                                         <td class="table-plus">{{ $fontList->fontType }}</td>

                                         @if ($fontList->support_bold == '1')
                                             <td>True</td>
                                         @else
                                             <td>False</td>
                                         @endif
                                         @if ($fontList->support_italic == '1')
                                             <td>True</td>
                                         @else
                                             <td>False</td>
                                         @endif

                                         <td class="table-plus">{{ $fontList->fontWeight }}</td>

                                         @if ($fontList->status == '1')
                                             <td>Active</td>
                                         @else
                                             <td>Disabled</td>
                                         @endif
                                         <td>{{ $roleManager::getUploaderName($fontList->emp_id) }}</td>
                                         <td>
                                             <div class="d-flex">

                                                 <Button class="dropdown-item"
                                                     onclick="edit_click('{{ $fontList->id }}')"><i
                                                         class="dw dw-edit2"></i>
                                                     Edit</Button>
                                                 @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                     <Button class="dropdown-item"
                                                         onclick="set_delete_id('{{ $fontList->id }}')"
                                                         data-backdrop="static" data-toggle="modal"
                                                         data-target="#delete_model" readonly><i
                                                             class="dw dw-delete-3"></i>
                                                         Delete</Button>
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

                 @include('partials.pagination', ['items' => $datas['fontLists']])

             </div>
         </div>
     </div>
 </div>
 </div>

 <div class="modal fade designer-access-container" id="add_font_model" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="myLargeModalLabel">Add Font</h5>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>

             <div class="modal-body">
                 <form method="post" id="add_font_family_form" enctype="multipart/form-data">
                     @csrf

                     <div class="form-group">
                         <h6>Font Name</h6>
                         <div class="input-group custom">
                             <input type="text" class="form-control" name="fontName" required="" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Font Family</h6>
                         <div class="input-group custom">
                             <input class="form-control" list="font_families" type="text" name="fontFamilyId"
                                 autocomplete="on"
                                 style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                 required>
                         </div>
                     </div>

                     <!-- <div class="form-group">
                            <h6>Font Type</h6>
                            <div class="col-sm-20">
                                <select class="custom-select form-control" name="fontType" style="width: 100%;" required>
                                    <option value="" selected disabled>Select Font Type</option>
                                    <option value="normal">Normal</option>
                                    <option value="bold">Bold</option>
                                    <option value="italic">Italic</option>
                                    <option value="bold italic">Bold Italic</option>
                                </select>
                            </div>
                        </div> -->

                     <div class="form-group">
                         <h6>Support Italic</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 name="support_italic">
                                 <option value="1">True</option>
                                 <option value="0" selected>False</option>
                             </select>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Support Bold</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 name="support_bold">
                                 <option value="1">True</option>
                                 <option value="0" selected>False</option>
                             </select>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Font File</h6>
                         <div class="input-group custom">
                             <input type="file" class="form-control-file form-control" name="fontFile">
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Status</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 name="status">
                                 <option value="1">Active</option>
                                 <option value="0">Disable</option>
                             </select>
                         </div>
                     </div>

                     <div class="row">
                         <div class="col-sm-12">
                             <input class="btn btn-primary btn-block" type="submit" name="submit">
                         </div>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>

 <div class="modal fade" id="edit_font_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="myLargeModalLabel">Edit Font</h5>
                 <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
             </div>

             <div class="modal-body">
                 <form method="post" id="edit_font_family_form" enctype="multipart/form-data">

                     @csrf
                     <input class="form-control" type="textname" id="font_family_id" name="id"
                         style="display: none" />

                     <div class="form-group">
                         <h6>Font Name</h6>
                         <div class="input-group custom">
                             <input type="text" class="form-control" id="fontName" name="fontName"
                                 required="" />
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Font Family</h6>
                         <div class="input-group custom">
                             <input class="form-control" list="font_families" type="text" id="fontFamilyId"
                                 name="fontFamilyId" autocomplete="on"
                                 style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                 required>
                         </div>
                     </div>

                     <!-- <div class="form-group">
                            <h6>Font Type</h6>
                            <div class="col-sm-20">
                                <select class="custom-select form-control" data-style="btn-outline-primary" id="fontType" name="fontType" style="width: 100%;" required>
                                    <option value="" selected disabled>Select Font Type</option>
                                    <option value="normal">Normal</option>
                                    <option value="bold">Bold</option>
                                    <option value="italic">Italic</option>
                                    <option value="bold italic">Bold Italic</option>
                                </select>
                            </div>
                        </div> -->

                     <div class="form-group">
                         <h6>Support Italic</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 id="support_italic" name="support_italic">
                                 <option value="1">True</option>
                                 <option value="0">False</option>
                             </select>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Support Bold</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 id="support_bold" name="support_bold">
                                 <option value="1">True</option>
                                 <option value="0">False</option>
                             </select>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Font File</h6>
                         <div class="col-sm-20">
                             <input type="file" class="form-control-file form-control" name="fontFile">
                         </div>
                         <textarea id="font_path_label" style="width: 100%; border: none;" disabled></textarea>
                         <div class="input-group custom" style="display: none">
                             <input class="form-control" type="textname" id="font_path" name="font_path">
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Status</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker form-control" data-style="btn-outline-primary"
                                 id="status" name="status">
                                 <option value="1">Active</option>
                                 <option value="0">Disable</option>
                             </select>
                         </div>
                     </div>

                     <div class="row">
                         <div class="col-sm-12">
                             <button class="btn btn-primary btn-block" id="update_click">Update</button>
                         </div>
                     </div>
                 </form>
             </div>

         </div>
     </div>
 </div>

 <div class="modal fade" id="delete_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <input type="text" id="delete_id" name="id" style="display: none;">
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

 <datalist id="font_families">
     @foreach ($datas['fontFamilies'] as $fontFamily)
         <option value="{{ $fontFamily->fontFamily }}"></option>
     @endforeach
 </datalist>
 @include('layouts.masterscript')
 <script>
     function edit_click(id) {

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('get_fontlist') }}",
             type: 'POST',
             data: {
                 id: id
             },
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 console.log(data);
                 if (data.error) {
                     window.alert(data.error);
                 } else {
                     $('#font_family_id').val(data.success.id);
                     $('#fontName').val(data.success.fontName);
                     $('#fontFamilyId').val(data.success.fontFamilyId);
                     $('#support_bold').val(data.success.support_bold);
                     $('#support_italic').val(data.success.support_italic);
                     $('#font_path_label').text(data.success.fontUrl);
                     $('#font_path').val(data.success.fontUrl);
                     $('#status').val(data.success.status);
                     $('#edit_font_model').modal('toggle');
                 }
             },
             error: function(error) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 window.alert(error.responseText);
             }
         })
     }

     $('#add_font_family_form').on('submit', function(event) {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var formData = new FormData(this);

         $.ajax({
             url: "{{ route('font_list.create') }}",
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#add_font_model').modal('toggle');
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
     });

     $(document).on('click', '#update_click', function() {
         event.preventDefault();

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var formData = new FormData(document.getElementById("edit_font_family_form"));

         $.ajax({
             url: "{{ route('font_list.update') }}",
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#edit_font_model').modal('toggle');
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
     });

     function set_delete_id($id) {
         $("#delete_id").val($id);
     }

     function delete_click() {

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var deleteId = $("#delete_id").val();
         var formData = new FormData();
         formData.append("id", deleteId);

         $.ajax({
             url: "{{ route('font_list.delete') }}",
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 $('#delete_model').modal('toggle');
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

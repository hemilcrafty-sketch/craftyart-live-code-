   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container seo-access-container">


       <div class="pd-ltr-10 xs-pd-20-10">
           <div class="min-height-200px">
               <div class="card-box">
                   <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                       <div class="row justify-content-between">
                           <div class="col-md-4 m-1">
                               @if ($roleManager::onlySeoAccess(Auth::user()->user_type) && !$roleManager::isSeoManager(Auth::user()->user_type))
                                   <a href="{{ route('create_keyword') }}" class="btn btn-primary item-form-input" data-backdrop="static"
                                       type="button">
                                       Add keyword</a>
                               @endif
                               @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                   <a href="{{ route('page_slug_history') }}" class="btn btn-primary item-form-input"
                                       data-backdrop="static" type="button">
                                       Page Slug History </a>
                               @endif
                           </div>

                           <div class="col-md-7">
                               @include('partials.filter_form ', [
                                   'action' => route('show_keyword'),
                                   'filterExtraNoIndex' => true,
                               ])
                           </div>
                       </div>

                       <div class="scroll-wrapper table-responsive tableFixHead">
                           <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">
                               <thead>
                                   <tr>
                                       <th style="width: 40px;">Id</th>
                                       <th>Keyword</th>
                                       <th>Title</th>
                                       <th>Meta Title</th>
                                       <th>Meta Desc</th>
                                       <th>Short Desc</th>
                                       {{-- <th>Long Desc</th> --}}
                                       <th style="width: 50px;">No Index</th>
                                       <th style="width: 50px;">Status</th>
                                       <th class="">Action</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach ($keywordArray as $keyword)
                                       <tr>
                                           <td class="table-plus">{{ $keyword->id }}</td>
                                           <td class="table-plus">{{ $keyword->name }}</td>
                                           <td class="table-plus">{{ $keyword->title }}</td>
                                           <td class="table-plus">{{ $keyword->meta_title }}</td>
                                           <td class="table-plus">{{ $keyword->meta_desc }}</td>
                                           <td class="table-plus">{{ $keyword->short_desc }}</td>
                                           {{-- <td class="table-plus">{{$keyword->long_desc}}</td> --}}

                                           @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                               @if ($keyword->no_index == '1')
                                                   <td><label id="noindex_label_{{ $keyword->id }}"
                                                           style="display: none;">TRUE</label>
                                                       <Button style="border: none" class="no-index"
                                                           onclick="noindex_click(this, '{{ $keyword->id }}')"><input
                                                               type="checkbox" checked class="switch-btn no-index"
                                                               data-size="small" data-color="#0059b2" />
                                                       </Button>
                                                   </td>
                                               @else
                                                   <td><label id="noindex_label_{{ $keyword->id }}"
                                                           style="display: none;">FALSE</label>
                                                       <Button style="border: none" class="no-index"
                                                           onclick="noindex_click(this, '{{ $keyword->id }}')"><input
                                                               type="checkbox" class="switch-btn no-index"
                                                               data-size="small" data-color="#0059b2" />
                                                       </Button>
                                                   </td>
                                               @endif
                                           @else
                                               @if ($keyword->no_index == '1')
                                                   <td>True</td>
                                               @else
                                                   <td>False</td>
                                               @endif
                                           @endif


                                           @if ($keyword->status == '1')
                                               <td>Active</td>
                                           @else
                                               <td>Disabled</td>
                                           @endif
                                           <td class="table-plus">
                                               <div class="d-flex">

                                                   <a class="dropdown-item"
                                                       href="{{ route('edit_keyword', $keyword->id) }}"><i
                                                           class="dw dw-edit2"></i> Edit</a>
                                                   @if ($roleManager::isAdmin(Auth::user()->user_type))
                                                       <Button class="dropdown-item"
                                                           onclick="delete_click('{{ $keyword->id }}')"><i
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
                   @include('partials.pagination', ['items' => $keywordArray])
               </div>
           </div>
       </div>
   </div>

   <div class="modal fade" id="add_keyword_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
       aria-hidden="false">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="myLargeModalLabel">Add Keyword</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>

               <div class="modal-body">
                   <form method="post" id="add_keyword_form" enctype="multipart/form-data">
                       @csrf
                       <div class="form-group">
                           <h6>Keyword</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" name="name" required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Title</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" name="title" required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>H2 Tag</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" name="h2_tag" required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Meta Title</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" name="meta_title" required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Meta Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 80px" class="form-control" name="meta_desc"></textarea>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Short Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 80px" class="form-control" name="short_desc"></textarea>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Long Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 120px" class="form-control" name="long_desc"></textarea>
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

   <div class="modal fade" id="edit_keyword_model" tabindex="-1" role="dialog"
       aria-labelledby="myLargeModalLabel" aria-hidden="false">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="myLargeModalLabel">Edit Keyword</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>

               <div class="modal-body">
                   <form method="post" id="edit_keyword_form" enctype="multipart/form-data">
                       @csrf
                       <input class="form-control" type="textname" id="keyword_id" name="keyword_id"
                           style="display: none" />
                       <div class="form-group">
                           <h6>Keyword</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" id="keyword" name="name"
                                   required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Title</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" id="title" name="title"
                                   required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>H2 Tag</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" id="h2_tag" name="h2_tag"
                                   required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Meta Title</h6>
                           <div class="input-group custom">
                               <input type="text" class="form-control" id="meta_title" name="meta_title"
                                   required="" />
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Meta Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 80px" class="form-control" id="meta_desc" name="meta_desc"></textarea>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Short Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 80px" class="form-control" id="short_desc" name="short_desc"></textarea>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Long Desc</h6>
                           <div class="input-group custom">
                               <textarea style="height: 120px" class="form-control" id="long_desc" name="long_desc"></textarea>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Status</h6>
                           <div class="col-sm-20">
                               <select id="status" class="selectpicker form-control"
                                   data-style="btn-outline-primary" name="status">
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

   @include('layouts.masterscript')
   <script>
       function edit_click(id) {

           // $.ajaxSetup({
           //     headers: {
           //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
           //     }
           // });

           // $.ajax({
           //     url: "get_keyword",
           //     type: 'POST',
           //     data: {
           //         id: id
           //     },
           //     beforeSend: function () {
           //         var main_loading_screen = document.getElementById("main_loading_screen");
           //         main_loading_screen.style.display = "block";
           //     },
           //     success: function (data) {
           //         var main_loading_screen = document.getElementById("main_loading_screen");
           //         main_loading_screen.style.display = "none";

           //         // if (data.error) {
           //         //     window.alert(data.error);
           //         // } else {
           //         //     $('#keyword_id').val(data.success.id);
           //         //     $('#keyword').val(data.success.name);
           //         //     $('#title').val(data.success.title);
           //         //     $('#h2_tag').val(data.success.h2_tag);
           //         //     $('#meta_title').val(data.success.meta_title);
           //         //     $('#meta_desc').val(data.success.meta_desc);
           //         //     $('#short_desc').val(data.success.short_desc);
           //         //     $('#long_desc').val(data.success.long_desc);
           //         //     $('#status').val(data.success.status);
           //         //     $('#edit_keyword_model').modal('toggle');
           //         // }
           //     },
           //     error: function (error) {
           //         var main_loading_screen = document.getElementById("main_loading_screen");
           //         main_loading_screen.style.display = "none";
           //         window.alert(error.responseText);
           //     }
           // })
       }



       $(document).on('click', '#update_click', function() {
           event.preventDefault();

           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           var formData = new FormData(document.getElementById("edit_keyword_form"));
           var status = formData.get("keyword_id");


           var url = "{{ route('keyword.update', ':status') }}";
           url = url.replace(":status", status);

           $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               beforeSend: function() {
                   $('#edit_keyword_model').modal('toggle');
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

       function delete_click(id) {

           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           var url = "{{ route('keyword.delete', ':id') }}";
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

       function noindex_click(parentElement, id) {
           //    alert('hello');

           let element = parentElement.firstElementChild;
           const originalChecked = element.checked;

           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           const url = "{{ route('check_n_i') }}";
           const formData = new FormData();
           formData.append('id', id);
           formData.append('type', 'k_page');

           $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               beforeSend: function() {
                   $("#main_loading_screen").show();
               },
               success: function(data) {
                   $("#main_loading_screen").hide();

                   if (data.error) {
                       alert(data.error);
                       // Revert checkbox if error
                       element.checked = !originalChecked;
                       element.dispatchEvent(new Event('change', {
                           bubbles: true
                       }));
                   } else {
                       const label = document.getElementById("noindex_label_" + id);
                       label.innerHTML = (label.innerHTML === "TRUE") ? "FALSE" : "TRUE";
                   }
               },
               error: function(error) {
                   $("#main_loading_screen").hide();
                   alert(error.responseText);
               },
               cache: false,
               contentType: false,
               processData: false
           });
       }
   </script>

   </body>

   </html>

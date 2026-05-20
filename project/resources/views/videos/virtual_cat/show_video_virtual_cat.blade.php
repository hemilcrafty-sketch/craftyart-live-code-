 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container">

     <div class="pd-ltr-20 xs-pd-20-10">
         <div class="min-height-200px">
             <div class="card-box">
                 <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                     <div class="row justify-content-between">
                         <div class="col-md-3 m-1">
                             @if ($roleManager::onlySeoAccess(Auth::user()->user_type) && !$roleManager::isSeoManager(Auth::user()->user_type))
                                 <a class="btn btn-primary item-form-input" href="create_video_virtual_cat" role="button"> Add
                                     New
                                     Video Category </a>
                             @endif
                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form ', [
                                 'action' => route('show_video_virtual_cat'),
                                 'filterExtraNoIndex' => true,
                             ])
                         </div>
                     </div>

                     <div class="scroll-wrapper table-responsive tableFixHead">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th>Category Id</th>
                                     <th>User</th>
                                     <th>Assign To</th>
                                     <th>Category Name</th>
                                     <th>Parent Category</th>
                                     <th class="datatable-nosort">Category Thumb</th>
                                     <th class="datatable-nosort">Mockup</th>
                                     <th>No Index</th>
                                     <th>Sequence Number</th>
                                     @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                         <th>IMP</th>
                                     @endif
                                     <th>Status</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($catArray as $cat)
                                     <tr>
                                         <td class="table-plus">{{ $cat->id }}</td>

                                         <td>{{ $roleManager::getUploaderName($cat->emp_id) }}</td>
                                         <td>{{ $cat->assignedSeo->name ?? 'N/A' }}</td>

                                         <td>{{ $cat->category_name }}</td>
                                         <td style="position: relative;">
                                             <label>{{ $helperController::getParentVideoCatName($cat->parent_category_id, true) }}</label>
                                         </td>

                                         <td><img src="{{ config('filesystems.storage_url') }}{{ $cat->category_thumb }}"
                                                 style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                         </td>
                                         <td><img src="{{ config('filesystems.storage_url') }}{{ $cat->mockup }}"
                                                 style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                         </td>

                                         @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                             @if ($cat->no_index == '1')
                                                 <td><label id="noindex_label_{{ $cat->id }}"
                                                         style="display: none;">TRUE</label><Button style="border: none"
                                                         onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                             type="checkbox" checked class="switch-btn"
                                                             data-size="small" data-color="#0059b2" /></Button></td>
                                             @else
                                                 <td><label id="noindex_label_{{ $cat->id }}"
                                                         style="display: none;">FALSE</label><Button
                                                         style="border: none"
                                                         onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                             type="checkbox" class="switch-btn" data-size="small"
                                                             data-color="#0059b2" /></Button></td>
                                             @endif
                                         @else
                                             @if ($cat->no_index == '1')
                                                 <td>True</td>
                                             @else
                                                 <td>False</td>
                                             @endif
                                         @endif

                                         <td>{{ $cat->sequence_number }}</td>

                                         @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                             @if ($cat->parent_category_id == 0)
                                                 @if ($cat->imp == '1')
                                                     <td><label id="imp_label_{{ $cat->id }}"
                                                             style="display: none;">TRUE</label>
                                                         <Button style="border: none"
                                                             onclick="imp_click('{{ $cat->id }}')"><input
                                                                 type="checkbox" checked class="switch-btn"
                                                                 data-size="small" data-color="#0059b2" /></Button>
                                                     </td>
                                                 @else
                                                     <td><label id="imp_label_{{ $cat->id }}"
                                                             style="display: none;">FALSE</label>
                                                         <Button style="border: none"
                                                             onclick="imp_click('{{ $cat->id }}')"><input
                                                                 type="checkbox" class="switch-btn" data-size="small"
                                                                 data-color="#0059b2" />
                                                         </Button>
                                                     </td>
                                                 @endif
                                             @else
                                                 <td> N/A</td>
                                             @endif
                                         @endif

                                         @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                            @if ($cat->status == '1')
                                                <td><label id="status_label_{{ $cat->id }}"
                                                        style="display: none;">LIVE</label><Button style="border: none"
                                                        onclick="status_click(this, '{{ $cat->id }}')"><input
                                                            type="checkbox" checked class="switch-btn"
                                                            data-size="small" data-color="#0059b2" /></Button></td>
                                            @else
                                                <td><label id="status_label_{{ $cat->id }}"
                                                        style="display: none;">NOT LIVE</label><Button
                                                        style="border: none"
                                                        onclick="status_click(this, '{{ $cat->id }}')"><input
                                                            type="checkbox" class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @endif
                                        @else
                                            @if ($cat->status == '1')
                                                <td>LIVE</td>
                                            @else
                                                <td>NOT LIVE</td>
                                            @endif
                                        @endif

                                         <td>
                                             <div class="dropdown">
                                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                     href="#" role="button" data-toggle="dropdown">
                                                     <i class="dw dw-more"></i>
                                                 </a>
                                                 <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                     <a class="dropdown-item"
                                                         href="edit_video_virtual_cat/{{ $cat->id }}"><i
                                                             class="dw dw-edit2"></i>
                                                         Edit</a>
                                                     @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                         <a class="dropdown-item" href="#"
                                                             onclick="set_delete_virtual_cat_id('{{ $cat->id }}')"
                                                             data-backdrop="static" data-toggle="modal"
                                                             data-target="#delete_virtual_cat_model">
                                                             <i class="dw dw-delete-3"></i> Delete
                                                         </a>
                                                     @endif
                                                 </div>
                                             </div>
                                         </td>

                                     </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
                 <hr class="my-1">
                 @include('partials.pagination', ['items' => $catArray])
             </div>
         </div>
     </div>
 </div>
 </div>

 <!-- Delete Confirmation Modal -->
 <div class="modal fade" id="delete_virtual_cat_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <input type="hidden" id="delete_virtual_cat_id" name="delete_virtual_cat_id">
             <div class="modal-header">
                 <h4 class="modal-title" id="myLargeModalLabel">Delete Video Virtual Category</h4>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you want to delete this video virtual category? This action cannot be undone.</p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                 <button type="button" class="btn btn-danger" onclick="delete_virtual_cat_click()">Delete</button>
             </div>
         </div>
     </div>
 </div>

 @include('layouts.masterscript')
 <script>
     function set_delete_virtual_cat_id($id) {
         $("#delete_virtual_cat_id").val($id);
     }

     function delete_virtual_cat_click() {
         var id = $("#delete_virtual_cat_id").val();
         if (id) {
             window.location.href = "delete_video_virtual_cat/" + id;
         }
     }

     function noindex_click(parentElement, $id) {
         let element = parentElement.firstElementChild;
         const originalChecked = element.checked;
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var status = $id;
         var url = "{{ route('check_n_i') }}";
         var formData = new FormData();
         formData.append('id', $id);
         formData.append('type', 'video_virtual_cat');
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
                     alert(data.error);
                     element.checked = !originalChecked;
                     element.dispatchEvent(new Event('change', {
                         bubbles: true
                     }));
                 } else {
                     var x = document.getElementById("noindex_label_" + $id);
                     if (x.innerHTML === "TRUE") {
                         x.innerHTML = "FALSE";
                     } else {
                         x.innerHTML = "TRUE";
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

     function imp_click($id) {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var status = $id;
         var url =
             "{{ route('cat.imp', ':status') }}";
         url = url.replace(":status", status);
         var formData = new FormData();
         formData.append('id', $id);
         formData.append('isVideoVirtual', '1');
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
                     var x = document.getElementById("imp_label_" + $id);
                     if (x.innerHTML === "TRUE") {
                         x.innerHTML = "FALSE";
                     } else {
                         x.innerHTML = "TRUE";
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

     function status_click(parentElement, $id) {
        let element = parentElement.firstElementChild;
        const originalChecked = element.checked;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData();
        formData.append('id', $id);
        formData.append('type', 'video_virtual_cat');
        var url = "{{ route('check_status') }}";
        
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
                    alert(data.error);
                    element.checked = !originalChecked;
                    element.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                } else {
                    var x = document.getElementById("status_label_" + $id);
                    if (x.innerHTML === "LIVE") {
                        x.innerHTML = "NOT LIVE";
                    } else {
                        x.innerHTML = "LIVE";
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
 </script>
 <script>
     const sortTable = (event, column, sortType) => {
         event.preventDefault();
         let url = new URL(window.location.href);
         url.searchParams.set('sort_by', column);
         url.searchParams.set('sort_order', sortType);
         window.location.href = url.toString();
     }
 </script>
 </body>

 </html>
 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

 <style>
     .video-items-container {
         background: #f8f9fa;
         min-height: 100vh;
         padding: 20px;
     }

     .items-card {
         background: white;
         border-radius: 8px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
         overflow: hidden;
     }

     .items-header {
         padding: 20px;
         border-bottom: 1px solid #e9ecef;
         background: white;
     }

     .items-table-wrapper {
         overflow-x: auto;
     }

     .items-table {
         width: 100%;
         margin: 0;
         border-collapse: separate;
         border-spacing: 0;
     }

     .items-table thead th {
         background: #f8f9fa;
         color: #495057;
         font-weight: 600;
         font-size: 13px;
         text-transform: uppercase;
         padding: 15px 12px;
         border-bottom: 2px solid #dee2e6;
         white-space: nowrap;
         position: sticky;
         top: 0;
         z-index: 10;
     }

     .items-table tbody td {
         padding: 12px;
         border-bottom: 1px solid #e9ecef;
         vertical-align: middle;
         font-size: 14px;
         color: #495057;
     }

     .items-table tbody tr:hover {
         background-color: #f8f9fa;
     }

     .video-thumb-cell {
         width: 120px;
     }

     .video-thumb-img {
         width: 100px;
         height: 60px;
         object-fit: cover;
         border-radius: 4px;
         border: 1px solid #dee2e6;
     }

     .status-badge {
         display: inline-block;
         padding: 4px 12px;
         border-radius: 12px;
         font-size: 12px;
         font-weight: 500;
     }

     .status-live {
         background: #d4edda;
         color: #155724;
     }

     .status-not-live {
         background: #f8d7da;
         color: #721c24;
     }

     .premium-badge {
         background: #fff3cd;
         color: #856404;
     }

     .free-badge {
         background: #d1ecf1;
         color: #0c5460;
     }

     .action-dropdown .dropdown-toggle {
         background: transparent;
         border: none;
         padding: 5px 10px;
         color: #6c757d;
         cursor: pointer;
     }

     .action-dropdown .dropdown-toggle::after {
         display: none !important;
     }

     .action-dropdown .dropdown-toggle:hover {
         color: #495057;
         background: #f8f9fa;
         border-radius: 4px;
     }

     /* Single horizontal toolbar: one line; scroll horizontally if viewport is narrow */
     .filter-section--single-line {
         display: flex;
         flex-wrap: nowrap;
         align-items: center;
         gap: 8px;
         width: 100%;
         overflow-x: auto;
         overflow-y: hidden;
         -webkit-overflow-scrolling: touch;
         padding-bottom: 4px;
     }

     .filter-section--single-line select.form-control {
         flex-shrink: 0;
     }

     .filter-section--single-line > .btn,
     .filter-section--single-line > a.btn {
         flex-shrink: 0;
     }

     .filter-section--single-line .filter-input {
         flex: 1 1 200px;
         min-width: 200px;
         max-width: 420px;
     }

     .filter-input {
         min-width: 200px;
     }

     .btn-add-new {
         background: #007bff;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 6px;
         font-weight: 500;
         transition: all 0.3s;
     }

     .btn-add-new:hover {
         background: #0056b3;
         transform: translateY(-1px);
         box-shadow: 0 4px 8px rgba(0,123,255,0.3);
     }

     .pagination-wrapper {
         padding: 20px;
         background: white;
         border-top: 1px solid #e9ecef;
     }

     .id-cell {
         font-family: 'Courier New', monospace;
         font-size: 12px;
         color: #6c757d;
     }

     .string-id {
         display: block;
         color: #007bff;
         font-size: 11px;
         margin-top: 2px;
     }
 </style>

 <div class="main-container">
     <div class="video-items-container ">
         <div class="items-card">
             <div class="items-header">
                 <div class="row align-items-center mb-3">
                     <div class="col-md-6">
                         <h4 class="mb-0" style="font-weight: 600; color: #212529;">Video Templates</h4>
                         <p class="text-muted mb-0" style="font-size: 14px;">Manage your video template items</p>
                     </div>
                     <div class="col-md-6 text-right">
                        @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                         <button class="btn btn-add-new" onclick="appSelection()">
                             <i class="fa fa-plus mr-2"></i>Add New Item
                         </button>
                        @endif
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-12">
                         <form method="GET" action="{{ route('show_v_item') }}" id="filter-form">
                             <div class="filter-section--single-line">
                                 @if ($roleManager::isSeoExecutive(Auth::user()->user_type))
                                 <select name="seo_employee" class="form-control" style="width: 168px;" onchange="this.form.submit()">
                                     <option value="" selected disabled>Assigned Filter</option>
                                     <option value="all" {{ request('seo_employee') == 'all' ? 'selected' : '' }}>All Templates</option>
                                     <option value="assigned" {{ request('seo_employee') == 'assigned' ? 'selected' : '' }}>Assigned Templates</option>
                                     <option value="unassigned" {{ request('seo_employee') == 'unassigned' ? 'selected' : '' }}>Not Assigned Templates</option>
                                 </select>
                                 @endif
                                 @if ($roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                                 <select name="seo_category_assigne" class="form-control" style="width: 188px;" onchange="this.form.submit()">
                                     <option value="" selected disabled>Assigned Category Filter</option>
                                     <option value="all" {{ request('seo_category_assigne') == 'all' ? 'selected' : '' }}>All Templates</option>
                                     <option value="assigned" {{ request('seo_category_assigne') == 'assigned' ? 'selected' : '' }}>Assigned Category</option>
                                     <option value="unassigned" {{ request('seo_category_assigne') == 'unassigned' ? 'selected' : '' }}>Not Assigned Category</option>
                                 </select>
                                 @endif
                                 <select name="premium_type" class="form-control" style="width: 138px;" onchange="this.form.submit()">
                                     <option value="" selected disabled>Access Type</option>
                                     <option value="all" {{ request('premium_type') == 'all' ? 'selected' : '' }}>All Templates</option>
                                     <option value="free" {{ request('premium_type') == 'free' ? 'selected' : '' }}>Free</option>
                                     <option value="freemium" {{ request('premium_type') == 'freemium' ? 'selected' : '' }}>Freemium</option>
                                     <option value="premium" {{ request('premium_type') == 'premium' ? 'selected' : '' }}>Premium</option>
                                 </select>
                                 <select name="template_status" class="form-control" style="width: 138px;" onchange="this.form.submit()">
                                     <option value="" selected disabled>Live Status</option>
                                     <option value="all" {{ request('template_status') == 'all' ? 'selected' : '' }}>All Templates</option>
                                     <option value="live" {{ request('template_status') == 'live' ? 'selected' : '' }}>Live Templates</option>
                                     <option value="not-live" {{ request('template_status') == 'not-live' ? 'selected' : '' }}>Not Live Templates</option>
                                 </select>
                                 @isset($noIndexStats)
                                     @php
                                         $vNoIndexTotal = ($noIndexStats['enabled'] ?? 0) + ($noIndexStats['disabled'] ?? 0);
                                         $vNoIndexSel = request('no_index_filter');
                                         $vNoIndexSel = in_array((string) $vNoIndexSel, ['0', '1'], true) ? (string) $vNoIndexSel : '';
                                     @endphp
                                     <select name="no_index_filter" class="form-control" style="width: 198px;" onchange="this.form.submit()"
                                         title="Index / No index">
                                         <option value="" {{ $vNoIndexSel === '' ? 'selected' : '' }}>All ({{ $vNoIndexTotal }})</option>
                                         <option value="0" {{ $vNoIndexSel === '0' ? 'selected' : '' }}>Index ({{ $noIndexStats['disabled'] ?? 0 }})</option>
                                         <option value="1" {{ $vNoIndexSel === '1' ? 'selected' : '' }}>No Index ({{ $noIndexStats['enabled'] ?? 0 }})</option>
                                     </select>
                                 @endisset
                                 <input type="text" name="query" class="form-control filter-input"
                                     placeholder="Search by ID, string ID, name, or category..." value="{{ request('query') }}">
                                 <select name="per_page" class="form-control" style="width: 118px;">
                                     <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per page</option>
                                     <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 per page</option>
                                     <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                                     <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                                 </select>
                                 <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                     <i class="fa fa-search mr-1"></i>Search
                                 </button>
                                 @if(request()->hasAny(['query','per_page','seo_employee','seo_category_assigne','premium_type','template_status','no_index_filter']))
                                     <a href="{{ route('show_v_item') }}" class="btn btn-secondary" style="white-space: nowrap;">
                                         <i class="fa fa-times mr-1"></i>Clear
                                     </a>
                                 @endif
                             </div>
                         </form>
                     </div>
                 </div>
             </div>

             <form id="create_item_action" action="create_v_item" method="GET"
                 style="display: none;">
                 <input type="text" id="passingAppId" name="passingAppId">
             </form>

             <div class="items-table-wrapper">
                 <table class="items-table">
                     <thead>
                         <tr>
                             <th style="width: 100px;">ID</th>
                             <th style="width: 120px;">User</th>
                             @if (!$roleManager::isSeoIntern(Auth::user()->user_type))
                             <th style="width: 150px;">Assign to</th>
                             @endif
                             <th style="width: 200px;">Category Name</th>
                             <th style="width: 250px;">Video Name</th>
                             <th class="video-thumb-cell">Thumbnail</th>
                             @if ($roleManager::isAdmin(Auth::user()->user_type))
                                 <th style="width: 80px;">Views</th>
                                 <th style="width: 100px;">Purchases</th>
                             @endif
                             <th style="width: 100px;">Premium</th>
                             <th style="width: 100px;">No Index</th>
                             <th style="width: 100px;">Status</th>
                             <th style="width: 80px; text-align: center;">Actions</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse ($itemArray['item'] as $item)
                             <tr>
                                 <td class="id-cell">
                                     #{{ $item->id }}
                                     <span class="string-id">{{ $item->string_id }}</span>
                                 </td>
                                 <td>{{ $roleManager::getEmployeeName($item->emp_id) }}</td>
                                 @if (!$roleManager::isSeoIntern(Auth::user()->user_type))
                                 <td>
                                     @php
                                         // First try category's SEO employee, then item's SEO employee
                                         $seoId = $categorySeoEmpIds[$item->category_id] ?? null;
                                         if (!$seoId || $seoId == 0) {
                                             $seoId = $item->seo_emp_id ?? null;
                                             if ($seoId == 0) {
                                                 $seoId = null;
                                             }
                                         }
                                     @endphp

                                     @if ($roleManager::isSeoExecutive(Auth::user()->user_type) || $roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                                     <select class="form-control form-control-sm assignSeoEmployee" data-category-id="{{ $item->id }}">
                                         <option value="">Select User</option>
                                         @foreach ($seoUsers ?? [] as $seoUser)
                                             <option value="{{ $seoUser->id }}" {{ $item->seo_emp_id == $seoUser->id ? 'selected' : '' }}>{{ $seoUser->name }}</option>
                                         @endforeach
                                     </select>
                                     @else
                                         {{ $seoId ? $roleManager::getUploaderName($seoId) : '-' }}
                                     @endif
                                 </td>
                                 @endif
                                 <td style="position: relative;">
                                     @if ($roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                                     <select class="form-control form-control-sm video-category-select" data-id="{{ $item->id }}">
                                         <option value="">Select Category</option>
                                         @foreach ($groupedVideoCategories as $group)
                                         <optgroup label="{{ $group['parent']->category_name }}">
                                             @foreach ($group['children'] as $child)
                                             <option value="{{ $child->id }}"
                                                     @if ($item->category_id == $child->id) selected @endif>
                                                 {{ $child->category_name }}
                                             </option>
                                             @endforeach
                                         </optgroup>
                                         @endforeach
                                     </select>
                                     <label class="parent-label mt-1 d-block text-secondary" style="font-size: 11px;">
                                         ({{ $helperController::getParentVideoCatName($item->category_id) }})
                                     </label>
                                     @else
                                     @if (!empty($item->category_id) && $item->category_id != 0)
                                         <label>({{ $helperController::getVCatName($item->category_id) }} - {{ $helperController::getParentVideoCatName($item->category_id) }})</label>
                                     @else
                                         <label class="text-muted">(No Category Assigned)</label>
                                     @endif
                                     @endif
                                 </td>
                                 <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                     title="{{ $item->video_name }}">
                                     {{ $item->video_name }}
                                 </td>
                                 <td class="video-thumb-cell">
                                     <img src="{{ config('filesystems.storage_url') }}{{ $item->video_thumb }}"
                                         class="video-thumb-img" alt="Thumbnail" />
                                 </td>
                                 @if ($roleManager::isAdmin(Auth::user()->user_type))
                                     <td>{{ number_format($item->views) }}</td>
                                     <td>{{ number_format($helperController::getVPurchaseTemplateCount($item->string_id)) }}</td>
                                 @endif
                                 <td>
                                     @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                         @if ($item->is_premium == '1')
                                             <label id="premium_label_{{ $item->id }}"
                                                     style="display: none;">PREMIUM</label><Button style="border: none"
                                                     onclick="premium_click(this, '{{ $item->id }}')"><input
                                                         type="checkbox" checked class="switch-btn"
                                                         data-size="small" data-color="#0059b2" /></Button>
                                         @else
                                             <label id="premium_label_{{ $item->id }}"
                                                     style="display: none;">FREE</label><Button
                                                     style="border: none"
                                                     onclick="premium_click(this, '{{ $item->id }}')"><input
                                                         type="checkbox" class="switch-btn" data-size="small"
                                                         data-color="#0059b2" /></Button>
                                         @endif
                                     @else
                                         @if ($item->is_premium == '1')
                                             <span class="status-badge premium-badge">Premium</span>
                                         @else
                                             <span class="status-badge free-badge">Free</span>
                                         @endif
                                     @endif
                                 </td>
                                 @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                     @if ($item->no_index == '1')
                                         <td><label id="noindex_label_{{ $item->id }}"
                                                 style="display: none;">TRUE</label><Button style="border: none"
                                                 onclick="noindex_click(this, '{{ $item->id }}')"><input
                                                     type="checkbox" checked class="switch-btn" data-size="small"
                                                     data-color="#0059b2" /></Button></td>
                                     @else
                                         <td><label id="noindex_label_{{ $item->id }}"
                                                 style="display: none;">FALSE</label><Button style="border: none"
                                                 onclick="noindex_click(this, '{{ $item->id }}')"><input
                                                     type="checkbox" class="switch-btn" data-size="small"
                                                     data-color="#0059b2" /></Button></td>
                                     @endif
                                 @else
                                     @if ($item->no_index == '1')
                                         <td><span class="status-badge status-live">True</span></td>
                                     @else
                                         <td><span class="status-badge status-not-live">False</span></td>
                                     @endif
                                 @endif
                                 <td>
                                     @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                         @if ($item->status == '1')
                                             <label id="status_label_{{ $item->id }}"
                                                     style="display: none;">LIVE</label><Button style="border: none"
                                                     onclick="status_click(this, '{{ $item->id }}')"><input
                                                         type="checkbox" checked class="switch-btn"
                                                         data-size="small" data-color="#0059b2" /></Button>
                                         @else
                                             <label id="status_label_{{ $item->id }}"
                                                     style="display: none;">NOT LIVE</label><Button
                                                     style="border: none"
                                                     onclick="status_click(this, '{{ $item->id }}')"><input
                                                         type="checkbox" class="switch-btn" data-size="small"
                                                         data-color="#0059b2" /></Button>
                                         @endif
                                     @else
                                         @if ($item->status == '1')
                                             <span class="status-badge status-live">Live</span>
                                         @else
                                             <span class="status-badge status-not-live">Not Live</span>
                                         @endif
                                     @endif
                                 </td>
                                 <td style="text-align: center;">
                                     <div class="dropdown action-dropdown">
                                         <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                             <i class="dw dw-more"></i>
                                         </a>
                                         <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                             @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type) || $roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                             <a class="dropdown-item" href="edit_v_item/{{ $item->id }}">
                                                 <i class="dw dw-edit2"></i> Edit
                                             </a>
                                             @endif
                                             <a class="dropdown-item" href="edit_seo_v_item/{{ $item->id }}">
                                                 <i class="dw dw-file"></i> Edit SEO Data
                                             </a>
                                             @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                 <a class="dropdown-item" href="#"
                                                     onclick="set_delete_id('{{ $item->id }}')"
                                                     data-backdrop="static" data-toggle="modal"
                                                     data-target="#delete_model">
                                                     <i class="dw dw-delete-3"></i> Delete
                                                 </a>
                                             @endif
                                         </div>
                                     </div>
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="13" style="text-align: center; padding: 40px; color: #6c757d;">
                                     <i class="fa fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                                     <p class="mb-0">No video items found</p>
                                 </td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>

             <div class="pagination-wrapper">
                 @include('partials.pagination', ['items' => $itemArray['item']])
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
                 <h4 class="modal-title" id="myLargeModalLabel">Delete Video Item</h4>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you want to delete this video item? This action cannot be undone.</p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                 <button type="button" class="btn btn-danger" onclick="delete_click()">Delete</button>
             </div>
         </div>
     </div>
 </div>

 @include('layouts.masterscript')
 <script>
     function set_delete_id($id) {
         $("#delete_id").val($id);
     }

     function delete_click() {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         id = $("#delete_id").val();
         var url = "{{ route('v_item.delete', ':id') }}";
         url = url.replace(":id", id);
         $.ajax({
             url: url,
             type: 'POST',
             beforeSend: function() {
                 $('#delete_model').modal('hide');
             },
             success: function(data) {
                 if (data.error) {
                     alert('Error: ' + data.error);
                 } else {
                     location.reload();
                 }
             },
             error: function(error) {
                 alert('Error: ' + error.responseText);
             },
             cache: false,
             contentType: false,
             processData: false
         })
     }

     function noindex_click(parentElement, $id) {
         let element = parentElement.firstElementChild;
         const originalChecked = element.checked;

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('v_item.noindex', ':id') }}".replace(':id', $id),
             type: 'POST',
             success: function(data) {
                 if (data.success) {
                     var x = document.getElementById("noindex_label_" + $id);
                     if (x.innerHTML === "TRUE") {
                         x.innerHTML = "FALSE";
                     } else {
                         x.innerHTML = "TRUE";
                     }
                 } else if (data.error) {
                     alert(data.error);
                     element.checked = originalChecked;
                 }
             },
             error: function(xhr) {
                 if (xhr.responseJSON && xhr.responseJSON.error) {
                     alert(xhr.responseJSON.error);
                 } else {
                     alert('An error occurred while updating No Index status');
                 }
                 element.checked = originalChecked;
             }
         });
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
        formData.append('type', 'video_item');
        var url = "{{ route('check_status') }}";
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "block";
                }
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
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
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

     function premium_click(parentElement, $id) {
        let element = parentElement.firstElementChild;
        const originalChecked = element.checked;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData();
        formData.append('id', $id);
        formData.append('type', 'video_item');
        var url = "{{ route('check_premium') }}";
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "block";
                }
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                if (data.error) {
                    alert(data.error);
                    element.checked = !originalChecked;
                    element.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                } else {
                    var x = document.getElementById("premium_label_" + $id);
                    if (x.innerHTML === "PREMIUM") {
                        x.innerHTML = "FREE";
                    } else {
                        x.innerHTML = "PREMIUM";
                    }
                }
            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

     function appSelection() {
         $('#create_item_action').submit();
     }

     $(document).on('change', '.video-category-select', function() {
         let categoryId = $(this).val();
         let videoId = $(this).data('id');
         let $select = $(this);

         if (categoryId !== '') {
             $.ajax({
                 url: "{{ route('v_item.assign-category') }}",
                 method: "POST",
                 data: {
                     _token: "{{ csrf_token() }}",
                     video_id: videoId,
                     category_id: categoryId
                 },
                 success: function(response) {
                     if (response.status === true) {
                         alert("Category updated to: " + response.category_name + " (" + response.parent_name + ")");
                         if (response.seo_users && response.seo_users.length > 0) {
                             let $row = $select.closest('tr');
                             let $seoSelect = $row.find('.assignSeoEmployee');
                             let seoOptions = '<option value="" selected disabled>Select User</option>';
                             response.seo_users.forEach(function(user) {
                                 seoOptions += '<option value="' + user.id + '">' + user.name + '</option>';
                             });
                             $seoSelect.html(seoOptions);
                         }
                     } else {
                         alert("Something went wrong.");
                     }
                 },
                 error: function() {
                     alert("Server error occurred.");
                 }
             });
         }
     });

     $(document).on('change', '.assignSeoEmployee', function() {
         let selectedUserId = $(this).val();
         let videoId = $(this).data('category-id');
         let $select = $(this);

         $.ajax({
             url: "{{ route('v_item.assign-seo') }}",
             type: 'POST',
             data: {
                 _token: "{{ csrf_token() }}",
                 id: videoId,
                 seo_emp_id: selectedUserId
             },
             success: function(response) {
                 if (response.status === true) {
                     $select.addClass("border-success");
                     setTimeout(function() { $select.removeClass("border-success"); }, 2000);
                     alert("Assign successfully");
                 } else {
                     alert(response.error || 'Something went wrong.');
                 }
             },
             error: function() {
                 alert('Server error occurred');
             }
         });
     });

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
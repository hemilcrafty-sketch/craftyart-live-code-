@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    .video-categories-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .categories-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .categories-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: white;
    }
    
    .categories-table-wrapper {
        overflow-x: auto;
    }
    
    .categories-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .categories-table thead th {
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
    
    .categories-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
        position: relative;
    }
    
    .categories-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .categories-table tbody tr:hover .category-link {
        color: #0056b3;
    }
    
    .cat-thumb-cell {
        width: 120px;
    }
    
    .cat-thumb-img {
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
    
    .filter-section {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
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
        color: white;
        text-decoration: none;
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
    
    .category-link {
        color: #007bff !important;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer !important;
        position: relative;
        z-index: 1;
        pointer-events: auto !important;
        display: inline-block;
    }
    
    .category-link:hover {
        color: #0056b3 !important;
        text-decoration: underline;
    }
    
    .category-link:visited {
        color: #007bff !important;
    }
    
    .no-image-text {
        font-size: 12px;
        color: #6c757d;
        font-style: italic;
    }
</style>

<div class="main-container">

    <div class="video-categories-container">
        <div class="categories-card">
            <div class="categories-header">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h4 class="mb-0" style="font-weight: 600; color: #212529;">Video Categories</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Manage your video category structure</p>
                    </div>
                    <div class="col-md-6 text-right">
                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) || $roleManager::isSeoExecutive(Auth::user()->user_type))
                            <a class="btn btn-add-new" href="create_v_cat" role="button">
                                <i class="fa fa-plus mr-2"></i>Add New Category
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        @include('partials.filter_form ', [
                            'action' => route('show_v_cat'),
                            'filterExtraNoIndex' => true,
                        ])
                    </div>
                </div>
            </div>

            <div class="categories-table-wrapper">
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 120px;">User</th>
                            <th style="width: 200px;">Category Name</th>
                            <th style="width: 180px;">Parent Category</th>
                            <th class="cat-thumb-cell">Thumb</th>
                            <th class="cat-thumb-cell">Mockup</th>
                            <th style="width: 100px;">Sequence</th>
                            <th style="width: 100px;">No Index</th>
                            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                <th style="width: 80px;">IMP</th>
                            @endif
                            <th style="width: 100px;">Status</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($catArray as $cat)
                            <tr>
                                <td class="id-cell">#{{ $cat->id }}</td>
                                <td>{{ $roleManager::getUploaderName($cat->emp_id) }}</td>
                                <td>{{ $cat->category_name }}</td>
                                <td>{{ $helperController::getParentVideoCatName($cat->parent_category_id, true) }}</td>
                                <td class="cat-thumb-cell">
                                    @if($cat->category_thumb && !str_contains($cat->category_thumb, 'no_image'))
                                        @php
                                            $thumbUrl = filter_var($cat->category_thumb, FILTER_VALIDATE_URL) 
                                                ? $cat->category_thumb 
                                                : config('filesystems.storage_url') . $cat->category_thumb;
                                        @endphp
                                        <img src="{{ $thumbUrl }}"
                                            onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2260%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%2260%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2212%22%3ENo Image%3C/text%3E%3C/svg%3E';"
                                            class="cat-thumb-img" alt="Thumb" />
                                    @else
                                        <span class="no-image-text">No image</span>
                                    @endif
                                </td>
                                <td class="cat-thumb-cell">
                                    @if($cat->mockup)
                                        @php
                                            $mockupUrl = filter_var($cat->mockup, FILTER_VALIDATE_URL) 
                                                ? $cat->mockup 
                                                : config('filesystems.storage_url') . $cat->mockup;
                                        @endphp
                                        <img src="{{ $mockupUrl }}"
                                            onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2260%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%2260%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2212%22%3ENo Image%3C/text%3E%3C/svg%3E';"
                                            class="cat-thumb-img" alt="Mockup" />
                                    @else
                                        <span class="no-image-text">No mockup</span>
                                    @endif
                                </td>
                                <td>{{ $cat->sequence_number }}</td>
                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                    @if ($cat->no_index == '1')
                                        <td><label id="noindex_label_{{ $cat->id }}"
                                                style="display: none;">TRUE</label><Button style="border: none"
                                                onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                    type="checkbox" checked class="switch-btn" data-size="small"
                                                    data-color="#0059b2" /></Button></td>
                                    @else
                                        <td><label id="noindex_label_{{ $cat->id }}"
                                                style="display: none;">FALSE</label><Button style="border: none"
                                                onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                    type="checkbox" class="switch-btn" data-size="small"
                                                    data-color="#0059b2" /></Button></td>
                                    @endif
                                @else
                                    @if ($cat->no_index == '1')
                                        <td><span class="status-badge status-live">True</span></td>
                                    @else
                                        <td><span class="status-badge status-not-live">False</span></td>
                                    @endif
                                @endif

                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                    @if ($cat->parent_category_id == 0)
                                        @if ($cat->imp == '1')
                                            <td><label id="imp_label_{{ $cat->id }}"
                                                    style="display: none;">TRUE</label><Button
                                                    style="border: none"
                                                    onclick="imp_click(this, '{{ $cat->id }}')"><input
                                                        type="checkbox" checked class="switch-btn"
                                                        data-size="small" data-color="#0059b2" /></Button>
                                            </td>
                                        @else
                                            <td>
                                                <label id="imp_label_{{ $cat->id }}"
                                                    style="display: none;">FALSE</label><Button
                                                    style="border: none"
                                                    onclick="imp_click(this, '{{ $cat->id }}')"><input
                                                        type="checkbox" class="switch-btn" data-size="small"
                                                        data-color="#0059b2" /></Button>
                                            </td>
                                        @endif
                                    @else
                                        <td><span class="no-image-text">N/A</span></td>
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
                                        <td><span class="status-badge status-live">Live</span></td>
                                    @else
                                        <td><span class="status-badge status-not-live">Not Live</span></td>
                                    @endif
                                @endif

                                <td style="text-align: center;">
                                    <div class="dropdown action-dropdown">
                                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="edit_v_cat/{{ $cat->id }}"><i
                                                    class="dw dw-edit2"></i> Edit</a>
                                            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                <a class="dropdown-item" href="#"
                                                    onclick="set_delete_cat_id('{{ $cat->id }}')"
                                                    data-backdrop="static" data-toggle="modal"
                                                    data-target="#delete_cat_model">
                                                    <i class="dw dw-delete-3"></i> Delete
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <div class="modal fade" id="Medium-modal" tabindex="-1" role="dialog"
                                    aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myLargeModalLabel">Delete
                                                </h4>
                                                <button type="button" class="close" data-bs-dismiss="modal"
                                                    aria-hidden="true">×</button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">No</button>
                                                <a href="delete_v_cat/{{ $cat->id }}"><button
                                                        type="button" class="btn btn-primary">Yes,
                                                        Delete</button></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" style="text-align: center; padding: 40px; color: #6c757d;">
                                    <i class="fa fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                                    <p class="mb-0">No video categories found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                @include('partials.pagination', ['items' => $catArray])
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_cat_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <input type="hidden" id="delete_cat_id" name="delete_cat_id">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Delete Video Category</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this video category? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="delete_cat_click()">Delete</button>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function set_delete_cat_id($id) {
        $("#delete_cat_id").val($id);
    }

    function delete_cat_click() {
        var id = $("#delete_cat_id").val();
        if (id) {
            window.location.href = "delete_v_cat/" + id;
        }
    }

    function imp_click(parentElement, $id) {
        let element = parentElement.firstElementChild;
        const originalChecked = element.checked;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('v_cat.imp', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        formData.append('isVideo', '1');
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
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                alert(error.responseText);
                element.checked = !originalChecked;
                element.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
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

        var status = $id;
        var url = "{{ route('check_n_i') }}";
        var formData = new FormData();
        formData.append('id', $id);
        formData.append('type', 'video_cat');
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
        formData.append('type', 'video_cat');
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

    const sortTable = (event, column, sortType) => {
        event.preventDefault();
        let url = new URL(window.location.href);
        url.searchParams.set('sort_by', column);
        url.searchParams.set('sort_order', sortType);
        window.location.href = url.toString();
    }

    // Initialize Switchery for toggle switches
    $(document).ready(function() {
        setTimeout(function() {
            var elems = document.querySelectorAll('.switch-btn');
            if (elems.length > 0) {
                elems.forEach(function(elem) {
                    if (!elem.hasAttribute('data-switchery')) {
                        try {
                            var switchery = new Switchery(elem, {
                                color: '#0059b2',
                                size: 'small'
                            });
                        } catch (e) {
                            console.error('Switchery init error:', e);
                        }
                    }
                });
            }
        }, 100);
    });
</script>
</body>

</html>
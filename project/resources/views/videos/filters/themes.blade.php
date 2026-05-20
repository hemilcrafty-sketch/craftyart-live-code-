@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<style>
    /* Bold styling for parent categories in select2 */
    .select2-results__option.parent-category {
        font-weight: 700 !important;
        color: #1a1a1a !important;
    }
    
    /* White text on hover for parent categories */
    .select2-results__option.parent-category:hover,
    .select2-results__option.parent-category.select2-results__option--highlighted {
        color: #ffffff !important;
    }
    
    .select2-results__option.child-category {
        font-weight: 400 !important;
        padding-left: 20px !important;
    }
    
    /* For selected items in the select2 */
    .select2-selection__choice[title*="parent-"] {
        font-weight: 600 !important;
    }
</style>
<div class="main-container seo-all-container">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box d-flex flex-column" style="height: 94vh; overflow: hidden;">
                <div class="row justify-content-between flex-wrap">
                    <div class="col-md-2 m-1">
                        @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                            <a href="javascript:void(0)" onclick="openAddModal()" class="btn btn-primary item-form-input">
                                Add Video Theme
                            </a>
                        @endif
                    </div>

                    <div class="col-md-8 col-12">
                        @include('partials.filter_form', [
                            'action' => route('show_video_theme'),
                        ])
                    </div>
                </div>

                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th style="width:150px">Name</th>
                                    <th style="width:150px">ID Name</th>
                                    <th style="width:200px">Category</th>
                                    <th style="width:150px">Status</th>
                                    <th style="width:150px">User</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($themeArray as $style)
                                    @php
                                        $newCategoryIds =
                                            isset($style->category_id) && $style->category_id != null
                                                ? json_decode($style->category_id, true)
                                                : [];
                                        if (!is_array($newCategoryIds)) {
                                            $newCategoryIds = [];
                                        }
                                        $categoryNames = $allCategories
                                            ->whereIn('id', $newCategoryIds)
                                            ->pluck('category_name')
                                            ->implode(', ');
                                    @endphp
                                    <tr>
                                        <td class="table-plus">{{ $style->id }}</td>
                                        <td class="table-plus">{{ $style->name }}</td>
                                        <td class="table-plus">{{ $style->id_name ?? '' }}</td>
                                        <td class="table-plus">{{ $categoryNames ?: 'N/A' }}</td>
                                        <td>{{ $style->status == '1' ? 'Active' : 'Disabled' }}</td>
                                        <td>{{ $roleManager::getUploaderName($style->emp_id) }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item w-50"
                                                    onclick="openEditModal({
                                                        id: '{{ $style->id }}',
                                                        name: '{{ addslashes($style->name) }}',
                                                        id_name: '{{ $style->id_name }}',
                                                        status: '{{ $style->status }}',
                                                        categories: {{ $style->category_id ? json_encode($style->category_id) : 'null' }}
                                                    })">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <button class="dropdown-item w-50"
                                                        onclick="delete_click('{{ $style->id }}')">
                                                        <i class="dw dw-delete-3"></i> Delete
                                                    </button>
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
                @include('partials.pagination', ['items' => $themeArray])
            </div>
        </div>
    </div>
</div>

<div class="modal fade seo-all-container" id="add_modal" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Video Theme</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="submit_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="item_id" value="">

                    <div class="form-group">
                        <h7>Name</h7>
                        <input type="text" class="form-control" id="itemName" name="name"
                            placeholder="Video Theme Name" required />
                    </div>

                    <div class="form-group">
                        <h7>ID Name</h7>
                        <input type="text" class="form-control" id="itemIDName" name="id_name" placeholder="ID Name"
                            required />
                    </div>

                    <div class="form-group">
                        <h6>Category</h6>
                        <div class="col-sm-20" id="newCategory">
                            <select class="custom-select2 form-control" multiple="multiple"
                                data-style="btn-outline-primary" name="category_ids[]"
                                id="editNewEditCategoryIds" required>
                                @foreach ($groupedVideoCategories as $group)
                                    {{-- Parent category as selectable option --}}
                                    <option value="{{ $group['parent']->id }}" class="parent-category">
                                        {{ $group['parent']->category_name }}
                                    </option>
                                    {{-- Child categories indented --}}
                                    @foreach ($group['children'] as $child)
                                        <option value="{{ $child->id }}" class="child-category">
                                            &nbsp;&nbsp;&nbsp;{{ $child->category_name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <select id="itemStatus" class="form-control" name="status">
                            <option value="1">Active</option>
                            <option value="0">Disable</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" id="submit_btn" class="btn btn-primary btn-block">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function openAddModal() {
        $('#modal_title').text('Add Video Theme');
        $('#submit_btn').text('Save');
        $('#submit_form')[0].reset();
        $('#item_id').val('');
        $('#editNewEditCategoryIds').val(null).trigger('change');
        $('#add_modal').modal('show');
    }

    function openEditModal(data) {
        $('#modal_title').text('Edit Video Theme');
        $('#submit_btn').text('Update');
        $('#item_id').val(data.id);
        $('#itemName').val(data.name);
        $('#itemStatus').val(data.status);
        $('#itemIDName').val(data.id_name);

        // Set categories
        if (data.categories) {
            let categories = typeof data.categories === 'string' ? JSON.parse(data.categories) : data.categories;
            if (Array.isArray(categories)) {
                $('#editNewEditCategoryIds').val(categories).trigger('change');
            }
        } else {
            $('#editNewEditCategoryIds').val(null).trigger('change');
        }

        $('#add_modal').modal('show');
    }

    $('#submit_form').on('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const id = $('#item_id').val();
        if (id) {
            formData.append('id', id);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: 'submit_video_theme',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert(data.error);
                } else {
                    location.reload();
                }
            },
            error: function(xhr) {
                $('#main_loading_screen').hide();
                alert(xhr.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    function delete_click(id) {
        if (!confirm('Are you sure you want to delete this item?')) return;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('video_theme.delete', ':id') }}";
        url = url.replace(":id", id);

        $.ajax({
            url: url,
            type: 'POST',
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert('error==>' + data.error);
                } else {
                    location.reload();
                }
            },
            error: function(error) {
                $('#main_loading_screen').hide();
                alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    $("#itemName").on("input", function() {
        const titleString = toTitleCase($(this).val());
        $("#itemIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });

    // Initialize select2 for categories
    $(document).ready(function() {
        $('.custom-select2').select2({
            placeholder: "Select categories",
            allowClear: true,
            dropdownParent: $('#add_modal'),
            templateResult: formatCategoryOption,
            templateSelection: formatCategorySelection
        });
        
        // Format options in dropdown
        function formatCategoryOption(option) {
            if (!option.id) {
                return option.text;
            }
            
            var $option = $(option.element);
            var $span = $('<span></span>');
            
            if ($option.hasClass('parent-category')) {
                $span.css({
                    'font-weight': '700',
                    'color': '#1a1a1a'
                });
            } else if ($option.hasClass('child-category')) {
                $span.css({
                    'padding-left': '20px',
                    'font-weight': '400'
                });
            }
            
            $span.text(option.text);
            return $span;
        }
        
        // Format selected items
        function formatCategorySelection(option) {
            if (!option.id) {
                return option.text;
            }
            
            var $option = $(option.element);
            var $span = $('<span></span>');
            
            if ($option.hasClass('parent-category')) {
                $span.css('font-weight', '600');
            }
            
            $span.text(option.text);
            return $span;
        }
    });
</script>

</body>

</html>

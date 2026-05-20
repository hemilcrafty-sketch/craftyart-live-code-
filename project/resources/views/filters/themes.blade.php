@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box d-flex flex-column" style="height: 94vh; overflow: hidden;">
                {{-- Filter and Action Row --}}
                <div class="row justify-content-between flex-wrap">
                    <div class="col-md-2 m-1">
                        @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                            <a href="javascript:void(0)" class="btn btn-primary item-form-input"
                                onclick="openThemeModal('add')">
                                Add Theme
                            </a>
                        @endif

                    </div>

                    <div class="col-md-9">
                        @include('partials.filter_form ', [
                            'action' => route('show_theme'),
                        ])
                    </div>
                </div>

                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th style="width:150px;">Name</th>
                                    <th style="width:200px;">New Category</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($themeArray as $theme)
                                    @php
                                        $newCategoryIds =
                                            isset($theme->new_category_id) && $theme->new_category_id != null
                                                ? json_decode($theme->new_category_id, true)
                                                : [];
                                        if (!is_array($newCategoryIds)) {
                                            $newCategoryIds = [$newCategoryIds];
                                        }
                                    @endphp
                                    <tr>
                                        <td class="table-plus">{{ $theme->id }}</td>

                                        <td class="table-plus">{{ $theme->name }}</td>
                                        <td class="table-plus">
                                            {{ \App\Http\Controllers\HelperController::getNewCatNames($newCategoryIds, true) }}
                                        </td>

                                        @if ($theme->status == '1')
                                            <td>Active</td>
                                        @else
                                            <td>Disabled</td>
                                        @endif
                                        <td>{{ $roleManager::getUploaderName($theme->emp_id) }}
                                        </td>
                                        <td>
                                            <div class="d-flex">

                                                <button class="dropdown-item"
                                                    onclick='openThemeModal("edit", {
       id: "{{ $theme->id }}",
       name: @json($theme->name),
       id_name: @json($theme->id_name),
       status: "{{ $theme->status }}",
       categories: {!! json_encode($newCategoryIds) !!}
   })'>
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <Button class="dropdown-item"
                                                        onclick="delete_click('{{ $theme->id }}')"><i
                                                            class="dw dw-delete-3"></i> Delete
                                                    </Button>
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

    <div class="modal fade seo-all-container" id="add_theme_model" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="theme_modal_title">Add Theme</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <form method="post" id="theme_form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="theme_id" id="theme_id" value="">
                        {{-- <input type="hidden" name="_method" id="theme_method" value="POST"> --}}

                        <div class="form-group">
                            <h7>Name</h7>
                            <input type="text" class="form-control" placeholder="Theme Name" id="themeName"
                                name="name" required />
                        </div>

                        <div class="form-group">
                            <h7>ID Name</h7>
                            <input type="text" class="form-control" placeholder="ID Name" id="themeIDName"
                                name="id_name" required />
                        </div>

                        <div class="form-group">
                            <h6>New Category</h6>
                            <div class="col-sm-20" id="newCategory">
                                <select class="custom-select2 form-control" multiple="multiple"
                                    data-style="btn-outline-primary" name="new_category_ids[]"
                                    id="editNewEditCategoryIds" required>
                                    @foreach ($allCategories as $newCategory)
                                        <option value="{{ $newCategory->id }}">{{ $newCategory->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <h6>Status</h6>
                            <select id="themeStatus" class="selectpicker form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-block" id="theme_submit_btn">Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.masterscript')
    <script>
        function openThemeModal(mode, data = {}) {
            $('#theme_form')[0].reset();
            $('#editNewEditCategoryIds').val([]).trigger('change');

            if (mode === 'edit') {
                $('#theme_modal_title').text('Edit Theme');
                $('#theme_submit_btn').text('Update');
                $('#theme_id').val(data.id);
                $('#themeName').val(data.name);
                $('#themeIDName').val(data.id_name);
                $('#themeStatus').val(data.status);
                $('#theme_method').val('PUT');
                $('#theme_form').attr('data-mode', 'edit');

                let categoryIds = data.categories;
                if (typeof categoryIds === 'string') {
                    try {
                        categoryIds = JSON.parse(categoryIds) || [];
                    } catch (e) {
                        categoryIds = [];
                    }
                }
                if (!Array.isArray(categoryIds)) {
                    categoryIds = categoryIds != null && categoryIds !== '' ? [categoryIds] : [];
                }
                categoryIds = categoryIds.map(String);
                $('#editNewEditCategoryIds').val(categoryIds).trigger('change');

            } else {
                $('#theme_modal_title').text('Add Theme');
                $('#theme_submit_btn').text('Save');
                $('#theme_method').val('POST');
                $('#theme_form').attr('data-mode', 'add');
            }

            $('#add_theme_model').modal('show');
        }

        $('#theme_form').on('submit', function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            let id = $('#theme_id').val();

            if (id) {
                formData.append('id', id); // Send ID when editing
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });

            $.ajax({
                url: 'submit_theme',
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });

            var url = "{{ route('theme.delete', ':id') }}";
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


        /* New Category */

        $(document).on('click', '#parentCategoryInput', function() {
            $("#newCategoryRequiredPopup").hide();
            if ($('.parent-category-input').hasClass('show')) {
                $('.parent-category-input').removeClass('show');
            } else {
                $(".parent-category-input").addClass('show');
            }
        });

        $(document).on("click", ".category", function(event) {
            $(".category").removeClass("selected");
            $(".subcategory").removeClass("selected");
            var id = $(this).data('id');
            $("input[name='new_category_id']").val(id);
            $("#parentCategoryInput span").html($(this).data('catname'));
            $('.parent-category-input').removeClass('show');
            $(this).addClass("selected");

        });

        $(document).on("click", ".subcategory", function(event) {
            event.stopPropagation();
            $(".category").removeClass("selected");
            $(".subcategory").removeClass("selected");
            var id = $(this).data('id');
            var parentId = $(this).data('pid');
            $("input[name='new_category_id']").val(id);
            $('.parent-category-input').removeClass('show');
            $("#parentCategoryInput span").html($(this).data('catname'));
            $(this).addClass("selected");

        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
                $('.custom-dropdown.parent-category-input.show').removeClass('show');
            }
        });

        $(document).on("click", "li.category.none-option", function() {
            $("input[name='new_category_id']").val("0");
            $('.parent-category-input').removeClass('show');
            $("#parentCategoryInput span").html('== none ==');
        });

        $('#categoryFilter').on('input', function() {
            var filterValue = $(this).val().toLowerCase();
            $('.category, .subcategory').each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(filterValue) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

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

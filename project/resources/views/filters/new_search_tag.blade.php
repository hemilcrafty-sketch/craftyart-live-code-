@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-access-container">


    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3 m-1">
                            @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                                <a href="#" class="btn btn-primary item-form-input" data-toggle="modal"
                                    data-target="#search_tag_modal" onclick="openAddModal()">Add New Search Tag</a>
                            @endif
                        </div>

                        <div class="col-md-8">
                            @include('partials.filter_form ', [
                                'action' => route('new_search_tags.index'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(108vh - 220px) !important ;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Assign To</th>
                                    <th>Name</th>
                                    <th>New Category</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($newSearchTags as $newSearchTag)
                                    <tr>
                                        <td class="table-plus">{{ $newSearchTag->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($newSearchTag->emp_id) }}
                                        </td>
                                        <td>{{ $newSearchTag->assignedSeo->name ?? 'N/A' }}</td>

                                        <td class="table-plus">{{ $newSearchTag->name }}</td>

                                        @php
                                            $catNames = '';
                                            $categoryIds = json_decode($newSearchTag->new_category_id);
                                            for ($i = 0; $i < count($categoryIds); $i++) {
                                                $cn = $helperController::getNewCatName($categoryIds[$i]);
                                                if ($i == count($categoryIds) - 1) {
                                                    $catNames = $catNames . $cn;
                                                } else {
                                                    $catNames = $catNames . $cn . ', ';
                                                }
                                            }
                                        @endphp

                                        <td class="table-plus">{{ $catNames }}</td>

                                        @if ($newSearchTag->status == '1')
                                            <td>Active</td>
                                        @else
                                            <td>Disabled</td>
                                        @endif
                                        <td>
                                            <div class="d-flex">

                                                <button class="dropdown-item seo-all-container"
                                                    onclick="openEditModal('{{ $newSearchTag->id }}')"
                                                    data-toggle="modal" data-target="#search_tag_modal">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <button class="dropdown-item seo-all-container"
                                                        onclick="delete_click('{{ $newSearchTag->id }}')"><i
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
                @include('partials.pagination', ['items' => $newSearchTags])
            </div>
        </div>
    </div>
</div>

<!-- Reusable Modal for Add/Edit -->
<div class="modal fade seo-all-container" id="search_tag_modal" tabindex="-1" role="dialog"
    aria-labelledby="searchTagModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="searchTagModalLabel">Add New Search Tag</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="search_tag_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="search_tag_id" id="search_tag_id" value="">

                    <div class="form-group">
                        <label for="searchTagName">Name</label>
                        <input type="text" class="form-control" id="searchTagName" name="name" required />
                    </div>

                    <div class="form-group">
                        <label for="searchTagIDName">ID Name</label>
                        <input type="text" class="form-control" id="searchTagIDName" name="id_name" required />
                    </div>

                    <div class="col-md-12 col-sm-12" style="height: auto;padding: 0;">
                        <div class="form-group category-dropbox-wrap">
                            <h7>Select New Category</h7>
                            <div class="input-subcategory-dropbox unset-bottom-border" id="parentCategoryInput"
                                style="height: auto; border-bottom: 1px solid #ced4da;"><span>== none ==</span> <i
                                    style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i></div>
                            <div class="custom-dropdown parent-category-input">
                                <input type="text" id="categoryFilter" class="form-control-file form-control"
                                    placeholder="Search categories">
                                <ul class="dropdown-menu-ul filter-wrap-list">
                                    <li class="category none-option">== none ==</li>
                                    @foreach ($allNewCategories as $category)
                                        @php
                                            $classBold =
                                                !empty($category['subcategories']) &&
                                                isset($category['subcategories'][0])
                                                    ? 'has-children'
                                                    : 'has-parent';
                                            $selected =
                                                isset($dataArray['item']['new_category_id']) &&
                                                $dataArray['item']['new_category_id'] == $category['id']
                                                    ? 'selected'
                                                    : '';
                                        @endphp
                                        <li class="category {{ $classBold }} {{ $selected }}"
                                            data-id="{{ $category['id'] }}"
                                            data-catname="{{ $category['category_name'] }}">
                                            <span>{{ $category['category_name'] }}</span>
                                            @if (!empty($category['subcategories']))
                                                <ul class="subcategories">
                                                    @foreach ($category['subcategories'] as $subcategory)
                                                        @include('partials.subcategory-optgroup', [
                                                            'subcategory' => $subcategory,
                                                            'sub_category_id' => $subcategory['id'],
                                                            'sub_category_name' => $subcategory['category_name'],
                                                        ])
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="popup-container" id="newCategoryRequiredPopup">
                                <p><span class="required-icon">!</span>Please select a new category.</p>
                            </div>
                            {{-- <input type="hidden" name="new_category_id" value="0"> --}}
                            <input type="hidden" name="new_category_id" id="new_category_id_input" value="">
                        </div>
                    </div>

                    @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                        <div class="form-group mt-3">
                            <label for="assignSubCatSelect">Assign Sub Categories Tag</label>
                            <select class="form-control" id="assignSubCatSelect" name="seo_emp_id">
                                <option disabled {{ empty($selectedSubCatId) ? 'selected' : '' }}>Select</option>
                                @foreach ($assignSubCat as $subcat)
                                    <option value="{{ $subcat->id }}"
                                        {{ isset($selectedSubCatId) && $selectedSubCatId == $subcat->id ? 'selected' : '' }}>
                                        {{ $subcat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif



                    <div class="form-group mt-4">
                        <label for="status">Status</label>
                        <select id="status" class="form-control" name="status">
                            <option value="1">Active</option>
                            <option value="0">Disable</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" id="modal_submit_button" type="submit"
                                value="Save">
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    let catIds = [];
    let catNames = [];

    // Open Add Modal
    function resetModalForm() {
        $('#search_tag_form')[0].reset();
        $('#search_tag_id').val('');
        $('input[name="new_category_id"]').val('0');
        $('#searchTagModalLabel').text("Add New Search Tag");
        $('#modal_submit_button').val('Save');
        $('#assignSubCatSelect').val([]).trigger('change');

        resetSelection(); // reset selected categories
    }

    function openAddModal() {
        resetModalForm();
        $('#search_tag_modal').modal('show');
    }

    // Open Edit Modal
    function openEditModal(id) {
        $('#search_tag_form')[0].reset();
        $('#searchTagModalLabel').text("Edit Search Tag");
        $('#modal_submit_button').val('Update');

        catIds = [];
        catNames = [];
        $(".category, .subcategory").removeClass("selected");

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: "{{ route('new_search_tags.edit', ':id') }}".replace(':id', id),
            type: 'GET',
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(response) {
                $('#main_loading_screen').hide();

                if (response.status === true) {
                    const tag = response.data;

                    $('#search_tag_id').val(tag.id);
                    $('#searchTagName').val(tag.name);
                    $('#searchTagIDName').val(tag.id_name);
                    $('#status').val(tag.status);
                    $('#assignSubCatSelect').val(tag.seo_emp_id).trigger('change');

                    catIds = [];
                    catNames = [];

                    $(".category, .subcategory").removeClass("selected");

                    const ids = tag.new_category_id;

                    ids.forEach(function(id) {
                        const el = $('.category[data-id="' + id + '"], .subcategory[data-id="' +
                            id + '"]');

                        if (el.length) {
                            el.addClass('selected');
                            const name = el.data('catname');
                            if (name && !catNames.includes(name)) {
                                catIds.push(id);
                                catNames.push(name);
                            }
                        }
                    });

                    $('input[name="new_category_id"]').val(catIds.join(','));
                    $('#parentCategoryInput span').text(catNames.length ? catNames.join(', ') :
                        '== none ==');

                    $('#search_tag_modal').modal('show');
                }
            },
            error: function(err) {
                $('#main_loading_screen').hide();
                alert(err.responseText);
            }
        });
    }

    // Submit Form
    $('#search_tag_form').on('submit', function(e) {
        e.preventDefault();

        const id = $('#search_tag_id').val();
        const formData = new FormData(this);

        let url = id ?
            "{{ route('new_search_tags.update', ':id') }}".replace(':id', id) :
            "{{ route('new_search_tags.store') }}";

        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    location.reload();
                }
            },
            error: function(err) {
                $('#main_loading_screen').hide();
                alert(err.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    // Delete Record
    function delete_click(id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var url = "{{ route('new_search_tags.destroy', ':id') }}".replace(":id", id);

        $.ajax({
            url: url,
            type: 'DELETE',
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
            error: function(error) {
                $('#main_loading_screen').hide();
                alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    // Category Dropdown Handling
    $(document).on('click', '#parentCategoryInput', function() {
        $("#newCategoryRequiredPopup").hide();
        $('.parent-category-input').toggleClass('show');
    });

    $(document).on('click', '.custom-dropdown .category', function() {
        addOrRemove($(this));
    });

    $(document).on("click", ".subcategory", function(event) {
        event.stopPropagation();
        addOrRemove($(this));
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
            $('.custom-dropdown.parent-category-input.show').removeClass('show');
        }
    });

    $(document).on("click", "li.category.none-option", function() {
        resetSelection();
    });

    // Filter Search in Dropdown
    $('#categoryFilter').on('input', function() {
        const filterValue = $(this).val().toLowerCase();
        $('.category, .subcategory').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(filterValue) > -1);
        });
    });

    // Handle Add or Remove Category/Subcategory
    function addOrRemove(ele) {
        const id = String(ele.data('id'));
        const catname = ele.data('catname');
        if (!id || !catname) return;

        const idIndex = catIds.indexOf(id);

        if (idIndex !== -1) {
            catIds.splice(idIndex, 1);
            catNames.splice(idIndex, 1);
            ele.removeClass("selected");
        } else {
            catIds.push(id);
            catNames.push(catname);
            ele.addClass("selected");
        }

        $('input[name="new_category_id"]').val(catIds.join(','));
        $('#parentCategoryInput span').text(catNames.length ? catNames.join(', ') : '== none ==');
    }

    // Clear All Selection
    function resetSelection() {
        catIds = [];
        catNames = [];

        $(".category, .subcategory").removeClass("selected");
        $('input[name="new_category_id"]').val("0");
        $('#parentCategoryInput span').html("== none ==");

        $('.parent-category-input').removeClass('show');
    }


    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

    $(document).on("keypress", "#searchTagName", function() {
        const titleString = toTitleCase($(this).val());
        $("#searchTagIDName").val(titleString.toLowerCase().replace(/\s+/g, '-'));
        $(this).val(titleString);
    });

    $(document).on("keypress", "#editSearchTagName", function() {
        const titleString = toTitleCase($(this).val());
        $("#editSearchTagIDName").val(titleString.toLowerCase().replace(/\s+/g, '-'));
        $(this).val(titleString);
    });
</script>
</body>

</html>

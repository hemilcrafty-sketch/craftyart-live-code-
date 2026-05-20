@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="xs-pd-10-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 95vh; overflow: hidden;">
                    {{-- Filter & Search Section --}}
                    <div class="filter-header">
                        <div class="row gx-2 gy-1 align-items-center justify-content-between flex-wrap">

                            @if (
                            $roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type) ||
                            $roleManager::isSeoExecutive(Auth::user()->user_type))
                            <!--                            <div class="col-md-auto col-12 d-flex">-->
                            <!--                                <button class="btn btn-primary w-100 item-form-input"-->
                            <!--                                        onclick="appSelection()">Add</button>-->
                            <!--                            </div>-->
                            <div class="col-md-3 m-1">
                                <a class="btn btn-primary item-form-input" href="add_attire" role="button"> Add
                                    Attire
                                </a>
                            </div>
                            @endif

                            {{-- Template Status Dropdown --}}
                            {{-- SEO Employee Filter --}}
                            @if ($roleManager::isSeoExecutive(Auth::user()->user_type))
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('show_attire_item') }}">
                                    <input type="hidden" name="query" value="{{ request('query') }}">
                                    <input type="hidden" name="seo_category_assigne"
                                           value="{{ request('seo_category_assigne', 'all') }}">
                                    <input type="hidden" name="premium_type"
                                           value="{{ request('premium_type', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    <input type="hidden" name="template_status"
                                           value="{{ request('template_status', 'all') }}">
                                    <input type="hidden" name="no_index_filter"
                                           value="{{ request('no_index_filter', '') }}">

                                    <select name="seo_employee" class="form-control item-form-input w-100"
                                            onchange="this.form.submit()">
                                        <option selected disabled>Assigned Filter</option>
                                        <option value="all"
                                                {{ request(
                                        'seo_employee') == 'all' ? 'selected' : '' }}>All Templates
                                        </option>
                                        <option value="assigned"
                                                {{ request(
                                        'seo_employee') == 'assigned' ? 'selected' : '' }}>Assigned
                                        Templates</option>
                                        <option value="unassigned"
                                                {{ request(
                                        'seo_employee') == 'unassigned' ? 'selected' : '' }}>Not
                                        Assigned Templates</option>
                                    </select>
                                </form>
                            </div>
                            @endif

                            {{-- SEO Category Assigned Filter --}}
                            @if ($roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('show_attire_item') }}">
                                    <input type="hidden" name="query" value="{{ request('query') }}">
                                    <input type="hidden" name="seo_employee"
                                           value="{{ request('seo_employee', 'all') }}">
                                    <input type="hidden" name="premium_type"
                                           value="{{ request('premium_type', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    <input type="hidden" name="template_status"
                                           value="{{ request('template_status', 'all') }}">
                                    <input type="hidden" name="no_index_filter"
                                           value="{{ request('no_index_filter', '') }}">

                                    <select name="seo_category_assigne" class="form-control item-form-input w-100"
                                            onchange="this.form.submit()">
                                        <option selected disabled>Assigned Category Filter</option>
                                        <option value="all"
                                                {{ request(
                                        'seo_category_assigne') == 'all' ? 'selected' : '' }}>All
                                        Templates</option>
                                        <option value="assigned"
                                                {{ request(
                                        'seo_category_assigne') == 'assigned' ? 'selected' : '' }}>
                                        Assigned Category</option>
                                        <option value="unassigned"
                                                {{ request(
                                        'seo_category_assigne') == 'unassigned' ? 'selected' : '' }}>
                                        Not Assigned Category</option>
                                    </select>
                                </form>
                            </div>
                            @endif

                            {{-- Premium Type --}}
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('show_attire_item') }}">
                                    <input type="hidden" name="query" value="{{ request('query') }}">
                                    <input type="hidden" name="seo_employee"
                                           value="{{ request('seo_employee', 'all') }}">
                                    <input type="hidden" name="seo_category_assigne"
                                           value="{{ request('seo_category_assigne', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    <input type="hidden" name="template_status"
                                           value="{{ request('template_status', 'all') }}">
                                    <input type="hidden" name="no_index_filter"
                                           value="{{ request('no_index_filter', '') }}">

                                    <select name="premium_type" class="form-control item-form-input w-100"
                                            onchange="this.form.submit()">
                                        <option selected disabled>Access Type</option>
                                        <option value="all"
                                                {{ request(
                                        'premium_type') == 'all' ? 'selected' : '' }}>All Templates
                                        </option>
                                        <option value="free"
                                                {{ request(
                                        'premium_type') == 'free' ? 'selected' : '' }}>Free</option>
                                        <option value="freemium"
                                                {{ request(
                                        'premium_type') == 'freemium' ? 'selected' : '' }}>Freemium
                                        </option>
                                        <option value="premium"
                                                {{ request(
                                        'premium_type') == 'premium' ? 'selected' : '' }}>Premium
                                        </option>
                                    </select>
                                </form>
                            </div>

                            {{-- Live Status --}}
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('show_attire_item') }}">
                                    <input type="hidden" name="query" value="{{ request('query') }}">
                                    <input type="hidden" name="seo_employee"
                                           value="{{ request('seo_employee', 'all') }}">
                                    <input type="hidden" name="seo_category_assigne"
                                           value="{{ request('seo_category_assigne', 'all') }}">
                                    <input type="hidden" name="premium_type"
                                           value="{{ request('premium_type', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    <input type="hidden" name="no_index_filter"
                                           value="{{ request('no_index_filter', '') }}">

                                    <select name="template_status" class="form-control w-100 item-form-input"
                                            onchange="this.form.submit()">
                                        <option selected disabled>Live Status</option>
                                        <option value="all"
                                                {{ request(
                                        'template_status') == 'all' ? 'selected' : '' }}>All Templates
                                        </option>
                                        <option value="live"
                                                {{ request(
                                        'template_status') == 'live' ? 'selected' : '' }}>Live Templates
                                        </option>
                                        <option value="not-live"
                                                {{ request(
                                        'template_status') == 'not-live' ? 'selected' : '' }}>Not Live
                                        Templates</option>
                                    </select>
                                </form>
                            </div>

                            {{-- Index / No index --}}
                            <div class="col-md-auto col-6 mt-1">
                                <form method="GET" action="{{ route('show_attire_item') }}">
                                    <input type="hidden" name="query" value="{{ request('query') }}">
                                    <input type="hidden" name="seo_employee"
                                           value="{{ request('seo_employee', 'all') }}">
                                    <input type="hidden" name="seo_category_assigne"
                                           value="{{ request('seo_category_assigne', 'all') }}">
                                    <input type="hidden" name="premium_type"
                                           value="{{ request('premium_type', 'all') }}">
                                    <input type="hidden" name="template_status"
                                           value="{{ request('template_status', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    @include('partials.no_index_filter_select')
                                </form>
                            </div>

                            {{-- Search --}}
                            <div class="col-md-auto col-12 mt-1">
                                <form action="{{ route('show_attire_item') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="template_status"
                                           value="{{ request('template_status', 'all') }}">
                                    <input type="hidden" name="animated" value="{{ request('animated', 'all') }}">
                                    <input type="hidden" name="seo_employee"
                                           value="{{ request('seo_employee', 'all') }}">
                                    <input type="hidden" name="seo_category_assigne"
                                           value="{{ request('seo_category_assigne', 'all') }}">
                                    <input type="hidden" name="premium_type"
                                           value="{{ request('premium_type', 'all') }}">
                                    <input type="hidden" name="no_index_filter"
                                           value="{{ request('no_index_filter', '') }}">
                                    <input type="text" class="form-control item-form-input" name="query"
                                           placeholder="Search..." value="{{ request()->input('query') }}">
                                    <button type="submit" class="btn btn-primary item-form-input">Search</button>
                                </form>
                            </div>

                        </div>
                    </div>

                    {{-- Scrollable Table --}}
                    <div class="scroll-wrapper table-responsive tableFixHead"
                         style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th style="width: 80px;">Id</th>
                                @if (!$roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type))
                                <th style="width: 130px;">User</th>
                                @endif
                                <th style="width: 150px;">Seo Executive</th>
                                @if (!$roleManager::isSeoIntern(Auth::user()->user_type))
                                <th style="width: 200px;">Assign to</th>
                                @endif

                                <th style="width: 200px;">Category Name</th>
                                <th style="width: 200px;">Poster Name</th>
                                <th style="width: 200px;">Poster Thumb</th>

                                @if (!$roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type))
                                <th style="width: 73px;">Views</th>
                                @endif


                                <th>No Index</th>
                                <th>Is Pinned</th>

                                @if (!$roleManager::isSeoExecutive(Auth::user()->user_type))
                                <th style="width: 124px;">Editor choice</th>
                                @endif

                                <th style="width: 150px;">Premium Type</th>
                                <th style="width: 75px;">Status</th>
                                <th style="width: 107px;">Created At</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                            </thead>
                            <tbody id="item_table">
                            @foreach ($itemArray['item'] as $item)
                            <tr style="background-color: #efefef;">
                                <td class="table-plus">{{ $item->id }}</td>

                                @if (!$roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type))
                                <td>{{ $roleManager::getEmployeeName($item->emp_id) }}</td>
                                @endif
                                @if (!$roleManager::isSeoIntern(Auth::user()->user_type))
                                <td>{{ $roleManager::getEmployeeName($item->seo_assigner_id) }}</td>
                                @endif

                                <td>
                                    @php
                                    $seoId = $categorySeoEmpIds[$item->new_category_id] ?? null;
                                    $user = $seoUsers[$seoId] ?? null;
                                    @endphp

                                    @if ($roleManager::isSeoExecutive(Auth::user()->user_type))
                                    {{-- Show dropdown only if user is SEO Executive --}}
                                    <select class="form-control assignSeoEmployee"
                                            data-category-id="{{ $item->id ?? 0 }}">
                                        <option value="" selected disabled>Select User</option>

                                        @foreach ($seoUsers as $seoUser)
                                        <option value="{{ $seoUser->id }}"
                                                {{ $item->seo_emp_id == $seoUser->id ? 'selected' : '' }}>
                                            {{ $seoUser->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @else
                                    {{ $roleManager::getUploaderName($item->seo_emp_id) }}
                                    @endif
                                </td>

                                <td style="position: relative;">
                                    @if ($roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                                    <select class="form-control new-category-select item-form-input" data-id="{{ $item->id }}">
                                        <option value="">Select Category</option>
                                        @foreach ($groupedNewCategories as $group)
                                        <option value="{{ $group['parent']->id }}" style="font-weight: bold"
                                                @if ($item->category_id == $group['parent']->id) selected @endif>
                                            {{ $group['parent']->category_name }}
                                        </option>

                                        @foreach ($group['children'] as $child)
                                        <option value="{{ $child->id }}"
                                                @if ($item->category_id == $child->id) selected @endif>
                                            &nbsp;&nbsp; {{ $child->category_name }}
                                        </option>
                                        @endforeach
                                        @endforeach
                                    </select>
                                    <label class="parent-label mt-1 d-block text-secondary">
                                        ({{ $helperController::getParentNewCatName($item->category_id) }})
                                    </label>
                                    @else
                                    @if (!empty($item->category_id) && $item->category_id != 0)
                                    <label>
                                        {{ $helperController::getCaricatureCatName($item->category_id) }}
                                    </label>
                                    @else
                                    <label>(No New Category Assigned)</label>
                                    @endif
                                    @endif
                                </td>
                                <td>{{ $item->post_name }}</td>
                                <td><img src="{{ $contentManager::getStorageLink($item->thumbnail_url) }}"
                                         style="max-width: 100px; max-height: 100px; width: auto; height: auto"/>
                                </td>

                                @if (!$roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type))
                                <td>{{ $item->views }} ({{ $item->trending_views }})</td>
                                @endif

                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                @if ($item->no_index == '1')
                                <td><label id="noindex_label_{{ $item->id }}"
                                           style="display: none;">TRUE</label>
                                    <Button style="border: none"
                                            onclick="noindex_click(this, '{{ $item->id }}')"><input
                                                type="checkbox" checked class="switch-btn"
                                                data-size="small" data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @else
                                <td><label id="noindex_label_{{ $item->id }}"
                                           style="display: none;">FALSE</label>
                                    <Button style="border: none"
                                            onclick="noindex_click(this, '{{ $item->id }}')"><input
                                                type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @endif
                                @else
                                @if ($item->no_index == '1')
                                <td>True</td>
                                @else
                                <td>False</td>
                                @endif
                                @endif

                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) ||
                                $roleManager::isSeoExecutive(Auth::user()->user_type))
                                @if ($item->pinned == '1')
                                <td><label id="pinned_label_{{ $item->id }}"
                                           style="display: none;">TRUE</label>
                                    <Button style="border: none"
                                            onclick="pinned_click('{{ $item->id }}')"><input
                                                type="checkbox" checked class="switch-btn"
                                                data-size="small" data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @else
                                <td>
                                    <label id="pinned_label_{{ $item->id }}"
                                           style="display: none;">FALSE</label>
                                    <Button style="border: none"
                                            onclick="pinned_click('{{ $item->id }}')"><input
                                                type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @endif
                                @else
                                @if ($item->pinned == '1')
                                <td>True</td>
                                @else
                                <td>False</td>
                                @endif
                                @endif


                                @if (
                                $roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type) ||
                                $roleManager::isSeoIntern(Auth::user()->user_type))
                                @if ($item->editor_choice == '1')
                                <td><label id="editorchoice_label_{{ $item->id }}"
                                           style="display: none;">TRUE</label>
                                    <Button style="border: none"
                                            onclick="editor_choice_click( '{{ $item->id }}')"><input
                                                type="checkbox" checked class="switch-btn"
                                                data-size="small" data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @else
                                <td><label id="editorchoice_label_{{ $item->id }}"
                                           style="display: none;">FALSE</label>
                                    <Button style="border: none"
                                            onclick="editor_choice_click('{{ $item->id }}')"><input
                                                type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @endif
                                @endif

                                @if ($roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type))
                                <td>
                                    <select class="form-control"
                                            onchange="onPremiumChange('{{ $item->id }}', this.value)">
                                        <option value="free"
                                                {{ $item->is_premium == '0' && $item->is_freemium == '0' ? 'selected' :
                                            '' }}>
                                            Free
                                        </option>
                                        <option value="freemium"
                                                {{ $item->is_premium == '0' && $item->is_freemium == '1' ? 'selected' :
                                            '' }}>
                                            Freemium
                                        </option>
                                        <option value="premium"
                                                {{ $item->is_premium == '1' && $item->is_freemium == '0' ? 'selected' :
                                            '' }}>
                                            Premium
                                        </option>
                                    </select>
                                </td>
                                @else
                                <td>
                                    @php
                                    if ($item->is_premium == '1' && $item->is_freemium == '0') {
                                    $label = 'Premium';
                                    } elseif ($item->is_premium == '0' && $item->is_freemium == '1') {
                                    $label = 'Freemium';
                                    } else {
                                    $label = 'Free';
                                    }
                                    @endphp
                                    <span>{{ $label }}</span>
                                </td>
                                @endif

                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) ||
                                $roleManager::isSeoExecutive(Auth::user()->user_type))
                                @if ($item->status == '1')
                                <td><label id="status_label_{{ $item->id }}"
                                           style="display: none;">Live</label>
                                    <Button style="border: none"
                                            onclick="status_click(this, '{{ $item->id }}')"><input
                                                type="checkbox" checked class="switch-btn"
                                                data-size="small" data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @else
                                <td><label id="status_label_{{ $item->id }}"
                                           style="display: none;">Not
                                        Live</label>
                                    <Button style="border: none"
                                            onclick="status_click(this, '{{ $item->id }}')"><input
                                                type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2"/>
                                    </Button>
                                </td>
                                @endif
                                @else
                                @if ($item->status == '1')
                                <td>True</td>
                                @else
                                <td>False</td>
                                @endif
                                @endif

                                <td>{{ $item->created_at }}</td>

                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                           href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item"
                                               href="{{ route('attire.edit', $item->id) }}">
                                                <i class="dw dw-edit2"></i> Edit Attire
                                            </a>
                                            <a class="dropdown-item"
                                               href="edit_seo_attire/{{ $item->id }}"><i
                                                        class="dw dw-edit2"></i> Edit SEO Data</a>
                                            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                            <Button class="dropdown-item"
                                                    onclick="set_delete_id('{{ $item->id }}')"
                                                    data-backdrop="static" data-toggle="modal"
                                                    data-target="#delete_model"><i
                                                        class="dw dw-delete-3"></i>Delete
                                            </Button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Footer --}}
                    <div class="pagination-footer w-100">
                        <div class="row mx-0 align-items-center flex-nowrap justify-content-between">
                            <div class="col-auto">
                                <div class="dataTables_info" role="status" aria-live="polite">
                                    {{ $itemArray['count_str'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    <ul class="pagination mb-0">
                                        {{
                                        $itemArray['item']->appends(request()->input())->links('pagination::bootstrap-4')
                                        }}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

@include('layouts.masterscript')
<script>
    $(document).on('change', '.new-category-select', function () {
        let categoryId = $(this).val();
        let designId = $(this).data('id');
        let $select = $(this);
        let $container = $select.closest('td');

        if (categoryId !== '') {
            $.ajax({
                url: "{{ route('attire.assign.newcategory') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: designId,
                    category_id: categoryId
                },
                success: function (response) {
                    if (response.status === true) {
                        const $row = $select.closest('tr');

                        alert("Category updated to: " + response.category_name);

                        const $seoSelect = $row.find('.assignSeoEmployee');
                        if (response.seo_users && response.seo_users.length > 0) {
                            let seoOptions =
                                `<option value="" selected disabled>Select User</option>`;
                            response.seo_users.forEach(function (user) {
                                seoOptions +=
                                    `<option value="${user.id}">${user.name}</option>`;
                            });
                            $seoSelect.html(seoOptions);
                        } else {
                            $seoSelect.html(`<option value="">No SEO Users Available</option>`);
                        }
                        // location.reload();
                    } else {
                        alert("Something went wrong.");
                    }
                },
                error: function () {
                    alert("Server error occurred.");
                }
            });
        }
    });


    $(document).on('change', '.assignSeoEmployee', function () {
        let selectedUserId = $(this).val();
        let designId = $(this).data('category-id');
        //  alert(designId);
        let $select = $(this);

        $.ajax({
            url: "{{ route('attire.assign-seo') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: designId,
                seo_emp_id: selectedUserId
            },
            success: function (response) {
                if (response.status === true) {
                    $select.addClass("border-success");
                    setTimeout(() => $select.removeClass("border-success"), 2000);
                    alert("Assign successfully")
                    // location.reload();
                } else {
                    alert(response.error || 'Something went wrong.');
                }
            },
            error: function () {
                alert('Server error occurred');
            }
        });
    });


    $(document).ready(function () {
        // $('#dynamic_form').on('submit', async function(event) {
        //     event.preventDefault();
        //
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        //         }
        //     });
        //
        //     const formData = new FormData(this);
        //     const base64Preview = formData.get('preview_url'); // ✅ Get the Base64 string
        //
        //     if (!base64Preview) {
        //         alert('Preview image is missing!');
        //         return;
        //     }
        //
        //     try {
        //         // Generate watermark from the Base64 image
        //         const thumbnail = await watermarkBase64(base64Preview);
        //
        //         // Add watermark image to form data
        //         formData.append('thumbnail_url', thumbnail);
        //
        //         // ✅ AJAX call
        //         $.ajax({
        //             url: 'create_attire',
        //             type: 'POST',
        //             data: formData,
        //             beforeSend: function() {
        //                 $("#main_loading_screen").show();
        //             },
        //             success: function(data) {
        //                 $("#main_loading_screen").hide();
        //
        //                 if (data.error) {
        //                     $('#result').html(
        //                         `<div class="alert alert-danger">${data.error.message}</div>`
        //                     );
        //                 } else {
        //                     $('#result').html(
        //                         `<div class="alert alert-success">${data.success}</div>`
        //                     );
        //                     location.reload();
        //                 }
        //             },
        //             error: function(xhr, status, error) {
        //                 alert('Error: ' + error);
        //                 $("#main_loading_screen").hide();
        //             },
        //             cache: false,
        //             contentType: false,
        //             processData: false,
        //         });
        //     } catch (err) {
        //         console.error('Watermark generation failed:', err);
        //         alert('Failed to generate watermark.');
        //     }
        // });


        // async function watermarkBase64(base64Image) {
        //     return new Promise((resolve, reject) => {
        //         const img = new Image();
        //         img.crossOrigin = 'anonymous';
        //         img.src = base64Image;
        //         img.onload = () => {
        //             const scale = 0.9;
        //             const canvas = document.createElement('canvas');
        //             canvas.width = img.width * scale;
        //             canvas.height = img.height * scale;
        //             const ctx = canvas.getContext('2d');
        //             if (!ctx) return reject('No canvas context');
        //
        //             // Background white
        //             ctx.fillStyle = 'white';
        //             ctx.fillRect(0, 0, canvas.width, canvas.height);
        //
        //             // Draw image scaled down
        //             ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        //
        //             // Watermark settings
        //             const text = 'CraftyArt';
        //             const fontSize = Math.floor(canvas.width / 23);
        //             ctx.font = `bold ${fontSize}px Arial`;
        //             ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
        //             ctx.textAlign = 'center';
        //             ctx.textBaseline = 'middle';
        //
        //             const textMetrics = ctx.measureText(text);
        //             const textWidth = textMetrics.width;
        //             const textHeight = fontSize * 1.2;
        //             const angle = -30 * (Math.PI / 180);
        //             const spacingX = textWidth * 1.6;
        //             const spacingY = textHeight * 3;
        //
        //             ctx.save();
        //             ctx.translate(canvas.width / 2, canvas.height / 2);
        //             ctx.rotate(angle);
        //             ctx.translate(-canvas.width / 2, -canvas.height / 2);
        //
        //             for (let x = -canvas.width; x < canvas.width * 2; x += spacingX) {
        //                 for (let y = -canvas.height; y < canvas.height * 2; y += spacingY) {
        //                     const offsetX = (y / spacingY) % 2 ? spacingX / 2 : 0;
        //                     ctx.fillText(text, x + offsetX, y);
        //                 }
        //             }
        //
        //             ctx.restore();
        //
        //             // 🔹 Reduce JPEG quality to shrink file size
        //             const watermarkedBase64 = canvas.toDataURL('image/jpeg', 0.7); // quality = 70%
        //             resolve(watermarkedBase64);
        //         };
        //         img.onerror = reject;
        //     });
        // }


        function updateCategory(catId, templateId) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
            $.ajax({
                url: "{{ route('update_caricature_category') }}",
                type: 'PUT',
                data: {
                    cat_id: catId,
                    tempId: templateId,
                },
                beforeSend: function () {
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    main_loading_screen.style.display = "block";
                },
                success: function (data) {
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    main_loading_screen.style.display = "none";
                    location.reload();
                },
                error: function (error) {
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    main_loading_screen.style.display = "none";
                    window.alert(error.responseText);
                },
            })
        }

        $('.editable').click(function () {
            $(".categoriesList").hide();
            let id = $(this).data('id');
            var categories = $(`#category-${id}`).val();
            categories = JSON.parse(categories);
            var categorisList = '';
            $.each(categories, function (index, val) {
                categorisList +=
                    `<li data-id="${val.id}" data-temp-id="${id}">${val.category_name}</li>`;
            });
            $(this).next().show();
            $(`#orderlistCategories-${id}`).html(categorisList);
        });

        $(document).on("click", ".categoriesList ul li", function () {
            let templateId = $(this).data('temp-id');
            let catId = $(this).data('id');
            updateCategory(catId, templateId)
            $(".categoriesList").hide();
        })

        $('input').blur(function () {
            var newText = $(this).val();
            // $(this).hide();
            $(this).siblings('label').text(newText).show();
        });

    });

    function notification_click($id) {
        $("#temp_id").val($id);
    }

    function reset_click($id) {
        $("#reset_temp_id").val($id);
    }

    function reset_creation($id) {
        $("#reset_creation_id").val($id);
    }

    function onPremiumChange(id, type) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        let formData = new FormData();
        formData.append('id', id);
        formData.append('type', type);

        $.ajax({
            url: "{{ route('attire.premium.update') }}", // use your correct route
            type: 'POST',
            data: formData,
            beforeSend: function () {
                document.getElementById("main_loading_screen").style.display = "block";
            },
            success: function (data) {
                document.getElementById("main_loading_screen").style.display = "none";
                if (data.error) {
                    alert(data.error);
                }
            },
            error: function (xhr) {
                document.getElementById("main_loading_screen").style.display = "none";
                alert('Something went wrong: ' + xhr.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    function pinned_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('attire.pinned', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    var x = document.getElementById("pinned_label_" + $id);
                    if (x.innerHTML === "TRUE") {
                        x.innerHTML = "FALSE";
                    } else {
                        x.innerHTML = "TRUE";
                    }
                }
            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

    function editor_choice_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('attire.editor.choice', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    var x = document.getElementById("editorchoice_label_" + $id);
                    if (x.innerHTML === "TRUE") {
                        x.innerHTML = "FALSE";
                    } else {
                        x.innerHTML = "TRUE";
                    }
                }
            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
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
        formData.append('type', 'attire');
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
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
            error: function (error) {
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

        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('attire.status', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
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
                    if (x.innerHTML === "Live") {
                        x.innerHTML = "Not Live";
                    } else {
                        x.innerHTML = "Live";
                    }
                }
            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }


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
        var url = "{{ route('attire.delete', ':id') }}";
        url = url.replace(":id", id);
        $.ajax({
            url: url,
            type: 'POST',
            beforeSend: function () {
                $('#delete_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert('error==>' + data.error);
                } else {
                    location.reload();
                }
            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }


    function appSelection() {
        $('#templateModal').modal('show');
    }
</script>
</body>

</html>

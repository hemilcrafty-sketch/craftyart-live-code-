@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container ">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                    <div class="row justify-content-between">
                        <div class="col-md-3 m-1">
                            @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                                <a href="javascript:void(0)" class="btn btn-primary item-form-input"
                                    onclick="openSearchTagModal('add')">
                                    Add Search Tag
                                </a>
                            @endif

                        </div>

                        <div class="col-md-8">
                            @include('partials.filter_form ', [
                                'action' => route('show_search_tag'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Assign To</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($searchTagArray as $searchTag)
                                    <tr>
                                        <td class="table-plus">{{ $searchTag->id }}</td>

                                        <td>{{ $roleManager::getUploaderName($searchTag->emp_id) }}</td>
                                        <td>{{ $searchTag->assignedSeo->name ?? 'N/A' }}</td>

                                        <td class="table-plus">{{ $searchTag->name }}</td>

                                        @if ($searchTag->status == '1')
                                            <td>Active</td>
                                        @else
                                            <td>Disabled</td>
                                        @endif
                                        <td>
                                            <div class="d-flex">

                                                <button class="dropdown-item"
                                                    onclick="openSearchTagModal('edit', {
        id: '{{ $searchTag->id }}',
        name: '{{ $searchTag->name }}',
        status: '{{ $searchTag->status }}',
        seo_emp_id: '{{ $searchTag->seo_emp_id }}'
    })">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>

                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <Button class="dropdown-item"
                                                        onclick="delete_click('{{ $searchTag->id }}')"><i
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
                @include('partials.pagination', ['items' => $searchTagArray])
            </div>
        </div>
    </div>
</div>

<div class="modal fade seo-all-container" id="add_search_tag_model" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="search_tag_modal_title">Add Search Tag</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="search_tag_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="search_tag_id" id="search_tag_id" />

                    <div class="form-group">
                        <h7>Name</h7>
                        <input type="text" class="form-control" placeholder="Search Tag" id="searchTagName"
                            name="name" required />
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

                    <div class="form-group">
                        <h6>Status</h6>
                        <select id="searchTagStatus" class="selectpicker form-control" name="status"
                            data-style="btn-outline-primary">
                            <option value="1">Active</option>
                            <option value="0">Disable</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary btn-block"
                                id="search_tag_submit_btn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function openSearchTagModal(mode, data = {}) {
        $('#search_tag_form')[0].reset();
        $('#search_tag_modal_title').text(mode === 'edit' ? 'Edit Search Tag' : 'Add Search Tag');
        $('#search_tag_submit_btn').text(mode === 'edit' ? 'Update' : 'Save');
        $('#search_tag_method').val(mode === 'edit' ? 'PUT' : 'POST');
        $('#search_tag_form').attr('data-mode', mode);
        $('#assignSubCatSelect').val('').trigger('change');

        if (mode === 'edit' && data) {
            $('#search_tag_id').val(data.id);
            $('#searchTagName').val(data.name);
            $('#searchTagStatus').val(data.status);
            if (data.seo_emp_id) {
                $('#assignSubCatSelect').val(data.seo_emp_id).trigger('change');
            }
        } else {
            $('#search_tag_id').val('');
            $('#searchTagStatus').val('1');
        }

        $('#add_search_tag_model').modal('show');
    }


    $('#search_tag_form').on('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const id = $('#search_tag_id').val();

        if (id) {
            formData.append('id', id); // Include ID if in edit mode
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: 'submit_search_tag',
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
            error: function(error) {
                $('#main_loading_screen').hide();
                alert(error.responseText);
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

        var url = "{{ route('searchTag.delete', ':id') }}";
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

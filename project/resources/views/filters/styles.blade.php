@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
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
                {{-- Filter and Action Row --}}
                <div class="row justify-content-between flex-wrap">
                    <div class="col-md-2 m-1">
                        @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                            <a href="javascript:void(0)" onclick="openAddStyleModal()" class="btn btn-primary item-form-input">
                                Add Style
                            </a>
                        @endif
                    </div>


                    <div class="col-md-8 col-12">
                        @include('partials.filter_form', [
                            'action' => route('show_style'),
                        ])
                    </div>
                </div>

                {{-- Scrollable Table --}}
                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th style="width:150px">Name</th>
                                    <th style="width:150px">ID Name</th>
                                    <th style="width:150px">Status</th>
                                    <th style="width:150px">User</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($styleArray as $style)
                                    <tr>
                                        <td class="table-plus">{{ $style->id }}</td>
                                        <td class="table-plus">{{ $style->name }}</td>
                                        <td class="table-plus">{{ $style->id_name ?? '' }}</td>
                                        <td>{{ $style->status == '1' ? 'Active' : 'Disabled' }}</td>
                                        <td>{{ $roleManager::getUploaderName($style->emp_id) }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item w-50"
                                                    onclick="openEditStyleModal('{{ $style->id }}', '{{ $style->name }}', '{{ $style->status }}', '{{ $style->id_name }}')">
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
                @include('partials.pagination', ['items' => $styleArray])
            </div>
        </div>
    </div>
</div>

<div class="modal fade seo-all-container" id="add_style_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="style_modal_title">Add Style</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="style_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="style_id" value="">

                    <div class="form-group">
                        <h7>Name</h7>
                        <input type="text" class="form-control" id="styleName" name="name"
                            placeholder="Style Name" required />
                    </div>

                    <div class="form-group">
                        <h7>ID Name</h7>
                        <input type="text" class="form-control" id="styleIDName" name="id_name" placeholder="ID Name"
                            required />
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <select id="styleStatus" class="form-control" name="status">
                            <option value="1">Active</option>
                            <option value="0">Disable</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" id="style_submit_btn" class="btn btn-primary btn-block">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    // Add New Style Click
    function openAddStyleModal() {
        $('#style_modal_title').text('Add Style');
        $('#style_submit_btn').text('Save');
        $('#style_form')[0].reset();
        $('#style_id').val('');
        $('#style_form').attr('data-mode', 'add');

        $('#add_style_model').modal('show');
    }

    // Edit Style Click
    function openEditStyleModal(id, name, status, id_name) {
        $('#style_modal_title').text('Edit Style');
        $('#style_submit_btn').text('Update');

        $('#style_id').val(id);
        $('#styleName').val(name);
        $('#styleStatus').val(status);
        $('#styleIDName').val(id_name);

        $('#style_form').attr('data-mode', 'edit');

        $('#add_style_model').modal('show');
    }


    $('#style_form').on('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const id = $('#style_id').val();

        if (id) {
            formData.append('id', id);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: 'submit_style',
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

        var url = "{{ route('style.delete', ':id') }}";
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

    const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
    $("#styleName").on("input", function() {
        const titleString = toTitleCase($(this).val());
        $("#styleIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });

    $(document).on("keypress", "#editName", function() {
        const titleString = toTitleCase($(this).val());
        $("#editIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
        $(this).val(titleString);
    });
</script>

</body>

</html>

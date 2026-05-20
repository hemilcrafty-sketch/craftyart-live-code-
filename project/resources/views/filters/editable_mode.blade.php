   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                        <div class="pd-20">
                            <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                                data-target="#add_theme_model" type="button">
                                Add Title </a>
                        </div>
                    @endif

                    <table class="table table-striped mt-2" data-ordering="false" data-paging="false">
                        <form method="GET" action="{{ route('create_editable_mode') }}" id="filter-form">
                            <div class="form-row">
                                <div class="col">
                                    <input type="text" name="query" class="form-control" placeholder="Search..."
                                        value="{{ request('query') }}">
                                </div>
                                <div class="col">
                                    <select name="per_page" class="form-control">
                                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10
                                        </option>
                                        <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20
                                        </option>
                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50
                                        </option>
                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100
                                        </option>
                                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500
                                        </option>
                                        <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>
                                            1000</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </div>
                        </form>
                        <thead>
                            <tr>
                                <th class="datatable-nosort"><a href="javascript:void(0);">Id
                                        @php
                                            $sortOrderById = 'desc';
                                            if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                $sortOrderById =
                                                    request('sort_order', 'desc') == 'asc' ? 'asc' : 'desc';
                                            }
                                            if (request('sort_by') != null && request('sort_by') != 'id') {
                                                $sortOrderById = '';
                                            }
                                        @endphp
                                        <span class="sort-arrow sort-asc {{ $sortOrderById == 'asc' ? 'active' : '' }}"
                                            onclick="sortTable(event,'id','asc')"></span>
                                        <span
                                            class="sort-arrow sort-desc {{ $sortOrderById == 'desc' ? 'active' : '' }}"
                                            onclick="sortTable(event,'id','desc')"></span>
                                    </a></th>
                                <th class="datatable-nosort"><a href="javascript:void(0);">
                                        Name
                                        @php
                                            $sortOrderByName = '';
                                            if (request('sort_by') == 'name') {
                                                $sortOrderByName =
                                                    request('sort_order', 'desc') == 'asc' ? 'asc' : 'desc';
                                            }
                                        @endphp
                                        <span
                                            class="sort-arrow sort-asc {{ $sortOrderByName == 'asc' ? 'active' : '' }}"
                                            onclick="sortTable(event,'name','asc')"></span>
                                        <span
                                            class="sort-arrow sort-desc {{ $sortOrderByName == 'desc' ? 'active' : '' }}"
                                            onclick="sortTable(event,'name','desc')"></span>
                                    </a></th>
                                <th class="datatable-nosort">Brand ID</th>
                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                    <th class="datatable-nosort">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($titleArray as $theme)
                                <tr>
                                    <td class="table-plus">{{ $theme->id }}</td>

                                    <td class="table-plus">{{ $theme->name }}</td>

                                    <td class="table-plus">{{ $theme->brand_id }}</td>

                                    @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                        <td>
                                            <Button class="dropdown-item"
                                                onclick="edit_click('{{ $theme->id }}', '{{ $theme->name }}', '{{ $theme->brand_id }}')"
                                                data-backdrop="static" data-toggle="modal"
                                                data-target="#edit_theme_model"><i class="dw dw-edit2"></i>
                                                Edit</Button>

                                            <Button class="dropdown-item"
                                                onclick="set_delete_id('{{ $theme->id }}')" data-backdrop="static"
                                                data-toggle="modal" data-target="#delete_model"><i
                                                    class="dw dw-delete-3"></i>Delete</Button>

                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_theme_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Theme</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_theme_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h7>Name</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Theme Name" name="name"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Brand ID</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Brand ID" name="brand_id" />
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

<div class="modal fade" id="edit_theme_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Theme</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="update_model">
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
    function edit_click($id, $theme_name, $brand_id) {
        $("#update_model").empty();
        html =
            '<form method="post" id="edit_theme_form" enctype="multipart/form-data"> @csrf <input class="form-control" type="textname" name="theme_id" value="' +
            $id +
            '" style="display: none"/> <div class="form-group"> <h7>Theme Name</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Theme Name" value="' +
            $theme_name +
            '" name="name" required="" /> </div> </div> <div class="form-group"> <h7>Brand ID</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Brand ID" value="' +
            $brand_id +
            '" name="brand_id"/> </div> </div> <div class="row"> <div class="col-sm-12"> <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button> </div> </div> </form>';

        $("#update_model").append(html);

    }

    function set_delete_id($id) {
        $("#delete_id").val($id);
    }

    $('#add_theme_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'submit_editable_mode',
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
    });

    $(document).on('click', '#update_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("edit_theme_form"));
        var status = formData.get("theme_id");


        var url = "{{ route('editable_mode.update', ':status') }}";
        url = url.replace(":status", status);

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

    function delete_click() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        id = $("#delete_id").val();

        var url = "{{ route('editable_mode.delete', ':id') }}";
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

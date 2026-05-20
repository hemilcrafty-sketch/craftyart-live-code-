   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div class="pd-20">

                        <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                            data-target="#add_cat_model" type="button">
                            Add Sub Category </a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subCatArray as $subCat)
                                <tr>
                                    <td class="table-plus">{{ $subCat->id }}</td>

                                    <td class="table-plus">{{ $subCat->name }}</td>

                                    @if ($subCat->status == '1')
                                        <td>Active</td>
                                    @else
                                        <td>Disabled</td>
                                    @endif

                                    <td>
                                        <Button class="dropdown-item"
                                            onclick="edit_click('{{ $subCat->id }}', '{{ $subCat->name }}', '{{ $subCat->status }}')"
                                            data-backdrop="static" data-toggle="modal" data-target="#edit_cat_model"><i
                                                class="dw dw-edit2"></i> Edit</Button>

                                        <Button class="dropdown-item" onclick="delete_click('{{ $subCat->id }}')"><i
                                                class="dw dw-delete-3"></i> Delete</Button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_cat_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Sub Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_cat_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h7>Name</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Sub Category Name" name="name"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select id="status" class="selectpicker form-control" data-style="btn-outline-primary"
                                name="status">
                                <option value="1">Active</option>
                                <option value="0">Disable</option>
                            </select>
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

<div class="modal fade" id="edit_cat_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Sub Category</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="update_model">
            </div>

        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    function edit_click($id, $cat_name, $status) {
        $("#update_model").empty();
        html =
            '<form method="post" id="edit_cat_form" enctype="multipart/form-data"> @csrf <!-- {{ $sub_cat_id = 1 }} --><input class="form-control" type="textname" name="sub_cat_id" value="' +
            $id +
            '" style="display: none"/> <div class="form-group"> <h7>Name</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Sub Category Name" value="' +
            $cat_name +
            '" name="name" required="" /> </div> </div> <div class="form-group"> <h6>Status</h6> <div class="col-sm-20"> <select id="status" class="selectpicker form-control" data-style="btn-outline-primary" name="status"> <option value="1">Active</option> <option value="0">Disable</option> </select> </div> </div> <div class="row"> <div class="col-sm-12"> <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button> </div> </div> </form>';

        $("#update_model").append(html);

    }

    $('#add_cat_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'submit_sub_cat',
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

        var formData = new FormData(document.getElementById("edit_cat_form"));
        var status = formData.get("sub_cat_id");


        var url = "{{ route('subCat.update', ':status') }}";
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

    function delete_click(id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('subCat.delete', ':id') }}";
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

</body>

</html>

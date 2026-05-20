 
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
                            data-target="#add_model" type="button">
                            Add</a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th class="datatable-nosort">Image</th>
                                <th>Type</th>
                                <th>Is Banner</th>
                                <th>Can Cancle</th>
                                <th>Keyword</th>
                                <th>Link</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas['list'] as $data)
                                <tr>
                                    <td class="table-plus">{{ $data->id }}</td>
                                    <td> <img src="{{ config('filesystems.storage_url') }}{{ $data->image }}"
                                            style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                    </td>
                                    <td>{{ $helperController::getInAppType($data->open_type) }}</td>
                                    <td>{{ $helperController::isBanner($data->is_banner) }}</td>
                                    <td>{{ $helperController::canCancle($data->can_cancle) }}</td>
                                    <td>{{ $data->keyword }}</td>
                                    <td>{{ $data->link }}</td>
                                    <td>{{ $helperController::dateRange($data->start_date, $data->end_date) }}</td>
                                    <td>{{ $helperController::checkStatus($data->status) }}</td>
                                    <td>
                                        <Button class="dropdown-item" onclick="edit_click('{{ $data }}')"
                                            data-backdrop="static" data-toggle="modal" data-target="#edit_model"><i
                                                class="dw dw-edit2"></i> Edit</Button>

                                        <Button class="dropdown-item" onclick="delete_click('{{ $data->id }}')"><i
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

<div class="modal fade" id="add_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>Image</h6>
                        <div class="input-group custom">
                            <input type="file" class="form-control" name="image" accept="image/png, image/jpeg"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Type</h6>
                        <div class="col-sm-20">
                            <select id="open_type_0" onchange="selectChangeFunc('0');" class="selectpicker form-control"
                                data-style="btn-outline-primary" name="open_type">
                                @foreach ($datas['type'] as $type)
                                    <option value="{{ $type->type }}">{{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="keyword_field_0">
                        <h6>Keyword</h6>
                        <div class="input-group custom">
                            <input id="keyword_0" type="text" class="form-control" name="keyword" />
                        </div>
                    </div>

                    <div class="form-group" id="link_field_0">
                        <h6>Link</h6>
                        <div class="input-group custom">
                            <input id="link_0" type="text" class="form-control" name="link" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Is Banner</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" name="is_banner">
                                <option value="1">True</option>
                                <option value="0">False</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Can Cancle</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="can_cancle">
                                <option value="1">True</option>
                                <option value="0">False</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Select Date Range</h6>
                        <input class="form-control datetimepicker-range" placeholder="Select Date" type="text"
                            name="date_range" readonly>
                    </div>


                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
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

<div class="modal fade" id="edit_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="edit_form" enctype="multipart/form-data">
                    @csrf
                    <input id="edit_id" class="form-control" type="textname" name="edit_id"
                        style="display: none" />
                    <div class="form-group">
                        <h6>Image</h6>
                        <div class="input-group custom">
                            <input type="file" class="form-control" name="image"
                                accept="image/png, image/jpeg" />
                        </div>

                        <div class="form-group">
                            <div class="input-group custom">
                                <img id="image_src"
                                    style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                            </div>
                        </div>

                        <div class="form-group">
                            <h6>Type</h6>
                            <div class="col-sm-20">
                                <select id="open_type_1" onchange="selectChangeFunc('1');"
                                    class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="open_type">
                                    @foreach ($datas['type'] as $type)
                                        <option value="{{ $type->type }}">{{ $type->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="keyword_field_1">
                            <h6>Keyword</h6>
                            <div class="input-group custom">
                                <input id="keyword_1" type="text" class="form-control" name="keyword" />
                            </div>
                        </div>

                        <div class="form-group" id="link_field_1">
                            <h6>Link</h6>
                            <div class="input-group custom">
                                <input id="link_1" type="text" class="form-control" name="link" />
                            </div>
                        </div>

                        <div class="form-group">
                            <h6>Is Banner</h6>
                            <div class="col-sm-20">
                                <select id="is_banner" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="is_banner">
                                    <option value="1">True</option>
                                    <option value="0">False</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <h6>Can Cancle</h6>
                            <div class="col-sm-20">
                                <select id="can_cancle" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="can_cancle">
                                    <option value="1">True</option>
                                    <option value="0">False</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <h6>Select Date Range</h6>
                            <input id="date_range" class="form-control datetimepicker-range"
                                placeholder="Select Date" type="text" name="date_range" readonly>
                        </div>


                        <div class="form-group">
                            <h6>Status</h6>
                            <div class="col-sm-20">
                                <select id="status" class="selectpicker form-control"
                                    data-style="btn-outline-primary" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary btn-block"
                                    id="update_click">Update</button>
                            </div>
                        </div>
                </form>
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function edit_click(data) {
        var obj = JSON.parse(data);
        $("#image_src").attr('src', obj.image);
        $("#edit_id").val(obj.id);
        $("#open_type_1").val(obj.open_type);
        $("#keyword_1").val(obj.keyword);
        $("#link_1").val(obj.link);
        $("#is_banner").val(obj.is_banner);
        $("#can_cancle").val(obj.can_cancle);
        if (obj.start_date === null || obj.start_date === "") {
            $("#date_range").val();
        } else {
            $("#date_range").val(obj.start_date + " - " + obj.end_date);
        }
        $("#status").val(obj.status);

        selectChangeFunc('1');
    }

    function selectChangeFunc(id) {
        type = $('#open_type_' + id).val();
        if (type === '1') {
            $('#keyword_' + id).removeAttr('required');
            var x1 = document.getElementById("keyword_field_" + id);
            x1.style.display = "none";

            $('#link_' + id).removeAttr('required');
            var x1 = document.getElementById("link_field_" + id);
            x1.style.display = "none";

        } else if (type === '2') {
            $('#keyword_' + id).attr('required', '');
            var x = document.getElementById("keyword_field_" + id);
            x.style.display = "block";

            $('#link_' + id).removeAttr('required');
            var x1 = document.getElementById("link_field_" + id);
            x1.style.display = "none";
        } else {
            $('#link_' + id).attr('required', '');
            var x = document.getElementById("link_field_" + id);
            x.style.display = "block";

            $('#keyword_' + id).removeAttr('required');
            var x1 = document.getElementById("keyword_field_" + id);
            x1.style.display = "none";
        }
    }

    $('#add_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'submit_message',
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

        type = $('#open_type_1').val();
        if (type === '2') {
            var field = $('#keyword_1').val();
            if (field === null || field === "") {
                alert("Please Enter keyword");
                return;
            }
        }
        if (type === '3' || type === '4') {
            var field = $('#link_1').val();
            if (field === null || field === "") {
                alert("Please Enter Link");
                return;
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("edit_form"));
        var status = formData.get("edit_id");


        var url = "{{ route('message.update', ':status') }}";
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

        var url = "{{ route('message.delete', ':id') }}";
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

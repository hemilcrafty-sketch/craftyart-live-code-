   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">
                    <span id="result"></span>
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                                        data-target="#add_formate_model" type="button">
                                        Add Formate </a>
                                </div>
                            </div>

                            @php
                                $isAdmin = Auth::user()->user_type;
                            @endphp
                            <input class="d-none" type="text" id="isAdmin" value="{{ $isAdmin }}">
                            @if ($isAdmin == 1)
                                <div class="col-sm-12 col-md-3">
                                    <div class="pd-20">
                                        <button id="excel_btn" class="btn btn-secondary buttons-excel buttons-html5"
                                            type="button"><span>Excel</span></button>
                                    </div>
                                </div>
                            @endif

                            @if ($roleManager::isAdmin(Auth::user()->user_type)))
                                <div class="col-sm-12 col-md-6">
                                @else
                                    <div class="col-sm-12 col-md-6">
                                        @if (!$roleManager::isAdmin(Auth::user()->user_type))
                                            <div class="col-sm-12 col-md-9">
                                        @endif
                            @endif
                            <div class="pt-20">
                                <form action="{{ route('formates.index') }}" method="GET">
                                    <div class="form-group">
                                        <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                            <label>Search:<input type="text" class="form-control" name="query"
                                                    placeholder="Search here....."
                                                    value="{{ request()->input('query') }}"></label> <button
                                                type="submit" class="btn btn-primary">Search</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                    <form id="create_size_action" action="{{ route('sizes.create') }}" method="GET"
                        style="display: none;">
                        <input type="text" id="passingAppId" name="passingAppId">
                        @csrf
                    </form>

                    <div class="col-sm-12 table-responsive">
                        <table id="temp_table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Format Name</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody id="formate_table">
                                @foreach ($formates as $formate)
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $formate->id }}</td>
                                        <td class="table-plus">{{ $formate->name }}</td>
                                        <td class="table-plus">{{ $formate->status ? 'Active' : 'UnActive' }}</td>
                                        <td>
                                            <Button class="dropdown-item btn-edit" data-id="{{ $formate->id }}"
                                                data-formate="{{ $formate->name }}" data-backdrop="static"
                                                data-toggle="modal" data-target="#edit_formate_model"><i
                                                    class="dw dw-edit2"></i> Edit</Button>
                                            <button class="dropdown-item"
                                                onclick="delete_click('{{ $formate->id }}')"><i
                                                    class="dw dw-delete-3"></i> Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                <ul class="pagination">
                                    {{ $formates->appends(request()->input())->links('pagination::bootstrap-4') }}
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
<div class="modal fade" id="send_notification_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Send Notification</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="notification_model">

                <form method="post" id="notification_form" enctype="multipart/form-data">
                    @csrf
                    <input id="temp_id" class="form-control" type="textname" name="temp_id"
                        style="display: none;" />
                    <div class="form-group">
                        <h7>Title</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Title" name="title"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Description</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Description" name="description"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Large Icon</h7>
                        <div class="input-group custom">
                            <input type="file" class="form-control" name="large_icon" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Big Picture</h7>
                        <div class="input-group custom">
                            <input type="file" class="form-control" name="big_picture" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Schedule</h7>
                        <div class="input-group custom">
                            <input class="form-control datetimepicker" placeholder="Select Date & Time"
                                type="text" name="schedule" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary btn-block"
                                id="send_notification_click">Send</button>
                        </div>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="reset_date_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Reset Date</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you reset the date?</p>
            </div>

            <input class="form-control" type="textname" id="reset_temp_id" name="reset_temp_id"
                style="display: none">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="reset_date_click" class="btn btn-primary">Yes, Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reset_creation_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Reset Creation Date</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you reset the creation date?</p>
            </div>

            <input class="form-control" type="textname" id="reset_creation_id" name="reset_creation_id"
                style="display: none">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="reset_creation_click" class="btn btn-primary">Yes, Reset</button>
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

<div class="modal fade" id="add_formate_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Formate</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="post" id="addFormateForm">
                    @csrf
                    <div class="form-group">
                        <h7>Name</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Enter Formate Name"
                                name="name" required="" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" id="btnSubmitForm" type="button"
                                name="submit" value="Submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_formate_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Formate</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="update_model">
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        jQuery.noConflict()

        $(document).on("click", "#update_click", function() {
            count = 0;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
            let id = $("input[name='formate_id']").val();
            var url = "{{ route('formates.update', ['formate' => ':id']) }}".replace(':id', id);

            $.ajax({
                url: url,
                type: 'PUT',
                dataType: 'json',
                data: {
                    "name": $("input[name='formate_name']").val()
                },
                beforeSend: function() {
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    main_loading_screen.style.display = "block";
                },
                success: function(data) {
                    if (data.error) {
                        $('#result').html('<div class="alert alert-danger">' + data.error +
                            '</div>');
                    } else {
                        $('#result').html('<div class="alert alert-success">' + data
                            .success + '</div>');
                        $("#edit_formate_model").removeClass("show");
                        $("#edit_formate_model").hide();
                        $(".modal-backdrop.fade.show").remove();
                        setTimeout(function() {
                            $('#result').html('');
                            location.reload();
                        }, 1000);
                    }
                    hideFields();
                },
                error: function(error) {
                    hideFields();
                    window.alert(error.responseText);
                }
            });

        });

        $(document).on("click", "#btnSubmitForm", function() {
            console.log("fdsfsdf");
            count = 0;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });

            $.ajax({
                url: "{{ route('formates.store') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    "name": $("input[name='name']").val()
                },
                beforeSend: function() {
                    var main_loading_screen = document.getElementById("main_loading_screen");
                    main_loading_screen.style.display = "block";
                },
                success: function(data) {
                    if (data.error) {
                        $('#result').html('<div class="alert alert-danger">' + data.error +
                            '</div>');
                    } else {
                        $('#result').html('<div class="alert alert-success">' + data
                            .success + '</div>');
                        $("#add_formate_model").removeClass("show");
                        $("#add_formate_model").hide();
                        $(".modal-backdrop.fade.show").remove();
                    }
                    hideFields();
                    setTimeout(function() {
                        $('#result').html('');
                    }, 3000);

                },
                error: function(error) {
                    hideFields();
                    window.alert(error.responseText);
                }
            });
        });

        function hideFields() {
            $('#addFormateForm')[0].reset();
            var main_loading_screen = document.getElementById("main_loading_screen");
            main_loading_screen.style.display = "none";
        }
    })

    function notification_click($id) {
        $("#temp_id").val($id);
    }

    function reset_click($id) {
        $("#reset_temp_id").val($id);
    }

    function reset_creation($id) {
        $("#reset_creation_id").val($id);
    }
    $(document).on("click", ".btn-edit", function() {
        let id = $(this).data('id');
        let formate = $(this).data('formate');
        $("#update_model").empty();
        html = `<form method="post" id="edit_formate_form">
                            @csrf
                            <input class="form-control" type="textname" name="formate_id" value="${id}" style="display: none"/>
                            <div class="form-group">
                                <h7>Formate Name</h7>
                                <div class="input-group custom">
                                    <input type="text" class="form-control" placeholder="Enter Formate Name" value="${formate}" name="formate_name" required="" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button>
                                </div>
                            </div>
                        </form>`;

        $("#update_model").append(html);
    })

    function premium_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('temp.premium', ':status') }}";
        url = url.replace(":status", status);

        var formData = new FormData();
        formData.append('id', $id);
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
                    var x = document.getElementById("premium_label_" + $id);
                    if (x.innerHTML === "TRUE") {
                        x.innerHTML = "FALSE";
                    } else {
                        x.innerHTML = "TRUE";
                    }
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

    function status_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('temp.status', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
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
                    var x = document.getElementById("status_label_" + $id);
                    if (x.innerHTML === "Live") {
                        x.innerHTML = "Not Live";
                    } else {
                        x.innerHTML = "Live";
                    }
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

    $(document).on('click', '#send_notification_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("notification_form"));
        var status = formData.get("temp_id");
        var url = "{{ route('poster.notification', ':status') }}";
        url = url.replace(":status", status);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#send_notification_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
                } else {
                    window.alert(data.success);
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

    $(document).on('click', '#reset_date_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $("#reset_temp_id").val();
        var url = "{{ route('reset.date', ':status') }}";

        url = url.replace(":status", status);

        var formData = new FormData();
        formData.append('id', status);


        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#reset_date_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
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

    $(document).on('click', '#reset_creation_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $("#reset_creation_id").val();
        var url = "{{ route('reset.creation', ':status') }}";
        url = url.replace(":status", status);

        var formData = new FormData();
        formData.append('id', status);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#reset_creation_model').modal('toggle');
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    window.alert(data.error);
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

    $(document).ready(function() {
        $('#application_id').change(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var value = $(this).val();

            $.ajax({
                url: "{{ route('item.custom_data') }}",
                method: "POST",
                data: {
                    value: value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    $('#item_table').html(result);
                },
                error: function(result) {
                    window.alert(result.responseText);
                }
            })
        });
    });

    function appSelection() {
        $('#passingAppId').val('1');
        $('#create_size_action').submit();
    }

    function set_delete_id($id) {
        $("#delete_id").val($id);
    }

    function delete_click(id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('formates.destroy', ':id') }}";
        url = url.replace(":id", id);

        $.ajax({
            url: url,
            type: 'DELETE',
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

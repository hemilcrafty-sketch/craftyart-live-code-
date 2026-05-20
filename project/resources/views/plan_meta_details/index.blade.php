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
                                        data-target="#add_meta_plan_details_model" type="button">
                                        Add Plan Meta Detail </a>
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
                                <form action="{{ route('planMetaFeatures.index') }}" method="GET">
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

                    <form id="create_relegion_action" action="{{ route('planMetaFeatures.create') }}" method="GET"
                        style="display: none;">
                        <input type="text" id="passingAppId" name="passingAppId">
                        @csrf
                    </form>

                    <div class="col-sm-12 table-responsive">
                        <table id="relegion_table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Meta Feature Key</th>
                                    <th>Meta Feature Value </th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody id="relegion_table">
                                @foreach ($planMetaDetails as $planMetaDetail)
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $planMetaDetail->id }}</td>
                                        <td class="table-plus">{{ $planMetaDetail->meta_feature_key }}</td>
                                        <td class="table-plus">{{ $planMetaDetail->meta_feature_value }}</td>
                                        <td>
                                            <Button class="dropdown-item btn-edit" data-id="{{ $planMetaDetail->id }}"
                                                data-backdrop="static" data-toggle="modal"
                                                data-target="#edit_meta_plan_detail_model"><i class="dw dw-edit2"></i>
                                                Edit</Button>
                                            <button class="dropdown-item"
                                                onclick="delete_click('{{ $planMetaDetail->id }}')"><i
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
                                    {{ $planMetaDetails->appends(request()->input())->links('pagination::bootstrap-4') }}
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

<div class="modal fade" id="add_meta_plan_details_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Plan Meta Feature</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="post" id="addForm">
                    @csrf
                    <div class="form-group">
                        <h7>Meta Feature Key</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Enter Meta Feature Key"
                                name="meta_feature_key" required="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <h7>Meta Feature Value</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Enter Meta Feature Value"
                                name="meta_feature_value" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Plan</h7>
                        <div class="input-group custom">
                            <select name="plan_id[]" id="planId" class="custom-select2 form-control"
                                multiple="multiple" data-style="btn-outline-primary">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <h7>Feature</h7>
                        <div class="input-group custom">
                            <select class="custom-select2 form-control" multiple="multiple"
                                data-style="btn-outline-primary" name="feature_ids[]" id="featureId">
                                @foreach ($features as $feature)
                                    <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                                @endforeach
                            </select>
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

<div class="modal fade" id="edit_meta_plan_detail_model" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Plan Meta Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="update_model">

            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $.noConflict();
</script>
<script>
    $(document).on("click", "#update_click", function() {
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var id = $("input[name='plan_meta_detail_id']").val();
        var url = "{{ route('planMetaFeatures.update', ['planMetaFeature' => ':id']) }}".replace(':id', id);
        $.ajax({
            url: url,
            type: 'PUT',
            dataType: 'json',
            data: {
                "meta_feature_key": $("input[name='exist_meta_feature_key']").val(),
                "meta_feature_value": $("input[name='exist_meta_feature_value']").val(),
                "plan_id": $("#selectedPlanId").val(),
                "feature_id": $("#selectedFeatureId").val(),
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                if (data.error) {
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                    '</div>');
                    $("#edit_meta_plan_detail_model").removeClass("show");
                    $(".modal-backdrop.fade.show").remove();
                    $("#edit_meta_plan_detail_model").hide();
                    location.reload();
                }
                // hideFields();
                setTimeout(function() {
                    $('#result').html('');
                }, 3000);

            },
            error: function(error) {
                // hideFields();
                window.alert(error.responseText);
            }
        });

    });
    $(document).ready(function() {

        $(document).on("click", "#btnSubmitForm", function() {
            count = 0;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });

            $.ajax({
                url: "{{ route('planMetaFeatures.store') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    "meta_feature_key": $("input[name='meta_feature_key']").val(),
                    "meta_feature_value": $("input[name='meta_feature_value']").val(),
                    "plan_id": $("#planId").val(),
                    "feature_id": $("#featureId").val(),
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
                        $("#add_meta_plan_details_model").removeClass("show");
                        $("#add_meta_plan_details_model").hide();
                        $(".modal-backdrop.fade.show").remove();
                        location.reload();
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
            $('#addForm')[0].reset();
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

    function destroySelect2() {
        $("#update_model").find('select').each(function() {
            $(this).select2('destroy');
        });
    }

    function initializeSelect2() {
        $("#update_model").find('select').each(function() {
            $(this).select2();
        });
    }

    $(document).on("click", ".btn-edit", function(e) {
        let id = $(this).data('id');
        $("#update_model").empty();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var url = "{{ route('planMetaFeatures.edit', ['planMetaFeature' => ':id']) }}".replace(':id', id);
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && data.view) {
                    $("#update_model").html(data.view);
                    initializeSelect2();
                } else {
                    console.error('Error: Invalid AJAX response');
                }
            },
            error: function(error) {}
        });
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

    function set_delete_id($id) {
        $("#delete_id").val($id);
    }

    function delete_click(id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('planMetaFeatures.destroy', ':id') }}";
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

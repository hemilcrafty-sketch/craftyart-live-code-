@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', 'App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3 m-1">
                            @if ($roleManager::onlySeoAccess(Auth::user()->user_type) && !$roleManager::isSeoManager(Auth::user()->user_type))
                                <a class="btn btn-primary item-form-input" href="create_cat" role="button"> Add New
                                    Category </a>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('show_cat'),
                            ])
                        </div>
                    </div>


                    <div class="scroll-wrapper table-responsive tableFixHead">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Category Id</th>
                                    <th>User</th>
                                    <th>Assign To</th>
                                    <th>App Name</th>
                                    <th>Category Name</th>
                                    <th>ID Name</th>
                                    <th class="datatable-nosort">Category Thumb</th>
                                    <th>Sequence Number</th>
                                    <th>No Index</th>
                                    @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                        <th>IMP</th>
                                    @endif
                                    <th>Status</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($catArray as $cat)
                                    <tr>
                                        <td class="table-plus">{{ $cat->id }}</td>

                                        <td>{{ $roleManager::getUploaderName($cat->emp_id) }}</td>

                                        <td>{{ $cat->assignedSeo->name ?? 'N/A' }}</td>
                                        <td>{{ $helperController::getAppName($cat->app_id) }}</td>


                                        <td><a target="_blank"
                                                href="show_item?cat={{ $cat->id }}">{{ $cat->category_name }}</a>
                                        </td>

                                        <td>{{ $cat->id_name }}</td>

                                        <td><img src="{{ config('filesystems.storage_url') }}{{ $cat->category_thumb }}"
                                                style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                        </td>

                                        <td>{{ $cat->sequence_number }}</td>

                                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                            @if ($cat->no_index == '1')
                                                <td><label id="noindex_label_{{ $cat->id }}"
                                                        style="display: none;">TRUE</label><Button style="border: none"
                                                        onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                            type="checkbox" checked class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @else
                                                <td><label id="noindex_label_{{ $cat->id }}"
                                                        style="display: none;">FALSE</label><Button style="border: none"
                                                        onclick="noindex_click(this, '{{ $cat->id }}')"><input
                                                            type="checkbox" class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @endif
                                        @else
                                            @if ($cat->no_index == '1')
                                                <td>True</td>
                                            @else
                                                <td>False</td>
                                            @endif
                                        @endif

                                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                            @if ($cat->imp == '1')
                                                <td><label id="imp_label_{{ $cat->id }}"
                                                        style="display: none;">TRUE</label><Button style="border: none"
                                                        onclick="imp_click('{{ $cat->id }}')"><input
                                                            type="checkbox" checked class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @else
                                                <td><label id="imp_label_{{ $cat->id }}"
                                                        style="display: none;">FALSE</label><Button style="border: none"
                                                        onclick="imp_click('{{ $cat->id }}')"><input
                                                            type="checkbox" class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @endif
                                        @endif

                                        @if ($cat->status == '1')
                                            <td>LIVE</td>
                                        @else
                                            <td>NOT LIVE</td>
                                        @endif

                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                    href="#" role="button" data-toggle="dropdown">
                                                    <i class="dw dw-more"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                                    <Button class="dropdown-item"
                                                        onclick="notification_click('{{ $cat->id }}')"
                                                        data-backdrop="static" data-toggle="modal"
                                                        data-target="#send_notification_model"><i
                                                            class="dw dw-notification1"></i>Send
                                                        Notification</Button>
                                                    <a class="dropdown-item" href="edit_cat/{{ $cat->id }}"><i
                                                            class="dw dw-edit2"></i> Edit</a>
                                                    @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                        <a class="dropdown-item"
                                                            href="delete_cat/{{ $cat->id }}"><i
                                                                class="dw dw-delete-3"></i> Delete</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <div class="modal fade" id="Medium-modal" tabindex="-1" role="dialog"
                                            aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="myLargeModalLabel">Delete</h4>
                                                        <button type="button" class="close" data-bs-dismiss="modal"
                                                            aria-hidden="true">×</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">No</button>
                                                        <a href="delete_cat/{{ $cat->id }}"><button type="button"
                                                                class="btn btn-primary">Yes,
                                                                Delete</button></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">

                @include('partials.pagination', ['items' => $catArray])

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
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function notification_click($id) {
        $("#notification_model").empty();
        html =
            '<form method="post" id="notification_form" enctype="multipart/form-data"> @csrf <input class="form-control" type="textname" name="temp_id" value="' +
            $id +
            '" style="display: none"/> <div class="form-group"> <h7>Title</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Title" name="title" required="" /> </div> </div> <div class="form-group"> <h7>Description</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Description" name="description" required="" /> </div> </div> <div class="form-group"> <h7>Large Icon</h7> <div class="input-group custom"> <input type="file" class="form-control" name="large_icon" /> </div> </div> <div class="form-group"> <h7>Big Picture</h7> <div class="input-group custom"> <input type="file" class="form-control" name="big_picture" /> </div> </div> <div class="row"> <div class="col-sm-12"> <button type="button" class="btn btn-primary btn-block" id="send_click">Send</button> </div> </div> </form>';

        $("#notification_model").append(html);

    }

    $(document).on('click', '#send_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("notification_form"));
        var status = formData.get("temp_id");


        var url = "{{ route('cat.notification', ':status') }}";
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

    function imp_click($id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('cat.imp', ':status') }}";
        url = url.replace(":status", status);
        var formData = new FormData();
        formData.append('id', $id);
        formData.append('isNew', '0');
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
                    var x = document.getElementById("imp_label_" + $id);
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
        formData.append('type', 'cat');
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var loader = document.getElementById("loader");
                if (loader) {
                    loader.style.display = "block";
                }
            },
            success: function(data) {
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

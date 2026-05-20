 
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
                            data-target="#add_package_model" type="button">
                            Add New Package </a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Package Name</th>
                                <th>Validity(Day)</th>
                                <th>Price Rs</th>
                                <th>Price $</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packageArray as $package)
                                <tr>
                                    <td class="table-plus">{{ $package->id }}</td>

                                    <td>{{ $package->package_name }}</td>

                                    <td>{{ $package->validity }}</td>

                                    <td>{{ $helperController::getOfferWithPrice($package->actual_price, $package->price) }}
                                    </td>

                                    <td>{{ $helperController::getOfferWithPrice($package->actual_price_dollar, $package->price_dollar) }}
                                    </td>

                                    @if ($package->status == '1')
                                        <td>Active</td>
                                    @else
                                        <td>Disabled</td>
                                    @endif

                                    <td>
                                        <Button class="dropdown-item"
                                            onclick="edit_click('{{ $package->id }}', '{{ $package->package_name }}', '{{ $package->desc }}', '{{ $package->validity }}', '{{ $package->actual_price }}', '{{ $package->price }}','{{ $package->actual_price_dollar }}', '{{ $package->price_dollar }}', '{{ $package->status }}')"
                                            data-backdrop="static" data-toggle="modal"
                                            data-target="#edit_package_model"><i class="dw dw-edit2"></i> Edit</Button>

                                        <Button class="dropdown-item" onclick="delete_click('{{ $package->id }}')"><i
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

<div class="modal fade" id="add_package_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Package</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_package_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h7>Package Name</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Package Name" name="package_name"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Description</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Description" name="desc"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>Validity (Day)</h7>
                        <div class="input-group custom">
                            <input type="number" class="form-control form-control-lg" placeholder="Validity"
                                name="validity" required="" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h7>Actual Price Rs</h7>
                                <div class="input-group custom">
                                    <input type="number" step="any" class="form-control form-control-lg"
                                        placeholder="100" name="actual_price" required="" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h7>Price Rs</h7>
                                <div class="input-group custom">
                                    <input type="number" step="any" class="form-control form-control-lg"
                                        placeholder="100" name="price" required="" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h7>Actual Price $</h7>
                                <div class="input-group custom">
                                    <input type="number" step="any" class="form-control form-control-lg"
                                        placeholder="100" name="actual_price_dollar" required="" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <h7>Price $</h7>
                                <div class="input-group custom">
                                    <input type="number" step="any" class="form-control form-control-lg"
                                        placeholder="100" name="price_dollar" required="" />
                                </div>
                            </div>
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

<div class="modal fade" id="edit_package_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Package</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="update_model">
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function edit_click($id, $package_name, $desc, $validity, $actual_price, $price, $actual_price_dollar,
        $price_dollar, $status) {
        $("#update_model").empty();
        var activeSelected = $status == 1 ? 'selected' : '';
        var disableSelected = $status == 0 ? 'selected' : '';

        html = '<form method="post" id="edit_package_form" enctype="multipart/form-data"> @csrf <!-- {{$package_id=1}} --><input class="form-control" type="textname" name="package_id" value="'+$id+'" style="display: none"/> <div class="form-group"> <h7>Package Name</h7> <div class="input-group custom"><input type="text" class="form-control" placeholder="Package Name" value="'+$package_name+'" name="package_name" required="" /> </div> </div><div class="form-group"> <h7>Description</h7> <div class="input-group custom"> <input type="text" class="form-control" placeholder="Description" value="'+$desc+'" name="desc" required="" /> </div></div><div class="form-group"> <h7>Validity (Day)</h7> <div class="input-group custom"> <input type="number" class="form-control form-control-lg" placeholder="Validity" value="'+$validity+'" name="validity" required=""/> </div> </div> <div class="row"> <div class="col-md-6 col-sm-12"> <div class="form-group"> <h7>Actual Price Rs</h7> <div class="input-group custom"> <input type="number" step="any" class="form-control form-control-lg" placeholder="100" value="'+$actual_price+'" name="actual_price" required=""/> </div> </div> </div> <div class="col-md-6 col-sm-12"> <div class="form-group"> <h7>Price Rs</h7> <div class="input-group custom"> <input type="number" step="any" class="form-control form-control-lg" placeholder="100" value="'+$price+'" name="price" required=""/> </div> </div> </div> </div> <div class="row"> <div class="col-md-6 col-sm-12"> <div class="form-group"> <h7>Actual Price $</h7> <div class="input-group custom"> <input type="number" step="any" class="form-control form-control-lg" placeholder="100" value="'+$actual_price_dollar+'" name="actual_price_dollar" required=""/> </div> </div> </div> <div class="col-md-6 col-sm-12"> <div class="form-group"> <h7>Price $</h7> <div class="input-group custom"> <input type="number" step="any" class="form-control form-control-lg" placeholder="100" value="'+$price_dollar+'" name="price_dollar" required=""/> </div> </div> </div> </div> <div class="form-group"> <h6>Status</h6> <div class="col-sm-20"> <select id="status" class="selectpicker form-control" data-style="btn-outline-primary" name="status"> <option value="1" '+activeSelected+'>Active</option> <option value="0" '+disableSelected+'>Disable</option> </select> </div> </div> <div class="row"> <div class="col-sm-12"> <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button> </div> </div> </form>';

        $("#update_model").append(html);

    }

    $('#add_package_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'submit_package',
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

        var formData = new FormData(document.getElementById("edit_package_form"));
        var status = formData.get("package_id");


        var url = "{{ route('package.update', ':status') }}";
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

    function delete_click(id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('delete.update', ':id') }}";
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

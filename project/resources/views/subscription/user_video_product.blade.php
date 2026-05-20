 
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
                            Add User Video Purchase </a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Video Template Name</th>
                                <th>amount</th>
                                <th>from_where</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultData as $videoTempPurchase)
                                <tr>
                                    <td class="table-plus">{{ $videoTempPurchase->id }}</td>
                                    <td>{{ \App\Http\Controllers\HelperController::getTemplateName($videoTempPurchase->product_id) }}
                                    </td>
                                    <td>{{ $videoTempPurchase->amount }}</td>
                                    <td>{{ $videoTempPurchase->from_where }}</td>
                                    @if ($videoTempPurchase->status == '1')
                                        <td>Active</td>
                                    @else
                                        <td>Disabled</td>
                                    @endif
                                    <td>
                                        <Button class="dropdown-item"
                                            onclick="edit_click('{{ $videoTempPurchase->id }}', '{{ $videoTempPurchase->product_id }}','{{ $videoTempPurchase->transaction_id }}', '{{ $videoTempPurchase->product_type }}', '{{ $videoTempPurchase->currency_code }}', '{{ $videoTempPurchase->amount }}', '{{ $videoTempPurchase->payment_method }}','{{ $videoTempPurchase->from_where }}', '{{ $videoTempPurchase->isManual }}', '{{ $videoTempPurchase->status }}','{{ $user_id }}')"
                                            data-backdrop="static" data-toggle="modal"
                                            data-target="#edit_package_model"><i class="dw dw-edit2"></i> Edit</Button>
                                        <Button class="dropdown-item"
                                            onclick="delete_click('{{ $videoTempPurchase->id }}')"><i
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
                <h5 class="modal-title" id="myLargeModalLabel">Add User Video Purchase</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_package_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>Product Id</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Product Id" name="product_id"
                                required="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Method</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control form-control-lg" placeholder="Payment Method"
                                name="payment_method" required="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Transaction Id</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Transaction Id"
                                name="transaction_id" required="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Currency</h6>
                        <div class="input-group custom">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="currency_code">
                                <option value="INR">INR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>Amount</h6>
                        <div class="input-group custom">
                            <input type="number" class="form-control" placeholder="Amount" name="amount"
                                required="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <h6>From Where</h6>
                        <div class="input-group custom">
                            <select class="selectpicker form-control" data-style="btn-outline-primary"
                                name="from_where">
                                <option value="Mobile">Mobile</option>
                                <option value="Web">Web</option>
                            </select>
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
                            <input type="hidden" name="user_id" value="{{ $user_id }}" />
                            <input type="hidden" name="product_type" value="1" />
                            <input type="hidden" name="isManual" value="1" />
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
                <h5 class="modal-title" id="myLargeModalLabel">Edit User Video Purchase</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="update_model">
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function edit_click(id, product_id, transaction_id, product_type, currency_code, amount, payment_method, from_where,
        isManual, status, userId) {
        $("#update_model").empty();

        let statusActiveSelect = status == 1 ? "selected=selected" : "";
        let statusInActiveSelect = status == 0 ? "selected=selected" : "";
        let fromWhereMobileSelect = from_where == "Mobile" ? "selected=selected" : "";
        let fromWhereWebSelect = from_where == "Web" ? "selected=selected" : "";
        let currencyINRSelect = currency_code == "RS" ? "selected=selected" : "";
        let currencyUSDSelect = currency_code == "$" ? "selected=selected" : "";

        var html = `<form method="post" id="edit_package_form" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <h6>Product Id</h6>
                                <div class="input-group custom">
                                    <input type="text" class="form-control" placeholder="Product Id" name="product_id" required="" value="${product_id}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>Payment Method</h6>
                                <div class="input-group custom">
                                    <input type="text" class="form-control form-control-lg" placeholder="Payment Method" name="payment_method" required="" value="${payment_method}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>Transaction Id</h6>
                                <div class="input-group custom">
                                    <input type="text" class="form-control" placeholder="Transaction Id" name="transaction_id" value="${transaction_id}" required="" />
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>Currency</h6>
                                <div class="input-group custom">
                                    <select class="selectpicker form-control" data-style="btn-outline-primary" name="currency_code">
                                        <option value="RS" ${currencyINRSelect}>INR</option>
                                        <option value="$" ${currencyUSDSelect}>USD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>Amount</h6>
                                <div class="input-group custom">
                                    <input type="number" class="form-control" placeholder="Amount" name="amount" required="" value="${amount}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>From Where</h6>
                                <div class="input-group custom">
                                    <select class="selectpicker form-control"
                                            data-style="btn-outline-primary"
                                            name="from_where">
                                        <option value="Mobile" ${fromWhereMobileSelect}>Mobile</option>
                                        <option value="Web" ${fromWhereWebSelect}>Web</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <h6>Status</h6>
                                <div class="col-sm-20">
                                    <select id="status" class="selectpicker form-control"
                                            data-style="btn-outline-primary"
                                            name="status">
                                        <option value="1" ${statusActiveSelect}>Active</option>
                                        <option value="0" ${statusInActiveSelect}>Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <input type="hidden" name="user_id" value="${userId}"/>
                                    <input type="hidden" name="product_type" value="0"/>
                                    <input type="hidden" name="isManual" value="1"/>
                                    <input type="hidden" name="id" value="${id}"/>
                                    <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button>
                                </div>
                            </div>
                        </form>`;
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
            url: "{{ route('manage_video_product.submit') }}",
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
        var url = "{{ route('manage_video_product.update', ':status') }}";
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
                if (!data.status) {
                    window.alert(data.success);
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

        var url = "{{ route('manage_video_product.delete', ':id') }}";
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

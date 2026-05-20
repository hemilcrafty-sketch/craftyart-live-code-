   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">
                <form method="post" id="ip_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf
                    <div class="form-group">
                        <h6>Primary Ip</h6>
                        <input class="form-control" type="textname" name="main_ip"
                            value="{{ $data['allowed_ip']->main_ip }}" required>
                    </div>

                    <div class="form-group">
                        <h6>Additional Ip</h6>
                        <input class="form-control" type="textname" name="additional" data-role="tagsinput"
                            placeholder="add Ips" value="{{ $data['allowed_ip']->additional }}">
                    </div>

                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>

            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf
                    <div class="form-group">
                        <h6>Onesignal Api Key</h6>
                        <input class="form-control" type="textname" name="key"
                            value="{{ $data['notification']->key }}" required>
                    </div>

                    <div class="form-group">
                        <h6>Onesignal Appid</h6>
                        <input class="form-control" type="textname" name="app_id"
                            value="{{ $data['notification']->app_id }}" required>
                    </div>

                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="pd-20 card-box mb-30">
                        <form method="post" id="notification_form" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <h7>Title</h7>
                                <div class="input-group custom">
                                    <input type="text" id="title" class="form-control" placeholder="Title"
                                        name="title" required="" />
                                </div>
                            </div>

                            <div class="form-group">
                                <h7>Description</h7>
                                <div class="input-group custom">
                                    <input type="text" id="description" class="form-control"
                                        placeholder="Description" name="description" required="" />
                                </div>
                            </div>

                            <div class="form-group">
                                <h7>Large Icon</h7>
                                <div class="input-group custom">
                                    <input type="file" id="large_icon" class="form-control" name="large_icon" />
                                </div>
                            </div>

                            <div class="form-group">
                                <h7>Big Picture</h7>
                                <div class="input-group custom">
                                    <input type="file" id="big_picture" class="form-control" name="big_picture" />
                                </div>
                            </div>

                            <div class="form-group">
                                <h7>Activity Name</h7>
                                <div class="input-group custom">
                                    <input type="text" id="activity_name" class="form-control" name="activity_name"
                                        list="activity_list" autocomplete="on"
                                        style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                        required="" />
                                </div>
                            </div>

                            <div class="form-group">
                                <h7>Schedule</h7>
                                <div class="input-group custom">
                                    <input id="schedule" class="form-control datetimepicker"
                                        placeholder="Select Date & Time" type="text" name="schedule" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <br />
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <button type="button" id="add_data"
                                            class="btn btn-success form-control-file">Add Intent Data
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="additional_data_container">
                            </div>


                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" class="btn btn-primary btn-block"
                                        id="send_notification_click">Send</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <datalist id="activity_list">
        <option value="com.crafty.art.activities.PremiumActivity"></option>
        <option value="com.crafty.art.referandearn.ReferAndEarnActivity"></option>
        <option value="com.crafty.brandkit.activity.BrandKitActivity"></option>
        <option value="com.crafty.art.customorder.activities.OrderListActivity"></option>
        <option value="com.crafty.art.activities.WebViewActivity"></option>
    </datalist>
</div>
@include('layouts.masterscript')

<script>
    $(document).on('click', '#add_data', function() {
        add_data();
    });

    $(document).on('click', '#remove_data', function() {
        $(this).closest(".row").remove();
    });

    function add_data() {
        html =
            '<div class="row"> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Key</h6> <input class="form-control" type="textname" name="key[]" value="" required> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Value</h6> <input class="form-control" type="textname" name="value[]" value="" required> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6 style="opacity: 0;">.</h6> <button type="button" id="remove_data" class="btn btn-danger form-control-file">Remove </button> </div> </div> </div>';

        $('#additional_data_container').append(html);
    }

    $('#ip_form').on('submit', function(event) {
        event.preventDefault();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        var url = "{{ route('ip.update') }}";
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";

            },
            success: function(data) {

                hideFields();

                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                    '</div>');
                }

                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });


    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        var url = "{{ route('notification.update', [$data['notification']->id]) }}";
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";

            },
            success: function(data) {

                hideFields();

                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                    '</div>');
                }

                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    $(document).on('click', '#send_notification_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("notification_form"));


        var url = "{{ route('custom.notification') }}";

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
                    hideFields();
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

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

        $('#title').val('');
        $('#description').val('');
        $('#large_icon').val('');
        $('#big_picture').val('');
        $('#activity_name').val('');
        $('#schedule').val('');
        $('#additional_data_container').empty();
    }
</script>
</body>

</html>

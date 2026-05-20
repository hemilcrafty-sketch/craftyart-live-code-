   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

<div class="main-container designer-access-container">

  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" class="designer-access-container" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf
                    <div class="form-group" style="display: none">
                        <h6>Font Name</h6>
                        <input class="form-control" type="textname" id="font_name" name="font_name">
                    </div>

                    <div class="form-group">
                        <h6>Font Thumb</h6>
                        <input type="file" class="form-control-file form-control height-auto" id="font_thumb"
                            name="font_thumb" required>
                    </div>

                    <div class="form-group">
                        <h6>Font File</h6>
                        <input type="file" class="form-control-file form-control height-auto" id="font_file"
                            name="font_file" required>
                    </div>

                    <div class="form-group">
                        <h6>Uniname</h6>
                        <div class="col-sm-20">
                            <select class="custom-select2 form-control" data-style="btn-outline-primary" name="uniname"
                                style="width: 100%;">
                                <option value="none">None</option>
                                <option value="kap">Kap</option>
                                <option value="krutidev">Krutidev</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" id="status"
                                name="status">
                                <option value="1">LIVE</option>
                                <option value="0">NOT LIVE</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')

<script>
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();

        count = 0;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'submit_font',
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
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
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

    function hideFields() {

        $("#font_name").val('');
        $("#font_thumb").val('');
        $("#font_file").val('');
        $("#status").val('1');

        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

    }
</script>

</body>

</html>

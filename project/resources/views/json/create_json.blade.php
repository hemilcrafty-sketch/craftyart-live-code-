   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">

  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">
                    <span id="result"></span>
                    @csrf
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                            <h6>Json File</h6>
                                <input type="file" id="json_file" class="form-control-file form-control" name="json_file" >
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Images</h6>
                                <input type="file" class="form-control" id="st_image" name="st_image[]" multiple required>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <h6>Select Application</h6>
                                <div class="col-sm-20">
                                    <select id="app_id" class="selectpicker form-control" data-style="btn-outline-primary" name="app_id" >
                                        <option value="" disabled="true" selected="true"> == Select Application == </option>
                                        @foreach($datas['apps'] as $app)
                                            <option value="{{ $app->id }}">{{ $app->app_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{ csrf_field() }}
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

$('#dynamic_form').on('submit', function (event) {
        event.preventDefault();
        count = 0;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        $.ajax({
            url: 'submit_json',
            type: 'POST',
            data: formData,
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {

                hideFields();
                if (data.error) {
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                }

                setTimeout(function () {
                    $('#result').html('');
                }, 3000);

            },
            error: function (xhr,status,error) {
                hideFields();
                window.alert(error);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    function hideFields() {
        $("#json_file").val('');
        $("#st_image").val('');
        $("#is_premium").val('0');
        $("#status").val('1');
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }

</script>

</body>
</html>

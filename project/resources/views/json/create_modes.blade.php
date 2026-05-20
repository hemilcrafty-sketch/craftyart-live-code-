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
                        <div class="col-md-3 col-sm-12">
                            <div class="form-group">
                                <h6>Name</h6>
                                <input type="text" class="form-control" id="mode_name" name="mode_name" required>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12">

                            <div class="form-group">
                                <h6 style="opacity: 0">Images</h6>
                                <input class="btn btn-primary" type="submit" name="submit">
                            </div>

                        </div>
                    </div>


                </form>
            </div>


        </div>

        <div class="card-box mr-30" style="display: none">
            <div class="pb-10">

                <table class="data-table table stripe hover nowrap">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th class="datatable-nosort">Action</th>
                    </tr>
                    </thead>
                    <tbody id="item_table">
                    <tr>
                        <td class="table-plus">1</td>
                        <td class="table-plus">1</td>
                        <td>
                            <a style="padding: 0.657rem 1rem; transition: all .3s ease-in-out; white-space: nowrap"
                               href=""><i class="dw dw-edit2"></i></a>
                            <a style="padding: 0.657rem 1rem; transition: all .3s ease-in-out; white-space: nowrap"
                               href=""><i class="dw dw-delete-3"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="table-plus">1</td>
                        <td class="table-plus">1</td>
                        <td>
                            <a style="padding: 0.657rem 1rem; transition: all .3s ease-in-out; white-space: nowrap"
                               href=""><i class="dw dw-edit2"></i></a>
                            <a style="padding: 0.657rem 1rem; transition: all .3s ease-in-out; white-space: nowrap"
                               href=""><i class="dw dw-delete-3"></i></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
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
            url: 'submit_editable_mode',
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
            error: function (error) {

                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    function hideFields() {

        $("#mode_name").val('');

        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

    }

</script>

</body>
</html>

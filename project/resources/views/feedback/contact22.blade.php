 
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pd-20">

                </div>
                <div class="pb-20">

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Index</th>
                                <th>User ID</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contactArray as $contactRow)
                                <tr>
                                    <td class="table-plus">{{ $contactRow->id }}</td>

                                    <td>{{ $contactRow->user_id }}</td>

                                    <td>{{ $contactRow->created_at }}</td>

                                    <td>
                                        <Button class="dropdown-item"
                                            onclick="show_click('{{ $contactRow->id }}')">Show</Button>
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

<div class="modal fade" id="show_contact_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Contact</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="contact_model">
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    function show_click(id) {
        $("#contact_model").empty();

        $.ajax({
            url: 'getContact/' + id,
            type: 'GET',
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                setTimeout(function() {
                    hideFields();
                    $('#show_contact_model').modal('toggle');
                    if (data.error) {
                        window.alert(data.error);
                    } else {
                        html =
                            '<div class="form-group"><div class="input-group custom"><textarea class="form-control" readonly>' +
                            data.success + '</textarea> </div> </div>';
                        $("#contact_model").append(html);
                    }
                }, 500);

            },
            error: function(error) {

                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }
</script>

</body>

</html>

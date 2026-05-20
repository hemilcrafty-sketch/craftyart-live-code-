   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

<style>
    .card {
        border-radius: 10px;
    }

    .card-header {
        background-color: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #e3e7eb;
        width: 100%;
    }

    .col {
        width: 20%;
        max-width: 10%;
    }
</style>
<div class="main-container">
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card">
                <h5 class="card-title my-2">Categories</h5>
                <div class="card-header">
                    <h5 class="card-title">All Data</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Total: </strong>{{ $datas['cat'] }}</li>
                        <li><strong>Live: </strong>{{ $datas['cat_live'] }}</li>
                        <li><strong>UnLive: </strong>{{ $datas['cat_unlive'] }}</li>
                    </ul>
                </div>
                <div class="card-header">
                    <h5 class="card-title">User Data</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Total: </strong>{{ $datas['user_cat'] }}</li>
                        <li><strong>Live: </strong>{{ $datas['user_cat_live'] }}</li>
                        <li><strong>UnLive: </strong>{{ $datas['user_cat_unlive'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <h5 class="card-title my-2">Templates</h5>
                <div class="card-header">
                    <h5 class="card-title">All Data</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Total: </strong>{{ $datas['all_item'] }}</li>
                        <li><strong>Live: </strong>{{ $datas['all_item_live'] }}</li>
                        <li><strong>UnLive: </strong>{{ $datas['all_item_unlive'] }}</li>
                    </ul>
                </div>
                <div class="card-header">
                    <h5 class="card-title">User Data</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Total: </strong>{{ $datas['item'] }}</li>
                        <li><strong>Live: </strong>{{ $datas['item_live'] }}</li>
                        <li><strong>UnLive: </strong>{{ $datas['item_unlive'] }}</li>
                    </ul>
                </div>
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
            url: 'update_cache_ver',
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

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";

    }
</script>
</body>

</html>

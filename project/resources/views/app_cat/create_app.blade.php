   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" action="submit_app" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>App Name</h6>
                        <input class="form-control" type="textname" name="app_name" required>
                    </div>
                    <div class="form-group">
                        <h6>App Thumb</h6>
                        <input type="file" class="form-control-file form-control height-auto" name="app_thumb"
                            required>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" name="status">
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
</body>

</html>

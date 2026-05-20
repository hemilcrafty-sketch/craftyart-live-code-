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
                        <a class="btn btn-primary" href="create_app" role="button"> Add New Application </a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>App Id</th>
                                <th>App Name</th>
                                <th class="datatable-nosort">App Thumb</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appArray as $app)
                                <tr>
                                    <td class="table-plus">{{ $app->id }}</td>
                                    <td>{{ $app->app_name }}</td>

                                    <td><img src="{{ config('filesystems.storage_url') }}{{ $app->app_thumb }}"
                                            style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                    </td>

                                    @if ($app->status == '1')
                                        <td>LIVE</td>
                                    @else
                                        <td>NOT LIVE</td>
                                    @endif

                                    <td>
                                        <a class="dropdown-item" href="edit_app/{{ $app->id }}"><i
                                                class="dw dw-edit2"></i> Edit</a>
                                        <a class="dropdown-item" href="delete_app/{{ $app->id }}"><i
                                                class="dw dw-delete-3"></i> Delete</a>
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
@include('layouts.masterscript')
</body>

</html>

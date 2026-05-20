@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                        </div>

                        <div class="col-md-7">
                            {{-- @include('partials.filter_form', [
                                'action' => route('panel_histroy'),
                            ]) --}}
                        </div>
                    </div>

                    {{-- <div class="col-sm-12 table-responsive"> --}}
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width:30px">Id</th>
                                    <th style="width:30px">Emp id</th>
                                    <th>Model</th>
                                    <th style="width:30px">Model Id</th>
                                    <th>Old value</th>
                                    <th>Updated value</th>
                                    <th style="width:50px">Ip address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($adminChangesLogs as $log)
                                    <tr style="background-color: #f9f9f9;">
                                        <td>{{ $log->id }}</td>
                                        <td>{{ $roleManager::getUploaderName($log->emp_id) }}</td>
                                        <td>{{ $log->model }}</td>
                                        <td>{{ $log->model_id }}</td>

                                        {{-- OLD VALUES --}}
                                        <td>
                                            @if (!empty($log->decoded_old_values))
                                                <ul style="margin:0; padding-left:15px;">
                                                    @foreach ($log->decoded_old_values as $key => $value)
                                                        <li>
                                                            <strong>{{ $key }}:</strong>
                                                            @if (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))
                                                                <pre style="white-space: pre-wrap; background:#f8f9fa; border-radius:5px; padding:5px; margin:4px 0;">{{ $value }}</pre>
                                                            @else
                                                                <span>{{ $value }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span style="color:#999;">—</span>
                                            @endif
                                        </td>

                                        {{-- UPDATED VALUES --}}
                                        <td>
                                            @if (!empty($log->decoded_updated_fields))
                                                <ul style="margin:0; padding-left:15px;">
                                                    @foreach ($log->decoded_updated_fields as $key => $value)
                                                        <li>
                                                            <strong>{{ $key }}:</strong>
                                                            @if (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))
                                                                <pre style="white-space: pre-wrap; background:#e8f5e9; border-radius:5px; padding:5px; margin:4px 0;">{{ $value }}</pre>
                                                            @else
                                                                <span>{{ $value }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span style="color:#999;">—</span>
                                            @endif
                                        </td>

                                        <td>{{ $log->ip_address }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">

                @include('partials.pagination', ['items' => $adminChangesLogs])
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
</body>

</html>

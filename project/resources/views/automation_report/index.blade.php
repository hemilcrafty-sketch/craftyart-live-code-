@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">

                    </div>
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 185px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">Id</th>
                                    <th style="width: 100px;">campaign name</th>
                                    <th style="width: 50px;">total</th>
                                    <th style="width: 50px;">send</th>
                                    <th style="width: 50px;">failed</th>
                                    <th style="width: 50px;">status</th>
                                    <th style="width: 60px;">type</th>
                                    <th style="width: 50px;">send type</th>
                                    <th>created at</th>
                                    <th>action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($automationReports as $automationReport)
                                    <tr>
                                        <td>{{ $automationReport->id }}</td>
                                        <td>{{ $automationReport->campaign_name }}</td>
                                        <td>{{ $automationReport->total }}</td>
                                        <td>{{ $automationReport->sent }}</td>
                                        <td>{{ $automationReport->failed }}</td>
                                        <td>{{ $automationReport->status }}</td>
                                        <td>{{ $automationReport->type }}</td>
                                        <td>{{ $automationReport->send_type }}</td>
                                        <td>{{ $automationReport->created_at }}</td>
                                        <td>
                                            @if ($automationReport->failed > 0)
                                                <a href="{{ route('automation_report.failed_logs', ['log_id' => $automationReport->id]) }}"
                                                    class="btn btn-sm btn-outline-danger">
                                                    Failed Logs
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $automationReports])
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
</body>

</html>

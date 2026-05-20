@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                            <a href="{{ route('campaign.index') }}" class="btn btn-primary m-1 item-form-input"
                               id="addNewFrameCatBtn">
                                + Create Campaign
                            </a>
                        </div>

                        <div class="col-md-9">
                            @include('partials.filter_form', [
                            'action' => route('campaign.report'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                         style="max-height: calc(108vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                               class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th style="width:30px">Log ID</th>
                                <th style="width:65px">Type</th>
                                <th>Template</th>
                                <th style="width:40px">Total</th>
                                <th style="width:50px">Sent</th>
                                <th style="width:50px">Failed</th>
                                <th style="width:70px">Status</th>
                                <th style="width:50px">Created At</th>
                                <th style="width:50px">Updated At</th>
                                <th>Action</th>
                                <th style="width:40px">Auto start</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->type }}</td>
                                <td>
                                    @if(is_array($log->template_name))
                                    @foreach($log->template_name as $value)
                                    {{ $value }}@if(!$loop->last)<br>@endif
                                    @endforeach
                                    @else
                                    {{ $log->template_name }}
                                    @endif
                                </td>
                                <td>{{ $log->total }}</td>
                                <td>
                                    @if(is_array($log->sent_type))
                                    @foreach($log->sent_type as $key => $value)
                                    {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last)<br>@endif
                                    @endforeach
                                    @else
                                    {{ $log->sent_type }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_array($log->failed_type))
                                    @foreach($log->failed_type as $key => $value)
                                    {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last)<br>@endif
                                    @endforeach
                                    @else
                                    {{ $log->failed_type }}
                                    @endif
                                </td>
                                <td>
                                    @if($log->status === 'paused')
                                    {{ $log->pause_type . ' ' . $log->status }}
                                    @else
                                    {{ $log->status }}
                                    @endif
                                    @if($log->error_message)
                                    <br><small class="text-danger" title="{{ $log->error_message }}">Error occurred</small>
                                    @endif
                                </td>
                                <td style="word-break: keep-all">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td style="word-break: keep-all">{{ $log->updated_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="d-flex flex-wrap" style="gap: 10px">

                                        @if($log->email_template_id)
                                        <a href="{{ route('email_template.preview', $log->email_template_id) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        @endif

                                        @if ($log->email_failed > 0 || $log->wp_failed > 0)
                                        <a href="{{ route('campaign.failed_logs', ['log_id' => $log->id]) }}"
                                           class="btn btn-sm btn-outline-danger">
                                            Failed Logs
                                        </a>
                                        @endif

                                        {{-- 🔹 Retry button for error status --}}
                                        {{-- @if ($log->status === 'error' && !$log->stopped)
                                        <form action="{{ route('campaign.retry', $log->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info">Retry</button>
                                        </form>
                                        @endif --}}

                                        {{-- 🔹 Stop always visible while not completed or stopped --}}
                                        @if (!in_array($log->status, ['completed','stopped']))
                                        <form action="{{ route('campaign.stop', $log->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Stop</button>
                                        </form>
                                        @endif

                                        {{-- 🔹 Pause/Resume logic --}}
                                        @if ($log->status === 'processing')
                                        <form action="{{ route('campaign.pause', $log->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary">Pause</button>
                                        </form>
                                        @elseif ($log->status === 'paused' && $log->pause_type === 'manual')
                                        <form action="{{ route('campaign.resume', $log->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Resume</button>
                                        </form>
                                        @elseif ($log->status === 'paused' && $log->pause_type === 'auto')
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="alert('This job was auto-paused after reaching the daily limit. It will resume automatically tomorrow.')"
                                                disabled>
                                            Auto Paused
                                        </button>
                                        @elseif ($log->status === 'pending')
                                        <form action="{{ route('campaign.resume', $log->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Resume</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($log->status === 'paused' && $log->pause_type === 'auto')
                                    <button style="border:none" onclick="autoResumeToggle('{{ $log->id }}')">
                                        <input type="checkbox" class="switch-btn" data-size="small"
                                               data-color="#0059B2" {{ $log->auto_resume ? 'checked' : '' }} />
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No records found.</td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $logs])
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    const setEnableUrl = "{{ route('campaign.toggle_auto_resume', ['id' => ':id']) }}";

    function autoResumeToggle(id) {
        let isChecked = $("#row_" + id + " .switch-btn").is(":checked");

        $.post(setEnableUrl.replace(':id', id), {
            _token: "{{ csrf_token() }}",
            auto_resume: isChecked ? 1 : 0
        }, function(res) {
            if (res.status) {
                alert(res.message);
            } else {
                alert('Something went wrong');
            }
        }).fail(function() {
            alert('Request failed!');
        });
    }
</script>
@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                            <a href="{{ route('email_template.index') }}" class="btn btn-primary m-1 item-form-input"
                                id="addNewFrameCatBtn">
                                + Add Email Template
                            </a>
                        </div>

                        <div class="col-md-9">
                            @include('partials.filter_form', [
                                'action' => route('email_report.view'),
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
                                    <th>Subject</th>
                                    <th  style="width:50px">Total</th>
                                    <th style="width:50px">Sent</th>
                                    <th style="width:50px">Failed</th>
                                    <th style="width:50px">Status</th>
                                    <th style="width:70px">Created At</th>
                                    <th style="width:70px">Updated At</th>
                                    <th>Action</th>
                                    <th  style="width:50px">Auto start</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>{{ $log->subject }}</td>
                                        <td>{{ $log->total }}</td>
                                        <td>{{ $log->sent }}</td>
                                        <td>{{ $log->failed }}</td>
                                        <td>
                                            @if($log->status === 'paused')
                                            {{ $log->pause_type . ' ' . $log->status }}
                                            @else
                                            {{ $log->status }}
                                            @endif
                                        </td>
                                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->updated_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap" style="gap: 10px">

                                                <a href="{{ route('email_template.preview', $log->email_template_id) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>

                                                @if ($log->failed > 0)
                                                <a href="{{ route('email_report.failed_logs', ['log_id' => $log->id]) }}"
                                                   class="btn btn-sm btn-outline-danger">
                                                    Failed Logs
                                                </a>
                                                @endif

                                                {{-- 🔹 Stop always visible while not completed or stopped --}}
                                                @if (!in_array($log->status, ['completed','stopped']))
                                                <form action="{{ route('email_report.stop', $log->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">Stop</button>
                                                </form>
                                                @endif

                                                {{-- 🔹 Pause/Resume logic --}}
                                                @if ($log->status === 'processing')
                                                <form action="{{ route('email_report.pause', $log->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary">Pause</button>
                                                </form>
                                                @elseif ($log->status === 'paused' && $log->pause_type === 'manual')
                                                <form action="{{ route('email_report.resume', $log->id) }}" method="POST" class="d-inline">
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
                                                <form action="{{ route('email_report.resume', $log->id) }}" method="POST" class="d-inline">
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
                                        <td colspan="8" class="text-center">No records found.</td>
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
    const setEnableUrl = "{{ route('email_report.toggle_auto_resume', ['id' => ':id']) }}";

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
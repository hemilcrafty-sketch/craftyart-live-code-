@include('layouts.masterhead')
<div class="main-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display: flex; flex-direction: column; height: 92vh; overflow: hidden;">
                <div class="row justify-content-between m-2">
                    <div class="col-md-3">
                        <h5 class="mt-2">Failed Logs For Campaign: {{ $log->subject }}</h5>
                        <small class="text-muted">Log ID: {{ $log_id }}</small>
                    </div>
                    <div class="col-md-5" style="text-align: end;">
                        @if($log->email_failed > 0)
                        <button id="resend-failed-email" class="btn btn-primary">Resend Failed Email</button>
                        @endif
                        @if($log->wp_failed > 0)
                        <button id="resend-failed-whatsapp" class="btn btn-info">Resend Failed WhatsApp</button>
                        @endif
                        <button id="resend-failed-all" class="btn btn-success">Resend All Failed</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                     style="max-height: calc(108vh - 220px) !important">
                    <table id="temp_table" style="table-layout: fixed; width: 100%;"
                           class="table table-striped table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Contact No.</th>
                            <th>Type</th>
                            <th>Error Message</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($failedLogs as $logDetail)
                        <tr>
                            <td>{{ $logDetail->user_id }}</td>
                            <td>{{ $logDetail->user->email ?? 'N/A' }}</td>
                            <td>{{ $logDetail->contact_no ?? 'N/A' }}</td>
                            <td>
                                {{$logDetail->type}}
                            </td>
                            <td class="text-danger">{{ Str::limit($logDetail->error_message, 100) }}</td>
                            <td>{{ \Carbon\Carbon::parse($logDetail->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($logDetail->updated_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No failed logs found.</td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        @include('partials.pagination', ['items' => $failedLogs])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    // Resend only failed emails
    document.getElementById('resend-failed-email')?.addEventListener('click', function() {
        if (!confirm("Resend all failed emails in background?")) return;

        fetch(`{{ route('campaign.resend_failed_email', ['log_id' => $log_id]) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });

    // Resend only failed WhatsApp
    document.getElementById('resend-failed-whatsapp')?.addEventListener('click', function() {
        if (!confirm("Resend all failed WhatsApp messages in background?")) return;

        fetch(`{{ route('campaign.resend_failed_whatsapp', ['log_id' => $log_id]) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });

    // Resend all failed (both email and WhatsApp)
    document.getElementById('resend-failed-all')?.addEventListener('click', function() {
        if (!confirm("Resend all failed emails and WhatsApp messages in background?")) return;

        fetch(`{{ route('campaign.resend_failed_all', ['log_id' => $log_id]) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });
</script>
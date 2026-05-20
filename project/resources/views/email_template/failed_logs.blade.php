@include('layouts.masterhead')
<div class="main-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display: flex; flex-direction: column; height: 92vh; overflow: hidden;">
                <div class="row justify-content-between m-2">
                    <div class="col-md-3">
                        <h5 class="mt-2">Failed Email Logs For Log ID: {{ $log_id }}</h5>
                    </div>
                    <div class="col-md-5" style="text-align: end;">
                        <button id="resend-failed-all" class="btn btn-primary">Resend Emails</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                    style="max-height: calc(108vh - 220px) !important">
                    <table id="temp_table" style="table-layout: fixed; width: 100%;"
                        class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Error Message</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($failedLogs as $log)
                                <tr>
                                    <td>{{ $log->email }}</td>
                                    <td>{{ $log->error_message }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->updated_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No failed logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    document.getElementById('resend-failed-all').addEventListener('click', function() {
        if (!confirm("Resend all failed emails in background?")) return;

            fetch(`{{ route('email_template.resend_failed_all', ['log_id' => $log_id]) }}`, {
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
    document.querySelectorAll('.resend-single').forEach(button => {
        button.addEventListener('click', function () {
            const logId = this.getAttribute('data-id');

            if (!confirm("Resend this email in background?")) return;

            fetch(`{{ route('email_template.resend_single') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: logId })
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
    });
</script>

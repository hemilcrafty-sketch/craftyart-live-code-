<div class="page-header">
    <div class="density d-flex justify-content-between">
        <h3>{{ $title }}</h3>
        <div>
            @php
                $changeLog = $changeLog ?? [];
            @endphp

            @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                @if (is_array($changeLog) && count($changeLog))
                    <button type="button" class="btn btn-outline-warning accept-reject-btn" data-bs-toggle="modal"
                        data-bs-target="#changeLogModal">
                        View Change Log
                    </button>
                @endif
            @endif

            @if (isset($slug))
                <button type="button" class="btn btn-outline-info check-density-btn" data-slug="{{ $slug }}"
                    data-type="{{ $type }}">
                    Check Keyword Density
                </button>
                @if (!empty($primary_keyword))
                    <button type="button" class="btn btn-outline-success check-primary-density-btn"
                        id="checkPrimaryKeywordBtn" data-slug="{{ $slug }}"
                        data-keyword="{{ $primary_keyword }}" data-type="{{ $type }}">
                        Primary Keyword Check
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
<div class="modal fade" id="densityResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"> <!-- Optional -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Keyword Density</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="densityResultContent" style="max-height: 83vh; overflow-y: auto;">
                Loading...
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="primaryKeywordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Primary Keyword Check</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="primaryKeywordResult">
                Loading...
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeLogModal" tabindex="-1" aria-labelledby="changeLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeLogLabel">Change Log</h5>
                <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="overflow-y: auto;">
                <div class="scroll-wrapper table-responsive tableFixHead">
                    <table id="temp_table" class="table table-striped table-bordered mb-0"
                        style="table-layout: fixed; width: 100%;">
                        <thead class="table-dark">
                            <tr>
                                <th>Field Name</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($changeLog as $log)
                                <tr>
                                    <td>{{ $log->key ?? '' }}</td>
                                    <td>
                                        {!! App\Http\Controllers\AppBaseController::renderChangeValue($log->key, $log->old) !!}
                                    </td>
                                    <td>
                                        {!! App\Http\Controllers\AppBaseController::renderChangeValue($log->key, $log->new, $log->old) !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No changes found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    $(document).on('click', '#changeLogBtn', function() {
        $('#changeLogModal').modal('show');
    });

    $(document).on('click', '.check-density-btn', function() {
        const slug = $(this).data('slug');
        const type = $(this).data('type');

        $.ajax({
            url: "{{ route('density.check.slug') }}",
            method: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                slug: slug,
                type: type
            },
            beforeSend: function() {
                $('#densityResultContent').html('<div class="text-center">Loading...</div>');
                $('#densityResultModal').modal('show');
            },
            success: function(html) {
                $('#densityResultContent').html(html);
            },
            error: function() {
                $('#densityResultContent').html(
                    '<div class="text-danger">Failed to fetch keyword density.</div>');
            }
        });
    });

    // $('#checkPrimaryKeywordBtn').on('click', function() {
    $(document).on('click', '#checkPrimaryKeywordBtn', function() {
        let slug = $(this).data('slug');
        let keyword = $(this).data('keyword');
        let type = $(this).data('type');


        if (!slug || !keyword || !type) {
            alert("Slug or Primary Keyword missing.");
            return;
        }

        $.ajax({
            url: "{{ route('density-checker.primary-check') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                slug: slug,
                type: type,
                keyword: keyword
            },
            beforeSend: function() {
                $('#primaryKeywordResult').html('Loading...');
                $('#primaryKeywordModal').modal('show');
            },
            success: function(res) {
                $('#primaryKeywordResult').html(res.html);
            },
            error: function(xhr) {
                $('#primaryKeywordResult').html(
                    '<div class="text-danger">Error occurred while checking.</div>');
            }
        });
    });
</script>

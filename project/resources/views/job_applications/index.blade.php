@include('layouts.masterhead')

<div class="main-container">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box d-flex flex-column" style="height: 90vh; overflow: hidden;">

                {{-- Top Row --}}
                <div class="pd-20 d-flex justify-content-between align-items-center">
                    <h4 class="text-blue h4">Job Applications</h4>
                </div>

                {{-- Filters --}}
                <div class="pd-20 bg-light border-top border-bottom">
                    <form action="{{ route('job_applications.index') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Search Candidate</label>
                                    <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Position</label>
                                    <select name="job_id" class="form-control">
                                        <option value="">All Positions</option>
                                        @foreach($jobOpenings as $job)
                                            <option value="{{ $job->id }}" {{ request('job_id') == $job->id ? 'selected' : '' }}>
                                                {{ $job->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Employment Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">All Types</option>
                                        @foreach($jobTypes as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0 pb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="actively_hiring" value="1" class="custom-control-input" id="activelyHiring" {{ request('actively_hiring') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="activelyHiring">Actively Hiring Only</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('job_applications.index') }}" class="btn btn-outline-secondary ml-1">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success mx-2 mt-2">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Scrollable Table --}}
                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table class="table table-striped table-bordered mb-0" id="application_table">
                            <thead>
                                <tr class="bg-light">
                                    <th>ID</th>
                                    <th>Apply Time</th>
                                    <th>Candidate</th>
                                    <th>Job Title</th>
                                    <th>Type</th>
                                    <th>Phone</th>
                                    <th>Resume</th>
                                    <th class="datatable-nosort text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($applications as $app)
                                    <tr>
                                        <td>#{{ $app->id }}</td>
                                        <td>
                                            <span class="font-weight-bold">{{ $app->created_at->format('d M Y') }}</span><br>
                                            <small class="text-muted">{{ $app->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $app->name }}</div>
                                            <div class="text-muted small">{{ $app->email }}</div>
                                        </td>
                                        <td>
                                            @if($app->jobOpening)
                                                <span class="text-blue font-weight-bold">{{ $app->jobOpening->title }}</span>
                                            @else
                                                <span class="badge badge-info text-white">General Application</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->jobOpening)
                                                <span class="badge badge-outline-secondary">{{ $app->jobOpening->type }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $app->phone }}</td>
                                        <td>
                                            @if($app->resume && \Storage::disk('local')->exists($app->resume))
                                                <a href="{{ route('job_applications.download', $app->id) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                            @elseif($app->resume_link)
                                                <a href="{{ $app->resume_link }}" target="_blank" class="btn btn-sm btn-outline-info shadow-sm text-info">
                                                    <i class="fa fa-link"></i> Link
                                                </a>
                                            @else
                                                <span class="text-muted small">None</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('job_applications.show', $app) }}"
                                                    class="btn btn-sm btn-info mr-2 shadow-sm text-white">
                                                    Details
                                                </a>

                                                <button class="btn btn-sm btn-outline-danger shadow-sm"
                                                    onclick="deleteApplication('{{ $app->id }}')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <div class="mb-2"><i class="fa fa-folder-open-o fa-3x"></i></div>
                                            No applications found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="my-1">

                {{-- Pagination --}}
                <div class="p-2">
                    {{ $applications->appends(request()->except('page'))->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteApplication(id) {
        let url = "{{ route('job_applications.destroy', ':id') }}";
        url = url.replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This application will be deleted permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    }
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    beforeSend: function () {
                        $('#main_loading_screen').show();
                    },
                    success: function (response) {
                        $('#main_loading_screen').hide();

                        Swal.fire(
                            'Deleted!',
                            'Application has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        $('#main_loading_screen').hide();

                        Swal.fire(
                            'Error!',
                            xhr.responseText,
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

</body>

</html>

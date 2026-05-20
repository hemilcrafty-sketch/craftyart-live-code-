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
                    <h4 class="text-blue h4">Job Openings</h4>
                    <a href="{{ route('job_openings.create') }}" class="btn btn-primary  px-4">
                        <i class="fa fa-plus"></i> Add Job
                    </a>
                </div>

                {{-- Filters --}}
                <div class="pd-20 bg-light border-top border-bottom">
                    <form action="{{ route('job_openings.index') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Search Position</label>
                                    <input type="text" name="search" class="form-control" placeholder="Title..."
                                        value="{{ request('search') }}">
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
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0 pb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="actively_hiring" value="1"
                                            class="custom-control-input" id="activelyHiring" {{ request('actively_hiring') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="activelyHiring">Actively Hiring
                                            Only</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('job_openings.index') }}" class="btn btn-outline-secondary ml-1">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success mx-3 mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Scrollable Table --}}
                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table class="table table-striped table-bordered mb-0" id="job_table">
                            <thead>
                                <tr class="bg-light">
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Hiring</th>
                                    <th class="datatable-nosort text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobOpenings as $job)
                                    <tr>
                                        <td>#{{ $job->id }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $job->title }}</div>
                                            <small class="text-muted"><code>{{ $job->slug }}</code></small>
                                        </td>
                                        <td>{{ $job->location }}</td>
                                        <td>
                                            <span class="badge badge-outline-secondary">{{ $job->type }}</span>
                                        </td>
                                        <td>
                                            @if($job->is_active)
                                                <span class="badge badge-success px-3">Active</span>
                                            @else
                                                <span class="badge badge-danger px-3">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($job->is_actively_hiring)
                                                <span class="badge badge-success text-white">Yes</span>
                                            @else
                                                <span class="badge badge-danger text-white">No</span>
                                            @endif

                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('job_openings.edit', $job) }}"
                                                    class="btn btn-sm btn-info mr-2 shadow-sm text-white">
                                                    Edit
                                                </a>

                                                <button class="btn btn-sm btn-outline-danger shadow-sm"
                                                    onclick="deleteJob('{{ $job->id }}')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <div class="mb-2"><i class="fa fa-folder-open-o fa-3x"></i></div>
                                            No job openings found matching your criteria.
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
                    {{ $jobOpenings->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteJob(id) {
        let url = "{{ route('job_openings.destroy', ':id') }}";
        url = url.replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This job opening will be deleted permanently!",
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
                            'Job opening has been deleted.',
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
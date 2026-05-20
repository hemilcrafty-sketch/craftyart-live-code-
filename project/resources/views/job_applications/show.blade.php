@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4 class="text-blue">Application Review</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('job_applications.index') }}">Job Applications</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Review</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <a href="{{ route('job_applications.index') }}" class="btn btn-outline-primary px-4">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 mb-30">
                    <div class="pd-20 card-box height-100-p">
                        <div class="text-center mb-4">
                            <div class="avatar-container mb-3">
                                <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                                    <i class="fa fa-user-o fa-2x text-blue"></i>
                                </div>
                            </div>
                            <h5 class="h5 mb-1">{{ $application->name }}</h5>
                            <div class="text-muted font-14 mb-2">Candidate for:</div>
                            @if($application->jobOpening)
                                <div class="text-blue font-weight-bold h6 mb-1">{{ $application->jobOpening->title }}</div>
                                <span class="badge badge-secondary badge-pill px-3">{{ $application->jobOpening->type }}</span>
                            @else
                                <span class="badge badge-info text-white badge-pill px-3">General Application</span>
                            @endif
                        </div>

                        <div class="profile-info border-top mt-4 pt-4">
                            <h5 class="mb-20 h5 text-blue">Contact Details</h5>
                            <ul class="list-unstyled">
                                <li class="mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-sm btn-outline-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; pointer-events: none;">
                                            <i class="fa fa-envelope-o"></i>
                                        </div>
                                        <div>
                                            <span class="d-block font-12 text-muted">Email Address</span>
                                            <a href="mailto:{{ $application->email }}" class="text-dark font-weight-bold font-14">{{ $application->email }}</a>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-sm btn-outline-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; pointer-events: none;">
                                            <i class="fa fa-phone"></i>
                                        </div>
                                        <div>
                                            <span class="d-block font-12 text-muted">Phone Number</span>
                                            <a href="tel:{{ $application->phone }}" class="text-dark font-weight-bold font-14">{{ $application->phone }}</a>
                                        </div>
                                    </div>
                                </li>
                                @if($application->portfolio_link)
                                <li class="mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-sm btn-outline-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; pointer-events: none;">
                                            <i class="fa fa-globe"></i>
                                        </div>
                                        <div>
                                            <span class="d-block font-12 text-muted">Portfolio</span>
                                            <a href="{{ $application->portfolio_link }}" target="_blank" class="text-primary font-weight-bold font-14 break-all">{{ $application->portfolio_link }}</a>
                                        </div>
                                    </div>
                                </li>
                                @endif
                                <li class="mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="btn btn-sm btn-outline-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; pointer-events: none;">
                                            <i class="fa fa-calendar-o"></i>
                                        </div>
                                        <div>
                                            <span class="d-block font-12 text-muted">Applied On</span>
                                            <span class="text-dark font-weight-bold font-14">{{ $application->created_at->format('d M Y, h:i A') }}</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-8 col-md-7 col-sm-12 mb-30">
                    <div class="card-box height-100-p overflow-hidden">
                        <div class="profile-tab height-100-p">
                            <div class="tab height-100-p">
                                <ul class="nav nav-tabs customtab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#application_details" role="tab">Submission Details</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="application_details" role="tabpanel">
                                        <div class="pd-20">
                                            @if($application->resume)
                                            <div class="mb-4">
                                                <h6 class="mb-10 text-blue h6"><i class="fa fa-file-pdf-o mr-2"></i> Resume / CV</h6>
                                                <div class="p-4 border rounded bg-light d-flex justify-content-between align-items-center shadow-sm">
                                                    <div>
                                                        <div class="font-weight-bold text-dark">{{ $application->name }}_Resume</div>
                                                        <small class="text-muted">Click the button to view or download the file</small>
                                                    </div>
                                                    <a href="{{ route('job_applications.download', $application->id) }}" class="btn btn-primary px-4">
                                                        <i class="fa fa-download mr-1"></i> Download Resume
                                                    </a>
                                                </div>
                                            </div>
                                            @elseif($application->resume_link)
                                            <div class="mb-4">
                                                <h6 class="mb-10 text-blue h6"><i class="fa fa-link mr-2"></i> Resume Link</h6>
                                                <div class="p-4 border rounded bg-light d-flex justify-content-between align-items-center shadow-sm">
                                                    <div>
                                                        <div class="font-weight-bold text-dark text-break">{{ $application->resume_link }}</div>
                                                        <small class="text-muted">The candidate provided a link to their resume</small>
                                                    </div>
                                                    <a href="{{ $application->resume_link }}" target="_blank" class="btn btn-primary px-4">
                                                        <i class="fa fa-external-link mr-1"></i> Open Link
                                                    </a>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="mb-4">
                                                <h6 class="mb-10 text-blue h6"><i class="fa fa-commenting-o mr-2"></i> Cover Letter / Message</h6>
                                                <div class="p-3 border rounded bg-white" style="white-space: pre-wrap; min-height: 150px; line-height: 1.6; border-left: 4px solid #1b00ff !important;">
                                                    @if($application->cover_letter)
                                                        {{ $application->cover_letter }}
                                                    @else
                                                        <span class="text-muted italic">No cover letter provided.</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($application->jobOpening)
                                            <div class="mb-4 pt-3 border-top">
                                                <h6 class="mb-10 text-muted h6">Position Information</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Department</small>
                                                        <span>{{ $application->jobOpening->department ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Location</small>
                                                        <span>{{ $application->jobOpening->location }}</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Experience</small>
                                                        <span>{{ $application->jobOpening->experience }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
</body>
</html>

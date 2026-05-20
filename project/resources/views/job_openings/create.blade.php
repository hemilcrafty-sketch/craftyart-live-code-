@include('layouts.masterhead')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')

<style>
    .job-section-header {
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .job-section-header i {
        margin-right: 10px;
        color: #1b00ff;
    }
    .job-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .job-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #cbd5e0;
    }
    .repeatable-row {
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        background: #f8fafc;
        transition: all 0.2s;
    }
    .repeatable-row:hover {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
    }
    .step-badge {
        width: 36px;
        height: 36px;
        background: #1b00ff;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(27, 0, 255, 0.2);
    }
    .btn-remove-item {
        color: #ef4444;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 4px 8px;
        background: transparent;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .btn-remove-item:hover {
        color: #b91c1c;
        background: #fef2f2;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-remove-item i {
        font-size: 0.95rem;
    }
    .btn-add-step {
        background: #f1f5f9;
        color: #475569;
        border: 2px dashed #cbd5e0;
        border-radius: 10px;
        padding: 12px;
        width: 100%;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-add-step:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
        color: #1e293b;
    }
</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pd-20 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="text-blue h4 mb-0">Create Job Opening</h4>
                    <a href="{{ route('job_openings.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fa fa-arrow-left"></i> Back to list
                    </a>
                </div>

                <div class="pd-20">
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="post" action="{{ route('job_openings.store') }}" id="jobOpeningForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="job-card pd-20 mb-30">
                            <div class="job-section-header">
                                <i class="fa fa-info-circle"></i>
                                <h4 class="h4 mb-0">Basic Information</h4>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title" id="jobTitle" value="{{ old('title') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Slug <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="slug" id="jobSlug" value="{{ old('slug') }}" required>
                                        <small class="text-muted">Auto-generated from title if left empty.</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark d-block">Job Icon (SVG/PNG)</label>
                                        <input type="file" class="form-control height-auto dynamic-file" 
                                            data-imgstore-id="icon"
                                            data-value="{{ $contentManager::getStorageLink(old('icon')) }}"
                                            data-nameset="true" data-validate="false"
                                            accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Department</label>
                                        <input type="text" class="form-control" name="department" value="{{ old('department') }}" placeholder="e.g. Engineering">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Experience <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="experience" value="{{ old('experience') }}" placeholder="e.g. 1-3 Years" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="location" value="{{ old('location') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Employment Type <span class="text-danger">*</span></label>
                                        <select class="form-control" name="type" required>
                                            <option value="full-time" {{ old('type') === 'full-time' ? 'selected' : '' }}>Full-time</option>
                                            <option value="part-time" {{ old('type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                                            <option value="contract" {{ old('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                            <option value="internship" {{ old('type') === 'internship' ? 'selected' : '' }}>Internship</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Salary Range</label>
                                        <input type="text" class="form-control" name="salary_range" value="{{ old('salary_range') }}" placeholder="e.g. $5k - $8k">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Short Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" rows="4" required>{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="h5 text-blue mb-3"><i class="fa fa-tasks mr-2"></i> Key Responsibilities</h5>
                                    <div class="repeatable-container" data-name="responsibilities">
                                        <div class="repeatable-row d-flex align-items-center mb-2 px-3 py-2 bg-light border-0">
                                            <input type="text" name="responsibilities[]" class="form-control" placeholder="e.g. Develop new features">
                                            <button type="button" class="btn-remove-item btn-remove-row ml-2" title="Remove Responsibility">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-link btn-add-row px-0 font-weight-bold">+ Add Responsibility</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="h5 text-blue mb-3"><i class="fa fa-mortar-board mr-2"></i> Requirements</h5>
                                    <div class="repeatable-container" data-name="requirements">
                                        <div class="repeatable-row d-flex align-items-center mb-2 px-3 py-2 bg-light border-0">
                                            <input type="text" name="requirements[]" class="form-control" placeholder="e.g. 3+ years of Laravel experience">
                                            <button type="button" class="btn-remove-item btn-remove-row ml-2" title="Remove Requirement">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-link btn-add-row px-0 font-weight-bold">+ Add Requirement</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Perks and Tools --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="h5 text-blue mb-3"><i class="fa fa-gift mr-2"></i> Perks & Benefits</h5>
                                    <div class="repeatable-container" data-name-base="perks">
                                        <div class="repeatable-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="small font-weight-bold text-muted">Icon</label>
                                                        <input type="file" class="form-control height-auto dynamic-file" 
                                                            data-imgstore-id="perks[0][icon]"
                                                            data-nameset="true" data-validate="false"
                                                            accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group mb-0">
                                                        <label class="small font-weight-bold text-muted">Title</label>
                                                        <input type="text" name="perks[0][title]" class="form-control" placeholder="e.g. Health Insurance">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right mt-2">
                                                <button type="button" class="btn-remove-item btn-remove-row">
                                                    <i class="fa fa-trash-o mr-1"></i> Remove Perk
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-link btn-add-row px-0 font-weight-bold">+ Add Benefit</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="h5 text-blue mb-3"><i class="fa fa-wrench mr-2"></i> Tools / Stack</h5>
                                    <div class="repeatable-container" data-name-base="tools">
                                        <div class="repeatable-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="small font-weight-bold text-muted">Icon</label>
                                                        <input type="file" class="form-control height-auto dynamic-file" 
                                                            data-imgstore-id="tools[0][icon]"
                                                            data-nameset="true" data-validate="false"
                                                            accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group mb-0">
                                                        <label class="small font-weight-bold text-muted">Tool Name</label>
                                                        <input type="text" name="tools[0][title]" class="form-control" placeholder="e.g. Adobe Illustrator">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right mt-2">
                                                <button type="button" class="btn-remove-item btn-remove-row">
                                                    <i class="fa fa-trash-o mr-1"></i> Remove Tool
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-link btn-add-row px-0 font-weight-bold">+ Add Tool</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Interview Steps --}}
                        <div class="mb-5 mt-5">
                            <div class="job-section-header">
                                <i class="fa fa-list-ol"></i>
                                <h5 class="h5 text-blue mb-0">Interview Steps</h5>
                            </div>
                            
                            <div id="stepsContainer">
                                @php 
                                    $steps = old('interview_steps', [['title' => '', 'description' => '']]); 
                                    if(empty($steps)) $steps = [['title' => '', 'description' => '']]; 
                                @endphp
                                @foreach($steps as $i => $step)
                                <div class="repeatable-row step-card-wrapper">
                                    <div class="d-flex align-items-start">
                                        <div class="step-badge mr-3">
                                            <span class="step-number">{{ $i + 1 }}</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold small text-muted">Step Title</label>
                                                        <input type="text" name="interview_steps[{{ $i }}][title]" 
                                                            class="form-control form-control-sm" 
                                                            value="{{ $step['title'] ?? '' }}" 
                                                            placeholder="e.g. HR Screening">
                                                    </div>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold small text-muted">Description</label>
                                                        <textarea name="interview_steps[{{ $i }}][description]" 
                                                            class="form-control form-control-sm" 
                                                            rows="2" 
                                                            placeholder="What happens in this stage?">{{ $step['description'] ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <button type="button" class="btn-remove-item" onclick="removeStep(this)">
                                                    <i class="fa fa-trash-o mr-1"></i> Remove Step
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <button type="button" class="btn-add-step btn-add-step-trigger mt-2">
                                <i class="fa fa-plus-circle mr-2"></i> Add Next Interview Step
                            </button>
                        </div>

                        {{-- Status --}}
                        <div class="mb-4 bg-light p-4 border-0 rounded-lg">
                            <h6 class="h6 mb-3 text-muted">Visibility Settings</h6>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" name="is_active" id="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold h6 mb-0" for="is_active">Job is active and visible</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="hidden" name="is_actively_hiring" value="0">
                                <input type="checkbox" class="custom-control-input" name="is_actively_hiring" id="is_actively_hiring" value="1" {{ old('is_actively_hiring', '1') === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-blue h6 mb-0" for="is_actively_hiring">Currently Actively Recruiting / Hiring</label>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <hr>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="fa fa-plus-circle mr-2"></i> Create Job Opening</button>
                            <a href="{{ route('job_openings.index') }}" class="btn btn-outline-secondary btn-lg px-5 ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
@include('layouts.masterscript')
<script>
    // Title to Slug helper
    const titleInput = document.getElementById('jobTitle');
    const slugInput = document.getElementById('jobSlug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!this.dataset.userEdited) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.userEdited = true;
            this.value = generateSlug(this.value);
        });
    }

    function generateSlug(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    // Repeatable Generic Logic
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-add-row')) {
            const container = e.target.closest('.repeatable-container');
            const rows = container.querySelectorAll('.repeatable-row');
            if (rows.length > 0) {
                const clone = rows[rows.length - 1].cloneNode(true);
                
                // Clean clone
                clone.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                clone.querySelectorAll('.text-danger').forEach(el => el.remove());
                clone.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea').forEach(el => el.value = '');
                clone.querySelectorAll('.dynamic-file-preview, .img-thumbnail').forEach(el => el.remove());
                // Insert before the add button
                container.insertBefore(clone, e.target.closest('.btn-add-row'));
                
                reindexRepeatable(container);

                // Localized enhancement to avoid calling the destructive global dynamicFileCmp()
                const localEnhance = (input) => {
                    if (!input || input.dataset.enhanced) return;
                    input.dataset.enhanced = "true";
                    const wrapper = document.createElement("div");
                    wrapper.classList.add("dynamic-file-input", "mb-3");
                    wrapper.style.width = "100%";
                    const controlsRow = document.createElement("div");
                    controlsRow.style.cssText = "display: flex; gap: 10px; align-items: center; margin-bottom: 10px;";
                    const dropdown = document.createElement("select");
                    dropdown.classList.add("form-select");
                    dropdown.style.width = "120px";
                    dropdown.innerHTML = `<option value="file" selected>File</option><option value="url">URL</option>`;
                    const urlInput = document.createElement("input");
                    urlInput.type = "text";
                    urlInput.classList.add("form-control", "url-input");
                    urlInput.placeholder = "Enter Image URL";
                    urlInput.style.display = "none";
                    urlInput.style.flex = "1";
                    const previewContainer = document.createElement("div");
                    previewContainer.style.cssText = "margin-top: 10px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px; background-color: #f8f9fa; display: none;";
                    const previewImg = document.createElement("img");
                    previewImg.classList.add("img-thumbnail", "image-preview");
                    previewImg.style.cssText = "max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain; display: block;";
                    const base64Input = document.createElement("input");
                    base64Input.type = "hidden";
                    if (input.dataset.imgstoreId) {
                        previewImg.id = input.dataset.imgstoreId;
                        base64Input.name = input.dataset.imgstoreId;
                    }
                    input.style.flex = "1";
                    input.parentNode.insertBefore(wrapper, input);
                    wrapper.appendChild(controlsRow);
                    controlsRow.appendChild(dropdown);
                    controlsRow.appendChild(input);
                    controlsRow.appendChild(urlInput);
                    previewContainer.appendChild(previewImg);
                    wrapper.appendChild(previewContainer);
                    wrapper.appendChild(base64Input);
                    if (input.dataset.accept) input.setAttribute("accept", input.dataset.accept);
                    if (input.dataset.value) {
                        urlInput.value = input.dataset.value;
                        previewImg.src = input.dataset.value;
                        base64Input.value = input.dataset.value;
                        previewContainer.style.display = "block";
                        if (input.dataset.value.startsWith("data:")) {
                            dropdown.value = "file";
                            input.style.display = "block";
                            urlInput.style.display = "none";
                        } else {
                            dropdown.value = "url";
                            input.style.display = "none";
                            urlInput.style.display = "block";
                        }
                    }
                    dropdown.addEventListener("change", function() {
                        const isFile = this.value === "file";
                        input.style.display = isFile ? "block" : "none";
                        urlInput.style.display = isFile ? "none" : "block";
                    });
                    input.addEventListener("change", function() {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                previewImg.src = e.target.result;
                                base64Input.value = e.target.result;
                                previewContainer.style.display = "block";
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                    urlInput.addEventListener("input", function() {
                        previewImg.src = this.value;
                        base64Input.value = this.value;
                        previewContainer.style.display = this.value ? "block" : "none";
                    });
                };

                clone.querySelectorAll('.dynamic-file').forEach(el => {
                    delete el.dataset.enhanced;
                    const wrapper = el.closest(".dynamic-file-input");
                    if (wrapper) {
                        wrapper.parentNode.insertBefore(el, wrapper);
                        wrapper.remove();
                    }
                    localEnhance(el);
                });
            }
        }

        if (e.target.closest('.btn-remove-row')) {
            const container = e.target.closest('.repeatable-container');
            const rows = container.querySelectorAll('.repeatable-row');
            if (rows.length > 1) {
                e.target.closest('.repeatable-row').remove();
                reindexRepeatable(container);
            } else {
                const row = e.target.closest('.repeatable-row');
                row.querySelectorAll('input, textarea').forEach(i => i.value = '');
                row.querySelectorAll('.dynamic-file-preview, .img-thumbnail').forEach(el => el.remove());
                row.querySelectorAll('.dynamic-file').forEach(el => {
                    el.dataset.value = '';
                    el.style.display = 'block';
                });
            }
        }
    });

    function reindexRepeatable(container) {
        const rows = container.querySelectorAll('.repeatable-row');
        rows.forEach((row, i) => {
            row.querySelectorAll('input, textarea, select').forEach(input => {
                // Update dynamic-file attributes if present
                if (input.dataset.imgstoreId) {
                    input.dataset.imgstoreId = input.dataset.imgstoreId.replace(/\[\d+\]/, `[${i}]`);
                }

                if (!input.name) return;
                
                // Handle standard indexed names
                input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
    }

    // Interview Steps Logic
    const stepsContainer = document.getElementById('stepsContainer');
    const addStepBtn = document.querySelector('.btn-add-step-trigger');
    
    if (addStepBtn) {
        addStepBtn.addEventListener('click', function() {
            const index = stepsContainer.querySelectorAll('.step-card-wrapper').length;
            const template = `
                <div class="repeatable-row step-card-wrapper">
                    <div class="d-flex align-items-start">
                        <div class="step-badge mr-3">
                            <span class="step-number">${index + 1}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small text-muted">Step Title</label>
                                        <input type="text" name="interview_steps[${index}][title]" 
                                            class="form-control form-control-sm" 
                                            placeholder="e.g. HR Screening">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small text-muted">Description</label>
                                        <textarea name="interview_steps[${index}][description]" 
                                            class="form-control form-control-sm" 
                                            rows="2" 
                                            placeholder="What happens in this stage?"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn-remove-step" onclick="removeStep(this)">
                                    <i class="fa fa-trash-o mr-1"></i> Remove Step
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            stepsContainer.insertAdjacentHTML('beforeend', template);
        });
    }

    function removeStep(btn) {
        const wrapper = btn.closest('.step-card-wrapper');
        const rows = stepsContainer.querySelectorAll('.step-card-wrapper');
        
        if (rows.length > 1) {
            wrapper.remove();
            reindexSteps();
        } else {
            wrapper.querySelectorAll('input, textarea').forEach(i => i.value = '');
        }
    }

    function reindexSteps() {
        stepsContainer.querySelectorAll('.step-card-wrapper').forEach((card, i) => {
            const badge = card.querySelector('.step-number');
            if (badge) badge.textContent = i + 1;
            
            card.querySelectorAll('input, textarea').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
    }
</script>
</body>
</html>

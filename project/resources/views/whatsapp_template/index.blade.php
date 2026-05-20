@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<div class="main-container">
    <div class="min-height-200px">
        {{-- TEMPLATES CARD --}}
        <div class="card-box d-flex flex-column mt-3" style="height: 73vh; overflow: hidden;">
            <div class="row justify-content-between">
                <div class="col-md-2 m-1">
                    <a href="#" class="btn btn-primary" id="btnAddTemplate">Add Whatsapp Template</a>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto">
                <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                        <tr>
                            <th style="width:10px">Id</th>
                            <th>Campaign Name</th>
                            <th style="width:30px">Params Count</th>
                            <th>Template Params</th>
                            <th>Media URL</th>
                            <th>URL</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="tplBody">
                        @foreach ($templates as $t)
                        <tr id="tpl-{{ $t->id }}">
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->campaign_name }}</td>
                            <td>{{ $t->template_params_count }}
                            </td>
                            <td>
                                @if(is_array($t->template_params) && count($t->template_params) > 0)
                                @foreach($t->template_params as $param)
                                <span class="">{{ $param }}</span><br>
                                @endforeach
                                @else
                                <span class="text-muted">No parameters</span>
                                @endif
                            </td>
                            <td>{{ $t->media_url ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($t->url)
                                <a href="{{ $t->url }}" target="_blank" data-toggle="tooltip" title="Click to view full image">
                                    <img src="{{ $t->url }}" alt="Template Media" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                                </a>
                                @else
                                <span class="text-muted">No URL</span>
                                @endif
                            </td>
                            <td>
                                <button class="dropdown-item btn-edit-template"
                                        data-id="{{ $t->id }}"><i class="dw dw-edit2"></i> Edit</button>
                                <button class="dropdown-item btn-delete-template text-danger"
                                        data-id="{{ $t->id }}"><i class="dw dw-delete-3"></i>
                                    Delete</button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                {{ $templates->links() }}
            </div>
        </div>

    </div>
</div>

{{-- Template Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title template-modal-title">Add Template</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="templateResult"></div>
                <form id="templateForm">
                    @csrf
                    <input type="hidden" id="templateId" name="id">
                    <div class="form-group">
                        <label>Campaign Name</label>
                        <input type="text" class="form-control" name="campaign_name" id="campaignName" required>
                    </div>
                    <div class="form-group">
                        <label>Template Variables</label>
                        <div id="dynamicParams"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddVariable">
                            <i class="fa fa-plus"></i> Add Variable
                        </button>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="mediaUrl" name="media_url" value="1">
                        <label class="form-check-label" for="mediaUrl">Media URL required?</label>
                    </div>
                    <div class="form-group">
                        <label>URL (if media_url = true)</label>
                        <input type="file" class="form-control dynamic-file" id="mediaField" data-imgstore-id="mediaUrlField"
                               data-required=false
                               data-value=""
                               data-nameset=true data-accept=".jpg, .jpeg, .webp, .svg"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitTemplate">Submit</button>
                </form>
            </div>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </div>
</div>

@include('layouts.masterscript')
<script>
    let PARAM_ENUM = {!! json_encode(\App\Enums\WhatsappParams::list()) !!};
</script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function createParamDropdown(index, selectedValue = '') {
            let html = `<div class="input-group mb-2 param-row" data-index="${index}">`;
            html += `<select class="form-control param-select" name="template_params[${index}]" required>`;
            html += `<option value="">-- Select Variable --</option>`;

            // Handle grouped parameters
            Object.keys(PARAM_ENUM).forEach(key => {
                console.log(" Params key ",key)
                if (Array.isArray(PARAM_ENUM[key])) {
                    // It's a group (UserData, PlanData, etc.)
                    html += `<optgroup label="${key}">`;

                    PARAM_ENUM[key].forEach(field => {
                        let value = `${key}.${field}`;
                        let selected = value === selectedValue ? "selected" : "";
                        html += `<option value="${value}" ${selected}>${field}</option>`;
                    });

                    html += `</optgroup>`;
                } else {
                    let value = key;
                    let selected = value === selectedValue ? "selected" : "";
                    html += `<option value="${value}" ${selected}>${value}</option>`;
                }
            });

            html += `</select>`;
            html += `<div class="input-group-append">`;
            html += `<button type="button" class="btn btn-outline-danger btn-remove-param">`;
            html += `<i class="fa fa-trash"></i>`;
            html += `</button>`;
            html += `</div>`;
            html += `</div>`;
            return html;
        }

        // Add variable button handler
        $('#btnAddVariable').on('click', function() {
            let paramRows = $('.param-row');
            let newIndex = paramRows.length;
            $('#dynamicParams').append(createParamDropdown(newIndex));
        });

        // Remove parameter handler
        $(document).on('click', '.btn-remove-param', function() {
            let row = $(this).closest('.param-row');

            // Remove the row
            row.remove();

            // Re-index remaining rows
            reindexParamRows();
        });

        function reindexParamRows() {
            $('.param-row').each(function(newIndex) {
                $(this).attr('data-index', newIndex);
                let select = $(this).find('.param-select');
                select.attr('name', `template_params[${newIndex}]`);
            });
        }

        $('#btnAddTemplate').on('click', function() {
            $('#templateResult').html('');
            $('#templateForm')[0].reset();
            $('#templateId').val('');
            resetDynamicFileValue("mediaField")
            $('#dynamicParams').html(''); // Clear dynamic params - no default dropdown
            $('.template-modal-title').text('Add Template');
            $('#templateModal').modal('show');
        });

        $('#mediaUrl').on('change', function() {
            // $('#mediaUrlField').prop('disabled', !this.checked).val('');
        });

        // Submit template (create or update depending on hidden id)
        $('#templateForm').submit(function(e) {
            e.preventDefault();
            $('#submitTemplate').prop('disabled', true);
            $('#templateResult').html('<div class="alert alert-info">Processing...</div>');

            const formData = new FormData(this);

            let id = $('#templateId').val();
            let url = "{{ route('whatsapp_template.store') }}";
            let method = 'POST';

            // Check if there are any variables
            let paramCount = $('.param-select').length;

            // If no variables, allow submission with empty params
            if (paramCount === 0) {
                // Proceed with empty params
            } else {
                // Validate that all dropdowns have values if there are any
                let allSelected = true;
                $('.param-select').each(function() {
                    if ($(this).val() === '') {
                        allSelected = false;
                        return false; // break the loop
                    }
                });

                if (!allSelected) {
                    $('#templateResult').html('<div class="alert alert-danger">Please select a value for all template variables.</div>');
                    $('#submitTemplate').prop('disabled', false);
                    return;
                }
            }

            let payload = {
                campaign_name: $('#campaignName').val(),
                template_params_count: paramCount,
                template_params: $('.param-select').map(function () {
                    return $(this).val();
                }).get(),
                media_url: $('#mediaUrl').is(':checked') ? 1 : 0,
                url: formData.get("mediaUrlField")
            };

            if (id) {
                url = "{{ url('whatsapp_template') }}/" + id;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: payload,
            }).done(function(res) {
                $('#templateResult').html('<div class="alert alert-success">' + (res.message ??
                    'Saved') + '</div>');
                setTimeout(function() {
                    location.reload();
                }, 800);
            }).fail(function(xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(i => '<p>' + i[0] + '</p>')
                        .join('');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#templateResult').html('<div class="alert alert-danger">' + msg + '</div>');
            }).always(function() {
                $('#submitTemplate').prop('disabled', false);
            });
        });

        $(document).on('click', '.btn-edit-template', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            $('#templateResult').html('');
            $.get("{{ url('whatsapp_template') }}/" + id + "/edit", function(res) {
                if (res.success) {
                    let t = res.template;
                    $('#templateId').val(t.id);
                    $('#campaignName').val(t.campaign_name);

                    // Clear existing params
                    $('#dynamicParams').html('');

                    // Add param dropdowns based on template data
                    if (t.template_params && t.template_params.length > 0) {
                        t.template_params.forEach((value, index) => {
                            $('#dynamicParams').append(createParamDropdown(index, value));
                        });
                    }
                    // If no params, don't add any dropdown (empty state)

                    if (t.media_url) {
                        $('#mediaUrl').prop('checked', true);
                        $('#mediaField').attr('data-value', t.url);
                        dynamicFileCmp();
                    } else {
                        $('#mediaUrl').prop('checked', false);
                        // $('#mediaUrlField').prop('disabled', true).val('');
                    }
                    $('.template-modal-title').text('Edit Template');
                    $('#templateModal').modal('show');
                } else {
                    alert(res.message || 'Could not fetch record');
                }
            }).fail(function() {
                alert('Could not fetch record');
            });
        });

        // Delete template
        $(document).on('click', '.btn-delete-template', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this template?')) return;
            let id = $(this).data('id');
            $.ajax({
                url: "{{ url('whatsapp_template') }}/" + id,
                method: "DELETE"
            }).done(function(res) {
                if (res.success) {
                    $('#tpl-' + id).remove();
                } else {
                    alert(res.message || 'Could not delete');
                }
            }).fail(function() {
                alert('Could not delete');
            });
        });

    });
</script>

</body>
</html>
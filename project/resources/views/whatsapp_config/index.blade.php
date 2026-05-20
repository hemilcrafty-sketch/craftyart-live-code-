@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<div class="main-container">
    <div class="min-height-200px">

        {{-- TEMPLATES CARD --}}
        <div class="card-box d-flex flex-column mt-3" style="height: 92vh; overflow: hidden;">
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
                                <th>Id</th>
                                <th>Campaign Name</th>
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
                                    <td>{{ is_array($t->template_params_count) ? json_encode($t->template_params_count) : $t->template_params_count }}
                                    </td>
                                    <td>{{ $t->media_url ? 'Yes' : 'No' }}</td>
                                    <td>{{ $t->url }}</td>
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
                        <label>Template Params Count</label>
                        <input type="number" class="form-control" name="template_params_count" id="templateParams">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="mediaUrl" name="media_url" value="1">
                        <label class="form-check-label" for="mediaUrl">Media URL required?</label>
                    </div>
                    <div class="form-group">
                        <label>URL (if media_url = true)</label>
                        <input type="url" class="form-control" name="url" id="mediaUrlField" disabled>
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
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#btnAddTemplate').on('click', function() {
            $('#templateResult').html('');
            $('#templateForm')[0].reset();
            $('#templateId').val('');
            $('#mediaUrlField').prop('disabled', true).val('');
            $('.template-modal-title').text('Add Template');
            $('#templateModal').modal('show');
        });

        $('#mediaUrl').on('change', function() {
            $('#mediaUrlField').prop('disabled', !this.checked).val('');
        });

        // Submit template (create or update depending on hidden id)
        $('#templateForm').submit(function(e) {
            e.preventDefault();
            $('#submitTemplate').prop('disabled', true);
            $('#templateResult').html('<div class="alert alert-info">Processing...</div>');

            let id = $('#templateId').val();
            let url = "{{ route('whatsapp_template.store') }}";
            let method = 'POST';

            let payload = {
                campaign_name: $('#campaignName').val(),
                template_params_count: $('#templateParams').val(),
                media_url: $('#mediaUrl').is(':checked') ? 1 : 0,
                url: $('#mediaUrlField').val()
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
                    
                    if (typeof t.template_params_count === 'object') {
                        $('#templateParams').val(t.template_params_count);
                    } else {
                        $('#templateParams').val(t.template_params_count);
                    }
                    if (t.media_url) {
                        $('#mediaUrl').prop('checked', true);
                        $('#mediaUrlField').prop('disabled', false).val(t.url);
                    } else {
                        $('#mediaUrl').prop('checked', false);
                        $('#mediaUrlField').prop('disabled', true).val('');
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

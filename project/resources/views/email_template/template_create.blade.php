@include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display:flex;flex-direction:column;height:90vh;overflow:hidden;">
                <div class="row justify-content-between mb-2">
                    <div class="col-md-3 m-2">
                        <button type="button" class="btn btn-primary" id="addTemplate">
                            + Add Template
                        </button>
                    </div>
                </div>
                <div class="scroll-wrapper table-responsive tableFixHead"
                     style="max-height:calc(110vh - 220px)!important">
                    <table id="template_table" class="table table-striped table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Preview</th>
                            <th width="150px">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($templateDatas as $templateData)
                        <tr id="row_{{ $templateData->id }}">
                            <td>{{ $templateData->id }}</td>
                            <td>{{ $templateData->name }}</td>
                            <td>{{ $templateData->status == 1 ? 'Active' : 'Inactive' }}</td>
                            <td><button class="btn btn-outline-primary preview-template" data-id="{{ $templateData->id }}">
                                    Preview
                                </button></td>
                            <td class="d-flex">
                                <button class="dropdown-item edit-template" data-id="{{ $templateData->id }}">
                                    <i class="dw dw-edit2"></i> Edit
                                </button>
                                <button class="dropdown-item delete-template" data-id="{{ $templateData->id }}">
                                    <i class="dw dw-delete-3"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="my-1">
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="template_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <form id="templateForm">@csrf
                <input type="hidden" name="id" id="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Template</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div id="formErrors" class="text-danger mb-2"></div>
                    <div class="form-group">
                        <h6>Template Name</h6>
                        <input class="form-control" name="name" id="name" placeholder="Enter template name">
                    </div>
                    <div class="form-group">
                        <h6>Email Template</h6>
                        <textarea class="form-control" name="email_template" id="email_template" cols="50"
                                  placeholder="Enter email template"></textarea>
                    </div>
                    <label class="fw-bold ml-2 mt-2">Status :</label>
                    <div class="d-flex">
                        <select class="form-control m-2 w-100" name="status" id="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTemplate">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $(document).ready(function() {

        $(document).on("click", ".preview-template", function() {
            let id = $(this).data("id");
            let url = "{{ route('email_template.preview', ':id') }}".replace(':id', id);
            window.open(url, '_blank'); // open in new tab
        });

        // Open Add Modal
        $(document).on("click", "#addTemplate", function() {
            $("#templateForm")[0].reset();
            $("#formErrors").html('');
            $("#id").val('');
            $("#modalTitle").text("Add Template");
            $("#template_modal").modal("show");
        });
        // Edit Template
        $(document).on("click", ".edit-template", function() {
            let id = $(this).data("id");
            $.get("{{ route('email_template.editTemplate', ':id') }}".replace(':id', id), function(
                res) {
                if (res.status) {
                    let template = res.data;
                    $("#id").val(template.id);
                    $("#name").val(template.name);
                    $("#email_template").val(template.email_template_content ?? '');
                    $("#status").val(template.status);
                    $("#modalTitle").text("Edit Template");
                    $("#template_modal").modal("show");
                }
            });
        });
        // Save (Add / Update)
        $(document).on("submit", "#templateForm", function(e) {
            e.preventDefault();
            let id = $("#id").val();
            let url = id ?
                "{{ route('email_template.storeTemplate', ':id') }}".replace(':id', id) :
                "{{ route('email_template.storeTemplate') }}";
            let formData = new FormData(this);
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(response.msg);
                    if (response.status) location.reload();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let error_html = '<ul>';
                        $.each(errors, function(key, value) {
                            error_html += '<li>' + value[0] + '</li>';
                        });
                        error_html += '</ul>';
                        $("#formErrors").html(error_html);
                    } else {
                        alert("Something went wrong!");
                    }
                }
            });
        });
        // Delete Template
        $(document).on("click", ".delete-template", function() {
            if (!confirm("Are you sure to delete this template?")) return;
            let id = $(this).data("id");
            $.ajax({
                url: "{{ route('email_template.deleteTemplate', ':id') }}".replace(':id', id),
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status) location.reload();
                    else alert('Failed to delete template');
                }
            });
        });
    });
</script>
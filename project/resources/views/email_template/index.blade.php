@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box p-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('email_report.view') }}" class="btn btn-success">View Email Report</a>
                </div>
                <form method="post" id="add_emailtemplate_item_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="emailtemplate_item_id">


                    <div class="form-group">
                        <h6>Choose Email Template</h6>
                        <div class="d-flex">
                            <select id="email_template_id" class="form-control" name="email_template_id">
                                <option value="">-- Select Template --</option>
                                @foreach ($emailTemplates as $tpl)
                                <option value="{{ $tpl->id }}">
                                    {{ $tpl->id }} - {{ $tpl->name }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" id="preview_template_btn" class="btn btn-info ml-2" disabled>
                                Preview
                            </button>
                        </div>
                    </div>

                    <div class="form-group" id="promo_code_container">
                        <h6>Select Promo Code</h6>
                        <select id="promo_code" name="promo_code" class="form-control">
                            <option value="">-- Select Promo Code --</option>
                            @foreach ($promoCodes as $promo)
                            <option value="{{ $promo->id }}">{{ $promo->promo_code }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="get_plan">
                        <h6>Select Suggested Plan</h6>
                        <select id="plan_id" name="plan_id" class="form-control">
                            <option value="">-- Select Plan --</option>
                            @foreach ($getPlans as $getPlan)
                            <option value="{{ $getPlan->id }}">{{ $getPlan->package_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Email Subject</h6>
                        <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
                    </div>

                    <div class="form-group">
                        <h6>Select Type</h6>
                        <select id="template_type" class="form-control" name="template_type" required>
                            <option value="1">Offer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h6>Select Users Type</h6>
                        <select id="select_users_type" class="form-control" name="select_users_type" required>
                            <option selected disabled>Select Audience</option>
                            <option value="1">All</option>
                            <option value="2">Premium</option>
                            <option value="3">Custom</option>
                            <option value="4">Expired Subscriber</option>
                            <option value="5">Active Subscriber (Monthly)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select User</label>
                        <select id="user_id" name="user_id[]" class="form-control border" multiple="multiple"></select>
                    </div>

                    <div class="form-group">
                        <h6>Auto Pause After Emails Sent</h6>
                        <input type="number" min="1" class="form-control" name="auto_pause_count"  required placeholder="Enter number of emails for auto pause">
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: 'Search email...',
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('get_email_tmp') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
                cache: true
            }
        });

        $('#select_users_type').on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue === '1' || selectedValue === '2' || selectedValue === '4' || selectedValue === '5') {
                $('#user_id').prop('disabled', true);
            } else if (selectedValue === '3') {
                $('#user_id').prop('disabled', false);
            }
        }).trigger('change');

        $('#email_template_id').on('change', function() {
            if ($(this).val() !== '') {
                $('#email_template').prop('disabled', true).val('');
                $('#preview_template_btn').prop('disabled', false);
            } else {
                $('#email_template').prop('disabled', false);
                $('#preview_template_btn').prop('disabled', true);
            }
        });

        $('#preview_template_btn').on('click', function() {
            let tplId = $('#email_template_id').val();
            if (tplId) {
                let url = "{{ url('email-template/preview') }}/" + tplId;
                window.open(url, '_blank');
            }
        });

        $('#add_emailtemplate_item_form').on('submit', function(e) {
            e.preventDefault();
            let usersType = $('#select_users_type').val();
            let selectedUsers = $('#user_id').val();
            if (usersType === '3' && (!selectedUsers || selectedUsers.length === 0)) {
                alert("Please select at least one user.");
                return;
            }
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('email_template.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(response.msg);
                    if (response.status) {
                        $('#add_emailtemplate_item_form')[0].reset();
                        $('#user_id').val(null).trigger('change');
                        window.location.href = "{{ route('email_report.view') }}";
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let error_html = '<ul>';
                        $.each(errors, function(key, value) {
                            error_html += '<li>' + value[0] + '</li>';
                        });
                        error_html += '</ul>';
                        alert(error_html);
                    } else {
                        alert("Something went wrong!");
                    }
                }
            });
        });
    });
</script>
</body>
</html>

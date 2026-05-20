@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box p-3">
                <div class="d-flex justify-content-between mb-3">
                    <h4 class="text-dark">Create Campaign</h4>
                    <div>
                        {{-- <a href="{{ route('email_report.view') }}" class="btn btn-outline-primary btn-sm mr-2">Email Report</a> --}}
                        {{-- <a href="{{ route('whatsapp_report.view') }}" class="btn btn-outline-success btn-sm">WhatsApp Report</a> --}}
                        <a href="{{ route('campaign.report') }}" class="btn btn-outline-primary btn-sm">Campaign Report</a>
                    </div>
                </div>

                <form method="post" id="add_campaign_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="campaign_item_id">

                    <!-- Campaign Type Selection -->
                    <div class="form-group mb-3">
                        <h6 class="text-dark font-weight-bold mb-2">Campaign Type</h6>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="card p-2 border border-primary campaign-type-card" id="email_card">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="email_campaign" name="campaign_type[]" value="email">
                                        <label class="form-check-label font-weight-bold text-dark" for="email_campaign">
                                            📧 Email Campaign
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center py-2">
                                <span class="text-muted">OR</span>
                            </div>
                            <div class="col-md-5">
                                <div class="card p-2 border border-success campaign-type-card" id="whatsapp_card">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="whatsapp_campaign" name="campaign_type[]" value="whatsapp">
                                        <label class="form-check-label font-weight-bold text-dark" for="whatsapp_campaign">
                                            💬 WhatsApp Campaign
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Campaign Section -->
                    <div id="email_section" style="display: none;" class="mb-3">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-dark">📧 Email Settings</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-dark">Email Template</label>
                                    <div class="d-flex">
                                        <select id="email_template_id" class="form-control" name="email_template_id" disabled>
                                            <option value="">-- Select Template --</option>
                                            @foreach ($emailTemplates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="preview_email_template_btn" class="btn btn-outline-info btn-sm ml-2" disabled>Preview</button>
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-dark">Email Subject</label>
                                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter email subject" disabled>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark">Email Type</label>
                                    <select id="template_type" class="form-control" name="template_type" disabled>
                                        <option value="1">Offer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Campaign Section -->
                    <div id="whatsapp_section" style="display: none;" class="mb-3">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-dark">💬 WhatsApp Settings</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark">WhatsApp Template</label>
                                    <select id="wp_template_id" class="form-control" name="wp_template_id" disabled>
                                        <option value="">-- Select Template --</option>
                                        @foreach ($wpTemplates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->campaign_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Settings -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 text-dark">⚙️ Campaign Settings</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-dark">Auto Pause After Sent</label>
                                <input type="number" min="1" class="form-control" name="auto_pause_count" placeholder="Enter number of messages for auto pause" required>
                                <small class="text-muted">Applies to both email and WhatsApp campaigns</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark">Promo Code</label>
                                        <select id="promo_code" name="promo_code" class="form-control">
                                            <option value="">-- Select Promo Code --</option>
                                            @foreach ($promoCodes as $promo)
                                            <option value="{{ $promo->id }}">{{ $promo->promo_code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark">Suggested Plan</label>
                                        <select id="plan_id" name="plan_id" class="form-control">
                                            <option value="">-- Select Plan --</option>
                                            @foreach ($getPlans as $getPlan)
                                            <option value="{{ $getPlan->id }}">{{ $getPlan->package_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-dark">Target Audience</label>
                                <select id="select_users_type" class="form-control" name="select_users_type" required>
                                    <option selected disabled>Select Audience Type</option>
                                    <option value="1">All Users</option>
                                    <option value="2">Premium Users</option>
                                    <option value="3">Custom Selection</option>
                                    <option value="4">Expired Subscribers</option>
                                    <option value="5">Active Monthly Subscribers</option>
                                    <option value="6">Inactive Drop User</option>
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Select Users (for Custom Audience)</label>
                                <select id="user_id" name="user_id[]" class="form-control border" multiple="multiple" disabled></select>
                                <small class="text-muted">Only required when "Custom Selection" is chosen</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-center mb-0">
                        <button class="btn btn-primary px-4" type="submit" id="submit_btn" disabled>
                            <i class="fa fa-play-circle"></i> Start Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<style>
    .campaign-type-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .campaign-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .campaign-type-card.active {
        background-color: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .form-check-input {
        margin-top: 0.2rem;
    }
    .card-header {
        padding: 0.5rem 1rem;
    }
    .card-body {
        padding: 1rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize Select2 for user selection
        $('#user_id').select2({
            placeholder: 'Type to search users by email...',
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

        // Card click handler for campaign type selection
        $('.campaign-type-card').on('click', function(e) {
            if (!$(e.target).is('input[type="checkbox"]')) {
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked'));
                checkbox.trigger('change');
            }
        });

        // Toggle campaign sections and enable/disable fields
        $('input[name="campaign_type[]"]').on('change', function() {
            const emailChecked = $('#email_campaign').is(':checked');
            const whatsappChecked = $('#whatsapp_campaign').is(':checked');

            // Update card styles
            $('#email_card').toggleClass('active', emailChecked);
            $('#whatsapp_card').toggleClass('active', whatsappChecked);

            // Toggle sections
            $('#email_section').toggle(emailChecked);
            $('#whatsapp_section').toggle(whatsappChecked);

            // Enable/disable email fields
            $('#email_template_id').prop('disabled', !emailChecked);
            $('#subject').prop('disabled', !emailChecked);
            $('#template_type').prop('disabled', !emailChecked);

            // Enable/disable WhatsApp fields
            $('#wp_template_id').prop('disabled', !whatsappChecked);

            // Enable/disable submit button
            $('#submit_btn').prop('disabled', !(emailChecked || whatsappChecked));

            // Update submit button text
            updateSubmitButtonText(emailChecked, whatsappChecked);
        });

        // User type selection handler
        $('#select_users_type').on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue === '1' || selectedValue === '2' || selectedValue === '4' || selectedValue === '5') {
                $('#user_id').prop('disabled', true).val(null).trigger('change');
            } else if (selectedValue === '3') {
                $('#user_id').prop('disabled', false);
            }
        });

        // Email template preview
        $('#email_template_id').on('change', function() {
            $('#preview_email_template_btn').prop('disabled', $(this).val() === '');
        });

        $('#preview_email_template_btn').on('click', function() {
            let tplId = $('#email_template_id').val();
            if (tplId) {
                let url = "{{ url('email-template/preview') }}/" + tplId;
                window.open(url, '_blank');
            }
        });

        // Form submission
        $('#add_campaign_form').on('submit', function(e) {
            e.preventDefault();

            // Validate campaign type selection
            const emailChecked = $('#email_campaign').is(':checked');
            const whatsappChecked = $('#whatsapp_campaign').is(':checked');

            if (!emailChecked && !whatsappChecked) {
                alert("Please select at least one campaign type (Email or WhatsApp).");
                return;
            }

            // Validate user selection for custom type
            let usersType = $('#select_users_type').val();
            let selectedUsers = $('#user_id').val();
            if (usersType === '3' && (!selectedUsers || selectedUsers.length === 0)) {
                alert("Please select at least one user for custom audience.");
                return;
            }

            // Validate email-specific fields if email campaign is selected
            if (emailChecked) {
                const emailTemplate = $('#email_template_id').val();
                const subject = $('#subject').val();

                if (!emailTemplate) {
                    alert("Please select an email template for email campaign.");
                    return;
                }
                if (!subject) {
                    alert("Please enter email subject for email campaign.");
                    return;
                }
            }

            // Validate WhatsApp-specific fields if WhatsApp campaign is selected
            if (whatsappChecked) {
                const wpTemplate = $('#wp_template_id').val();

                if (!wpTemplate) {
                    alert("Please select a WhatsApp template for WhatsApp campaign.");
                    return;
                }
            }

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('start_combined_campaign') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Starting...');
                },
                success: function(response) {
                    alert(response.msg);
                    if (response.status) {
                        $('#add_campaign_form')[0].reset();
                        $('#user_id').val(null).trigger('change');
                        $('input[name="campaign_type[]"]').prop('checked', false);
                        $('.campaign-type-card').removeClass('active');
                        $('#email_section, #whatsapp_section').hide();
                        $(':input').prop('disabled', false);
                        $('#user_id').prop('disabled', true);
                        $('#submit_btn').prop('disabled', true).html('<i class="fa fa-play-circle"></i> Start Campaign');

                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    } else {
                        $('#submit_btn').prop('disabled', false);
                        updateSubmitButtonText(emailChecked, whatsappChecked);
                    }
                },
                error: function(xhr) {
                    $('#submit_btn').prop('disabled', false);
                    updateSubmitButtonText(
                        $('#email_campaign').is(':checked'),
                        $('#whatsapp_campaign').is(':checked')
                    );

                    if (xhr.status === 422) {
                        let response = xhr.responseJSON;
                        if (response.errors) {
                            let error_html = '<ul>';
                            $.each(response.errors, function(key, value) {
                                if (Array.isArray(value)) {
                                    value.forEach(function(errorMsg) {
                                        error_html += '<li>' + errorMsg + '</li>';
                                    });
                                } else {
                                    error_html += '<li>' + value + '</li>';
                                }
                            });
                            error_html += '</ul>';
                            alert('Validation Errors:' + error_html);
                        } else if (response.msg) {
                            alert(response.msg);
                        } else {
                            alert("Validation failed! Please check your inputs.");
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.msg) {
                        alert(xhr.responseJSON.msg);
                    } else {
                        alert("Something went wrong! Please try again.");
                    }
                }
            });
        });

        // Function to update submit button text
        function updateSubmitButtonText(emailChecked, whatsappChecked) {
            let buttonText = '<i class="fa fa-play-circle"></i> ';
            if (emailChecked && whatsappChecked) {
                buttonText += 'Start Email & WhatsApp Campaign';
            } else if (emailChecked) {
                buttonText += 'Start Email Campaign';
            } else if (whatsappChecked) {
                buttonText += 'Start WhatsApp Campaign';
            } else {
                buttonText += 'Start Campaign';
            }
            $('#submit_btn').html(buttonText);
        }
    });
</script>
</body>
</html>
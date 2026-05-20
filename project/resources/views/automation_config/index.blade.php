@include('layouts.masterhead')

<style>
    .nav-tabs .nav-link {
        border: 3px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .disabled-section {
        opacity: 0.6;
        pointer-events: none;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        /*height: 24px;*/
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    /* Checked state */
    .switch input:checked+.slider {
        background-color: #007bff;
    }

    .switch input:checked+.slider:before {
        transform: translateX(22px);
    }

    /* Layout */
    .toggle-container {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        gap: 10px;
    }
</style>

<div class="main-container">
    <div class="min-height-200px">
        <div class="card-box p-3">

            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="automationConfigTabs" role="tablist" style="margin-bottom: 50px;">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#offerPurchaseAutomation">Campaign for Registration
                        Purchase Offer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#commonCheckoutDropUser">Campaign for Checkout
                        Drop User</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#commonRecentExpire">Campaign for Expire Subscriber</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#watermarkExport">Campaign for Watermark Export</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#instantTemplatePurchase">Campaign for Template Purchase</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#wpFeedbackCampaign">Campaign for WP Feedback</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="offerPurchaseAutomation">
                    @include('automation_config.partials.offer_purchase')
                </div>
                <div class="tab-pane fade" id="commonCheckoutDropUser">
                    @include('automation_config.partials.common_checkout_drop')
                </div>
                <div class="tab-pane fade" id="commonRecentExpire">
                    @include('automation_config.partials.common_recent_expire')
                </div>
                <div class="tab-pane fade" id="watermarkExport">
                    @include('automation_config.partials.watermark_export')
                </div>
                <div class="tab-pane fade" id="instantTemplatePurchase">
                    @include('automation_config.partials.instant_template_purchase')
                </div>
                <div class="tab-pane fade" id="wpFeedbackCampaign">
                    @include('automation_config.partials.wp_feedback')
                </div>
            </div>

        </div>
    </div>
</div>

{{-- pass server data into JS safely --}}
<script>
    let existingEmailFrequencies = @json($configs['email_checkout_drop_automation']['frequencies'] ?? []);
    let existingWhatsappFrequencies = @json($configs['whatsapp_checkout_drop_automation'] ?? []);
    let existingCommonFrequencies = @json($configs['checkout_drop_automation'] ?? []);
    let whatsappTemplateMap = @json($whatsappTemplates->pluck('campaign_name', 'id'));
    let templateMap = @json($emailTemplates->pluck('name', 'id'));
    let promoMap = @json($promoCodes->pluck('promo_code', 'id'));
    let offerPurchaseConfig = @json($configs['account_create_automation'] ?? []);
    let existingRecentExpireFrequencies = @json($configs['recent_expire_automation'] ?? []);
    let existingWatermarkFrequencies = @json($configs['download_with_export_automation'] ?? []);
    let templatePurchaseConfig = @json($configs['instant_template_purchase'] ?? []);
    let plans = @json($plans);
    let wpFeedbackConfig = @json($configs['wp_feedback_automation'] ?? []);
</script>

@include('layouts.masterscript')

<script>
    $(document).ready(function() {
        initializeOfferPurchaseForm();

        $('#emailConfigForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let tplId = form.find('#email_template_id').val();
            let subject = form.find('#subject').val().trim();

            if (!tplId) {
                alert("Please select a template before saving.");
                return;
            }
            if (!subject) {
                alert("Please Enter Email Subject");
                return;
            }

            let data = {
                subject: subject,
                template: tplId
            };
            setConfigData(form.find('#config_name').val(), data);
        });

        $('#whatsappConfigForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let tplId = form.find('#whatsapp_template_id').val();

            if (!tplId) {
                alert("Please select a template before saving.");
                return;
            }

            let data = {
                template: tplId
            };
            setConfigData(form.find('#config_name').val(), data);
        });

        // ---------------- FREQUENCIES (Email Checkout Drop) ----------------
        let emailFrequencies = [];

        if (Array.isArray(existingEmailFrequencies) && existingEmailFrequencies.length > 0) {
            emailFrequencies = existingEmailFrequencies.map(f => ({
                days: parseInt(f.days, 10) || 0,
                subject: f.subject || '',
                template: f.template ?? '',
                promo_code: f.promo_code ?? ''
            }));
            renderEmailFrequencyList();
        }

        $("#addFrequencyBtn").on("click", function() {
            $("#editIndexEmail").val("");
            $("#freq_days").val("");
            $("#freq_subject").val("");
            $("#freq_template").val("");
            $("#freq_promo_code").val("");
            $("#saveFrequencyBtn").text("Add");
            $("#emailFrequencyModal").modal("show");
        });

        $("#saveFrequencyBtn").on("click", function() {
            let days = $("#freq_days").val().trim();
            let subject = $("#freq_subject").val().trim();
            let template = $("#freq_template").val();
            let promo_code = $("#freq_promo_code").val();

            if (!days || !subject || !template || !promo_code) {
                alert("Please fill all fields");
                return;
            }

            let obj = {
                days: parseInt(days, 10),
                subject,
                template,
                promo_code
            };
            let index = $("#editIndexEmail").val();

            if (index === "") {
                emailFrequencies.push(obj);
            } else {
                emailFrequencies[index] = obj;
            }

            renderEmailFrequencyList();
            $("#emailFrequencyModal").modal("hide");
        });

        function renderEmailFrequencyList() {
            if (emailFrequencies.length === 0) {
                $("#frequencyList").html("<p>No frequencies added yet.</p>");
                return;
            }

            let html =
                `<table class="table table-bordered">
                <thead><tr><th>Days</th><th>Subject</th><th>Template</th><th>Promo Code</th><th>Actions</th></tr></thead><tbody>`;

            emailFrequencies.forEach((f, i) => {
                html += `<tr>
                    <td>${f.days}</td>
                    <td>${f.subject}</td>
                    <td>${templateMap[f.template] ?? f.template}</td>
                    <td>${promoMap[f.promo_code] ?? f.promo_code}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editEmailFrequency(${i})">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteEmailFrequency(${i})">Delete</button>
                    </td>
                </tr>`;
            });

            html += "</tbody></table>";
            $("#frequencyList").html(html);
        }

        window.editEmailFrequency = function(i) {
            let f = emailFrequencies[i];
            $("#editIndexEmail").val(i);
            $("#freq_days").val(f.days);
            $("#freq_subject").val(f.subject);
            $("#freq_template").val(f.template);
            $("#freq_promo_code").val(f.promo_code);
            $("#saveFrequencyBtn").text("Update");
            $("#emailFrequencyModal").modal("show");
        };

        window.deleteEmailFrequency = function(i) {
            if (confirm("Delete this frequency?")) {
                emailFrequencies.splice(i, 1);
                renderEmailFrequencyList();
            }
        };

        $("#emailCheckoutDropForm").on("submit", function(e) {
            e.preventDefault();
            let form = $(this);
            let subject = form.find('#subject').val().trim();
            let template = form.find('#email_template_id').val();
            let promo_code = form.find('#main_promo_code').val();

            if (!template || !subject || !promo_code) {
                alert("Please fill main fields properly");
                return;
            }
            if (emailFrequencies.length === 0) {
                alert("Please add at least one frequency");
                return;
            }

            let data = {
                subject,
                template,
                promo_code,
                frequencies: emailFrequencies
            };
            setConfigData(form.find('#config_name').val(), data);
        });

        // ---------------- WHATSAPP CHECKOUT DROP ----------------
        let whatsappFrequencies = [];

        // Load existing frequencies
        if (Array.isArray(existingWhatsappFrequencies) && existingWhatsappFrequencies.length > 0) {
            whatsappFrequencies = existingWhatsappFrequencies.map(f => ({
                days: parseInt(f.days, 10) || 0,
                drop_from_offer: {
                    template: f.drop_from_offer?.template ?? ''
                },
                drop_from_subscription: {
                    template: f.drop_from_subscription?.template ?? '',
                    promo_code: f.drop_from_subscription?.promo_code ?? ''
                },
                drop_from_templates: {
                    template: f.drop_from_templates?.template ?? '',
                    promo_code: f.drop_from_templates?.promo_code ?? ''
                }
            }));
            renderWhatsappFrequencyList();
        }

        // Add button click
        $("#addWhatsappFrequencyBtn").on("click", function() {
            $("#editIndexWhatsapp").val("");
            $("#freq_days_wp").val("");
            $("#freq_drop_offer_template").val("");
            $("#freq_drop_subscription_template").val("");
            $("#freq_drop_subscription_promo_code").val("");
            $("#freq_drop_templates_template").val("");
            $("#freq_drop_templates_promo_code").val("");
            $("#saveWhatsappFrequencyBtn").text("Add");
            $("#whatsappFrequencyModal").modal("show");
        });

        // Save frequency
        $("#saveWhatsappFrequencyBtn").on("click", function() {
            let days = $("#freq_days_wp").val().trim();
            if (!days) {
                alert("Please enter frequency days");
                return;
            }

            let obj = {
                days: parseInt(days, 10),
                drop_from_offer: {
                    template: $("#freq_drop_offer_template").val()
                },
                drop_from_subscription: {
                    template: $("#freq_drop_subscription_template").val(),
                    promo_code: $("#freq_drop_subscription_promo_code").val()
                },
                drop_from_templates: {
                    template: $("#freq_drop_templates_template").val(),
                    promo_code: $("#freq_drop_templates_promo_code").val()
                }
            };

            let index = $("#editIndexWhatsapp").val();
            if (index === "") whatsappFrequencies.push(obj);
            else whatsappFrequencies[index] = obj;

            renderWhatsappFrequencyList();
            $("#whatsappFrequencyModal").modal("hide");
        });

        // Render frequency list
        function renderWhatsappFrequencyList() {
            if (whatsappFrequencies.length === 0) {
                $("#whatsappFrequencyList").html("<p>No campaigns added yet.</p>");
                return;
            }

            let html = `<table class="table table-bordered">
            <thead><tr>
                <th>Days</th>
                <th>Drop From Offer</th>
                <th>Drop From Subscription</th>
                <th>Drop From Templates</th>
                <th>Actions</th>
            </tr></thead><tbody>`;

            whatsappFrequencies.forEach((f, i) => {
                html += `<tr>
                <td>${f.days}</td>
                <td>${templateMap[f.drop_from_offer.template] ?? '-'}</td>
                <td>${templateMap[f.drop_from_subscription.template] ?? '-'} (${promoMap[f.drop_from_subscription.promo_code] ?? '-'})</td>
                <td>${templateMap[f.drop_from_templates.template] ?? '-'} (${promoMap[f.drop_from_templates.promo_code] ?? '-'})</td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editWhatsappFrequency(${i})">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteWhatsappFrequency(${i})">Delete</button>
                </td>
            </tr>`;
            });

            html += "</tbody></table>";
            $("#whatsappFrequencyList").html(html);
        }

        // Edit frequency
        window.editWhatsappFrequency = function(i) {
            let f = whatsappFrequencies[i];
            $("#editIndexWhatsapp").val(i);
            $("#freq_days_wp").val(f.days);
            $("#freq_drop_offer_template").val(f.drop_from_offer.template);
            $("#freq_drop_subscription_template").val(f.drop_from_subscription.template);
            $("#freq_drop_subscription_promo_code").val(f.drop_from_subscription.promo_code);
            $("#freq_drop_templates_template").val(f.drop_from_templates.template);
            $("#freq_drop_templates_promo_code").val(f.drop_from_templates.promo_code);
            $("#saveWhatsappFrequencyBtn").text("Update");
            $("#whatsappFrequencyModal").modal("show");
        };

        // Delete frequency
        window.deleteWhatsappFrequency = function(i) {
            if (confirm("Delete this campaign?")) {
                whatsappFrequencies.splice(i, 1);
                renderWhatsappFrequencyList();
            }
        };

        // Submit form
        $("#whatsappCheckoutDropForm").on("submit", function(e) {
            e.preventDefault();
            if (whatsappFrequencies.length === 0) {
                alert("Please add at least one campaign");
                return;
            }

            let data = whatsappFrequencies;

            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    name: "whatsapp_checkout_drop_automation",
                    data: data
                },
                success: function(response) {
                    alert(response.message || "Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    alert("Error while saving config");
                }
            });
        });

        // ---------------- AJAX Save Function ----------------
        function setConfigData(name, data) {
            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    name: name,
                    data: data
                },
                success: function(response) {
                    alert(response.message || "Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Error while saving config");
                }
            });
        }

        // ---------------- Template Preview ----------------
        $(document).on('change', '.email_template_id, .whatsapp_template_id', function() {
            let btn = $(this).closest('.d-flex').find('.preview_template_btn');
            btn.prop('disabled', $(this).val() === '');
        });

        $(document).on('click', '.preview_template_btn', function() {
            let tplId = $(this).closest('.d-flex').find('select').val();
            if (!tplId) tplId = $(this).closest('form').find('select').val();
            if (tplId) {
                let url = "{{ url('email-template/preview') }}/" + tplId;
                window.open(url, '_blank');
            } else {
                alert('Please select a template first.');
            }
        });
    });

    // Initialize the new offer purchase automation form
    function initializeOfferPurchaseForm() {
        // Load existing config if available
        if (offerPurchaseConfig.email) {
            $('#email_automation_enable').prop('checked', offerPurchaseConfig.email.enable);
            if(offerPurchaseConfig.email.enable){
                $('#email_config_section').removeClass('disabled-section');
            } else {
                $('#email_config_section').addClass('disabled-section');
            }
            $('#email_template_id_offer').val(offerPurchaseConfig.email.config.template || '');
            $('#subject_offer').val(offerPurchaseConfig.email.config.subject || '');
        } else {
            $('#email_config_section').removeClass('disabled-section');
        }

        if (offerPurchaseConfig.wp) {
            $('#whatsapp_automation_enable').prop('checked', offerPurchaseConfig.wp.enable);
            if(offerPurchaseConfig.wp.enable){
                $('#whatsapp_config_section').removeClass('disabled-section');
            } else {
                $('#whatsapp_config_section').addClass('disabled-section');
            }
            $('#whatsapp_template_id_offer').val(offerPurchaseConfig.wp.config.template || '');
        } else {
            $('#whatsapp_config_section').addClass('disabled-section');
        }

        $('#email_automation_enable').on('change', function() {
            $('#email_config_section').toggleClass('disabled-section', !$(this).is(':checked'));
        });

        $('#whatsapp_automation_enable').on('change', function() {
            $('#whatsapp_config_section').toggleClass('disabled-section', !$(this).is(':checked'));
        });

        $('#offerPurchaseForm').on('submit', function(e) {
            e.preventDefault();
            let emailEnable = $('#email_automation_enable').is(':checked') ? 1 : 0;
            let whatsappEnable = $('#whatsapp_automation_enable').is(':checked') ? 1 : 0;

            let emailTemplate = $('#email_template_id_offer').val();
            let emailSubject = $('#subject_offer').val().trim();
            let whatsappTemplate = $('#whatsapp_template_id_offer').val();

            if (emailEnable && (!emailTemplate || !emailSubject)) {
                alert("Please fill all required fields for email automation");
                return;
            }

            if (whatsappEnable && !whatsappTemplate) {
                alert("Please select a WhatsApp template for WhatsApp automation");
                return;
            }

            // Prepare data structure
            let data = {
                email: {
                    enable: emailEnable,
                    config: {}
                },
                wp: {
                    enable: whatsappEnable,
                    config: {}
                }
            };

            if (1) {
                data.email.config = {
                    subject: emailSubject,
                    template: emailTemplate
                };
            }

            if (1) {
                data.wp.config = {
                    template: whatsappTemplate
                };
            }
            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                data: {
                    name: "account_create_automation",
                    data: data
                },
                success: function(response) {
                    alert(response.message ||
                        "Offer Purchase Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    alert("Error while saving config");
                }
            });
        });
    }

    // ---------------- COMMON CHECKOUT DROP CAMPAIGNS ----------------
    let commonFrequencies = [];

    // Load existing common frequencies
    if (Array.isArray(existingCommonFrequencies) && existingCommonFrequencies.length > 0) {
        commonFrequencies = existingCommonFrequencies.map(f => ({
            day: parseInt(f.day, 10) || 0,
            email: {
                enable: f.email?.enable ? 1 : 0,
                config: {
                    offer: {
                        subject: f.email?.config?.offer?.subject || '',
                        template: f.email?.config?.offer?.template || '',
                        promo_code: f.email?.config?.offer?.promo_code || ''
                    },
                    subscription: {
                        subject: f.email?.config?.subscription?.subject || '',
                        template: f.email?.config?.subscription?.template || '',
                        promo_code: f.email?.config?.subscription?.promo_code || ''
                    },
                    templates: {
                        subject: f.email?.config?.templates?.subject || '',
                        template: f.email?.config?.templates?.template || '',
                        promo_code: f.email?.config?.templates?.promo_code || ''
                    }
                }
            },
            wp: {
                enable: f.wp?.enable ? 1 : 0,
                config: {
                    offer: {
                        template: f.wp?.config?.offer?.template || ''
                    },
                    subscription: {
                        template: f.wp?.config?.subscription?.template || '',
                        promo_code: f.wp?.config?.subscription?.promo_code || ''
                    },
                    templates: {
                        template: f.wp?.config?.templates?.template || '',
                        promo_code: f.wp?.config?.templates?.promo_code || ''
                    }
                }
            }
        }));
        renderCommonFrequencyList();
    }

    // Toggle sections based on enable switches
    $('#common_email_enable').on('change', function() {
        $('#common_email_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    $('#common_whatsapp_enable').on('change', function() {
        $('#common_whatsapp_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    // Add button click
    $("#addCommonFrequencyBtn").on("click", function() {
        $("#editIndexCommon").val("");
        $("#common_freq_days").val("");

        // Reset email section
        $("#common_email_enable").prop('checked', false);

        // Reset all email fields
        $("#common_offer_email_subject").val("");
        $("#common_offer_email_template").val("");
        $("#common_offer_email_promo_code").val("");

        $("#common_subscription_email_subject").val("");
        $("#common_subscription_email_template").val("");
        $("#common_subscription_email_promo_code").val("");

        $("#common_templates_email_subject").val("");
        $("#common_templates_email_template").val("");
        $("#common_templates_email_promo_code").val("");

        $('#common_email_section').addClass('disabled-section');

        // Reset WhatsApp section
        $("#common_whatsapp_enable").prop('checked', false);
        $("#common_freq_drop_offer_template").val("");
        $("#common_freq_drop_subscription_template").val("");
        $("#common_freq_drop_subscription_promo_code").val("");
        $("#common_freq_drop_templates_template").val("");
        $("#common_freq_drop_templates_promo_code").val("");
        $('#common_whatsapp_section').addClass('disabled-section');

        $("#saveCommonFrequencyBtn").text("Add Campaign");
        $("#commonFrequencyModal").modal("show");
    });

    // Save common frequency
    $("#saveCommonFrequencyBtn").on("click", function() {
        let days = $("#common_freq_days").val().trim();
        let emailEnable = $("#common_email_enable").is(':checked');
        let whatsappEnable = $("#common_whatsapp_enable").is(':checked');

        if (!days) {
            alert("Please enter frequency days");
            return;
        }

        if (!emailEnable && !whatsappEnable) {
            alert("Please enable at least Email or WhatsApp for this campaign");
            return;
        }

        let obj = {
            day: parseInt(days, 10),
            email: {
                enable: emailEnable ? 1 : 0,
                config: {
                    offer: {},
                    subscription: {},
                    templates: {}
                }
            },
            wp: {
                enable: whatsappEnable ? 1 : 0,
                config: {
                    offer: {},
                    subscription: {},
                    templates: {}
                }
            }
        };

        // Email config
        // if (emailEnable) {
            let offerTemplate = $("#common_offer_email_template").val();
            let offerSubject = $("#common_offer_email_subject").val().trim();
            let offerPromo = $("#common_offer_email_promo_code").val();

            let subscriptionTemplate = $("#common_subscription_email_template").val();
            let subscriptionSubject = $("#common_subscription_email_subject").val().trim();
            let subscriptionPromo = $("#common_subscription_email_promo_code").val();

            let templatesTemplate = $("#common_templates_email_template").val();
            let templatesSubject = $("#common_templates_email_subject").val().trim();
            let templatesPromo = $("#common_templates_email_promo_code").val();

            // Validate required fields
            if (!offerTemplate || !offerSubject) {
                alert("Please fill required email fields for Drop From Offer (Subject and Template)");
                return;
            }
            if (!subscriptionTemplate || !subscriptionSubject) {
                alert("Please fill required email fields for Drop From Subscription (Subject and Template)");
                return;
            }
            if (!templatesTemplate || !templatesSubject) {
                alert("Please fill required email fields for Drop From Templates (Subject and Template)");
                return;
            }

            obj.email.config = {
                offer: {
                    subject: offerSubject,
                    template: offerTemplate,
                    promo_code: offerPromo || ''
                },
                subscription: {
                    subject: subscriptionSubject,
                    template: subscriptionTemplate,
                    promo_code: subscriptionPromo || ''
                },
                templates: {
                    subject: templatesSubject,
                    template: templatesTemplate,
                    promo_code: templatesPromo || ''
                }
            };
        // }

        // WhatsApp config
        if (1) {
            let offerTemplate = $("#common_freq_drop_offer_template").val();
            let subscriptionTemplate = $("#common_freq_drop_subscription_template").val();
            let subscriptionPromo = $("#common_freq_drop_subscription_promo_code").val();
            let templatesTemplate = $("#common_freq_drop_templates_template").val();
            let templatesPromo = $("#common_freq_drop_templates_promo_code").val();

            if (!offerTemplate || !subscriptionTemplate || !templatesTemplate) {
                alert("Please select all WhatsApp templates");
                return;
            }

            obj.wp.config = {
                offer: {
                    template: offerTemplate
                },
                subscription: {
                    template: subscriptionTemplate,
                    promo_code: subscriptionPromo || ''
                },
                templates: {
                    template: templatesTemplate,
                    promo_code: templatesPromo || ''
                }
            };
        }

        let index = $("#editIndexCommon").val();
        if (index === "") {
            commonFrequencies.push(obj);
        } else {
            commonFrequencies[index] = obj;
        }

        renderCommonFrequencyList();
        $("#commonFrequencyModal").modal("hide");
    });

    // Render common frequency list
    function renderCommonFrequencyList() {
        if (commonFrequencies.length === 0) {
            $("#commonFrequencyList").html("<p>No campaigns added yet.</p>");
            return;
        }

        let html = `<table class="table table-bordered">
    <thead><tr>
        <th>Days</th>
        <th>Email</th>
        <th>WhatsApp</th>
        <th>Actions</th>
    </tr></thead><tbody>`;

        commonFrequencies.forEach((f, i) => {
            let emailStatus = f.email.enable ?
                `✅ <div class="">
                    <strong>Offer:</strong> ${templateMap[f.email.config.offer.template] || f.email.config.offer.template}<br>
                    <strong>Subscription:</strong> ${templateMap[f.email.config.subscription.template] || f.email.config.subscription.template}
                    ${f.email.config.subscription.promo_code ? `(PromoCode: ${promoMap[f.email.config.subscription.promo_code] || f.email.config.subscription.promo_code})` : ''}<br>
                    <strong>Templates:</strong> ${templateMap[f.email.config.templates.template] || f.email.config.templates.template}
                    ${f.email.config.templates.promo_code ? `(PromoCode: ${promoMap[f.email.config.templates.promo_code] || f.email.config.templates.promo_code})` : ''}
                </div>` :
                '❌ Disabled';

            let whatsappStatus = f.wp.enable ?
                `✅ <div class="">
                    <strong>Offer:</strong> ${whatsappTemplateMap[f.wp.config.offer.template] || f.wp.config.offer.template}<br>
                    <strong>Subscription:</strong> ${whatsappTemplateMap[f.wp.config.subscription.template] || f.wp.config.subscription.template}
                    ${f.wp.config.subscription.promo_code ? `(PromoCode: ${promoMap[f.wp.config.subscription.promo_code] || f.wp.config.subscription.promo_code})` : ''}<br>
                    <strong>Templates:</strong> ${whatsappTemplateMap[f.wp.config.templates.template] || f.wp.config.templates.template}
                    ${f.wp.config.templates.promo_code ? `(PromoCode: ${promoMap[f.wp.config.templates.promo_code] || f.wp.config.templates.promo_code})` : ''}
                </div>` :
                '❌ Disabled';

            html += `<tr>
            <td>${f.day}</td>
            <td>${emailStatus}</td>
            <td>${whatsappStatus}</td>
            <td>
                <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editCommonFrequency(${i})">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCommonFrequency(${i})">Delete</button>
            </td>
        </tr>`;
        });

        html += "</tbody></table>";
        $("#commonFrequencyList").html(html);
    }

    // Edit common frequency
    window.editCommonFrequency = function(i) {
        let f = commonFrequencies[i];
        $("#editIndexCommon").val(i);
        $("#common_freq_days").val(f.day);

        // Email section
        $("#common_email_enable").prop('checked', f.email.enable);

        // Drop From Offer Email
        $("#common_offer_email_subject").val(f.email.config.offer.subject || '');
        $("#common_offer_email_template").val(f.email.config.offer.template || '');
        $("#common_offer_email_promo_code").val(f.email.config.offer.promo_code || '');

        // Drop From Subscription Email
        $("#common_subscription_email_subject").val(f.email.config.subscription.subject || '');
        $("#common_subscription_email_template").val(f.email.config.subscription.template || '');
        $("#common_subscription_email_promo_code").val(f.email.config.subscription.promo_code || '');

        // Drop From Templates Email
        $("#common_templates_email_subject").val(f.email.config.templates.subject || '');
        $("#common_templates_email_template").val(f.email.config.templates.template || '');
        $("#common_templates_email_promo_code").val(f.email.config.templates.promo_code || '');

        $('#common_email_section').toggleClass('disabled-section', !f.email.enable);

        // WhatsApp section
        $("#common_whatsapp_enable").prop('checked', f.wp.enable);

        // Drop From Offer WhatsApp
        $("#common_freq_drop_offer_template").val(f.wp.config.offer.template || '');

        // Drop From Subscription WhatsApp
        $("#common_freq_drop_subscription_template").val(f.wp.config.subscription.template || '');
        $("#common_freq_drop_subscription_promo_code").val(f.wp.config.subscription.promo_code || '');

        // Drop From Templates WhatsApp
        $("#common_freq_drop_templates_template").val(f.wp.config.templates.template || '');
        $("#common_freq_drop_templates_promo_code").val(f.wp.config.templates.promo_code || '');

        $('#common_whatsapp_section').toggleClass('disabled-section', !f.wp.enable);

        $("#saveCommonFrequencyBtn").text("Update Campaign");
        $("#commonFrequencyModal").modal("show");
    };

    // Delete common frequency
    window.deleteCommonFrequency = function(i) {
        if (confirm("Are you sure you want to delete this campaign?")) {
            commonFrequencies.splice(i, 1);
            renderCommonFrequencyList();
        }
    };

    // Submit common form
    $("#commonCheckoutDropForm").on("submit", function(e) {
        e.preventDefault();
        if (commonFrequencies.length === 0) {
            alert("Please add at least one campaign");
            return;
        }
        // Validate that at least one campaign has email or WhatsApp enabled
        let hasEnabledCampaign = commonFrequencies.some(f => f.email.enable || f.wp.enable);
        if (!hasEnabledCampaign) {
            alert("At least one campaign must have Email or WhatsApp enabled");
            return;
        }
        let data = commonFrequencies;
        $.ajax({
            url: "{{ route('automation_config.store') }}",
            type: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                name: "checkout_drop_automation",
                data: data
            },
            success: function(response) {
                alert(response.message || "Common campaign config saved successfully!");
                location.reload();
            },
            error: function(xhr) {
                alert("Error while saving config");
            }
        });
    });

    // Update template preview for common modal
    $(document).on('click', '.preview_template_btn', function() {
        let templateSelect = $(this).data('template-select');
        let tplId = templateSelect ? $("#" + templateSelect).val() : $(this).closest('.d-flex').find('select')
            .val();

        if (!tplId) tplId = $(this).closest('form').find('select').val();
        if (tplId) {
            let url = "{{ url('email-template/preview') }}/" + tplId;
            window.open(url, '_blank');
        } else {
            alert('Please select a template first.');
        }
    });

    // ---------------- RECENT EXPIRE CAMPAIGNS (SIMPLIFIED) ----------------
    let recentExpireFrequencies = [];

    // Load existing recent expire frequencies
    if (Array.isArray(existingRecentExpireFrequencies) && existingRecentExpireFrequencies.length > 0) {
        recentExpireFrequencies = existingRecentExpireFrequencies.map(f => ({
            day: parseInt(f.day, 10) || 0,
            suggested_plan: f.suggested_plan || 0,
            email: {
                enable: f.email?.enable ? 1 : 0,
                config: {
                    subject: f.email?.config?.subject || '',
                    template: f.email?.config?.template || '',
                    promo_code: f.email?.config?.promo_code || ''
                }
            },
            wp: {
                enable: f.wp?.enable ? 1 : 0,
                config: {
                    template: f.wp?.config?.template || '', // Simple template field
                    promo_code: f.wp?.config?.promo_code || '' // Simple promo code field
                }
            }
        }));
        renderRecentExpireFrequencyList();
    }

    // Toggle sections based on enable switches
    $('#recent_expire_email_enable').on('change', function() {
        $('#recent_expire_email_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    $('#recent_expire_whatsapp_enable').on('change', function() {
        $('#recent_expire_whatsapp_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    // Add button click
    $("#addRecentExpireFrequencyBtn").on("click", function() {
        $("#editIndexRecentExpire").val("");
        $("#recent_expire_freq_days").val("");

        // Reset email section
        $("#recent_expire_email_enable").prop('checked', false);
        $("#recent_expire_email_subject").val("");
        $("#recent_expire_email_template").val("");
        $("#recent_expire_email_promo_code").val("");
        $('#recent_expire_email_section').addClass('disabled-section');

        // Reset WhatsApp section
        $("#recent_expire_whatsapp_enable").prop('checked', false);
        $("#recent_expire_whatsapp_template").val("");
        $("#recent_expire_whatsapp_promo_code").val("");
        $('#recent_expire_whatsapp_section').addClass('disabled-section');

        $("#saveRecentExpireFrequencyBtn").text("Add Campaign");
        $("#recentExpireFrequencyModal").modal("show");
    });

    // Save recent expire frequency
    $("#saveRecentExpireFrequencyBtn").on("click", function() {
        let days = $("#recent_expire_freq_days").val().trim();
        let emailEnable = $("#recent_expire_email_enable").is(':checked');
        let whatsappEnable = $("#recent_expire_whatsapp_enable").is(':checked');

        if (!days) {
            alert("Please enter frequency days");
            return;
        }

        if (!emailEnable && !whatsappEnable) {
            alert("Please enable at least Email or WhatsApp for this campaign");
            return;
        }

        let obj = {
            day: parseInt(days, 10),
            suggested_plan : $("#recent_expire_suggested_plan").val().trim(),
            email: {
                enable: emailEnable ? 1 : 0,
                config: {}
            },
            wp: {
                enable: whatsappEnable ? 1 : 0,
                config: {}
            }
        };

        // Email config
        if (1) {
            let emailSubject = $("#recent_expire_email_subject").val().trim();
            let emailTemplate = $("#recent_expire_email_template").val();
            let emailPromoCode = $("#recent_expire_email_promo_code").val();

            if (!emailSubject || !emailTemplate) {
                alert("Please fill required email fields (Subject and Template)");
                return;
            }

            obj.email.config = {
                subject: emailSubject,
                template: emailTemplate,
                promo_code: emailPromoCode || ''
            };
        }

        // WhatsApp config - SIMPLIFIED (just like email)
        if (1) {
            let whatsappTemplate = $("#recent_expire_whatsapp_template").val();
            let whatsappPromoCode = $("#recent_expire_whatsapp_promo_code").val();

            if (!whatsappTemplate) {
                alert("Please select a WhatsApp template");
                return;
            }

            obj.wp.config = {
                template: whatsappTemplate,
                promo_code: whatsappPromoCode || ''
            };
        }

        let index = $("#editIndexRecentExpire").val();
        if (index === "") {
            recentExpireFrequencies.push(obj);
        } else {
            recentExpireFrequencies[index] = obj;
        }

        renderRecentExpireFrequencyList();
        $("#recentExpireFrequencyModal").modal("hide");
    });

    // Render recent expire frequency list
    function renderRecentExpireFrequencyList() {
        if (recentExpireFrequencies.length === 0) {
            $("#recentExpireFrequencyList").html("<p>No campaigns added yet.</p>");
            return;
        }

        let html = `<table class="table table-bordered">
                        <thead><tr>
                            <th>Days</th>
                            <th>Suggested Plan</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Actions</th>
                        </tr></thead><tbody>`;

        recentExpireFrequencies.forEach((f, i) => {

            let planName = '-';
            let plan = plans.find(p => p.id == f.suggested_plan);
            if (plan) {
                planName = plan.package_name;
            }

            let emailStatus = f.email.enable ?
                `✅ ${templateMap[f.email.config.template] || f.email.config.template}` +
                (f.email.config.promo_code ?
                    ` (PromoCode: ${promoMap[f.email.config.promo_code] || f.email.config.promo_code})` : '') :
                '❌ Disabled';

            let whatsappStatus = f.wp.enable ?
                `✅ ${whatsappTemplateMap[f.wp.config.template] || f.wp.config.template}` +
                (f.wp.config.promo_code ?
                    ` (PromoCode: ${promoMap[f.wp.config.promo_code] || f.wp.config.promo_code})` : '') :
                '❌ Disabled';


            html += `<tr>
            <td>${f.day}</td>
            <td>${planName}</td>
            <td>${emailStatus}</td>
            <td>${whatsappStatus}</td>
            <td>
                <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editRecentExpireFrequency(${i})">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecentExpireFrequency(${i})">Delete</button>
            </td>
        </tr>`;
        });

        html += "</tbody></table>";
        $("#recentExpireFrequencyList").html(html);
    }

    // Edit recent expire frequency
    window.editRecentExpireFrequency = function(i) {
        let f = recentExpireFrequencies[i];
        $("#editIndexRecentExpire").val(i);
        $("#recent_expire_freq_days").val(f.day);
        $("#recent_expire_suggested_plan").val(f.suggested_plan || "");

        // Email section
        $("#recent_expire_email_enable").prop('checked', f.email.enable);
        $("#recent_expire_email_subject").val(f.email.config.subject || '');
        $("#recent_expire_email_template").val(f.email.config.template || '');
        $("#recent_expire_email_promo_code").val(f.email.config.promo_code || '');
        $('#recent_expire_email_section').toggleClass('disabled-section', !f.email.enable);

        // WhatsApp section - SIMPLIFIED
        $("#recent_expire_whatsapp_enable").prop('checked', f.wp.enable);
        $("#recent_expire_whatsapp_template").val(f.wp.config.template || '');
        $("#recent_expire_whatsapp_promo_code").val(f.wp.config.promo_code || '');
        $('#recent_expire_whatsapp_section').toggleClass('disabled-section', !f.wp.enable);

        $("#saveRecentExpireFrequencyBtn").text("Update Campaign");
        $("#recentExpireFrequencyModal").modal("show");
    };

    // Delete recent expire frequency
    window.deleteRecentExpireFrequency = function(i) {
        if (confirm("Delete this campaign?")) {
            recentExpireFrequencies.splice(i, 1);
            renderRecentExpireFrequencyList();
        }
    };

    // Submit recent expire form
    $("#commonRecentExpireForm").on("submit", function(e) {
        e.preventDefault();
        if (recentExpireFrequencies.length === 0) {
            alert("Please add at least one campaign");
            return;
        }

        let hasEnabledCampaign = recentExpireFrequencies.some(f => f.email.enable || f.wp.enable);
        if (!hasEnabledCampaign) {
            alert("At least one campaign must have Email or WhatsApp enabled");
            return;
        }

        let data = recentExpireFrequencies;

        $.ajax({
            url: "{{ route('automation_config.store') }}",
            type: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                name: "recent_expire_automation",
                data: data
            },
            success: function(response) {
                alert(response.message || "Recent expire campaign config saved successfully!");
                location.reload();
            },
            error: function(xhr) {
                alert("Error while saving config");
            }
        });
    });



    // ---------------- WATERMARK EXPORT CAMPAIGNS ----------------
    let watermarkFrequencies = [];
    if (Array.isArray(existingWatermarkFrequencies) && existingWatermarkFrequencies.length > 0) {
        watermarkFrequencies = existingWatermarkFrequencies.map(f => ({
            day: parseInt(f.day, 10) || 0,
            email: {
                enable: f.email?.enable ? 1 : 0,
                config: {
                    subject: f.email?.config?.subject || '',
                    template: f.email?.config?.template || '',
                    promo_code: f.email?.config?.promo_code || ''
                }
            },
            wp: {
                enable: f.wp?.enable ? 1 : 0,
                config: {
                    template: f.wp?.config?.template || '',
                    promo_code: f.wp?.config?.promo_code || ''
                }
            }
        }));
        renderWatermarkFrequencyList();
    }

    // Toggle sections based on enable switches
    $('#watermark_email_enable').on('change', function() {
        $('#watermark_email_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    $('#watermark_whatsapp_enable').on('change', function() {
        $('#watermark_whatsapp_section').toggleClass('disabled-section', !$(this).is(':checked'));
    });

    // Add button click
    $("#addWatermarkFrequencyBtn").on("click", function() {
        $("#editIndexWatermark").val("");
        $("#watermark_freq_days").val("");

        // Reset email section
        $("#watermark_email_enable").prop('checked', false);
        $("#watermark_email_subject").val("");
        $("#watermark_email_template").val("");
        $("#watermark_email_promo_code").val("");
        $('#watermark_email_section').addClass('disabled-section');

        // Reset WhatsApp section
        $("#watermark_whatsapp_enable").prop('checked', false);
        $("#watermark_whatsapp_template").val("");
        $("#watermark_whatsapp_promo_code").val("");
        $('#watermark_whatsapp_section').addClass('disabled-section');

        $("#saveWatermarkFrequencyBtn").text("Add Campaign");
        $("#watermarkFrequencyModal").modal("show");
    });

    // Save watermark frequency
    $("#saveWatermarkFrequencyBtn").on("click", function() {
        let days = $("#watermark_freq_days").val().trim();
        let emailEnable = $("#watermark_email_enable").is(':checked');
        let whatsappEnable = $("#watermark_whatsapp_enable").is(':checked');

        if (!days) {
            alert("Please enter frequency days");
            return;
        }

        if (!emailEnable && !whatsappEnable) {
            alert("Please enable at least Email or WhatsApp for this campaign");
            return;
        }

        let obj = {
            day: parseInt(days, 10),
            email: {
                enable: emailEnable ? 1 : 0,
                config: {}
            },
            wp: {
                enable: whatsappEnable ? 1 : 0,
                config: {}
            }
        };

        // Email config
        if (emailEnable) {
            let emailSubject = $("#watermark_email_subject").val().trim();
            let emailTemplate = $("#watermark_email_template").val();
            let emailPromoCode = $("#watermark_email_promo_code").val();

            if (!emailSubject || !emailTemplate) {
                alert("Please fill required email fields (Subject and Template)");
                return;
            }

            obj.email.config = {
                subject: emailSubject,
                template: emailTemplate,
                promo_code: emailPromoCode || ''
            };
        }

        // WhatsApp config
        if (whatsappEnable) {
            let whatsappTemplate = $("#watermark_whatsapp_template").val();
            let whatsappPromoCode = $("#watermark_whatsapp_promo_code").val();

            if (!whatsappTemplate) {
                alert("Please select a WhatsApp template");
                return;
            }

            obj.wp.config = {
                template: whatsappTemplate,
                promo_code: whatsappPromoCode || ''
            };
        }

        let index = $("#editIndexWatermark").val();
        if (index === "") {
            watermarkFrequencies.push(obj);
        } else {
            watermarkFrequencies[index] = obj;
        }

        renderWatermarkFrequencyList();
        $("#watermarkFrequencyModal").modal("hide");
    });

    // Render watermark frequency list
    function renderWatermarkFrequencyList() {
        if (watermarkFrequencies.length === 0) {
            $("#watermarkFrequencyList").html("<p>No campaigns added yet.</p>");
            return;
        }

        let html = `<table class="table table-bordered">
    <thead><tr>
        <th>Days</th>
        <th>Email</th>
        <th>WhatsApp</th>
        <th>Actions</th>
    </tr></thead><tbody>`;

        watermarkFrequencies.forEach((f, i) => {
            let emailStatus = f.email.enable ?
                `✅ ${templateMap[f.email.config.template] || f.email.config.template}` +
                (f.email.config.promo_code ?
                    ` (PromoCode: ${promoMap[f.email.config.promo_code] || f.email.config.promo_code})` : '') :
                '❌ Disabled';

            let whatsappStatus = f.wp.enable ?
                `✅ ${whatsappTemplateMap[f.wp.config.template] || f.wp.config.template}` +
                (f.wp.config.promo_code ?
                    ` (PromoCode: ${promoMap[f.wp.config.promo_code] || f.wp.config.promo_code})` : '') :
                '❌ Disabled';

            html += `<tr>
            <td>${f.day}</td>
            <td>${emailStatus}</td>
            <td>${whatsappStatus}</td>
            <td>
                <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editWatermarkFrequency(${i})">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteWatermarkFrequency(${i})">Delete</button>
            </td>
        </tr>`;
        });

        html += "</tbody></table>";
        $("#watermarkFrequencyList").html(html);
    }

    // Edit watermark frequency
    window.editWatermarkFrequency = function(i) {
        let f = watermarkFrequencies[i];
        $("#editIndexWatermark").val(i);
        $("#watermark_freq_days").val(f.day);

        // Email section
        $("#watermark_email_enable").prop('checked', f.email.enable);
        $("#watermark_email_subject").val(f.email.config.subject || '');
        $("#watermark_email_template").val(f.email.config.template || '');
        $("#watermark_email_promo_code").val(f.email.config.promo_code || '');
        $('#watermark_email_section').toggleClass('disabled-section', !f.email.enable);

        // WhatsApp section
        $("#watermark_whatsapp_enable").prop('checked', f.wp.enable);
        $("#watermark_whatsapp_template").val(f.wp.config.template || '');
        $("#watermark_whatsapp_promo_code").val(f.wp.config.promo_code || '');
        $('#watermark_whatsapp_section').toggleClass('disabled-section', !f.wp.enable);

        $("#saveWatermarkFrequencyBtn").text("Update Campaign");
        $("#watermarkFrequencyModal").modal("show");
    };

    // Delete watermark frequency
    window.deleteWatermarkFrequency = function(i) {
        if (confirm("Delete this campaign?")) {
            watermarkFrequencies.splice(i, 1);
            renderWatermarkFrequencyList();
        }
    };

    // Submit watermark export form
    $("#watermarkExportForm").on("submit", function(e) {
        e.preventDefault();
        if (watermarkFrequencies.length === 0) {
            alert("Please add at least one campaign");
            return;
        }

        let hasEnabledCampaign = watermarkFrequencies.some(f => f.email.enable || f.wp.enable);
        if (!hasEnabledCampaign) {
            alert("At least one campaign must have Email or WhatsApp enabled");
            return;
        }

        let data = watermarkFrequencies;

        $.ajax({
            url: "{{ route('automation_config.store') }}",
            type: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                name: "download_with_export_automation",
                data: data
            },
            success: function(response) {
                alert(response.message || "Watermark Export campaign config saved successfully!");
                location.reload();
            },
            error: function(xhr) {
                alert("Error while saving config");
            }
        });
    });

    /*Instant Template Purchase */
    function initializeTemplatePurchaseForm() {
        // Common promo code
        if (templatePurchaseConfig.promo) {
            $('#template_common_promo_code').val(templatePurchaseConfig.promo);
        }

        // Email section initialization
        if (templatePurchaseConfig.email) {
            $('#template_email_automation_enable').prop('checked', templatePurchaseConfig.email.enable || false);
            if(templatePurchaseConfig.email.enable){
                $('#template_email_config_section').removeClass('disabled-section');
            } else {
                $('#template_email_config_section').addClass('disabled-section');
            }
            $('#template_email_template_id').val(templatePurchaseConfig.email.config?.template || '');
            $('#template_email_subject').val(templatePurchaseConfig.email.config?.subject || '');
        } else {
            $('#template_email_config_section').addClass('disabled-section');
        }

        // WhatsApp section initialization
        if (templatePurchaseConfig.wp) {
            $('#template_whatsapp_automation_enable').prop('checked', templatePurchaseConfig.wp.enable || false);
            if(templatePurchaseConfig.wp.enable){
                $('#template_whatsapp_config_section').removeClass('disabled-section');
            } else {
                $('#template_whatsapp_config_section').addClass('disabled-section');
            }
            $('#template_whatsapp_template_id').val(templatePurchaseConfig.wp.config?.template || '');
        } else {
            $('#template_whatsapp_config_section').addClass('disabled-section');
        }

        // Toggle event handlers
        $('#template_email_automation_enable').on('change', function() {
            $('#template_email_config_section').toggleClass('disabled-section', !$(this).is(':checked'));
        });

        $('#template_whatsapp_automation_enable').on('change', function() {
            $('#template_whatsapp_config_section').toggleClass('disabled-section', !$(this).is(':checked'));
        });

        // Form submission handler
        $('#instantTemplatePurchaseForm').on('submit', function(e) {
            e.preventDefault();

            let emailEnable = $('#template_email_automation_enable').is(':checked') ? 1 : 0;
            let whatsappEnable = $('#template_whatsapp_automation_enable').is(':checked') ? 1 : 0;

            let commonPromoCode = $('#template_common_promo_code').val();
            let emailTemplate = $('#template_email_template_id').val();
            let emailSubject = $('#template_email_subject').val().trim();
            let whatsappTemplate = $('#template_whatsapp_template_id').val();

            // Validation - if main automation is enabled, at least one channel should be enabled
            if (!emailEnable && !whatsappEnable) {
                alert("Please enable at least Email or WhatsApp automation");
                return;
            }

            // If email is enabled, validate email fields
            if (emailEnable && (!emailTemplate || !emailSubject)) {
                alert("Please fill all required fields for email automation");
                return;
            }

            // If WhatsApp is enabled, validate WhatsApp fields
            if (whatsappEnable && !whatsappTemplate) {
                alert("Please select a WhatsApp template for WhatsApp automation");
                return;
            }

            // Prepare data structure as per your requirement
            let data = {
                promo: commonPromoCode || '',
                email: {
                    enable: emailEnable,
                    config: {}
                },
                wp: {
                    enable: whatsappEnable,
                    config: {}
                }
            };

            // Email config (only if enabled)
                data.email.config = {
                    subject: emailSubject,
                    template: emailTemplate
                };

            // WhatsApp config (only if enabled)
                data.wp.config = {
                    template: whatsappTemplate
                };

            // Send AJAX request
            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                data: {
                    name: "instant_template_purchase",
                    data: data
                },
                success: function(response) {
                    alert(response.message || "Template Purchase Automation config saved successfully!");
                    location.reload();
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert("Error while saving config");
                }
            });
        });

        // Template preview functionality
        $('#template_email_template_id, #template_whatsapp_template_id').on('change', function() {
            let btn = $(this).closest('.d-flex').find('.preview_template_btn');
            btn.prop('disabled', $(this).val() === '');
        });

        // Preview button click handler
        $(document).on('click', '.preview_template_btn', function() {
            let tplId = $(this).closest('.d-flex').find('select').val();
            if (tplId) {
                let url = "{{ url('email-template/preview') }}/" + tplId;
                window.open(url, '_blank');
            } else {
                alert('Please select a template first.');
            }
        });
    }

    // Call the initialization function when the tab is shown
    $('a[data-toggle="tab"][href="#instantTemplatePurchase"]').on('shown.bs.tab', function(e) {
        initializeTemplatePurchaseForm();
    });

    // Also initialize when document is ready if this tab is active
    $(document).ready(function() {
        if ($('#instantTemplatePurchase').hasClass('active')) {
            initializeTemplatePurchaseForm();
        }
    });

    // ---------------- WP FEEDBACK CAMPAIGN ----------------
    function initializeWpFeedbackForm() {
        if (wpFeedbackConfig.days_after_purchase) {
            $('#wp_feedback_days_after_purchase').val(wpFeedbackConfig.days_after_purchase);
        }

        if (wpFeedbackConfig.wp) {
            $('#wp_feedback_whatsapp_enable').prop('checked', !!wpFeedbackConfig.wp.enable);
            if (wpFeedbackConfig.wp.enable) {
                $('#wp_feedback_whatsapp_section').removeClass('disabled-section');
            } else {
                $('#wp_feedback_whatsapp_section').addClass('disabled-section');
            }
            $('#wp_feedback_whatsapp_template').val(wpFeedbackConfig.wp.config?.template || '');
        } else {
            $('#wp_feedback_whatsapp_section').addClass('disabled-section');
        }

        $('#wp_feedback_whatsapp_enable').on('change', function() {
            $('#wp_feedback_whatsapp_section').toggleClass('disabled-section', !$(this).is(':checked'));
        });

        $('#wpFeedbackForm').on('submit', function(e) {
            e.preventDefault();

            let days = parseInt($('#wp_feedback_days_after_purchase').val(), 10);
            let wpEnable = $('#wp_feedback_whatsapp_enable').is(':checked') ? 1 : 0;
            let wpTemplate = $('#wp_feedback_whatsapp_template').val();

            if (!days || days < 1) {
                alert("Please enter a valid number of days");
                return;
            }

            if (wpEnable && !wpTemplate) {
                alert("Please select a WhatsApp template");
                return;
            }

            let data = {
                days_after_purchase: days,
                wp: {
                    enable: wpEnable,
                    config: {
                        template: wpTemplate
                    }
                }
            };

            $.ajax({
                url: "{{ route('automation_config.store') }}",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
                data: { name: "wp_feedback_automation", data: data },
                success: function(response) {
                    alert(response.message || "WP Feedback config saved successfully!");
                    location.reload();
                },
                error: function() {
                    alert("Error while saving config");
                }
            });
        });
    }

    $('a[data-toggle="tab"][href="#wpFeedbackCampaign"]').on('shown.bs.tab', function() {
        initializeWpFeedbackForm();
    });

    $(document).ready(function() {
        if ($('#wpFeedbackCampaign').hasClass('active')) {
            initializeWpFeedbackForm();
        }
    });

</script>
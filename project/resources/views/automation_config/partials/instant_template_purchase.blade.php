<form id="instantTemplatePurchaseForm">
    @csrf
    <input type="hidden" name="name" value="instant_template_purchase_automation">

    <!-- COMMON PROMO CODE SECTION -->
    <div class="form-group">

        <div class="form-group mt-3">
            <label>Select PromoCode</label>
            <select id="template_common_promo_code" class="form-control">
                <option value="">-- Select Promo Code --</option>
                @foreach ($promoCodes as $promoCode)
                <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr>

    <!-- EMAIL SECTION -->
    <div class="form-group">
        <div class="toggle-container">
            <span class="toggle-label">Enable Email Template Purchase Automation</span>
            <label class="switch">
                <input type="checkbox" id="template_email_automation_enable">
                <span class="slider"></span>
            </label>
        </div>

        <div id="template_email_config_section" class="config-section disabled-section">
            <h6>Email Template</h6>
            <div class="d-flex">
                <select id="template_email_template_id" class="form-control email_template_id">
                    <option value="">-- Select Email Template --</option>
                    @foreach ($emailTemplates as $tpl)
                    <option value="{{ $tpl->id }}">
                        {{ $tpl->id }} - {{ $tpl->name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-info ml-2 preview_template_btn" disabled>Preview</button>
            </div>

            <div class="form-group mt-3">
                <label>Email Subject</label>
                <input type="text" class="form-control" id="template_email_subject">
            </div>
        </div>
    </div>

    <hr>

    <!-- WHATSAPP SECTION -->
    <div class="form-group">
        <div class="toggle-container">
            <span class="toggle-label">Enable WhatsApp Template Purchase Automation</span>
            <label class="switch">
                <input type="checkbox" id="template_whatsapp_automation_enable">
                <span class="slider"></span>
            </label>
        </div>

        <div id="template_whatsapp_config_section" class="config-section disabled-section">
            <h6>WhatsApp Template</h6>
            <div class="d-flex">
                <select id="template_whatsapp_template_id" class="form-control whatsapp_template_id">
                    <option value="">-- Select WhatsApp Template --</option>
                    @foreach ($whatsappTemplates as $tpl)
                    <option value="{{ $tpl->id }}">
                        {{ $tpl->id }} - {{ $tpl->campaign_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Template Purchase Automation Config</button>
</form>

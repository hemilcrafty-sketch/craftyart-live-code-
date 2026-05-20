<form id="offerPurchaseForm">
    @csrf
    <input type="hidden" name="name" value="account_create_automation">

    <!-- EMAIL -->
    <div class="form-group">
        <div class="toggle-container">
            <span class="toggle-label">Enable Email Offer Purchase Automation</span>
            <label class="switch">
                <input type="checkbox" id="email_automation_enable">
                <span class="slider"></span>
            </label>
        </div>

        <div id="email_config_section" class="config-section disabled-section">
            <h6>Email Template</h6>
            <div class="d-flex">
                <select id="email_template_id_offer" class="form-control email_template_id">
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
                <input type="text" class="form-control" id="subject_offer">
            </div>
        </div>
    </div>

    <hr>

    <!-- WHATSAPP -->
    <div class="form-group">
        <div class="toggle-container">
            <span class="toggle-label">Enable WhatsApp Offer Purchase Automation</span>
            <label class="switch">
                <input type="checkbox" id="whatsapp_automation_enable">
                <span class="slider"></span>
            </label>
        </div>

        <div id="whatsapp_config_section" class="config-section disabled-section">
            <h6>WhatsApp Template</h6>
            <div class="d-flex">
                <select id="whatsapp_template_id_offer" class="form-control whatsapp_template_id">
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

    <button type="submit" class="btn btn-primary">Save Offer Purchase Automation Config</button>
</form>

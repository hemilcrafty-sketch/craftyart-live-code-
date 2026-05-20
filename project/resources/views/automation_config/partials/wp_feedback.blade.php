<form id="wpFeedbackForm">
    @csrf

    <!-- Days After Purchase -->
    <div class="form-group">
        <label>Send After How Many Days of Purchase</label>
        <input type="number" class="form-control" id="wp_feedback_days_after_purchase" min="1" value="2" style="max-width: 200px;">
        <small class="text-muted">Feedback link will be sent this many days after purchase.</small>
    </div>

    <hr>

    <!-- WHATSAPP -->
    <div class="form-group">
        <div class="toggle-container">
            <span class="toggle-label">Enable WhatsApp Feedback Campaign</span>
            <label class="switch">
                <input type="checkbox" id="wp_feedback_whatsapp_enable">
                <span class="slider"></span>
            </label>
        </div>

        <div id="wp_feedback_whatsapp_section" class="config-section disabled-section">
            <h6>WhatsApp Template</h6>
            <div class="d-flex">
                <select id="wp_feedback_whatsapp_template" class="form-control">
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

    <button type="submit" class="btn btn-primary">Save WP Feedback Config</button>
</form>

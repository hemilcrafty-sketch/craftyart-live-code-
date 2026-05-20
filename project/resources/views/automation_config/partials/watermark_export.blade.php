<form id="watermarkExportForm">
    @csrf
    <input type="hidden" name="name" id="config_name" value="download_with_export_automation">

    <!-- Campaign (Frequencies) -->
    <div class="form-group mt-2">
        <h6>Campaign</h6>
        <button type="button" class="btn btn-success mb-2" id="addWatermarkFrequencyBtn">
            + Add Campaign
        </button>
        <div id="watermarkFrequencyList"></div>
    </div>

    <button type="submit" class="btn btn-primary">Save Watermark Export Campaign Config</button>
</form>

<!-- Watermark Export Frequency Modal -->
<div class="modal fade" id="watermarkFrequencyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Campaign - Watermark Export</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndexWatermark">

                <div class="form-group">
                    <label>Frequency in Days</label>
                    <input type="number" class="form-control" id="watermark_freq_days" min="0" placeholder="e.g., 5">
                </div>

                <!-- Email Section -->
                <div class="p-3 mb-3" style="border: 2px solid #007bff; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable Email</span>
                        <label class="switch">
                            <input type="checkbox" id="watermark_email_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="watermark_email_section">
                        <div class="form-group">
                            <label>Email Subject</label>
                            <input type="text" class="form-control" id="watermark_email_subject" placeholder="Enter email subject">
                        </div>
                        <div class="form-group">
                            <label>Email Template</label>
                            <div class="d-flex">
                                <select id="watermark_email_template" class="form-control">
                                    <option value="">-- Select Email Template --</option>
                                    @foreach ($emailTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info ml-2 preview_template_btn" data-template-select="watermark_email_template" disabled>Preview</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Promo Code (Optional)</label>
                            <select id="watermark_email_promo_code" class="form-control">
                                <option value="">-- Select Promo Code (Optional) --</option>
                                @foreach ($promoCodes as $promoCode)
                                <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Section -->
                <div class="p-3" style="border: 2px solid #28a745; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable WhatsApp</span>
                        <label class="switch">
                            <input type="checkbox" id="watermark_whatsapp_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="watermark_whatsapp_section">
                        <div class="form-group">
                            <label>WhatsApp Template</label>
                            <div class="d-flex">
                                <select id="watermark_whatsapp_template" class="form-control">
                                    <option value="">-- Select WhatsApp Template --</option>
                                    @foreach ($whatsappTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->campaign_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info ml-2 preview_template_btn" data-template-select="watermark_whatsapp_template" disabled>Preview</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Promo Code (Optional)</label>
                            <select id="watermark_whatsapp_promo_code" class="form-control">
                                <option value="">-- Select Promo Code (Optional) --</option>
                                @foreach ($promoCodes as $promoCode)
                                <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="saveWatermarkFrequencyBtn" class="btn btn-primary">Save Campaign</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
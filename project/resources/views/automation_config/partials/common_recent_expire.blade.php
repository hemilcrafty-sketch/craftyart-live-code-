<form id="commonRecentExpireForm">
    @csrf
    <input type="hidden" name="name" id="config_name" value="recent_expire_automation">

    <!-- Campaign (Frequencies) -->
    <div class="form-group mt-2">
        <h6>Campaign</h6>
        <button type="button" class="btn btn-success mb-2" id="addRecentExpireFrequencyBtn">
            + Add Campaign
        </button>
        <div id="recentExpireFrequencyList"></div>
    </div>

    <button type="submit" class="btn btn-primary">Save Recent Expire Campaign Config</button>
</form>

<!-- Recent Expire Frequency Modal -->
<div class="modal fade" id="recentExpireFrequencyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Campaign - Recent Expire</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndexRecentExpire">

                <div class="form-group">
                    <label>Frequency in Days</label>
                    <input type="number" class="form-control" id="recent_expire_freq_days" min="0" placeholder="e.g., 5">
                </div>

                <div class="form-group">
                    <label>Suggested plans</label>
                    <select id="recent_expire_suggested_plan" class="form-control">
                        <option value="">-- Select Suggested Plan --</option>
                        @foreach ($plans as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->package_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Email Section -->
                <div class="p-3 mb-3" style="border: 2px solid #007bff; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable Email</span>
                        <label class="switch">
                            <input type="checkbox" id="recent_expire_email_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="recent_expire_email_section">
                        <div class="form-group">
                            <label>Email Subject</label>
                            <input type="text" class="form-control" id="recent_expire_email_subject" placeholder="Enter email subject">
                        </div>
                        <div class="form-group">
                            <label>Email Template</label>
                            <div class="d-flex">
                                <select id="recent_expire_email_template" class="form-control">
                                    <option value="">-- Select Email Template --</option>
                                    @foreach ($emailTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info ml-2 preview_template_btn" data-template-select="recent_expire_email_template" disabled>Preview</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Promo Code (Optional)</label>
                            <select id="recent_expire_email_promo_code" class="form-control">
                                <option value="">-- Select Promo Code (Optional) --</option>
                                @foreach ($promoCodes as $promoCode)
                                <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Section - SIMPLIFIED -->
                <div class="p-3" style="border: 2px solid #28a745; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable WhatsApp</span>
                        <label class="switch">
                            <input type="checkbox" id="recent_expire_whatsapp_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="recent_expire_whatsapp_section">
                        <div class="form-group">
                            <label>WhatsApp Template</label>
                            <div class="d-flex">
                                <select id="recent_expire_whatsapp_template" class="form-control">
                                    <option value="">-- Select WhatsApp Template --</option>
                                    @foreach ($whatsappTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->campaign_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info ml-2 preview_template_btn" data-template-select="recent_expire_whatsapp_template" disabled>Preview</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Promo Code (Optional)</label>
                            <select id="recent_expire_whatsapp_promo_code" class="form-control">
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
                <button type="button" id="saveRecentExpireFrequencyBtn" class="btn btn-primary">Save Campaign</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
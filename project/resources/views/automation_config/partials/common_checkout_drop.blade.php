<form id="commonCheckoutDropForm">
    @csrf
    <input type="hidden" name="name" id="config_name" value="checkout_drop_automation">

    <!-- Campaign (Frequencies) -->
    <div class="form-group mt-2">
        <h6>Campaign</h6>
        <button type="button" class="btn btn-success mb-2" id="addCommonFrequencyBtn">
            + Add Campaign
        </button>
        <div id="commonFrequencyList"></div>
    </div>

    <button type="submit" class="btn btn-primary">Save Common Campaign Config</button>
</form>

<!-- Common Frequency Modal -->
<div class="modal fade" id="commonFrequencyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Campaign</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndexCommon">

                <div class="form-group">
                    <label>Frequency in Days</label>
                    <input type="number" class="form-control" id="common_freq_days" min="0"
                           placeholder="e.g., 5">
                </div>

                <!-- Email Section -->
                <div class="p-3 mb-3" style="border: 2px solid #007bff; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable Email</span>
                        <label class="switch">
                            <input type="checkbox" id="common_email_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="common_email_section">
                        <div class="p-3 mb-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Offer</h6>
                            <div class="form-group">
                                <label>Email Subject</label>
                                <input type="text" class="form-control" id="common_offer_email_subject"
                                       placeholder="Enter email subject">
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Email Template</label>
                                        <div class="d-flex">
                                            <select id="common_offer_email_template" class="form-control">
                                                <option value="">-- Select Email Template --</option>
                                                @foreach ($emailTemplates as $tpl)
                                                <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                                    {{ $tpl->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-info ml-2 preview_template_btn"
                                                    data-template-select="common_offer_email_template" readonly="true">Preview</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 mb-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Subscription</h6>
                            <div class="form-group">
                                <label>Email Subject</label>
                                <input type="text" class="form-control" id="common_subscription_email_subject"
                                       placeholder="Enter email subject">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Email Template</label>
                                        <div class="d-flex">
                                            <select id="common_subscription_email_template" class="form-control">
                                                <option value="">-- Select Email Template --</option>
                                                @foreach ($emailTemplates as $tpl)
                                                <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                                    {{ $tpl->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-info ml-2 preview_template_btn"
                                                    data-template-select="common_subscription_email_template" readonly="true">Preview</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Promo Code (Optional)</label>
                                        <select id="common_subscription_email_promo_code" class="form-control">
                                            <option value="">-- Select Promo Code (Optional) --</option>
                                            @foreach ($promoCodes as $promoCode)
                                            <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 mb-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Templates</h6>
                            <div class="form-group">
                                <label>Email Subject</label>
                                <input type="text" class="form-control" id="common_templates_email_subject"
                                       placeholder="Enter email subject">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Email Template</label>
                                        <div class="d-flex">
                                            <select id="common_templates_email_template" class="form-control">
                                                <option value="">-- Select Email Template --</option>
                                                @foreach ($emailTemplates as $tpl)
                                                <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                                    {{ $tpl->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-info ml-2 preview_template_btn"
                                                    data-template-select="common_templates_email_template" readonly="true">Preview</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Promo Code (Optional)</label>
                                        <select id="common_templates_email_promo_code" class="form-control">
                                            <option value="">-- Select Promo Code (Optional) --</option>
                                            @foreach ($promoCodes as $promoCode)
                                            <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Section -->
                <div class="p-3" style="border: 2px solid #28a745; border-radius: 10px;">
                    <div class="toggle-container mb-3">
                        <span class="toggle-label">Enable WhatsApp</span>
                        <label class="switch">
                            <input type="checkbox" id="common_whatsapp_enable">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="common_whatsapp_section">
                        <!-- Drop From Subscription -->
                        <div class="p-3 mb-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Offer</h6>
                            <div class="form-group">
                                <label>WhatsApp Template</label>
                                <select id="common_freq_drop_offer_template" class="form-control">
                                    <option value="">-- Select WhatsApp Template --</option>
                                    @foreach ($whatsappTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                        {{ $tpl->campaign_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Drop From Subscription -->
                        <div class="p-3 mb-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Subscription</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label>WhatsApp Template</label>
                                    <select id="common_freq_drop_subscription_template" class="form-control">
                                        <option value="">-- Select WhatsApp Template --</option>
                                        @foreach ($whatsappTemplates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                            {{ $tpl->campaign_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label>Promo Code (Optional)</label>
                                    <select id="common_freq_drop_subscription_promo_code" class="form-control">
                                        <option value="">-- Select Promo Code (Optional) --</option>
                                        @foreach ($promoCodes as $promoCode)
                                        <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Drop From Templates -->
                        <div class="p-3" style="border: 2px dotted black; border-radius: 20px;">
                            <h6>Drop From Templates</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label>WhatsApp Template</label>
                                    <select id="common_freq_drop_templates_template" class="form-control">
                                        <option value="">-- Select WhatsApp Template --</option>
                                        @foreach ($whatsappTemplates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->id }} -
                                            {{ $tpl->campaign_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label>Promo Code (Optional)</label>
                                    <select id="common_freq_drop_templates_promo_code" class="form-control">
                                        <option value="">-- Select Promo Code (Optional) --</option>
                                        @foreach ($promoCodes as $promoCode)
                                        <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="saveCommonFrequencyBtn" class="btn btn-primary">Save Campaign</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
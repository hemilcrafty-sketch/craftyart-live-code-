<form id="whatsappCheckoutDropForm">
    @csrf

    <!-- Campaign (Frequencies) -->
    <div class="form-group mt-2">
        <h6>Campaign</h6>
        <button type="button" class="btn btn-success mb-2" id="addWhatsappFrequencyBtn">
            + Add Campaign
        </button>
        <div id="whatsappFrequencyList"></div>
    </div>

    <button type="submit" class="btn btn-primary">Save Whatsapp Config</button>
</form>

<!-- Frequency Modal -->
<div class="modal fade" id="whatsappFrequencyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Campaign</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndexWhatsapp">

                <div class="form-group">
                    <label>Frequency in Days</label>
                    <input type="number" class="form-control" id="freq_days_wp" min="0" placeholder="e.g., 5">
                </div>

                <!-- Drop From Offer -->
                <div class="p-3" style="border: 2px dotted black; border-radius: 20px;">
                    <h6>Drop From Offer</h6>
                    <div class="form-group">
                        <label>Whatsapp Template</label>
                        <select id="freq_drop_offer_template" class="form-control">
                            <option value="">Select Whatsapp Template</option>
                            @foreach ($emailTemplates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Drop From Subscription -->
                <div class="p-3 mt-3" style="border: 2px dotted black; border-radius: 20px;">
                    <h6>Drop From Subscription</h6>
                    <div class="d-flex">
                        <div class="col-6">
                            <label>Whatsapp Template</label>
                            <select id="freq_drop_subscription_template" class="form-control">
                                <option value="">Select Whatsapp Template</option>
                                @foreach ($emailTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Promo Code</label>
                            <select id="freq_drop_subscription_promo_code" class="form-control">
                                <option value="">Select Promo Code</option>
                                @foreach ($promoCodes as $promoCode)
                                    <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Drop From Templates -->
                <div class="p-3 mt-3" style="border: 2px dotted black; border-radius: 20px;">
                    <h6>Drop From Templates</h6>
                    <div class="d-flex">
                        <div class="col-6">
                            <label>Whatsapp Template</label>
                            <select id="freq_drop_templates_template" class="form-control">
                                <option value="">Select Whatsapp Template</option>
                                @foreach ($emailTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Promo Code</label>
                            <select id="freq_drop_templates_promo_code" class="form-control">
                                <option value="">Select Promo Code</option>
                                @foreach ($promoCodes as $promoCode)
                                    <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" id="saveWhatsappFrequencyBtn" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

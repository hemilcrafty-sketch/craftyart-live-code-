<form id="emailCheckoutDropForm">
    @csrf

    <input type="hidden" name="name" id="config_name" value="email_checkout_drop_automation">

    <div class="p-4" style="border: 2px dotted black; border-radius: 20px;">
        <h6>Instant Email</h6>
        <div class="form-group">
            <label>Choose Email Template</label>
            <div class="d-flex">
                <select id="email_template_id" class="form-control email_template_id">
                    <option value="">-- Select Email Template --</option>
                    @foreach ($emailTemplates as $tpl)
                    <option value="{{ $tpl->id }}"
                            {{ old(
                    'template', $configs['email_checkout_drop_automation']['template'] ?? '') == $tpl->id ? 'selected' :
                    '' }}>
                    {{ $tpl->id }} - {{ $tpl->name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-info ml-2 preview_template_btn" disabled>
                    Preview
                </button>
            </div>
        </div>

        <div class="form-group">
            <label>Subject</label>
            <input type="text" class="form-control" name="subject" id="subject"
                   value="{{ old('subject', $configs['email_checkout_drop_automation']['subject'] ?? '') }}">
        </div>

        <div class="form-group">
            <label>Choose promo code</label>
            <div class="d-flex">
                {{-- MAIN promo code select (unique id) --}}
                <select name="promo_code" id="main_promo_code" class="form-control promo_code">
                    <option value="">-- Select promo code --</option>
                    @foreach ($promoCodes as $promoCode)
                    <option value="{{ $promoCode->id }}"
                            {{ old(
                    'promo_code', $configs['email_checkout_drop_automation']['promo_code'] ?? '') == $promoCode->id ?
                    'selected' : '' }}>
                    {{ $promoCode->promo_code }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <h6>Campaign</h6>
        <button type="button" class="btn btn-success mb-2" id="addFrequencyBtn">
            + Add Campaign
        </button>
        <div id="frequencyList"></div>
    </div>

    <button type="submit" class="btn btn-primary">Save Email Config</button>
</form>

<!-- Frequency Modal -->
<div class="modal fade" id="frequencyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Campaign</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndex" value="">
                <div class="form-group">
                    <label>Frequency in Days</label>
                    <input type="number" class="form-control" id="freq_days" min="0">
                </div>
                <div class="form-group">
                    <label>Email Subject</label>
                    <input type="text" class="form-control" id="freq_subject">
                </div>
                <div class="form-group">
                    <label>Email Template</label>
                    <div class="d-flex">
                        <select id="freq_template" class="form-control">
                            <option value="">-- Select Email Template --</option>
                            @foreach ($emailTemplates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->id }} - {{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-info ml-2 preview_template_btn" disabled>Preview</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Choose promo code</label>
                    <div class="d-flex">
                        {{-- per-frequency promo code (unique id) --}}
                        <select name="promo_code" id="freq_promo_code" class="form-control promo_code">
                            <option value="">-- Select promo code --</option>
                            @foreach ($promoCodes as $promoCode)
                            <option value="{{ $promoCode->id }}">{{ $promoCode->promo_code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveFrequencyBtn" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<form id="whatsappConfigForm">
    @csrf

    {{-- hidden config name --}}
    <input type="hidden" name="name" id="config_name" value="whatsapp_offer_purchase_automation">

    <div class="form-group">
        <h6>Whatsapp Template</h6>
        <div class="d-flex">
            <select id="whatsapp_template_id" class="form-control whatsapp_template_id">
                <option value="">-- Select Whatsapp Template --</option>
                @foreach ($whatsappTemplates as $tpl)
                    <option value="{{ $tpl->id }}"
                        {{ old('template', $configs['whatsapp_offer_purchase_automation']['template'] ?? '') == $tpl->id ? 'selected' : '' }}>
                        {{ $tpl->id }} - {{ $tpl->campaign_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- <div class="form-group">
        <label>Whatsapp Message Title</label>
        <input type="text" class="form-control" name="subject" id="whatsapp_subject"
            value="{{ old('subject', $configs['whatsapp_offer_purchase_automation']['subject'] ?? '') }}">
    </div> --}}

    <button type="submit" class="btn btn-primary">Save Whatsapp Config</button>
</form>

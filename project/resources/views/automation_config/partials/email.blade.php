<form id="emailConfigForm">
    @csrf

    {{-- hidden config name --}}
    <input type="hidden" name="name" id="config_name" value="email_offer_purchase_automation">

    <div class="form-group">
        <h6>Email Template</h6>

        <div class="d-flex">
            <select id="email_template_id" class="form-control email_template_id">
                <option value="">-- Select Email Template --</option>
                @foreach ($emailTemplates as $tpl)
                    <option value="{{ $tpl->id }}"
                            {{ old('template', $configs['email_offer_purchase_automation']['template'] ?? '') == $tpl->id ? 'selected' : '' }}>
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
        <label>Email Subject</label>
        <input type="text" class="form-control" name="subject" id="subject"
               value="{{ old('subject', $configs['email_offer_purchase_automation']['subject'] ?? '') }}">
    </div>

    <button type="submit" class="btn btn-primary">Save Email Config</button>
</form>

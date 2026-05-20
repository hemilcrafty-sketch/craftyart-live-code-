@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    .payment-config-wrapper {
        padding: 20px;
    }

    .page-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 25px 30px;
        margin-bottom: 25px;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .page-header-card h4 {
        margin: 0;
        font-weight: 700;
        font-size: 24px;
        color: white;
    }

    .page-header-card p {
        margin: 8px 0 0 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
    }

    .add-gateway-btn {
        background: white;
        border: none;
        color: #667eea;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .add-gateway-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: #667eea;
    }

    .gateway-section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .section-header-row {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .section-icon-box {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .section-icon-box i {
        color: white;
        font-size: 20px;
    }

    .section-title-text h5 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
        font-size: 18px;
    }

    .section-title-text small {
        color: #718096;
        font-size: 13px;
    }


    .gateway-card-item {
        background: #f8f9fa;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .gateway-card-item.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
    }

    .gateway-card-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .gateway-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .gateway-info-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .gateway-radio-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .gateway-name-label {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        cursor: pointer;
    }

    .status-badge-pill {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge-pill.active {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .status-badge-pill.inactive {
        background: #e2e8f0;
        color: #718096;
    }

    .gateway-actions-btns {
        display: flex;
        gap: 8px;
    }

    .action-btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-btn-icon.edit {
        background: #e6f7ff;
        color: #1890ff;
    }

    .action-btn-icon.edit:hover {
        background: #1890ff;
        color: white;
    }

    .action-btn-icon.delete {
        background: #fff1f0;
        color: #ff4d4f;
    }

    .action-btn-icon.delete:hover {
        background: #ff4d4f;
        color: white;
    }


    .payment-types-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
    }

    .payment-types-box h6 {
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 10px;
    }

    .payment-type-tags-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .payment-type-tag-item {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .payment-type-tag-item.caricature {
        background: #fff0f6;
        color: #c41d7f;
    }

    .payment-type-tag-item.template {
        background: #e6f7ff;
        color: #0958d9;
    }

    .payment-type-tag-item.video {
        background: #f6ffed;
        color: #389e0d;
    }

    .payment-type-tag-item.ai_credit {
        background: #fff7e6;
        color: #d46b08;
    }

    .payment-type-tag-item.subscription {
        background: #f9f0ff;
        color: #722ed1;
    }

    .credentials-grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .credential-field-box {
        margin-bottom: 0;
    }

    .credential-field-box label {
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 6px;
        display: block;
    }

    .credential-field-box input {
        width: 100%;
        padding: 8px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .credential-field-box input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .credential-field-box input:disabled {
        background: #f7fafc;
        color: #a0aec0;
    }

    .save-config-btn {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
    }

    .save-config-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(17, 153, 142, 0.4);
        color: white;
    }

    /* Environment Mode Selector Styles */
    .environment-mode-selector {
        background: #f8f9fa;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .environment-label {
        font-size: 14px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 12px;
        display: block;
    }

    .mode-toggle-group {
        display: flex;
        gap: 15px;
        margin-bottom: 8px;
    }

    .mode-option {
        flex: 1;
    }

    .mode-radio {
        display: none;
    }

    .mode-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        font-size: 14px;
        color: #4a5568;
        gap: 8px;
    }

    .mode-label:hover {
        border-color: #cbd5e0;
        background: #f7fafc;
    }

    .mode-radio:checked + .mode-label {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .mode-radio:disabled + .mode-label {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .empty-state-box {
        text-align: center;
        padding: 40px 20px;
        color: #718096;
    }

    .empty-state-box i {
        font-size: 40px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .empty-state-box h6 {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .empty-state-box p {
        font-size: 13px;
        margin: 0;
    }


    /* Modal Styles */

    .modal-dialog.modal-lg {
        margin: 1.75rem auto;
    }

    .modal-content {
        max-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .modal-body {
        overflow-y: auto;
        flex: 1 1 auto;
        max-height: calc(90vh - 130px); /* Adjust based on header+footer height */
    }

    .modal-header,
    .modal-footer {
        flex-shrink: 0;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 18px 25px;
        border-bottom: none;
    }

    .modal-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 18px;
        color: white;
    }

    .modal-header .close {
        color: white;
        opacity: 0.9;
        text-shadow: none;
        font-size: 26px;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 25px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 6px;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .payment-types-selector-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .payment-types-selector-box h6 {
        font-size: 13px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 10px;
    }

    .payment-type-checkbox-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-type-checkbox-item:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }

    .payment-type-checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .payment-type-checkbox-item label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        color: #2d3748;
        flex: 1;
        font-size: 13px;
    }

    .payment-type-checkbox-item.checked {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
    }

    .credential-row-box {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        border: 1px solid #e2e8f0;
    }

</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="payment-config-wrapper">
                <!-- Page Header -->
                <div class="page-header-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4><i class="fas fa-credit-card mr-2"></i>Payment Gateway Configuration</h4>
                            <p>Manage payment gateways for national and international transactions</p>
                        </div>
                        <button type="button" class="add-gateway-btn" data-bs-toggle="modal"
                                data-bs-target="#addGatewayModal">
                            <i class="fas fa-plus mr-2"></i>Add New Gateway
                        </button>
                    </div>
                </div>

                {{-- Compact Pending Configuration Warning --}}
                    @if(!empty($nationalPaymentPendingConfig) && count($nationalPaymentPendingConfig) > 0)
                    <div class="mb-4"
                         style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 15px 20px;">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #856404;"></i>
                            </div>
                            <div>
                                <strong style="color: #856404; font-size: 15px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-info-circle mr-1"></i>Pending National Configuration Required
                                </strong>
                                <p style="color: #856404; margin-bottom: 8px; font-size: 13px;">
                                    The following payment types need gateway configuration for national transactions:
                                </p>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @foreach($nationalPaymentPendingConfig as    $pendingType)
                                    @php
                                       $config = ['icon' => 'fa-crown', 'label' => ucwords(str_replace('_', ' ', $pendingType))];
                                    @endphp
                                    <span class="payment-type-tag-item subscription">
                                        <i class="fas {{ $config['icon'] }} mr-1"></i> {{ $config['label'] }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                {{-- Pending Configuration Warning for International --}}
                    @if(!empty($internationalPaymentPendingConfig) && count($internationalPaymentPendingConfig) > 0)
                    <div class="mb-4"
                         style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 15px 20px;">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #856404;"></i>
                            </div>
                            <div>
                                <strong style="color: #856404; font-size: 15px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-info-circle mr-1"></i>Pending International Configuration Required
                                </strong>
                                <p style="color: #856404; margin-bottom: 8px; font-size: 13px;">
                                    The following payment types need gateway configuration for international
                                    transactions:
                                </p>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @foreach($internationalPaymentPendingConfig as $pendingType)
                                    @php  
                                        $config = ['icon' => 'fa-crown', 'label' => ucwords(str_replace('_', ' ', $pendingType))];
                                    @endphp
                                    <span class="payment-type-tag-item subscription">
                                        <i class="fas {{ $config['icon'] }} mr-1"></i> {{ $config['label'] }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- National Payment Section -->
                <div class="gateway-section-card">
                    <div class="section-header-row">
                        <div class="section-icon-box">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="section-title-text">
                            <h5>National Payment Gateways (India)</h5>
                            <small>Configure payment gateways for domestic transactions</small>
                        </div>
                    </div>

                    <form id="nationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="NATIONAL">
                        <div class="row">
                            @forelse($nationalGateways as $gateway)
                            @php
                            $credentials = is_array($gateway->credentials) ? $gateway->credentials :
                            (is_string($gateway->credentials) ? json_decode($gateway->credentials, true) : []);
                            $paymentTypes = is_array($gateway->payment_types) ? $gateway->payment_types :
                            (is_string($gateway->payment_types) ? json_decode($gateway->payment_types, true) : []);
                            $credentials = $credentials ?? [];
                            $paymentTypes = $paymentTypes ?? [];
                            @endphp
                            <div class="col-md-6">
                                <div class="gateway-card-item active"
                                     id="national-card-{{ $gateway->id }}">
                                    <div class="gateway-header-row">
                                        <div class="gateway-info-left">
                                            <label for="national-{{ $gateway->id }}" class="gateway-name-label">
                                                {{ ucfirst(str_replace('_', ' ', $gateway->gateway)) }}
                                            </label>
                                        </div>
                                        <div class="gateway-actions-btns">
                                            <button type="button" class="action-btn-icon edit edit-gateway-btn"
                                                    data-id="{{ $gateway->id }}" title="Edit Gateway">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Payment Types -->
                                    @if(count($paymentTypes) > 0)
                                    <div class="payment-types-box">
                                        <h6><i class="fas fa-tags mr-2"></i>Supported Payment Types</h6>
                                        <div class="payment-type-tags-row">
                                            @foreach($paymentTypes as $type)
                                            @php
                                               $config = ['icon' => 'fa-crown', 'label' => ucwords(str_replace('_', ' ', $type))];
                                            @endphp
                                            <span class="payment-type-tag-item subscription">
                                                <i class="fas {{ $config['icon'] }}"></i> {{ $config['label'] }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="payment-types-box">
                                        <h6><i class="fas fa-tags mr-2"></i>No Payment Types Supported</h6>
                                    </div>
                                    @endif

                                    <!-- Credentials -->
                                    <div class="gateway-fields" id="national-fields-{{ $gateway->id }}"
                                         >
                                        @if(count($credentials) > 0)

                                        {{-- Add Sandbox/Live toggle for PhonePe --}}
                                        @if(strtolower($gateway->gateway) === 'phonepe')
                                        <div class="environment-mode-selector mb-4">
                                            <label class="environment-label">
                                                <i class="fas fa-server mr-2"></i>Environment Mode
                                            </label>
                                            <div class="mode-toggle-group">
                                                <div class="mode-option">
                                                    <label for="phonepe-sandbox-{{ $gateway->id }}" class="mode-label">
                                                        <i class="fas fa-flask"></i> Sandbox
                                                    </label>
                                                </div>
                                                <div class="mode-option">

                                                    <label for="phonepe-live-{{ $gateway->id }}" class="mode-label">
                                                        <i class="fas fa-check-circle"></i> Live
                                                    </label>
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-info-circle"></i>
                                                Sandbox mode uses test credentials. Switch to Live for production.
                                            </small>
                                        </div>
                                        @endif

                                        <div class="credentials-grid-layout">

                                            @foreach($credentials as $fieldKey => $fieldValue)
                                            <div class="credential-field-box">
                                                <label for="national-{{ $gateway->id }}-{{ $fieldKey }}">
                                                    {{ ucfirst(str_replace('_', ' ', $fieldKey)) }}
                                                    @if($fieldKey !== 'webhook_url')
                                                    <span class="text-danger">*</span>
                                                    @else
                                                    <i class="fas fa-info-circle text-info"
                                                       title="Auto-generated webhook URL"></i>
                                                    @endif
                                                </label>
                                                <input type="text"
                                                       class="gateway-input encrypted-field {{ $fieldKey === 'webhook_url' ? 'webhook-url-field' : '' }}"
                                                       id="national-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       name="credentials[{{ $fieldKey }}]"
                                                       value="{{ $fieldValue }}"
                                                       readonly
                                                       placeholder="Enter {{ str_replace('_', ' ', $fieldKey) }}"
                                                        }}
                                                {{ $fieldKey === 'webhook_url' ? '' : 'required' }}
                                                {{ $fieldKey === 'webhook_url' ? 'readonly' : '' }}
                                                style="{{ $fieldKey === 'webhook_url' ? 'background: #f0f9ff;
                                                border-color: #0ea5e9; color: #0369a1; cursor: not-allowed;' : '' }}">
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="empty-state-box">
                                            <i class="fas fa-key"></i>
                                            <h6>No Credentials Configured</h6>
                                            <p>Click edit button to add credential fields</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="empty-state-box">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h6>No National Gateways</h6>
                                    <p>Click "Add New Gateway" button to add national payment gateways</p>
                                </div>
                            </div>
                            @endforelse
                        </div>

                        @if(count($nationalGateways) > 0)
                        <div class="text-right mt-4">
                            <button type="button" class="save-config-btn" id="saveNationalConfig">
                                <i class="fas fa-save mr-2"></i>Save National Configuration
                            </button>
                        </div>
                        @endif
                    </form>
                </div>


                <!-- International Payment Section -->
                <div class="gateway-section-card">
                    <div class="section-header-row">
                        <div class="section-icon-box"
                             style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="section-title-text">
                            <h5>International Payment Gateways</h5>
                            <small>Configure payment gateways for international transactions</small>
                        </div>
                    </div>

                    

                    <form id="internationalPaymentForm">
                        @csrf
                        <input type="hidden" name="scope" value="INTERNATIONAL">
                        <div class="row">
                            @forelse($internationalGateways as $gateway)
                            @php
                            $credentials = is_array($gateway->credentials) ? $gateway->credentials :
                            (is_string($gateway->credentials) ? json_decode($gateway->credentials, true) : []);
                            $paymentTypes = is_array($gateway->payment_types) ? $gateway->payment_types :
                            (is_string($gateway->payment_types) ? json_decode($gateway->payment_types, true) : []);
                            $credentials = $credentials ?? [];
                            $paymentTypes = $paymentTypes ?? [];
                            @endphp
                            <div class="col-md-6">
                                <div class="gateway-card-item active }}"
                                     id="international-card-{{ $gateway->id }}">
                                    <div class="gateway-header-row">
                                        <div class="gateway-info-left">

                                            <label for="international-{{ $gateway->id }}" class="gateway-name-label">
                                                {{ ucfirst(str_replace('_', ' ', $gateway->gateway)) }}
                                            </label>
                                        </div>
                                        <div class="gateway-actions-btns">
                                            <button type="button" class="action-btn-icon edit edit-gateway-btn"
                                                    data-id="{{ $gateway->id }}" title="Edit Gateway">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                        </div>
                                    </div>

                                    <!-- Payment Types -->
                                    @if(count($paymentTypes) > 0)
                                    <div class="payment-types-box">
                                        <h6><i class="fas fa-tags mr-2"></i>Supported Payment Types</h6>
                                        <div class="payment-type-tags-row">
                                            @foreach($paymentTypes as $type)
                                            @php
                                                $config = ['icon' => 'fa-crown', 'label' => ucwords(str_replace('_', ' ', $type))];
                                            @endphp
                                            <span class="payment-type-tag-item subscription">
                                                <i class="fas {{ $config['icon'] }}"></i> {{ $config['label'] }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="payment-types-box">
                                        <h6><i class="fas fa-tags mr-2"></i>No Payment Types Supported</h6>
                                    </div>
                                    @endif

                                    <!-- Credentials -->
                                    <div class="gateway-fields" id="international-fields-{{ $gateway->id }}">
                                        @if(count($credentials) > 0)
                                        <div class="credentials-grid-layout">
                                            @foreach($credentials as $fieldKey => $fieldValue)
                                            <div class="credential-field-box">
                                                <label for="international-{{ $gateway->id }}-{{ $fieldKey }}">
                                                    {{ ucfirst(str_replace('_', ' ', $fieldKey)) }}
                                                    @if($fieldKey !== 'webhook_url')
                                                    <span class="text-danger">*</span>
                                                    @else
                                                    <i class="fas fa-info-circle text-info"
                                                       title="Auto-generated webhook URL"></i>
                                                    @endif
                                                </label>
                                                <input type="text"
                                                       class="gateway-input encrypted-field {{ $fieldKey === 'webhook_url' ? 'webhook-url-field' : '' }}"
                                                       id="international-{{ $gateway->id }}-{{ $fieldKey }}"
                                                       name="credentials[{{ $fieldKey }}]"
                                                       value="{{ $fieldValue }}"
                                                       readonly disabled
                                                       placeholder="Enter {{ str_replace('_', ' ', $fieldKey) }}"
                                                {{ $fieldKey === 'webhook_url' ? '' : 'required' }}
                                                {{ $fieldKey === 'webhook_url' ? 'readonly' : '' }}
                                                style="{{ $fieldKey === 'webhook_url' ? 'background: #f0f9ff;
                                                border-color: #0ea5e9; color: #0369a1; cursor: not-allowed;' : '' }}">
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="empty-state-box">
                                            <i class="fas fa-key"></i>
                                            <h6>No Credentials Configured</h6>
                                            <p>Click edit button to add credential fields</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="empty-state-box">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h6>No International Gateways</h6>
                                    <p>Click "Add New Gateway" button to add international payment gateways</p>
                                </div>
                            </div>
                            @endforelse
                        </div>

                        @if(count($internationalGateways) > 0)
                        <div class="text-right mt-4">
                            <button type="button" class="save-config-btn" id="saveInternationalConfig">
                                <i class="fas fa-save mr-2"></i>Save International Configuration
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('pricing.payment_configuration.modals')
@include('layouts.masterscript')

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

<script>


    $(document).ready(function () {

        // Payment type configuration for dynamic rendering
        const paymentTypeConfig = {
            @foreach($paymentType as $type)
            @php
               $config = ['icon' => 'fa-crown', 'label' => ucwords(str_replace('_', ' ', $type))];
            @endphp
            '{{ $type }}': {
                icon: '{{ $config['icon'] }}',
                label: '{{ $config['label'] }}'
            },
            @endforeach
        };

        function decrypt(jsonStr, passphrase = "E@7r1K7!6v#KZx^m") {

            if (!jsonStr) {
                return null;
            }

            try {

                const json = typeof jsonStr === "string" ? JSON.parse(jsonStr) : jsonStr;

                if (!json || !json.s || !json.i || !json.c) {
                    return null;
                }

                // Convert values
                const salt = CryptoJS.enc.Hex.parse(json.s);
                const iv = CryptoJS.enc.Hex.parse(json.i);
                const ciphertext = CryptoJS.enc.Base64.parse(json.c);

                // === Key Derivation ===
                const passWA = CryptoJS.enc.Utf8.parse(passphrase);
                const concatedPassphrase = passWA.clone().concat(salt);

                let md5 = [];
                md5[0] = CryptoJS.MD5(concatedPassphrase);

                let result = md5[0];
                let i = 1;

                while (result.sigBytes < 32) {
                    md5[i] = CryptoJS.MD5(md5[i - 1].clone().concat(concatedPassphrase));
                    result = result.clone().concat(md5[i]);
                    i++;
                }

                const key = CryptoJS.lib.WordArray.create(result.words.slice(0, 8), 32);

                // === Decrypt ===
                const decrypted = CryptoJS.AES.decrypt(
                    {ciphertext: ciphertext},
                    key,
                    {
                        iv: iv,
                        mode: CryptoJS.mode.CBC,
                        padding: CryptoJS.pad.Pkcs7
                    }
                );


                const decryptedStr = decrypted.toString(CryptoJS.enc.Utf8);

                try {
                    return JSON.parse(decryptedStr);
                } catch {
                    return decryptedStr;
                }

            } catch (error) {
                return null;
            }
        }

        // Handle gateway radio button selection (just UI update, no activation)
        $(document).on('change', '.national-gateway-radio, .international-gateway-radio', function () {
            const gatewayId = $(this).data('id');
            const scope = $(this).hasClass('national-gateway-radio') ? 'NATIONAL' : 'INTERNATIONAL';
            const gatewayName = $(this).val();

            // Just update UI, don't activate yet
            const radioClass = scope === 'NATIONAL' ? '.national-gateway-radio' : '.international-gateway-radio';

            $(radioClass).each(function () {
                const id = $(this).data('id');
                const card = $(`#${scope.toLowerCase()}-card-${id}`);
                const fields = $(`#${scope.toLowerCase()}-fields-${id}`);

                if (id == gatewayId) {
                    // Enable this gateway's fields for editing
                    card.addClass('active');
                    fields.css('opacity', '1');
                    fields.find('input').prop('disabled', false);
                } else {
                    // Disable other gateways' fields
                    card.removeClass('active');
                    fields.css('opacity', '0.6');
                    fields.find('input').prop('disabled', true);
                }
            });
        });

        // Payment type checkbox styling
        $(document).on('change', '.payment-type-checkbox-item input[type="checkbox"]', function () {
            if ($(this).is(':checked')) {
                $(this).closest('.payment-type-checkbox-item').addClass('checked');
            } else {
                $(this).closest('.payment-type-checkbox-item').removeClass('checked');
            }
        });

        // Add new credential field in add modal
        $(document).on('click', '#addNewCredentialField', function () {

            const fieldHtml = `
            <div class="credential-row-box">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Key *</label>
                            <input type="text"
                                   class="form-control credential-key"
                                   name="credential_keys[]"
                                   placeholder="e.g., api_key, secret_key"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Value *</label>
                            <input type="text"
                                   class="form-control credential-value"
                                   name="credential_values[]"
                                   placeholder="Enter value"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                style="margin-bottom: 8px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
            $('#addCredentialsContainer').append(fieldHtml);

            // Enable remove button for first field if there are more than 1 fields
            if ($('#addCredentialsContainer .credential-row-box').length > 1) {
                $('#addCredentialsContainer .credential-row-box:first .remove-add-credential-field').prop('disabled', false);
            }
        });

        // Remove credential field in add modal
        $(document).on('click', '.remove-add-credential-field', function () {
            if ($('#addCredentialsContainer .credential-row-box').length > 1) {
                $(this).closest('.credential-row-box').remove();

                // Disable remove button for first field if only 1 field remains
                if ($('#addCredentialsContainer .credential-row-box').length === 1) {
                    $('#addCredentialsContainer .credential-row-box:first .remove-add-credential-field').prop('disabled', true);
                }
            }
        });

        // Add new gateway with credentials and payment types
        $('#addGatewayForm').on('submit', function (e) {
            e.preventDefault();

            // Validate payment types
            const selectedTypes = $('#addGatewayModal input[name="payment_types[]"]:checked').length;

            // if (selectedTypes === 0) {
            //     showNotification('error', 'Please select at least one payment type');
            //     return;
            // }

            const formData = new FormData(this);
            const saveBtn = $(this).find('button[type="submit"]');
            const originalText = saveBtn.html();

            // Log form data for debugging

            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                url: '{{ route("payment.config.add-gateway") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#addGatewayModal').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function (xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error adding gateway';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showNotification('error', message);
                }
            });
        });

        // Edit gateway - Open modal with data
        $(document).on('click', '.edit-gateway-btn', function (e) {
            e.stopPropagation();
            const gatewayId = $(this).data('id');

            $('#editGatewayModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

            $.get(`payment_configuration/${gatewayId}/get`, function (response) {
                if (response.success) {
                    populateEditModal(response.gateway);
                } else {
                    $('#editGatewayModal').modal('hide');
                    showNotification('error', response.message || 'Failed to load gateway data');
                }
            }).fail(function () {
                $('#editGatewayModal').modal('hide');
                showNotification('error', 'Failed to load gateway data');
            });
        });

// Populate edit modal with gateway data
        function populateEditModal(gateway) {

            // Parse credentials if it's a string
            let credentials = gateway.credentials || {};
            if (typeof credentials === 'string') {
                try {
                    credentials = JSON.parse(credentials);
                } catch (e) {
                    credentials = {};
                }
            }

            // Parse payment_types if it's a string
            let paymentTypes = gateway.payment_types || [];
            if (typeof paymentTypes === 'string') {
                try {
                    paymentTypes = JSON.parse(paymentTypes);
                } catch (e) {
                    paymentTypes = [];
                }
            }

            let credentialsHtml = '';
            let index = 0;
            if (Object.keys(credentials).length > 0) {
                Object.entries(credentials).forEach(([key, value]) => {
                    const decryptValue = decrypt(value);
                    credentialsHtml += `
                <div class="credential-row-box">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Key *</label>
                                <input type="text" class="form-control" name="credential_keys[]" value="${key}" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small">Field Value *</label>
                                <input type="text" class="form-control" name="credential_values[]" value="${decryptValue}" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-edit-credential-field" style="margin-bottom: 8px;" ${index === 0 ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
                    index++;
                });
            } else {
                credentialsHtml = `
            <div class="credential-row-box">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Key *</label>
                            <input type="text" class="form-control" name="credential_keys[]" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Value *</label>
                            <input type="text" class="form-control" name="credential_values[]" required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-edit-credential-field" style="margin-bottom: 8px;" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
            }

            // Generate payment types HTML dynamically
            const availablePaymentTypes = @json($paymentType);
            let paymentTypesHtml = '';
            
            availablePaymentTypes.forEach(type => {
                const config = paymentTypeConfig[type] || { icon: 'fa-tag', label: type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) };
                const isChecked = paymentTypes.includes(type);
                
                paymentTypesHtml += `
        <div class="payment-type-checkbox-item ${isChecked ? 'checked' : ''}" data-type="${type}">
            <input type="checkbox" name="payment_types[]" value="${type}" id="edit-type-${type}" ${isChecked ? 'checked' : ''}>
            <label for="edit-type-${type}"><i class="fas ${config.icon} mr-2"></i>${config.label}</label>
        </div>`;
            });

            $('#editModalBody').html(`
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Gateway Name *</label>
                    <input type="text" name="gateway" class="form-control" value="${gateway.gateway}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Payment Scope *</label>
                    <select name="scope" class="form-control" required>
                        <option value="NATIONAL" ${gateway.payment_scope === 'NATIONAL' ? 'selected' : ''}>National</option>
                        <option value="INTERNATIONAL" ${gateway.payment_scope === 'INTERNATIONAL' ? 'selected' : ''}>International</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="payment-types-selector-box">
            <h6><i class="fas fa-tags mr-2"></i>Select Payment Types *</h6>
            <small class="text-muted d-block mb-3">Choose which payment types this gateway will handle</small>
            ${paymentTypesHtml}
        </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="fas fa-key mr-2"></i>Credentials Configuration *</h6>
                <button type="button" class="btn btn-sm btn-success" id="addEditCredentialField">
                    <i class="fas fa-plus"></i> Add Field
                </button>
            </div>
            <div id="editCredentialsContainer">
                ${credentialsHtml}
            </div>
        </div>
    `);

            $('#editGatewayId').val(gateway.id);

            // Attach event handlers
            $('.payment-type-checkbox-item input[type="checkbox"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $(this).closest('.payment-type-checkbox-item').addClass('checked');
                } else {
                    $(this).closest('.payment-type-checkbox-item').removeClass('checked');
                }
            });

            $('#addEditCredentialField').on('click', function () {
                const newFieldHtml = `
            <div class="credential-row-box">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Key *</label>
                            <input type="text" class="form-control" name="credential_keys[]" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Value *</label>
                            <input type="text" class="form-control" name="credential_values[]" required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-edit-credential-field" style="margin-bottom: 8px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
                $('#editCredentialsContainer').append(newFieldHtml);

                if ($('#editCredentialsContainer .credential-row-box').length > 1) {
                    $('#editCredentialsContainer .credential-row-box:first .remove-edit-credential-field').prop('disabled', false);
                }
            });
        }

// Remove credential field in edit modal
        $(document).on('click', '.remove-edit-credential-field', function () {
            if ($('#editCredentialsContainer .credential-row-box').length > 1) {
                $(this).closest('.credential-row-box').remove();

                if ($('#editCredentialsContainer .credential-row-box').length === 1) {
                    $('#editCredentialsContainer .credential-row-box:first .remove-edit-credential-field').prop('disabled', true);
                }
            }
        });

// Update gateway
        $('#editGatewayForm').on('submit', function (e) {
            e.preventDefault();

            // Validate payment types
            const selectedTypes = $('input[name="payment_types[]"]:checked').length;
            // if (selectedTypes === 0) {
            //     showNotification('error', 'Please select at least one payment type');
            //     return;
            // }

            const gatewayId = $('#editGatewayId').val();
            const formData = new FormData(this);

            // const credentials = formData.getAll('credential_values[]');
            // formData.delete('credential_values[]');
            //
            // for (const credential of credentials) {
            //     const encrypted = encrypt(credential);
            //     formData.append('credential_values[]', encrypted);
            // }

            const saveBtn = $(this).find('button[type="submit"]');
            const originalText = saveBtn.html();

            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');


            $.ajax({
                url: `payment_configuration/${gatewayId}/update`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    saveBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#editGatewayModal').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function (xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error updating gateway';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotification('error', message);
                }
            });
        });

// Radio button change handlers
        $('.national-gateway-radio').on('change', function () {
            handleGatewaySelection('national', $(this).data('id'));
        });

        $('.international-gateway-radio').on('change', function () {
            handleGatewaySelection('international', $(this).data('id'));
        });

        function handleGatewaySelection(scope, gatewayId) {
            $(`.${scope}-gateway-radio`).each(function () {
                const currentId = $(this).data('id');
                const card = $(`#${scope}-card-${currentId}`);
                const badge = $(`#badge-${currentId}`);
                const fields = $(`#${scope}-fields-${currentId}`);

                if (currentId == gatewayId) {
                    card.addClass('active');
                    fields.find('.gateway-input').prop('disabled', false);
                    fields.css('opacity', '1');
                    badge.removeClass('inactive').addClass('active').html('<i class="fas fa-circle" style="font-size: 6px;"></i> Active');
                } else {
                    card.removeClass('active');
                    fields.find('.gateway-input').prop('disabled', true);
                    fields.css('opacity', '0.6');
                    badge.removeClass('active').addClass('inactive').html('<i class="fas fa-circle" style="font-size: 6px;"></i> Inactive');
                }
            });
        }

// Save configuration
        $('#saveNationalConfig').on('click', function () {
            saveConfiguration('NATIONAL');
        });

        $('#saveInternationalConfig').on('click', function () {
            saveConfiguration('INTERNATIONAL');
        });

        function encryptFromServer(value) {
            return $.ajax({
                url: '/encrypt-value',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    value: value
                }
            });
        }


        async function saveConfiguration(scope) {
            const radioClass = scope === 'NATIONAL' ? '.national-gateway-radio' : '.international-gateway-radio';
            const selectedRadio = $(radioClass + ':checked');

            if (selectedRadio.length === 0) {
                showNotification('error', `Please select a ${scope.toLowerCase()} payment gateway`);
                return;
            }

            const selectedGatewayId = selectedRadio.data('id');
            const selectedGateway = selectedRadio.val();
            const fieldsContainer = $(`#${scope.toLowerCase()}-fields-${selectedGatewayId}`);
            const selectedFields = fieldsContainer.find('.gateway-input');

            let isValid = true;
            const credentials = {};
            let hasAnyValue = false;

            selectedFields.each(function () {
                const name = $(this).attr('name');
                const value = $(this).val().trim();
                const fieldName = name.replace('credentials[', '').replace(']', '');

                // Skip validation for webhook_url (read-only field)
                if (fieldName === 'webhook_url') {
                    if (value) {
                        credentials[fieldName] = value;
                    }
                    return; // Skip to next field
                }


                // Skip validation for default value fields
                if (fieldName === 'client_version' || fieldName === 'salt_index' || fieldName === 'environment') {
                    if (value) {
                        credentials[fieldName] = value;
                        hasAnyValue = true;
                    }
                    return; // Skip to next field
                }

                if (value) {
                    $(this).removeClass('is-invalid');
                    $(this).css('border-color', '#e2e8f0');
                    credentials[fieldName] = value;
                    hasAnyValue = true;
                } else {
                    // Don't mark as invalid, just skip empty fields
                    $(this).removeClass('is-invalid');
                    $(this).css('border-color', '#e2e8f0');
                }
            });

            // Add environment mode for PhonePe
            if (selectedGateway.toLowerCase() === 'phonepe') {
                const environmentRadio = $(`input[name="phonepe_environment_${selectedGatewayId}"]:checked`);
                if (environmentRadio.length > 0) {
                    credentials['environment'] = environmentRadio.val();
                } else {
                    credentials['environment'] = 'sandbox'; // Default to sandbox
                }
            }

            // Allow activation even with empty credentials (user can fill later)
            if (!hasAnyValue && !credentials.webhook_url) {
                showNotification('warning', 'Gateway will be activated but credentials are empty. Please add credentials to process payments.');
            }

            const saveBtn = scope === 'NATIONAL' ? $('#saveNationalConfig') : $('#saveInternationalConfig');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');


            // First, activate the gateway
            $.ajax({
                url: `payment_configuration/${selectedGatewayId}/activate`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    scope: scope
                },
                success: function (activateResponse) {
                    if (activateResponse.success) {
                        // Then, save the credentials
                        $.ajax({
                            url: 'payment_configuration/' + selectedGatewayId + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                gateway: selectedGateway,
                                scope: scope,
                                credential_keys: Object.keys(credentials),
                                credential_values: Object.values(credentials)
                            },
                            success: function (updateResponse) {
                                saveBtn.prop('disabled', false).html(originalText);
                                if (updateResponse.success) {
                                    showNotification('success', 'Configuration saved and gateway activated successfully!');

                                    // Update UI for all gateways in this scope
                                    $(radioClass).each(function () {
                                        const id = $(this).data('id');
                                        const card = $(`#${scope.toLowerCase()}-card-${id}`);
                                        const badge = $(`#badge-${id}`);
                                        const fields = $(`#${scope.toLowerCase()}-fields-${id}`);

                                        if (id == selectedGatewayId) {
                                            // Activate this gateway
                                            card.addClass('active');
                                            badge.removeClass('inactive').addClass('active')
                                                .html('<i class="fas fa-circle" style="font-size: 6px;"></i> Active');
                                            fields.css('opacity', '1');
                                            fields.find('input').prop('disabled', false);
                                            fields.find('input').css('border-color', '#e2e8f0');
                                        } else {
                                            // Deactivate other gateways
                                            card.removeClass('active');
                                            badge.removeClass('active').addClass('inactive')
                                                .html('<i class="fas fa-circle" style="font-size: 6px;"></i> Inactive');
                                            fields.css('opacity', '0.6');
                                            fields.find('input').prop('disabled', true);
                                        }
                                    });
                                }
                            },
                            error: function (xhr) {
                                saveBtn.prop('disabled', false).html(originalText);
                                let message = 'Error saving credentials';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                showNotification('error', message);
                            }
                        });
                    } else {
                        saveBtn.prop('disabled', false).html(originalText);
                        showNotification('error', activateResponse.message || 'Failed to activate gateway');
                    }
                },
                error: function (xhr) {
                    saveBtn.prop('disabled', false).html(originalText);
                    let message = 'Error activating gateway';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotification('error', message);
                }
            });
        }

// Notification function
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' : 'alert-warning';
            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';

            $('.notification-alert').remove();

            const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert"
         style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); border-radius: 10px;">
        <div class="d-flex align-items-center">
            <i class="fas ${icon} mr-2" style="font-size: 18px;"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="close ml-2" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    </div>`;

            $('body').append(alertHtml);

            setTimeout(() => {
                $('.notification-alert').alert('close');
            }, 4000);
        }

// Initialize radio button states on page load
        $('.national-gateway-radio:checked').trigger('change');
        $('.international-gateway-radio:checked').trigger('change');

// Reset add form when modal is hidden
        $('#addGatewayModal').on('hidden.bs.modal', function () {
            $('#addGatewayForm')[0].reset();
            $('.payment-type-checkbox-item').removeClass('checked');
            $('#addCredentialsContainer').html(`
        <div class="credential-row-box">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <label class="small">Field Key *</label>
                        <input type="text" class="form-control credential-key" name="credential_keys[]" placeholder="e.g., api_key, secret_key" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <label class="small">Field Value *</label>
                        <input type="text" class="form-control credential-value" name="credential_values[]" placeholder="Enter value" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger remove-add-credential-field" style="margin-bottom: 8px;" disabled>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `);
        });

    }); // End of document.ready
</script>

@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')

<style>
    .subscription-badges .badge {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: help;
    }

    .subscription-field-row {
        align-items: center;
    }

    /* Drag and Drop Styles */
    .drag-handle {
        cursor: grab;
        color: #6c757d;
        font-size: 18px;
        user-select: none;
        display: inline-block;
        padding: 5px;
    }

    .drag-handle:hover {
        color: #007bff;
    }

    .drag-handle-cell {
        width: 50px;
        text-align: center;
        padding: 8px 0 !important;
    }

    .feature-handle {
        display: block;
        margin: 0 auto;
        width: 30px;
        height: 30px;
        line-height: 20px;
        text-align: center;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .category-handle {
        width: 30px;
        height: 30px;
        line-height: 20px;
        text-align: center;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #e9ecef;
    }

    /* Sortable Container */
    .sortable-container {
        position: relative;
    }

    /* Category Container */
    .category-container {
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        background: white;
        transition: all 0.3s ease;
    }

    .category-container.sortable-ghost {
        opacity: 0.4;
        background: #f1f8ff;
        border: 2px dashed #007bff;
    }

    .category-container.sortable-chosen {
        background: #e3f2fd;
        border-color: #2196f3;
        box-shadow: 0 0 15px rgba(33, 150, 243, 0.2);
    }

    .category-container.sortable-drag {
        transform: rotate(2deg);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 9999;
    }

    /* Category Header */
    .category-header {
        background: #f8f9fa;
        padding: 0;
        border-bottom: 2px solid #dee2e6;
    }

    .category-header td {
        padding: 12px 15px !important;
        font-weight: bold;
    }

    /* Features Container */
    .features-container {
        padding: 0;
    }

    /* Feature Group Container - NEW */
    .feature-group-container {
        position: relative;
        margin-bottom: 0;
        border-bottom: 1px solid #dee2e6;
        transition: all 0.3s ease;
        background: white;
    }

    .feature-group-container:last-child {
        border-bottom: none;
    }

    .feature-group-container.sortable-ghost {
        opacity: 0.4;
        background: #f1f8ff;
        border: 2px dashed #007bff;
    }

    .feature-group-container.sortable-chosen {
        background: #e3f2fd;
        border-color: #2196f3;
        box-shadow: 0 0 15px rgba(33, 150, 243, 0.2);
        z-index: 100;
    }

    .feature-group-container.sortable-drag {
        transform: rotate(2deg);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 9999;
    }

    .feature-group-container.selected {
        background-color: #e8f5e9;
    }

    .feature-group-container:hover {
        background-color: #f8f9fa;
    }

    /* Feature Row */
    .feature-row {
        display: table;
        width: 100%;
        border-collapse: collapse;
        position: relative;
    }

    .feature-row .drag-handle-cell,
    .feature-row .feature-cell {
        display: table-cell;
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }

    .feature-row .drag-handle-cell {
        width: 50px;
        text-align: center;
    }

    /* Sub-row */
    .sub-row-container {
        background: #f8f9fa;
        border-left: 3px solid #007bff;
        padding: 15px;
        display: none;
        transition: all 0.3s ease;
    }

    .sub-row-container.active {
        display: block;
        background: #f0f8ff;
    }

    .sub-row-container.dragging {
        background: #e3f2fd;
    }

    /* Feature Table */
    .feature-table {
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
    }

    .feature-items {
        border: none;
    }

    .feature-items thead {
        background: #e9ecef;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .feature-items th {
        padding: 10px !important;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }

    /* Alert Box */
    .alert-info {
        background-color: #f0f8ff;
        border-color: #b3d9ff;
        color: #0066cc;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    /* Select All Container */
    .select-all-container {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 15px;
        text-align: right;
    }

    /* Ensure proper spacing */
    .feature-group-container .feature-row .feature-cell {
        padding: 12px 15px;
    }

    /* Make sure buttons remain clickable */
    .feature-group-container * {
        position: relative;
        z-index: 1;
    }

    .feature-group-container.dragging * {
        pointer-events: none;
    }

    .feature-group-container .drag-handle {
        pointer-events: auto !important;
    }

    /* Prevent text selection during drag */
    .sortable-drag *,
    .sortable-chosen * {
        user-select: none;
        -webkit-user-select: none;
    }

    /* Group dragging */
    .feature-group-container.dragging {
        opacity: 0.8;
    }

    /* Capping row styling */
    .sub-row-content {
        border-radius: 4px;
        padding: 10px;
        background: white;
        border: 1px solid #dee2e6;
        margin: 5px 0;
    }

    /* Remove the old table row styles */
    .feature-row-container,
    .sub-row {
        display: none;
    }

</style>

<div class="main-container">
    <div id="loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">
                <form method="post" id="createPlanForm">
                    <span id="result"></span>
                    @csrf
                    <input type="hidden" name="id" value="{{ $plan->id ?? '' }}">

                    <input type="hidden" name="_method" value="POST">

                    <div class="row">
                        <div class="col-md-10 col-sm-10">
                            <div class="form-group">
                                <h6>Plan Name</h6>
                                <input id="planName" placeholder="Enter Plan Name"
                                       value="{{ old('name', $planData->name ?? '') }}"
                                       class="form-control-file form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-2 m-auto">
                            <div class="form-group d-flex">
                                <h6>Recommended : </h6>
                                <input type="hidden" name="is_recommended" value="0">

                                <input type="checkbox" name="is_recommended" class="ml-3" value="1"
                                       {{ old('is_recommended', $planData->is_recommended ?? false) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                @php
                                use App\Http\Controllers\Utils\ContentManager;
                                @endphp

                                <h6>Plan Svg Icon</h6>
                                <input type="file"
                                       class="form-control-file form-select form-control dynamic-file height-auto"
                                       data-value="{{ ContentManager::getStorageLink($planData->icon) }}"
                                       data-accept=".svg" data-imgstore-id="icon" data-nameset="true">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <h6>Sub Plan Title</h6>
                                <input id="subTitle" placeholder="Enter Sub Plan Title"
                                       value="{{ old('sub_title', $planData->sub_title ?? '') }}"
                                       class="form-control-file form-control" name="sub_title" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <h6>Button Name</h6>
                                <input id="btnName" placeholder="Enter Button Name"
                                       value="{{ old('btn_name', $planData->btn_name ?? '') }}"
                                       class="form-control-file form-control" name="btn_name" required>
                            </div>
                        </div>
                    </div>

                    @if ($isFreePlan)
                    <input type="hidden" value="1" name="is_free_type">
                    @endif

                    <div class="card-body mt-3" style="box-shadow: 2px 2px 15px 0px silver;border-radius: 15px;">
                        @if (!$isFreePlan)
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Duration</h6>
                                    <select class="form-control" id="planDurationSelect">
                                        <option disabled selected>Select</option>
                                        @foreach ($planCategory as $category)
                                        <option value="{{ $category->id }}"
                                                data-string-id="{{ $category->string_id }}"
                                                data-duration-type="{{ $category->duration }}"
                                                {{ in_array($category->id, $alreadyAddedIds ?? []) ? 'disabled' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Additional Duration Value ( Days )</h6>
                                    <input class="form-control" placeholder="Additional Duration Value"
                                           name="additional_plan[]" type="number" min="0" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <h6>INR Price</h6>
                                    <input class="form-control inr-price" min="0" name="plan_price[]"
                                           type="number" step="any" placeholder="Enter Price">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <h6>INR Offer Price</h6>
                                    <input class="form-control inr-offer-price" min="0" type="number"
                                           name="plan_price[]" step="any" placeholder="Offer Price">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <h6>USD Price</h6>
                                    <input class="form-control usd-price" type="number" min="0"
                                           name="plan_price[]" step="any" placeholder="Enter Price">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <h6>USD Offer Price</h6>
                                    <input class="form-control usd-offer-price" min="0" type="number"
                                           name="plan_price[]" step="any" placeholder="Offer Price">
                                </div>
                            </div>
                        </div>

                        <!-- Subscription IDs Section -->
                        <div class="row mt-3" id="subscriptionIdsSection">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Subscription IDs (Payment Gateway IDs)</h6>
                                            <small class="text-muted">Add gateway keys and their subscription IDs</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSubscriptionFields()">
                                            <i class="fa fa-plus"></i> Add Gateway
                                        </button>
                                    </div>
                                    <div class="card-body" id="subscriptionFieldsContainer" style="display: none;">
                                        <div id="subscriptionFields">
                                            <!-- Dynamic fields will be added here -->
                                        </div>
                                        <div class="text-right mt-3">
                                            <button type="button" class="btn btn-success btn-sm" onclick="addSubscriptionField()">
                                                <i class="fa fa-plus"></i> Add Another Gateway
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add/Update Button Row -->
                        <div class="row mt-3">
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" id="addUpdateBtn" onclick="addCondition()">Add to Table</button>
                            </div>
                        </div>

                        <h4 class="mt-4">Duration Table : </h4>
                        <table id="conditionsTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Duration (Days)</th>
                                <th>Additional Duration (Days)</th>
                                <th>INR Price</th>
                                <th>INR Offer Price</th>
                                <th>USD Price</th>
                                <th>USD Offer Price</th>
                                <th>Subscription IDs</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($subPlan->isNotEmpty())
                            @foreach ($subPlan as $sub)
                            @php
                            $data = $sub->plan_details ?? [];
                            $subscriptionIds = $sub->subscription_ids ? $sub->subscription_ids : [];
                            @endphp
                            <tr id="row-{{ $sub->id }}" data-subplan-id="{{ $sub->id }}">
                                <td data-category-id="{{ $sub->duration_id }}"
                                    data-string-id="{{ $sub->string_id }}">
                                    {{ $sub->category->name ?? 'N/A' }}
                                    ({{ $sub->category->duration ?? 'N/A' }})
                                </td>
                                <td>{{ $data['additional_duration'] ?? '' }}</td>
                                <td>{{ $data['inr_price'] ?? '' }}</td>
                                <td>{{ $data['inr_offer_price'] ?? '' }}</td>
                                <td>{{ $data['usd_price'] ?? '' }}</td>
                                <td>{{ $data['usd_offer_price'] ?? '' }}</td>
                                <td class="subscription-cell">
                                    @if(!empty($subscriptionIds))
                                    @php
                                    // If it's already a JSON string, use it directly
                                    if (is_string($subscriptionIds)) {
                                    $jsonValue = $subscriptionIds;
                                    } else {
                                    $jsonValue = json_encode($subscriptionIds);
                                    }
                                    @endphp

                                    @php
                                    // For display, decode if needed
                                    if (is_string($subscriptionIds)) {
                                    $displayIds = json_decode($subscriptionIds, true);
                                    } else {
                                    $displayIds = $subscriptionIds;
                                    }
                                    @endphp

                                    @if(!empty($displayIds) && is_array($displayIds))
                                    <div class="subscription-badges">
                                        @foreach($displayIds as $key => $value)
                                        <span class="badge badge-info mr-1 mb-1" title="{{ $value }}">
                        {{ $key }}
                    </span>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-muted">No subscription IDs</span>
                                    @endif
                                    <input type="hidden" class="subscription-ids"
                                           value="{{ $jsonValue }}">
                                    @else
                                    <span class="text-muted">No subscription IDs</span>
                                    <input type="hidden" class="subscription-ids" value="{}">
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm"
                                            onclick="editRow(this)">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="removeRow(this)">Remove</button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                        @endif
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <h6>Plan Description</h6>
                                <textarea class="form-control" cols="30" rows="10" name="description" id="descriptionInput">{{ old('description', $planData->description ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <h6>Plan Sequence Number</h6>
                                <input type="number"
                                       value="{{ old('sequence_number', $planData->sequence_number ?? '') }}"
                                       placeholder="Enter Sequence Number" class="form-control" required
                                       name="sequence_number" id="descriptionInput">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <h6 class="mt-3">Plan Features</h6>
                            <div class="form-group form-container tableview">
                                @php
                                $appearanceData = collect($plan->appearance ?? []);
                                $selectedFeatureIds = $appearanceData->pluck('features_id')->toArray();
                                $appearanceByFeatureId = $appearanceData->keyBy('features_id');
                                @endphp

                                <!-- Drag Instructions -->
                                <div class="alert alert-info mb-3">
                                    <i class="fa fa-info-circle"></i> Drag the <i class="fa fa-arrows-alt"></i> icon to reorder. Drag categories or features within their sections.
                                </div>

                                <!-- Drag and Drop Container -->
                                <div id="features-drag-container" class="sortable-container">
                                    @foreach ($categoryFeatures as $categoryIndex => $categoryFeature)
                                    @if ($categoryFeature->Planfeatures->isNotEmpty())
                                    <!-- Category Container - Draggable -->
                                    <div class="category-container draggable-category"
                                         data-category-id="{{ $categoryFeature->id }}"
                                         data-index="{{ $categoryIndex }}">

                                        <!-- Category Header with Drag Handle -->
                                        <div class="category-header">
                                            <table class="feature-table w-100 mb-0">
                                                <tbody>
                                                <tr>
                                                    <td colspan="8" style="background-color: #f8f9fa; font-weight: bold; padding: 12px 15px;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="drag-handle category-handle mr-2">&#9776;</div>
                                                            <span>{{ \Str::title($categoryFeature->name) }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Features Container for this Category -->
                                        <div class="features-container" data-category-id="{{ $categoryFeature->id }}">
                                            <!-- Table Header -->
                                            <table class="feature-table feature-items w-100">
                                                <thead style="z-index: 2">
                                                <tr>
                                                    <th style="width:50px;">Drag</th>
                                                    <th style="width:50px;">No</th>
                                                    <th style="width:500px;">Feature Title</th>
                                                    <th style="width:70px;">Switch</th>
                                                    <th style="width:200px;">Appearance</th>
                                                    <th style="width:120px;">Capping</th>
                                                    <th style="width:120px;">Visible to user</th>
                                                    <th style="width:85px;">
                                                        <span>Include in Database</span>
                                                        <br>
                                                        Select All <input type="checkbox" class="feature-checkbox select_all" data-category="{{ $categoryFeature->id }}">
                                                    </th>
                                                </tr>
                                                </thead>
                                            </table>

                                            <!-- Features List Container (for Sortable) -->
                                            <div class="feature-list-container sortable-features"
                                                 data-category-id="{{ $categoryFeature->id }}">
                                                @foreach ($categoryFeature->Planfeatures as $featureIndex => $rowFeature)
                                                @php
                                                $appearance = $appearanceByFeatureId[$rowFeature->id] ?? [];
                                                $metaValue = isset($appearance['meta_value'])
                                                ? (int) $appearance['meta_value']
                                                : 0;
                                                $metaAppearance = $appearance['meta_appearance'] ?? '';
                                                $metaFeatureValue = $appearance['meta_feature_value'] ?? ['', ''];
                                                $subName = $appearance['sub_name'] ?? '';
                                                $isSelected = in_array($rowFeature->id, $selectedFeatureIds);
                                                $isVisibletoUser = $appearance['is_feature_visible'] ?? false;
                                                $order = $appearance['feature_order'] ?? $featureIndex;
                                                @endphp

                                                <!-- Feature Group Container (Main container that gets dragged) -->
                                                <div class="feature-group-container draggable-feature {{ $isSelected ? 'selected' : '' }}"
                                                     data-feature-id="{{ $rowFeature->id }}"
                                                     data-index="{{ $featureIndex }}"
                                                     data-category-id="{{ $categoryFeature->id }}"
                                                     data-order="{{ $order }}">

                                                    <!-- Main feature row -->
                                                    <div class="feature-row">
                                                        <div class="drag-handle-cell">
                                                            <div class="drag-handle feature-handle">&#9776;</div>
                                                        </div>

                                                        <div class="feature-cell" style="width:50px;">
                                                            {{ $rowFeature->id }}
                                                        </div>

                                                        <div class="feature-cell" style="width:500px;">
                                                            {{ $rowFeature->name }}
                                                            <!-- Hidden Slug -->
                                                            <input type="hidden"
                                                                   name="slug[{{ $rowFeature->id }}][slug]"
                                                                   value="{{ $rowFeature->slug }}">
                                                        </div>

                                                        <div class="feature-cell" style="width:70px;">
                                                            <select name="meta_data[{{ $rowFeature->id }}][meta_value]"
                                                                    class="form-control">
                                                                <option value="0"
                                                                        {{ $metaValue === 0 ? 'selected' : '' }}>OFF
                                                                </option>
                                                                <option value="1"
                                                                        {{ $metaValue === 1 ? 'selected' : '' }}>ON
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="feature-cell" style="width:200px;">
                                                            <input type="text"
                                                                   name="meta_data_appearance[{{ $rowFeature->id }}][meta_appearance]"
                                                                   class="form-control meta-appearance-input"
                                                                   value="{{ $metaAppearance }}"
                                                                   placeholder="Appearance value...">
                                                            <input type="hidden"
                                                                   name="meta_appearance_type[{{ $rowFeature->id }}][meta_type]"
                                                                   value="{{ $rowFeature->appearance_type }}">
                                                        </div>

                                                        <div class="feature-cell text-center" style="width:120px;">
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary subfeature-toggle-btn"
                                                                    data-feature-id="{{ $rowFeature->id }}"
                                                                    {{ $metaAppearance ? '' : 'disabled' }}>
                                                            Add Cape
                                                            </button>
                                                        </div>

                                                        <div class="feature-cell text-center" style="width:120px;">
                                                            <input type="checkbox" name="feature_visible[{{ $rowFeature->id }}]"
                                                                   class="visible-checkbox"
                                                                   value="1"
                                                                   {{ $isVisibletoUser ? 'checked' : '' }}>
                                                        </div>

                                                        <div class="feature-cell text-center" style="width:85px;">
                                                            <input type="checkbox" name="features[]"
                                                                   value="{{ $rowFeature->id }}"
                                                                   class="feature-checkbox"
                                                                   {{ $isSelected ? 'checked' : '' }}>
                                                        </div>
                                                    </div>

                                                    <!-- Sub-feature row (capping) - Now inside the same container -->
                                                    <div class="sub-row-container" id="sub-row-{{ $rowFeature->id }}"
                                                         data-feature-id="{{ $rowFeature->id }}"
                                                         style="{{ $metaFeatureValue[0] ? 'display:block;' : '' }}">
                                                        <div class="sub-row-content">
                                                            <div class="row align-items-center" style="justify-content: end;">
                                                                <div class="col-md-1">
                                                                    <p style="font-size: 35px; font-weight: 700; text-align: center; color: black;"
                                                                       class="mt-3">&#10551;</p>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label mb-1">Name</label>
                                                                    <input type="text"
                                                                           name="feature_sub_name[{{ $rowFeature->id }}]"
                                                                           class="form-control sub-name-input"
                                                                           value="{{ $subName }}">
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label mb-1">Feature Value</label>
                                                                    <input type="text"
                                                                           name="feature_meta_value[{{ $rowFeature->id }}][meta_feature_value][]"
                                                                           class="form-control sub-value-input"
                                                                           value="{{ $metaFeatureValue[0] ?? '' }}">
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label mb-1">Feature Duration Limit</label>
                                                                    <select name="feature_meta_value[{{ $rowFeature->id }}][meta_feature_value][]"
                                                                            class="form-control sub-frequency-select">
                                                                        <option value="">-- Choose --</option>
                                                                        <option value="daily"
                                                                                {{ ($metaFeatureValue[1] ?? '') === 'daily' ? 'selected' : '' }}>
                                                                        Daily</option>
                                                                        <option value="monthly"
                                                                                {{ ($metaFeatureValue[1] ?? '') === 'monthly' ? 'selected' : '' }}>
                                                                        Monthly</option>
                                                                        <option value="lifetime"
                                                                                {{ ($metaFeatureValue[1] ?? '') === 'lifetime' ? 'selected' : '' }}>
                                                                        Lifetime</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-1 text-end">
                                                                    <label class="form-label mb-1 invisible">actions</label>
                                                                    <div>
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-danger sub-row-remove">Remove</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    let requiredSlug = @json($slugOptions);
    let isFrrPlan = @json($isFreePlan) ?? false;
</script>
<script>
    let editingRow = null;
    let subscriptionFieldsVisible = false;
    let featureSubRowMap = new Map();
    $(document).ready(function() {
        initializeFeatureSubRowMapping();
        initializeFeatureStates();
        setupEventHandlers();
        // initializeSelectAll();
        initializeDragAndDrop();

        if (!isFrrPlan) {
            disableUsedOptions();
            addSubscriptionField();
        }
    });
    function initializeFeatureSubRowMapping() {
        // Map each feature to its sub-row container
        $('.feature-group-container').each(function() {
            const featureId = $(this).data('feature-id');
            const subRow = $(this).find('.sub-row-container');
            if (subRow.length) {
                featureSubRowMap.set(featureId, subRow);
            }
        });
    }
    function initializeDragAndDrop() {
        // Initialize categories dragging
        const categoriesContainer = document.getElementById('features-drag-container');
        if (!categoriesContainer) return;

        // Sortable for categories (parent level)
        new Sortable(categoriesContainer, {
            animation: 150,
            handle: '.category-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onStart: function(evt) {
                // When dragging category, highlight all its features
                const categoryId = evt.item.dataset.categoryId;
                $(`.feature-group-container[data-category-id="${categoryId}"]`).addClass('dragging');
            },
            onEnd: function(evt) {
                // Remove dragging class
                $('.feature-group-container').removeClass('dragging');
                updateCategoryOrder();
            }
        });

        // Initialize sortable for each category's features
        document.querySelectorAll('.feature-list-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.feature-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onStart: function(evt) {
                    const featureId = evt.item.dataset.featureId;
                    const subRow = featureSubRowMap.get(featureId);

                    // Add dragging class to the entire container
                    $(evt.item).addClass('dragging');
                    if (subRow && subRow.length) {
                        subRow.addClass('dragging');
                    }
                },
                onMove: function(evt) {
                    // Prevent dropping features into wrong categories
                    const draggedCategory = evt.dragged.dataset.categoryId;
                    const targetParent = evt.to.closest('.features-container');

                    if (targetParent && targetParent.dataset.categoryId !== draggedCategory) {
                        return false;
                    }
                    return true;
                },
                onEnd: function(evt) {
                    const featureId = evt.item.dataset.featureId;
                    const subRow = featureSubRowMap.get(featureId);

                    // Remove dragging class
                    $(evt.item).removeClass('dragging');
                    if (subRow && subRow.length) {
                        subRow.removeClass('dragging');
                    }

                    updateFeatureOrder();
                }
            });
        });
    }
    function updateCategoryOrder() {
        const categoryContainers = document.querySelectorAll('.category-container');
        const orderData = [];

        categoryContainers.forEach((container, index) => {
            const categoryId = container.dataset.categoryId;
            if (categoryId) {
                orderData.push({
                    categoryId: categoryId,
                    order: index
                });
            }
        });

        updateCategoryOrderInput(orderData);
    }
    function updateFeatureOrder() {
        const categoryContainers = document.querySelectorAll('.category-container');
        const orderData = [];
        let globalOrder = 0;

        categoryContainers.forEach(container => {
            const categoryId = container.dataset.categoryId;
            const featureGroups = container.querySelectorAll('.feature-group-container:not(.sortable-ghost)');

            featureGroups.forEach((featureGroup, index) => {
                const featureId = featureGroup.dataset.featureId;
                if (featureId) {
                    orderData.push({
                        featureId: featureId,
                        categoryId: categoryId,
                        order: globalOrder++
                    });
                }
            });
        });

        updateFeatureOrderInput(orderData);
    }
    function updateCategoryOrderInput(orderData) {
        $('input[name^="category_order["]').remove();

        orderData.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `category_order[${item.categoryId}]`;
            input.value = item.order;
            document.querySelector('#createPlanForm').appendChild(input);
        });
    }
    function updateFeatureOrderInput(orderData) {
        $('input[name^="feature_order["]').remove();

        orderData.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `feature_order[${item.featureId}]`;
            input.value = item.order;
            document.querySelector('#createPlanForm').appendChild(input);
        });
    }
    function initializeFeatureStates() {
        $('.feature-group-container').each(function() {
            const container = $(this);
            const featureId = container.data('feature-id');
            const checkbox = container.find('input.feature-checkbox');
            const appearanceInput = container.find('.meta-appearance-input');
            const subBtn = container.find('.subfeature-toggle-btn');
            const slug = container.find('input[name^="slug"]').val();

            setSubBtnState(featureId, appearanceInput, subBtn);
            toggleRowHighlight(checkbox);

            if (requiredSlug.includes(slug)) {
                checkbox.prop('checked', true)
                    .prop('required', true)
                    .prop('disabled', true);
                toggleRowHighlight(checkbox);
            }
        });

        // Update order on load
        updateFeatureOrder();
        updateCategoryOrder();
    }
    function setupEventHandlers() {
        // Appearance input change
        $(document).on('input change', '.meta-appearance-input', function() {
            const container = $(this).closest('.feature-group-container');
            const featureId = container.data('feature-id');
            const subBtn = container.find('.subfeature-toggle-btn');
            setSubBtnState(featureId, $(this), subBtn);

            if (!$(this).val()) {
                hideSubRow(featureId);
            }
        });

        // Sub-feature toggle button
        $(document).on('click', '.subfeature-toggle-btn', function(e) {
            e.stopPropagation();
            const btn = $(this);
            if (btn.prop('disabled')) return;
            const container = btn.closest('.feature-group-container');
            const featureId = container.data('feature-id');
            const subRow = container.find('.sub-row-container');

            if (subRow.is(':visible')) {
                hideSubRow(featureId);
            } else {
                showSubRow(featureId);
            }
        });

        // Remove sub-row
        $(document).on('click', '.sub-row-remove', function(e) {
            e.stopPropagation();
            const container = $(this).closest('.feature-group-container');
            const featureId = container.data('feature-id');
            removeSubRow(featureId);
        });

        // Feature checkbox change
        $(document).on('change', '.feature-checkbox', function(e) {
            e.stopPropagation();
            const checkbox = $(this);
            const container = checkbox.closest('.feature-group-container');
            const featureId = container.data('feature-id');

            toggleRowHighlight(checkbox);
            setSubRowRequiredByCheckbox(featureId, checkbox.is(':checked'));
        });

        // select_all logic - per-category select all
        $(document).on('change', '.select_all', function(e) {
            e.stopPropagation();
            const isChecked = $(this).prop('checked');
            const categoryId = $(this).data('category');
            const featuresContainer = $(this).closest('.features-container');

            featuresContainer.find('.feature-checkbox').each(function() {
                const checkbox = $(this);
                const container = checkbox.closest('.feature-group-container');
                const featureId = container.data('feature-id');
                const slug = container.find('input[name^="slug"]').val();

                // Only affect features in the same category
                if (container.data('category-id') == categoryId) {
                    if (requiredSlug.includes(slug)) {
                        checkbox.prop('checked', true)
                            .prop('required', true)
                            .prop('disabled', true);
                    } else {
                        checkbox.prop('checked', isChecked)
                            .prop('disabled', false)
                            .prop('required', false);
                    }

                    toggleRowHighlight(checkbox);
                    setSubRowRequiredByCheckbox(featureId, checkbox.is(':checked'));
                }
            });
        });

        // Make sure inputs and selects don't trigger drag
        $(document).on('mousedown', 'select, input, button', function(e) {
            e.stopPropagation();
        });
    }
    function initializeSelectAll() {
        const selectAllHtml = `
            <div class="select-all-container">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input select-all-features" id="selectAllFeatures">
                    <label class="form-check-label" for="selectAllFeatures">
                        <strong>Select All Features</strong>
                    </label>
                </div>
            </div>
        `;

        $('#features-drag-container').before(selectAllHtml);

        $('#selectAllFeatures').change(function() {
            const isChecked = $(this).prop('checked');

            $('.feature-checkbox').each(function() {
                const checkbox = $(this);
                const container = checkbox.closest('.feature-group-container');
                const slug = container.find('input[name^="slug"]').val();

                if (requiredSlug.includes(slug)) {
                    checkbox.prop('checked', true)
                        .prop('required', true)
                        .prop('disabled', true);
                } else {
                    checkbox.prop('checked', isChecked)
                        .prop('disabled', false)
                        .prop('required', false);
                }

                toggleRowHighlight(checkbox);

                const featureId = container.data('feature-id');
                setSubRowRequiredByCheckbox(featureId, checkbox.is(':checked'));
            });
        });
    }
    /* ------- Helper Functions ------- */

    function setSubBtnState(featureId, appearanceInput, subBtn) {
        const appearanceVal = $.trim(appearanceInput.val());
        subBtn.prop('disabled', !appearanceVal);
    }
    function showSubRow(featureId) {
        const subRow = featureSubRowMap.get(featureId);
        if (subRow && subRow.length) {
            subRow.slideDown(120);
            subRow.addClass('active');
        }
    }
    function hideSubRow(featureId) {
        const subRow = featureSubRowMap.get(featureId);
        if (subRow && subRow.length) {
            subRow.slideUp(120, function() {
                subRow.find('input, select').prop('required', false);
                subRow.removeClass('active');
            });
        }
    }
    function removeSubRow(featureId) {
        const subRow = featureSubRowMap.get(featureId);
        if (subRow && subRow.length) {
            subRow.find('.sub-name-input, .sub-value-input').val('');
            subRow.find('.sub-frequency-select').val('');
            subRow.slideUp(120, function() {
                subRow.removeClass('active');
            });
        }
    }
    function setSubRowRequiredByCheckbox(featureId, isChecked) {
        // Optional: Add if needed
    }
    function toggleRowHighlight(checkbox) {
        const container = checkbox.closest('.feature-group-container');
        if (checkbox.prop('checked')) {
            container.addClass('selected');
        } else {
            container.removeClass('selected');
        }
    }
    /* ------- Subscription Functions ------- */

    function toggleSubscriptionFields() {
        const container = document.getElementById('subscriptionFieldsContainer');
        const toggleBtn = document.querySelector('#subscriptionIdsSection .card-header button');

        if (!subscriptionFieldsVisible) {
            container.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fa fa-minus"></i> Hide Gateway Fields';
            toggleBtn.classList.remove('btn-outline-primary');
            toggleBtn.classList.add('btn-outline-secondary');

            if (document.querySelectorAll('#subscriptionFields .subscription-field-row').length === 0) {
                addSubscriptionField();
            }
        } else {
            container.style.display = 'none';
            toggleBtn.innerHTML = '<i class="fa fa-plus"></i> Add Gateway';
            toggleBtn.classList.remove('btn-outline-secondary');
            toggleBtn.classList.add('btn-outline-primary');
        }

        subscriptionFieldsVisible = !subscriptionFieldsVisible;
    }
    function addSubscriptionField(key = '', value = '') {
        const container = document.getElementById('subscriptionFields');
        const fieldRow = document.createElement('div');
        fieldRow.className = 'row subscription-field-row mb-2';
        fieldRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control subscription-key"
                       placeholder="Gateway Key (e.g., razorpay, stripe)"
                       value="${key}">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control subscription-value"
                       placeholder="Subscription ID"
                       value="${value}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-subscription-field"
                        onclick="removeSubscriptionField(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(fieldRow);
    }
    function removeSubscriptionField(button) {
        const fieldRow = button.closest('.subscription-field-row');
        fieldRow.remove();
    }
    function getSubscriptionIds() {
        const subscriptionData = {};
        const rows = document.querySelectorAll('#subscriptionFields .subscription-field-row');

        rows.forEach(row => {
            const key = row.querySelector('.subscription-key').value.trim();
            const value = row.querySelector('.subscription-value').value.trim();

            if (key && value) {
                subscriptionData[key] = value;
            }
        });

        return subscriptionData;
    }
    function clearSubscriptionFields() {
        const container = document.getElementById('subscriptionFields');
        container.innerHTML = '';

        subscriptionFieldsVisible = false;
        const fieldsContainer = document.getElementById('subscriptionFieldsContainer');
        const toggleBtn = document.querySelector('#subscriptionIdsSection .card-header button');

        fieldsContainer.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fa fa-plus"></i> Add Gateway';
        toggleBtn.classList.remove('btn-outline-secondary');
        toggleBtn.classList.add('btn-outline-primary');
    }
    function loadSubscriptionFields(subscriptionIds) {
        clearSubscriptionFields();

        if (subscriptionIds && Object.keys(subscriptionIds).length > 0) {
            for (const [key, value] of Object.entries(subscriptionIds)) {
                addSubscriptionField(key, value);
            }
            toggleSubscriptionFields();
        } else {
            addSubscriptionField();
        }
    }
    function addCondition() {
        const getVal = (selector, index = 0) => document.querySelectorAll(selector)[index]?.value || '';

        const categorySelect = document.getElementById("planDurationSelect");
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const stringId = selectedOption?.getAttribute('string-id') || '';
        const durationType = selectedOption?.getAttribute('data-duration-type') || '';
        const categoryId = selectedOption?.value || '';
        const categoryName = selectedOption?.text || '';

        const subscriptionIds = getSubscriptionIds();
        const subscriptionIdsJSON = JSON.stringify(subscriptionIds);

        const values = {
            category: categoryName,
            categoryId: categoryId,
            durationTypeVal: durationType,
            addDurationValue: getVal('input[name="additional_plan[]"]') || '0',
            inrOfferPrice: getVal('.inr-offer-price'),
            inrPrice: getVal('.inr-price'),
            usdOfferPrice: getVal('.usd-offer-price'),
            usdPrice: getVal('.usd-price'),
            subscriptionIds: subscriptionIdsJSON
        };

        if (!categoryId || !values.inrPrice || !values.inrOfferPrice || !values.usdPrice || !values.usdOfferPrice) {
            alert("Please fill all required fields!");
            return;
        }

        const subscriptionDisplay = Object.keys(subscriptionIds).length > 0
            ? `<div class="subscription-badges">${Object.entries(subscriptionIds).map(([k, v]) => `<span class="badge badge-info mr-1 mb-1" title="${v}">${k}</span>`).join('')}</div>`
            : '<span class="text-muted">No subscription IDs</span>';

        const rowHTML = `
            <td data-category-id="${values.categoryId}" data-string-id="${stringId}">
                ${categoryName} (${durationType})
            </td>
            <td>${values.addDurationValue}</td>
            <td>${values.inrPrice}</td>
            <td>${values.inrOfferPrice}</td>
            <td>${values.usdPrice}</td>
            <td>${values.usdOfferPrice}</td>
            <td class="subscription-cell">
                ${subscriptionDisplay}
                <input type="hidden" class="subscription-ids" value='${subscriptionIdsJSON}'>
            </td>
            <td>
                <button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
            </td>`;

        if (editingRow) {
            const prevCategoryId = editingRow.querySelector('td[data-category-id]')?.dataset.categoryId;
            enableDropdownOption(prevCategoryId);

            editingRow.innerHTML = rowHTML;
            editingRow = null;
            setAddButtonText("Add to Table");
        } else {
            const newRow = document.createElement('tr');
            newRow.innerHTML = rowHTML;
            document.querySelector('#conditionsTable tbody').appendChild(newRow);
        }

        selectedOption.disabled = true;
        resetInputs();
        clearSubscriptionFields();
    }
    function editRow(button) {
        editingRow = button.closest('tr');
        const row = editingRow;

        const cells = row.querySelectorAll('td');
        const categoryId = cells[0].getAttribute('data-category-id') || '';
        const subscriptionIdsField = row.querySelector('.subscription-ids');
        let subscriptionIds = {};

        if (subscriptionIdsField && subscriptionIdsField.value) {
            try {
                const rawValue = subscriptionIdsField.value.trim();
                const textarea = document.createElement('textarea');
                textarea.innerHTML = rawValue;
                const decodedValue = textarea.value;

                if (decodedValue && decodedValue !== '{}' && decodedValue !== '[]' && decodedValue !== '""') {
                    subscriptionIds = JSON.parse(decodedValue);
                }
            } catch (e) {
                console.error('Error parsing subscription IDs:', e);
                try {
                    subscriptionIds = JSON.parse(subscriptionIdsField.value);
                } catch (e2) {
                    subscriptionIds = {};
                }
            }
        }

        document.querySelector('#planDurationSelect').value = categoryId;
        document.querySelector('input[name="additional_plan[]"]').value = cells[1].textContent.trim();
        document.querySelector('.inr-price').value = cells[2].textContent.trim();
        document.querySelector('.inr-offer-price').value = cells[3].textContent.trim();
        document.querySelector('.usd-price').value = cells[4].textContent.trim();
        document.querySelector('.usd-offer-price').value = cells[5].textContent.trim();

        loadSubscriptionFields(subscriptionIds);
        setAddButtonText("Update Row");
    }
    function removeRow(button) {
        if (confirm("Are you sure you want to remove this row?")) {
            const row = button.closest('tr');
            const categoryId = row.querySelector('td[data-category-id]')?.dataset.categoryId;
            enableDropdownOption(categoryId);
            row.remove();

            if (editingRow === row) {
                resetInputs();
                clearSubscriptionFields();
                editingRow = null;
                setAddButtonText("Add to Table");
            }
        }
    }
    function resetInputs() {
        document.querySelector('#planDurationSelect').selectedIndex = 0;
        document.querySelector('input[name="additional_plan[]"]').value = '';
        document.querySelector('.inr-price').value = '';
        document.querySelector('.inr-offer-price').value = '';
        document.querySelector('.usd-price').value = '';
        document.querySelector('.usd-offer-price').value = '';
    }
    function setAddButtonText(text) {
        document.getElementById('addUpdateBtn').textContent = text;
    }
    function enableDropdownOption(categoryId) {
        if (!categoryId) return;
        const option = document.querySelector(`#planDurationSelect option[value="${categoryId}"]`);
        if (option) option.disabled = false;
    }
    function disableUsedOptions() {
        const rows = document.querySelectorAll('#conditionsTable tbody tr');
        rows.forEach(row => {
            const categoryId = row.querySelector('td[data-category-id]')?.dataset.categoryId;
            if (categoryId) {
                const option = document.querySelector(`#planDurationSelect option[value="${categoryId}"]`);
                if (option) option.disabled = true;
            }
        });
    }
    // Form submission
    $('#createPlanForm').on('submit', function(e) {
        updateFeatureOrder();
        updateCategoryOrder();
        e.preventDefault();

        const formData = new FormData(this);
        const planId = $('#string_id').val();
        const isUpdate = planId && planId.trim() !== "";

        $('.feature-checkbox:checked').each(function() {
            formData.append('features[]', $(this).val());
        });

        $('#conditionsTable tbody tr').each(function(i, row) {
            const $row = $(row);
            const cells = $row.find('td');

            formData.append(`subplans[${i}][duration_value]`, cells.eq(1).text().trim());
            formData.append(`subplans[${i}][inr_price]`, cells.eq(2).text().trim());
            formData.append(`subplans[${i}][inr_offer_price]`, cells.eq(3).text().trim());
            formData.append(`subplans[${i}][usd_price]`, cells.eq(4).text().trim());
            formData.append(`subplans[${i}][usd_offer_price]`, cells.eq(5).text().trim());
            formData.append(`subplans[${i}][duration_id]`, cells.eq(0).data('category-id'));
            formData.append(`subplans[${i}][string_id]`, cells.eq(0).data('string-id'));

            const subscriptionIds = $row.find('.subscription-ids').val();
            if (subscriptionIds) {
                try {
                    const parsedIds = JSON.parse(subscriptionIds);
                    for (const [key, value] of Object.entries(parsedIds)) {
                        formData.append(`subplans[${i}][subscription_ids][${key}]`, value);
                    }
                } catch (e) {
                    console.error('Error parsing subscription IDs:', e);
                }
            }

            const id = $row.data('subplan-id');
            if (id) {
                formData.append(`subplans[${i}][id]`, id);
            }
        });

        const url = `{{ route('plans.store') }}`;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: () => $("#loading_screen").show(),
            success: (data) => {
                $('#result').html(
                    `<div class="alert alert-${data.error ? 'danger' : 'success'}">${data.error || data.success}</div>`
                );
                $("#loading_screen").hide()
                if (!data.error) {
                    window.location.href = "{{ route('plans.index') }}";
                }
            },
            error: (err) => {
                $("#loading_screen").hide()
                alert(err.responseText);
            }
        });
    });
    function hideFields() {
        $('#createPlanForm')[0].reset();
        $("#loading_screen").hide();
    }
</script>
</body>

</html>

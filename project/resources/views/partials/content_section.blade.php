<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/styles/style.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<!-- (Route::currentRouteName() == 'create_keyword' || Route::currentRouteName() == 'edit_keyword' || Route::currentRouteName() == 'create_cat'
  || Route::currentRouteName() == 'edit_cat' || Route::currentRouteName() == 'create_new_cat' || Route::currentRouteName() == 'edit_new_cat') ? 'display: none;' : 'display: block'-->
<div class="content-div">
    <input type="hidden" name="contents" id="content" value="{{ $contents }}">
    <div class="content_type col-md-12"></div>
</div>

<button type="button" class="btn btn-dark mb-3 w-100" onclick="openContentDialog()">Add Content</button>

<div class="modal fade" id="add_content_model" tabindex="-1" aria-labelledby="add_content_model" aria-hidden="true">
    <div class="modal-dialog content-modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="button-container">
                    <button type="button" class="btn btn-dark d-block w-100 px-4 mb-2 openContentModel"
                        data-toggle="modal">Contents</button>
                    @if(Route::currentRouteName() != 'create_v_cat' && Route::currentRouteName() != 'edit_v_cat')
                        <button type="button" class="btn btn-dark w-100 px-4 mb-2 showTemplete" data-toggle="modal">
                            Show Templates
                        </button>
                    @endif
                    <!-- <button class="btn btn-dark d-block w-100 px-4 mb-2 adsModel" data-toggle="modal" data-target="#AdsModel">Show
            Ads</button> -->
                    <button type="button" class="btn btn-dark d-block w-100 px-4 add_cta_modal" data-toggle="modal"
                        id="addButton" data-target="#add_cta_modal" onclick="">CTA</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="content_modal" tabindex="-1" aria-labelledby="content_modal" aria-hidden="true">
    <div class="modal-dialog content-modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="button-container">
                    <button type="button" class="btn btn-dark content-type d-block w-100 px-4 mb-2"
                        data-type="content">Content</button>
                    <button type="button" class="btn btn-dark content-type d-block w-100 px-4 mb-2"
                        data-type="images">Image</button>
                    <button type="button" class="btn btn-dark content-type d-block w-100 px-4 mb-2"
                        data-type="video">Video</button>
                    <button type="button" class="btn btn-dark content-type d-block w-100 px-4"
                        data-type="button">Button</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contentModel" tabindex="-1" aria-hidden="true" style="height: 100%; overflow: hidden;">
    <div class="modal-dialog"
        style="height: calc(100% - 50px); overflow: hidden; margin: 0; left: 50%; top: 50%; transform: translate(-50%, -50%);">
        <div class="modal-content" style="height: 100%; overflow: hidden;">
            <div class="modal-header">
                <h4 class="modal-title text-capitalize content-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body contentType-body" style="height: 100%; overflow: hidden;">

            </div>
            <div class="modal-footer">
                <button class="btn btn-dark btn-content-save save-tag">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="AdsModel" tabindex="-1" aria-labelledby="AdsModel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-capitalize content-title">Ads</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ads_title_value" class="form-label">Title</label>
                        <input type="text" id="ads_title_value" class="form-control" placeholder="Enter Ads Title">
                        <span class="ads_title_value_error text-danger"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ads_colors" class="form-label">Color</label>
                        <div class="col-md-12">
                            <input type="text" id="ads_colors" class="form-control" />
                            <span class="ads_colors_error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ads_button_value" class="form-label">Button</label>
                        <div class="col-md-12">
                            <input type="text" id="ads_button_value" class="form-control"
                                placeholder="Enter Ads Button" />
                            <span class="ads_button_error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ads_button_link" class="form-label">Button Link</label>
                        <div class="col-md-12">
                            <input type="url" id="ads_button_link" class="form-control"
                                placeholder="Enter Ads Button Link" />
                            <span class="ads_button_link_error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="adsopenInNewTab">
                            <label class="form-check-label" for="adsopenInNewTab">Open in new tab</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="adsnofollow">
                            <label class="form-check-label" for="adsnofollow">Add rel="nofollow"</label>
                        </div>
                    </div>
                    <div class="col-md-12 mb-5">
                        <label for="ads_description" class="form-label">Description</label>
                        <div class="tinyMce" id="ads_description"></div>
                        <span class="ads_description_error text-danger"></span>
                    </div>
                    <div class="col-md-12 mb-3 mt-5">
                        <label for="image" class="form-label">Image <span class="text-danger"> (Size: 300 *
                                300)</span></label>
                        <input type="file" class="form-control ads_image" accept="image/*" id="image"
                            onchange="adspreviewImage(event)">
                        <div id="ads-image-preview-div" class="mb-3 mt-3 d-none">
                            <input type="hidden" id="ads_image_link">
                            <img class="ads-image-preview" id="ads-image-preview" width="100" height="100">
                        </div>
                        <span class="ads_image_error text-danger"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-dark btn-ads-save">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="marginModal" tabindex="-1" role="dialog" aria-labelledby="marginModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marginModalLabel">Add Margin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="margin-popup" class="margin-popup">
                    <div>
                        <label for="margin-bottom">Bottom:</label>
                        <input type="number" pattern="[0-9]" id="margin-bottom" placeholder="e.g., 20px">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="insertMargin">Apply Margins</button>
            </div>
        </div>
    </div>
</div>
@if(Route::currentRouteName() == 'create_video_virtual_cat' || Route::currentRouteName() == 'edit_video_virtual_cat')

    <div class="modal fade" id="api_virtual_modal" tabindex="-1" role="dialog" aria-labelledby="api_virtual_title"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 90%; max-height: 90vh;">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title" id="api_virtual_title">Add More Template CTA</h5>
                    <button id="closeCTAModal" name="closeCTAModal" class="close"
                        onclick="closeCTAModalManually(event,'api_virtual')" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 20px;">
                    <div class="form-group">
                        <h7>Virtual Type</h7>
                        <div class="input-group custom">
                            <select class="virtualType form-control" id="virtualType">
                                <option value="data" selected>Data</option>
                                <option value="url">Url</option>
                            </select>
                        </div>
                    </div>
                    <div id="bgMoreTemplateContainer"></div>
                    <div id="urlContainer" style="display: none;">
                        <h7>Virtual Category Link</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Enter Virtual Category Slug"
                                id="ctaMoreTemplateVirtualSlug" disabled>
                        </div>
                        <small class="text-muted">URL option is disabled for video virtual categories</small>
                    </div>
                    <div id="dataContainer" style="display: none;">
                        <div class="form-group">
                            <h7>Title</h7>
                            <div class="input-group custom">
                                <input type="text" class="form-control" placeholder="Name" id="ctaMoreTemplateTitle"
                                    maxlength="60" oninput="updateCTATitleCount(this)">
                            </div>
                            <small id="ctaTitleCounter" class="text-muted">60 remaining of 60 characters</small>
                        </div>
                        <div class="modal-body" id="ctaMoreTemplateDesc"></div>
                        {{-- <div class="form-group">
                            <div id="moreTemplateVirtualContainer">
                                @include('partials.virtual_section', ['virtualCondition' => json_encode([]), 'nameset' =>
                                '0', 'limitSet' => true])
                            </div>
                        </div> --}}
                        <div class="form-group">
                            <div id="moreTemplateVirtualContainer">
                                @php
                                    $currentRoute = Route::currentRouteName() ?? '';
                                    $currentPath = request()->path();
                                    $isVideoPage = str_contains($currentRoute, 'video') || str_contains($currentPath, 'video');
                                    $modalConfigFile = $isVideoPage ? 'videovirtualcolumns' : 'virtualcolumns';
                                @endphp
                                @include('partials.virtual_section', ['virtualCondition' => json_encode([]), 'nameset' => '0', 'limitSet' => true, 'configFile' => $modalConfigFile])
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary"
                                onclick="onMoreTemplateSubmit('More Template','api_virtual','api_virtual_modal')">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else


    <div class="modal fade" id="addTemplateKeywordModel" tabindex="-1" aria-labelledby="addTemplateKeywordModel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-capitalize content-title">Template</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" @if (
                    Route::currentRouteName() == 'create_keyword' ||
                    Route::currentRouteName() == 'edit_keyword' ||
                    Route::currentRouteName() == 'create_cat' ||
                    Route::currentRouteName() == 'edit_cat' ||
                    Route::currentRouteName() == 'create_new_cat' ||
                    Route::currentRouteName() == 'edit_new_cat'
                ) style="display: none;" @else style="display: block;" @endif>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="template_keyword" class="form-label">Keywords</label>
                            <input type="text" id="template_keyword" class="form-control"
                                placeholder="Enter Template Keywords" data-role="tagsinput">
                            <span class="template_keyword_error text-danger"></span>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="template_title" class="form-label">Title</label>
                            <input type="text" id="template_title" class="form-control" placeholder="Enter Title">
                            <span class="template_title_error text-danger"></span>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="template_desc" class="form-label">Description</label>
                            <textarea id="template_desc" class="form-control"></textarea>
                            <span class="template_desc_error text-danger"></span>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="template_only_video" class="form-label">Only Videos</label>
                            <select id="template_only_video" class="form-control">
                                <option value="0" selected>False</option>
                                <option value="1">True</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="template_link_target" class="form-label">Keywords target</label>
                            <select id="template_link_target" class="form-control">
                                <option value="loadmore_here" selected>Loadmore here</option>
                                <option value="loadmore_other_page">Loadmore other page</option>
                            </select>
                        </div>
                        <div class="keyword_link_option" style="display: none">
                            <div class="col-md-12 mb-3">
                                <label for="template_keyword_link" class="form-label">Keywords Link</label>
                                <input type="url" id="template_keyword_link" class="form-control"
                                    placeholder="Enter Keywords Link">
                                <span class="template_keyword_link_error text-danger"></span>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="templete_target">
                                    <label class="form-check-label" for="templete_target">Open in new tab</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="templete_rel">
                                    <label class="form-check-label" for="templete_rel">Add rel="nofollow"</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-dark btn-template-save" data-field-required="{{ Route::currentRouteName() == 'create_keyword' ||
            Route::currentRouteName() == 'edit_keyword' ||
            Route::currentRouteName() == 'create_cat' ||
            Route::currentRouteName() == 'edit_cat' ||
            Route::currentRouteName() == 'create_new_cat' ||
            Route::currentRouteName() == 'edit_new_cat' }}">Save</button>
                </div>
            </div>
        </div>
    </div>

@endif

@include('partials.cta_section', ['ctaSection' => $ctaSection])
<script>

    @php
        $currentRoute = Route::currentRouteName() ?? '';
        $currentPath = request()->path();
        $isVideoPage = str_contains($currentRoute, 'video') || str_contains($currentPath, 'video');
        $configFile = $isVideoPage ? 'videovirtualcolumns' : 'virtualcolumns';
    @endphp
    const virtualConfig = @json(config($configFile));
    const columns = virtualConfig.columns;
    const sorting = virtualConfig.sorting;
    const operators = virtualConfig.operators;

    // Function to update CTA Title character count
    window.updateCTATitleCount = function (input) {
        const max = 60;
        const remaining = max - input.value.length;
        const counterElement = document.getElementById('ctaTitleCounter');
        if (counterElement) {
            counterElement.textContent = remaining + ' remaining of ' + max + ' characters';
        }
    };

    // Handle Virtual Type dropdown change for video virtual categories
    @if($isVideoPage)
        $(document).ready(function () {
            // Set default state
            $('#virtualType').val('data');
            $('#urlContainer').hide();
            $('#dataContainer').show();

            // Flag to track if this is initial load or user interaction
            let isInitialLoad = true;

            // Handle virtual type change
            $('#virtualType').on('change', function () {
                const selectedType = $(this).val();

                if (selectedType === 'url') {
                    // Only show alert if this is user interaction, not initial load
                    if (!isInitialLoad) {
                        alert('URL option is not available for video virtual categories. Please use Data option.');
                    }
                    // Always switch back to data for video virtual categories
                    $(this).val('data');
                    $('#urlContainer').hide();
                    $('#dataContainer').show();
                } else {
                    $('#urlContainer').hide();
                    $('#dataContainer').show();
                }

                // After first change, mark as user interaction
                isInitialLoad = false;
            });

            // Flag to track editing mode
            let isEditingMode = false;

            // When modal is shown, reset the flags
            $('#api_virtual_modal').on('shown.bs.modal', function () {
                isInitialLoad = true;
                isEditingMode = true;
                // Ensure data is selected and containers are properly shown/hidden
                $('#virtualType').val('data');
                $('#urlContainer').hide();
                $('#dataContainer').show();

                // Initialize title character counter
                const titleInput = document.getElementById('ctaMoreTemplateTitle');
                if (titleInput && window.updateCTATitleCount) {
                    window.updateCTATitleCount(titleInput);
                }

                // Reset editing mode after a short delay
                setTimeout(function () {
                    isEditingMode = false;
                }, 1000);
            });

            // When modal is hidden, reset flags
            $('#api_virtual_modal').on('hidden.bs.modal', function () {
                isInitialLoad = true;
                isEditingMode = false;
            });

            // Update the change handler to check editing mode
            $('#virtualType').off('change').on('change', function () {
                const selectedType = $(this).val();

                if (selectedType === 'url') {
                    // Only show alert if this is user interaction, not initial load or editing
                    if (!isInitialLoad && !isEditingMode) {
                        alert('URL option is not available for video virtual categories. Please use Data option.');
                    }
                    // Always switch back to data for video virtual categories
                    $(this).val('data');
                    $('#urlContainer').hide();
                    $('#dataContainer').show();
                } else {
                    $('#urlContainer').hide();
                    $('#dataContainer').show();
                }

                // After first change, mark as user interaction
                isInitialLoad = false;
            });
        });
    @endif
</script>
@if($isVideoPage)
    <script src="{{ asset('assets/js/video_virtual.js') }}?v={{ time() }}"></script>
@else
    <script src="{{ asset('assets/js/virtual.js') }}?v={{ time() }}"></script>
@endif
<script>
    var currentRoute = "{{ Route::currentRouteName() }}";
</script>

<script src="{{ asset('assets/js/colorpicker.js') }}"></script>
<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
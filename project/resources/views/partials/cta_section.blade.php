<style>
  .common-btn {
    height: 30px;
    padding: 0 20px;
    flex: auto;
    transition: background-color 0.3s;
  }

  .common-btn:hover {
    background-color: #0056b3;
  }

  .common-btn img {
    position: absolute;
    transform: translate(40%, -50%);
    width: 1600px;
    height: auto;
    opacity: 0;
    background-color: white;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
  }

  .input-group img {
    width: 50px;
  }

  .common-btn:hover img {
    opacity: 1;
  }

  #cta_more_template_modal .modal-dialog.modal-dialog-centered {
    width: 60vw !important;
    max-width: 60vW !important;
  }

  .select2.select2-container.select2-container--default {
    width: 100% !important;
  }
</style>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<div class="modal fade" id="add_cta_modal" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header " style="display: none;">
        <h5 class="modal-title" id="cta_take_action_modal_title">Add Take Action CTA</h5>
        <button id="closeMainCTAModal" name="closeMainCTAModal" type="button" class="close closeMainCTAModal"
          data-bs-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body" style="display: flex; flex-direction: column; gap: 10px;">
        <div style="display: flex;" id="cta_take_action_btn_parent">
          <button type="button" id="cta_take_action_btn" class="btn-primary common-btn ctaOpenModalBtn"
            onclick="openCTAModalManually(event,'cta_take_action_modal')">Take Action CTA
            <img src="{{asset('assets/vendors/images/ctatakeaction.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_convert_btn_parent">
          <button type="button" id="cta_convert_btn" class="btn-primary common-btn ctaOpenModalBtn"
            onclick="openCTAModalManually(event,'cta_convert_modal')">Convert CTA
            <img src="{{asset('assets/vendors/images/ctaconvert.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_help_btn_parent">
          <button type="button" id="cta_help_btn" onclick="openCTAModalManually(event,'cta_help_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Help
            CTA
            <img src="{{asset('assets/vendors/images/ctahelp.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_general_btn_parent">
          <button type="button" id="cta_general_btn" onclick="openCTAModalManually(event,'cta_general_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">General
            CTA
            <img src="{{asset('assets/vendors/images/ctageneral.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_general_btn_parent">
          <button type="button" id="cta_general_btn" onclick="openCTAModalManually(event,'cta_more_template_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">More Templates CTA
            <img src="{{asset('assets/vendors/images/ctamoretemplates.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_scrollable_btn_parent">
          <button type="button" id="cta_scrollable_btn" onclick="openCTAModalManually(event,'cta_scrollable_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Scrollable CTA
            <img src="{{asset('assets/vendors/images/ctascrollable.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_ads_btn_parent">
          <button type="button" id="cta_ads_btn" onclick="openCTAModalManually(event,'cta_ads_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">AD CTA
            <img src="{{asset('assets/vendors/images/ctaad.webp')}}" alt="">

          </button>
        </div>
        <div style="display: flex;" id="cta_hero_btn_parent">
          <button type="button" id="cta_hero_btn" onclick="openCTAModalManually(event,'cta_hero_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Hero Section CTA
            <img src="{{asset('assets/vendors/images/ctahero.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_how_to_make_btn_parent">
          <button type="button" id="cta_how_to_make_btn" onclick="openCTAModalManually(event,'cta_how_to_make_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">How to make CTA
            <img src="{{asset('assets/vendors/images/ctaHowToMake.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_process_btn_parent">
          <button type="button" id="cta_process_btn" onclick="openCTAModalManually(event,'cta_process_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Process CTA
            <img src="{{asset('assets/vendors/images/ctaprocess.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_feature_btn_parent">
          <button type="button" id="cta_feature_btn" onclick="openCTAModalManually(event,'cta_feature_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Feature CTA
            <img src="{{asset('assets/vendors/images/ctafeature.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_suggestion_btn_parent">
          <button type="button" id="cta_suggestion_btn" class="btn-primary common-btn ctaOpenModalBtn"
            onclick="openCTAModalManually(event,'cta_suggestion_modal')">Suggestion CTA
            <img src="{{asset('assets/vendors/images/ctasuggestion.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_multiplebtn_btn_parent">
          <button type="button" id="cta_multiplebtn_btn" onclick="openCTAModalManually(event,'cta_multiplebtn_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Multiple Button CTA
            <img src="{{asset('assets/vendors/images/ctamultiplebtn.webp')}}" alt="">
          </button>
        </div>
        <div style="display: flex;" id="cta_offer_btn_parent">
          <button type="button" id="cta_offer_btn" onclick="openCTAModalManually(event,'cta_offer_modal')"
            class="btn-primary common-btn ctaOpenModalBtn">Offer CTA
            <img src="{{asset('assets/vendors/images/ctaoffer.webp')}}" alt="">
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_take_action_modal" tabindex="-1" role="dialog"
  aria-labelledby="cta_take_action_modal_title" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_take_action_modal_title">Add Take Action CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_take_action')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaTakeActionTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaTakeActionDesc"></textarea>
          </div>
        </div>
        <div id="bgTakeContainer"></div>
        <div class="form-group">
          <h7>Button</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaTakeActionBtn">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaTakeActionBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaTakeActionBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaTakeActionBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="ctaTakeActionBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaTakeActionBtnRel">Add rel="nofollow"</label>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onTakeSubmit('Take Action','cta_take_action','cta_take_action_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cta_convert_modal" tabindex="-1" role="dialog" aria-labelledby="cta_convert_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_convert_title">Add Convert CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_convert')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaConvertTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaConvertDesc"></textarea>
          </div>
        </div>
        <div id="bgConvertContainer"></div>
        <div class="form-group">
          <h7>Button</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaConvertBtn">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaConvertBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaConvertBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaConvertBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="ctaConvertBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaConvertBtnRel">Add rel="nofollow"</label>
          </div>
          <div class="form-group mt-2 input-container">
            <h7>Image Alt</h7>
            <div class="input-group custom">
              <div class="input-group custom mr-3">
                <input type="text" class="form-control" placeholder="Img Alt" id="ctaConvertImgAlt">
              </div>
              <input id="convertFile" class="dynamic-file" type="file" data-imgstore-id="convertFileBase64"
                data-accept=".jpg, .jpeg, .webp, .svg" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onConvertSubmit('Convert','cta_convert','cta_convert_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cta_suggestion_modal" tabindex="-1" role="dialog" aria-labelledby="cta_suggestion_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_suggestion_title">Add Suggestion CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" class="close"
          onclick="closeCTAModalManually(event,'cta_suggestion')" data-bs-dismiss="modal">×</button>
      </div>

      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaSuggestionTitle">
          </div>
        </div>
        <div id="bgSuggestionContainer"></div>
        <div class="form-group">
          <h7>Title Position</h7>
          <div class="input-group custom">
            <select class="selectpicker form-control" id="suggestionTitlePosition">
              <option value="1">Top</option>
              <option value="0" selected>Left</option>
            </select>
          </div>
        </div>

        <div class="modal-body" id="ctaSuggestionDesc" class="ctaSuggestionDesc">

        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onSuggestionSubmit('Suggestion','cta_suggestion','cta_suggestion_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_more_template_modal" tabindex="-1" role="dialog"
  aria-labelledby="cta_more_template_title" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_more_template_title">Add More Template CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" class="close"
          onclick="closeCTAModalManually(event,'cta_more_template')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Virtual Type</h7>
          <div class="input-group custom">
            <select class="virtualType form-control" id="virtualType">
              <option value="url">Url</option>
              <option value="data">Data</option>
            </select>
          </div>
        </div>
        <div id="bgMoreTemplateContainer"></div>
        <div id="urlContainer">
          <h7>Virtual Category Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Enter Virtual Category Slug"
              id="ctaMoreTemplateVirtualSlug">
          </div>
        </div>
        <div id="dataContainer" style="display: none;">
          <div class="form-group">
            <h7>Title</h7>
            <div class="input-group custom">
              <input type="text" class="form-control" placeholder="Name" id="ctaMoreTemplateTitle">
            </div>
          </div>
          <div class="modal-body" id="ctaMoreTemplateDesc"></div>
          <div class="form-group">
            <div id="moreTemplateVirtualContainer">
              @php
                // Force use videovirtualcolumns for video pages
                $configFile = 'videovirtualcolumns';
                $currentRoute = Route::currentRouteName() ?? '';
                $currentPath = request()->path();
                $isVideoPage = str_contains($currentRoute, 'video') || str_contains($currentPath, 'video');
                // Debug info
                error_log("CTA Modal - Route: {$currentRoute}, Path: {$currentPath}, IsVideo: " . ($isVideoPage ? 'YES' : 'NO') . ", Config: {$configFile}");
              @endphp
              <!-- DEBUG: Route={{$currentRoute}}, Path={{$currentPath}}, IsVideo={{$isVideoPage ? 'YES' : 'NO'}}, Config={{$configFile}} -->
              @include('partials.virtual_section', [
                'virtualCondition' => json_encode([]),
                'nameset' => '0',
                'limitSet' => true,
                'configFile' => $configFile
              ])
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            @if(Route::currentRouteName() == 'create_video_virtual_cat' || Route::currentRouteName() == 'edit_video_virtual_cat')
            <button type="button" class="btn btn-primary"
              onclick="onMoreTemplateSubmit('More Template','api_virtual','cta_more_template_modal')">Submit</button>
            @else
            <button type="button" class="btn btn-primary"
              onclick="onMoreTemplateSubmit('More Template','cta_more_template','cta_more_template_modal')">Submit</button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_scrollable_modal" tabindex="-1" role="dialog" aria-labelledby="cta_scrollable_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_scrollable_title">Add Scrollable CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" class="close"
          onclick="closeCTAModalManually(event,'cta_scrollable')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaScrollableTitle">
          </div>
        </div>
        <div class="modal-body" id="ctaScrollableDesc"></div>

        <div id="bgScrollableContainer"></div>

        <div class="form-group">
          <h7>Button</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaScrollableBtn">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaScrollableBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaScrollableBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaScrollableBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check mb-3">
            <input type="checkbox" id="ctaScrollableBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaScrollableBtnRel">Add rel="nofollow"</label>
          </div>
          <div class="form-group">
            <div id="scrollableImageContainer" class="form-group">
            </div>
            <div style="text-align: right;">
              <button type="button" class="btn btn-secondary" id="addImageButton" onclick="addImageField()">Add
                Image</button>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary"
                onclick="onScrollableSubmit('Scrollable','cta_scrollable','cta_scrollable_modal')">Submit</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_help_modal" tabindex="-1" role="dialog" aria-labelledby="cta_help_modal_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_help_modal_title">Add Help CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_help')" data-bs-dismiss="modal">×</button>
      </div>

      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaHelpTitle">
          </div>
        </div>

        <div class="form-group">
          <h7>Description</h7>
          <div class="modal-body" id="ctaHelpDesc" class="ctaHelpDesc"></div>
        </div>

        <div id="bgHelpContainer"></div>

        <div class="form-group">
          <h7>Info</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaHelpInfo">
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onHelpSubmit('Help','cta_help','cta_help_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_general_modal" tabindex="-1" role="dialog" aria-labelledby="cta_general_modal_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_general_modal_title">Add General CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_general')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Title" id="ctaGeneralTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" class="form-control" id="ctaGeneralDesc"></textarea>
          </div>
        </div>
        <div id="bgGeneralContainer"></div>
        <div class="form-group">
          <h7>Button Name</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaGeneralBtnName">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaGeneralBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaGeneralBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaGeneralBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="ctaGeneralBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaGeneralBtnRel">Add rel="nofollow"</label>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onGeneralSubmit('General','cta_general','cta_general_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_hero_modal" tabindex="-1" role="dialog" aria-labelledby="cta_hero_modal_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_hero_title">Add Hero CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_hero')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaHeroTitle">
          </div>
        </div>
        <div id="bgHeroContainer"></div>
        <div class="form-group">
          <h7>Button</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaHeroBtn">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaHeroBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaHeroBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaHeroBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="ctaHeroBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaHeroBtnRel">Add rel="nofollow"</label>
          </div>
          <div class="form-group mt-2 input-container">
            <h7>Image Alt</h7>
            <div class="input-group custom">
              <div class="input-group custom mr-3">
                <input type="text" class="form-control" placeholder="Img Alt" id="ctaHeroImgAlt">
              </div>
              <input id="heroFile" class="dynamic-file" data-imgstore-id="heroFileBase64" type="file"
                data-accept=".jpg, .jpeg, .webp, .svg" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onHeroSubmit('Hero','cta_hero','cta_hero_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_ads_modal" tabindex="-1" role="dialog" aria-labelledby="cta_ads_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_ads_title">Add Ads CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_ads')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaAdTitle">
          </div>
        </div>
        <div id="bgAdsContainer"></div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaAdDesc"></textarea>
          </div>
        </div>
        <div class="form-group mt-2 input-container">
          <h7>Button Image Alt</h7>
          <div class="input-group custom">
            <div class="input-group custom mr-3">
              <input type="text" class="form-control" placeholder="Button Img Alt" id="ctaAdBtnImgAlt">
            </div>
            <input id="ctaAdBtnFile" class="dynamic-file" data-imgstore-id="adBtnFileBase64" type="file"
              data-accept=".jpg, .jpeg, .webp, .svg" />
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Link" id="ctaAdBtnLink">
          </div>
        </div>
        <div class="form-group">
          <div class="form-check mt-4 mb-2">
            <input type="checkbox" id="ctaAdBtnTarget" class="form-check-input">
            <label class="form-check-label" for="ctaAdBtnTarget">Open in new tab</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="ctaAdBtnRel" class="form-check-input">
            <label class="form-check-label" for="ctaAdBtnRel">Add rel="nofollow"</label>
          </div>
          <hr />
          <h3>Info Image</h3>
          <div class="form-group mt-2 input-container">
            <h7>Image Alt</h7>
            <div class="input-group custom">
              <div class="input-group custom mr-3">
                <input type="text" class="form-control" placeholder="Img Alt" id="ctaAdImgAlt">
              </div>
              <input id="adFile" class="dynamic-file" data-imgstore-id="adFileBase64" type="file"
                data-accept=".jpg, .jpeg, .webp, .svg" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onAdSubmit('Ads','cta_ads','cta_ads_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cta_how_to_make_modal" tabindex="-1" role="dialog" aria-labelledby="cta_how_to_make_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_how_to_make_title">Add How to Make CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_how_to_make')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaHowToMakeTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaHowToMakeDesc"></textarea>
          </div>
        </div>
        <div id="bgHowToMakeContainer"></div>
        <div class="form-group">
          <div id="ctaHowToMakeContainer" class="form-group">
          </div>
          <div style="text-align: right;;margin-top: 50px;">
            <button type="button" class="btn btn-secondary" id="addSubHowToMakeButton"
              onclick="addImageFieldWithDetails('ctaHowToMakeContainer')">Add Field</button>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onHowToMakeSubmit('How to make','cta_how_to_make','cta_how_to_make_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_process_modal" tabindex="-1" role="dialog" aria-labelledby="cta_process_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_process_title">Add Process CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_process')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaProcessTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaProcessDesc"></textarea>
          </div>
        </div>
        <div id="bgProcessContainer"></div>
        <div class="form-group">
          <h7>Image Position</h7>
          <div class="input-group custom">
            <select class="selectpicker form-control" id="processImgPosition">
              <option value="1" selected>Right</option>
              <option value="0">Left</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <h7>Step Position</h7>
          <div class="input-group custom">
            <select class="selectpicker form-control" id="processStepPosition">
              <option value="1" selected>Horizontal</option>
              <option value="0">Vertical</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <div id="ctaProcessContainer" class="form-group">
          </div>
          <div style="text-align: right;;margin-top: 50px;">
            <button type="button" class="btn btn-secondary" id="addSubProcessButton"
              onclick="addImageFieldWithDetails('ctaProcessContainer')">Add
              Field</button>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onProcessSubmit('Process','cta_process','cta_process_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_feature_modal" tabindex="-1" aria-labelledby="cta_feature_title" style="display: none;"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_feature_title">Add Feature CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_feature')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaFeatureTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaFeatureDesc"></textarea>
          </div>
        </div>
        <div id="bgFeatureContainer"></div>
        <div class="form-group">
          <h7>Image Position</h7>
          <div class="input-group custom">
            <select class="selectpicker form-control" id="featureImgPosition">
              <option value="1" selected>Right</option>
              <option value="0">Left</option>
            </select>
          </div>
        </div>
        <h6>Info Image</h6>
        <div class="form-group mt-2 ">
          <h7>Image Alt</h7>
          <div class="input-group custom input-container">
            <div class="input-group custom mr-3">
              <input type="text" class="form-control" placeholder="Img Alt" id="ctaFeatureInfoImgAlt">
            </div>
            <input id="featureInfoFile" class="dynamic-file" data-imgstore-id="featureInfoFileBase64" type="file"
              data-accept=".jpg, .jpeg, .webp, .svg" />
          </div>
        </div>
        <hr>
        <div class="form-group">
          <div id="ctaFeatureContainer" class="form-group">
          </div>
          <div style="text-align: right;">
            <button type="button" class="btn btn-secondary" id="addSubFeatureButton"
              onclick="addButtonField('ctaFeatureContainer')">Add Field</button>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onFeatureSubmit('Feature','cta_feature','cta_feature_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_offer_modal" tabindex="-1" aria-labelledby="cta_offer_title" style="display: none;"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_feature_title">Add Offer CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_offer')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaOfferTitle">
          </div>
          </diiv>
          <div class="form-group">
            <h7>Offer</h7>
            <div class="input-group custom">
              <input type="text" class="form-control" placeholder="Name" id="ctaOfferDesc">
            </div>
          </div>
          <div id="bgOfferContainer"></div>
          <div class="form-group">
            <h7>Button</h7>
            <div class="input-group custom">
              <input type="text" class="form-control" placeholder="Name" id="ctaOfferBtn">
            </div>
          </div>
          <div class="form-group">
            <h7>Button Link</h7>
            <div class="input-group custom">
              <input type="text" class="form-control" placeholder="Link" id="ctaOfferBtnLink">
            </div>
          </div>
          <div class="form-group">
            <div class="form-check mt-4 mb-2">
              <input type="checkbox" id="ctaOfferBtnTarget" class="form-check-input">
              <label class="form-check-label" for="ctaOfferBtnTarget">Open in new tab</label>
            </div>
            <div class="form-check">
              <input type="checkbox" id="ctaOfferBtnRel" class="form-check-input">
              <label class="form-check-label" for="ctaOfferBtnRel">Add rel="nofollow"</label>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <button type="button" class="btn btn-primary"
                  onclick="onOfferSubmit('Offer','cta_offer','cta_offer_modal')">Submit</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="cta_multiplebtn_modal" tabindex="-1" role="dialog" aria-labelledby="cta_multiplebtn_title"
  style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cta_multiplebtn_title">Add Multiple Button CTA</h5>
        <button id="closeCTAModal" name="closeCTAModal" type="button" class="close closeCTAModal"
          onclick="closeCTAModalManually(event,'cta_multiplebtn')" data-bs-dismiss="modal">×</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="form-group">
          <h7>Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control" placeholder="Name" id="ctaMultipleBtnTitle">
          </div>
        </div>
        <div class="form-group">
          <h7>Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control" id="ctaMultipleBtnDesc"></textarea>
          </div>
        </div>
        <div id="bgMultipleBtnContainer"></div>
        <div class="form-group">
          <h7>Image Position</h7>
          <div class="input-group custom">
            <select class="selectpicker" id="multipleBtnImgPosition">
              <option value="1" class="input-group custom">Right</option>
              <option value="0" class="input-group custom" selected>Left</option>
            </select>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <div id="ctaMultipleBtnContainer" class="form-group">
          </div>
          <div style="text-align: right;">
            <button type="button" class="btn btn-secondary" id="addSubMultipleBtnButton"
              onclick="addButtonFieldWithImage('ctaMultipleBtnContainer')">Add Field</button>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button type="button" class="btn btn-primary"
              onclick="onMultipleBtnSubmit('Multiple Button','cta_multiplebtn','cta_multiplebtn_modal')">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<!-- Add this before your script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

</script>

<script type="text/javascript">
  var ctaSection = @json($ctaSection);
  var editingCTAElement = null;
  var editedContent = null;
  var selectedID = null;
  $(document).on('click', closeCTAModal, () => {
    $(".ctaModal").modal('hide');
  })

  function addImageFieldClick(e) {
    e.preventDefault();
  }

  function createBackgroundView(prefix, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
    <div class="form-group">
      <h7>Background Position</h7>
      <div class="input-group custom">
        <select class="selectpicker form-control" id="bgType${prefix}">
          <option value="0" selected>Default</option>
          <option value="1">Color</option>
          <option value="2">Image</option>
        </select>
      </div>
    </div>

    <div class="form-group" id="bgColor${prefix}Container" style="display:none">
      <h7>Color</h7>
      <div class="input-group custom">
        <input type="color" class="form-control" id="cta${prefix}BgColor">
      </div>
    </div>

    <div class="form-group mt-2 input-container" id="bgImage${prefix}Container" style="display:none">
          <h7>Bg Image Alt</h7>
          <div class="input-group custom">
            <div class="input-group custom mr-3">
              <input type="text" class="form-control" placeholder="Bg Img Alt" id="cta${prefix}BgImgAlt">
            </div>
            <input id="bg${prefix}File" class="dynamic-file" type="file" data-imgstore-id="bg${prefix}FileBase64"
              data-accept=".jpg, .jpeg, .webp, .svg" />
          </div>
    </div>
  `;
    // <img id="bg${prefix}CTAImg" name="bg${prefix}CTAImg" width="100px" class="img-thumbnail" alt="">
    //           // <input type="hidden" id="bg${prefix}FileBase64" class="bgTakeFileBase64" />
    initializeBackgroundSelection(prefix)
  }

  document.getElementById('virtualType').addEventListener('change', (event) => {
    const selectedValue = event.target.value;
    const urlContainer = document.getElementById('urlContainer')
    const dataContainer = document.getElementById('dataContainer')
    urlContainer.style.display = selectedValue == 'url' ? 'block' : 'none';
    dataContainer.style.display = selectedValue == 'url' ? 'none' : 'block';
  })

  function changeVirtualContainer() {

  }

  function initializeBackgroundSelection(prefix) {
    const bgType = document.getElementById(`bgType${prefix}`);
    const colorContainer = document.getElementById(`bgColor${prefix}Container`);
    const imageContainer = document.getElementById(`bgImage${prefix}Container`);

    if (!bgType) return;

    bgType.addEventListener('change', (event) => {
      let selectedValue = event.target.value;

      if (selectedValue === '0') {
        colorContainer.style.display = 'none';
        imageContainer.style.display = 'none';
      } else if (selectedValue === '1') {
        colorContainer.style.display = 'block';
        imageContainer.style.display = 'none';
      } else if (selectedValue === '2') {
        colorContainer.style.display = 'none';
        imageContainer.style.display = 'block';
      }
    });
  }

  function addImageField(base64String = null, imgAlt = null, target = null, rel = null, imgLink = null) {
    const container = document.getElementById("scrollableImageContainer");
    const existingFields = container.querySelectorAll(".custom-image");
    // Check if previous fields are filled
    for (let field of existingFields) {
      // const base64Input = field.querySelector("input.scrollableFileBase64");
      const base64Input = field.querySelector("#scrollableFileBase64");
      const imageAlt = field.querySelector("input.ctaScrollableImgAlt");
      const imglinkInput = field.querySelector("input.ctaScrollableImglink");
      if (
        !imageAlt?.value.trim() ||
        !imglinkInput.value.trim() ||
        !base64Input?.src
      ) {
        alert("Please fill previous img alt field and upload images before adding a new field.");
        return;
      }

      if (!isValidUrl(imglinkInput.value.trim())) {
        alert("Please add valid url");
        return;
      }
    }
    const newField = document.createElement("div");
    newField.className = "form-group row ml-1 mt-2"; // Flexbox parent container for row alignment
    newField.innerHTML = `
    <div class="input-container custom-image"> 
      <div style="flex-grow: 1; display: flex;">
        <input type="file" class="scrollableFile dynamic-file" data-accept=".jpg, .jpeg, .webp, .svg" data-setclass="true" data-imgstore-id="scrollableFileBase64" style="flex-grow: 1;" />
        <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" onclick="removeImageField(this)">
          <i class="dw dw-delete-3"></i>
        </button>
      </div>
      <div class="form-group mt-2">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control ctaScrollableImglink" placeholder="Link" >
          </div>
        </div>
      <div class="form-group mt-2">
        <h7>Image Alt</h7>
        <div class="input-group custom">
          <input type="text" class="form-control ctaScrollableImgAlt" placeholder="Img Alt">
        </div>
      </div> 
      <div class="form-check mt-4 mb-2">
        <input type="checkbox" class="form-check-input ctaScrollableTarget" >
        <label class="form-check-label" for="ctaScrollableTarget">Open in new tab</label>
      </div>
      <div class="form-check">
        <input type="checkbox" class="form-check-input ctaScrollableRel">
        <label class="form-check-label" for="ctaScrollableRel">Add rel="nofollow"</label>
      </div>  
    </div>`;
    container.appendChild(newField);

    if (base64String) {
      const imgElement = newField.querySelector("img");
      // const base64Input = newField.querySelector("input.scrollableFileBase64");
      const dynamicFileInput = newField.querySelector("input.scrollableFile");
      dynamicFileInput.setAttribute("data-value", getStorageLink(base64String))

      const imgAltInput = newField.querySelector("input.ctaScrollableImgAlt");
      const imglinkInput = newField.querySelector("input.ctaScrollableImglink");
      const targetInput = newField.querySelector("input.ctaScrollableTarget");
      const relInput = newField.querySelector("input.ctaScrollableRel");

      imgElement.src = getStorageLink(base64String);
      // base64Input.value = base64String;
      imgAltInput.value = imgAlt;
      imglinkInput.value = imgLink;
      targetInput.checked = target === 1;
      relInput.checked = rel === 1;
    }
    dynamicFileCmp();
  }

  function addButtonField(id, btnName = null, btnLink = null, target = null, rel = null) {
    const container = document.getElementById(id);
    const existingFields = container.querySelectorAll(".input-container");
    for (let field of existingFields) {
      const subtitleInput = field.querySelector("#ctaBtnName");
      const ctaBtnLink = field.querySelector("#ctaBtnLink");
      if (
        !subtitleInput.value.trim() ||
        !ctaBtnLink.value.trim()
      ) {
        alert("Please fill in all previous field before adding a new field.");
        return;
      }
      if (!isValidUrl(ctaBtnLink.value.trim())) {
        alert("Please add valid url");
        return;
      }
    }
    const newField = document.createElement("div");
    newField.innerHTML = `
    <div class="input-container">
        <div class="form-group">
          <h7>Button</h7>
          
          <div class="input-group custom">
            <input type="text" class="form-control ctaBtnName" placeholder="Name" id="ctaBtnName">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control ctaBtnLink" placeholder="Link" id="ctaBtnLink">
          </div>
        </div>

        <div class="form-group">
          <div class="form-check mt-2 mb-2">
            <input type="checkbox" id="ctaBtnTarget"  class="form-check-input ctaBtnTarget">
            <label class="form-check-label" for="ctaBtnTarget">Open in new tab</label>
          </div>

          <div class="form-check">
            <input type="checkbox" id="ctaBtnRel" class="ctaBtnRel form-check-input">
            <div style="flex-grow: 1; display: flex;justify-content:space-between">
            <label class="form-check-label" for="ctaBtnRel">Add rel="nofollow"</label>
            <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" onclick="removeImageField(this)">
          <i class="dw dw-delete-3"></i>
        </button>
        </div>
        </div>
    </div>
  `;

    container.appendChild(newField);
    if (btnName) {
      const ctaBtnName = newField.querySelector("#ctaBtnName");
      const ctaBtnLink = newField.querySelector("#ctaBtnLink");
      const ctaBtnTarget = newField.querySelector("#ctaBtnTarget");
      const ctaBtnRel = newField.querySelector("#ctaBtnRel");

      ctaBtnName.value = btnName;
      ctaBtnLink.value = btnLink;
      ctaBtnTarget.checked = target === 1;
      ctaBtnRel.checked = rel === 1;
    }
  }

  function addButtonFieldWithImage(id, btnName = null, btnLink = null, target = null, rel = null, base64String =
    null,
    imgAlt = null) {
    const container = document.getElementById(id);
    const existingFields = container.querySelectorAll(".input-container");
    for (let field of existingFields) {
      const subtitleInput = field.querySelector("#ctaBtnName");
      const ctaBtnLink = field.querySelector("#ctaBtnLink");
      const base64Input = field.querySelector("#subFileBase64");
      const imageAlt = field.querySelector("#ctaSubImgAlt");
      if (
        !subtitleInput.value.trim() ||
        !ctaBtnLink.value.trim() ||
        !imageAlt.value.trim() ||
        !base64Input.src
      ) {
        alert("Please fill in all previous field before adding a new field.");
        return;
      }
      if (!isValidUrl(ctaBtnLink.value.trim())) {
        alert("Please add valid url");
        return;
      }
    }
    const newField = document.createElement("div");
    newField.innerHTML = `
    <div class="input-container">
      <div style="flex-grow: 1; display: flex;">
        <input type="file" class="subFile dynamic-file" data-accept=".svg" data-setclass="true" data-imgstore-id="subFileBase64" style="flex-grow: 1;" />
        <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" onclick="removeImageField(this)">
          <i class="dw dw-delete-3"></i>
        </button>
      </div>
      <div class="form-group">
        <h7>Image Alt</h7>
        <div class="input-group custom mr-3">
            <input type="text" class="form-control ctaSubImgAlt" placeholder="Img Alt" id="ctaSubImgAlt">
        </div>
      </div>
        <div class="form-group">
          <h7>Button</h7>
          
          <div class="input-group custom">
            <input type="text" class="form-control ctaBtnName" placeholder="Name" id="ctaBtnName">
          </div>
        </div>
        <div class="form-group">
          <h7>Button Link</h7>
          <div class="input-group custom">
            <input type="text" class="form-control ctaBtnLink" placeholder="Link" id="ctaBtnLink">
          </div>
        </div>

        <div class="form-group">
          <div class="form-check mt-2 mb-2">
            <input type="checkbox" id="ctaBtnTarget" class="ctaBtnTarget form-check-input">
            <label class="form-check-label" for="ctaBtnTarget">Open in new tab</label>
          </div>

          <div class="form-check">
            <input type="checkbox" id="ctaBtnRel" class="ctaBtnRel form-check-input">
            <div style="flex-grow: 1; display: flex;justify-content:space-between">
            <label class="form-check-label" for="ctaBtnRel">Add rel="nofollow"</label>
          </div>
        </div>
    </div>
  `;

    container.appendChild(newField);

    if (base64String) {
      // const imgElement = newField.querySelector("img");
      // const base64Input = newField.querySelector("input.subFileBase64");
      // imgElement.src = getStorageLink(base64String);
      // base64Input.value = base64String;

      // const imgElement = newField.querySelector("#subFileBase64");
      // imgElement.src = base64String;
      const dynamicFileInput = newField.querySelector("input.subFile");
      dynamicFileInput.setAttribute("data-value", getStorageLink(base64String))

    }
    if (imgAlt) {
      const subtitleInput = newField.querySelector("#ctaSubImgAlt");
      subtitleInput.value = imgAlt;
    }
    if (btnName) {

      const ctaBtnName = newField.querySelector("#ctaBtnName");
      const ctaBtnLink = newField.querySelector("#ctaBtnLink");
      const ctaBtnTarget = newField.querySelector("#ctaBtnTarget");
      const ctaBtnRel = newField.querySelector("#ctaBtnRel");

      ctaBtnName.value = btnName;
      ctaBtnLink.value = btnLink;
      ctaBtnTarget.checked = target === 1;
      ctaBtnRel.checked = rel === 1;
    }
    dynamicFileCmp();
  }

  function addImageFieldWithDetails(id, base64String = null, subTitle = null, subDesc = null, imgAlt = null) {
    const container = document.getElementById(id);
    const existingFields = container.querySelectorAll(".input-container");

    // Validate each existing field
    let count = 0;
    for (let field of existingFields) {
      const subtitleInput = field.querySelector("#ctaSubTitle");
      const subDescInput = id == 'ctaHowToMakeContainer' ? ctaHowToMakeQuill[count].root.innerHTML : ctaProcessQuill[
        count].root.innerHTML;
      const base64Input = field.querySelector("#subFileBase64");

      const imageAlt = field.querySelector("#ctaSubImgAlt");

      if (
        !subtitleInput.value.trim() ||
        subDescInput == '' ||
        !imageAlt.value.trim() ||
        !base64Input.src
      ) {
        alert("Please fill in all previous subtitles, descriptions, and upload images before adding a new field.");
        return;
      }
      count++;
    }

    // Create the new field structure
    const newField = document.createElement("div");
    newField.className = "form-group row ml-1 mt-2";
    newField.innerHTML = `
    <div class="input-container">
      <div class="form-group">
          <h7>Sub Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control ctaSubTitle" placeholder="Name" id="ctaSubTitle">
          </div>
      </div>
      <div class="modal-body" id="ctaSubDesc${count}"></div>

      <div class="form-group">
        <h7>Image Alt</h7>
        <div class="input-group custom mr-3">
            <input type="text" class="form-control ctaSubImgAlt" placeholder="Img Alt" id="ctaSubImgAlt">
        </div>
      </div>

      <div style="flex-grow: 1; display: flex;margin-top:50px">
        <input type="file" class="subFile dynamic-file" data-accept=".jpg, .jpeg, .webp, .svg"
          data-imgstore-id="subFileBase64" data-setclass=true style="flex-grow: 1;" />
        <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" 
          onclick="removeSubField(this)">
          <i class="dw dw-delete-3"></i>
        </button>
      </div>
    </div>`;

    container.appendChild(newField);

    $(`#ctaSubDesc${count}`).css('max-height', '120px');
    $(`#ctaSubDesc${count}`).css('overflow-x', 'scroll');
    if (id == 'ctaHowToMakeContainer') {
      ctaHowToMakeQuill[count] = registerCTAQuillForProcess(`#ctaSubDesc${count}`, 'cta_how_to_make_modal')
      handleEditorEvents(ctaHowToMakeQuill[count])
    } else {
      ctaProcessQuill[count] = registerCTAQuillForProcess(`#ctaSubDesc${count}`, 'cta_process_modal')
      handleEditorEvents(ctaProcessQuill[count])
    }
    if (base64String) {
      // const imgElement = newField.querySelector("img");
      // const base64Input = newField.querySelector("input.subFileBase64");
      // imgElement.src = getStorageLink(base64String);
      // base64Input.value = base64String;

      const dynamicFileInput = newField.querySelector("input.subFile");
      dynamicFileInput.setAttribute("data-value", getStorageLink(base64String))

      // imgElement.src = base64String;
    }

    if (subTitle) {
      const subtitleInput = newField.querySelector("#ctaSubTitle");
      subtitleInput.value = subTitle;
    }

    if (imgAlt) {
      const subtitleInput = newField.querySelector("#ctaSubImgAlt");
      subtitleInput.value = imgAlt;
    }

    if (subDesc) {
      if (id == 'ctaHowToMakeContainer') {
        ctaHowToMakeQuill[count].root.innerHTML = subDesc
      } else {
        ctaProcessQuill[count].root.innerHTML = subDesc
      }
    }
    dynamicFileCmp();
  }

  function addImageFieldWithDetails2(id, base64String = null, subTitle = null, subDesc = null, imgAlt = null) {
    const container = document.getElementById(id);
    const existingFields = container.querySelectorAll(".input-container");
    // Validate each existing field
    if (!base64String && !subTitle && !subDesc && !imgAlt) {
      for (let field of existingFields) {
        const subtitleInput = field.querySelector("#ctaSubTitle");
        const subDescInput = field.querySelector("#ctaSubDesc");
        const base64Input = field.querySelector("input.subFileBase64");
        const imageAlt = field.querySelector("#ctaSubImgAlt");

        if (
          !subtitleInput.value.trim() ||
          !subDescInput.value.trim() ||
          !imageAlt.value.trim() ||
          !base64Input.value.trim()
        ) {
          alert("Please fill in all previous subtitles, descriptions, and upload images before adding a new field.");
          return;
        }
      }
    }

    // Create the new field structure
    const newField = document.createElement("div");
    newField.className = "form-group row ml-1 mt-2";
    newField.innerHTML = `
    <div class="input-container">
      <div class="form-group">
          <h7>Sub Title</h7>
          <div class="input-group custom">
            <input type="text" class="form-control ctaSubTitle" placeholder="Name" id="ctaSubTitle">
          </div>
      </div>
      <div class="form-group">
          <h7>Sub Description</h7>
          <div class="input-group custom">
            <textarea style="height: 120px" rows="4" class="form-control ctaSubDesc" id="ctaSubDesc"></textarea> 
          </div>
      </div>  
      <div class="form-group">
      <h7>Image Alt</h7>
        <div class="input-group custom mr-3">
            <input type="text" class="form-control ctaSubImgAlt" placeholder="Img Alt" id="ctaSubImgAlt">
        </div>
        </div>  
      <div style="flex-grow: 1; display: flex;">
        <input type="file" class="subFile" accept=".jpg, .jpeg, .webp, .svg"
          onchange="loadBase64Image(this,'input.subFileBase64')" style="flex-grow: 1;" />
        <img width="80px" class="img-thumbnail" alt="" />
        <input type="hidden" class="subFileBase64" />
        <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" 
          onclick="removeSubField(this)">
          <i class="dw dw-delete-3"></i>
        </button>
      </div>
    </div>
  `;

    container.appendChild(newField);
    if (base64String) {
      const imgElement = newField.querySelector("img");
      const base64Input = newField.querySelector("input.subFileBase64");
      imgElement.src = getStorageLink(base64String);
      base64Input.value = base64String;
    }

    if (subTitle) {
      const subtitleInput = newField.querySelector("#ctaSubTitle");
      subtitleInput.value = subTitle;
    }

    if (imgAlt) {
      const subtitleInput = newField.querySelector("#ctaSubImgAlt");
      subtitleInput.value = imgAlt;
    }

    if (subDesc) {
      const subDescInput = newField.querySelector("#ctaSubDesc");
      subDescInput.value = subDesc;
    }
  }

  function registerCTAQuillForProcess(selector, id) {
    const toolbar = [
      ['undo', 'redo'],
      ['bold', 'italic', 'underline', 'strike'],
      [{
        'header': [2, 3, 4, 5, 6, false]
      }],
      [{
        'script': 'super'
      }, {
        'script': 'sub'
      }],
      [{
        'list': 'ordered'
      }, {
        'list': 'bullet'
      }, {
        'indent': '-1'
      }, {
        'indent': '+1'
      }],
      ['direction', {
        'align': []
      }],
      [{
        'color': []
      }],
      ["custom"],
      ['link', 'clean']
    ];

    function initQuill(selector) {
      return new Quill(selector, {
        theme: 'snow',
        modules: {
          toolbar: {
            container: toolbar,
            handlers: {
              'link': openLinkModal,
              'custom': function (value) {
                colorPickerCustomQuill(value, this.quill, id)
              },
            }
          },
          history: {
            userOnly: false,
            maxStack: 500
          },
          clipboard: {
            matchVisual: false
          }
        }
      });
    }

    function openLinkModal(value) {
      if (!value) return;
      $('.linkUrlError').text('');
      $('#linkModal').modal('show');
      $('#linkUrl').val('');
      $('#openInNewTab, #nofollow').prop('checked', false);
      document.getElementById('insertLink').onclick = () => insertLink(this.quill);
    }
    return initQuill(selector)
  }

  function registerCTAQuill() {
    function applyMarginOnNewLine(quill) {
      quill.on('text-change', (delta) => {
        delta.ops.forEach(op => {
          if (op.insert === '\n') {
            const range = quill.getSelection();
            const [parentBlock] = quill.scroll.descendant(Quill.import('blots/block'), range.index - 1);
            if (parentBlock && !parentBlock.domNode.style.marginBottom) {
              parentBlock.domNode.style.marginBottom = '16px';
            }
          }
        });
      });
    }
    if (!ctaSuggestionQuill) {
      ctaSuggestionQuill = registerCTAQuillForProcess('#ctaSuggestionDesc', "cta_suggestion_modal");
      applyMarginOnNewLine(ctaSuggestionQuill);
      handleEditorEvents(ctaSuggestionQuill);
    }
    if (!ctaHelpQuill) {
      ctaHelpQuill = registerCTAQuillForProcess('#ctaHelpDesc', "cta_help_modal");
      applyMarginOnNewLine(ctaHelpQuill);
      handleEditorEvents(ctaHelpQuill);
    }

    if (!ctaScrollableQuill) {
      ctaScrollableQuill = registerCTAQuillForProcess('#ctaScrollableDesc', "cta_scrollable_modal");
      applyMarginOnNewLine(ctaScrollableQuill);
      handleEditorEvents(ctaScrollableQuill);
    }
    if (!ctaMoreTemplateQuill) {
      ctaMoreTemplateQuill = registerCTAQuillForProcess('#ctaMoreTemplateDesc', "cta_more_template_modal");
      applyMarginOnNewLine(ctaMoreTemplateQuill);
      handleEditorEvents(ctaMoreTemplateQuill);
    }


  }

  function handleEditorEvents(selectedQuill) {
    selectedQuill.on('editor-change', function (eventName, ...args) {
      if (eventName === 'text-change') {
        handlePasteEvent(selectedQuill);
      }
    });

    function handlePasteEvent(selectedQuill) {
      selectedQuill.clipboard.addMatcher(Node.TEXT_NODE, function (node, delta) {
        const pastedText = node.data;
        const currentFormat = selectedQuill.getFormat();
        const headerRegex = /<h([2-6])>/g;
        if (pastedText.match(headerRegex)) {
          const match = headerRegex.exec(pastedText);
          const headerLevel = match[1];
          const desiredFormat = currentFormat;
          delta.ops.forEach(op => {
            op.attributes = desiredFormat;
          });
        } else {
          let contentType;
          let styleLevel;
          let tool = {};

          switch (type) {
            case "p":
            case "content":
              contentType = "header";
              styleLevel = null;
              break;
            case "bullets":
              contentType = "list";
              styleLevel = "bullet";
              tool = {
                list: "bullet"
              };
              break;
            case "numbers":
              contentType = "list";
              styleLevel = "ordered";
              tool = {
                list: "ordered"
              };
              break;
            case "checklists":
              contentType = "list";
              styleLevel = checkbox ? "checked" : "unchecked";
              tool = {
                list: "check"
              };
              break;
          }
          if (contentType === "header") {
            delta.ops.forEach(op => {
              op.attributes = currentFormat;
            });
          }
        }

        return delta;
      });
    }

    if (selectedQuill) {
      selectedQuill.root.addEventListener('paste', function (event) {
        event.preventDefault(); // Prevent default paste behavior

        var pastedText = (event.originalEvent || event).clipboardData.getData('text/plain');

        if (pastedText) {
          var range = selectedQuill.getSelection(true);

          if (range) {
            setTimeout(() => {
              selectedQuill.insertText(range.index, pastedText);
              selectedQuill.setSelection(range.index + pastedText.length);
              handlePasteEvent(selectedQuill);
            }, 5);
          } else {
            setTimeout(() => {
              // If no selection, insert pasted text at the end of the document
              selectedQuill.insertText(selectedQuill.getLength(), pastedText);

              // Set the selection to the end of the pasted text
              selectedQuill.setSelection(selectedQuill.getLength());
              handlePasteEvent(selectedQuill);
            }, 5);
          }
        }
      });
    }
  }

  function loadBase64Image(input, id) {
    validateImage(input);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = input.nextElementSibling;
        const base64String = e.target.result;
        img.src = base64String;
        const base64Input = input.closest(".input-container").querySelector(id);
        base64Input.value = base64String;

      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function removeImageField(button) {
    const fieldToRemove = button.closest(".input-container");
    fieldToRemove.remove();
  }

  function removeSubField(button) {
    const fieldToRemove = button.closest(".input-container");
    fieldToRemove.remove();
  }

  function loadCTA() {
    if (typeof ctaSection === "string") {
      ctaSection = JSON.parse(ctaSection);
    }
    if (typeof ctaSection === "object" && !Array.isArray(ctaSection)) {
      for (var key in ctaSection) {
        const ctaData = {
          [key]: ctaSection[key]
        };
        addOrUpdateCTA(ctaData, key);
      }
    }
  }

  function onModalOpen() {
    const inputs = document.querySelectorAll('input[name="cta_name[]"]');
    const values = [];
    content.forEach((input) => {
      values.push(input.type)
    })
    const containsTakeAction = values.some(value => value && value.includes("cta_take_action"));
    const containsConvert = values.some(value => value && value.includes("cta_convert"));
    const containsHelp = values.some(value => value && value.includes("cta_help"));
    const containsGeneral = values.some(value => value && value.includes("cta_general"));
    const containsScrollable = values.some(value => value && value.includes("cta_scrollable"));
    const containsAd = values.some(value => value && value.includes("cta_ads"));
    const containsHeroSection = values.some(value => value && value.includes("cta_hero"));
    const containsHowtoMake = values.some(value => value && value.includes("cta_how_to_make"));
    const containsProcess = values.some(value => value && value.includes("cta_process"));
    const containsFeature = values.some(value => value && value.includes("cta_feature"));
    const containsSuggestion = values.some(value => value && value.includes("cta_suggestion"));
    const containsMultipleBtn = values.some(value => value && value.includes("cta_multiplebtn"));
    const containsOfferBtn = values.some(value => value && value.includes("cta_offer"));
    // disableButton(containsTakeAction, 'cta_take_action_btn')
    // disableButton(containsConvert, 'cta_convert_btn')
    // disableButton(containsHelp, 'cta_help_btn')
    // disableButton(containsGeneral, 'cta_general_btn')
    // disableButton(containsScrollable, 'cta_scrollable_btn')
    // disableButton(containsAd, 'cta_ads_btn')
    // disableButton(containsHeroSection, 'cta_hero_btn')
    // disableButton(containsHowtoMake, 'cta_how_to_make_btn')
    // disableButton(containsProcess, 'cta_process_btn')
    // disableButton(containsFeature, 'cta_feature_btn')
    // disableButton(containsSuggestion, 'cta_suggestion_btn')
    // disableButton(containsMultipleBtn, 'cta_multiplebtn_btn')
    // disableButton(containsOfferBtn, 'cta_offer_btn')
  }

  function disableButton(containsTakeAction, id) {
    let button = document.getElementById(id);
    let buttonParent = document.getElementById(`${id}_parent`);
    if (containsTakeAction) {
      buttonParent.style.cursor = "not-allowed";
      button.style.opacity = "0.5";
    } else {
      button.style.opacity = "1";
      buttonParent.style.cursor = "pointer";
    }
    button.disabled = containsTakeAction;
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadCTA();
    createBackgroundView("Take", "bgTakeContainer")
    createBackgroundView("Convert", "bgConvertContainer")
    createBackgroundView("Help", "bgHelpContainer")
    createBackgroundView("General", "bgGeneralContainer")
    createBackgroundView("Ads", "bgAdsContainer")
    createBackgroundView("Hero", "bgHeroContainer")
    createBackgroundView("Scrollable", "bgScrollableContainer")
    createBackgroundView("HowToMake", "bgHowToMakeContainer")
    createBackgroundView("MoreTemplate", "bgMoreTemplateContainer")
    createBackgroundView("Process", "bgProcessContainer")
    createBackgroundView("Feature", "bgFeatureContainer")
    createBackgroundView("Suggestion", "bgSuggestionContainer")
    createBackgroundView("MultipleBtn", "bgMultipleBtnContainer")
    createBackgroundView("Offer", "bgOfferContainer")
  });

  function resetConvertCTAModal() {
    $('#cta_convert_modal_title').text('Add Convert CTA');
    $('#ctaConvertTitle').val('');
    $('textarea#ctaConvertDesc').val('');
    $('textarea#ctaConvertDesc').css('height', '120px');
    $('#ctaConvertBtn').val('');
    $('#ctaConvertImgAlt').val('');
    const fileInput = document.getElementById("convertFile");
    fileInput.value = "";
    fileInput.files = null;
    $('#ctaConvertBtnLink').val('');
    $('#ctaConvertBtnTarget').prop('checked', false);
    $('#ctaConvertBtnRel').prop('checked', false);
    // $('#convertCTAImg').attr('src', "")
    $('#convertFileBase64').attr('src', "")
    $('#convertFileBase64').closest('.dynamic-file-input').find('input[type="hidden"]').val("");
    resetBgPara("Convert");
    $(".closeMainCTAModal").click()
  }

  function onConvertSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaConvertTitle = $('#ctaConvertTitle').val();
    var ctaConvertDesc = $('textarea#ctaConvertDesc').val();
    var ctaConvertBtnName = $('#ctaConvertBtn').val();
    var ctaConvertBtnlLink = $('#ctaConvertBtnLink').val();
    var ctaImgAlt = $('#ctaConvertImgAlt').val();
    var ctaConvertOpenInNewTab = $('#ctaConvertBtnTarget').is(':checked');
    var ctaConvertNoFollow = $('#ctaConvertBtnRel').is(':checked');
    if (!ctaConvertTitle.trim() || !ctaConvertDesc.trim() || !ctaConvertBtnName.trim() || !ctaConvertBtnlLink
      .trim()) {
      window.alert('Add name, Description, Button Name and link!');
      return;
    }
    if (!isValidUrl(ctaConvertBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }

    var ctaBase64String = $('#convertFileBase64').attr('src');
    if (editingCTAElement == null && !ctaBase64String) {
      window.alert('Please select a file!');
      return;
    }
    if (!ctaImgAlt.trim()) {
      window.alert("Add Image Alt Name")
      return;
    }

    const ctaData = {
      title: ctaConvertTitle,
      desc: ctaConvertDesc,
      bg: getBgObject("Convert"),
      button: {
        name: ctaConvertBtnName,
        link: ctaConvertBtnlLink,
        target: ctaConvertOpenInNewTab ? 1 : 0,
        rel: ctaConvertNoFollow ? 1 : 0,
      },
      name: ctaName,
      image: {

        src: ctaBase64String,
        alt: ctaImgAlt
      },
      value: ctaValue
    };

    if (editedContent && ctaBase64String.trim() && ctaBase64String.startsWith("uploadedFiles")) {
      if (editedContent.image.height) ctaData.image.height = editedContent.image.height
      if (editedContent.image.width) ctaData.image.width = editedContent.image.width
    }
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }


  function onTakeSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaTakeActionTitle = $('#ctaTakeActionTitle').val();
    var ctaTakeActionDesc = $('textarea#ctaTakeActionDesc').val();
    var ctaTakeActionBtnName = $('#ctaTakeActionBtn').val();
    var ctaTakeActionBtnlLink = $('#ctaTakeActionBtnLink').val();
    var ctaTakeActionOpenInNewTab = $('#ctaTakeActionBtnTarget').is(':checked');
    var ctaTakeActionNoFollow = $('#ctaTakeActionBtnRel').is(':checked');

    if (!ctaTakeActionTitle.trim() || !ctaTakeActionDesc.trim() || !ctaTakeActionBtnName.trim()) {
      window.alert('Add name and link!');
      return;
    }

    if (!isValidUrl(ctaTakeActionBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }



    const ctaData = {
      title: ctaTakeActionTitle,
      desc: ctaTakeActionDesc,
      bg: getBgObject("Take"),
      button: {
        name: ctaTakeActionBtnName,
        link: ctaTakeActionBtnlLink,
        target: ctaTakeActionOpenInNewTab ? 1 : 0,
        rel: ctaTakeActionNoFollow ? 1 : 0,
      },
      name: ctaName,
      value: ctaValue
    };

    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function getBgObject(prefix) {
    const bg = {};
    let bgTypeTake = $(`#bgType${prefix}`).val();
    bg.value = bgTypeTake;
    if (bgTypeTake == 1) {
      let ctaBGColor = $(`#cta${prefix}BgColor`).val();
      bg.color = ctaBGColor;
    } else if (bgTypeTake == 2) {
      let ctaBgBase64String = $(`#bg${prefix}FileBase64`).attr('src');
      // let bgTakeFile = $(`#bg${prefix}CTAImg`).attr('src');

      let ctaTakeBgImgAlt = $(`#cta${prefix}BgImgAlt`).val();
      if (!ctaBgBase64String.trim()) {
        window.alert('Please select a Background File');
        return;
      }
      if (!ctaBgBase64String.trim()) {
        ctaBgBase64String = bgTakeFile.replace("../", "")
      }
      if (!ctaTakeBgImgAlt.trim()) {
        window.alert('please Add Bg Image Alt');
        return;
      }
      bg.src = ctaBgBase64String;
      bg.alt = ctaTakeBgImgAlt;
    }
    return bg;
  }

  function onHelpSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaHelpTitle = $('#ctaHelpTitle').val();
    var ctaHelpInfo = $('#ctaHelpInfo').val();
    if (!ctaHelpTitle.trim() || !ctaHelpInfo.trim() || ctaHelpQuill.getText().trim() == '') {
      window.alert('Add name Description and info');
      return;
    }
    const ctaHelpDesc = ctaHelpQuill.root.innerHTML;
    const ctaData = {
      title: ctaHelpTitle,
      desc: ctaHelpDesc,
      bg: getBgObject("Help"),
      info: ctaHelpInfo,
      name: ctaName,
      value: ctaValue
    };
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onGeneralSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaGeneralTitle = $('#ctaGeneralTitle').val();
    var ctaGeneralDesc = $('textarea#ctaGeneralDesc').val();
    var ctaGeneralInfo = $('#ctaGeneralBtnName').val();
    var ctaGeneralBtnlLink = $('#ctaGeneralBtnLink').val();
    var ctaGeneralOpenInNewTab = $('#ctaGeneralBtnTarget').is(':checked');
    var ctaGeneralNoFollow = $('#ctaGeneralBtnRel').is(':checked');
    if (!ctaGeneralTitle.trim() || !ctaGeneralDesc.trim() || !ctaGeneralInfo.trim()) {
      window.alert('Add name Description and info');
      return;
    }
    if (!isValidUrl(ctaGeneralBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }

    const ctaData = {
      title: ctaGeneralTitle,
      desc: ctaGeneralDesc,
      bg: getBgObject("General"),
      ["button"]: {
        link: ctaGeneralBtnlLink,
        target: ctaGeneralOpenInNewTab ? 1 : 0,
        rel: ctaGeneralNoFollow ? 1 : 0,
        name: ctaGeneralInfo,
      },
      name: ctaName,
      value: ctaValue
    };
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onAdSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaAdTitle = $('#ctaAdTitle').val();
    var ctaAdDesc = $('textarea#ctaAdDesc').val();
    var ctaAdBtnlLink = $('#ctaAdBtnLink').val();
    var ctaImgAlt = $('#ctaAdImgAlt').val();
    var ctaBtnImgAlt = $('#ctaAdBtnImgAlt').val();
    var ctaAdOpenInNewTab = $('#ctaAdBtnTarget').is(':checked');
    var ctaAdNoFollow = $('#ctaAdBtnRel').is(':checked');
    if (!ctaAdTitle.trim() || !ctaAdDesc.trim()) {
      window.alert('Add name and Description!');
      return;
    }

    if (!isValidUrl(ctaAdBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }

    var ctaBtnBase64String = $('#adBtnFileBase64').attr('src');
    if (editingCTAElement == null && !ctaBtnBase64String.trim()) {
      window.alert('Please select a Button file!');
      return;
    }


    var ctaBase64String = $('#adFileBase64').attr('src');

    if (editingCTAElement == null && !ctaBase64String.trim()) {
      window.alert('Please select a file!');
      return;
    }

    if (!ctaBtnImgAlt.trim() || !ctaImgAlt.trim()) {
      window.alert("Add Image Alt Name")
      return;
    }

    const ctaData = {
      title: ctaAdTitle,
      desc: ctaAdDesc,
      name: ctaName,
      bg: getBgObject("Ads"),
      image: {

        src: ctaBase64String,
        alt: ctaImgAlt
      },
      button: {
        link: ctaAdBtnlLink,
        src: ctaBtnBase64String,
        alt: ctaBtnImgAlt,
        target: ctaAdOpenInNewTab ? 1 : 0,
        rel: ctaAdNoFollow ? 1 : 0,
      },
      value: ctaValue
    };
    if (editedContent && ctaBtnBase64String.trim().startsWith("uploadedFiles")) {
      if (editedContent.button.height) ctaData.button.height = editedContent.button.height
      if (editedContent.button.width) ctaData.button.width = editedContent.button.width
    }
    if (editedContent && ctaBtnBase64String.trim().startsWith("uploadedFiles")) {
      if (editedContent.image.height) ctaData.image.height = editedContent.image.height
      if (editedContent.image.width) ctaData.image.width = editedContent.image.width
    }
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onHeroSubmit(ctaName, ctaValue, modalKeyName) {
    var ctaHeroTitle = $('#ctaHeroTitle').val();
    var ctaHeroBtnName = $('#ctaHeroBtn').val();
    var ctaHeroBtnlLink = $('#ctaHeroBtnLink').val();
    var ctaHeroOpenInNewTab = $('#ctaHeroBtnTarget').is(':checked');
    var ctaHeroNoFollow = $('#ctaHeroBtnRel').is(':checked');
    var ctaImgAlt = $('#ctaHeroImgAlt').val();
    var fileInput = document.getElementById('heroFile');
    if (!ctaHeroTitle.trim() || !ctaHeroBtnName.trim() || !ctaHeroBtnlLink.trim()) {
      window.alert('Add name, Button Name and link!');
      return;
    }

    if (!isValidUrl(ctaHeroBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }


    var ctaBase64String = $('#heroFileBase64').attr('src');

    if (editingCTAElement == null && !ctaBase64String.trim()) {
      window.alert('Please select a file!');
      return;
    }

    if (!ctaImgAlt.trim()) {
      window.alert("Add Image Alt Name")
      return;
    }

    const ctaData = {
      title: ctaHeroTitle,
      name: ctaName,
      bg: getBgObject("Hero"),
      image: {

        src: ctaBase64String,
        alt: ctaImgAlt
      },
      button: {
        name: ctaHeroBtnName,
        link: ctaHeroBtnlLink,
        target: ctaHeroOpenInNewTab ? 1 : 0,
        rel: ctaHeroNoFollow ? 1 : 0,
      },
      value: ctaValue
    };

    if (editedContent && !ctaBase64String.trim() && ctaBase64String.startsWith("uploadedFiles")) {
      if (editedContent.image.height) ctaData.image.height = editedContent.image.height
      if (editedContent.image.width) ctaData.image.width = editedContent.image.width
    }
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onMoreTemplateSubmit(ctaName, ctaValue, modalKeyName) {
    console.log("=== onMoreTemplateSubmit called ===");
    const virtualType = $('select#virtualType').val();
    console.log("Virtual Type:", virtualType);
    
    let ctaData = new Object();
    ctaData.virtualType = virtualType;
    
    if (virtualType == 'url') {
      const ctaMoreTemplateVirtualSlug = $('#ctaMoreTemplateVirtualSlug').val();
      console.log("Virtual Slug:", ctaMoreTemplateVirtualSlug);
      if (!ctaMoreTemplateVirtualSlug) {
        alert("Please Enter Virtual Slug")
        return;
      }
      ctaData.slug = ctaMoreTemplateVirtualSlug;
    } else {
      const ctaMoreTemplateTitle = $('#ctaMoreTemplateTitle').val();
      const ctaMoreTemplateDesc = ctaMoreTemplateQuill ? ctaMoreTemplateQuill.root.innerHTML : '';
      const container = document.getElementById("moreTemplateVirtualContainer");
      const generatedQuery = container ? container.querySelector("#generatedQuery").value : '';
      
  console.log("Title:", ctaMoreTemplateTitle);
      console.log("Description:", ctaMoreTemplateDesc);
      console.log("Generated Query:", generatedQuery);
      console.log("Quill Editor:", ctaMoreTemplateQuill);
      
      // Check each field individually and show specific error message
      if (!ctaMoreTemplateTitle) {
        alert("Please scroll up and enter a Title");
        return;
      }
      
      // Make description optional - use empty paragraph if not provided
      const finalDesc = (!ctaMoreTemplateDesc || ctaMoreTemplateDesc.trim() === '' || ctaMoreTemplateDesc === '<p><br></p>') 
        ? '<p></p>' 
        : ctaMoreTemplateDesc;
      
      if (!generatedQuery) {
        alert("Please add at least one condition to the Conditions Table");
        return;
      }
      ctaData.title = ctaMoreTemplateTitle;
      ctaData.desc = finalDesc;
      ctaData.query = generatedQuery;
    }
    
    ctaData.bg = getBgObject('MoreTemplate');
    ctaData.name = ctaName;
    ctaData.value = ctaValue;
    
    console.log("CTA Data:", ctaData);
    console.log("Calling addOrUpdateCTA...");
    
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    
    // Disable the Show Templates button after adding More Template CTA
    let showTemplete = document.querySelector(".showTemplete");
    if (showTemplete) {
      showTemplete.disabled = true;
    }
    
    console.log("Hiding modal...");
    $(`#${modalKeyName}`).modal("hide");
    console.log("=== onMoreTemplateSubmit completed ===");
  }

  function resetMoreTemplateCTAModal() {
    $('#cta_more_template_title').text('Add More Template CTA');
    const virtualType = document.getElementById('virtualType');
    virtualType.value = 'url';
    virtualType.dispatchEvent(new Event('change'));
    $('#ctaMoreTemplateVirtualSlug').val('');
    ctaMoreTemplateQuill.root.innerHTML = "";
    $('#ctaMoreTemplateTitle').val('')
    const moreTemplateVirtualContainer = document.getElementById('moreTemplateVirtualContainer');
    $('#moreTemplateVirtualContainer #generatedQuery').val('')
    const columnSelects = moreTemplateVirtualContainer.querySelector('#column');
    columnSelects.selectedIndex = 0;
    columnSelects.dispatchEvent(new Event('change'));
    $('#moreTemplateVirtualContainer #conditionsTable tbody').empty();

    resetBgPara("MoreTemplate");
  }

  function onScrollableSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaScrollableTitle = $('#ctaScrollableTitle').val();
    const ctaScrollableDesc = ctaScrollableQuill.root.innerHTML;
    const ctaScrollableBtnName = $('#ctaScrollableBtn').val();
    const ctaScrollableBtnlLink = $('#ctaScrollableBtnLink').val();
    const ctaScrollableOpenInNewTab = $('#ctaScrollableBtnTarget').is(':checked');
    const ctaScrollableNoFollow = $('#ctaScrollableBtnRel').is(':checked');
    const files = document.querySelectorAll('.scrollableFile');

    if (!ctaScrollableTitle.trim() || !ctaScrollableDesc.trim() || !ctaScrollableBtnName.trim() ||
      !ctaScrollableBtnlLink.trim()) {
      window.alert('Add Title, Description, Button Name, and Link!');
      return;
    }

    if (!isValidUrl(ctaScrollableBtnlLink)) {
      window.alert('Enter Valid URL');
      return;
    }
    // const container = document.getElementById("scrollableVirtualContainer");
    // const generatedQuery = container.querySelector("#generatedQuery").value;
    const container = document.getElementById("scrollableImageContainer");
    const existingFields = container.querySelectorAll(".custom-image");

    // Check if previous fields are filled
    for (let field of existingFields) {
      const base64Input = field.querySelector(".scrollableFileBase64");
      const imageAlt = field.querySelector("input.ctaScrollableImgAlt");
      const imglinkInput = field.querySelector("input.ctaScrollableImglink");

      if (
        !imageAlt?.value.trim() ||
        !imglinkInput.value.trim() ||
        !base64Input.src
      ) {
        alert("Please fill previous img alt field and upload images before adding a new field.");
        return;
      }

      if (!isValidUrl(imglinkInput.value.trim())) {
        alert("Please add valid url");
        return;
      }
    }

    const fileList = [];
    const targetList = [];
    const relList = [];
    const imgAltList = [];
    const linkList = [];
    const fileInputs = document.querySelectorAll(".scrollableFileBase64");
    const imgAltInputs = document.querySelectorAll(".ctaScrollableImgAlt");
    const linkInputs = document.querySelectorAll(".ctaScrollableImglink");
    const targetInputs = document.querySelectorAll(".ctaScrollableTarget");
    const relInputs = document.querySelectorAll(".ctaScrollableRel");

    fileInputs.forEach((input) => {
      if (input.src) {
        fileList.push(input.src);
      }
    });

    if (editingCTAElement === null && fileList.length === 0) {
      window.alert('Please select at least one image!');
      return;
    }

    imgAltInputs.forEach((input) => {
      if (input.value) {
        imgAltList.push(input.value);
      }
    });

    linkInputs.forEach((input) => {
      if (input.value) {
        linkList.push(input.value);
      }
    });

    targetInputs.forEach((input) => {
      if (input) {
        targetList.push(input.checked ? 1 : 0);
      }
    });
    relInputs.forEach((input) => {
      if (input) {
        relList.push(input.checked ? 1 : 0);
      }
    });

    const images = [];
    for (let i = 0; i < fileInputs.length; i++) {
      images.push({
        target: targetList[i],
        rel: relList[i],
        alt: imgAltList[i],
        link: linkList[i],
        src: fileList[i],
      });
    }
    const ctaData = {
      title: ctaScrollableTitle,
      desc: ctaScrollableDesc,
      bg: getBgObject("Scrollable"),
      button: {
        name: ctaScrollableBtnName,
        link: ctaScrollableBtnlLink,
        target: ctaScrollableOpenInNewTab ? 1 : 0,
        rel: ctaScrollableNoFollow ? 1 : 0,
      },
      name: ctaName,
      images: images,
      value: ctaValue
    };
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    $(`#cta_scrollable_modal`).modal("hide");
  }

  function onHowToMakeSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaHowToMakeTitle = $('#ctaHowToMakeTitle').val();
    const ctaHowToMakeDesc = $('textarea#ctaHowToMakeDesc').val();
    const ctaHowToMakeSubTitle = document.querySelectorAll('.ctaHowToMakeSubTitle');
    const ctaHowToMakeSubDesc = document.querySelectorAll('.ctaHowToMakeSubDesc');
    const ctaHowToMakeSubFiles = document.querySelectorAll('.scrollableFileBase64');
    const container = document.getElementById("ctaHowToMakeContainer");
    const existingFields = container.querySelectorAll(".input-container");
    if (!ctaHowToMakeTitle.trim() || !ctaHowToMakeDesc.trim()) {
      window.alert('Add Title, Description');
      return;
    }
    let count = 0;
    for (let field of existingFields) {
      const subtitleInput = field.querySelector("#ctaSubTitle");
      const subDescInput = ctaHowToMakeQuill[count].root.innerHTML;
      const base64Input = field.querySelector("#subFileBase64");

      if (
        !subtitleInput.value.trim() ||
        subDescInput == '' ||
        !base64Input.src
      ) {
        alert("Please fill in all previous subtitles, descriptions, and upload images before adding a new field.");
        return;
      }
      count++;
    }

    const fileList = [];
    const subTitleList = [];
    const subDescList = [];
    const imgAltList = [];
    const ctaHowToMakeContainer = document.getElementById("ctaHowToMakeContainer");

    const fileInputs = ctaHowToMakeContainer.querySelectorAll(".subFileBase64");
    const subTitleInputs = ctaHowToMakeContainer.querySelectorAll(".ctaSubTitle");
    const imgAltInputs = ctaHowToMakeContainer.querySelectorAll(".ctaSubImgAlt");
    const subDescInputs = ctaHowToMakeContainer.querySelectorAll(".ctaSubDesc");

    fileInputs.forEach((input) => {
      if (input.src) {
        fileList.push(input.src);
      }
    });
    let count2 = 0;
    subTitleInputs.forEach((input) => {
      if (input.value) {
        subTitleList.push(input.value);
        if (ctaHowToMakeQuill[count2]) {
          let text = ctaHowToMakeQuill[count2].root.innerHTML;
          subDescList.push(text);
        } else {
          subDescList.push('');
        }
        count2++;
      }
    });
    subDescInputs.forEach((input) => {
      if (input.value) {
        subDescList.push(input.value);
      }
    });
    imgAltInputs.forEach((input) => {
      if (input.value) {
        imgAltList.push(input.value);
      }
    });


    if (editingCTAElement === null && fileList.length === 0) {
      window.alert('Please add Atleast 1 Sub Field');
      return;
    }

    const stepSection = [];
    for (let i = 0; i < subTitleList.length; i++) {
      stepSection.push({
        title: subTitleList[i],
        desc: subDescList[i],
        image: {
          src: fileList[i],
          alt: imgAltList[i]
        },
      });
    }

    const ctaData = {
      title: ctaHowToMakeTitle,
      desc: ctaHowToMakeDesc,
      bg: getBgObject("HowToMake"),
      name: ctaName,
      stepsection: stepSection,
      value: ctaValue
    };
    stepSection.forEach((value, index) => {
      if (editedContent && value.image.src.startsWith("uploadedFiles")) {
        const matchedData = editedContent.stepsection.filter(value2 => value2.image.src == value.image.src);
        if (matchedData[0].image.width) value.image.width = matchedData[0].image.width;
        if (matchedData[0].image.height) value.image.height = matchedData[0].image.height;
      }
    })
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onProcessSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaProcessTitle = $('#ctaProcessTitle').val();
    const ctaProcessDesc = $('textarea#ctaProcessDesc').val();
    const processImgPosition = $('select#processImgPosition').val();
    const processStepPosition = $('select#processStepPosition').val();
    const ctaProcessSubTitle = document.querySelectorAll('.ctaProcessSubTitle');
    const ctaProcessSubDesc = document.querySelectorAll('.ctaProcessSubDesc');
    const ctaProcessSubFiles = document.querySelectorAll('.processFileBase64');
    const container = document.getElementById("ctaProcessContainer");
    const existingFields = container.querySelectorAll(".input-container");

    var fileInput = document.getElementById('File');

    if (!ctaProcessTitle.trim() || !ctaProcessDesc.trim()) {
      window.alert('Add Title, Description');
      return;
    }
    let count = 0;
    for (let field of existingFields) {
      const subtitleInput = field.querySelector("#ctaSubTitle");
      const subDescInput = ctaProcessQuill[count].root.innerHTML;
      const base64Input = field.querySelector("#subFileBase64");
      if (
        !subtitleInput.value.trim() ||
        subDescInput == '' ||
        !base64Input.src
      ) {
        alert("Please fill in all previous subtitles, descriptions, and upload images before adding a new field.");
        return;
      }
      count++;
    }

    const fileList = [];
    const subTitleList = [];
    const subDescList = [];
    const imgAltList = [];
    const ctaProcessContainer = document.getElementById("ctaProcessContainer");
    const fileInputs = ctaProcessContainer.querySelectorAll(".subFileBase64");
    const subTitleInputs = ctaProcessContainer.querySelectorAll(".ctaSubTitle");
    const imgAltInputs = ctaProcessContainer.querySelectorAll(".ctaSubImgAlt");
    const subDescInputs = ctaProcessContainer.querySelectorAll(".ctaSubDesc");

    fileInputs.forEach((input) => {
      if (input.src) {
        fileList.push(input.src);
      }
    });
    let count2 = 0;
    subTitleInputs.forEach((input) => {
      if (input.value) {
        subTitleList.push(input.value);
        if (ctaProcessQuill[count2]) {
          let text = ctaProcessQuill[count2].root.innerHTML;
          subDescList.push(text);
        } else {
          subDescList.push('');
        }
        count2++;
      }
    });
    imgAltInputs.forEach((input) => {
      if (input.value) {
        imgAltList.push(input.value);
      }
    });

    if (editingCTAElement === null && subTitleList.length === 0) {
      window.alert('Please add Atleast 1 Sub Field');
      return;
    }

    const stepSection = [];
    for (let i = 0; i < subTitleList.length; i++) {
      stepSection.push({
        title: subTitleList[i],
        desc: subDescList[i],
        image: {
          src: fileList[i],
          alt: imgAltList[i]
        },
      });
    }
    const ctaData = {
      title: ctaProcessTitle,
      desc: ctaProcessDesc,
      bg: getBgObject("Process"),
      name: ctaName,
      imgposition: processImgPosition,
      stepposition: processStepPosition,
      stepsection: stepSection,
      value: ctaValue
    };
    stepSection.forEach((value, index) => {
      if (editedContent && value.image.src.startsWith("uploadedFiles")) {
        const matchedData = editedContent.stepsection.filter(value2 => value2.image.src == value.image.src);
        if (matchedData[0].image.width) value.image.width = matchedData[0].image.width;
        if (matchedData[0].image.height) value.image.height = matchedData[0].image.height;
      }
    })
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onOfferSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaOfferTitle = $('#ctaOfferTitle').val();
    const ctaOfferDesc = $('#ctaOfferDesc').val();
    const ctaOfferBtnName = $('#ctaOfferBtn').val();
    const ctaOfferBtnlLink = $('#ctaOfferBtnLink').val();
    const ctaOfferOpenInNewTab = $('#ctaOfferBtnTarget').is(':checked');
    const ctaOfferNoFollow = $('#ctaOfferBtnRel').is(':checked');
    if (!ctaOfferTitle.trim() || !ctaOfferBtnName.trim() || !ctaOfferBtnlLink.trim()) {
      window.alert('Please fill Title,button name,button link and Image Alt');
      return;
    }

    const ctaData = {
      title: ctaOfferTitle,
      desc: ctaOfferDesc,
      name: ctaName,
      bg: getBgObject("Offer"),
      button: {
        name: ctaOfferBtnName,
        link: ctaOfferBtnlLink,
        target: ctaOfferOpenInNewTab ? 1 : 0,
        rel: ctaOfferNoFollow ? 1 : 0,
      },
      value: ctaValue
    };
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }


  function resetOfferCTAModal() {
    $('#cta_offer_modal_title').text('Add Offer CTA');
    $('#ctaOfferTitle').val('');
    $('#ctaOfferDesc').val('');
    $('#ctaOfferBtn').val('');
    $('#ctaOfferBtnLink').val('');
    $('#ctaOfferBtnTarget').prop('checked', false);
    $('#ctaOfferBtnRel').prop('checked', false);
    resetBgPara("Offer");
    $(".closeMainCTAModal").click()
  }

  function onFeatureSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaFeatureTitle = $('#ctaFeatureTitle').val();
    const ctaFeatureDesc = $('textarea#ctaFeatureDesc').val();
    const featureImgPosition = $('select#featureImgPosition').val();
    const ctaFeatureInfoImgAlt = $('#ctaFeatureInfoImgAlt').val();
    if (!ctaFeatureTitle.trim() || !ctaFeatureDesc.trim() || !ctaFeatureInfoImgAlt.trim()) {
      window.alert('Please fill all Field');
      return;
    }


    let ctaBase64InfoString = $('#featureInfoFileBase64').attr('src');
    if (editingCTAElement == null && !ctaBase64InfoString.trim()) {
      window.alert('Please select a Info file!');
      return;
    }
    // if (!ctaBase64InfoString.trim()) {
    //   let ctaInfoFile = $(`#featureInfoCTAImg`).attr('src');

    //   ctaBase64InfoString = ctaInfoFile.replace("../", "")
    //   console.log(ctaBase64InfoString);
    // }

    const btnNameList = [];
    const btnLinkList = [];
    const targetList = [];
    const relList = [];

    const ctaFeatureContainer = document.getElementById("ctaFeatureContainer");
    const btnNameInputs = ctaFeatureContainer.querySelectorAll(".ctaBtnName");
    const linkInputs = ctaFeatureContainer.querySelectorAll(".ctaBtnLink");
    const targetInputs = ctaFeatureContainer.querySelectorAll(".ctaBtnTarget");
    const relInputs = ctaFeatureContainer.querySelectorAll(".ctaBtnRel");

    btnNameInputs.forEach((input) => {
      if (input.value) {
        btnNameList.push(input.value);
      }
    });

    if (editingCTAElement === null && btnNameList.length === 0) {
      window.alert('Please select at least one image!');
      return;
    }

    linkInputs.forEach((input) => {
      if (input.value) {
        btnLinkList.push(input.value);
      }
    });

    targetInputs.forEach((input) => {
      if (input) {
        targetList.push(input.checked ? 1 : 0);
      }
    });
    relInputs.forEach((input) => {
      if (input) {
        relList.push(input.checked ? 1 : 0);
      }
    });

    const btns = [];
    for (let i = 0; i < btnNameList.length; i++) {
      btns.push({
        name: btnNameList[i],
        link: btnLinkList[i],
        target: targetList[i],
        rel: relList[i],
      });
    }
    const ctaData = {
      title: ctaFeatureTitle,
      desc: ctaFeatureDesc,
      bg: getBgObject("Feature"),
      buttons: btns,
      name: ctaName,
      image: {
        src: ctaBase64InfoString,
        alt: ctaFeatureInfoImgAlt
      },
      imgposition: featureImgPosition,
      value: ctaValue
    };
    if (editedContent) {
      if (editedContent.image.width) ctaData.image.width = editedContent.image.width;
      if (editedContent.image.height) ctaData.image.height = editedContent.image.height;
    }
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onMultipleBtnSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaMultipleBtnTitle = $('#ctaMultipleBtnTitle').val();
    const ctaMultipleBtnDesc = $('textarea#ctaMultipleBtnDesc').val();
    const multipleBtnImgPosition = $('select#multipleBtnImgPosition').val();
    const container = document.getElementById("ctaMultipleBtnContainer");
    const existingFields = container.querySelectorAll(".input-container");
    if (!ctaMultipleBtnTitle.trim() || !ctaMultipleBtnDesc.trim() || !multipleBtnImgPosition.trim()) {
      window.alert('Please fill all Field');
      return;
    }

    for (let field of existingFields) {
      const ctaBtnName = field.querySelector("#ctaBtnName");
      const ctaBtnLink = field.querySelector("#ctaBtnLink");
      const base64Input = field.querySelector("#subFileBase64");
      const imgAlt = field.querySelector("#ctaSubImgAlt");
      if (
        !ctaBtnName.value.trim() ||
        !ctaBtnLink.value.trim() ||
        !base64Input.src || !imgAlt.value.trim()
      ) {
        alert("Please fill in all previous field before adding a new field.");
        return;
      }
      if (!isValidUrl(ctaBtnLink.value)) {
        alert("Please Add Valid Url")
        return;
      }
    }
    const btnNameList = [];
    const btnLinkList = [];
    const targetList = [];
    const relList = [];
    const imgAltList = [];
    const fileList = [];
    const ctaMultipleBtnContainer = document.getElementById('ctaMultipleBtnContainer');
    const btnNameInputs = ctaMultipleBtnContainer.querySelectorAll(".ctaBtnName");
    const linkInputs = ctaMultipleBtnContainer.querySelectorAll(".ctaBtnLink");
    const imgAltInputs = ctaMultipleBtnContainer.querySelectorAll(".ctaSubImgAlt");
    const targetInputs = ctaMultipleBtnContainer.querySelectorAll(".ctaBtnTarget");
    const relInputs = ctaMultipleBtnContainer.querySelectorAll(".ctaBtnRel");
    const fileInputs = ctaMultipleBtnContainer.querySelectorAll(".subFileBase64");

    btnNameInputs.forEach((input) => {
      if (input.value) {
        btnNameList.push(input.value);
      }
    });

    if (editingCTAElement === null && btnNameList.length === 0) {
      window.alert('Please select at least one image!');
      return;
    }

    linkInputs.forEach((input) => {
      if (input.value) {
        btnLinkList.push(input.value);
      }
    });
    targetInputs.forEach((input) => {
      if (input) {
        targetList.push(input.checked ? 1 : 0);
      }
    });
    relInputs.forEach((input) => {
      if (input) {
        relList.push(input.checked ? 1 : 0);
      }
    });
    fileInputs.forEach((input) => {
      if (input.src) {
        fileList.push(input.src);
      }
    });
    imgAltInputs.forEach((input) => {
      if (input.value) {
        imgAltList.push(input.value);
      }
    });
    const stepSection = [];
    for (let i = 0; i < btnNameList.length; i++) {
      stepSection.push({
        btn: {
          name: btnNameList[i],
          link: btnLinkList[i],
          target: targetList[i],
          rel: relList[i],
        },
        image: {
          src: fileList[i],
          alt: imgAltList[i]
        },
      });
    }

    const ctaData = {
      title: ctaMultipleBtnTitle,
      desc: ctaMultipleBtnDesc,
      bg: getBgObject("MultipleBtn"),
      name: ctaName,
      imgposition: multipleBtnImgPosition,
      stepsection: stepSection,
      value: ctaValue
    };
    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    closeCTAModal(ctaValue + "_modal")
  }

  function onSuggestionSubmit(ctaName, ctaValue, modalKeyName) {
    const ctaSuggestionTitle = $('#ctaSuggestionTitle').val();
    const suggestionTitlePosition = $('select#suggestionTitlePosition').val();
    if (!ctaSuggestionTitle.trim() || ctaSuggestionQuill.getText().trim() == '') {
      window.alert('Please fill all Field Name and Description');
      return;
    }
    const ctaSuggestionDesc = ctaSuggestionQuill.root.innerHTML;
    const ctaData = {
      name: ctaName,
      title: ctaSuggestionTitle,
      bg: getBgObject("Suggestion"),
      desc: ctaSuggestionDesc,
      value: ctaValue,
      titleposition: suggestionTitlePosition
    };

    addOrUpdateCTA(ctaData, ctaValue, modalKeyName);
    $(`#cta_suggestion_modal`).modal("hide");
  }


  // function loadBase64String(str) {
  //   const base64Regex = /^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/;
  //   const hasBase64Prefix = str.startsWith('data:image');
  //   const base64String = hasBase64Prefix ? str.split(',')[1] : str;
  //   if (base64Regex.test(base64String)) {
  //     return str;
  //   } else {
  //     return `${storageUrl}${str}`
  //   }
  // }

  function resetProcessCTAModal() {
    $('#cta_how_to_make_title').text('Add Process CTA');
    $('#ctaProcessTitle').val('');
    $('textarea#ctaProcessDesc').val('');
    $('textarea#ctaProcessDesc').css('height', '120px');
    $('#ctaProcessImgAlt').val('');
    $('#processCTAImg').attr('src', "")
    $('#ctaProcessContainer').empty();
    resetBgPara("Process");
    $(".closeMainCTAModal").click()
  }


  function resetFeatureCTAModal() {
    $('#cta_feature_title').text('Add Feature CTA');
    $('#ctaFeatureTitle').val('');
    $('textarea#ctaFeatureDesc').val('');
    $('textarea#ctaFeatureDesc').css('height', '120px');
    $('#ctaFeatureInfoImgAlt').val('');
    // $('#featureInfoCTAImg').attr('src', "");
    $('#featureInfoFileBase64').attr('src', "")
    $('#featureInfoFileBase64').closest('.dynamic-file-input').find('input[type="hidden"]').val("");
    $('#ctaFeatureContainer').empty();
    resetBgPara("Feature");
    $(".closeMainCTAModal").click()
    const fileInput2 = document.getElementById("featureInfoFile");
    fileInput2.value = "";
    fileInput2.files = null;
  }

  function openCTAModal(id, ctaKey) {
    $(".closeMainCTAModal").click();
    openFCTA(`${ctaKey}_modal`)
  }

  function openCTAModalManually(e, id) {
    e.preventDefault();
    openFCTA(id)
    resetTakeActionCTAModal();
    resetConvertCTAModal();
    resetGeneralCTAModal();
    resetAdCtaModal();
    resetFeatureCTAModal();
    resetProcessCTAModal();
    resetHelpCTAModal();
    resetHeroCTAModal();
    resetHowToMakeCTAModal();
    resetScrollableCTAModal();
    resetMoreTemplateCTAModal();
    resetSuggestionCTAModal();
    resetOfferCTAModal();
    resetMultipleBtnCTAModal();
  }

  function openFCTA(id) {
    $(".closeMainCTAModal").click();
    $(`#${id}`).modal("show");
  }

  function closeCTAModalManually(e, id) {
    e.preventDefault();
    $(`#${id}_modal`).modal("hide");
    editingCTAElement = null;
    editedContent = null;
    selectedID = null;
  }

  $(document).on('click', ".ctaOpenModalBtn", () => {
    $(".closeMainCTAModal").click();
  })

  function closeCTAModal(id) {
    editingCTAElement = null;
    editedContent = null;
    $(".closeCTAModal").click();
  }

  function resetSuggestionCTAModal() {
    $(".closeMainCTAModal").click()
    $('#cta_suggestion_title').text('Add Suggestion CTA');
    $('#ctaSuggestionTitle').val('');
    $('#ctaSuggestionDesc').css('max-height', '120px');
    $('#ctaSuggestionDesc').css('overflow-x', 'scroll');
    resetBgPara("Suggestion");
    ctaSuggestionQuill.root.innerHTML = '';
  }

  function ctaInsertLink(quillInstance) {
    var href = $('#ctaLinkUrl').val();
    var openInNewTab = $('#openInNewTab').prop('checked');
    var noFollow = $('#nofollow').prop('checked');
    var target = openInNewTab ? '_blank' : '_self';

    if (href) {
      $(".linkUrlError").text('');
      quillInstance.format('link', {
        href: href,
        target: target,
        nofollow: noFollow
      });
      $('#ctaLinkModal').modal('hide');
    } else {
      var href = $('.ctaLinkUrlError').text('Please Add Link Here');
    }
  }

  function resetMultipleBtnCTAModal() {
    $('#cta_multiplebtn_title').text('Add Multiple Button CTA');
    $('#ctaMultipleBtnTitle').val('');
    $('textarea#ctaMultipleBtnDesc').val('');
    $('textarea#ctaMultipleBtnDesc').css('height', '120px');
    $('#ctaMultipleBtnContainer').empty();
    resetBgPara("MultipleBtn");
    $(".closeMainCTAModal").click()
  }

  function resetHowToMakeCTAModal() {
    $('#cta_how_to_make_title').text('Add How To Make CTA');
    $('#ctaHowToMakeTitle').val('');
    $('textarea#ctaHowToMakeDesc').val('');
    $('textarea#ctaHowToMakeDesc').css('height', '120px');
    $('#ctaHowToMakeContainer').empty();
    resetBgPara("HowToMake");
    $(".closeMainCTAModal").click()
  }

  function resetScrollableCTAModal() {
    $('#scrollable_cta_modal_title').text('Add Scrollable CTA');
    $('#ctaScrollableTitle').val('');
    $('#ctaScrollableBtn').val('');
    $('#ctaScrollableBtnLink').val('');
    $('#ctaScrollableBtnTarget').prop('checked', false);
    $('#ctaScrollableBtnRel').prop('checked', false);
    ctaScrollableQuill.root.innerHTML = "";
    $('#scrollableImageContainer').empty();
    resetBgPara("Scrollable");
    $(".closeMainCTAModal").click()
    // addImageField();
  }



  function resetAdCtaModal() {
    $('#cta_ads_title').text('Add Ads CTA');
    $('#ctaAdTitle').val('');
    $('textarea#ctaAdDesc').val('');
    $('#ctaAdBtnLink').val('');
    $('#ctaAdImgAlt').val('');
    $('#ctaAdBtnImgAlt').val('');
    $('#ctaAdBtnLink').val('');
    $('textarea#ctaAdDesc').css('height', '120px');
    $('#ctaAdBtnTarget').prop('checked', false);
    $('#ctaAdBtnRel').prop('checked', false);
    $(".closeMainCTAModal").click()
    $('#adFileBase64').attr('src', "")
    $('#adFileBase64').closest('.dynamic-file-input').find('input[type="hidden"]').val("");
    $('#adBtnFileBase64').attr('src', "")
    $('#adBtnFileBase64').closest('.dynamic-file-input').find('input[type="hidden"]').val("");
    // const fileInput = document.getElementById("adFile");
    // fileInput.value = "";
    // fileInput.files = null;
    // $('#adCTAImg').attr('src', "")
    // const fileInput2 = document.getElementById("ctaAdBtnFile");
    // fileInput2.value = "";
    // fileInput2.files = null;
    resetBgPara("Ads");
    $('#adBtnImg').attr('src', "")
  }

  function resetTakeActionCTAModal() {
    $('#cta_take_action_modal_title').text('Add Take Action CTA');
    $('#ctaTakeActionTitle').val('');
    $('textarea#ctaTakeActionDesc').val('');
    $('textarea#ctaTakeActionDesc').css('height', '120px');
    $('#ctaTakeActionBtn').val('');
    $('#ctaTakeActionBtnLink').val('');
    $('#ctaTakeActionBtnTarget').prop('checked', false);
    $('#ctaTakeActionBtnRel').prop('checked', false);
    $(".closeMainCTAModal").click()
    resetBgPara("Take")
  }

  function resetBgPara(prefix) {
    $(`#cta${prefix}BgColor`).val('');
    $(`#cta${prefix}BgImgAlt`).val('');
    $(`#bg${prefix}CTAImg`).attr('src', "")
    const bgTypeTake = document.getElementById(`bgType${prefix}`);
    bgTypeTake.value = '0';
    bgTypeTake.dispatchEvent(new Event('change'));
  }

  function resetHelpCTAModal() {
    $('#cta_help_modal_title').text('Add Help CTA');
    $('#ctaHelpDesc').css('max-height', '120px');
    $('#ctaHelpDesc').css('overflow-x', 'scroll');
    ctaHelpQuill.root.innerHTML = '';
    $('#ctaHelpTitle').val('');
    $('#ctaHelpInfo').val('');
    resetBgPara("Help");
    $(".closeMainCTAModal").click()
  }

  function resetHeroCTAModal() {
    $('#cta_hero_modal_title').text('Add Hero CTA');
    $('#ctaHeroTitle').val('');
    $('#ctaHeroBtn').val('');
    $('#ctaHeroImgAlt').val('');
    const fileInput = document.getElementById("heroFile");
    fileInput.value = "";
    fileInput.files = null;
    $('#ctaHeroBtnLink').val('');
    $('#ctaHeroBtnTarget').prop('checked', false);
    $('#ctaHeroBtnRel').prop('checked', false);
    $('#heroFileBase64').attr('src', "")
    $('#heroFileBase64').closest('.dynamic-file-input').find('input[type="hidden"]').val("");
    resetBgPara("Hero");
    $(".closeMainCTAModal").click()

  }

  function resetGeneralCTAModal() {
    $('#cta_general_modal_title').text('Add General CTA');
    $('textarea#ctaGeneralDesc').css('height', '120px');
    $('#ctaGeneralTitle').val('');
    $('textarea#ctaGeneralDesc').val('');
    $('#ctaGeneralBtnName').val('');
    resetBgPara("General");
    $(".closeMainCTAModal").click()
  }

  function hideCTAModal() {
    editingCTAElement = null;
    editedContent = null;
    $('#closeTakeCTAModal').click();
  }

  function deleteElement(element) {
    $(element).closest('.sortable-row').remove();
  }
</script>
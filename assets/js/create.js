var quill,
  ads_description,
  ctaSuggestionQuill,
  ctaHelpQuill,
  ctaScrollableQuill,
  ctaMoreTemplateQuill,
  ctaProcessQuill = [],
  ctaHowToMakeQuill = [];
var imageCount = 0;
var content = [];
var formChanged = false;
var conditionRoutes = [
  "create_keyword",
  "edit_keyword",
  "create_cat",
  "edit_cat",
  "create_new_cat",
  "edit_new_cat",
];

$(document).ready(function () {
  window.setTimeout(function () {
    $(".alert")
      .fadeTo(500, 0)
      .slideUp(500, function () {
        $(this).remove();
      });
  }, 4000);
  $(":input, textarea").change(function () {
    formChanged = true;
  });

  $(document).on("change", "#template_link_target", function () {
    if (this.value == "loadmore_other_page") {
      $(".keyword_link_option").show();
    } else {
      $(".keyword_link_option").hide();
    }
  });

  // $(window).on('beforeunload', function () {
  //     if (formChanged) {
  //         return "You have unsaved changes. Are you sure you want to leave this page?";
  //     }
  // });

  $("#add-pages-form").submit(function () {
    formChanged = false;
  });

  var $pageSlugInput = $("#page_slug");

  $(
    "#contentModel,#cta_suggestion_modal,#cta_scrollable_modal, #addTemplateKeywordModel, #AdsModel, #linkModal, #marginModal"
  ).modal({
    backdrop: "static",
    keyboard: false,
  });

  // Function to generate slug
  function generateSlug(input) {
    // Replace special characters and trim the input
    // return input.replace(/[^a-z0-9-\s]/ig, '').trim().toLowerCase().replace(/\s+/g, '-');
    return input
      .replace(/[^a-z0-9\/\s-]/gi, "")
      .trim()
      .toLowerCase()
      .replace(/\s+/g, "-");
  }

  // Event listener for input field blur
  $pageSlugInput.on("blur", function () {
    var inputValue = $(this).val();
    var slug = generateSlug(inputValue);
    $(this).val(slug); // Update input value with generated slug
  });

  var id = $("#id").val();
  var contents = $("#content").val() ?? null;

  var removeStyleRegex = /(<a[^>]*)\s*style="[^"]*"/gi;

  if (contents !== null && contents !== "") {
    content = JSON.parse(contents);
    if (!Array.isArray(contents)) {
      content = $.map(content, function (value) {
        return value;
      });
    }

    content.forEach(function (item) {
      if (item.type === "content" && item.value && item.value.content) {
        item.value.content.forEach(function (contentItem) {
          if (contentItem.value) {
            contentItem.value = contentItem.value.replace(
              removeStyleRegex,
              "$1"
            ); // Remove all styles from <a> tags
          }
        });
      }
      if (item.type === "ads" && item.value && item.value.description) {
        item.value.description = item.value.description.replace(
          removeStyleRegex,
          "$1"
        );
      }
    });
    callContent(content);
  }

  // if (faq !== null && faq !== '') {
  //     faqs = JSON.parse(faq);
  //     // Loop through the parsed data and remove styles from <a> tags
  //     faqs.forEach(function (item) {
  //         if (item.answer) {
  //             item.answer = item.answer.replace(removeStyleRegex, '$1'); // Remove style attribute from <a> tags
  //         }
  //     });
  //     callFaqs(faqs);
  // }
});

$(document).ready(function () {
  let availableTags = (window.rawTags || []).map((item) => item.name);
  let keywordsInput = $("#keywords");

  if (keywordsInput.length && typeof keywordsInput.tagsinput === 'function') {
    keywordsInput.tagsinput();
    let $input = keywordsInput.tagsinput("input");

    if ($input && $input.length && typeof $input.autocomplete === 'function') {
      $input.autocomplete({
        appendTo: "body",
        minLength: 0,
        source: function (request, response) {
          let existing = keywordsInput.tagsinput("items");
          let results = $.ui.autocomplete.filter(availableTags, request.term);
          response(
            $.grep(results, function (tag) {
              return $.inArray(tag, existing) === -1;
            })
          );
        },
        select: function (event, ui) {
          $("#keywords").tagsinput("add", ui.item.value);
          $(this).val("");
          return false;
        },
        open: function () {
          let inputWidth = $input.outerWidth();
          $(".ui-autocomplete").css({
            width: inputWidth / 2 + "px",
            left: $input.offset().left + "px",
            top: $input.offset().top + $input.outerHeight() + "px",
            zIndex: 9999,
          });
        },
      });
    }
  }
  $input.on("focus", function () {
    $(this).autocomplete("search", "");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  let Link = Quill.import("formats/link");

  class CustomLink extends Link {
    static create(value) {
      let node = super.create(value);
      if (value && value.target) {
        node.setAttribute("href", value.href);
        if (value.target == "_self" || value.target == "_blank") {
          node.setAttribute("target", value.target);
        } else {
          node.setAttribute("target", "_blank");
        }
        if (value.nofollow) {
          node.setAttribute("rel", "nofollow");
        } else {
          node.setAttribute("rel", "dofollow");
        }
        return node;
      } else {
        node.setAttribute("href", value);
        return node;
      }
    }
  }

  Quill.register("formats/link", CustomLink, true);

  // Define the toolbar options including the target attribute dropdown
  // let toolbarOptions = {
  //     container: [
  //         ["undo", "redo"],
  //         ["bold", "italic"],
  //         [{ 'color': [] }],
  //         [{ 'link': 'link' }],  // Use 'link' as the identifier for the link button
  //         [{ 'target': '_self' }, { 'target': '_blank' }] // Target dropdown menu
  //     ],
  //     handlers: {
  //         'link': function (value) {
  //             if (value) {
  //                 $(".linkUrlError").text('');
  //                 $('#linkModal').modal('show');
  //                 $('#linkUrl').val('');
  //                 $('#openInNewTab').prop('checked', false);
  //                 $('#nofollow').prop('checked', false);

  //                 // Define the event listener function
  //                 var insertLinkHandler = function () {
  //                     insertLink(this.quill);
  //                     // Remove the event listener after execution
  //                     document.getElementById('insertLink').removeEventListener('click', insertLinkHandler);
  //                 }.bind(this);

  //                 // Add the event listener
  //                 document.getElementById('insertLink').addEventListener('click', insertLinkHandler);
  //             }
  //         },
  //         'target': function (value) {
  //             this.quill.format('link', { target: value });
  //         }
  //     }
  // };

  // // Initialize Quill editors with customized toolbar

  // ads_description = new Quill("#ads_description", {
  //     theme: "snow",
  //     modules: {
  //         toolbar: toolbarOptions,
  //         history: {
  //             userOnly: true,
  //             maxStack: 500
  //         },
  //     },
  // });

  // ads_description.on('text-change', function (delta, oldDelta, source) {
  //     if (source === 'user') {
  //         formChanged = true;
  //         // User made changes
  //     }
  // });

  // answer = new Quill("#answer", {
  //     theme: "snow",
  //     modules: {
  //         toolbar: toolbarOptions,
  //         history: {
  //             userOnly: true,
  //             maxStack: 500
  //         },
  //     },
  // });
  // answer.on('text-change', function (delta, oldDelta, source) {
  //     if (source === 'user') {
  //         formChanged = true;
  //         // User made changes
  //     }
  // });

  // Update undo and redo icons
  $(".ql-undo").html('<i class="fa-solid fa-rotate-left"></i>');
  $(".ql-redo").html('<i class="fa-solid fa-rotate-right"></i>');
});

function insertLink(quillInstance) {
  var href = $("#linkUrl").val();
  var openInNewTab = $("#openInNewTab").prop("checked");
  var noFollow = $("#nofollow").prop("checked");
  var target = openInNewTab ? "_blank" : "_self";

  if (href) {
    $(".linkUrlError").text("");
    sessionStorage.setItem("href", href);
    quillInstance.format("link", {
      href: href,
      target: target,
      nofollow: noFollow,
    });

    // Close the modal
    $("#linkModal").modal("hide");
  } else {
    var href = $(".linkUrlError").text("Please Add Link Here");
  }
}

function openContentDialog() {
  $("#add_content_model").modal("show");
}

$(".openContentModel").on("click", function (event) {
  event.preventDefault();
  if (event.type === "keydown" && event.key === "Enter") {
    return;
  }
  $("#add_content_model").modal("hide");
  $(".save-tag").attr("data-target", "");
  $(".save-tag").attr("data-tag-id", "");
  $("#content_modal").attr("data-open-id", "");
  $("#content_modal").modal("show");
});
$(".adsModel").on("click", function (event) {
  event.preventDefault();
  $("#add_content_model").modal("hide");
  $(".btn-ads-save").attr("data-target", "");
  $(".btn-ads-save").attr("data-id", "");
  blank_ads_form();
});

var addButton = document.getElementById("addButton");
if (addButton) {
  addButton.addEventListener("click", () => {
    $("#add_content_model").modal("hide");
    onModalOpen();
    registerCTAQuill();
  });
}

$(document).on("click", ".content-type", function (event) {
  event.preventDefault();
  var type = $(this).data("type");
  $("#contentModel .content-title").text(type);
  $("#content_modal").modal("hide");

  var open_id = $("#content_modal").attr("data-open-id");

  var isButtonKeyExists = false;
  var isImageOrVideoExists = false;
  var ish2Exists = false;
  if (open_id !== "" && open_id !== undefined) {
    let contentValue = content[open_id].value;
    isButtonKeyExists =
      contentValue.hasOwnProperty("button") && type === "button";
    isImageOrVideoExists =
      (contentValue.hasOwnProperty("images") ||
        contentValue.hasOwnProperty("video")) &&
      (type === "images" || type === "video");
    ish2Exists = contentValue.hasOwnProperty("h2") && type === "h2";
  }

  if (isButtonKeyExists) {
    alert("Button tag is already added");
  } else if (isImageOrVideoExists) {
    alert("Image or video is already added");
  } else if (ish2Exists) {
    alert("H2 Tag already added.");
  } else {
    $("#content_modal").modal("hide");
    $("#contentModel").modal("show");
    if (type === "button") {
      $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="col-md-12">
                    <label for="button_name" class="form-label">Button</label>
                    <input type="text" id="button_name" name="button_name" class="form-control">
                    <span class="text-danger button_name_error"></span>
                </div>
                <div class="col-md-12 mt-3">
                    <label for="button_link" class="form-label">Button Link</label>
                    <input type="url" id="content_button_link" name="button_link" class="form-control">
                    <span class="text-danger button_link_error"></span>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnOpenInNewTab">
                    <label class="form-check-label" for="contentBtnOpenInNewTab">Open in new tab</label>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnNofollow">
                    <label class="form-check-label" for="contentBtnNofollow">Add rel="nofollow"</label>
                </div>
            `);
    } else if (type === "images") {
      $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="selected-images">
                    <div class="col-md-12">
                        <div class="mb-2">Image <span class="text-danger">(Size: 500*513)</span></div>
                        <label for="image_upload" class="custom-file-upload">
                            <i class="fas fa-plus"></i> Upload Image
                        </label>
                        <input type="file" id="image_upload" accept=".jpg, .jpeg, .webp, .svg" onchange="validateImage(this)" class="form-control" style="display: none;">
                    </div>
                    <div class="selected-image-preview"></div>
                    <span class="text-danger image_error"></span>
                </div>
            `);
    } else if (type === "video") {
      $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="selected-videos">
                    <div class="col-md-12">
                        <div class="mb-2">Video <span class="text-danger">(Size: 500*513)</span></div>
                        <label for="video_upload" class="custom-file-upload">
                            <i class="fas fa-plus"></i> Upload Video
                        </label>
                        <input type="file" id="video_upload" name="video_upload" accept="video/*" class="form-control" style="display: none;">
                    </div>
                    <div class="selected-video-preview"></div>
                    <span class="text-danger video_error"></span>
                </div>
            `);
    } else if (type === "h2") {
      $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="col-md-12">
                    <label for="description_type" class="form-label">Description</label>
                    <div id="description_type"></div>
                    <span class="text-danger description-error"></span>
                </div>
            `);
      tinyEditorInit(type);
    } else {
      $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="col-md-12">
                    <label for="description_type" class="form-label">Description</label>
                    <div id="description_type"></div>
                    <span class="text-danger description-error"></span>
                </div>
            `);
      tinyEditorInit(type);
    }
  }
});

$(document).on("click", "button.btn.btn-danger.removeBannerBtn", function () {
  $("div#banner-image-video-preview-div").html("");
  $("div#banner-image-video-preview-div").html(
    "<input type='hidden' name='remove_banner' value='1'>"
  );
  $("#banner").val("");
});

function isValidURL(url) {
  try {
    new URL(url);
    return true;
  } catch (error) {
    return false;
  }
}


function addOrUpdateCTA(ctaData, ctaValue, modalkeyname) {
  if (editingCTAElement) {
    const ctaJsonString = btoa(
      unescape(encodeURIComponent(JSON.stringify(ctaData)))
    );
    $(editingCTAElement)
      .closest(".sortable-row")
      .find('input[id="cta_data[]"]')
      .val(ctaJsonString);
    const ctaKey = ctaData.value;
    const ctaDetails = ctaData;
    $(editingCTAElement)
      .closest(".sortable-row")
      .find("p")
      .val(ctaDetails.name);
    const index = 0;
    content.forEach((input, index) => {
      if (input.id === selectedID) {
        content[index] = {
          type: ctaKey,
          value: ctaData,
          id: selectedID,
        };
        callContent(content);
      }
    });
  } else {
    const ctaKey = ctaData.value;
    var addData = {
      type: ctaKey,
      value: ctaData,
      id: Math.random().toString(36).substr(2, 9),
    };
    content.push(addData);
    callContent(content);
  }
  editingCTAElement = null;
  editedContent = null;
  selectedID = null;
}

function setBgPara(prefix, ctaDetails) {
  const bgTypeTake = document.getElementById(`bgType${prefix}`);
  bgTypeTake.value =
    ctaDetails.bg && ctaDetails.bg.value ? ctaDetails.bg.value : 0;
  bgTypeTake.dispatchEvent(new Event("change"));
  if (bgTypeTake.value == 1) {
    $(`#cta${prefix}BgColor`).val(ctaDetails.bg.color);
  } else if (bgTypeTake.value == 2) {
    // $(`#bg${prefix}CTAImg`).attr('src', getStorageLink(ctaDetails.bg.src));
    $(`#bg${prefix}File`).attr("data-value", getStorageLink(ctaDetails.bg.src));
    $(`#cta${prefix}BgImgAlt`).val(ctaDetails.bg.alt);
  }
}

async function editCTAModal(element) {
  editingCTAElement = element;
  const inputField = $(editingCTAElement)
    .closest(".sortable-row")
    .find('input[id="cta_data[]"]');
  ctaJsonString = inputField.val();
  if (!ctaJsonString || !ctaJsonString.trim()) {
    return;
  }
  // const ctaData = JSON.parse(new TextDecoder("utf-8").decode(Uint8Array.from(atob(ctaJsonString), c => c.charCodeAt(0))));
  const ctaData = JSON.parse(decodeURIComponent(escape(atob(ctaJsonString))));
  const ctaKey = ctaData.value;
  selectedID = inputField.attr("data-cta-id");
  content.forEach((input, index) => {
    if (input.type === ctaKey) {
      editedContent = content[index].value;
    }
  });
  const ctaDetails = ctaData;
  if (ctaKey == "cta_take_action") {
    resetTakeActionCTAModal();
    $("#ctaTakeActionTitle").val(ctaDetails.title);
    $("#ctaTakeBgColor").val(ctaDetails.bgColor ?? "");
    $("textarea#ctaTakeActionDesc").val(ctaDetails.desc);
    $("textarea#ctaTakeActionDesc").css("height", "120px");
    $("#ctaTakeActionBtn").val(ctaDetails.button.name);
    $("#ctaTakeActionBtnLink").val(ctaDetails.button.link);
    $("#ctaTakeActionBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaTakeActionBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    setBgPara("Take", ctaDetails);
  } else if (ctaKey == "cta_convert") {
    resetConvertCTAModal();
    const ctaImgDetails = ctaDetails["image"];
    $("#ctaConvertTitle").val(ctaDetails.title);
    $("textarea#ctaConvertDesc").val(ctaDetails.desc).css("height", "120px");
    $("#ctaConvertBtn").val(ctaDetails.button.name);
    $("#ctaConvertBtnLink").val(ctaDetails.button.link);
    $("#convertFileBase64").val(ctaDetails.image.src);
    $("#ctaConvertImgAlt").val(ctaDetails.image.alt);
    $("#ctaConvertBtnTarget").prop("checked", ctaDetails.button.target === 1);
    $("#ctaConvertBtnRel").prop("checked", ctaDetails.button.rel === 1);
    $("#convertFile").attr("data-value", getStorageLink(ctaDetails.image.src));
    setBgPara("Convert", ctaDetails);
  } else if (ctaKey == "cta_help") {
    resetHelpCTAModal();
    $("#ctaHelpTitle").val(ctaDetails.title);
    ctaHelpQuill.root.innerHTML = ctaDetails.desc;
    $("#ctaHelpDesc").css("max-height", "120px");
    $("#ctaHelpDesc").css("overflow-x", "scroll");
    $("#ctaHelpInfo").val(ctaDetails.info);
    setBgPara("Help", ctaDetails);
  } else if (ctaKey == "cta_general") {
    resetGeneralCTAModal();
    $("#ctaGeneralTitle").val(ctaDetails.title);
    $("textarea#ctaGeneralDesc").val(ctaDetails.desc);
    $("textarea#ctaGeneralDesc").css("height", "120px");
    $("#ctaGeneralBtnName").val(ctaDetails.button.name);
    $("#ctaGeneralBtnLink").val(ctaDetails.button.link);
    $("#ctaGeneralBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaGeneralBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    setBgPara("General", ctaDetails);
  } else if (ctaKey == "cta_ads") {
    resetAdCtaModal();
    $("#ctaAdTitle").val(ctaDetails.title);
    $("textarea#ctaAdDesc").val(ctaDetails.desc);
    $("textarea#ctaAdDesc").css("height", "120px");
    $("#ctaAdBtnLink").val(ctaDetails.button.link);
    $("#ctaAdBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaAdBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    $("#ctaAdImgAlt").val(ctaDetails.image.alt);
    $("#ctaAdBtnImgAlt").val(ctaDetails.button.alt);
    $("#adFile").attr("data-value", getStorageLink(ctaDetails.image.src));
    $("#ctaAdBtnFile").attr(
      "data-value",
      getStorageLink(ctaDetails.button.src)
    );
    setBgPara("Ads", ctaDetails);
  } else if (ctaKey == "cta_hero") {
    resetHeroCTAModal();
    $("#ctaHeroTitle").val(ctaDetails.title);
    $("#ctaHeroBtn").val(ctaDetails.button.name);
    $("#ctaHeroBtnLink").val(ctaDetails.button.link);
    $("#ctaHeroImgAlt").val(ctaDetails.image.alt);
    $("#heroFile").attr("data-value", getStorageLink(ctaDetails.image.src));
    $("#ctaHeroBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaHeroBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    setBgPara("Hero", ctaDetails);
  } else if (ctaKey == "cta_more_template") {
    registerCTAQuill();
    resetMoreTemplateCTAModal();
    const virtualType = ctaDetails.virtualType;
    $("#virtualType").val(virtualType);
    $("#urlContainer").css("display", virtualType == "url" ? "block" : "none");
    $("#dataContainer").css("display", virtualType == "url" ? "none" : "block");
    if (virtualType == "url") {
      $("#ctaMoreTemplateVirtualSlug").val(ctaDetails.slug);
    } else {
      $("#ctaMoreTemplateTitle").val(ctaDetails.title);
      // Update character counter for title
      const titleInput = document.getElementById('ctaMoreTemplateTitle');
      if (titleInput && window.updateCTATitleCount) {
        window.updateCTATitleCount(titleInput);
      }
      ctaMoreTemplateQuill.root.innerHTML = ctaDetails.desc;
      $("#moreTemplateVirtualContainer #generatedQuery").val(ctaDetails.query);
      await processConditions(ctaDetails.query.split(" && "));
    }
    setBgPara("MoreTemplate", ctaDetails);
  } else if (ctaKey == "cta_scrollable") {
    registerCTAQuill();
    resetScrollableCTAModal();
    $("#scrollableImageContainer").empty();
    $("#ctaScrollableTitle").val(ctaDetails.title);
    ctaScrollableQuill.root.innerHTML = ctaDetails.desc;
    $("#ctaScrollableBtn").val(ctaDetails.button.name);
    $("#ctaScrollableBtnLink").val(ctaDetails.button.link);
    $("#ctaScrollableBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaScrollableBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    $("#scrollableVirtualContainer #generatedQuery").val(ctaDetails.query);
    if (Array.isArray(ctaDetails.images)) {
      ctaDetails.images.forEach((value) => {
        addImageField(
          value.src,
          value.alt,
          value.target,
          value.rel,
          value.link
        );
      });
    }
    setBgPara("Scrollable", ctaDetails);
  } else if (ctaKey === "cta_how_to_make") {
    resetHowToMakeCTAModal();
    $("#ctaHowToMakeContainer").empty();
    $("#ctaHowToMakeTitle").val(ctaDetails.title);
    $("textarea#ctaHowToMakeDesc").val(ctaDetails.desc);
    $("textarea#ctaHowToMakeDesc").css("height", "120px");
    if (Array.isArray(ctaDetails.stepsection)) {
      ctaDetails.stepsection.forEach((step) => {
        addImageFieldWithDetails(
          "ctaHowToMakeContainer",
          step.image.src,
          step.title,
          step.desc,
          step.image.alt
        );
      });
    }
    setBgPara("HowToMake", ctaDetails);
  } else if (ctaKey == "cta_process") {
    resetProcessCTAModal();
    $("#ctaProcessContainer").empty();
    $("#ctaProcessTitle").val(ctaDetails.title);
    $("textarea#ctaProcessDesc").val(ctaDetails.desc);
    $("textarea#ctaProcessDesc").css("height", "120px");
    $("#processImgPosition").val(ctaDetails.imgposition);
    $("#processStepPosition").val(ctaDetails.stepposition);
    if (Array.isArray(ctaDetails.stepsection)) {
      ctaDetails.stepsection.forEach((step) => {
        addImageFieldWithDetails(
          "ctaProcessContainer",
          step.image.src,
          step.title,
          step.desc,
          step.image.alt
        );
      });
    }
    setBgPara("Process", ctaDetails);
    registerCTAQuill();
  } else if (ctaKey == "cta_feature") {
    resetFeatureCTAModal();
    $("#ctaFeatureTitle").val(ctaDetails.title);
    $("textarea#ctaFeatureDesc").val(ctaDetails.desc);
    $("textarea#ctaFeatureDesc").css("height", "120px");
    $("#featureImgPosition").val(ctaDetails.imgposition);
    $("#featureInfoFileBase64").val(ctaDetails.image.alt);
    $("#heroFile").attr("data-value", getStorageLink(ctaDetails.image.src));
    if (Array.isArray(ctaDetails.buttons)) {
      ctaDetails.buttons.forEach((btn) => {
        addButtonField(
          "ctaFeatureContainer",
          btn.name,
          btn.link,
          btn.rel,
          btn.target
        );
      });
    }
    setBgPara("Feature", ctaDetails);
  } else if (ctaKey == "cta_suggestion") {
    registerCTAQuill();
    resetSuggestionCTAModal();
    $("#ctaSuggestionTitle").val(ctaDetails.title);
    $("#suggestionTitlePosition").val(ctaDetails.titleposition);
    $("#ctaSuggestionDesc").css("max-height", "120px");
    $("#ctaSuggestionDesc").css("overflow-x", "scroll");
    ctaSuggestionQuill.root.innerHTML = ctaDetails.desc;
    setBgPara("Suggestion", ctaDetails);
  } else if (ctaKey == "cta_multiplebtn") {
    resetMultipleBtnCTAModal();
    $("#ctaMultipleBtnTitle").val(ctaDetails.title);
    $("textarea#ctaMultipleBtnDesc").val(ctaDetails.desc);
    $("textarea#ctaMultipleBtnDesc").css("height", "120px");
    $("#multipleBtnImgPosition").val(ctaDetails.imgposition);
    if (Array.isArray(ctaDetails.stepsection)) {
      ctaDetails.stepsection.forEach((step) => {
        addButtonFieldWithImage(
          "ctaMultipleBtnContainer",
          step.btn.name,
          step.btn.link,
          step.btn.target,
          step.btn.rel,
          step.image.src,
          step.image.alt
        );
      });
    }
    setBgPara("MultipleBtn", ctaDetails);
  } else if (ctaKey == "cta_offer") {
    resetOfferCTAModal();
    $("#ctaOfferTitle").val(ctaDetails.title);
    $("#ctaOfferDesc").val(ctaDetails.desc);
    $("#ctaOfferBtn").val(ctaDetails.button.name);
    $("#ctaOfferBtnLink").val(ctaDetails.button.link);
    $("#ctaOfferBtnTarget").prop(
      "checked",
      ctaDetails.button.target == 1 ? true : false
    );
    $("#ctaOfferBtnRel").prop(
      "checked",
      ctaDetails.button.rel == 1 ? true : false
    );
    setBgPara("Offer", ctaDetails);
  }
  dynamicFileCmp();
  openCTAModal(ctaKey + "_btn", ctaKey);
}

async function fetchOptions(table, nameColumn, idColumn, value, database = null) {
  try {
    // Use absolute path from root to avoid URL issues
    let url = `${APP_BASE_URL}/get-dependent-value/${table}/${nameColumn}/${idColumn}/${value}`;
    if (database) {
      url += `/${database}`;
    }
    let response = await fetch(url);
    let data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching options:', error);
    return [];
  }
}

async function processConditions(conditions) {
  const moreTemplateVirtualContainer = document.getElementById(
    "moreTemplateVirtualContainer"
  );
  for (const condition of conditions) {
    let parts = condition.match(
      /^\s*([\w.]+)?\s*(=|!=|>=|<=|>|<|LIKE|NOT LIKE|IN|NOT IN|BETWEEN|NOT BETWEEN|IS NULL|IS NOT NULL|REGEXP|NOT REGEXP|RANGE|SORT|LIMIT)?\s*(.*)$/i
    );
    if (!parts) continue;
    let column = parts[1] ? parts[1].trim() : "";
    let operator = parts[2] ? parts[2].trim() : "";
    let value = parts[3] ? parts[3].trim() : "";

    if (operator == "LIMIT") {
      secondValue = value;
      setValueInTable(
        "limit",
        "limit",
        operator,
        value,
        secondValue,
        null,
        moreTemplateVirtualContainer
      );
    } else if (operator == "SORT") {
      const sortObject = sorting.find((col) => col.column_name === column);
      secondValue = value == "asc" ? "Ascending" : "Descending";
      setValueInTable(
        sortObject.column,
        column,
        operator,
        value,
        secondValue,
        null,
        moreTemplateVirtualContainer
      );
    } else {
      const columnObject = columns.find((col) => col.column_name === column);
      let secondValue = "";

      if (["IS NULL", "IS NOT NULL"].includes(operator)) {
        secondValue = "Null";
      } else if (columnObject.type == "boolean") {
        secondValue = value == 0 ? false : true;
      } else if (
        ["IN", "NOT IN", "=", "!="].includes(operator) &&
        columnObject.type !== "number"
      ) {
        const isMultiple = columnObject.isMultiple;
        const processValue = value.replace(/[\[\]()']/g, "").split(",");

        let resolvedValues = [];
        for (const pValue of processValue) {
          const dynamicValue = await fetchOptions(
            columnObject.table_name,
            columnObject.dependent_column_name,
            columnObject.dependent_column_id,
            pValue
          );
          resolvedValues.push(dynamicValue);
        }

        secondValue = resolvedValues.join(",");

        if (["IN", "NOT IN"].includes(operator)) {
          secondValue = isMultiple ? `[${secondValue}]` : `(${secondValue})`;
        }
      } else {
        secondValue = value;
      }

      setValueInTable(
        columnObject.column,
        column,
        operator,
        value,
        secondValue,
        null,
        moreTemplateVirtualContainer
      );
    }
  }
}


$(document).on("click", ".btn-ads-save", function (event) {
  event.preventDefault();
  var title = $("#ads_title_value").val();
  var colors = $("#ads_colors").val();
  var button = $("#ads_button_value").val();
  var btn_link = $("#ads_button_link").val();
  var desc = ads_description.root.innerHTML;
  var image = $("#ads_image_link").val();
  var adsopenInNewTab = $("#adsopenInNewTab").prop("checked") ? 1 : 0;
  var adsnofollow = $("#adsnofollow").prop("checked") ? 1 : 0;
  var dataId = $(this).attr("data-id");

  if (title.trim() == "")
    $(".ads_title_value_error").text("Please fill this field");
  else $(".ads_title_value_error").text("");

  if (colors.trim() == "")
    $(".ads_colors_error").text("Please fill this field");
  else $(".ads_colors_error").text("");

  if (button.trim() == "")
    $(".ads_button_error").text("Please fill this field");
  else $(".ads_button_error").text("");

  if (btn_link.trim() == "")
    $(".ads_button_link_error").text("Please fill this field");
  else if (!isValidURL(btn_link.trim()))
    $(".ads_button_link_error").text("Invalid Url.");
  else $(".ads_button_link_error").text("");

  if (ads_description.getText().trim() == "")
    $(".ads_description_error").text("Please fill this field");
  else $(".ads_description_error").text("");

  if (image.trim() == "") $(".ads_image_error").text("Please fill this field");
  else $(".ads_image_error").text("");

  if (
    title.trim() == "" ||
    colors.trim() == "" ||
    button.trim() == "" ||
    btn_link.trim() == "" ||
    !isValidURL(btn_link.trim()) ||
    ads_description.getText().trim() == "" ||
    image.trim() == ""
  ) {
    return;
  }
  formChanged = true;

  var addData = {
    type: "ads",
    value: {
      title: title,
      description: desc,
      color: colors,
      image: image,
      button: button,
      button_link: btn_link,
      button_target: adsopenInNewTab,
      button_rel: adsnofollow,
    },
  };
  if ($(this).attr("data-target") === "edit") {
    content[dataId] = addData;
  } else {
    content.push(addData);
  }

  blank_ads_form();
  callContent(content);
  $("#AdsModel").modal("hide");
});

$(document).on("click", ".btn-content-save", function (event) {
  event.preventDefault();
  var type = $("#contentType").val();
  if (type == "button") {
    if ($("#button_name").val().trim() == "") {
      $(".button_name_error").text("Please fill this field.");
    } else {
      $(".button_name_error").text("");
    }
    if ($("#content_button_link").val().trim() == "") {
      $(".button_link_error").text("Please fill this field.");
    } else if (!isValidURL($("#content_button_link").val().trim())) {
      $(".button_link_error").text("Invalid Url.");
    } else {
      $(".button_link_error").text("");
    }

    if (
      $("#button_name").val().trim() == "" ||
      $("#content_button_link").val().trim() == "" ||
      !isValidURL($("#content_button_link").val().trim())
    ) {
      return;
    }
  } else if (type == "images") {
    if ($(".image_url").val() == undefined) {
      $(".image_error").text("Please fill this field.");
      return;
    } else {
      $(".image_error").text("");
    }
  } else if (type == "video") {
    if ($(".video_url").val() == undefined) {
      $(".video_error").text("Please fill this field.");
      return;
    } else {
      $(".video_error").text("");
    }
  } else {
    if (quill.getText().trim() == "") {
      $(".description-error").text("Please fill this field.");
      return;
    } else {
      $(".description-error").text("");
    }
  }
  var dataId = $(this).attr("data-tag-id");
  var dataIndex = $(this).attr("data-index");
  formChanged = true;

  if (dataId == "" || dataId == undefined) {
    var index = content.length; // Determine the index based on the length of the outer array
  } else {
    var index = dataId;
  }

  // Create a nested array if it doesn't exist at the determined index
  if (!content[index]) {
    content[index] = {
      type: "content",
      value: [],
    };
    var dataIndex = $(this).attr("data-index");
  }

  var addData = {};

  if (type == "button") {
    var value = $("#button_name").val();
    var btn_link = $("#content_button_link").val();
    var openinnewtab = $("#contentBtnOpenInNewTab").prop("checked") ? 1 : 0;
    var nofollow = $("#contentBtnNofollow").prop("checked") ? 1 : 0;
    addData.button = {
      link: btn_link,
      value: value,
      openinnewtab: openinnewtab,
      nofollow: nofollow,
    };
  } else if (type == "images") {
    var image_url = $(".image_url").val();
    var image_alt_name = $(".image_alt_name").val();
    addData.images = {
      alt: image_alt_name,
      link: image_url,
    };
  } else if (type == "video") {
    var video_url = $(".video_url").val();
    var video_alt_name = $(".video_alt_name").val();
    addData.video = {
      link: video_url,
      alt: video_alt_name,
    };
  } else if (type == "h2") {
    addData.h2 = quill.root.innerHTML;
  } else {
    var desc = quill.root.innerHTML;
    addData = {
      key: type,
      value: desc,
    };
  }

  if ($(this).attr("data-target") === "edit") {
    if (addData.images || addData.video || addData.button || addData.h2) {
      if (addData.images) {
        delete content[dataId].value["images"];
      } else if (addData.video) {
        delete content[dataId].value["video"];
      } else if (addData.button) {
        delete content[dataId].value["button"];
      } else if (addData.h2) {
        delete content[dataId].value["h2"];
      }
      Object.assign(content[dataId].value, addData);
    } else {
      var contentEditId = $(this).attr("data-content-id");
      content[dataId].value[dataIndex][contentEditId] = addData;
    }
  } else {
    if (addData.images) {
      if (content[index].value["video"]) {
        delete content[index].value["video"];
      }
      content[index].value["images"] = addData.images;
    } else if (addData.video) {
      if (content[index].value["images"]) {
        delete content[index].value["images"];
      }
      content[index].value["video"] = addData.video;
    } else if (addData.button) {
      content[index].value["button"] = addData.button;
    } else if (addData.h2) {
      content[index].value["h2"] = addData.h2;
    } else {
      if (!content[index].value.hasOwnProperty("content")) {
        content[index].value["content"] = [];
      }
      content[index].value["content"].push(addData);
    }
  }

  // Specify the order you want the keys to be in
  const order = ["images", "video", "button", "h2", "content"];

  // Iterate over the content array and reorder the keys within each item's value
  content.forEach((item) => {
    if (item.type === "content" && item.value) {
      item.value = reorderKeys(item.value, order);
    }
  });
  callContent(content);
});

// Function to reorder the keys of an object
function reorderKeys(obj, order) {
  const ordered = {};
  order.forEach((key) => {
    if (obj.hasOwnProperty(key)) {
      ordered[key] = obj[key];
    }
  });
  return { ...ordered, ...obj };
}

$(document).ready(function () {
  var id = $("#id").val();
});

function callContent(content, fieldRequired = true) {
  var data = "";
  content.forEach((ele, index) => {
    data += `<div class="mb-3 p-2 border border-2 sortable_content_list content_show_${index}" data-id="${index}"><div class="d-flex"><div class="drag-handle">&#9776;</div></div>`;
    if (ele.type == "api") {
      data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3">
                        <div class="col-md-1 d-flex">Templete</div>
                        <div class="col-md-11">
                            <div class="content-box">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-2">Title : </div><div class="col-md-10"> ${ele.value.title ?? ""
        }</div>
                                    <div class="col-md-12"> ${ele.value.description ?? ""
        }</div>`;
      if (
        ele.value.keyword &&
        ele.value.only_video &&
        ele.value.keyword_target
      ) {
        data += `<div class="col-md-2">Keyword : </div><div class="col-md-10"> ${ele.value.keyword
          }</div>
                        <div class="col-md-2">Only Video : </div><div class="col-md-10"> ${ele.value.only_video == "1" ||
            ele.value.only_video == 1
            ? "True"
            : "False"
          }</div>
                        <div class="col-md-2">Keyword target : </div><div class="col-md-10"> ${ele.value.keyword_target == "loadmore_here"
            ? "Loadmore here"
            : "Loadmore other page"
          }</div>`;
        if (ele.value.keyword_target == "loadmore_other_page") {
          data += `<div class="col-md-2">Keyword link : </div> <div class="col-md-10"><a href="${ele.value.keyword_link
            }" target="${ele.value.link_target == 1 ? "_blank" : "_self"}" rel="${ele.value.link_rel == 1 ? "nofollow" : "dofollow"
            }"> link</a></div>`;
        }
      }

      data += `</div>
                            </div>  
                        </div>
                    </div>
                    <div class="mt-3">
                        ${!conditionRoutes.includes(currentRoute)
          ? `<button type="button" class="btn btn-success edit-template" data-id="${index}">Edit Keyword</button>`
          : ""
        }
                        
                        <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">delete</button>
                    </div>
                </div>`;
    } else if (ele.type == "api_virtual") {
      data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3">
                        <div class="col-md-1 d-flex">Virtual Template</div>
                        <div class="col-md-11">
                            <div class="content-box">
                                <div class="row mb-3 align-items-center">`;

      if (ele.value.virtualType === 'url') {
        data += `<div class="col-md-2">Type : </div><div class="col-md-10">URL</div>
                 <div class="col-md-2">Slug : </div><div class="col-md-10">${ele.value.slug ?? ""}</div>`;
      } else {
        data += `<div class="col-md-2">Type : </div><div class="col-md-10">Data</div>
                 <div class="col-md-2">Title : </div><div class="col-md-10">${ele.value.title ?? ""}</div>
                 <div class="col-md-12">${ele.value.desc ?? ""}</div>
                 <div style="display: none;">Query : ${ele.value.query ?? ""}</div>`;
      }

      data += `</div>
                            </div>  
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success edit-virtual-template" data-id="${index}">Edit Virtual Template</button>
                        <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">delete</button>
                    </div>
                </div>`;
    } else if (ele.type.startsWith("cta")) {
      // const ctaJsonString = btoa(decodeURIComponent(encodeURIComponent(JSON.stringify(ele.value))));
      // const ctaJsonString = btoa(String.fromCharCode(...new TextEncoder().encode(JSON.stringify(ele.value))));

      const ctaJsonString = btoa(
        unescape(encodeURIComponent(JSON.stringify(ele.value)))
      );
      const ctaKey = ele.type;
      const ctaDetails = ele.value;
      //         data += `
      //   <div class="sortable-row bg-light sortable_content_data" style="display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;">
      //     <input class="form-control" type="hidden" id="cta_data[]" value='${ctaJsonString}'/>
      //     <input class="form-control" type="textname" id="cta_id[]" value="${ctaDetails.value}" style="display: none;"/>
      //   <input class="form-control" type="textname" id="cta_name[]" value="${ctaDetails.name}" style="display: none;"/>
      //     <p style="color: black;">${ctaDetails.name}</p>
      //     <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" data-id="${index}" onclick="editCTAModal(this)"><i class="dw dw-edit2"></i></button>
      //     <button type="button" class="dropdown-item" style="padding: 0; width: 30px; height: 30px; display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;" onclick="deleteContent(${index})"><i class="dw dw-delete-3"></i></button>
      //   </div>`;
      // <div class="col-md-1 d-flex">CTA</div>
      data += `<div class="sortable-row">
            <div class="d-flex align-items-center  bg-light mb-2 py-2 px-3">
                    <div class="col-md-2">
                        <div>
                            <div class="bg-light " style="display: flex; flex-direction: row; gap: 5px; justify-content: center; align-items: center;">
                                <input class="form-control" type="hidden" data-cta-id='${ele.id}' id="cta_data[]" value='${ctaJsonString}'/>
                                <input class="form-control" type="textname" id="cta_id[]" value="${ctaDetails.value}" style="display: none;"/>
                                <input class="form-control" type="textname" id="cta_name[]" value="${ctaDetails.name}" style="display: none;"/>
                                <p style="color: black;">${ctaDetails.name}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                    <button type="button" class="btn btn-success" data-id="${index}" onclick="editCTAModal(this)">Edit CTA</button>
                    <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">Remove CTA</button>
                    </div>
                </div>
                </div>`;

      // $('#cta_section').append(html);
    } else if (ele.type == "ads") {
      data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3">
                    <div class="col-md-1 d-flex">Ads</div>
                    <div class="col-md-11">
                        <div class="content-box">
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Title:</div>
                                <div class="col-md-11">${ele.value.title}</div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-12">${ele.value.description
        }</div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Color:</div>
                                <div class="col-md-11"><span class="ads-color-box" style="background:${ele.value.color
        }"></span></div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Image:</div>
                                <div class="col-md-11"><img src="${ele.value.image
        }" class="image-check" OnError="this.remove()" alt="Placeholder Image" width="100" height="100"></div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Button:</div>
                                <div class="col-md-11"><a href="${ele.value.button_link
        }" target="${ele.value.button_target ? "_blank" : "_self"
        }" rel="${ele.value.button_rel ? "nofollow" : "dofollow"
        }" class="btn btn-info text-white">${ele.value.button}</a></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success edit-ads" data-id="${index}">Edit Ads</button>
                    <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">Remove Ads</button>
                </div>`;
    } else {
      var i = 0;
      Object.entries(ele.value).forEach(([key, pageData]) => {
        if (key == "button") {
          data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex"> ${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10"><a href="${pageData.link
            }" class="btn btn-info" target="${pageData.openinnewtab == 1 ? "_blank" : "_self"
            }" rel="${pageData.nofollow == 1 ? "nofollow" : "dofollow"}">${pageData.value
            }</a></div>
                            </div>`;
        } else if (key == "images") {
          data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex"> ${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10">`;
          data += `<img src="${getStorageLink(pageData.link)}" alt="${pageData.alt
            }" width="70" height="70" class="me-2 image-check" OnError="this.remove()">`;
          data += `</div>
                            </div>`;
        } else if (key == "video") {
          data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex"> ${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10">`;
          data += `<video src="${pageData.link}" controls class="me-2 video-check" alt="${pageData.alt}" width="320" height="240" OnError="this.remove()">
                                Your browser does not support the video tag.
                            </video>
                            `;
          data += `</div>
                            </div>`;
        } else if (key == "h2") {
          data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex">${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10 ql-editor">${pageData}</div>
                            </div>`;
        } else {
          data += `<div class="border border-2 pt-2 sortable_content_data_container">`;
          pageData.forEach((contentItem, contentIndex) => {
            var hrefObject = contentItem.value.href;
            if (typeof hrefObject === "object") {
              var actualHrefValue = hrefObject.value;
            } else {
              let hrefVal = sessionStorage.getItem("href");
              contentItem.value = contentItem.value.replace(
                "[object Object]",
                hrefVal
              );
            }
            data += `<div class="d-flex align-items-center gap-2 bg-light mb-2 py-2 px-3 sortable_content_data removeTag_${index}_${key}_${contentIndex}" data-id="${contentIndex}">
                                    <div class="d-flex">
                                      <div class="drag-handle mx-3">&#9776;</div>
                                      <div>${contentItem.key == "p"
                ? "paragraph"
                : contentItem.key
              }</div>
                                    </div>
                                    <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}" data-content-id="${contentIndex}">Edit</button>
                                    <div><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${contentIndex})">delete</button></div>
                                    <div class="col-md-10 ql-editor">${contentItem.value
              }</div>
                                </div>`;
          });
          data += `</div>`;
        }
      });
      data += `<div class="mt-3">
                        <button type="button" class="btn btn-success" data-id="${index}" onclick="openModal('old', ${index})">Add tag</button>
                        <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">Remove Content</button>
                    </div>`;
    }
    data += `</div>`;
  });
  $(".content_type").html(data);
  if (content.length > 0) $("#content").val(JSON.stringify(content));
  else $("#content").val(null);

  initializeSortable();
  var isapiExists = content.some(function (obj) {
    return obj.type == "api" || obj.type == "cta_more_template";
  });
  let showTemplete = document.querySelector(".showTemplete");
  if (showTemplete) {
    showTemplete.disabled = isapiExists;
  }

  $("#contentModel").modal("hide");
}

$(document).on("click", ".edit-virtual-template", function (event) {
  event.preventDefault();
  event.stopImmediatePropagation();

  // Prevent multiple clicks
  if ($(this).hasClass('processing')) {
    return;
  }

  $(this).addClass('processing');

  try {
    editVirtualTemplateModal(this);
  } finally {
    // Remove processing class after a delay
    setTimeout(() => {
      $(this).removeClass('processing');
    }, 1000);
  }
});

async function editVirtualTemplateModal(element) {
  // Find the content data for this element
  const contentIndex = $(element).data("id");
  const contentItem = content[contentIndex];

  if (!contentItem || contentItem.type !== "api_virtual") {
    console.error("Invalid content item for virtual template editing");
    return;
  }

  // Set editing state
  editingCTAElement = element;
  selectedID = contentItem.id;
  editedContent = contentItem.value;

  const ctaDetails = contentItem.value;

  // Reset and populate the modal
  resetApiVirtualModal();

  const virtualType = ctaDetails.virtualType;
  $("#virtualType").val(virtualType);
  $("#urlContainer").css("display", virtualType == "url" ? "block" : "none");
  $("#dataContainer").css("display", virtualType == "url" ? "none" : "block");

  if (virtualType == "url") {
    $("#ctaMoreTemplateVirtualSlug").val(ctaDetails.slug);
  } else {
    $("#ctaMoreTemplateTitle").val(ctaDetails.title);
    // Update character counter for title
    const titleInput = document.getElementById('ctaMoreTemplateTitle');
    if (titleInput && window.updateCTATitleCount) {
      window.updateCTATitleCount(titleInput);
    }

    if (ctaMoreTemplateQuill) {
      ctaMoreTemplateQuill.root.innerHTML = ctaDetails.desc;
    }

    $("#moreTemplateVirtualContainer #generatedQuery").val(ctaDetails.query);
  }

  setBgPara("MoreTemplate", ctaDetails);

  // Open the modal first
  $("#api_virtual_modal").modal("show");

  // Process conditions after modal is shown (for data type only)
  if (virtualType === "data" && ctaDetails.query && ctaDetails.query.trim()) {
    // Use a unique identifier to prevent multiple processing of the same query
    const queryHash = btoa(ctaDetails.query).replace(/[^a-zA-Z0-9]/g, '');
    const processingKey = `processing_${queryHash}`;

    if (window[processingKey]) {
      return;
    }

    setTimeout(async () => {
      const conditionsTableBody = document.querySelector("#moreTemplateVirtualContainer #conditionsTable tbody");

      // Double check that we're not already processing and table is empty
      if (!conditionsTableBody || conditionsTableBody.children.length > 0 || window[processingKey]) {
        return;
      }

      try {
        window[processingKey] = true;

        const moreTemplateVirtualContainer = document.getElementById("moreTemplateVirtualContainer");
        if (moreTemplateVirtualContainer && typeof processConditionsVideo === 'function') {
          await processConditionsVideo(ctaDetails.query.split(" && "), moreTemplateVirtualContainer);
        } else {
          await processConditions(ctaDetails.query.split(" && "));
        }

      } catch (error) {
        console.error("Error processing conditions:", error);
      } finally {
        // Clear the processing flag after a delay to prevent immediate re-processing
        setTimeout(() => {
          delete window[processingKey];
        }, 2000);
      }
    }, 500);
  }
}

function resetApiVirtualModal() {
  $('#api_virtual_title').text('Edit Virtual Template CTA');
  const virtualType = document.getElementById('virtualType');
  if (virtualType) {
    // For video virtual categories, always set to 'data' instead of 'url'
    const isVideoPage = window.location.href.includes('video') ||
      document.body.classList.contains('video-page') ||
      currentRoute.includes('video');
    virtualType.value = isVideoPage ? 'data' : 'url';
    virtualType.dispatchEvent(new Event('change'));
  }
  $('#ctaMoreTemplateVirtualSlug').val('');
  if (ctaMoreTemplateQuill) {
    ctaMoreTemplateQuill.root.innerHTML = "";
  }
  $('#ctaMoreTemplateTitle').val('');
  // Reset character counter for title
  const titleInput = document.getElementById('ctaMoreTemplateTitle');
  if (titleInput && window.updateCTATitleCount) {
    window.updateCTATitleCount(titleInput);
  }
  const moreTemplateVirtualContainer = document.getElementById('moreTemplateVirtualContainer');
  if (moreTemplateVirtualContainer) {
    const generatedQuery = moreTemplateVirtualContainer.querySelector('#generatedQuery');
    if (generatedQuery) {
      generatedQuery.value = '';
    }
    const columnSelects = moreTemplateVirtualContainer.querySelector('#column');
    if (columnSelects) {
      columnSelects.selectedIndex = 0;
    }

    // Clear the conditions table to prevent infinite data
    const conditionsTableBody = moreTemplateVirtualContainer.querySelector('#conditionsTable tbody');
    if (conditionsTableBody) {
      conditionsTableBody.innerHTML = "";
    }

    // Reset any other form elements that might cause issues
    const operatorSelect = moreTemplateVirtualContainer.querySelector('#operator');
    if (operatorSelect) {
      operatorSelect.selectedIndex = 0;
    }

    const valueInput = moreTemplateVirtualContainer.querySelector('#value');
    if (valueInput) {
      valueInput.value = '';
    }

    // Clear processing flags from the container
    delete moreTemplateVirtualContainer.dataset.processingConditions;
  }

  // Clear any processing flags
  Object.keys(window).forEach(key => {
    if (key.startsWith('processing_')) {
      delete window[key];
    }
  });
}

function initializeSortable() {
  try {
    $(".content_type").sortable({
      handle: ".drag-handle",
      animation: 150,
      onStart: function (evt) { },
      onEnd: function (evt) {
        let newOrder = [];
        $(".content_type .sortable_content_list").each(function () {
          let index = $(this).data("id");
          newOrder.push(content[index]);
        });

        content = newOrder;
        $("#content").val(JSON.stringify(content));
      },
    });

    $(".sortable_content_list").each(function () {
      let parentIndex = $(this).data("id");

      $(this)
        .find(".sortable_content_data_container")
        .each(function () {
          const container = this;

          new Sortable(container, {
            handle: ".drag-handle",
            animation: 150,
            onEnd: function () {
              let childOrder = [];

              $(container)
                .find(".sortable_content_data")
                .each(function () {
                  let dataIndex = $(this).data("id");
                  if (dataIndex !== undefined) {
                    childOrder.push(
                      content[parentIndex].value["content"][dataIndex]
                    );
                  }
                });

              content[parentIndex].value["content"] = childOrder;
              $("#content").val(JSON.stringify(content));
            },
          });
        });
    });
  } catch (e) { }
}

function openModal(target, index) {
  $(".save-tag").attr("data-target", target);
  $(".save-tag").attr("data-tag-id", index);
  $("#content_modal").attr("data-open-id", index);
  $("#content_modal").modal("show");
}

function deleteContent(index) {
  Swal.fire({
    title: "Are you sure?",
    text: "You want to delete this content?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      content.splice(index, 1);
      $("#content").val(JSON.stringify(content));
      callContent(content);
    }
  });
}

function removesingleContent(id, index, contentIndex) {
  Swal.fire({
    title: "Are you sure?",
    text: "You want to delete this content?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      if (contentIndex != null) {
        content[id].value[index].splice(contentIndex, 1);
        if (content[id].value[index].length == 0) {
          // content[id].value.splice(index, 1);
          delete content[id].value[index];
        }
        // delete content[id].value[index][contentIndex];
      } else {
        // content[id].value.splice(index, 1);
        delete content[id].value[index];
      }
      if (Object.keys(content[id].value).length == 0) {
        delete content[id];
      }
      $("#content").val(JSON.stringify(content));
      callContent(content);
    }
  });
}

$(document).on("change", "#image_upload", function (e) {
  var file = e.target.files[0];
  if (file) {
    var reader = new FileReader();
    reader.onload = function (e) {
      var img = new Image();
      img.src = e.target.result;

      img.onload = function () {
        var maxWidth = 500,
          maxHeight = 513;
        $(".selected-image-preview").html(""); // Clear previous images
        if (img.width > maxWidth || img.height > maxHeight) {
          $(".image_error").text(
            "Image dimensions should not exceed " + maxWidth + " * " + maxHeight
          );
          e.target.value = ""; // Clear the file input
        } else {
          $(".image_error").text("");
          var imageHtml = `<div class="image-container mb-3 mt-3 d-flex">
                            <img src="${img.src}" alt="Image" class="img-thumbnail img-editor image-check"  onerror="this.parentElement.remove()">
                            <div class="image-actions mt-3 d-flex">
                                <input type="hidden" class="image_url" value='${img.src}'>
                                <input type="text" class="form-control image_alt_name" id="imageName"  placeholder="Enter image name">
                                <button class="btn btn-danger delete-image mx-2">Delete</button>
                            </div>
                        </div>`;
          $(".selected-image-preview").append(imageHtml);
        }
      };
    };
    reader.readAsDataURL(file);
  }
});

$(document).on("change", "#video_upload", function (e) {
  var file = e.target.files[0];
  if (file) {
    var reader = new FileReader();
    reader.onload = function (e) {
      $(".selected-video-preview").html(""); // Clear previous videos
      var video = document.createElement("video");
      video.onloadedmetadata = function () {
        var maxWidth = 500,
          maxHeight = 513;
        $(".selected-video-preview").html(""); // Clear previous videos
        if (this.videoWidth > maxWidth && this.videoHeight > maxHeight) {
          $(".video_error").text(
            "Video dimensions should not exceed " +
            maxWidth +
            "x" +
            maxHeight +
            " pixels."
          );
          $("#video_upload").val(""); // Clear the file input
        } else {
          $(".video_error").text("");
          var videoHtml = `<div class="video-container mb-3 mt-3">
                        <video src="${e.target.result}" controls class="video-preview" alt="Video Preview" width="320" height="240" onerror="this.parentElement.remove()">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video-actions mt-3 d-flex">
                            <input type="hidden" class="video_url" name="videoFile" value='${e.target.result}'>
                            <input type="text" class="form-control video_alt_name" id="videoName"  name="videoName" placeholder="Enter video name">
                            <button class="btn btn-danger delete-video mx-2">Delete</button>
                        </div>
                    </div>`;
          $(".selected-video-preview").append(videoHtml);
        }
      };
      video.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

// $(document).on("change", "#video_upload", function (e) {
//     var file = e.target.files[0];
//     if (file) {
//         var reader = new FileReader();
//         reader.onload = function (e) {
//             var fileSrc = e.target.result;
//             $(".selected-video-preview").html(""); // Clear previous videos
//             var videoHtml = `<div class="video-container mb-3 mt-3">
//                 <video src="${fileSrc}" controls class="video-preview" alt="Video Preview" width="320" height="240" onerror="this.parentElement.remove()">
//                     Your browser does not support the video tag.
//                 </video>
//                 <div class="video-actions mt-3 d-flex">
//                     <input type="hidden" class="video_url" name="videoFile" value='${fileSrc}'>
//                     <input type="text" class="form-control video_alt_name" id="videoName"  name="videoName" placeholder="Enter video name">
//                     <button class="btn btn-danger delete-video mx-2">Delete</button>
//                 </div>
//             </div>`;
//             $(".selected-video-preview").append(videoHtml);
//         };
//         reader.readAsDataURL(file);
//     }
// });

$(document).on("click", ".delete-image", function () {
  $(this).closest(".image-container").remove();
  $(this).closest(".image_url").val("");
});

$(document).on("click", ".delete-video", function () {
  $(this).closest(".video-container").remove();
  $(this).closest(".video_url").val("");
});

new lc_color_picker('input[name="colors"], #ads_colors', {
  // (array) containing supported modes (solid | linear-gradient | radial-gradient)
  modes: ["solid", "linear-gradient"],

  // (bool) whether to allow colors transparency tune
  transparency: true,

  // (bool) whether to open the picker when field is focused
  open_on_focus: true,

  // (bool) whether to enable dark picker theme
  dark_theme: false,

  // (bool) whether to stretch the trigger in order to cover the whole input field
  no_input_mode: false,

  // (string) defines the wrapper width. "auto" to leave it up to CSS, "inherit" to statically copy input field width, or any other CSS sizing
  wrap_width: "auto",

  // (object) defining shape and position of the in-field preview
  preview_style: {
    input_padding: 35, // extra px padding eventually added to the target input to not cover text
    side: "right", // right or left
    width: 30,
    separator_color: "#ccc", // (string) CSS color applird to preview element as separator
  },

  // (array) defining default colors used when trigger field has no value. First parameter for solid color, second for gradient
  fallback_colors: ["#008080", "linear-gradient(90deg, #fff 0%, #000 100%)"],

  // (function) triggered every time field value changes. Passes value and target field object as parameters
  on_change: null, // function(new_value, target_field) {},

  // (array) option used to translate script texts
  labels: [
    "click to change color",
    "Solid",
    "Linear Gradient",
    "Radial Gradient",
    "add gradient step",
    "gradient angle",
    "gradient shape",
    "color",
    "opacity",
  ],
});

$(document).on("click", ".edit-content", function (event) {
  event.preventDefault();
  var dataId = $(this).attr("data-id");
  var dataIndex = $(this).attr("data-index");
  var editData = content[dataId].value[dataIndex];
  var type = dataIndex;
  $("#contentModel").modal("show");
  $("#content_modal").modal("hide");
  $(".save-tag").attr("data-target", "edit");
  $(".save-tag").attr("data-tag-id", dataId);
  $(".save-tag").attr("data-index", dataIndex); // Add index attribute to target specific content item
  $("#content_modal").attr("data-open-id", dataIndex);
  $(".modal-title").text("Edit " + type);
  if (type === "button") {
    $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="col-md-12">
                    <label for="button_name" class="form-label">Button</label>
                    <input type="text" id="button_name" name="button_name" class="form-control" value="${editData.value
      }">
                    <span class="text-danger button_name_error"></span>
                </div>
                <div class="col-md-12 mt-3">
                    <label for="content_button_link" class="form-label">Button Link</label>
                    <input type="url" id="content_button_link" name="button_link" class="form-control" value="${editData.link
      }">
                    <span class="text-danger button_link_error"></span>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnOpenInNewTab" ${editData.openinnewtab == 1 ? "checked" : ""
      }>
                    <label class="form-check-label" for="contentBtnOpenInNewTab">Open in new tab</label>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnNofollow" ${editData.nofollow == 1 ? "checked" : ""
      }>
                    <label class="form-check-label" for="contentBtnNofollow">Add rel="nofollow"</label>
                </div>
            `);
  } else if (type === "images") {
    // Handle edit for images
    var imageHtml = `<input type="hidden" id="contentType" value="${type}">
                    <div class="selected-images">
                        <div class="col-md-12">
                            <label for="image_upload" class="custom-file-upload">
                                <i class="fas fa-plus"></i> Upload Image
                            </label>
                            <input type="file" id="image_upload" multiple accept=".jpg, .jpeg, .webp, .svg" onchange="validateImage(this)" class="form-control" style="display: none;">
                        </div>`;

    imageHtml += `<div class="selected-image-preview"><div class="image-container mb-3 mt-3 d-flex">
                <img src="${getStorageLink(editData.link)}" alt="Image ${editData.alt
      }" class="img-thumbnail img-editor image-check" OnError="this.parent('.image-container').remove()">
                <div class="image-actions mt-3 d-flex">
                    <input type="hidden" class="image_url" value='${editData.link
      }'>
                    <input type="text" class="form-control image_alt_name" id="imageName" value='${editData.alt
      }' placeholder="Enter image name">
                    <button class="btn btn-danger delete-image mx-2">Delete</button>
                </div></div></div>`;
    imageHtml += `<span class="text-danger image_error"></span>`;
    imageHtml += `</div>`;
    $(".contentType-body").html(imageHtml);
  } else if (type === "video") {
    var videoHtml = `<input type="hidden" id="contentType" value="${type}">
                        <div class="selected-videos">
                            <div class="col-md-12">
                                <label for="video_upload" class="custom-file-upload">
                                    <i class="fas fa-plus"></i> Upload Video
                                </label>
                                <input type="file" id="video_upload" name="video_upload" multiple accept="video/*" class="form-control" style="display: none;">
                            </div>`;
    videoHtml += `<div class="selected-video-preview"><div class="video-container mb-3 mt-3">
                <video src="${editData.link}" controls class="video-preview" alt="Video Preview" width="320" height="240" onerror="this.onerror=null;this.remove();">
                    Your browser does not support the video tag.
                </video>
                <div class="video-actions mt-3 d-flex">
                    <input type="hidden" class="video_url" name="videoFile" value='${editData.link}'>
                    <input type="text" class="form-control video_alt_name" id="videoName" value='${editData.alt}' name="videoName" placeholder="Enter video name">
                    <button class="btn btn-danger delete-video mx-2">Delete</button>
                </div>
            </div></div>`;

    videoHtml += `<span class="text-danger video_error"></span>`;
    videoHtml += `</div>`;
    $(".contentType-body").html(videoHtml);
  } else if (type === "h2") {
    $(".contentType-body").html(`
        <input type="hidden" id="contentType" value="${type}">
        <div class="col-md-12">
            <label for="description_type" class="form-label">Description</label>
            <div id="description_type">${editData}</div>
            <span class="text-danger description-error"></span>
        </div>
        `);
    tinyEditorInit(type);
  } else {
    var contentId = $(this).attr("data-content-id");
    var content_val = editData[contentId];
    var type = content_val.key;
    var editContent = content_val.value;

    $(".save-tag").attr("data-content-id", contentId);
    $(".contentType-body").html(`
            <input type="hidden" id="contentType" value="${type}">
            <div class="col-md-12" style="height: 100%;overflow: hidden;display: flex;flex-direction: column;">
                <label for="description_type" class="form-label">Description</label>
                <div id="description_type" style="height: 100%; overflow: auto;">${editContent}</div>
                <span class="text-danger description-error"></span>
            </div>
        `);
    if (type == "checklists") {
      var parsedContent = $(editContent);
      var isChecked = parsedContent.filter('[data-checked="true"]').length > 0;
    }
    tinyEditorInit(type, isChecked, editContent);
  }
});

$(document).on("click", ".edit-ads", function (event) {
  event.preventDefault();
  var id = $(this).attr("data-id");
  $(".btn-ads-save").attr("data-id", id);
  $(".btn-ads-save").attr("data-target", "edit");
  var editData = content[id].value;

  $("#ads_title_value").val(editData.title);
  $("#ads_colors").val(editData.color);
  $("#ads_button_option").val(editData.color);
  $("#adsopenInNewTab").prop(
    "checked",
    editData.button_target == 1 ? true : false
  );
  $("#adsnofollow").prop("checked", editData.button_rel == 1 ? true : false);
  $("#ads_colors").siblings(".lccp-preview").css("background", editData.color);
  $("#ads_button_value").val(editData.button);
  $("#ads_button_option").val(editData.button_option);
  $("#ads_button_link").val(editData.button_link);
  ads_description.root.innerHTML = editData.description;
  if (editData.image != "") {
    $("#ads-image-preview-div").removeClass("d-none");
    $("#ads-image-preview-div").addClass("d-flex");
    $("#ads_image_link").val(editData.image);
    $("#ads-image-preview").attr("src", editData.image);
  }

  $("#AdsModel").modal("show");
});

function validateImage(inputElement) {
  const file = inputElement.files[0];
  if (file) {
    const fileType = file.type;
    const fileSize = file.size / 1024;
    if (!["image/jpeg", "image/webp", "image/svg+xml"].includes(fileType)) {
      alert(
        "Invalid file type! Please upload a JPG, JPEG, WebP, or SVG image."
      );
      inputElement.value = "";
      return false;
    }
    if (
      fileType === "image/jpeg" ||
      fileType === "image/jpg" ||
      fileType === "image/svg+xml"
    ) {
      if (fileSize > 1024) {
        alert("Image size must be less than 50KB for JPG, JPEG, or SVG files.");
        inputElement.value = "";
        return false;
      }
    } else if (fileType === "image/webp") {
      if (fileSize > 1024) {
        alert("Image size must be less than 200KB for WebP files.");
        inputElement.value = "";
        return false;
      }
    }
    return true;
  }
  return false;
}


$(document).on("click", ".edit-template", function (event) {
  event.preventDefault();
  var id = $(this).attr("data-id");
  $(".btn-template-save").attr("data-id", id);
  $(".btn-template-save").attr("data-target", "edit");
  var editData = content[id].value;

  $("#template_title").val(editData.title ?? "");
  $("#template_desc").val(editData.description ?? "");
  $("#template_keyword").val(editData.keyword);
  $("#template_keyword").tagsinput("removeAll");
  if (editData.keyword && editData.keyword.trim() !== "") {
    editData.keyword.split(",").forEach((tag) => {
      $("#template_keyword").tagsinput("add", tag.trim());
    });
  }
  $("#template_keyword_link").val(editData.keyword_link);
  $("#template_only_video")
    .val(editData.only_video ? editData.only_video : 0)
    .change();
  $("#template_link_target").val(editData.keyword_target).change();
  $("#templete_target").prop(
    "checked",
    editData.link_target == 1 ? true : false
  );
  $("#templete_rel").prop("checked", editData.link_rel == 1 ? true : false);

  $("#addTemplateKeywordModel").modal("show");
});

$(document).on("click", ".showTemplete", function (event) {
  event.preventDefault();

  // Check if current route is video virtual category - open modal directly
  if (currentRoute === "create_video_virtual_cat" || currentRoute === "edit_video_virtual_cat") {
    // Check if More Template CTA already exists
    var isMoreTemplateExists = content.some(function (obj) {
      return obj.type == "api_virtual";
    });

    if (isMoreTemplateExists) {
      alert("More Template CTA already exists.");
      return;
    }

    $("#api_virtual_modal").modal("show");
    $("#add_content_model").modal("hide");
    return;
  }

  var isapiExists = content.some(function (obj) {
    return obj.type == "api";
  });

  if (isapiExists) {
    alert("Templete already exists.");
  } else {
    if (conditionRoutes.includes(currentRoute)) {
      var addData = {
        type: "api",
        value: {
          title: "Template",
          description: "Show Template",
        },
      };
      content.push(addData);
      callContent(content);
      $("#add_content_model").modal("hide");
      return;
    }

    $(".btn-template-save").attr("data-target", "");
    $(".btn-template-save").attr("data-id", "");
    blank_template_form();

    $("#addTemplateKeywordModel").modal("show");
  }
  $("#add_content_model").modal("hide");
});

function previewImage(event) {
  var input = event.target;
  var reader = new FileReader();
  reader.onload = function () {
    var previewDiv = document.getElementById("banner-image-preview-div");
    previewDiv.classList.remove("d-none");
    previewDiv.classList.add("d-flex");

    var imgElement = document.getElementById("banner-image-preview");
    imgElement.classList.add("d-none");
    imgElement.src = reader.result;
  };
  reader.readAsDataURL(input.files[0]);
}

function previewFile(event) {
  validateImage(event);
  var input = event;
  var file = input.files[0];
  var reader = new FileReader();

  reader.onload = function () {
    var previewImageElement = $("#banner-image-preview");
    var previewVideoElement = $("#banner-video-preview");
    var previewDiv = $("#banner-image-video-preview-div");
    var bannerType = $("#banner_type");

    if (file.type.startsWith("image/")) {
      var img = new Image();
      img.onload = function () {
        if (this.width <= 500 && this.height <= 400) {
          previewImageElement
            .removeClass("d-none")
            .addClass("d-flex")
            .attr("src", reader.result);
          previewVideoElement.removeClass("d-flex").addClass("d-none");
          bannerType.val("image");
          $(".banner-image-error").text("");
          $(".save-page-data").removeAttr("disabled");
          $("button.btn.btn-danger.removeBannerBtn").show();
        } else {
          $(".banner-image-error").text(
            "Image dimensions should not exceed 500x400 pixels."
          );
          previewImageElement
            .removeClass("d-flex")
            .addClass("d-none")
            .attr("src", "");
          previewVideoElement.addClass("d-none");
          bannerType.val("");
          input.value = ""; // Clear the file input
          $(".save-page-data").attr("disabled", "disabled");
          $("button.btn.btn-danger.removeBannerBtn").hide();
        }
      };
      img.src = reader.result;
    } else if (file.type.startsWith("video/")) {
      var video = document.createElement("video");
      video.onloadedmetadata = function () {
        if (this.videoWidth <= 500 && this.videoHeight <= 400) {
          previewVideoElement
            .removeClass("d-none")
            .addClass("d-flex")
            .attr("src", reader.result);
          previewImageElement.removeClass("d-flex").addClass("d-none");
          bannerType.val("video");
          $(".banner-image-error").text("");
          $(".save-page-data").removeAttr("disabled");
          $("button.btn.btn-danger.removeBannerBtn").show();
        } else {
          $(".banner-image-error").text(
            "Video dimensions should not exceed 500x400 pixels."
          );
          previewVideoElement
            .removeClass("d-flex")
            .addClass("d-none")
            .attr("src", "");
          previewImageElement.addClass("d-none");
          bannerType.val("");
          input.value = ""; // Clear the file input
          $(".save-page-data").attr("disabled", "disabled");
          $("button.btn.btn-danger.removeBannerBtn").hide();
        }
      };
      video.src = URL.createObjectURL(file);
    }
    previewDiv.removeClass("d-none").addClass("d-flex");
  };
  if (file) {
    reader.readAsDataURL(file);
  }
}

function previewVideo(event) {
  var input = event.target;
  var reader = new FileReader();
  reader.onload = function () {
    var previewDiv = document.getElementById("banner-video-preview-div");
    previewDiv.classList.remove("d-none");
    previewDiv.classList.add("d-flex");
    var videoElement = document.getElementById("banner-video-preview");
    videoElement.src = reader.result;
  };
  reader.readAsDataURL(input.files[0]);
}

function adspreviewImage(event) {
  var input = event.target;
  var reader = new FileReader();
  reader.onload = function (event) {
    var img = new Image();
    img.src = event.target.result;

    img.onload = function () {
      var maxWidth = 300,
        maxHeight = 300;
      if (img.width > maxWidth || img.height > maxHeight) {
        $(".ads_image_error").text(
          "Image dimensions should not exceed " + maxWidth + " * " + maxHeight
        );

        $("#ads-image-preview-div").removeClass("d-flex").addClass("d-none");

        $("#ads-image-preview").attr("src", "");

        $("#ads_image_link").val("");
        input.value = ""; // Clear the file input
      } else {
        $(".ads_image_error").text("");
        $("#ads-image-preview-div").removeClass("d-none").addClass("d-flex");

        $("#ads-image-preview").attr("src", reader.result);

        $("#ads_image_link").val(reader.result);
      }
    };
  };
  reader.readAsDataURL(input.files[0]);
}

$(document).on("click", ".btn-template-save", function (event) {
  event.preventDefault();
  var fieldRequired = $(this).attr("data-field-required");
  if (!fieldRequired) {
    var title = $("#template_title").val();
    var description = $("#template_desc").val();

    var keywords = $("#template_keyword").val();
    var keywords_link = $("#template_keyword_link").val();
    var only_video = $("#template_only_video").val();
    var keywords_target = $("#template_link_target").val();
    var templete_target = $("#templete_target").prop("checked") ? 1 : 0;
    var templete_rel = $("#templete_rel").prop("checked") ? 1 : 0;
    if (keywords.trim() == "") {
      $(".template_keyword_error").text("Please fill this field");
      return;
    }

    if (title.trim() == "") {
      $(".template_title_error").text("Please fill this field");
      return;
    }

    if (description.trim() == "") {
      $(".template_desc_error").text("Please fill this field");
      return;
    }

    if (keywords_target == "loadmore_other_page") {
      if (keywords_link.trim() == "") {
        $(".template_keyword_link_error").text("Please fill this field.");
      } else if (!isValidURL(keywords_link.trim())) {
        $(".template_keyword_link_error").text("Invalid Url.");
      } else {
        $(".template_keyword_link_error").text("");
      }
      if (
        keywords.trim() == "" ||
        keywords_link.trim() == "" ||
        !isValidURL(keywords_link.trim())
      )
        return;
    } else {
      if (keywords.trim() == "") return;
      keywords_link = "";
      templete_target = 0;
      templete_rel = 0;
    }

    formChanged = true;
    var addData = {
      type: "api",
      value: {
        title: title,
        description: description,
        keyword: keywords,
        keyword_target: keywords_target,
        only_video: only_video,
        keyword_link: keywords_link,
        link_target: templete_target,
        link_rel: templete_rel,
      },
    };
  } else {
    var addData = {
      type: "api",
      value: {
        title: "Template",
        description: "Show Template",
      },
    };
  }
  if ($(this).attr("data-target") === "edit") {
    var dataId = $(this).attr("data-id");
    content[dataId] = addData;
  } else {
    content.push(addData);
  }
  blank_template_form();
  callContent(content, !fieldRequired);
  $("#addTemplateKeywordModel").modal("hide");
});

function blank_template_form() {
  $("#template_title").val("");
  $("#template_desc").val("");
  $("#template_keyword").val("");
  $("#template_keyword_link").val("");
  $("#templete_target").prop("checked", false);
  $("#templete_rel").prop("checked", false);
}

$(document).on("shown.bs.modal", "#addTemplateKeywordModel", function () {
  $(".template_keyword_error").text("");
});

$(document).on("shown.bs.modal", "#AdsModel", function () {
  $(".ads_title_value_error").text("");
  $(".ads_colors_error").text("");
  $(".ads_button_error").text("");
  $(".ads_button_link_error").text("");
  $(".ads_description_error").text("");
  $(".ads_image_error").text("");
});

function blank_ads_form() {
  $("#ads_title_value").val("");
  $("#ads_colors").val("");
  $("#ads_button_option").val("_self");
  $("#ads_colors")
    .siblings(".lccp-preview")
    .css("background", "linear-gradient(90deg, rgba(255, 255, 255, .4), #000)");
  $("#ads_button_value").val("");
  $("#ads_button_link").val("");
  $("#adsopenInNewTab").prop("checked", false);
  $("#adsnofollow").prop("checked", false);
  $(".ads_image").val("");
  ads_description.root.innerHTML = "";
  $("#ads_image_link").val("");
  $("#ads-image-preview").attr("src", "");
  $("#ads-image-preview-div").removeClass("d-flex");
  $("#ads-image-preview-div").addClass("d-none");
}

function tinyEditorInit(type, checkbox = false, editContent = "") {
  var tool = {};
  var styleLevel = null;
  var contentType = null;
  switch (type) {
    case "h2":
      contentType = "header";
      styleLevel = parseInt(type.substring(1));
      tool = { header: [type.substring(1)] };
      break;
    case "h3":
      contentType = "header";
      styleLevel = parseInt(type.substring(1));
      tool = { header: [type.substring(1)] };
      break;
    case "h4":
      contentType = "header";
      styleLevel = parseInt(type.substring(1));
      tool = { header: [type.substring(1)] };
      break;
    case "h5":
      contentType = "header";
      styleLevel = parseInt(type.substring(1));
      tool = { header: [type.substring(1)] };
      break;
    case "h6":
      contentType = "header";
      styleLevel = parseInt(type.substring(1));
      tool = { header: [type.substring(1)] };
      break;
    case "p":
      contentType = "header";
      styleLevel = null;
      tool = {};
      break;
    case "content":
      contentType = "header";
      styleLevel = null;
      tool = {};
      break;
    case "bullets":
      contentType = "list";
      styleLevel = "bullet";
      tool = { list: "bullet" };
      break;
    case "numbers":
      contentType = "list";
      styleLevel = "ordered";
      tool = { list: "ordered" };
      break;
    case "checklists":
      contentType = "list";
      styleLevel = checkbox ? "checked" : "unchecked";
      tool = { list: "check" };
      break;
  }

  if (type == "h2") {
    var toolbar = [
      ["undo", "redo"],
      ["bold"],
      [tool],
      [{ align: [] }],
      [{ color: [] }],
    ];
  } else {
    var toolbar = [
      ["undo", "redo"],
      ["bold", "italic", "underline", "strike"],
      [{ header: [2, 3, 4, 5, 6, false] }],
      [{ script: "super" }, { script: "sub" }],
      [
        { list: "ordered" },
        { list: "bullet" },
        { indent: "-1" },
        { indent: "+1" },
      ],
      ["direction", { align: [] }],
      [{ color: [] }],
      ["custom"],
      ["margin"],
      ["link"],
      ["copy"],
      ["clean"],
      [{ target: "_self" }, { target: "_blank" }],
    ];
  }

  quill = new Quill("#description_type", {
    theme: "snow",
    modules: {
      toolbar: {
        container: toolbar,
        handlers: {
          custom: function (value) {
            colorPickerCustomQuill(value, this.quill, "contentModel");
          },
          margin: function (value) {
            if (value) {
              const range = this.quill.getSelection();
              if (!range) {
                window.alert("Select any block");
                return;
              }
              const [block] = quill.scroll.descendant(
                Quill.import("blots/block"),
                range.index
              );
              if (block) {
                const blockDomNode = block.domNode;
                $("#margin-bottom").val(
                  (blockDomNode.style.marginBottom || "0")
                    .match(/\d+/g)
                    .join("")
                );
              }
              $("#marginModal").modal("show");
              var insertMarginHandler = function () {
                applyMargins(this.quill, range);
                document
                  .getElementById("insertMargin")
                  .removeEventListener("click", insertMarginHandler);
              }.bind(this);
              document
                .getElementById("insertMargin")
                .addEventListener("click", insertMarginHandler);
            }
          },
          link: function (value) {
            if (value) {
              $(".linkUrlError").text("");
              $("#linkModal").modal("show");
              $("#linkUrl").val("");
              $("#openInNewTab").prop("checked", false);
              $("#nofollow").prop("checked", false);

              var insertLinkHandler = function () {
                insertLink(this.quill);
                document
                  .getElementById("insertLink")
                  .removeEventListener("click", insertLinkHandler);
              }.bind(this);

              document
                .getElementById("insertLink")
                .addEventListener("click", insertLinkHandler);
            }
          },
          target: function (value) {
            this.quill.format("link", { target: value });
          },
        },
      },
      history: {
        userOnly: false,
        maxStack: 500,
      },
      clipboard: {
        matchVisual: false, // Disable Quill's built-in paste formatting
      },
    },
  });
  quill.root.innerHTML = editContent; // Directly insert content
  function applyMargins(quill, range) {
    if (range) {
      const marginBottom = document.getElementById("margin-bottom").value || 0;
      const [block] = quill.scroll.descendant(
        Quill.import("blots/block"),
        range.index
      );
      if (block) {
        const blockDomNode = block.domNode;
        blockDomNode.style.marginBottom = `${marginBottom ? marginBottom + "px" : ""
          }`;
      }
    }

    $("#marginModal").modal("hide");
  }

  // function showColorPicker(value) {

  //   }

  quill.on("text-change", function (delta, oldDelta, source) {
    delta.ops.forEach(function (op, index) {
      if (typeof op.insert !== "undefined" && op.insert === "\n") {
        const range = quill.getSelection();
        const [parentBlock] = quill.scroll.descendant(
          Quill.import("blots/block"),
          range.index - 1
        );

        if (parentBlock) {
          const blockDomNode = parentBlock.domNode;
          if (!blockDomNode.style.marginBottom) {
            blockDomNode.style.marginBottom = "16px";
          }
        }
      }
    });
  });

  quill.on("editor-change", function (eventName, ...args) {
    if (eventName === "text-change") {
      handlePasteEvent(quill);
    }
  });

  function handlePasteEvent(quill) {
    quill.clipboard.addMatcher(Node.TEXT_NODE, function (node, delta) {
      const pastedText = node.data;
      const currentFormat = quill.getFormat();
      const headerRegex = /<h([2-6])>/g;
      if (pastedText.match(headerRegex)) {
        const match = headerRegex.exec(pastedText);
        const headerLevel = match[1];
        const desiredFormat = currentFormat;
        delta.ops.forEach((op) => {
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
            tool = { list: "bullet" };
            break;
          case "numbers":
            contentType = "list";
            styleLevel = "ordered";
            tool = { list: "ordered" };
            break;
          case "checklists":
            contentType = "list";
            styleLevel = checkbox ? "checked" : "unchecked";
            tool = { list: "check" };
            break;
        }
        if (contentType === "header") {
          delta.ops.forEach((op) => {
            op.attributes = currentFormat;
          });
        }
      }
      return delta;
    });
  }

  if (quill) {
    quill.root.addEventListener("paste", function (event) {
      event.preventDefault();

      const clipboardData = event.clipboardData || window.clipboardData;
      const pastedHtml = clipboardData.getData("text/html");
      const pastedText = clipboardData.getData("text/plain");

      // 1. If user pasted raw HTML code (like <b>hello</b>)
      if (pastedText && /<[^>]*>/.test(pastedText)) {
        const range = quill.getSelection(true);
        quill.clipboard.dangerouslyPasteHTML(range.index, pastedText);
        quill.setSelection(range.index + pastedText.length);
        return;
      }

      // 2. If actual HTML from clipboard exists (copy from websites, Word etc.)
      if (pastedHtml) {
        const range = quill.getSelection(true);
        quill.clipboard.dangerouslyPasteHTML(range.index, pastedHtml);
        return;
      }

      // 3. Fallback: normal text paste
      if (pastedText) {
        const range = quill.getSelection(true);
        quill.insertText(range.index, pastedText);
        quill.setSelection(range.index + pastedText.length);
      }
    });
  }

  // Set the content format to the selected tag
  if (styleLevel !== null && contentType !== null) {
    quill.format(contentType, styleLevel);
  }

  $(".ql-undo").html('<i class="fa-solid fa-rotate-left"></i>');
  $(".ql-redo").html('<i class="fa-solid fa-rotate-right"></i>');
}

function colorPickerCustomQuill(value, quill, id) {
  let contentModel = document.getElementById(id);
  let button = contentModel.querySelector(".ql-custom");
  let picker = contentModel.querySelector("#color-picker");
  let parent = button.parentElement;
  // parent.style.position = "relative";
  if (!picker) {
    picker = document.createElement("input");
    picker.id = "color-picker";
    picker.type = "color";
    picker.style.visibility = "hidden";
    picker.style.position = "absolute";
    picker.style.left = "0px";
    picker.style.top = "0px";
    parent.appendChild(picker);

    picker.addEventListener(
      "input",
      function () {
        quill.format("color", picker.value);
      },
      false
    );

    picker.addEventListener("change", function () {
      picker.value = "";
    });
  }
  setTimeout(() => {
    picker.click();
  }, 200);
}

$(document).on("click", ".ql-undo", function () {
  var editor;
  if ($(this).parents(".ql-toolbar").siblings("#description_type").length) {
    editor = quill;
  } else if ($(this).parents(".ql-toolbar").siblings("#meta_desc").length) {
    editor = meta_desc;
  } else if ($(this).parents(".ql-toolbar").siblings("#description").length) {
    editor = description;
  } else if (
    $(this).parents(".ql-toolbar").siblings("#ads_description").length
  ) {
    editor = ads_description;
  }
  if (editor) {
    editor.history.undo();
  }
});

$(document).on("click", ".ql-redo", function () {
  var editor;
  if ($(this).parents(".ql-toolbar").siblings("#description_type").length) {
    editor = quill;
  } else if ($(this).parents(".ql-toolbar").siblings("#meta_desc").length) {
    editor = meta_desc;
  } else if ($(this).parents(".ql-toolbar").siblings("#description").length) {
    editor = description;
  } else if (
    $(this).parents(".ql-toolbar").siblings("#ads_description").length
  ) {
    editor = ads_description;
  }
  if (editor) {
    editor.history.redo();
  }
});

////////////////////////////////////////////////// - \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

$(document).on("click", ".ql-copy", function () {
  const htmlContent = quill.root.innerHTML;

  const tempDiv = document.createElement("div");
  tempDiv.textContent = htmlContent;
  tempDiv.contentEditable = true;
  tempDiv.style.position = "fixed";
  tempDiv.style.left = "-9999px";
  document.body.appendChild(tempDiv);

  const range = document.createRange();
  range.selectNodeContents(tempDiv);
  const selection = window.getSelection();
  selection.removeAllRanges();
  selection.addRange(range);

  try {
    const success = document.execCommand("copy");
  } catch (err) {
    console.error("Copy error:", err);
  }
  document.body.removeChild(tempDiv);
});

$("#marginModal input").on("keydown", function (e) {
  if (e.key === "Enter") {
    e.preventDefault();
    e.stopPropagation();
  }
});

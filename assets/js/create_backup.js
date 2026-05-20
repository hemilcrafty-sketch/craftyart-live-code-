var quill, ads_description, question, answer;
var imageCount = 0;
var content = [];
var faqs = [];
var formChanged = false;

$(document).ready(function () {
    window.setTimeout(function () {
        $(".alert").fadeTo(500, 0).slideUp(500, function () {
            $(this).remove();
        });
    }, 4000);
    $(':input, textarea').change(function () {
        formChanged = true;
    });

    $(document).on('change', "#template_link_target", function () {
        if (this.value == 'loadmore_other_page') {
            $(".keyword_link_option").show();
        } else {
            $(".keyword_link_option").hide();
        }
    });

    $(window).on('beforeunload', function () {
        if (formChanged) {
            return "You have unsaved changes. Are you sure you want to leave this page?";
        }
    });

    $("#add-pages-form").submit(function () {
        formChanged = false;
    });

    var $pageSlugInput = $('#page_slug');

    $('#faqsModel, #contentModel, #addTemplateKeywordModel, #AdsModel, #linkModal').modal({
        backdrop: 'static',
        keyboard: false
    });

    // Function to generate slug
    function generateSlug(input) {
        // Replace special characters and trim the input
        // return input.replace(/[^a-z0-9-\s]/ig, '').trim().toLowerCase().replace(/\s+/g, '-');
        return input.replace(/[^a-z0-9\/\s-]/ig, '').trim().toLowerCase().replace(/\s+/g, '-');
    }

    // Event listener for input field blur
    $pageSlugInput.on('blur', function () {
        var inputValue = $(this).val();
        var slug = generateSlug(inputValue);
        $(this).val(slug); // Update input value with generated slug
    });

    var id = $("#id").val();
    var contents = $("#content").val() ?? null;
    var faq = $('#faqs').val();

    var removeStyleRegex = /(<a[^>]*)\s*style="[^"]*"/gi;


    if (contents !== null && contents !== '') {
        content = JSON.parse(contents);
        if (!Array.isArray(contents)) {
            content = $.map(content, function (value) {
                return value;
            });
        }

        content.forEach(function (item) {
            if (item.type === 'content' && item.value && item.value.content) {
                item.value.content.forEach(function (contentItem) {
                    if (contentItem.value) {
                        contentItem.value = contentItem.value.replace(removeStyleRegex, '$1'); // Remove all styles from <a> tags
                    }
                });
            }
            if (item.type === 'ads' && item.value && item.value.description) {
                item.value.description = item.value.description.replace(removeStyleRegex, '$1');
            }
        });
        callContent(content, id);
    }

    if (faq !== null && faq !== '') {
        faqs = JSON.parse(faq);
        // Loop through the parsed data and remove styles from <a> tags
        faqs.forEach(function (item) {
            if (item.answer) {
                item.answer = item.answer.replace(removeStyleRegex, '$1'); // Remove style attribute from <a> tags
            }
        });
        callFaqs(faqs);
    }
});


document.addEventListener("DOMContentLoaded", function () {

    let Link = Quill.import('formats/link');
    class CustomLink extends Link {
        static create(value) {
            let node = super.create(value);
            if (value && value.target) {
                node.setAttribute('href', value.href);
                if (value.target == '_self' || value.target == '_blank') {
                    node.setAttribute('target', value.target);
                } else {
                    node.setAttribute('target', '_blank');
                }
                if (value.nofollow) {
                    node.setAttribute('rel', 'nofollow');
                } else {
                    node.setAttribute('rel', 'dofollow');
                }
                return node;
            } else {
                node.setAttribute('href', value);
                return node;
            }
        }
    }
    Quill.register('formats/link', CustomLink, true);

    // Define the toolbar options including the target attribute dropdown
    let toolbarOptions = {
        container: [
            ["undo", "redo"],
            ["bold", "italic"],
            [{ 'color': [] }],
            [{ 'link': 'link' }],  // Use 'link' as the identifier for the link button
            [{ 'target': '_self' }, { 'target': '_blank' }] // Target dropdown menu
        ],
        handlers: {
            'link': function (value) {
                if (value) {
                    $(".linkUrlError").text('');
                    $('#linkModal').modal('show');
                    $('#linkUrl').val('');
                    $('#openInNewTab').prop('checked', false);
                    $('#nofollow').prop('checked', false);

                    // Define the event listener function
                    var insertLinkHandler = function () {
                        insertLink(this.quill);
                        // Remove the event listener after execution
                        document.getElementById('insertLink').removeEventListener('click', insertLinkHandler);
                    }.bind(this);

                    // Add the event listener
                    document.getElementById('insertLink').addEventListener('click', insertLinkHandler);
                }
            },
            'target': function (value) {
                this.quill.format('link', { target: value });
            }
        }
    };

    // Initialize Quill editors with customized toolbar

    ads_description = new Quill("#ads_description", {
        theme: "snow",
        modules: {
            toolbar: toolbarOptions,
            history: {
                userOnly: true,
                maxStack: 500
            },
        },
    });

    ads_description.on('text-change', function (delta, oldDelta, source) {
        if (source === 'user') {
            formChanged = true;
            // User made changes
        }
    });

    answer = new Quill("#answer", {
        theme: "snow",
        modules: {
            toolbar: toolbarOptions,
            history: {
                userOnly: true,
                maxStack: 500
            },
        },
    });
    answer.on('text-change', function (delta, oldDelta, source) {
        if (source === 'user') {
            formChanged = true;
            // User made changes
        }
    });

    // Update undo and redo icons
    $(".ql-undo").html('<i class="fa-solid fa-rotate-left"></i>');
    $(".ql-redo").html('<i class="fa-solid fa-rotate-right"></i>');
});

function insertLink(quillInstance) {
    var href = $('#linkUrl').val();
    var openInNewTab = $('#openInNewTab').prop('checked');
    var noFollow = $('#nofollow').prop('checked');
    var target = openInNewTab ? '_blank' : '_self';

    if (href) {

        $(".linkUrlError").text('');
        // Apply the link format to the Quill editor
        console.log(href);
        sessionStorage.setItem("href", href);
        quillInstance.format('link', { href: href, target: target, nofollow: noFollow });

        // Close the modal
        $('#linkModal').modal('hide');
    } else {
        var href = $('.linkUrlError').text('Please Add Link Here');
    }

}

$(".openContentModel").on("click", function () {
    $("#add_content_model").modal("hide");
    $(".save-tag").attr("data-target", "");
    $(".save-tag").attr("data-tag-id", "");
    $("#addContentModel").attr("data-open-id", "");
});

$(".adsModel").on("click", function () {
    $("#add_content_model").modal("hide");
    $(".btn-ads-save").attr("data-target", "");
    $(".btn-ads-save").attr("data-id", "");
    blank_ads_form();
});

$(document).on("click", ".content-type", function () {
    var type = $(this).data("type");
    $("#contentModel .content-title").text(type);
    $("#addContentModel").modal("hide");

    var open_id = $("#addContentModel").attr("data-open-id");

    var isButtonKeyExists = false;
    var isImageOrVideoExists = false;
    var ish2Exists = false;
    if (open_id !== "" && open_id !== undefined) {
        let contentValue = content[open_id].value;
        isButtonKeyExists = contentValue.hasOwnProperty('button') && type === 'button';
        isImageOrVideoExists = (contentValue.hasOwnProperty('images') || contentValue.hasOwnProperty('video')) && (type === 'images' || type === 'video');
        ish2Exists = contentValue.hasOwnProperty('h2') && type === 'h2';
    }

    if (isButtonKeyExists) {
        alert("Button tag is already added");
    } else if (isImageOrVideoExists) {
        alert("Image or video is already added");
    } else if (ish2Exists) {
        alert("H2 Tag already added.");
    } else {
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
                        <input type="file" id="image_upload" name="image_upload" accept="image/*" class="form-control" style="display: none;">
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
    $("div#banner-image-video-preview-div").html("<input type='hidden' name='remove_banner' value='1'>");
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

$(document).on("click", ".btn-content-save", function () {

    var type = $("#contentType").val();
    if (type == "button") {
        if ($("#button_name").val().trim() == '') {
            $('.button_name_error').text('Please fill this field.');
        } else {
            $('.button_name_error').text('');
        }
        if ($("#content_button_link").val().trim() == '') {
            $('.button_link_error').text('Please fill this field.');
        } else if (!isValidURL($("#content_button_link").val().trim())) {
            $('.button_link_error').text('Invalid Url.');
        } else {
            $('.button_link_error').text('');
        }

        if ($("#button_name").val().trim() == '' || ($("#content_button_link").val().trim() == '' || !isValidURL($("#content_button_link").val().trim()))) {
            return;
        }

    } else if (type == "images") {
        if ($(".image_url").val() == undefined) {
            $(".image_error").text('Please fill this field.'); return;
        } else {
            $(".image_error").text('');
        }
    } else if (type == "video") {
        if ($(".video_url").val() == undefined) {
            $(".video_error").text('Please fill this field.'); return;
        } else {
            $(".video_error").text('');
        }
    } else {
        if (quill.getText().trim() == '') {
            $('.description-error').text('Please fill this field.');
            return;
        } else {
            $('.description-error').text('');
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
        var openinnewtab = $("#contentBtnOpenInNewTab").prop('checked') ? 1 : 0;
        var nofollow = $("#contentBtnNofollow").prop('checked') ? 1 : 0;
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
            link: image_url
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
            value: desc
        };
    }

    if ($(this).attr("data-target") === "edit") {
        if (addData.images || addData.video || addData.button || addData.h2) {
            if (addData.images) {
                delete content[dataId].value['images'];
            } else if (addData.video) {
                delete content[dataId].value['video'];
            } else if (addData.button) {
                delete content[dataId].value['button'];
            } else if (addData.h2) {
                delete content[dataId].value['h2'];
            }
            Object.assign(content[dataId].value, addData);
        } else {
            var contentEditId = $(this).attr("data-content-id");
            content[dataId].value[dataIndex][contentEditId] = addData;
        }
    } else {
        if (addData.images) {
            if (content[index].value['video']) {
                delete content[index].value['video'];
            }
            content[index].value['images'] = addData.images;
        } else if (addData.video) {
            if (content[index].value['images']) {
                delete content[index].value['images'];
            }
            content[index].value['video'] = addData.video;
        } else if (addData.button) {
            content[index].value['button'] = addData.button;
        } else if (addData.h2) {
            content[index].value['h2'] = addData.h2;
        } else {
            if (!content[index].value.hasOwnProperty('content')) {
                content[index].value['content'] = [];
            }
            content[index].value['content'].push(addData);
        }
    }

    // Specify the order you want the keys to be in
    const order = ['images', 'video', 'button', 'h2', 'content'];

    // Iterate over the content array and reorder the keys within each item's value
    content.forEach(item => {
        if (item.type === "content" && item.value) {
            item.value = reorderKeys(item.value, order);
        }
    });
    callContent(content);
});
// Function to reorder the keys of an object
function reorderKeys(obj, order) {
    const ordered = {};
    order.forEach(key => {
        if (obj.hasOwnProperty(key)) {
            ordered[key] = obj[key];
        }
    });
    return { ...ordered, ...obj };
}
$(document).ready(function () {
    var id = $("#id").val();
});

function callContent(content, id) {
    var data = "";
    console.log(content);
    console.log(id);
    console.log("22222222222");
    content.forEach((ele, index) => {
        data += `<div class="mb-3 p-2 border border-2 sortable_content_list content_show_${index}" data-id="${index}"><div class="drag-handle">&#9776;</div>`;
        if (ele.type == "api") {
            data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 sortable_content_data">
                        <div class="col-md-1 d-flex">Templete</div>
                        <div class="col-md-11">
                            <div class="content-box">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-2">Keyword : </div><div class="col-md-10"> ${ele.value.keyword}</div>
                                    <div class="col-md-2">Keyword target : </div><div class="col-md-10"> ${ele.value.keyword_target == "loadmore_here" ? "Loadmore here" : "Loadmore other page"}</div>`;
            if (ele.value.keyword_target == "loadmore_other_page") {
                console.log(523);
                data += `<div class="col-md-2">Keyword link : </div> <div class="col-md-10"><a href="${ele.value.keyword_link}" target="${ele.value.link_target == 1 ? '_blank' : '_self'}" rel="${ele.value.link_rel == 1 ? 'nofollow' : 'dofollow'}"> link</a></div>`;
            }
            data += `</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success edit-template" data-id="${index}">Edit Keyword</button>
                        <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">delete</button>
                    </div>
                </div>`;
        } else if (ele.type == "ads") {
            console.log(536);
            data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 sortable_content_data">
                    <div class="col-md-1 d-flex">Ads</div>
                    <div class="col-md-11">
                        <div class="content-box">
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Title:</div>
                                <div class="col-md-11">${ele.value.title}</div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-1">Description:</div>
                                <div class="col-md-11">${ele.value.description}</div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Color:</div>
                                <div class="col-md-11"><span class="ads-color-box" style="background:${ele.value.color}"></span></div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Image:</div>
                                <div class="col-md-11"><img src="${ele.value.image}" class="image-check" OnError="this.remove()" alt="Placeholder Image" width="100" height="100"></div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">Button:</div>
                                <div class="col-md-11"><a href="${ele.value.button_link}" target="${ele.value.button_target ? "_blank" : '_self'}" rel="${ele.value.button_rel ? "nofollow" : 'dofollow'}" class="btn btn-info text-white">${ele.value.button}</a></div>
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
                    console.log(576);
                    data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex"> ${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10"><a href="${pageData.link}" class="btn btn-info" target="${pageData.openinnewtab == 1 ? '_blank' : '_self'}" rel="${pageData.nofollow == 1 ? 'nofollow' : 'dofollow'}">${pageData.value}</a></div>
                            </div>`;
                } else if (key == "images") {
                    data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 removeTag_${index}_${key}" data-id="${key}">
                                <div class="col-md-1 d-flex"> ${key}</div>
                                <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}">Edit</button>
                                <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${null})">delete</button></div>
                                <div class="col-md-10">`;
                    data += `<img src="${pageData.link}" alt="${pageData.alt}" width="70" height="70" class="me-2 image-check" OnError="this.remove()">`;
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
                    data += `<div class="border border-2 pt-2">`;
                    pageData.forEach((contentItem, contentIndex) => {
                        var hrefObject = contentItem.value.href;
                        console.log(hrefObject);
                        if (typeof hrefObject === 'object') {
                            var actualHrefValue = hrefObject.value;
                        } else {
                            let hrefVal = sessionStorage.getItem("href");
                            contentItem.value = contentItem.value.replace("[object Object]", hrefVal);
                        }

                        data += `<div class="d-flex align-items-center bg-light mb-2 py-2 px-3 sortable_content_data removeTag_${index}_${key}_${contentIndex}" data-id="${contentIndex}">
                                    <div class="col-md-1 d-flex"><div class="drag-handle mx-3">&#9776;</div> ${contentItem.key == 'p' ? 'paragraph' : contentItem.key}</div>
                                    <button type="button" class="btn btn-success edit-content" data-id="${index}" data-index="${key}" data-content-id="${contentIndex}">Edit</button>
                                    <div class="col-md-1"><button type="button" class="btn btn-danger" onclick="removesingleContent(${index},'${key}',${contentIndex})">delete</button></div>
                                    <div class="col-md-10 ql-editor">${contentItem.value}</div>
                                </div>`;
                    });
                    data += `</div>`;
                }
            });
            data += `<div class="mt-3">
                        <button type="button" class="btn btn-success" data-id="${index}" onclick="openModal('old', ${index})" data-bs-toggle="modal" data-bs-target="#addContentModel">Add tag</button>
                        <button type="button" class="btn btn-danger" onclick="deleteContent(${index})">Remove Content</button>
                    </div>`;
        }
        data += `</div>`;
    });
    $(".content_type").html(data);
    $("#content").val(JSON.stringify(content));
    initializeSortable();

    $("#contentModel").modal("hide");
}

function initializeSortable() {
    $(".content_type").sortable({
        items: ".sortable_content_list", // selector for sortable items
        handle: ".drag-handle", // selector for the handle
        update: function (event, ui) {
            console.log("Parent Drag2")
            var newOrder = [];
            $(".content_type .sortable_content_list").each(function (index) {
                newOrder.push(content[$(this).data("id")]);
            });
            content = newOrder;
            callContent(content);
        },
    });

    $(".sortable_content_list").sortable({
        items: ".sortable_content_data", // selector for sortable items inside .sortable_content_list
        handle: ".drag-handle", // selector for the handle
        update: function (event, ui) {
            console.log("Child Drag2")
            var parentIndex = $(this).data("id");
            var childOrder = [];

            $(this)
                .find(".sortable_content_data")
                .each(function () {
                    var dataIndex = $(this).data("id");
                    // Push the content item corresponding to the dataIndex
                    childOrder.push(content[parentIndex].value['content'][dataIndex]);
                });

            // Update the content array with the reordered content items
            content[parentIndex].value['content'] = childOrder;

            callContent(content);
        },
    });
}

function openModal(target, index) {
    $(".save-tag").attr("data-target", target);
    $(".save-tag").attr("data-tag-id", index);
    $("#addContentModel").attr("data-open-id", index);
}

function deleteContent(index) {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to delete this content?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
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
        confirmButtonText: "Yes, delete it!"
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
                var maxWidth = 500, maxHeight = 513;
                $(".selected-image-preview").html(""); // Clear previous images
                if (img.width > maxWidth || img.height > maxHeight) {
                    $('.image_error').text('Image dimensions should not exceed ' + maxWidth + ' * ' + maxHeight);
                    e.target.value = ''; // Clear the file input
                } else {
                    $('.image_error').text('');
                    var imageHtml = `<div class="image-container mb-3 mt-3 d-flex">
                            <img src="${img.src}" alt="Image" class="img-thumbnail img-editor image-check"  onerror="this.parentElement.remove()">
                            <div class="image-actions mt-3 d-flex">
                                <input type="hidden" class="image_url" name="imageFile" value='${img.src}'>
                                <input type="text" class="form-control image_alt_name" id="imageName"  name="imageName" placeholder="Enter image name">
                                <button class="btn btn-danger delete-image mx-2">Delete</button>
                            </div>
                        </div>`;
                    $(".selected-image-preview").append(imageHtml);
                }
            }
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
            var video = document.createElement('video');
            video.onloadedmetadata = function () {
                var maxWidth = 500, maxHeight = 513;
                $(".selected-video-preview").html(""); // Clear previous videos
                if (this.videoWidth > maxWidth && this.videoHeight > maxHeight) {
                    $(".video_error").text("Video dimensions should not exceed " + maxWidth + "x" + maxHeight + " pixels.");
                    $("#video_upload").val(''); // Clear the file input
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
    $(this).closest(".image_url").val('');
});

$(document).on("click", ".delete-video", function () {
    $(this).closest(".video-container").remove();
    $(this).closest(".video_url").val('');
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

$(document).on("click", ".edit-content", function () {
    var dataId = $(this).attr("data-id");
    var dataIndex = $(this).attr("data-index");
    var editData = content[dataId].value[dataIndex];
    var type = dataIndex;
    $("#contentModel").modal("show");
    $(".save-tag").attr("data-target", "edit");
    $(".save-tag").attr("data-tag-id", dataId);
    $(".save-tag").attr("data-index", dataIndex); // Add index attribute to target specific content item
    $("#addContentModel").attr("data-open-id", dataIndex);
    $(".modal-title").text("Edit " + type);
    if (type === "button") {
        $(".contentType-body").html(`
                <input type="hidden" id="contentType" value="${type}">
                <div class="col-md-12">
                    <label for="button_name" class="form-label">Button</label>
                    <input type="text" id="button_name" name="button_name" class="form-control" value="${editData.value}">
                    <span class="text-danger button_name_error"></span>
                </div>
                <div class="col-md-12 mt-3">
                    <label for="content_button_link" class="form-label">Button Link</label>
                    <input type="url" id="content_button_link" name="button_link" class="form-control" value="${editData.link}">
                    <span class="text-danger button_link_error"></span>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnOpenInNewTab" ${editData.openinnewtab == 1 ? 'checked' : ''}>
                    <label class="form-check-label" for="contentBtnOpenInNewTab">Open in new tab</label>
                </div>
                <div class="col-md-12 mt-3 form-check">
                    <input type="checkbox" class="form-check-input" id="contentBtnNofollow" ${editData.nofollow == 1 ? 'checked' : ''}>
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
                            <input type="file" id="image_upload" name="image_upload" multiple accept="image/*" class="form-control" style="display: none;">
                        </div>`;


        imageHtml += `<div class="selected-image-preview"><div class="image-container mb-3 mt-3 d-flex">
                <img src="${editData.link}" alt="Image ${editData.alt}" class="img-thumbnail img-editor image-check" OnError="this.parent('.image-container').remove()">
                <div class="image-actions mt-3 d-flex">
                    <input type="hidden" class="image_url" name="imageFile" value='${editData.link}'>
                    <input type="text" class="form-control image_alt_name" id="imageName" value='${editData.alt}' name="imageName" placeholder="Enter image name">
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
            <div class="col-md-12">
                <label for="description_type" class="form-label">Description</label>
                <div id="description_type">${editContent}</div>
                <span class="text-danger description-error"></span>
            </div>
        `);
        if (type == 'checklists') {
            var parsedContent = $(editContent);
            var isChecked = parsedContent.filter('[data-checked="true"]').length > 0;
        }
        tinyEditorInit(type, isChecked);
    }
});

$(document).on("click", ".edit-ads", function () {
    var id = $(this).attr("data-id");
    $(".btn-ads-save").attr("data-id", id);
    $(".btn-ads-save").attr("data-target", "edit");
    var editData = content[id].value;

    $("#ads_title_value").val(editData.title);
    $("#ads_colors").val(editData.color);
    $('#ads_button_option').val(editData.color);
    $("#adsopenInNewTab").prop('checked', editData.button_target == 1 ? true : false);
    $("#adsnofollow").prop('checked', editData.button_rel == 1 ? true : false);
    $("#ads_colors")
        .siblings(".lccp-preview")
        .css("background", editData.color);
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

$(document).on("click", ".edit-template", function () {
    var id = $(this).attr("data-id");
    $(".btn-template-save").attr("data-id", id);
    $(".btn-template-save").attr("data-target", "edit");
    var editData = content[id].value;

    $("#template_keyword").val(editData.keyword);
    $("#template_keyword_link").val(editData.keyword_link);
    $("#template_link_target").val(editData.keyword_target).change();
    $("#templete_target").prop('checked', editData.link_target == 1 ? true : false);
    $("#templete_rel").prop('checked', editData.link_rel == 1 ? true : false);

    $("#addTemplateKeywordModel").modal("show");
});

$(document).on("click", ".showTemplete", function () {
    var isapiExists = content.some(function (obj) {
        return obj.type == "api";
    });

    if (isapiExists) {
        alert("Templete already exists.");
    } else {


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
        imgElement.src = reader.result;
    };
    reader.readAsDataURL(input.files[0]);
}

function previewFile(event) {
    var input = event.target;
    var file = input.files[0];
    var reader = new FileReader();

    reader.onload = function () {
        var previewImageElement = $("#banner-image-preview");
        var previewVideoElement = $("#banner-video-preview");
        var previewDiv = $("#banner-image-video-preview-div");
        var bannerType = $("#banner_type");

        if (file.type.startsWith('image/')) {
            var img = new Image();
            img.onload = function () {
                if (this.width <= 500 && this.height <= 400) {
                    previewImageElement.removeClass("d-none").addClass("d-flex").attr("src", reader.result);
                    previewVideoElement.removeClass("d-flex").addClass("d-none");
                    bannerType.val('image');
                    $(".banner-image-error").text("");
                    $(".save-page-data").removeAttr('disabled');
                    $("button.btn.btn-danger.removeBannerBtn").show();
                } else {
                    $(".banner-image-error").text("Image dimensions should not exceed 500x400 pixels.");
                    previewImageElement.removeClass("d-flex").addClass("d-none").attr("src", '');
                    previewVideoElement.addClass("d-none");
                    bannerType.val('');
                    input.value = ''; // Clear the file input
                    $(".save-page-data").attr('disabled', 'disabled');
                    $("button.btn.btn-danger.removeBannerBtn").hide();
                }
            };
            img.src = reader.result;
        } else if (file.type.startsWith('video/')) {
            var video = document.createElement('video');
            video.onloadedmetadata = function () {
                if (this.videoWidth <= 500 && this.videoHeight <= 400) {
                    previewVideoElement.removeClass("d-none").addClass("d-flex").attr("src", reader.result);
                    previewImageElement.removeClass("d-flex").addClass("d-none");
                    bannerType.val('video');
                    $(".banner-image-error").text("");
                    $(".save-page-data").removeAttr('disabled');
                    $("button.btn.btn-danger.removeBannerBtn").show();

                } else {
                    $(".banner-image-error").text("Video dimensions should not exceed 500x400 pixels.");
                    previewVideoElement.removeClass("d-flex").addClass("d-none").attr("src", "");
                    previewImageElement.addClass("d-none");
                    bannerType.val('');
                    input.value = ''; // Clear the file input
                    $(".save-page-data").attr('disabled', 'disabled');
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
            var maxWidth = 300, maxHeight = 300;
            if (img.width > maxWidth || img.height > maxHeight) {
                $('.ads_image_error').text('Image dimensions should not exceed ' + maxWidth + ' * ' + maxHeight);

                $("#ads-image-preview-div").removeClass("d-flex").addClass("d-none");

                $("#ads-image-preview").attr('src', '');

                $("#ads_image_link").val('');
                input.value = ''; // Clear the file input
            } else {
                $('.ads_image_error').text('');
                $("#ads-image-preview-div").removeClass("d-none").addClass("d-flex");

                $("#ads-image-preview").attr('src', reader.result);

                $("#ads_image_link").val(reader.result);
            }
        }
    };
    reader.readAsDataURL(input.files[0]);
}

$(document).on("click", ".btn-ads-save", function () {
    var title = $("#ads_title_value").val();
    var colors = $("#ads_colors").val();
    var button = $("#ads_button_value").val();
    var btn_link = $("#ads_button_link").val();
    var desc = ads_description.root.innerHTML;
    var image = $("#ads_image_link").val();
    var adsopenInNewTab = $("#adsopenInNewTab").prop('checked') ? 1 : 0;
    var adsnofollow = $('#adsnofollow').prop('checked') ? 1 : 0;
    var dataId = $(this).attr("data-id");

    if (title.trim() == '') $('.ads_title_value_error').text('Please fill this field');
    else $('.ads_title_value_error').text('');

    if (colors.trim() == '') $('.ads_colors_error').text('Please fill this field');
    else $('.ads_colors_error').text('');

    if (button.trim() == '') $('.ads_button_error').text('Please fill this field');
    else $('.ads_button_error').text('');

    if (btn_link.trim() == '') $('.ads_button_link_error').text('Please fill this field');
    else if (!isValidURL(btn_link.trim())) $('.ads_button_link_error').text('Invalid Url.');
    else $('.ads_button_link_error').text('');

    if (ads_description.getText().trim() == '') $('.ads_description_error').text('Please fill this field');
    else $('.ads_description_error').text('');

    if (image.trim() == '') $('.ads_image_error').text('Please fill this field');
    else $('.ads_image_error').text('');

    if (title.trim() == '' || colors.trim() == '' || button.trim() == '' || (btn_link.trim() == '' || !isValidURL(btn_link.trim())) || ads_description.getText().trim() == '' || image.trim() == '') {
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
            button_rel: adsnofollow
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

$(document).on("click", ".btn-template-save", function () {
    var keywords = $("#template_keyword").val();
    var keywords_link = $("#template_keyword_link").val();
    var keywords_target = $("#template_link_target").val();
    var templete_target = $("#templete_target").prop('checked') ? 1 : 0;
    var templete_rel = $("#templete_rel").prop('checked') ? 1 : 0;
    if (keywords.trim() == '') {
        $('.template_keyword_error').text('Please fill this field');
    } else {
        $('.template_keyword_error').text('');
    }

    if (keywords_target == 'loadmore_other_page') {
        if (keywords_link.trim() == '') {
            $('.template_keyword_link_error').text('Please fill this field.');
        } else if (!isValidURL(keywords_link.trim())) {
            $('.template_keyword_link_error').text('Invalid Url.');
        } else {
            $('.template_keyword_link_error').text('');
        }
        if (keywords.trim() == '' || keywords_link.trim() == '' || !isValidURL(keywords_link.trim())) return;
    } else {
        if (keywords.trim() == '') return;
        keywords_link = '';
        templete_target = 0;
        templete_rel = 0;
    }


    formChanged = true;
    var addData = {
        type: "api",
        value: {
            keyword: keywords,
            keyword_target: keywords_target,
            keyword_link: keywords_link,
            link_target: templete_target,
            link_rel: templete_rel
        }
    };
    if ($(this).attr("data-target") === "edit") {
        var dataId = $(this).attr('data-id');
        content[dataId] = addData;
    } else {
        content.push(addData);
    }
    blank_template_form();
    callContent(content);
    $("#addTemplateKeywordModel").modal("hide");
});

function blank_template_form() {
    $("#template_keyword").val("");
    $("#template_keyword_link").val("");
    $("#templete_target").prop('checked', false);
    $("#templete_rel").prop('checked', false);
}


$(document).on('shown.bs.modal', "#addTemplateKeywordModel", function () {
    $('.template_keyword_error').text('');
});

$(document).on('shown.bs.modal', "#AdsModel", function () {
    $('.ads_title_value_error').text('');
    $('.ads_colors_error').text('');
    $('.ads_button_error').text('');
    $('.ads_button_link_error').text('');
    $('.ads_description_error').text('');
    $('.ads_image_error').text('');
});

function blank_ads_form() {
    $("#ads_title_value").val("");
    $("#ads_colors").val("");
    $('#ads_button_option').val("_self");
    $("#ads_colors")
        .siblings(".lccp-preview")
        .css(
            "background",
            "linear-gradient(90deg, rgba(255, 255, 255, .4), #000)"
        );
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

function tinyEditorInit(type, checkbox = false) {
    var tool = {};
    var styleLevel = null;
    var contentType = null;
    switch (type) {
        case "h2":
            contentType = "header";
            styleLevel = parseInt(type.substring(1)); // Extract the number from 'H2', 'H3', etc.
            tool = { header: [type.substring(1)] };
            break;
        case "h3":
            contentType = "header";
            styleLevel = parseInt(type.substring(1)); // Extract the number from 'H2', 'H3', etc.
            tool = { header: [type.substring(1)] };
            break;
        case "h4":
            contentType = "header";
            styleLevel = parseInt(type.substring(1)); // Extract the number from 'H2', 'H3', etc.
            tool = { header: [type.substring(1)] };
            break;
        case "h5":
            contentType = "header";
            styleLevel = parseInt(type.substring(1)); // Extract the number from 'H2', 'H3', etc.
            tool = { header: [type.substring(1)] };
            break;
        case "h6":
            contentType = "header";
            styleLevel = parseInt(type.substring(1)); // Extract the number from 'H2', 'H3', etc.
            tool = { header: [type.substring(1)] };
            break;
        case "p":
            contentType = "header";
            styleLevel = null; // Extract the number from 'H2', 'H3', etc.
            tool = {};
            break;
        case "content":
            contentType = "header";
            styleLevel = null; // Extract the number from 'H2', 'H3', etc.
            tool = {};
            break;
        case "bullets":
            contentType = "list";
            styleLevel = "bullet"; // Extract the number from 'H2', 'H3', etc.
            tool = { list: "bullet" };
            break;
        case "numbers":
            contentType = "list";
            styleLevel = "ordered"; // Extract the number from 'H2', 'H3', etc.
            tool = { list: "ordered" };
            break;
        case "checklists":
            contentType = "list";
            styleLevel = checkbox ? "checked" : "unchecked"; // Extract the number from 'H2', 'H3', etc.
            tool = { list: "check" };
            break;
    }

    if (type == 'h2') {
        var toolbar = [
            ["undo", "redo"], // Add undo and redo buttons to the toolbar
            ["bold"],
            [tool],
            [{ 'align': [] }],
            [{ 'color': [] }],
        ]
    } else {
        var toolbar = [
            ["undo", "redo"], // Add undo and redo buttons to the toolbar
            ["bold", "italic"],
            [tool],
            [{ 'align': [] }],
            [{ 'color': [] }],
            ["link"],
            [{ 'target': '_self' }, { 'target': '_blank' }] // Target dropdown menu
        ]
    }


    quill = new Quill("#description_type", {
        theme: "snow",
        modules: {
            toolbar: {
                container: toolbar,
                handlers: {
                    'link': function (value) {
                        if (value) {
                            $(".linkUrlError").text('');
                            $('#linkModal').modal('show');
                            $('#linkUrl').val('');
                            $('#openInNewTab').prop('checked', false);
                            $('#nofollow').prop('checked', false);

                            var insertLinkHandler = function () {
                                insertLink(this.quill);
                                document.getElementById('insertLink').removeEventListener('click', insertLinkHandler);
                            }.bind(this);

                            document.getElementById('insertLink').addEventListener('click', insertLinkHandler);
                        }
                    },
                    'target': function (value) {
                        this.quill.format('link', { target: value });
                    }
                },
            },
            history: {
                userOnly: false, // Record changes made by the user
                maxStack: 500, // Maximum number of actions to store in the history stack
            },
            clipboard: {
                matchVisual: false, // Disable Quill's built-in paste formatting
            }
        },
    });

    // Add event listener to detect Enter key press
    // quill.container.addEventListener('keydown', function(event) {
    //     console.log(event.key);
    //     if (event.key === 'Enter') {
    //         const selection = quill.getSelection();
    //         if (selection) {
    //             quill.setSelection(selection.index + 1); // Move cursor to the end of the editor
    //         }
    //     }
    // });

    // Add event listener to detect Enter key press
    quill.on('text-change', function (delta, oldDelta, source) {
        if (source === 'user') {
            const ops = delta.ops;
            const length = ops.length;

            for (let i = 0; i < length; i++) {
                const op = ops[i];
                if (op.insert) {
                    const text = op.insert;
                    const index = quill.getLength() - text.length;
                    quill.formatText(index, text.length, { [contentType]: styleLevel });
                }
            }

            formChanged = true;
        } else {
            const index = quill.getSelection().index;
            quill.formatText(index + 1, index + 2, { [contentType]: styleLevel });
        }
    });

    quill.on('editor-change', function (eventName, ...args) {
        if (eventName === 'text-change') {
            handlePasteEvent(quill);
        }
    });

    function handlePasteEvent(quill) {
        quill.clipboard.addMatcher(Node.TEXT_NODE, function (node, delta) {
            const pastedText = node.data;
            const currentFormat = quill.getFormat(); // Get current format at cursor position

            // Regular expression to match header tags
            const headerRegex = /<h([2-6])>/g;

            // Check if pasted text contains header tags
            if (pastedText.match(headerRegex)) {
                // Extract the header level from the pasted text
                const match = headerRegex.exec(pastedText);
                const headerLevel = match[1];

                // Determine the desired format based on the header level
                const desiredFormat = currentFormat;

                // Apply the desired format to the pasted text
                delta.ops.forEach(op => {
                    op.attributes = desiredFormat;
                });
            } else {
                // If no header tags found, apply the appropriate format based on content type
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
                    // Add cases for other content types as needed
                }

                // Apply the appropriate format to the pasted text
                if (contentType === "header") {
                    delta.ops.forEach(op => {
                        op.attributes = currentFormat; // Default to h1 if no specific header format applied
                    });
                }
            }

            return delta;
        });
    }

    if (quill) {
        quill.root.addEventListener('paste', function (event) {
            event.preventDefault(); // Prevent default paste behavior

            var pastedText = (event.originalEvent || event).clipboardData.getData('text/plain');

            if (pastedText) {
                var range = quill.getSelection(true);

                if (range) {
                    // If there is a selection, delete the selected text

                    setTimeout(() => {
                        // Insert the pasted text at the start index of the selection
                        quill.insertText(range.index, pastedText);

                        // Set the selection to the end of the pasted text
                        quill.setSelection(range.index + pastedText.length);
                        handlePasteEvent(quill);
                    }, 5);
                } else {
                    setTimeout(() => {
                        // If no selection, insert pasted text at the end of the document
                        quill.insertText(quill.getLength(), pastedText);

                        // Set the selection to the end of the pasted text
                        quill.setSelection(quill.getLength());
                        handlePasteEvent(quill);
                    }, 5);
                }
            }
        });
    }

    // Set the content format to the selected tag
    if (styleLevel !== null && contentType !== null) {
        quill.format(contentType, styleLevel);
    }

    $('.ql-undo').html('<i class="fa-solid fa-rotate-left"></i>');
    $('.ql-redo').html('<i class="fa-solid fa-rotate-right"></i>');
}

$(document).on('click', '.ql-undo', function () {
    var editor;

    // Find the corresponding Quill instance based on the clicked toolbar button
    if ($(this).parents('.ql-toolbar').siblings('#description_type').length) {
        editor = quill;
    } else if ($(this).parents('.ql-toolbar').siblings('#meta_desc').length) {
        editor = meta_desc;
    } else if ($(this).parents('.ql-toolbar').siblings('#description').length) {
        editor = description;
    } else if ($(this).parents('.ql-toolbar').siblings('#ads_description').length) {
        editor = ads_description;
    }

    // Perform undo action if the editor is defined
    if (editor) {
        editor.history.undo();
    }
});

$(document).on('click', '.ql-redo', function () {
    var editor;

    // Find the corresponding Quill instance based on the clicked toolbar button
    if ($(this).parents('.ql-toolbar').siblings('#description_type').length) {
        editor = quill;
    } else if ($(this).parents('.ql-toolbar').siblings('#meta_desc').length) {
        editor = meta_desc;
    } else if ($(this).parents('.ql-toolbar').siblings('#description').length) {
        editor = description;
    } else if ($(this).parents('.ql-toolbar').siblings('#ads_description').length) {
        editor = ads_description;
    }

    // Perform redo action if the editor is defined
    if (editor) {
        editor.history.redo();
    }
});

$(document).on('click', '.btn-faqs-save', function () {
    let questionInput = $("#question").val();
    if (answer.getText().trim() == '') $('.answer_error').text('Please fill this field.');
    else $('.answer_error').text('');



    if (questionInput.trim() == '') $('.question_error').text('Please fill this field.');
    else $('.question_error').text('');

    if (questionInput.trim() == '' || answer.getText().trim() == '') return;



    if ($(this).attr("data-type") == 'edit') {
        var dataId = $(this).attr("data-index");
        if (dataId == "" || dataId == undefined) {
            var index = faqs.length; // Determine the index based on the length of the outer array
            faqs[index] = [];
        } else {
            var index = dataId;
        }

        faqs[index] = {
            'question': questionInput,
            'answer': answer.root.innerHTML
        };
    } else {
        faqs.push({
            'question': questionInput,
            'answer': answer.root.innerHTML
        });
    }

    callFaqs(faqs);

    $('.btn-faqs-save').attr('data-type', '');
    $('.btn-faqs-save').attr('data-index', 0);

    // question.root.innerHTML = '';
    answer.root.innerHTML = '';
    $("#question").val('');
    // $("#answer").val('');
    $("#faqsModel").modal("hide");
});

function callFaqs(faqs) {
    var data = "";
    faqs.forEach((ele, index) => {
        data += `<div class="mb-3 p-2 border border-2 sortable_faqs_list content_show_${index}" data-id="${index}"><div class="drag-handle">&#9776;</div>
                <div class="col-12 mb-2 py-2 px-3 sortable_content_data">
                    <div class="row">
                        <div class="col-md-2">Question : </div>
                        <div class="col-md-10">${ele.question}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">Answer : </div>
                        <div class="col-md-10">${ele.answer}</div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" onclick="editFaq(${index})">Edit Faq</button>
                    <button type="button" class="btn btn-danger" onclick="deleteFaq(${index})">Remove Faq</button>
                </div>
            </div>`;
    });
    $(".faqs-content").html(data);
    $("#faqs").val(JSON.stringify(faqs));
    initializeFaqs();
}

function initializeFaqs() {
    $(".faqs-content").sortable({
        items: ".sortable_faqs_list", // selector for sortable items
        handle: ".drag-handle", // selector for the handle
        update: function (event, ui) {
            var newOrder = [];
            $(".faqs-content .sortable_faqs_list").each(function (index) {
                newOrder.push(faqs[$(this).data("id")]);
            });
            faqs = newOrder;
            callFaqs(faqs);
        },
    });
}

function editFaq(index) {
    answer.root.innerHTML = faqs[index].answer;
    $("#question").val(faqs[index].question);
    $('.btn-faqs-save').attr('data-type', 'edit');
    $('.btn-faqs-save').attr('data-index', index);
    $("#faqsModel").modal("show");
}

function deleteFaq(index) {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to delete this faq?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            faqs.splice(index, 1);
            callFaqs(faqs);
        }
    });
}

$(document).on('shown.bs.modal', '#faqsModel', function () {
    $('.question_error').text('');
    $('.answer_error').text('');
})

$(document).on('hide.bs.modal', '#faqsModel', function () {
    $('.btn-faqs-save').attr('data-type', '');
    $('.btn-faqs-save').attr('data-index', 0);

    // question.root.innerHTML = '';
    answer.root.innerHTML = '';
    $("#question").val('');
});
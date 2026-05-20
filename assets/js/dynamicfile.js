$(document).ready(function () {
    dynamicFileCmp();
});

function getStorageLink(src) {
    if (!src) return "";
    const base64Regex = /^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/;
    const hasBase64Prefix = src.startsWith('data:image');
    const base64String = hasBase64Prefix ? src.split(',')[1] : src;
    if (base64Regex.test(base64String)) {
        return src;
    } else {
        // *Debug
        if (src.startsWith("https://assets.craftyart.in") || src.startsWith(STORAGE_URL) || src.startsWith(storageUrl)) {
            return src;
        }
        return `${storageUrl}${src}`
        // release
        // if (src.startsWith("https://assets.craftyart.in") || src.startsWith(STORAGE_URL)) {
        //     return src;
        // }
        // return `${STORAGE_URL}${src}`
    }
}

function resetDynamicFileValue(id) {
    const input = document.getElementById(id)
    if (input) {
        input.dataset.value = "";
        const wrapper = input.closest(".dynamic-file-input");
        const urlInput = wrapper.querySelector(".url-input");
        const previewImg = wrapper.querySelector(".image-preview");
        const base64Input = wrapper.querySelector("input[type='hidden']");
        urlInput.value = ""
        previewImg.src = "";
        base64Input.value = "";
    }
}

function dynamicFileCmp() {

    document.querySelectorAll("input[type='file'].dynamic-file").forEach(input => {
        const isFieldRequired = input.dataset.required == null ? true : input.dataset.required;
        if (input.dataset.enhanced) {
            const wrapper = input.closest(".dynamic-file-input");
            const urlInput = wrapper.querySelector(".url-input");
            const previewImg = wrapper.querySelector(".image-preview");
            const previewContainer = previewImg.parentElement;
            const base64Input = wrapper.querySelector("input[type='hidden']");
            const dropdown = wrapper.querySelector("select");
            if (!base64Input.value && input.dataset.value && isFieldRequired) {
                urlInput.value = input.dataset.value;
                previewImg.src = input.dataset.value;
                base64Input.value = input.dataset.value;
                previewContainer.style.display = "block";
                if (input.dataset.value.startsWith("data:")) {
                    dropdown.value = "file";
                    input.style.display = "block";
                    urlInput.style.display = "none";
                } else {
                    dropdown.value = "url";
                    input.style.display = "none";
                    urlInput.style.display = "block";
                }
            } else {
                console.log("Dsasasdsads ", base64Input.value);
                if (base64Input.value && !base64Input.value.startsWith("data:")) {
                    previewImg.src = base64Input.value;
                    urlInput.value = base64Input.value;
                    dropdown.value = "url";
                    input.style.display = "none";
                    urlInput.style.display = "block";
                    previewContainer.style.display = "block";
                } else {
                    urlInput.value = ""
                    previewImg.src = "";
                    base64Input.value = "";
                    previewContainer.style.display = "none";
                }
            }
            return; // Exit since it's already enhanced
        }

        input.dataset.enhanced = "true";
        const wrapper = document.createElement("div");
        wrapper.classList.add("dynamic-file-input", "mb-3");
        wrapper.style.width = "100%";

        // Create a flex container for dropdown and file input
        const controlsRow = document.createElement("div");
        controlsRow.style.display = "flex";
        controlsRow.style.gap = "10px";
        controlsRow.style.alignItems = "center";
        controlsRow.style.marginBottom = "10px";

        // Create dropdown for File or URL selection
        const dropdown = document.createElement("select");
        dropdown.classList.add("form-select");
        dropdown.style.width = "120px";
        dropdown.style.flexShrink = "0";
        dropdown.innerHTML = `
            <option value="file" selected>File</option>
            <option value="url">URL</option>
        `;

        // Create URL input field
        const urlInput = document.createElement("input");
        urlInput.type = "text";
        urlInput.classList.add("form-control", "url-input");
        urlInput.placeholder = "Enter Image URL";
        urlInput.style.display = "none";
        urlInput.style.flex = "1";

        // Create image preview container
        const previewContainer = document.createElement("div");
        previewContainer.style.marginTop = "10px";
        previewContainer.style.border = "1px solid #e0e0e0";
        previewContainer.style.borderRadius = "6px";
        previewContainer.style.padding = "10px";
        previewContainer.style.backgroundColor = "#f8f9fa";
        previewContainer.style.display = "none";

        // Create image preview
        const previewImg = document.createElement("img");
        previewImg.classList.add("img-thumbnail", "image-preview");
        previewImg.style.maxWidth = "200px";
        previewImg.style.maxHeight = "200px";
        previewImg.style.width = "auto";
        previewImg.style.height = "auto";
        previewImg.style.objectFit = "contain";
        previewImg.style.display = "block";

        const base64Input = document.createElement("input");
        base64Input.type = "hidden";

        if (input.dataset.imgstoreId) {
            previewImg.id = input.dataset.imgstoreId;
        }
        if (input.dataset.nameset) {
            base64Input.name = input.dataset.imgstoreId;
        }

        if (input.dataset.setclass) {
            previewImg.classList.add(input.dataset.imgstoreId);
        }

        // Style the file input
        input.style.flex = "1";

        // Insert elements dynamically
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(controlsRow);
        controlsRow.appendChild(dropdown);
        controlsRow.appendChild(input);
        controlsRow.appendChild(urlInput);
        previewContainer.appendChild(previewImg);
        wrapper.appendChild(previewContainer);
        wrapper.appendChild(base64Input);

        // Set accept attribute from data-accept
        if (input.dataset.accept) {
            input.setAttribute("accept", input.dataset.accept);
        }
        if (input.dataset.value) {
            urlInput.value = input.dataset.value;
            previewImg.src = input.dataset.value;
            base64Input.value = input.dataset.value;
            previewContainer.style.display = "block";
            if (input.dataset.value.startsWith("data:")) {
                dropdown.value = "file";
                input.style.display = "block";
                urlInput.style.display = "none";
            } else {
                dropdown.value = "url";
                input.style.display = "none";
                urlInput.style.display = "block";
            }
        }

        // Dropdown change event
        dropdown.addEventListener("change", function () {
            if (this.value === "file") {
                input.style.display = "block";
                urlInput.style.display = "none";
            } else {
                input.style.display = "none";
                urlInput.style.display = "block";
            }
        });

        input.addEventListener("change", function () {

            const file = this.files[0];
            if (file) {
                let isDataValidate = input.dataset.validate ?? "true";
                console.log("isDataValidate", isDataValidate, "---", typeof isDataValidate)
                if (isDataValidate == "false" || validateImage(this)) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        base64Input.value = e.target.result;
                        previewContainer.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // URL input event
        urlInput.addEventListener("input", function () {
            const urlPattern = new RegExp(`^${STORAGE_URL.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}.*\\.(png|jpg|jpeg|webp|svg|gif)$`, "i");
            if (urlPattern.test(this.value)) {
                //release
                // if(urlPattern.test(this.value)){
                previewImg.src = this.value;
                base64Input.value = this.value;
                previewContainer.style.display = "block";
            } else {
                if (isFieldRequired == "false") {
                    previewImg.src = this.value;
                    base64Input.value = "";
                    previewContainer.style.display = this.value ? "block" : "none";
                } else {
                    previewImg.src = input.dataset.value ?? "";
                    base64Input.value = "";
                    previewContainer.style.display = "none";
                }
            }
        });
    });

    function validateImage(inputElement) {
        const file = inputElement.files[0];
        if (file) {
            const fileType = file.type;
            const fileSize = file.size / 1024;
            // console.log("ygefe", fileType);
            if (
                !["image/jpeg", "image/webp", "image/svg+xml", "image/gif"].includes(
                    fileType
                )
            ) {
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
                if (fileSize > 50) {
                    alert(
                        "Image size must be less than 50KB for JPG, JPEG, or SVG files."
                    );
                    inputElement.value = "";
                    return false;
                }
            } else if (fileType === "image/webp") {
                if (fileSize > 200) {
                    alert("Image size must be less than 200KB for WebP files.");
                    inputElement.value = "";
                    return false;
                }
            } else if (fileType === "image/gif") {
                if (fileSize > 300) {
                    alert("Image size must be less than 300KB for GIF files.");
                    inputElement.value = "";
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}

@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<style>
    .canvas-container {
        border: 1px solid #ddd;
        margin-top: 20px;
        margin-bottom: 20px;
        background: white;
        overflow: hidden;
        max-width: 100%;
        width: 100%;
        min-height: 500px;
        position: relative;
        text-align: center;
        padding: 20px;
    }

    .canvas-controls {
        margin-top: 15px;
        display: none;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .loading-screen {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    #fabricCanvas {
        border: 1px solid #ccc;
        background: white;
        display: block;
        margin: 0 auto;
        max-width: 100%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .image-preview-container {
        display: flex;
        gap: 20px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .image-preview {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px;
        background: #f9f9f9;
        text-align: center;
    }

    .preview-image {
        max-width: 200px;
        max-height: 200px;
        object-fit: contain;
    }

    .dimension-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        text-align: center;
    }

    .canvas-buttons {
        display: flex;
        gap: 10px;
        margin: 10px 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-purple {
        background-color: #6f42c1;
        color: white;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .count-badge {
        background-color: #6c757d;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        margin-left: 10px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }

    .canvas-section {
        margin-top: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .canvas-placeholder {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        background: #fff;
        border: 3px dashed #dee2e6;
        border-radius: 8px;
        font-size: 16px;
    }

    .status-info {
        background: #e9ecef;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
        text-align: center;
        font-weight: bold;
    }
</style>

<div class="main-container">
    <div class="xs-pd-10-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div class="p-3">
                    <form method="post" class="attire-access" id="dynamic_form" enctype="multipart/form-data">
                        <span id="result"></span>
                        @csrf

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Post Name</h6>
                                    <input type="text" class="form-control attire-access" id="post_name" name="post_name"
                                        placeholder="Enter post name" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Select Skin Color</h6>
                                    <input type="color" class="form-control" id="skin_color" name="skin_color"
                                        value="#ffd9b3" required>
                                </div>
                            </div>
                        </div>

                        <!-- Image Uploads -->
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Attire Image</h6>
                                    <input type="file" class="form-control" id="attire_url" accept="image/*"
                                        required>
                                    <small class="text-muted">Upload the main attire image for editing</small>
                                    <div id="attirePreview" class="image-preview-container"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Preview Image</h6>
                                    <input type="file" class="form-control dynamic-file"
                                        data-accept=".jpg, .jpeg, .webp, .svg, .png" data-imgstore-id="preview_url"
                                        data-nameset="true" data-validate="false" required>
                                </div>
                            </div>
                        </div>

                        <!-- Canvas Editor Section -->
                        <div class="canvas-section">
                            <h4>Coordinate Image</h4>
                            <div id="canvasStatus" class="status-info">
                                Please upload an attire image to start editing
                            </div>

                            <!-- Canvas Controls -->
                            <div class="canvas-controls" id="canvasControls">
                                <div class="canvas-buttons">
                                    <button type="button" class="btn btn-purple" id="addBoyBtn">
                                        <i class="fa fa-male"></i> Add Boy Face
                                    </button>
                                    <button type="button" class="btn btn-purple" id="addGirlBtn">
                                        <i class="fa fa-female"></i> Add Girl Face
                                    </button>
                                    <button type="button" class="btn btn-danger" id="deleteBtn">
                                        <i class="fa fa-trash"></i> Delete Selected
                                    </button>
                                    <button type="button" class="btn btn-success" id="clearAllBtn">
                                        <i class="fa fa-refresh"></i> Clear All
                                    </button>

                                    <div class="ms-auto">
                                        <span class="count-badge">
                                            <i class="fa fa-male"></i> Boys: <span id="boyCount">0</span>
                                        </span>
                                        <span class="count-badge">
                                            <i class="fa fa-female"></i> Girls: <span id="girlCount">0</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="canvas-instructions">
                                    <small class="text-muted">
                                        • Click on buttons to add faces • Drag to reposition • Select and press Delete
                                        to remove • Resize using corners
                                    </small>
                                </div>
                            </div>

                            <!-- Canvas Container -->
                            <div class="canvas-container" id="canvasContainer">
                                <canvas id="fabricCanvas"></canvas>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-check"></i> Submit Design
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Screen -->
<div id="main_loading_screen" class="loading-screen">
    <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-3 text-white">
        <h4>Processing Your Design...</h4>
    </div>
</div>

@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        let canvas = null;
        let scaleFactor = 1;
        let boyCount = 0;
        let girlCount = 0;
        let attireBase64 = null;
        let backgroundImage = null;
        let attireAspectRatio = null;
        let attireWidth = 0;
        let attireHeight = 0;

        // Initialize Fabric.js canvas immediately
        function initializeCanvas() {
            // Clear existing canvas if any
            if (canvas) {
                canvas.dispose();
            }

            // Create canvas with default size
            canvas = new fabric.Canvas('fabricCanvas', {
                width: 600,
                height: 500,
                backgroundColor: '#f8f9fa',
                preserveObjectStacking: true,
                selection: true
            });

            // Set up canvas events
            canvas.on('object:added', updateCounts);
            canvas.on('object:removed', updateCounts);
            canvas.on('object:modified', updateCounts);
        }

        // Call initialization on page load
        initializeCanvas();

        // Update status message
        function updateStatus(message, type = 'info') {
            const statusEl = $('#canvasStatus');
            statusEl.removeClass('alert-danger alert-success alert-info alert-warning').addClass('alert-' + type);
            statusEl.text(message);
        }

        // Load image to canvas as background
        function loadImageToCanvas(img) {
            try {
                // Clear existing objects except background
                canvas.getObjects().forEach(obj => {
                    if (obj !== backgroundImage) {
                        canvas.remove(obj);
                    }
                });

                // Remove existing background image
                if (backgroundImage) {
                    canvas.remove(backgroundImage);
                }

                // Calculate scale to fit canvas
                const maxWidth = 800;
                const maxHeight = 600;

                scaleFactor = Math.min(
                    maxWidth / img.width,
                    maxHeight / img.height,
                    1
                );

                const scaledWidth = img.width * scaleFactor;
                const scaledHeight = img.height * scaleFactor;

                // Create fabric image from the uploaded image
                fabric.Image.fromURL(attireBase64, function(fabricImg) {
                    // Scale the image
                    fabricImg.set({
                        scaleX: scaleFactor,
                        scaleY: scaleFactor,
                        originX: 'left',
                        originY: 'top',
                        left: 0,
                        top: 0,
                        selectable: false,
                        evented: false,
                        hasControls: false,
                        hasBorders: false,
                        lockMovementX: true,
                        lockMovementY: true,
                        lockRotation: true,
                        lockScalingX: true,
                        lockScalingY: true
                    });

                    // Set canvas size to match image
                    canvas.setWidth(scaledWidth);
                    canvas.setHeight(scaledHeight);

                    // Add image to canvas
                    canvas.add(fabricImg);
                    backgroundImage = fabricImg;

                    // Move image to back
                    canvas.sendToBack(fabricImg);

                    // Render canvas
                    canvas.renderAll();

                    // Show canvas and controls
                    $('#canvasContainer').show();
                    $('#canvasControls').show();

                    updateStatus('Canvas ready! Add boy and girl faces to the image.', 'success');

                }, {
                    crossOrigin: 'anonymous'
                });

            } catch (error) {
                updateStatus('Error loading image to canvas: ' + error.message, 'danger');
            }
        }

        // Handle Attire Image Selection
        $('#attire_url').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!file.type.match('image.*')) {
                alert('Please select a valid image file (JPEG, PNG, etc.)');
                return;
            }

            updateStatus('Loading attire image...', 'info');
            const reader = new FileReader();

            reader.onload = function (event) {
                attireBase64 = event.target.result;
                $('#attirePreview').html(`
                    <div class="image-preview">
                        <img src="${attireBase64}" alt="Attire Preview" class="preview-image">
                        <input type="hidden" value="${attireBase64}" name="attire_url">
                        <div class="dimension-text" id="attireDimension">Loading dimensions...</div>
                    </div>
                `);

                const img = new Image();
                img.onload = function () {
                    attireWidth = img.width;
                    attireHeight = img.height;
                    attireAspectRatio = parseFloat((img.width / img.height).toFixed(4));
                    $('#attireDimension').text(`Dimensions: ${img.width} x ${img.height} (Ratio: ${attireAspectRatio})`);
                    loadImageToCanvas(img);
                };
                img.onerror = () => updateStatus('Error loading attire image dimensions', 'danger');
                img.src = attireBase64;
            };
            reader.readAsDataURL(file);
        });

        // Handle Coordinate Image Selection
        $('#coordinate_url').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!file.type.match('image.*')) {
                alert('Please select a valid image file (JPEG, PNG, etc.)');
                return;
            }

            // Check if attire image is loaded first
            if (!attireWidth || !attireHeight || !attireAspectRatio) {
                alert('Please upload an attire image first');
                $(this).val('');
                return;
            }

            updateStatus('Loading coordinate image...', 'info');
            const reader = new FileReader();

            reader.onload = function (event) {
                const coordinateBase64 = event.target.result;
                $('#coordinatePreview').html(`
                    <div class="image-preview">
                        <img src="${coordinateBase64}" alt="Coordinate Preview" class="preview-image">
                        <input type="hidden" value="${coordinateBase64}" name="coordinate_url">
                        <div class="dimension-text" id="coordinateDimension">Loading dimensions...</div>
                    </div>
                `);

                const img = new Image();
                img.onload = function () {
                    const w = img.width;
                    const h = img.height;
                    const coordinateAspectRatio = parseFloat((w / h).toFixed(4));
                    $('#coordinateDimension').text(`Dimensions: ${w} x ${h} (Ratio: ${coordinateAspectRatio})`);

                    // Enhanced ratio validation with tolerance
                    const ratioDifference = Math.abs(attireAspectRatio - coordinateAspectRatio);
                    const tolerance = 0.002;

                    if (ratioDifference > tolerance) {
                        const errorMsg = `Aspect ratio mismatch!\n
Attire: ${attireWidth}x${attireHeight} (Ratio: ${attireAspectRatio})\n
Coordinate: ${w}x${h} (Ratio: ${coordinateAspectRatio})\n
Difference: ${ratioDifference.toFixed(4)} (Max allowed: ${tolerance})`;

                        updateStatus('Aspect ratio mismatch!', 'danger');
                        alert(errorMsg);
                        $('#coordinatePreview').html('<div class="text-danger">Invalid aspect ratio - Please use same ratio as attire image</div>');
                        $(this).val('');
                        return;
                    }

                    // Load coordinate image to canvas
                    fabric.Image.fromURL(coordinateBase64, function (fabricImg) {
                        // Remove any existing coordinate image
                        canvas.getObjects().forEach(obj => {
                            if (obj.coordinateImage) {
                                canvas.remove(obj);
                            }
                        });

                        // Calculate scale to match attire dimensions on canvas
                        const targetWidth = attireWidth * scaleFactor;
                        const targetHeight = attireHeight * scaleFactor;

                        const scaleX = targetWidth / w;
                        const scaleY = targetHeight / h;

                        fabricImg.set({
                            scaleX: scaleX,
                            scaleY: scaleY,
                            originX: 'left',
                            originY: 'top',
                            left: 0,
                            top: 0,
                            opacity: 0.7,
                            selectable: true,
                            hasControls: true,
                            hasBorders: true,
                            lockRotation: false,
                            lockScalingX: true,
                            lockScalingY: true,
                            lockUniScaling: true,
                            coordinateImage: true
                        });

                        canvas.add(fabricImg);
                        canvas.bringToFront(fabricImg);
                        canvas.renderAll();

                        updateStatus(`Coordinate image loaded successfully!`, 'success');
                    });
                };
                img.onerror = () => updateStatus('Error loading coordinate image', 'danger');
                img.src = coordinateBase64;
            };
            reader.readAsDataURL(file);
        });

        // Handle preview image selection
        $('#preview_url').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const previewBase64 = event.target.result;

                // Show preview image
                $('#previewContainer').html(`
                    <div class="image-preview">
                        <img src="${previewBase64}" alt="Preview Image" class="preview-image">
                        <input type="hidden" value="${previewBase64}" name="preview_url">
                        <div class="dimension-text" id="previewDimension">Loading dimensions...</div>
                    </div>
                `);

                // Get image dimensions
                const img = new Image();
                img.onload = function() {
                    const w = img.width;
                    const h = img.height;
                    const previewAspectRatio = parseFloat((w / h).toFixed(4));

                    // Validate aspect ratio if attire is loaded
                    if (attireAspectRatio) {
                        const ratioDifference = Math.abs(attireAspectRatio - previewAspectRatio);
                        const tolerance = 0.002;

                        if (ratioDifference > tolerance) {
                            $('#previewDimension').html(`<span class="text-danger">Dimensions: ${w} x ${h} (Ratio: ${previewAspectRatio}) - WARNING: Ratio mismatch!</span>`);
                            updateStatus('Preview image ratio mismatch!', 'warning');
                        } else {
                            $('#previewDimension').text(`Dimensions: ${w} x ${h} (Ratio: ${previewAspectRatio})`);
                        }
                    } else {
                        $('#previewDimension').text(`Dimensions: ${w} x ${h} (Ratio: ${previewAspectRatio})`);
                    }
                };
                img.src = previewBase64;
            };
            reader.readAsDataURL(file);
        });

        // Update face counts
        function updateCounts() {
            if (!canvas) return;

            boyCount = 0;
            girlCount = 0;

            canvas.getObjects().forEach(obj => {
                // Skip background image and coordinate images
                if (obj === backgroundImage || obj.selectable === false || obj.coordinateImage) return;

                if (obj.faceType === 'boy') boyCount++;
                if (obj.faceType === 'girl') girlCount++;
            });

            $('#boyCount').text(boyCount);
            $('#girlCount').text(girlCount);

            // Update status
            if (boyCount === 0 && girlCount === 0) {
                updateStatus('Canvas ready! Add boy and girl faces to the image.', 'info');
            } else {
                updateStatus(`Canvas has ${boyCount} boy faces and ${girlCount} girl faces.`, 'success');
            }
        }

        // Add boy face
        $('#addBoyBtn').on('click', function() {
            if (!canvas || !backgroundImage) {
                alert('Please upload an attire image first');
                return;
            }
            addFace('boy', 'Male\nFace', '#3498db');
        });

        // Add girl face
        $('#addGirlBtn').on('click', function() {
            if (!canvas || !backgroundImage) {
                alert('Please upload an attire image first');
                return;
            }
            addFace('girl', 'Female\nFace', '#e83e8c');
        });

        // Add face to canvas
        function addFace(type, label, color) {
            try {
                // Calculate position (center of canvas)
                const centerX = canvas.getWidth() / 2;
                const centerY = canvas.getHeight() / 2;

                // Create face ellipse
                const circle = new fabric.Ellipse({
                    rx: 55,
                    ry: 70,
                    fill: "rgba(0, 0, 0, 0.7)",
                    stroke: "black",
                    strokeWidth: 0.5,
                    originX: "center",
                    originY: "center",
                });

                // Create label
                const text = new fabric.Text(label, {
                    fontSize: 25,
                    fill: '#ffffff',
                    originX: 'center',
                    originY: 'center',
                    textAlign: 'center'
                });

                // Create group
                const group = new fabric.Group([circle, text], {
                    left: centerX,
                    top: centerY,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    faceType: type,
                    lockRotation: false,
                    cornerStyle: 'circle',
                    cornerColor: color,
                    transparentCorners: false,
                    cornerSize: 12,
                    padding: 10
                });

                // Add to canvas
                canvas.add(group);
                canvas.setActiveObject(group);
                canvas.renderAll();

                updateCounts();
            } catch (error) {
                alert('Error adding face: ' + error.message);
            }
        }

        // Delete selected object
        $('#deleteBtn').on('click', function() {
            if (!canvas) return;
            const activeObjects = canvas.getActiveObjects();
            if (activeObjects.length > 0) {
                // Don't allow deleting background image and coordinate images
                const objectsToDelete = activeObjects.filter(obj => obj !== backgroundImage && !obj.coordinateImage);
                if (objectsToDelete.length > 0) {
                    canvas.discardActiveObject();
                    canvas.remove(...objectsToDelete);
                    canvas.renderAll();
                    updateCounts();
                }
            } else {
                alert('Please select a face to delete');
            }
        });

        // Clear all faces
        $('#clearAllBtn').on('click', function() {
            if (!canvas) return;

            if (confirm('Are you sure you want to remove all faces?')) {
                const objects = canvas.getObjects();
                objects.forEach(obj => {
                    // Don't remove background image and coordinate images
                    if (obj !== backgroundImage && obj.selectable !== false && !obj.coordinateImage) {
                        canvas.remove(obj);
                    }
                });
                canvas.renderAll();
                updateCounts();
            }
        });

        // Form submission
        $('#dynamic_form').on('submit', async function(event) {
            event.preventDefault();

            if (!attireBase64) {
                alert('Please select an attire image');
                return;
            }

            if (!canvas || boyCount + girlCount === 0) {
                alert('Please add at least one face to the canvas');
                return;
            }

            // Check if preview image is uploaded
            const previewInput = $('input[name="preview_url"]');
            if (!previewInput.val()) {
                alert('Please upload a preview image');
                return;
            }

            try {
                // Show loading
                $("#main_loading_screen").show();

                // Generate coordinate image
                const coordinateImageBase64 = canvas.toDataURL({
                    format: "jpeg",
                    quality: 0.9,
                });

                // Collect face data with proper scaling
                const faceData = [];
                canvas.getObjects().forEach((obj) => {
                    if (obj.faceType && obj !== backgroundImage && !obj.coordinateImage) {
                        // Get actual dimensions considering object scaling
                        const actualWidth = (obj.width * (obj.scaleX || 1)) / scaleFactor;
                        const actualHeight = (obj.height * (obj.scaleY || 1)) / scaleFactor;

                        // Calculate actual position (adjust for origin point)
                        let actualLeft = obj.left / scaleFactor;
                        let actualTop = obj.top / scaleFactor;

                        // Adjust for origin point if not top-left
                        if (obj.originX === 'center') {
                            actualLeft -= (actualWidth / 2);
                        }
                        if (obj.originY === 'center') {
                            actualTop -= (actualHeight / 2);
                        }

                        faceData.push({
                            x: Math.round(actualLeft),
                            y: Math.round(actualTop),
                            width: Math.round(actualWidth),
                            height: Math.round(actualHeight),
                            angle: obj.angle || 0,
                            gender: obj.faceType
                        });
                    }
                });

                // Create FormData
                const formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('post_name', $('#post_name').val());
                formData.append('skin_color', $('#skin_color').val());
                formData.append('head_count', boyCount + girlCount);
                formData.append('json', JSON.stringify(faceData));
                formData.append("faces", JSON.stringify({
                    male: boyCount,
                    female: girlCount
                }));

                // Append all images
                formData.append('attire_url', attireBase64);
                formData.append('coordinate_image', coordinateImageBase64);

                const previewBase64 = $('input[name="preview_url"]').val();
                if (previewBase64) {
                    formData.append('preview_url', previewBase64);
                    const thumbnail = await watermarkBase64(previewBase64);
                    formData.append('thumbnail_url', thumbnail);
                }

                // Submit via AJAX
                $.ajax({
                    url: 'create_attire',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $("#main_loading_screen").hide();

                        if (data.success) {
                            $('#result').html(
                                `<div class="alert alert-success">${data.success}</div>`
                            );
                            setTimeout(() => {
                                window.location.href = '{{ route('show_attire_item') }}'; // Adjust route as needed
                            }, 2000);
                        } else if (data.error) {
                            $('#result').html(
                                `<div class="alert alert-danger">${data.error}</div>`
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#main_loading_screen").hide();
                        let errorMessage = 'Error submitting form: ' + error;
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        $('#result').html(
                            `<div class="alert alert-danger">${errorMessage}</div>`
                        );
                    }
                });

            } catch (err) {
                $("#main_loading_screen").hide();
                alert('Failed to submit form: ' + err.message);
            }
        });

        async function watermarkBase64(base64Image) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.src = base64Image;
                img.onload = () => {
                    const scale = 0.9;
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width * scale;
                    canvas.height = img.height * scale;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return reject('No canvas context');

                    // Background white
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // Draw image scaled down
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                    // Watermark settings
                    const text = 'CraftyArt';
                    const fontSize = Math.floor(canvas.width / 23);
                    ctx.font = `bold ${fontSize}px Arial`;
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    const textMetrics = ctx.measureText(text);
                    const textWidth = textMetrics.width;
                    const textHeight = fontSize * 1.2;
                    const angle = -30 * (Math.PI / 180);
                    const spacingX = textWidth * 1.6;
                    const spacingY = textHeight * 3;

                    ctx.save();
                    ctx.translate(canvas.width / 2, canvas.height / 2);
                    ctx.rotate(angle);
                    ctx.translate(-canvas.width / 2, -canvas.height / 2);

                    for (let x = -canvas.width; x < canvas.width * 2; x += spacingX) {
                        for (let y = -canvas.height; y < canvas.height * 2; y += spacingY) {
                            const offsetX = (y / spacingY) % 2 ? spacingX / 2 : 0;
                            ctx.fillText(text, x + offsetX, y);
                        }
                    }

                    ctx.restore();

                    // Reduce JPEG quality to shrink file size
                    const watermarkedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                    resolve(watermarkedBase64);
                };
                img.onerror = reject;
            });
        }

    });
</script>

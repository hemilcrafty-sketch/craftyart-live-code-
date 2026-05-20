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

    .current-image {
        border: 3px solid #28a745;
        margin-bottom: 10px;
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
                                    <input type="text" class="form-control attire-access" id="post_name"
                                           name="post_name" placeholder="Enter post name" value="{{ $attire->post_name }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Select Skin Color</h6>
                                    <input type="color" class="form-control" id="skin_color" name="skin_color"
                                           value="{{ $attire->skin_color ?? '#ffd9b3' }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Image Uploads for Updates -->
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Update Attire Image</h6>
                                    <input type="file" class="form-control" id="attire_url" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                    <div id="attirePreview" class="image-preview-container"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <h6>Update Preview Image</h6>
                                    <input type="file" class="form-control dynamic-file"
                                           data-accept=".jpg, .jpeg, .webp, .svg, .png" data-imgstore-id="preview_url"
                                           data-value="{{ $contentManager::getStorageLink($attire->preview_url) }}"
                                           data-nameset="true" data-validate="false">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                            </div>
                        </div>

                        <!-- Canvas Editor Section -->
                        <div class="canvas-section">
                            <h4>Coordinate Image Editor</h4>
                            <div id="canvasStatus" class="status-info">
                                @if ($attire->attire_url)
                                Current coordinate image loaded. You can edit it below.
                                @else
                                Please upload an attire image to start editing
                                @endif
                            </div>

                            <!-- Canvas Controls -->
                            <div class="canvas-controls" id="canvasControls"
                                 style="{{ $attire->attire_url ? 'display: block;' : 'display: none;' }}">
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
                            <div class="canvas-container" id="canvasContainer"
                                 style="{{ $attire->attire_url ? 'display: block;' : 'display: none;' }}">
                                <canvas id="fabricCanvas"></canvas>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-check"></i> Update Design
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
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
        <h4>Updating Your Design...</h4>
    </div>
</div>

@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        let canvas, backgroundImage, scaleFactor = 1;
        let attireBase64 = '';
        let attireWidth = 0, attireHeight = 0;
        let attireAspectRatio = null;
        let coordinateAspectRatio = null;
        let boyCount = 0, girlCount = 0;

        const savedFaceData = @json($attire->json ? json_decode($attire->json, true) : []);
        const existingAttireUrl = "{{ $contentManager::getStorageLink($attire->attire_url) }}";

        // Initialize Fabric.js canvas immediately
        function initializeCanvas() {
            if (canvas) canvas.dispose();

            canvas = new fabric.Canvas('fabricCanvas', {
                width: 600,
                height: 500,
                backgroundColor: '#f8f9fa',
                preserveObjectStacking: true,
                selection: true
            });

            canvas.on('object:added', updateCounts);
            canvas.on('object:removed', updateCounts);
            canvas.on('object:modified', updateCounts);

            if (existingAttireUrl) {
                loadExistingAttireImage();
            }
        }

        // Load existing attire image
        function loadExistingAttireImage() {
            updateStatus('Loading existing attire image...', 'info');
            fetch(existingAttireUrl)
                .then(response => response.blob())
                .then(blob => {
                    const reader = new FileReader();
                    reader.onload = function() {
                        // attireBase64 = reader.result;
                        displayExistingImagePreview();
                        loadImageToCanvasFromUrl(existingAttireUrl);
                    };
                    reader.readAsDataURL(blob);
                })
                .catch(() => updateStatus('Error loading existing image', 'danger'));
        }

        // Display existing image preview
        function displayExistingImagePreview() {
            $('#attirePreview').html(`
                <div class="image-preview">
                    <img src="${existingAttireUrl}" alt="Current Attire" class="preview-image">
                    <input type="hidden" value="${attireBase64}" name="attire_url">
                    <div class="dimension-text">Current Image</div>
                </div>
            `);
        }

        // Load image from URL to canvas
        function loadImageToCanvasFromUrl(imageUrl) {
            fabric.Image.fromURL(imageUrl, function(img) {
                const maxWidth = 800;
                const maxHeight = 600;

                scaleFactor = Math.min(maxWidth / img.width, maxHeight / img.height, 1);
                const scaledWidth = img.width * scaleFactor;
                const scaledHeight = img.height * scaleFactor;

                attireWidth = img.width;
                attireHeight = img.height;
                attireAspectRatio = parseFloat((img.width / img.height).toFixed(4));

                img.set({
                    scaleX: scaleFactor,
                    scaleY: scaleFactor,
                    originX: 'left',
                    originY: 'top',
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

                canvas.setWidth(scaledWidth);
                canvas.setHeight(scaledHeight);
                canvas.add(img);
                backgroundImage = img;
                canvas.sendToBack(img);
                canvas.renderAll();

                $('#canvasContainer').show();
                $('#canvasControls').show();

                if (savedFaceData && savedFaceData.length > 0) {
                    loadSavedFaces(savedFaceData);
                    updateStatus(`Loaded ${savedFaceData.length} saved faces. Canvas ready!`, 'success');
                } else {
                    updateStatus('Canvas ready! Add boy and girl faces to the image.', 'success');
                }
            }, { crossOrigin: 'anonymous' });
        }

        // Load saved faces from JSON data
        function loadSavedFaces(faceData) {
            if (!faceData || !faceData.length) return;

            canvas.getObjects().forEach(obj => {
                if (obj !== backgroundImage && obj.faceType) canvas.remove(obj);
            });

            faceData.forEach(face => {
                try {
                    const canvasX = face.x * scaleFactor;
                    const canvasY = face.y * scaleFactor;
                    const canvasWidth = face.width * scaleFactor;
                    const canvasHeight = face.height * scaleFactor;

                    const circle = new fabric.Ellipse({
                        rx: canvasWidth / 2,
                        ry: canvasHeight / 2,
                        fill: "rgba(0, 0, 0, 0.7)",
                        stroke: "black",
                        strokeWidth: 0.5,
                        originX: "center",
                        originY: "center",
                    });

                    const label = face.gender === 'boy' ? 'Male\nFace' : 'Female\nFace';
                    const color = face.gender === 'boy' ? '#3498db' : '#e83e8c';

                    const text = new fabric.Text(label, {
                        fontSize: Math.max(12, canvasWidth / 8),
                        fill: '#ffffff',
                        originX: 'center',
                        originY: 'center',
                        textAlign: 'center'
                    });

                    const group = new fabric.Group([circle, text], {
                        left: canvasX,
                        top: canvasY,
                        selectable: true,
                        hasControls: true,
                        hasBorders: true,
                        faceType: face.gender,
                        lockRotation: false,
                        cornerStyle: 'circle',
                        cornerColor: color,
                        transparentCorners: false,
                        cornerSize: 12,
                        padding: 10,
                        angle: face.angle || 0
                    });

                    canvas.add(group);
                } catch (error) {
                    console.error('Error loading face:', error);
                }
            });

            canvas.renderAll();
            updateCounts();
        }

        // Handle attire image selection
        $('#attire_url').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                alert('Please select a valid image file (JPEG, PNG, etc.)');
                return;
            }

            updateStatus('Loading attire image...', 'info');
            const reader = new FileReader();

            reader.onload = function(event) {
                attireBase64 = event.target.result;

                $('#attirePreview').html(`
                    <div class="image-preview">
                        <img src="${attireBase64}" alt="Attire Preview" class="preview-image">
                        <input type="hidden" value="${attireBase64}" name="attire_url">
                        <div class="dimension-text" id="attireDimension">Loading dimensions...</div>
                    </div>
                `);

                const img = new Image();
                img.onload = function() {
                    attireWidth = img.width;
                    attireHeight = img.height;
                    attireAspectRatio = parseFloat((img.width / img.height).toFixed(4));
                    $('#attireDimension').text(`Dimensions: ${img.width} x ${img.height} (Ratio: ${attireAspectRatio})`);
                    loadImageToCanvas(img);
                };
                img.src = attireBase64;
            };
            reader.readAsDataURL(file);
        });

        // Handle Coordinate Image Upload with better ratio handling
        $('#coordinate_url').on('change', function(e) {
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

            reader.onload = function(event) {
                const coordinateBase64 = event.target.result;
                $('#coordinatePreview').html(`
                    <div class="image-preview">
                        <img src="${coordinateBase64}" alt="Coordinate Preview" class="preview-image">
                        <input type="hidden" value="${coordinateBase64}" name="coordinate_url">
                        <div class="dimension-text" id="coordinateDimension">Loading dimensions...</div>
                    </div>
                `);

                const img = new Image();
                img.onload = function() {
                    const w = img.width, h = img.height;
                    coordinateAspectRatio = parseFloat((w / h).toFixed(4));
                    $('#coordinateDimension').text(`Dimensions: ${w} x ${h} (Ratio: ${coordinateAspectRatio})`);

                    // Enhanced ratio validation with better tolerance
                    const ratioDifference = Math.abs(attireAspectRatio - coordinateAspectRatio);
                    const tolerance = 0.002; // Increased tolerance for minor differences

                    if (ratioDifference > tolerance) {
                        const errorMsg = `Aspect ratio mismatch!\n
Attire: ${attireWidth}x${attireHeight} (Ratio: ${attireAspectRatio})\n
Coordinate: ${w}x${h} (Ratio: ${coordinateAspectRatio})\n
Difference: ${ratioDifference.toFixed(4)} (Max allowed: ${tolerance})\n\n
Please use images with the same aspect ratio.`;
                        updateStatus('Aspect ratio mismatch!', 'danger');
                        alert(errorMsg);
                        $('#coordinatePreview').html('<div class="text-danger">Invalid aspect ratio - Please use same ratio as attire image</div>');
                        $(this).val('');
                        return;
                    }

                    // If ratios are close enough, proceed with loading
                    updateStatus('Loading coordinate image to canvas...', 'info');

                    // Load coordinate image to canvas
                    fabric.Image.fromURL(coordinateBase64, function(fabricImg) {
                        // Remove any existing coordinate image
                        canvas.getObjects().forEach(obj => {
                            if (obj.coordinateImage) {
                                canvas.remove(obj);
                            }
                        });

                        // Calculate scale to match attire dimensions on canvas
                        // Force the coordinate image to match the attire aspect ratio exactly
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
                            lockScalingX: true, // Lock scaling to maintain ratio
                            lockScalingY: true,
                            lockUniScaling: true,
                            coordinateImage: true
                        });

                        canvas.add(fabricImg);
                        canvas.bringToFront(fabricImg);
                        canvas.renderAll();

                        updateStatus(`Coordinate image loaded successfully! Automatically adjusted to match attire ratio.`, 'success');
                    });
                };
                img.src = coordinateBase64;
            };
            reader.readAsDataURL(file);
        });

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

        // Handle preview image selection
        $('.dynamic-file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const base64Data = event.target.result;
                const imgstoreId = $(this).data('imgstore-id');

                // Store base64 data in hidden input
                $(`input[name="${imgstoreId}"]`).remove();
                $(this).after(`<input type="hidden" name="${imgstoreId}" value="${base64Data}">`);
            }.bind(this);
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
                // Don't allow deleting background image
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

        // Update form submission URL for edit
        $('#dynamic_form').on('submit', async function(event) {
            event.preventDefault();

            if (!attireBase64 && !existingAttireUrl) {
                alert('Please select an attire image');
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

                if (attireBase64) {
                    formData.append('attire_url', attireBase64);
                }

                const base64Preview = $('input[name="preview_url"]').val();
                function isBase64(str) {
                    if (typeof str !== 'string') return false;
                    const base64Regex = /^data:(.*);base64,([A-Za-z0-9+/=]+)$/;
                    return base64Regex.test(str);
                }

                if (base64Preview && isBase64(base64Preview)) {
                    formData.append('preview_url', base64Preview);
                    const thumbnail = await watermarkBase64(base64Preview);
                    formData.append('thumbnail_url', thumbnail);
                }

                if (coordinateImageBase64) {
                    formData.append('coordinate_image', coordinateImageBase64);
                }

                // Submit via AJAX
                $.ajax({
                    url: '{{ route('attire.update', $attire->id) }}',
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
                                window.location.href = '{{ route('show_attire_item') }}';
                            }, 2000);
                        } else if (data.error) {
                            $('#result').html(
                                `<div class="alert alert-danger">${data.error}</div>`
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#main_loading_screen").hide();
                        alert('Error updating form: ' + error);
                    }
                });

            } catch (err) {
                $("#main_loading_screen").hide();
                alert('Failed to update form: ' + err.message);
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
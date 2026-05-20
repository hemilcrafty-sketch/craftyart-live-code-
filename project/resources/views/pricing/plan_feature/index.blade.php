@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box d-flex flex-column" style="height: 94vh; overflow: hidden;">
                <span id="result"></span>
                <div class="row justify-content-between">
                    <div class="col-md-2 m-1">
                        <a href="#" class="btn btn-primary item-form-input" data-backdrop="static" data-toggle="modal"
                           data-target="#add_feature_model" type="button" id="addFeatureButton">
                            Add Plan Feature
                        </a>
                    </div>

                    <div class="col-md-7">
                        @include('partials.filter_form ', [
                        'action' => route('features.index'),
                        ])
                    </div>
                </div>

                <form id="create_feature_action" action="{{ route('features.create') }}" method="GET"
                      style="display: none;">
                    <input type="text" id="passingAppId" name="passingAppId">
                    @csrf
                </form>

                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>slug</th>
                                <th>Category Feature</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                            </thead>
                            <tbody id="relegion_table">
                            @foreach ($features as $feature)
                            <tr style="background-color: #efefef;">
                                <td class="table-plus">{{ $feature->id }}</td>
                                <td class="table-plus">{{ $feature->name }}</td>
                                <td class="table-plus">{{ $feature->slug }}</td>
                                <td class="table-plus">{{ $feature->categoryFeatures->name }}</td>
                                <td>
                                    <Button class="dropdown-item btn-edits" data-id="{{ $feature->id }}"
                                            data-feature="{{ $feature->name }}" data-backdrop="static"
                                            data-toggle="modal" data-target="#edit_feature_model"><i
                                                class="dw dw-edit2"></i> Edit
                                    </Button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $features])
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_feature_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
         aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Feature</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form method="post" id="featureForm">
                        @csrf
                        <input type="hidden" name="feature_id" id="featureId" />

                        <div class="form-group">
                            <h7>Name</h7>
                            <input type="text" class="form-control" name="name" id="featureName"
                                   placeholder="Enter Feature Name" required />
                        </div>

                        <div class="form-group">
                            <h7>Slug</h7>
                            <input type="text" class="form-control" name="slug" id="featureSlug"
                                   placeholder="e.g. access_template (lowercase, underscores)"
                                   autocomplete="off"
                                   pattern="[a-z0-9_]+"
                                   title="Use lowercase letters, numbers, and underscores only" />
                            <small class="form-text text-muted">Slug can only be set once. After it is saved, it cannot be changed.</small>
                        </div>

                        <div class="form-group">
                            <h7>Description</h7>
                            <textarea class="form-control" name="description" id="featureDescription" rows="5" required></textarea>
                        </div>

                        <div class="form-group">
                            <h7>Category Feature</h7>
                            <select name="category_feature_id" id="categoryFeatureId" class="form-control">
                                @foreach ($categoryFeatures as $categoryFeature)
                                <option value="{{ $categoryFeature->id }}">
                                    {{ \Str::title($categoryFeature->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <h7>Appearance Type</h7>
                            <select name="appearance_type" id="appearanceType" class="form-control">
                                <option value="0">Switch</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <input type="button" class="btn btn-primary btn-block" id="btnSaveFeature"
                                       value="Save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.masterscript')

    <script>
        // Function to reset the form fields
        function resetFeatureForm() {
            $("#featureId").val('');
            $("#featureName").val('');
            var $slug = $("#featureSlug");
            $slug.val('').prop('readonly', false).removeClass('bg-light').attr('required', 'required');
            $("#featureDescription").val('');
            $("#categoryFeatureId").val($("#categoryFeatureId option:first").val());
            $("#appearanceType").val('0');
            $("#modalTitle").text('Add Feature');
        }

        function setSlugFieldState(slugValue) {
            var $slug = $("#featureSlug");
            var trimmed = (slugValue != null && slugValue !== undefined) ? String(slugValue).trim() : '';
            if (trimmed !== '') {
                $slug.val(trimmed).prop('readonly', true).addClass('bg-light').removeAttr('required');
            } else {
                $slug.val('').prop('readonly', false).removeClass('bg-light').attr('required', 'required');
            }
        }

        // Reset form when Add Feature button is clicked
        $(document).on("click", "#addFeatureButton", function() {
            resetFeatureForm();
        });

        // Reset form when modal is closed
        $('#add_feature_model').on('hidden.bs.modal', function () {
            resetFeatureForm();
        });

        $(document).on("click", "#btnSaveFeature", function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            let featureId = $("#featureId").val();

            $.ajax({
                url: "{{ route('features.store') }}",
                type: "POST",
                dataType: "json",
                data: {
                    feature_id: featureId,
                    name: $("#featureName").val(),
                    slug: $("#featureSlug").val(),
                    description: $("#featureDescription").val(),
                    category_feature_id: $("#categoryFeatureId").val(),
                    appearance_type: $("#appearanceType").val(),
                },
                beforeSend: function() {
                    $("#loading_screen").show();
                },
                success: function(data) {
                    $("#loading_screen").hide();
                    alert(data.message);
                    if (data.status) {
                        $("#add_feature_model").modal("hide");
                        location.reload();
                    }
                },
                error: function(xhr) {
                    $("#loading_screen").hide();
                    alert("An unexpected error occurred.");
                    console.error(xhr.responseText);
                }
            });
        });

        $(document).on("click", ".btn-edits", function() {
            let id = $(this).data("id");

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            $.ajax({
                url: "{{ route('features.edit', ':id') }}".replace(":id", id),
                type: "GET",
                dataType: "json",
                success: function(data) {
                    let feature = data.feature;

                    $("#featureId").val(feature.id);
                    $("#featureName").val(feature.name);
                    setSlugFieldState(feature.slug);
                    $("#featureDescription").val(feature.description);
                    $("#categoryFeatureId").val(feature.category_feature_id);
                    $("#appearanceType").val(feature.appearance_type);
                    $("#modalTitle").text('Edit Feature');

                    $("#add_feature_model").modal("show");
                },
            });
        });

        function delete_click(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });

            var url = @json(route('features.destroy', ['feature' => 'PLACEHOLDER']));
            url = url.replace('PLACEHOLDER', id);

            $.ajax({
                url: url,
                type: 'DELETE',
                beforeSend: function() {
                    var loading_screen = document.getElementById("loading_screen");
                    loading_screen.style.display = "block";
                },
                success: function(data) {
                    var loading_screen = document.getElementById("loading_screen");
                    loading_screen.style.display = "none";
                    if (data.error) {
                        window.alert('error==>' + data.error);
                    } else {
                        location.reload();
                    }
                },
                error: function(error) {
                    var loading_screen = document.getElementById("loading_screen");
                    loading_screen.style.display = "none";
                    window.alert(error.responseText);
                },
                cache: false,
                contentType: false,
                processData: false
            })
        }

        $("#appearanceType").change(function() {
            if ($(this).val() == "switch") {
                $("#switchGroup").show();
                $("#messageGroup").hide();
            } else {
                $("#switchGroup").hide();
                $("#messageGroup").show();
            }
        })
    </script>
</div>
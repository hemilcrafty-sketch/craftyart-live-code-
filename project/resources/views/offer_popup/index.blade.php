@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@include('layouts.masterhead')

<div class="main-container designer-access-container">
    <div class="min-height-200px">
        <div class="card-box">
            <div style="display:flex;flex-direction:column;height:90vh;overflow:hidden;">
                <div class="row justify-content-between mb-2">
                    <div class="col-md-3 m-2">
                        @if ($roleManager::isAdmin(Auth::user()->user_type))
                            <button type="button" class="btn btn-primary" id="addOfferPopUp"
                                @if ($offers->count() > 0) disabled @endif>
                                + Add Offer Pop Up
                            </button>
                        @endif
                    </div>
                </div>

                <div class="scroll-wrapper table-responsive tableFixHead"
                    style="max-height:calc(110vh - 220px)!important">
                    <table id="offer_table" class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Festival Name</th>
                                <th>Enable Promo</th>
                                <th>Enable Offer</th>
                                <th>Duration</th>
                                <th>Frequency ( Day )</th>
                                <th>Enable Force</th>
                                <th>Force Show</th>
                                <th width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offers as $offer)
                                <tr id="row_{{ $offer->id }}">
                                    <td>{{ $offer->id }}</td>
                                    <td>{{ $offer->title }}</td>
                                    <td>{{ $offer->festival_name }}</td>
                                    <td>
                                        <button style="border:none" onclick="setPromoEnable('{{ $offer->id }}')">
                                            <input type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2" {{ $offer->enable_promo_code ? 'checked' : '' }} />
                                        </button>
                                    </td>

                                    <td>
                                        <button style="border:none" onclick="setEnable('{{ $offer->id }}')">
                                            <input type="checkbox" class="switch-btn" data-size="small"
                                                data-color="#0059b2" {{ $offer->enable_offer ? 'checked' : '' }} />
                                        </button>
                                    </td>

                                    <td>{{ $offer->duration_time_label }}</td>
                                    <td>{{ $offer->frequency_duration }}</td>
                                    <td>{{ $offer->enable_force ? 'Yes' : 'No' }}</td>
                                    <td>{{ $offer->force_show_duration_label }}</td>

                                    <td>
                                        <button class="dropdown-item edit-offer" data-id="{{ $offer->id }}"><i
                                                class="dw dw-edit2"></i> Edit</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="my-1">
        </div>
    </div>
</div>

<!-- OFFER MODAL -->
<div class="modal fade" id="offer_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <form id="offerForm">@csrf
                <input type="hidden" name="id" id="id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Offer Pop Up</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">×</button>
                </div>

                <div class="modal-body">

                    <label class="fw-bold ml-2 mt-2">Title</label>
                    <input type="text" class="form-control m-2" name="title" id="title" required>

                    <label class="fw-bold ml-2 mt-2">Festival Name</label>
                    <input type="text" class="form-control m-2" name="festival_name" id="festival_name" required>

                    <label class="fw-bold ml-2 mt-2">Description</label>
                    <input type="text" class="form-control m-2" name="description" id="description" required>

                    <label class="fw-bold ml-2 mt-2">Sub Description</label>
                    <input type="text" class="form-control m-2" name="sub_description" id="sub_description" required>

                    <hr>

                    <!-- Hidden to ensure unchecked value goes as 0 -->
                    <input type="hidden" name="enable_promo_code" value="0">

                    <div class="form-check m-2">
                        <input type="checkbox" class="form-check-input" value="1" name="enable_promo_code"
                            id="enable_promo_code">
                        <label class="form-check-label" for="enable_promo_code">Enable Promo Code</label>
                    </div>

                    <label class="fw-bold ml-2 mt-2">Promo Code</label>
                    <select class="form-control m-2" name="promo_code" id="promo_code">
                        @foreach ($promoCodes as $code)
                            <option value="{{ $code->id }}">{{ $code->promo_code }}</option>
                        @endforeach
                    </select>

                    <label class="fw-bold ml-2 mt-2">Button Name</label>
                    <input type="text" class="form-control m-2" name="btn_name" id="btn_name" required>

                    <label class="fw-bold ml-2 mt-2">Button Link</label>
                    <input type="text" class="form-control m-2" name="btn_link" id="btn_link" required>

                    <div class="form-check m-2">
                        <input type="checkbox" class="form-check-input" id="enable_offer" name="enable_offer">
                        <label class="form-check-label">Enable Offer</label>
                    </div>

                    <label class="fw-bold ml-2 mt-2">Duration :</label>
                    <div class="d-flex">
                        <input type="number" class="form-control m-2 w-50" min="1"
                            name="duration_time_value" id="duration_time_value">
                        <select class="form-control m-2 w-50" name="duration_time_unit" id="duration_time_unit">
                            <option value="sec">Sec</option>
                            <option value="min">Min</option>
                            <option value="hour">Hour</option>
                        </select>
                    </div>

                    <label class="fw-bold ml-2 mt-2">Frequency (Day)</label>
                    <input type="number" class="form-control m-2" min="0" name="frequency_duration_value"
                        id="frequency_duration_value">

                    <hr>

                    <div class="form-check m-2">
                        <input type="checkbox" class="form-check-input" id="enable_force" name="enable_force">
                        <label class="form-check-label">Enable Force</label>
                    </div>

                    <label class="fw-bold ml-2 mt-2">Force Show :</label>
                    <div class="d-flex">
                        <input type="number" class="form-control m-2 w-50" min="1"
                            name="force_show_duration_value" id="force_show_duration_value">
                        <select class="form-control m-2 w-50" name="force_show_duration_unit"
                            id="force_show_duration_unit">
                            <option value="sec">Sec</option>
                            <option value="min">Min</option>
                            <option value="hour">Hour</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    // OPEN ADD
    $("#addOfferPopUp").click(function() {
        $("#offerForm")[0].reset();
        $("#id").val("");
        $("#modalTitle").text("Add Offer Pop Up");
        $("#offer_modal").modal("show");
        $("#promo_code").prop("required", false);
    });

    // EDIT
    $(document).on("click", ".edit-offer", function() {
        let id = $(this).data("id");

        $.get("{{ url('offer-popup') }}/" + id + "/edit", function(res) {

            $("#id").val(res.id);
            $("#title").val(res.title);
            $("#festival_name").val(res.festival_name);
            $("#description").val(res.description);
            $("#sub_description").val(res.sub_description);

            $("#enable_promo_code").prop("checked", res.enable_promo_code == 1);
            $("#promo_code").val(res.promo_code);
            $("#promo_code").prop("required", res.enable_promo_code == 1);

            $("#btn_name").val(res.btn_name);
            $("#btn_link").val(res.btn_link);

            $("#enable_offer").prop("checked", res.enable_offer == 1);
            $("#duration_time_value").val(res.duration.value);
            $("#duration_time_unit").val(res.duration.unit);
            $("#frequency_duration_value").val(res.frequency_duration);

            $("#enable_force").prop("checked", res.enable_force == 1);
            $("#force_show_duration_value").val(res.force_show_duration.value);
            $("#force_show_duration_unit").val(res.force_show_duration.unit);

            toggleForceInputs();

            $("#modalTitle").text("Edit Offer Pop Up");
            $("#offer_modal").modal("show");
        });
    });

    // SAVE
    $("#offerForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('offer-popup.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    $("#offer_modal").modal("hide");
                    window.location.reload();
                } else {
                    alert(res.message || "Something went wrong.");
                }
            },
            error: function(xhr) {
                alert("Error occurred.");
            }
        });
    });

    // PROMO CODE REQUIRED TOGGLE
    $("#enable_promo_code").change(function() {
        $("#promo_code").prop("required", $(this).is(":checked"));
    });

    // FORCE OPTION
    function toggleForceInputs() {
        if ($("#enable_offer").is(":checked")) {
            $("#enable_force").prop("disabled", false);
            $("#force_show_duration_value, #force_show_duration_unit")
                .prop("disabled", !$("#enable_force").is(":checked"));
        } else {
            $("#enable_force").prop("checked", false).prop("disabled", true);
            $("#force_show_duration_value, #force_show_duration_unit").prop("disabled", true);
        }
    }

    $("#enable_offer, #enable_force").on("change", toggleForceInputs);

    // ENABLE TOGGLE
    const setEnableUrl = "{{ route('offer-popup.set-enable', ['id' => ':id']) }}";

    function setEnable(id) {
        let isChecked = $("#row_" + id + " .switch-btn").is(":checked");

        $.post(setEnableUrl.replace(':id', id), {
            _token: "{{ csrf_token() }}",
            enable_offer: isChecked ? 1 : 0
        });
    }

    const setPromoEnableUrl = "{{ route('offer-popup.set-enable-promo', ['id' => ':id']) }}";

    function setPromoEnable(id) {
        let isChecked = $("#row_" + id + " .switch-btn").eq(1).is(":checked");

        $.post(setPromoEnableUrl.replace(':id', id), {
            _token: "{{ csrf_token() }}",
            enable_promo_code: isChecked ? 1 : 0
        });
    }
</script>

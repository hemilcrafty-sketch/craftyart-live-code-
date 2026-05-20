@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">

            <div class="card-box d-flex flex-column" style="height: 20vh; overflow: hidden;">
                <div class="row d-flex justify-content-start">
                    <div class="col-md-12 d-flex m-1">
                        <a href="#"
                            class="btn btn-primary item-form-input 
                                {{ $discounts->count() > 0 ? 'disabled' : '' }}"
                            data-backdrop="static" data-toggle="modal" data-target="#plan_user_discount" type="button">
                            Additional User Discount
                        </a>
                        @if ($discounts->count() > 0)
                            <small class="text-danger d-block ml-3 mt-2">
                                ( Note: Only one discount record can be added. The button is disabled because a record
                                already
                                exists. You can edit the discount any time. )
                            </small>
                        @endif
                    </div>
                </div>
                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>additional User Discount ( % )</th>
                                    <th>Factor ( % )</th>
                                    <th>X <span class="ml-3" style="font-size: 10px;">( x = 1 - Discount / 100
                                            )</span></th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody id="discountTableBody">
                                @foreach ($discounts as $d)
                                    <tr id="row-{{ $d->id }}">
                                        <td>{{ $d->id }}</td>
                                        <td>{{ $d->discount_percentage }}%</td>
                                        <td>{{ $d->factor }}%</td>
                                        <td>{{ $d->x }}</td>
                                        <td>
                                            {{-- <button class="btn btn-sm btn-danger deleteDiscount"
                                                data-id="{{ $d->id }}">Delete</button> --}}

                                            <button class="dropdown-item editDiscount"
                                                data-id="{{ $d->id }}">
                                                <i class="dw dw-edit2"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <div class="card-box d-flex flex-column mt-3" style="height: 73vh; overflow: hidden;">
                <span id="result"></span>
                <div class="row justify-content-between">
                    <div class="col-md-2 m-1">
                        {{-- <a href="#" class="btn btn-primary item-form-input" data-backdrop="static"
                            data-toggle="modal" data-target="#plan_duration_modal" type="button">
                            Add Plan Duration </a> --}}

                        <a href="#" class="btn btn-primary" id="btnAddPlanDuration" type="button">
                            Add Plan Duration
                        </a>

                    </div>

                    <div class="col-md-7">
                        @include('partials.filter_form ', [
                            'action' => route('planduration.index'),
                        ])
                    </div>
                </div>

                <form id="create_feature_action" action="" method="GET" style="display: none;">
                    <input type="text" id="passingAppId" name="passingAppId">
                    @csrf
                </form>

                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Duration Name</th>
                                    <th>Duration Type ( Day )</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody id="relegion_table">
                                @foreach ($getPlanCategories as $getPlanCategory)
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $getPlanCategory->id }}</td>
                                        <td class="table-plus">{{ $getPlanCategory->name }}</td>
                                        <td class="table-plus">{{ $getPlanCategory->duration }}</td>

                                        <td>
                                            <button class="dropdown-item btn-edits" data-id="{{ $getPlanCategory->id }}"
                                                data-name="{{ $getPlanCategory->name }}" data-toggle="modal"
                                                data-target="#edit_plan_category">
                                                <i class="dw dw-edit2"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $getPlanCategories])
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="plan_duration_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title modal-title-text">Add Plan Duration</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <span id="result"></span>
                <form id="planDurationForm">
                    @csrf
                    <input type="hidden" name="id" id="planDurationId">

                    <div class="form-group">
                        <label>Plan Title</label>
                        <input type="text" class="form-control" name="name" id="planName" required />
                    </div>

                    <div class="form-group">
                        <label>Duration Type (Day)</label>
                        <input type="number" class="form-control" name="duration" id="planDuration" required />
                    </div>

                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="isAnnual" name="is_annual" value="1">
                        <label class="form-check-label" for="isAnnual">Is Annual</label>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitPlanDuration">Submit</button>
                </form>
            </div>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </div>
</div>


<div class="modal fade" id="plan_user_discount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title modal-title-text">Add User Discount</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <span id="result"></span>

            <div class="modal-body">
                <div id="result"></div>
                <form id="planUserDiscountForm">
                    @csrf
                    <input type="hidden" name="id" id="planUserDiscountId">

                    <div class="form-group">
                        <label>User Discount (%)</label>
                        <input type="number" class="form-control" placeholder="user discount"
                            name="discount_percentage" id="discountPercentage" required />
                    </div>

                    <div class="form-group">
                        <label>Factor (%)</label>
                        <input type="number" class="form-control" placeholder="Factor ( % )"
                            name="factor" id="factor" required />
                    </div>

                    <div class="form-group">
                        <label>Retention Factor</label>
                        <input type="number" class="form-control" placeholder="x" name="x" id="planX"
                            step="any" />
                        <small class="form-text text-muted">Automatically calculated: x = 1 - discount / 100</small>
                    </div>

                    <button type="submit" class="btn btn-primary" id="planUserDiscount">Submit</button>
                </form>
            </div>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </div>
</div>

@include('layouts.masterscript')

<script>
    $(document).ready(function() {

        // Open Add modal
        $(document).on("click", "#btnAddPlanDuration", function() {
            $("#planDurationForm")[0].reset();
            $("#planDurationId").val('');
            $("#isAnnual").prop('checked', false); // reset checkbox
            $(".modal-title-text").text("Add Plan Duration");
            $("#result").html('');
            $("#plan_duration_modal").modal("show");
        });

        // Open Edit modal
        $(document).on("click", ".btn-edits", function() {
            const id = $(this).data("id");
            const url = "{{ route('planduration.edit', ':id') }}".replace(':id', id);

            $.get(url, function(data) {
                const p = data.planCategory;
                $("#planDurationId").val(p.id);
                $("#planName").val(p.name);
                $("#planDuration").val(p.duration);
                $("#isAnnual").prop('checked', p.is_annual == 1); // ✅ prefill checkbox
                $(".modal-title-text").text("Edit Plan Duration");
                $("#result").html('');
                $("#plan_duration_modal").modal("show");
            });
        });

        $(document).on("submit", "#planDurationForm", function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            // Ensure checkbox sends 0 if unchecked
            if (!$('#isAnnual').is(':checked')) {
                formData.set('is_annual', 0);
            } else {
                formData.set('is_annual', 1);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content"),
                }
            });

            $.ajax({
                url: "{{ route('planduration.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#submitPlanDuration").prop("disabled", true);
                    $("#result").html('<div class="alert alert-info">Processing...</div>');
                },
                success: function(response) {
                    $("#result").html('<div class="alert alert-success">' + response
                        .message + '</div>');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    let msg = "<p>Something went wrong.</p>";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).map(e =>
                            "<p>" + e[0] + "</p>").join('');
                    }
                    $("#result").html('<div class="alert alert-danger">' + msg + '</div>');
                },
                complete: function() {
                    $("#submitPlanDuration").prop("disabled", false);
                }
            });
        });

    });




    $(document).ready(function() {

        // Auto calculate x
        $('#discountPercentage').on('input', function() {
            let discount = $(this).val();
            if (discount !== '') {
                let x = 1 - (discount / 100);
                $('#planX').val(x.toFixed(2));
            } else {
                $('#planX').val('');
            }
        });

        // Submit form
        $('#planUserDiscountForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('plan_discount.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#plan_user_discount').modal('hide');
                        location.reload(); // Refresh page OR update row dynamically
                    }
                }
            });
        });

        // Edit
        $(document).on('click', '.editDiscount', function() {
            let id = $(this).data('id');
            $.get("{{ url('plan/plan-discount/edit') }}/" + id, function(data) {
                $('#planUserDiscountId').val(data.id);
                $('#planUserDiscountId').val(data.id);
                $('#discountPercentage').val(data.discount_percentage).trigger('input');
                $('#factor').val(data.factor).trigger('input');
                $('#planX').val(data.x).prop('readonly', true);
                $('.modal-title-text').text('Update User Discount');
                $('#plan_user_discount').modal('show');
            });
        });

        // Delete
        // $(document).on('click', '.deleteDiscount', function() {
        //     let id = $(this).data('id');
        //     if (confirm("Are you sure?")) {
        //         $.ajax({
        //             url: "{{ url('plan/plan-discount/delete') }}/" + id,
        //             method: "DELETE",
        //             data: {
        //                 _token: "{{ csrf_token() }}"
        //             },
        //             success: function(res) {
        //                 if (res.success) {
        //                     $('#row-' + id).remove();
        //                 }
        //             }
        //         });
        //     }
        // });

    });
</script>
</body>

</html>

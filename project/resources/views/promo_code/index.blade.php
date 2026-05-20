@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <!-- Left: Add Button -->
                        <div class="col-md-3">
                            <a href="#" class="btn btn-primary m-1 item-form-input" data-toggle="modal" data-target="#add_promocode"
                                id="openAddModal">
                                Add Promo
                            </a>
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            @include('partials.filter_form', [
                                'action' => route('promocode.index'),
                            ])
                        </div>
                    </div>

                    {{-- <div class="col-sm-12 table-responsive"> --}}
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px">Id</th>
                                    <th>Promo Code</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th>Expiry Date</th>
                                    <th>Additional Day</th>
                                    <th>Discount Upto</th>
                                    <th>Minimum Purchase</th>
                                    <th class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($promoCodes as $promoCode)
                                    <tr style="background-color: #efefef;">
                                        <td>{{ $promoCode->id }}</td>
                                        <td>{{ $promoCode->promo_code }}</td>
                                        <td>{{ $promoCode->disc }}%</td>
                                        <td>
                                            @if ($promoCode->status == 1)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $promoCode->expiry_date }}</td>
                                        <td>{{ $promoCode->additional_days }}</td>
                                        <td>₹{{ $promoCode->disc_upto_inr }} /
                                            ${{ $promoCode->disc_upto_usd }}</td>
                                        <td>₹{{ $promoCode->min_cart_inr }} /
                                            ${{ $promoCode->min_cart_usd }}</td>
                                        <td>
                                            <div class="d-flex">

                                                <button class="dropdown-item edit-btn" data-id="{{ $promoCode->id }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </button>
                                                <button class="dropdown-item delete-btn"
                                                    data-id="{{ $promoCode->id }}">
                                                    <i class="dw dw-delete-3"></i> Delete
                                                </button>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">

                @include('partials.pagination', ['items' => $promoCodes])
            </div>
        </div>
    </div>
</div>

{{-- @include('color.create'); --}}
<div class="modal fade" id="add_promocode" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered custom-modal-width">
        <div class="modal-content">
            <form id="addForm">
                @csrf
                <input type="hidden" name="id" id="promo_id">
                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Promo Code</h5>
                    <button type="button" class="close" onclick="closeModal()" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <!-- Promo Code -->
                    <div class="form-group">
                        <label>Promo Code</label>
                        <input type="text" class="form-control" name="promo_code" id="promo_code" required>
                    </div>

                    <div class="form-group">
                        <label>Select User</label>
                        <select id="user_id" name="user_id[]" class="form-control border"
                            multiple="multiple"></select>
                    </div>


                    <div class="form-group">
                        <label>Type</label>
                        <select class="form-control" name="type" id="type">.
                            <option value="0" selected>Both</option>
                            <option value="1">Individual</option>
                            <option value="2">Subscription</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Discount (In %)</label>
                        <input type="number" class="form-control" name="disc" id="disc" required>
                    </div>
                    <div class="form-group">
                        <label>Additional Discount Day</label>
                        <input type="number" class="form-control" name="additional_days" id="additional_days"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="status">
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" id="expiry_date">
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>Discount Upto INR</label>
                            <input type="number" min="0" class="form-control" name="disc_upto_inr"
                                id="disc_upto_inr">
                        </div>
                        <div class="col-6">
                            <label>Minimum Purchase INR</label>
                            <input type="number" min="0" class="form-control" name="min_cart_inr"
                                id="min_cart_inr">
                        </div>
                        <div class="col-6">
                            <label>Discount Upto USD</label>
                            <input type="number" min="0" class="form-control" name="disc_upto_usd"
                                id="disc_upto_usd">
                        </div>
                        <div class="col-6">
                            <label>Minimum Purchase USD</label>
                            <input type="number" min="0" class="form-control" name="min_cart_usd"
                                id="min_cart_usd">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div id="result" class="mr-auto"></div>
                    <button type="submit" class="btn btn-primary" id="btnSubmitForm">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    let promoCodes = @json($promoCodes);

    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: 'Search email...',
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('get_users_by_email') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });


        // Open modal with cleared form
        $('#openAddModal').click(function() {
            $('#addForm')[0].reset();
            $('#promo_id').val('');
            $('#user_id').empty().val(null).trigger('change');
        });

        $('.edit-btn').click(function() {
            const data = $(this).data();

            // ✅ If promoCodes is paginated, get from promoCodes.data
            const promo = promoCodes.data.find(p => p.id == data.id);
            if (!promo) return alert("Promo code not found");

            // Fill form
            $('#promo_id').val(promo.id);
            $('#promo_code').val(promo.promo_code);
            $('#type').val(promo.type);
            $('#disc').val(promo.disc);
            $('#status').val(promo.status);
            $('#expiry_date').val(promo.expiry_date);
            $('#disc_upto_inr').val(promo.disc_upto_inr);
            $('#additional_days').val(promo.additional_days);
            $('#min_cart_inr').val(promo.min_cart_inr);
            $('#disc_upto_usd').val(promo.disc_upto_usd);
            $('#min_cart_usd').val(promo.min_cart_usd);

            const userData = JSON.parse(promo.user_id || "[]");
            $('#user_id').empty().val(null).trigger('change');

            if (userData.length > 0) {
                $.ajax({
                    url: "{{ route('get_users_by_ids') }}",
                    method: "GET",
                    data: {
                        user_ids: userData
                    },
                    success: function(response) {
                        response.forEach(user => {
                            const option = new Option(`${user.id} - ${user.email}`,
                                user.uid, true, true);
                            $('#user_id').append(option);
                        });
                        $('#user_id').val(userData).trigger('change');
                    }
                });
            }

            $('#add_promocode').modal('show');
        });

        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            let discount = parseFloat($('#disc').val()) || 0;
            let additionalDay = parseFloat($('#additional_days').val()) || 0;

            if (discount <= 0 && additionalDay <= 0) {
                alert(
                    "Either Discount or Additional Discount Day must be greater than 0. Both cannot be 0."
                );
                return false;
            }

            $.ajax({
                url: "{{ route('promocode.store') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: () => {
                    $('#result').html('<div class="text-info">Processing...</div>');
                },
                success: (res) => {
                    $('#result').html('');
                    if (res.error) {
                        $('#result').html('<div class="text-danger">' + res.error +
                            '</div>');
                    } else if (res.success) {
                        $('#result').html('<div class="text-success">' + res.success +
                            '</div>');
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: (xhr) => {
                    $('#result').html('');
                    alert('Something went wrong: ' + xhr.responseText);
                }
            });
        });

    });

    $(document).on("click", ".delete-btn", function() {
        var id = $(this).data("id");

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var url = "{{ route('promocode.destroy', ':id') }}".replace(":id", id);
        if (confirm("Are you sure you want to delete this review?")) {
            $.ajax({
                url: url,
                type: 'DELETE',
                beforeSend: function() {
                    $("#main_loading_screen").show();
                },
                success: function(response) {
                    $("#main_loading_screen").hide();
                    location.reload();
                },
                error: function(xhr) {
                    $("#main_loading_screen").hide();
                    alert("Error: " + xhr.responseText);
                }
            });
        }
    });
</script>
</body>

</html>

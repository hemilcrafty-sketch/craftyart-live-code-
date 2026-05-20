@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container seo-all-container">

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box d-flex flex-column" style="height: 94vh; overflow: hidden;">
                {{-- Filter and Action Row --}}
                <div class="row justify-content-between flex-wrap">
                    <div class="col-md-2 m-1">
                        @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                        <a href="javascript:void(0)" class="btn btn-primary item-form-input"
                           onclick="openCreditModal('add')">
                            Add Credit
                        </a>
                        @endif
                    </div>

                    <div class="col-md-9">
                        @include('partials.filter_form ', [
                        'action' => route('ai_credits.index'),
                        ])
                    </div>
                </div>

                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Credits</th>
                                <th>Discount</th>
                                <th>INR Price</th>
                                <th>USD Price</th>
                                <th>Status</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($aiCredits as $aiCredit)
                            <tr>
                                <td class="table-plus">{{ $aiCredit->id }}</td>
                                <td class="table-plus">{{ number_format($aiCredit->credits, 0) }}</td>
                                <td class="table-plus">{{ $aiCredit->disc ? $aiCredit->disc . '%' : '-' }}</td>
                                <td class="table-plus">{{ $aiCredit->offer_inr_price }}</td>
                                <td class="table-plus">{{ $aiCredit->offer_usd_price }}</td>
                                @if ($aiCredit->status == '1')
                                <td>Active</td>
                                @else
                                <td>Inactive</td>
                                @endif
                                <td>
                                    <div class="d-flex">
                                        <button class="dropdown-item"
                                                onclick='openCreditModal("edit", {
                                                        id: "{{ $aiCredit->id }}",
                                                        credits: "{{ $aiCredit->credits }}",
                                                        disc: "{{ $aiCredit->disc }}",
                                                        inr_price: "{{ $aiCredit->inr_price }}",
                                                        usd_price: "{{ $aiCredit->usd_price }}",
                                                        status: "{{ $aiCredit->status }}"
                                                    })'>
                                            <i class="dw dw-edit2"></i> Edit
                                        </button>

                                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                        <Button class="dropdown-item"
                                                onclick="delete_click('{{ $aiCredit->id }}')">
                                            <i class="dw dw-delete-3"></i> Delete
                                        </Button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $aiCredits])
            </div>
        </div>
    </div>

    <div class="modal fade seo-all-container" id="add_credit_model" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="credit_modal_title">Add Credit</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <form method="post" id="credit_form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="credit_id" id="credit_id" value="">

                        <div class="form-group">
                            <h7>Credits</h7>
                            <input type="number" class="form-control" placeholder="Enter credits" id="creditCredits"
                                   name="credits" min="1"/>
                        </div>

                        <div class="form-group">
                            <h7>Discount (%)</h7>
                            <input type="number" step="any" class="form-control" placeholder="Enter discount percentage"
                                   id="creditDisc"
                                   name="disc"  min="0" max="100"/>
                        </div>

                        <div class="form-group">
                            <h7>INR Price</h7>
                            <input type="number" step="any" class="form-control" placeholder="Enter INR price"
                                   id="creditInrPrice"
                                   name="inr_price" required min="1"/>
                        </div>

                        <div class="form-group">
                            <h7>USD Price</h7>
                            <input type="number" step="any" class="form-control" placeholder="Enter USD price"
                                   id="creditUsdPrice"
                                   name="usd_price" required min="1"/>
                        </div>

                        <div class="form-group">
                            <h6>Status</h6>
                            <select id="creditStatus" class="selectpicker form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-block" id="credit_submit_btn">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>

    function resetCreditForm() {
        $('#credit_form')[0].reset();
        $('#credit_id').val('');
        $('#creditStatus').val('1');
        $('#credit_form').attr('data-mode', '');
    }

    function openCreditModal(mode, data = {}) {
        resetCreditForm();

        if (mode === 'edit') {
            $('#credit_modal_title').text('Edit Credit');
            $('#credit_submit_btn').text('Update');
            $('#credit_id').val(data.id);
            $('#creditCredits').val(data.credits);
            $('#creditDisc').val(data.disc);
            $('#creditInrPrice').val(data.inr_price);
            $('#creditUsdPrice').val(data.usd_price);
            $('#creditStatus').val(data.status);
            $('#credit_form').attr('data-mode', 'edit');
        } else {
            $('#credit_modal_title').text('Add Credit');
            $('#credit_submit_btn').text('Save');
            $('#credit_form').attr('data-mode', 'add');
        }

        $('#add_credit_model').modal('show');
    }

    $('#credit_form').on('submit', function (event) {
        event.preventDefault();

        let formData = new FormData(this);
        let id = $('#credit_id').val();

        if (id) {
            formData.append('id', id);
        }

        // Validate prices
        let inrPrice = parseFloat($('#creditInrPrice').val());
        let usdPrice = parseFloat($('#creditUsdPrice').val());

        if (inrPrice <= 0) {
            alert('INR Price must be greater than 0');
            return;
        }

        if (usdPrice <= 0) {
            alert('USD Price must be greater than 0');
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        $.ajax({
            url: '{{ route("ai_credits.submit") }}',
            type: 'POST',
            data: formData,
            beforeSend: function () {
                $('#main_loading_screen').show();
            },
            success: function (data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert(data.error);
                } else {
                    $('#add_credit_model').modal('hide');
                    location.reload();
                }
            },
            error: function (xhr) {
                $('#main_loading_screen').hide();
                let message = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    message = xhr.responseText;
                }
                alert(message);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    function delete_click(id) {
        if (!confirm('Are you sure you want to delete this credit?')) {
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('ai_credits.destroy', ':id') }}";
        url = url.replace(":id", id);

        $.ajax({
            url: url,
            type: 'DELETE',
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "block";
                }
            },
            success: function (data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    location.reload();
                }
            },
            error: function (error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                if (main_loading_screen) {
                    main_loading_screen.style.display = "none";
                }
                alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }
</script>
</body>

</html>
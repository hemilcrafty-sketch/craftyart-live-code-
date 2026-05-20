@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <!-- Left: Add Button -->
                        <div class="col-md-3">
                            @if ($roleManager::onlyDesignerAccess(Auth::user()->user_type))
                                <button type="button" class="btn btn-primary m-1 item-form-input"
                                    id="addNewbackgroundItemBtn">
                                    + Add bonus Package
                                </button>
                            @endif
                        </div>

                        <!-- Right: Filter Form -->
                        <div class="col-md-7">
                            {{-- @include('partials.filter_form', [
                                'action' => route('bonus-package.index'),
                            ]) --}}
                        </div>
                    </div>
                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>bonus Package</th>
                                    <th>INR Price</th>
                                    <th>USD Price</th>
                                    <th>Additional Day</th>
                                    <th class="datatable-nosort" style="width:150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($BouncePackages as $row)
                                    <tr>
                                        <td>{{ $row->id }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $row->bonus_code }}</span>
                                        </td>
                                        <td>{{ $row->inr_price }}</td>
                                        <td>{{ $row->usd_price }}</td>
                                        <td>{{ $row->additional_day }}</td>
                                        <td>
                                            <button type="button" class="dropdown-item edit-bonus"
                                                data-id="{{ $row->id }}" data-string_id="{{ $row->string_id }}"
                                                data-bonus_code="{{ $row->bonus_code }}"
                                                data-inr_price="{{ $row->inr_price }}"
                                                data-usd_price="{{ $row->usd_price }}"
                                                data-additional_day="{{ $row->additional_day }}">
                                                <i class="dw dw-edit2"></i> Edit
                                            </button>
                                            <form action="{{ route('bonus-package.destroy', $row->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item"><i
                                                        class="dw dw-delete-3"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                {{-- @include('partials.pagination', ['items' => $allCategories]) --}}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bounce_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add bonus Package</h5>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>

            <div class="modal-body">
                <form id="bounce_form" method="POST" action="{{ route('bonus-package.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="bounce_id">
                    <input type="hidden" name="string_id" id="string_id">

                    <div class="form-group mb-3">
                        <label>bonus Package</label>
                        <input type="text" name="bonus_code" id="bonus_code" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>INR Price</label>
                        <input type="number" step="0.01" name="inr_price" id="inr_price" class="form-control"
                            required>
                    </div>

                    <div class="form-group mb-3">
                        <label>USD Price</label>
                        <input type="number" step="0.01" name="usd_price" id="usd_price" class="form-control"
                            required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Additional Day</label>
                        <input type="number" name="additional_day" id="additional_day" class="form-control" required>
                    </div>

                    <div class="align-content-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    // 🔹 Add New bonus Code
    $(document).on("click", "#addNewbackgroundItemBtn", function() {
        $("#bounce_form")[0].reset(); // modified (was wrong form id)
        $("#bounce_id").val("");
        $("#string_id").val("");
        $("#modal_title").text("Add bonus Code");
        $("#bounce_modal").modal("show"); // modified (was wrong modal id)
    });

    // 🔹 Edit bonus Code
    $(document).on("click", ".edit-bonus", function() {
        $("#bounce_id").val($(this).data("id"));
        $("#string_id").val($(this).data("string_id"));
        $("#bonus_code").val($(this).data("bonus_code"));
        $("#inr_price").val($(this).data("inr_price"));
        $("#usd_price").val($(this).data("usd_price"));
        $("#additional_day").val($(this).data("additional_day"));

        $("#modal_title").text("Edit bonus Package");
        $("#bounce_modal").modal("show");
    });
</script>
</body>

</html>

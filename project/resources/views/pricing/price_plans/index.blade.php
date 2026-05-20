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
                    <div class="col-md-3 m-1">
                        <a href="{{ route('plans.create') }}" class="btn btn-primary item-form-input"> Add New Plans
                        </a>
                        <a href="{{ route('plans.create', ['type' => 'free']) }}"
                            class="btn item-form-input btn-success {{ $hasFreePlan ? 'disabled' : '' }}"
                            {{ $hasFreePlan ? 'aria-disabled=true tabindex=-1' : '' }}>
                            Add Free Plans
                        </a>
                    </div>

                    <div class="col-md-7">
                        @include('partials.filter_form ', [
                            'action' => route('plans.index'),
                        ])
                    </div>
                </div>
                <div class="flex-grow-1 overflow-auto">
                    <div class="scroll-wrapper table-responsive tableFixHead" style="max-height: 100%;">
                        <table id="temp_table" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Plan Name</th>
                                    <th style="width:200px">Description</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort" style="width:100px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="size_table">
                                @foreach ($plans as $plan)
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $plan->id }}</td>
                                        <td class="table-plus">{{ $plan->name }}</td>
                                        <td class="table-plus">{{ $plan->description }}</td>
                                        <td class="table-plus">{{ $plan->status ? 'Active' : 'InActive' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a class="dropdown-item"
                                                    href="{{ route('plans.create', ['id' => $plan->id]) }}">
                                                    <i class="dw dw-edit2"></i> Edit
                                                </a>

                                                <button class="dropdown-item"
                                                        onclick=" delete_click({{ $plan->id }}) "
                                                        {{ $plan->status ? '' : 'disabled' }}>
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
                @include('partials.pagination', ['items' => $plans])
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    function delete_click(id) {
        if (!confirm("Are you sure you want to InActive this plan ? ")) {
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        var url = "{{ route('plans.destroy', ':id') }}".replace(":id", id);
        $.ajax({
            url: url,
            type: 'DELETE',
            success: function(data) {
                if (data.status) {
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            },
            error: function(xhr) {
                alert("Error: " + xhr.responseText);
            }
        });
    }
</script>
</body>

</html>

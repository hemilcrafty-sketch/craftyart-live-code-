@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                    <div class="row justify-content-between">
                        <div class="col-md-3 m-1">
                            @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                                <a class="btn btn-primary item-form-input"
                                    href="{{ route('video_sizes.create') }}">Add New Video Size</a>
                            @endif
                        </div>
                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('video_sizes.index'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(108vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width:50px">Id</th>
                                    <th>Size Name</th>
                                    <th style="width:170px">Categories</th>
                                    <th>User</th>
                                    <th>Width Ration</th>
                                    <th>Height Ration</th>
                                    <th style="width:80px">Paper Size</th>
                                    <th>Units</th>
                                    <th style="width:80px">Status</th>
                                    <th style="width:200px" class="datatable-nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody id="size_table">
                                @foreach ($sizes as $size)
                                    @php
                                        $categoryIds =
                                            isset($size->category_id) && $size->category_id != null
                                                ? json_decode($size->category_id, true)
                                                : [];
                                        if (!is_array($categoryIds)) {
                                            $categoryIds = [$categoryIds];
                                        }
                                    @endphp
                                    <tr style="background-color: #efefef;">
                                        <td class="table-plus">{{ $size->id }}</td>
                                        <td class="table-plus">{{ $size->size_name }}</td>
                                        <td class="table-plus">
                                            {{ \App\Http\Controllers\HelperController::getVideoMainCatNames($categoryIds) }}
                                        </td>
                                        <td class="table-plus">
                                            {{ $roleManager::getUploaderName($size->emp_id) }}
                                        </td>
                                        <td class="table-plus">{{ $size->width_ration }}, {{ $size->width }}</td>
                                        <td class="table-plus">{{ $size->height_ration }}, {{ $size->height }}</td>
                                        <td class="table-plus">{{ $size->paper_size }}</td>
                                        <td class="table-plus">{{ $size->unit ? \Str::title($size->unit) : '—' }}</td>
                                        <td class="table-plus">{{ $size->status ? 'Active' : 'UnActive' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a class="dropdown-item"
                                                    href="{{ route('video_sizes.edit', $size->id) }}"><i
                                                        class="dw dw-edit2"></i> Edit</a>
                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <button class="dropdown-item"
                                                        onclick="videoSizeDelete('{{ $size->id }}')"><i
                                                            class="dw dw-delete-3"></i> Delete</button>
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
                @include('partials.pagination', ['items' => $sizes])
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    function videoSizeDelete(id) {
        if (!confirm('Are you sure you want to delete this video size?')) {
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ url('video_sizes') }}/" + id;

        $.ajax({
            url: url,
            type: 'DELETE',
            beforeSend: function() {
                $('#main_loading_screen').show();
            },
            success: function(data) {
                $('#main_loading_screen').hide();
                if (data.error) {
                    alert('error==>' + data.error);
                } else {
                    location.reload();
                }
            },
            error: function(error) {
                $('#main_loading_screen').hide();
                alert(error.responseText);
            }
        });
    }
</script>
</body>

</html>

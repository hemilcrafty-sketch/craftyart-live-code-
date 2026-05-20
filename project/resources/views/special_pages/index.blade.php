@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">

    <div class="pd-ltr-10 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                    <div class="row justify-content-between">
                        <div class="col-md-3 m-1">
                            @if ($roleManager::onlySeoAccess(Auth::user()->user_type) && !$roleManager::isSeoManager(Auth::user()->user_type))
                                <a class="btn btn-primary item-form-input" href="create_pages" role="button"> Add </a>
                            @endif
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('special_page.index'),
                                'filterExtraNoIndex' => true,
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th class="datatable-nosort">Id</th>
                                    <th class="datatable-nosort" style="width:150px">Created Record</th>
                                    <th class="datatable-nosort" style="width:150px">Updated Record</th>
                                    <th class="datatable-nosort">User</th>
                                    <th class="datatable-nosort">Title</th>
                                    <th style="width: 250px;">Description</th>
                                    <th>Meta title</th>
                                    <th style="width: 200px;">Meta Description</th>
                                    <th>No Index</th>
                                    <th>Status</th>
                                    <th class="datatable-nosort" style="width:200px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($specialPages as $specialPage)
                                    <tr>
                                        <td class="table-plus">{{ $specialPage->id }}</td>
                                        <td class="table-plus">{{ $specialPage->created_at }}</td>
                                        <td class="table-plus">{{ $specialPage->updated_at }}</td>
                                        <td>{{ $roleManager::getUploaderName($specialPage->emp_id) }}</td>
                                        <td class="table-plus">{{ $specialPage->title }}</td>
                                        <td class="table-plus">{{ $specialPage->description }}</td>
                                        <td class="table-plus">{{ $specialPage->meta_title }}</td>
                                        <td class="table-plus">{{ $specialPage->meta_desc }}</td>
                                        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                            @if ($specialPage->no_index == '1')
                                                <td><label id="noindex_label_{{ $specialPage->id }}"
                                                        style="display: none;">TRUE</label><Button style="border: none"
                                                        onclick="noindex_click(this, '{{ $specialPage->id }}')"><input
                                                            type="checkbox" checked class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @else
                                                <td><label id="noindex_label_{{ $specialPage->id }}"
                                                        style="display: none;">FALSE</label><Button style="border: none"
                                                        onclick="noindex_click(this, '{{ $specialPage->id }}')"><input
                                                            type="checkbox" class="switch-btn" data-size="small"
                                                            data-color="#0059b2" /></Button></td>
                                            @endif
                                        @else
                                            @if ($specialPage->no_index == '1')
                                                <td>True</td>
                                            @else
                                                <td>False</td>
                                            @endif
                                        @endif
                                        <td class="table-plus">
                                            {{ $specialPage->status == 0 ? 'Draft' : 'Publish' }}
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="dropdown-item"><a
                                                        href="{{ route('edit_pages', ['id' => $specialPage->id]) }}"><i
                                                            class="dw dw-edit2"></i>
                                                        Edit</a></button>
                                                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); deletePages({{ $specialPage->id }})">
                                                        <i class="dw dw-delete-3"></i> Delete
                                                    </a>
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
                @include('partials.pagination', ['items' => $specialPages])
            </div>
        </div>
    </div>
</div>
</div>
@include('layouts.masterscript')
<script>
    function deletePages(id) {
        if (confirm('Are you sure you want to delete this page')) {
            $.ajax({
                url: "{{ route('special_page.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr) {}
            });
        }
    }

    function noindex_click(parentElement, $id) {
        let element = parentElement.firstElementChild;
        const originalChecked = element.checked;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var status = $id;
        var url = "{{ route('check_n_i') }}";
        var formData = new FormData();
        formData.append('id', $id);
        formData.append('type', 's_page');
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                if (data.error) {
                    alert(data.error);
                    element.checked = !originalChecked;
                    element.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                } else {
                    var x = document.getElementById("noindex_label_" + $id);
                    if (x.innerHTML === "TRUE") {
                        x.innerHTML = "FALSE";
                    } else {
                        x.innerHTML = "TRUE";
                    }
                }

            },
            error: function(error) {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "none";
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    }
</script>
<script>
    const sortTable = (event, column, sortType) => {
        event.preventDefault();
        let url = new URL(window.location.href);
        url.searchParams.set('sort_by', column);
        url.searchParams.set('sort_order', sortType);
        window.location.href = url.toString();
    }
</script>
</body>

</html>

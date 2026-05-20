   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div class="pd-20">

                        <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                            data-target="#add_seach_tag_model" type="button">
                            Add Search Tag </a>
                    </div>

                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Old Slug</th>
                                <th>New Slug</th>
                                <th class="datatable-nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($slugs['lists'] as $slug)
                                <tr>
                                    <td class="table-plus">{{ $slug->id }}</td>

                                    <td>{{ $roleManager::getUploaderName($slug->emp_id) }}</td>

                                    <td>{{ $slug->typeName }}</td>

                                    <td class="table-plus">{{ $slug->old_slug }}</td>
                                    <td class="table-plus">{{ $slug->new_slug }}</td>

                                    <td>
                                        <Button class="dropdown-item"
                                            onclick="edit_click('{{ $slug->id }}', '{{ $slug->old_slug }}', '{{ $slug->new_slug }}', '{{ $slug->type }}')"
                                            data-backdrop="static" data-toggle="modal"
                                            data-target="#edit_search_tag_model"><i class="dw dw-edit2"></i>
                                            Edit</Button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_seach_tag_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Page Slug</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_tag_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h7>Old Slug</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Search Tag" name="old_slug"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>New Slug</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Search Tag" name="new_slug"
                                required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Type</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" name="type">

                                @foreach ($slugs['types'] as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" type="submit" name="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_search_tag_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Page Slug</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="update_model">
                <form method="post" id="edit_tag_form" enctype="multipart/form-data">
                    @csrf
                    <input class="form-control" type="textname" id="page_id" name="page_id" style="display: none" />
                    <div class="form-group">
                        <h7>Old Slug</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Search Tag" id="old_slug"
                                name="old_slug" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h7>New Slug</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Search Tag" id="new_slug"
                                name="new_slug" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Type</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" id="type"
                                name="type">

                                @foreach ($slugs['types'] as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary btn-block"
                                id="update_click">Update</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    function edit_click(id, old_slug, new_slug, type) {
        $('#page_id').val(id);
        $('#old_slug').val(old_slug);
        $('#new_slug').val(new_slug);
        $('#type').val(type).trigger('change');
    }

    $('#add_tag_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'create_page_slug',
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
                    window.alert('error==>' + data.error);
                } else {
                    location.reload();
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
    });

    $(document).on('click', '#update_click', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("edit_tag_form"));
        var status = formData.get("page_id");


        var url = "{{ route('edit_page_slug', ':status') }}";
        url = url.replace(":status", status);

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
                    window.alert('error==>' + data.error);
                } else {
                    location.reload();
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
    });
</script>

</body>

</html>

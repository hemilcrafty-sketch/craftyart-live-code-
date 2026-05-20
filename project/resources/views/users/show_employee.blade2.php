@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@php
use App\Enums\UserRole;
@endphp
@include('layouts.masterhead')
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div class="pd-20">

                        <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                           data-target="#add_employee_model" type="button">
                            Add Employee </a>
                    </div>

                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="scroll-wrapper table-responsive tableFixHead">
                                    <table id="temp_table" style="table-layout: fixed; width: 100%;"
                                           class="table table-striped table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Index</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Employee Type</th>
                                            <th>Manager/Executive</th>
                                            <th>Status</th>
                                            <th class="datatable-nosort">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach ($employeeArray as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>

                                            <td>{{ $user->name }}</td>

                                            <td>{{ $user->email }}</td>

                                            <td>{{ $roleManager::getUserType($user->user_type) }}</td>
                                            <td>
                                                @if($user->team_leader_id)
                                                {{ $user->leader->name }}
                                                @else
                                                -
                                                @endif
                                            </td>

                                            @if ($user->status == 1)
                                            <td>Enabled</td>
                                            @else
                                            <td>Disabled</td>
                                            @endif

                                            <td>
                                                <div class="dropdown">
                                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                       href="#" role="button" data-toggle="dropdown">
                                                        <i class="dw dw-more"></i>
                                                    </a>
                                                    <div
                                                            class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">

                                                        <Button class="dropdown-item"
                                                                onclick="edit_click('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->user_type }}', '{{ $user->team_leader_id }}')"
                                                                data-backdrop="static" data-toggle="modal"
                                                                data-target="#edit_employee_model">
                                                            <i class="dw dw-edit2"></i>Edit
                                                        </Button>


                                                        <Button class="dropdown-item"
                                                                onclick="reset_click('{{ $user->id }}')"
                                                                data-backdrop="static" data-toggle="modal"
                                                                data-target="#reset_pass_model"><i
                                                                    class="icon-copy dw dw-refresh"></i>Reset
                                                            Password
                                                        </Button>

                                                        @if ($user->status == 1)
                                                        <Button class="dropdown-item"
                                                                onclick="delete_click('{{ $user->id }}')"><i
                                                                    class="dw dw-delete-3"></i> Disable
                                                        </Button>
                                                        @else
                                                        <Button class="dropdown-item"
                                                                onclick="delete_click('{{ $user->id }}')"><i
                                                                    class="dw dw-delete-3"></i> Enable
                                                        </Button>
                                                        @endif

                                                    </div>
                                                </div>
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
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_employee_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Employee</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_employee_form" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <h6>Name</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Name" name="name" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Email</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Email" name="email" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Password</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Password" name="password"
                                   required />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Role</h6>
                        <div class="col-sm-20">
                            <select id="user_type" class="selectpicker form-control" data-style="btn-outline-primary"
                                    name="user_type" required>
                                <option value="">Select Type</option>
                                @foreach ($roles as $role)
                                @if ($role['id'] != UserRole::ADMIN->id())
                                <option value="{{ $role['id'] }}">{{ $role['role']->value }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Manager Dropdown (shown only for SEO_INTERN and SALES roles) -->
                    <div class="form-group" id="manager_section" style="display: none;">
                        <h6 id="manager_label">Select Manager</h6>
                        <select class="form-control" name="team_leader_id">
                            <option value="">Select Manager</option>
                        </select>
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" type="submit" name="submit" value="Submit">
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reset_pass_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Reset Password</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="reset_pass_form" enctype="multipart/form-data">
                    @csrf

                    <input type="text" class="form-control" id="password_id" name="password_id" required=""
                           style="display: none" />

                    <div class="form-group">
                        <h7>New Passowrd</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" id="new_password" placeholder="Passowrd"
                                   name="new_password" required="" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary btn-block" id="reset_btn">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_employee_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Edit Employee</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="update_model">
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    const roles = @json($roles);
    const showExecutive = @json($showExecutive);
    const salesManagers = @json($salesManagers);

    // Define role IDs from enum
    const SEO_INTERN_ID = {{ UserRole::SEO_INTERN->id() }};
    const SEO_EXECUTIVE_ID = {{ UserRole::SEO_EXECUTIVE->id() }};
    const SALES_ID = {{ UserRole::SALES->id() }};
    const SALES_MANAGER_ID = {{ UserRole::SALES_MANAGER->id() }};
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('user_type');
        const managerSection = document.getElementById('manager_section');
        const managerLabel = document.getElementById('manager_label');
        const managerDropdown = managerSection.querySelector('select[name="team_leader_id"]');

        roleSelect.addEventListener('change', function() {
            const selectedRole = parseInt(this.value);

            if (selectedRole === SEO_INTERN_ID) {
                // For SEO Intern, show SEO Executive dropdown
                managerLabel.textContent = 'Select SEO Executive';
                populateManagerDropdown(showExecutive, SEO_EXECUTIVE_ID);
                managerSection.style.display = 'block';
            } else if (selectedRole === SALES_ID) {
                // For Sales, show Sales Manager dropdown
                managerLabel.textContent = 'Select Sales Manager';
                populateManagerDropdown(salesManagers, SALES_MANAGER_ID);
                managerSection.style.display = 'block';
            } else {
                managerSection.style.display = 'none';
            }
        });

        function populateManagerDropdown(users, roleId) {
            managerDropdown.innerHTML = '<option value="">Select Manager</option>';
            users.forEach(user => {
                if (parseInt(user.user_type) === roleId) {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    managerDropdown.appendChild(option);
                }
            });
        }
    });

    function edit_click(id, name, email, user_type, team_leader_id) {
        $("#update_model").empty();
        let optionsHtml = "";

        roles.forEach(role => {
            if (role.id != SEO_INTERN_ID) { // Don't show ADMIN role
                const selected = role.id == user_type ? "selected" : "";
                optionsHtml += `<option value="${role.id}" ${selected}>${role.role}</option>`;
            }
        });

        const html = `
    <form method="post" id="edit_employee_form" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
        <input type="hidden" name="id" value="${id}" />
        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" name="name" value="${name}" required />
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" class="form-control" name="email" value="${email}" required />
        </div>
        <div class="form-group">
            <label>Role</label>
            <select id="user_type_edit" class="form-control" name="user_type">
                ${optionsHtml}
            </select>
        </div>
        <div class="form-group" id="manager_section_edit" style="display: none;">
            <label id="manager_label_edit">Select Manager</label>
            <select id="team_leader_id_edit" class="form-control" name="team_leader_id">
                <option value="">Select Manager</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary btn-block" id="update_click">Update</button>
    </form>
    `;

        $("#update_model").append(html);

        // Wait until HTML is rendered
        setTimeout(() => {
            const selectedRole = parseInt(user_type);

            if (selectedRole === SEO_INTERN_ID) {
                // For SEO Intern, show SEO Executive dropdown
                $("#manager_label_edit").text('Select SEO Executive');
                populateManagerDropdownEdit(showExecutive, SEO_EXECUTIVE_ID, team_leader_id);
                $("#manager_section_edit").show();
            } else if (selectedRole === SALES_ID) {
                // For Sales, show Sales Manager dropdown
                $("#manager_label_edit").text('Select Sales Manager');
                populateManagerDropdownEdit(salesManagers, SALES_MANAGER_ID, team_leader_id);
                $("#manager_section_edit").show();
            }

            $("#user_type_edit").on("change", function() {
                const selectedVal = parseInt($(this).val());

                if (selectedVal === SEO_INTERN_ID) {
                    // For SEO Intern, show SEO Executive dropdown
                    $("#manager_label_edit").text('Select SEO Executive');
                    populateManagerDropdownEdit(showExecutive, SEO_EXECUTIVE_ID);
                    $("#manager_section_edit").show();
                } else if (selectedVal === SALES_ID) {
                    // For Sales, show Sales Manager dropdown
                    $("#manager_label_edit").text('Select Sales Manager');
                    populateManagerDropdownEdit(salesManagers, SALES_MANAGER_ID);
                    $("#manager_section_edit").show();
                } else {
                    $("#manager_section_edit").hide();
                }
            });
        }, 10);

        function populateManagerDropdownEdit(users, roleId, selectedId = null) {
            const $dropdown = $("#team_leader_id_edit");
            $dropdown.empty().append(`<option value="">Select Manager</option>`);

            users.forEach(user => {
                if (parseInt(user.user_type) === roleId) {
                    const selected = user.id == selectedId ? "selected" : "";
                    $dropdown.append(`<option value="${user.id}" ${selected}>${user.name}</option>`);
                }
            });
        }
    }

    function reset_click($id) {
        $("#password_id").val($id);
    }

    $('#add_employee_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: 'create_employee',
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

        var formElement = document.getElementById("edit_employee_form");
        var formData = new FormData(formElement);
        var status = formData.get("employee_id");
        var url = "{{ route('employee.update', ':status') }}";
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
                    window.alert(data.error);
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

    $(document).on('click', '#reset_btn', function() {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(document.getElementById("reset_pass_form"));
        var status = formData.get("password_id");
        console.log(status)

        var url = "{{ route('employee.reset', ':status') }}";
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
                    window.alert(data.error);
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

    function delete_click(id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var url = "{{ route('employee.delete', ':id ') }}";
        url = url.replace(":id", id);

        $.ajax({
            url: url,
            type: 'POST',
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
    }
</script>
</body>

</html>
 
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">
                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <h1>Users</h1>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route('show_users') }}" method="GET">
                                        <div class="form-group">
                                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                <label>Search:<input type="text" class="form-control" name="query"
                                                        placeholder="Search here....."
                                                        value="{{ request()->input('query') }}"></label>
                                                <input type="hidden" class="form-control" name="type" value="{{$type}}">
                                                <button type="submit" class="btn btn-primary">Search</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-container">
                            <a class="card {{ $type == 'all' ? 'active' : '' }}" href="{{route('show_users')}}?type=all"
                                data-id="active_subscriber">
                                <div class="icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="content">
                                    <h3>All Users</h3>
                                    <p><strong>{{$userCount}}</strong></p>
                                </div>
                            </a>
                            <a class="card {{ $type == 'active' ? 'active' : '' }}"
                                href="{{route('show_users')}}?type=active" data-id="active_subscriber">
                                <div class="icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="content">
                                    <h3>Active Subscriber</h3>
                                    <p><strong>{{$activeSubscriberCount}}</strong></p>
                                </div>
                            </a>
                            <a class="card {{ $type == 'expired' ? 'active' : '' }}"
                                href="{{route('show_users')}}?type=expired" data-id="expired_subscriber">
                                <div class="icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="content">
                                    <h3>Expired Subscriber</h3>
                                    <p><strong>{{$expiredSubscriberCount}}</strong></p>
                                </div>
                            </a>
                            <a class="card {{ $type == 'upcomming' ? 'active' : '' }}"
                                href="{{route('show_users')}}?type=upcomming" data-id="upcomming_expired_subscriber">
                                <div class="icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div class="content">
                                    <h3>Upcoming Expired Subscriber</h3>
                                    <p><strong>{{$upcommingExpiredSubscriberCount}}</strong></p>
                                </div>
                            </a>
                        </div>

                        <section class="section-wrap {{ $type == 'all' ? 'active' : '' }}" id="userAllList">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Index</th>
                                            <th>UID</th>
                                            <th class="datatable-nosort">Picture</th>
                                            <th>Name</th>
                                            <th>Email OR Number</th>
                                            <th>Type</th>
                                            <th>Premium</th>
                                            <th>Created At</th>
                                            <th class="datatable-nosort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userArray['users'] as $user)
                                        <tr>
                                            <td><a href="javascript:void(0);" data-id="{{ $user->id }}"
                                                    class="btn-setting" id="setting-user-{{ $user->id }}"><svg
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                        style="width: 25px;fill: #5d5e5e;">
                                                        <!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                        <path
                                                            d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z" />
                                                    </svg></a>
                                                <div class="row-setting-popup">
                                                    <ul>
                                                        <li><a href="{{route('manage_subscription.show',$user->id)}}">Manage
                                                                Subscription</a></li>
                                                        <li><a
                                                                href="{{route('manage_template_product.show',$user->id)}}">Manage
                                                                Template Product</a></li>
                                                        <li><a href="{{route('manage_video_product.show',$user->id)}}">Manage
                                                                Video Product</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->uid}}</td>
                                            <td><img src="{{ config('filesystems.storage_url')
                                                        }}{{$user->photo_uri}}" style="max-width: 100px; max-height: 100px; width: auto; height:
                                                    auto" />
                                            </td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$helperController::getMailOrNumber($user)}}</td>
                                            <td>{{$user->login_type}}</td>
                                            @if($user->is_premium =='1')
                                            <td>TRUE</td>
                                            @else
                                            <td>FALSE</td>
                                            @endif
                                            <td>{{$user->created_at}}</td>
                                            <td><a href="user_detail/{{$user->uid}}">Show</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                        aria-live="polite">
                                        {{ $userArray['count_str'] }}</div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers"
                                        id="DataTables_Table_0_paginate">
                                        <ul class="pagination">
                                            {{
                                            $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4')
                                            }}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="section-wrap {{ $type == 'active' ? 'active' : '' }}"
                            id="allActiveSubscriberList">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Index</th>
                                            <th>UID</th>
                                            <th class="datatable-nosort">Picture</th>
                                            <th>Name</th>
                                            <th>Email OR Number</th>
                                            <th>Type</th>
                                            <th>Premium</th>
                                            <th>Created At</th>
                                            <th class="datatable-nosort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userArray['users'] as $user)
                                        <tr>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->uid}}</td>
                                            <td><img src="{{ config('filesystems.storage_url')
                                                        }}{{$user->photo_uri}}" style="max-width: 100px; max-height: 100px; width: auto; height:
                                                    auto" />
                                            </td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$helperController::getMailOrNumber($user)}}</td>
                                            <td>{{$user->login_type}}</td>
                                            @if($user->is_premium =='1')
                                            <td>TRUE</td>
                                            @else
                                            <td>FALSE</td>
                                            @endif
                                            <td>{{$user->created_at}}</td>
                                            <td><a href="user_detail/{{$user->uid}}">Show</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                        aria-live="polite">
                                        {{ $userArray['count_str'] }}</div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers"
                                        id="DataTables_Table_0_paginate">
                                        <ul class="pagination">
                                            {{
                                            $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4')
                                            }}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="section-wrap {{ $type == 'expired' ? 'active' : '' }}"
                            id="allExpiredSubscriberList">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Index</th>
                                            <th>UID</th>
                                            <th class="datatable-nosort">Picture</th>
                                            <th>Name</th>
                                            <th>Email OR Number</th>
                                            <th>Type</th>
                                            <th>Premium</th>
                                            <th>Created At</th>
                                            <th class="datatable-nosort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userArray['users'] as $user)
                                        <tr>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->uid}}</td>
                                            <td><img src="{{ config('filesystems.storage_url')
                                                        }}{{$user->photo_uri}}" style="max-width: 100px; max-height: 100px; width: auto; height:
                                                    auto" />
                                            </td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$helperController::getMailOrNumber($user)}}</td>
                                            <td>{{$user->login_type}}</td>
                                            @if($user->is_premium =='1')
                                            <td>TRUE</td>
                                            @else
                                            <td>FALSE</td>
                                            @endif
                                            <td>{{$user->created_at}}</td>
                                            <td><a href="user_detail/{{$user->uid}}">Show</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                        aria-live="polite">
                                        {{ $userArray['count_str'] }}</div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers"
                                        id="DataTables_Table_0_paginate">
                                        <ul class="pagination">
                                            {{
                                            $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4')
                                            }}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="section-wrap {{ $type == 'upcomming' ? 'active' : '' }}"
                            id="allUpcommingExpiredSubscriberList">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Index</th>
                                            <th>UID</th>
                                            <th class="datatable-nosort">Picture</th>
                                            <th>Name</th>
                                            <th>Email OR Number</th>
                                            <th>Type</th>
                                            <th>Premium</th>
                                            <th>Created At</th>
                                            <th class="datatable-nosort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userArray['users'] as $user)
                                        <tr>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->uid}}</td>
                                            <td><img src="{{ config('filesystems.storage_url')
                                                        }}{{$user->photo_uri}}" style="max-width: 100px; max-height: 100px; width: auto; height:
                                                    auto" />
                                            </td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$helperController::getMailOrNumber($user)}}</td>
                                            <td>{{$user->login_type}}</td>
                                            @if($user->is_premium =='1')
                                            <td>TRUE</td>
                                            @else
                                            <td>FALSE</td>
                                            @endif
                                            <td>{{$user->created_at}}</td>
                                            <td><a href="user_detail/{{$user->uid}}">Show</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                        aria-live="polite">
                                        {{ $userArray['count_str'] }}</div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers"
                                        id="DataTables_Table_0_paginate">
                                        <ul class="pagination">
                                            {{
                                            $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4')
                                            }}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $(document).ready(function () {
        $('.btn-setting').on('click', function () {
            $('.row-setting-popup').hide();
            var $popup = $(this).next('.row-setting-popup');
            $popup.toggle();
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.btn-setting').length) {
                $('.row-setting-popup').hide();
            }
        });

        $(document).on('click', ".card-container .card", function (event) {
            $(".card").removeClass("active");
            $(this).addClass("active");
        });
    });
</script>
</body>

</html>
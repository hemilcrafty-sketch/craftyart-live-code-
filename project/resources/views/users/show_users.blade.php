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
                         </div>
                         <div class="card-container">
                             <a class="card {{ $type == 'all' ? 'active' : '' }} all-card"
                                 href="{{ route('show_users') }}?type=all" data-id="active_subscriber">
                                 <div class="content">
                                     <h3>All Users</h3>
                                     <p><strong>{{ $userCount }}</strong></p>
                                 </div>
                             </a>
                             <a class="card {{ $type == 'active' ? 'active' : '' }} active-card"
                                 href="{{ route('show_users') }}?type=active" data-id="active_subscriber">
                                 <div class="content">
                                     <h3>Active Subscriber</h3>
                                     <p><strong>{{ $activeSubscriberCount }}</strong></p>
                                 </div>
                             </a>
                             <a class="card {{ $type == 'expired' ? 'active' : '' }} expired-card"
                                 href="{{ route('show_users') }}?type=expired" data-id="expired_subscriber">
                                 <div class="content">
                                     <h3>Expired Subscriber</h3>
                                     <p><strong>{{ $expiredSubscriberCount }}</strong></p>
                                 </div>
                             </a>
                             <a class="card {{ $type == 'upcomming' ? 'active' : '' }} upcomming-card"
                                 href="{{ route('show_users') }}?type=upcomming"
                                 data-id="upcomming_expired_subscriber">
                                 <div class="content">
                                     <h3>Upcoming Expired Subscriber</h3>
                                     <p><strong>{{ $upcommingExpiredSubscriberCount }}</strong></p>
                                 </div>
                             </a>
                         </div>

                         <form method="GET" action="{{ route('show_users') }}" id="filter-form">
                             <div class="row justify-content-end gx-2 gy-2 m-3">
                                 <!-- Search Input -->
                                 <div class="col-md-2">
                                     <input type="text" name="query" class="form-control" placeholder="Search..."
                                         value="{{ request('query') }}">
                                 </div>

                                 <!-- Per Page Dropdown -->
                                 <div class="col-md-2">
                                     <select name="per_page" class="form-control">
                                         <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10
                                         </option>
                                         <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20
                                         </option>
                                         <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50
                                         </option>
                                         <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>
                                             100</option>
                                         <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>
                                             500</option>
                                         <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>
                                             1000</option>
                                     </select>
                                 </div>

                                 <!-- Apply Button -->
                                 <div class="col-md-2">
                                     <button type="submit" class="btn btn-primary w-100">Apply</button>
                                 </div>

                                 <!-- Export Button -->
                                 <div class="col-md-2">
                                     {{-- Hidden Export Checkbox to preserve export_all in query --}}
                                     <input type="hidden" name="export_all"
                                         value="{{ request('export_all') == '1' ? '1' : '0' }}">
                                     <a href="{{ route('users.export', request()->all()) }}"
                                         class="btn btn-success w-100">
                                         Export to Excel
                                     </a>
                                 </div>
                             </div>
                         </form>

                         <section class="section-wrap {{ $type == 'all' ? 'active' : '' }}" id="userAllList">
                             <div class="col-sm-12 table-responsive">
                                 <!-- The rest of your table structure -->
                                 <table class="table table-striped mt-2">
                                     <thead>
                                         <tr>
                                             <th></th>
                                             <th>
                                                 <a href="javascript:void(0)">
                                                     No
                                                     @php
                                                         $sortOrderById = 'desc';
                                                         if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                             $sortOrderById =
                                                                 request('sort_order', 'desc') == 'asc'
                                                                     ? 'asc'
                                                                     : 'desc';
                                                         }
                                                         if (request('sort_by') != null && request('sort_by') != 'id') {
                                                             $sortOrderById = '';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderById == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderById == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','desc')"></span>

                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Subscription</th>
                                             <th class="datatable-nosort">Template</th>
                                             <th class="datatable-nosort">Video</th>
                                             <th>
                                                 <a class="colum-tbl" href="javascript:void(0);">
                                                     Name
                                                     @php
                                                         $sortOrderByName = '';
                                                         if (request('sort_by') == 'name') {
                                                             $sortOrderByName =
                                                                 request('sort_order') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByName == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByName == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','desc')"></span>
                                                 </a>
                                             </th>
                                             <th>Email OR Number</th>
                                             <th>Premium</th>
                                             <th>
                                                 <a class="colum-tbl" href="javascript(void);">
                                                     Created At
                                                     @php
                                                         $sortOrderByCreated = '';
                                                         if (request('sort_by') == 'created_at') {
                                                             $sortOrderByCreated =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByCreated == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByCreated == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Action</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($userArray['users'] as $user)
                                             <tr>
                                                 <td>
                                                     <a href="javascript:void(0);" data-id="{{ $user->id }}"
                                                         class="btn-setting" id="setting-user-{{ $user->id }}">
                                                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                             style="width: 25px;fill: #5d5e5e;"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                                             <path
                                                                 d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z" />
                                                         </svg>
                                                     </a>
                                                     <div class="row-setting-popup">
                                                         <ul>
                                                             <li><a
                                                                     href="{{ route('manage_subscription.show', $user->id) }}">Manage
                                                                     Subscription</a></li>
                                                             <li><a
                                                                     href="{{ route('manage_template_product.show', $user->id) }}">Manage
                                                                     Template Product</a></li>
                                                             <li><a
                                                                     href="{{ route('manage_video_product.show', $user->id) }}">Manage
                                                                     Video Product</a></li>
                                                         </ul>
                                                     </div>
                                                 </td>
                                                 <td>{{ $user->id }}</td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserSubscriptionCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserTemplateCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserVideoCount($user->uid) }}
                                                 </td>
                                                 <td>{{ $user->name }}</td>
                                                 <td>{{ $helperController::getMailOrNumber($user) }}</td>
                                                 <td>{{ $user->is_premium == '1' ? 'TRUE' : 'FALSE' }}</td>
                                                 <td>{{ $user->created_at }}</td>
                                                 <td><a href="user_detail/{{ $user->uid }}">Show</a></td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="row">
                                 <div class="col-sm-12 col-md-5">
                                     <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                         aria-live="polite">{{ $userArray['count_str'] }}</div>
                                 </div>
                                 <div class="col-sm-12 col-md-7">
                                     <div class="dataTables_paginate paging_simple_numbers"
                                         id="DataTables_Table_0_paginate">
                                         <ul class="pagination">
                                             {{ $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4') }}
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </section>

                         <section class="section-wrap {{ $type == 'active' ? 'active' : '' }}"
                             id="allActiveSubscriberList">
                             <div class="col-sm-12 table-responsive">
                                 <form method="GET" action="{{ route('show_users') }}" id="filter-form">
                                     <div class="form-row">
                                         <div class="col">
                                             <input type="text" name="query" class="form-control"
                                                 placeholder="Search..." value="{{ request('query') }}">
                                         </div>
                                         <div class="col">
                                             <select name="per_page" class="form-control">
                                                 <option value="10"
                                                     {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                                 <option value="20"
                                                     {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                                                 <option value="50"
                                                     {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                                 <option value="100"
                                                     {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                                 <option value="500"
                                                     {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                                 <option value="1000"
                                                     {{ request('per_page') == '1000' ? 'selected' : '' }}>1000
                                                 </option>
                                             </select>
                                         </div>
                                         <div class="col">
                                             <input type="hidden" name="type" value="active">
                                             <button type="submit" class="btn btn-primary">Apply</button>
                                         </div>
                                         <div class="col">
                                             <label style="display: none;">
                                                 <input type="checkbox" name="export_all" value="1"
                                                     {{ request('export_all') == '1' ? 'checked' : '' }}> Export All
                                             </label>
                                             <a href="{{ route('active_subscription.export', request()->all()) }}"
                                                 class="btn btn-success">Export to Excel</a>
                                         </div>
                                     </div>
                                 </form>
                                 <!-- The rest of your table structure -->
                                 <table class="table table-striped mt-2">
                                     <thead>
                                         <tr>
                                             <th>
                                                 <a href="javascript:void(0);">No
                                                     @php
                                                         $sortOrderById = 'desc';
                                                         if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                             $sortOrderById =
                                                                 request('sort_order', 'desc') == 'asc'
                                                                     ? 'asc'
                                                                     : 'desc';
                                                         }
                                                         if (request('sort_by') != null && request('sort_by') != 'id') {
                                                             $sortOrderById = '';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderById == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderById == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Subscription</th>
                                             <th class="datatable-nosort">Template</th>
                                             <th class="datatable-nosort">Video</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Name
                                                     @php
                                                         $sortOrderByName = '';
                                                         if (request('sort_by') == 'name') {
                                                             $sortOrderByName =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByName == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByName == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','desc')"></span>
                                                 </a>
                                             </th>
                                             <th>Email OR Number</th>
                                             <th>Premium</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Created At
                                                     @php
                                                         $sortOrderByCreated = '';
                                                         if (request('sort_by') == 'created_at') {
                                                             $sortOrderByCreated =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByCreated == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByCreated == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Action</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($userArray['users'] as $user)
                                             <tr>
                                                 <td>{{ $user->id }}</td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserSubscriptionCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserTemplateCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserVideoCount($user->uid) }}
                                                 </td>
                                                 <td>{{ $user->name }}</td>
                                                 <td>{{ $helperController::getMailOrNumber($user) }}</td>
                                                 <td>{{ $user->is_premium == '1' ? 'TRUE' : 'FALSE' }}</td>
                                                 <td>{{ $user->created_at }}</td>
                                                 <td><a href="user_detail/{{ $user->uid }}">Show</a></td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="row">
                                 <div class="col-sm-12 col-md-5">
                                     <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                         aria-live="polite">{{ $userArray['count_str'] }}</div>
                                 </div>
                                 <div class="col-sm-12 col-md-7">
                                     <div class="dataTables_paginate paging_simple_numbers"
                                         id="DataTables_Table_0_paginate">
                                         <ul class="pagination">
                                             {{ $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4') }}
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </section>

                         <section class="section-wrap {{ $type == 'expired' ? 'active' : '' }}"
                             id="allExpiredSubscriberList">
                             <div class="col-sm-12 table-responsive">
                                 <form method="GET" action="{{ route('show_users') }}" id="filter-form">
                                     <div class="form-row">
                                         <div class="col">
                                             <input type="text" name="query" class="form-control"
                                                 placeholder="Search..." value="{{ request('query') }}">
                                         </div>
                                         <div class="col">
                                             <select name="per_page" class="form-control">
                                                 <option value="10"
                                                     {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                                 <option value="20"
                                                     {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                                                 <option value="50"
                                                     {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                                 <option value="100"
                                                     {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                                 <option value="500"
                                                     {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                                 <option value="1000"
                                                     {{ request('per_page') == '1000' ? 'selected' : '' }}>1000
                                                 </option>
                                             </select>
                                         </div>
                                         <div class="col">
                                             <input type="hidden" name="type" value="expired">
                                             <button type="submit" class="btn btn-primary">Apply</button>
                                         </div>
                                         <div class="col">
                                             <label style="display: none;">
                                                 <input type="checkbox" name="export_all" value="1"
                                                     {{ request('export_all') == '1' ? 'checked' : '' }}> Export All
                                             </label>
                                             <a href="{{ route('expired_subscription.export', request()->all()) }}"
                                                 class="btn btn-success">Export to Excel</a>
                                         </div>
                                     </div>
                                 </form>
                                 <!-- The rest of your table structure -->
                                 <table class="table table-striped mt-2">
                                     <thead>
                                         <tr>
                                             <th>
                                                 <a href="javascript:void(0);">No
                                                     @php
                                                         $sortOrderById = 'desc';
                                                         if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                             $sortOrderById =
                                                                 request('sort_order', 'desc') == 'asc'
                                                                     ? 'asc'
                                                                     : 'desc';
                                                         }
                                                         if (request('sort_by') != null && request('sort_by') != 'id') {
                                                             $sortOrderById = '';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderById == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderById == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Subscription</th>
                                             <th class="datatable-nosort">Template</th>
                                             <th class="datatable-nosort">Video</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Name
                                                     @php
                                                         $sortOrderByName = '';
                                                         if (request('sort_by') == 'name') {
                                                             $sortOrderByName =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByName == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByName == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','desc')"></span>
                                                 </a>
                                             </th>
                                             <th>Email OR Number</th>
                                             <th>Premium</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Created At
                                                     @php
                                                         $sortOrderByCreated = '';
                                                         if (request('sort_by') == 'created_at') {
                                                             $sortOrderByCreated =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByCreated == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByCreated == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Action</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($userArray['users'] as $user)
                                             <tr>
                                                 <td>{{ $user->id }}</td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserSubscriptionCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserTemplateCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserVideoCount($user->uid) }}
                                                 </td>
                                                 <td>{{ $user->name }}</td>
                                                 <td>{{ $helperController::getMailOrNumber($user) }}</td>
                                                 <td>{{ $user->is_premium == '1' ? 'TRUE' : 'FALSE' }}</td>
                                                 <td>{{ $user->created_at }}</td>
                                                 <td><a href="user_detail/{{ $user->uid }}">Show</a></td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="row">
                                 <div class="col-sm-12 col-md-5">
                                     <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                         aria-live="polite">{{ $userArray['count_str'] }}</div>
                                 </div>
                                 <div class="col-sm-12 col-md-7">
                                     <div class="dataTables_paginate paging_simple_numbers"
                                         id="DataTables_Table_0_paginate">
                                         <ul class="pagination">
                                             {{ $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4') }}
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </section>

                         <section class="section-wrap {{ $type == 'upcomming' ? 'active' : '' }}"
                             id="allUpcommingExpiredSubscriberList">
                             <div class="col-sm-12 table-responsive">
                                 <form method="GET" action="{{ route('show_users') }}" id="filter-form">
                                     <div class="form-row">
                                         <div class="col">
                                             <input type="text" name="query" class="form-control"
                                                 placeholder="Search..." value="{{ request('query') }}">
                                         </div>
                                         <div class="col">
                                             <select name="per_page" class="form-control">
                                                 <option value="10"
                                                     {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                                 <option value="20"
                                                     {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                                                 <option value="50"
                                                     {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                                 <option value="100"
                                                     {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                                 <option value="500"
                                                     {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                                 <option value="1000"
                                                     {{ request('per_page') == '1000' ? 'selected' : '' }}>1000
                                                 </option>
                                             </select>
                                         </div>
                                         <div class="col">
                                             <button type="submit" class="btn btn-primary">Apply</button>
                                             <input type="hidden" name="type" value="upcomming">
                                         </div>
                                         <div class="col">
                                             <label style="display: none;">
                                                 <input type="checkbox" name="export_all" value="1"
                                                     {{ request('export_all') == '1' ? 'checked' : '' }}> Export All
                                             </label>
                                             <a href="{{ route('users.export', request()->all()) }}"
                                                 class="btn btn-success">Export to Excel</a>
                                         </div>
                                     </div>
                                 </form>
                                 <!-- The rest of your table structure -->
                                 <table class="table table-striped mt-2">
                                     <thead>
                                         <tr>
                                             <th>
                                                 <a href="javascript:void(0);">No
                                                     @php
                                                         $sortOrderById = 'desc';
                                                         if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                             $sortOrderById =
                                                                 request('sort_order', 'desc') == 'asc'
                                                                     ? 'asc'
                                                                     : 'desc';
                                                         }
                                                         if (request('sort_by') != null && request('sort_by') != 'id') {
                                                             $sortOrderById = '';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderById == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderById == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'id','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Subscription</th>
                                             <th class="datatable-nosort">Template</th>
                                             <th class="datatable-nosort">Video</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Name
                                                     @php
                                                         $sortOrderByName = '';
                                                         if (request('sort_by') == 'name') {
                                                             $sortOrderByName =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByName == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByName == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'name','desc')"></span>
                                                 </a>
                                             </th>
                                             <th>Email OR Number</th>
                                             <th>Premium</th>
                                             <th>
                                                 <a href="javascript:void(0);">
                                                     Created At
                                                     @php
                                                         $sortOrderByCreated = '';
                                                         if (request('sort_by') == 'created_at') {
                                                             $sortOrderByCreated =
                                                                 request('sort_order', 'asc') == 'asc' ? 'asc' : 'desc';
                                                         }
                                                     @endphp
                                                     <span
                                                         class="sort-arrow sort-asc {{ $sortOrderByCreated == 'asc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','asc')"></span>
                                                     <span
                                                         class="sort-arrow sort-desc {{ $sortOrderByCreated == 'desc' ? 'active' : '' }}"
                                                         onclick="sortTable(event,'created_at','desc')"></span>
                                                 </a>
                                             </th>
                                             <th class="datatable-nosort">Action</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($userArray['users'] as $user)
                                             <tr>
                                                 <td>
                                                     <a href="javascript:void(0);" data-id="{{ $user->id }}"
                                                         class="btn-setting" id="setting-user-{{ $user->id }}">
                                                     </a>
                                                     <div class="row-setting-popup">
                                                         <ul>
                                                             <li><a
                                                                     href="{{ route('manage_subscription.show', $user->id) }}">Manage
                                                                     Subscription</a></li>
                                                             <li><a
                                                                     href="{{ route('manage_template_product.show', $user->id) }}">Manage
                                                                     Template Product</a></li>
                                                             <li><a
                                                                     href="{{ route('manage_video_product.show', $user->id) }}">Manage
                                                                     Video Product</a></li>
                                                         </ul>
                                                     </div>
                                                 </td>
                                                 <td>{{ $user->id }}</td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserSubscriptionCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserTemplateCount($user->uid) }}
                                                 </td>
                                                 <td>{{ App\Http\Controllers\HelperController::getUserVideoCount($user->uid) }}
                                                 </td>
                                                 <td>{{ $user->name }}</td>
                                                 <td>{{ $helperController::getMailOrNumber($user) }}</td>
                                                 <td>{{ $user->is_premium == '1' ? 'TRUE' : 'FALSE' }}</td>
                                                 <td>{{ $user->created_at }}</td>
                                                 <td><a href="user_detail/{{ $user->uid }}">Show</a></td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="row">
                                 <div class="col-sm-12 col-md-5">
                                     <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                         aria-live="polite">{{ $userArray['count_str'] }}</div>
                                 </div>
                                 <div class="col-sm-12 col-md-7">
                                     <div class="dataTables_paginate paging_simple_numbers"
                                         id="DataTables_Table_0_paginate">
                                         <ul class="pagination">
                                             {{ $userArray['users']->appends(request()->input())->links('pagination::bootstrap-4') }}
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
     $(document).ready(function() {
         $('.btn-setting').on('click', function() {
             $('.row-setting-popup').hide();
             var $popup = $(this).next('.row-setting-popup');
             $popup.toggle();
         });

         $(document).on('click', function(event) {
             if (!$(event.target).closest('.btn-setting').length) {
                 $('.row-setting-popup').hide();
             }
         });

         $(document).on('click', ".card-container .card", function(event) {
             $(".card").removeClass("active");
             $(this).addClass("active");
         });
     });

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

 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container ">
     <div class="">
         <div class="min-height-200px">
             <div class="card-box">
                 <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">
                     <div class="row justify-content-between">
                         <div class="col-md-3 m-1">
                             <a class="btn btn-primary" href="create_font" role="button"> Add New Font </a>
                         </div>

                         <div class="col-md-7">
                             @include('partials.filter_form ', [
                                 'action' => route('show_fonts'),
                             ])
                         </div>
                     </div>


                     <div class="scroll-wrapper table-responsive tableFixHead"
                         style="max-height: calc(110vh - 220px) !important">
                         <table id="temp_table" style="table-layout: fixed; width: 100%;"
                             class="table table-striped table-bordered mb-0">
                             <thead>
                                 <tr>
                                     <th class="datatable-nosort sorting_disabled">
                                         <a href="javascript:void(0);">Font Id
                                             @php
                                                 $sortOrderById = 'desc';
                                                 if (request('sort_by') == 'id' || request('sort_by') == null) {
                                                     $sortOrderById =
                                                         request('sort_order', 'desc') == 'asc' ? 'asc' : 'desc';
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
                                     <th class="datatable-nosort">
                                         <a href="javascript:void(0);">
                                             Font Name
                                             @php
                                                 $sortOrderByName = '';
                                                 if (request('sort_by') == 'name') {
                                                     $sortOrderByName =
                                                         request('sort_order', 'desc') == 'asc' ? 'asc' : 'desc';
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
                                     <th class="datatable-nosort">Extension</th>
                                     <th class="datatable-nosort">Uniname</th>
                                     <th class="datatable-nosort">Font Weight</th>
                                     <th class="datatable-nosort">Font Thumb</th>
                                     <th class="datatable-nosort">Status</th>
                                     <th class="datatable-nosort">User</th>
                                     <th class="datatable-nosort">Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($appArray as $app)
                                     <tr>
                                         <td class="table-plus">{{ $app->id }}</td>
                                         <td>{{ $app->name }}</td>
                                         <td>{{ $app->extension }}</td>
                                         <td>{{ $app->uniname }}</td>
                                         <td>{{ $app->fontWeight }}</td>
                                         <td><img src="{{ config('filesystems.storage_url') }}{{ $app->thumb }}"
                                                 width="100" /></td>

                                         @if ($app->status == '1')
                                             <td>LIVE</td>
                                         @else
                                             <td>NOT LIVE</td>
                                         @endif
                                         <td>{{ $roleManager::getUploaderName($app->emp_id) }}
                                         </td>
                                         <td>
                                             <div class="d-flex">

                                                 <a class="dropdown-item" href="edit_font/{{ $app->id }}"><i
                                                         class="dw dw-edit2"></i> Edit</a>
                                                 @if ($roleManager::isAdminOrDesignerManager(Auth::user()->user_type))
                                                     <a class="dropdown-item" href="delete_font/{{ $app->id }}"><i
                                                             class="dw dw-delete-3"></i> Delete</a>
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

                 @include('partials.pagination', ['items' => $appArray])
             </div>
         </div>
     </div>
 </div>
 </div>
 @include('layouts.masterscript')
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

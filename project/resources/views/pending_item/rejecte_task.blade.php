   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container">
       {{-- <div class="pd-ltr-20 xs-pd-20-10"> --}}
       <div class="min-height-200px">
           <a href="{{ route('show_pending_task') }}" class="btn btn-info mt-2 mb-2"><i class="bi bi-cloud-download"></i>
               Pending</a>
           <div class="card-box mb-30">
               <div class="scroll-wrapper table-responsive tableFixHead"
                   style="max-height: calc(110vh - 220px) !important;">
                   <table class="table table-striped table-bordered mb-0" id="temp_table">
                       <thead>
                           <tr>
                               <th>Id</th>
                               <th>Name</th>
                               <th class="datatable-nosort">Description</th>
                               <th class="datatable-nosort">Changes BY</th>
                               <th class="datatable-nosort">Category Name</th>
                               <th class="datatable-nosort">Status</th>
                               <th class="datatable-nosort">Reason</th>
                               <th class="datatable-nosort">Created At</th>
                               <th class="datatable-nosort">Updated At</th>
                               <th class="datatable-nosort">Action</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach ($datas as $data)
                               @php
                                   $jsonData = json_decode($data->data);
                                   $categoryName = $jsonData->category_name ?? '';
                               @endphp
                               <tr>
                                   <td class="table-plus">{{ $data->id }}</td>
                                   <td>{{ $data->changes_title }}</td>
                                   <td>{{ $data->changes_desc }}</td>
                                   <td>{{ $data->requestor_name }}</td>
                                   <td>{{ $categoryName }}</td>
                                   <td>{{ $data->status_type }}</td>
                                   <td>{{ $data->reason }}</td>
                                   <td>{{ $data->created_at }}</td>
                                   <td>{{ $data->updated_at }}</td>
                                   <td>
                                       @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) || $roleManager::isSeoExecutive(Auth::user()->user_type))
                                           <div style="display: flex;gap: 10px;">
                                               <button class="btn btn-warning"
                                                   onclick="previewPage({{ $data->id }}, '{{ $data->preview_route }}')">
                                                   <i class="bi bi-eye"></i>
                                               </button>
                                           </div>
                                       @endif
                                   </td>
                               </tr>
                           @endforeach
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>

   <!-- Rejection Reason Modal -->



   @include('layouts.masterscript')
   <script>
       function previewPage(id, routeKey) {
           if (!routeKey) {
               alert("Preview route not set.");
               return;
           }

           const baseUrl = "{{ url('/') }}"; // gives http://192.168.29.121/templates3
           const fullRoute = `${baseUrl}/resubmit/${routeKey}/${id}`;

           window.open(fullRoute, '_blank');
       }
   </script>
   </body>

   </html>

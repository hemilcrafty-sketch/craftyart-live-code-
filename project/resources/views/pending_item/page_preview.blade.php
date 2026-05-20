   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container">
       <div class="min-height-200px">
           <div class="card-box">
               <div class="scroll-wrapper table-responsive tableFixHead p-3"
                   style="max-height: calc(117vh - 220px) !important;">
                   <table id="temp_table" class="table table-striped table-bordered mb-0">
                       <tr>
                           <th>Change Title</th>
                           <th>Description</th>
                           <th>Action Type</th>
                           <th>Target Table</th>
                           <th>Requested By</th>
                       </tr>
                       <tr>
                           <td>{{ $task->changes_title }}</td>
                           <td>{{ $task->changes_desc }}</td>
                           <td>{{ ucfirst($task->action) }}</td>
                           <td>{{ $task->table_name }}</td>
                           <td>{{ $task->requestor_name }}</td>
                       </tr>

                   </table>

                   <h5 class="mt-4"></h5>
                   <table class="table table-bordered">
                       <thead>
                           <tr>
                               <th>Field</th>
                               <th>Value</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach ($data as $key => $value)
                               <tr>
                                   <td>{{ $key }}</td>
                                   <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                               </tr>
                           @endforeach
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>


   @include('layouts.masterscript')

   <script></script>
   </body>

   </html>

   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container">
       <div class="">
           <div class="min-height-200px">
               <div class="card-box">
                   <div class="row justify-content-between p-3">
                       <div class="col-md-3">
                           <button type="button" class="btn btn-primary m-1" id="addNewTemplateRate">
                               + Add Caricature Rate
                           </button>
                       </div>

                       <div class="col-md-7">
                           @include('partials.filter_form', [
                               'action' => route('caricatureRate.index'),
                           ])
                       </div>
                   </div>
               </div>

               <div class="row mt-2">
                   @foreach ($getTemplateData as $rate)
                       @php
                           $value = json_decode($rate->value, true);
                       @endphp

                       <div class="col-xl-3 mb-30">
                           <div class="card-box height-200-p widget-style2">
                               <div class="d-flex flex-wrap align-items-center">
                                   <div class="widget-data w-100">
                                       <div class="weight-600 font-15 text-center">
                                           Name : <span
                                               class="text-secondary fw-bold">{{ ucwords(str_replace('_', ' ', $rate->name)) }}</span>
                                       </div>
                                       <div class="row mt-3">
                                           <div class="col-6">
                                               <hr>
                                               <h6 class="text-center">INR Value</h6>
                                               <hr>
                                               <div class="details text-left mt-3" style="font-size:15px;">
                                                   <p>Base Price : <span class="text-secondary fw-bold">₹
                                                           {{ $value['inr']['base_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Head Price : <span class="text-secondary fw-bold">₹
                                                           {{ $value['inr']['head_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Max Price : <span class="text-secondary fw-bold">₹
                                                           {{ $value['inr']['max_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Editor's choice : <span class="text-secondary fw-bold">₹
                                                           {{ $value['inr']['editor_choice'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Animation : <span class="text-secondary fw-bold">₹
                                                           {{ $value['inr']['animation'] ?? '-' }}</span>
                                               </div>
                                           </div>
                                           <div class="col-6">
                                               <hr>
                                               <h6 class="text-center">USD Value</h6>
                                               <hr>
                                               <div class="details text-left mt-3" style="font-size:15px;">
                                                   <p>Base Price : <span class="text-secondary fw-bold">$
                                                           {{ $value['usd']['base_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Head Price : <span class="text-secondary fw-bold">$
                                                           {{ $value['usd']['head_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Max Price : <span class="text-secondary fw-bold">$
                                                           {{ $value['usd']['max_price'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Editor's choice : <span class="text-secondary fw-bold">$
                                                           {{ $value['usd']['editor_choice'] ?? '-' }}</span>
                                                   </p>
                                                   <p>Animation : <span class="text-secondary fw-bold">$
                                                           {{ $value['usd']['animation'] ?? '-' }}</span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="action d-flex text-right gap-2">
                                       <button class="dropdown-item edit-template-rate-btn"
                                           data-id="{{ $rate->id }}">
                                           <i class="dw dw-edit2"></i> Edit
                                       </button>
                                   </div>
                               </div>
                           </div>
                       </div>
                   @endforeach
               </div>
               @include('partials.pagination', ['items' => $getTemplateData])
           </div>
       </div>
   </div>

   <div class="modal fade" id="add_Caricature_Rate_model" tabindex="-1" role="dialog"
       aria-labelledby="myLargeModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="myLargeModalLabel">Add Template Rate</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">X</button>
               </div>

               <div class="modal-body">
                   <form id="templateRateForm">
                       @csrf
                       <input type="hidden" id="templateRateId" name="id">

                       <div class="form-group">
                           <label for="rateName">Name</label>
                           <input type="text" class="form-control" id="rateName" name="name"
                               placeholder="Enter name" required>
                       </div>

                       <h6 class="text-center">INR Value</h6>
                       <div class="row">
                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Base Price</label>
                                   <input type="number" min="0" class="form-control" id="inrBase"
                                       name="inr_base" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Head Price</label>
                                   <input type="number" min="0" class="form-control" id="inrHead"
                                       name="inr_head" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Max Price</label>
                                   <input type="number" min="0" class="form-control" id="inrMax"
                                       name="inr_max" placeholder="Enter value" required>
                               </div>
                           </div>
                       </div>

                       <div class="row">
                           <div class="col-md-6 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Editor's choice</label>
                                   <input type="number" min="0" class="form-control" id="inrEditorChoice"
                                       name="editor_choice" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-6 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Animation</label>
                                   <input type="number" min="0" class="form-control" id="inrAnimation"
                                       name="animation" placeholder="Enter value" required>
                               </div>
                           </div>
                       </div>

                       <h6 class="text-center">USD Value</h6>
                       <div class="row">
                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Base Price</label>
                                   <input type="number" min="0" step="0.01" class="form-control"
                                       id="usdBase" name="usd_base" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Head Price</label>
                                   <input type="number" min="0" step="0.01" class="form-control"
                                       id="usdHead" name="usd_head" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Max Price</label>
                                   <input type="number" min="0" step="0.01" class="form-control"
                                       id="usdMax" name="usd_max" placeholder="Enter value" required>
                               </div>
                           </div>
                       </div>

                       <div class="row">
                           <div class="col-md-6 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Editor's choice</label>
                                   <input type="number" min="0" step="0.01" class="form-control"
                                       id="usdEditorChoice" name="editor_choice" placeholder="Enter value" required>
                               </div>
                           </div>

                           <div class="col-md-6 col-sm-12">
                               <div class="form-group">
                                   <label class="text-secondary">Animation</label>
                                   <input type="number" min="0" step="0.01" class="form-control"
                                       id="usdAnimation" name="animation" placeholder="Enter value" required>
                               </div>
                           </div>
                       </div>

                       <div class="text-right">
                           <button type="submit" class="btn btn-primary">Save</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>
   @include('layouts.masterscript')
   <script>
       $(document).ready(function() {
           $('#addNewTemplateRate').on('click', function() {
               $('#templateRateForm')[0].reset();
               $('#rateName').prop('disabled', false);
               $('#templateRateId').val('');
               $('#add_Caricature_Rate_model .modal-title').text('Add Caricature Rate');
               $('#add_Caricature_Rate_model').modal('show');
           });

           function formatName(str) {
               return str
                   .replace(/_/g, ' ')
                   .replace(/\b\w/g, char => char.toUpperCase());
           }

           $(document).on('click', '.edit-template-rate-btn', function() {
               const id = $(this).data('id');

               $.get(`{{ url('templateRate') }}/${id}/edit`, function(response) {
                   if (response) {
                       $('#templateRateId').val(response.id);
                       $('#rateName').val(formatName(response.name)).prop('disabled', true);


                       let value = {};
                       value = typeof response.value === 'string' ? JSON.parse(response.value) :
                           response.value;
                       $('#inrBase').val(value.inr?.base_price ?? '');
                       $('#inrHead').val(value.inr?.head_price ?? '');
                       $('#inrMax').val(value.inr?.max_price ?? '');
                       $('#inrEditorChoice').val(value.inr?.editor_choice ?? '');
                       $('#inrAnimation').val(value.inr?.animation ?? '');

                       $('#usdBase').val(value.usd?.base_price ?? '');
                       $('#usdHead').val(value.usd?.head_price ?? '');
                       $('#usdMax').val(value.usd?.max_price ?? '');
                       $('#usdEditorChoice').val(value.usd?.editor_choice ?? '');
                       $('#usdAnimation').val(value.usd?.animation ?? '');
                       $('#add_Caricature_Rate_model .modal-title').text('Edit Caricature Rate');
                       $('#add_Caricature_Rate_model').modal('show');
                   }
               });
           });

           $('#templateRateForm').on('submit', function(e) {
               e.preventDefault();

               let id = $('#templateRateId').val();
               let name = $('#rateName').val();

               let value = {
                   inr: {
                       base_price: Number($('#inrBase').val()),
                       head_price: Number($('#inrHead').val()),
                       max_price: Number($('#inrMax').val()),
                       editor_choice: Number($('#inrEditorChoice').val()),
                       animation: Number($('#inrAnimation').val())
                   },
                   usd: {
                       base_price: Number($('#usdBase').val()),
                       head_price: Number($('#usdHead').val()),
                       max_price: Number($('#usdMax').val()),
                       editor_choice: Number($('#usdEditorChoice').val()),
                       animation: Number($('#usdEditorChoice').val()),
                   }
               };

               $.ajax({
                   url: "{{ route('templateRate.store') }}",
                   method: 'POST',
                   data: {
                       id: id,
                       name: name,
                       type: 1,
                       value: JSON.stringify(value),
                       _token: $('meta[name="csrf-token"]').attr('content')
                   },
                   success: function(response) {
                       location.reload();
                       $('#add_Caricature_Rate_model').modal('hide');
                       $('#templateRateForm')[0].reset();
                   },
                   error: function(err) {
                       console.error('Error:', err);
                   }
               });
           });

           // $(document).on('click', '.delete-template-btn', function() {
           //     let id = $(this).data('id');
           //
           //     if (confirm('Are you sure you want to delete this template rate?')) {
           //         $.ajax({
           //             url: "{{ route('templateRate.destroy', ':id') }}".replace(':id', id),
           //             type: 'DELETE',
           //             data: {
           //                 _token: $('meta[name="csrf-token"]').attr(
           //                     'content')
           //             },
           //             success: function(response) {
           //                 if (response.success) {
           //                     location.reload();
           //                 } else {
           //                     alert('Failed to delete the template rate.');
           //                 }
           //             },
           //             error: function() {
           //                 alert('Error occurred while deleting.');
           //             }
           //         });
           //     }
           // });

       });
   </script>
   </body>

   </html>

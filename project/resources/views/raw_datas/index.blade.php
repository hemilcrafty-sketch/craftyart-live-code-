   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container designer-access-container">
       <div class="">
           <div class="min-height-200px">
               <div class="card-box">
                   <div style="display: flex; flex-direction: column; height: 91vh; overflow: hidden;">
                       <!-- Filter Form -->
                       <div class="row justify-content-end">
                           <div class="col-md-9">
                               @include('partials.filter_form', [
                                   'action' => route('raw_datas.index'),
                               ])
                           </div>
                       </div>

                       <!-- Table Wrapper -->
                       <div class="scroll-wrapper table-responsive tableFixHead"
                           style="max-height: calc(110vh - 220px) !important">
                           <table id="temp_table" class="table table-striped table-bordered mb-0 mt-1"
                               style="table-layout: fixed; width: 100%;">
                               <thead>
                                   <tr>
                                       <th>Id</th>
                                       <th>Name</th>
                                       <th class="datatable-nosort">Item Thumb</th>
                                       <th class="datatable-nosort text-center" style="width: 300px;">File</th>
                                       <th class="datatable-nosort">Asset Type</th>
                                       <th class="datatable-nosort">Action</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach ($rawDataItems as $rawDataItem)
                                       <tr>
                                           <td class="table-plus">{{ $rawDataItem->id }}</td>
                                           <td>{{ $rawDataItem->name ?? '' }}</td>
                                           <td>
                                               <img src="{{ $rawDataItem->thumbnail ? $contentManager::getStorageLink($rawDataItem->thumbnail) : '' }}"
                                                   style="max-width: 100px; max-height: 100px; width: auto; height: auto;" />
                                           </td>
                                           <td class="text-center">
                                               @if ($rawDataItem->asset_type === 'video')
                                                   <video controls style="max-width: 300px; max-height: 150px;">
                                                       <source
                                                           src="{{ $contentManager::getStorageLink($rawDataItem->image) }}"
                                                           type="video/mp4">
                                                       Your browser does not support the video tag.
                                                   </video>
                                               @elseif ($rawDataItem->asset_type === 'audio')
                                                   <audio controls style="max-width: 300px;">
                                                       <source
                                                           src="{{ $contentManager::getStorageLink($rawDataItem->image) }}"
                                                           type="audio/mpeg">
                                                       Your browser does not support the audio element.
                                                   </audio>
                                               @else
                                                   <img src="{{ $rawDataItem->image ? $contentManager::getStorageLink($rawDataItem->image) : '' }}"
                                                       style="max-width: 100px; max-height: 100px; width: auto; height: auto;" />
                                               @endif
                                           </td>
                                           <td>{{ $rawDataItem->asset_type }}</td>
                                           <td>
                                               @if ($rawDataItem->category_name === 'Not Assign')
                                                   <button class="dropdown-item"
                                                       id="edit-row-btn-{{ $rawDataItem->id }}"
                                                       onclick="editRawDataItem({{ $rawDataItem['id'] }})">
                                                       <i class="dw dw-edit2"></i> Edit
                                                   </button>
                                               @endif

                                               <!-- Hidden Edit Trigger -->
                                               <button id="btn-edit-trigger" data-backdrop="static" data-toggle="modal"
                                                   data-target="#edit_rawdata_model" type="button"
                                                   style="display: none;">
                                               </button>

                                               {{-- Uncomment to enable delete --}}
                                               {{-- <a class="dropdown-item" href="#" onclick="deleterawDataItem({{ $rawDataItem['id'] }})">
                                        <i class="dw dw-delete-3"></i> Delete
                                    </a> --}}
                                           </td>
                                       </tr>
                                   @endforeach
                               </tbody>
                           </table>

                           <!-- Pagination -->
                       </div>
                   </div>
                   <hr class="my-1">
                   @include('partials.pagination', ['items' => $rawDataItems])
               </div>
           </div>
       </div>
   </div>

   <div class="modal fade designer-access-container" id="edit_rawdata_model" tabindex="-1" role="dialog"
       aria-labelledby="myLargeModalLabel" aria-hidden="false">
       <div class="modal-dialog modal-dialog-centered" style="max-width: 676px;">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="myLargeModalLabel">Edit Raw Data</h5>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
               </div>
               <div id="result"></div>
               <div class="modal-body">
                   <form method="post" id="dynamic_form" enctype="multipart/form-data">
                       @csrf

                       <!-- Shape Category -->
                       <div class="form-group">
                           <h6>Shape Category</h6>
                           <select class="form-control" name="parent_category_id" id="parentCategorySelect">
                               <option value="">Select Shape Category</option>
                               <!-- Options will be populated dynamically -->
                           </select>
                       </div>

                       <!-- Sub Category -->
                       <div class="form-group">
                           <h6>Sub Category</h6>
                           <select class="form-control" name="sub_category_id" id="subCategorySelect">
                               <option value="">Select Subcategory</option>
                           </select>
                       </div>

                       <!-- Background Type (unchanged) -->
                       <div class="form-group" id="type_group" style="display: none;">
                           <h6 id="labelType">Background Type</h6>
                           <div class="col-sm-20">
                               <select id="item_type" class="selectpicker form-control" data-style="btn-outline-primary"
                                   name="item_type">
                               </select>
                           </div>
                       </div>

                       <input type="hidden" name="string_id" value="{{ $data['rawItem']->string_id ?? '' }}">
                       <input type="hidden" name="user_id" value="{{ $data['rawItem']->user_id ?? '' }}">
                       <input type="hidden" name="asset_type" value="{{ $data['rawItem']->asset_type ?? '' }}">
                       <input class="form-control" name="name" value="{{ $data['rawItem']->name ?? '' }}">
                       <input type="hidden" name="width" value="{{ $data['rawItem']->width ?? '' }}">
                       <input type="hidden" name="height" value="{{ $data['rawItem']->height ?? '' }}">
                       <input type="hidden" name="image" value="{{ $data['rawItem']->image ?? '' }}">
                       <input type="hidden" name="thumbnail" value="{{ $data['rawItem']->thumbnail ?? '' }}">
                       <input type="hidden" name="duration" value="{{ $data['rawItem']->duration ?? '' }}">
                       <input type="hidden" name="asset_size" value="{{ $data['rawItem']->asset_size ?? '' }}">
                       <input type="hidden" name="compress_vdo" value="{{ $data['rawItem']->compress_vdo ?? '' }}">

                       <div class="form-group">
                           <h6>Is Premium</h6>
                           <div class="col-sm-20">
                               <select id="is_premium" class="selectpicker form-control"
                                   data-style="btn-outline-primary" name="is_premium">
                                   <option value="0"
                                       {{ isset($data['rawItem']) && $data['rawItem']->is_premium == 0 ? 'selected' : '' }}>
                                       FALSE</option>
                                   <option value="1"
                                       {{ isset($data['rawItem']) && $data['rawItem']->is_premium == 1 ? 'selected' : '' }}>
                                       TRUE</option>
                               </select>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Status</h6>
                           <div class="col-sm-20">
                               <select class="selectpicker form-control" data-style="btn-outline-primary"
                                   id="status" name="status">
                                   <option value="1"
                                       {{ isset($data['rawItem']) && $data['rawItem']->status == 1 ? 'selected' : '' }}>
                                       LIVE</option>
                                   <option value="0"
                                       {{ isset($data['rawItem']) && $data['rawItem']->status == 0 ? 'selected' : '' }}>
                                       NOT LIVE</option>
                               </select>
                           </div>
                       </div>

                       <div>
                           <input type="hidden" name="rawDataItemId" id="rawDataItemId"
                               value="{{ $data['rawItem']->id ?? '' }}">
                           <input class="btn btn-primary" type="submit" name="Assign">
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>

   @include('layouts.masterscript')
   <script>
       const categories = {
           0: @json($data['stkCatArray']),
           1: @json($data['bgCatArray']),
           2: @json($data['frameCatArray']),
           3: @json($data['svgCatArray']),
           4: @json($data['audioCatArray']),
           5: @json($data['gifCatArray']),
           6: @json($data['videoCatArray']),
       };
       let rawTypes = @json($data['rawTypes']);
       let assetTypes = @json($data['assetTypes']);
       let bgMode = @json($data['bg_mode']);
       let stickerMode = @json($data['sticker_mode']);

       console.log(categories)

       function populateParentCategories(rawItem) {
           const $parentSelect = $('#parentCategorySelect').empty();
           $parentSelect.append('<option value="">Select Shape Category</option>');

           rawTypes.forEach((type, key) => {
               const assetType = rawItem.asset_type ?? null;
               if (
                   (assetType === assetTypes[0] && [0, 1].includes(key)) || // image -> sticker, background
                   (assetType === assetTypes[1] && key === 5) || // gif -> gif
                   (assetType === assetTypes[3] && key === 6) || // video -> video
                   (assetType === assetTypes[2] && key === 3) || // vector -> vector
                   (assetType === assetTypes[4] && key === 4) || // audio -> audio
                   (assetType === assetTypes[5] && key === 2) // frame -> frame
               ) {
                   const selected = key !== 0 && key !== 1 ? 'selected' : '';
                   $parentSelect.append(`<option value="${key}" ${selected}>${type}</option>`);
               }
           });

           $parentSelect.trigger('change');
       }

       $(document).on("change", "#parentCategorySelect", function() {
           const id = $(this).val();
           console.log(id)
           const $subSelect = $('#subCategorySelect');
           const $modeSelect = $('#item_type');
           $subSelect.empty().append('<option value="">Select Subcategory</option>');

           $('#type_group').toggle(id === "1" || id === "0"); // keep comparison consistent with string

           if (id === "") return;

           const subcats = categories[id]; // `categories` should have string keys like "0", "1", etc.
           if (!Array.isArray(subcats)) {
               console.warn("No subcategories for ID:", id);
               return;
           }

           subcats.forEach(sub => {
               const subName =
                   id === "0" ? sub.stk_category_name :
                   id === "1" ? sub.bg_category_name :
                   sub.name;

               $subSelect.append(`<option value="${sub.id}">${subName}</option>`);
           });

           $subSelect.trigger('change');

           if (id === "1" || id === "0") {
               $('#labelType').text(id === "1" ? "Background Type" : "Sticker Type")
               $modeSelect.empty();
               const modes = id === "1" ? bgMode : stickerMode;
               modes.forEach(mode => {
                   $modeSelect.append(`<option value="${mode.value}">${mode.type}</option>`);
               });

               $modeSelect.trigger('change');
           }
       });

       function editRawDataItem(id) {
           $.ajax({
               url: "{{ route('raw_datas.edit', ':id') }}".replace(':id', id),
               type: 'GET',
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function(response) {
                   if (response.status && response.data) {
                       const raw = response.data;

                       $('#edit_rawdata_model').modal('show');

                       $("input[name='name']").val(raw.name);
                       $("input[name='string_id']").val(raw.string_id);
                       $("input[name='user_id']").val(raw.user_id);
                       $("input[name='asset_type']").val(raw.asset_type);
                       $("input[name='width']").val(raw.width);
                       $("input[name='height']").val(raw.height);
                       $("input[name='image']").val(raw.image);
                       $("input[name='thumbnail']").val(raw.thumbnail);
                       $("input[name='asset_size']").val(raw.asset_size);
                       $("input[name='duration']").val(raw.duration);
                       $("input[name='compress_vdo']").val(raw.compress_vdo);
                       $("input[name='rawDataItemId']").val(raw.id);

                       populateParentCategories(raw);
                       // Show/hide item_type if needed
                       $('#type_group').toggle(selectedParentId == 1);
                   } else {
                       alert('Raw data not found.');
                   }
               },
               error: function(xhr) {
                   console.error(xhr);
                   alert('Failed to load raw data item.');
               }
           });
       }

       $('#dynamic_form').on('submit', function(event) {
           event.preventDefault();
           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           var categorySelected = $('#parentCategorySelect').val() !== '';
           var subCategorySelected = $('#subCategorySelect').val() !== '';

           if (!categorySelected) {
               alert('Please select a category.');
               return;
           }

           if (!subCategorySelected) {
               alert('Please select a subcategory.');
               return;
           }

           var formData = new FormData(this);
           formData.append('_method', 'PUT');
           var id = $("#rawDataItemId").val();
           var url = "{{ route('raw_datas.update', ['raw_data' => ':id']) }}".replace(':id', id);

           $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               success: function(data) {
                   if (data.error) {
                       window.alert(data.error);
                       $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                   } else {
                       window.location.replace("{{ route('raw_datas.index') }}");
                   }
                   setTimeout(function() {
                       $('#result').html('');
                   }, 3000);
               },
               error: function(error) {
                   window.alert(error.responseText);
               },
           });
       });


       /*const deleteUrlTemplate = "{{ url('svg_items') }}/";

       function deleterawDataItem(id) {
           if (confirm('Are You Sure You Want To Delete This Svg Item?')) {
               $.ajax({
                   url: deleteUrlTemplate + id,
                   type: 'DELETE',
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   success: function(response) {
                       if (response.status) {
                           window.location.reload();
                       } else {
                           alert(response.error || 'Something went wrong');
                       }
                   },
                   error: function(xhr) {
                       alert('An error occurred while deleting the item.');
                       console.error(xhr.responseText);
                   }
               });
           }
       }*/

       // function hideFields() {
       //     $("#svg_category_name").val('');
       //     $("#svg_category_thumb").val('');
       //     $("#sequence_number").val('');
       //     $("#status").val('');
       //     $("#item_thumb").val('');
       //     $("#item_file").val('');
       //     $("#parentCategoryInput span").text("=== none ===");
       // }
   </script>
   </body>

   </html>

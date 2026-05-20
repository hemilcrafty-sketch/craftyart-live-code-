   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')

   <div class="main-container seo-access-container">

       <div class="pd-ltr-20 xs-pd-20-10">
           <div class="min-height-200px">
               <div class="pd-20 card-box mb-30">
                   <form method="post" id="dynamic_form" enctype="multipart/form-data">
                       <span id="result"></span>
                       @csrf
                       <div class="row">
                           <div class="col-md-4">
                               <div class="form-group">
                                   <h6>Category Name</h6>
                                   <input class="form-control" type="textname" name="category_name" id="categoryName"
                                       required>
                               </div>
                           </div>

                           <div class="col-md-4">
                               <div class="form-group">
                                   <h6>Slug</h6>
                                   <input class="form-control" type="text" name="slug" id="slug"
                                          placeholder="Please Enter Slug"
                                          required>
                               </div>
                           </div>

                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <h6>Canonical Link</h6>
                                   <div class="input-group custom mb-0">
                                       <input type="text" class="form-control canonical_link"
                                           name="canonical_link" />
                                   </div>
                                   <p class="text-end" style="font-size: 12px;">Only admin or fenil can modify canonical
                                       link</p>
                               </div>
                           </div>



                           @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                               <div class="col-md-4 col-sm-12">
                                   <div class="form-group">
                                       <h6>Assign Sub Categories Tag</h6>
                                       <select class="form-control" id="assignSubCatSelect" name="seo_emp_id">
                                           <option disabled selected>Select</option>
                                           @foreach ($assignSubCat as $subcat)
                                               <option value="{{ $subcat->id }}">
                                                   {{ $subcat->name }}
                                               </option>
                                           @endforeach
                                       </select>
                                   </div>
                               </div>
                           @endif

                       </div>

                       <div class="row">
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Meta Title</h6>
                                   <input class="form-control" type="text" name="meta_title" id="meta_title"
                                       maxlength="60" oninput="updateCount(this, 'metaCounter')" required>
                                   <small id="metaCounter" class="text-muted">60 remaining of 60 letters</small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Primary Keyword</h6>
                                   <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                       name="primary_keyword" placeholder="Enter Primary Keyword" required>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H1 Tag</h6>
                                   <input class="form-control" type="text" name="h1_tag" id="h1_tag"
                                       maxlength="60" oninput="updateCount(this, 'h1Counter')" required>
                                   <small id="h1Counter" class="text-muted">60 remaining of 60 letters</small>
                               </div>
                           </div>


                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Tag Line</h6>
                                   <input class="form-control" type="textname" name="tag_line" required>
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Meta Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="meta_desc" maxlength="160" oninput="updateCount(this, 'metaDescCounter')"></textarea>
                                   <small id="metaDescCounter" class="text-muted">160 remaining of 160 characters</small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Short Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="short_desc" maxlength="350" oninput="updateCount(this, 'shortDescCounter')"></textarea>
                                   <small id="shortDescCounter" class="text-muted">350 remaining of 350 characters</small>
                               </div>
                           </div>


                           {{-- <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H2 Tag</h6>
                                   <input class="form-control" type="textname" name="h2_tag">
                               </div>
                           </div> --}}

                       </div>
                       <div class="form-group"">

                        <select class="form-control form-control-sm video-category-select seo-all-container" name="parent_category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($groupedVideoCategories as $group)
                            <optgroup label="{{ $group['parent']->category_name }}">
                                @foreach ($group['children'] as $child)
                                <option value="{{ $child->id }}">
                                    {{ $child->category_name }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>

                       </div>


                    <input type="hidden" name="category_id" value="0">

                       <div class="row">
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Category Thumb</h6>
                                   <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                       class="form-control-file form-control height-auto dynamic-file"
                                       data-imgstore-id="category_thumb" data-nameset="true">
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Mockup</h6>
                                   <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                       class="form-control-file form-control height-auto dynamic-file"
                                       data-imgstore-id="mockup" data-nameset="true">
                               </div>

                           </div>
                       </div>
                       <div class="form-group">
                           <h6>Banner</h6>
                           <input type="file" accept=".jpg, .jpeg, .webp, .svg"
                               class="form-control-file form-control height-auto dynamic-file"
                               data-imgstore-id="banner" data-nameset="true">
                       </div>

                       @include('partials.content_section', [
                           'contents' => old('contents'),
                           'ctaSection' => [],
                       ])
                       <div style="margin-bottom: 10px;">
                           @include('partials.faqs_section', ['faqs' => ''])
                       </div>

                       <div class="form-group">
                           <h6>Sequence Number</h6>
                           <input class="form-control" type="textname" id="sequence_number" name="sequence_number"
                               required>
                       </div>

                       <div class="form-group">
                           <h6>Status</h6>
                           <div class="col-sm-20">
                               <select class="selectpicker form-control status" data-style="btn-outline-primary"
                                   id="status" name="status">
                                   <option value="1">LIVE</option>
                                   <option value="0">NOT LIVE</option>
                               </select>
                           </div>
                       </div>

                       {{-- @section('content')
                           <div id="virtualcontainer">
                               @include('partials.virtual_section', [
                                   'virtualCondition' => json_encode([]),
                                   'nameset' => true,
                                   'limitSet' => false,
                                   'configFile' => 'videovirtualcolumns',
                               ])
                           </div> --}}

                           {{-- Add hidden field for generatedQuery with empty value for video virtual categories --}}
                           <input type="hidden" name="generatedQuery" value="">

                           <div>
                               <input class="btn btn-primary" type="submit" name="submit">
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>
       @include('layouts.masterscript')
       {{-- <script src="{{ asset('assets/js/video_virtual.js') }}?v={{ time() }}"></script> --}}
       <script>
           // Initialize Quill editor for More Template CTA modal
           let ctaMoreTemplateQuill = null;

           $('#api_virtual_modal').on('shown.bs.modal', function () {
               if (!ctaMoreTemplateQuill) {
                   const ctaMoreTemplateDesc = document.getElementById('ctaMoreTemplateDesc');
                   if (ctaMoreTemplateDesc) {
                       ctaMoreTemplateQuill = new Quill('#ctaMoreTemplateDesc', {
                           theme: 'snow',
                           modules: {
                               toolbar: [
                                   ['bold', 'italic', 'underline'],
                                   [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                   ['link'],
                                   ['clean']
                               ]
                           }
                       });
                   }
               }
           });

           // Only attach virtualcontainer event listeners if it exists
           const virtualcontainer = document.getElementById("virtualcontainer");
           if (virtualcontainer) {
               virtualcontainer.querySelector(".save-condition").addEventListener("click", function(event) {
                   saveCondition(event, virtualcontainer);
               });

               virtualcontainer.querySelector(".add-sorting").addEventListener("click", function(event) {
                   addSorting(event, virtualcontainer);
               });
           }
       </script>
       <script>
           $('#dynamic_form').on('submit', function(event) {
               event.preventDefault();

               // The dynamic file component handles file uploads and creates hidden inputs
               // We'll let the backend validation handle file requirements

               // Skip virtual query check for video virtual category pages
               // The virtual query is created inside the CTA modal instead
               // const virtualContainer = document.getElementById("virtualcontainer");
               // if (virtualContainer) {
               //     const generatedQuery = virtualContainer.querySelector("#generatedQuery");
               //     if (!generatedQuery.value) {
               //         alert("Please Create Virtual Query");
               //         return;
               //     }
               // }

               count = 0;
               $.ajaxSetup({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                   }
               });
               const formObject = {};
               var formData = new FormData(this);
               formData.forEach((value, key) => {
                   formObject[key] = value;
               });
               $.ajax({
                   url: 'submit_video_virtual_cat',
                   type: 'POST',
                   data: formData,
                   beforeSend: function() {
                       var main_loading_screen = document.getElementById("main_loading_screen");
                       main_loading_screen.style.display = "block";
                   },
                   success: function(data) {
                       hideFields();

                       if (data.error) {
                           var error_html = '';
                           window.alert(data.error)

                       } else {
                           $('#result').html('<div class="alert alert-success">' + data.success +
                               '</div>');
                           window.alert("Done")
                           window.location.href = "{{ route('show_video_virtual_cat') }}";
                       }
                   },
                   error: function(error) {
                       hideFields();
                       window.alert(error.responseText);
                   },
                   cache: false,
                   contentType: false,
                   processData: false
               })
           });

           function hideFields() {
               var main_loading_screen = document.getElementById("main_loading_screen");
               main_loading_screen.style.display = "none";
           }

           $(document).on('click', '#parentCategoryInput', function() {
               if ($('.parent-category-input').hasClass('show')) {
                   $('.parent-category-input').removeClass('show');
               } else {
                   $(".parent-category-input").addClass('show');
               }
           });

           $(document).on("click", ".category", function(event) {
               $(".category").removeClass("selected");
               $(".subcategory").removeClass("selected");
               var id = $(this).data('id');
               $("input[name='parent_category_id']").val(id);
               $("#parentCategoryInput span").html($(this).data('catname'));
               $('.parent-category-input').removeClass('show');
               $(this).addClass("selected");
           });

           $(document).on("click", ".subcategory", function(event) {
               event.stopPropagation();
               $(".category").removeClass("selected");
               $(".subcategory").removeClass("selected");
               var id = $(this).data('id');
               var parentId = $(this).data('pid');
               $("input[name='parent_category_id']").val(id);
               $('.parent-category-input').removeClass('show');
               $("#parentCategoryInput span").html($(this).data('catname'));
               $(this).addClass("selected");
           });

           $(document).on("click", "li.category.none-option", function() {
               $("input[name='parent_category_id']").val("0");
               $('.parent-category-input').removeClass('show');
               $("#parentCategoryInput span").html('== none ==');
           });

           // Category Name to Slug auto-generation
           $(document).ready(function() {
               const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
               
               $("#categoryName").off("input").on("input", function() {
                   const titleString = toTitleCase($(this).val());
                   $("#slug").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
                   $(this).val(titleString);
               });
           });

           function updateCount(input, counterId) {
    let max = 60; // default
    if (counterId === 'metaCounter') max = 60;
    if (counterId === 'h1Counter') max = 60;
    if (counterId === 'metaDescCounter') max = 160;
    if (counterId === 'shortDescCounter') max = 350;

    const remaining = max - input.value.length;
    const counterElement = document.getElementById(counterId);
    if (counterElement) {
        counterElement.textContent = remaining + ' remaining of ' + max + ' characters';
    }
}
       </script>




       </body>

       </html>

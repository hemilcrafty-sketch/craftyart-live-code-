 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container seo-access-container">



     <div class="pd-ltr-20 xs-pd-20-10">
         <div class="min-height-200px">

             <div class="page-header">
                 <div class="row">
                     <div class="col-md-6 col-sm-12">
                         <div class="title">
                             Keyword
                         </div>
                     </div>
                 </div>
             </div>

             <div class="pd-20 card-box mb-30">
                 <form method="post" id="add_keyword_form" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                         <div class="col-md-6 col-sm-12">
                             <div class="form-group">
                                 <h6>Keyword</h6>
                                 <div class="input-group custom">
                                     <input type="text" class="form-control" name="name" required="" />
                                 </div>
                             </div>
                         </div>

                         <div class="col-md-6 col-sm-12">
                             <div class="form-group">
                                 <h6>Canonical Link</h6>
                                 <div class="input-group custom mb-0">
                                     <input type="text" class="form-control canonical_link" name="canonical_link" />
                                 </div>
                                 <p class="text-end" style="font-size: 12px;">Only admin or fenil can modify canonical
                                     link</p>
                             </div>
                         </div>

                         <div class="col-md-6 form-group">
                             <h6>Title</h6>
                             <div class="input-group custom">
                                 <input type="text" class="form-control" name="title" maxlength="60"
                                     oninput="updateCount(this, 'titleCounter')" required>
                             </div>
                             <small id="titleCounter" class="text-muted">60 remaining of 60 letters</small>
                         </div>

                         <div class=" col-md-6 col-sm-12 form-group">
                             <h6>Primary Keyword</h6>
                             <div class="input-group custom">
                                 <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                     name="primary_keyword" placeholder="Enter Primary Keyword" required>
                             </div>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>H2 Tag</h6>
                         <div class="input-group custom">
                             <input type="text" class="form-control" id="h2_tag" name="h2_tag">
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Meta Title</h6>
                         <div class="input-group custom">
                             <input type="text" class="form-control" name="meta_title" maxlength="60"
                                 oninput="updateMetaCount(this)" required>
                         </div>
                         <small id="metaCounter" class="text-muted">60 remaining of 60 letters</small>
                     </div>

                     <div class="form-group">
                         <h6>Meta Desc</h6>
                         <div class="input-group custom">
                             <textarea oninput="autoResize(this)" class="form-control" name="meta_desc"></textarea>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Short Desc</h6>
                         <div class="input-group custom">
                             <textarea oninput="autoResize(this)" class="form-control" name="short_desc"></textarea>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Long Desc</h6>
                         <div class="input-group custom">
                             <textarea oninput="autoResize(this)" class="form-control" name="long_desc"></textarea>
                         </div>
                     </div>

                     <div class="form-group">
                         <h6>Banner</h6>
                         <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                             class="form-control-file form-control dynamic-file height-auto" data-imgstore-id="banner"
                             data-nameset="true">
                     </div>
                     <!-- <div style="margin-bottom: 10px;">
                            @include('partials.faqs_section', ['faqs' => ''])
                        </div> -->


                     @php
                         // Allow only Admin, SEO Manager, and SEO Executive to select category
                         $isRestricted = !$roleManager::isAdminOrSeoManager(Auth::user()->user_type);
                     @endphp

                     <div class="form-group category-dropbox-wrap">
                         <h6>Category</h6>

                         {{-- Main dropdown --}}
                         <div class="input-subcategory-dropbox {{ $isRestricted ? 'disabled-category' : '' }}"
                             id="parentCategoryInput"
                             @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                             <span>== none ==</span>
                             <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                         </div>

                         {{-- Dropdown list --}}
                         <div class="custom-dropdown parent-category-input"
                             @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                             <ul class="dropdown-menu-ul">
                                 <li class="category none-option">== none ==</li>
                                 @foreach ($allCategories as $category)
                                     @php
                                         $classBold =
                                             !empty($category['subcategories']) && isset($category['subcategories'][0])
                                                 ? 'has-children'
                                                 : 'has-parent';
                                     @endphp
                                     <li class="category {{ $classBold }}" data-id="{{ $category['id'] }}"
                                         data-catname="{{ $category['category_name'] }}">
                                         {{ $category['category_name'] }}
                                         @if (!empty($category['subcategories']))
                                             <ul class="subcategories">
                                                 @foreach ($category['subcategories'] as $subcategory)
                                                     @include('partials.subcategory-optgroup', [
                                                         'subcategory' => $subcategory,
                                                         'sub_category_id' => $subcategory['id'],
                                                         'sub_category_name' => $subcategory['category_name'],
                                                     ])
                                                 @endforeach
                                             </ul>
                                         @endif
                                     </li>
                                 @endforeach
                             </ul>
                         </div>
                     </div>

                     {{-- Hidden input --}}
                     <input type="hidden" name="category_id" value="0" {{ $isRestricted ? 'readonly' : '' }}>

                     {{-- Restriction message --}}
                     @if ($isRestricted)
                         <small class="text-danger">
                             You are not allowed to select a category (Only Admin, SEO Manager can).
                         </small>
                     @endif

                     @include('partials.top_template_categories', ['top_keywords' => []])
                     @include('partials.content_section', [
                         'contents' => $page->contents ?? old('contents'),
                         'ctaSection' => [],
                     ])
                     <div style="margin-bottom: 10px;">
                         @include('partials.faqs_section', ['faqs' => ''])
                     </div>

                     <div class="form-group">
                         <h6>Status</h6>
                         <div class="col-sm-20">
                             <select class="selectpicker status form-control" data-style="btn-outline-primary"
                                 name="status">
                                 <option value="1">Active</option>
                                 <option value="0">Disable</option>
                             </select>
                         </div>
                     </div>

                     @if($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                     <div class="row seo-all-container">
                         <div class="col-md-6">
                             <div class="form-group">
                                 <h6>Priority</h6>
                                 <input type="number" class="form-control seo-all-container" id="priority" name="priority"
                                     placeholder="0.90" step="0.01" min="0" max="1" value="0.90">
                                 <small class="text-muted">Value between 0.00 and 1.00</small>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <h6>Frequency</h6>
                                 <select id="frequency" class="form-control seo-all-container" name="frequency">
                                     <option value="daily" selected>Daily</option>
                                     <option value="weekly">Weekly</option>
                                     <option value="monthly">Monthly</option>
                                 </select>
                             </div>
                         </div>
                     </div>
                     @endif

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

 @include('layouts.masterscript')
 <script>
     function autoResize(textarea) {
         textarea.style.height = 'auto'; // Reset the height to auto
         textarea.style.height = textarea.scrollHeight + 'px'; // Set the height to match the content
     }
     $('.bg_type_id').change(function() {
         if ($(this).val() === '0' || $(this).val() === '1') {
             $('.back_image').attr('required', '');
             var x = document.getElementById("back_image_field");
             x.style.display = "block";

             $('.color_code').removeAttr('required');
             var x1 = document.getElementById("color_code_field");
             x1.style.display = "none";

         } else {
             $('.color_code').attr('required', '');
             var x = document.getElementById("color_code_field");
             x.style.display = "block";

             $('.back_image').removeAttr('required');
             var x1 = document.getElementById("back_image_field");
             x1.style.display = "none";

         }
     });


     $('#add_keyword_form').on('submit', function(event) {
         event.preventDefault();
         const parentDiv = document.querySelector('#sortable');
         if (parentDiv.children.length == 0) {
             window.alert('add top keywords');
             return;
         }


         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var formData = new FormData(this);

         $.ajax({
             url: 'submit_keyword',
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
                     window.location.href = "{{ route('show_keyword') }}";
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

     $(document).on('click', '#remove_bg_info', function() {
         $(this).closest(".row").remove();
     });

     $(document).on('click', '#add_text_info', function() {
         dynamic_text_field();
     });

     $(document).on('click', '#remove_text_info', function() {
         $(this).closest(".row").remove();
     });

     $(document).on('click', '#add_component_info', function() {
         dynamic_component_field();
     });

     $(document).on('click', '#remove_component_info', function() {
         $(this).closest(".row").remove();
     });

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
         $("input[name='category_id']").val(id);
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
         $("input[name='category_id']").val(id);
         $('.parent-category-input').removeClass('show');
         $("#parentCategoryInput span").html($(this).data('catname'));
         $(this).addClass("selected");
     });

     $(document).on("click", "li.category.none-option", function() {
         $("input[name='category_id']").val("0");
         $('.parent-category-input').removeClass('show');
         $("#parentCategoryInput span").html('== none ==');
     });

     function hideFields() {
         $('#createSizeForm')[0].reset();
         var main_loading_screen = document.getElementById("main_loading_screen");
         main_loading_screen.style.display = "none";
     }

     function updateMetaCount(input) {
         const max = 60;
         const remaining = max - input.value.length;
         document.getElementById('metaCounter').textContent =
             remaining + ' remaining of ' + max + ' letters';
     }

     // For pre-filled values (edit mode)
     document.addEventListener("DOMContentLoaded", function() {
         const metaTitleInput = document.querySelector('input[name="meta_title"]');
         if (metaTitleInput) {
             updateMetaCount(metaTitleInput);
         }
     });

     function updateCount(input, counterId) {
         const max = 60;
         const remaining = max - input.value.length;
         document.getElementById(counterId).textContent =
             remaining + ' remaining of ' + max + ' letters';
     }

     function updateCount(input, counterId) {
         const max = 60;
         const remaining = max - input.value.length;
         document.getElementById(counterId).textContent =
             remaining + ' remaining of ' + max + ' letters';
     }
 </script>

 </body>

 </html>

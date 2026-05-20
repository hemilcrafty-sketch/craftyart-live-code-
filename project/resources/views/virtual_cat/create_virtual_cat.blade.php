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
                           <div class="col-md-2">
                               <div class="form-group">
                                   <h6>Category Name</h6>
                                   <input class="form-control" type="textname" name="category_name" id="categoryName"
                                       required>
                               </div>
                           </div>

                           <div class="col-md-2">
                               <div class="form-group">
                                   <h6>ID Name</h6>
                                   <input class="form-control" type="textname" name="id_name" id="categoryIDName"
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
                       @include('videos.partials.sitemap_seo_fields', [
                           'priority' => old('priority', 0.90),
                           'frequency' => old('frequency', 'daily'),
                       ])
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
                                   <textarea style="height: 120px" class="form-control" name="meta_desc"></textarea>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Short Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="short_desc"></textarea>
                               </div>
                           </div>


                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H2 Tag</h6>
                                   <input class="form-control" type="textname" name="h2_tag">
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Long Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="long_desc"></textarea>
                               </div>
                           </div>

                       </div>
                       <div class="row">



                       </div>

                       <div class="row">

                       </div>
                       <div class="form-group">
                           <h6>Category Size</h6>
                           <input class="form-control" type="textname" name="size" required>
                       </div>
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

                       <div class="form-group">
                           <h6>Application</h6>
                           <div class="col-sm-20">
                               <select class="selectpicker form-control" data-style="btn-outline-primary"
                                   id="app_id" name="app_id" required>
                                   <option value="" selected="true" disabled>== Select Application ==</option>
                                   @foreach ($appArray as $app)
                                       <option value="{{ $app->id }}">{{ $app->app_name }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       @include('partials.content_section', [
                           'contents' => old('contents'),
                           'ctaSection' => [],
                       ])
                       <div style="margin-bottom: 10px;">
                           @include('partials.faqs_section', ['faqs' => ''])
                       </div>

                       @include('partials.top_template_categories', ['top_keywords' => []])


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

                       <div id="virtualcontainer">
                               @include('partials.virtual_section', [
                                   'virtualCondition' => json_encode([]),
                                   'nameset' => true,
                                   'limitSet' => false,
                               ])
                           </div>

                           <div>
                               <input class="btn btn-primary" type="submit" name="submit">
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>
       @include('layouts.masterscript')
       <script>
           const virtualcontainer = document.getElementById("virtualcontainer");

           virtualcontainer.querySelector(".save-condition").addEventListener("click", function(event) {
               saveCondition(event, virtualcontainer);
           });

           virtualcontainer.querySelector(".add-sorting").addEventListener("click", function(event) {
               addSorting(event, virtualcontainer);
           });
       </script>
       <script>
           $('#dynamic_form').on('submit', function(event) {
               event.preventDefault();
               const categoryThumbInpurt = document.querySelector("[name='category_thumb']");
               const mockupInpurt = document.querySelector("[name='mockup']");

               if (!categoryThumbInpurt.value || !mockupInpurt.value) {
                   alert("Please select Banner Thumb mockup file or enter a valid image URL.");
                   return;
               }

               const virtualContainer = document.getElementById("virtualcontainer");
               const generatedQuery = virtualContainer.querySelector("#generatedQuery");
               if (!generatedQuery.value) {
                   alert("Please Create Virtual Query");
                   return;
               }

               const parentDiv = document.querySelector('#sortable');
               if (parentDiv.children.length == 0) {
                   window.alert('add top keywords');
                   return;
               }

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
                   url: 'submit_virtual_cat',
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
                           window.location.href = "{{ route('show_virtual_cat') }}";
                       }
                       // setTimeout(function() {
                       //   $('#result').html('');
                       //   jQuery('#dynamic_form').get(0).reset();
                       //   $("#parentCategoryInput span").html("== none ==");
                       // }, 3000);
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

               // $("#category_name").val('');
               // $("#category_size").val('');
               // $("#category_thumb").val('');
               // $("#app_id").val('');
               // $("#sequence_number").val('');
               // $("#status").val('');

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
           const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
           $("#categoryName").on("input", function() {
               const titleString = toTitleCase($(this).val());
               $("#categoryIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
               $(this).val(titleString);
           });

           function updateCount(input, counterId) {
    const max = 60;
    const remaining = max - input.value.length;
    document.getElementById(counterId).textContent =
        remaining + ' remaining of ' + max + ' letters';
}
       </script>




       </body>

       </html>

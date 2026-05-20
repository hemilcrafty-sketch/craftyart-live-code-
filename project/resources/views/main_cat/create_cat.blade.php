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
                                   <div class="input-group custom m-0">
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
                                   <input class="form-control" type="text" name="meta_title" maxlength="60"
                                       oninput="updateMetaCount(this)" required>
                                   <small id="metaCounter" class="text-muted">60 remaining of 60 letters</small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Primary Keyword</h6>
                                   <input type="text" class="form-control" id="primary_keyword"
                                       name="primary_keyword" placeholder="Enter Primary Keyword" required>
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H1 Tag</h6>
                                   <input class="form-control" type="text" name="h1_tag" maxlength="60"
                                       oninput="updateH1Count(this)" required>
                                   <small id="h1Counter" class="text-muted">60 remaining of 60 letters</small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H2 Tag</h6>
                                   <input class="form-control" type="textname" name="h2_tag">
                               </div>
                           </div>
                       </div>

                       <div class="row">
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
                                   <h6>Long Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="long_desc"></textarea>
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Tag Line</h6>
                                   <input class="form-control" type="textname" name="tag_line" required>
                               </div>
                           </div>
                       </div>

                       <div class="form-group">
                           <h6>Category Size</h6>
                           <input class="form-control" type="textname" name="size" required>
                       </div>

                       {{-- <div class="form-group category-dropbox-wrap">
                            <h6>Parent Category</h6>

                            <div class="input-subcategory-dropbox" id="parentCategoryInput"><span>== none ==</span> <i
                                    style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i></div>
                            <div class="custom-dropdown parent-category-input">
                                <ul class="dropdown-menu-ul">
                                    <li class="category none-option">== none ==</li>
                                    @foreach ($allCategories as $category)
                                    @php
                                    $classBold = (!empty($category['subcategories']) &&
                                    isset($category['subcategories'][0])) ? "has-children" : "has-parent";
                                    @endphp
                                    <li class="category {{$classBold}}" data-id="{{$category['id']}}"
                                        data-catname="{{$category['category_name']}}">
                                        {{ $category['category_name'] }}
                                        @if (!empty($category['subcategories']))
                                        <ul class="subcategories">
                                            @foreach ($category['subcategories'] as $subcategory)
                                            @include('partials.subcategory-optgroup', ['subcategory' =>
                                            $subcategory,'sub_category_id' => $subcategory['id'],'sub_category_name' =>
                                            $subcategory['category_name']])
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <input type="hidden" name="parent_category_id" value="0"> --}}
                       <div class="form-group">
                           <h6>Category Thumb</h6>
                           <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                               class="form-control-file form-control height-auto dynamic-file"
                               data-imgstore-id="category_thumb" data-nameset="true">
                       </div>

                       <div class="form-group">
                           <h6>Banner</h6>
                           <input type="file" data-accept=".jpeg,.jpg,.svg,.webp"
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
       $('#dynamic_form').on('submit', function(event) {
           event.preventDefault();
           const categoryThumbInpurt = document.querySelector("[name='category_thumb']");
           if (!categoryThumbInpurt.value) {
               alert("Please select a  file or enter a valid image URL.");
               return
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

           var formData = new FormData(this);

           $.ajax({
               url: 'submit_cat',
               type: 'POST',
               data: formData,
               beforeSend: function() {
                   var main_loading_screen = document.getElementById("main_loading_screen");
                   main_loading_screen.style.display = "block";
               },
               success: function(data) {
                   hideFields();
                   if (data.error) {
                       // var error_html = '';
                       // for (var count = 0; count < data.error.length; count++) {
                       //     error_html += '<p>' + data.error[count] + '</p>';
                       // }
                       // $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                       window.alert(data.error);
                   } else {
                       $('#result').html('<div class="alert alert-success">' + data.success +
                           '</div>');
                       window.location.href = "{{ route('show_cat') }}";
                   }
                   setTimeout(function() {
                       $('#result').html('');
                       jQuery('#dynamic_form').get(0).reset();
                       $("#parentCategoryInput span").html("== none ==");
                   }, 3000);
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

           $("#category_name").val('');
           $("#category_size").val('');
           $("#category_thumb").val('');
           $("#banner").val('');
           $("#app_id").val('');
           $("#sequence_number").val('');
           $("#status").val('');

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

       function updateMetaCount(input) {
           const max = 60;
           const remaining = max - input.value.length;
           document.getElementById('metaCounter').textContent =
               remaining + ' remaining of ' + max + ' letters';
       }

       function updateH1Count(input) {
           const max = 60;
           const remaining = max - input.value.length;
           document.getElementById('h1Counter').textContent =
               remaining + ' remaining of ' + max + ' letters';
       }
   </script>

   </body>

   </html>

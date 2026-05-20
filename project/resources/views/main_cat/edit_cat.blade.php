   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container seo-access-container">

       @include('partials.density_checker', [
           'title' => 'Category Page',
           'slug' => $datas['cat']->id_name,
           'type' => 1,
           'primary_keyword' => $datas['cat']->primary_keyword,
       ])
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
                                   <input class="form-control" type="textname" name="category_name" id="category_name"
                                       value="{{ $datas['cat']->category_name }}" required>
                               </div>
                           </div>

                           <div class="col-md-2">
                               <div class="form-group">
                                   <h6>ID Name</h6>
                                   <input class="form-control" type="textname" name="id_name"
                                       value="{{ $datas['cat']->id_name }}" required>
                               </div>
                           </div>
                           <div class="col-md-4 col-sm-12">
                               <div class="form-group">
                                   <h6>Canonical Link</h6>
                                   <div class="input-group custom mb-0">
                                       <input type="text" class="form-control canonical_link" name="canonical_link"
                                           value="{{ $datas['cat']->canonical_link }}" />
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
                                           <option disabled {{ empty($datas['cat']->seo_emp_id) ? 'selected' : '' }}>
                                               Select
                                           </option>
                                           @foreach ($assignSubCat as $subcat)
                                               <option value="{{ $subcat->id }}"
                                                   {{ isset($datas['cat']->seo_emp_id) && $datas['cat']->seo_emp_id == $subcat->id ? 'selected' : '' }}>
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
                                       maxlength="60" oninput="updateCount(this, 'metaCounter')"
                                       value="{{ $datas['cat']->meta_title }}" required>
                                   <small id="metaCounter" class="text-muted"></small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Primary Keyword</h6>
                                   <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                       name="primary_keyword" placeholder="Enter Primary Keyword" required
                                       value="{{ $datas['cat']->primary_keyword }}">
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H1 Tag</h6>
                                   <input class="form-control" type="text" name="h1_tag" id="h1_tag"
                                       maxlength="60" oninput="updateCount(this, 'h1Counter')"
                                       value="{{ $datas['cat']->h1_tag }}" required>
                                   <small id="h1Counter" class="text-muted"></small>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>H2 Tag</h6>
                                   <input class="form-control" type="textname" name="h2_tag"
                                       value="{{ $datas['cat']->h2_tag }}">
                               </div>
                           </div>
                       </div>

                       <div class="row">
                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Meta Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="meta_desc">{{ $datas['cat']->meta_desc }}</textarea>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Short Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="short_desc">{{ $datas['cat']->short_desc }}</textarea>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Long Desc</h6>
                                   <textarea style="height: 120px" class="form-control" name="long_desc">{{ $datas['cat']->long_desc }}</textarea>
                               </div>
                           </div>

                           <div class="col-md-6">
                               <div class="form-group">
                                   <h6>Tag Line</h6>
                                   <input class="form-control" type="textname" name="tag_line"
                                       value="{{ $datas['cat']->tag_line }}" required>
                               </div>
                           </div>
                       </div>


                       <div class="form-group">
                           <h6>Category Size</h6>
                           <input class="form-control" type="textname" name="size"
                               value="{{ $datas['cat']->size }}" required>
                       </div>

                       {{-- <div class="form-group category-dropbox-wrap">
                            <h6>Parent Category</h6>

                            <div class="input-subcategory-dropbox" id="parentCategoryInput"><span>
                                    @if (isset($datas['cat']->category_name) && $datas['cat']->category_name != '')
                                    {{ $datas['cat']->category_name }}
                                    @else
                                    {{"== none =="}}
                                    @endif
                                </span> <i style="font-size:18px" class="fa down-arrow-dropbox">&#xf107;</i>
                            </div>
                            <div class="custom-dropdown parent-category-input">

                                <ul class="dropdown-menu-ul">
                                    <li class="category none-option">== none ==</li>
                                    @foreach ($datas['allCategories'] as $category)
                                    @php
                                    $classBold = (!empty($category['subcategories']) &&
                                    isset($category['subcategories'][0])) ? "has-children" :
                                    "has-parent";
                                    $selected = (isset($datas['cat']->id) && $datas['cat']->id == $category['id']) ?
                                    "selected" : "";
                                    @endphp
                                    <li class="category {{$classBold}} {{$selected}}" data-id="{{$category['id']}}"
                                        data-catname="{{$category['category_name']}}">
                                        <span>{{ $category['category_name'] }}</span>
                                        @if (!empty($category['subcategories']))
                                        <ul class="subcategories">
                                            @foreach ($category['subcategories'] as $subcategory)
                                            @include('partials.subcategory-optgroup', ['subcategory' =>
                                            $subcategory,'sub_category_id' =>
                                            $subcategory['id'],'sub_category_name' => $subcategory['category_name']])
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div> --}}
                       {{-- <input type="hidden" name="parent_category_id"
                            value="{{isset($datas['cat']->parent_category_id) ? $datas['cat']->parent_category_id : '0'}}">
                        --}}

                       <div class="form-group">
                           <h6>Category Thumb</h6>
                           <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                               class="form-control-file form-control height-auto dynamic-file"
                               data-imgstore-id="category_thumb"
                               data-value="{{ $contentManager::getStorageLink($datas['cat']->category_thumb) }}"
                               data-nameset="true"><br />
                           <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->category_thumb }}"
                                width="100" />
                            <input class="form-control" type="textname" id="cat_thumb_path" name="cat_thumb_path"
                                value="{{ $datas['cat']->category_thumb }}" style="display: none"> -->
                       </div>

                       <div class="form-group">
                           <h6>Banner</h6>
                           <input type="file" data-accept=".jpeg,.jpg,.svg,.webp"
                               class="form-control-file form-control height-auto dynamic-file" data-required="false"
                               data-value="{{ $contentManager::getStorageLink($datas['cat']->banner) }}"
                               data-imgstore-id="banner" data-nameset="true"><br />
                           <!-- <img src="{{ config('filesystems.storage_url') }}{{ $datas['cat']->banner }}" width="100" />
                            <input class="form-control" type="textname" id="banner_path" name="banner_path"
                                value="{{ $datas['cat']->banner }}" style="display: none"> -->
                       </div>


                       <div class="form-group">
                           <h6>Application</h6>
                           <div class="col-sm-20">
                               <select class="selectpicker form-control" data-style="btn-outline-primary"
                                   name="app_id">
                                   @foreach ($datas['app'] as $app)
                                       @if ($datas['cat']->app_id == $app->id)
                                           <option value="{{ $app->id }}"
                                               selected="{{ $datas['cat']->app_id }}">
                                               {{ $app->app_name }}
                                           </option>
                                       @else
                                           <option value="{{ $app->id }}">{{ $app->app_name }}</option>
                                       @endif
                                   @endforeach
                               </select>
                           </div>
                       </div>

                       @include('partials.content_section', [
                           'contents' => $datas['cat']->contents ?? old('contents'),
                           'ctaSection' => [],
                       ])

                       <div style="margin-bottom: 10px;">
                           @include('partials.faqs_section', ['faqs' => $datas['cat']->faqs ?? ''])
                       </div>
                       @include('partials.top_template_categories', [
                           'top_keywords' => $datas['cat']->top_keywords,
                       ])

                       <div class="form-group">
                           <h6>Sequence Number</h6>
                           <input class="form-control" type="textname" name="sequence_number"
                               value="{{ $datas['cat']->sequence_number }}" required>
                       </div>
                       <div class="form-group">
                           <h6>Status</h6>
                           <div class="col-sm-20">
                               <select class="selectpicker form-control status" data-style="btn-outline-primary"
                                   name="status">
                                   @if ($datas['cat']->status == '1')
                                       <option value="1" selected>LIVE</option>
                                       <option value="0">NOT LIVE</option>
                                   @else
                                       <option value="1">LIVE</option>
                                       <option value="0" selected>NOT LIVE</option>
                                   @endif
                               </select>
                           </div>
                       </div>
                       <div>
                           <input class="btn btn-primary submit-btn" type="submit" name="submit">
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

           var url = "{{ route('cat.update', [$datas['cat']->id]) }}";

           $.ajax({
               url: url,
               type: 'POST',
               data: formData,
               beforeSend: function() {
                   var main_loading_screen = document.getElementById("main_loading_screen");
                   main_loading_screen.style.display = "block";
               },
               success: function(data) {
                   hideFields();
                   if (data.error) {
                       window.alert(data.error);
                   } else {
                       window.location.replace("../show_cat");
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
           main_loading_screen.style.display = "block";
       }


       const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
       $("#category_name").on("input", function() {
           const titleString = toTitleCase($(this).val());
           $("#categoryIDName").val(`${titleString.toLowerCase().replace(/\s+/g, '-')}`);
           $(this).val(titleString);
       });

       // $(document).on('click','#parentCategoryInput',function(){
       //     if( $('.parent-category-input').hasClass('show') ){
       //         $('.parent-category-input').removeClass('show');
       //     }else{
       //         $(".parent-category-input").addClass('show');
       //     }
       // });

       // $(document).on("click", ".category", function(event) {
       //     $(".category").removeClass("selected");
       //     $(".subcategory").removeClass("selected");
       //     var id = $(this).data('id');
       //     $("input[name='parent_category_id']").val(id);
       //     $("#parentCategoryInput span").html($(this).data('catname'));
       //     $('.parent-category-input').removeClass('show');
       //     $(this).addClass("selected");
       // });

       // $(document).on("click", ".subcategory", function(event) {
       //     event.stopPropagation();
       //     $(".category").removeClass("selected");
       //     $(".subcategory").removeClass("selected");
       //     var id = $(this).data('id');
       //     var parentId = $(this).data('pid');
       //     $("input[name='parent_category_id']").val(id);
       //     $('.parent-category-input').removeClass('show');
       //     $("#parentCategoryInput span").html($(this).data('catname'));
       //     $(this).addClass("selected");
       // });

       // $(document).on("click","li.category.none-option",function(){
       //     $("input[name='parent_category_id']").val("0");
       //     $('.parent-category-input').removeClass('show');
       //     $("#parentCategoryInput span").html('== none ==');
       // });

       // $(document).on('click', function(e) {
       //     if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
       //         $('.custom-dropdown.parent-category-input.show').removeClass('show');
       //     }
       // });

       function updateCount(input, counterId) {
           const max = 60;
           const remaining = max - input.value.length;
           document.getElementById(counterId).textContent =
               remaining + ' remaining of ' + max + ' letters';
       }

       // Run once on page load to set initial counts for prefilled values
       document.addEventListener("DOMContentLoaded", function() {
           updateCount(document.getElementById('h1_tag'), 'h1Counter');
           updateCount(document.getElementById('meta_title'), 'metaCounter');
       });
   </script>
   </body>

   </html>

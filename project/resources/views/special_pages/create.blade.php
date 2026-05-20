   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
   @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
   @inject('helperController', 'App\Http\Controllers\HelperController')
   @include('layouts.masterhead')
   <div class="main-container seo-access-container">
       <div class="px-4">
           @if ($errors->any())
               @php
                   $get_errors = $errors->toarray();
               @endphp
               @if (
                   !isset($get_errors['page_slug']) &&
                       !isset($get_errors['meta_title']) &&
                       !isset($get_errors['title']) &&
                       !isset($get_errors['breadcrumb']) &&
                       !isset($get_errors['pre_breadcrumb']) &&
                       !isset($get_errors['button']) &&
                       !isset($get_errors['button_link']) &&
                       !isset($get_errors['meta_desc']) &&
                       !isset($get_errors['description']) &&
                       !isset($get_errors['colors']))
                   <div class="alert alert-danger alert-dismissible mt-4">
                       <ul>
                           @foreach ($errors->all() as $error)
                               @if (
                                   !isset($get_errors['page_slug']) &&
                                       !isset($get_errors['meta_title']) &&
                                       !isset($get_errors['title']) &&
                                       !isset($get_errors['breadcrumb']) &&
                                       !isset($get_errors['button']) &&
                                       !isset($get_errors['button_link']) &&
                                       !isset($get_errors['meta_desc']) &&
                                       !isset($get_errors['description']) &&
                                       !isset($get_errors['colors']))
                                   <li>{{ $error }}</li>
                               @endif
                           @endforeach
                       </ul>
                       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                   </div>
               @endif
           @endif

           @if (session('success'))
               <div class="alert alert-success alert-dismissible mt-4" role="alert">
                   <strong>Success!</strong> {{ session('success') }}
                   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>
           @endif

           <div id="main_loading_screen" style="display: none;">
               <div id="loader-wrapper">
                   <div id="loader"></div>
                   <div class="loader-section section-left"></div>
                   <div class="loader-section section-right"></div>
               </div>
           </div>

           @include('partials.density_checker', [
               'title' => 'Special Page',
               'slug' => $page->page_slug ?? '',
               'type' => 2,
               'primary_keyword' => $page->primary_keyword ?? '',
           ])

           <div class="card w-100">
               <div class="card-body">

                   <form method="post" id="add-pages-form" enctype="multipart/form-data">
                       @csrf
                       <input type="hidden" name="id" id="id" class="special-page-id"
                           value="{{ $id ?: '' }}">
                       <div>
                           <div class="row mb-3">
                               <div class="col-md-6">
                                   <label for="page_slug" class="form-label">Page Slug</label>
                                   <input type="text" class="form-control" name="page_slug" id="page_slug"
                                       placeholder="Enter page slug" maxlength="75"
                                       oninput="$('.pageSlugletter').text(75 - this.value.length)"
                                       value="{{ $page->page_slug ?? old('page_slug') }}">
                                   @php
                                       $page_slug = $page->page_slug ?? old('page_slug');
                                       $slug_count = 75 - strlen($page_slug);
                                   @endphp
                                   <p class="text-end"><span class="pageSlugletter"><?= $slug_count ?></span>
                                       remaining of 75 letters
                                   </p>
                                   @error('page_slug')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>

                               <div class="col-md-6">
                                   <label for="canonical_link" class="form-label canonical_link">Canonical Link</label>
                                   <input type="text" name="canonical_link" class="form-control canonical_link"
                                       id="canonical_link" placeholder="Enter Canonical Link"
                                       value="{{ $page->canonical_link ?? old('canonical_link') }}">
                                   <p class="text-end" style="font-size: 12px;"></p>
                                   @php
                                       $canonical_link = $page->canonical_link ?? old('canonical_link');
                                       $slug_count = 75 - strlen($canonical_link);
                                   @endphp
                                   @error('canonical_link')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                           </div>

                           <div class="row mb-3">
                               <div class="col-md-6">
                                   <label for="meta_title" class="form-label">Meta Title</label>
                                   <input type="text" class="form-control" id="meta_title" maxlength="60"
                                       name="meta_title" placeholder="Enter meta title"
                                       oninput="$('.metatitleletter').text(60 - this.value.length)"
                                       value="{{ $page->meta_title ?? old('meta_title') }}">
                                   @php
                                       $meta_title = $page->meta_title ?? old('page_slug');
                                       $meta_title_count = 60 - strlen($meta_title);
                                   @endphp
                                   <p class="text-end"><span class="metatitleletter"><?= $meta_title_count ?></span>
                                       remaining of 60
                                       letters</p>
                                   @error('meta_title')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               <div class="col-md-6">
                                   <label for="title" class="form-label">Title (H1)</label>
                                   <input type="text" class="form-control" id="title" maxlength="60"
                                       name="title" placeholder="Enter title"
                                       oninput="$('.titleletter').text(60 - this.value.length)"
                                       value="{{ $page->title ?? old('title') }}">
                                   @php
                                       $title = $page->title ?? old('page_slug');
                                       $title_count = 60 - strlen($title);
                                   @endphp
                                   <p class="text-end"><span class="titleletter"><?= $title_count ?></span> remaining
                                       of 60 letters</p>
                                   @error('title')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>

                               <div class="col-md-6">
                                   <label for="primary_keyword" class="form-label">Primary Keyword</label>
                                   <input type="text" class="form-control" id="primary_keyword" maxlength="60"
                                       name="primary_keyword" placeholder="Enter Primary Keyword" required
                                       value="{{ $page->primary_keyword ?? old('primary_keyword') }}">
                               </div>
                           </div>

                           <div class="row mx-1 mb-3 rounded-3" style="border: dashed; padding: 10px;">
                               <label for="pre_breadcrumb" class="form-label">Pre Breadcrumb</label>
                               <hr />
                               <div class="col-md-3">
                                   <label for="name" class="form-label">Name</label>
                                   <input type="text" class="form-control" name="pre_breadcrumb_name"
                                       placeholder="Enter Name" value="{{ $page->pre_breadcrumb->value ?? '' }}"
                                       required>
                                   @error('pre_breadcrumb')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               <div class="col-md-3">
                                   <label for="button_link" class="form-label">Link</label>
                                   <input type="url" class="form-control" name="pre_breadcrumb_link"
                                       placeholder="Enter Link" value="{{ $page->pre_breadcrumb->link ?? '' }}"
                                       required>
                                   @error('pre_breadcrumb')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>

                               <div class="col-md-3">
                                   <div class="form-check mt-4 mb-2">
                                       <input type="checkbox" name="pre_breadcrumb_target" class="form-check-input"
                                           value="1"
                                           {{ isset($page->pre_breadcrumb) && $page->pre_breadcrumb->openinnewtab == 1 ? 'checked' : '' }}>
                                       <label class="form-check-label" for="button_target">Open in new tab</label>
                                   </div>
                                   <div class="form-check">
                                       <input type="checkbox" name="pre_breadcrumb_rel" class="form-check-input"
                                           value="1"
                                           {{ isset($page->pre_breadcrumb) && $page->pre_breadcrumb->nofollow == 1 ? 'checked' : '' }}>
                                       <label class="form-check-label" for="button_rel">Add rel="nofollow"</label>
                                   </div>
                               </div>

                               <div class="col-md-3">
                                   <label for="breadcrumb" class="form-label">Breadcrumb</label>
                                   <input type="text" class="form-control" id="breadcrumb" name="breadcrumb"
                                       placeholder="Enter Breadcrumb"
                                       value="{{ $page->breadcrumb ?? old('breadcrumb') }}">
                                   @error('breadcrumb')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                           </div>


                           <div class="row mb-3">
                               <div class="col-md-6">
                                   <label for="banner" class="form-label">Banner <span class="text-danger"> (Size:
                                           500*400)</span></label>
                                   <input type="file" class="form-control dynamic-file" data-imgstore-id="banner"
                                       data-required=false
                                       data-value="{{ $id && $page->banner ? $contentManager::getStorageLink($page->banner) : '' }}"
                                       data-nameset=true data-accept=".jpg, .jpeg, .webp, .svg"
                                       data-callback="previewFile">
                                   <span class="text-danger banner-image-error"></span>
                                   @error('banner')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                                   <?php
                                   $img_type = '';
                                   if ($id && $page->banner_type == 'image') {
                                       $img_type = 'image';
                                   } elseif ($id && $page->banner_type == 'video') {
                                       $img_type = 'video';
                                   }
                                   ?>
                                   <input type="hidden" class="form-control" id="banner_type" name="banner_type"
                                       value="{{ $img_type }}">

                                   <div id="banner-image-video-preview-div"
                                       class="mb-3 mt-3 {{ $id && $page->banner && $file != '' ? 'd-flex' : 'd-none' }}">
                                       <img class="banner-image-preview @if ($id && $page->banner_type == 'image') d-flex @else d-none @endif "
                                           id="banner-image-preview">
                                       <video src="{{ $id && $page->banner_type == 'video' ? $file : '' }}" controls
                                           class="banner-video-preview  @if ($id && $page->banner_type == 'video') d-flex @else d-none @endif"
                                           id="banner-video-preview" alt="Video Preview" width="320"
                                           height="240">
                                           Your browser does not support the video tag.
                                       </video>
                                       <button type="button" class="btn btn-danger removeBannerBtn"
                                           style="display:{{ $id && ($page->banner_type == 'video' || $page->banner_type == 'image') ? 'block' : 'none' }}">delete</button>
                                   </div>
                               </div>
                           </div>

                           @php
                               if (isset($page) && $page != null) {
                                   $page->hero_bg_option = isset($page->hero_bg_option)
                                       ? $page->hero_bg_option
                                       : 'color';
                               }

                           @endphp
                           <div class="row mb-3">
                               <div class="col-md-6">
                                   <label for="heroBgOption" class="form-label">Background Option</label>
                                   <select name="hero_bg_option" id="heroBgOption" class="form-control">
                                       <option value="color"
                                           {{ isset($page) && $page->hero_bg_option == 'color' ? 'selected' : '' }}>
                                           Color</option>
                                       <option value="image"
                                           {{ isset($page) && $page->hero_bg_option == 'image' ? 'selected' : '' }}>
                                           Image</option>
                                   </select>
                               </div>
                               <div id="heroParentDiv"
                                   class="{{ isset($page) && $page->hero_bg_option == 'image' ? 'col-md-3' : 'col-md-6' }}">
                                   <div class="hero-bg-color" id="heroBGColor"
                                       style="display: {{ isset($page) && $page->hero_bg_option == 'image' ? 'none' : 'block' }}">
                                       <label for="colors" class="form-label">Background Color</label>
                                       <input type="text" id="colors" name="colors"
                                           value="{{ isset($page) && $page->colors ? $page->colors : '#008080' }}">
                                       @error('colors')
                                           <span class="text-danger">{{ $message }}</span>
                                       @enderror
                                   </div>
                                   <div class="hero-bg-image" id="heroBGImage"
                                       style="display: {{ isset($page) && $page->hero_bg_option == 'image' ? 'block' : 'none' }}">
                                       <label for="bgColor" class="form-label">Background Image</label>
                                       <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                           class="form-control dynamic-file" data-callback="previewImage"
                                           id="heroBGImageFile" data-imgstore-id="hero_background_image"
                                           data-value="{{ $id && $page->hero_background_image ? $contentManager::getStorageLink($page->hero_background_image) : '' }}"
                                           data-nameset="hero_background_image" />
                                       <input type="hidden" name="hero_bg_image"
                                           value="{{ isset($page) && $page->hero_background_image ? $page->hero_background_image : '' }}">
                                       @error('hero_background_image')
                                           <span class="text-danger">{{ $message }}</span>
                                       @enderror
                                   </div>
                               </div>
                               @if (@$page->hero_bg_option == 'image')
                                   <div class="col-md-3 col-hero-img" id="heroBgDiv">
                                       <img src="{{ config('filesystems.storage_url') }}{{ $heroBgImageFile }}"
                                           class="preveiew-image" id="heroBgImg">
                                       <a class="btn-remove-pv-img" id="heroBgImgRemove"
                                           style="display: {{ $bodyBgImageFile == '' ? 'none' : 'block' }}"
                                           onclick="removeImgs('{{ $id }}', 'heroBgDiv', 'hero_background_image')">Remove</a>
                                   </div>
                               @endif
                           </div>
                           <div class="row mb-3">
                               <div class="col-md-3">
                                   <label for="button" class="form-label">Button</label>
                                   <input type="text" class="form-control" id="button" name="button"
                                       placeholder="Enter Button Name" value="{{ $page->button ?? old('button') }}">
                                   @error('button')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               <div class="col-md-3">
                                   <label for="button_link" class="form-label">Button Link</label>
                                   <input type="url" class="form-control" id="button_link" list="sizeList"
                                       autocomplete="on" name="button_link" placeholder="Enter Button Link"
                                       value="{{ $page->button_link ?? old('button_link') }}">
                                   <p id="suggestionText" style="display:none;"></p>
                                   @error('button_link')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               <datalist id="sizeList">
                                   @foreach ($sizes as $size)
                                       <option
                                           value="https://editor.craftyartapp.com/{{ base64_encode(json_encode(['w' => $size->width_ration, 'h' => $size->height_ration, 'tag' => 'wdefrghsdfg'])) }}"
                                           label="{{ $size->width_ration }} * {{ $size->height_ration }}">
                                       </option>
                                   @endforeach
                               </datalist>
                               <div class="col-md-2">
                                   <div class="form-check mt-4 mb-2">
                                       <input type="checkbox" name="button_target" class="form-check-input"
                                           id="button_target" value="1"
                                           {{ isset($page->button_target) && $page->button_target == 1 ? 'checked' : '' }}>
                                       <label class="form-check-label" for="button_target">Open in new tab</label>
                                   </div>
                                   <div class="form-check">
                                       <input type="checkbox" name="button_rel" class="form-check-input"
                                           id="button_rel" value="1"
                                           {{ isset($page->button_rel) && $page->button_rel == 1 ? 'checked' : '' }}>
                                       <label class="form-check-label" for="button_rel">Add rel="nofollow"</label>
                                   </div>
                               </div>
                               <div class="col-md-2">
                                   <label for="button_link" class="form-label">Select Page Submision</label>
                                   <select id="dropdown-page-submission status" class="status" name="status">
                                       @php
                                       $selected = isset($page->status) && $page->status == 1 ? 'selected' : ''; @endphp
                                       <option value="1" {{ $selected }}>Publish
                                       </option>
                                       @php
                                           $selected = isset($page->status) && $page->status == 0 ? 'selected' : '';
                                       @endphp
                                       <option value="0" {{ $selected }}>Draft
                                       </option>
                                   </select>
                                   @error('status')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               @if($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                               <div class="col-md-2 seo-all-container">
                                   <label for="priority" class="form-label">Priority</label>
                                   <input type="number" class="form-control seo-all-container" id="priority" name="priority"
                                       placeholder="0.90" step="0.01" min="0" max="1"
                                       value="{{ $page->priority ?? 0.90 }}">
                                   <small class="text-muted">Value between 0.00 and 1.00</small>
                                   @error('priority')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               @endif
                           </div>

                           @if($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
                           <div class="row mb-3 seo-all-container">
                               <div class="col-md-3 seo-all-container">
                                   <label for="frequency" class="form-label">Frequency</label>
                                   <select id="frequency" class="form-control seo-all-container" name="frequency">
                                       @php
                                           $frequency = $page->frequency ?? 'daily';
                                       @endphp
                                       <option value="daily" {{ $frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                       <option value="weekly" {{ $frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                       <option value="monthly" {{ $frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                   </select>
                                   @error('frequency')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                           </div>
                           @endif


                           <div class="row">
                               <div class="col-md-6">
                                   <label for="meta_desc" class="form-label">Meta Description</label>
                                   <textarea name="meta_desc" id="meta_desc" class="form-control" maxlength="160"
                                       oninput="$('.metaDescChar').text(160 - this.value.length)" rows="5">{{ $page->meta_desc ?? old('meta_desc') }}</textarea>
                                   @php
                                       $meta_desc = $page->meta_desc ?? old('meta_desc');
                                       $meta_desc_count = 160 - strlen($meta_desc);
                                   @endphp
                                   <p class="text-end"><span class="metaDescChar"><?= $meta_desc_count ?></span>
                                       remaining of 160 letters
                                   </p>
                                   @error('meta_desc')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                               <div class="col-md-6">
                                   <label for="description" class="form-label">Description</label>
                                   <textarea name="description" id="description" class="form-control" maxlength="350"
                                       oninput="$('.DescChar').text(350 - this.value.length)" rows="5">{{ $page->description ?? old('description') }}</textarea>
                                   @php
                                       $description = $page->description ?? old('description');
                                       $desc_count = 350 - strlen($description);
                                   @endphp
                                   <p class="text-end"><span class="DescChar"><?= $desc_count ?></span> remaining of
                                       350 letters</p>
                                   @error('description')
                                       <span class="text-danger">{{ $message }}</span>
                                   @enderror
                               </div>
                           </div>

                           @php
                               $isRestricted = !$roleManager::isAdminOrSeoManager(Auth::user()->user_type);
                           @endphp

                           <div class="row">
                               <div class="form-group category-dropbox-wrap">
                                   <h6>Category</h6>

                                   {{-- Main dropdown --}}
                                   <div class="input-subcategory-dropbox {{ $isRestricted ? 'disabled-category' : '' }}"
                                       id="parentCategoryInput"
                                       @if ($isRestricted) style="pointer-events: none; opacity: 0.6;" @endif>
                                       <span>
                                           @if (isset($category->category_name) && $category->category_name != '')
                                               {{ $category->category_name }}
                                           @else
                                               {{ '== none ==' }}
                                           @endif
                                       </span>
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
                                                       !empty($category['subcategories']) &&
                                                       isset($category['subcategories'][0])
                                                           ? 'has-children'
                                                           : 'has-parent';
                                                   $selected =
                                                       isset($page->cat_id) && $page->cat_id == $category['id']
                                                           ? 'selected'
                                                           : '';
                                               @endphp
                                               <li class="category {{ $classBold }} {{ $selected }}"
                                                   data-id="{{ $category['id'] }}"
                                                   data-catname="{{ $category['category_name'] }}">
                                                   <span>{{ $category['category_name'] }}</span>
                                                   @if (!empty($category['subcategories']))
                                                       <ul class="subcategories">
                                                           @foreach ($category['subcategories'] as $subcategory)
                                                               @include('partials.subcategory-optgroup', [
                                                                   'subcategory' => $subcategory,
                                                                   'sub_category_id' => $subcategory['id'],
                                                                   'sub_category_name' =>
                                                                       $subcategory['category_name'],
                                                               ])
                                                           @endforeach
                                                       </ul>
                                                   @endif
                                               </li>
                                           @endforeach
                                       </ul>
                                   </div>
                               </div>

                               {{-- Hidden field --}}
                               <input type="hidden" name="cat_id" class="new_cat_id_item"
                                   value="{{ isset($page->cat_id) ? $page->cat_id : '0' }}"
                                   {{ $isRestricted ? 'readonly' : '' }}>

                               {{-- Optional notice --}}
                               @if ($isRestricted)
                                   <small class="text-danger">
                                       You are not allowed to change the category (Only Admin, SEO Manager can).
                                   </small>
                               @endif
                           </div>



                       </div>
                       <br />
                       <div class="body-section">
                           <h2>Body Section</h2>
                           <hr> <br />
                           <div class="row mb-3">
                               <div class="col-md-4">
                                   <label for="bodyBGImage" class="form-label">Background Images</label>
                                   <input type="file" data-accept=".jpg, .jpeg, .webp, .svg"
                                       class="form-control dynamic-file" data-imgstore-id="body_background_image"
                                       data-value="{{ $id && $page->body_background_image ? $contentManager::getStorageLink($page->body_background_image) : '' }}"
                                       data-nameset=true />
                                   <input type="hidden" name="body_bg_image"
                                       value="{{ @$page->body_background_image }}">
                               </div>
                               <!-- <div class="col-md-8" id="bodyBgDiv" style="display: {{ $bodyBgImageFile == '' ? 'none' : 'block' }}">
                  <img src="{{ $bodyBgImageFile }}" class="preveiew-image" id="bodyBgImg">
                  <a class="btn-remove-pv-img" style="display: {{ $bodyBgImageFile == '' ? 'none' : 'block' }}"
                    id="bodyImgRemove" onclick="removeImgs('{{ $id }}', 'bodyBgDiv', 'body_background_image')">Remove</a>
                </div> -->
                           </div>
                       </div>
                       <hr class="border border-4 rounded-3">
                       <!-- <div class="content-div">
              <input type="hidden" name="contents" id="content" value="{{ $page->contents ?? old('contents') }}">
              <div class="content_type col-md-12"></div>
            </div>

            <button type="button" class="btn btn-dark mb-3 w-100" onclick="openContentDialog()">Add Content</button> -->
                       @include('partials.content_section', [
                           'contents' => $page->contents ?? old('contents'),
                           'ctaSection' => $page->cta ?? [],
                       ])

                       <div style="margin-bottom: 10px;">
                           @include('partials.faqs_section', ['faqs' => $page->faqs ?? ''])
                       </div>
                       <hr class="border border-4 rounded-3">
                       <div style="margin-bottom: 10px;">
                           @include('partials.top_template_categories', [
                               'top_keywords' => $page->top_keywords ?? [],
                           ])
                       </div>
                       <div class="footer-button-sticky">
                           <button type="submit"
                               class="btn btn-success save-page-data w-100 submit-btn">Save</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       <!-- <div class="modal fade" id="resumeGuideModel" tabindex="-1" aria-labelledby="resumeGuideModel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title text-capitalize content-title">How To Make</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <label for="question" class="form-label">Title</label>
                <div>
                  <input type="text" name="title_resume_guide" class="form-control" id="titleResumeGuide" required>
                </div>
                <span class="title_resume_guide_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="descriptionResumeGuide" class="form-label">Description</label>
                <div>
                  <textarea class="form-control" name="description_resume_guide" id="descriptionResumeGuide" required></textarea>
                </div>
                <span class="description_resume_guide_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="answer" class="form-label">Image</label>
                <div>
                  <input class="form-control" type="file" id="imageFileGuideResume" required>
                </div>
                <span class="description_resume_guide_error text-danger"></span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-dark btn-resume-save">Save</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="resumeContentModel" tabindex="-1" aria-labelledby="resumeContentModel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title text-capitalize content-title">Category Content</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <label for="titleResumeContent" class="form-label">Title</label>
                <div>
                  <input type="text" name="title_resume_content" class="form-control" id="titleResumeContent" required>
                </div>
                <span class="title_resume_content_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="descriptionResumeContent" class="form-label">Description</label>
                <div>
                  <div id="desc_resume_content_edtr" class="form-control"> </div>
                  <input type="hidden" id="hiddenDescriptionContent">
                </div>
                <span class="description_resume_content_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="imageFileContentResume" class="form-label">Image</label>
                <div>
                  <input class="form-control" type="file" id="imageFileContentResume" required>
                </div>
                <span class="image_file_content_resume_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="buttonResumeContent" class="form-label">Button</label>
                <div>
                  <input type="text" name="button_resume_content" class="form-control" id="buttonResumeContent"
                    required>
                </div>
                <span class="button_resume_content_error text-danger"></span>
              </div>
              <div class="col-md-12 my-2">
                <label for="buttonLinkResumeContent" class="form-label">Button Link</label>
                <div>
                  <input type="url" name="button_link_resume_content" class="form-control" id="buttonLinkResumeContent"
                    required>
                </div>
                <span class="button_link_resume_content_error text-danger"></span>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="resumeContentOpenInNewTab">
                <label class="form-check-label" for="resumeContentOpenInNewTab">Open in new tab</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-dark btn-resume-content-save">Save</button>
          </div>
        </div>
      </div>
    </div> -->


   </div>
   @include('layouts.masterscript')

   <script>
       let button_link = document.querySelector("#button_link");
       let suggestionText = document.querySelector("#suggestionText");
       let heroBgImg = document.querySelector("#heroBgImg");
       var url = "{{ route('add.update.form') }}";

       function previewImage(input) {
           let heroBgImg = document.querySelector("#heroBgImg");

           if (input.files && input.files[0]) {
               let reader = new FileReader();

               reader.onload = function(e) {
                   heroBgImg.src = e.target.result;
                   heroBgImg.style.display = "block"; // Show the image
               };

               reader.readAsDataURL(input.files[0]); // Convert image to base64 URL
           }
       }

       document.addEventListener("DOMContentLoaded", function() {
           var bgOption = document.getElementById("heroBgOption");
           var bgColorDiv = document.getElementById("heroBGColor");
           var bgImageDiv = document.getElementById("heroBGImage");
           var heroParentDiv = document.getElementById("heroParentDiv");
           var bgDiv = document.getElementById("heroBgDiv");

           function toggleBackgroundOption() {
               if (bgOption.value === "image") {
                   bgColorDiv.style.display = "none";
                   bgImageDiv.style.display = "block";
                   bgDiv.style.display = "block";
                   heroParentDiv.classList.remove("col-md-6")
                   heroParentDiv.classList.add("col-md-3")
               } else {
                   bgColorDiv.style.display = "block";
                   bgImageDiv.style.display = "none";
                   bgDiv.style.display = "none";
                   heroParentDiv.classList.add("col-md-6")
                   heroParentDiv.classList.remove("col-md-3")
               }
           }

           // Attach event listener to dropdown
           bgOption.addEventListener("change", toggleBackgroundOption);

           // Initialize on page load
           toggleBackgroundOption();
       });

       button_link.addEventListener('change', (event) => {
           const value = event.target.value;
           const prefix = "https://editor.craftyartapp.com/";
           if (value.startsWith(prefix)) {
               const base64Part = value.substring(prefix.length);
               const jsonString = atob(base64Part);
               const jsonObject = JSON.parse(jsonString);
               suggestionText.style.display = "block"
               suggestionText.textContent = `${jsonObject.w} * ${jsonObject.h}`
           } else {
               suggestionText.style.display = "none   "
           }
       });

       $('#add-pages-form').on('submit', function(event) {
           event.preventDefault();

           const parentDiv = document.querySelector('#sortable');
           const isSeoManager = {{ $roleManager::isAdminOrSeoManager(Auth::user()->user_type) ? 'true' : 'false' }};
           
           if (parentDiv.children.length == 0 && !isSeoManager) {
               window.alert('add top keywords');
               return;
           }

           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
               }
           });

           var formData = new FormData(this);
           const formObject = {};
           for (let [key, value] of formData.entries()) {}
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
                       $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                   } else {
                       window.location.reload();
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

       function removeImgs(id, elementId, type) {

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
           $("input[name='cat_id']").val(id);
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
           $("input[name='cat_id']").val(id);
           $('.parent-category-input').removeClass('show');
           $("#parentCategoryInput span").html($(this).data('catname'));
           $(this).addClass("selected");
       });

       $(document).on("click", "li.category.none-option", function() {
           $("input[name='cat_id']").val("0");
           $('.parent-category-input').removeClass('show');
           $("#parentCategoryInput span").html('== none ==');
       });

       $(document).on('click', function(e) {
           if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
               $('.custom-dropdown.parent-category-input.show').removeClass('show');
           }
       });
   </script>


   </body>


   </html>

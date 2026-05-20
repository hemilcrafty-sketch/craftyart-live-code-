<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\User;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoSlugHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoCatController extends AppBaseController
{

    public function create()
    {
        $allCategories = VideoCategory::getAllCategoriesWithSubcategories();
        $appArray = \App\Models\AppCategory::all();
        $userRole = User::where('user_type', 5)->get();
        return view('videos/create_cat', compact('allCategories', 'appArray', 'userRole'));
    }

    public function store(Request $request)
    {
        // Check access for creating new category
        $accessCheck = $this->isAccessByRole("seo_all");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $slugError = VideoSlugHistory::checkSlugValidation($request->input('slug'));
        if ($slugError) {
            return response()->json([
                'error' => $slugError,
            ]);
        }

        // Validate character limits for meta_desc and short_desc
        $request->validate([
            'meta_desc' => 'nullable|string|max:160',
            'short_desc' => 'nullable|string|max:350',
        ]);

        $res = new VideoCategory;

        // Basic fields
        $res->category_name = $request->input('category_name');

        // Validate parent category
        $parentCategoryId = $request->input('parent_category_id', 0);

        if ($parentCategoryId && $parentCategoryId != 0) {
            $parentCat = VideoCategory::find($parentCategoryId);

            if ($parentCat && $parentCat->parent_category_id != 0) {
                return response()->json([
                    'error' => 'Use Parent Category.'
                ]);
            }
        }

        $res->slug = $request->input('slug');
        $res->canonical_link = $request->input('canonical_link');

        // Validate canonical link for video categories
        if (!empty($res->canonical_link)) {
            $canonicalError = $this->validateVideoCategoryCanonicalLink($res->canonical_link, $res->slug);
            if ($canonicalError) {
                return response()->json([
                    'error' => $canonicalError
                ]);
            }
        }

        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->category_title = $request->input('category_title');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        if ($request->hasFile('category_thumb')) {
            // Traditional file upload
            $image = $request->file('category_thumb');
            $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } elseif ($request->has('category_thumb') && $request->input('category_thumb')) {
            // Base64 image from dynamic file input
            $base64Image = $request->input('category_thumb');
            if (strpos($base64Image, 'data:image') === 0) {
                // It's a base64 image, save it
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatThumb/' . $new_name, $image_base64);
                $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                // It's a URL, store it as is
                $res->category_thumb = $base64Image;
            }
        }

        // Handle mockup
        if ($request->hasFile('mockup')) {
            $mockup = $request->file('mockup');
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        } elseif ($request->has('mockup') && $request->input('mockup')) {
            $base64Image = $request->input('mockup');
            if (strpos($base64Image, 'data:image') === 0) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatMockup/' . $new_name, $image_base64);
                $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->mockup = $base64Image;
            }
        }

        // Handle banner
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        } elseif ($request->has('banner') && $request->input('banner')) {
            $base64Image = $request->input('banner');
            if (strpos($base64Image, 'data:image') === 0) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatBanner/' . $new_name, $image_base64);
                $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->banner = $base64Image;
            }
        }

        // Generate folder string for file storage
        $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
        while (VideoCategory::where('fldr_str', $fldrStr)->exists()) {
            $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
        }
        $res->fldr_str = $fldrStr;

        // Handle contents - store as JSON file
        if ($request->input('contents')) {
            $contents = \App\Http\Controllers\Utils\ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        // Handle faqs - store as JSON file
        if (isset($request->faqs)) {
            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title ?? '';
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

        // Handle top_keywords - keep as JSON in database
        if ($request->has('top_keywords')) {
            $topKeywords = $request->input('top_keywords');
            $res->top_keywords = is_string($topKeywords) ? json_decode($topKeywords, true) : $topKeywords;
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $this->applyVideoSitemapFieldsFromRequest($request, $res, true);
        $res->save();

        return response()->json([
            'success' => 'Category Added successfully.'
        ]);
    }

    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'no_index', 'value' => 'No Index'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $noIndexStats = $this->noIndexListingStats(VideoCategory::class);

        $query = VideoCategory::query();
        $this->applyNoIndexFilter($request, $query);

        // Handle search query
        $searchQuery = $request->input('query');
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('id', 'like', '%' . $searchQuery . '%')
                    ->orWhere('category_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('sequence_number', 'like', '%' . $searchQuery . '%');
            });
        }

        // Handle filter by specific field
        $filterBy = $request->input('filter_by');
        $filterValue = $request->input('filter_value');
        if ($filterBy && $filterValue !== null && $filterValue !== '') {
            $query->where($filterBy, 'like', '%' . $filterValue . '%');
        }

        // Handle sorting (whitelist columns)
        $allowedSort = array_column($searchableFields, 'id');
        $sortBy = $request->input('sort_by', 'id');
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'id';
        }
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Handle pagination
        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $catArray = $query->get();
            // Create a mock paginator for 'all' option
            $catArray = new \Illuminate\Pagination\LengthAwarePaginator(
                $catArray,
                $catArray->count(),
                $catArray->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $catArray = $query->paginate($perPage)->appends($request->query());
        }

        return view('videos.show_cat', compact('catArray', 'searchableFields', 'noIndexStats'));
    }


    public function edit($id)
    {
        $cat = VideoCategory::findOrFail($id);

        // Load contents and faqs from JSON files
        $cat->contents = isset($cat->contents) ? StorageUtils::get($cat->contents) : "";
        $cat->faqs = isset($cat->faqs) ? StorageUtils::get($cat->faqs) : "";

        $datas['cat'] = $cat;
        $datas['allCategories'] = VideoCategory::getAllCategoriesWithSubcategories();
        $datas['appArray'] = \App\Models\AppCategory::all();
        $datas['userRole'] = User::where('user_type', 5)->get();
        return view('videos.edit_cat', compact('datas'));
    }


    public function update(Request $request, VideoCategory $videoCat)
    {
        $currentuserid = auth()->user()->id;
        $res = VideoCategory::whereId($request->id)->first();
        if (!$res) {
            return response()->json([
                'error' => "Category Not Found",
            ]);
        }

        // Get SEO employee IDs for access check
        $seoEmpIds = $res->seo_emp_id ?? '';

        $slugError = VideoSlugHistory::checkSlugValidation($request->input('slug'), $request->id);
        if ($slugError) {
            return response()->json([
                'error' => $slugError,
            ]);
        }

        // Check access - SEO Manager and Admin can always edit
        // Other users can edit if they created it or are assigned as SEO
        $user = auth()->user();
        $userType = $user->user_type;
        
        if (RoleManager::isAdminOrSeoManager($userType)) {
            // Admin and SEO Manager can edit all categories
        } else {
            $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id ?? $currentuserid, [$seoEmpIds]);
            if ($accessCheck) {
                if ($request->ajax()) {
                    return response()->json(['error' => $accessCheck], 403);
                }
                return redirect()->back()->with('error', $accessCheck);
            }
        }

        // Update basic fields
        $res->category_name = $request->input('category_name');

        // Update parent category validation
        $newParentCategoryId = $request->input('parent_category_id', 0);

        if ($newParentCategoryId && $newParentCategoryId != 0) {
            $parentCat = VideoCategory::find($newParentCategoryId);
            if ($parentCat && $parentCat->parent_category_id != 0) {
                return response()->json([
                    'error' => 'Use Parent Category.'
                ]);
            }
        }

        $res->slug = $request->input('slug');
        $res->canonical_link = $request->input('canonical_link');

        // Validate canonical link for video categories
        if (!empty($res->canonical_link)) {
            $canonicalError = $this->validateVideoCategoryCanonicalLink($res->canonical_link, $res->slug);
            if ($canonicalError) {
                return response()->json([
                    'error' => $canonicalError
                ]);
            }
        }

        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->category_title = $request->input('category_title');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        if ($request->hasFile('category_thumb')) {
            $image = $request->file('category_thumb');
            $this->validate($request, ['category_thumb' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old image
            if ($res->category_thumb && $res->category_thumb != 'uploadedFiles/vCatThumb/no_image.png' && !filter_var($res->category_thumb, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->category_thumb);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } elseif ($request->has('category_thumb') && $request->input('category_thumb')) {
            $base64Image = $request->input('category_thumb');
            if (strpos($base64Image, 'data:image') === 0) {
                // Delete old image
                if ($res->category_thumb && $res->category_thumb != 'uploadedFiles/vCatThumb/no_image.png' && !filter_var($res->category_thumb, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->category_thumb);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatThumb/' . $new_name, $image_base64);
                $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->category_thumb = $base64Image;
            }
        }

        // Handle mockup
        if ($request->hasFile('mockup')) {
            $mockup = $request->file('mockup');
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old mockup
            if ($res->mockup && !filter_var($res->mockup, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->mockup);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        } elseif ($request->has('mockup') && $request->input('mockup')) {
            $base64Image = $request->input('mockup');
            if (strpos($base64Image, 'data:image') === 0) {
                if ($res->mockup && !filter_var($res->mockup, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->mockup);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatMockup/' . $new_name, $image_base64);
                $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->mockup = $base64Image;
            }
        }

        // Handle banner
        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old banner
            if ($res->banner && !filter_var($res->banner, FILTER_VALIDATE_URL)) {
                StorageUtils::delete('public/' . $res->banner);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        } elseif ($request->has('banner') && $request->input('banner')) {
            $base64Image = $request->input('banner');
            if (strpos($base64Image, 'data:image') === 0) {
                if ($res->banner && !filter_var($res->banner, FILTER_VALIDATE_URL)) {
                    StorageUtils::delete('public/' . $res->banner);
                }
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.png';
                $image_parts = explode(";base64,", $base64Image);
                $image_base64 = base64_decode($image_parts[1]);
                Storage::disk('public')->put('uploadedFiles/vCatBanner/' . $new_name, $image_base64);
                $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
            } elseif (filter_var($base64Image, FILTER_VALIDATE_URL)) {
                $res->banner = $base64Image;
            }
        }

        // Generate or use existing folder string
        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
            $res->fldr_str = $fldrStr;
        }

        // Store old paths for cleanup
        $oldContentPath = $res->contents ?? null;
        $oldFaqPath = $res->faqs ?? null;

        // Handle contents - store as JSON file
        if ($request->input('contents')) {
            $contents = \App\Http\Controllers\Utils\ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;

            // Delete old content file if exists and is different
            if ($oldContentPath && $oldContentPath != $contentPath && !filter_var($oldContentPath, FILTER_VALIDATE_URL)) {
                try {
                    StorageUtils::delete($oldContentPath);
                } catch (\Exception $e) {
                    // Ignore if file doesn't exist
                }
            }
        }

        // Handle faqs - store as JSON file
        if (isset($request->faqs)) {
            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title ?? '';
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;

            // Delete old faq file if exists and is different
            if ($oldFaqPath && $oldFaqPath != $faqPath && !filter_var($oldFaqPath, FILTER_VALIDATE_URL)) {
                try {
                    StorageUtils::delete($oldFaqPath);
                } catch (\Exception $e) {
                    // Ignore if file doesn't exist
                }
            }
        }

        // Handle top_keywords - keep as JSON in database
        if ($request->has('top_keywords')) {
            $topKeywords = $request->input('top_keywords');
            $res->top_keywords = is_string($topKeywords) ? json_decode($topKeywords, true) : $topKeywords;
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $this->applyVideoSitemapFieldsFromRequest($request, $res, false);
        $res->save();

        // AJAX response
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
        }

        return redirect()->route('show_v_cat')->with('success', 'Category updated successfully.');
    }


    public function destroy($id)
    {
        // $res = VideoCategory::find($id);
        // $category_thumb = $res->category_thumb;
        // $contains = Str::contains($category_thumb, 'no_image');
        // if (!$contains) {
        //     try {
        //         unlink(storage_path("app/public/" . $category_thumb));
        //     } catch (\Exception $e) {
        //     }
        // }
        // VideoCategory::destroy(array('id', $id));
        return redirect('show_v_cat');
    }

    public function imp_update(Request $request, $id)
    {
        // Only Admin or SEO Manager can update IMP status
        if (!\App\Http\Controllers\Utils\RoleManager::isAdminOrSeoManager(auth()->user()->user_type)) {
            return response()->json([
                'error' => "Ask admin or manager for changes"
            ]);
        }

        try {
            $res = VideoCategory::findOrFail($id);

            // Toggle IMP status
            $res->imp = $res->imp == '1' ? '0' : '1';

            if ($res->save()) {
                return response()->json([
                    'success' => 'done'
                ]);
            } else {
                return response()->json(['error' => 'Failed to save'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found'], 404);
        }
    }

    /**
     * Update cat_link for all child categories when parent id_name changes
     */
    //    private function updateChildCatLink($idName, $parentID)
//    {
//        $childCategories = VideoCategory::where('parent_category_id', $parentID)->get();
//        foreach ($childCategories as $childCat) {
//            $childCat->cat_link = $idName . '/' . $childCat->id_name;
//            $childCat->save();
//        }
//    }

    /**
     * Validate canonical link for video categories
     * Video categories use URL format: {slug} (direct slug without prefix)
     */
    private function validateVideoCategoryCanonicalLink($canonicalLink, $slug): ?string
    {
        if (isset($canonicalLink)) {
            if (!str_starts_with($canonicalLink, HelperController::$videoFrontendUrl)) {
                return "Canonical link must be start with " . HelperController::$videoFrontendUrl;
            }
            // Video categories use direct slug format: {slug}
            $pageUrl = HelperController::$videoFrontendUrl . $slug;
            if (rtrim($pageUrl, '/') == rtrim($canonicalLink, '/')) {
                return "Canonical link cannot be same as page url";
            }
        }
        return null;
    }
}
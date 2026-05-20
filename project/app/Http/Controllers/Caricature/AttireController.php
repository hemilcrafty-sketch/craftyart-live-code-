<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Religion;
use App\Models\Style;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;
use Cache;
use Illuminate\Support\Facades\Storage;

class AttireController extends AppBaseController
{

    public function show(Request $request)
    {
        $paginate_count = 20;
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        if ($idAdmin) {
            $currentuserid = -1;
        }

        $sortingField = $request->get('sortingField', 'created_at') ?: 'created_at';

        // Start base query
        $temp_data_query = Attire::query()->where('deleted', 0);

        if ($request->filled('template_status')) {
            if ($request->template_status === 'not-live') {
                $temp_data_query->where('status', 0);
            } elseif ($request->template_status === 'live') {
                $temp_data_query->where('status', 1);
            }
        }

        // if ($request->filled('seo_employee')) {
        //     if ($request->seo_employee === 'assigned') {
        //         $temp_data_query->whereNotNull('seo_emp_id')->where('seo_emp_id', '!=', 0);
        //     } elseif ($request->seo_employee === 'unassigned') {
        //         $temp_data_query->where(function ($q) {
        //             $q->whereNull('seo_emp_id')->orWhere('seo_emp_id', 0);
        //         });
        //     }
        // }

        if ($request->filled('seo_category_assigne')) {
            if ($request->seo_category_assigne === 'assigned') {
                $temp_data_query->whereNotNull('category_id')->where('category_id', '!=', 0);
            } elseif ($request->seo_category_assigne === 'unassigned') {
                $temp_data_query->where(function ($q) {
                    $q->whereNull('category_id')->orWhere('category_id', 0);
                });
            }
        }

        if ($request->filled('premium_type')) {
            if ($request->premium_type === 'free') {
                $temp_data_query->where('is_premium', 0)->where('is_freemium', 0);
            } elseif ($request->premium_type === 'freemium') {
                $temp_data_query->where('is_freemium', 1);
            } elseif ($request->premium_type === 'premium') {
                $temp_data_query->where('is_premium', 1);
            }
        }

        $this->applyNoIndexFilter($request, $temp_data_query);

        // if (!$idAdmin) {
        //     if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
        //         $temp_data_query->whereHas('newCategory', function ($q) use ($currentuserid) {
        //             $q->where('seo_emp_id', $currentuserid);
        //         });
        //     } else {
        //         $temp_data_query->where('seo_emp_id', $currentuserid);
        //     }
        // }


        // ✅ search query (keeps other filters)
        if ($request->filled('query')) {
            $search = $request->input('query'); // <-- Correct way

            $temp_data_query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('string_id', 'LIKE', "%{$search}%")
                    ->orWhere('post_name', 'LIKE', "%{$search}%");
            });
        }

        if (!$request->filled('query')) {
            if ($request->filled('cat')) {
                $temp_data_query->where('category_id', $request->new_cat);
            }
        }

        $temp_data_count = $temp_data_query->count();
        $temp_data = $temp_data_query->orderBy($sortingField, 'desc')->paginate($paginate_count);

        // Count string logic
        $total = $temp_data_count;
        $count = $total;
        $total_diff = $paginate_count - 1;
        $diff = $paginate_count - 1;

        if ($total < $paginate_count) {
            $diff = $total - 1;
        }

        if ($request->has('page')) {
            $count = $request->page * $paginate_count;
            if ($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            }
        } else {
            $count = $paginate_count;
            if ($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            }
        }

        $ccc = $total == 0
            ? "Showing 0-0 of 0 entries"
            : "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";

        // Extra data for view
        $seoExecutiveNIntern = User::whereIn('user_type', [5, 6])
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        $data['count_str'] = $ccc;
        $data['item'] = $temp_data;
        $data['seoExecutiveNIntern'] = $seoExecutiveNIntern;
        $data['categories'] = CaricatureCategory::all();

        $parentCategories = CaricatureCategory::where('parent_category_id', 0)->get();
        $groupedNewCategories = [];

        foreach ($parentCategories as $parent) {
            $children = CaricatureCategory::where('parent_category_id', $parent->id)->get();
            $groupedNewCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }

        $newCategoryIds = collect($temp_data->items())->pluck('category_id')->unique()->filter();
        $newCategoriesForSEO = CaricatureCategory::whereIn('id', $newCategoryIds)->get()->keyBy('id');
        $categorySeoEmpIds = [];

        // foreach ($newCategoriesForSEO as $category) {
        //     $id = $category->seo_emp_id ?? null;
        //     if (!empty($id)) {
        //         $categorySeoEmpIds[$category->id] = $id;
        //     }
        // }

        if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
            $seoUsers = User::where('user_type', 6)
                ->where('team_leader_id', $currentuserid)
                ->get()
                ->keyBy('id');
        }

        $noIndexStats = $this->noIndexListingStats(Attire::class, static function ($q) {
            $q->where('deleted', 0);
        });

        return view('attire.show_attire', [
            'itemArray' => $data,
            'categorySeoEmpIds' => $categorySeoEmpIds,
            'seoUsers' => $seoUsers ?? [],
            'groupedNewCategories' => $groupedNewCategories,
            'noIndexStats' => $noIndexStats,
        ]);
    }

    public function add(Request $request)
    {
        return view('attire.add_attire');
    }

    public function edit($id)
    {
        $attire = Attire::findOrFail($id);

        return view('attire.edit_attire', compact('attire'));
    }

    public function store(Request $request)
    {
        $imageFields = [
            ['img' => $request->preview_url, 'name' => "Preview Image", 'required' => true],
            ['img' => $request->attire_url, 'name' => "Attire Image", 'required' => true],
            ['img' => $request->thumbnail_url, 'name' => "Watermark Image", 'required' => true],
            ['img' => $request->coordinate_image, 'name' => "Coordinate Image", 'required' => true]
        ];

        // Validate base64 format
        $validationError = ContentManager::validateBase64Images($imageFields, [
            'webp' => 500, 'jpg' => 500, 'jpeg' => 500, 'svg' => 500, 'png' => 500
        ]);

        if ($validationError) {
            return response()->json(['error' => $validationError]);
        }

        try {
            // ✅ Step 1: Extract image dimensions (without saving)
            $ratios = [];
            $ratioValues = [];
            foreach ($imageFields as $field) {
                $info = ContentManager::extractBase64Dimensions($field['img']);
                if (!$info || !$info['width'] || !$info['height']) {
                    return response()->json(['error' => "Unable to read image size for {$field['name']}"]);
                }
                $ratio = round($info['width'] / $info['height'], 4);
                $ratios[$field['name']] = "{$info['width']}x{$info['height']} (Ratio: {$ratio})";
                $ratioValues[$field['name']] = $ratio;
            }

            // ✅ Step 2: Compare ratios (tolerance ±0.002)
            $uniqueRatios = [];
            foreach ($ratioValues as $ratio) {
                $matched = false;
                foreach ($uniqueRatios as $uniqueRatio) {
                    if (abs($ratio - $uniqueRatio) <= 0.002) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $uniqueRatios[] = $ratio;
                }
            }

            if (count($uniqueRatios) > 1) {
                $ratioDetails = collect($ratios)->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
                return response()->json([
                    'error' => "All images must have similar aspect ratios (max difference: 0.002). {$ratioDetails}"
                ]);
            }

            // ✅ Step 3: Ratios are valid — now save images
            $res = new Attire();
            $res->post_name = $request->post_name;
            $res->string_id = HelperController::generateStringIds(10, "", Attire::class);
            $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $res->post_name)));
            $res->id_name = $res->string_id . '-' . $slug;

            // Create unique folder
            $fldrStr = HelperController::generateID('', 10);
            while (Attire::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateID('', 10);
            }
            $res->fldr_str = $fldrStr;

            // Save images after ratio check passes
            $res->preview_url = ContentManager::saveImageToPath($request->preview_url, "caricature/attire/{$fldrStr}/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->attire_url = ContentManager::saveImageToPath($request->attire_url, "caricature/attire/{$fldrStr}/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->thumbnail_url = ContentManager::saveImageToPath($request->thumbnail_url, "caricature/attire/{$fldrStr}/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
            $res->coordinate_image = ContentManager::saveImageToPath($request->coordinate_image, "caricature/attire/{$fldrStr}/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

            // Save metadata
            $res->width = $info['width'];
            $res->height = $info['height'];
            $res->json = $request->json;
            $res->head_count = $request->head_count;
            $res->emp_id = auth()->user()->id;
            $res->skin_color = $request->skin_color;
            $res->faces = $request->faces;
            $res->save();
            return response()->json(['success' => "Attire created successfully"]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $attire = Attire::findOrFail($id);

        // Collect only new images from request
        $imageFields = [];
        if ($request->has('preview_url') && !empty($request->preview_url)) {
            $imageFields[] = ['img' => $request->preview_url, 'name' => "Preview Image", 'required' => true];
        }
        if ($request->has('attire_url') && !empty($request->attire_url)) {
            $imageFields[] = ['img' => $request->attire_url, 'name' => "Attire Image", 'required' => true];
        }
        if ($request->has('thumbnail_url') && !empty($request->thumbnail_url)) {
            $imageFields[] = ['img' => $request->thumbnail_url, 'name' => "Watermark Image", 'required' => true];
        }
        if ($request->has('coordinate_image') && !empty($request->coordinate_image)) {
            $imageFields[] = ['img' => $request->coordinate_image, 'name' => "Coordinate Image", 'required' => true];
        }

        // Validate base64 image format
        if (!empty($imageFields)) {
            $validationError = ContentManager::validateBase64Images($imageFields, [
                'webp' => 500, 'jpg' => 500, 'jpeg' => 500, 'svg' => 500, 'png' => 500
            ]);
            if ($validationError) {
                return response()->json(['error' => $validationError]);
            }

            // ✅ Step 1: Check aspect ratio consistency before saving
            $ratios = [];
            $ratioValues = [];
            foreach ($imageFields as $field) {
                $info = ContentManager::extractBase64Dimensions($field['img']);
                if (!$info || !$info['width'] || !$info['height']) {
                    return response()->json(['error' => "Unable to read image size for {$field['name']}"]);
                }
                $ratio = round($info['width'] / $info['height'], 4);
                $ratios[$field['name']] = "{$info['width']}x{$info['height']} (Ratio: {$ratio})";
                $ratioValues[$field['name']] = $ratio;
            }

            // Compare ratios with 0.002 tolerance
            $uniqueRatios = [];
            foreach ($ratioValues as $ratio) {
                $matched = false;
                foreach ($uniqueRatios as $uniqueRatio) {
                    if (abs($ratio - $uniqueRatio) <= 0.002) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $uniqueRatios[] = $ratio;
                }
            }

            if (count($uniqueRatios) > 1) {
                $ratioDetails = collect($ratios)->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
                return response()->json([
                    'error' => "All uploaded images must have similar aspect ratios (max difference: 0.002). {$ratioDetails}"
                ]);
            }
        }

        try {
            // ✅ Step 2: Save textual and image fields only after ratio check passes
            $attire->post_name = $request->post_name;
            $attire->skin_color = $request->skin_color;

            if ($attire->isDirty('post_name')) {
                $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $attire->post_name)));
                $attire->id_name = $attire->string_id . '-' . $slug;
            }

            // ✅ Step 3: Replace updated images
            foreach (['preview_url', 'attire_url', 'thumbnail_url', 'coordinate_image'] as $field) {
                if ($request->has($field) && !empty($request->$field)) {
                    $attire->$field = ContentManager::saveImageToPath(
                        $request->$field,
                        'caricature/attire/' . $attire->fldr_str . "/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp
                    );
                    if (in_array($field, ['thumbnail_url'])) {
                        if ($attire->$field && Storage::exists($attire->$field)) {
                            Storage::delete($attire->$field);
                        }
                    }
                }
            }

            // ✅ Step 4: Update image size info (thumbnail reference)
            $thumbnailPublicUrl = HelperController::generatePublicUrl($attire->thumbnail_url);
            $thumbnailSize = ContentManager::getImageSizeFromUrl($thumbnailPublicUrl);
            $attire->width = $thumbnailSize['width'] ?? 0;
            $attire->height = $thumbnailSize['height'] ?? 0;

            $attire->head_count = $request->head_count;
            $attire->json = $request->json;
            $attire->faces = $request->faces;
            $attire->save();

            return response()->json(['success' =>   "Attire updated successfully"]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function edit_seo(Attire $attire, $id)
    {
        $res = Attire::find($id);
        if (!$res) {
            $res = Attire::where('string_id', $id)->first();
        }
        if (!$res) {
            abort(404);
            return;
        }


        if ($res->related_tags) {
            $res->related_tags = implode(",", json_decode($res->related_tags, true));
        }


        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";

        $datas['item'] = $res;
        $datas['styleArray'] = Style::all();
        $categoryId = $datas['item']['category_id'];
        $selectCategory = CaricatureCategory::find($categoryId);


        $datas['allCategories'] = CaricatureCategory::getAllCategoriesWithSubcategories(1);
        $datas['select_category'] = (isset($selectCategory) && $selectCategory) ? $selectCategory : [];

        if ($res->new_category_id != "" && $res->new_category_id != 0) {
            $category = CaricatureCategory::find($res->new_category_id);
            if ($category) {
                $rootParentId = $category->getRootParentId();
                $rootParentId = $rootParentId ?: $res->new_category_id;
                $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
                $datas['themeArray'] = [];
            } else {
                $datas['themeArray'] = [];
            }
        } else {
            $datas['themeArray'] = [];
        }

        $datas['religions'] = Religion::all();

        return view('attire/edit_seo_attire')->with('dataArray', $datas);
    }

    public function update_seo(Request $request): JsonResponse
    {
        $attire = Attire::find($request->id);
        if (!$attire) {
            return response()->json(['error' => "Attire Data not Found"]);
        }

        $id_name = $request->input('id_name');
        if (!$id_name) {
            return response()->json(['error' => "Id name not Found"]);
        }
        $id_name = str_replace($attire->string_id . '-', '', $id_name);
        $idNameError = "";
        if (preg_match('/\s/', $id_name))
            $idNameError = 'ID Name should not contain whitespace.';
        if (preg_match('/[A-Z]/', $id_name))
            $idNameError = 'ID Name should not contain capital letters.';
        if (preg_match('/[^\w\s-]/', $id_name))
            $idNameError = 'ID Name should not contain special characters.';

        if ($idNameError) {
            return response()->json(['error' => $idNameError]);
        }

        $fullIdName = $attire->string_id . '-' . $id_name;
        $existingAttire = Attire::where('id_name', $fullIdName)
            ->where('id', '!=', $request->id)
            ->first();

        if ($existingAttire) {
            return response()->json(['error' => "ID name already exists for another attire."]);
        }
        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json(['error' => $contentError]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents)];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json(['error' => $validationError]);
        }

        // Validate FAQs
        if (isset($request->faqs) && str_replace(['[', ']'], '', $request->faqs) != '') {
            if (!isset($request->faqs_title)) {
                return response()->json(['error' => "Please Add Faq Title"]);
            }

            // Validate FAQ JSON structure
            $faqs = json_decode($request->faqs, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => "Invalid FAQ JSON format"]);
            }

            if (!is_array($faqs)) {
                return response()->json(['error' => "FAQs must be a valid JSON array"]);
            }
        }

        // Validate canonical link
        $canonical_link = $request->input('canonical_link');
        $canonicalError = "";
        if (isset($canonical_link)) {
            if (!str_starts_with($canonical_link, HelperController::$frontendUrl)) {
                $canonicalError = "Canonical link must be start with " . HelperController::$frontendUrl;
            }
        }
        if ($canonicalError) {
            return response()->json(['error' => $canonicalError]);
        }

        // Validate category
        $cat = CaricatureCategory::find($request->input('category_id'));
//        if ($cat && $cat->parent_category_id == 0) {
//            return response()->json(['error' => 'Use Child Category.']);
//        }

        // Validate post name and meta title
        if (strcasecmp($request->input('post_name'), $request->input('meta_title')) === 0) {
            return response()->json(['error' => "Post Name and Meta Title should not be the same."]);
        }


        try {
            $oldCategoryID = $attire->category_id;
            $attire->id_name = $fullIdName;
            $attire->canonical_link = $canonical_link;
            $attire->category_id = $request->input('category_id');
            $attire->post_name = $request->input('post_name');
            $attire->meta_title = $request->input('meta_title');
            $attire->h2_tag = $request->input('h2_tag');
            $attire->long_desc = $request->input('long_desc');
            $attire->meta_description = $request->input('meta_description');

            // Handle keywords as array
//            $keywordsArray = $request->input('keywords', []);
//            $attire->related_tags = !empty($keywordsArray) ? json_encode($keywordsArray) : null;

            $religionIds = $request->input('religion_id', []);
            $attire->religion_id = !empty($religionIds) ? json_encode($religionIds) : null;

            //            $themeIds = $request->input('theme_id', []);
//            $attire->theme_id = !empty($themeIds) ? json_encode($themeIds) : null;

            //            $styleIds = $request->input('style_id', []);
//            $attire->style_id = !empty($styleIds) ? json_encode($styleIds) : null;
            $attire->style_id = $request->input('styles');

            $attire->status = $request->input('status');
            $attire->priority = $request->input('priority', 0.90);
            $attire->frequency = $request->input('frequency', 'daily');
            $attire->is_premium = $request->input('is_premium');
            $attire->is_freemium = $request->input('is_freemium');

            $contentPath = $attire->contents;
            $faqsPath = $attire->faqs;

            if ($request->input('contents')) {
                $contents = ContentManager::getContents($request->input('contents'), $attire->fldr_str);
                $contentPath = $contentPath ?? 'caricature/attire/' . $attire->fldr_str . '/jn/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($contentPath, $contents);
                $attire->contents = $contentPath;
            }

            // Handle FAQs
            if (isset($request->faqs)) {
                $faqPath = 'caricature/attire/' . $attire->fldr_str . '/fq/' . StorageUtils::getNewName() . ".json";
                $faqs = [];
                $faqs['title'] = $request->faqs_title;
                $faqs['faqs'] = $faqsPath ?? json_decode($request->faqs);
                StorageUtils::put($faqPath, json_encode($faqs));
                $attire->faqs = $faqPath;
            }

            $attire->save();
            self::updateAttireCount($attire->category_id, $oldCategoryID);
            return response()->json(['success' => "Done"]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function assignNewCategory(Request $request)
    {
        if (!RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $request->validate([
            'id' => 'required|integer',
            'category_id' => 'required|integer',
        ]);

        $attire = Attire::find($request->id);
        $category = CaricatureCategory::find($request->category_id);
        $parentCategory = $category->parent_category_id ? CaricatureCategory::find($category->parent_category_id) : null;

        if (!$attire || !$category) {
            return response()->json(['status' => false, 'message' => 'Invalid data.']);
        }
        $oldCategoryId = $attire->category_id;
        $attire->category_id = $request->category_id;
        $attire->save();

        // $seoEmpId = $category->seo_emp_id ?? null;
        $users = collect();
        if (!empty($seoEmpId)) {
            $users = User::where('id', $seoEmpId)->select('id', 'name')->get();
        }
        self::updateAttireCount(
            newCatId: $request->category_id,
            oldCategoryID: $oldCategoryId,
        );

        return response()->json([
            'status' => true,
            'message' => 'New category assigned successfully.',
            'category_name' => $category->category_name,
            'parent_name' => $parentCategory->category_name ?? '',
            'seo_users' => $users,
        ]);
    }

    public function updateCategory(Request $request)
    {
        if (isset($request->cat_id) && $request->cat_id != "") {
            $category = CaricatureCategory::where('id', $request->cat_id)->first();
            if (!$category) {
                return response()->json([
                    "status" => false,
                    "error" => "category not found"
                ]);
            }
            $cateId = $category->id;
            try {
                Attire::where("id", $request->tempId)->update(["category_id" => $cateId]);
                return response()->json([
                    "status" => true,
                    "success" => "Category update of the template"
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "error" => $e->getMessage()
                ]);
            }
        }
    }

    public function assignSeo(Request $request)
    {
        $isAccess = RoleManager::isSeoExecutive(Auth::user()->user_type);
        if ($isAccess) {
            $res = Attire::find($request->id);
            // dd($res->seo_emp_id);
            // $res->seo_emp_id = $request->seo_emp_id;
            $res->seo_assigner_id = Auth::user()->id;
            $res->save();
            return response()->json([
                'status' => true,
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => "Not Permission to assign",
            ]);
        }
    }

    public function updateAttirePremium(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:free,freemium,premium',
        ]);
        $idAdmin = RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        if ($idAdmin) {
            $template = Attire::findOrFail($request->id);
            switch ($request->type) {
                case 'premium':
                    $template->is_premium = 1;
                    $template->is_freemium = 0;
                    break;
                case 'freemium':
                    $template->is_premium = 0;
                    $template->is_freemium = 1;
                    break;
                case 'free':
                    $template->is_premium = 0;
                    $template->is_freemium = 0;
                    break;
            }

            $template->save();
            return response()->json(['success' => "done"]);
        } else {
            return response()->json([
                'error' => "Not valid request",
            ]);
        }
    }

    public function pinned_update(Request $request)
    {
        $currentuserid = Auth::user()->user_type;
        $idAdmin = RoleManager::isAdminOrSeoManager($currentuserid) || RoleManager::isSeoExecutive($currentuserid);

        if ($idAdmin) {
            $res = Attire::find($request->id);

            if ($res->pinned == 1) {
                $res->pinned = 0;
            } else {
                $res->pinned = 1;
            }

            $res->save();

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => false
            ]);
        }
    }

    public function editorChoiceUpdate(Request $request)
    {

        $res = Attire::find($request->id);
        $idAdmin = roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        if ($idAdmin) {
            if ($res->editor_choice == 1) {
                $res->editor_choice = 0;
            } else {
                $res->editor_choice = 1;
            }

            $res->save();

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => $res->status == 1 ? true : false
            ]);
        }
    }

    public function status_update(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type) || RoleManager::isSeoExecutive(Auth::user()->user_type);

        $res = Attire::find($request->id);

        if ($idAdmin) {

            if ($res->status == 1) {
                $res->status = 0;
            } else {
                $missingFields = self::checkRequiredValidation($res);
                if (!empty($missingFields)) {
                    return response()->json([
                        'error' => 'The following fields are missing or empty: ' . implode(', ', $missingFields),
                    ]);
                }
                $res->status = 1;
            }


            $res->save();
            self::updateAttireCount($res->category_id);
            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => false
            ]);
        }


    }
    public function destroy(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);
        if ($idAdmin) {
            $res = Attire::find($request->id);
            $res->deleted = 1;
            $res->save();
        }

        return redirect('show_item');
    }

    public function checkRequiredValidation($res): array
    {
        $requiredFields = [
            ['value' => $res->post_name, 'name' => 'Post Name'],
            ['value' => $res->id_name, 'name' => 'ID Name'],
            ['value' => $res->category_id, 'name' => 'Category Id'],
            ['value' => $res->meta_title, 'name' => 'Meta Title'],
            ['value' => $res->meta_description, 'name' => 'Meta Description'],
        ];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            $value = $field['value'];
            $name = $field['name'];
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                $missingFields[] = $name;
            }
        }
        return $missingFields;
    }

    public static function updateAttireCount($newCatId, $oldCategoryID = null): void
    {
        $affectedCategories = [];

        if ($oldCategoryID && $oldCategoryID != 0 && $oldCategoryID != $newCatId) {
            $affectedCategories[] = $oldCategoryID;
            $oldCategory = CaricatureCategory::find($oldCategoryID);
            if ($oldCategory && $oldCategory->parent_category_id != 0) {
                $affectedCategories[] = $oldCategory->parent_category_id;
            }
        }

        if ($newCatId && $newCatId != 0) {
            $affectedCategories[] = $newCatId;
            $newCategory = CaricatureCategory::find($newCatId);
            if ($newCategory && $newCategory->parent_category_id != 0) {
                $affectedCategories[] = $newCategory->parent_category_id;
            }
        }

        // Remove duplicates
        $affectedCategories = array_unique($affectedCategories);

        foreach ($affectedCategories as $catId) {
            self::adjustCategoryCount($catId);
            self::clearCacheByCat($catId);
        }
    }

//    public static function updateAttireCount($newCatId, $oldCategoryID = null): void
//    {
//        if ($oldCategoryID && $oldCategoryID != 0) {
//            self::adjustCategoryCount($oldCategoryID);
//        }
//        if ($newCatId && $newCatId != 0 && $oldCategoryID !== $newCatId) {
//            self::adjustCategoryCount($newCatId);
//            self::clearCacheByCat($oldCategoryID);
//            self::clearCacheByCat($newCatId);
//        }
//    }

    public static function clearCacheByCat($catId)
    {
        if ($catId != 0) {
            $category = CaricatureCategory::where('id', $catId)->where('status', 1)->first();
            if (!empty($category->parent_category_id) && $category->parent_category_id != 0) {
                //                Cache::tags(["category_$category->cat_link"])->flush();
//                Cache::tags(["category_$category->id"])->flush();
//                Cache::tags(["category_$category->id_name"])->flush();
                $parent = CaricatureCategory::where('id', $category->parent_category_id)->where('status', 1)->first();
                if ($parent) {
                    //                    Cache::tags(["category_$parent->cat_link"])->flush();
//                    Cache::tags(["category_$parent->id"])->flush();
//                    Cache::tags(["category_$parent->id_name"])->flush();
                }
            }
        }
    }

    protected static function adjustCategoryCount(int $categoryId): void
    {
        $category = CaricatureCategory::where('id', $categoryId)
            ->where('status', 1)
            ->first();

        if (!$category)
            return;

        $childIds = CaricatureCategory::where('status', 1)
            ->where('parent_category_id', $categoryId)
            ->pluck('id')
            ->toArray();

        foreach ($childIds as $childId) {
            $grandChildren = CaricatureCategory::where('parent_category_id', $childId)
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
            $childIds = array_merge($childIds, $grandChildren);
        }

        $allIds = array_merge([$categoryId], $childIds);

        $count = Attire::whereIn('category_id', $allIds)
            ->where('status', 1)
            ->where('deleted', 0)
            ->count();

        $category->total_templates = $count;
        $category->save();
    }

//    protected static function adjustCategoryCount(int $categoryId): void
//    {
//        $category = CaricatureCategory::where('id', $categoryId)->where('status', 1)->first();
//
//        if ($category) {
//            // Count how many designs are assigned to this category
//            $count = Attire::where('category_id', $categoryId)->where('status', 1)->where('deleted', 0)->count();
//            $category->total_templates = $count;
//            $category->save();
//
//            // Also update parent category count if it exists
//            if (!empty($category->parent_category_id) && $category->parent_category_id != 0) {
//                $parent = CaricatureCategory::where('id', $category->parent_category_id)
//                    ->where('status', 1)
//                    ->first();
//
//                if ($parent) {
//                    // Get all child category IDs from parent (assuming stored as JSON like [4,5,6])
//                    $childIds = !empty($parent->child_cat_ids)
//                        ? json_decode($parent->child_cat_ids, true)
//                        : [];
//
//                    // Include parent itself if you want
//                    $allIds = array_merge([$parent->id], $childIds);
//
//                    // Count total Attire entries for all related category IDs
//                    $parentCount = Attire::whereIn('category_id', $allIds)->where('status', 1)->where('deleted', 0)->count();
//
//                    $parent->total_templates = $parentCount;
//                    $parent->save();
//                }
//            }
//        }
//    }


}
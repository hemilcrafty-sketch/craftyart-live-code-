<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Routing\Redirector;

class CaricatureCategoryController extends AppBaseController
{

    public function show(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'id_name', 'value' => 'ID Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'status', 'value' => 'Status'],
            ['id' => 'no_index', 'value' => 'No Index'],
        ];

        $noIndexStats = $this->noIndexListingStats(CaricatureCategory::class);

        $query = CaricatureCategory::query();
        $this->applyNoIndexFilter($request, $query);

        $catArray = $this->applyFiltersAndPagination(
            $request,
            $query,
            $searchableFields,
            [
                'parent_query' => CaricatureCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'parent_category_id',
            ]
        );
        return view('caricature_cat.show_caricature_cat', compact('catArray', 'searchableFields', 'noIndexStats'));
    }

    public function create()
    {
        $allCategories = CaricatureCategory::getAllCategoriesWithSubcategories();
        $userRole = User::where('user_type', 5)->get();
        return view('caricature_cat/create_cari_cat', compact( 'allCategories', 'userRole'));
    }

    public function store(Request $request): JsonResponse
    {
        $accessCheck = $this->isAccessByRole("seo_all");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        if (HelperController::checkCaricatureCategoryAvail(0, $request->input('category_name'), $request->input('id_name'), $request->input('parent_category_id'))) {
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }

        $cat = CaricatureCategory::find($request->input('parent_category_id'));
        if ($cat && $cat->parent_category_id != 0) {
            return response()->json([
                'error' => 'Use Parent Category.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => true], ['img' => $request->banner, 'name' => "Banner", 'required' => false], ['img' => $request->mockup, 'name' => "Mockup", 'required' => true]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        if (isset($request->faqs) && str_replace(['[', ']'], '', $request->faqs) != '') {
            if (!isset($request->faqs_title)) {
                return response()->json([
                    'error' => "Please Add Faq Title"
                ]);
            }
        }

        $keywordNames = $request->input('keyword_name');
        $keywordLinks = $request->input('keyword_link');
        $keywordTargets = $request->input('keyword_target');
        $keywordRels = $request->input('keyword_rel');

        $topKeywords = [];
        for ($i = 0; $i < count($keywordNames); $i++) {
            $keyword['value'] = $keywordNames[$i];
            $keyword['link'] = $keywordLinks[$i];
            $keyword['openinnewtab'] = $keywordTargets[$i];
            $keyword['nofollow'] = $keywordRels[$i];
            $topKeywords[] = $keyword;
        }

        $slug = '';
        if ($request->input('parent_category_id') != null && $request->input('parent_category_id') != 0) {
            $idName = CaricatureCategory::where('id', $request->input('parent_category_id'))->value('id_name') ?? '';
            if (isset($idName))
                $slug = $idName . "/" . $request->input('id_name');
        } else {
            $slug = $request->input('id_name');
        }

        $canonical_link = $request->input('canonical_link');
        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $slug);
        if ($canonicalError) {
            return response()->json([
                'error' => $canonicalError
            ]);
        }

        // if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
        //     $res = new NewCategoryPending;
        // } else {
        $res = new CaricatureCategory;
        // }

        $res->string_id = HelperController::generateId();
        $res->canonical_link = $canonical_link;
        $res->cat_link = $slug;
        $res->category_name = $request->input('category_name');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->id_name = $request->input('id_name');
        $res->tag_line = $request->input('tag_line');
        $res->meta_title = $request->input('meta_title');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->imp = 0;
        $fldrStr = HelperController::generateFolderID('');
        while (CaricatureCategory::where('fldr_str', $fldrStr)->exists()) {
            $fldrStr = HelperController::generateFolderID('');
        }

        $res->fldr_str = $fldrStr;
        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'caricature/category/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'caricature/category/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'caricature/category/'.$fldrStr."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');
        $res->status = !RoleManager::isSeoIntern(Auth::user()->user_type) ? $request->input('status') : 0;
        $res->priority = $request->input('priority', 0.90);
        $res->frequency = $request->input('frequency', 'daily');
        $res->parent_category_id = $request->input('parent_category_id');

        $res->emp_id = auth()->user()->id;

        $res->seo_emp_id = $request->input('seo_emp_id'); // Store as plain integer




        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'caricature/category/'.$fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        if (isset($request->faqs)) {
            $faqPath = 'caricature/category/'.$fldrStr. '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

        $res->child_updated_at = HelperController::newCatChildUpdatedAt($res->parent_category_id);

         $res->save();

        self::updateParentChildRelation($res->id, $res->parent_category_id);

//        Cache::tags(["caricature_$slug"])->flush();
//        Cache::tags(["caricature_$res->id"])->flush();
//        Cache::tags(["caricature_$res->id_name"])->flush();
//        Cache::tags(['categories'])->flush();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function update(Request $request, CaricatureCategory $mainCategory)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdmin(Auth::user()->user_type);

        $res = CaricatureCategory::find($request->id);

        if (!$res) {
            return response()->json(['error' => 'Page not found']);
        }

        $slug = '';
        if ($request->input('parent_category_id') != null && $request->input('parent_category_id') != 0) {
            $idName = CaricatureCategory::where('id', $request->input('parent_category_id'))->value('id_name') ?? '';
            if (isset($idName))
                $slug = $idName . "/" . $request->input('id_name');
        } else {
            $slug = $request->input('id_name');
        }

        $canonical_link = $request->input('canonical_link');
        if (HelperController::checkCaricatureCategoryAvail($res->id ?? 0, $request->input('category_name'), $request->input('id_name'), $request->input('parent_category_id'))) {
            return response()->json(['error' => 'Category Name or Id Name Already exist.']);
        }

        $cat = CaricatureCategory::find($request->input('parent_category_id'));
        if ($cat && $cat->parent_category_id != 0) {
            return response()->json(['error' => 'Use Parent Category.']);
        }

        if ($res->parent_category_id == $res->id) {
            return response()->json(['error' => "You cannot assign self as parent category"]);
        }

        if (!roleManager::isAdmin(Auth::user()->user_type) && RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $slug);
            if ($canonicalError) {
                return response()->json(['error' => $canonicalError]);
            } else {
                $res->canonical_link = $request->canonical_link;

                if ($request->has('status')) {
                    $res->status = $request->status;
                }

                // Add priority and frequency for SEO Manager
                if ($request->has('priority')) {
                    $res->priority = $request->input('priority', $res->priority ?? 0.90);
                }
                if ($request->has('frequency')) {
                    $res->frequency = $request->input('frequency', $res->frequency ?? 'daily');
                }

                $seoIds = $request->input('seo_emp_id');
                $res->seo_emp_id = $seoIds;

                $parentCategoryId = CaricatureCategory::select('parent_category_id as value')->where('id', $request->input('parent_category_id'))->first();
                $res->parent_category_id = (isset($parentCategoryId->value) && $parentCategoryId->value == $request->id)
                    ? $res->parent_category_id
                    : $request->input('parent_category_id');

                $res->save();
                return response()->json(['success' => 'done']);
            }
        }
        $seoEmpIds = $res->seo_emp_id ?? '';

        if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $res->seo_emp_id = $request->input('seo_emp_id');
        }

        $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id ?? $currentuserid, [$seoEmpIds]);
        if ($accessCheck) {
            return response()->json(['error' => $accessCheck]);
        }

        $cat = CaricatureCategory::find($request->input('parent_category_id'));
        if ($cat && $cat->parent_category_id != 0) {
            return response()->json(['error' => 'Use Parent Category.']);
        }

        if (isset($res->id)) {
            if ($request->input('parent_category_id') == $res->id) {
                return response()->json(['error' => "You cannot assign self as parent category"]);
            }
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json(['error' => $contentError]);
        }

        if (isset($request->faqs) && str_replace(['[', ']'], '', $request->faqs) != '') {
            if (!isset($request->faqs_title)) {
                return response()->json(['error' => "Please Add Faq Title"]);
            }
        }

//        if (!RoleManager::isSeoIntern(Auth::user()->user_type) && !isset($request->isPreview)) {
//            $checkStatusError = HelperController::checkStatusCondition($request->id, $request->input('status'), 1, $request->parent_category_id);
//            if ($checkStatusError) {
//                return response()->json(['error' => $checkStatusError]);
//            }
//        }

        $base64Images = [
            ...ContentManager::getBase64Contents($request->contents),
            ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => true],
            ['img' => $request->banner, 'name' => "Banner", 'required' => false],
            ['img' => $request->mockup, 'name' => "Mockup", 'required' => true]
        ];

        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json(['error' => $validationError]);
        }


        $keywordNames = $request->input('keyword_name');
        $keywordLinks = $request->input('keyword_link');
        $keywordTargets = $request->input('keyword_target');
        $keywordRels = $request->input('keyword_rel');

        $topKeywords = [];
        for ($i = 0; $i < count($keywordNames); $i++) {
            $topKeywords[] = [
                'value' => $keywordNames[$i],
                'link' => $keywordLinks[$i],
                'openinnewtab' => $keywordTargets[$i],
                'nofollow' => $keywordRels[$i],
            ];
        }

//        Cache::tags(["category_$res->cat_link"])->flush();
//        Cache::tags(["category_$res->id_name"])->flush();

        $oldIdName = $res->id_name;

        if ($idAdmin) {
            $res->id_name = $request->input('id_name');
        }

        if ($canonical_link && $idAdmin) {
            $res->canonical_link = $canonical_link;
        }

        $res->cat_link = $slug;
        $res->tag_line = $request->input('tag_line');
        $res->category_name = $request->input('category_name');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');

        $parentCategoryId = CaricatureCategory::select('parent_category_id as value')->where('id', $request->input('parent_category_id'))->first();
        $res->parent_category_id = (isset($parentCategoryId->value) && $parentCategoryId->value == $request->id)
            ? $res->parent_category_id
            : $request->input('parent_category_id');



        if ($oldIdName != $res->id_name && $res->parent_category_id == 0) {
            if (isset($res_id)) {
                self::updateChildCatLink($res->id_name, $res->id);
            }
        }


        if ($res->parent_category_id != null && $res->parent_category_id != 0) {
            $res->total_templates = self::getTotalTemplates($res->id ?? 0);
        }

        $res->size = $request->input('size');

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'caricature/category/'.$res->fldr_str."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'caricature/category/'.$res->fldr_str."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'caricature/category/'.$res->fldr_str."/thumb_file/" . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $seoIds = $request->input('seo_emp_id');
            // $seoIds = is_array($seoIds) ? array_map('intval', $seoIds) : [];
            $res->seo_emp_id = $seoIds;
        }

        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');

        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        }

        $res->priority = $request->input('priority', $res->priority ?? 0.90);
        $res->frequency = $request->input('frequency', $res->frequency ?? 'daily');

        $res->parent_category_id = $request->input('parent_category_id');
        // $res->emp_id = auth()->user()->id;


        $contentPath = null;
        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $res->fldr_str);
            $contentPath = 'caricature/category/'.$res->fldr_str . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
        }
        $res->contents = $contentPath;

        $faqPath = null;
        if (isset($request->faqs)) {
            $faqPath = 'caricature/category/'.$res->fldr_str . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [
                'title' => $request->faqs_title,
                'faqs' => json_decode($request->faqs)
            ];
            StorageUtils::put($faqPath, json_encode($faqs));
        }

        $res->faqs = $faqPath;
        $res->child_updated_at = HelperController::caricatureChildUpdatedAt($res->parent_category_id);
        $res->save();

        return response()->json(['success' => "done"]);
    }

    public function updateChildCatLink($idName, $parentID): void
    {
        $childCategories = CaricatureCategory::where('parent_category_id', $parentID)->get();
        foreach ($childCategories as $childCat) {
            $childCat->cat_link = $idName . '/' . $childCat->id_name;
            $childCat->save();
        }
    }

    public static function updateParentChildRelation($childId, $newParentId, $oldParentId = null, $isUpdate = false): void
    {
        if ($isUpdate && $oldParentId !== null && $oldParentId != $newParentId) {
            $oldParent = CaricatureCategory::where('id', $oldParentId)
                ->where('parent_category_id', 0)
                ->where('child_cat_ids', 'like', '%' . $childId . '%')
                ->first();

            if ($oldParent) {
                $childIds = json_decode($oldParent->child_cat_ids, true) ?? [];

                if (($key = array_search($childId, $childIds)) !== false) {
                    unset($childIds[$key]);
                    $oldParent->child_cat_ids = json_encode(array_values($childIds)); // Reindex
                    $oldParent->save();
                }
            }
        }

        if (!empty($newParentId) && $newParentId != 0) {
            $newParent = CaricatureCategory::find($newParentId);
            if ($newParent) {
                $childIds = json_decode($newParent->child_cat_ids, true) ?? [];
                if (!in_array($childId, $childIds)) {
                    $childIds[] = $childId;
                    $newParent->child_cat_ids = json_encode($childIds);
                    $newParent->save();
                }
            }
        }
    }
    public static function getTotalTemplates($id)
    {
        $designCount = Attire::where('category_id', $id)->whereStatus("1")->count();
        return $designCount;
    }

    public function edit(CaricatureCategory $caricatureCategory, $id)
    {
        $res = CaricatureCategory::find($id);
        if (!$res) {
            abort(404);
        }
        if (isset($res->top_keywords)) {
            $res->top_keywords = json_decode($res->top_keywords);
        } else {
            $res->top_keywords = [];
        }

        $allCategories = CaricatureCategory::getAllCategoriesWithSubcategories();

        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";
        $datas['cat'] = $res;
        $datas['allCategories'] = $allCategories;
        $datas['parent_category'] = CaricatureCategory::where('id', $datas['cat']->parent_category_id)->first();
        $userRole = User::where('user_type', 5)->get();
        return view('caricature_cat/edit_caricature_cat')
            ->with('datas', $datas)
            ->with('userRole', $userRole);
    }


    public function destroy(CaricatureCategory $mainCategory, $id): Redirector|Application|RedirectResponse
    {
        return redirect('show_cari_cat');
    }

}
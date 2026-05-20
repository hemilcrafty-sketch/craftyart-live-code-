<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\Category;
use App\Models\NewCategoryPending;
use App\Models\PendingTask;
use App\Models\User;
use App\Models\VirtualCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Cache;

class NewCategoryController extends AppBaseController
{
    public function show(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'id_name', 'value' => 'ID Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'status', 'value' => 'Status'],
            ['id' => 'no_index', 'value' => 'No Index'],
        ];

        $catArray = $this->applyFiltersAndPagination(
            $request,
            NewCategory::query(),
            $searchableFields,
            [
                'parent_query' => NewCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'parent_category_id',
            ]
        );
        return view('main_new_cat.show_new_cat', compact('catArray', 'searchableFields'));
    }

    public function create()
    {
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $appArray = AppCategory::all();
        $userRole = User::where('user_type', 5)->get();
        return view('main_new_cat/create_new_cat', compact('appArray', 'allCategories', 'userRole'));
    }

    public function store(Request $request)
    {
        $accessCheck = $this->isAccessByRole("seo_all");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        if (HelperController::checkCategoryAvail(0, $request->input('category_name'), $request->input('id_name'), $request->input('parent_category_id'))) {
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }

        $cat = NewCategory::find($request->input('parent_category_id'));
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

        // $validationError = ContentManager::validateMultipleImageFiles(
        //     [
        //         ["file" => $request->file('category_thumb'), "name" => "Category Thumb", "required" => true],
        //         ["file" => $request->file('mockup'), "name" => "Mockup", "required" => true],
        //         ["file" => $request->file('banner'), "name" => "Banner", "required" => false],
        //     ]
        // );

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
            $idName = NewCategory::where('id', $request->input('parent_category_id'))->value('id_name') ?? '';
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
        $res = new NewCategory;
        // }

        $res->string_id = $this->generateId();
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

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        // $banner = $request->file('banner');
        // if ($banner) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/thumb_file', $new_name);
        //     $res->banner = 'uploadedFiles/thumb_file/' . $new_name;
        // }


        // $res->size = $request->input('size');
        // $image = $request->file('category_thumb');
        // $bytes = random_bytes(20);
        // $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        // StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        // $res->category_thumb = 'uploadedFiles/thumb_file/' . $new_name;

        // $mockup = $request->file('mockup');
        // $bytes = random_bytes(20);
        // $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
        // StorageUtils::storeAs($mockup, 'uploadedFiles/thumb_file', $new_name);
        // $res->mockup = 'uploadedFiles/thumb_file/' . $new_name;

        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = json_encode(HelperController::processCTA($request));
        $res->sequence_number = $request->input('sequence_number');
        $res->status = !RoleManager::isSeoIntern(Auth::user()->user_type) ? $request->input('status') : 0;
        $res->parent_category_id = $request->input('parent_category_id');

        $res->emp_id = auth()->user()->id;

        $res->seo_emp_id = $request->input('seo_emp_id'); // Store as plain integer


        $fldrStr = HelperController::generateFolderID('');
        while (NewCategory::where('fldr_str', $fldrStr)->exists()) {
            $fldrStr = HelperController::generateFolderID('');
        }

        $res->fldr_str = $fldrStr;

        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        //        if ($request->input('faqs')) {
//            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
//            StorageUtils::put($faqPath, $request->input('faqs'));
//            $res->faqs = $faqPath;
//        }
        if (isset($request->faqs)) {
            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

        $res->child_updated_at = HelperController::newCatChildUpdatedAt($res->parent_category_id);

        PendingTaskController::store($res, 'NewCategory', 'New Category', 'New Category Add', "new_cat", 'add', null, RoleManager::isAdminOrSeoManager(Auth::user()->user_type));

        // $res->save();

        self::updateParentChildRelation($res->id, $res->parent_category_id);

        // if ($image != null) {
        //     try {
        //         unlink(storage_path("app/public/" . $request->input('cat_thumb_path')));
        //     } catch (\Exception $e) {
        //     }
        // }

        Cache::tags(["category_$slug"])->flush();
        Cache::tags(["category_$res->id"])->flush();
        Cache::tags(["category_$res->id_name"])->flush();
        Cache::tags(['categories'])->flush();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function preview($mode, $id)
    {

        $pendingData = PendingTask::findOrFail($id);
        $res = json_decode($pendingData->data);
        if (!$res) {
            abort(404);
        }
        if (isset($res->top_keywords)) {
            $res->top_keywords = json_decode($res->top_keywords);
        } else {
            $res->top_keywords = [];
        }
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $datas['app'] = AppCategory::all();
        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";
        $datas['cat'] = $res;
        $datas['allCategories'] = $allCategories;
        $datas['parent_category'] = NewCategory::where('id', $datas['cat']->parent_category_id)->first();
        $userRole = User::where('user_type', 5)->get();
        $changelog = json_decode($pendingData->change_log ?? '[]');

        return view('main_new_cat.edit_new_cat')
            ->with('datas', $datas)
            ->with('userRole', $userRole)
            ->with('previewId', $id)
            ->with('previewStatus', $pendingData->status)
            ->with('changeLog', $changelog)
            ->with('isDisableAll', $mode == 'preview')
            ->with('previewMode', true);
    }

    public function edit(NewCategory $mainCategory, $id)
    {
        $res = NewCategory::find($id);
        if (!$res) {
            abort(404);
        }
        if (isset($res->top_keywords)) {
            $res->top_keywords = json_decode($res->top_keywords);
        } else {
            $res->top_keywords = [];
        }

        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        $datas['app'] = AppCategory::all();
        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";
        $datas['cat'] = $res;
        $datas['allCategories'] = $allCategories;
        $datas['parent_category'] = NewCategory::where('id', $datas['cat']->parent_category_id)->first();
        $userRole = User::where('user_type', 5)->get();

        return view('main_new_cat/edit_new_cat')
            ->with('datas', $datas)
            ->with('userRole', $userRole);
    }
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function getDecodedFileContent($path)
    {
        if (!$path || !Storage::exists($path)) {
            return null;
        }

        $content = Storage::get($path);
        $decoded = json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    public static function generateChangeLog($oldData, $newData, $parentKey = null)
    {
        if ($newData instanceof \Illuminate\Database\Eloquent\Model) {
            $newData = $newData->toArray();
        }
        if ($oldData instanceof \Illuminate\Database\Eloquent\Model) {
            $oldData = $oldData->toArray();
        }

        $changeLog = [];

        if (is_array($newData) || is_object($newData)) {
            if (key((array) $newData) !== 0) {
                foreach ($newData as $key => $newValue) {
                    $oldValue = (is_array($oldData) || is_object($oldData)) ? ($oldData[$key] ?? null) : null;

                    $fullKey = $parentKey ? "$parentKey.$key" : $key;

                    // ✅ Handle contents/faqs file path comparison by content
                    if (in_array($key, ['contents', 'faqs'])) {
                        $newDecoded = self::getDecodedFileContent($newValue);
                        $oldDecoded = self::getDecodedFileContent($oldValue);

                        if (json_encode($newDecoded) !== json_encode($oldDecoded)) {
                            $changeLog[] = [
                                'key' => $fullKey,
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }

                        continue;
                    }

                    // Decode if it's JSON
                    if (is_string($newValue) && self::isJson($newValue)) {
                        $newValue = json_decode($newValue, true);
                    }

                    if (is_string($oldValue) && self::isJson($oldValue)) {
                        $oldValue = json_decode($oldValue, true);
                    }

                    // Recursively handle arrays/objects
                    if ($newValue !== null && $oldValue !== null) {
                        if (is_array($newValue) || is_object($newValue)) {
                            $nestedChange = self::generateChangeLog($oldValue, $newValue, $fullKey);
                            $changeLog = array_merge($changeLog, $nestedChange);
                        } elseif ($oldValue != $newValue) {
                            $changeLog[] = [
                                'key' => $fullKey,
                                'old' => $oldValue,
                                'new' => $newValue
                            ];
                        }
                    } elseif ($newValue === null && $oldValue !== null) {
                        $changeLog[] = [
                            'key' => $fullKey,
                            'old' => $oldValue,
                            'new' => null
                        ];
                    } elseif ($oldValue === null && $newValue !== null) {
                        $changeLog[] = [
                            'key' => $fullKey,
                            'old' => null,
                            'new' => $newValue
                        ];
                    }
                }
            } else {
                // Indexed array or list: do direct JSON comparison
                $newJson = json_encode($newData);
                $oldJson = json_encode($oldData);
                if ($newJson !== $oldJson) {
                    $changeLog[] = [
                        'key' => $parentKey,
                        'old' => $oldJson,
                        'new' => $newJson
                    ];
                }
            }
        } else {
            // Primitive comparison
            if ($oldData != $newData) {
                $changeLog[] = [
                    'key' => $parentKey,
                    'old' => $oldData,
                    'new' => $newData
                ];
            }
        }

        return $changeLog;
    }

    public function update(Request $request, NewCategory $mainCategory)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdmin(Auth::user()->user_type);

        if (isset($request->isPreview)) {
            $pendingData = PendingTask::find($request->id);
            $res = json_decode($pendingData->data);
            $oldData = clone $res;
        } else {
            $res = NewCategory::find($request->id);
            $oldData = clone $res;
        }

        if (!$res) {
            return response()->json(['error' => 'Page not found']);
        }

        $slug = '';
        if ($request->input('parent_category_id') != null && $request->input('parent_category_id') != 0) {
            $idName = NewCategory::where('id', $request->input('parent_category_id'))->value('id_name') ?? '';
            if (isset($idName))
                $slug = $idName . "/" . $request->input('id_name');
        } else {
            $slug = $request->input('id_name');
        }

        $canonical_link = $request->input('canonical_link');
        if (HelperController::checkCategoryAvail($res->id ?? 0, $request->input('category_name'), $request->input('id_name'), $request->input('parent_category_id'))) {
            return response()->json(['error' => 'Category Name or Id Name Already exist.']);
        }

        if (HelperController::isCategoryInPending($request->input('id_name'))) {
            return response()->json([
                'error' => 'Category Name or Id Name Already in pending task.'
            ]);
        }

        $cat = NewCategory::find($request->input('parent_category_id'));
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

                $seoIds = $request->input('seo_emp_id');
                $res->seo_emp_id = $seoIds;

                $parentCategoryId = NewCategory::select('parent_category_id as value')->where('id', $request->input('parent_category_id'))->first();
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

        $cat = NewCategory::find($request->input('parent_category_id'));
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

        if (!RoleManager::isSeoIntern(Auth::user()->user_type) && !isset($request->isPreview)) {
            $checkStatusError = HelperController::checkStatusCondition($request->id, $request->input('status'), 1, $request->parent_category_id);
            if ($checkStatusError) {
                return response()->json(['error' => $checkStatusError]);
            }
        }

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

        Cache::tags(["category_$res->cat_link"])->flush();
        Cache::tags(["category_$res->id_name"])->flush();

        $oldIdName = $res->id_name;

        if ($idAdmin) {
            $res->id_name = $request->input('id_name');
        }

        if ($canonical_link && $idAdmin) {
            $res->canonical_link = $canonical_link;
        }

        $oldParentCategoryID = $res->parent_category_id;

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

        $parentCategoryId = NewCategory::select('parent_category_id as value')->where('id', $request->input('parent_category_id'))->first();
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

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $seoIds = $request->input('seo_emp_id');
            // $seoIds = is_array($seoIds) ? array_map('intval', $seoIds) : [];
            $res->seo_emp_id = $seoIds;
        }

        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = json_encode(HelperController::processCTA($request));
        $res->sequence_number = $request->input('sequence_number');

        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        }

        $res->parent_category_id = $request->input('parent_category_id');
        // $res->emp_id = auth()->user()->id;

        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = HelperController::generateFolderID('');
            while (NewCategory::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateFolderID('');
            }

            $res->fldr_str = $fldrStr;
        }
        
        $oldContentPath = $res->contents ?? null;
        $oldFaqPath = $res->faqs;

        $contentPath = null;
        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
        }
        $res->contents = $contentPath;

        $faqPath = null;
        if (isset($request->faqs)) {
            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [
                'title' => $request->faqs_title,
                'faqs' => json_decode($request->faqs)
            ];
            StorageUtils::put($faqPath, json_encode($faqs));
        }

        $res->faqs = $faqPath;
        $res->child_updated_at = HelperController::newCatChildUpdatedAt($res->parent_category_id);

        // Generate Change Log
        $oldArray = $oldData instanceof \Illuminate\Database\Eloquent\Model ? $oldData->toArray() : (array) $oldData;
        $newArray = $res instanceof \Illuminate\Database\Eloquent\Model ? $res->toArray() : (array) $res;
        $changeLog = self::generateChangeLog($oldArray, $newArray);

        if (isset($request->isPreview)) {
            return PendingTaskController::updatePendingTask($request->id, $res, $changeLog);
        } else {
            $response = PendingTaskController::store(
                $res,
                'NewCategory',
                'New Category',
                'New Category edit',
                'new_cat',
                'update',
                $res->id,
                RoleManager::isAdminOrSeoManager(Auth::user()->user_type),
                "",
                1,
                $changeLog
            );


            // If store() returns a JSON response, return it immediately
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
                self::handlePostSaveActions($res, $oldParentCategoryID, $oldContentPath, $oldFaqPath);
            }
        }
        return response()->json(['success' => "done"]);
    }

    // In HelperController or any relevant controller
    public static function handlePostSaveActions($res, $oldParentCategoryID, $oldContentPath = null, $oldFaqPath = null)
    {
        $slug = $res->slug ?? null;
        self::updateParentChildRelation($res->id, $res->parent_category_id, $oldParentCategoryID, true);

        self::updateDesignCount(
            $res->parent_category_id != null && $res->parent_category_id != 0 ? $res->parent_category_id : $res->id,
            $oldParentCategoryID,
            $res->id
        );

        // Delete old files if provided
        if ($oldContentPath) {
            StorageUtils::delete($oldContentPath);
        }

        if ($oldFaqPath) {
            StorageUtils::delete($oldFaqPath);
        }
        if ($slug) {
            Cache::tags(["category_$slug"])->flush();
        }
        Cache::tags(["category_$res->id"])->flush();
        Cache::tags(["category_$res->id_name"])->flush();
        Cache::tags(['categories'])->flush();
    }


    public function updateChildCatLink($idName, $parentID)
    {
        $childCategories = NewCategory::where('parent_category_id', $parentID)->get();
        foreach ($childCategories as $childCat) {
            $childCat->cat_link = $idName . '/' . $childCat->id_name;
            $childCat->save();
        }
    }

    public function imp_update(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);

        if ($idAdmin) {

            if ($request->isNew == "1") {
                $res = NewCategory::find($request->id);
            } else if ($request->isVirtual == "1") {
                $res = VirtualCategory::find($request->id);
            } else {
                $res = Category::find($request->id);
            }

            if ($res->imp == 1) {
                $res->imp = 0;
            } else {
                $res->imp = 1;
            }

            $res->save();

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Ask admin or manager for changes"
            ]);
        }
    }


    public function destroy(NewCategory $mainCategory, $id)
    {
        // $res=NewCategory::find($id);
        // $category_thumb = $res->category_thumb;
        // $contains = Str::contains($category_thumb, 'no_image');

        // if(!$contains) {
        //     try {
        //         unlink(storage_path("app/public/".$category_thumb));
        //     } catch (\Exception $e) {}
        // }

        // NewCategory::destroy(array('id', $id));
        return redirect('show_new_cat');
    }

    public function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (NewCategory::where('string_id', $string_id)->exists());
        return $string_id;
    }

    public static function updateParentChildRelation($childId, $newParentId, $oldParentId = null, $isUpdate = false): void
    {
        if ($isUpdate && $oldParentId !== null && $oldParentId != $newParentId) {
            $oldParent = NewCategory::where('id', $oldParentId)
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
            $newParent = NewCategory::find($newParentId);
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



    public static function updateDesignCount($newParentCatId = null, $oldParentCatID = null): void
    {
        if (!is_null($oldParentCatID) && $oldParentCatID != 0) {
            self::recalculateParentCategoryDesignCount($oldParentCatID);
        }

        if (!is_null($newParentCatId) && $newParentCatId != 0 && $newParentCatId !== $oldParentCatID) {
            self::recalculateParentCategoryDesignCount($newParentCatId);
        }
    }

    public static function getTotalTemplates($id)
    {
        $designCount = Design::where('new_category_id', $id)->whereStatus("1")->count();
        return $designCount;
    }

    protected static function recalculateParentCategoryDesignCount($parentCategoryId)
    {
        $parent = NewCategory::find($parentCategoryId);

        if (!$parent)
            return;

        $childCategoryIds = NewCategory::where('parent_category_id', $parentCategoryId)->pluck('id')->toArray();

        if (empty($childCategoryIds)) {
            $parent->total_templates = 0;
        } else {
            // Count all Designs assigned to these child categories
            $designCount = Design::whereIn('new_category_id', $childCategoryIds)->count();
            $parent->total_templates = $designCount;
        }

        $parent->save();
    }

    // public static function updateDesignCount($newParentCatId, $oldParentCatID, $totalTemplates): void
    // {
    //     if ($newParentCatId !== $oldParentCatID) {
    //         if ($oldParentCatID !== 0) {
    //             $oldParent = NewCategory::where('id', $oldParentCatID)->first();
    //             if ($oldParent) {
    //                 $oldParent->total_templates = $oldParent->total_templates - $totalTemplates;
    //                 $oldParent->save();
    //             }

    //         }
    //         if ($newParentCatId !== 0) {
    //             $newParent = NewCategory::where('id', $newParentCatId)->first();
    //             if ($newParent) {
    //                 $newParent->total_templates = $newParent->total_templates + $totalTemplates;
    //                 $newParent->save();
    //             }
    //         }
    //     }
    // }


}
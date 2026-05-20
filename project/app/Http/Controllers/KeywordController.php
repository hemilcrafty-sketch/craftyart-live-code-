<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\SpecialKeyword;
use App\Models\NewCategory;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Cache;

class KeywordController extends AppBaseController
{

    public function create(): Factory|View|Application
    {
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        return view('filters/create_keyword', compact('allCategories'));
    }
    public function edit($id): Factory|View|Application
    {
        $specialKeyword = SpecialKeyword::whereId($id)->first();
        if (!$specialKeyword) {
            abort(404);
        }

        $allCategories = NewCategory::getAllCategoriesWithSubcategories();

        if (isset($specialKeyword->top_keywords)) {
            $specialKeyword->top_keywords = json_decode($specialKeyword->top_keywords);
        } else {
            $specialKeyword->top_keywords = [];
        }

        $datas['allCategories'] = $allCategories;

        $specialKeyword->contents = isset($specialKeyword->contents) ? StorageUtils::get($specialKeyword->contents) : "";
        $specialKeyword->faqs = isset($specialKeyword->faqs) ? StorageUtils::get($specialKeyword->faqs) : "";

        $datas['data'] = $specialKeyword;
        $datas['cat'] = NewCategory::find($specialKeyword->cat_id);

        return view('filters/edit_keyword')->with('datas', $datas);

    }


    public function show(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'title', "value" => 'Title'],
            ["id" => 'meta_title', "value" => 'Meta Title'],
            ["id" => 'meta_desc', "value" => 'Meta Desc'],
            ["id" => 'short_desc', "value" => 'Short Desc'],
            ["id" => 'status', "value" => 'Status'],
            ["id" => 'no_index', "value" => 'No Index'],
        ];

        $noIndexStats = $this->noIndexListingStats(SpecialKeyword::class);

        $query = SpecialKeyword::query();
        $this->applyNoIndexFilter($request, $query);

        $keywordArray = $this->applyFiltersAndPagination($request, $query, $searchableFields);
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();

        return view('filters/special_keywords', compact('allCategories', 'keywordArray', 'searchableFields', 'noIndexStats'));
    }


    public function add(Request $request): ?JsonResponse
    {
        $accessCheck = $this->isAccessByRole("seo");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $data = SpecialKeyword::where("name", $request->input('name'))->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Keyword Already exist.'
            ]);
        }

        $name = $request->input('name');
        $canonical_link = $request->input('canonical_link');

        if ($this->checkName($name)) {
            return $this->checkName($name);
        }

        // if ($this->checkName($canonical_link)) {
        //     return $this->checkName($canonical_link);
        // }

        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 3, $name);
        if ($canonicalError) {
            return response()->json([
                'error' => $canonicalError
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->banner, 'name' => "Banner", 'required' => false]];
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

        $checkStatusError = HelperController::checkStatusCondition($request->category_id, $request->input('status'), 2);
        if ($checkStatusError) {
            return response()->json([
                'error' => $checkStatusError
            ]);
        }

        // $validationError = ContentManager::validateMultipleImageFiles(
        //     [
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

        $res = new SpecialKeyword;
        $res->name = $name;
        $res->canonical_link = $canonical_link;
        $res->cat_id = $request->input('category_id');
        $res->string_id = $this->generateId();
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->title = $request->input('title');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = "";

        //        $res->status = $request->input('status');
        $res->status = !RoleManager::isSeoIntern(Auth::user()->user_type) ? $request->input('status') : 0;
        $res->priority = $request->input('priority', 0.90);
        $res->frequency = $request->input('frequency', 'daily');

        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        // $banner = $request->file('banner');
        // if ($banner != null) {
        //     $new_name = StorageUtils::getNewName() . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/thumb_file', $new_name);
        //     $res->banner = 'uploadedFiles/thumb_file/' . $new_name;
        // }

        $fldrStr = HelperController::generateFolderID('');
        while (SpecialKeyword::where('fldr_str', $fldrStr)->exists()) {
            $fldrStr = HelperController::generateFolderID('');
        }

        $res->fldr_str = $fldrStr;

        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'k/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        //        if ($request->input('faqs')) {
//            $faqPath = 'k/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
//            StorageUtils::put($faqPath, $request->input('faqs'));
//            $res->faqs = $faqPath;
//        }

        if (isset($request->faqs)) {
            $faqPath = 'k/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

        $res->save();

        Cache::tags(["kp_$res->name"])->flush();

        HelperController::newCatChildUpdatedAt($res->cat_id);
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function get(Request $request): JsonResponse
    {

        $res = SpecialKeyword::find($request->id);
        if ($res) {
            return response()->json([
                'success' => $res
            ]);


        } else {
            return response()->json([
                'error' => 'No data found.'
            ]);
        }

    }

    public function update(Request $request, SpecialKeyword $specialKeyword): ?JsonResponse
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdmin(Auth::user()->user_type);

        $checkStatusError = HelperController::checkStatusCondition($request->category_id, $request->input('status'), 2);
        if ($checkStatusError) {
            return response()->json([
                'error' => $checkStatusError
            ]);
        }

        $data = SpecialKeyword::where("id", '!=', $request->id)->where("name", $request->input('name'))->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Keyword Already exist.'
            ]);
        }

        $res = SpecialKeyword::find($request->id);
        // dd($request->id);
        if (!$res) {
            return response()->json([
                'error' => 'Page not found',
            ]);
        }
        $name = $request->input('name');
        // dd($name);
        $canonical_link = $request->input('canonical_link');

        if (!roleManager::isAdmin(Auth::user()->user_type) && roleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            if (roleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
                $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 3, $name);
                if ($canonicalError) {
                    return response()->json([
                        'error' => $canonicalError
                    ]);
                } else {
                    $res->canonical_link = $canonical_link;
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
                    
                    $res->cat_id = $request->input('category_id');

                    $res->save();
                    return response()->json([
                        'success' => 'done',
                    ]);
                }

            }
            return response()->json([
                'error' => 'You don\'t have rights to change this page',
            ]);
        }


        $seoAssignerID = $res->newCategory->seo_emp_id ?? 0;
        // dd($seoAssignerID);


        $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id ?? $currentuserid, [$seoAssignerID]);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }



        if ($this->checkName($name)) {
            return $this->checkName($name);
        }

        //        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 3, $name);
//        if ($canonicalError) {
//            return response()->json([
//                'error' => $canonicalError
//            ]);
//        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->banner, 'name' => "Banner", 'required' => false]];
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


        Cache::tags(["kp_$res->name"])->flush();
        if ($idAdmin) {
            $res->name = $name;
        }

        if ($canonical_link && $idAdmin) {
            $res->canonical_link = $canonical_link;
        }

        $res->name = $name;
        $res->cat_id = $request->input('category_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->title = $request->input('title');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = json_encode(HelperController::processCTA($request));
        if (!roleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        }
        $res->priority = $request->input('priority', $res->priority ?? 0.90);
        $res->frequency = $request->input('frequency', $res->frequency ?? 'daily');
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = HelperController::generateFolderID('');
            while (SpecialKeyword::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateFolderID('');
            }

            $res->fldr_str = $fldrStr;
        }

        $oldContentPath = $res->contents;
        $oldFaqPath = $res->faqs;

        $contentPath = null;
        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'k/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
        }
        $res->contents = $contentPath;

        $faqPath = null;
        //        if ($request->input('faqs')) {
//            $faqPath = 'k/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
//            StorageUtils::put($faqPath, $request->input('faqs'));
//        }
        if (isset($request->faqs)) {
            $faqPath = 'k/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
        }
        $res->faqs = $faqPath;


        $res->save();
        Cache::tags(["kp_$res->name"])->flush();

        HelperController::newCatChildUpdatedAt($res->cat_id);

        StorageUtils::delete($oldContentPath);
        StorageUtils::delete($oldFaqPath);

        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function delete(Request $request, SpecialKeyword $specialKeyword): JsonResponse
    {
        // SpecialKeyword::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    private function checkName($name): ?JsonResponse
    {
        if (preg_match('/\s/', $name)) {
            return response()->json([
                'error' => 'Name whitespace.'
            ]);
        }

        if (preg_match('/[A-Z]/', $name)) {
            return response()->json([
                'error' => 'Name contains a capital word.'
            ]);
        }

        if (preg_match('/[^\w\s-]/', $name)) {
            return response()->json([
                'error' => 'Name contains a special character.'
            ]);
        }
        return null;
    }

    public function generateId($length = 8): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (SpecialKeyword::where('string_id', $string_id)->exists());
        return $string_id;
    }

}

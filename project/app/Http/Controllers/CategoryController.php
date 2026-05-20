<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends AppBaseController
{


    public function index()
    {

    }

    public function create()
    {

        $appArray = AppCategory::all();
        $assignSubCat = User::where('user_type', 5)->get();
        return view('main_cat/create_cat', compact('appArray', 'assignSubCat'));
    }

    public function store(Request $request)
    {

        // $data = Category::where("category_name", $request->input('category_name'))->first();
        // if ($data != null) {
        //     return response()->json([
        //         'error' => 'Category Already exist.'
        //     ]);
        // }

        // $data = Category::where("id_name", $request->input('id_name'))->first();
        // if ($data != null) {
        //     return response()->json([
        //         'error' => 'ID Name Already exist.'
        //     ]);
        // }

        $accessCheck = $this->isAccessByRole("seo");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        if (HelperController::checkCategoryAvail($request->id, $request->input('category_name'), $request->input('id_name'))) {
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => true], ['img' => $request->banner, 'name' => 'Banner', 'required' => false]];
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

        $canonical_link = $request->input('canonical_link');
        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $request->input('id_name'));
        if ($canonicalError) {
            return response()->json([
                'error' => $canonicalError
            ]);
        }

        $res = new Category;
        $res->emp_id = auth()->user()->id;
        $res->seo_emp_id = $request->seo_emp_id ?? 0;
        $res->canonical_link = $canonical_link;
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->string_id = $this->generateId();
        $res->tag_line = $request->input('tag_line');
        $res->meta_title = $request->input('meta_title');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->size = $request->input('size');

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        // $banner = $request->file('banner');
        // if ($banner) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/thumb_file', $new_name);
        //     $res->banner = 'uploadedFiles/thumb_file/' . $new_name;
        // }

        // $image = $request->file('category_thumb');
        // $bytes = random_bytes(20);
        // $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        // StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        // $res->category_thumb = 'uploadedFiles/thumb_file/' . $new_name;

        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = json_encode(HelperController::processCTA($request));
        $res->sequence_number = $request->input('sequence_number');
        //        $res->status = $request->input('status');
        $res->status = !roleManager::isSeoIntern(Auth::user()->user_type) ? $request->input('status') : 0;
        $fldrStr = HelperController::generateFolderID('');
        while (Category::where('fldr_str', $fldrStr)->exists()) {
            $fldrStr = HelperController::generateFolderID('');
        }

        $res->fldr_str = $fldrStr;

        if ($request->input('contents')) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }

        if (isset($request->faqs)) {
            $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
            $res->faqs = $faqPath;
        }

        $res->save();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function show(Request $request): \Illuminate\Contracts\View\View
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'Id'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'id_name', 'value' => 'ID Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $catArray = $this->applyFiltersAndPagination($request, Category::with('assignedSeo'), $searchableFields);

        return view('main_cat.show_cat', compact('catArray', 'searchableFields'));
    }

    public function edit(Category $mainCategory, $id)
    {
        $res = Category::find($id);
        if (!$res) {
            abort(404);
        }

        if (isset($res->top_keywords)) {
            $res->top_keywords = json_decode($res->top_keywords);
        } else {
            $res->top_keywords = [];
        }

        $datas['app'] = AppCategory::all();
        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";
        $datas['cat'] = $res;
        $datas['parent_category'] = Category::where('id', $datas['cat']->parent_category_id)->first();
        $assignSubCat = User::where('user_type', 5)->get();

        return view('main_cat/edit_cat')
            ->with('datas', $datas)
            ->with('assignSubCat', $assignSubCat);

    }

    public function update(Request $request, Category $mainCategory)
    {

        $res = Category::find($request->id);
        $currentuserid = Auth::user()->id;
        if (!$res) {
            return response()->json(['error' => 'Page not found']);
        }

        if (HelperController::checkCategoryAvail($request->id, $request->input('category_name'), $request->input('id_name'))) {
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }
        $canonical_link = $request->input('canonical_link');
        if (!roleManager::isAdmin(Auth::user()->user_type) && RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            // if (roleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $request->input('id_name'));
            if ($canonicalError) {
                return response()->json([
                    'error' => $canonicalError
                ]);
            } else {
                $res->canonical_link = $request->canonical_link;
                if ($request->has('status')) {
                    $res->status = $request->status;
                }
                $res->save();
                return response()->json([
                    'success' => 'done',
                ]);
            }
            // }
            // return response()->json([
            //     'error' => 'You don\'t have rights to change this page',
            // ]);
        }

        $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id ?? $currentuserid, [$res['seo_emp_id']]);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents, $request->long_desc, $request->h2_tag);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => true], ['img' => $request->banner, 'name' => 'Banner', 'required' => false]];
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
        } else {
            unset($request->faqs);
        }

        // $validationError = ContentManager::validateMultipleImageFiles(
        //     [
        //         ["file" => $request->file('category_thumb'), "name" => "Category Thumb", "required" => false],
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


        //        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $request->input('id_name'));
//        if ($canonicalError) {
//            return response()->json([
//                'error' => $canonicalError
//            ]);
//        }

        $res = Category::find($request->id);
        if ($canonical_link && roleManager::isAdmin(Auth::user()->user_type)) {
            $res->canonical_link = $canonical_link;
        }

        if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
            $res->seo_emp_id = $request->seo_emp_id;
        }

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
        $res->size = $request->input('size');

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);


        // $image = $request->file('category_thumb');
        // if ($image != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        //     StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        //     $res->category_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        // }

        // $banner = $request->file('banner');
        // if ($banner != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/thumb_file', $new_name);
        //     StorageUtils::delete($res->banner);
        //     $res->banner = 'uploadedFiles/thumb_file/' . $new_name;
        // }

        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->cta = json_encode(HelperController::processCTA($request));
        $res->sequence_number = $request->input('sequence_number');
        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        }

        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = HelperController::generateFolderID('');
            while (Category::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateFolderID('');
            }

            $res->fldr_str = $fldrStr;
        }

        $oldContentPath = $res->contents;
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
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqPath, json_encode($faqs));
        }
        $res->faqs = $faqPath;

        // $res->emp_id = $currentuserid;
        $res->save();

        StorageUtils::delete($oldContentPath);
        StorageUtils::delete($oldFaqPath);

        // if ($image != null) {
        //     try {
        //         unlink(storage_path("app/public/" . $request->input('cat_thumb_path')));
        //     } catch (\Exception $e) {
        //     }
        // }

        return response()->json([
            'success' => "done"
        ]);
    }

    public function destroy(Category $mainCategory, $id)
    {
        // $res=Category::find($id);
        // $category_thumb = $res->category_thumb;
        // $contains = Str::contains($category_thumb, 'no_image');

        // if(!$contains) {
        //     try {
        //         unlink(storage_path("app/public/".$category_thumb));
        //     } catch (\Exception $e) {}
        // }

        // Category::destroy(array('id', $id));
        return redirect('show_cat');
    }

    public function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (Category::where('string_id', $string_id)->exists());
        return $string_id;
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\SpecialKeyword;
use App\Models\NewCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KeywordControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    }

    public function create()
    {
        $allCategories = NewCategory::getAllCategoriesWithSubcategories();
        return view('filters/create_keyword', compact('allCategories'));
    }
    public function edit($id)
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
        $specialKeyword->contents = StorageUtils::exists($specialKeyword->contents) ? file_get_contents(StorageUtils::get($specialKeyword->contents)) : "";
        $specialKeyword->faqs = StorageUtils::exists($specialKeyword->faqs) ? file_get_contents(StorageUtils::get($specialKeyword->faqs)) : "";
        $datas['data'] = $specialKeyword;
        $datas['cat'] = NewCategory::find($specialKeyword->cat_id);

        return view('filters/edit_keyword')->with('datas', $datas);

    }


    public function show(SpecialKeyword $specialKeyword)
    {
        return view('filters/special_keywords')->with('keywordArray', SpecialKeyword::all());
    }

    public function add(Request $request)
    {


        $data = SpecialKeyword::where("name", $request->input('name'))->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Keyword Already exist.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents,$request->long_desc,$request->h2_tag);
        if ($contentError){
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img'=> $request->banner,'name' => "Banner",'required'=>false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }


        // $validationError = ContentManager::validateMultipleImageFiles([$request->file('banner')]);
        // if ($validationError) {
        //     return response()->json([
        //         'error' => $validationError
        //     ]);
        // }
        $name = $request->input('name');
        $canonical_link = $request->input('canonical_link');

        if ($this->checkName($name)) {
            return $this->checkName($name);
        }

//        if ($this->checkName($canonical_link)) {
//            return $this->checkName($canonical_link);
//        }

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

        // $banner = $request->file('banner');

        // if ($banner != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/banner_file', $new_name);
        //     $res->banner = 'uploadedFiles/banner_file/' . $new_name;
        // }
        $res->banner = ContentManager::saveImageToPath($request->banner,'uploadedFiles/banner_file/'.bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->name = $name;
        $fldrStr = HelperController::generateFolderID('', 10);
        $res->fldr_str = $fldrStr;
        $contents = ContentManager::getContents($request->input('contents'), $fldrStr, [], []);
        $contentPath = 'ct/' . uniqid() . ".json";
        StorageUtils::put($contentPath, $contents);
        $res->contents = $contentPath;
        $faqsPath = null;
        if (!empty($request->input('faqs'))) {
            $faqsPath = 'faqs/' . uniqid() . ".json";
            StorageUtils::put($faqsPath, $request->input('faqs'));
        }
        $res->faqs = $faqsPath;
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
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function get(Request $request)
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

    public function update(Request $request, SpecialKeyword $specialKeyword)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = HelperController::isAdmin($currentuserid);

        $data = SpecialKeyword::where("id", '!=', $request->id)->where("name", $request->input('name'))->first();
        if ($data != null) {
            return response()->json([
                'error' => 'Keyword Already exist.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents,$request->long_desc,$request->h2_tag);
        if ($contentError){
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents),['img'=> $request->banner,'name' => "Banner",'required'=>false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        // $validationError = ContentManager::validateMultipleImageFiles([$request->file('banner')]);
        // if ($validationError) {
        //     return response()->json([
        //         'error' => $validationError
        //     ]);
        // }

        $name = $request->input('name');
        $canonical_link = $request->input('canonical_link');

        if ($this->checkName($name)) {
            return $this->checkName($name);
        }

        if ($this->checkName($canonical_link)) {
            return $this->checkName($canonical_link);
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

        $res = SpecialKeyword::find($request->id);
        if ($idAdmin) {
            $res->name = $name;
            $res->canonical_link = $canonical_link;
        }
        $res->banner = ContentManager::saveImageToPath($request->banner,'uploadedFiles/banner_file/'.bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        // $banner = $request->file('banner');
        // if ($banner != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/banner_file', $new_name);
        //     $res->banner = 'uploadedFiles/banner_file/' . $new_name;
        // }
        // if ($banner != null) {
        //     try {
        //         StorageUtils::delete($request->input('banner_path'));
        //     } catch (\Exception $e) {
        //     }
        // }

        $availableImage = [];
        $availableVideo = [];
        $availableContent = SpecialKeyword::where('id', $request->id)->value('contents');
        $contentsArray = json_decode($availableContent, true);

        if (!empty($contentsArray)) {
            foreach ($contentsArray as $content) {
                if ($content['type'] == 'content') {
                    foreach ($content['value'] as $key => $item) {
                        if (isset($key) && $key == 'video') {
                            $availableVideo[] = $item['link'];
                        }
                        if (isset($key) && $key == 'images') {
                            $availableImage[] = $item['link'];
                        }
                    }
                } else if ($content['type'] == 'ads') {
                    if (isset($content['value']['image'])) {
                        $availableImage[] = $content['value']['image'];
                    }
                }
            }
        }
        $fldrStr = SpecialKeyword::where('id', $request->id)->value('fldr_str');
        if ($fldrStr == null || $fldrStr == "") {
            $fldrStr = HelperController::generateFolderID('', 10);
        }
        $contentPath = $res->contents;
        if (isset($request->contents)) {
            $contents = ContentManager::getContents($request->contents, $fldrStr, $availableImage, $availableVideo);

            if (!isset($contentPath)) {
                $contentPath = 'ct/' . uniqid() . ".json";
            }
            StorageUtils::put($contentPath, $contents);
            $res->contents = $contentPath;
        }
        $faqsPath = $res->faqs;
        if (isset($request->faqs)) {
            if (!isset($faqsPath)) {
                $faqsPath = 'faqs/' . uniqid() . ".json";
            }
            StorageUtils::put($faqsPath, $request->input('faqs'));
            $res->faqs = $faqsPath;
        }

        if ($fldrStr != "") {
            $res->fldr_str = $fldrStr;
        }
        // $res->faqs = $request->input('faqs');


        $res->cat_id = $request->input('category_id');
        $res->meta_title = $request->input('meta_title');
        $res->title = $request->input('title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->top_keywords = json_encode($topKeywords);
        // $res->cta = json_encode(HelperController::processCTA($request));
        $res->status = $request->input('status');
        $res->save();

        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function delete(Request $request, SpecialKeyword $specialKeyword)
    {
        // SpecialKeyword::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    private function checkName($name)
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

    public function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (SpecialKeyword::where('string_id', $string_id)->exists());
        return $string_id;
    }

}
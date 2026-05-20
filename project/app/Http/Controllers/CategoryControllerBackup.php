<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryControllerBackup extends Controller
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
        $appArray = AppCategory::all();
        return view('main_cat/create_cat', compact('appArray'));
    }

    public function store(Request $request)
    {
//        $data = Category::where("category_name", $request->input('category_name'))->first();
//        if ($data != null) {
//            return response()->json([
//                'error' => 'Category Already exist.'
//            ]);
//        }
//
//        $data = Category::where("id_name", $request->input('id_name'))->first();
//        if ($data != null) {
//            return response()->json([
//                'error' => 'ID Name Already exist.'
//            ]);
//        }

        if(HelperController::checkCategoryAvail(0,$request->input('category_name'),$request->input('id_name'))){
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents,$request->long_desc,$request->h2_tag);
        if ($contentError){
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents),['img'=> $request->category_thumb,'name' => "Category Thumb",'required'=>true],['img'=> $request->banner,'name' => 'Banner','required'=>false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        // $validationError = ContentManager::validateMultipleImageFiles([$request->file('category_thumb'), $request->file('banner')]);
        // if ($validationError) {
        //     return response()->json([
        //         'error' => $validationError
        //     ]);
        // }

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

        $res = new Category;
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
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->string_id = HelperController::generateID();
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->tag_line = $request->input('tag_line');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->size = $request->input('size');
        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb,'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner,'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        // $image = $request->file('category_thumb');
        // if ($image != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        //     StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        //     $res->category_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        // } else {
        //     $res->category_thumb = 'uploadedFiles/thumb_file/no_image.png';
        // }

        // $banner = $request->file('banner');
        // if ($banner != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/banner_file', $new_name);
        //     $res->banner = 'uploadedFiles/banner_file/' . $new_name;
        // }


        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');
        $res->status = $request->input('status');
        $res->save();

        // if ($image != null) {
        //     try {
        //         StorageUtils::delete($request->input('cat_thumb_path'));
        //     } catch (\Exception $e) {
        //     }
        // }

        return response()->json([
            'success' => "done"
        ]);
    }

    public function show(Category $mainCategory)
    {
        return view('main_cat/show_cat')->with('catArray', Category::all());
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
        // $res->contents = isset($res->contents) ? json_encode(json_decode(file_get_contents(StorageUtils::get($res->contents)), false)) : "";
        $res->contents = StorageUtils::exists($res->contents) ? file_get_contents(StorageUtils::get($res->contents)) : "";
        $res->faqs = StorageUtils::exists($res->faqs) ? file_get_contents(StorageUtils::get($res->faqs)) : "";
        $datas['cat'] = $res;
        $datas['parent_category'] = Category::where('id', $datas['cat']->parent_category_id)->first();
        return view('main_cat/edit_cat')->with('datas', $datas);
    }

    public function update(Request $request, Category $mainCategory)
    {

//        $data = Category::where('id', "!=", $request->id)->where("category_name", $request->input('category_name'))->first();
//        if ($data != null) {
//            return response()->json([
//                'error' => 'Category Already exist.'
//            ]);
//        }
//
//        $data = Category::where('id', "!=", $request->id)->where("id_name", $request->input('id_name'))->first();
//        if ($data != null) {
//            return response()->json([
//                'error' => 'ID Name Already exist.'
//            ]);
//        }

        if(HelperController::checkCategoryAvail($request->id,$request->input('category_name'),$request->input('id_name'))){
            return response()->json([
                'error' => 'Category Name or Id Name Already exist.'
            ]);
        }

        $contentError = ContentManager::validateContent($request->contents,$request->long_desc,$request->h2_tag);
        if ($contentError){
            return response()->json([
                'error' => $contentError
            ]);
        }


        $base64Images = [...ContentManager::getBase64Contents($request->contents),['img'=> $request->category_thumb,'name' => "Category Thumb",'required'=>true],['img'=> $request->banner,'name' => 'Banner','required'=>false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        // $validationError = ContentManager::validateMultipleImageFiles([$request->file('category_thumb'), $request->file('banner')]);
        // if ($validationError) {
        //     return response()->json([
        //         'error' => $validationError
        //     ]);
        // }

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

        $res = Category::find($request->id);
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');

        $res->meta_title = $request->input('meta_title');
        $res->tag_line = $request->input('tag_line');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');

        $availableImage = [];
        $availableVideo = [];
        $availableContent = Category::where('id', $request->id)->value('contents');
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
        $fldrStr = Category::where('id', $request->id)->value('fldr_str');
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
        $res->size = $request->input('size');

        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb,'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner,'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

        // $image = $request->file('category_thumb');
        // if ($image != null) {
        //     // $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        //     StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
        //     $res->category_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        // }

        // $banner = $request->file('banner');
        // if ($banner != null) {
        //     $bytes = random_bytes(20);
        //     $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
        //     StorageUtils::storeAs($banner, 'uploadedFiles/banner_file', $new_name);
        //     $res->banner = 'uploadedFiles/banner_file/' . $new_name;
        // }

        $res->app_id = $request->input('app_id');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();

        // if ($image != null) {
        //     try {
        //         StorageUtils::delete($request->input('cat_thumb_path'));
        //     } catch (\Exception $e) {
        //     }
        // }

        // if ($banner != null) {
        //     try {
        //         StorageUtils::delete($request->input('banner_path'));
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
}
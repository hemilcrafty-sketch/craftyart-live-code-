<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use App\Models\VirtualCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VirtualCategoryControllerBackup extends Controller
{
  // public function __construct()
  // {
  //   $this->middleware('auth');
  // }

  private $columns;
  private $operators;
  private $sorting;
  public function __construct()
  {
    $this->middleware('auth');
    $config = config('virtualcolumns');
    $this->columns = $config['columns'];
    $this->sorting = $config['sorting'];
    $this->operators = $config['operators'];
  }


  public function create()
  {
    $appArray = AppCategory::all();
    $columns = $this->columns;
    $operators = $this->operators;
    $soring = $this->sorting;
    

    return view('virtual_cat/create_virtual_cat', compact('appArray', 'columns', 'operators'));
  }


  public function store(Request $request)
  {
      if(!isset($request->category_thumb) || !isset($request->banner) || !isset($request->mockup)){
          return response()->json([
              'error' => "Banner Category thumb and mockup is required"
          ]);
      }



      if(HelperController::checkCategoryAvail($request->id ?? 0,$request->input('category_name'),$request->input('id_name'))){
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

      $base64Images =[ ...ContentManager::getBase64Contents($request->contents),['img'=> $request->category_thumb,'name' => "Category Thumb",'required'=>true],['img'=> $request->banner,'name' => "Banner",'required'=>true], ['img'=> $request->mockup,'name' => "Mockup",'required'=>true]];
      $validationError = ContentManager::validateBase64Images($base64Images);
      if ($validationError) {
          return response()->json([
              'error' => $validationError
          ]);
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

    if ($request->id)
      $res = VirtualCategory::find($request->id);
    else
      $res = new VirtualCategory();
    $res->string_id = $this->generateId();
    $res->category_name = $request->input('category_name');
    $res->id_name = $request->input('id_name');

    $res->meta_title = $request->input('meta_title');
    $res->tag_line = $request->input('tag_line');
    $res->h1_tag = $request->input('h1_tag');
    $res->h2_tag = $request->input('h2_tag');
      $res->primary_keyword = $request->input('primary_keyword');
    $res->meta_desc = $request->input('meta_desc');
    $res->short_desc = $request->input('short_desc');
    $res->long_desc = $request->input('long_desc');
    $res->virtual_query = $request->input('generatedQuery');
    $fldrStr = HelperController::generateFolderID('', 10);
    $res->fldr_str = $fldrStr;
    $contentPath = null;
    if(!empty($request->input('contents'))){
        $contents = ContentManager::getContents($request->input('contents'), $fldrStr, [], []);
        $contentPath = 'ct/' . uniqid() . ".json";
        StorageUtils::put($contentPath, $contents);
    }
    $res->contents = $contentPath;
    $faqsPath = null;
    if (!empty($request->input('faqs'))) {
        $faqsPath = 'faqs/' . uniqid() . ".json";
        StorageUtils::put($faqsPath, $request->input('faqs'));
    }
    $res->faqs = $faqsPath;
    $res->size = $request->input('size');
    $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb,'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
    $res->banner = ContentManager::saveImageToPath($request->banner,'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
    $res->mockup = ContentManager::saveImageToPath($request->mockup,'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
    $res->app_id = $request->input('app_id');
    $res->top_keywords = json_encode($topKeywords);
    // $res->cta = json_encode(HelperController::processCTA($request));
    $res->sequence_number = $request->input('sequence_number');
    $res->status = $request->input('status');
    $res->parent_category_id = $request->input('parent_category_id');
    $res->save();
    return response()->json([
      'success' => "done"
    ]);
  }

  public function index(Request $request){
        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $catArray = VirtualCategory::where('category_name', 'like', '%' . $query . '%')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return view('virtual_cat/show_virtual_cat')->with('catArray', $catArray);
  }

  public function edit(VirtualCategory $mainCategory, $id)
  {
    $res = VirtualCategory::find($id);
    if (!$res) {
      abort(404);
    }

    if (isset($res->top_keywords)) {
      $res->top_keywords = json_decode($res->top_keywords);
    } else {
      $res->top_keywords = [];
    }

    $datas['app'] = AppCategory::all();
    $datas['cat'] = $res;
    $datas['columns'] = $this->columns;
    $datas['id'] = $id;
    $datas['operators'] = $this->operators;
    $datas['sorting'] = $this->sorting;
    $datas['virtualCondition'] = QueryManager::getConditionData($res->virtual_query);
    $datas['parent_category'] = VirtualCategory::where('id', $datas['cat']->parent_category_id)->first();
    return view('virtual_cat/edit_virtual_cat')->with('datas', $datas);
  }


  public function destroy(VirtualCategory $mainCategory, $id)
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
    return redirect('show_virtual_cat');
  }

  public function generateId($length = 8)
  {
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
      $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    } while (VirtualCategory::where('string_id', $string_id)->exists());
    return $string_id;
  }
}
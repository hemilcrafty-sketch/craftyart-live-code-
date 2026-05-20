<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AppCategory;
use App\Models\Category;
use App\Models\NewCategory;
use App\Models\User;
use App\Models\VirtualCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VirtualCategoryController extends AppBaseController
{

    private mixed $columns;
    private mixed $operators;
    private mixed $sorting;

    public function __construct()
    {
        parent::__construct();
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
        $assignSubCat = User::where('user_type', 5)->get();


        return view('virtual_cat/create_virtual_cat', compact('appArray', 'columns', 'operators', 'assignSubCat'));
    }


    public function store(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdmin(Auth::user()->user_type);

        $res = null;
        $canonical_link = $request->input('canonical_link');
        if ($request->id) {
            $res = VirtualCategory::find($request->id);
            // if (!$res) {
            //     return response()->json([
            //         'error' => 'Page not found',
            //     ]);
            // }

            if (!$idAdmin && $res->emp_id != 0 && RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {

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

            }
        } else {
            $res = new VirtualCategory();
            $res->emp_id = $currentuserid;
        }

        $accessCheck = $this->isAccessByRole("seo", $request->id, $res->emp_id ?? $currentuserid, [$res['seo_emp_id']]);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        if (HelperController::checkCategoryAvail($request->id ?? 0, $request->input('category_name'), $request->input('id_name'))) {
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


        //        $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 1, $request->input('id_name'));
//        if ($canonicalError) {
//            return response()->json([
//                'error' => $canonicalError
//            ]);
//        }
        $res->seo_emp_id = ($request->seo_emp_id ?? 0) ?? $res->seo_emp_id;
        $res->canonical_link = $canonical_link;
        $res->string_id = $this->generateId();
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->tag_line = $request->input('tag_line');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->h2_tag = $request->input('h2_tag');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->long_desc = $request->input('long_desc');
        $res->virtual_query = $request->input('generatedQuery');

        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = HelperController::generateFolderID('');
            while (VirtualCategory::where('fldr_str', $fldrStr)->exists()) {
                $fldrStr = HelperController::generateFolderID('');
            }

            $res->fldr_str = $fldrStr;
        }

        $oldContentPath = $res->contents;
        $oldFaqPath = $res->faqs;

        $contentPath = null;
        if (!empty($request->input('contents'))) {
            $contents = ContentManager::getContents($request->input('contents'), $fldrStr, [], []);
            $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
            StorageUtils::put($contentPath, $contents);
        }
        $res->contents = $contentPath;

        $faqsPath = null;

        if (!empty($request->input('faqs'))) {
            $faqsPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
            $faqs = [];
            $faqs['title'] = $request->faqs_title;
            $faqs['faqs'] = json_decode($request->faqs);
            StorageUtils::put($faqsPath, json_encode($faqs));
        }

        $res->faqs = $faqsPath;

        $res->size = $request->input('size');
        $res->category_thumb = ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->banner = ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->mockup = ContentManager::saveImageToPath($request->mockup, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);
        $res->app_id = $request->input('app_id');
        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number');
        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        } else {
            $res->status = $request->id ? $res->status : 0;
        }
        $res->parent_category_id = $request->input('parent_category_id');
        $this->applyVideoSitemapFieldsFromRequest($request, $res, !$request->id);
        $res->save();


        StorageUtils::delete($oldContentPath);
        StorageUtils::delete($oldFaqPath);

        return response()->json([
            'success' => "done"
        ]);
    }

    public function index(Request $request)
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'ID'],
            ['id' => 'category_name', 'value' => 'Category Name'],
            ['id' => 'id_name', 'value' => 'ID Name'],
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'no_index', 'value' => 'No Index'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $noIndexStats = $this->noIndexListingStats(VirtualCategory::class);

        $query = VirtualCategory::with('assignedSeo');
        $this->applyNoIndexFilter($request, $query);

        $catArray = $this->applyFiltersAndPagination(
            $request,
            $query,
            $searchableFields
        );

        return view('virtual_cat.show_virtual_cat', compact('catArray', 'searchableFields', 'noIndexStats'));
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

        $allCategories = VirtualCategory::all();
        $datas['app'] = AppCategory::all();
        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";
        $datas['cat'] = $res;
        $datas['allCategories'] = $allCategories;
        $datas['columns'] = $this->columns;
        $datas['id'] = $id;
        $datas['operators'] = $this->operators;
        $datas['sorting'] = $this->sorting;
        $datas['virtualCondition'] = QueryManager::getConditionData($res->virtual_query);
        $datas['parent_category'] = VirtualCategory::where('id', $datas['cat']->parent_category_id)->first();
        $assignSubCat = User::where('user_type', 5)->get();

        return view('virtual_cat/edit_virtual_cat')
            ->with('datas', $datas)
            ->with('assignSubCat', $assignSubCat);
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
}

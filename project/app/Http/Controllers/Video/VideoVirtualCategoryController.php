<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use App\Models\User;
use App\Models\Video\VideoSlugHistory;
use App\Models\Video\VideoVirtualCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Video\VideoCategory;

class VideoVirtualCategoryController extends AppBaseController
{

    private mixed $columns;
    private mixed $operators;
    private mixed $sorting;

    public function __construct()
    {
        parent::__construct();
        $config = config('videovirtualcolumns');
        $this->columns = $config['columns'];
        $this->sorting = $config['sorting'];
        $this->operators = $config['operators'];
    }


    public function create()
    {
        $columns = $this->columns;
        $operators = $this->operators;
        $assignSubCat = User::where('user_type', 5)->get();
        $parentCategories = VideoCategory::where('parent_category_id', 0)->get();
        $groupedVideoCategories = [];

        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }
        return view('videos.virtual_cat.create_video_virtual_cat', compact('columns', 'operators', 'assignSubCat', 'groupedVideoCategories'));
    }


    public function store(Request $request, $id = null)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdmin(Auth::user()->user_type);

        // Validate character limits for meta_desc and short_desc
        $request->validate([
            'meta_desc' => 'nullable|string|max:160',
            'short_desc' => 'nullable|string|max:350',
        ]);

        $res = null;
        $canonical_link = $request->input('canonical_link');

        // Check for ID from route parameter or request input
        $categoryId = $id ?? $request->input('id');



        $parent_category_id = $request->input('parent_category_id');
        if (!$parent_category_id) {
            return response()->json([
                'error' => 'Please Select Category'
            ]);
        }
        $category = VideoCategory::find($parent_category_id);
        if (!$category || $category->parent_category_id == 0) {
            return response()->json([
                'error' => 'Use Child Category.'
            ]);
        }

        if ($categoryId) {
            $res = VideoVirtualCategory::find($categoryId);

            if (!$idAdmin && $res->emp_id != 0 && RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {

                $canonicalError = $this->validateVideoCanonicalLink($canonical_link, $request->input('slug'));
                if ($canonicalError) {
                    return response()->json([
                        'error' => $canonicalError
                    ]);
                } else {
                    $res->canonical_link = $request->canonical_link;
                    $res->parent_category_id = $parent_category_id;
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
            $res = new VideoVirtualCategory();
            $res->emp_id = $currentuserid;
        }

        $slugError = VideoSlugHistory::checkSlugValidation($request->input('slug'), $categoryId);
        if ($slugError) {
            return response()->json([
                'error' => $slugError,
            ]);
        }

        // Check access - Admin and SEO Manager can always edit
        // Other users need to pass the access check
        $user = Auth::user();
        $userType = $user->user_type;
        
        if (!RoleManager::isAdminOrSeoManager($userType)) {
            $accessCheck = $this->isAccessByRole("seo", $categoryId, $res->emp_id ?? $currentuserid, [$res['seo_emp_id']]);
            if ($accessCheck) {
                return response()->json([
                    'error' => $accessCheck,
                ]);
            }
        }

        if (HelperController::checkCategoryAvail($categoryId ?? 0, $request->input('category_name'), null)) {
            return response()->json([
                'error' => 'Category Name Already exist.'
            ]);
        }

        // Validate canonical link for video virtual categories
        if (!empty($canonical_link)) {
            $canonicalError = $this->validateVideoCanonicalLink($canonical_link, $request->input('slug'));
            if ($canonicalError) {
                return response()->json([
                    'error' => $canonicalError
                ]);
            }
        }

        $contentError = ContentManager::validateContent($request->contents, null, null);
        if ($contentError) {
            return response()->json([
                'error' => $contentError
            ]);
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents)];

        // Add image validation only if images are provided
        if ($request->category_thumb) {
            $base64Images[] = ['img' => $request->category_thumb, 'name' => "Category Thumb", 'required' => false];
        }
        if ($request->banner) {
            $base64Images[] = ['img' => $request->banner, 'name' => "Banner", 'required' => false];
        }
        if ($request->mockup) {
            $base64Images[] = ['img' => $request->mockup, 'name' => "Mockup", 'required' => false];
        }
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

        $keywordNames = $request->input('keyword_name', []);
        $keywordLinks = $request->input('keyword_link', []);
        $keywordTargets = $request->input('keyword_target', []);
        $keywordRels = $request->input('keyword_rel', []);

        $topKeywords = [];
        if (!empty($keywordNames) && is_array($keywordNames)) {
            for ($i = 0; $i < count($keywordNames); $i++) {
                $keyword['value'] = $keywordNames[$i];
                $keyword['link'] = $keywordLinks[$i] ?? '';
                $keyword['openinnewtab'] = $keywordTargets[$i] ?? '';
                $keyword['nofollow'] = $keywordRels[$i] ?? '';
                $topKeywords[] = $keyword;
            }
        }

        $res->slug = $request->input('slug');
        $res->seo_emp_id = ($request->seo_emp_id ?? 0) ?? $res->seo_emp_id;
        $res->canonical_link = $canonical_link;
        $res->string_id = $this->generateId();
        $res->category_name = $request->input('category_name') ?: '';
        $res->tag_line = $request->input('tag_line') ?: '';
        $res->meta_title = $request->input('meta_title') ?: '';
        $res->primary_keyword = $request->input('primary_keyword') ?: '';
        $res->h1_tag = $request->input('h1_tag') ?: '';
        $res->h2_tag = $request->input('h2_tag') ?: '';
        $res->meta_desc = $request->input('meta_desc') ?: '';
        $res->short_desc = $request->input('short_desc') ?: '';

        $fldrStr = $res->fldr_str;
        if (!$fldrStr) {
            $fldrStr = HelperController::generateFolderID('');
            while (VideoVirtualCategory::where('fldr_str', $fldrStr)->exists()) {
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
        $res->category_thumb = $request->category_thumb ? ContentManager::saveImageToPath($request->category_thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp) : '';
        $res->banner = $request->banner ? ContentManager::saveImageToPath($request->banner, 'uploadedFiles/banner_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp) : null;
        $res->mockup = $request->mockup ? ContentManager::saveImageToPath($request->mockup, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp) : null;
        $res->top_keywords = json_encode($topKeywords);
        $res->sequence_number = $request->input('sequence_number') ?: 0;
        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
        } else {
            $res->status = $categoryId ? $res->status : 0;
        }
        $res->parent_category_id = $parent_category_id;
        $this->applyVideoSitemapFieldsFromRequest($request, $res, !$res->exists);
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
            ['id' => 'sequence_number', 'value' => 'Sequence Number'],
            ['id' => 'no_index', 'value' => 'No Index'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $noIndexStats = $this->noIndexListingStats(VideoVirtualCategory::class);

        $query = VideoVirtualCategory::with('assignedSeo');
        $this->applyNoIndexFilter($request, $query);

        $catArray = $this->applyFiltersAndPagination(
            $request,
            $query,
            $searchableFields
        );

        return view('videos.virtual_cat.show_video_virtual_cat', compact('catArray', 'searchableFields', 'noIndexStats'));
    }


    public function edit(VideoVirtualCategory $mainCategory, $id)
    {
        $res = VideoVirtualCategory::find($id);
        if (!$res) {
            abort(404);
        }

        if (isset($res->top_keywords)) {
            $res->top_keywords = json_decode($res->top_keywords);
        } else {
            $res->top_keywords = [];
        }

        $allCategories = VideoVirtualCategory::all();
        $res->contents = isset($res->contents) ? StorageUtils::get($res->contents) : "";
        $res->faqs = isset($res->faqs) ? StorageUtils::get($res->faqs) : "";


        // Parse CTA section from contents
        // $ctaSection = [];
        // if (!empty($res->contents)) {
        //   $contentsArray = json_decode($res->contents, true);
        //   if (is_array($contentsArray)) {
        //     foreach ($contentsArray as $content) {
        //       if (isset($content['type']) && $content['type'] === 'cta_api_more_template') {
        //         $ctaSection[] = $content;
        //       }
        //     }
        //   }
        // }

        // dd($ctaSection);
        $datas['cat'] = $res;
        $datas['allCategories'] = $allCategories;
        $datas['columns'] = $this->columns;
        $datas['id'] = $id;
        $datas['operators'] = $this->operators;
        $datas['sorting'] = $this->sorting;
        $parentCategories = VideoCategory::where('parent_category_id', 0)->get();
        $groupedVideoCategories = [];

        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }
        $datas['groupedVideoCategories'] = $groupedVideoCategories;
        // No longer using virtual_query
        $datas['virtualCondition'] = [];

        $datas['parent_category'] = VideoVirtualCategory::where('id', $datas['cat']->parent_category_id)->first();
        $assignSubCat = User::where('user_type', 5)->get();

        return view('videos.virtual_cat.edit_video_virtual_cat')
            ->with('datas', $datas)
            ->with('assignSubCat', $assignSubCat);
    }


    public function destroy($id)
    {
        // $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);
        // if ($idAdmin) {
        //     $res = VideoVirtualCategory::find($id);
        //     if ($res) {
        //         VideoVirtualCategory::destroy($id);
        //     }
        // }
        return redirect('show_video_virtual_cat');
    }


    public function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (NewCategory::where('string_id', $string_id)->exists());
        return $string_id;
    }

    /**
     * Validate canonical link for video virtual categories
     * Video virtual categories use URL format: templates/p/{slug}
     */
    private function validateVideoCanonicalLink($canonicalLink, $slug): ?string
    {
        if (isset($canonicalLink)) {
            if (!RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
                return "You have no access to modify Canonical link";
            }
            if (!str_starts_with($canonicalLink, HelperController::$videoFrontendUrl)) {
                return "Canonical link must be start with " . HelperController::$videoFrontendUrl;
            }
            // Video virtual categories use type 0 format: templates/p/{slug}
            $pageUrl = HelperController::$videoFrontendUrl . 'templates/p/' . $slug;
            if (rtrim($pageUrl, '/') == rtrim($canonicalLink, '/')) {
                return "Canonical link cannot be same as page url";
            }
        }
        return null;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\SpecialPage;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\NewCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\PendingTask;
use App\Models\Size;

class SpecialPagesControllerBackup extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filters = ["title", "description", "page_slug"];
        $query = SpecialPage::query();
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $status = $request->input('status','all');
        if ($perPage == "all" || $perPage == "All") {
            $perPage = SpecialPage::count();
        }
        if ($request->input('query') != '' && $status == 'all' ) {
            $query = HelperController::applyFilters($query, $filters, $request->input('query'));
        }
        if($status != "all"){
            $query->where('status', $status);
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }
        $specialPages = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        return view("special_pages.index", compact('specialPages'));
    }

    public function create($id = 0): Factory|View|Application|RedirectResponse
    {

        $page = SpecialPage::find($id);
        if (!isset($page) && $id != 0) {
            return redirect()->route('pages.list')->with('error', 'page not found.');
        }

        $allCategories = NewCategory::getAllCategoriesWithSubcategories();

        $file = $heroBgImageFile = $bodyBgImageFile = '';

        if (isset($page->pre_breadcrumb) && $page->pre_breadcrumb != "") {
            $page->pre_breadcrumb = json_decode($page->pre_breadcrumb);
        }

        if (isset($page->top_keywords) && $page->top_keywords != "") {
            $page->top_keywords = json_decode($page->top_keywords);
        }

        if (isset($page->contents))
            $page->contents = StorageUtils::exists($page->contents) ? StorageUtils::get($page->contents) : "";

        if (isset($page->faqs))
            $page->faqs = StorageUtils::exists($page->faqs) ? StorageUtils::get($page->faqs) : "";

        $sizes = Size::getAllSizes();

        $category = null;
        if (isset($page)) {
            $category = NewCategory::find($page->cat_id);
        }
        return view('special_pages.create', compact('id', 'page', 'file', 'heroBgImageFile', 'bodyBgImageFile', 'category', 'allCategories', 'sizes'));
    }

    public function addUpdatePage(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = HelperController::isAdmin(Auth::user()->user_type);
        $res = null;
        if ($request->id) {
            $res = SpecialPage::find($request->id);
            if (!$res) {
                return response()->json([
                    'error' => 'Page not found',
                ]);
            }

            if (!$idAdmin && $res->emp_id != 0 && $currentuserid != $res->emp_id) {

                if(HelperController::isAdminOrFenil($currentuserid)) {
                    $canonicalError = ContentManager::validateCanonicalLink($request->canonical_link, 2, $request->page_slug);
                    if ($canonicalError) {
                        return response()->json([
                            'error' => $canonicalError
                        ]);
                    } else {
                        $res->canonical_link = $request->canonical_link;
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
        }


        $data = $request->all();
        $data['button_target'] = isset($data['button_target']) ? 1 : 0;
        $data['button_rel'] = isset($data['button_rel']) ? 1 : 0;
        $availableImage = [];
        $availableVideo = [];

        if(isset($data['canonical_link'])) {
            $canonicalError = ContentManager::validateCanonicalLink($data['canonical_link'], 2, $data['page_slug']);
            if ($canonicalError) {
                return response()->json([
                    'error' => $canonicalError
                ]);
            }
        } else {
            $data['canonical_link'] = $res ? $res->canonical_link : null;
        }

        if (!isset($request->contents)) {
            return response()->json([
                'error' => "Please Add Contents"
            ]);
        }

        if (!isset($request->id)) {
            $isPageSlugAvail = SpecialPage::where('page_slug', $request->page_slug)->exists();

            if ($isPageSlugAvail) {
                return response()->json([
                    'error' => "The Page Slug is already available"
                ]);
            }
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->hero_background_image, 'name' => "Hero Background Image", 'required' => $request->hero_bg_option == "image"], ['img' => $request->body_background_image, 'name' => "Body Background Image", 'required' => false], ['img' => $request->banner, 'name' => "Banner", 'required' => false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        if ($request->id) {
            $availableContent = SpecialPage::where('id', $request->id)->value('contents');
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
        }

        $contentPath = null;
        $faqsPath = null;
        if ($request->id) {
            $res = SpecialPage::find($request->id);
            if (!$res->string_id) {
                $data['string_id'] = $this->generateId();
            }
            $fldrStr = SpecialPage::where('id', $request->id)->value('fldr_str');
            $contentPath = SpecialPage::where('id', $request->id)->value('contents');
            $faqsPath = SpecialPage::where('id', $request->id)->value('faqs');
            if ($fldrStr == null || $fldrStr == "") {
                $fldrStr = HelperController::generateFolderID('', 10);
            }
        } else {
            $fldrStr = HelperController::generateFolderID('', 10);
            $data['string_id'] = $this->generateId();
        }
        $data['page_slug'] = $request->page_slug;
        unset($data['_token']);
        unset($data['image']);
        unset($data['/add-update-pages']);
        unset($data['/page/add-update-pages']);
        try {
            $pre_breadcrumb = [];
            $pre_breadcrumb['value'] = $data['pre_breadcrumb_name'];
            $pre_breadcrumb['link'] = $data['pre_breadcrumb_link'];
            $pre_breadcrumb['openinnewtab'] = isset($data['pre_breadcrumb_target']) ? 1 : 0;
            $pre_breadcrumb['nofollow'] = isset($data['pre_breadcrumb_rel']) ? 1 : 0;
            $data['pre_breadcrumb'] = json_encode($pre_breadcrumb);
            unset($data['pre_breadcrumb_name']);
            unset($data['pre_breadcrumb_link']);
            unset($data['pre_breadcrumb_target']);
            unset($data['pre_breadcrumb_rel']);

            $keywordNames = $data['keyword_name'];
            $keywordLinks = $data['keyword_link'];
            $keywordTargets = $data['keyword_target'];
            $keywordRels = $data['keyword_rel'];
            $topKeywords = [];
            for ($i = 0; $i < count($keywordNames); $i++) {
                $keyword['value'] = $keywordNames[$i];
                $keyword['link'] = $keywordLinks[$i];
                $keyword['openinnewtab'] = $keywordTargets[$i];
                $keyword['nofollow'] = $keywordRels[$i];
                $topKeywords[] = $keyword;
            }
            $data['top_keywords'] = json_encode($topKeywords);
            unset($data['keyword_name']);
            unset($data['keyword_link']);
            unset($data['keyword_target']);
            unset($data['keyword_rel']);

            if (isset($request->contents)) {
                $contents = ContentManager::getContents($request->contents, $fldrStr, $availableImage, $availableVideo);
                $contentPath = $contentPath ?? 'sp/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($contentPath, $contents);
                $data['contents'] = $contentPath;
            }

            if (isset($request->faqs)) {
                $faqsPath = $faqsPath ?? 'sp/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($faqsPath, $request->faqs);
                $data['faqs'] = $faqsPath;
            }

            $data['banner'] = ContentManager::saveImageToPath($request->banner, 'p/' . $fldrStr . '/I' . time());
            $data['hero_background_image'] = ContentManager::saveImageToPath($request->hero_background_image, 'p/' . $fldrStr . '/I' . time());
            $data['body_background_image'] = ContentManager::saveImageToPath($request->body_background_image, 'p/' . $fldrStr . '/I' . time());

            if (isset($request->remove_banner) && $request->remove_banner == 1) {
                $data['banner'] = "";
                $data['banner_type'] = "";
                unset($data['remove_banner']);
            }
            if (isset($request->body_bg_image) || $request->body_bg_image == null) {
                unset($data['body_bg_image']);
            }
            if (isset($request->hero_bg_image) || $request->hero_bg_imag == null) {
                unset($data['hero_bg_image']);
            }

            if ($fldrStr != "") {
                $data['fldr_str'] = $fldrStr;
            }

            if (!$idAdmin) {
                $data['emp_id'] = $currentuserid;
            }

            if ($request->id) {
                SpecialPage::where('id', $request->id)->update($data);
                return response()->json([
                    'success' => 'done',
                ]);
            } else {
                SpecialPage::create($data);
            }

            return response()->json([
                'success' => 'done',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /*public function addUpdatePage(Request $request): JsonResponse
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = HelperController::isAdmin(Auth::user()->user_type);
        if ($request->id) {
            $res = SpecialPage::find($request->id);
            if (!$res) {
                return response()->json([
                    'error' => 'Page not found',
                ]);
            }
            if (!$idAdmin && $res->emp_id != 0 && $currentuserid != $res->emp_id) {
                if(HelperController::isAdminOrFenil($currentuserid)){
                    $canonicalError = ContentManager::validateCanonicalLink($request->canonical_link, 2, $request->page_slug);
                    if ($canonicalError) {
                        return response()->json([
                            'error' => $canonicalError
                        ]);
                    } else {
                        $res->canonical_link = $request->canonical_link;
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
        }


        $data = $request->all();
        $data['button_target'] = isset($data['button_target']) ? 1 : 0;
        $data['button_rel'] = isset($data['button_rel']) ? 1 : 0;
        $availableImage = [];
        $availableVideo = [];

//        if($request->id) {
            if(isset($data['canonical_link'])){
                $canonicalError = ContentManager::validateCanonicalLink($data['canonical_link'], 2, $data['page_slug']);
                if ($canonicalError) {
                    return response()->json([
                        'error' => $canonicalError
                    ]);
                }
            } else {
                $data['canonical_link'] = $request->id ? $res->canonical_link : null;
            }
//        }

        if (!isset($request->contents)) {
            return response()->json([
                'error' => "Please Add Contents"
            ]);
        }

        if (!isset($request->id)) {
            $isPageSlugAvail = SpecialPage::where('page_slug', $request->page_slug)->exists();

            if ($isPageSlugAvail) {
                return response()->json([
                    'error' => "The Page Slug is already available"
                ]);
            }
        }

        $base64Images = [...ContentManager::getBase64Contents($request->contents), ['img' => $request->hero_background_image, 'name' => "Hero Background Image", 'required' => $request->hero_bg_option == "image"], ['img' => $request->body_background_image, 'name' => "Body Background Image", 'required' => false], ['img' => $request->banner, 'name' => "Banner", 'required' => false]];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json([
                'error' => $validationError
            ]);
        }

        if ($request->id) {
            $availableContent = SpecialPage::where('id', $request->id)->value('contents');
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
        }

        $contentPath = null;
        $faqsPath = null;
        if ($request->id) {
            $res = SpecialPage::find($request->id);
            if (!$res->string_id) {
                $data['string_id'] = $this->generateId();
            }
            $fldrStr = SpecialPage::where('id', $request->id)->value('fldr_str');
            $contentPath = SpecialPage::where('id', $request->id)->value('contents');
            $faqsPath = SpecialPage::where('id', $request->id)->value('faqs');
            if ($fldrStr == null || $fldrStr == "") {
                $fldrStr = HelperController::generateFolderID('', 10);
            }
        } else {
            $fldrStr = HelperController::generateFolderID('', 10);
            $data['string_id'] = $this->generateId();
        }
        $data['page_slug'] = $request->page_slug;
        unset($data['_token']);
        unset($data['image']);
        unset($data['/add-update-pages']);
        unset($data['/page/add-update-pages']);
        try {
            $pre_breadcrumb = [];
            $pre_breadcrumb['value'] = $data['pre_breadcrumb_name'];
            $pre_breadcrumb['link'] = $data['pre_breadcrumb_link'];
            $pre_breadcrumb['openinnewtab'] = isset($data['pre_breadcrumb_target']) ? 1 : 0;
            $pre_breadcrumb['nofollow'] = isset($data['pre_breadcrumb_rel']) ? 1 : 0;
            $data['pre_breadcrumb'] = json_encode($pre_breadcrumb);
            unset($data['pre_breadcrumb_name']);
            unset($data['pre_breadcrumb_link']);
            unset($data['pre_breadcrumb_target']);
            unset($data['pre_breadcrumb_rel']);

            $keywordNames = $data['keyword_name'];
            $keywordLinks = $data['keyword_link'];
            $keywordTargets = $data['keyword_target'];
            $keywordRels = $data['keyword_rel'];
            $topKeywords = [];
            for ($i = 0; $i < count($keywordNames); $i++) {
                $keyword['value'] = $keywordNames[$i];
                $keyword['link'] = $keywordLinks[$i];
                $keyword['openinnewtab'] = $keywordTargets[$i];
                $keyword['nofollow'] = $keywordRels[$i];
                $topKeywords[] = $keyword;
            }
            $data['top_keywords'] = json_encode($topKeywords);
            unset($data['keyword_name']);
            unset($data['keyword_link']);
            unset($data['keyword_target']);
            unset($data['keyword_rel']);

            if (isset($request->contents)) {
                $contents = ContentManager::getContents($request->contents, $fldrStr, $availableImage, $availableVideo);
                $contentPath = $contentPath ?? 'sp/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($contentPath, $contents);
                $data['contents'] = $contentPath;
            }

            if (isset($request->faqs)) {
                $faqsPath = $faqsPath ?? 'sp/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
                StorageUtils::put($faqsPath, $request->faqs);
                $data['faqs'] = $faqsPath;
            }

            $data['banner'] = ContentManager::saveImageToPath($request->banner, 'p/' . $fldrStr . '/I' . time());
            $data['hero_background_image'] = ContentManager::saveImageToPath($request->hero_background_image, 'p/' . $fldrStr . '/I' . time());
            $data['body_background_image'] = ContentManager::saveImageToPath($request->body_background_image, 'p/' . $fldrStr . '/I' . time());

            if (isset($request->remove_banner) && $request->remove_banner == 1) {
                $data['banner'] = "";
                $data['banner_type'] = "";
                unset($data['remove_banner']);
            }
            if (isset($request->body_bg_image) || $request->body_bg_image == null) {
                unset($data['body_bg_image']);
            }
            if (isset($request->hero_bg_image) || $request->hero_bg_imag == null) {
                unset($data['hero_bg_image']);
            }

            if ($fldrStr != "") {
                $data['fldr_str'] = $fldrStr;
            }

            if (!$idAdmin) {
                $data['emp_id'] = $currentuserid;
            }

            if ($request->id) {
                SpecialPage::where('id', $request->id)->update($data);
                return response()->json([
                    'success' => 'done',
                ]);
            } else {
                SpecialPage::create($data);
            }

            return response()->json([
                'success' => 'done',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }*/

    public function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (SpecialPage::where('string_id', $string_id)->exists());
        return $string_id;
    }

    public function destroy(SpecialPage $specialPage)
    {
        try {
            //   $category_thumb = $svgItem->thumb;
            //   $contains = Str::contains($category_thumb, 'no_image');
            //   if (!$contains) {
            //     unlink(storage_path("app/public/" . $category_thumb));
            //   }
//            $specialPage->delete();
            return response()->json([
                'status' => true,
                'success' => "Page Delete Successfully",
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }


}

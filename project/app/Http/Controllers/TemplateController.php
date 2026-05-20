<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Font;
use App\Models\BgMode;
use App\Models\EditableMode;
use App\Models\ResizeMode;
use App\Models\LockType;
use App\Models\StickerMode;
use App\Models\Design;
use App\Models\Category;
use App\Models\AppCategory;
use App\Models\TextAlignment;
use App\Models\StickerCategory;
use App\Models\BgCategory;
use App\Models\SubCategory;
use App\Models\Style;
use App\Models\Theme;
use App\Models\SearchTag;
use App\Models\Interest;
use App\Models\Language;
use App\Models\SpecialKeyword;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\Color;
use App\Models\Formate;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\Religion;
use App\Models\Size;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TemplateController extends AppBaseController
{


    public function index()
    {
    }

    public function show(Request $request)
    {
        $paginate_count = 20;
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        $condition = $idAdmin ? '!=' : '=';

        if ($idAdmin) {
            $currentuserid = -1;
        }

        $sortingField = $request->get('sortingField', 'created_at') ?: 'created_at';

        // Start base query
        $temp_data_query = Design::query()->where('deleted', 0);

        // ✅ template_status filter
        if ($request->filled('template_status')) {
            if ($request->template_status === 'not-live') {
                $temp_data_query->where('status', 0);
            } elseif ($request->template_status === 'live') {
                $temp_data_query->where('status', 1);
            }
        }

        // ✅ seo_employee filter
        if ($request->filled('seo_employee')) {
            if ($request->seo_employee === 'assigned') {
                $temp_data_query->whereNotNull('seo_emp_id')->where('seo_emp_id', '!=', 0);
            } elseif ($request->seo_employee === 'unassigned') {
                $temp_data_query->where(function ($q) {
                    $q->whereNull('seo_emp_id')->orWhere('seo_emp_id', 0);
                });
            }
        }

        // ✅ seo_category_assigne filter
        if ($request->filled('seo_category_assigne')) {
            if ($request->seo_category_assigne === 'assigned') {
                $temp_data_query->whereNotNull('new_category_id')->where('new_category_id', '!=', 0);
            } elseif ($request->seo_category_assigne === 'unassigned') {
                $temp_data_query->where(function ($q) {
                    $q->whereNull('new_category_id')->orWhere('new_category_id', 0);
                });
            }
        }

        // ✅ premium_type filter
        if ($request->filled('premium_type')) {
            if ($request->premium_type === 'free') {
                $temp_data_query->where('is_premium', 0)->where('is_freemium', 0);
            } elseif ($request->premium_type === 'freemium') {
                $temp_data_query->where('is_freemium', 1);
            } elseif ($request->premium_type === 'premium') {
                $temp_data_query->where('is_premium', 1);
            }
        }

        // ✅ user role restrictions
        if (!$idAdmin) {
            if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
                $temp_data_query->whereHas('newCategory', function ($q) use ($currentuserid) {
                    $q->where('seo_emp_id', $currentuserid);
                });
            } else {
                $temp_data_query->where('seo_emp_id', $currentuserid);
            }
        }

        // ✅ animated filter
        if ($request->filled('animated')) {
            if ($request->animated === 'false') {
                $temp_data_query->where('animation', 0);
            } elseif ($request->animated === 'true') {
                $temp_data_query->where('animation', 1);
            }
        }

        // ✅ no_index filter (index vs noindex)
        $this->applyNoIndexFilter($request, $temp_data_query);

        // ✅ search query (keeps other filters)
        if ($request->filled('query')) {
            $search = $request->input('query'); // <-- Correct way

            $cat_id = Category::where('category_name', 'LIKE', "%{$search}%")->value('id');
            $app_id = AppCategory::where('app_name', 'LIKE', "%{$search}%")->value('id');

            $temp_data_query->where(function ($q) use ($search, $cat_id, $app_id) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('string_id', 'LIKE', "%{$search}%")
                    ->orWhere('post_name', 'LIKE', "%{$search}%");

                if ($cat_id) {
                    $q->orWhere('category_id', $cat_id);
                }
                if ($app_id) {
                    $q->orWhere('app_id', $app_id);
                }
            });
        }

        // ✅ category filter (only if no search query)
        if (!$request->filled('query')) {
            if ($request->filled('cat')) {
                $temp_data_query->where('category_id', $request->cat);
            } elseif ($request->filled('new_cat')) {
                $temp_data_query->where('new_category_id', $request->new_cat);
            }
        }

        // Count & paginate after all filters
        $temp_data_count = $temp_data_query->count();
        $temp_data = $temp_data_query->orderBy($sortingField, 'desc')->paginate($paginate_count);

        // Count string logic
        $total = $temp_data_count;
        $count = $total;
        $total_diff = $paginate_count - 1;
        $diff = $paginate_count - 1;

        if ($total < $paginate_count) {
            $diff = $total - 1;
        }

        if ($request->has('page')) {
            $count = $request->page * $paginate_count;
            if ($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            }
        } else {
            $count = $paginate_count;
            if ($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            }
        }

        $ccc = $total == 0
            ? "Showing 0-0 of 0 entries"
            : "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";

        // Extra data for view
        $seoExecutiveNIntern = User::whereIn('user_type', [5, 6])
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        $data['count_str'] = $ccc;
        $data['item'] = $temp_data;
        $data['seoExecutiveNIntern'] = $seoExecutiveNIntern;
        $data['newCategories'] = NewCategory::all();
        $datas['cat'] = Category::all();
        $datas['apps'] = AppCategory::all();

        $parentCategories = NewCategory::where('parent_category_id', 0)->get();
        $groupedNewCategories = [];

        foreach ($parentCategories as $parent) {
            $children = NewCategory::where('parent_category_id', $parent->id)->get();
            $groupedNewCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }

        $newCategoryIds = collect($temp_data->items())->pluck('new_category_id')->unique()->filter();
        $newCategoriesForSEO = NewCategory::whereIn('id', $newCategoryIds)->get()->keyBy('id');
        $categorySeoEmpIds = [];

        foreach ($newCategoriesForSEO as $category) {
            $id = $category->seo_emp_id ?? null;
            if (!empty($id)) {
                $categorySeoEmpIds[$category->id] = $id;
            }
        }

        if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
            $seoUsers = User::where('user_type', 6)
                ->where('team_leader_id', $currentuserid)
                ->get()
                ->keyBy('id');
        }

        $noIndexStats = $this->noIndexListingStats(Design::class, static function ($q) use ($idAdmin, $currentuserid) {
            $q->where('deleted', 0);

            // Apply same role-based filtering as main query
            if (!$idAdmin) {
                if (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
                    $q->whereHas('newCategory', function ($subQ) use ($currentuserid) {
                        $subQ->where('seo_emp_id', $currentuserid);
                    });
                } else {
                    $q->where('seo_emp_id', $currentuserid);
                }
            }
        });

        return view('item.show_item', [
            'itemArray' => $data,
            'datas' => $datas,
            'categorySeoEmpIds' => $categorySeoEmpIds,
            'seoUsers' => $seoUsers ?? [],
            'groupedNewCategories' => $groupedNewCategories,
            'noIndexStats' => $noIndexStats,
        ]);
    }

    public function create(Request $request)
    {

        $datas['cat'] = Category::all();
        $datas['appId'] = $request->input('passingAppId');
        $datas['bg_mode'] = BgMode::all();
        $datas['sticker_mode'] = StickerMode::all();
        $datas['resize_mode'] = ResizeMode::all();
        $datas['lock_type'] = LockType::all();
        $datas['txt_align'] = TextAlignment::all();
        $datas['editable_mode'] = EditableMode::all()->sortBy("brand_id");
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();
        $datas['themeArray'] = Theme::all();
        $datas['searchTagArray'] = SearchTag::all();
        $datas['interestArray'] = Interest::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();
        $datas['specialKeywords'] = SpecialKeyword::all();

        return view('item/create_item')->with('datas', $datas);
    }

    public function updateTempCategory(Request $request)
    {
        if (isset($request->cat_id) && $request->cat_id != "") {
            $category = NewCategory::where('id', $request->cat_id)->first();
            if (!$category) {
                return response()->json([
                    "status" => false,
                    "error" => "category not found"
                ]);
            }
            $cateId = $category->id;
            try {
                Design::where("id", $request->tempId)->update(["new_category_id" => $cateId]);
                return response()->json([
                    "status" => true,
                    "success" => "Category update of the template"
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "error" => $e->getMessage()
                ]);
            }
        }
    }

    public function store(Request $request)
    {
        $accessCheck = $this->isAccessByRole("design");
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }
        $currentuserid = Auth::user()->id;
        $res = new Design();
        $this->validate($request, ['post_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        $this->validate($request, ['st_image.*' => 'required|image|mimes:jpg,png,gif|max:2048']);

        $bg_type_id = $request->input('bg_type_id');
        if ($bg_type_id == 0 || $bg_type_id == 1) {
            $this->validate($request, ['back_image' => 'required|image|mimes:jpg,png,gif|max:2048']);
        }

        $res->emp_id = $currentuserid;
        $res->app_id = $request->input('app_id');
        $res->category_id = $request->input('category_id');
        $res->sub_cat_id = $request->input('sub_category_id');
        $res->style_id = $request->input('styles');
        $res->theme_id = $request->input('theme_id');
        $res->bg_id = $request->input('bg_cat');
        $res->interest_id = $request->input('interest_id');
        $res->lang_id = $request->input('lang_id');
        $res->post_name = $request->input('post_name');
        $res->back_image_type = $bg_type_id;
        $res->ratio = $request->input('ratio');
        $res->grad_angle = $request->input('grad_angle');
        $res->grad_ratio = $request->input('grad_ratio');
        $res->width = $request->input('width');
        $res->height = $request->input('height');
        $res->status = $request->input('status');

        $date_range = $request->input('date_range');
        if ($date_range != null) {
            $date_range = str_replace(' ', '', $date_range);
            $split_date = explode("-", $date_range);
            if (sizeof($split_date) < 2) {
                return response()->json([
                    'error' => 'Please select end date.'
                ]);
            }
            $res->start_date = $split_date[0];
            $res->end_date = $split_date[1];
        }

        $res->is_premium = $request->input('is_premium');
        $res->keywords = $request->input('keywords');
        $post_thumb = $request->file('post_thumb');
        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
        StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
        $res->post_thumb = 'uploadedFiles/thumb_file/' . $new_name;

        if ($bg_type_id == 0 || $bg_type_id == 1) {
            $back_image = $request->file('back_image');
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $back_image->getClientOriginalExtension();
            StorageUtils::storeAs($back_image, 'uploadedFiles/bg_file', $new_name);
            $res->back_image = 'uploadedFiles/bg_file/' . $new_name;
        } else {
            $res->back_color = $request->input('color_code');
        }

        $st_image = $request->file('st_image');
        $st_count = count($st_image);
        $sticker_data = array();
        for ($i = 0; $i < $st_count; $i++) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $st_image[$i]->getClientOriginalExtension();
            StorageUtils::storeAs($st_image[$i], 'uploadedFiles/sticker_file', $new_name);
            $st_data = array();
            $st_data['st_image'] = 'uploadedFiles/sticker_file/' . $new_name;
            $st_data['st_x_pos'] = $request->input('st_x_pos')[$i];
            $st_data['st_y_pos'] = $request->input('st_y_pos')[$i];
            $st_data['st_width'] = $request->input('st_width')[$i];
            $st_data['st_height'] = $request->input('st_height')[$i];
            $st_data['st_rotation'] = $request->input('st_rotation')[$i];
            $st_data['st_opacity'] = $request->input('st_opacity')[$i];
            $st_data['st_type'] = $request->input('st_type')[$i];
            $st_data['st_color'] = $request->input('st_color')[$i];
            $st_data['st_resize'] = $request->input('st_resize')[$i];
            $st_data['st_lock_type'] = $request->input('st_lock_type')[$i];
            $st_data['st_cat'] = $request->input('st_cat')[$i];
            $st_data['st_order'] = $request->input('st_order')[$i];
            $sticker_data[] = $st_data;
        }

        $st_text = $request->input('text');
        $txt_count = count($st_text);
        $text_data = array();
        for ($i = 0; $i < $txt_count; $i++) {
            $txt_data = array();
            $txt_data['text'] = $st_text[$i];
            $txt_data['font_family'] = $request->input('font_family')[$i];
            $txt_data['txt_align'] = $request->input('txt_align')[$i];
            $txt_data['is_editable'] = $request->input('is_editable')[$i];
            $txt_data['editable_title'] = $request->input('editable_title')[$i];
            $txt_data['txt_size'] = $request->input('txt_size')[$i];
            $txt_data['txt_color'] = $request->input('txt_color')[$i];
            $txt_data['txt_color_alpha'] = $request->input('txt_color_alpha')[$i];
            $txt_data['txt_width'] = $request->input('txt_width')[$i];
            $txt_data['txt_height'] = $request->input('txt_height')[$i];
            $txt_data['txt_x_pos'] = $request->input('txt_x_pos')[$i];
            $txt_data['txt_y_pos'] = $request->input('txt_y_pos')[$i];
            $txt_data['line_spacing'] = $request->input('line_spacing')[$i];
            $txt_data['lineSpaceMultiplier'] = $request->input('lineSpaceMultiplier')[$i];
            $txt_data['word_spacing'] = $request->input('word_spacing')[$i];
            $txt_data['txt_curve'] = $request->input('txt_curve')[$i];
            $txt_data['txt_rotation'] = $request->input('txt_rotation')[$i];
            $txt_data['txt_opacity'] = $request->input('txt_opacity')[$i];
            $txt_data['txt_order'] = $request->input('txt_order')[$i];
            $txt_data['layer_effects'] = $request->input('txt_effect')[$i];
            $text_data[] = $txt_data;
        }

        $res->component_info = json_encode($sticker_data);
        $res->text_info = json_encode($text_data);
        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function edit(Design $design, $id)
    {
        $res = Design::find($id);
        if (!$res) {
            $res = Design::where('string_id', $id)->first();
        }
        if (!$res) {
            abort(404);
            return;
        }

        if ($res->related_tags) {
            $res->related_tags = implode(",", json_decode($res->related_tags, true));
        }
        if ($res->new_related_tags) {
            $res->new_related_tags = isset($res->new_related_tags) ? implode(",", json_decode($res->new_related_tags, true)) : "";
        }
        if ($res->color_id) {
            $res->color_id = isset($res->color_id) ? implode(",", json_decode($res->color_id, true)) : "";
        }
        $datas['app'] = AppCategory::all();
        $datas['cat'] = Category::all();
        $datas['item'] = $res;

        if (isset($datas['item']['new_related_tags'])) {
            $newTags = "";

            // return $datas['item'];

            foreach (explode(',', $datas['item']['new_related_tags']) as $value) {
                $dataRes = NewSearchTag::where('id', $value)->first();
                if ($dataRes) {
                    $newTags = $newTags . $dataRes->name . ",";
                }
            }

            $datas['item']['new_related_tags'] = $newTags;
        }


        $categoryId = $datas['item']['new_category_id'];
        $selectCategory = NewCategory::find($categoryId);

        if ($datas['item']->designs) {
            $jsonData = json_decode($datas['item']->designs, true);
            if (!$jsonData) {
                $path = "/" . $datas['item']->designs;
                $jsonData = json_decode(StorageUtils::get($path), true);
            }
        } else {
            $jsonData = null;
        }

        if ($jsonData) {
            $jsonData = JSONUtils::replaceMediaUrl($jsonData, "");
        }

        $datas['designs'] = $jsonData ?? [];

        $datas['thumbs'] = json_decode($datas['item']->thumb_array, true);

        $datas['bg_mode'] = BgMode::all();
        $datas['sticker_mode'] = StickerMode::all();
        $datas['resize_mode'] = ResizeMode::all();
        $datas['lock_type'] = LockType::all();
        $datas['txt_align'] = TextAlignment::all();
        $datas['editable_mode'] = EditableMode::all()->sortByDesc("brand_id");
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();

        $datas['searchTagArray'] = SearchTag::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();
        $datas['specialKeywords'] = SpecialKeyword::all();
        $datas['allCategories'] = NewCategory::getAllCategoriesWithSubcategories(1);
        $datas['select_category'] = (isset($selectCategory) && $selectCategory) ? $selectCategory : [];

        if ($res->new_category_id != "" && $res->new_category_id != 0) {
            $category = NewCategory::find($res->new_category_id);
            if ($category) {
                $rootParentId = $category->getRootParentId();
                $rootParentId = $rootParentId ?: $res->new_category_id;
                $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
                $datas['sizes'] = Size::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
                $datas['themeArray'] = Theme::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
                $datas['interestArray'] = Interest::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
            } else {
                $datas['sizes'] = [];
                $datas['themeArray'] = [];
                $datas['interestArray'] = [];
            }
        } else {
            $datas['sizes'] = Size::where('status', 1)->get();
            $datas['themeArray'] = [];
            $datas['interestArray'] = [];
        }


        $datas['colors'] = Color::all();
        $datas['religions'] = Religion::all();
        $datas['formates'] = Formate::all();
        $datas['newSearchTagArray'] = NewSearchTag::all();


        return view('item/edit_item')->with('dataArray', $datas);
    }

    public function edit_seo(Design $design, $id)
    {
        $res = Design::find($id);
        if (!$res) {
            $res = Design::where('string_id', $id)->first();
        }
        if (!$res) {
            abort(404);
            return;
        }


        if ($res->related_tags) {
            $res->related_tags = implode(",", json_decode($res->related_tags, true));
        }
        if ($res->new_related_tags) {
            $res->new_related_tags = isset($res->new_related_tags) ? implode(",", json_decode($res->new_related_tags, true)) : "";
        }
        if ($res->color_id) {
            $res->color_id = isset($res->color_id) ? implode(",", json_decode($res->color_id, true)) : "";
        }

        $datas['app'] = AppCategory::all();
        $datas['cat'] = Category::all();
        $datas['item'] = $res;

        if (isset($datas['item']['new_related_tags'])) {
            $newTags = "";

            // return $datas['item'];

            foreach (explode(',', $datas['item']['new_related_tags']) as $value) {
                $dataRes = NewSearchTag::where('id', $value)->first();
                if ($dataRes) {
                    $newTags = $newTags . $dataRes->name . ",";
                }
            }

            $datas['item']['new_related_tags'] = $newTags;
        }


        $categoryId = $datas['item']['new_category_id'];
        $selectCategory = NewCategory::find($categoryId);

        if ($datas['item']->designs) {
            $jsonData = json_decode($datas['item']->designs, true);

            if (!$jsonData) {
                $path = "/" . $datas['item']->designs;
                $jsonData = json_decode(StorageUtils::get($path), true);
            }
        } else {
            $jsonData = null;
        }

        if ($jsonData) {
            $jsonData = JSONUtils::replaceMediaUrl($jsonData, "");
        }

        $datas['designs'] = $jsonData ?? [];

        $datas['thumbs'] = json_decode($datas['item']->thumb_array, true);

        $datas['bg_mode'] = BgMode::all();
        $datas['sticker_mode'] = StickerMode::all();
        $datas['resize_mode'] = ResizeMode::all();
        $datas['lock_type'] = LockType::all();
        $datas['txt_align'] = TextAlignment::all();
        $datas['editable_mode'] = EditableMode::all()->sortByDesc("brand_id");
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();

        $datas['searchTagArray'] = SearchTag::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();
        $datas['specialKeywords'] = SpecialKeyword::all();
        $datas['allCategories'] = NewCategory::getAllCategoriesWithSubcategories(1);
        $datas['select_category'] = (isset($selectCategory) && $selectCategory) ? $selectCategory : [];

        if ($res->new_category_id != "" && $res->new_category_id != 0) {
            $category = NewCategory::find($res->new_category_id);
            if ($category) {
                $rootParentId = $category->getRootParentId();
                $rootParentId = $rootParentId ?: $res->new_category_id;
                $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
                $datas['sizes'] = Size::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
                $datas['themeArray'] = Theme::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
                $datas['interestArray'] = Interest::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
            } else {
                $datas['sizes'] = [];
                $datas['themeArray'] = [];
                $datas['interestArray'] = [];
            }
        } else {
            $datas['sizes'] = Size::where('status', 1)->get();
            $datas['themeArray'] = [];
            $datas['interestArray'] = [];
        }

        $datas['colors'] = Color::all();
        $datas['religions'] = Religion::all();
        $datas['formates'] = Formate::all();
        $datas['newSearchTagArray'] = NewSearchTag::all();


        return view('item/edit_seo_raw')->with('dataArray', $datas);
    }

    public function update(Request $request, Design $design)
    {

        $pageNumbers = $request->input('design_page_number');
        for ($i = 0; $i < count($pageNumbers); $i++) {
            $pageNumber = $pageNumbers[$i];
            $post_thumb = $request->file('post_thumb_' . $pageNumber);
            if ($post_thumb != null) {
                $this->validate($request, ['post_thumb_' . $pageNumber => 'required|image|mimes:jpg,png,gif|max:2048']);
            }

            $back_image = $request->file('back_image_' . $pageNumber);
            $bg_type_id = $request->input('bg_type_id_' . $pageNumber);
            if ($bg_type_id == 0 || $bg_type_id == 1) {
                if ($back_image != null) {
                    $this->validate($request, ['back_image_' . $pageNumber => 'required|image|mimes:jpg,png,gif|max:2048']);
                }
            }
        }

        $currentuserid = Auth::user()->id;
        $idAdmin = roleManager::isAdminOrSeoManager(Auth::user()->user_type);
        $res = Design::find($request->id);

        $accessCheck = $this->isAccessByRole("design", $res->id, $res->emp_id ?? $currentuserid);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }


        $bgDataAdjust['black_point'] = 1.0;
        $bgDataAdjust['brightness'] = 1.0;
        $bgDataAdjust['brilliance'] = 1.0;
        $bgDataAdjust['contrast'] = 1.0;
        $bgDataAdjust['exposure'] = 1.0;
        $bgDataAdjust['highlight'] = 1.0;
        $bgDataAdjust['saturation'] = 1.0;
        $bgDataAdjust['shadow'] = 1.0;
        $bgDataAdjust['sharpness'] = 1.0;
        $bgDataAdjust['tint'] = 1.0;
        $bgDataAdjust['vibrance'] = 1.0;
        $bgDataAdjust['blur'] = 0;

        $bgDataFilter['intensity'] = 1.0;
        $bgDataFilter['type'] = 0;

        $bgDataCrop['top'] = 0;
        $bgDataCrop['bottom'] = 0;
        $bgDataCrop['left'] = 0;
        $bgDataCrop['right'] = 0;
        $bgDataFlip['h'] = false;
        $bgDataFlip['v'] = false;

        $designData = array();
        $auto_create = 0;
        $thumbArray = [];
        $total_pages = count($pageNumbers);

        $first_post_thumb_path = $res->post_thumb;

        $hasDesignData = true;

        for ($i = 0; $i < $total_pages; $i++) {
            $pageNumber = $pageNumbers[$i];
            $layerTypes = $request->input('layerType_' . $pageNumber);
            if ($layerTypes) {
                $textCount = 0;
                if ($hasDesignData && count($layerTypes) == 0) {
                    $hasDesignData = false;
                }
                for ($j = 0; $j < count($layerTypes); $j++) {
                    $layerType = $layerTypes[$j];
                    if ($layerType == 2) {
                        $family = $request->input('font_family_' . $pageNumber)[$textCount];
                        if (strpos(strtolower($family ?? ''), 'bahnschrift') !== false) {
                            return response()->json([
                                'error' => 'bahnschrift are not allowed'
                            ]);
                        }
                        $textCount++;
                    }
                }
            } else {
                $hasDesignData = false;
            }
        }

        for ($i = 0; $i < $total_pages; $i++) {
            $pageNumber = $pageNumbers[$i];
            $post_thumb = $request->file('post_thumb_' . $pageNumber);
            if ($post_thumb != null) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
                StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
                if ($i == 0) {
                    $first_post_thumb_path = 'uploadedFiles/thumb_file/' . $new_name;
                }
                $thumbArray[] = 'uploadedFiles/thumb_file/' . $new_name;
            } else {
                if ($i == 0) {
                    $first_post_thumb_path = $request->input('post_thumb_path_' . $pageNumber);
                }
                $post_thumb_path = $request->input('post_thumb_path_' . $pageNumber);
                $thumbArray[] = $post_thumb_path;
            }
        }

        if ($hasDesignData) {
            for ($i = 0; $i < $total_pages; $i++) {
                $pageNumber = $pageNumbers[$i];
                $post_thumb = $request->file('post_thumb_' . $pageNumber);
                $back_image = $request->file('back_image_' . $pageNumber);
                $bg_type_id = $request->input('bg_type_id_' . $pageNumber);
                $bgData['layerType'] = 0;
                $bgData['thumb'] = $thumbArray[$i];

                if ($bg_type_id == 0 || $bg_type_id == 1) {
                    if ($back_image != null) {
                        $bytes = random_bytes(20);
                        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $back_image->getClientOriginalExtension();
                        StorageUtils::storeAs($back_image, 'uploadedFiles/bg_file', $new_name);
                        $bgData['image'] = 'uploadedFiles/bg_file/' . $new_name;
                    } else {
                        $bgData['image'] = $request->input('back_image_path_' . $pageNumber);
                    }
                    $bgData['color'] = null;
                } else {
                    $bgData['image'] = null;
                    $bgData['color'] = $request->input('color_code_' . $pageNumber);
                }
                $bgData['width'] = 100;
                $bgData['height'] = 100;
                $bgData['type'] = $bg_type_id;
                $bgData['gradAngle'] = $request->input('grad_angle_' . $pageNumber);
                $bgData['gradRatio'] = $request->input('grad_ratio_' . $pageNumber);
                $bgData['animation'] = 0;
                $bgData['videoStartTime'] = 0;
                $bgData['videoEndTime'] = 0;
                $bgData['autoCreate'] = false;
                $bgData['adjustment'] = $bgDataAdjust;
                $bgData['filter'] = $bgDataFilter;
                $bgData['crop'] = $bgDataCrop;
                $bgData['flip'] = $bgDataFlip;

                $layersData = array();
                $stickerCount = 0;
                $textCount = 0;
                $layerTypes = $request->input('layerType_' . $pageNumber);
                for ($j = 0; $j < count($layerTypes); $j++) {
                    $layerType = $layerTypes[$j];
                    if ($layerType == 1) {
                        $st_image = null;
                        try {
                            $st_image = $request->file('st_image_' . $pageNumber)[$stickerCount];
                        } catch (\Exception $e) {
                            $st_image = null;
                        }
                        $new_name = null;
                        if ($st_image != null) {
                            $bytes = random_bytes(20);
                            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $st_image->getClientOriginalExtension();
                            StorageUtils::storeAs($st_image, 'uploadedFiles/sticker_file', $new_name);
                            $new_name = 'uploadedFiles/sticker_file/' . $new_name;
                        } else {
                            $new_name = $request->input('st_image_path_' . $pageNumber)[$stickerCount];
                        }

                        $layer['layerType'] = 1;
                        $layer['isVideo'] = false;
                        $layer['image'] = $new_name;
                        $layer['left'] = $request->input('st_x_pos_' . $pageNumber)[$stickerCount];
                        $layer['top'] = $request->input('st_y_pos_' . $pageNumber)[$stickerCount];
                        $layer['originX'] = 'left';
                        $layer['originY'] = 'top';
                        $layer['width'] = $request->input('st_width_' . $pageNumber)[$stickerCount];
                        $layer['height'] = $request->input('st_height_' . $pageNumber)[$stickerCount];
                        $layer['scaleX'] = $request->input('st_scale_x_' . $pageNumber)[$stickerCount];
                        $layer['scaleY'] = $request->input('st_scale_y_' . $pageNumber)[$stickerCount];
                        $layer['rotation'] = $request->input('st_rotation_' . $pageNumber)[$stickerCount];
                        $layer['opacity'] = $request->input('st_opacity_' . $pageNumber)[$stickerCount];
                        $layer['type'] = $request->input('st_type_' . $pageNumber)[$stickerCount];
                        $layer['color'] = $request->input('st_color_' . $pageNumber)[$stickerCount];
                        $layer['resizeType'] = $request->input('st_resize_' . $pageNumber)[$stickerCount];
                        $layer['lockType'] = $request->input('st_lock_type_' . $pageNumber)[$stickerCount];
                        $layer['animation'] = 0;
                        $layer['adjustment'] = $bgDataAdjust;
                        $layer['filter'] = $bgDataFilter;
                        $layer['crop'] = $bgDataCrop;
                        $layer['flip'] = $bgDataFlip;
                        $layer['videoStartTime'] = 0;
                        $layer['videoEndTime'] = 0;

                        if ($request->input('st_is_editable_' . $pageNumber)[$stickerCount] == 1) {
                            $auto_create = 1;
                            $bgData['autoCreate'] = true;
                        }

                        $edt_name = $request->input('st_editable_title_' . $pageNumber)[$stickerCount];
                        $edt_data = EditableMode::where('name', $edt_name)->first();

                        $layer['isEditable'] = $request->input('st_is_editable_' . $pageNumber)[$stickerCount];
                        $layer['isUrl'] = $request->input('st_is_url_' . $pageNumber)[$stickerCount];
                        $layer['editableTitle'] = $edt_name;
                        if ($edt_data != null) {
                            $layer['editableId'] = $edt_data->brand_id;
                        } else {
                            $layer['editableId'] = null;
                        }
                        $layersData[] = $layer;
                        $stickerCount++;
                    } else {
                        $layerText['layerType'] = 2;
                        $layerText['isVideo'] = false;
                        $layerText['text'] = $request->input('text_' . $pageNumber)[$textCount];
                        $layerText['font'] = $request->input('font_family_' . $pageNumber)[$textCount];
                        $layerText['width'] = $request->input('txt_width_' . $pageNumber)[$textCount];
                        $layerText['height'] = $request->input('txt_height_' . $pageNumber)[$textCount];
                        $layerText['scaleX'] = $request->input('txt_scale_x_' . $pageNumber)[$textCount];
                        $layerText['scaleY'] = $request->input('txt_scale_y_' . $pageNumber)[$textCount];
                        $layerText['left'] = $request->input('txt_x_pos_' . $pageNumber)[$textCount];
                        $layerText['top'] = $request->input('txt_y_pos_' . $pageNumber)[$textCount];
                        $layerText['originX'] = 'left';
                        $layerText['originY'] = 'top';
                        $layerText['size'] = $request->input('txt_size_' . $pageNumber)[$textCount];
                        $layerText['color'] = $request->input('txt_color_' . $pageNumber)[$textCount];
                        $layerText['opacity'] = $request->input('txt_opacity_' . $pageNumber)[$textCount];
                        $layerText['rotation'] = $request->input('txt_rotation_' . $pageNumber)[$textCount];
                        $layerText['update'] = $request->input('txt_update_' . $pageNumber)[$textCount] == 1 ? true : false;

                        if ($request->input('is_editable_' . $pageNumber)[$textCount] == 1) {
                            $auto_create = 1;
                            $bgData['autoCreate'] = true;
                        }

                        $edt_name = $request->input('editable_title_' . $pageNumber)[$textCount];
                        $edt_data = EditableMode::where('name', $edt_name)->first();

                        $layerText['isEditable'] = $request->input('is_editable_' . $pageNumber)[$textCount];
                        $layerText['isUrl'] = $request->input('is_url_' . $pageNumber)[$textCount];
                        $layerText['editableTitle'] = $edt_name;
                        if ($edt_data != null) {
                            $layerText['editableId'] = $edt_data->brand_id;
                        } else {
                            $layerText['editableId'] = null;
                        }

                        $layerText['curve'] = $request->input('txt_curve_' . $pageNumber)[$textCount];

                        $alignment = $request->input('txt_align_' . $pageNumber)[$textCount];
                        $layerTextFormat['alignment'] = $alignment;
                        $layerTextFormat['textAlign'] = TextAlignment::where('value', $alignment)->first()->stringVal;
                        $layerTextFormat['bold'] = false;
                        $layerTextFormat['italic'] = false;
                        $layerTextFormat['capital'] = false;
                        $layerTextFormat['underline'] = false;
                        $layerTextFormat['bulletSpan'] = false;
                        $layerTextFormat['numericSpan'] = false;
                        $layerText['format'] = $layerTextFormat;

                        $layerTextSpacing['anchor'] = '1';
                        $layerTextSpacing['letter'] = $request->input('word_spacing_' . $pageNumber)[$textCount];
                        $layerTextSpacing['line'] = $request->input('line_spacing_' . $pageNumber)[$textCount];
                        $layerTextSpacing['lineMultiplier'] = $request->input('lineSpaceMultiplier_' . $pageNumber)[$textCount];
                        $layerText['spacing'] = $layerTextSpacing;

                        $layerText['effects'] = $request->input('txt_effect_' . $pageNumber)[$textCount];

                        $layersData[] = $layerText;
                        $textCount++;
                    }
                }

                $bgData['layers'] = $layersData;
                $designData[] = $bgData;
            }

            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.json';
            $filePath = 'uploadedFiles/fab_jsons/' . $new_name;

            StorageUtils::put($filePath, json_encode($designData));
            $res->designs = $filePath;

        } else {
            $res->designs = null;
        }

        $res->post_thumb = $first_post_thumb_path;
        $res->thumb_array = json_encode($thumbArray);

        $res->total_pages = $total_pages;
        $res->auto_create = $auto_create;

        $res->has_bug = 0;

        $res->save();

        HelperController::newCatChildUpdatedAt($res->new_category_id);

        DB::table('update_template_data_history')->insert([
            'user_id' => $currentuserid,
            'template_id' => $res->id,
            'type' => 0,
        ]);

        return response()->json([
            'success' => 'Done'
        ]);
    }

    public function update_seo(Request $request, Design $design)
    {
        $post_thumb = $request->file('post_thumb');
        $additional_thumb = $request->file('additional_thumb');

        if ($post_thumb) {
            $this->validate($request, ['post_thumb' => 'required|image|mimes:jpg,png,gif,webp,jpeg|max:2048']);
        }

        if ($additional_thumb) {
            $this->validate($request, ['additional_thumb' => 'required|image|mimes:jpg,png,gif,webp,jpeg|max:2048']);
        }

        $pageNumbers = $request->input('design_page_number');
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);

        $res = Design::find($request->id);
        if (!$res)
            return response()->json(['error' => "Data not found"]);

        $id_name = $request->input('id_name');
        $canonical_link = $request->input('canonical_link');
        $cat = NewCategory::find($request->input('new_category_id'));
        if ($cat && $cat->parent_category_id == 0) {
            return response()->json(['error' => 'Use Child Category.']);
        }

        if (!RoleManager::isAdmin(Auth::user()->user_type) && $currentuserid != $res->seo_emp_id) {
            if ($idAdmin) {
                $canonicalError = ContentManager::validateCanonicalLink($canonical_link, 0, $res->id_name);
                if ($canonicalError)
                    return response()->json(['error' => $canonicalError]);

                $res->canonical_link = $canonical_link;

                $res->new_category_id = $request->input('new_category_id');
                if ($request->has('status')) {
                    $res->status = $request->status;
                }
                
                // SEO Manager માટે priority અને frequency પણ update કરો
                if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
                    $res->priority = $request->input('priority', $res->priority ?? 0.90);
                    $res->frequency = $request->input('frequency', $res->frequency ?? 'daily');
                }

                $res->save();
                return response()->json(['success' => 'done']);
            }
        }

        if ($cat->seo_emp_id == null || $cat->seo_emp_id == 0) {
            return response()->json(['error' => "SEO Assigner not found in related category."]);
        }

        $seoAssignerID = $res->newCategory->seo_emp_id;
        $accessCheck = $this->isAccessByRole("seo", $request->id, $res->seo_emp_id ?? $currentuserid, [$seoAssignerID]);
        if ($accessCheck)
            return response()->json(['error' => $accessCheck]);

        if ($res->id_name == null || $idAdmin || RoleManager::isSeoExecutive(Auth::user()->user_type)) {
            $id_name = str_replace($res->string_id . '-', '', $id_name);
            if (preg_match('/\s/', $id_name))
                return response()->json(['error' => 'ID Name whitespace.']);
            if (preg_match('/[A-Z]/', $id_name))
                return response()->json(['error' => 'ID Name contains a capital word.']);
            if (preg_match('/[^\w\s-]/', $id_name))
                return response()->json(['error' => 'ID Name contains a special character.']);
            $res->id_name = $res->string_id . '-' . $id_name;
        }

        $cat = NewCategory::find($request->input('new_category_id'));
        if ($cat && $cat->parent_category_id == 0) {
            return response()->json(['error' => 'Use Child Category.']);
        }

        if (strcasecmp($request->input('post_name'), $request->input('meta_title')) === 0) {
            return response()->json(['error' => "Post Name and Meta Title should not be the same."]);
        }

        $oldNewCategoryID = $res->new_category_id;
        $oldSearchTag = json_decode($res->new_related_tags ?? "[]");
        $oldSpecialKeywords = json_decode($res->special_keywords ?? "[]");

        $res->canonical_link = $canonical_link;
        $res->category_id = $request->input('category_id');
        $res->new_category_id = $request->input('new_category_id');
        $res->style_id = $request->input('styles');
        $res->special_keywords = $request->input('special_keywords');
        $res->post_name = $request->input('post_name');
        $res->meta_title = $request->input('meta_title');
        $res->h2_tag = $request->input('h2_tag');
        $res->width = $request->input('width');
        $res->height = $request->input('height');
        $res->ratio = round($res->width / $res->height, 2);
        $res->description = $request->input('description');
        $res->meta_description = $request->input('meta_description');

        $keywords = explode(',', $request->input('keywords'));
        $res->related_tags = json_encode($keywords);
        $newKeywords = explode(',', $request->input('new_keywords'));
        $res->cta = json_encode(HelperController::processCTA($request));

        $newTags = [];
        foreach ($newKeywords as $value) {
            $dataRes = NewSearchTag::where('name', $value)->whereJsonContains('new_category_id', $request->input('new_category_id'))->first();
            if ($dataRes)
                $newTags[] = $dataRes->id;
        }
        $res->new_related_tags = json_encode($newTags);

        // color
        $res->color_id = json_encode(array_filter(
            is_array($request->input('color_ids')) ? $request->input('color_ids') : explode(',', $request->input('color_ids'))
        ));

        // religion_id (preserve if not submitted)
        $existingReligion = json_decode($res->religion_id ?? "[]", true);
        $religionInput = $request->input('religion_id');
        if ($religionInput !== null) {
            $religionArray = is_array($religionInput) ? $religionInput : explode(',', $religionInput);
            $res->religion_id = json_encode(array_filter($religionArray));
        } else {
            $res->religion_id = json_encode($existingReligion);
        }

        // interest_id (preserve if not submitted)
        $existingInterest = json_decode($res->interest_id ?? "[]", true);
        $interestInput = $request->input('interest_id');
        if ($interestInput !== null) {
            $interestArray = is_array($interestInput) ? $interestInput : explode(',', $interestInput);
            $res->interest_id = json_encode(array_filter($interestArray));
        } else {
            $res->interest_id = json_encode($existingInterest);
        }

        // lang_id (preserve if not submitted)
        $existingLang = json_decode($res->lang_id ?? "[]", true);
        $langInput = $request->input('lang_id');
        if ($langInput !== null) {
            $langArray = is_array($langInput) ? $langInput : explode(',', $langInput);
            $res->lang_id = json_encode(array_filter($langArray));
        } else {
            $res->lang_id = json_encode($existingLang);
        }

        // theme
        $res->theme_id = json_encode(array_filter(
            is_array($request->input('theme_id')) ? $request->input('theme_id') : explode(',', $request->input('theme_id'))
        ));

        $date_range = $request->input('date_range');
        if ($date_range != null) {
            $date_range = str_replace(' ', '', $date_range);
            $split_date = explode("-", $date_range);
            if (sizeof($split_date) < 2)
                return response()->json(['error' => 'Please select end date.']);
            $res->start_date = $split_date[0];
            $res->end_date = $split_date[1];
        }

        if (!RoleManager::isSeoIntern(Auth::user()->user_type)) {
            $res->status = $request->input('status');
            $res->is_premium = $request->input('is_premium');
            $res->is_freemium = $request->input('is_freemium');
        }

        $res->priority = $request->input('priority', $res->priority ?? 0.90);
        $res->frequency = $request->input('frequency', $res->frequency ?? 'daily');

        $res->total_pages = count($pageNumbers);
        $res->orientation = $request->orientation;
        $res->template_size = $request->template_size;
        $res->default_thumb_pos = $request->default_thumb_pos;

        if ($res->no_index == 0) {
            if (!isset($res->h2_tag) || !isset($res->description) || !isset($res->meta_description)) {
                return response()->json(['error' => 'Page is index please add H2, Description and Meta Description']);
            }
        } else {
            if (isset($res->h2_tag) || isset($res->description) || isset($res->meta_description)) {
                if (!isset($res->h2_tag))
                    return response()->json(['error' => 'Description or meta description available so H2 is Required']);
                if (!isset($res->description))
                    return response()->json(['error' => 'H2 or meta description available so description is Required']);
                if (!isset($res->meta_description))
                    return response()->json(['error' => 'Description or h2 available so Meta Description is Required']);
            }
        }

        if ($res->status == 1) {
            $missingFields = self::checkRequiredValidation($res);
            if (!empty($missingFields)) {
                return response()->json(['error' => 'The following fields are missing or empty: ' . implode(', ', $missingFields)]);
            }
        }

        if ($additional_thumb) {
            $new_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $additional_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($additional_thumb, 'uploadedFiles/thumb_file', $new_name);
            $res->additional_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        }

        if ($post_thumb) {
            $new_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
            $new_post_thumb_path = 'uploadedFiles/thumb_file/' . $new_name;
            $res->post_thumb = $new_post_thumb_path;

            // Update thumb_array[0] to match post_thumb (same as edit_item behavior)
            $thumbArray = json_decode($res->thumb_array ?? '[]', true);
            if (is_array($thumbArray) && count($thumbArray) > 0) {
                $thumbArray[0] = $new_post_thumb_path;
                $res->thumb_array = json_encode($thumbArray);
            }
        }

        $res->save();
        // Moved to Design model booted function
        // self::clearCacheByTag($request->input('special_keywords'), $oldSpecialKeywords);
        // self::updateDesignCount($res->new_category_id, $oldNewCategoryID, newTags: $newTags, oldTags: $oldSearchTag);
        HelperController::newCatChildUpdatedAt($res->new_category_id);

        DB::table('update_template_data_history')->insert([
            'user_id' => $currentuserid,
            'template_id' => $res->id,
            'type' => 1,
        ]);

        return response()->json(['success' => 'Done']);
    }

    public function removePostThumb($id)
    {
        try {
            $design = Design::find($id);

            if (!$design) {
                return response()->json(['error' => 'Design not found'], 404);
            }

            // Check access permissions
            $currentuserid = Auth::user()->id;
            if (!$design->newCategory || $design->newCategory->seo_emp_id == null) {
                return response()->json(['error' => "SEO Assigner not found in related category."], 403);
            }

            $seoAssignerID = $design->newCategory->seo_emp_id;
            $accessCheck = $this->isAccessByRole("seo", $id, $design->seo_emp_id ?? $currentuserid, [$seoAssignerID]);
            if ($accessCheck) {
                return response()->json(['error' => $accessCheck], 403);
            }

            // Remove reference from database only, keep file in storage
            if ($design->post_thumb) {
                $design->post_thumb = null;
                $design->save();
            }

            return response()->json(['success' => 'Post Thumb removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove Post Thumb: ' . $e->getMessage()], 500);
        }
    }

    public function removeAdditionalThumb($id)
    {
        try {
            $design = Design::find($id);

            if (!$design) {
                return response()->json(['error' => 'Design not found'], 404);
            }

            // Check access permissions
            $currentuserid = Auth::user()->id;
            if (!$design->newCategory || $design->newCategory->seo_emp_id == null) {
                return response()->json(['error' => "SEO Assigner not found in related category."], 403);
            }

            $seoAssignerID = $design->newCategory->seo_emp_id;
            $accessCheck = $this->isAccessByRole("seo", $id, $design->seo_emp_id ?? $currentuserid, [$seoAssignerID]);
            if ($accessCheck) {
                return response()->json(['error' => $accessCheck], 403);
            }

            // Remove reference from database only, keep file in storage
            if ($design->additional_thumb) {
                $design->additional_thumb = null;
                $design->save();
            }

            return response()->json(['success' => 'Additional Thumb removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove Additional Thumb: ' . $e->getMessage()], 500);
        }
    }




    public function destroy(Request $request)
    {

        $currentuserid = Auth::user()->id;

        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type);

        if ($idAdmin) {
            $res = Design::find($request->id);
            $res->deleted = 1;
            $res->save();

            // try {
            //     unlink(storage_path("app/public/" . $res->post_thumb));
            // } catch (\Exception $e) {
            // }

            // for ($i = 0; $i < $st_count; $i++) {
            //    try {
            //         unlink(storage_path("app/public/".$component_info[$i]['thumb']));
            //    } catch (\Exception $e) {
            //    }
            //    try {
            //         unlink(storage_path("app/public/".$component_info[$i]['image']));
            //    } catch (\Exception $e) {
            //    }
            // }

            // Design::destroy(array('id', $request->id));
        }

        return redirect('show_item');
    }

    public function reset_date(Request $request)
    {

        $res = Design::find($request->id);
        $res->start_date = null;
        $res->end_date = null;
        $res->save();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function reset_creation(Request $request)
    {

        $res = Design::find($request->id);
        $res->created_at = Carbon::now();
        $res->updated_at = Carbon::now();
        $res->latest = 1;
        $res->save();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function status_update(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdminOrSeoManager(Auth::user()->user_type) || RoleManager::isSeoExecutive(Auth::user()->user_type);

        $res = Design::find($request->id);

        if ($idAdmin) {

            if ($res->status == 1) {
                $res->status = 0;
            } else {
                $missingFields = self::checkRequiredValidation($res);
                if (!empty($missingFields)) {
                    return response()->json([
                        'error' => 'The following fields are missing or empty: ' . implode(', ', $missingFields),
                    ]);
                }
                $res->status = 1;
            }

            $res->save();

            $newTags = json_decode($res->new_related_tags ?? "[]");

            // Moved to Design model booted function
            // self::updateDesignCount($res->new_category_id, newTags: $newTags, increament: $res->status === 0 ? -1 : 1);

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => false
            ]);
        }


    }

    public function pinned_update(Request $request)
    {
        $currentuserid = Auth::user()->user_type;
        $idAdmin = RoleManager::isAdminOrSeoManager($currentuserid) || RoleManager::isSeoExecutive($currentuserid);

        if ($idAdmin) {
            $res = Design::find($request->id);

            if ($res->pinned == 1) {
                $res->pinned = 0;
            } else {
                $res->pinned = 1;
            }

            $res->save();

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => false
            ]);
        }
    }

    public function editorChoiceUpdate(Request $request)
    {

        $res = Design::find($request->id);
        $idAdmin = roleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        if ($idAdmin) {
            if ($res->editor_choice == 1) {
                $res->editor_choice = 0;
            } else {
                $res->editor_choice = 1;
            }

            $res->save();

            return response()->json([
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'error' => "Not valid request",
                'checked' => $res->status == 1 ? true : false
            ]);
        }
    }

    public function assignSeo(Request $request)
    {
        $isAccess = RoleManager::isSeoExecutive(Auth::user()->user_type);
        if ($isAccess) {
            $res = Design::find($request->id);
            // dd($res->seo_emp_id);
            $res->seo_emp_id = $request->seo_emp_id;
            $res->seo_assigner_id = Auth::user()->id;
            $res->save();
            return response()->json([
                'status' => true,
                'success' => "done"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => "Not Permission to assign",
            ]);
        }
    }

    public function assignNewCategory(Request $request)
    {
        if (!RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $request->validate([
            'design_id' => 'required|integer|exists:designs,id',
            'new_category_id' => 'required|integer|exists:new_categories,id',
        ]);

        $design = Design::find($request->design_id);
        $category = NewCategory::find($request->new_category_id);
        $parentCategory = $category->parent_category_id ? NewCategory::find($category->parent_category_id) : null;

        if (!$design || !$category) {
            return response()->json(['status' => false, 'message' => 'Invalid data.']);
        }

        $oldCategoryId = $design->new_category_id;

        $design->new_category_id = $request->new_category_id;
        $design->save();

        $oldTags = is_array($design->tag_id) ? $design->tag_id : json_decode($design->tag_id ?? '[]', true);
        $newTags = [];

        // Moved to Design model booted function
        // self::updateDesignCount(
        //     newCatId: $request->new_category_id,
        //     oldNewCategoryID: $oldCategoryId,
        //     newTags: $newTags,
        //     oldTags: $oldTags,
        //     increament: 1
        // );

        $seoEmpId = $category->seo_emp_id ?? null;
        $users = collect();

        if (!empty($seoEmpId)) {
            $users = User::where('id', $seoEmpId)->select('id', 'name')->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'New category assigned successfully.',
            'category_name' => $category->category_name,
            'parent_name' => $parentCategory->category_name ?? '',
            'seo_users' => $users,
        ]);
    }


    public function updateTemplatePremium(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:free,freemium,premium',
        ]);
        $idAdmin = RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type);
        if ($idAdmin) {
            $template = Design::findOrFail($request->id);
            switch ($request->type) {
                case 'premium':
                    $template->is_premium = 1;
                    $template->is_freemium = 0;
                    break;
                case 'freemium':
                    $template->is_premium = 0;
                    $template->is_freemium = 1;
                    break;
                case 'free':
                    $template->is_premium = 0;
                    $template->is_freemium = 0;
                    break;
            }

            $template->save();
            return response()->json(['success' => "done"]);
        } else {
            return response()->json([
                'error' => "Not valid request",
            ]);
        }
    }


    public function checkRequiredValidation($res): array
    {
        $requiredFields = [
            ['value' => $res->post_name, 'name' => 'Post Name'],
            ['value' => $res->id_name, 'name' => 'ID Name'],
            ['value' => $res->category_id, 'name' => 'Category'],
            ['value' => $res->related_tags, 'name' => 'Related Tags'],
            ['value' => $res->new_related_tags, 'name' => 'New Keywords'],
            ['value' => $res->lang_id, 'name' => 'Language'],
            ['value' => $res->theme_id, 'name' => 'Theme'],
            ['value' => $res->style_id, 'name' => 'Style'],
            ['value' => $res->orientation, 'name' => 'Orientation'],
            ['value' => $res->ratio, 'name' => 'Ratio'],
            ['value' => $res->width, 'name' => 'Width'],
            ['value' => $res->height, 'name' => 'Height'],
        ];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            $value = $field['value'];
            $name = $field['name'];
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                $missingFields[] = $name;
            }
        }
        return $missingFields;
    }


    public function getCustomData(Request $request)
    {
        $value = $request->get('value');

        if ($value == 0) {
            $data = DB::table('templates')->where('status', '1')->get();
        } else {
            $data = DB::table('templates')->where('app_id', $value)->where('status', '1')->get();
        }

        // return Response::json($data);
        if ($data->count() == 0) {
            return "";
        } else {
            $output = '';
            foreach ($data as $item) {

                $output .= '<tr>
                                <td class="table-plus">' . $item->id . '</td>

                                <td>' . HelperController::getAppName($item->app_id) . '</td>

                                <td>' . HelperController::getCatName($item->category_id) . '</td>

                                <td>' . $item->post_name . '</td>

                                <td><img src="' . $item->post_thumb . '" width="100"/></td>

                                <td>' . $this->getPremiumHTML($item->id, $item->is_premium) . '</td>

                                <td>' . $this->getStatusHTML($item->id, $item->status) . '</td>

                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">

                                            <Button class="dropdown-item" onclick="notification_click(' . $item->id . ')" data-backdrop="static" data-toggle="modal" data-target="#send_notification_model"><i class="dw dw-notification1" ></i>Send Notification</Button>

                                            <a class="dropdown-item" href="edit_item/' . $item->id . '"><i class="dw dw-edit2"></i> Edit</a>

                                            <a class="dropdown-item" href="delete_item/' . $item->id . '"><i class="dw dw-delete-3"></i> Delete</a>

                                            <Button class="dropdown-item" onclick="reset_click(' . $item->id . ')" data-backdrop="static" data-toggle="modal" data-target="#reset_date_model"><i class="icon-copy dw dw-refresh" ></i>Reset Date</Button>

                                        </div>
                                    </div>


                                </td>

                            </tr>';
            }
            return $output;
        }
    }

    public static function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (Design::where('string_id', $string_id)->exists());
        return $string_id;
    }

    function getPremiumHTML($id, $is_premium)
    {
        $htmlString = '';
        if ($is_premium == '1') {
            $htmlString = '<td><label id="premium_label_' . $id . '" style="display: none;">TRUE</label><Button style="border: none" onclick="premium_click(' . $id . ')"><input type="checkbox" checked class="switch-btn" data-size="small" data-color="#0059b2" /></Button></td>';
        } else {
            $htmlString = '<td><label id="premium_label_' . $id . '" style="display: none;">FALSE</label><Button style="border: none" onclick="premium_click(' . $id . ')"><input type="checkbox" class="switch-btn" data-size="small" data-color="#0059b2" /></Button></td>';
        }
        return $htmlString;
    }

    function getStatusHTML($id, $status)
    {
        $htmlString = '';
        if ($status == '1') {
            $htmlString = '<td><label id="status_label_' . $id . '" style="display: none;">Live</label><Button style="border: none" onclick="status_click(' . $id . ')"><input type="checkbox" checked class="switch-btn" data-size="small" data-color="#0059b2" /></Button></td>';
        } else {
            $htmlString = '<td><label id="status_label_' . $id . '" style="display: none;">Not Live</label><Button style="border: none" onclick="status_click(' . $id . ')"><input type="checkbox" class="switch-btn" data-size="small" data-color="#0059b2" /></Button></td>';
        }
        return $htmlString;
    }

    public function getNewSearchTag(Request $request)
    {
        // $newSearchTags = NewSearchTag::where('new_category_id', 'LIKE', "%\"" . $request->cateId . "\"%")->where('status', 1)->get();
        $newSearchTags = NewSearchTag::whereJsonContains('new_category_id', $request->cateId)->get();

        if (isset($newSearchTags) && !empty($newSearchTags)) {
            return response()->json([
                'status' => true,
                'success' => $newSearchTags,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => 'New Search tags are not found.',
            ]);
        }
    }


}

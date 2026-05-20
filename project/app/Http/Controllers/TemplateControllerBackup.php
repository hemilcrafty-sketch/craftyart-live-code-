<?php

namespace App\Http\Controllers;

use App\Models\Font;
use App\Models\BgMode;
use App\Models\EditableMode;
use App\Models\ResizeMode;
use App\Models\LockType;
use App\Models\StickerMode;
use App\Models\Template;
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
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use App\Http\Controllers\Utils\StorageUtils;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Async\Pool;

class TemplateControllerBackup extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    }

    public function show(Request $request)
    {

        $paginate_count = 20;

        $currentuserid = Auth::user()->id;

        $idAdmin = HelperController::isAdmin($currentuserid);

        $condition = "=";

        if ($idAdmin) {
            $condition = "!=";
            $currentuserid = -1;
        }

        $temp_data = [];
        $temp_data_count = 0;
        $sortingField = $request->has('sortingField') ? $request->get('sortingField') : 'created_at';
        if ($sortingField == '') {
            $sortingField = 'created_at';
        }
        if ($request->has('query')) {

            $cat_id = $request->input('query');
            $cat_data = Category::where('category_name', 'LIKE', '%' . $request->input('query') . '%')->first();
            if ($cat_data != null) {
                $cat_id = $cat_data->id;
            }

            $app_id = $request->input('query');
            $app_data = AppCategory::where('app_name', 'LIKE', '%' . $request->input('query') . '%')->first();
            if ($app_data != null) {
                $app_id = $app_data->id;
            }


            $temp_data_count = Design::where('id', 'LIKE', '%' . $request->input('query') . '%')
                ->where('emp_id', $condition, $currentuserid)
                ->orWhere('post_name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('category_id', 'LIKE', '%' . $cat_id . '%')
                ->orWhere('app_id', 'LIKE', '%' . $app_id . '%')
                ->orderBy($sortingField, 'desc')
                ->count();

            $temp_data = Design::where('id', 'LIKE', '%' . $request->input('query') . '%')
                ->where('emp_id', $condition, $currentuserid)
                ->orWhere('post_name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('category_id', 'LIKE', '%' . $cat_id . '%')
                ->orWhere('app_id', 'LIKE', '%' . $app_id . '%')
                ->orderBy($sortingField, 'desc')
                ->paginate($paginate_count);
        } else {
            $temp_data_count = Design::where('emp_id', $condition, $currentuserid)->orderBy($sortingField, 'desc')->count();
            $temp_data = Design::where('emp_id', $condition, $currentuserid)->orderBy($sortingField, 'desc')->paginate($paginate_count);
        }

        $total = $temp_data_count;
        $count = $total;
        $total_diff = $paginate_count - 1;
        $diff = $paginate_count - 1;

        if ($total < $paginate_count) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * $paginate_count;
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

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['item'] = $temp_data;

        return view('item/show_item')->with('itemArray', $data);
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
        $datas['editable_mode'] = EditableMode::all();
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();
        $datas['themeArray'] = Theme::all();
        $datas['searchTagArray'] = SearchTag::all();
        $datas['interestArray'] = Interest::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();

        return view('item/create_item')->with('datas', $datas);

    }

    public function store(Request $request)
    {

        $currentuserid = Auth::user()->id;

        $res = new Template();
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

    public function edit(Template $template, $id)
    {
        $datas['app'] = AppCategory::all();
        $datas['cat'] = Category::all();
        $datas['item'] = Template::find($id);
        $datas['bg_mode'] = BgMode::all();
        $datas['sticker_mode'] = StickerMode::all();
        $datas['resize_mode'] = ResizeMode::all();
        $datas['lock_type'] = LockType::all();
        $datas['txt_align'] = TextAlignment::all();
        $datas['editable_mode'] = EditableMode::all();
        $datas['component_info'] = json_decode($datas['item']->component_info, true);
        $datas['text_info'] = json_decode($datas['item']->text_info, true);
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();
        $datas['themeArray'] = Theme::all();
        $datas['searchTagArray'] = SearchTag::all();
        $datas['interestArray'] = Interest::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();

        return view('item/edit_item')->with('dataArray', $datas);
    }

    public function edit2(Template $template, $id)
    {
        $res = Design::find($id);

        $datas['app'] = AppCategory::all();
        $datas['cat'] = Category::all();
        $datas['item'] = $res;
        $datas['designs'] = json_decode($datas['item']->designs, true);
        $datas['bg_mode'] = BgMode::all();
        $datas['sticker_mode'] = StickerMode::all();
        $datas['resize_mode'] = ResizeMode::all();
        $datas['lock_type'] = LockType::all();
        $datas['txt_align'] = TextAlignment::all();
        $datas['editable_mode'] = EditableMode::all();
        $datas['stkCat'] = StickerCategory::all();
        $datas['bgCat'] = BgCategory::all();
        $datas['subCatArray'] = SubCategory::all();
        $datas['styleArray'] = Style::all();
        $datas['themeArray'] = Theme::all();
        $datas['searchTagArray'] = SearchTag::all();
        $datas['interestArray'] = Interest::all();
        $datas['langArray'] = Language::all();
        $datas['fonts'] = Font::all();

        return view('item/edit_item_2')->with('dataArray', $datas);
    }

    public function update(Request $request, Template $template)
    {

        $post_thumb = $request->file('post_thumb');
        if ($post_thumb != null) {
            $this->validate($request, ['post_thumb' => 'required|image|mimes:jpg,png,gif|max:2048']);
        }

        $dy_st_image = $request->file('dy_st_image');
        if ($dy_st_image != null) {
            $this->validate($request, ['dy_st_image.*' => 'required|image|mimes:jpg,png,gif|max:2048']);
        }

        $back_image = $request->file('back_image');
        $bg_type_id = $request->input('bg_type_id');
        if ($bg_type_id == 0 || $bg_type_id == 1) {
            if ($back_image != null) {
                $this->validate($request, ['back_image' => 'required|image|mimes:jpg,png,gif|max:2048']);
            }
        }

        $res = Template::find($request->id);

        $res->category_id = $request->input('category_id');
        $res->sub_cat_id = $request->input('sub_category_id');
        $res->style_id = $request->input('styles');
        $res->theme_id = $request->input('theme_id');
        $res->bg_cat_id = $request->input('bg_cat');
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
        $res->description = $request->input('description');
        $res->keywords = $request->input('keywords');

        if ($post_thumb != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
            $res->post_thumb = 'uploadedFiles/thumb_file/' . $new_name;
        }

        if ($bg_type_id == 0 || $bg_type_id == 1) {
            if ($back_image != null) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $back_image->getClientOriginalExtension();
                StorageUtils::storeAs($back_image, 'uploadedFiles/bg_file', $new_name);
                $res->back_image = 'uploadedFiles/bg_file/' . $new_name;
            }
            $res->back_color = null;
        } else {
            $res->back_image = null;
            $res->back_color = $request->input('color_code');
        }

        $st_image_path = $request->input('st_image_path');
        $st_count = count($st_image_path);
        $sticker_data = array();
        for ($i = 0; $i < $st_count; $i++) {
            $st_image = null;
            try {
                $st_image = $request->file('st_image')[$i];
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
                $new_name = $st_image_path[$i];
            }

            $st_data = array();
            $st_data['st_image'] = $new_name;
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
            $st_data['st_order'] = $request->input('st_order')[$i];
            $st_data['st_cat'] = $request->input('st_cat')[$i];
            $st_data['is_editable'] = $request->input('st_is_editable')[$i];
            $st_data['editable_title'] = $request->input('st_editable_title')[$i];
            $st_data['st_id'] = 0;
            $sticker_data[] = $st_data;
        }


        if ($dy_st_image != null) {
            $st_count = count($dy_st_image);
            for ($i = 0; $i < $st_count; $i++) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $dy_st_image[$i]->getClientOriginalExtension();
                StorageUtils::storeAs($dy_st_image[$i], 'uploadedFiles/sticker_file', $new_name);
                $st_data = array();
                $st_data['st_image'] = 'uploadedFiles/sticker_file/' . $new_name;
                $st_data['st_x_pos'] = $request->input('dy_st_x_pos')[$i];
                $st_data['st_y_pos'] = $request->input('dy_st_y_pos')[$i];
                $st_data['st_width'] = $request->input('dy_st_width')[$i];
                $st_data['st_height'] = $request->input('dy_st_height')[$i];
                $st_data['st_rotation'] = $request->input('dy_st_rotation')[$i];
                $st_data['st_opacity'] = $request->input('dy_st_opacity')[$i];
                $st_data['st_type'] = $request->input('dy_st_type')[$i];
                $st_data['st_color'] = $request->input('dy_st_color')[$i];
                $st_data['st_resize'] = $request->input('dy_st_resize')[$i];
                $st_data['st_lock_type'] = $request->input('dy_st_lock_type')[$i];
                $st_data['st_order'] = $request->input('dy_st_order')[$i];
                $st_data['st_cat'] = $request->input('dy_st_cat')[$i];
                $st_data['is_editable'] = $request->input('dy_st_is_editable')[$i];
                $st_data['editable_title'] = $request->input('dy_st_editable_title')[$i];
                $sticker_data[] = $st_data;
            }
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


        if ($post_thumb != null) {
            try {
                unlink(storage_path("app/public/" . $request->input('post_thumb_path')));
            } catch (\Exception $e) {
            }
        }

        if ($bg_type_id == 0 || $bg_type_id == 1) {
            try {
                if ($back_image != null) {
                    unlink(storage_path("app/public/" . $request->input('back_image_path')));
                }
            } catch (\Exception $e) {
            }

        }

        for ($i = 0; $i < $st_count; $i++) {
            $st_image = null;
            try {
                $st_image = $request->file('st_image')[$i];
            } catch (\Exception $e) {
                $st_image = null;
            }
            if ($st_image != null) {
                unlink(storage_path("app/public/" . $st_image_path[$i]));
            }
        }

        return response()->json([
            'success' => "done"
        ]);

    }

    public function update2(Request $request, Template $template)
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

        $res = Design::find($request->id);

        $res->category_id = $request->input('category_id');
        $res->sub_cat_id = $request->input('sub_category_id');
        $res->style_id = $request->input('styles');
        $res->interest_id = $request->input('interest_id');
        $res->lang_id = $request->input('lang_id');
        $res->post_name = $request->input('post_name');


        $res->ratio = $request->input('ratio');
        $res->width = $request->input('width');
        $res->height = $request->input('height');

        $res->description = $request->input('description');
        $res->related_tags = $request->input('keywords');

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
        $res->status = $request->input('status');
        $res->is_premium = $request->input('is_premium');

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

        for ($i = 0; $i < count($pageNumbers); $i++) {
            $pageNumber = $pageNumbers[$i];

            $post_thumb = $request->file('post_thumb_' . $pageNumber);
            $back_image = $request->file('back_image_' . $pageNumber);
            $bg_type_id = $request->input('bg_type_id_' . $pageNumber);
            $bgData['layerType'] = 0;
            if ($post_thumb != null) {
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
                StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
                if ($i == 0) {
                    $res->post_thumb = 'uploadedFiles/thumb_file/' . $new_name;
                }
                $bgData['thumb'] = 'uploadedFiles/thumb_file/' . $new_name;
            } else {
                $bgData['thumb'] = $request->input('post_thumb_path_' . $pageNumber);
            }

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
                    $layer['height'] = $request->input('st_width_' . $pageNumber)[$stickerCount];
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

                    if ($request->input('st_is_editable_' . $pageNumber)[$textCount] == 1) {
                        $auto_create = 1;
                    }

                    $layer['isEditable'] = $request->input('st_is_editable_' . $pageNumber)[$stickerCount];
                    $layer['editableTitle'] = $request->input('st_editable_title_' . $pageNumber)[$stickerCount];

                    $layersData[] = $layer;
                    $stickerCount++;
                } else {
                    $layerText['layerType'] = 2;
                    $layerText['isVideo'] = false;
                    $layerText['text'] = $request->input('text_' . $pageNumber)[$textCount];
                    $layerText['font'] = $request->input('font_family_' . $pageNumber)[$textCount];
                    $layerText['width'] = $request->input('txt_width_' . $pageNumber)[$textCount];
                    $layerText['height'] = $request->input('txt_height_' . $pageNumber)[$textCount];
                    $layerText['left'] = $request->input('txt_x_pos_' . $pageNumber)[$textCount];
                    $layerText['top'] = $request->input('txt_y_pos_' . $pageNumber)[$textCount];
                    $layerText['originX'] = 'left';
                    $layerText['originY'] = 'top';
                    $layerText['size'] = $request->input('txt_size_' . $pageNumber)[$textCount];
                    $layerText['color'] = $request->input('txt_color_' . $pageNumber)[$textCount];
                    $layerText['opacity'] = $request->input('txt_opacity_' . $pageNumber)[$textCount];
                    $layerText['rotation'] = $request->input('txt_rotation_' . $pageNumber)[$textCount];

                    if ($request->input('is_editable_' . $pageNumber)[$textCount] == 1) {
                        $auto_create = 1;
                    }

                    $layerText['isEditable'] = $request->input('is_editable_' . $pageNumber)[$textCount];
                    $layerText['editableTitle'] = $request->input('editable_title_' . $pageNumber)[$textCount];
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

                    $layerText['Effects'] = $request->input('txt_effect_' . $pageNumber)[$textCount];

                    $layersData[] = $layerText;
                    $textCount++;
                }

            }

            $bgData['layers'] = $layersData;
            $designData[] = $bgData;

        }

        $res->auto_create = $auto_create;
        $res->designs = json_encode($designData);

        $res->save();

        for ($i = 0; $i < count($pageNumbers); $i++) {
            $pageNumber = $pageNumbers[$i];
            $post_thumb = $request->file('post_thumb_' . $pageNumber);
            if ($post_thumb != null) {
                try {
                    unlink(storage_path("app/public/" . $request->input('post_thumb_path_' . $pageNumber)));
                } catch (\Exception $e) {
                }
            }

            $back_image = $request->file('back_image_' . $pageNumber);
            $bg_type_id = $request->input('bg_type_id_' . $pageNumber);
            if ($bg_type_id == 0 || $bg_type_id == 1) {
                if ($back_image != null) {
                    try {
                        unlink(storage_path("app/public/" . $request->input('back_image_path_' . $pageNumber)));
                    } catch (\Exception $e) {
                    }
                }
            }

            $stickerCount = 0;
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

                    if ($st_image != null) {
                        try {
                            unlink(storage_path("app/public/" . $request->input('st_image_path_' . $pageNumber)[$stickerCount]));
                        } catch (\Exception $e) {
                        }
                    }
                    $stickerCount++;
                }
            }
        }

        return response()->json([
            'success' => json_encode($res)
        ]);
    }

    public function updateDatas()
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = HelperController::isAdmin($currentuserid);
        if ($idAdmin) {

            $datas = Design::all();
            foreach ($datas as $data) {
                $designs = json_decode($data->designs);

                foreach ($designs as $design) {
                    $design->autoCreate = false;
                    foreach ($design->layers as $layer) {

                        if (isset($layer->isEditable)) {
                            if ($layer->isEditable == '1' || $layer->isEditable == 1) {
                                $design->autoCreate = true;

                            }
                        }
                    }
                }

                $data->designs = json_encode($designs);
                $data->save();
            }
            return 'd';
        }
    }

    public function update2Backup(Request $request, Template $template)
    {
        $datas = Design::all();

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

        $pool = Pool::create();

        foreach ($datas as $data) {
            $pool->add(function () use ($data, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip) {
                $template = Template::find($data->id);

                $bgData['layerType'] = 0;
                $bgData['width'] = 100;
                $bgData['height'] = 100;
                $bgData['type'] = $template->back_image_type;
                $bgData['thumb'] = $template->post_thumb;
                $bgData['image'] = $template->back_image;
                $bgData['color'] = $template->back_color;
                $bgData['gradAngle'] = $template->grad_angle;
                $bgData['gradRatio'] = $template->grad_ratio;
                $bgData['animation'] = 0;
                $bgData['videoStartTime'] = 0;
                $bgData['videoEndTime'] = 0;

                $bgData['adjustment'] = $bgDataAdjust;
                $bgData['filter'] = $bgDataFilter;
                $bgData['crop'] = $bgDataCrop;
                $bgData['flip'] = $bgDataFlip;

                $layersData = array();

                $component_info = json_decode($template->component_info);
                for ($i = 0; $i < count($component_info); $i++) {
                    $layer['layerType'] = 1;
                    $layer['isVideo'] = false;
                    $layer['image'] = $component_info[$i]->st_image;
                    $layer['left'] = $component_info[$i]->st_x_pos;
                    $layer['top'] = $component_info[$i]->st_y_pos;
                    $layer['originX'] = 'left';
                    $layer['originY'] = 'top';
                    $layer['width'] = $component_info[$i]->st_width;
                    $layer['height'] = $component_info[$i]->st_height;
                    $layer['rotation'] = $component_info[$i]->st_rotation;
                    $layer['opacity'] = $component_info[$i]->st_opacity;
                    $layer['type'] = $component_info[$i]->st_type;
                    $layer['color'] = $component_info[$i]->st_color;
                    $layer['resizeType'] = $component_info[$i]->st_resize;
                    $layer['lockType'] = $component_info[$i]->st_lock_type;
                    $layer['animation'] = 0;
                    $layer['adjustment'] = $bgDataAdjust;
                    $layer['filter'] = $bgDataFilter;
                    $layer['crop'] = $bgDataCrop;
                    $layer['flip'] = $bgDataFlip;
                    $layer['videoStartTime'] = 0;
                    $layer['videoEndTime'] = 0;
                    $layersData[] = $layer;
                }

                $text_info = json_decode($template->text_info);
                for ($i = 0; $i < count($text_info); $i++) {
                    $layerText['layerType'] = 2;
                    $layerText['isVideo'] = false;
                    $layerText['text'] = $text_info[$i]->text;
                    $layerText['font'] = $text_info[$i]->font_family;
                    $layerText['width'] = $text_info[$i]->txt_width;
                    $layerText['height'] = $text_info[$i]->txt_height;
                    $layerText['left'] = $text_info[$i]->txt_x_pos;
                    $layerText['top'] = $text_info[$i]->txt_y_pos;
                    $layerText['originX'] = 'left';
                    $layerText['originY'] = 'top';
                    $layerText['size'] = $text_info[$i]->txt_size;
                    $layerText['color'] = $text_info[$i]->txt_color;
                    $layerText['opacity'] = $text_info[$i]->txt_opacity;
                    $layerText['rotation'] = $text_info[$i]->txt_rotation;
                    $layerText['isEditable'] = $text_info[$i]->is_editable;
                    $layerText['editableTitle'] = $text_info[$i]->editable_title;
                    $layerText['curve'] = $text_info[$i]->txt_curve;

                    $alignment = $text_info[$i]->txt_align;
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
                    $layerTextSpacing['letter'] = $text_info[$i]->word_spacing;
                    $layerTextSpacing['line'] = $text_info[$i]->line_spacing;
                    $layerTextSpacing['lineMultiplier'] = $text_info[$i]->lineSpaceMultiplier;
                    $layerText['spacing'] = $layerTextSpacing;

                    $layerText['Effects'] = $text_info[$i]->layer_effects;

                    $layersData[] = $layerText;
                }

                $bgData['layers'] = $layersData;
                $designData[] = $bgData;

                Design::where('id', $data->id)->update(['designs' => json_encode($designData)]);
                return 'done';
            })->then(function ($output) {
                // Handle success
            })->catch(function (Throwable $exception) {
                // Handle exception
                return response()->json([
                    'success' => json_encode($exception->getMessage())
                ]);
            });

        }

        $pool->wait();

        return response()->json([
            'success' => json_encode($request->all())
        ]);
    }

    public function migrateBackup(Template $template, $id)
    {

        $datas = Template::all();

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

        $pool = Pool::create();

        foreach ($datas as $template) {
            $pool->add(function () use ($template, $bgDataAdjust, $bgDataFilter, $bgDataCrop, $bgDataFlip) {

                $res = new Design();
                $res->id = $template->id;
                $res->app_id = $template->app_id;
                $res->emp_id = $template->emp_id;
                $res->category_id = $int_var = (int) filter_var($template->category_id, FILTER_SANITIZE_NUMBER_INT);
                $res->sub_cat_id = $template->sub_cat_id;
                $res->style_id = $template->style_id;
                $res->interest_id = $template->interest_id;
                $res->lang_id = $template->lang_id;
                $res->post_name = $template->post_name;
                $res->post_thumb = $template->post_thumb;
                $res->ratio = $template->ratio;
                $res->width = $template->width;
                $res->height = $template->height;
                $res->total_pages = 1;
                $res->description = $template->description;
                $res->related_tags = $template->related_tags;
                $res->start_date = $template->start_date;
                $res->end_date = $template->end_date;
                $res->size = $template->size;
                $res->trending_views = $template->trending_views;
                $res->views = $template->views;
                $res->is_premium = $template->is_premium;
                $res->status = $template->status;
                $res->latest = $template->latest;
                $res->deleted = $template->deleted;
                $res->created_at = $template->created_at;
                $res->updated_at = $template->updated_at;


                $bgData['layerType'] = 0;
                $bgData['width'] = 100;
                $bgData['height'] = 100;
                $bgData['type'] = $template->back_image_type;
                $bgData['thumb'] = $template->post_thumb;
                $bgData['image'] = $template->back_image;
                $bgData['color'] = $template->back_color;
                $bgData['gradAngle'] = $template->grad_angle;
                $bgData['gradRatio'] = $template->grad_ratio;
                $bgData['animation'] = 0;
                $bgData['videoStartTime'] = 0;
                $bgData['videoEndTime'] = 0;

                $bgData['adjustment'] = $bgDataAdjust;
                $bgData['filter'] = $bgDataFilter;
                $bgData['crop'] = $bgDataCrop;
                $bgData['flip'] = $bgDataFlip;

                $layersData = array();

                $auto_create = 0;

                $component_info = json_decode($template->component_info);
                for ($i = 0; $i < count($component_info); $i++) {
                    $layer['layerType'] = 1;
                    $layer['isVideo'] = false;
                    $layer['image'] = $component_info[$i]->st_image;
                    $layer['left'] = $component_info[$i]->st_x_pos;
                    $layer['top'] = $component_info[$i]->st_y_pos;
                    $layer['originX'] = 'left';
                    $layer['originY'] = 'top';
                    $layer['width'] = $component_info[$i]->st_width;
                    $layer['height'] = $component_info[$i]->st_height;
                    $layer['rotation'] = $component_info[$i]->st_rotation;
                    $layer['opacity'] = $component_info[$i]->st_opacity;
                    $layer['type'] = $component_info[$i]->st_type;
                    $layer['color'] = $component_info[$i]->st_color;
                    $layer['resizeType'] = $component_info[$i]->st_resize;
                    try {
                        $layer['lockType'] = $component_info[$i]->st_lock_type;
                    } catch (\Exception $e) {
                        $layer['lockType'] = 0;
                    }

                    $layer['animation'] = 0;
                    $layer['adjustment'] = $bgDataAdjust;
                    $layer['filter'] = $bgDataFilter;
                    $layer['crop'] = $bgDataCrop;
                    $layer['flip'] = $bgDataFlip;
                    $layer['videoStartTime'] = 0;
                    $layer['videoEndTime'] = 0;
                    $layersData[] = $layer;
                }

                $text_info = json_decode($template->text_info);
                for ($i = 0; $i < count($text_info); $i++) {
                    $layerText['layerType'] = 2;
                    $layerText['isVideo'] = false;
                    $layerText['text'] = $text_info[$i]->text;
                    $layerText['font'] = $text_info[$i]->font_family;
                    $layerText['width'] = $text_info[$i]->txt_width;
                    $layerText['height'] = $text_info[$i]->txt_height;
                    $layerText['left'] = $text_info[$i]->txt_x_pos;
                    $layerText['top'] = $text_info[$i]->txt_y_pos;
                    $layerText['originX'] = 'left';
                    $layerText['originY'] = 'top';
                    $layerText['size'] = $text_info[$i]->txt_size;
                    $layerText['color'] = $text_info[$i]->txt_color;
                    $layerText['opacity'] = $text_info[$i]->txt_opacity;
                    $layerText['rotation'] = $text_info[$i]->txt_rotation;
                    $layerText['isEditable'] = $text_info[$i]->is_editable;
                    $layerText['editableTitle'] = $text_info[$i]->editable_title;
                    $layerText['curve'] = $text_info[$i]->txt_curve;
                    $layerText['lockType'] = 0;

                    if ($text_info[$i]->is_editable == '1' || $text_info[$i]->is_editable == 1) {
                        $auto_create = 1;
                    }

                    $alignment = $text_info[$i]->txt_align;
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
                    $layerTextSpacing['letter'] = $text_info[$i]->word_spacing;
                    $layerTextSpacing['line'] = $text_info[$i]->line_spacing;
                    $layerTextSpacing['lineMultiplier'] = $text_info[$i]->lineSpaceMultiplier;
                    $layerText['spacing'] = $layerTextSpacing;

                    try {
                        $layerText['Effects'] = $text_info[$i]->layer_effects;
                    } catch (\Exception $e) {
                        $layerText['Effects'] = null;
                    }

                    $layersData[] = $layerText;
                }

                $bgData['layers'] = $layersData;

                $designData = array();
                $designData[] = $bgData;
                $res->auto_create = $auto_create;
                $res->designs = json_encode($designData);
                $res->save();


                return 'done';
            })->then(function ($output) {
                // Handle success
                return 'donedfe';
            })->catch(function (Throwable $exception) {
                // Handle exception
                return response()->json([
                    'success' => json_encode($exception->getMessage())
                ]);
            });
        }

        $pool->wait();

        return 'done2';
    }

    public function destroy(Request $request)
    {

        $res = Template::find($request->id);

        try {
            unlink(storage_path("app/public/" . $res->post_thumb));
        } catch (\Exception $e) {
        }

        try {
            unlink(storage_path("app/public/" . $res->back_image));
        } catch (\Exception $e) {
        }

        $component_info = json_decode($res->component_info, true);
        $st_count = count($component_info);

        for ($i = 0; $i < $st_count; $i++) {
            try {
                unlink(storage_path("app/public/" . $component_info[$i]['st_image']));
            } catch (\Exception $e) {
            }
        }

        Template::destroy(array('id', $request->id));
        return redirect('show_item');
    }

    public function reset_date(Request $request)
    {

        $res = Template::find($request->id);
        $res->start_date = null;
        $res->end_date = null;
        $res->save();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function reset_creation(Request $request)
    {

        $res = Template::find($request->id);
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

        $res = Template::find($request->id);

        if ($res->status == 1) {
            $res->status = 0;
        } else {
            $res->status = 1;
        }

        $res->save();

        return response()->json([
            'success' => "done"
        ]);
    }

    public function premium_update(Request $request)
    {

        $res = Template::find($request->id);

        if ($res->is_premium == 1) {
            $res->is_premium = 0;
        } else {
            $res->is_premium = 1;
        }

        $res->save();

        return response()->json([
            'success' => "done"
        ]);
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

}

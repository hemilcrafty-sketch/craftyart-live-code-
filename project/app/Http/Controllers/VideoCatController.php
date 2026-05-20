<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoCat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoCatController extends AppBaseController
{

    public function index()
    {

    }

    public function create()
    {
        $allCategories = VideoCat::getAllCategoriesWithSubcategories();
        $appArray = \App\Models\AppCategory::all();
        $userRole = \App\Models\User::whereIn('user_type', [1, 2, 3])->get();
        return view('videos/create_cat', compact('allCategories', 'appArray', 'userRole'));
    }

    public function store(Request $request)
    {
        $res = new VideoCat;
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->canonical_link = $request->input('canonical_link');
        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        $image = $request->file('category_thumb');
        if ($image != null) {
            $this->validate($request, ['category_thumb' => 'required|image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        } else {
            $res->category_thumb = 'uploadedFiles/vCatThumb/no_image.png';
        }

        // Handle mockup
        $mockup = $request->file('mockup');
        if ($mockup != null) {
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        }

        // Handle banner
        $banner = $request->file('banner');
        if ($banner != null) {
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        }

        $res->app_id = $request->input('app_id');
        
        // Handle JSON fields
        if ($request->has('contents')) {
            $res->contents = json_encode($request->input('contents'));
        }
        if ($request->has('faqs')) {
            $res->faqs = json_encode($request->input('faqs'));
        }
        if ($request->has('top_keywords')) {
            $res->top_keywords = json_encode($request->input('top_keywords'));
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $res->emp_id = auth()->user()->id;
        $res->save();
        
        return response()->json([
            'success' => 'Category Added successfully.'
        ]);
    }

    public function show(Request $request)
    {

        $query = $request->input('query', '');
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Filter and paginate results
        $cateArray = VideoCat::where('category_name', 'like', '%' . $query . '%')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return view('videos/show_cat')->with('catArray', $cateArray);
    }

    public function edit(VideoCat $videoCat, $id)
    {
        $datas['cat'] = VideoCat::find($id);
        $datas['allCategories'] = VideoCat::getAllCategoriesWithSubcategories();
        $datas['appArray'] = \App\Models\AppCategory::all();
        $datas['userRole'] = \App\Models\User::whereIn('user_type', [1, 2, 3])->get();
        return view('videos/edit_cat')->with('datas', $datas);
    }

    public function update(Request $request, VideoCat $videoCat)
    {
        $res = VideoCat::find($request->id);
        $res->category_name = $request->input('category_name');
        $res->id_name = $request->input('id_name');
        $res->canonical_link = $request->input('canonical_link');
        $res->seo_emp_id = $request->input('seo_emp_id');
        $res->meta_title = $request->input('meta_title');
        $res->primary_keyword = $request->input('primary_keyword');
        $res->h1_tag = $request->input('h1_tag');
        $res->tag_line = $request->input('tag_line');
        $res->meta_desc = $request->input('meta_desc');
        $res->short_desc = $request->input('short_desc');
        $res->h2_tag = $request->input('h2_tag');
        $res->long_desc = $request->input('long_desc');

        // Handle category thumb
        $image = $request->file('category_thumb');
        if ($image != null) {
            $this->validate($request, ['category_thumb' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old image
            if ($res->category_thumb && $res->category_thumb != 'uploadedFiles/vCatThumb/no_image.png') {
                StorageUtils::delete('public/' . $res->category_thumb);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
            $res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
        }

        // Handle mockup
        $mockup = $request->file('mockup');
        if ($mockup != null) {
            $this->validate($request, ['mockup' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old mockup
            if ($res->mockup) {
                StorageUtils::delete('public/' . $res->mockup);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $mockup->getClientOriginalExtension();
            StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
            $res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
        }

        // Handle banner
        $banner = $request->file('banner');
        if ($banner != null) {
            $this->validate($request, ['banner' => 'image|mimes:jpg,png,gif,webp,svg|max:2048']);
            // Delete old banner
            if ($res->banner) {
                StorageUtils::delete('public/' . $res->banner);
            }
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $banner->getClientOriginalExtension();
            StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
            $res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
        }

        $res->app_id = $request->input('app_id');
        
        // Handle JSON fields
        if ($request->has('contents')) {
            $res->contents = json_encode($request->input('contents'));
        }
        if ($request->has('faqs')) {
            $res->faqs = json_encode($request->input('faqs'));
        }
        if ($request->has('top_keywords')) {
            $res->top_keywords = json_encode($request->input('top_keywords'));
        }

        $res->sequence_number = $request->input('sequence_number');
        $res->parent_category_id = $request->input('parent_category_id', 0);
        $res->status = $request->input('status');
        $res->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.'
        ]);
    }

    public function destroy(VideoCat $videoCat, $id)
    {
        $res = VideoCat::find($id);
        $category_thumb = $res->category_thumb;
        $contains = Str::contains($category_thumb, 'no_image');
        if (!$contains) {
            try {
                StorageUtils::delete($category_thumb);
            } catch (\Exception $e) {
            }
        }
        VideoCat::destroy(array('id', $id));
        return redirect('show_v_cat');
    }
}
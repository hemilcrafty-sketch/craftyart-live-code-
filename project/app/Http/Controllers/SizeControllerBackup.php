<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use Carbon\Carbon;

class SizeControllerBackup extends AppBaseController
{

    /** * Display a listing of the resource.*/

    public function index(Request $request)
    {
        $filters = ["size_name", "unit"];
        $query = Size::query();
        if (isset($request->query) && $request->input('query') != '') {
            $query = $this->applyFilters($query, $filters, $request->input('query'));
        }
        $sizes = $query->orderBy('id', 'desc')->paginate(10);

        return view("size.index", compact('sizes'));
    }

    /** Show the form for creating a new resource. */
    public function create(Request $request)
    {
        $allNewCategories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();
        return view("size.create", compact('allNewCategories'));
    }

    /** Store a newly created resource in storage. */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size_name' => 'required|string',
            'id_name' => 'required|string',
            'width_ration' => 'required|string',
            'height_ration' => 'required|string',
            'new_category_ids' => 'required',
            'thumb' => 'required|image|mimes:png|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {

            $image = $request->file('thumb');
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);

            $newCategoryIdsInput = $request->input('new_category_ids');
            if (is_array($newCategoryIdsInput)) {
                $newCategoryIds = $newCategoryIdsInput;
            } else if (is_string($newCategoryIdsInput)) {
                $newCategoryIds = explode(',', $newCategoryIdsInput);
            } else {
                $newCategoryIds = [];
            }
            $inputs = [
                "size_name" => $request->size_name,
                "thumb" => 'uploadedFiles/thumb_file/' . $new_name,
                "id_name" => $request->id_name,
                "new_category_id" => json_encode($newCategoryIds),
                "width_ration" => $request->width_ration,
                "height_ration" => $request->height_ration,
                "width" => $request->width,
                "height" => $request->height,
                "paper_size" => $request->paperSize,
                "emp_id" => auth()->user()->id,
                "status" => $request->status,
            ];

            Size::create($inputs);
            return response()->json([
                'status' => true,
                'success' => "Size has been added successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Show the form for editing the specified resource.*/
    public function edit(Size $size)
    {
        $allNewCategories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();
        $dataArray['item'] = $size;
        $dataArray['allCategories'] = $allNewCategories;
        return view("size.edit", compact('dataArray'));
    }

    /** Update the specified resource in storage.*/
    public function update(Request $request, Size $size)
    {
        try {
            $image = $request->file('thumb');
            $thumbPath = $request->input('thumb_path');

            if ($image != null) {
                $this->validate($request, ['thumb' => 'required|image|mimes:png|max:10']);
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
                StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
                $thumbPath = 'uploadedFiles/thumb_file/' . $new_name;
            }

            $newCategoryIdsInput = $request->input('new_category_ids');
            if (is_array($newCategoryIdsInput)) {
                $newCategoryIds = $newCategoryIdsInput;
            } else if (is_string($newCategoryIdsInput)) {
                $newCategoryIds = explode(',', $newCategoryIdsInput);
            } else {
                $newCategoryIds = [];
            }
            $inputs = [
                "size_name" => $request->size_name,
                "thumb" => $thumbPath,
                "id_name" => $request->id_name,
                "new_category_id" => json_encode($newCategoryIds),
                "width_ration" => $request->width_ration,
                "height_ration" => $request->height_ration,
                "width" => $request->width,
                "height" => $request->height,
                "paper_size" => $request->paperSize,
                "emp_id" => auth()->user()->id,
                "status" => $request->status,
            ];

            $size->update($inputs);

            if ($image != null) {
                try {
                    StorageUtils::delete($request->input('thumb_path'));
                } catch (\Exception $e) {
                }
            }

            return response()->json([
                'status' => true,
                'success' => "Size has been updated successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Remove the specified resource from storage.*/

    public function destroy(Size $size)
    {
        try {
            $size->delete();
            return response()->json([
                'status' => true,
                'success' => "Size has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getSizeList(Request $request)
    {
        try {
            $category = NewCategory::find($request->cateId);
            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: $request->cateId;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $sizes = Size::whereJsonContains('new_category_id', $catId)->where('status', 1)->get();
            return response()->json([
                'status' => true,
                'success' => "Size has been deleted successfully.",
                'data' => $sizes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
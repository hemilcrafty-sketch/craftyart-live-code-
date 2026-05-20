<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SizeController extends AppBaseController
{

    /** * Display a listing of the resource.*/

    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'Id'],
            ['id' => 'size_name', 'value' => 'Size Name'],
            ['id' => 'paper_size', 'value' => 'Paper Size'],
            ['id' => 'height_ration', 'value' => 'Height Ratio'],
            ['id' => 'width_ration', 'value' => 'Width Ratio'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $sizes = $this->applyFiltersAndPagination(
            $request,
            Size::query(),
            $searchableFields,
            [
                'parent_query' => NewCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'new_category_id',
            ]
        );

        return view('size.index', compact('sizes', 'searchableFields'));
    }


    /** Show the form for creating a new resource. */
    public function create(Request $request): Factory|View|Application
    {
        $allNewCategories = NewCategory::where('parent_category_id', 0)->where('status', 1)->get();
        return view("size.create", compact('allNewCategories'));
    }

    /** Store a newly created resource in storage. */

    public function store(Request $request)
    {

        try {
            $image = $request->file('thumb');
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);

            $newCategoryIdsInput = $request->input('new_category_ids');
            $newCategoryIds = is_array($newCategoryIdsInput)
                ? $newCategoryIdsInput
                : explode(',', $newCategoryIdsInput ?? '');

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
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
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
    public function update(Request $request, Size $size)
    {
        try {
            $user = auth()->user();
            $image = $request->file('thumb');
            $thumbPath = $request->input('thumb_path');

            // Access check
            $accessCheck = $this->isAccessByRole("seo_all", $request->id, $size->emp_id);
            if ($accessCheck) {
                return response()->json([
                    'error' => $accessCheck,
                ]);
            }

            // Validate and store image
            if ($image) {
                $this->validate($request, [
                    'thumb' => 'image|mimes:png|max:10240' // max:10KB in kilobytes
                ]);

                $new_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
                StorageUtils::storeAs($image, 'uploadedFiles/thumb_file', $new_name);
                $thumbPath = 'uploadedFiles/thumb_file/' . $new_name;
            }

            // Parse category IDs
            $newCategoryIdsInput = $request->input('new_category_ids');
            $newCategoryIds = is_array($newCategoryIdsInput)
                ? $newCategoryIdsInput
                : explode(',', $newCategoryIdsInput ?? '');

            // Update fields
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
                "emp_id" => $user->id,
                "status" => $request->status,
            ];


            $size->update($inputs);

            // Delete old image if new uploaded
            if ($image) {
                try {
                    StorageUtils::delete($request->input('thumb_path'));
                } catch (\Exception $e) {
                    // Log optional
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

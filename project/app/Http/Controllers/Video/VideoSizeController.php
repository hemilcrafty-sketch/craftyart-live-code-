<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoSize;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VideoSizeController extends AppBaseController
{
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
            VideoSize::query(),
            $searchableFields,
            [
                'parent_query' => VideoCategory::query(),
                'related_column' => 'category_name',
                'column_value' => 'category_id',
            ]
        );

        return view('videos.sizes.index', compact('sizes', 'searchableFields'));
    }

    public function create(Request $request): Factory|View|Application
    {
        $allVideoCategories = VideoCategory::where('parent_category_id', 0)->where('status', 1)->get();

        return view('videos.sizes.create', compact('allVideoCategories'));
    }

    public function store(Request $request)
    {
        try {
            $base64Images = [['img' => $request->thumb, 'name' => "Category Thumb", 'required' => true]];
            $validationError = ContentManager::validateBase64Images($base64Images);
            if ($validationError) {
                return response()->json([
                    'error' => $validationError
                ]);
            }

            $categoryIdsInput = $request->input('category_ids');
            $categoryIds = is_array($categoryIdsInput)
                ? $categoryIdsInput
                : explode(',', $categoryIdsInput ?? '');

            $inputs = [
                'size_name' => $request->size_name,
                'thumb' => ContentManager::saveImageToPath($request->thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp),
                'id_name' => $request->id_name,
                'category_id' => json_encode($categoryIds),
                'width_ration' => $request->width_ration,
                'height_ration' => $request->height_ration,
                'width' => $request->width,
                'height' => $request->height,
                'paper_size' => $request->paperSize,
                'emp_id' => auth()->user()->id,
                'status' => $request->status,
            ];

            VideoSize::create($inputs);

            return response()->json([
                'status' => true,
                'success' => 'Video size has been added successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(VideoSize $video_size)
    {
        $allVideoCategories = VideoCategory::where('parent_category_id', 0)->where('status', 1)->get();
        $dataArray['item'] = $video_size;
        $dataArray['allCategories'] = $allVideoCategories;

        return view('videos.sizes.edit', compact('dataArray'));
    }

    public function update(Request $request, VideoSize $video_size)
    {
        try {
            $user = auth()->user();
            $previousThumbPath = $video_size->thumb;

            $accessCheck = $this->isAccessByRole('seo_all', $request->id, $video_size->emp_id);
            if ($accessCheck) {
                return response()->json([
                    'error' => $accessCheck,
                ]);
            }

            $base64Images = [
                ['img' => $request->thumb, 'name' => "Thumb", 'required' => true],
            ];
            $validationError = ContentManager::validateBase64Images($base64Images);
            if ($validationError) {
                return response()->json(['error' => $validationError]);
            }

            $categoryIdsInput = $request->input('category_ids');
            $categoryIds = is_array($categoryIdsInput)
                ? $categoryIdsInput
                : explode(',', $categoryIdsInput ?? '');

            $thumbPath = ContentManager::saveImageToPath($request->thumb, 'uploadedFiles/thumb_file/' . bin2hex(random_bytes(20)) . Carbon::now()->timestamp);

            $inputs = [
                'size_name' => $request->size_name,
                'thumb' => $thumbPath,
                'id_name' => $request->id_name,
                'category_id' => json_encode($categoryIds),
                'width_ration' => $request->width_ration,
                'height_ration' => $request->height_ration,
                'width' => $request->width,
                'height' => $request->height,
                'paper_size' => $request->paperSize,
                'emp_id' => $user->id,
                'status' => $request->status,
            ];

            $video_size->update($inputs);

            return response()->json([
                'status' => true,
                'success' => 'Video size has been updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(VideoSize $video_size)
{
        try {
            $video_size->delete();

            return response()->json([
                'status' => true,
                'success' => 'Video size has been deleted successfully.',
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
            $category = VideoCategory::find($request->cateId);
            if (!$category) {
                $sizes = VideoSize::where('status', 1)->orderBy('id')->get();

                return response()->json([
                    'status' => true,
                    'success' => 'ok',
                    'data' => $sizes,
                ]);
            }

            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: (int) $request->cateId;
            $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $sizes = VideoSize::whereJsonContains('category_id', $catId)->where('status', 1)->orderBy('id')->get();

            return response()->json([
                'status' => true,
                'success' => 'ok',
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

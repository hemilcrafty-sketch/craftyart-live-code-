<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Review;
use Exception;

class ReviewsController extends AppBaseController
{
    public function index(Request $request)
    {
        $searchableFields = [['id'=>'id','value'=>'Id'], ['id'=>'name','value'=>'Name'], ['id'=>'email','value'=>'Email'], ['id'=>'feedback','value'=>'Feedback'], ['id'=>'rate','value'=>'Rate']];
        $query = Review::query();
        $reviews = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        return view("reviews.index", compact('searchableFields', 'reviews'));
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $review = $request->all();
        if ($id) {
            $data = Review::find($id);
            if (!$data || $data->user_id != null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found or user is not anonymous'
                ]);
            }
        }
        $base64Images = [
            [
                'img' => $review['photo_uri'],
                'name' => "User Image",
                'required' => true
            ]
        ];
        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json(['error' => $validationError]);
        }

        $review['photo_uri'] = ContentManager::saveImageToPath(
            $review['photo_uri'],
            'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
        );
        $id ? Review::find($id)->update($review) : Review::create($review);

        return response()->json([
            'success' => true,
            'message' => $id ? 'Review updated!' : 'Review submitted!'
        ]);
    }

    public function reviewStatus(Request $request): JsonResponse
    {
        $reviewId = $request->id;
        $review = Review::find($reviewId);
        try {
            $isApprove = ($review->is_approve == 0) ? 1 : 0;
            Review::where('id', $reviewId)->update(['is_approve' => $isApprove]);
            return response()->json([
                'status' => true,
                'is_approve' => $isApprove,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review || $review->user_id != null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found or user is not anonymous'
            ]);
        }
        if ($review->delete()) {
            return response()->json(['success' => true, 'message' => 'Review deleted successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Failed to delete review']);
    }
}



<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoReview;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoReviewController extends AppBaseController
{
  public function index(Request $request): Factory|View|Application
  {
    $searchableFields = [
      ['id' => 'id', 'value' => 'Id'],
      ['id' => 'name', 'value' => 'Name'],
      ['id' => 'email', 'value' => 'Email'],
      ['id' => 'feedback', 'value' => 'Feedback'],
      ['id' => 'rate', 'value' => 'Rate']
    ];

    $query = VideoReview::query();
    $videoReviews = $this->applyFiltersAndPagination($request, $query, $searchableFields);

    return view("videos.reviews.index", compact('searchableFields', 'videoReviews'));
  }

  public function store(Request $request): JsonResponse
  {
    try {
      $id = $request->input('id');
      $videoReview = $request->all();

      if ($id) {
        $data = VideoReview::find($id);
        if (!$data || $data->user_id != null) {
          return response()->json([
            'success' => false,
            'message' => 'Data not found or user is not anonymous'
          ]);
        }
      }

      // Check if photo_uri exists
      if (!isset($videoReview['photo_uri']) || empty($videoReview['photo_uri'])) {
        return response()->json([
          'success' => false,
          'error' => 'Photo is required. Please upload an image.'
        ]);
      }

      $base64Images = [
        [
          'img' => $videoReview['photo_uri'],
          'name' => "User Image",
          'required' => true
        ]
      ];

      $validationError = ContentManager::validateBase64Images($base64Images);
      if ($validationError) {
        return response()->json(['success' => false, 'error' => $validationError]);
      }

      $videoReview['photo_uri'] = ContentManager::saveImageToPath(
        $videoReview['photo_uri'],
        'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
      );

      $id ? VideoReview::find($id)->update($videoReview) : VideoReview::create($videoReview);

      return response()->json([
        'success' => true,
        'message' => $id ? 'Review updated!' : 'Review submitted!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }

  public function reviewStatus(Request $request): JsonResponse
  {
    $reviewId = $request->id;
    $review = VideoReview::find($reviewId);
    try {
      $isApprove = ($review->is_approve == 0) ? 1 : 0;
      VideoReview::where('id', $reviewId)->update(['is_approve' => $isApprove]);
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
    $review = VideoReview::find($id);
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

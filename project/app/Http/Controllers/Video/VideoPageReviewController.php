<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Video\VideoPageReview;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoVirtualCategory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoPageReviewController extends AppBaseController
{
  public function index(Request $request): Factory|View|Application
  {
    $searchableFields = [
      ["id" => 'id', "value" => 'ID'],
      ['id' => 'name', 'value' => 'Name'],
      ['id' => 'email', 'value' => 'Email'],
      ['id' => 'feedback', 'value' => 'Feedback'],
      ['id' => 'rate', 'value' => 'Rate']
    ];

    $query = VideoPageReview::query()->whereIn('p_type', [6, 7, 8]);
    $videoPageReviews = $this->applyFiltersAndPagination($request, $query, $searchableFields);

    return view("videos.page_reviews.index", [
      'videoPageReviews' => $videoPageReviews,
      'searchableFields' => $searchableFields,
    ]);
  }

  public function store(Request $request): JsonResponse
  {
    $id = $request->input('id');
    if ($id) {
      $data = VideoPageReview::find($id);
      if (!$data || $data->user_id != null) {
        return response()->json([
          'success' => false,
          'message' => 'Data Not Found Or User Is Not Anonymous'
        ]);
      }
    }

    $videoPageReview = $request->all();
    $base64Images = [
      [
        'img' => $videoPageReview['photo_uri'],
        'name' => "User Image",
        'required' => true
      ]
    ];

    $validationError = ContentManager::validateBase64Images($base64Images);
    if ($validationError) {
      return response()->json(['error' => $validationError]);
    }

    $videoPageReview['photo_uri'] = ContentManager::saveImageToPath(
      $videoPageReview['photo_uri'],
      'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
    );

    $id ? VideoPageReview::findOrFail($id)->update($videoPageReview) : VideoPageReview::create($videoPageReview);
    return response()->json([
      'success' => true,
      'message' => $id ? 'Review updated!' : 'Review submitted!'
    ]);
  }

  public function reviewStatus(Request $request): JsonResponse
  {
    $reviewId = $request->id;
    $review = VideoPageReview::find($reviewId);
    try {
      $isApprove = ($review->is_approve == 0) ? 1 : 0;
      VideoPageReview::where('id', $reviewId)->update(['is_approve' => $isApprove]);
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
    $review = VideoPageReview::find($id);
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

  public static function getModalMap($type): array
  {
    if ($type == 6)
      return ['model' => VideoCategory::class, 'field' => 'category_name'];
    else if ($type == 7)
      return ['model' => VideoVirtualCategory::class, 'field' => 'category_name'];
    else if ($type == 8)
      return ['model' => VideoTemplate::class, 'field' => 'video_name'];
    else
      return ['model' => null, 'field' => ''];
  }

  public function getSelectedVideoPageData(Request $request): JsonResponse
  {
    $type = (int) $request->input('type');
    $query = $request->input('q');
    $modelMap = self::getModalMap($type);
    $results = [];

    if (isset($modelMap)) {
      $model = $modelMap['model'];
      $field = $modelMap['field'];

      $queryBuilder = $model::where(function ($q) use ($query, $field) {
        $q->where($field, 'like', "%$query%")
          ->orWhere('id', 'like', "%$query%");
      });

      // Add string_id based on model
      if ($model == VideoTemplate::class) {
        $queryBuilder->orWhere('string_id', 'like', "%$query%")
          ->where('isDeleted', 0);
      }

      $results = $queryBuilder->limit(100)
        ->get()
        ->map(fn($item) => [
          'id' => $item->string_id ?? $item->id,
          'label' => "{$item->id} - " . ($item->string_id ?? '') . " - {$item->$field}"
        ]);
    }
    return response()->json($results);
  }

  public function getSelectedVideoPageTitle(Request $request): JsonResponse
  {
    $type = (int) $request->input('type');
    $pId = $request->input('v_id'); // Keep v_id for compatibility with existing AJAX calls
    $modelMap = self::getModalMap($type);

    if (!isset($modelMap)) {
      return response()->json(['label' => 'Unknown'], 400);
    }

    $model = $modelMap['model'];
    $field = $modelMap['field'];

    if ($model == VideoTemplate::class) {
      $item = $model::query()
        ->where(function ($q) use ($pId) {
          $q->where('string_id', $pId);
          if ($pId !== '' && $pId !== null && ctype_digit((string) $pId)) {
            $q->orWhere('id', (int) $pId);
          }
        })
        ->where('isDeleted', 0)
        ->first();
    } else {
      $item = $model::query()
        ->where(function ($q) use ($pId) {
          $q->where('string_id', $pId);
          if ($pId !== '' && $pId !== null && ctype_digit((string) $pId)) {
            $q->orWhere('id', (int) $pId);
          }
        })
        ->first();
    }

    if (!$item) {
      return response()->json(['label' => 'Not Found'], 404);
    }

    return response()->json([
      'label' => "{$item->id} - " . ($item->string_id ?? '') . " - {$item->$field}"
    ]);
  }
}

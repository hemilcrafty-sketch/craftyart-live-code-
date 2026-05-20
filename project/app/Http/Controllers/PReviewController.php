<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\PReview;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\Categoryegory;
use App\Models\VirtualCategory;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PReviewController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [["id" => 'id', "value" => 'ID'], ['id' => 'name', 'value' => 'Name'], ['id' => 'email', 'value' => 'Email'], ['id' => 'feedback', 'value' => 'Feedback'], ['id' => 'rate', 'value' => 'Rate']];

        $query = PReview::query()->where(function ($q) {
            $q->whereNull('p_type')->orWhereNotIn('p_type', [6, 7, 8]);
        });
        $pReviews = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        return view("p_reviews.index", [
            'pReviews' => $pReviews,
            'searchableFields' => $searchableFields,
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');
        if ($id) {
            $data = PReview::find($id);
            if (!$data || $data->user_id != null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found Or User Is Not Anonymous'
                ]);
            }
            if (in_array((int) $data->p_type, [6, 7, 8], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Edit this review from Video → Page Review.',
                ], 422);
            }
        }
        $pReview = $request->all();
        $pType = (int) ($pReview['p_type'] ?? -1);
        if (in_array($pType, [6, 7, 8], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Video page reviews must be added from Video → Page Review.',
            ], 422);
        }
        $base64Images = [
            [
                'img' => $pReview['photo_uri'],
                'name' => "User Image",
                'required' => true
            ]
        ];

        $validationError = ContentManager::validateBase64Images($base64Images);
        if ($validationError) {
            return response()->json(['error' => $validationError]);
        }

        $pReview['photo_uri'] = ContentManager::saveImageToPath(
            $pReview['photo_uri'],
            'uploadedFiles/thumb_file/' . StorageUtils::getNewName()
        );

        $id ? PReview::findOrFail($id)->update($pReview) : PReview::create($pReview);
        return response()->json([
            'success' => true,
            'message' => $id ? 'Review updated ! ' : 'Review submitted ! '
        ]);
    }

    public function reviewStatus(Request $request): JsonResponse
    {
        $reviewId = $request->id;
        $review = PReview::find($reviewId);
        try {
            $isApprove = ($review->is_approve == 0) ? 1 : 0;
            PReview::where('id', $reviewId)->update(['is_approve' => $isApprove]);
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
        $review = PReview::find($id);
        if (!$review || $review->user_id != null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found or user is not anonymous'
            ]);
        }
        if (in_array((int) $review->p_type, [6, 7, 8], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Delete this review from Video → Page Review.',
            ], 422);
        }
        if ($review->delete()) {
            return response()->json(['success' => true, 'message' => 'Review deleted successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Failed to delete review']);
    }

    public static function getModalMap($type): ?array
    {
        if ($type == 0) {
            return ['model' => Design::class, 'field' => 'post_name'];
        }
        if ($type == 1) {
            return ['model' => NewCategory::class, 'field' => 'category_name'];
        }
        if ($type == 2) {
            return ['model' => SpecialPage::class, 'field' => 'page_slug'];
        }
        if ($type == 3) {
            return ['model' => SpecialKeyword::class, 'field' => 'name'];
        }
        if ($type == 4) {
            return ['model' => Category::class, 'field' => 'category_name'];
        }
        if ($type == 5) {
            return ['model' => VirtualCategory::class, 'field' => 'category_name'];
        }

        return null;
    }

    public function getSelectedPageData(Request $request): JsonResponse
    {
        $type = (int) $request->input('type');
        $query = $request->input('q');
        $modelMap = self::getModalMap($type);
        $results = [];
        if ($modelMap !== null) {
            $model = $modelMap['model'];
            $field = $modelMap['field'];
            $results = $model::where(function ($q) use ($query, $field) {
                $q->where($field, 'like', "%$query%")
                    ->orWhere('id', 'like', "%$query%")
                    ->orWhere('string_id', 'like', "%$query%");
            })
                ->limit(100)
                ->get()
                ->map(function ($item) use ($field) {
                    $sid = $item->string_id ?? $item->id_name ?? $item->id;

                    return [
                        'id' => $sid,
                        'label' => "{$item->id} - {$sid} - {$item->$field}",
                    ];
                });
        }
        return response()->json($results);
    }

    public function getSelectedPageTitle(Request $request): JsonResponse
    {
        $type = (int) $request->input('type');
        $pId = $request->input('p_id');
        $modelMap = self::getModalMap($type);
        if ($modelMap === null) {
            return response()->json(['label' => 'Unknown'], 400);
        }
        $model = $modelMap['model'];
        $field = $modelMap['field'];
        $item = $model::where('string_id', $pId)->first();
        if (!$item) {
            return response()->json(['label' => 'Not Found'], 404);
        }

        return response()->json([
            'label' => "{$item->id} - {$item->string_id} - {$item->$field}",
        ]);
    }
}
<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Automation\WpFeedbackResponse;
use App\Models\UserData;
use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class WpFeedbackController extends AppBaseController
{
    const FOLLOWUP_LABELS = [
        'personal_use_only' => 'Personal Use Only',
        'professional' => 'Professional',
        'call_not_receive' => 'Call Not Receive',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
        'switched_off' => 'Switched Off',
        'not_reachable' => 'Not Reachable',
        'call_cut' => 'Call Cut',
    ];
    public function index(Request $request): Factory|View|Application
    {
        $query = WpFeedbackRequest::with('response');

        // Sales employee filter - same logic as UnifiedLeadsController
        $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
        if ($isSalesEmployee) {
            $userId = auth()->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereHas('response');
            } elseif ($request->status === 'pending') {
                $query->whereDoesntHave('response')
                    ->where(function ($pendingQuery) {
                        $pendingQuery->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            } elseif ($request->status === 'expired') {
                $query->whereDoesntHave('response')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
            }
        }
        if ($request->filled('rating')) {
            $query->whereHas('response', fn($q) => $q->where('rating', $request->rating));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            // search by user_id (uid) or name via join
            $matchingUids = UserData::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('number', 'like', "%{$search}%")
                ->pluck('uid');
            $query->whereIn('wp_feedback_requests.user_id', $matchingUids);
        }

        // Sorting
        $sortOptions = [
            'newest' => ['wp_feedback_requests.created_at', 'desc'],
            'oldest' => ['wp_feedback_requests.created_at', 'asc'],
            'rating_asc' => ['wfr.rating', 'asc'],
            'rating_desc' => ['wfr.rating', 'desc'],
            'submitted_asc' => ['wfr.submitted_at', 'asc'],
            'submitted_desc' => ['wfr.submitted_at', 'desc'],
        ];
        $sort = request('sort', 'newest');
        [$sortCol, $sortDir] = $sortOptions[$sort] ?? $sortOptions['newest'];

        $query->leftJoin('wp_feedback_responses as wfr', 'wp_feedback_requests.id', '=', 'wfr.wp_feedback_request_id')
            ->select('wp_feedback_requests.*')
            ->orderBy($sortCol, $sortDir);

        /** @var LengthAwarePaginator $feedbackRequests */
        $feedbackRequests = $query->paginate(20)->appends($request->query());

        $feedbackRequests->getCollection()->transform(function (WpFeedbackRequest $requestItem) {
            $response = $requestItem->response;

            $requestItem->display_status = $response
                ? 'completed'
                : ($requestItem->isExpired() ? 'expired' : 'pending');
            $requestItem->has_feedback_response = (bool) $response;
            $requestItem->display_feedback = $response?->feedback_text;
            $requestItem->display_suggestions = $response?->suggestions;

            return $requestItem;
        });

        // Attach user info
        $userIds = collect($feedbackRequests->items())->pluck('user_id')->unique();
        $users = UserData::whereIn('uid', $userIds)->get()->keyBy('uid');

        // Stats
        $allRequests = WpFeedbackRequest::with('response')->get();
        $stats = [
            'total' => $allRequests->count(),
            'pending' => $allRequests->filter(fn(WpFeedbackRequest $item) => !$item->response && !$item->isExpired())->count(),
            'completed' => $allRequests->filter(fn(WpFeedbackRequest $item) => (bool) $item->response)->count(),
            'expired' => $allRequests->filter(fn(WpFeedbackRequest $item) => !$item->response && $item->isExpired())->count(),
            'avg_rating' => round((float) WpFeedbackResponse::avg('rating'), 1) ?: 0,
            'total_responses' => WpFeedbackResponse::count(),
        ];
        $stats['response_rate'] = $stats['total'] > 0
            ? round(($stats['total_responses'] / $stats['total']) * 100, 1)
            : 0;

        return view('wp_feedback.index', compact('feedbackRequests', 'users', 'stats'))
            ->with('followupLabels', self::FOLLOWUP_LABELS);
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $feedbackRequest = WpFeedbackRequest::findOrFail($request->id);

        if ($request->has('followup_call') && $request->followup_call == 0) {
            $feedbackRequest->followup_call = 0;
            $feedbackRequest->followup_note = '';
            $feedbackRequest->followup_label = '';
        } else {
            $feedbackRequest->followup_call = 1;
            $feedbackRequest->followup_note = $request->followup_note ?? '';
            $feedbackRequest->followup_label = $request->followup_label ?? '';
        }
        $feedbackRequest->emp_id = auth()->user()->id;
        $feedbackRequest->save();

        // Broadcast followup change via WebSocket for real-time updates
        WebSocketBroadcastController::broadcastWpFeedbackFollowUpChanged($feedbackRequest);

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }

    /**
     * Resend / send a new feedback link for a purchase.
     * Can be triggered from the dashboard.
     */
    //    public function resend(Request $request, int $id): JsonResponse
//    {
//        $feedbackRequest = WpFeedbackRequest::with('response')->findOrFail($id);
//
//        // Enforce max 2 requests per purchase (1 auto + 1 manual)
//        $total = WpFeedbackRequest::where('purchase_id', $feedbackRequest->purchase_id)->count();
//        if ($total >= 2) {
//            return response()->json(['success' => false, 'message' => 'Resend limit reached for this purchase.'], 422);
//        }
//
//        // Resolve user
//        $user = UserData::where('uid', $feedbackRequest->user_id)->first();
//        if (!$user || empty($user->number)) {
//            return response()->json(['success' => false, 'message' => 'User phone not found.'], 422);
//        }
//
//        $phone = preg_replace('/\D/', '', $user->number);
//
//        $token = WpFeedbackRequest::generateUniqueToken();
//        $purchase = MasterPurchaseHistory::find($feedbackRequest->purchase_id);
//        $daysAfter = $purchase ? (int) \Carbon\Carbon::parse($purchase->created_at)->diffInDays(now()) : 0;
//
//        $newRequest = WpFeedbackRequest::create([
//            'user_id'             => $feedbackRequest->user_id,
//            'contact_no'          => $phone,
//            'purchase_id'         => $feedbackRequest->purchase_id,
//            'unique_token'        => $token,
//            'status'              => 'pending',
//            'expires_at'          => now()->addDays(30),
//            'days_after_purchase' => $daysAfter,
//        ]);
//
//        $feedbackUrl = 'feedback/' . $newRequest->string_id;
//
//        try {
//            $result = WhatsAppService::sendTemplateMessage(
//                campaignName:   'feedbackren2121',
//                userName:       $user->name ?? 'User',
//                mobile:         '91' . ltrim($phone, '0'),
//                templateParams: [$user->name ?? 'User', $feedbackUrl],
//            );
//
//            if (!empty($result['success']) && $result['success'] === 'true') {
//                $newRequest->update(['sent_at' => now()]);
//                Log::info('WP Feedback resent from panel', ['new_request_id' => $newRequest->id, 'original_id' => $id]);
//                return response()->json(['success' => true, 'message' => 'Feedback link resent successfully.']);
//            }
//
//            $newRequest->delete(); // rollback record if WA failed
//            return response()->json(['success' => false, 'message' => $result['message'] ?? 'WhatsApp send failed.'], 500);
//        } catch (\Exception $e) {
//            $newRequest->delete();
//            Log::error('WP Feedback resend exception', ['id' => $id, 'error' => $e->getMessage()]);
//            return response()->json(['success' => false, 'message' => 'Unexpected error.'], 500);
//        }
//    }
//
//    /**
//     * Send a fresh feedback link directly from the purchase history dashboard.
//     * POST /wp-feedback/send  { purchase_id }
//     */
//    public function sendFromDashboard(Request $request): JsonResponse
//    {
//        $request->validate(['purchase_id' => 'required|integer']);
//
//        $purchase = MasterPurchaseHistory::find($request->purchase_id);
//        if (!$purchase) {
//            return response()->json(['success' => false, 'message' => 'Purchase not found.'], 404);
//        }
//
//        $total = WpFeedbackRequest::where('purchase_id', $purchase->id)->count();
//        if ($total >= 2) {
//            return response()->json(['success' => false, 'message' => 'Feedback link already sent twice for this purchase.'], 422);
//        }
//
//        $user = UserData::where('uid', $purchase->user_id)->first();
//        if (!$user || empty($user->number)) {
//            return response()->json(['success' => false, 'message' => 'User phone not found.'], 422);
//        }
//
//        $phone = preg_replace('/\D/', '', $user->number);
//        $token = WpFeedbackRequest::generateUniqueToken();
//        $daysAfter = (int) \Carbon\Carbon::parse($purchase->created_at)->diffInDays(now());
//
//        $campaignName = $total === 0 ? 'feedback20' : 'feedbackren2121';
//
//        $newRequest = WpFeedbackRequest::create([
//            'user_id'             => $purchase->user_id,
//            'contact_no'          => $phone,
//            'purchase_id'         => $purchase->id,
//            'unique_token'        => $token,
//            'status'              => 'pending',
//            'expires_at'          => now()->addDays(30),
//            'days_after_purchase' => $daysAfter,
//        ]);
//
//        $feedbackUrl = 'feedback/' . $newRequest->string_id;
//
//        try {
//            $result = WhatsAppService::sendTemplateMessage(
//                campaignName:   $campaignName,
//                userName:       $user->name ?? 'User',
//                mobile:         '91' . ltrim($phone, '0'),
//                templateParams: [$user->name ?? 'User', $feedbackUrl],
//            );
//
//            if (!empty($result['success']) && $result['success'] === 'true') {
//                $newRequest->update(['sent_at' => now()]);
//                return response()->json(['success' => true, 'message' => 'Feedback link sent successfully.']);
//            }
//
//            $newRequest->delete();
//            return response()->json(['success' => false, 'message' => $result['message'] ?? 'WhatsApp send failed.'], 500);
//        } catch (\Exception $e) {
//            $newRequest->delete();
//            Log::error('WP Feedback sendFromDashboard exception', ['purchase_id' => $purchase->id, 'error' => $e->getMessage()]);
//            return response()->json(['success' => false, 'message' => 'Unexpected error.'], 500);
//        }
//    }
}



<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Models\Automation\UnifiedSupport;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Automation\WpFeedbackResponse;
use App\Models\UserData;
use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewWpFeedbackController extends AppBaseController
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
        $perPage = (int) $request->get('per_page', 50);
        $perPage = min(max($perPage, 10), 500);

        $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
        $userId = auth()->user()->id;

        // Base query starting from WpFeedbackRequest to handle all statuses (pending, completed, expired)
        $query = \App\Models\Automation\WpFeedbackRequest::with(['userData.personalDetails', 'response', 'unifiedSupport']);

        if ($isSalesEmployee) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'completed') {
                $query->whereHas('response');
            } elseif ($status === 'pending') {
                $query->whereDoesntHave('response')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            } elseif ($status === 'expired') {
                $query->whereDoesntHave('response')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
            }
        }

        // Filters
        if ($request->filled('followup_status')) {
            $status = $request->followup_status === 'completed' ? 1 : 0;
            $query->whereHas('unifiedSupport', function($q) use ($status) {
                $q->where('wp_feedback_followup_call', $status);
            });
        }

        if ($request->filled('followup_label')) {
            $label = $request->followup_label;
            $query->whereHas('unifiedSupport', function($q) use ($label) {
                $q->where('wp_feedback_followup_label', $label);
            });
        }

        if ($request->filled('rating')) {
            $query->whereHas('response', fn($q) => $q->where('rating', $request->rating));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $uids = UserData::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->pluck('uid')
                ->toArray();
                
            $query->where(function ($q) use ($search, $uids) {
                $q->where('contact_no', 'like', "%{$search}%")
                    ->orWhere('purchase_id', 'like', "%{$search}%");
                if (!empty($uids)) {
                    $q->orWhereIn('user_id', $uids);
                }
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('wp_feedback_requests.created_at', 'asc');
        } elseif (in_array($sort, ['rating_desc', 'rating_asc', 'submitted_desc', 'submitted_asc'])) {
            $query->leftJoin('wp_feedback_responses as wfr', 'wp_feedback_requests.id', '=', 'wfr.wp_feedback_request_id')
                  ->select('wp_feedback_requests.*');
            
            if ($sort === 'rating_desc') {
                $query->orderBy('wfr.rating', 'desc');
            } elseif ($sort === 'rating_asc') {
                $query->orderBy('wfr.rating', 'asc');
            } elseif ($sort === 'submitted_desc') {
                $query->orderBy('wfr.submitted_at', 'desc');
            } elseif ($sort === 'submitted_asc') {
                $query->orderBy('wfr.submitted_at', 'asc');
            }
        } else {
            $query->orderBy('wp_feedback_requests.created_at', 'desc');
        }

        $feedbackRequests = $query->paginate($perPage)->appends($request->except('page'));

        // Stats Calculation (Comprehensive)
        $allStatsRequests = \App\Models\Automation\WpFeedbackRequest::with('response')->get();
        $stats = [
            'total_sent' => $allStatsRequests->count(),
            'total_responses' => $allStatsRequests->filter(fn($item) => (bool) $item->response)->count(),
            'pending' => $allStatsRequests->filter(fn($item) => !$item->response && !$item->isExpired())->count(),
            'completed' => $allStatsRequests->filter(fn($item) => (bool) $item->response)->count(),
            'expired' => $allStatsRequests->filter(fn($item) => !$item->response && $item->isExpired())->count(),
            'completed_followup' => UnifiedSupport::where('wp_feedback_followup_call', 1)->count(),
            'avg_rating' => round(\App\Models\Automation\WpFeedbackResponse::avg('rating') ?? 0, 1),
        ];
        $stats['response_rate'] = $stats['total_sent'] > 0
            ? round(($stats['total_responses'] / $stats['total_sent']) * 100, 1)
            : 0;

        $followupLabels = self::FOLLOWUP_LABELS;
        // dd('feedbackRequests', $feedbackRequests, 'stats', $stats, 'followupLabels', $followupLabels);  
        return view('wp_feedback.new_index', compact('feedbackRequests', 'stats', 'followupLabels'));
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $wpReq = \App\Models\Automation\WpFeedbackRequest::findOrFail($request->id);
        
        $empName = 'N/A';
        $followupCall = 0;
        $followupNote = null;
        $followupLabel = null;
        $empId = 0;


        $followupCall = $request->followup_call ?? 1;
        $followupNote = $request->followup_note ?? '';
        $followupLabel = $request->followup_label ?? '';
        $empId = auth()->user()->id;

        // Sync UnifiedSupport
        $purchaseId = $wpReq->purchase_id;
        
        // Try to find if this ID exists in MasterPurchaseHistory
        $phExists = \App\Models\Revenue\MasterPurchaseHistory::where('id', $purchaseId)->exists();
        
        $support = UnifiedSupport::where('purchase_history_id', $purchaseId)
            ->orWhere('transaction_log_id', $purchaseId)
            ->first();

        if (!$support) {
            $support = new UnifiedSupport();
            $support->purchase_history_id = $purchaseId; // Always set this for the relationship link
            if (!$phExists) {
                $support->transaction_log_id = $purchaseId;
            }
            $support->user_id = $wpReq->user_id;
        }

        $support->wp_feedback_followup_call = $followupCall;
        $support->wp_feedback_followup_note = $followupNote;
        $support->wp_feedback_followup_label = $followupLabel;
        $support->emp_id = $empId;
        $support->save();

        // Compatibility update for legacy column in wp_feedback_requests
        $wpReq->followup_call = $followupCall;
        $wpReq->followup_note = $followupNote;
        $wpReq->followup_label = $followupLabel;
        $wpReq->emp_id = $empId;
        $wpReq->save();

        return response()->json([
            'success' => true,
            'message' => 'Feedback followup updated successfully in unified support.'
        ]);
    }
}

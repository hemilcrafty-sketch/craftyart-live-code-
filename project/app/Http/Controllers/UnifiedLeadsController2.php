<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Automation\UnifiedSupport;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Order;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UnifiedLeadsController extends AppBaseController
{
    /** Subscription (active order) & expiry (transaction log) follow-up labels */
    const FOLLOWUP_LABELS = [
        'interested' => 'Interested',
        'highly_interested' => 'Highly Interested',
        'need_time' => 'Need Time',
        'call_cut' => 'Call Cut',
        'not_reachable' => 'Not Reachable',
        'switched_off' => 'Switched Off',
        'not_interested' => 'Not Interested',
        'personal_use_only' => 'Personal Use Only',
        'call_me_after_sometime' => 'Call Me After Sometime',
        'call_not_receive' => 'Call Not Receive',
        'active_plan' => 'Active Plan',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
    ];

    /** WhatsApp Feedback follow-up labels (same as WpFeedbackController) */
    const FEEDBACK_FOLLOWUP_LABELS = [
        'personal_use_only' => 'Personal Use Only',
        'professional' => 'Professional',
        'call_not_receive' => 'Call Not Receive',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
        'switched_off' => 'Switched Off',
        'not_reachable' => 'Not Reachable',
        'call_cut' => 'Call Cut',
    ];

    public function index(Request $request)
    {
        try {
            $perPage = (int)$request->get('per_page', 50);
            $perPage = min(max($perPage, 10), 500);

            $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
            $userId = auth()->user()->id;

            $feedbackLookup = [];

            $query = $this->buildPurchaseHistoryQuery($request, $isSalesEmployee, $userId);

            $paginatedPurchases = $query->paginate($perPage, ['*'], 'page', $request->get('page', 1));

            $empIds = $paginatedPurchases->pluck('emp_id')
                ->filter(fn($id) => $id !== null && $id > 0)
                ->unique()
                ->values()
                ->all();

            $feedbackEmpIds = $this->getWpFeedbackRequestEmpIdsForNameLookup();
            $allEmpIds = array_unique(array_merge($empIds, $feedbackEmpIds));

            $employeeNames = $this->getEmployeeNames($allEmpIds);

            // Pre-load missing userData to avoid N+1 queries
            $missingUserIds = [];
            foreach ($paginatedPurchases as $purchase) {
                if (!$purchase->userData) {
                    $missingUserIds[] = $purchase->user_id;
                }
            }

            // Fetch all missing users in one query
            $missingUsers = [];
            if (!empty($missingUserIds)) {
                $missingUsers = UserData::with('personalDetails')
                    ->whereIn('uid', array_unique($missingUserIds))
                    ->get()
                    ->keyBy('uid');
            }

            $transformedLeads = collect();
            foreach ($paginatedPurchases as $purchase) {
                $lead = $this->transformPurchaseHistoryToLead($purchase, $employeeNames, $feedbackLookup, $missingUsers, true, true);
                if ($lead !== null) {
                    $transformedLeads->push($lead);
                }
            }

            $leads = new LengthAwarePaginator(
                $transformedLeads,
                $paginatedPurchases->total(),
                $paginatedPurchases->perPage(),
                $paginatedPurchases->currentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $stats = $this->calculateOptimizedStatsFromPurchaseHistory($query);

            return view('unified_leads.index', [
                'leads' => $leads,
                'stats' => $stats,
                'total' => $paginatedPurchases->total(),
                'followupLabels' => self::FOLLOWUP_LABELS,
                'feedbackFollowupLabels' => self::FEEDBACK_FOLLOWUP_LABELS,
            ]);
        } catch (\Exception $e) {
            $emptyLeads = new LengthAwarePaginator([], 0, $perPage ?? 50, 1);
            $stats = $this->getDefaultStats();

            return view('unified_leads.index', [
                'leads' => $emptyLeads,
                'stats' => $stats,
                'total' => 0,
                'followupLabels' => self::FOLLOWUP_LABELS,
                'feedbackFollowupLabels' => self::FEEDBACK_FOLLOWUP_LABELS,
                'error' => 'An error occurred while loading leads.'
            ]);
        }
    }

    /**
     * Get default stats in case of error
     */
    protected function getDefaultStats()
    {
        return [
            'total' => 0,
            'active_subscriptions' => 0,
            'expired_subscriptions' => 0,
            'pending_feedback' => 0,
            'completed_feedback' => 0,
            'active_needs_followup' => 0,
            'active_with_followup' => 0,
            'active_expiring_week' => 0,
            'active_expiring_month' => 0,
            'feedback_sent' => 0,
            'feedback_needs_followup' => 0,
            'feedback_with_followup' => 0,
            'expired_today' => 0,
            'expired_week' => 0,
            'expired_month' => 0,
            'expired_needs_followup' => 0,
            'expired_with_followup' => 0,
            'subscription_followups' => 0,
            'feedback_followups' => 0,
            'expiry_followups' => 0,
        ];
    }

    /**
     * Build purchase history query with filters
     */
    protected function buildPurchaseHistoryQuery($request, bool $isSalesEmployee, int $userId)
    {
        $query = MasterPurchaseHistory::with(['userData.personalDetails', 'unifiedSupport'])
            ->select([
                'id', 'user_id', 'product_id', 'product_type', 'transaction_id',
                'payment_id', 'currency_code', 'amount', 'payment_method',
                'from_where', 'contact_no', 'payment_status', 'isManual', 'status',
                'emp_id', 'expired_at', 'email_sent', 'wp_sent', 'created_at', 'updated_at'
            ]);

        if ($isSalesEmployee) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });
        }

        if ($request->filled('subscription_filter')) {
            if ($request->subscription_filter === 'Active') {
                $query->where('status', 1);
            } elseif ($request->subscription_filter === 'Expired') {
                $query->where('status', 0);
            }
        }

        if ($request->filled('subscription_followup_filter')) {
            // For now, we'll filter in memory since whereHas doesn't work across connections
            // The filtering will happen in the transform method
        }

        if ($request->filled('expiry_followup_filter')) {
            // For now, we'll filter in memory since whereHas doesn't work across connections
            // The filtering will happen in the transform method
        }

        if ($request->filled('subscription_followup_label')) {
            // For now, we'll filter in memory since whereHas doesn't work across connections
            // The filtering will happen in the transform method
        }

        if ($request->filled('expiry_followup_label')) {
            // For now, we'll filter in memory since whereHas doesn't work across connections
            // The filtering will happen in the transform method
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contact_no', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhereHas('userData', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('status', 'desc')->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        return $query;
    }

    /**
     * Transform purchase history to lead array
     */
    protected function transformPurchaseHistoryToLead($purchase, array $employeeNames, array $feedbackLookup, $missingUsers, bool $includeActive, bool $includeExpired)
    {
        $user = $purchase->userData;

        // Use pre-loaded missing users to avoid N+1 query
        if (!$user && is_object($missingUsers) && isset($missingUsers[$purchase->user_id])) {
            $user = $missingUsers[$purchase->user_id];
        }

        // If still no user, create a minimal user object to show the record
        if (!$user) {
            $user = (object)[
                'uid' => $purchase->user_id,
                'name' => 'Unknown User',
                'email' => '-',
                'number' => $purchase->contact_no ?? '-',
                'personalDetails' => (object)['usage' => '-']
            ];
        }

        // Determine if this is an active or expired subscription based on status field AND expired_at date
        $isActive = (int)$purchase->status === 1;

        // Check if subscription has expired based on expired_at date
        if ($isActive && $purchase->expired_at) {
            $expiredAtDate = Carbon::parse($purchase->expired_at);
            if ($expiredAtDate->isPast()) {
                $isActive = false; // Override status if expired_at is in the past
            }
        }

        // Filter based on subscription status
        if ($isActive && !$includeActive) {
            return null;
        }
        if (!$isActive && !$includeExpired) {
            return null;
        }

        $feedbackData = $this->resolveFeedbackData($feedbackLookup, $user->uid, $user->email);
        $feedbackModel = $feedbackData['feedback_model'];
        $hasFeedbackFollowup = $feedbackData['feedback_sent'] && $feedbackModel;

        // Get followup data from unified_support table
        $support = $purchase->unifiedSupport;

        // Use expired_at from purchase_history table for all subscriptions
        $expiryDate = null;
        if ($purchase->expired_at) {
            $expiryDate = Carbon::parse($purchase->expired_at);
        } else if ($isActive) {
            // Fallback: For active subscriptions without expired_at, get expiry from transaction_logs
            $subscriptionDetails = $this->getSubscriptionDetailsForUser($user->uid);
            $expiryDate = $subscriptionDetails['expiry_date'];
        }

        $daysToExpiry = $isActive && $expiryDate ? now()->diffInDays($expiryDate, false) : null;
        $daysActive = $isActive && $purchase->created_at ? now()->diffInDays($purchase->created_at) : null;
        $daysExpired = !$isActive && $expiryDate ? now()->diffInDays($expiryDate) : null;

        // Get email/WhatsApp counts and emp_id from purchase_history table and sync to unified_support
        $purchaseEmailCount = (int)($purchase->email_sent ?? 0);
        $purchaseWpCount = (int)($purchase->wp_sent ?? 0);
        $purchaseEmpId = $purchase->emp_id ?? null;

        // Get or create unified_support record
        if (!$support) {
            $support = UnifiedSupport::firstOrCreate(
                ['purchase_history_id' => $purchase->id],
                ['user_id' => $purchase->user_id]
            );
        }

        // Sync purchase_history data (email/WhatsApp counts and emp_id) to unified_support
        $needsUpdate = false;

        if ($isActive) {
            // For active subscriptions, sync to subscription_email_sent and subscription_wp_sent
            if ($support->subscription_email_sent != $purchaseEmailCount || $support->subscription_wp_sent != $purchaseWpCount) {
                $support->subscription_email_sent = $purchaseEmailCount;
                $support->subscription_wp_sent = $purchaseWpCount;
                $needsUpdate = true;
            }
            $emailSent = $purchaseEmailCount > 0;
            $waSent = $purchaseWpCount > 0;
            $communicationCounts = [
                'email_count' => $purchaseEmailCount,
                'whatsapp_count' => $purchaseWpCount
            ];
        } else {
            // For expired subscriptions, sync from purchase_history to unified_support first
            if ($support->expire_email_sent != $purchaseEmailCount || $support->expire_wp_sent != $purchaseWpCount) {
                $support->expire_email_sent = $purchaseEmailCount;
                $support->expire_wp_sent = $purchaseWpCount;
                $needsUpdate = true;
            }

            // Then read from unified_support to display
            $expireEmailCount = (int)($support->expire_email_sent ?? 0);
            $expireWpCount = (int)($support->expire_wp_sent ?? 0);

            $emailSent = $expireEmailCount > 0;
            $waSent = $expireWpCount > 0;
            $communicationCounts = [
                'email_count' => $expireEmailCount,
                'whatsapp_count' => $expireWpCount
            ];
        }

        // Sync emp_id from purchase_history to unified_support (only if not already set by followup)
        if ($purchaseEmpId !== null && $support->emp_id === null) {
            $support->emp_id = $purchaseEmpId;
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            $support->save();
        }

        $journeyMeta = $this->buildJourneyMeta(
            $isActive ? 'Active' : 'Expired',
            $purchase->created_at,
            $expiryDate,
            $daysActive,
            $daysToExpiry
        );

        $outreachFb = $this->buildOutreachFeedbackExtras(
            'unified_support',
            'Purchase #' . $purchase->id,
            $feedbackModel,
            $feedbackData,
            $isActive
        );

        // Get followup data from unified_support table
        $subscriptionFollowupCall = $support && $isActive ? (int)$support->subscription_followup_call : 0;
        $subscriptionFollowupNote = $support && $isActive ? ($support->subscription_followup_note ?? '') : '';
        $subscriptionFollowupLabel = $support && $isActive ? ($support->subscription_followup_label ?? '') : '';

        $expireFollowupCall = $support && !$isActive ? (int)$support->expire_followup_call : 0;
        $expireFollowupNote = $support && !$isActive ? ($support->expire_followup_note ?? '') : '';
        $expireFollowupLabel = $support && !$isActive ? ($support->expire_followup_label ?? '') : '';

        // Get account creation communication counts
        $accountCreationEmailCount = $support ? (int)($support->account_creation_email_sent ?? 0) : 0;
        $accountCreationWpCount = $support ? (int)($support->account_creation_wp_sent ?? 0) : 0;

        $empId = $support ? $support->emp_id : null;

        $feedbackFollowupCall = $hasFeedbackFollowup ? (int)($feedbackModel->followup_call ? 1 : 0) : 0;

        // Determine lifecycle stage
        $lifecycleStage = $isActive ? 'active' : 'expired';
        $lifecycleColor = $isActive ? '#16a34a' : '#dc2626';
        $lifecycleLabel = $isActive ? 'Active' : 'Expired';

        if ($isActive && $daysToExpiry !== null && $daysToExpiry <= 7 && $daysToExpiry > 0) {
            $lifecycleStage = 'expiring_soon';
            $lifecycleColor = '#ea580c';
            $lifecycleLabel = 'Expiring Soon';
        } elseif ($isActive && $daysActive > 60) {
            $lifecycleStage = 'active_long';
            $lifecycleColor = '#84cc16';
            $lifecycleLabel = 'Active (60+ days)';
        } elseif (!$isActive && $daysExpired !== null) {
            if ($daysExpired <= 7) {
                $lifecycleStage = 'expired_recent';
                $lifecycleLabel = 'Recently Expired';
            } elseif ($daysExpired <= 30) {
                $lifecycleStage = 'expired_month';
                $lifecycleLabel = 'Expired (< 30 days)';
            } else {
                $lifecycleStage = 'expired_old';
                $lifecycleLabel = 'Expired (30+ days)';
            }
        }

        return [
            'id' => 'purchase_' . $purchase->id,
            'source' => 'purchase_history',
            'source_label' => $isActive ? 'Active Subscription' : 'Expired Subscription',
            'source_color' => $isActive ? '#4f46e5' : '#dc3545',
            'lifecycle_stage' => $lifecycleStage,
            'lifecycle_color' => $lifecycleColor,
            'lifecycle_label' => $lifecycleLabel,
            'days_active' => $daysActive,
            'days_to_expiry' => $daysToExpiry,
            'days_expired' => $daysExpired,
            'user_id' => $user->uid,
            'name' => $user->name ?? '-',
            'email' => $user->email ?? '-',
            'contact_no' => $purchase->contact_no ?: ($user->number ?? '-'),
            'amount' => $purchase->currency_code . ' ' . $purchase->amount,
            'amount_numeric' => $purchase->amount ?? 0,
            'plan_type' => $this->getProductTypeName($purchase->product_type),
            'plan_title' => $this->getProductTitle($purchase->product_id, $purchase->product_type),
            'subscription_status' => $isActive ? 'Active' : 'Expired',
            'order_status' => $purchase->payment_status ?? 'Success',
            'usage_type' => is_object($user->personalDetails) ? ($user->personalDetails->usage ?? '-') : '-',
            'from_where' => $purchase->from_where ?: '-',
            'email_sent' => $emailSent,
            'email_count' => $communicationCounts['email_count'],
            'whatsapp_sent' => $waSent,
            'whatsapp_count' => $communicationCounts['whatsapp_count'],
            'comm_pattern' => $this->commPattern($emailSent, $waSent),
            'journey_start_label' => $journeyMeta['journey_start_label'],
            'journey_end_label' => $journeyMeta['journey_end_label'],
            'journey_bar_pct' => $journeyMeta['journey_bar_pct'],
            'journey_caption' => $journeyMeta['journey_caption'],
            'comm_track_source' => $outreachFb['comm_track_source'],
            'comm_track_ref' => $outreachFb['comm_track_ref'],
            'comm_track_blurb' => $outreachFb['comm_track_blurb'],
            'feedback_wp_string_id' => $outreachFb['feedback_wp_string_id'],
            'feedback_wp_expires_label' => $outreachFb['feedback_wp_expires_label'],
            'feedback_wp_completed_label' => $outreachFb['feedback_wp_completed_label'],
            'feedback_wp_submitted_label' => $outreachFb['feedback_wp_submitted_label'],
            'feedback_wp_status_raw' => $outreachFb['feedback_wp_status_raw'],
            'feedback_comment_preview' => $outreachFb['feedback_comment_preview'],
            'feedback_request_date_short' => $this->formatShortDateTime($feedbackData['feedback_request_date'] ?? null),
            'account_creation_email_count' => $accountCreationEmailCount,
            'account_creation_wp_count' => $accountCreationWpCount,
            'subscription_followup_id' => $isActive ? 'purchase_' . $purchase->id : null,
            'subscription_followup_call' => $subscriptionFollowupCall,
            'subscription_followup_note' => $subscriptionFollowupNote,
            'subscription_followup_label' => $subscriptionFollowupLabel,
            'subscription_followup_by' => $isActive ? RoleManager::getUploaderName($empId) : '-',
            'feedback_followup_id' => $hasFeedbackFollowup ? 'feedback_' . $feedbackModel->id : null,
            'feedback_followup_call' => $feedbackFollowupCall,
            'feedback_followup_note' => $hasFeedbackFollowup ? (string)($feedbackModel->followup_note ?? '') : '',
            'feedback_followup_label' => $hasFeedbackFollowup ? (string)($feedbackModel->followup_label ?? '') : '',
            'feedback_followup_by' => $hasFeedbackFollowup ? RoleManager::getUploaderName($feedbackModel->emp_id) : '-',
            'expiry_followup_id' => !$isActive ? 'purchase_' . $purchase->id : null,
            'expiry_followup_call' => $expireFollowupCall,
            'expiry_followup_note' => $expireFollowupNote,
            'expiry_followup_label' => $expireFollowupLabel,
            'expiry_followup_by' => !$isActive ? RoleManager::getUploaderName($empId) : '-',
            'followup_call' => ($subscriptionFollowupCall || $feedbackFollowupCall || $expireFollowupCall) ? 1 : 0,
            'followup_note' => $isActive ? $subscriptionFollowupNote : $expireFollowupNote,
            'followup_label' => $isActive ? $subscriptionFollowupLabel : $expireFollowupLabel,
            'followup_by' => $employeeNames[$empId] ?? 'N/A',
            'followup_supported' => true,
            'requires_subscription_followup' => $isActive,
            'requires_feedback_followup' => $feedbackData['feedback_sent'],
            'requires_expiry_followup' => !$isActive,
            'feedback_status' => $feedbackData['feedback_status'],
            'feedback_rating' => $feedbackData['feedback_rating'],
            'feedback_details' => $feedbackData['feedback_details'],
            'feedback_sent' => $feedbackData['feedback_sent'],
            'feedback_request_date' => $feedbackData['feedback_request_date'],
            'expire_date' => $expiryDate?->format('Y-m-d'),
            'expire_meta' => $this->getExpireMeta($isActive, $expiryDate, $daysToExpiry, $daysExpired),
            'followup_context' => $isActive
                ? 'Ask about subscription experience, plan satisfaction and renewal intent.'
                : 'Ask why the plan expired and whether the user wants renewal or another package.',
            'created_at' => $purchase->created_at ? (is_string($purchase->created_at) ? $purchase->created_at : $purchase->created_at->format('Y-m-d H:i:s')) : null,
        ];
    }

    /**
     * Get subscription details for a user (for active subscriptions only)
     */
    protected function getSubscriptionDetailsForUser($userId)
    {
        $transaction = TransactionLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'expiry_date' => $transaction ? Carbon::parse($transaction->expired_at) : null,
            'transaction' => $transaction
        ];
    }

    /**
     * Get communication counts for a user from orders/transactions
     */
    protected function getCommunicationCountsForUser($userId)
    {
        // Get from active order if exists
        $order = Order::where('user_id', $userId)
            ->where('is_deleted', 0)
            ->whereIn('status', ['success', 'paid'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($order) {
            return [
                'email_count' => $order->email_template_count ?? 0,
                'whatsapp_count' => $order->whatsapp_template_count ?? 0
            ];
        }

        // Get from latest transaction log
        $transaction = TransactionLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'email_count' => $transaction->email_template_count ?? 0,
            'whatsapp_count' => $transaction->whatsapp_template_count ?? 0
        ];
    }

    /**
     * Get product type name
     */
    protected function getProductTypeName($productType)
    {
        $types = [
            1 => 'Premium',
            2 => 'Business',
            3 => 'Enterprise',
            // Add more product types as needed
        ];

        return $types[$productType] ?? 'Unknown';
    }

    /**
     * Get product title based on product_id and type
     */
    protected function getProductTitle($productId, $productType)
    {
        // You can implement logic to get actual product names from pricing tables
        return $this->getProductTypeName($productType) . ' Plan';
    }

    /**
     * Get expire meta text
     */
    protected function getExpireMeta($isActive, $expiryDate, $daysToExpiry, $daysExpired = null)
    {
        if ($isActive && $daysToExpiry !== null) {
            return $daysToExpiry > 0 ? $daysToExpiry . ' days left' : 'Expired';
        } elseif (!$isActive && $daysExpired !== null) {
            return $daysExpired . ' days ago';
        } elseif (!$isActive && $expiryDate) {
            $daysExpired = now()->diffInDays($expiryDate);
            return $daysExpired . ' days ago';
        }

        return null;
    }

    /**
     * Calculate stats from purchase history
     */
    protected function calculateOptimizedStatsFromPurchaseHistory($purchaseQuery)
    {
        // Clone query for counting
        $totalPurchases = (clone $purchaseQuery)->count();

        // Count active vs expired based on status field
        $activePurchases = (clone $purchaseQuery)->where('status', 1)->count();
        $expiredPurchases = (clone $purchaseQuery)->where('status', 0)->count();

        // For followup stats, count directly from unified_support (simplified for cross-database)
        $activeNeedsFollowup = 0;
        $activeWithFollowup = 0;
        $expiredNeedsFollowup = 0;
        $expiredWithFollowup = 0;

        // Get feedback stats
        try {
            $feedbackRequests = WpFeedbackRequest::with('response')->get();

            $feedbackSent = $feedbackRequests->where('sent_at', '!=', null)->count();
            $pendingFeedback = $feedbackRequests->filter(function ($feedback) {
                return !$feedback->response && !$feedback->isExpired();
            })->count();
            $completedFeedback = $feedbackRequests->whereNotNull('response')->count();
            $feedbackNeedsFollowup = $feedbackRequests->filter(function ($feedback) {
                return $feedback->response && !$feedback->followup_call;
            })->count();
            $feedbackWithFollowup = $feedbackRequests->filter(function ($feedback) {
                return $feedback->response && $feedback->followup_call;
            })->count();

            $feedbackStats = [
                'feedback_sent' => $feedbackSent,
                'pending_feedback' => $pendingFeedback,
                'completed_feedback' => $completedFeedback,
                'feedback_needs_followup' => $feedbackNeedsFollowup,
                'feedback_with_followup' => $feedbackWithFollowup,
            ];
        } catch (\Exception $e) {
            $feedbackStats = [
                'feedback_sent' => 0,
                'pending_feedback' => 0,
                'completed_feedback' => 0,
                'feedback_needs_followup' => 0,
                'feedback_with_followup' => 0,
            ];
        }

        // Calculate expiry-related stats from purchase_history
        try {
            $expiredToday = (clone $purchaseQuery)
                ->where('status', 0)
                ->whereDate('expired_at', today())
                ->count();

            $expiredWeek = (clone $purchaseQuery)
                ->where('status', 0)
                ->where('expired_at', '>=', now()->subDays(7))
                ->count();

            $expiredMonth = (clone $purchaseQuery)
                ->where('status', 0)
                ->where('expired_at', '>=', now()->subDays(30))
                ->count();
        } catch (\Exception $e) {
            $expiredToday = 0;
            $expiredWeek = 0;
            $expiredMonth = 0;
        }

        // Calculate active expiring stats (need to check transaction_logs for active subscriptions)
        try {
            $activeExpiringWeek = (clone $purchaseQuery)
                ->where('status', 1)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transaction_logs')
                        ->whereColumn('transaction_logs.user_id', 'purchase_history.user_id')
                        ->where('transaction_logs.expired_at', '>', now())
                        ->where('transaction_logs.expired_at', '<=', now()->addDays(7));
                })
                ->count();

            $activeExpiringMonth = (clone $purchaseQuery)
                ->where('status', 1)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transaction_logs')
                        ->whereColumn('transaction_logs.user_id', 'purchase_history.user_id')
                        ->where('transaction_logs.expired_at', '>', now())
                        ->where('transaction_logs.expired_at', '<=', now()->addDays(30));
                })
                ->count();
        } catch (\Exception $e) {
            $activeExpiringWeek = 0;
            $activeExpiringMonth = 0;
        }

        return [
            // Basic Stats
            'total' => $totalPurchases,
            'active_subscriptions' => $activePurchases,
            'expired_subscriptions' => $expiredPurchases,
            'pending_feedback' => $feedbackStats['pending_feedback'],
            'completed_feedback' => $feedbackStats['completed_feedback'],

            // Active Subscription Tracking
            'active_needs_followup' => $activeNeedsFollowup,
            'active_with_followup' => $activeWithFollowup,
            'active_expiring_week' => $activeExpiringWeek,
            'active_expiring_month' => $activeExpiringMonth,

            // Feedback Tracking
            'feedback_sent' => $feedbackStats['feedback_sent'],
            'feedback_needs_followup' => $feedbackStats['feedback_needs_followup'],
            'feedback_with_followup' => $feedbackStats['feedback_with_followup'],

            // Expiry Tracking
            'expired_today' => $expiredToday,
            'expired_week' => $expiredWeek,
            'expired_month' => $expiredMonth,
            'expired_needs_followup' => $expiredNeedsFollowup,
            'expired_with_followup' => $expiredWithFollowup,

            // Followup Type Stats
            'subscription_followups' => $activePurchases,
            'feedback_followups' => $feedbackStats['completed_feedback'],
            'expiry_followups' => $expiredPurchases,
        ];
    }

    /**
     * Build active orders query with filters (legacy method - kept for compatibility)
     */
    protected function buildActiveOrdersQuery($request, bool $isSalesEmployee, int $userId)
    {
        $query = Order::with(['user.personalDetails', 'user.latestTransactionLog'])
            ->where('is_deleted', 0)
            ->whereIn('status', ['success', 'paid'])
            ->select([
                'id', 'user_id', 'contact_no', 'amount', 'currency', 'type',
                'email_template_count', 'whatsapp_template_count',
                'followup_call', 'followup_note', 'followup_label', 'emp_id',
                'created_at', 'updated_at'
            ]);

        // Sales employee filter
        if ($isSalesEmployee) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });
        }

        // Apply subscription followup filter (database level)
        if ($request->filled('subscription_followup_filter')) {
            if ($request->subscription_followup_filter === 'pending') {
                $query->where('followup_call', 0);
            } elseif ($request->subscription_followup_filter === 'completed') {
                $query->where('followup_call', 1);
            }
        }

        // Apply subscription followup label filter (database level)
        if ($request->filled('subscription_followup_label')) {
            $subLabel = $request->subscription_followup_label;
            if ($subLabel === '__empty__') {
                $query->where(function ($q) {
                    $q->whereNull('followup_label')->orWhere('followup_label', '');
                });
            } else {
                $query->where('followup_label', $subLabel);
            }
        }

        // Apply search filter (database level)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contact_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Order by created_at descending for consistent pagination
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        return $query;
    }

    /**
     * Build expired users query with filters
     */
    protected function buildExpiredUsersQuery($request, bool $isSalesEmployee, int $userId)
    {
        $query = TransactionLog::with(['userData.personalDetails', 'subscription', 'subPlan'])
            ->where('expired_at', '<', now())
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('transaction_logs as tl2')
                    ->whereColumn('tl2.user_id', 'transaction_logs.user_id')
                    ->where('tl2.expired_at', '>', now());
            })
            ->whereRaw('transaction_logs.id = (
        SELECT MAX(tl3.id)
        FROM transaction_logs tl3
        WHERE tl3.user_id = transaction_logs.user_id
      )')
            ->select([
                'id', 'user_id', 'contact_no', 'paid_amount', 'currency_code',
                'plan_id', 'type', 'email_template_count', 'whatsapp_template_count',
                'followup_call', 'followup_note', 'followup_label', 'emp_id',
                'expired_at', 'created_at', 'updated_at'
            ]);

        // Sales employee filter
        if ($isSalesEmployee) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });
        }

        // Apply expiry followup filter (database level)
        if ($request->filled('expiry_followup_filter')) {
            if ($request->expiry_followup_filter === 'pending') {
                $query->where('followup_call', 0);
            } elseif ($request->expiry_followup_filter === 'completed') {
                $query->where('followup_call', 1);
            }
        }

        // Apply expiry followup label filter (database level)
        if ($request->filled('expiry_followup_label')) {
            $expLabel = $request->expiry_followup_label;
            if ($expLabel === '__empty__') {
                $query->where(function ($q) {
                    $q->whereNull('followup_label')->orWhere('followup_label', '');
                });
            } else {
                $query->where('followup_label', $expLabel);
            }
        }

        // Apply search filter (database level)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contact_no', 'like', "%{$search}%")
                    ->orWhereHas('userData', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Order by expired_at descending for consistent pagination
        $query->orderBy('expired_at', 'desc')->orderBy('id', 'desc');

        return $query;
    }

    /**
     * Calculate stats using optimized count queries
     */
    protected function calculateOptimizedStats($activeQuery, $expiredQuery)
    {
        // Clone queries for counting
        $activeCount = (clone $activeQuery)->count();
        $expiredCount = (clone $expiredQuery)->count();

        // For followup stats, simplified for cross-database (set to 0 for now)
        $activeNeedsFollowup = 0;
        $activeWithFollowup = 0;
        $expiredNeedsFollowup = 0;
        $expiredWithFollowup = 0;

        // Get feedback stats
        try {
            $feedbackRequests = WpFeedbackRequest::with('response')->get();

            $feedbackSent = $feedbackRequests->where('sent_at', '!=', null)->count();
            $pendingFeedback = $feedbackRequests->filter(function ($feedback) {
                return !$feedback->response && !$feedback->isExpired();
            })->count();
            $completedFeedback = $feedbackRequests->whereNotNull('response')->count();
            $feedbackNeedsFollowup = $feedbackRequests->filter(function ($feedback) {
                return $feedback->response && !$feedback->followup_call;
            })->count();
            $feedbackWithFollowup = $feedbackRequests->filter(function ($feedback) {
                return $feedback->response && $feedback->followup_call;
            })->count();

            $feedbackStats = [
                'feedback_sent' => $feedbackSent,
                'pending_feedback' => $pendingFeedback,
                'completed_feedback' => $completedFeedback,
                'feedback_needs_followup' => $feedbackNeedsFollowup,
                'feedback_with_followup' => $feedbackWithFollowup,
            ];
        } catch (\Exception $e) {
            $feedbackStats = [
                'feedback_sent' => 0,
                'pending_feedback' => 0,
                'completed_feedback' => 0,
                'feedback_needs_followup' => 0,
                'feedback_with_followup' => 0,
            ];
        }

        // Calculate expiry-related stats (simplified for performance)
        try {
            $expiredToday = (clone $expiredQuery)
                ->whereDate('expired_at', today())
                ->count();

            $expiredWeek = (clone $expiredQuery)
                ->where('expired_at', '>=', now()->subDays(7))
                ->count();

            $expiredMonth = (clone $expiredQuery)
                ->where('expired_at', '>=', now()->subDays(30))
                ->count();
        } catch (\Exception $e) {
            $expiredToday = 0;
            $expiredWeek = 0;
            $expiredMonth = 0;
        }

        // Calculate active expiring stats (simplified)
        try {
            $activeExpiringWeek = Order::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transaction_logs')
                    ->whereColumn('transaction_logs.user_id', 'orders.user_id')
                    ->where('transaction_logs.expired_at', '>', now())
                    ->where('transaction_logs.expired_at', '<=', now()->addDays(7));
            })
                ->where('is_deleted', 0)
                ->whereIn('status', ['success', 'paid'])
                ->count();

            $activeExpiringMonth = Order::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transaction_logs')
                    ->whereColumn('transaction_logs.user_id', 'orders.user_id')
                    ->where('transaction_logs.expired_at', '>', now())
                    ->where('transaction_logs.expired_at', '<=', now()->addDays(30));
            })
                ->where('is_deleted', 0)
                ->whereIn('status', ['success', 'paid'])
                ->count();
        } catch (\Exception $e) {
            $activeExpiringWeek = 0;
            $activeExpiringMonth = 0;
        }

        return [
            // Basic Stats
            'total' => $activeCount + $expiredCount,
            'active_subscriptions' => $activeCount,
            'expired_subscriptions' => $expiredCount,
            'pending_feedback' => $feedbackStats['pending_feedback'],
            'completed_feedback' => $feedbackStats['completed_feedback'],

            // Active Subscription Tracking
            'active_needs_followup' => $activeNeedsFollowup,
            'active_with_followup' => $activeWithFollowup,
            'active_expiring_week' => $activeExpiringWeek,
            'active_expiring_month' => $activeExpiringMonth,

            // Feedback Tracking
            'feedback_sent' => $feedbackStats['feedback_sent'],
            'feedback_needs_followup' => $feedbackStats['feedback_needs_followup'],
            'feedback_with_followup' => $feedbackStats['feedback_with_followup'],

            // Expiry Tracking
            'expired_today' => $expiredToday,
            'expired_week' => $expiredWeek,
            'expired_month' => $expiredMonth,
            'expired_needs_followup' => $expiredNeedsFollowup,
            'expired_with_followup' => $expiredWithFollowup,

            // Followup Type Stats
            'subscription_followups' => $activeCount,
            'feedback_followups' => $feedbackStats['completed_feedback'],
            'expiry_followups' => $expiredCount,
        ];
    }

    protected function getFeedbackLookup(): array
    {
        // Only load recent feedback requests (last 30 days) to avoid loading 100k records
        $feedbackRequests = WpFeedbackRequest::with(['response'])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        $lookupByUserId = [];
        $lookupByEmail = [];

        // Process in chunks to avoid memory issues
        foreach ($feedbackRequests as $feedback) {
            $response = $feedback->response;
            $feedbackStatus = $response
                ? 'Completed'
                : ($feedback->isExpired() ? 'Expired' : 'Pending');

            $payload = [
                'feedback_status' => $feedbackStatus,
                'feedback_rating' => $response->rating ?? null,
                'feedback_details' => $response ? [
                    'rating' => $response->rating,
                    'comment' => $response->feedback_text,
                    'suggestions' => $response->suggestions,
                    'submitted_at' => $response->submitted_at ? (is_string($response->submitted_at) ? $response->submitted_at : $response->submitted_at->format('Y-m-d H:i:s')) : null,
                ] : null,
                'feedback_sent' => (bool)$feedback->sent_at,
                'feedback_request_date' => $feedback->sent_at
                    ? (is_string($feedback->sent_at) ? $feedback->sent_at : $feedback->sent_at->format('Y-m-d H:i:s'))
                    : null,
                'feedback_model' => $feedback,
            ];

            if (!empty($feedback->user_id) && !isset($lookupByUserId[$feedback->user_id])) {
                $lookupByUserId[$feedback->user_id] = $payload;
            }

            // Get email from feedback request directly if available, otherwise skip
            if (!empty($feedback->email)) {
                $email = strtolower((string)$feedback->email);
                if ($email !== '' && !isset($lookupByEmail[$email])) {
                    $lookupByEmail[$email] = $payload;
                }
            }
        }

        return [
            'by_user_id' => $lookupByUserId,
            'by_email' => $lookupByEmail,
        ];
    }


    protected function commPattern(bool $emailSent, bool $whatsappSent): string
    {
        if ($emailSent && $whatsappSent) {
            return 'both';
        }
        if ($emailSent) {
            return 'email_only';
        }
        if ($whatsappSent) {
            return 'whatsapp_only';
        }

        return 'none';
    }

    /**
     * Visual timeline: subscription start → expiry (active) or end (expired).
     *
     * @param \Carbon\Carbon|string|null $startAt
     * @param \Carbon\Carbon|null $endAt
     */
    protected function buildJourneyMeta(string $subscriptionStatus, $startAt, $endAt, ?int $daysActive, ?int $daysToExpiry): array
    {
        $empty = [
            'journey_start_label' => null,
            'journey_end_label' => null,
            'journey_bar_pct' => null,
            'journey_caption' => '-',
        ];

        if (!$startAt) {
            return $empty;
        }

        $start = $startAt instanceof Carbon ? $startAt->copy() : Carbon::parse($startAt);
        $startLabel = $start->format('d M Y');

        if ($subscriptionStatus === 'Active') {
            if ($endAt instanceof Carbon && $endAt->isFuture()) {
                $totalDays = max(1, $start->diffInDays($endAt));
                $elapsed = min($totalDays, max(0, $start->diffInDays(now())));
                $pct = (int)round(min(100, max(0, ($elapsed / $totalDays) * 100)));

                return [
                    'journey_start_label' => $startLabel,
                    'journey_end_label' => $endAt->format('d M Y'),
                    'journey_bar_pct' => $pct,
                    'journey_caption' => sprintf('Day %d of ~%d until expiry', $elapsed, $totalDays),
                ];
            }

            $caption = $daysActive !== null && $daysActive > 0
                ? sprintf('Active %d day%s', $daysActive, $daysActive === 1 ? '' : 's')
                : 'Active subscription';
            if ($daysToExpiry !== null && $daysToExpiry > 0) {
                $caption .= sprintf(' · %d day%s left', $daysToExpiry, $daysToExpiry === 1 ? '' : 's');
            }

            return [
                'journey_start_label' => $startLabel,
                'journey_end_label' => $endAt instanceof Carbon ? $endAt->format('d M Y') : null,
                'journey_bar_pct' => null,
                'journey_caption' => $caption,
            ];
        }

        if ($subscriptionStatus === 'Expired' && $endAt instanceof Carbon) {
            $totalDays = max(1, $start->diffInDays($endAt));

            return [
                'journey_start_label' => $startLabel,
                'journey_end_label' => $endAt->format('d M Y'),
                'journey_bar_pct' => 100,
                'journey_caption' => sprintf('%d-day cycle · ended %s', $totalDays, $endAt->format('d M Y')),
            ];
        }

        return $empty;
    }

    public function followupUpdate(Request $request)
    {
        $id = $request->id;
        $parts = explode('_', $id);
        $source = $parts[0];
        $realId = $parts[1];

        if ($source === 'order') {
            $record = Order::find($realId);
        } elseif ($source === 'expired') {
            $record = TransactionLog::find($realId);
        } elseif ($source === 'feedback') {
            $record = WpFeedbackRequest::find($realId);
        } elseif ($source === 'purchase') {
            // For purchase records, use unified_support table
            $purchase = MasterPurchaseHistory::find($realId);
            if (!$purchase) {
                return response()->json(['success' => false, 'message' => 'Purchase record not found'], 404);
            }

            // Get or create unified_support record
            $support = UnifiedSupport::firstOrCreate(
                ['purchase_history_id' => $realId],
                ['user_id' => $purchase->user_id]
            );

            // Determine if this is active or expired subscription
            $isActive = (int)$purchase->status === 1;
            if ($isActive && $purchase->expired_at) {
                $expiredAtDate = Carbon::parse($purchase->expired_at);
                if ($expiredAtDate->isPast()) {
                    $isActive = false;
                }
            }

            $empName = 'N/A';
            if ($request->has('followup_call') && (int)$request->followup_call === 0) {
                // Reset followup
                if ($isActive) {
                    $support->subscription_followup_call = 0;
                    $support->subscription_followup_note = null;
                    $support->subscription_followup_label = null;
                } else {
                    $support->expire_followup_call = 0;
                    $support->expire_followup_note = null;
                    $support->expire_followup_label = null;
                }
                $support->emp_id = 0;
            } else {
                // Set followup
                $labelRaw = $request->input('followup_label');
                $labelNorm = is_string($labelRaw) ? trim($labelRaw) : '';
                $labelNorm = $labelNorm === '' ? null : $labelNorm;

                if ($labelNorm !== null && !array_key_exists($labelNorm, self::FOLLOWUP_LABELS)) {
                    return response()->json(['success' => false, 'message' => 'Invalid follow-up label'], 422);
                }

                if ($isActive) {
                    $support->subscription_followup_call = 1;
                    $support->subscription_followup_note = $request->followup_note ?? '';
                    $support->subscription_followup_label = $labelNorm;
                } else {
                    $support->expire_followup_call = 1;
                    $support->expire_followup_note = $request->followup_note ?? '';
                    $support->expire_followup_label = $labelNorm;
                }

                $support->emp_id = auth()->user()->id;
                $empName = auth()->user()->name ?? 'Admin';
            }

            $support->save();

            return response()->json([
                'success' => true,
                'message' => 'Followup updated successfully',
                'emp_name' => $empName
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid source'], 400);
        }

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        $empName = 'N/A';
        if ($request->has('followup_call') && (int)$request->followup_call === 0) {
            $record->followup_call = $record instanceof WpFeedbackRequest ? false : 0;
            $record->followup_note = null;
            $record->followup_label = null;

            // For feedback, use emp_id (as it doesn't have ownership concept)
            // For orders and transaction_logs, we keep emp_id as is (don't reset to 0)
            if ($record instanceof WpFeedbackRequest) {
                $record->emp_id = 0;
            }
        } else {
            $labelRaw = $request->input('followup_label');
            $labelNorm = is_string($labelRaw) ? trim($labelRaw) : '';
            $labelNorm = $labelNorm === '' ? null : $labelNorm;

            if ($record instanceof WpFeedbackRequest) {
                if ($labelNorm !== null && !array_key_exists($labelNorm, self::FEEDBACK_FOLLOWUP_LABELS)) {
                    return response()->json(['success' => false, 'message' => 'Invalid WhatsApp feedback follow-up label'], 422);
                }
            } else {
                if ($labelNorm !== null && !array_key_exists($labelNorm, self::FOLLOWUP_LABELS)) {
                    return response()->json(['success' => false, 'message' => 'Invalid follow-up label'], 422);
                }
            }

            $record->followup_call = $record instanceof WpFeedbackRequest ? true : 1;
            $record->followup_note = $request->followup_note ?? '';
            $record->followup_label = $labelNorm;

            // Set emp_id to current user (take ownership) - same as OrderUserController
            $record->emp_id = auth()->user()->id;

            $empName = auth()->user()->name ?? 'Admin';
        }

        $saved = $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully',
            'emp_name' => $empName
        ]);
    }

    /**
     * Only query wp_feedback_requests.emp_id when the follow-up migration has been applied.
     * Avoids SQLSTATE[42S22] if code is deployed before `php artisan migrate`.
     */
    protected function wpFeedbackRequestsHasFollowupColumns(): bool
    {
        try {
            $conn = 'crafty_automation_mysql';

            return Schema::connection($conn)->hasTable('wp_feedback_requests')
                && Schema::connection($conn)->hasColumn('wp_feedback_requests', 'emp_id');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return int[]
     */
    protected function getWpFeedbackRequestEmpIdsForNameLookup(): array
    {
        if (!$this->wpFeedbackRequestsHasFollowupColumns()) {
            return [];
        }

        return WpFeedbackRequest::query()
            ->where('emp_id', '>', 0) // Only get emp_ids that are actual user IDs (not 0 or null)
            ->pluck('emp_id')
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    protected function getEmployeeNames(array $employeeIds): array
    {
        if (empty($employeeIds)) {
            return [];
        }

        return User::whereIn('id', $employeeIds)
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function resolveFeedbackData(array $feedbackLookup, $userId, $email): array
    {
        $emailKey = strtolower((string)$email);
        $data = $feedbackLookup['by_user_id'][$userId]
            ?? ($emailKey !== '' ? ($feedbackLookup['by_email'][$emailKey] ?? null) : null);

        return $data ?? [
            'feedback_status' => null,
            'feedback_rating' => null,
            'feedback_details' => null,
            'feedback_sent' => false,
            'feedback_request_date' => null,
            'feedback_model' => null,
        ];
    }

    protected function formatPlanTitle(?string $type): string
    {
        $value = trim((string)$type);

        if ($value === '') {
            return '-';
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    /**
     * Explains where email/WhatsApp counts live + WhatsApp feedback request metadata.
     *
     * @param 'unified_support'|'active_order'|'transaction_log' $commSource
     */
    protected function buildOutreachFeedbackExtras(
        string $commSource,
        string $recordRef,
               $feedbackModel,
        array  $feedbackData,
        bool   $isActive = true
    ): array
    {
        if ($commSource === 'unified_support') {
            $blurb = $isActive
                ? 'Email and WhatsApp counts are stored in unified_support table (subscription_email_sent, subscription_wp_sent).'
                : 'Email and WhatsApp counts are stored in unified_support table (expire_email_sent, expire_wp_sent).';
        } elseif ($commSource === 'active_order') {
            $blurb = 'Email and WhatsApp counts are stored on the active order record while the user is subscribed.';
        } else {
            $blurb = 'Email and WhatsApp counts are stored on this expired transaction log (latest row per user).';
        }

        $extras = [
            'comm_track_source' => $commSource,
            'comm_track_ref' => $recordRef,
            'comm_track_blurb' => $blurb,
            'feedback_wp_string_id' => null,
            'feedback_wp_expires_label' => null,
            'feedback_wp_completed_label' => null,
            'feedback_wp_submitted_label' => null,
            'feedback_wp_status_raw' => null,
            'feedback_comment_preview' => null,
        ];

        if ($feedbackModel instanceof WpFeedbackRequest) {
            $extras['feedback_wp_string_id'] = $feedbackModel->string_id;
            $extras['feedback_wp_status_raw'] = $feedbackModel->status;
            $extras['feedback_wp_expires_label'] = $this->formatShortDateTime($feedbackModel->expires_at);
            $extras['feedback_wp_completed_label'] = $this->formatShortDateTime($feedbackModel->completed_at);
        }

        $details = $feedbackData['feedback_details'] ?? null;
        if (is_array($details)) {
            if (!empty($details['submitted_at'])) {
                $extras['feedback_wp_submitted_label'] = $this->formatShortDateTime($details['submitted_at']);
            }
            if (!empty($details['comment'])) {
                $extras['feedback_comment_preview'] = Str::limit(strip_tags((string)$details['comment']), 220);
            }
        }

        return $extras;
    }

    protected function formatShortDateTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $c = $value instanceof Carbon ? $value->copy() : Carbon::parse($value);

            return $c->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

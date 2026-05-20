<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Automation\UnifiedSupport;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Automation\WpFeedbackResponse;
use App\Models\Order;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserDataDeleted;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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

    public function index(Request $request): View|Factory|string|Application
    {
        try {
            $perPage = (int)$request->get('per_page', 50);
            $perPage = min(max($perPage, 10), 500);

            $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
            $userId = auth()->user()->id;

            $query = $this->buildPurchaseHistoryQuery($request, $isSalesEmployee, $userId);
            $clonedQuery = clone $query;
            $paginatedPurchases = $query->paginate($perPage, ['*'], 'page', $request->get('page', 1));

            $transformedLeads = collect();
            foreach ($paginatedPurchases as $purchase) {
                $lead = $this->transformPurchaseHistoryToLead($purchase);
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

            $stats = $this->calculateOptimizedStatsFromPurchaseHistory($clonedQuery);

            return view('unified_leads.index', [
                'leads' => $leads,
                'stats' => $stats,
                'total' => $paginatedPurchases->total(),
                'followupLabels' => self::FOLLOWUP_LABELS,
                'feedbackFollowupLabels' => self::FEEDBACK_FOLLOWUP_LABELS,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Build purchase history query with filters
     */
    protected function buildPurchaseHistoryQuery($request, bool $isSalesEmployee, int $userId): Builder|MasterPurchaseHistory
    {
        $query = MasterPurchaseHistory::with(['userData.personalDetails', 'deletedUser', 'employee', 'unifiedSupport', 'wpFeedbackRequest', 'wpFeedbackResponse'])
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
    protected function transformPurchaseHistoryToLead(MasterPurchaseHistory $purchase): ?array
    {
        /** @var UserData|UserDataDeleted $user */
        $user = $purchase->deletedUser ?? $purchase->userData;
        $isDeletedUser = $purchase->deletedUser !== null;

        $userData = [
            'uid' => $user?->uid ?? $purchase->user_id,
            'name' => $user?->name ?? 'Unknown User',
            'email' => $user?->email ?? '-',
            'number' => $purchase->contact_no ?? $user?->contact_no ?? '-',
            'personalDetails' => $user?->personalDetails?->usage ?? '-',
            'isDeleted' => $isDeletedUser,
        ];

        // Determine if this is an active or expired subscription based on status field AND expired_at date
        $isActive = $purchase->status === 1;

        // Check if subscription has expired based on expired_at date
        if ($isActive && $purchase->expired_at) {
            $expiredAtDate = Carbon::parse($purchase->expired_at);
            if ($expiredAtDate->isPast()) {
                $isActive = false; // Override status if expired_at is in the past
            }
        }

//        // Filter based on subscription status
//        if ($isActive && !$includeActive) {
//            return null;
//        }
//        if (!$isActive && !$includeExpired) {
//            return null;
//        }

        $feedbackData = $this->resolveFeedbackData($purchase);
        $feedbackModel = $feedbackData['feedback_model'];
        $hasFeedbackFollowup = $feedbackData['feedback_sent'] && $feedbackModel;

        // Get followup data from unified_support table
        $support = $purchase->unifiedSupport;

        // Use expired_at from purchase_history table for all subscriptions
        $expiryDate = null;
        if ($purchase->expired_at) {
            $expiryDate = Carbon::parse($purchase->expired_at);
        }

        $daysToExpiry = $isActive && $expiryDate ? now()->diffInDays($expiryDate, false) : null;
        $daysActive = $isActive && $purchase->created_at ? now()->diffInDays($purchase->created_at) : null;
        $daysExpired = !$isActive && $expiryDate ? now()->diffInDays($expiryDate) : null;

        // Get email/WhatsApp counts and emp_id from purchase_history table and sync to unified_support
        $purchaseEmailCount = $purchase->email_sent ?? 0;
        $purchaseWpCount = $purchase->wp_sent ?? 0;
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
            'isDeleted' => $userData['isDeleted'],
            'user_id' => $userData['uid'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'contact_no' => $userData['number'],
            'amount' => $purchase->currency_code . ' ' . $purchase->amount,
            'amount_numeric' => $purchase->amount ?? 0,
            'plan_type' => $this->getProductTypeName($purchase->product_type),
            'plan_title' => $this->getProductTitle($purchase->product_id, $purchase->product_type),
            'subscription_status' => $isActive ? 'Active' : 'Expired',
            'order_status' => $purchase->payment_status ?? 'Success',
            'usage_type' => $userData['personalDetails'],
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
     * Get product type name
     */
    protected function getProductTypeName($productType): string
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
    protected function getProductTitle($productId, $productType): string
    {
        // You can implement logic to get actual product names from pricing tables
        return $this->getProductTypeName($productType) . ' Plan';
    }

    /**
     * Get expire meta text
     */
    protected function getExpireMeta($isActive, $expiryDate, $daysToExpiry, $daysExpired = null): ?string
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
    protected function calculateOptimizedStatsFromPurchaseHistory($purchaseQuery): array
    {

        $subTypes = ['old_sub', 'new_sub', 'offer_sub'];

        // Clone query for counting
        $totalPurchases = (clone $purchaseQuery)->count();

        // Count active vs expired based on status field
        $activePurchases = (clone $purchaseQuery)->whereIn('product_type', $subTypes)->where('status', 1)->count();
        $expiredPurchases = (clone $purchaseQuery)->whereIn('product_type', $subTypes)->where('status', 0)->count();

        // For followup stats, count directly from unified_support (simplified for cross-database)
        $activeNeedsFollowup = 0;
        $activeWithFollowup = 0;
        $expiredNeedsFollowup = 0;
        $expiredWithFollowup = 0;

        // Get feedback stats
        try {

            $feedbackSent = WpFeedbackRequest::count();
            $completedFeedback = WpFeedbackResponse::count();

            $base = WpFeedbackRequest::query();
            $pendingFeedback = (clone $base)->whereDoesntHave('response')->where('expires_at', '>', Carbon::now())->count();
            $feedbackNeedsFollowup = (clone $base)->whereHas('response')->whereNull('followup_call')->count();
            $feedbackWithFollowup = (clone $base)->whereHas('response')->whereNotNull('followup_call')->count();

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
                ->whereIn('product_type', $subTypes)
                ->whereDate('expired_at', today())
                ->count();

            $expiredWeek = (clone $purchaseQuery)
                ->where('status', 0)
                ->whereIn('product_type', $subTypes)
                ->where('expired_at', '>=', now()->subDays(7))
                ->count();

            $expiredMonth = (clone $purchaseQuery)
                ->where('status', 0)
                ->whereIn('product_type', $subTypes)
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
                ->whereIn('product_type', $subTypes)
                ->whereBetween('expired_at', [
                    Carbon::now(),
                    Carbon::now()->addDays(7)
                ])
                ->count();

            $activeExpiringMonth = (clone $purchaseQuery)
                ->where('status', 1)
                ->whereIn('product_type', $subTypes)
                ->whereBetween('expired_at', [
                    Carbon::now(),
                    Carbon::now()->addDays(30)
                ])
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
     * Calculate stats using optimized count queries
     */
    protected function calculateOptimizedStats($activeQuery, $expiredQuery): array
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
        $feedbackRequests = WpFeedbackRequest::with(['response'])
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
    protected function buildJourneyMeta(string $subscriptionStatus, \Carbon\Carbon|string|null $startAt, ?\Carbon\Carbon $endAt, ?int $daysActive, ?int $daysToExpiry): array
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

    public function followupUpdate(Request $request): JsonResponse
    {
        $id = $request->id;
        $parts = explode('_', $id);
        $source = $parts[0];
        $realId = $parts[1];

        if ($source === 'order') {
            $record = Order::find($realId);
        } elseif ($source === 'expired') {
            $record = MasterPurchaseHistory::find($realId);
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

        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully',
            'emp_name' => $empName
        ]);
    }

    protected function resolveFeedbackData(MasterPurchaseHistory $purchase): array
    {
        $feedback = $purchase->wpFeedbackRequest;
        $response = $purchase->wpFeedbackResponse;

        if ($feedback) {
            return [
                'feedback_status' => $response ? 'Completed' : ($feedback->isExpired() ? 'Expired' : 'Pending'),
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
        }
        return [
            'feedback_status' => null,
            'feedback_rating' => null,
            'feedback_details' => null,
            'feedback_sent' => false,
            'feedback_request_date' => null,
            'feedback_model' => null,
        ];
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

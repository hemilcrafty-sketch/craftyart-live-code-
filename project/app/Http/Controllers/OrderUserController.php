<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Models\Order;
use App\Models\PersonalDetails;
use App\Models\ManageSubscription;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\SubPlan;
use App\Models\Revenue\Sale;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserData;
use App\Models\Pricing\PaymentConfiguration;
use App\Models\PurchaseHistory;
use App\Services\PhonePeAutoPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pricing\OfferPage;


class OrderUserController extends AppBaseController
{
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

    /**
     * Constructor - Exclude payment callback methods from auth middleware
     */
    public function __construct()
    {
        // DON'T call parent::__construct() to avoid applying auth to all methods
        // Instead, apply auth middleware only to specific methods (exclude payment methods)
        $this->middleware('auth')->except([
            'paymentLinkCallback',
            'phonePePaymentLinkCallback',
            'paymentSuccess',
            'paymentFailed',
            'showPhonePePaymentPage',
            'initiatePhonePePayment'
        ]);
    }

    public function index(Request $request)
    {
        // AJAX request to get new orders data
        Log::info("ssdsadsadsadsadsadsa");

        if ($request->ajax() && $request->get('get_new_orders')) {
            $lastId = $request->get('last_id', 0);
            $statusFilter = $request->get('status_filter', 'all');

            $newOrdersQuery = Order::with(['user'])
                ->where('is_deleted', 0)
                  ->where('show_data', 1)
                ->where('id', '>', $lastId);

            // Apply same status filter as main query
            if ($statusFilter === 'active') {
                $newOrdersQuery->whereIn('status', ['success', 'paid']);
            } elseif ($statusFilter === 'pending') {
                $newOrdersQuery->where('status', 'pending');
            } elseif ($statusFilter === 'failed') {
                $newOrdersQuery->where('status', 'failed');
            } else {
                // Default: show only pending and failed
                $newOrdersQuery->whereIn('status', ['failed', 'pending']);
            }

            $newOrders = $newOrdersQuery->orderBy('id', 'desc')
                ->get()
                ->map(function ($order) {
                    $planItems = $order->plan_items;
                    if (is_object($planItems) && method_exists($planItems, 'toArray')) {
                        $planItems = $planItems->toArray();
                    }

                    return [
                        'id' => $order->id,
                        'user_name' => $order->user?->name ?? '-',
                        'email' => $order->user?->email ?? '-',
                        'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                        'amount_with_symbol' => $order->amount_with_symbol ?? '-',
                        'type' => $order->type ?? '',
                        'plan_items' => is_array($planItems) ? implode(', ', $planItems) : '-',
                        'status' => $order->status,
                        'is_subscription_active' => $order->isSubscriptionActive(),
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'email_template_count' => $order->email_template_count ?? 0,
                        'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                        'from_where' => $order->from_where ?? '-',
                    ];
                });

            return response()->json([
                'new_orders' => $newOrders
            ]);
        }

        $searchTerm = $request->get('search');
        $statusFilter = $request->get('status_filter', 'all');

        $query = Order::query()
            ->with(['user'])
            ->where('is_deleted', 0);

        // Status filter - include active subscriptions
        if ($statusFilter === 'active') {
            // Show only active subscriptions
            $query->whereIn('status', ['success', 'paid']);
        } elseif ($statusFilter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'failed') {
            $query->where('status', 'failed');
        } else {
            // Default: show only pending and failed
            $query->whereIn('status', ['failed', 'pending']);
        }


        // changes Distribute lead to Every Sales User
        $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);

        if ($isSalesEmployee) {
            $userId = auth()->user()->id;

            // Wrap the conditions in a closure to maintain status filter
            $query->where(function ($q) use ($userId) {
                $q->whereNull('emp_id')
                    ->orWhere('emp_id', 0)
                    ->orWhere('emp_id', $userId);
            });

            //            $salesEmployeeIds = User::whereUserType(auth()->user()->user_type)
//                ->whereStatus(1)
//                ->orderBy('id')
//                ->pluck('id')
//                ->toArray();
//
//            $totalSalesEmployees = count($salesEmployeeIds);
//            $employeeIndex = array_search($userId, $salesEmployeeIds);

            //            if ($employeeIndex !== false && $totalSalesEmployees > 0) {

            //                $query->where(function ($q) use (
//                    $userId,
//                    $employeeIndex,
//                    $totalSalesEmployees
//                ) {
//
//                    /** 1️⃣ OWN LEADS */
//                    $q->where('orders.emp_id', $userId);
//
//                    /** 2️⃣ UNASSIGNED DISTRIBUTED GROUPS */
//                    $q->orWhereIn('orders.id', function ($sub) use (
//                        $employeeIndex,
//                        $totalSalesEmployees
//                    ) {
//
//                        $sub->select('o.id')
//                            ->from('orders as o')
//                            ->join(DB::raw("
//                        (
//                            SELECT
//                                user_id,
//                                plan_id,
//                                ROW_NUMBER() OVER (
//                                    ORDER BY user_id
//                                ) - 1 AS grp_index
//                            FROM orders
//                            WHERE is_deleted = 0
//                            AND emp_id = 0
//                            AND status IN ('failed','pending')
//                            GROUP BY user_id, plan_id
//                        ) g
//                    "), function ($join) {
//                                $join->on('o.user_id', '=', 'g.user_id');
//                            })
//                            ->whereRaw('g.grp_index % ? = ?', [
//                                $totalSalesEmployees,
//                                $employeeIndex
//                            ]);
//                    });
//                });
//            }
        }

        // Filters
        $filterType = $request->get('filter_type', 'all');
        $typeFilter = $request->get('type_filter');
        $amountSort = $request->get('amount_sort');
        $whatsappFilter = $request->get('whatsapp_filter');
        $emailFilter = $request->get('email_filter');
        $fromWhere = $request->get('from_where');
        $followupFilter = $request->get('followup_filter');
        $followupLabelFilter = $request->get('followup_label_filter'); // New filter
        $usageTypeFilter = $request->get('usage_type_filter'); // Usage Type filter

        $activeOfferSlugs = OfferPage::whereHas('offerPackage', function ($q) {
            $q->where('status', 1);
        })->pluck('slug')->toArray();

        if ($filterType === 'remove_duplicate') {
            $query->whereIn('id', function ($sub) use ($statusFilter) {
                $sub->select(DB::raw('MAX(id)'))
                    ->from('orders')
                    ->where('is_deleted', 0);

                // Apply same status filter in subquery
                if ($statusFilter === 'active') {
                    $sub->whereIn('status', ['success', 'paid']);
                } elseif ($statusFilter === 'pending') {
                    $sub->where('status', 'pending');
                } elseif ($statusFilter === 'failed') {
                    $sub->where('status', 'failed');
                } else {
                    // Default: show only pending and failed
                    $sub->whereIn('status', ['failed', 'pending']);
                }

                $sub->groupBy('user_id');
            });
        }

        // Remove the old status filter logic since it's now handled at the top
        // if (in_array($statusFilter, ['pending', 'failed'])) {
        //     $query->where('status', $statusFilter);
        // }

        if (in_array($typeFilter, ['old_sub', 'new_sub', 'template', 'video', 'caricature'])) {
            $query->where('type', $typeFilter);
        }

        if (!empty($fromWhere)) {
            $query->where(function ($q) use ($fromWhere, $activeOfferSlugs) {

                if ($fromWhere === 'meta_google') {
                    $q->whereNotNull('fbc')
                        ->where(function ($qq) {
                            $qq->whereNotNull('gclid')
                                ->orWhereNotNull('wbraid')
                                ->orWhereNotNull('gbraid');
                        });
                } elseif ($fromWhere === 'meta') {
                    $q->whereNotNull('fbc')
                        ->whereNull('gclid')
                        ->whereNull('wbraid')
                        ->whereNull('gbraid');
                } elseif ($fromWhere === 'google') {
                    $q->whereNull('fbc')
                        ->where(function ($qq) {
                            $qq->whereNotNull('gclid')
                                ->orWhereNotNull('wbraid')
                                ->orWhereNotNull('gbraid');
                        });
                } elseif ($fromWhere === 'seo') {
                    $q->whereNull('fbc')
                        ->whereNull('gclid')
                        ->whereNull('wbraid')
                        ->whereNull('gbraid');
                } elseif (in_array($fromWhere, $activeOfferSlugs)) {
                    $q->where(function ($query) use ($fromWhere) {
                        $query->where('url', 'LIKE', "%/{$fromWhere}?%")
                            ->orWhere('url', 'LIKE', "%/{$fromWhere}");
                    });
                }

            });
        }

        // New followup label filter
        if (!empty($followupLabelFilter) && array_key_exists($followupLabelFilter, self::FOLLOWUP_LABELS)) {
            $query->where('followup_label', $followupLabelFilter);
        }

        // Usage Type filter - Join with personal_details table to filter by usage
        if (!empty($usageTypeFilter) && in_array($usageTypeFilter, ['personal', 'professional'])) {
            $query->whereHas('user', function ($q) use ($usageTypeFilter) {
                $q->whereHas('personalDetails', function ($pd) use ($usageTypeFilter) {
                    $pd->where('usage', $usageTypeFilter);
                });
            });
        }

        $filters = [
            'whatsapp_template_count' => $whatsappFilter,
            'email_template_count' => $emailFilter,
            'followup_call' => $followupFilter,
        ];

        foreach ($filters as $column => $filter) {
            match ($filter) {
                'sent' => $query->where("$column", '>', 0),
                'not_sent' => $query->where("$column", '<=', 0),
                'called' => $query->where("$column", 1),
                'not_called' => $query->where("$column", 0),
                default => null,
            };
        }

        // changes Remove Unnecessary Search Filter
        // Search
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->orWhereHas('user', function ($uq) use ($searchTerm) {
                    $uq->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        // Sorting
        $sortBy = in_array($amountSort, ['asc', 'desc']) ? 'amount' : 'id';
        $sortOrder = $amountSort ?: 'desc';
        $request->merge(['sort_by' => $sortBy, 'sort_order' => $sortOrder]);

        $searchableFields = [];
        $OrderUsers = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        $datas = [];
        $datas['packageArray'] = Subscription::all();

        return view('order_user.index', compact(
            'OrderUsers',
            'searchableFields',
            'filterType',
            'statusFilter',
            'typeFilter',
            'amountSort',
            'whatsappFilter',
            'emailFilter',
            'fromWhere',
            'followupFilter',
            'followupLabelFilter', // Pass to view
            'usageTypeFilter', // Pass usage type filter to view
            'activeOfferSlugs', // Pass active slugs to view
            'datas',
            'searchTerm'
        ))->with('followupLabels', self::FOLLOWUP_LABELS); // Pass labels to view
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $orderUser = Order::whereId($request->id)->first();

        if (!$orderUser) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check authorization
        $isSalesUser = RoleManager::isSalesEmployee($currentUser->user_type);
        $isAdminOrManager = RoleManager::isAdmin($currentUser->user_type) ||
            RoleManager::isManager($currentUser->user_type) ||
            RoleManager::isSalesManager($currentUser->user_type);

        // Admin, Manager, and Sales Manager can always update followup
        if ($isAdminOrManager) {
            // Allow update
        }
        // Sales user can only update if order is not assigned or assigned to them
        elseif ($isSalesUser) {
            if (!empty($orderUser->emp_id) && $orderUser->emp_id != 0 && $orderUser->emp_id != $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is assigned to another Sales user. Only the assigned user can update followup.'
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update followup'
            ], 403);
        }

        // Update followup
        if ($request->has('followup_call') && $request->followup_call == 0) {
            $orderUser->followup_call = 0;
            $orderUser->followup_note = null;
            $orderUser->followup_label = null;
        } else {
            $orderUser->followup_call = 1;
            $orderUser->followup_note = $request->followup_note ?? '';
            $orderUser->followup_label = $request->followup_label;
        }

        // Set emp_id to current Sales user (take ownership)
        $orderUser->emp_id = $currentUser->id;

        $orderUser->save();

        // Broadcast followup change via WebSocket for real-time updates
        WebSocketBroadcastController::broadcastOrderFollowUpChanged($orderUser);

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }

    /**
     * Get user usage type from personal_details
     */
    public function getUserUsage(Request $request): JsonResponse
    {
        $orderId = $request->order_id;
        $order = Order::find($orderId);

        if (!$order || !$order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order or user not found'
            ]);
        }

        $personalDetails = PersonalDetails::where('uid', $order->user_id)->first();

        return response()->json([
            'success' => true,
            'user_id' => $order->user_id,
            'usage' => $personalDetails->usage ?? null
        ]);
    }

    /**
     * Get purchase history for a user
     */
    public function getPurchaseHistory($userId)
    {
        try {
            // Get only successful orders
            $allOrders = Order::with(['subPlan', 'subscription', 'offerPackage.subPlan'])
                ->where('user_id', $userId)
                ->where('is_deleted', 0)
                ->whereIn('status', ['success', 'paid']) // Only successful orders
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    // Determine payment method
                    $paymentMethod = 'Completed';

                    // First, try to find payment method from Sale record
                    $sale = Sale::where('order_id', $order->id)->first();
                    if ($sale && $sale->payment_method) {
                        // Use payment_method from sale record
                        $paymentMethod = ucfirst($sale->payment_method); // 'phonepe' -> 'Phonepe', 'razorpay' -> 'Razorpay'
                    } else {
                        // Fallback: Determine from order fields
                        if (!empty($order->razorpay_payment_id)) {
                            $paymentMethod = 'Razorpay';
                        } elseif (!empty($order->stripe_payment_intent_id) || !empty($order->stripe_txn_id)) {
                            $paymentMethod = 'Stripe';
                        }
                    }

                    // Determine transaction ID
                    $transactionId = $order->razorpay_payment_id
                        ?? $order->stripe_payment_intent_id
                        ?? $order->stripe_txn_id
                        ?? $order->crafty_id
                        ?? '-';

                    // Format plan items based on type
                    $planItemsDisplay = '-';

                    if (in_array($order->type, ['template', 'video', 'caricature'])) {
                        $planItems = $order->plan_items;
                        if ($planItems && $planItems->isNotEmpty()) {
                            $stringIds = $planItems->pluck('string_id')->filter()->toArray();
                            $planItemsDisplay = !empty($stringIds) ? implode(', ', $stringIds) : 'Custom Items';
                        } else {
                            $planItemsDisplay = 'Custom Items';
                        }
                    } elseif ($order->type === 'new_sub') {
                        $plan = $order->subPlan;
                        if ($plan) {
                            $planItemsDisplay = $plan->plan_name ?? 'New Subscription';
                        } else {
                            $planItemsDisplay = 'Subscription #' . $order->plan_id;
                        }
                    } elseif ($order->type === 'old_sub') {
                        $plan = $order->subscription;
                        if ($plan) {
                            $planItemsDisplay = $plan->package_name ?? 'Subscription';
                        } else {
                            $planItemsDisplay = 'Package #' . $order->plan_id;
                        }
                    } elseif ($order->type === 'offer') {
                        $offerPackage = $order->offerPackage;
                        if ($offerPackage && $offerPackage->subPlan) {
                            $planItemsDisplay = $offerPackage->subPlan->plan_name ?? 'Offer Plan';
                        } else {
                            $planItemsDisplay = 'Offer #' . $order->plan_id;
                        }
                    } else {
                        $planItemsDisplay = $order->plan_id ? $order->plan_id : 'Unknown';
                    }

                    return [
                        'id' => $order->id,
                        'amount' => $order->amount_with_symbol,
                        'status' => $order->status,
                        'type' => $order->type,
                        'plan_items' => $planItemsDisplay,
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $transactionId,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'is_subscription_active' => $order->isSubscriptionActive(),
                        'user_name' => $order->user?->name ?? 'Unknown',
                        'email' => $order->user?->email ?? '',
                        'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '',
                    ];
                });

            // Split into recent (10) and remaining
            $recentOrders = $allOrders->take(10);
            $remainingCount = $allOrders->count() - 10;

            return response()->json([
                'success' => true,
                'orders' => $recentOrders,
                'all_orders' => $allOrders, // Send all for load more
                'total_orders' => $allOrders->count(),
                'showing_count' => $recentOrders->count(),
                'remaining_count' => max(0, $remainingCount),
            ]);

        } catch (\Exception $e) {
            \Log::error('Purchase History Error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plans based on subscription type
     */
    public function getPlans(Request $request)
    {
        try {
            $subscriptionType = $request->get('subscription_type', 'new_sub');
            $plans = [];

            \Log::info('Getting plans', ['subscription_type' => $subscriptionType]);

            if ($subscriptionType === 'new_sub') {
                // Get new subscription plans from sub_plans table (crafty_pricing_mysql database)
                try {
                    $newPlans = SubPlan::with('plan') // Load related plan
                        ->orderBy('id', 'asc')
                        ->get();

                    \Log::info('New plans fetched', ['count' => $newPlans->count()]);

                    foreach ($newPlans as $subPlan) {
                        // Get plan details
                        $planDetails = is_string($subPlan->plan_details) ? json_decode($subPlan->plan_details, true) : $subPlan->plan_details;

                        // Get plan name from related plan
                        $planName = $subPlan->plan->name ?? 'Plan ' . $subPlan->id;

                        // Get price from plan_details (use offer price if available, otherwise regular price)
                        $planPrice = $planDetails['inr_offer_price'] ?? $planDetails['inr_price'] ?? 0;

                        // Get duration info based on duration_id
                        $durationText = '';
                        if ($subPlan->duration_id == 1) {
                            $durationText = ' (Monthly)';
                        } elseif ($subPlan->duration_id == 2) {
                            $durationText = ' (Yearly)';
                        } elseif ($subPlan->duration_id == 3) {
                            $durationText = ' (Lifetime)';
                        }

                        $plans[] = [
                            'id' => $subPlan->string_id ?? $subPlan->id,
                            'name' => $planName . $durationText,
                            'price' => $planPrice,
                            'type' => 'new_sub'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Error fetching new plans', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                }

            } elseif ($subscriptionType === 'old_sub') {
                // Get old subscription plans from subscriptions table (crafty_db database)
                try {
                    $oldPlans = Subscription::orderBy('price', 'asc')
                        ->get();

                    \Log::info('Old plans fetched', ['count' => $oldPlans->count()]);

                    foreach ($oldPlans as $plan) {
                        // Extract plan name from package_name or desc
                        $planName = $plan->package_name ?? $plan->desc ?? 'Plan ' . $plan->id;

                        // Clean up package name if it's a package identifier
                        if (strpos($planName, 'com.') === 0) {
                            $planName = ucwords(str_replace(['com.', '.'], ['', ' '], $planName));
                        }

                        $plans[] = [
                            'id' => $plan->id,
                            'name' => $planName . ' (' . $plan->validity . ' days)',
                            'price' => $plan->price ?? 0,
                            'type' => 'old_sub'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Error fetching old plans', ['error' => $e->getMessage()]);
                }
            }

            \Log::info('Plans prepared', ['count' => count($plans), 'plans' => $plans]);

            return response()->json([
                'success' => true,
                'plans' => $plans,
                'subscription_type' => $subscriptionType
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Plans Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading plans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate email - check if email exists in user_data table and user is active
     */
    public function validateEmail(Request $request)
    {
        try {
            $email = $request->input('email');

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
            }

            \Log::info('Validating email', ['email' => $email]);

            // Check if email exists in user_data table
            $user = UserData::where('email', $email)->first();

            if (!$user) {
                \Log::info('Email not found', ['email' => $email]);
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'is_active' => false,
                    'message' => 'Email not found in system'
                ]);
            }

            // Check if user is active
            // Note: NULL or 1 means active, only 0 means inactive
            $isActive = ($user->status !== 0 && $user->status !== '0');

            \Log::info('Email validation result', [
                'email' => $email,
                'exists' => true,
                'is_active' => $isActive,
                'user_id' => $user->id,
                'status' => $user->status,
                'status_type' => gettype($user->status)
            ]);

            return response()->json([
                'success' => true,
                'exists' => true,
                'is_active' => $isActive,
                'user_id' => $user->id,
                'message' => $isActive ? 'Email verified - User is active' : 'User account is inactive'
            ]);

        } catch (\Exception $e) {
            \Log::error('Email Validation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error validating email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add transaction manually
     */
    public function addTransactionManually(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'contact' => 'required|string|max:15',
                'method' => 'required|string',
                'transaction_id' => 'required|string',
                'currency_code' => 'required|string|in:INR,USD',
                'price_amount' => 'required|numeric|min:0',
                'paid_amount' => 'required|numeric|min:0',
                'plan_id' => 'required|integer',
                'usage_purpose' => 'required|in:personal,professional',
                'fromWallet' => 'nullable|boolean',
                'fromWhere' => 'nullable|string',
                'coins' => 'nullable|integer|min:0',
            ]);

            // Find user by email
            $user = UserData::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this email'
                ], 404);
            }

            // Check if user is active
            if ($user->status == 0 && $user->status !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive'
                ], 400);
            }

            // Get plan details
            $plan = Subscription::find($validated['plan_id']);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            // Check if transaction already exists
            $existingTransaction = TransactionLog::where('transaction_id', $validated['transaction_id'])->first();
            if ($existingTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID already exists'
                ], 400);
            }

            // Create transaction log (same as panel)
            $transactionLog = new TransactionLog();
            $transactionLog->plan_id = $validated['plan_id'];
            $transactionLog->user_id = $user->uid; // Use uid, not id
            $transactionLog->payment_method = $validated['method'];
            $transactionLog->from_where = $validated['fromWhere'] ?? 'Manual';
            $transactionLog->transaction_id = $validated['transaction_id'];
            $transactionLog->promo_code_id = 0;

            // Set default values for required fields
            $transactionLog->cancellation_reason = '';
            $transactionLog->url = '';
            $transactionLog->subscription_status = '';
            $transactionLog->followup_note = '';
            $transactionLog->type = 0; // Old plan type
            $transactionLog->payment_status = 1; // Success
            $transactionLog->emp_id = auth()->user()->id ?? 0;
            $transactionLog->by_sales_team = 1;
            $transactionLog->subscription_is_active = 1;
            $transactionLog->is_trial = 0;
            $transactionLog->is_e_mandate = 0;
            $transactionLog->yearly = 0;
            $transactionLog->email_template_count = 0;
            $transactionLog->whatsapp_template_count = 0;
            $transactionLog->followup_call = 0;

            // Set currency and price
            if (!strcasecmp($validated['currency_code'], "INR")) {
                $transactionLog->currency_code = "Rs";
                $transactionLog->price_amount = $plan->price;
            } else {
                $transactionLog->currency_code = "$";
                $transactionLog->price_amount = $plan->price_dollar;
            }

            $transactionLog->paid_amount = $validated['paid_amount'];
            $transactionLog->net_amount = $validated['paid_amount'];
            $transactionLog->coins = $validated['coins'] ?? 0;
            $transactionLog->isManual = 1;

            // Deactivate old subscriptions
            TransactionLog::where('user_id', $user->uid)->update(['status' => 0]);

            // Set validity and expiry
            $transactionLog->validity = $plan->validity;
            $expiryDate = now()->addDays($plan->validity);
            $transactionLog->expired_at = $expiryDate;
            $transactionLog->save();

            // Update user premium status
            $user->is_premium = "1";
            $user->save();

            // Update personal details with usage purpose
            PersonalDetails::updateOrCreate(
                ['uid' => $user->uid], // Use uid
                ['usage' => $validated['usage_purpose']]
            );

            \Log::info('Manual transaction added successfully', [
                'transaction_log_id' => $transactionLog->id,
                'user_id' => $user->id,
                'user_uid' => $user->uid,
                'email' => $validated['email'],
                'amount' => $validated['paid_amount'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'data' => [
                    'transaction_log_id' => $transactionLog->id,
                    'user_id' => $user->id,
                    'transaction_id' => $validated['transaction_id'],
                    'amount' => $validated['paid_amount'],
                    'plan_name' => $plan->package_name ?? 'Subscription',
                    'expiry_date' => $expiryDate->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Add Transaction Manually Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error adding transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create payment link
     */
    public function createPaymentLink(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'contact_no' => 'required|string|max:15',
                'payment_method' => 'required|in:razorpay,phonepe',
                'plan_id' => 'required|string',
                'subscription_type' => 'required|in:old_sub,new_sub',
                'amount' => 'required|numeric|min:1',
                'plan_type' => 'required|in:personal,professional',
                'usage_type' => 'nullable|in:personal,professional',
                'caricature' => 'nullable|integer|min:0|max:100',
            ]);

            // Validate email exists in user_data table and user is active
            $user = UserData::where('email', $validated['email'])->first();

            if (!$user) {
                \Log::warning('Payment link creation failed - Email not found', [
                    'email' => $validated['email']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Email not found in system. Please register first.'
                ], 400);
            }

            // Check if user is inactive (status = 0)
            // Note: NULL or 1 means active, only 0 means inactive
            // Use loose comparison to handle both string "0" and integer 0
            if ($user->status == 0 && $user->status !== null) {
                \Log::warning('Payment link creation failed - User inactive', [
                    'email' => $validated['email'],
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'status_type' => gettype($user->status)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive. Please activate the account first.'
                ], 400);
            }

            \Log::info('Email validated successfully for payment link', [
                'email' => $validated['email'],
                'user_id' => $user->id,
                'status' => $user->status
            ]);

            // Generate reference ID
            $referenceId = Sale::generateReferenceId();

            // Create sale record
            $sale = Sale::create([
                'sales_person_id' => auth()->id(),
                'user_name' => $validated['user_name'],
                'email' => $validated['email'],
                'contact_no' => $validated['contact_no'],
                'payment_method' => $validated['payment_method'],
                'plan_id' => $validated['plan_id'],
                'subscription_type' => $validated['subscription_type'],
                'amount' => $validated['amount'],
                'plan_type' => $validated['plan_type'],
                'caricature' => $validated['caricature'] ?? 0,
                'usage_type' => $validated['usage_type'] ?? $validated['plan_type'], // Default to plan_type if not provided
                'reference_id' => $referenceId,
                'status' => 'created',
            ]);

            // Update or create personal_details with usage type
            PersonalDetails::updateOrCreate(
                ['uid' => $user->uid],
                [
                    'user_name' => $validated['user_name'],
                    'usage' => $validated['plan_type'], // Store usage purpose in personal_details
                ]
            );

            \Log::info('Personal details updated with usage type', [
                'uid' => $user->uid,
                'usage' => $validated['plan_type']
            ]);

            // Create payment link via Razorpay or PhonePe
            if ($validated['payment_method'] === 'razorpay') {
                $paymentLink = $this->createRazorpayPaymentLink($sale);

                if ($paymentLink) {
                    $sale->update([
                        'payment_link_id' => $paymentLink['id'],
                        'payment_link_url' => $paymentLink['payment_link_url'],
                        'short_url' => $paymentLink['short_url'],
                    ]);
                }
            } elseif ($validated['payment_method'] === 'phonepe') {
                $paymentLink = $this->createPhonePePaymentLink($sale);

                if ($paymentLink) {
                    $sale->update([
                        'payment_link_id' => $paymentLink['id'],
                        'phonepe_order_id' => $paymentLink['phonepe_order_id'] ?? null,
                        'payment_link_url' => $paymentLink['payment_link_url'],
                        'short_url' => $paymentLink['short_url'],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment link created successfully',
                'data' => [
                    'reference_id' => $sale->reference_id,
                    'payment_link' => $sale->short_url ?? $sale->payment_link_url,
                    'amount' => $sale->amount,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Create Payment Link Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating payment link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique Crafty ID in format: CRAFT_xxxxx
     * Example: CRAFT_kdshzyr021
     */
    private function generateCraftyId()
    {
        do {
            // Generate random alphanumeric string (lowercase + numbers)
            $randomStr = strtolower(substr(md5(uniqid(mt_rand(), true)), 0, 10));
            $craftyId = 'CRAFT_' . $randomStr;
        } while (Order::where('crafty_id', $craftyId)->exists());

        return $craftyId;
    }

    /**
     * Generate unique Razorpay Order ID in format: RZP_xxxxx
     * Example: RZP_edvoyjcx5u
     */
    private function generateRazorpayOrderId()
    {
        do {
            // Generate random alphanumeric string (lowercase + numbers)
            $randomStr = strtolower(substr(md5(uniqid(mt_rand(), true)), 0, 10));
            $razorpayOrderId = 'RZP_' . $randomStr;
        } while (Order::where('razorpay_order_id', $razorpayOrderId)->exists());

        return $razorpayOrderId;
    }

    /**
     * Get payment credentials from payment_configurations table
     */
    private function getPaymentCredentials($gateway, $scope = 'NATIONAL')
    {
        $config = PaymentConfiguration::whereRaw('LOWER(gateway) = ?', [strtolower($gateway)])
            ->where('payment_scope', $scope)
            ->where('is_active', 1)
            ->first();

        if (!$config) {
            \Log::warning("Payment configuration not found", [
                'gateway' => $gateway,
                'scope' => $scope
            ]);
            return null;
        }
        return PaymentConfiguration::decryptCredentials($config->credentials);

        // return $config->credentials;
    }

    /**
     * Get base URL for redirects (handles both localhost and IP addresses)
     */
    private function getBaseUrl()
    {
        // Use Laravel's url() helper which automatically handles the correct base path
        return url('/');
    }

    /**
     * Create Razorpay payment link
     */
    private function createRazorpayPaymentLink($sale)
    {
        try {
            // Get credentials from payment_configurations table
            $credentials = $this->getPaymentCredentials('razorpay', 'NATIONAL');
            // decryptCredentials()

            \Log::info('Razorpay credentials retrieved', [
                'credentials' => $credentials,
                'type' => gettype($credentials)
            ]);

            if (!$credentials) {
                throw new \Exception('Razorpay credentials not configured in payment_configurations');
            }

            $razorpayKey = $credentials['key_id'] ?? null;
            $razorpaySecret = $credentials['secret_key'] ?? $credentials['key_secret'] ?? null;
            


            \Log::info('Razorpay key and secret', [
                'key' => $razorpayKey,
                'secret' => $razorpaySecret ? 'SET' : 'NOT SET'
            ]);

            if (!$razorpayKey || !$razorpaySecret) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $url = 'https://api.razorpay.com/v1/payment_links';

            $data = [
                'amount' => $sale->amount * 100, // Convert to paise
                'currency' => 'INR',
                'accept_partial' => false,
                'description' => 'Payment for ' . $sale->plan_type . ' plan',
                'customer' => [
                    'name' => $sale->user_name,
                    'email' => $sale->email,
                    'contact' => $sale->contact_no,
                ],
                'notify' => [
                    'sms' => true,
                    'email' => true,
                ],
                'reminder_enable' => true,
                'reference_id' => $sale->reference_id,
                'callback_url' => "https://www.craftyartapp.com",
                'callback_method' => 'get',
                // Prefill customer details on payment page
                'options' => [
                    'checkout' => [
                        'readonly' => [
                            'contact' => true,
                            'email' => true,
                            'name' => true,
                        ],
                        'prefill' => [
                            'contact' => $sale->contact_no,
                            'email' => $sale->email,
                            'name' => $sale->user_name,
                        ],
                    ],
                ],
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return [
                    'id' => $result['id'],
                    'payment_link_url' => $result['short_url'] ?? $result['url'] ?? null,
                    'short_url' => $result['short_url'] ?? null,
                ];
            } else {
                $errorResponse = json_decode($response, true);
                $errorMessage = 'Razorpay API Error';

                if (isset($errorResponse['error']['description'])) {
                    $errorMessage = $errorResponse['error']['description'];
                }

                \Log::error('Razorpay API Error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error_message' => $errorMessage
                ]);

                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            \Log::error('Razorpay Payment Link Creation Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create PhonePe payment link using Standard Payment Gateway API
     * Uses salt_key for checksum generation (no OAuth required)
     */
    /**
     * Create PhonePe payment link using OAuth authentication
     * Uses PhonePeTokenService for token management
     */
    private function createPhonePePaymentLink($sale)
    {
        try {
            // Get credentials from payment_configurations table
            $credentials = $this->getPaymentCredentials('phonepe_pg', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured in payment_configurations');
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $environment = $credentials['environment'] ?? 'sandbox';

            if (!$merchantId) {
                throw new \Exception('PhonePe merchant_id is required');
            }

            // Use OAuth token service
            $tokenService = app(\App\Services\PhonePeTokenService::class);
            $token = $tokenService->getAccessToken();

            if (!$token) {
                throw new \Exception('Failed to get PhonePe access token');
            }

            // Generate unique merchant order ID (must be alphanumeric, no special chars)
            $merchantOrderId = 'TX' . time() . rand(100000, 999999);

            // Get base URL for callbacks
            $baseUrl = $this->getBaseUrl();
            $redirectUrl = $baseUrl . '/payment-link/phonepe-callback?ref=' . $sale->reference_id;

            // Build payment payload matching working implementation
            $payload = [
                'merchantId' => $merchantId,
                'merchantOrderId' => $merchantOrderId,
                'merchantUserId' => $merchantId, // Use merchant ID as user ID
                'amount' => (int) ($sale->amount * 100), // Convert to paise, must be integer
                'paymentFlow' => [
                    'type' => 'PG_CHECKOUT',
                    'message' => 'Payment for subscription',
                    'merchantUrls' => [
                        'redirectUrl' => $redirectUrl
                    ]
                ]
                // No expireAfter field - let PhonePe use default
            ];

            // Use correct API URL based on environment
            $apiUrl = ($environment === 'production')
                ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

            \Log::info('PhonePe OAuth Payment Request', [
                'reference_id' => $sale->reference_id,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $sale->amount,
                'environment' => $environment,
                'api_url' => $apiUrl,
                'token_length' => strlen($token),
                'payload' => $payload
            ]);

            // Make API call with OAuth token (use O-Bearer prefix as per working implementation)
            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($apiUrl, $payload);

            $httpCode = $response->status();
            $responseData = $response->json();

            \Log::info('PhonePe OAuth API Response', [
                'http_code' => $httpCode,
                'response' => $responseData,
                'raw_body' => $response->body()
            ]);

            // Check for successful response
            if ($httpCode === 200 && isset($responseData['redirectUrl'])) {
                // Get order ID and redirect URL from response
                $phonePeOrderId = $responseData['orderId'] ?? $merchantOrderId;
                $paymentUrl = $responseData['redirectUrl'];

                \Log::info('PhonePe Payment Link Created Successfully', [
                    'merchant_order_id' => $merchantOrderId,
                    'phonepe_order_id' => $phonePeOrderId,
                    'redirect_url' => $paymentUrl
                ]);

                return [
                    'id' => $merchantOrderId,
                    'phonepe_order_id' => $phonePeOrderId,
                    'payment_link_url' => $paymentUrl,
                    'short_url' => $paymentUrl,
                ];
            } else {
                // Handle error response
                $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'PhonePe API error';
                $errorCode = $responseData['code'] ?? $responseData['errorCode'] ?? 'UNKNOWN';

                \Log::error('PhonePe API Error', [
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'full_response' => $responseData
                ]);

                throw new \Exception("PhonePe Error [{$errorCode}]: {$errorMessage}");
            }

        } catch (\Exception $e) {
            \Log::error('PhonePe Payment Link Creation Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Show PhonePe payment page (custom payment link page)
     */
    public function showPhonePePaymentPage($referenceId)
    {
        try {
            $sale = Sale::where('reference_id', $referenceId)->firstOrFail();

            // Check if already paid
            if ($sale->status === 'paid' && $sale->order_id) {
                return redirect()->to($this->getBaseUrl() . '/payment-success?ref=' . $referenceId);
            }

            return view('payment.phonepe-payment-page', compact('sale'));

        } catch (\Exception $e) {
            \Log::error('PhonePe Payment Page Error', [
                'reference_id' => $referenceId,
                'error' => $e->getMessage()
            ]);

            return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId . '&error=invalid_link');
        }
    }

    /**
     * Initiate PhonePe payment from custom payment page - Manual API Approach
     */
    public function initiatePhonePePayment(Request $request)
    {
        try {
            $referenceId = $request->get('reference_id');
            $sale = Sale::where('reference_id', $referenceId)->firstOrFail();

            // Check if already paid
            if ($sale->status === 'paid' && $sale->order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already completed',
                    'redirect_url' => $this->getBaseUrl() . '/payment-success?ref=' . $referenceId
                ]);
            }

            // Get PhonePe credentials from payment_configurations table
            $credentials = $this->getPaymentCredentials('phonepe_pg', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured in payment_configurations');
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $saltKey = $credentials['salt_key'] ?? null;
            $saltIndex = $credentials['salt_index'] ?? 1;
            $env = $credentials['environment'] ?? 'uat';

            if (!$merchantId || !$saltKey) {
                throw new \Exception('PhonePe credentials not configured');
            }

            // Decode salt_key if it's base64 encoded
            if (base64_encode(base64_decode($saltKey, true)) === $saltKey) {
                $saltKey = base64_decode($saltKey);
            }

            // Generate unique merchant transaction ID
            $merchantTransactionId = 'TXN_' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 14));

            // Build payment payload
            $payload = [
                'merchantId' => $merchantId,
                'merchantTransactionId' => $merchantTransactionId,
                'merchantUserId' => 'MUID_' . substr(md5($sale->email), 0, 10),
                'amount' => (int) ($sale->amount * 100), // Convert to paise
                'redirectUrl' => url('/payment-link/phonepe-callback?ref=' . $referenceId),
                'redirectMode' => 'REDIRECT',
                'callbackUrl' => url('/payment-link/phonepe-callback?ref=' . $referenceId),
                'mobileNumber' => $sale->contact_no,
                'paymentInstrument' => [
                    'type' => 'PAY_PAGE'
                ]
            ];

            // Encode payload to base64
            $base64Payload = base64_encode(json_encode($payload));

            // Generate X-VERIFY header
            $stringToHash = $base64Payload . '/pg/v1/pay' . $saltKey;
            $sha256Hash = hash('sha256', $stringToHash);
            $xVerifyHeader = $sha256Hash . '###' . $saltIndex;

            // Determine API URL based on environment
            $apiUrl = ($env === 'production')
                ? 'https://api.phonepe.com/apis/pg/v1/pay'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay';

            \Log::info('PhonePe Payment Request', [
                'reference_id' => $referenceId,
                'merchant_transaction_id' => $merchantTransactionId,
                'amount' => $sale->amount,
                'api_url' => $apiUrl,
                'payload' => $payload
            ]);

            // Make API call using cURL
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $base64Payload]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-VERIFY: ' . $xVerifyHeader,
                'accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            \Log::info('PhonePe API Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success']) {
                // Update sale with PhonePe transaction ID
                $sale->update([
                    'payment_link_id' => $merchantTransactionId,
                ]);

                // Get redirect URL from response
                $redirectUrl = $responseData['data']['instrumentResponse']['redirectInfo']['url'] ?? null;

                if (!$redirectUrl) {
                    throw new \Exception('No redirect URL in PhonePe response');
                }

                return response()->json([
                    'success' => true,
                    'redirect_url' => $redirectUrl,
                    'merchant_transaction_id' => $merchantTransactionId
                ]);
            } else {
                $errorMessage = $responseData['message'] ?? 'PhonePe API error';
                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            \Log::error('Initiate PhonePe Payment Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment link callback
     */
    public function paymentLinkCallback(Request $request)
    {
        try {
            // Get reference_id from multiple possible sources
            $referenceId = $request->get('reference_id')
                ?? $request->get('razorpay_payment_link_reference_id')
                ?? null;

            $paymentId = $request->get('razorpay_payment_id');
            $paymentLinkId = $request->get('razorpay_payment_link_id');
            $paymentLinkStatus = $request->get('razorpay_payment_link_status');

            \Log::info('Razorpay Payment Link Callback Received', [
                'reference_id' => $referenceId,
                'payment_id' => $paymentId,
                'payment_link_id' => $paymentLinkId,
                'status' => $paymentLinkStatus,
                'all_params' => $request->all()
            ]);

            // If no reference_id, try to find sale by payment_link_id
            if (!$referenceId && $paymentLinkId) {
                $sale = Sale::where('payment_link_id', $paymentLinkId)->first();
                if ($sale) {
                    $referenceId = $sale->reference_id;
                    \Log::info('Found reference_id from payment_link_id', [
                        'payment_link_id' => $paymentLinkId,
                        'reference_id' => $referenceId
                    ]);
                }
            }

            if (!$referenceId) {
                \Log::error('No reference_id found in Razorpay callback', [
                    'all_params' => $request->all()
                ]);
                return redirect()->to($this->getBaseUrl() . '/payment-failed?error=no_reference');
            }

            // Find sale
            $sale = Sale::where('reference_id', $referenceId)->first();

            if (!$sale) {
                \Log::error('Sale not found for reference_id', ['reference_id' => $referenceId]);
                return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId . '&error=sale_not_found');
            }

            // IMPORTANT: Verify this is a Razorpay payment
            if ($sale->payment_method !== 'razorpay') {
                \Log::warning('Razorpay callback received for non-Razorpay payment', [
                    'reference_id' => $referenceId,
                    'payment_method' => $sale->payment_method
                ]);
                return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId . '&error=invalid_payment_method');
            }

            if ($paymentLinkStatus === 'paid') {
                // Check if order already exists
                if ($sale->order_id) {
                    \Log::info('Order already exists for sale', ['sale_id' => $sale->id, 'order_id' => $sale->order_id]);
                    return redirect()->to($this->getBaseUrl() . '/payment-success?ref=' . $referenceId);
                }

                // Update sale status if not already paid
                if ($sale->status !== 'paid') {
                    $sale->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                // Create order and activate subscription
                $order = $this->createOrderFromSale($sale);

                if ($order) {
                    \Log::info('Order created successfully from Razorpay callback', [
                        'sale_id' => $sale->id,
                        'order_id' => $order->id,
                        'reference_id' => $referenceId
                    ]);
                    return redirect()->to($this->getBaseUrl() . '/payment-success?ref=' . $referenceId);
                }
            }

            \Log::warning('Razorpay payment callback failed', [
                'reference_id' => $referenceId,
                'status' => $paymentLinkStatus,
                'sale_found' => isset($sale)
            ]);

            return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId);

        } catch (\Exception $e) {
            \Log::error('Razorpay Payment Link Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . ($referenceId ?? 'unknown') . '&error=exception');
        }
    }

    /**
     * Handle PhonePe payment link callback
     */
    public function phonePePaymentLinkCallback(Request $request)
    {
        try {
            \Log::info('PhonePe Callback Received', [
                'all_params' => $request->all()
            ]);

            // Get reference_id from URL parameter
            $referenceId = $request->get('ref');

            if (!$referenceId) {
                \Log::error('No reference_id found in PhonePe callback');
                return redirect()->to($this->getBaseUrl() . '/payment-failed?error=no_reference');
            }

            // Find sale by reference_id
            $sale = Sale::where('reference_id', $referenceId)->first();

            if (!$sale) {
                \Log::error('Sale not found for reference_id', ['reference_id' => $referenceId]);
                return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId . '&error=sale_not_found');
            }

            // IMPORTANT: Verify this is a PhonePe payment
            if ($sale->payment_method !== 'phonepe') {
                \Log::warning('PhonePe callback received for non-PhonePe payment', [
                    'reference_id' => $referenceId,
                    'payment_method' => $sale->payment_method
                ]);
                return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId . '&error=invalid_payment_method');
            }

            // Check payment status with PhonePe API
            $paymentStatus = $this->checkPhonePePaymentStatus($sale->payment_link_id);

            if ($paymentStatus && $paymentStatus['success'] && $paymentStatus['state'] === 'COMPLETED') {
                // Check if order already exists
                if ($sale->order_id) {
                    \Log::info('Order already exists for sale', ['sale_id' => $sale->id, 'order_id' => $sale->order_id]);
                    return redirect()->to($this->getBaseUrl() . '/payment-success?ref=' . $referenceId);
                }

                // Update sale status
                if ($sale->status !== 'paid') {
                    $sale->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                // Create order and activate subscription
                $order = $this->createOrderFromSale($sale);

                if ($order) {
                    \Log::info('Order created successfully from PhonePe callback', [
                        'sale_id' => $sale->id,
                        'order_id' => $order->id,
                        'reference_id' => $referenceId
                    ]);
                    return redirect()->to($this->getBaseUrl() . '/payment-success?ref=' . $referenceId);
                }
            }

            \Log::warning('PhonePe payment callback failed', [
                'reference_id' => $referenceId,
                'payment_status' => $paymentStatus
            ]);

            return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . $referenceId);

        } catch (\Exception $e) {
            \Log::error('PhonePe Payment Link Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->to($this->getBaseUrl() . '/payment-failed?ref=' . ($referenceId ?? 'unknown') . '&error=exception');
        }
    }

    /**
     * Check PhonePe payment status using OAuth Checkout API
     * Uses OAuth token for authentication
     */
    private function checkPhonePePaymentStatus($merchantTransactionId)
    {
        try {
            // Get credentials from payment_configurations table
            $credentials = $this->getPaymentCredentials('phonepe_pg', 'NATIONAL');


            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured in payment_configurations');
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $env = $credentials['environment'] ?? 'uat';

            if (!$merchantId) {
                throw new \Exception('PhonePe merchant_id not configured');
            }

            // Use OAuth token service
            $tokenService = app(\App\Services\PhonePeTokenService::class);
            $token = $tokenService->getAccessToken();

            if (!$token) {
                throw new \Exception('Failed to get PhonePe access token');
            }

            // Determine API URL based on environment (use OAuth checkout status endpoint)
            $statusUrl = ($env === 'production')
                ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$merchantTransactionId}/status"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$merchantTransactionId}/status";

            \Log::info('PhonePe Status Check Request', [
                'merchant_id' => $merchantId,
                'transaction_id' => $merchantTransactionId,
                'status_url' => $statusUrl
            ]);

            // Make API call with OAuth token
            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get($statusUrl);

            if (!$response->successful()) {
                \Log::error('PhonePe Status Check Failed', [
                    'http_code' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            $statusData = $response->json();

            \Log::info('PhonePe Status Check Response', [
                'response' => $statusData
            ]);

            // OAuth checkout API returns different structure
            $orderId = $statusData['orderId'] ?? null;
            $state = $statusData['state'] ?? 'UNKNOWN';
            $amount = $statusData['amount'] ?? null;
            $currency = $statusData['currency'] ?? 'INR';

            return [
                'success' => ($state === 'COMPLETED'),
                'state' => $state,
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_details' => $statusData['paymentDetails'] ?? [],
            ];

        } catch (\Exception $e) {
            \Log::error('PhonePe Status Check Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Create order from sale and activate subscription
     */
    private function createOrderFromSale($sale)
    {
        try {
            // Generate crafty_id in format: CRAFT_xxxxx (e.g., CRAFT_kdshzyr021)
            $craftyId = $this->generateCraftyId();

            // Generate razorpay_order_id in format: RZP_xxxxx (e.g., RZP_edvoyjcx5u)
            $razorpayOrderId = $this->generateRazorpayOrderId();

            // Create or find user based on email/contact
            $user = UserData::where('email', $sale->email)
                ->orWhere('number', $sale->contact_no)
                ->first();

            if (!$user) {
                // Generate unique UID
                $uid = 'user_' . uniqid() . '_' . time();

                // Create new user using DB insert (UserData doesn't have fillable)
                // Must use 'mysql' connection explicitly
                // Table name is 'user_data' (singular), not 'user_datas'
                $userId = \DB::connection('mysql')->table('user_data')->insertGetId([
                    'uid' => $uid,
                    'name' => $sale->user_name,
                    'email' => $sale->email,
                    'number' => $sale->contact_no,
                    'refer_id' => strtoupper(substr(md5($uid), 0, 8)),
                    'is_premium' => 0,
                    'can_update' => 1,
                    'web_update' => 0,
                    'cheap_rate' => 0,
                    'status' => 1,
                    'creator' => 0,
                    'profile_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Fetch the created user
                $user = UserData::find($userId);
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->uid,
                'emp_id' => $sale->sales_person_id ?? 0,
                'type' => $sale->subscription_type,
                'plan_id' => $sale->plan_id,
                'amount' => $sale->amount,
                'paid' => $sale->amount,
                'currency' => 'INR',
                'status' => 'success',
                'crafty_id' => $craftyId,
                'razorpay_payment_id' => $sale->payment_link_id,
                'razorpay_order_id' => $razorpayOrderId,
                'contact_no' => $sale->contact_no,
                'followup_call' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
            ]);

            // Update sale with order_id
            $sale->update([
                'order_id' => $order->id,
            ]);

            // Activate subscription based on plan_id and subscription_type
            if ($sale->plan_id && $sale->subscription_type) {
                $this->activateSubscription($user, $sale);
            }

            // Create or update personal details with usage_type
            if ($sale->usage_type) {
                PersonalDetails::updateOrCreate(
                    ['uid' => $user->uid],
                    [
                        'user_name' => $sale->user_name,
                        'usage' => $sale->usage_type,
                    ]
                );
            }

            \Log::info('Order created from payment link', [
                'sale_id' => $sale->id,
                'order_id' => $order->id,
                'user_id' => $user->uid,
                'crafty_id' => $craftyId,
                'reference_id' => $sale->reference_id,
                'usage_type' => $sale->usage_type
            ]);

            // Broadcast order creation via WebSocket for real-time updates
            // Only broadcast if status is pending or failed (for order list display)
            if (in_array($order->status, ['pending', 'failed'])) {
                WebSocketBroadcastController::broadcastOrderCreatedDirect($order);
            }

            return $order;

        } catch (\Exception $e) {
            \Log::error('Create Order From Sale Error', [
                'sale_id' => $sale->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Activate subscription for user
     */
    private function activateSubscription($user, $sale)
    {
        try {
            // Determine plan type and find the plan
            $planType = 0; // Default to old plan
            $plan = null;

            if ($sale->subscription_type === 'new_sub') {
                // New subscription plan
                $plan = SubPlan::where('string_id', $sale->plan_id)
                    ->orWhere('id', $sale->plan_id)
                    ->first();
                $planType = 1;
            } elseif ($sale->subscription_type === 'old_sub') {
                // Old subscription plan
                $plan = Subscription::find($sale->plan_id);
                $planType = 0;
            } elseif ($sale->subscription_type === 'offer') {
                // Offer plan
                $plan = OfferPackage::where('string_id', $sale->plan_id)
                    ->orWhere('id', $sale->plan_id)
                    ->first();
                $planType = 2;
            }

            if (!$plan) {
                \Log::warning('Subscription plan not found', [
                    'plan_id' => $sale->plan_id,
                    'subscription_type' => $sale->subscription_type
                ]);
                return;
            }

            // Calculate expiry date
            $validity = $plan->days ?? $plan->validity ?? 365;
            $expiryDate = now()->addDays($validity);

            // Get plan limit (for new plans)
            $planLimit = null;
            if ($planType === 1 && isset($plan->plan_limit)) {
                $planLimit = is_string($plan->plan_limit) ? $plan->plan_limit : json_encode($plan->plan_limit);
            }

            // Create or update transaction log
            $transactionLog = TransactionLog::updateOrCreate(
                ['user_id' => $user->uid],
                [
                    'plan_id' => $sale->plan_id,
                    'emp_id' => $sale->sales_person_id ?? 0,
                    'contact_no' => $sale->contact_no,
                    'transaction_id' => $sale->reference_id,
                    'payment_id' => $sale->payment_link_id,
                    'currency_code' => 'INR',
                    'price_amount' => $sale->amount,
                    'paid_amount' => $sale->amount,
                    'net_amount' => $sale->amount,
                    'payment_method' => $sale->payment_method,
                    'from_where' => 'Payment Link',
                    'validity' => $validity,
                    'plan_limit' => $planLimit,
                    'type' => $planType,
                    'payment_status' => 1, // 1 = paid
                    'status' => 1, // 1 = active
                    'expired_at' => $expiryDate,
                    // Required fields with defaults
                    'cancellation_reason' => '',
                    'url' => '',
                    'subscription_status' => 'active',
                    'followup_note' => '',
                    'by_sales_team' => 0,
                    'subscription_is_active' => 1,
                    'is_trial' => 0,
                    'is_e_mandate' => 0,
                    'yearly' => $planType === 12 ? 1 : 0,
                    'isManual' => 0,
                    'email_template_count' => 0,
                    'whatsapp_template_count' => 0,
                    'followup_call' => 0,
                ]
            );

            // Update user premium status
            \DB::connection('mysql')->table('user_data')
                ->where('uid', $user->uid)
                ->update([
                    'is_premium' => 1,
                    'validity' => $expiryDate->format('Y-m-d H:i:s'),
                    'total_validity' => $validity,
                    'subscription' => $sale->plan_id,
                    'updated_at' => now(),
                ]);

            // Create ManageSubscription entry
            $manageSubscription = ManageSubscription::create([
                'user_id' => $user->uid,
                'is_base_price' => $plan->is_base_price ?? 0,
                'package_name' => $plan->package_name ?? $plan->name ?? 'Subscription',
                'desc' => $plan->desc ?? $plan->description ?? '',
                'validity' => $validity,
                'actual_price' => $plan->actual_price ?? $sale->amount,
                'actual_price_dollar' => $plan->actual_price_dollar ?? null,
                'price' => $sale->amount,
                'price_dollar' => $plan->price_dollar ?? null,
                'months' => $plan->months ?? ($validity >= 365 ? 12 : ($validity >= 30 ? 1 : 0)),
                'has_offer' => $plan->has_offer ?? 0,
                'sequence_number' => $plan->sequence_number ?? 0,
                'status' => 1, // Active
            ]);

            \Log::info('Subscription activated', [
                'user_id' => $user->uid,
                'plan_id' => $sale->plan_id,
                'plan_type' => $planType,
                'validity' => $validity,
                'expired_at' => $expiryDate
            ]);

            // Setup PhonePe AutoPay for recurring subscriptions
            $this->setupAutoPayForSubscription($user, $sale, $manageSubscription, $transactionLog);

        } catch (\Exception $e) {
            \Log::error('Activate Subscription Error', [
                'user_id' => $user->uid ?? null,
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Setup PhonePe AutoPay for recurring subscription
     */
    private function setupAutoPayForSubscription($user, $sale, $manageSubscription, $transactionLog)
    {
        try {
            // Only setup AutoPay for subscriptions (not one-time purchases)
            if (!in_array($sale->subscription_type, ['new_sub', 'old_sub'])) {
                \Log::info('Skipping AutoPay setup - not a subscription', [
                    'subscription_type' => $sale->subscription_type
                ]);
                return;
            }

            // Create purchase history record
            $purchaseHistory = PurchaseHistory::create([
                'user_id' => $user->uid,
                'order_id' => $sale->order_id,
                'subscription_id' => $manageSubscription->id,
                'transaction_log_id' => $transactionLog->id,
                'plan_id' => $sale->plan_id,
                'amount' => $sale->amount,
                'payment_method' => $sale->payment_method,
                'payment_id' => $sale->payment_link_id,
                'reference_id' => $sale->reference_id,
                'upi_id' => null, // Will be collected from user
                'mobile' => $sale->contact_no,
                'email' => $sale->email,
                'status' => 'completed',
                'is_autopay_enabled' => false,
                'autopay_status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Initialize PhonePe AutoPay Service
            $autoPayService = new PhonePeAutoPayService();

            // Setup AutoPay subscription
            $result = $autoPayService->setupSubscription($purchaseHistory, $manageSubscription);

            if ($result['success']) {
                \Log::info('PhonePe AutoPay setup successful', [
                    'user_id' => $user->uid,
                    'purchase_id' => $purchaseHistory->id,
                    'subscription_id' => $manageSubscription->id,
                    'phonepe_order_id' => $result['phonepe_response']['orderId'] ?? null
                ]);

                // Update purchase history with AutoPay enabled
                $purchaseHistory->update([
                    'is_autopay_enabled' => true,
                    'autopay_status' => 'PENDING', // Will become ACTIVE after user approves mandate
                    'next_autopay_date' => now()->addMonths($manageSubscription->months ?? 1)
                ]);

            } else {
                \Log::error('PhonePe AutoPay setup failed', [
                    'user_id' => $user->uid,
                    'purchase_id' => $purchaseHistory->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('AutoPay Setup Exception', [
                'user_id' => $user->uid ?? null,
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - AutoPay setup failure shouldn't block subscription activation
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess(Request $request)
    {
        \Log::info('Payment Success Page Accessed', [
            'ref' => $request->get('ref'),
            'all_params' => $request->all()
        ]);

        $referenceId = $request->get('ref');
        $sale = null;

        if ($referenceId) {
            $sale = Sale::where('reference_id', $referenceId)->first();

            \Log::info('Sale found for payment success', [
                'sale_id' => $sale->id ?? null,
                'payment_method' => $sale->payment_method ?? null,
                'status' => $sale->status ?? null,
                'order_id' => $sale->order_id ?? null
            ]);

            // If sale exists and payment is successful, create order if not already created
            if ($sale && $sale->payment_method === 'phonepe') {
                \Log::info('Processing PhonePe payment success', [
                    'sale_id' => $sale->id,
                    'payment_link_id' => $sale->payment_link_id
                ]);

                try {
                    // Check payment status with PhonePe
                    $paymentStatus = $this->checkPhonePePaymentStatus($sale->payment_link_id);

                    \Log::info('PhonePe payment status checked', [
                        'sale_id' => $sale->id,
                        'payment_status' => $paymentStatus
                    ]);

                    if ($paymentStatus && $paymentStatus['success'] && $paymentStatus['state'] === 'COMPLETED') {
                        // Update sale status if not already paid
                        if ($sale->status !== 'paid') {
                            $sale->update([
                                'status' => 'paid',
                                'paid_at' => now(),
                            ]);

                            \Log::info('Sale status updated to paid', ['sale_id' => $sale->id]);
                        }

                        // Create order if not already created
                        if (!$sale->order_id) {
                            \Log::info('Creating order from payment success page', ['sale_id' => $sale->id]);

                            $order = $this->createOrderFromSale($sale);

                            if ($order) {
                                \Log::info('Order created from payment success page', [
                                    'sale_id' => $sale->id,
                                    'order_id' => $order->id,
                                    'reference_id' => $referenceId
                                ]);
                            } else {
                                \Log::error('Failed to create order from payment success page', [
                                    'sale_id' => $sale->id
                                ]);
                            }
                        } else {
                            \Log::info('Order already exists for sale', [
                                'sale_id' => $sale->id,
                                'order_id' => $sale->order_id
                            ]);
                        }
                    } else {
                        \Log::warning('PhonePe payment not completed', [
                            'sale_id' => $sale->id,
                            'payment_status' => $paymentStatus
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing payment success', [
                        'reference_id' => $referenceId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } else {
            \Log::warning('No reference ID provided to payment success page');
        }

        return view('payment.success', compact('sale', 'referenceId'));
    }

    /**
     * Payment failed page
     */
    public function paymentFailed(Request $request)
    {
        $referenceId = $request->get('ref');
        $sale = null;

        if ($referenceId) {
            $sale = Sale::where('reference_id', $referenceId)->first();
        }

        return view('payment.failed', compact('sale', 'referenceId'));
    }

    /**
     * Check PhonePe payment status (for testing/admin)
     */
    public function checkPhonePeStatusApi($merchantOrderId)
    {
        try {
            $status = $this->checkPhonePePaymentStatus($merchantOrderId);

            if ($status) {
                return response()->json([
                    'success' => true,
                    'data' => $status
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check payment status'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Razorpay payment link status (for testing/admin)
     */
    public function checkRazorpayStatusApi($paymentLinkId)
    {
        try {
            $razorpayKey = env('RAZORPAY_KEY');
            $razorpaySecret = env('RAZORPAY_SECRET');

            if (!$razorpayKey || !$razorpaySecret) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $url = 'https://api.razorpay.com/v1/payment_links/' . $paymentLinkId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch payment link status',
                    'http_code' => $httpCode
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

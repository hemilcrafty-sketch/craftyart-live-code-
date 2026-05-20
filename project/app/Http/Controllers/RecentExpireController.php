<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Utils\RoleManager;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentExpireController extends AppBaseController
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

    public function index(Request $request): Factory|View|Application
    {
        $activeUserIds = TransactionLog::where('expired_at', '>', now())
            ->pluck('user_id')
            ->toArray();

        $query = TransactionLog::with(['userData', 'subscription', 'subPlan', 'offer'])
            ->where('expired_at', '<', now())
            ->whereNotIn('user_id', $activeUserIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('transaction_logs')
                    ->groupBy('user_id');
            });


        // changes Distribute lead to Every Sales User
        $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
        if ($isSalesEmployee) {
            $salesEmployees = User::whereUserType(UserRole::SALES->id())->get();
            $salesEmployeeIds = $salesEmployees->pluck('id')->toArray();
            if (!empty($salesEmployeeIds)) {
                $userId = auth()->user()->id;
                $employeeIndex = array_search($userId, $salesEmployeeIds);
                if ($employeeIndex !== false) {
                    $totalSalesEmployees = count($salesEmployeeIds);

                    $query->where(function ($q) use ($userId, $employeeIndex, $totalSalesEmployees) {
                        $q->where('emp_id', $userId)
                            ->orWhere(function ($subQuery) use ($employeeIndex, $totalSalesEmployees) {
                                $subQuery->where('emp_id', 0)
                                    ->whereRaw("(id % ?) = ?", [$totalSalesEmployees, $employeeIndex]);
                            });
                    });
                }
            }
        }

        // Filters
        $filterType = $request->get('filter_type', 'all');
        $amountSort = $request->get('amount_sort');
        $whatsappFilter = $request->get('whatsapp_filter');
        $emailFilter = $request->get('email_filter');
        $followupFilter = $request->get('followup_filter');
        $search = trim($request->get('search'));
        $followupLabelFilter = $request->get('followup_label_filter'); // New filter
        $usageTypeFilter = $request->get('usage_type_filter'); // Usage Type filter


        if (!empty($followupLabelFilter) && array_key_exists($followupLabelFilter, self::FOLLOWUP_LABELS)) {
            $query->where('transaction_logs.followup_label', $followupLabelFilter);
        }

        // Usage Type filter - Join with personal_details table to filter by usage
        if (!empty($usageTypeFilter) && in_array($usageTypeFilter, ['personal', 'professional'])) {
            $query->whereHas('userData', function ($q) use ($usageTypeFilter) {
                $q->whereHas('personalDetails', function ($pd) use ($usageTypeFilter) {
                    $pd->where('usage', $usageTypeFilter);
                });
            });
        }

        // WhatsApp filter
        if ($whatsappFilter === 'sent') {
            $query->where('transaction_logs.whatsapp_template_count', '>', 0);
        } elseif ($whatsappFilter === 'not_sent') {
            $query->where('transaction_logs.whatsapp_template_count', '<=', 0);
        }

        // Email filter
        if ($emailFilter === 'sent') {
            $query->where('transaction_logs.email_template_count', '>', 0);
        } elseif ($emailFilter === 'not_sent') {
            $query->where('transaction_logs.email_template_count', '<=', 0);
        }

        // Follow-up filter
        if ($followupFilter === 'called') {
            $query->where('transaction_logs.followup_call', 1);
        } elseif ($followupFilter === 'not_called') {
            $query->where('transaction_logs.followup_call', 0);
        }

        // 🔍 Global search across ALL records (not just current page)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $like = "%{$search}%";

                // Search in transaction_logs fields
                $q->where('transaction_logs.id', 'LIKE', $like)
                    ->orWhere('transaction_logs.paid_amount', 'LIKE', $like)
                    ->orWhere('transaction_logs.expired_at', 'LIKE', $like)
                    ->orWhere('transaction_logs.followup_note', 'LIKE', $like)
                    ->orWhere('transaction_logs.followup_label', 'LIKE', "%{$like}%")
                    ->orWhereRaw("CASE WHEN transaction_logs.followup_call = 1 THEN 'Called' ELSE 'Not Called' END LIKE ?", [$like])
                    ->orWhereHas('userData', function ($userQuery) use ($like) {
                        $userQuery->where('user_data.name', 'LIKE', $like)
                            ->orWhere('user_data.email', 'LIKE', $like)
                            ->orWhere('user_data.contact_no', 'LIKE', $like);
                    })
                    ->orWhereHas('subscription', function ($subQuery) use ($like) {
                        $subQuery->where('subscriptions.package_name', 'LIKE', $like);
                    });
            });
        }

        if (in_array($amountSort, ['asc', 'desc'])) {
            $query->orderBy('transaction_logs.paid_amount', $amountSort);
        } else {
            $query->orderBy('transaction_logs.expired_at', 'desc');
        }

        $recentExpires = $query->paginate(15)->appends($request->except('page'));

        $datas['packageArray'] = Subscription::all();

        return view('recent_expire.index', compact(
            'recentExpires',
            'filterType',
            'amountSort',
            'whatsappFilter',
            'emailFilter',
            'followupLabelFilter',
            'followupFilter',
            'usageTypeFilter',
            'datas'
        ))->with('followupLabels', self::FOLLOWUP_LABELS);
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $recentExpire = TransactionLog::findOrFail($request->id);

        if ($request->has('followup_call') && $request->followup_call == 0) {
            $recentExpire->followup_call = 0;
            $recentExpire->followup_note = ''; // Set to empty string instead of null
            $recentExpire->followup_label = ''; // Set to empty string instead of null
        } else {
            $recentExpire->followup_call = 1;
            $recentExpire->followup_note = $request->followup_note ?? '';
            $recentExpire->followup_label = $request->followup_label ?? ''; // Save label with fallback
        }
        $recentExpire->emp_id = auth()->user()->id;
        $recentExpire->save();

        // Broadcast followup change via WebSocket for real-time updates
        WebSocketBroadcastController::broadcastTransactionFollowUpChanged($recentExpire);

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }
}
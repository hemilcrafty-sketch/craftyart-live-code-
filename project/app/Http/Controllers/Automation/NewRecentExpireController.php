<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\RoleManager;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Utils\UserRole;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Models\Subscription;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewRecentExpireController extends Controller
{
    const FOLLOWUP_LABELS = [
        'personal_use_only' => 'Personal Use Only',
        'professional' => 'Professional',
        'call_not_receive' => 'Call Not Receive',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
        'switched_off' => 'Switched Off',
        'not_reachable' => 'Not Reachable',
        'call_cut' => 'Call Cut',
        'active_plan' => 'Active Plan',
    ];

    public function index(Request $request): Factory|View|Application
    {
        $automationDb = \Illuminate\Support\Facades\DB::connection('crafty_automation_mysql')->getDatabaseName();

        $subscriptionTypes = ['old_sub', 'new_sub', 'offer_sub'];

        $query = \App\Models\Revenue\MasterPurchaseHistory::with(['userData', 'subscription', 'subPlan', 'offer', 'transactionLog'])
            ->leftJoin(\Illuminate\Support\Facades\DB::raw("`{$automationDb}`.`unified_support` as `us`"), 'purchase_history.id', '=', 'us.purchase_history_id')
            ->select('purchase_history.*',
                'us.expire_followup_call',
                'us.expire_followup_note',
                'us.expire_followup_label',
                'us.emp_id as support_emp_id'
            )
            ->whereIn('purchase_history.product_type', $subscriptionTypes)
            ->where('purchase_history.expired_at', '<', now())
            ->whereNotIn('purchase_history.user_id', function ($q) {
                $q->select('user_id')
                    ->from('purchase_history')
                    ->where('expired_at', '>', now());
            })
            ->whereIn('purchase_history.id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('purchase_history')
                    ->groupBy('user_id');
            });

        // Distribution logic
        if (RoleManager::isSalesEmployee(auth()->user()->user_type)) {
            $userId = auth()->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->whereNull('purchase_history.emp_id')
                    ->orWhere('purchase_history.emp_id', 0)
                    ->orWhere('purchase_history.emp_id', $userId);
            });
        }

        // Filters
        $filterType = $request->get('filter_type', 'all');
        $amountSort = $request->get('amount_sort', 'none');
        $whatsappFilter = $request->get('whatsapp_filter', 'all');
        $emailFilter = $request->get('email_filter', 'all');
        $followupFilter = $request->get('followup_filter', 'all');
        $followupLabelFilter = $request->get('followup_label_filter', 'all');
        $usageTypeFilter = $request->get('usage_type_filter', 'all');

        if ($whatsappFilter == 'sent') {
            $query->where('purchase_history.wp_sent', '>', 0);
        } elseif ($whatsappFilter == 'not_sent') {
            $query->where('purchase_history.wp_sent', 0);
        }

        if ($emailFilter == 'sent') {
            $query->where('purchase_history.email_sent', '>', 0);
        } elseif ($emailFilter == 'not_sent') {
            $query->where('purchase_history.email_sent', 0);
        }

        if ($followupFilter == 'called') {
            $query->where('us.expire_followup_call', 1);
        } elseif ($followupFilter == 'not_called') {
            $query->where(function ($q) {
                $q->whereNull('us.expire_followup_call')
                    ->orWhere('us.expire_followup_call', 0);
            });
        }

        if ($followupLabelFilter != 'all') {
            $query->where('us.expire_followup_label', $followupLabelFilter);
        }

        if ($usageTypeFilter != 'all') {
            $query->whereHas('userData', function ($q) use ($usageTypeFilter) {
                $q->whereHas('personalDetails', function ($pd) use ($usageTypeFilter) {
                    $pd->where('usage', $usageTypeFilter);
                });
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_history.contact_no', 'like', "%{$search}%")
                    ->orWhereHas('userData', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($amountSort, ['asc', 'desc'])) {
            $query->orderBy('purchase_history.paid_amount', $amountSort);
        } else {
            $query->orderBy('purchase_history.expired_at', 'desc');
        }

        $recentExpires = $query->paginate(15)->appends($request->except('page'));

        $datas['packageArray'] = Subscription::all();
        $followupLabels = self::FOLLOWUP_LABELS;

        return view('recent_expire.new_index', compact(
            'recentExpires', 'datas', 'followupLabels',
            'filterType', 'amountSort', 'whatsappFilter', 'emailFilter',
            'followupFilter', 'followupLabelFilter', 'usageTypeFilter'
        ));
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $purchase = \App\Models\Revenue\MasterPurchaseHistory::findOrFail($request->id);

        $empName = 'N/A';
        $followupCall = 0;
        $followupNote = null;
        $followupLabel = null;
        $empId = 0;

        if ($request->has('followup_call') && (int) $request->followup_call === 0) {
            // Reset logic
        } else {
            $followupCall = 1;
            $followupNote = $request->followup_note ?? '';
            $followupLabel = $request->followup_label ?? '';
            $empId = auth()->user()->id;
            $empName = auth()->user()->name ?? 'Admin';
        }

        // Sync UnifiedSupport (New Logic)
        $support = \App\Models\Automation\UnifiedSupport::updateOrCreate(
            ['purchase_history_id' => $purchase->id],
            ['user_id' => $purchase->user_id]
        );
        $support->expire_followup_call = $followupCall;
        $support->expire_followup_note = $followupNote;
        $support->expire_followup_label = $followupLabel;
        $support->emp_id = $empId;
        $support->save();

        WebSocketBroadcastController::broadcastTransactionFollowUpChanged($purchase);

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully',
            'emp_name' => $empName
        ]);
    }
}

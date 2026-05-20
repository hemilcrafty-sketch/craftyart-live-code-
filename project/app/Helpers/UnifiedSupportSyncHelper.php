<?php

namespace App\Helpers;

use App\Models\Automation\UnifiedSupport;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Revenue\MasterPurchaseHistory;
use Illuminate\Support\Facades\DB;

class UnifiedSupportSyncHelper
{
    public static function syncAllData()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Sync WP Feedback Requests
        |--------------------------------------------------------------------------
        */

        WpFeedbackRequest::where('is_unified_synced', 0)
            ->where(function ($q) {
                $q->where('followup_call', 1)
                    ->orWhere(function ($q) {
                        $q->whereNotNull('followup_note')
                            ->where('followup_note', '!=', '');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('followup_label')
                            ->where('followup_label', '!=', '');
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($requests) {

                // Optimized: Fetch all existing records for this chunk at once
                $purchaseIds = $requests->pluck('purchase_id')->filter()->toArray();
                $existingSupports = UnifiedSupport::whereIn('purchase_history_id', $purchaseIds)
                    ->get()
                    ->keyBy('purchase_history_id');

                foreach ($requests as $request) {

                    if (empty($request->purchase_id)) {
                        continue;
                    }

                    $data = [
                        'purchase_history_id' => $request->purchase_id,
                        'user_id' => $request->user_id,
                        'emp_id' => $request->emp_id ?? 0,
                        'event_date' => $request->created_at,
                        'source_type' => 'wp_feedback_request',
                    ];

                    if ($request->followup_call !== null) {
                        $data['wp_feedback_followup_call'] = $request->followup_call;
                    }

                    if (!empty($request->followup_note)) {
                        $data['wp_feedback_followup_note'] = $request->followup_note;
                    }

                    if (!empty($request->followup_label)) {
                        $data['wp_feedback_followup_label'] = $request->followup_label;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Check Existing Record (Optimized)
                    |--------------------------------------------------------------------------
                    */

                    $existing = $existingSupports->get($request->purchase_id);

                    if ($existing) {
                        $existing->update($data);
                    } else {
                        $data['created_at'] = $request->created_at;
                        $data['updated_at'] = $request->updated_at;
                        UnifiedSupport::create($data);
                    }
                }

                // Mark as synced
                WpFeedbackRequest::whereIn('id', $requests->pluck('id'))->update(['is_unified_synced' => 1]);
            });

        /*
        |--------------------------------------------------------------------------
        | 2. Sync Transaction Logs via Purchase History
        |--------------------------------------------------------------------------
        */

        MasterPurchaseHistory::where('is_unified_synced', 0)
            ->whereHas('transactionLog', function ($q) {
                $q->from('crafty_db.transaction_logs')
                    ->where('followup_call', 1)
                    ->orWhere(function ($q) {
                        $q->whereNotNull('followup_note')
                            ->where('followup_note', '!=', '');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('followup_label')
                            ->where('followup_label', '!=', '');
                    });
            })
            ->with('transactionLog')
            ->orderBy('id')
            ->chunkById(100, function ($purchaseHistories) {

                // Optimized: Fetch all existing records for this chunk at once
                $ids = $purchaseHistories->pluck('id')->toArray();
                $existingSupports = UnifiedSupport::whereIn('purchase_history_id', $ids)
                    ->get()
                    ->keyBy('purchase_history_id');

                foreach ($purchaseHistories as $ph) {

                    $log = $ph->transactionLog;

                    if (!$log) {
                        continue;
                    }

                    $data = [
                        'purchase_history_id' => $ph->id,
                        'user_id' => $ph->user_id,
                        'emp_id' => $ph->emp_id ?? 0,
                        'event_date' => $ph->created_at,
                        'source_type' => 'transaction_log',
                    ];

                    if ($log->followup_call !== null) {
                        $data['expire_followup_call'] = $log->followup_call;
                    }

                    if (!empty($log->followup_note)) {
                        $data['expire_followup_note'] = $log->followup_note;
                    }

                    if (!empty($log->followup_label)) {
                        $data['expire_followup_label'] = $log->followup_label;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Record Check (Optimized)
                    |--------------------------------------------------------------------------
                    */

                    $existing = $existingSupports->get($ph->id);

                    if ($existing) {
                        $existing->update($data);
                    } else {
                        $data['created_at'] = $ph->created_at;
                        $data['updated_at'] = $ph->updated_at;
                        UnifiedSupport::create($data);
                    }
                }

                // Bulk update synced status for this chunk (Much faster than updating in loop)
                MasterPurchaseHistory::whereIn('id', $ids)->update(['is_unified_synced' => 1]);
            });
    }
}
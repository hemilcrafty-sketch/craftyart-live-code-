<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Vendor\RevenueHistory;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * Job: Update revenue_history status based on purchase payment status.
     * Gets revenue_history records created before 30 days and updates their status
     * based on payment_status from MasterPurchaseHistory.
     */
    public function processRevenueHistoryJob(): void
    {
        try {
            $thirtyDaysAgo = now()->subDays(30);

            $revenueHistories = RevenueHistory::where('created_at', '<', $thirtyDaysAgo)
                ->whereIn('status', ['pending', null, ''])
                ->where('type', '!=', 'withdraw')
                ->get();

            if ($revenueHistories->isEmpty()) {
                Log::info('Revenue history job: No records to process');
                return;
            }

            $processedCount = 0;
            $updatedCount = 0;
            $failedCount = 0;

            foreach ($revenueHistories as $revenueHistory) {
                try {
                    $purchaseHistory = MasterPurchaseHistory::find($revenueHistory->purchase_id);

                    if (!$purchaseHistory) {
                        Log::warning('Revenue history job: Purchase history not found', [
                            'revenue_history_id' => $revenueHistory->id,
                            'purchase_id' => $revenueHistory->purchase_id
                        ]);
                        $failedCount++;
                        continue;
                    }

                    $newStatus = $purchaseHistory->payment_status === 'paid' ? 'active' : 'failed';

                    $revenueHistory->update(['status' => $newStatus]);

                    $updatedCount++;
                    $processedCount++;

                    Log::info('Revenue history status updated', [
                        'revenue_history_id' => $revenueHistory->id,
                        'purchase_id' => $revenueHistory->purchase_id,
                        'payment_status' => $purchaseHistory->payment_status,
                        'new_status' => $newStatus
                    ]);

                } catch (\Exception $e) {
                    $failedCount++;
                    $processedCount++;

                    Log::error('Error processing revenue_history', [
                        'revenue_history_id' => $revenueHistory->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Revenue history job completed', [
                'total_records' => $revenueHistories->count(),
                'processed_count' => $processedCount,
                'updated_count' => $updatedCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue history job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

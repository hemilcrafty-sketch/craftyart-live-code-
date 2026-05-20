<?php

namespace App\Services;

use App\Models\Creator\Designer\DesignSubmission;
use App\Models\UserData;
use App\Models\Vendor\VendorAccount;
use App\Models\Vendor\RevenueHistory;
use App\Models\Vendor\WalletSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service to handle freelancer designer earnings when templates are purchased
 */
class FreelancerEarningsService
{
    /**
     * Process freelancer earnings when a template is purchased
     *
     * @param int $purchaseId - MasterPurchaseHistory ID
     * @param string $productId - Template/Design ID
     * @param string $productType - Product type (template, video, etc.)
     * @param float $purchaseAmount - Amount paid by customer
     * @param string $buyerUserId - Buyer's user_data.uid
     * @param string $currency - Currency code (INR, USD, etc.)
     * @return array|null Returns earnings data or null if not applicable
     */
    public function processTemplatePurchase(
        int    $purchaseId,
        string $productId,
        string $productType,
        float  $purchaseAmount,
        string $buyerUserId,
        string $currency = 'INR'
    ): ?array
    {
        try {
            // Find the design submission by crafty_design_id
            $designSubmission = DesignSubmission::where('crafty_design_id', $productId)
                ->where('status', 'live')
                ->first();

            if (!$designSubmission) {
                Log::info("No freelancer design found for product_id: {$productId}");
                return null;
            }

            // Get freelancer designer
            $designer = UserData::find($designSubmission->designer_id);
            if (!$designer || $designer->creator != 1) {
                Log::warning("Designer not found or not a freelancer for design_submission: {$designSubmission->id}");
                return null;
            }

            // Get wallet settings for commission rate
            $walletSettings = WalletSetting::getDefault();
            if (!$walletSettings) {
                Log::error("No default wallet settings found");
                return null;
            }

            // Use freelancer_commission_rate (fallback to platform_commission_rate if not set)
            $freelancerPercentage = (float)($walletSettings->freelancer_commission_rate ?? $walletSettings->platform_commission_rate ?? 10.00);
            $freelancerAmount = round(($purchaseAmount * $freelancerPercentage) / 100, 2);

            // Create vendor account if doesn't exist
            $this->ensureVendorAccountExists($designer->uid);

            // Create revenue_history entry
            $revenueHistory = $this->createRevenueHistoryEntry(
                $purchaseId,
                $designer->uid,
                $buyerUserId,
                $freelancerAmount,
                $freelancerPercentage,
                $purchaseAmount,
                $currency,
                $productType
            );

            // Update design_submissions total_sales and total_revenue
            $this->updateDesignSubmissionStats($designSubmission, $purchaseAmount);

            // Update vendor account balance
            $this->updateVendorAccountBalance($designer->uid, $freelancerAmount);

            Log::info("Freelancer earnings processed", [
                'purchase_id' => $purchaseId,
                'designer_id' => $designer->id,
                'designer_uid' => $designer->uid,
                'amount' => $freelancerAmount,
                'percentage' => $freelancerPercentage,
            ]);

            return [
                'revenue_history_id' => $revenueHistory->id,
                'designer_id' => $designer->id,
                'designer_uid' => $designer->uid,
                'designer_name' => $designer->name,
                'freelancer_amount' => $freelancerAmount,
                'freelancer_percentage' => $freelancerPercentage,
                'purchase_amount' => $purchaseAmount,
                'currency' => $currency,
            ];

        } catch (\Exception $e) {
            Log::error("Error processing freelancer earnings: " . $e->getMessage(), [
                'purchase_id' => $purchaseId,
                'product_id' => $productId,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Ensure vendor account exists for freelancer
     */
    private function ensureVendorAccountExists(string $userUid): void
    {
        $exists = VendorAccount::where('user_id', $userUid)->exists();

        if (!$exists) {
            VendorAccount::create([
                'user_id' => $userUid,
                'total_earning' => 0,
                'available_balance' => 0,
                'pending_balance' => 0,
                'withdrawn_amount' => 0,
                'currency' => 'INR',
                'status' => 'active',
            ]);
        }
    }

    /**
     * Create revenue_history row for the transaction
     */
    private function createRevenueHistoryEntry(
        int    $purchaseId,
        string $designerUid,
        string $buyerUid,
        float  $freelancerAmount,
        float  $freelancerPercentage,
        float  $purchaseAmount,
        string $currency,
        string $productType
    ): RevenueHistory
    {
        return RevenueHistory::create([
            'string_id' => 'earn_' . Str::random(16),
            'purchase_id' => $purchaseId,
            'payout_reference' => null,
            'user_id' => $designerUid,
            'vendor_amount' => (int)round($freelancerAmount * 100), // Store in paise/cents
            'vendor_percentage' => (int)$freelancerPercentage,
            'purchase_user_id' => $buyerUid,
            'purchase_amount' => (int)round($purchaseAmount * 100), // Store in paise/cents
            'currency' => $currency,
            'type' => $productType,
            'vendor_type' => 'freelancer',
            'status' => 'active',
        ]);
    }

    /**
     * Update design_submissions total_sales and total_revenue
     */
    private function updateDesignSubmissionStats(DesignSubmission $designSubmission, float $purchaseAmount): void
    {
        DB::connection('crafty_creator_mysql')->table('design_submissions')
            ->where('id', $designSubmission->id)
            ->increment('total_sales', 1);

        DB::connection('crafty_creator_mysql')->table('design_submissions')
            ->where('id', $designSubmission->id)
            ->increment('total_revenue', $purchaseAmount);
    }

    /**
     * Update vendor account balance
     */
    private function updateVendorAccountBalance(string $userUid, float $amount): void
    {
        $amountInCents = (int)round($amount * 100);

        DB::connection('crafty_vendor_mysql')->table('vendor_accounts')
            ->where('user_id', $userUid)
            ->increment('total_earning', $amountInCents);

        DB::connection('crafty_vendor_mysql')->table('vendor_accounts')
            ->where('user_id', $userUid)
            ->increment('available_balance', $amountInCents);
    }

    /**
     * Get freelancer earnings summary
     */
    public function getFreelancerEarningsSummary(string $designerUid): array
    {
        $vendorAccount = VendorAccount::where('user_id', $designerUid)->first();

        $totalEarnings = $vendorAccount ? $vendorAccount->total_earning / 100 : 0;
        $availableBalance = $vendorAccount ? $vendorAccount->available_balance / 100 : 0;
        $withdrawnAmount = $vendorAccount ? $vendorAccount->withdrawn_amount / 100 : 0;

        $totalSales = RevenueHistory::where('user_id', $designerUid)
            ->where('vendor_type', 'freelancer')
            ->where('status', 'active')
            ->count();

        // Get designer_id from user_data first (crafty_db)
        $userData = UserData::where('uid', $designerUid)->first();
        $designerId = $userData ? $userData->id : null;

        if ($designerId) {
            // Use direct DB query to avoid cross-database issues
            $totalDesigns = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('designer_id', $designerId)
                ->count();

            $liveDesigns = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('designer_id', $designerId)
                ->where('status', 'live')
                ->count();
        } else {
            $totalDesigns = 0;
            $liveDesigns = 0;
        }

        return [
            'total_earnings' => $totalEarnings,
            'available_balance' => $availableBalance,
            'withdrawn_amount' => $withdrawnAmount,
            'total_sales' => $totalSales,
            'total_designs' => $totalDesigns,
            'live_designs' => $liveDesigns,
            'currency' => $vendorAccount->currency ?? 'INR',
        ];
    }

    /**
     * Get freelancer transaction history
     */
    public function getFreelancerTransactionHistory(string $designerUid, int $limit = 50): array
    {
        $transactions = RevenueHistory::where('user_id', $designerUid)
            ->where('vendor_type', 'freelancer')
            ->with('purchaseHistory')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'string_id' => $transaction->string_id,
                'purchase_id' => $transaction->purchase_id,
                'amount' => $transaction->vendor_amount / 100,
                'percentage' => $transaction->vendor_percentage,
                'purchase_amount' => $transaction->purchase_amount / 100,
                'currency' => $transaction->currency,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->toIso8601String(),
            ];
        })->toArray();
    }
}

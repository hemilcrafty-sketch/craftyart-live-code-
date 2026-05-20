<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Vendor\VendorAccount;
use App\Models\Vendor\RevenueHistory;
use App\Models\Vendor\VendorWithdraw;
use App\Models\Vendor\UserBankDetails;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\UserData;
use App\Models\Vendor\WalletSetting;
use App\Models\Design;
use App\Models\Creator\Designer\DesignSubmission;
use App\Models\Pricing\PaymentConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class VendorController extends ApiController
{

    /**
     * Normalize UPI VPA to lowercase and trim spaces
     */
    protected static function normalizeUpiVpa(string $vpa): string
    {
        return strtolower(trim($vpa));
    }

    /**
     * Validate UPI VPA format (localpart@psp)
     */
    protected static function validateUpiVpaFormat(?string $vpa): array
    {
        $normalized = self::normalizeUpiVpa((string) $vpa);
        if ($normalized === '') {
            return ['ok' => false, 'error' => 'UPI ID is required', 'normalized' => ''];
        }
        if (strlen($normalized) > 255) {
            return ['ok' => false, 'error' => 'UPI ID is too long (max 255 characters)', 'normalized' => $normalized];
        }
        if (!preg_match('/^[a-z0-9._-]{1,256}@[a-z0-9][a-z0-9._-]{1,63}$/', $normalized)) {
            return [
                'ok' => false,
                'error' => 'Invalid UPI ID format. Use youraddress@bank (e.g. name@okhdfcbank or 9876543210@paytm)',
                'normalized' => $normalized,
            ];
        }

        return ['ok' => true, 'normalized' => $normalized];
    }

    /**
     * Get total referral coins for user (earnings - withdrawals - bank validation)
     */
    public static function getReferralCoins($userId)
    {
        return RevenueHistory::where('user_id', $userId)
            ->where('vendor_type', 'affiliate')
            ->selectRaw("
            SUM(
                CASE
                    WHEN type IN ('withdraw', 'bank_validation') THEN -vendor_amount
                    ELSE vendor_amount
                END
            ) as total_coins
        ")
            ->value('total_coins') ?? 0;
    }

    /**
     * Get total freelancer coins for user (earnings - withdrawals - bank validation)
     */
    public static function getFreelancerCoins($userId)
    {
        return RevenueHistory::where('user_id', $userId)
            ->where('vendor_type', 'freelancer')
            ->selectRaw("
            SUM(
                CASE
                    WHEN type IN ('withdraw', 'bank_validation') THEN -vendor_amount
                    ELSE vendor_amount
                END
            ) as total_coins
        ")
            ->value('total_coins') ?? 0;
    }

    /**
     * Get earnings summary: total earnings, withdrawals, available balance (includes bank validation fee)
     */
    public static function affiliateSummary($userId, $type): array
    {
        $data = RevenueHistory::query()
            ->where('user_id', (string) $userId)
            ->where('vendor_type', (string) $type)
            ->selectRaw("
            SUM(CASE WHEN type NOT IN ('withdraw', 'bank_validation') THEN vendor_amount ELSE 0 END) as earnings,
            SUM(CASE WHEN type IN ('withdraw', 'bank_validation') THEN vendor_amount ELSE 0 END) as withdraw
        ")
            ->first();

        if (!$data) {
            return [
                'earnings' => 0,
                'withdraw' => 0,
                'available' => 0,
            ];
        }

        $earnings = (int) ($data->earnings ?? 0);
        $withdraw = (int) ($data->withdraw ?? 0);

        return [
            'earnings' => $earnings,
            'withdraw' => $withdraw,
            'available' => max(0, $earnings - $withdraw),
        ];
    }

    /**
     * Get referral transaction history (paginated)
     */
    function referralHistory(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request))
            return $this->failed(msg: "Unauthorized");

        return $this->getHistoryByType($request, 'affiliate'); // 'affiliate' for referral users
    }

    /**
     * Get freelancer transaction history (paginated, creator only)
     */
    function freelancerHistory(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request))
            return $this->failed(msg: "Unauthorized");

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();

        if (!$userData)
            return $this->failed(msg: "User is not Creator");

        return $this->getHistoryByType($request, 'freelancer');
    }

    /**
     * Get referral dashboard: link, statistics, wallet settings
     */
    function getReferralDashboard(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) {
            return $this->failed(msg: "User not found");
        }

        // Get wallet settings from crafty_vendor
        $wallet = WalletSetting::query()->where('is_active', true)->orderBy('id')->first();

        // Get referral statistics
        $referralStats = self::affiliateSummary($this->uid, 'affiliate');
        $totalEarned = (int) ($referralStats['earnings'] ?? 0);
        $totalWithdrawn = (int) ($referralStats['withdraw'] ?? 0);
        $availableBalance = max(0, $totalEarned - $totalWithdrawn);

        // Count people referred (users who have this user's ID as referral_user_id)
        $peopleReferred = UserData::where('referral_user_id', $user->id)->count();

        // Calculate commission paid (total withdrawn amount)
        $commissionPaid = $totalWithdrawn;

        // Calculate average per year
        $userCreatedAt = \Carbon\Carbon::parse($user->created_at);
        $now = \Carbon\Carbon::now();
        $yearsSinceCreation = max(1, $userCreatedAt->diffInYears($now) ?: 1); // At least 1 year
        $averagePerYear = $yearsSinceCreation > 0 ? round($totalEarned / $yearsSinceCreation, 2) : 0;

        // Generate referral link using APP_BASE_URL from .env
        // This should be the frontend URL (e.g., https://craftyart.com) not the panel URL
        $baseUrl ="https://www.craftyartapp.com";
        $referralLink = $baseUrl . '?REFERRALCODE=' . strtoupper($user->refer_id);


        return $this->successed(
            msg: "Referral dashboard loaded successfully",
            datas: [
                'referral_link' => $referralLink,
                'refer_id' => $user->refer_id,
                'referral_code' => $user->refer_id,

                // Dynamic statistics
                'statistics' => [
                    'people_referred' => $peopleReferred,
                    'commission_paid' => $commissionPaid,
                    'average_per_year' => $averagePerYear,
                    'total_earned' => $totalEarned,
                    'available_balance' => $availableBalance,
                ],

                // Wallet configuration from crafty_vendor.wallet_settings
                'wallet_settings' => $wallet ? [
                    'min_withdrawal_threshold' => (float) $wallet->min_withdrawal_threshold,
                    'max_withdrawal_limit' => $wallet->max_withdrawal_limit !== null ? (float) $wallet->max_withdrawal_limit : null,
                    'platform_commission_rate' => (float) $wallet->platform_commission_rate,
                    'freelancer_commission_rate' => (float) ($wallet->freelancer_commission_rate ?? $wallet->platform_commission_rate),
                    'referral_commission_rate' => (float) ($wallet->referral_commission_rate ?? 10.00),
                    'payment_type' => $wallet->payment_type,
                ] : null,

                // Additional info
                'user_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'member_since' => $userCreatedAt->format('Y-m-d'),
                    'years_active' => $yearsSinceCreation,
                ],
            ]
        );
    }


    /**
     * Attach referral code to user (one-time only)
     */
    function attachReferralCode(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $code = $request->get('referral_code') ?? $request->get('refer_id');
        if ($code === null || $code === '') {
            return $this->failed(msg: "referral_code or refer_id is required");
        }

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) {
            return $this->failed(msg: "User not found");
        }

        if (!empty($user->referral_user_id)) {
            return $this->failed(msg: "Referral code already applied");
        }

        $referrer = UserData::where('refer_id', $code)->first();
        if (!$referrer) {
            return $this->failed(msg: "Invalid referral code");
        }

        if ($referrer->uid === $user->uid) {
            return $this->failed(msg: "You cannot use your own referral code");
        }

        $user->referral_user_id = $referrer->id;
        $user->referral_code = $referrer->refer_id;
        $user->save();

        return $this->successed(
            msg: "Referral code applied successfully",
            datas: [
                'referral_user_id' => $referrer->id,
                'referral_code' => $referrer->refer_id,
            ]
        );
    }

    /**
     * Common helper to get transaction history by type
     */
    //   private function getHistoryByType(Request $request, string $affiliateType): array|string
    // {
    //     try {
    //         $perPage = 10;
    //         $page = $request->get('page', 1);

    //         $referralHistory = RevenueHistory::whereUserId($this->uid)
    //             ->where('vendor_type', $affiliateType)
    //             ->paginate($perPage, ['*'], 'page', $page);

    //         $referralData = [];
    //         foreach ($referralHistory->items() as $referralItem) {

    //             $isWithdraw = $referralItem->type === 'withdraw';

    //             $data = [
    //                 'string_id' => $referralItem->string_id,
    //                 'type' => $referralItem->type,
    //                 'is_withdraw' => $isWithdraw,
    //                 'vendor_amount' => $referralItem->vendor_amount,
    //                 'vendor_percentage' => $referralItem->vendor_percentage,
    //                 'purchase_amount' => $referralItem->purchase_amount,
    //                 'purchase_user_id' => $referralItem->purchase_user_id,
    //                 'vendor_type' => $referralItem->vendor_type,
    //                 'purchase_id' => $referralItem->purchase_id,
    //                 'payout_reference' => $referralItem->payout_reference,
    //                 'status' => $referralItem->status,
    //                 'created_at' => $referralItem->created_at,
    //             ];

    //             if (!$isWithdraw) {
    //                 if ($affiliateType === 'affiliate') {
    //                     // Buyer who triggered the commission (purchase_user_id on revenue_history)
    //                     if ($referralItem->purchase_user_id) {
    //                         $buyer = UserData::where('uid', $referralItem->purchase_user_id)->first();
    //                         if ($buyer) {
    //                             $data['purchase_user'] = [
    //                                 'uid' => $buyer->uid,
    //                                 'name' => $buyer->name,
    //                                 'email' => $buyer->email,
    //                             ];
    //                         }
    //                     }
    //                     // Type affiliate: Referral - show referral user info
    //                     $referUser = $referralItem->purchaseHistory?->userData;
    //                     if ($referUser) {
    //                         $data['referralUser'] = [
    //                             'name' => $referUser->name,
    //                             'email' => $referUser->email,
    //                         ];
    //                     }
    //                 } else {
    //                     // Type freelancer: Freelancer - show purchase_by (who made the purchase)
    //                     $purchaseUser = $referralItem->purchaseHistory?->userData;
    //                     if ($purchaseUser) {
    //                         $data['purchaseBy'] = [
    //                             'name' => $purchaseUser->name,
    //                             'email' => $purchaseUser->email,
    //                         ];
    //                     }
    //                 }
    //             }

    //             $referralData[] = $data;
    //         }

    //         $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
    //         $datas = [
    //             'data' => $referralData,
    //             'user_id' => $this->uid,
    //             'vendor_type' => $affiliateType,
    //             'type_name' => $typeName,
    //             'current_page' => $referralHistory->currentPage(),
    //             'last_page' => $referralHistory->lastPage(),
    //             'per_page' => $referralHistory->perPage(),
    //             'total' => $referralHistory->total(),
    //             'is_last_page' => $page == $referralHistory->lastPage(),
    //         ];

    //         return $this->successed(msg: "Data Fetched Successfully", datas: $datas);
    //     } catch (\Exception $e) {
    //         return $this->failed(msg: $e->getMessage());
    //     }
    // }
    private function getHistoryByType(Request $request, string $affiliateType): array|string
    {
        try {
            $perPage = 10;
            $page = $request->get('page', 1);

            $referralHistory = RevenueHistory::whereUserId($this->uid)
                ->where('vendor_type', $affiliateType)
                ->paginate($perPage, ['*'], 'page', $page);

            $referralData = [];
            foreach ($referralHistory->items() as $referralItem) {

                $isWithdraw = $referralItem->type === 'withdraw';

                $data = [
                    'string_id' => $referralItem->string_id,
                    'type' => $referralItem->type,
                    'is_withdraw' => $isWithdraw,
                    'vendor_amount' => $referralItem->vendor_amount,
                    'vendor_percentage' => $referralItem->vendor_percentage,
                    'purchase_amount' => $referralItem->purchase_amount,
                    'purchase_user_id' => $referralItem->purchase_user_id,
                    'vendor_type' => $referralItem->vendor_type,
                    'purchase_id' => $referralItem->purchase_id,
                    'payout_reference' => $referralItem->payout_reference,
                    'status' => $referralItem->status,
                    'created_at' => $referralItem->created_at,
                ];

                if (!$isWithdraw) {
                    if ($affiliateType === 'affiliate') {
                        // Buyer who triggered the commission (purchase_user_id on revenue_history)
                        if ($referralItem->purchase_user_id) {
                            $buyer = UserData::where('uid', $referralItem->purchase_user_id)->first();
                            if ($buyer) {
                                $data['purchase_user'] = [
                                    'uid' => $buyer->uid,
                                    'name' => $buyer->name,
                                    'email' => $buyer->email,
                                ];
                            }
                        }
                        // Type affiliate: Referral - show referral user info
                        $referUser = $referralItem->purchaseHistory?->userData;
                        if ($referUser) {
                            $data['referralUser'] = [
                                'name' => $referUser->name,
                                'email' => $referUser->email,
                            ];
                        }
                    } else {
                        // Type freelancer: Freelancer - show purchase_by (who made the purchase)
                        $purchaseUser = $referralItem->purchaseHistory?->userData;
                        if ($purchaseUser) {
                            $data['purchaseBy'] = [
                                'name' => $purchaseUser->name,
                                'email' => $purchaseUser->email,
                            ];
                        }
                    }
                }

                $referralData[] = $data;
            }

            if ($request->get('dummy_data') == 1) {
                $referralData = [];
                for ($i = 1; $i <= 20; $i++) {
                    $referralData[] = [
                        'string_id' => "dummy_id_$i",
                        'type' => 'commission',
                        'is_withdraw' => false,
                        'vendor_amount' => 10,
                        'vendor_percentage' => 10,
                        'purchase_amount' => 100,
                        'purchase_user_id' => "dummy_uid_$i",
                        'vendor_type' => $affiliateType,
                        'purchase_id' => "dummy_purchase_$i",
                        'payout_reference' => null,
                        'status' => 'completed',
                        'created_at' => date('Y-m-d H:i:s'),
                        'purchase_user' => ['uid' => "u_$i", 'name' => "Dummy User $i", 'email' => "dummy$i@gmail.com"],
                        'purchaseBy' => ['name' => "Dummy User $i", 'email' => "dummy$i@gmail.com"],
                    ];
                }
            }

            $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
            $datas = [
                'data' => $referralData,
                'user_id' => $this->uid,
                'vendor_type' => $affiliateType,
                'type_name' => $typeName,
                'current_page' => $referralHistory->currentPage(),
                'last_page' => ($request->get('dummy_data') == 1) ? 1 : $referralHistory->lastPage(),
                'per_page' => ($request->get('dummy_data') == 1) ? 20 : $referralHistory->perPage(),
                'total' => ($request->get('dummy_data') == 1) ? 20 : $referralHistory->total(),
                'is_last_page' => ($request->get('dummy_data') == 1) ? true : ($page == $referralHistory->lastPage()),
            ];

            return $this->successed(msg: "Data Fetched Successfully", datas: $datas);
        } catch (\Exception $e) {
            return $this->failed(msg: $e->getMessage());
        }
    }

    /**
     * Distribute commissions after purchase (referral + freelancer)
     */
    function addSubscription(Request $request)
    {
        $purchaseAmount = $request->get('purchase_amount');
        $type = $request->get('type');
        $userId = $request->get('user_id');
        $productId = $request->get('product_id'); // For freelancer commission calculation

        if (is_null($purchaseAmount) || is_null($type) || is_null($userId)) {
            return $this->failed(msg: "Invalid Params: user_id, purchase_amount, and type are required");
        }

        $this->uid = $userId;

        $purchaseHistoryId = $request->get('purchase_id');
        if ($purchaseHistoryId === null && $request->filled('transaction_id')) {
            $purchaseHistoryId = MasterPurchaseHistory::where('transaction_id', $request->get('transaction_id'))->value('id');
        }
        if ($purchaseHistoryId === null || $purchaseHistoryId === '') {
            return $this->failed(msg: "purchase_id or a valid revenue transaction_id is required");
        }

        $userData = UserData::whereUid($userId)->first();
        if (!$userData) {
            return $this->failed(msg: "User not found");
        }

        $results = [];

        // Add referral points if user was referred
        $referralUserId = $userData->referral_user_id;
        if ($referralUserId) {
            try {
                // Get referrer by ID
                $referrer = UserData::find($referralUserId);
                if ($referrer) {
                    $this->addReferralPoints($referrer->uid, $purchaseHistoryId, $purchaseAmount, 'INR', $type, $userId);
                    $results['referral_user_id'] = $referralUserId;
                }
            } catch (\Exception $e) {
            }
        }
        // Add freelancer commission if this is a template purchase and has a designer
        if ($productId && in_array($type, ['template', 'caricature', 'video'])) {
            try {
                $freelancerUserId = $this->getFreelancerUserIdFromProduct($productId, $type);
                if ($freelancerUserId) {
                    $this->addFreelancerPoints($freelancerUserId, (int) $purchaseHistoryId, $purchaseAmount, "INR", $type, $userId);
                    $results['freelancer_user_id'] = $freelancerUserId;
                }
            } catch (\Exception $e) {
                Log::error('Add freelancer commission failed', [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'purchase_id' => $purchaseHistoryId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->successed(
            msg: "Commissions processed successfully",
            datas: array_merge([
                'purchase_id' => $purchaseHistoryId,
                'purchase_amount' => $purchaseAmount,
            ], $results)
        );
    }

    /**
     * Get freelancer user UID from product (design template)
     * @param string $productId Product ID (design string_id)
     * @param string $type Product type ('template', 'caricature', 'video')
     * @return string|null Freelancer user UID or null if not found
     */
    /**
     * Find designer UID from product (template/caricature/video)
     */
    private function getFreelancerUserIdFromProduct($productId, $type): ?string
    {
        try {
            if ($type === 'template' || $type === 'caricature') {
                // Method 1: Find via DesignSubmission (crafty_design_id = product string_id)
                // design_submissions.designer_id is user_data.id
                $designSubmission = DesignSubmission::where('crafty_design_id', $productId)
                    ->where('status', 'live') // Status is 'live' when published
                    ->first();

                if ($designSubmission && $designSubmission->designer_id) {
                    // designer_id is user_data.id, get UID directly
                    $userData = UserData::where('id', $designSubmission->designer_id)
                        ->where('creator', 1) // Verify user is a creator
                        ->first();

                    if ($userData && $userData->uid) {
                        return $userData->uid;
                    }
                }

                // Method 2: Try direct designer_id from Design table
                // designs.designer_id can be user_data.id (for freelancers)
                $design = Design::where('string_id', $productId)->first();
                if ($design && $design->designer_id) {
                    // Check if designer_id is user_data.id
                    $userData = UserData::where('id', $design->designer_id)
                        ->where('creator', 1)
                        ->first();

                    if ($userData && $userData->uid) {
                        return $userData->uid;
                    }
                }
            } elseif ($type === 'video') {
                // For video templates, similar logic can be applied
                // Check if video has a design_submission or similar structure
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get freelancer user ID from product', [
                'product_id' => $productId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Add referral commission to referrer's account
     */
    function addReferralPoints($referralUserId, int $purchaseHistoryId, $purchaseAmount, $currency, $type, ?string $purchaseUserId = null): void
    {

        $walletSettings = WalletSetting::query()->where('is_active', true)->first();
        if (!$walletSettings) {
            return;
        }

        // Use referral_commission_rate for referral/affiliate commissions (fallback to platform_commission_rate only if referral_commission_rate is not set)
        $referralPercentage = $walletSettings->referral_commission_rate ?? $walletSettings->platform_commission_rate ?? 10.00;

        // purchase_amount & vendor_amount are stored in rupees (same unit as vendor_withdraw.amount and referral coin sums)
        $purchaseAmountRupees = (float) $purchaseAmount;

        if (
            RevenueHistory::query()
                ->where('user_id', $referralUserId)
                ->where('purchase_id', $purchaseHistoryId)
                ->where('vendor_type', 'affiliate')
                ->exists()
        ) {
            return;
        }

        try {
            $referralHistory = new RevenueHistory();
            $referralHistory->string_id = HelperController::generateRandomId(modelSource: RevenueHistory::class, stringType: "lower");
            $referralHistory->user_id = $referralUserId;
            $referralHistory->purchase_id = $purchaseHistoryId;
            $referralHistory->purchase_user_id = $purchaseUserId;
            $referralHistory->vendor_amount = (int) round($purchaseAmountRupees * (float) $referralPercentage / 100, 0);
            $referralHistory->vendor_percentage = (int) $referralPercentage;
            $referralHistory->purchase_amount = (int) round($purchaseAmountRupees, 0);
            $referralHistory->currency = $currency;
            $referralHistory->type = $type;
            $referralHistory->vendor_type = 'affiliate';
            $referralHistory->status = 'pending';
            $referralHistory->save();

        } catch (\Exception $e) {
            //            return ResponseHandler::sendResponse($request,new ResponseInterface(statusCode: 500,success: false,msg: $e->getMessage()));
        }
    }

    /**
     * Add freelancer commission when template is purchased
     */
    function addFreelancerPoints($freelancerUserId, int $purchaseHistoryId, $purchaseAmount, $currency, $type, ?string $purchaseUserId = null): void
    {
        $walletSettings = WalletSetting::query()->where('is_active', true)->first();
        if (!$walletSettings) {
            return;
        }

        // Use freelancer_commission_rate for freelancer/designer commissions (fallback to platform_commission_rate if not set)
        $freelancerPercentage = $walletSettings->freelancer_commission_rate ?? $walletSettings->platform_commission_rate ?? 10.00;

        // purchase_amount & vendor_amount are stored in rupees (same unit as vendor_withdraw.amount)
        $purchaseAmountRupees = (float) $purchaseAmount;

        // Check if entry already exists (prevent duplicates)
        if (
            RevenueHistory::query()
                ->where('user_id', $freelancerUserId)
                ->where('purchase_id', $purchaseHistoryId)
                ->where('vendor_type', 'freelancer')
                ->exists()
        ) {
            return;
        }

        try {
            $freelancerHistory = new RevenueHistory();
            $freelancerHistory->string_id = HelperController::generateRandomId(modelSource: RevenueHistory::class, stringType: "lower");
            $freelancerHistory->user_id = $freelancerUserId;
            $freelancerHistory->purchase_id = $purchaseHistoryId;
            $freelancerHistory->purchase_user_id = $purchaseUserId;
            $freelancerHistory->vendor_amount = (int) round($purchaseAmountRupees * (float) $freelancerPercentage / 100, 0);
            $freelancerHistory->vendor_percentage = (int) $freelancerPercentage;
            $freelancerHistory->purchase_amount = (int) round($purchaseAmountRupees, 0);
            $freelancerHistory->currency = $currency;
            $freelancerHistory->type = $type;
            $freelancerHistory->vendor_type = 'freelancer';
            $freelancerHistory->status = 'active'; // Active immediately for freelancer commissions
            $freelancerHistory->save();

        } catch (\Exception $e) {
            Log::error('Failed to add freelancer commission', [
                'freelancer_user_id' => $freelancerUserId,
                'purchase_id' => $purchaseHistoryId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate UPI format (client-side check, no Razorpay call)
     */
    public function validateUpi(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        $check = self::validateUpiVpaFormat($request->get('upi'));
        if (!$check['ok']) {
            return $this->successed(
                msg: $check['error'],
                datas: [
                    'valid' => false,
                    'upi' => $check['normalized'] !== '' ? $check['normalized'] : null,
                    'error' => $check['error'],
                ]
            );
        }

        return $this->successed(
            msg: 'UPI ID format is valid',
            datas: [
                'valid' => true,
                'upi' => $check['normalized'],
            ]
        );
    }



    /**
     * Add UPI or Bank Account with Razorpay ₹1 validation
     */
    function addBankDetails(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request))
            return $this->failed(msg: "UnAuthorized");

        $type = $request->get('type', 'vpa'); // 'bank_account' or 'vpa'
        $isBankAccount = $type === 'bank_account';

        // Validate based on type
        if ($isBankAccount) {
            $bankName = $request->get('bank_name', null);
            $holderName = $request->get('holder_name', null);
            $accountNumber = $request->get('account_number', null);
            $ifscCode = $request->get('ifsc_code', null);

            if (is_null($bankName) || is_null($holderName) || is_null($accountNumber) || is_null($ifscCode)) {
                return $this->failed(msg: "Invalid Params: bank_name, holder_name, account_number, ifsc_code required");
            }
        } else {
            $vpaCheck = self::validateUpiVpaFormat($request->get('upi'));
            if (!$vpaCheck['ok']) {
                return $this->failed(msg: $vpaCheck['error']);
            }
            $vpaAddress = $vpaCheck['normalized'];
        }

        // Check minimum balance requirement (must have ₹1 + min threshold for validation)
        $wallet = WalletSetting::query()->where('is_active', true)->orderBy('id')->first();
        if (!$wallet) {
            return $this->failed(msg: "Wallet settings are not configured");
        }

        $affiliate = self::affiliateSummary($this->uid, 'affiliate');
        $freelancer = self::affiliateSummary($this->uid, 'freelancer');
        $totalAvailable = (int) (($affiliate['available'] ?? 0) + ($freelancer['available'] ?? 0));
        $minThreshold = (float) $wallet->min_withdrawal_threshold;

        // Need at least min threshold + ₹1 for validation
        $requiredBalance = $minThreshold + 1;

//        if ($totalAvailable < $requiredBalance) {
//            return $this->failed(
//                msg: 'You need at least ₹' . $requiredBalance . ' to add payment details (₹' . $minThreshold . ' minimum + ₹1 validation fee). Current available: ₹' . $totalAvailable . '.',
//                datas: [
//                    'min_withdrawal_threshold' => $minThreshold,
//                    'validation_fee' => 1,
//                    'required_balance' => $requiredBalance,
//                    'available_balance' => $totalAvailable,
//                ]
//            );
//        }

        try {
            // Check if payment method already exists (only active/processing, allow retry if failed)
            if ($isBankAccount) {
                $existingBankAccount = UserBankDetails::where('user_id', $this->uid)
                    ->where('bank_account_number', $accountNumber)
                    ->where('ifsc_code', $ifscCode)
                    ->whereIn('status', ['active', 'completed', 'processing'])
                    ->first();

                if ($existingBankAccount) {
                    return $this->failed(msg: "This bank account is already added. Please use a different account.");
                }

                // Delete old failed entries for same account (allow retry)
                UserBankDetails::where('user_id', $this->uid)
                    ->where('bank_account_number', $accountNumber)
                    ->where('ifsc_code', $ifscCode)
                    ->where('status', 'failed')
                    ->update(['status' => 'deleted']);
            } else {
                $existingVpa = UserBankDetails::where('user_id', $this->uid)
                    ->whereRaw('LOWER(TRIM(upi)) = ?', [$vpaAddress])
                    ->whereIn('status', ['active', 'completed', 'processing'])
                    ->first();

                if ($existingVpa) {
                    return $this->failed(msg: "This UPI ID is already added. Please use a different UPI ID.");
                }

                // Delete old failed entries for same UPI (allow retry)
                UserBankDetails::where('user_id', $this->uid)
                    ->whereRaw('LOWER(TRIM(upi)) = ?', [$vpaAddress])
                    ->where('status', 'failed')
                    ->update(['status' => 'deleted']);
            }

            // Check payment type from wallet settings
            $isManualPayment = $wallet->payment_type === 'manual';

            // Manual payment: instant activation, no Razorpay validation
            if ($isManualPayment) {
                $userBankDetails = new UserBankDetails();
                $userBankDetails->user_id = $this->uid;
                $userBankDetails->string_id = HelperController::generateRandomId(modelSource: UserBankDetails::class, stringType: "lower");
                $userBankDetails->status = 'active'; // Instant active for manual payments

                if ($isBankAccount) {
                    $userBankDetails->bank_name = $bankName;
                    $userBankDetails->bank_holder_name = $holderName;
                    $userBankDetails->bank_account_number = $accountNumber;
                    $userBankDetails->ifsc_code = $ifscCode;
                    $userBankDetails->withdraw_type = 0;
                } else {
                    $userBankDetails->upi = $vpaAddress;
                    $userBankDetails->withdraw_type = 1;
                }

                $userBankDetails->save();

                // Set as primary if first payment method
                $bankDetailsCount = UserBankDetails::whereUserId($this->uid)
                    ->whereIn('status', ['active', 'completed'])
                    ->count();

//                if ($bankDetailsCount == 1) {
//                    $userData = UserData::whereUid($this->uid)->first();
//                    if ($userData) {
//                        $userData->primary_bank_id = $userBankDetails->id;
//                        $userData->save();
//                    }
//                }

                $responseData = [
                    'bank_details_id' => $userBankDetails->string_id,
                    'type' => $type,
                    'status' => $userBankDetails->status,
                    'withdraw_type' => $userBankDetails->withdraw_type,
                    'payment_mode' => 'manual',
                    'message' => 'Payment details added successfully. Manual verification by admin.'
                ];

                if ($isBankAccount) {
                    $responseData['bank_name'] = $userBankDetails->bank_name;
                    $responseData['holder_name'] = $userBankDetails->bank_holder_name;
                    $responseData['account_number'] = '****' . substr($userBankDetails->bank_account_number, -4);
                    $responseData['ifsc_code'] = $userBankDetails->ifsc_code;
                } else {
                    $responseData['upi'] = $userBankDetails->upi;
                }

                return $this->successed(
                    msg: $isBankAccount ? "Bank account added successfully." : "UPI added successfully.",
                    datas: $responseData
                );
            }

            // Razorpay payment: automatic validation with ₹1 penny drop
            // Get Razorpay credentials from payment_configuration
            $razorpayConfig = PaymentConfiguration::where('gateway', 'razorpay')
                ->where('payment_scope', 'NATIONAL')
                ->first();

            if (!$razorpayConfig) {
                return $this->failed(msg: "Razorpay configuration not found. Please contact admin.");
            }

            $credentials = PaymentConfiguration::decryptCredentials($razorpayConfig->credentials);
            $razorpayKey = $credentials['key_id'] ?? null;
            $razorpaySecret = $credentials['key_secret'] ?? null;

            if (!$razorpayKey || !$razorpaySecret) {
                return $this->failed(msg: "Razorpay credentials not configured properly.");
            }

            // Get or create Razorpay contact
            $userData = UserData::whereUid($this->uid)->first();
            if (!$userData) {
                return $this->failed(msg: "User not found");
            }

            $vendorAccount = VendorAccount::where('user_id', $this->uid)->first();

            if (!$vendorAccount || !$vendorAccount->contact_id) {
                // Create Razorpay Contact
                $name = preg_replace('/[^a-zA-Z\s]/', '', $userData->name);
                $name = trim($name);

                $contactData = [
                    'name' => $name,
                    'email' => $userData->email,
                    'contact' => $userData->contact_no ?? '',
                    'type' => 'vendor',
                    'reference_id' => 'usr_' . $this->uid,
                    'notes' => ['user_id' => $this->uid]
                ];

                $response = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                    ->post('https://api.razorpay.com/v1/contacts', $contactData);

                if (!$response->successful()) {
                    $error = $response->json();
                    Log::error('Razorpay contact creation failed', ['error' => $error]);
                    return $this->failed(msg: $error['error']['description'] ?? 'Failed to create Razorpay contact');
                }

                $contact = $response->json();
                $contactId = $contact['id'];


                $vendorAccount = VendorAccount::updateOrCreate(
                    ['user_id' => $this->uid],
                    [
                        'contact_id' => $contactId,
                        'status' => 'active',
                        'string_id' => $userData->string_id ?? HelperController::generateRandomId(modelSource: VendorAccount::class, stringType: "lower")
                    ]
                );
            }

            // Create Razorpay fund account
            $fundAccountData = [
                'contact_id' => $vendorAccount->contact_id,
                'account_type' => $type
            ];

            if ($isBankAccount) {
                $fundAccountData['bank_account'] = [
                    'name' => $holderName,
                    'ifsc' => $ifscCode,
                    'account_number' => $accountNumber
                ];
            } else {
                $fundAccountData['vpa'] = [
                    'address' => $vpaAddress
                ];
            }

            $fundAccountResponse = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                ->post('https://api.razorpay.com/v1/fund_accounts', $fundAccountData);

            if (!$fundAccountResponse->successful()) {
                $error = $fundAccountResponse->json();
                Log::error('Razorpay fund account creation failed', ['error' => $error]);
                return $this->failed(msg: $error['error']['description'] ?? 'Failed to create fund account');
            }

            $fundAccount = $fundAccountResponse->json();
            $fundAccountId = $fundAccount['id'];

            // Save payment details to database with 'processing' status
            $userBankDetails = new UserBankDetails();
            $userBankDetails->user_id = $this->uid;
            $userBankDetails->string_id = HelperController::generateRandomId(modelSource: UserBankDetails::class, stringType: "lower");
            $userBankDetails->razorpay_bank_account_id = $fundAccountId;
            $userBankDetails->status = 'processing'; // Will be updated after ₹1 validation

            if ($isBankAccount) {
                $userBankDetails->bank_name = $bankName;
                $userBankDetails->bank_holder_name = $holderName;
                $userBankDetails->bank_account_number = $accountNumber;
                $userBankDetails->ifsc_code = $ifscCode;
                $userBankDetails->withdraw_type = 0; // Bank Account
            } else {
                $userBankDetails->upi = $vpaAddress;
                $userBankDetails->withdraw_type = 1; // UPI
            }

            $userBankDetails->save();

            // Initiate Razorpay fund account validation (₹1 penny drop)
            // Note: Bank accounts require amount, but VPA (UPI) does not
            $validationData = [
                'fund_account' => [
                    'id' => $fundAccountId
                ],
                'notes' => [
                    'purpose' => 'fund_account_validation',
                    'user_id' => $this->uid,
                    'bank_details_id' => $userBankDetails->string_id
                ]
            ];

            // Add amount and currency only for bank accounts
            if ($isBankAccount) {
                $validationData['amount'] = 100; // ₹1 in paise
                $validationData['currency'] = 'INR';
            }

            $validationResponse = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                ->post('https://api.razorpay.com/v1/fund_accounts/validations', $validationData);

            if (!$validationResponse->successful()) {
                $error = $validationResponse->json();
                Log::error('Razorpay validation failed', ['error' => $error]);

                $userBankDetails->status = 'failed';
                $userBankDetails->save();

                return $this->failed(
                    msg: $error['error']['description'] ?? 'Validation failed',
                    datas: [
                        'bank_details_id' => $userBankDetails->string_id,
                        'status' => 'failed',
                        'error' => $error
                    ]
                );
            }

            $validation = $validationResponse->json();
            $validationId = $validation['id'];
            $validationStatus = $validation['status'] ?? 'created';

            // Deduct ₹1 for validation from user's balance
            $validationHistory = new RevenueHistory();
            $validationHistory->string_id = HelperController::generateRandomId(modelSource: RevenueHistory::class, stringType: "lower");
            $validationHistory->user_id = $this->uid;
            $validationHistory->purchase_id = null;
            $validationHistory->payout_reference = $validationId;
            $validationHistory->vendor_amount = 1; // ₹1 validation fee
            $validationHistory->vendor_percentage = 0;
            $validationHistory->purchase_amount = 0;
            $validationHistory->currency = 'INR';
            $validationHistory->type = 'bank_validation';
            $validationHistory->vendor_type = 'affiliate'; // Deduct from affiliate balance first
            $validationHistory->save();

            // Set as primary if first payment method
            $bankDetailsCount = UserBankDetails::whereUserId($this->uid)
                ->whereIn('status', ['active', 'completed', 'processing'])
                ->where('status', '!=', 'deleted')
                ->count();

//            if ($bankDetailsCount == 1) {
//                $userData->primary_bank_id = $userBankDetails->id;
//                $userData->save();
//            }

            // Prepare response
            $responseData = [
                'bank_details_id' => $userBankDetails->string_id,
                'type' => $type,
                'status' => $userBankDetails->status,
                'withdraw_type' => $userBankDetails->withdraw_type,
                'validation_fee_deducted' => 1,
                'razorpay_validation_id' => $validationId,
                'razorpay_validation_status' => $validationStatus,
                'message' => 'Payment details added. ₹1 sent via Razorpay for validation. Status will be updated automatically via webhook.'
            ];

            if ($isBankAccount) {
                $responseData['bank_name'] = $userBankDetails->bank_name;
                $responseData['holder_name'] = $userBankDetails->bank_holder_name;
                $responseData['account_number'] = '****' . substr($userBankDetails->bank_account_number, -4);
                $responseData['ifsc_code'] = $userBankDetails->ifsc_code;
            } else {
                $responseData['upi'] = $userBankDetails->upi;
            }

            return $this->successed(
                msg: $isBankAccount ? "Bank account added. ₹1 validation initiated via Razorpay." : "UPI added. ₹1 validation initiated via Razorpay.",
                datas: $responseData
            );
        } catch (\Exception $e) {
            Log::error('Add payment details failed', [
                'user_id' => $this->uid,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return $this->failed(msg: $e->getMessage());
        }
    }

    /**
     * Get user's bank accounts and UPIs
     */
    function getBankDetails(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        $userBankDetails = UserBankDetails::whereUserId($this->uid)
            ->whereIn('status', ['active', 'completed', 'processing'])
            ->where('status', '!=', 'deleted')
            ->orderBy('id', 'desc')
            ->get();

        return $this->successed(
            msg: "Fetched Successfully",
            datas: [
                'datas' => $userBankDetails
            ]
        );
    }

    /**
     * Update existing UPI or Bank Account
     */
    function updateBankDetails(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        $stringId = $request->get('id');
        if (is_null($stringId)) {
            return $this->failed(msg: "Invalid Params: id required");
        }

        $type = $request->get('type', 'vpa'); // 'bank_account' or 'vpa'
        $isBankAccount = $type === 'bank_account';

        $bankDetail = UserBankDetails::whereStringId($stringId)
            ->whereUserId($this->uid)
            ->first();

        if (!$bankDetail) {
            return $this->failed(msg: "Payment Details Not Found");
        }

        try {
            if ($isBankAccount) {
                $bankName = $request->get('bank_name', null);
                $holderName = $request->get('holder_name', null);
                $accountNumber = $request->get('account_number', null);
                $ifscCode = $request->get('ifsc_code', null);

                if (is_null($bankName) || is_null($holderName) || is_null($accountNumber) || is_null($ifscCode)) {
                    return $this->failed(msg: "Invalid Params: bank_name, holder_name, account_number, ifsc_code required");
                }

                $bankDetail->update([
                    'bank_name' => $bankName,
                    'bank_holder_name' => $holderName,
                    'bank_account_number' => $accountNumber,
                    'ifsc_code' => $ifscCode,
                    'withdraw_type' => 0,
                ]);

                return $this->successed(
                    msg: "Updated Successfully",
                    datas: [
                        'bank_details_id' => $bankDetail->string_id,
                        'type' => 'bank_account',
                        'bank_name' => $bankDetail->bank_name,
                        'holder_name' => $bankDetail->bank_holder_name,
                        'account_number' => '****' . substr($bankDetail->bank_account_number, -4),
                        'ifsc_code' => $bankDetail->ifsc_code,
                        'status' => $bankDetail->status
                    ]
                );
            } else {
                $vpaCheck = self::validateUpiVpaFormat($request->get('upi'));
                if (!$vpaCheck['ok']) {
                    return $this->failed(msg: $vpaCheck['error']);
                }
                $vpaAddress = $vpaCheck['normalized'];

                $bankDetail->update([
                    'upi' => $vpaAddress,
                    'withdraw_type' => 1,
                ]);

                return $this->successed(
                    msg: "Updated Successfully",
                    datas: [
                        'bank_details_id' => $bankDetail->string_id,
                        'type' => 'vpa',
                        'upi' => $bankDetail->upi,
                        'status' => $bankDetail->status
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error('Update payment details failed', [
                'user_id' => $this->uid,
                'error' => $e->getMessage()
            ]);
            return $this->failed(msg: $e->getMessage());
        }
    }

    /**
     * Delete (soft delete) UPI or Bank Account
     */
    function deleteBankDetails(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        $stringId = $request->get('id');
        if (is_null($stringId)) {
            return $this->failed(msg: "Invalid Params: id required");
        }

        $bankDetail = UserBankDetails::whereStringId($stringId)
            ->whereUserId($this->uid)
            ->first();

        if (!$bankDetail) {
            return $this->failed(msg: "Payment Details Not Found");
        }

        try {
            $bankDetail->update(['status' => 'deleted']);

            // Clear primary_bank_id if this was primary
//            $userData = UserData::whereUid($this->uid)->first();
//            if ($userData && $userData->primary_bank_id == $bankDetail->id) {
//                $userData->primary_bank_id = null;
//                $userData->save();
//            }

            return $this->successed("Deleted Successfully");

        } catch (\Exception $e) {
            Log::error('Delete payment details failed', [
                'user_id' => $this->uid,
                'error' => $e->getMessage()
            ]);
            return $this->failed(msg: $e->getMessage());
        }
    }





    /**
     * Check UPI or Bank Account status (always active for manual payments)
     */
    public function checkBankValidationStatus(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $stringId = $request->get('id');

        if (is_null($stringId) || $stringId === '') {
            return $this->failed(msg: "Invalid Params: id required");
        }

        try {
            $bankDetails = UserBankDetails::whereStringId($stringId)
                ->whereUserId($this->uid)
                ->where('status', '!=', 'deleted')
                ->first();

            if (!$bankDetails) {
                return $this->failed(msg: "Payment Details Not Found");
            }

            $responseData = [
                'bank_details_id' => $bankDetails->string_id,
                'status' => $bankDetails->status,
                'type' => $bankDetails->withdraw_type == 0 ? 'bank_account' : 'vpa',
            ];

            if ($bankDetails->withdraw_type == 0) {
                // Bank Account
                $responseData['bank_name'] = $bankDetails->bank_name;
                $responseData['holder_name'] = $bankDetails->bank_holder_name;
                $responseData['account_number'] = '****' . substr($bankDetails->bank_account_number, -4);
                $responseData['ifsc_code'] = $bankDetails->ifsc_code;
            } else {
                // UPI
                $responseData['upi'] = $bankDetails->upi;
            }

            return $this->successed(
                msg: "Payment details retrieved successfully",
                datas: $responseData
            );

        } catch (\Exception $e) {
            Log::error('Check payment details status failed', [
                'user_id' => $this->uid,
                'error' => $e->getMessage()
            ]);
            return $this->failed(msg: $e->getMessage());
        }
    }


    /**
     * Request referral withdrawal
     */
    function referralWithdraw(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        return $this->processWithdraw($request, 'affiliate'); // 'affiliate' for referral users
    }

    /**
     * Request freelancer withdrawal (creator only)
     */
    function freelancerWithdraw(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();

        if (!$userData)
            return $this->failed(msg: "User is not Creator");

        return $this->processWithdraw($request, 'freelancer'); // 'freelancer' for freelancer users
    }

    /**
     * Common withdrawal processing logic
     */
    private function processWithdraw(Request $request, string $affiliateType): array|string
    {
        // COMMENTED OUT - Using manual payout for now
        // $this->initializeRazorpay();

        $amount = $request->get('amount');
        $bankDetailsId = $request->get('bank_details_id') ?? $request->get('string_id');
        $bankDetailsId = is_string($bankDetailsId) ? trim($bankDetailsId) : $bankDetailsId;

        if (is_null($amount) || $bankDetailsId === '' || $bankDetailsId === null) {
            return $this->failed(msg: "Invalid Params");
        }

        $walletSettings = WalletSetting::query()->where('is_active', true)->first();
        if (!$walletSettings) {
            return $this->failed(msg: "Wallet Settings not configure");
        }

        $maxWithdraw = $walletSettings->max_withdrawal_limit;
        $minWithdraw = $walletSettings->min_withdrawal_threshold;
        // Wallet settings and vendor amounts are in rupees (same unit)
        $minWithdrawRupees = (float) $minWithdraw;
        $maxWithdrawRupees = $maxWithdraw !== null ? (float) $maxWithdraw : null;

        if ((float) $amount < $minWithdrawRupees) {
            return $this->failed(
                msg: "Minimum withdrawal amount is ₹{$minWithdraw}"
            );
        }

        if ($maxWithdrawRupees !== null && (float) $amount > $maxWithdrawRupees) {
            return $this->failed(
                msg: "Maximum withdrawal amount is ₹{$maxWithdraw}"
            );
        }

        // Only block duplicate requests for the same payout lane (referral vs freelancer).
        // A pending affiliate withdrawal must not block freelancer withdrawals (and vice versa).
        $pendingCount = VendorWithdraw::whereUserId($this->uid)
            ->where('vendor_type', $affiliateType)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        if ($pendingCount != 0) {
            return $this->failed(msg: "Request Withdraw is Already in Pending. You can add new request after resolve this request");
        }

        // Get UPI details (must be active)
        $bankDetails = UserBankDetails::whereStringId($bankDetailsId)
            ->whereUserId($this->uid)
            ->whereIn('status', ['active', 'completed'])
            ->where('status', '!=', 'deleted')
            ->first();

        if (!$bankDetails) {
            return $this->failed(
                msg: "Payment Details Not Found",
                datas: [
                    'hint' => 'Use bank_details_id from GET bank list for this user. Payment method must be active.',
                ]
            );
        }

        // Get coins based on affiliate type
        $availableCoins = $affiliateType === 'affiliate'
            ? self::getReferralCoins($this->uid)
            : self::getFreelancerCoins($this->uid);

        if ($availableCoins < $amount) {
            $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
            return $this->failed(msg: "Amount is More than Available {$typeName} coins");
        }

        try {
            // Create withdrawal record first
            $PayoutWithdraw = new VendorWithdraw();
            $PayoutWithdraw->string_id = HelperController::generateRandomId(modelSource: VendorWithdraw::class, stringType: "lower");
            $PayoutWithdraw->bank_details_id = $bankDetailsId;
            $PayoutWithdraw->user_id = $this->uid;
            $PayoutWithdraw->currency = "INR";
            $PayoutWithdraw->amount = $amount;
            $PayoutWithdraw->vendor_type = $affiliateType;
            $PayoutWithdraw->status = 'pending'; // Set to pending for manual payout
            $PayoutWithdraw->save();

            // COMMENTED OUT - Using manual payout for now, skip Razorpay payout creation
            /*
            // Process payout immediately via Razorpay
            $amountInPaise = (int)($amount * 100);

            // Determine payout mode based on withdraw_type
            $payoutMode = $bankDetails->withdraw_type == 0 ? 'IMPS' : 'UPI';

            // Create payout using Razorpay Payout API
            $payoutData = [
                'account_number' => env('RAZORPAY_ACCOUNT_NUMBER', ''), // Your Razorpay account number
                'fund_account_id' => $bankDetails->razorpay_bank_account_id,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'mode' => $payoutMode,
                'purpose' => 'payout',
                'queue_if_low_balance' => true,
                'reference_id' => 'withdraw_' . $PayoutWithdraw->string_id,
                'narration' => 'Referral Withdrawal',
                'notes' => [
                    'user_id' => $this->uid,
                    'withdraw_id' => $PayoutWithdraw->string_id,
                    'type' => 'referral_withdrawal',
                    'vendor_type' => $affiliateType
                ]
            ];

            $response = Http::withBasicAuth($this->razorpayKey, $this->razorpaySecret)
                ->post('https://api.razorpay.com/v1/payouts', $payoutData);

            if (!$response->successful()) {
                $error = $response->json();

                // Update withdrawal status to failed
                $PayoutWithdraw->status = 'failed';
                $PayoutWithdraw->save();

                throw new \Exception($error['error']['description'] ?? 'Failed to create payout');
            }

            $payout = $response->json();

            // Update withdrawal record with payout details
            $PayoutWithdraw->razorpay_payout_id = $payout['id'];
            $PayoutWithdraw->razorpay_payout_status = $payout['status'] ?? 'created';
            $PayoutWithdraw->utr = $payout['utr'] ?? null;
            $PayoutWithdraw->save();

            // Add withdrawal entry to referral history
            $referralHistory = new RevenueHistory();
            $referralHistory->string_id = HelperController::generateStringIds(source: RevenueHistory::class);
            $referralHistory->user_id = $this->uid;
            $referralHistory->purchase_id = null;
            $referralHistory->payout_reference = $payout['id'];
            $referralHistory->vendor_amount = $amount;
            $referralHistory->vendor_percentage = 0;
            $referralHistory->purchase_amount = 0;
            $referralHistory->currency = 'INR';
            $referralHistory->type = 'withdraw';
            $referralHistory->vendor_type = $affiliateType;
            $referralHistory->save();

            $typeName = $affiliateType === 0 ? 'referral' : 'freelancer';

            return $this->successed(
                msg: "Withdrawal processed successfully",
                datas: [
                    'withdraw_id' => $PayoutWithdraw->string_id,
                    'payout_id' => $payout['id'],
                    'status' => $payout['status'] ?? 'created',
                    'amount' => $amount,
                    'currency' => 'INR',
                    'mode' => $payoutMode,
                    'utr' => $payout['utr'] ?? null,
                    'vendor_type' => $affiliateType,
                    'type_name' => $typeName
                ]
            );
            */

            // Manual payout flow - just create withdrawal record
            $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';

            return $this->successed(
                msg: "Withdrawal request created successfully. Awaiting manual payout.",
                datas: [
                    'withdraw_id' => $PayoutWithdraw->string_id,
                    'status' => $PayoutWithdraw->status,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'vendor_type' => $affiliateType,
                    'type_name' => $typeName
                ]
            );

        } catch (\Exception $e) {
            $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
            Log::error('Withdrawal request failed', [
                'user_id' => $this->uid,
                'vendor_type' => $affiliateType,
                'type_name' => $typeName,
                'error' => $e->getMessage()
            ]);

            // If withdrawal record was created, update status to failed
            if (isset($PayoutWithdraw) && $PayoutWithdraw->id) {
                $PayoutWithdraw->status = 'failed';
                $PayoutWithdraw->save();
            }

            return $this->failed(msg: "Withdrawal failed: " . $e->getMessage());
        }
    }

    /**
     * Get referral withdrawal list (paginated)
     */
    function referralWithdrawList(Request $request): array|string
    {
        return $this->getWithdrawRequestByType($request, 'affiliate'); // 'affiliate' for referral users
    }

    /**
     * Manually add freelancer coins (testing/admin only)
     */
    function addFreelancerCoins(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $amount = $request->get('amount'); // Amount in rupees to add
        $purchaseAmount = $request->get('purchase_amount', $amount * 10); // Original purchase amount (default: 10x commission)
        $type = $request->get('type', 'template'); // Type: template, caricature, video, etc.

        if (is_null($amount) || $amount <= 0) {
            return $this->failed(msg: "Invalid amount. Amount must be greater than 0");
        }

        $walletSettings = WalletSetting::query()->where('is_active', true)->first();
        if (!$walletSettings) {
            return $this->failed(msg: "Wallet Settings not configured");
        }

        // Get platform_commission_rate for freelancer
        $platformCommissionRate = $walletSettings->platform_commission_rate ?? 10.00;

        try {
            // Create revenue_history entry to add coins
            $freelancerHistory = new RevenueHistory();
            $freelancerHistory->string_id = HelperController::generateRandomId(modelSource: RevenueHistory::class, stringType: "lower");
            $freelancerHistory->user_id = $this->uid;
            $freelancerHistory->purchase_id = null; // No actual purchase for manual addition
            $freelancerHistory->purchase_user_id = null;
            $freelancerHistory->vendor_amount = (int) round((float) $amount, 0); // Amount in rupees
            $freelancerHistory->vendor_percentage = (int) $platformCommissionRate;
            $freelancerHistory->purchase_amount = (int) round((float) $purchaseAmount, 0);
            $freelancerHistory->currency = 'INR';
            $freelancerHistory->type = $type;
            $freelancerHistory->vendor_type = 'freelancer';
            $freelancerHistory->status = 'active';
            $freelancerHistory->save();

            // Get updated balance
            $availableCoins = self::getFreelancerCoins($this->uid);

            return $this->successed(
                msg: "Freelancer coins added successfully",
                datas: [
                    'amount_added' => $amount,
                    'available_coins' => $availableCoins,
                    'commission_rate' => $platformCommissionRate,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Failed to add freelancer coins', [
                'user_id' => $this->uid,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            return $this->failed(msg: "Failed to add coins: " . $e->getMessage());
        }
    }

    /**
     * Get freelancer withdrawal list (paginated, creator only)
     */
    function freelancerWithdrawList(Request $request): array|string
    {
        return $this->getWithdrawRequestByType($request, 'freelancer'); // 'freelancer' for freelancer users
    }

    /**
     * Common helper to get withdrawal list by type
     */
    //     private function getWithdrawRequestByType(Request $request, string $affiliateType): array|string
    // {
    //     if ($this->isFakeRequestAndUser($request)) {
    //         return $this->failed(msg: "UnAuthorized");
    //     }

    //     if ($affiliateType === 'freelancer') {
    //         $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();

    //         if (!$userData)
    //             return $this->failed(msg: "User is not Creator");
    //     }

    //     $perPage = 10;
    //     $page = $request->get('page', 1);

    //     $PayoutWithdraws = VendorWithdraw::whereUserId($this->uid)
    //         ->where('vendor_type', $affiliateType)
    //         ->paginate($perPage, ['*'], 'page', $page);

    //     $PayoutWithdrawsData = [];
    //     foreach ($PayoutWithdraws->items() as $withdrawItem) {
    //         $PayoutWithdrawsData[] = [
    //             'string_id' => $withdrawItem->string_id,
    //             'user_id' => $withdrawItem->user_id,
    //             'bank_details_id' => $withdrawItem->bank_details_id,
    //             'amount' => $withdrawItem->amount,
    //             'currency' => $withdrawItem->currency,
    //             'vendor_type' => $withdrawItem->vendor_type,
    //             'status' => $withdrawItem->status,
    //             'razorpay_payout_id' => $withdrawItem->razorpay_payout_id,
    //             'razorpay_payout_status' => $withdrawItem->razorpay_payout_status,
    //             'utr' => $withdrawItem->utr,
    //             'completed_at' => $withdrawItem->completed_at,
    //             'failure_reason' => $withdrawItem->failure_reason,
    //             'created_at' => $withdrawItem->created_at,
    //             'updated_at' => $withdrawItem->updated_at,
    //         ];
    //     }

    //     $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
    //     $datas = [
    //         'data' => $PayoutWithdrawsData,
    //         'vendor_type' => $affiliateType,
    //         'type_name' => $typeName,
    //         'current_page' => $PayoutWithdraws->currentPage(),
    //         'last_page' => $PayoutWithdraws->lastPage(),
    //         'per_page' => $PayoutWithdraws->perPage(),
    //         'total' => $PayoutWithdraws->total(),
    //         'is_last_page' => $page == $PayoutWithdraws->lastPage(),
    //     ];

    //     return $this->successed(msg: "Data Fetched Successfully", datas: $datas);
    // }




    private function getWithdrawRequestByType(Request $request, string $affiliateType): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "UnAuthorized");
        }

        if ($affiliateType === 'freelancer') {
            $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();

            if (!$userData)
                return $this->failed(msg: "User is not Creator");
        }

        $perPage = 10;
        $page = $request->get('page', 1);

        $PayoutWithdraws = VendorWithdraw::whereUserId($this->uid)
            ->where('vendor_type', $affiliateType)
            ->paginate($perPage, ['*'], 'page', $page);

        $PayoutWithdrawsData = [];
        foreach ($PayoutWithdraws->items() as $withdrawItem) {
            $PayoutWithdrawsData[] = [
                'string_id' => $withdrawItem->string_id,
                'user_id' => $withdrawItem->user_id,
                'bank_details_id' => $withdrawItem->bank_details_id,
                'amount' => $withdrawItem->amount,
                'currency' => $withdrawItem->currency,
                'vendor_type' => $withdrawItem->vendor_type,
                'status' => $withdrawItem->status,
                'razorpay_payout_id' => $withdrawItem->razorpay_payout_id,
                'razorpay_payout_status' => $withdrawItem->razorpay_payout_status,
                'utr' => $withdrawItem->utr,
                'completed_at' => $withdrawItem->completed_at,
                'failure_reason' => $withdrawItem->failure_reason,
                'created_at' => $withdrawItem->created_at,
                'updated_at' => $withdrawItem->updated_at,
            ];
        }

        if ($request->get('dummy_data') == 1) {
            $PayoutWithdrawsData = [];
            for ($i = 1; $i <= 20; $i++) {
                $PayoutWithdrawsData[] = [
                    'string_id' => "dummy_withdraw_$i",
                    'user_id' => $this->uid,
                    'bank_details_id' => "dummy_bank_$i",
                    'amount' => 500,
                    'currency' => 'INR',
                    'vendor_type' => $affiliateType,
                    'status' => 'completed',
                    'razorpay_payout_id' => "dummy_payout_$i",
                    'razorpay_payout_status' => 'processed',
                    'utr' => "UTR123456$i",
                    'completed_at' => date('Y-m-d H:i:s'),
                    'failure_reason' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $typeName = $affiliateType === 'affiliate' ? 'referral' : 'freelancer';
        $datas = [
            'data' => $PayoutWithdrawsData,
            'vendor_type' => $affiliateType,
            'type_name' => $typeName,
            'current_page' => $PayoutWithdraws->currentPage(),
            'last_page' => ($request->get('dummy_data') == 1) ? 1 : $PayoutWithdraws->lastPage(),
            'per_page' => ($request->get('dummy_data') == 1) ? 20 : $PayoutWithdraws->perPage(),
            'total' => ($request->get('dummy_data') == 1) ? 20 : $PayoutWithdraws->total(),
            'is_last_page' => ($request->get('dummy_data') == 1) ? true : ($page == $PayoutWithdraws->lastPage()),
        ];

        return $this->successed(msg: "Data Fetched Successfully", datas: $datas);
    }


    /**
     * Process Payout to User's Bank Account
     * Admin function to process approved withdrawal requests
     * COMMENTED OUT - Using manual payout for now
     */
    /*
    function processPayoutToBank(Request $request): array|string
    {
        // Note: This should be called by admin, add admin authentication check

        $this->initializeRazorpay();

        try {
            $withdrawId = $request->get('withdraw_id');

            if (is_null($withdrawId)) {
                return $this->failed(msg: "Invalid Params");
            }

            // Get withdrawal request
            $withdrawal = VendorWithdraw::whereStringId($withdrawId)->first();

            if (!$withdrawal) {
                return $this->failed(msg: "Withdrawal request not found");
            }

            if ($withdrawal->status !== 'pending') {
                return $this->failed(msg: "Withdrawal request is not pending");
            }

            // Get bank details (contains fund account ID)
            $bankDetails = UserBankDetails::whereStringId($withdrawal->bank_details_id)
                ->whereStatus(1)
                ->first();

            if (!$bankDetails || !$bankDetails->razorpay_bank_account_id) {
                return $this->failed(msg: "Fund account not found in Razorpay");
            }

            // Convert amount to paise
            $amountInPaise = (int)($withdrawal->amount * 100);

            // Create payout using Razorpay Payout API
            $payoutData = [
                'account_number' => env('RAZORPAY_ACCOUNT_NUMBER', ''), // Your Razorpay account number
                'fund_account_id' => $bankDetails->razorpay_bank_account_id,
                'amount' => $amountInPaise,
                'currency' => $withdrawal->currency ?? 'INR',
                'mode' => $bankDetails->withdraw_type == 0 ? 'IMPS' : 'UPI', // IMPS for bank, UPI for VPA
                'purpose' => 'payout',
                'queue_if_low_balance' => true,
                'reference_id' => 'withdraw_' . $withdrawal->string_id,
                'narration' => 'Referral Withdrawal',
                'notes' => [
                    'user_id' => $withdrawal->user_id,
                    'withdraw_id' => $withdrawal->string_id,
                    'type' => 'referral_withdrawal'
                ]
            ];

            $response = Http::withBasicAuth($this->razorpayKey, $this->razorpaySecret)
                ->post('https://api.razorpay.com/v1/payouts', $payoutData);

            if (!$response->successful()) {
                $error = $response->json();
                throw new \Exception($error['error']['description'] ?? 'Failed to create payout');
            }

            $payout = $response->json();

            // Update withdrawal request
            $withdrawal->status = 'processing';
            $withdrawal->razorpay_payout_id = $payout['id'];
            $withdrawal->razorpay_payout_status = $payout['status'] ?? 'created';
            $withdrawal->utr = $payout['utr'] ?? null;
            $withdrawal->save();

            // Note: Withdrawal entry will be added to referral_history only when payout is successfully completed via webhook

            return $this->successed(
                msg: "Payout created successfully",
                datas: [
                    'payout_id' => $payout['id'],
                    'status' => $payout['status'] ?? 'created',
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency ?? 'INR',
                    'mode' => $payoutData['mode'],
                    'utr' => $payout['utr'] ?? null
                ]
            );

        } catch (\Exception $e) {
            Log::error('Process payout failed', [
                'error' => $e->getMessage()
            ]);

            return $this->failed(msg: "Failed to process payout: " . $e->getMessage());
        }
    }
    */

    /**
     * Get withdrawal status (manual payout - DB only)
     */
    function getPayoutStatus(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $withdrawId = $request->get('withdraw_id');
        if ($withdrawId === null || $withdrawId === '') {
            return $this->failed(msg: "withdraw_id is required");
        }

        $withdrawal = VendorWithdraw::whereStringId($withdrawId)
            ->whereUserId($this->uid)
            ->first();

        if (!$withdrawal) {
            return $this->failed(msg: "Withdrawal request not found");
        }

        $bankDetails = UserBankDetails::whereStringId($withdrawal->bank_details_id)->first();

        $datas = [
            'withdraw_id' => $withdrawal->string_id,
            'status' => $withdrawal->status,
            'amount' => $withdrawal->amount,
            'currency' => $withdrawal->currency,
            'vendor_type' => $withdrawal->vendor_type,
            'utr' => $withdrawal->utr,
            'completed_at' => $withdrawal->completed_at,
            'failure_reason' => $withdrawal->failure_reason,
            'created_at' => $withdrawal->created_at,
            'bank_details' => null,
        ];

        if ($bankDetails) {
            if ($bankDetails->withdraw_type == 0) {
                // Bank Account
                $datas['bank_details'] = [
                    'type' => 'bank_account',
                    'bank_name' => $bankDetails->bank_name,
                    'holder_name' => $bankDetails->bank_holder_name,
                    'account_number' => '****' . substr($bankDetails->bank_account_number, -4),
                    'ifsc_code' => $bankDetails->ifsc_code,
                ];
            } else {
                // UPI
                $datas['bank_details'] = [
                    'type' => 'vpa',
                    'upi' => $bankDetails->upi,
                ];
            }
        }

        return $this->successed(msg: "Payout status retrieved", datas: $datas);
    }

    /**
     * Send notification about payout status (placeholder)
     */
    private function notifyUserPayoutStatus(VendorWithdraw $withdrawal)
    {
        // TODO: email / push / WhatsApp when payout completes or fails
    }

    /**
     * Razorpay fund account validation webhook handler
     * Automatically updates payment method status when Razorpay validates
     */
    public function handleFundAccountValidationWebhook(Request $request)
    {
        try {
            $payload = $request->all();

            // Verify webhook signature
            $razorpayConfig = PaymentConfiguration::where('gateway', 'razorpay')
                ->where('payment_scope', 'NATIONAL')
                ->first();

            if (!$razorpayConfig) {
                Log::error('Razorpay config not found for webhook');
                return response()->json(['error' => 'Config not found'], 500);
            }

            $credentials = PaymentConfiguration::decryptCredentials($razorpayConfig->credentials);
            $webhookSecret = $credentials['webhook_secret'] ?? env('RAZORPAY_WEBHOOK_SECRET');

            if ($webhookSecret) {
                $expectedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);
                $actualSignature = $request->header('X-Razorpay-Signature');

                if ($expectedSignature !== $actualSignature) {
                    Log::warning('Invalid Razorpay webhook signature');
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            $event = $payload['event'] ?? null;
            $validationData = $payload['payload']['fund_account']['validation'] ?? null;

            if (!$event || !$validationData) {
                Log::error('Invalid webhook payload structure');
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Only process fund account validation events
            if (!str_starts_with($event, 'fund_account.validation.')) {
                return response()->json(['status' => 'ignored'], 200);
            }

            $validationId = $validationData['id'] ?? null;
            $status = $validationData['status'] ?? null;
            $fundAccountId = $validationData['fund_account']['id'] ?? null;

            if (!$validationId || !$fundAccountId) {
                Log::error('Validation ID or Fund Account ID not found');
                return response()->json(['error' => 'Missing IDs'], 400);
            }

            // Find bank details by fund_account_id
            $bankDetails = UserBankDetails::where('razorpay_bank_account_id', $fundAccountId)
                ->first();

            if (!$bankDetails) {
                Log::warning('Bank details not found for validation', [
                    'fund_account_id' => $fundAccountId
                ]);
                return response()->json(['error' => 'Bank details not found'], 404);
            }

            // Map Razorpay status to our status
            $mappedStatus = match ($status) {
                'created' => 'processing',
                'completed' => 'active',
                'failed' => 'failed',
                default => 'processing'
            };

            // Update bank details with new status
            $bankDetails->status = $mappedStatus;
            $bankDetails->save();

            Log::info('Fund account validation webhook processed', [
                'validation_id' => $validationId,
                'fund_account_id' => $fundAccountId,
                'status' => $status,
                'mapped_status' => $mappedStatus,
                'bank_details_id' => $bankDetails->string_id
            ]);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Fund account validation webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

}

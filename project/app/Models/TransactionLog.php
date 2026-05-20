<?php

namespace App\Models;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Http\Controllers\Utils\HelperController;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\TransactionLog
 *
 * @property int $id
 * @property int $plan_id
 * @property string|null $subscription_id
 * @property int $subscription_is_active
 * @property string|null $cancellation_reason
 * @property string $user_id
 * @property string|null $contact_no
 * @property string|null $order_id
 * @property string $transaction_id
 * @property string|null $payment_id
 * @property string $currency_code
 * @property float $price_amount
 * @property float $paid_amount
 * @property float $net_amount
 * @property int|null $coins
 * @property float|null $discount
 * @property int $promo_code_id
 * @property string $payment_method
 * @property string $from_where
 * @property int $isManual
 * @property int $validity
 * @property int $yearly
 * @property array $plan_limit
 * @property int $type
 * @property int $payment_status
 * @property int|null $status
 * @property string $subscription_status
 * @property int $is_trial
 * @property int $is_e_mandate
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $expired_at
 * @property-read UserData|null $userData
 * @property-read OfferPackage|null $offer
 * @property-read SubPlan|null $subPlan
 * @property-read Subscription|null $subscription
 * @method static Builder|TransactionLog newModelQuery()
 * @method static Builder|TransactionLog newQuery()
 * @method static Builder|TransactionLog query()
 * @method static Builder|TransactionLog whereCoins($value)
 * @method static Builder|TransactionLog whereContactNo($value)
 * @method static Builder|TransactionLog whereCreatedAt($value)
 * @method static Builder|TransactionLog whereCurrencyCode($value)
 * @method static Builder|TransactionLog whereDiscount($value)
 * @method static Builder|TransactionLog whereExpiredAt($value)
 * @method static Builder|TransactionLog whereFromWhere($value)
 * @method static Builder|TransactionLog whereId($value)
 * @method static Builder|TransactionLog whereIsManual($value)
 * @method static Builder|TransactionLog whereNetAmount($value)
 * @method static Builder|TransactionLog whereOrderId($value)
 * @method static Builder|TransactionLog wherePaidAmount($value)
 * @method static Builder|TransactionLog wherePaymentId($value)
 * @method static Builder|TransactionLog wherePaymentMethod($value)
 * @method static Builder|TransactionLog wherePaymentStatus($value)
 * @method static Builder|TransactionLog wherePlanId($value)
 * @method static Builder|TransactionLog whereSubscriptionId($value)
 * @method static Builder|TransactionLog whereSubscriptionIsActive($value)
 * @method static Builder|TransactionLog wherePriceAmount($value)
 * @method static Builder|TransactionLog wherePromoCodeId($value)
 * @method static Builder|TransactionLog whereStatus($value)
 * @method static Builder|TransactionLog whereTransactionId($value)
 * @method static Builder|TransactionLog whereUpdatedAt($value)
 * @method static Builder|TransactionLog whereUserId($value)
 * @method static Builder|TransactionLog whereValidity($value)
 * @method static Builder|TransactionLog whereType($value)
 * @mixin Eloquent
 */

class TransactionLog extends Model
{
    public static int $OLD_PLAN = 0;
    public static int $NEW_PLAN = 1;
    public static int $OFFER_PLAN = 2;

    protected $connection = 'mysql';
    use HasFactory;

    protected $guarded = [];
//    protected $fillable = [
//        'plan_id',
//        'subscription_id',
//        'subscription_is_active',
//        'user_id',
//        'contact_no',
//        'order_id',
//        'transaction_id',
//        'payment_id',
//        'currency_code',
//        'price_amount',
//        'paid_amount',
//        'net_amount',
//        'coins',
//        'discount',
//        'promo_code_id',
//        'payment_method',
//        'from_where',
//        'fbc',
//        'gclid',
//        'url',
//        'isManual',
//        'validity',
//        'yearly',
//        'plan_limit',
//        'type',
//        'payment_status',
//        'is_trial',
//        'is_e_mandate',
//        'status',
//        'expired_at',
//    ];

    public static function generatePaymentId(): string
    {
        $txnId = PaymentController::generateID();
        while (TransactionLog::wherePaymentId($txnId)->exists()) {
            $txnId = PaymentController::generateID();
        }
        return $txnId;
    }

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'plan_id', 'id');
    }

    public function subPlan(): BelongsTo
    {
        return $this->belongsTo(SubPlan::class, 'plan_id', 'string_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(OfferPackage::class, 'plan_id', 'string_id');
    }

    public function isOldPlan(): string
    {
        return $this->type == self::$OLD_PLAN;
    }

    public function isNewPlan(): string
    {
        return $this->type == self::$NEW_PLAN;
    }

    public function isOfferPlan(): string
    {
        return $this->type == self::$OFFER_PLAN;
    }

    public function getPlanLimitAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

    public function getRelatedPlanAttribute(): OfferPackage|Subscription|SubPlan|null
    {
        return match ($this->type) {
            0 => $this->subscription,
            1 => $this->subPlan,
            2 => $this->offer,
            default => null,
        };
    }

    public function getAutomationCommonData($planId): array
    {
        $currency = $this->currency_code;
        if (!$this->userData) {
            return ['success' => false, 'message' => "User not found for transaction {$this->id}"];
        }

        $commonData['userData'] = [
            'name' => $this->userData->name ?? '',
            'email' => $this->userData->email ?? '',
            'password' => "",
        ];

        $paymentLink =  "https://www.craftyartapp.com/plans";

        $plan = Subscription::whereId($planId)->first();
        if (!$plan) {
            return ['success' => false, 'message' => "Subscription not found for transaction {$this->id}"];
        }

        $commonData['type'] = "plan";
        $commonData['data'] = AutomationUtils::formatOldPlanData($plan, $currency);
        $commonData['planType'] = 'old_sub';
        $commonData['link'] = $paymentLink;
        $commonData['waBtnLink'] = str_replace("https://www.craftyartapp.com/", "", $paymentLink);
        return $commonData;
    }

//    public function getAutomationCommonData(): array
//    {
//        $currency = $this->currency_code;
//        $planType = $this->type; // 0 = old plan, 1 = new plan, 2 = offer plan
//
//        // Check if user exists
//        if (!$this->userData) {
//            return ['success' => false, 'message' => "User not found for transaction {$this->id}"];
//        }
//
//        $commonData['userData'] = [
//            'name' => $this->userData->name ?? '',
//            'email' => $this->userData->email ?? '',
//            'password' => "",
//        ];
//
//        $paymentLink =  "https://editor.craftyartapp.com/plans";
//
//        // Handle based on type
//        if ($planType === 1) { // New plan (SubPlan)
//            $plan = $this->subPlan;
//            if (!$plan) {
//                return ['success' => false, 'message' => "New SubPlan not found for transaction {$this->id}"];
//            }
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatNewPlanData($plan, $currency);
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = 'new_sub'; // Using same terminology as Order model
//
//        } elseif ($planType === 0) { // Old plan (Subscription)
//            $plan = $this->subscription;
//            if (!$plan) {
//                return ['success' => false, 'message' => "Subscription not found for transaction {$this->id}"];
//            }
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatOldPlanData($plan, $currency);
//            $commonData['plan'] = $plan;
//            $commonData['planType'] = 'old_sub'; // Using same terminology as Order model
//
//        } elseif ($planType === 2) { // Offer plan
//            $offerPackage = $this->offer;
//            if (!$offerPackage) {
//                return ['success' => false, 'message' => "Offer Package not found for transaction {$this->id}"];
//            }
//
//            $offerSubPlan = $offerPackage->subPlan;
//            if (!$offerSubPlan) {
//                return ['success' => false, 'message' => "Offer SubPlan not found for transaction {$this->id}"];
//            }
//
//            $commonData['type'] = "plan";
//            $commonData['data'] = AutomationUtils::formatNewPlanData($offerSubPlan, $currency);
//            $commonData['plan'] = $offerSubPlan;
//            $commonData['planType'] = 'offer'; // Using same terminology as Order model
//
//        } else {
//            return ['success' => false, 'message' => "Invalid plan type provided for transaction {$this->id}"];
//        }
//
//        $commonData['link'] = $paymentLink;
//        $commonData['waBtnLink'] = str_replace("https://www.craftyartapp.com/", "", $paymentLink);
//
//        return $commonData;
//    }

    public function getContactNoAttribute($value): ?string
    {
        return $this->userData->contact_no ?? $value;
    }

}

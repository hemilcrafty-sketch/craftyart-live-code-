<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

class RefundedTransactionLog extends Model
{
    protected $connection = 'mysql';
    protected $table = 'refunded_transaction_logs'; // exact table name
    protected $guarded = [];

    use HasFactory;
}

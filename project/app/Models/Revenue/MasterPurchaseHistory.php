<?php

namespace App\Models\Revenue;

use App\Models\Order;
use App\Models\User;
use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Revenue\MasterPurchaseHistory
 *
 * @property int $id
 * @property int $emp_id
 * @property int $by_sales_team
 * @property string $user_id
 * @property string|null $contact_no
 * @property string $product_id
 * @property string $product_type
 * @property string|null $subscription_id
 * @property int $subscription_is_active
 * @property string|null $cancellation_reason
 * @property string|null $order_id
 * @property string $transaction_id
 * @property string $payment_id
 * @property string $currency_code
 * @property float $amount
 * @property float|null $paid_amount
 * @property float $net_amount
 * @property int $promo_code_id
 * @property int $discount
 * @property string $payment_method
 * @property string $from_where
 * @property string|null $fbc
 * @property string|null $gclid
 * @property int $isManual
 * @property string|null $url
 * @property int $validity
 * @property int $yearly
 * @property string|null $plan_limit
 * @property string|null $subscription_status
 * @property int $is_trial
 * @property int $is_e_mandate
 * @property string $payment_status
 * @property int $status
 * @property int $email_sent
 * @property int $wp_sent
 * @property int $used
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $expired_at
 * @property-read Order|null $order
 * @property-read UserData|null $userData
 * @method static Builder|MasterPurchaseHistory with($value)
 * @method static Builder|MasterPurchaseHistory newModelQuery()
 * @method static Builder|MasterPurchaseHistory newQuery()
 * @method static Builder|MasterPurchaseHistory query()
 * @method static Builder|MasterPurchaseHistory whereAmount($value)
 * @method static Builder|MasterPurchaseHistory whereBySalesTeam($value)
 * @method static Builder|MasterPurchaseHistory whereCancellationReason($value)
 * @method static Builder|MasterPurchaseHistory whereContactNo($value)
 * @method static Builder|MasterPurchaseHistory whereCreatedAt($value)
 * @method static Builder|MasterPurchaseHistory whereCurrencyCode($value)
 * @method static Builder|MasterPurchaseHistory whereDiscount($value)
 * @method static Builder|MasterPurchaseHistory whereEmailSent($value)
 * @method static Builder|MasterPurchaseHistory whereEmpId($value)
 * @method static Builder|MasterPurchaseHistory whereExpiredAt($value)
 * @method static Builder|MasterPurchaseHistory whereFbc($value)
 * @method static Builder|MasterPurchaseHistory whereFromWhere($value)
 * @method static Builder|MasterPurchaseHistory whereGclid($value)
 * @method static Builder|MasterPurchaseHistory whereId($value)
 * @method static Builder|MasterPurchaseHistory whereIsEMandate($value)
 * @method static Builder|MasterPurchaseHistory whereIsManual($value)
 * @method static Builder|MasterPurchaseHistory whereIsTrial($value)
 * @method static Builder|MasterPurchaseHistory whereNetAmount($value)
 * @method static Builder|MasterPurchaseHistory whereOrderId($value)
 * @method static Builder|MasterPurchaseHistory wherePaidAmount($value)
 * @method static Builder|MasterPurchaseHistory wherePaymentId($value)
 * @method static Builder|MasterPurchaseHistory wherePaymentMethod($value)
 * @method static Builder|MasterPurchaseHistory wherePlanLimit($value)
 * @method static Builder|MasterPurchaseHistory whereProductId($value)
 * @method static Builder|MasterPurchaseHistory whereProductType($value)
 * @method static Builder|MasterPurchaseHistory wherePromoCodeId($value)
 * @method static Builder|MasterPurchaseHistory wherePaymentStatus($value)
 * @method static Builder|MasterPurchaseHistory whereStatus($value)
 * @method static Builder|MasterPurchaseHistory whereSubscriptionId($value)
 * @method static Builder|MasterPurchaseHistory whereSubscriptionIsActive($value)
 * @method static Builder|MasterPurchaseHistory whereSubscriptionStatus($value)
 * @method static Builder|MasterPurchaseHistory whereTransactionId($value)
 * @method static Builder|MasterPurchaseHistory whereUpdatedAt($value)
 * @method static Builder|MasterPurchaseHistory whereUrl($value)
 * @method static Builder|MasterPurchaseHistory whereUsed($value)
 * @method static Builder|MasterPurchaseHistory whereUserId($value)
 * @method static Builder|MasterPurchaseHistory whereValidity($value)
 * @method static Builder|MasterPurchaseHistory whereWpSent($value)
 * @method static Builder|MasterPurchaseHistory whereYearly($value)
 * @mixin Eloquent
 */

class MasterPurchaseHistory extends Model
{
    public static array $types = [
        '0' => 'template',
        '1' => 'font',
        '2' => 'sticker',
        '3' => 'background',
        '4' => 'video',
        '5' => 'caricature',
        '6' => 'ai_credit',
        '7' => 'old_sub',
        '8' => 'new_sub',
        '9' => 'offer_sub',
        '10' => 'business_support',
    ];

    public static array $sub_type = [
        '0' => 'old_sub',
        '1' => 'new_sub',
        '2' => 'offer_sub',
    ];

    protected $table = 'purchase_history';
    protected $connection = 'crafty_revenue_mysql';
    use HasFactory;

    protected $guarded = [];

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class,'emp_id','id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,'transaction_id','payment_id');
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(
            MasterPurchaseHistory::class,
            'user_id',
            'user_id'
        );
    }
}

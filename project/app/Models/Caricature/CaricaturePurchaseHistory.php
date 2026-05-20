<?php

namespace App\Models\Caricature;

use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Traits\UpdateLogger;

/**
 * App\Models\Caricature\CaricaturePurchaseHistory
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $contact_no
 * @property string $product_id
 * @property int $product_type
 * @property string|null $order_id
 * @property string $transaction_id
 * @property string $payment_id
 * @property string $currency_code
 * @property string $amount
 * @property string|null $paid_amount
 * @property float $net_amount
 * @property int $promo_code_id
 * @property string $payment_method
 * @property string $from_where
 * @property int|null $isManual
 * @property int $payment_status
 * @property int $status
 * @property int $used
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserData|null $userData
 * @method static Builder|CaricaturePurchaseHistory newModelQuery()
 * @method static Builder|CaricaturePurchaseHistory newQuery()
 * @method static Builder|CaricaturePurchaseHistory query()
 * @method static Builder|CaricaturePurchaseHistory whereAmount($value)
 * @method static Builder|CaricaturePurchaseHistory whereContactNo($value)
 * @method static Builder|CaricaturePurchaseHistory whereCreatedAt($value)
 * @method static Builder|CaricaturePurchaseHistory whereCurrencyCode($value)
 * @method static Builder|CaricaturePurchaseHistory whereFromWhere($value)
 * @method static Builder|CaricaturePurchaseHistory whereId($value)
 * @method static Builder|CaricaturePurchaseHistory whereIsManual($value)
 * @method static Builder|CaricaturePurchaseHistory whereNetAmount($value)
 * @method static Builder|CaricaturePurchaseHistory whereOrderId($value)
 * @method static Builder|CaricaturePurchaseHistory wherePaidAmount($value)
 * @method static Builder|CaricaturePurchaseHistory wherePaymentId($value)
 * @method static Builder|CaricaturePurchaseHistory wherePaymentMethod($value)
 * @method static Builder|CaricaturePurchaseHistory wherePaymentStatus($value)
 * @method static Builder|CaricaturePurchaseHistory whereProductId($value)
 * @method static Builder|CaricaturePurchaseHistory whereProductType($value)
 * @method static Builder|CaricaturePurchaseHistory wherePromoCodeId($value)
 * @method static Builder|CaricaturePurchaseHistory whereStatus($value)
 * @method static Builder|CaricaturePurchaseHistory whereTransactionId($value)
 * @method static Builder|CaricaturePurchaseHistory whereUpdatedAt($value)
 * @method static Builder|CaricaturePurchaseHistory whereUserId($value)
 * @method static Builder|CaricaturePurchaseHistory whereUsed($value)
 * @mixin Eloquent
 */
class CaricaturePurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'crafty_caricature_mysql';
    use HasFactory;
    use UpdateLogger;

    protected $fillable = [
        'user_id',
        'contact_no',
        'product_id',
        'product_type',
        'order_id',
        'transaction_id',
        'payment_id',
        'currency_code',
        'amount',
        'paid_amount',
        'net_amount',
        'promo_code_id',
        'payment_method',
        'from_where',
        'fbc',
        'isManual',
        'payment_status',
        'status',
    ];

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

}

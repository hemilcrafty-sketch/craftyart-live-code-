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
 * App\Models\Caricature\AiPurchaseHistory
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
 * @method static Builder|AIPurchaseHistory newModelQuery()
 * @method static Builder|AIPurchaseHistory newQuery()
 * @method static Builder|AIPurchaseHistory query()
 * @method static Builder|AIPurchaseHistory whereAmount($value)
 * @method static Builder|AIPurchaseHistory whereContactNo($value)
 * @method static Builder|AIPurchaseHistory whereCreatedAt($value)
 * @method static Builder|AIPurchaseHistory whereCurrencyCode($value)
 * @method static Builder|AIPurchaseHistory whereFromWhere($value)
 * @method static Builder|AIPurchaseHistory whereId($value)
 * @method static Builder|AIPurchaseHistory whereIsManual($value)
 * @method static Builder|AIPurchaseHistory whereNetAmount($value)
 * @method static Builder|AIPurchaseHistory whereOrderId($value)
 * @method static Builder|AIPurchaseHistory wherePaidAmount($value)
 * @method static Builder|AIPurchaseHistory wherePaymentId($value)
 * @method static Builder|AIPurchaseHistory wherePaymentMethod($value)
 * @method static Builder|AIPurchaseHistory wherePaymentStatus($value)
 * @method static Builder|AIPurchaseHistory whereProductId($value)
 * @method static Builder|AIPurchaseHistory whereProductType($value)
 * @method static Builder|AIPurchaseHistory wherePromoCodeId($value)
 * @method static Builder|AIPurchaseHistory whereStatus($value)
 * @method static Builder|AIPurchaseHistory whereTransactionId($value)
 * @method static Builder|AIPurchaseHistory whereUpdatedAt($value)
 * @method static Builder|AIPurchaseHistory whereUserId($value)
 * @method static Builder|AIPurchaseHistory whereUsed($value)
 * @mixin Eloquent
 */
class AIPurchaseHistory extends Model
{
    protected $table = 'ai_purchase_history';
    protected $connection = 'crafty_ai_mysql';
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

<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\PurchaseHistory
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
 * @property int $isManual
 * @property int $payment_status
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserData|null $userData
 * @method static Builder|PurchaseHistory newModelQuery()
 * @method static Builder|PurchaseHistory newQuery()
 * @method static Builder|PurchaseHistory query()
 * @method static Builder|PurchaseHistory whereAmount($value)
 * @method static Builder|PurchaseHistory whereContactNo($value)
 * @method static Builder|PurchaseHistory whereCreatedAt($value)
 * @method static Builder|PurchaseHistory whereCurrencyCode($value)
 * @method static Builder|PurchaseHistory whereFromWhere($value)
 * @method static Builder|PurchaseHistory whereId($value)
 * @method static Builder|PurchaseHistory whereIsManual($value)
 * @method static Builder|PurchaseHistory whereNetAmount($value)
 * @method static Builder|PurchaseHistory whereOrderId($value)
 * @method static Builder|PurchaseHistory wherePaidAmount($value)
 * @method static Builder|PurchaseHistory wherePaymentId($value)
 * @method static Builder|PurchaseHistory wherePaymentMethod($value)
 * @method static Builder|PurchaseHistory wherePaymentStatus($value)
 * @method static Builder|PurchaseHistory whereProductId($value)
 * @method static Builder|PurchaseHistory whereProductType($value)
 * @method static Builder|PurchaseHistory wherePromoCodeId($value)
 * @method static Builder|PurchaseHistory whereStatus($value)
 * @method static Builder|PurchaseHistory whereTransactionId($value)
 * @method static Builder|PurchaseHistory whereUpdatedAt($value)
 * @method static Builder|PurchaseHistory whereUserId($value)
 * @mixin Eloquent
 */
class PurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'mysql';
    use HasFactory;

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
        'gclid',
        'isManual',
        'payment_status',
        'status',
    ];

    public function userData()
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}

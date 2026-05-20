<?php

namespace App\Models\Revenue;

use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\BusinessSupportPurchaseHistory
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
 * @method static Builder|BusinessSupportPurchaseHistory newModelQuery()
 * @method static Builder|BusinessSupportPurchaseHistory newQuery()
 * @method static Builder|BusinessSupportPurchaseHistory query()
 * @method static Builder|BusinessSupportPurchaseHistory whereAmount($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereContactNo($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereCreatedAt($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereCurrencyCode($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereFromWhere($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereId($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereIsManual($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereNetAmount($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereOrderId($value)
 * @method static Builder|BusinessSupportPurchaseHistory wherePaidAmount($value)
 * @method static Builder|BusinessSupportPurchaseHistory wherePaymentId($value)
 * @method static Builder|BusinessSupportPurchaseHistory wherePaymentMethod($value)
 * @method static Builder|BusinessSupportPurchaseHistory wherePaymentStatus($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereProductId($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereProductType($value)
 * @method static Builder|BusinessSupportPurchaseHistory wherePromoCodeId($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereStatus($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereTransactionId($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereUpdatedAt($value)
 * @method static Builder|BusinessSupportPurchaseHistory whereUserId($value)
 * @mixin Eloquent
 */
class BusinessSupportPurchaseHistory extends Model
{
    protected $table = 'business_support_purchase_history';
    protected $connection = 'crafty_revenue_mysql';
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

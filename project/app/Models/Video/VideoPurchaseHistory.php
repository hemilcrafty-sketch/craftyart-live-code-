<?php

namespace App\Models\Video;

use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoPurchaseHistory
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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserData|null $userData
 * @method static Builder|VideoPurchaseHistory newModelQuery()
 * @method static Builder|VideoPurchaseHistory newQuery()
 * @method static Builder|VideoPurchaseHistory query()
 * @method static Builder|VideoPurchaseHistory whereAmount($value)
 * @method static Builder|VideoPurchaseHistory whereContactNo($value)
 * @method static Builder|VideoPurchaseHistory whereCreatedAt($value)
 * @method static Builder|VideoPurchaseHistory whereCurrencyCode($value)
 * @method static Builder|VideoPurchaseHistory whereFromWhere($value)
 * @method static Builder|VideoPurchaseHistory whereId($value)
 * @method static Builder|VideoPurchaseHistory whereIsManual($value)
 * @method static Builder|VideoPurchaseHistory whereNetAmount($value)
 * @method static Builder|VideoPurchaseHistory whereOrderId($value)
 * @method static Builder|VideoPurchaseHistory wherePaidAmount($value)
 * @method static Builder|VideoPurchaseHistory wherePaymentId($value)
 * @method static Builder|VideoPurchaseHistory wherePaymentMethod($value)
 * @method static Builder|VideoPurchaseHistory wherePaymentStatus($value)
 * @method static Builder|VideoPurchaseHistory whereProductId($value)
 * @method static Builder|VideoPurchaseHistory whereProductType($value)
 * @method static Builder|VideoPurchaseHistory wherePromoCodeId($value)
 * @method static Builder|VideoPurchaseHistory whereStatus($value)
 * @method static Builder|VideoPurchaseHistory whereTransactionId($value)
 * @method static Builder|VideoPurchaseHistory whereUpdatedAt($value)
 * @method static Builder|VideoPurchaseHistory whereUserId($value)
 * @mixin Eloquent
 */
class VideoPurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'crafty_video_mysql';
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
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

}

<?php

namespace App\Models\Revenue;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * App\Models\Revenue\AutoPayTransaction
 *
 * @property int $id
 * @property string $user_id
 * @property string $order_id
 * @property string $subscription_id
 * @property string|null $transaction_id
 * @property double $amount
 * @property string $currency
 * @property string $transaction_type
 * @property string $status
 * @property string $payment_status
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string|null $failure_reason
 * @property array $webhook_data
 * @property bool $is_autopay
 * @property Carbon|null $notification_time
 * @property Carbon|null $payment_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserData|null $userData
 * @property-read UserSubscriptions|null $subscription
 * @method static Builder|AutoPayTransaction newModelQuery()
 * @method static Builder|AutoPayTransaction newQuery()
 * @method static Builder|AutoPayTransaction query()
 * @method static Builder|AutoPayTransaction with($value)
 * @method static Builder|AutoPayTransaction whereId($value)
 * @method static Builder|AutoPayTransaction whereOrderId($value)
 * @method static Builder|AutoPayTransaction whereSubscriptionId($value)
 * @method static Builder|AutoPayTransaction whereTransactionId($value)
 * @method static Builder|AutoPayTransaction whereCurrency($value)
 * @method static Builder|AutoPayTransaction whereTransactionType($value)
 * @method static Builder|AutoPayTransaction whereStatus($value)
 * @method static Builder|AutoPayTransaction wherePaymentStatus($value)
 * @method static Builder|AutoPayTransaction whereIsAutopay($value)
 * @method static Builder|AutoPayTransaction whereNotificationTime($value)
 * @method static Builder|AutoPayTransaction wherePaymentTime($value)
 * @method static Builder|AutoPayTransaction whereCreatedAt($value)
 * @method static Builder|AutoPayTransaction whereUpdatedAt($value)
 * @mixin Eloquent
 */

class AutoPayTransaction extends Model
{
    protected $table = 'autopay_transactions';
    protected $connection = 'crafty_revenue_mysql';

    protected $guarded = [];

    protected $casts = [
        'webhook_data' => 'array',
        'is_autopay' => 'boolean',
        'amount' => 'decimal:2'
    ];

    /**
     * Relationships
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscriptions::class, 'subscription_id', 'gateway_subscription_id');
    }

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

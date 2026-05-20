<?php

namespace App\Models\Revenue;

use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Revenue\UserSubscriptions
 *
 * @property int $id
 * @property string $user_id
 * @property string $plan_id
 * @property string $payment_gateway
 * @property string $gateway_subscription_id
 * @property string|null $gateway_customer_id
 * @property string|null $cancellation_reason
 * @property string $currency
 * @property int $amount
 * @property string $status
 * @property int $is_trial
 * @property string|null $trial_start
 * @property string|null $trial_end
 * @property string|null $current_start
 * @property string|null $current_end
 * @property int|null $total_count
 * @property int $paid_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserData|null $userData
 * @method static Builder|UserSubscriptions newModelQuery()
 * @method static Builder|UserSubscriptions newQuery()
 * @method static Builder|UserSubscriptions query()
 * @method static Builder|UserSubscriptions whereAmount($value)
 * @method static Builder|UserSubscriptions whereCancellationReason($value)
 * @method static Builder|UserSubscriptions whereCreatedAt($value)
 * @method static Builder|UserSubscriptions whereCurrency($value)
 * @method static Builder|UserSubscriptions whereCurrentEnd($value)
 * @method static Builder|UserSubscriptions whereCurrentStart($value)
 * @method static Builder|UserSubscriptions whereGatewayCustomerId($value)
 * @method static Builder|UserSubscriptions whereGatewaySubscriptionId($value)
 * @method static Builder|UserSubscriptions whereId($value)
 * @method static Builder|UserSubscriptions whereIsTrial($value)
 * @method static Builder|UserSubscriptions wherePaidCount($value)
 * @method static Builder|UserSubscriptions wherePaymentGateway($value)
 * @method static Builder|UserSubscriptions wherePlanId($value)
 * @method static Builder|UserSubscriptions whereStatus($value)
 * @method static Builder|UserSubscriptions whereTotalCount($value)
 * @method static Builder|UserSubscriptions whereTrialEnd($value)
 * @method static Builder|UserSubscriptions whereTrialStart($value)
 * @method static Builder|UserSubscriptions whereUpdatedAt($value)
 * @method static Builder|UserSubscriptions whereUserId($value)
 * @mixin Eloquent
 */

class UserSubscriptions extends Model
{

    protected $table = 'user_subscriptions';
    protected $connection = 'crafty_revenue_mysql';
    use HasFactory;

    protected $guarded = [];

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}

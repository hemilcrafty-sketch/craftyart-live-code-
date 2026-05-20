<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan\SubPlan
 *
 * @property int $id
 * @property string|null $string_id
 * @property string|null $plan_id
 * @property int $duration_id
 * @property array $plan_details
 * @property array $subscription_ids
 * @property int|null $deleted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PlanDuration|null $category
 * @property-read PlanDuration|null $duration
 * @property-read Plan|null $plan
 * @property-read Plan|null $planRef
 * @method static Builder|SubPlan with($value)
 * @method static Builder|SubPlan newModelQuery()
 * @method static Builder|SubPlan newQuery()
 * @method static Builder|SubPlan query()
 * @method static Builder|SubPlan whereCreatedAt($value)
 * @method static Builder|SubPlan whereDeleted($value)
 * @method static Builder|SubPlan whereDurationId($value)
 * @method static Builder|SubPlan whereId($value)
 * @method static Builder|SubPlan wherePlanDetails($value)
 * @method static Builder|SubPlan wherePlanId($value)
 * @method static Builder|SubPlan whereStringId($value)
 * @method static Builder|SubPlan whereUpdatedAt($value)
 * @method static Builder|SubPlan whereSubscriptionIds($value)
 * @mixin Eloquent
 */
class SubPlan extends Model
{
    public static array $PLAN_KEYS = ["inr_price", "usd_price", "inr_discount", "usd_discount", "inr_offer_price", "usd_offer_price", "additional_duration"];

    protected $connection = 'crafty_pricing_mysql';

    // Add the fields that can be mass-assigned
    protected $fillable = [
        "string_id",
        'plan_id',
        'deleted',
        'duration_id',
        'plan_details',
        'subscription_ids',
    ];

    // SubPlan.php
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function planRef(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id');
    }

    public function getPlanDetailsAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

    public function getSubscriptionIdsAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

}

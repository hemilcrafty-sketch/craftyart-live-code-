<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Pricing\OfferPackage
 *
 * @property int $id
 * @property string $string_id
 * @property string $plan_id
 * @property string|null $sub_plan_id
 * @property string $duration_id
 * @property string $package_name
 * @property array|null $plan_details  JSON: additional_duration, inr_price, inr_offer_price, inr_trial_days,
 *                                     inr_trial_price, inr_discount, usd_price, usd_offer_price,
 *                                     usd_trial_days, usd_trial_price, usd_discount
 * @property array|null $subscription_ids
 * @property int|null $custom_days
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PlanDuration|null $duration
 * @property-read Plan|null $plan
 * @mixin Eloquent
 */
class OfferPackage extends Model
{
    use HasFactory;

    protected $connection = 'crafty_pricing_mysql';
    protected $table = 'offer_package';

    protected $fillable = [
        'string_id',
        'plan_id',
        'sub_plan_id',
        'duration_id',
        'package_name',
        'custom_days',
        'plan_details',
        'subscription_ids',
        'status',
    ];

    protected $casts = [
        'plan_details' => 'array',
        'subscription_ids' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function subPlan(): BelongsTo
    {
        return $this->belongsTo(SubPlan::class, 'sub_plan_id', 'string_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getPlanDetailsAttribute($value): array
    {
        $defaults = [
            'additional_duration' => 0,
            'inr_price' => 0,
            'inr_offer_price' => 0,
            'inr_trial_days' => 0,
            'inr_trial_price' => 0,
            'inr_discount' => '0%',
            'usd_price' => 0,
            'usd_offer_price' => 0,
            'usd_trial_days' => 0,
            'usd_trial_price' => 0,
            'usd_discount' => '0%',
        ];

        if ($value === null) {
            return $defaults;
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        return array_merge($defaults, $decoded ?? []);
    }

    public function getSubscriptionIdsAttribute($value): array
    {
        return $value === null ? [] : (is_array($value) ? $value : json_decode($value, true));
    }

    public function offerPages()
    {
        return $this->hasMany(OfferPage::class, 'offer_package_id');
    }
}
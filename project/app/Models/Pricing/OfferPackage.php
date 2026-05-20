<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\OfferPackage
 *
 * @property int $id
 * @property string $string_id
 * @property string $plan_id
 * @property string $duration_id
 * @property string $bounce_code_id
 * @property string|null $sub_plan_id
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PlanDuration|null $duration
 * @property-read Plan|null $plan
 * @method static Builder|OfferPackage with($value)
 * @method static Builder|OfferPackage newModelQuery()
 * @method static Builder|OfferPackage newQuery()
 * @method static Builder|OfferPackage query()
 * @method static Builder|OfferPackage whereBounceCodeId($value)
 * @method static Builder|OfferPackage whereCreatedAt($value)
 * @method static Builder|OfferPackage whereDurationId($value)
 * @method static Builder|OfferPackage whereId($value)
 * @method static Builder|OfferPackage wherePlanId($value)
 * @method static Builder|OfferPackage whereStatus($value)
 * @method static Builder|OfferPackage whereStringId($value)
 * @method static Builder|OfferPackage whereSubPlanId($value)
 * @method static Builder|OfferPackage whereUpdatedAt($value)
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
        'duration_id',
        'sub_plan_id',
        'bounce_code_id',
        'status'
    ];

    // 🔹 Relationships
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
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

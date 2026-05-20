<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan\PlanCategoryFeature
 *
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PlanFeature> $plan_features
 * @property-read int|null $plan_features_count
 * @method static Builder|PlanCategoryFeature newModelQuery()
 * @method static Builder|PlanCategoryFeature newQuery()
 * @method static Builder|PlanCategoryFeature query()
 * @method static Builder|PlanCategoryFeature whereCreatedAt($value)
 * @method static Builder|PlanCategoryFeature whereId($value)
 * @method static Builder|PlanCategoryFeature whereName($value)
 * @method static Builder|PlanCategoryFeature whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PlanCategoryFeature extends Model
{
    use HasFactory;
    protected $connection = 'crafty_pricing_mysql';

    protected $fillable = ["name"];

    public function PlanFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class, "category_feature_id", "id");
    }
}

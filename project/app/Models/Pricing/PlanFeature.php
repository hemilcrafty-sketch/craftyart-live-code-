<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan\PlanFeature
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $category_feature_id
 * @property string|null $appearance_type
 * @property string|null $appearance_meta_data
 * @property string|null $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PlanCategoryFeature|null $categoryFeatures
 * @property-read Collection<int, Plan> $plans
 * @property-read int|null $plans_count
 * @method static Builder|PlanFeature newModelQuery()
 * @method static Builder|PlanFeature newQuery()
 * @method static Builder|PlanFeature query()
 * @method static Builder|PlanFeature whereAppearanceMetaData($value)
 * @method static Builder|PlanFeature whereAppearanceType($value)
 * @method static Builder|PlanFeature whereCategoryFeatureId($value)
 * @method static Builder|PlanFeature whereCreatedAt($value)
 * @method static Builder|PlanFeature whereDescription($value)
 * @method static Builder|PlanFeature whereId($value)
 * @method static Builder|PlanFeature whereName($value)
 * @method static Builder|PlanFeature whereSlug($value)
 * @method static Builder|PlanFeature whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PlanFeature extends Model
{
    use HasFactory;
    protected $connection = 'crafty_pricing_mysql';
    protected $fillable = [
        "name",
        "slug",
        "description",
        "category_feature_id",
        "appearance_type",
    ];
    public const ACTIVE = 1;
    public const INACTIVE = 0;
    public static array $STATUS = [
        self::ACTIVE => "Active",
        self::INACTIVE => "InActive"
    ];

    public function categoryFeatures(): BelongsTo
    {
        return $this->belongsTo(PlanCategoryFeature::class, "category_feature_id", "id");
    }
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'feature_plan', 'plan_feature_id', 'plan_id');
    }

}

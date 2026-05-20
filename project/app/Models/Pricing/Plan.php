<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan
 *
 * @property int $id
 * @property string $name e.g., Basic, Standard, Premium
 * @property string|null $sub_title
 * @property string|null $btn_name
 * @property int $is_recommended
 * @property string $string_id
 * @property int|null $sequence_number
 * @property string|null $icon
 * @property string|null $description
 * @property array $appearance
 * @property int $is_free_type
 * @property int $status 1:Active | 0:InActive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PlanFeature> $features
 * @property-read int|null $features_count
 * @property-read Collection<int, SubPlan> $subPlans
 * @property-read int|null $sub_plans_count
 * @method static Builder|Plan newModelQuery()
 * @method static Builder|Plan newQuery()
 * @method static Builder|Plan query()
 * @method static Builder|Plan whereAppearance($value)
 * @method static Builder|Plan whereBtnName($value)
 * @method static Builder|Plan whereCreatedAt($value)
 * @method static Builder|Plan whereDescription($value)
 * @method static Builder|Plan whereIcon($value)
 * @method static Builder|Plan whereId($value)
 * @method static Builder|Plan whereIsFreeType($value)
 * @method static Builder|Plan whereIsRecommended($value)
 * @method static Builder|Plan whereName($value)
 * @method static Builder|Plan whereSequenceNumber($value)
 * @method static Builder|Plan whereStatus($value)
 * @method static Builder|Plan whereStringId($value)
 * @method static Builder|Plan whereSubTitle($value)
 * @method static Builder|Plan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Plan extends Model
{
    use HasFactory;
    protected $connection = 'crafty_pricing_mysql';
    protected $fillable = [
        "id",
        "name",
        "btn_name",
        "sub_title",
        "is_recommended",
        "string_id",
        "sequence_number",
        "icon",
        "description",
        "appearance",
        "is_free_type",
        "status",
    ];


    private function finalResponse($msg, $access = false, $extra = []): array|string
    {
        return ['isAccess' => $access,
            "msg" => $msg,
            "data" => $extra];
    }

    private function limitResponse($limit, $type, $used, $planDetails = []): array|string
    {
        $canAccess = $used < $limit;
        $msg = $canAccess ? 'Access allowed' : "Your $type limit is expired";

        return $this->finalResponse($msg, $canAccess, [
            "data" => $planDetails,
        ]);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'feature_plan', 'string_id', 'plan_feature_id');
    }


    public function subPlans(): HasMany
    {
        return $this->hasMany(SubPlan::class, 'plan_id', 'id');
    }

    public function getAppearanceAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

    public function getPlanDetailsAttribute(): array
    {
        $appearance = [];
        foreach ($this->appearance as $item) {
            $subName = $item['sub_name'] ?? null;
            $metaFeatureValue = $item['meta_feature_value'] ?? [];
            $slug = $item['slug'] ?? null;
            $metaValue = $item['meta_value'] ?? null;
            if (!isset($metaFeatureValue[1]) || $metaFeatureValue[1] == null) {
                continue;
            }

            if (!empty($slug) && $metaValue == 1) {
                $appearance[] = [
                    'sub_name' => $subName,
                    'slug' => $slug,
                    'access_mode' => $metaFeatureValue[1],
                    'limit' => $metaFeatureValue[0] ?? 0,
                    'meta_value' => $metaValue,
                ];
            }
        }

        return $appearance;
    }


}

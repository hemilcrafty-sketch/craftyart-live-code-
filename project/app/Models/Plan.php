<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
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

}

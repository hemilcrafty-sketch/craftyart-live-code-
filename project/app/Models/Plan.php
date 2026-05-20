<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function features()
    {
        return $this->belongsToMany(PlanFeature::class, 'feature_plan', 'string_id', 'plan_feature_id');
    }


    public function subPlans()
    {
        return $this->hasMany(SubPlan::class, 'plan_id', 'id');
    }

}

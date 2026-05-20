<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanMetaDetail extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        "plan_id",
        "feature_id",
        "meta_feature_key",
        "meta_feature_value"
    ];
}

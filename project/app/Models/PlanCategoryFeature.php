<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCategoryFeature extends Model
{
    use HasFactory;
    protected $connection = 'mysql';

    protected $fillable = ["name"];

    public function Planfeatures()
    {
        return $this->hasMany(PlanFeature::class, "category_feature_id", "id");
    }
}

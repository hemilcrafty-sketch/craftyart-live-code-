<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryFeature extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ["name"];

    public function features()
    {
        return $this->hasMany(Feature::class,"category_feature_id","id");
    }
}

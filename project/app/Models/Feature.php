<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        "name",
        "description",
        "category_feature_id",
        "appearance_type",
    ];
    public  const ACTIVE = 1;
    public  const INACTIVE = 0;
    public static $STATUS = [
        self::ACTIVE => "Active",
        self::INACTIVE => "InActive"
    ];

    public function categoryFeatures()
    {
        return $this->belongsTo(CategoryFeature::class,"category_feature_id","id");
    }
    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }
}

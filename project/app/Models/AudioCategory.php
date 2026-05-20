<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class AudioCategory extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $fillable = [
        'parent_category_id',
        'emp_id',
        'name',
        'thumb',
        'sequence_number',
        'status',
    ];

    public function audioItem()
    {
        return $this->hasMany(AudioItem::class);
    }
    public function subcategories()
    {
        return $this->hasMany(AudioCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(AudioCategory::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = AudioCategory::where('parent_category_id', 0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = AudioCategory::where('id', $category)->get();
        if (empty($categories->toArray())) {
            $categories = AudioCategory::where('id_name', $category)->get();
        }

        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }

    protected function getSubcategoriesTree()
    {
        $subcategories = $this->subcategories;
        foreach ($subcategories as $subcategory) {
            $subcategory->subcategories = $subcategory->getSubcategoriesTree();
        }
        return $subcategories;
    }
}
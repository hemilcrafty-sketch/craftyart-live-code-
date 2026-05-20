<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class VectorCategory extends Model
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

    public function svgItem()
    {
        return $this->hasMany(VectorItem::class);
    }
    public function subcategories()
    {
        return $this->hasMany(VectorCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(VectorCategory::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = VectorCategory::where('parent_category_id', 0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = VectorCategory::where('id', $category)->get();
        if (empty($categories->toArray())) {
            $categories = VectorCategory::where('id_name', $category)->get();
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

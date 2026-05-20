<?php

namespace App\Models;

use App\Http\Controllers\HelperController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UpdateLogger;
use Illuminate\Support\Facades\Cache;

/**
 * @method static select(string $string)
 */
class NewCategory extends Model
{
    protected $connection = 'mysql';
    protected $table = 'new_categories';
    use HasFactory;
    use UpdateLogger;
    protected $fillable = [
        'string_id',
        'canonical_link',
        'cat_link',
        'category_name',
        'primary_keyword',
        'id_name',
        'tag_line',
        'meta_title',
        'h1_tag',
        'h2_tag',
        'meta_desc',
        'short_desc',
        'long_desc',
        'category_thumb',
        'banner',
        'contents',
        'faqs',
        'mockup',
        'app_id',
        'top_keywords',
        'cta',
        'sequence_number',
        'status',
        'priority',
        'frequency',
        'parent_category_id',
        'emp_id',
        'seo_emp_id',
        'fldr_str',
        'child_updated_at',
        'total_templates',
        'child_cat_ids'
    ];

    public function checkIsLive()
    {
        return $this->status === 1;
    }

    public function parentCategory($isStatus = null)
    {
        if ($isStatus != null) {
            return $this->belongsTo(NewCategory::class, 'parent_category_id', 'id')->where('status', $isStatus);
        } else {
            return $this->belongsTo(NewCategory::class, 'parent_category_id', 'id');
        }
    }


    public function getRootParentId()
    {
        $category = $this;
        while ($category->parentCategory && $category->parentCategory->{"parent_category_id"} != 0) {
            $category = $category->parentCategory;
        }
        return $category->{"parent_category_id"};
    }

    public static function getAllCategoriesWithSubcategories($isStatus = null)
    {
        if ($isStatus != null) {
            $categories = NewCategory::where('parent_category_id', 0)->where('status', $isStatus)->get();
        } else {
            $categories = NewCategory::where('parent_category_id', 0)->get();
        }
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }
    public static function getCategoriesWithSubcategories($category, $isStatus = null)
    {
        if (is_numeric($category)) {
            if ($isStatus != null) {
                $categories = NewCategory::where('id', $category)->where('status', $isStatus)->get();
            } else {
                $categories = NewCategory::where('id', $category)->get();
            }
        } else {
            if ($isStatus != null) {
                $categories = NewCategory::where('id_name', $category)->where('status', $isStatus)->get();
            } else {
                $categories = NewCategory::where('id_name', $category)->get();
            }
        }

        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }



    public static function getCategoriesWithSubcategories2(array $childCatIds, $isStatus = null)
    {
        $subCategories = NewCategory::whereIn('id', $childCatIds)
            ->where('status', 1)
            ->get();

        $extractSubcategories = function ($subcategories) use (&$extractSubcategories) {
            return collect($subcategories)->map(function ($subcategory) use ($extractSubcategories) {
                return [
                    'id' => $subcategory['id'],
                    'category_name' => $subcategory['category_name'],
                    'category_thumb' => HelperController::$mediaUrl . $subcategory['category_thumb'],
                    'id_name' => $subcategory['id_name'],
                    'status' => $subcategory['status'],
                ];
            })->toArray();
        };
        return $extractSubcategories($subCategories);
    }

    protected function getSubcategoriesTree($isStatus = null)
    {
        $subcategories = $this->subcategories($isStatus)->get();
        foreach ($subcategories as $subcategory) {
            $subcategory->subcategories = $subcategory->getSubcategoriesTree($isStatus);
        }
        return $subcategories;
    }

    public function subcategories($isStatus = null)
    {
        if ($isStatus != null) {
            return $this->hasMany(NewCategory::class, 'parent_category_id', 'id')->where('status', $isStatus);
        } else {
            return $this->hasMany(NewCategory::class, 'parent_category_id', 'id');
        }
    }


    public function newSearchTag(): HasMany
    {
        return $this->hasMany(NewSearchTag::class, 'new_category_id', 'id');
    }

    public static function getAllIMPCategoriesWithSubcategories($isStatus = null)
    {
        if ($isStatus != null) {
            $categories = NewCategory::where('parent_category_id', 0)->where('imp', 1)->where('status', $isStatus)->get();
        } else {
            $categories = NewCategory::where('parent_category_id', 0)->where('imp', 1)->get();
        }
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }

    protected static function booted()
    {
        static::created(function ($category) {
            $category->updateQuietly([
                'cat_link' => self::buildCategoryHierarchyPath($category->id),
                'child_cat_ids' => json_encode([])
            ]);
            self::clearCategoryCache($category);
        });

        static::updated(function ($category) {
            $oldValues = $category->getOriginal();
            $newValues = $category->getAttributes();

            $oldIdName = $oldValues['id_name'];
            $newIdName = $newValues['id_name'];

            $oldParentId = $oldValues['parent_category_id'];
            $newParentId = $newValues['parent_category_id'];

            self::clearCategoryCache($category);

            // Case 1: id_name changed - update self cat_link and all descendants
            if ($oldIdName !== $newIdName) {
                self::updateCatLinkForIdNameChange($category);
            }

            // Case 2: parent_category_id changed - full hierarchy update
            if ($oldParentId != $newParentId) {
                if ($oldParentId != 0) {
                    self::updateHierarchyFromRoot($oldParentId);
                }
                if ($newParentId != 0) {
                    self::updateHierarchyFromRoot($newParentId);
                } else {
                    self::updateHierarchyFromRoot($category->id);
                }
            } else {
                self::updateHierarchyFromRoot($newParentId == null || $newParentId == 0 ? $category->id : $newParentId);
            }
        });
    }

    /**
     * Clear cache for a category (all related tags)
     */
    private static function clearCategoryCache($category): void
    {
        Cache::tags(["category_$category->cat_link"])->flush();
        Cache::tags(["category_$category->id_name"])->flush();
        Cache::tags(["category_$category->id"])->flush();
        Cache::tags(['categories'])->flush();
    }

    /**
     * Get direct children of a category
     */
    private static function getChildren($categoryId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('parent_category_id', $categoryId)->get();
    }

    /**
     * Update cat_link when id_name changes
     * Updates self cat_link and all descendants recursively
     */
    private static function updateCatLinkForIdNameChange($category): void
    {
        $newCatLink = self::buildCategoryHierarchyPath($category->id);
        $category->updateQuietly(['cat_link' => $newCatLink]);
        self::clearCategoryCache($category);

        // Update all direct children's cat_link and their descendants
        foreach (self::getChildren($category->id) as $child) {
            self::updateCatLinkForIdNameChange($child);
        }
    }

    /**
     * Update entire hierarchy from root when parent changes
     */
    private static function updateHierarchyFromRoot($categoryId): void
    {
        $rootParent = self::findRootParent($categoryId);
        if (!$rootParent)
            return;

        self::updateCatLinkAndChildCatIds($rootParent);
        self::updateTemplateCountsFromBottom($rootParent);
        self::clearHierarchyCacheRecursive($rootParent->id);
    }

    /**
     * Find the root parent (where parent_category_id = 0)
     */
    private static function findRootParent($categoryId): ?NewCategory
    {
        $category = self::find($categoryId);

        while ($category && $category->parent_category_id != 0) {
            $category = self::find($category->parent_category_id);
        }

        return $category;
    }

    /**
     * Update cat_link and child_cat_ids for entire hierarchy starting from a category
     */
    private static function updateCatLinkAndChildCatIds($category): void
    {
        $newCatLink = self::buildCategoryHierarchyPath($category->id);
        $directChildren = self::getChildren($category->id);
        $childIds = $directChildren->pluck('id')->toArray();

        $category->updateQuietly([
            'cat_link' => $newCatLink,
            'child_cat_ids' => json_encode($childIds)
        ]);

        // Recursively update all direct children
        foreach ($directChildren as $child) {
            self::updateCatLinkAndChildCatIds($child);
        }
    }

    /**
     * Update template counts from bottom to top
     */
    private static function updateTemplateCountsFromBottom($category): void
    {
        $directChildren = self::getChildren($category->id);

        // First, recursively update all children
        foreach ($directChildren as $child) {
            self::updateTemplateCountsFromBottom($child);
        }

        // Now update this category's total_templates
        self::updateCategoryTemplateCount($category);
    }

    /**
     * Update total_templates for a single category
     */
    private static function updateCategoryTemplateCount($category): void
    {
        // Get own template count from Design table
        $ownCount = Design::where('new_category_id', $category->id)
            ->whereStatus(1)
            ->count();

        // Get all direct children's template counts
        $directChildren = self::getChildren($category->id);
        $childrenCount = $directChildren->sum('total_templates');

        // Update total_templates
        $totalCount = $ownCount + $childrenCount;
        if ($category->total_templates !== $totalCount) {
            $category->updateQuietly(['total_templates' => $totalCount]);
        }
    }

    /**
     * Clear cache for a category and all its descendants recursively
     */
    private static function clearHierarchyCacheRecursive($categoryId): void
    {
        $category = self::find($categoryId);
        if (!$category)
            return;

        self::clearCategoryCache($category);

        // Clear cache for all direct children
        foreach (self::getChildren($categoryId) as $child) {
            self::clearHierarchyCacheRecursive($child->id);
        }
    }

    /**
     * Build complete hierarchy path for a category
     * Traverses up the parent chain to build full path
     */
    private static function buildCategoryHierarchyPath($categoryId): string
    {
        $path = [];
        $category = self::find($categoryId);

        while ($category) {
            array_unshift($path, $category->id_name);

            if ($category->parent_category_id == 0) {
                break;
            }

            $category = self::find($category->parent_category_id);
        }

        return implode('/', $path);
    }
}

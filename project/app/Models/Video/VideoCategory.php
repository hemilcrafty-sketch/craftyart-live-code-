<?php

namespace App\Models\Video;

use App\Http\Controllers\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Video\VideoCategory
 *
 * @property int $id
 * @property int $total_templates
 * @property int|null $parent_category_id
 * @property int $emp_id
 * @property int|null $seo_emp_id
 * @property string|null $string_id
 * @property string $category_name
 * @property string|null $category_title
 * @property string $slug
 * @property string|null $canonical_link
 * @property string|null $meta_title
 * @property string|null $primary_keyword
 * @property string|null $h1_tag
 * @property string|null $tag_line
 * @property string|null $meta_desc
 * @property string|null $short_desc
 * @property string|null $h2_tag
 * @property string|null $long_desc
 * @property string $category_thumb
 * @property string|null $mockup
 * @property string|null $banner
 * @property string|null $contents
 * @property string|null $faqs
 * @property array|null $top_keywords
 * @property int $sequence_number
 * @property int $status
 * @property int $imp
 * @property int $no_index
 * @property string|null $fldr_str
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read VideoCategory|null $parentCategory
 * @property-read Collection<int, VideoCategory> $subcategories
 * @property-read int|null $subcategories_count
 * @property-read Collection<int, \App\Models\Video\VideoTemplate> $videoTemplates
 * @property-read int|null $video_templates_count
 * @method static Builder|VideoCategory newModelQuery()
 * @method static Builder|VideoCategory newQuery()
 * @method static Builder|VideoCategory query()
 * @method static Builder|VideoCategory whereBanner($value)
 * @method static Builder|VideoCategory whereCanonicalLink($value)
 * @method static Builder|VideoCategory whereCategoryName($value)
 * @method static Builder|VideoCategory whereCategoryThumb($value)
 * @method static Builder|VideoCategory whereCategoryTitle($value)
 * @method static Builder|VideoCategory whereContents($value)
 * @method static Builder|VideoCategory whereCreatedAt($value)
 * @method static Builder|VideoCategory whereEmpId($value)
 * @method static Builder|VideoCategory whereFaqs($value)
 * @method static Builder|VideoCategory whereFldrStr($value)
 * @method static Builder|VideoCategory whereH1Tag($value)
 * @method static Builder|VideoCategory whereH2Tag($value)
 * @method static Builder|VideoCategory whereId($value)
 * @method static Builder|VideoCategory whereImp($value)
 * @method static Builder|VideoCategory whereLongDesc($value)
 * @method static Builder|VideoCategory whereMetaDesc($value)
 * @method static Builder|VideoCategory whereMetaTitle($value)
 * @method static Builder|VideoCategory whereMockup($value)
 * @method static Builder|VideoCategory whereNoIndex($value)
 * @method static Builder|VideoCategory whereParentCategoryId($value)
 * @method static Builder|VideoCategory wherePrimaryKeyword($value)
 * @method static Builder|VideoCategory whereSeoEmpId($value)
 * @method static Builder|VideoCategory whereSequenceNumber($value)
 * @method static Builder|VideoCategory whereShortDesc($value)
 * @method static Builder|VideoCategory whereSlug($value)
 * @method static Builder|VideoCategory whereStatus($value)
 * @method static Builder|VideoCategory whereStringId($value)
 * @method static Builder|VideoCategory whereTagLine($value)
 * @method static Builder|VideoCategory whereTopKeywords($value)
 * @method static Builder|VideoCategory whereTotalTemplates($value)
 * @method static Builder|VideoCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoCategory extends Model
{
    protected $table = 'main_categories';
    protected $connection = 'crafty_video_mysql';
    use HasFactory;

    protected $fillable = [
        'category_name',
        'slug',
        'canonical_link',
        'seo_emp_id',
        'meta_title',
        'primary_keyword',
        'h1_tag',
        'tag_line',
        'meta_desc',
        'short_desc',
        'h2_tag',
        'long_desc',
        'category_thumb',
        'mockup',
        'banner',
        'contents',
        'faqs',
        'top_keywords',
        'parent_category_id',
        'sequence_number',
        'status',
        'emp_id',
        'fldr_str',
        'total_templates'
    ];

    protected $casts = [
        'top_keywords' => 'array',
    ];


    public function videoTemplates()
    {
        return $this->hasMany(VideoTemplate::class, 'category_id', 'id');
    }

    public function subcategories()
    {
        return $this->hasMany(VideoCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(VideoCategory::class, 'parent_category_id', 'id');
    }

    /**
     * Root main_categories id for size/theme filters (parity with NewCategory::getRootParentId).
     */
    public function getRootParentId(): int
    {
        $category = $this;
        while ($category->parentCategory && (int) $category->parentCategory->parent_category_id !== 0) {
            $category = $category->parentCategory;
        }

        return (int) $category->parent_category_id;
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = VideoCategory::where('parent_category_id', 0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = VideoCategory::where('id', $category)->get();

        if (empty($categories->toArray())) {
            $categories = VideoCategory::where('slug', $category)->get();
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

    protected static function booted()
    {
        static::created(function (VideoCategory $category) {
            self::generateStringId($category->id, $category->string_id);
            VideoSlugHistory::updateVideoSlug(id: $category->id, slug: $category->slug, type: "category");
            self::clearCategoryCache($category);
        });

        static::updated(function (VideoCategory $category) {
            self::generateStringId($category->id, $category->string_id);
            VideoSlugHistory::updateVideoSlug(id: $category->id, slug: $category->slug, type: "category", update: true);
            $oldValues = $category->getOriginal();
            $newValues = $category->getAttributes();
            $oldParentId = $oldValues['parent_category_id'];
            $newParentId = $newValues['parent_category_id'];
            self::clearCategoryCache($category);

            // parent_category_id changed - full hierarchy update
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

    private static function generateStringId($catId, $stringID): void
    {
        if ($stringID && $stringID != "")
            return;

        $generateStringID = HelperController::generateStringIds(source: VideoCategory::class);
        $category = VideoCategory::whereId($catId)->first();
        $category->string_id = $generateStringID;
        $category->saveQuietly();
    }

    /**
     * Clear cache for a category (all related tags)
     */
    private static function clearCategoryCache($category): void
    {
        Cache::tags(["vi_category_$category->id"])->flush();
        Cache::tags(['vi_category'])->flush();
    }

    /**
     * Get direct children of a category
     */
    private static function getChildren($categoryId): Collection
    {
        return self::where('parent_category_id', $categoryId)->get();
    }

    /**
     * Update entire hierarchy from root when parent changes
     */
    private static function updateHierarchyFromRoot($categoryId): void
    {
        $rootParent = self::findRootParent($categoryId);
        if (!$rootParent)
            return;

        self::updateTemplateCountsFromBottom($rootParent);
        self::clearHierarchyCacheRecursive($rootParent->id);
    }

    /**
     * Find the root parent (where parent_category_id = 0)
     */
    private static function findRootParent($categoryId): ?VideoCategory
    {
        $category = self::find($categoryId);

        while ($category && $category->parent_category_id != 0) {
            $category = self::find($category->parent_category_id);
        }

        return $category;
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
        $ownCount = VideoTemplate::where('category_id', $category->id)
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

}

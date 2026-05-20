<?php

namespace App\Models;

use App\Http\Controllers\HelperController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;
use Illuminate\Support\Facades\Cache;

class Design extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;

    /**
     * Mass assignable (e.g. freelancer publish from DesignerSystemController → crafty_db.designs).
     */
    protected $fillable = [
        'string_id',
        'creator_id',
        'creator_draft_id',
        'emp_id',
        'designer_id',
        'seo_emp_id',
        'seo_assigner_id',
        'app_id',
        'category_id',
        'new_category_id',
        'sub_cat_id',
        'style_id',
        'interest_id',
        'lang_id',
        'post_name',
        'id_name',
        'h2_tag',
        'meta_title',
        'meta_description',
        'description',
        'canonical_link',
        'post_thumb',
        'additional_thumb',
        'thumb_array',
        'default_thumb_pos',
        'video_thumb',
        'related_tags',
        'new_related_tags',
        'special_keywords',
        'ratio',
        'width',
        'height',
        'designs',
        'fab_designs',
        'total_pages',
        'size',
        'template_size',
        'animation',
        'theme_id',
        'color_id',
        'religion_id',
        'auto_create',
        'orientation',
        'cta',
        'has_bug',
        'is_fix',
        'is_premium',
        'is_freemium',
        'editor_choice',
        'status',
        'deleted',
        'latest',
        'no_index',
        'pinned',
        'priority',
        'frequency',
        'views',
        'web_views',
        'trending_views',
    ];

    public function getsize()
    {
        // Define the hasOne relationship
        return $this->hasOne(Size::class, 'width_ration', 'width')
            ->where('height_ration', $this->height);
    }


    public function parent()
    {
        return $this->belongsTo(Design::class, 'category_id');
    }

    public function getPaperSizeAttribute()
    {
        return $this->getsize ? $this->getsize->paper_size : 'Unknown Size';
    }

    public function newCategory()
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id');
    }

    public function getPageLinkAttribute($value): string
    {
        return HelperController::$webPageUrl . "templates/p/$this->id_name";
    }

    protected static function booted()
    {
        static::created(function ($design) {
            // When a design is created, update the category hierarchy
            if ($design->new_category_id) {
                $design->adjustCategoryCount($design->new_category_id);
            }
        });

        static::deleted(function ($design) {
            // When a design is deleted, update the category hierarchy
            if ($design->new_category_id) {
                $design->adjustCategoryCount($design->new_category_id);
            }
        });

        static::updated(function ($design) {
            $oldValues = $design->getOriginal();
            $newValues = $design->getAttributes();
            $newCategoryId = $newValues['new_category_id'];
            $oldCategoryId = $oldValues['new_category_id'];
            $newTags = json_decode($newValues['new_related_tags'] ?? "[]", true);
            $oldTags = json_decode($oldValues['new_related_tags'] ?? "[]", true);
            $design->afterUpdate($design->id, $newCategoryId, $oldCategoryId, $newTags, $oldTags);
        });
    }

    public function afterUpdate($designId, $newCategoryId, $oldCategoryId, $newTags = [], $oldTags = []): void
    {
        $this->updateDesignCount($newCategoryId, $oldCategoryId, $newTags, $oldTags);
        $this->clearCacheByCat($oldCategoryId);
        $this->clearCacheByCat($newCategoryId);
        $this->clearCacheByTag($newTags, $oldTags);
    }

    public function updateDesignCount($newCatId, $oldCategoryId = null, array $newTags = [], array $oldTags = []): void
    {
        if ($oldCategoryId && $oldCategoryId != 0) {
            $this->adjustCategoryCount($oldCategoryId);
        }
        if ($newCatId && $newCatId != 0 && $oldCategoryId !== $newCatId) {
            $this->adjustCategoryCount($newCatId);
        }
        // Adjust tag counts
        $this->adjustTagCounts($oldTags);
        $this->adjustTagCounts($newTags);
    }

    public function clearCacheByCat($catId): void
    {
        if ($catId != 0) {
            $category = NewCategory::where('id', $catId)->where('status', 1)->first();
            if (!empty($category) && !empty($category->parent_category_id) && $category->parent_category_id != 0) {
                $this->safeFlushCacheTags(["category_$category->cat_link"]);
                $this->safeFlushCacheTags(["category_$category->id"]);
                $this->safeFlushCacheTags(["category_$category->id_name"]);
                $parent = NewCategory::where('id', $category->parent_category_id)->where('status', 1)->first();
                if ($parent) {
                    $this->safeFlushCacheTags(["category_$parent->cat_link"]);
                    $this->safeFlushCacheTags(["category_$parent->id"]);
                    $this->safeFlushCacheTags(["category_$parent->id_name"]);
                }
            }
        }
    }

    public function clearCacheByTag($newTagIds, $oldTagIds): void
    {
        if (is_string($newTagIds)) {
            $decoded = json_decode($newTagIds, true);
            $newTagIds = $decoded !== null ? $decoded : explode(',', $newTagIds);
        }
        $newTagIds = is_array($newTagIds) ? $newTagIds : [];
        $oldTagIds = is_array($oldTagIds) ? $oldTagIds : [];
        $newTagIds = array_map('strval', $newTagIds);
        $oldTagIds = array_map('strval', $oldTagIds);
        $removed = array_diff($oldTagIds, $newTagIds);
        $added = array_diff($newTagIds, $oldTagIds);
        $changed = array_values(array_unique(array_merge($removed, $added)));
        foreach ($changed as $tagId) {
            $tag = NewSearchTag::find($tagId);
            if ($tag) {
                $this->safeFlushCacheTags(["kp_$tag->id"]);
            }
        }
    }

    protected function adjustCategoryCount(int $categoryId): void
    {
        $category = NewCategory::where('id', $categoryId)->where('status', 1)->first();

        if ($category) {
            // Count designs directly in this category
            $ownCount = self::where('new_category_id', $categoryId)
                ->whereStatus(1)
                ->count();

            // Get all direct children's template counts
            $directChildren = NewCategory::where('parent_category_id', $categoryId)
                ->where('status', 1)
                ->get();
            $childrenCount = $directChildren->sum('total_templates');

            // Update total_templates
            $totalCount = $ownCount + $childrenCount;
            if ($category->total_templates !== $totalCount) {
                $category->updateQuietly(['total_templates' => $totalCount]);
            }

            // Update all ancestors up the hierarchy
            if (!empty($category->parent_category_id) && $category->parent_category_id != 0) {
                $this->adjustCategoryCount($category->parent_category_id);
            }
        }
    }

    protected function adjustTagCounts(array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            $tag = NewSearchTag::find($tagId);
            if ($tag) {
                // Count designs that have this tag (assumes `new_related_tags` is JSON)
                $count = self::whereJsonContains('new_related_tags', $tagId)->count();
                $tag->total_templates = $count;
                $tag->saveQuietly();
            }
        }
    }

    protected static function safeFlushCacheTags(array $tags): void
    {
        try {
            // Check if the current cache driver supports tagging
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags($tags)->flush();
            }
        } catch (\Exception $e) {
            // Silently fail - caching is not critical for functionality
        }
    }

}

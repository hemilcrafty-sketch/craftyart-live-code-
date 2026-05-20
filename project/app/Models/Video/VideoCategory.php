<?php

namespace App\Models\Video;

use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\QueryManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Cache;
use Closure;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoCategory
 *
 * @property int $id
 * @property string $string_id
 * @property int|null $parent_category_id
 * @property string|null $child_cat_ids
 * @property int $total_templates
 * @property int $emp_id
 * @property string $category_name
 * @property string|null $fldr_str
 * @property string|null $cat_link
 * @property string $slug
 * @property string|null $canonical_link
 * @property string|null $full_slug
 * @property int|null $seo_emp_id
 * @property string|null $meta_title
 * @property string|null $primary_keyword
 * @property string|null $h1_tag
 * @property string|null $tag_line
 * @property string|null $category_title
 * @property string|null $meta_desc
 * @property string|null $short_desc
 * @property string|null $h2_tag
 * @property string|null $long_desc
 * @property string $category_thumb
 * @property string|null $mockup
 * @property string|null $banner
 * @property int|null $app_id
 * @property string|null $contents
 * @property string|null $faqs
 * @property array|null $top_keywords
 * @property int $sequence_number
 * @property int $status
 * @property int $imp
 * @property int $no_index
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read array $parent
 * @property-read VideoCategory|null $parentCategory
 * @property-read Collection<int, VideoCategory> $subcategories
 * @property-read Collection<int, VideoVirtualCategory> $virtualPages
 * @property-read int|null $subcategories_count
 * @property-read Collection<int, VideoTemplate> $videoTemplates
 * @property-read int|null $video_templates_count
 * @method static Builder|VideoCategory newModelQuery()
 * @method static Builder|VideoCategory newQuery()
 * @method static Builder|VideoCategory query()
 * @method static Builder|VideoCategory find($value)
 * @method static Builder|VideoCategory whereStringId($value)
 * @method static Builder|VideoCategory whereAppId($value)
 * @method static Builder|VideoCategory whereBanner($value)
 * @method static Builder|VideoCategory whereCanonicalLink($value)
 * @method static Builder|VideoCategory whereCatLink($value)
 * @method static Builder|VideoCategory whereSlug($value)
 * @method static Builder|VideoCategory whereCategoryName($value)
 * @method static Builder|VideoCategory whereCategoryThumb($value)
 * @method static Builder|VideoCategory whereCategoryTitle($value)
 * @method static Builder|VideoCategory whereChildCatIds($value)
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
 * @method static Builder|VideoCategory whereStatus($value)
 * @method static Builder|VideoCategory whereTagLine($value)
 * @method static Builder|VideoCategory whereTopKeywords($value)
 * @method static Builder|VideoCategory whereTotalTemplates($value)
 * @method static Builder|VideoCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class VideoCategory extends Model
{
    public static array $defaultCategorySelect = ['id', 'parent_category_id', 'string_id', 'category_name', 'category_thumb', 'banner', 'mockup', 'cat_link', 'child_cat_ids', 'imp'];

    protected $table = 'main_categories';
    protected $connection = 'crafty_video_mysql';
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'top_keywords' => 'array',
    ];

    public function videoTemplates(): HasMany
    {
        return $this->hasMany(VideoTemplate::class, 'category_id', 'id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(VideoCategory::class, 'parent_category_id', 'id');
    }

    public function virtualPages(): HasMany
    {
        return $this->hasMany(VideoVirtualCategory::class, 'parent_category_id', 'id');
    }


    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(VideoCategory::class, 'parent_category_id', 'id');
    }

    public function getCatLinkAttribute($value): string
    {
        return '/templates/' . $value;
    }

    public function getSlugAttribute($value): string
    {
        return '/' . $value;
    }

    public function getFullSlugAttribute(): string
    {
        return "https://www.myvideoinvites.com$this->slug";
    }

    /**
     * @return VideoCategory|null Returns an associative array based on `VideoCategory` model with tree data, or null if not found.
     */
    public static function findId(
        ?array $select = [],
        ?int   $isStatus = null,
        ?int   $id = null,
        ?int   $parentId = null,
        ?bool  $getChild = true
    ): ?VideoCategory
    {
        $cacheKey = 'vi_categories_id_' . md5(json_encode([
                'select' => $select,
                'status' => $isStatus,
                'id' => $id,
                'parentId' => $parentId,
                'getChild' => $getChild,
            ]));

        $callback = function () use ($select, $isStatus, $id, $parentId, $getChild) {

            $select = self::resolveSelect($select);

            $query = VideoCategory::query()->when($select, fn($q) => $q->select($select));
            $query->whereId($id);
            if ($parentId !== null) $query->whereParentCategoryId($parentId);
            if ($isStatus !== null) $query->whereStatus($isStatus);
            $query->where('total_templates', '>', 0);

            /** @var VideoCategory $category */
            $category = $query->first();

            if (!$category) return null;
            if (!$getChild) return $category;

            $parentCat = null;
            if ($category->parent_category_id != 0) $parentCat = VideoCategory::find($category->parent_category_id);

            $buildTree = self::getChilds(isStatus: $isStatus, parentCat: $parentCat, select: $select);

            return $buildTree($category);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags(["vi_category_$id"])->remember(
                $cacheKey,
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }

    /**
     * @return VideoCategory|null Returns an associative array based on `VideoCategory` model with tree data, or null if not found.
     */
    public static function findBySlug(
        ?array  $select = [],
        ?int    $isStatus = null,
        ?string $id = null,
        ?int    $parentId = null,
        ?bool   $getChild = true
    ): ?VideoCategory
    {

        $cacheKey = 'vi_categories_slug_' . md5(json_encode([
                'select' => $select,
                'status' => $isStatus,
                'id' => $id,
                'parentId' => $parentId,
                'getChild' => $getChild,
            ]));

        $callback = function () use ($select, $isStatus, $id, $parentId, $getChild) {
            $select = self::resolveSelect($select);

            $query = VideoCategory::query()->when($select, fn($q) => $q->select($select));
            $query->whereSlug($id);
            if ($parentId !== null) $query->whereParentCategoryId($parentId);
            if ($isStatus !== null) $query->whereStatus($isStatus);
            $query->where('total_templates', '>', 0);

            /** @var VideoCategory $category */
            $category = $query->first();

            if (!$category) return null;
            if (!$getChild) return $category;

            $parentCat = null;
            if ($category->parent_category_id != 0) $parentCat = VideoCategory::find($category->parent_category_id);

            $buildTree = self::getChilds(isStatus: $isStatus, parentCat: $parentCat, select: $select);

            return $buildTree($category);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags(["vi_category_$id"])->remember(
                $cacheKey,
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }

    public static function getAllCatsWithChilds(
        ?array $select = [],
        ?array $filters = [],
        int    $limit = 10,
        ?int   $page = null): LengthAwarePaginator
    {

        $cacheKey = 'categories_with_childs_' . md5(json_encode([
                'select' => $select,
                'filters' => $filters,
                'limit' => $limit,
                'page' => $page,
            ]));

        $callback = function () use ($select, $filters, $limit, $page) {
            $select = self::resolveSelect($select);

            $query = VideoCategory::query()->when($select, fn($q) => $q->select($select))->where('parent_category_id', 0);
            $query->where('total_templates', '>', 0);
            QueryManager::getQuery($query, $filters);

            $query->orderBy('sequence_number', 'ASC');

            $isPaginated = $page !== null;

            $rootCategories = $isPaginated
                ? $query->paginate($limit, ['*'], 'page', $page)
                : $query->get();

            $parentIds = $isPaginated
                ? $rootCategories->getCollection()->pluck('id')
                : $rootCategories->pluck('id');

            $buildTree = self::getChilds(isStatus: $filters['status'] ?? null, parentIds: $parentIds->toArray(), select: $select);

            if ($isPaginated) {
                $rootCategories->getCollection()->transform($buildTree);
                return $rootCategories;
            }

            $built = $rootCategories->map($buildTree);

            return new LengthAwarePaginator(
                $built,
                $built->count(),
                max($built->count(), 1),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags(["vi_categories"])->remember(
                $cacheKey,
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }

    public static function getParentCategories(
        ?array $select = [],
        ?int   $isStatus = null,
        ?int   $isImp = null,
        int    $limit = 10,
        ?int   $page = null): LengthAwarePaginator
    {

        $select = self::resolveSelect($select);

        $query = VideoCategory::query()->when($select, fn($q) => $q->select($select))->where('parent_category_id', 0);

        if ($isStatus !== null) $query->whereStatus($isStatus);
        if ($isImp !== null) $query->whereImp($isImp);

        $query->where('total_templates', '>', 0);
        $query->orderBy('sequence_number', 'ASC');

        $isPaginated = $page !== null;

        $rootCategories = $isPaginated
            ? $query->paginate($limit, ['*'], 'page', $page)
            : $query->get();

        if ($rootCategories instanceof LengthAwarePaginator) {
            return $rootCategories;
        }

        return new LengthAwarePaginator(
            $rootCategories,
            $rootCategories->count(),
            $rootCategories->count(),
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

    }

    // Method to get the root parent ID
    public function getRootParentId()
    {
        $category = $this;
        while ($category->parentCategory && $category->parentCategory->{"parent_category_id"} != 0) {
            $category = $category->parentCategory;
        }
        return $category->{"parent_category_id"};
    }

    private static function resolveSelect(?array $select): ?array
    {
        if ($select === null) return self::$defaultCategorySelect;
        if (count($select) === 0) return null;
        return $select;
    }

    /**
     * @param int|null $isStatus
     * @param VideoCategory|null $parentCat
     * @param int[]|null $parentIds
     * @param array|null $select
     * @return Closure
     */

    private static function getChilds(?int $isStatus = null, ?VideoCategory $parentCat = null, ?array $parentIds = null, ?array $select = null): Closure
    {
        $childQuery = VideoCategory::query()->when($select, fn($q) => $q->select($select));

        if ($parentCat !== null) $childQuery->whereParentCategoryId($parentCat->id);
        elseif ($parentIds !== null) $childQuery->whereIn('parent_category_id', $parentIds);

        if ($isStatus !== null) $childQuery->whereStatus($isStatus);

        $childQuery->where('total_templates', '>', 0);

        $childrenGrouped = $childQuery->orderBy('sequence_number')->get()->groupBy('parent_category_id');

        return function ($category) use ($childrenGrouped, $parentCat) {
            $catArr = $category;

            if ($parentCat) {
                $catArr['parent'] = [
                    'id' => $parentCat->id,
                    'category_name' => $parentCat->category_name,
                    'cat_link' => $parentCat->cat_link,
                ];
            }

            $catArr['subcategories'] = ($childrenGrouped[$category->id] ?? collect())->map(function ($child) use ($category, $parentCat) {
                $childArr = $child;
                $childArr['parent'] = [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'cat_link' => $category->cat_link,
                ];


                return $childArr;
            })->values();

            return $catArr;
        };
    }

    private static function getChildsBackup(?int $isStatus = null, ?VideoCategory $parentCat = null, ?array $parentIds = null, ?array $select = null): Closure
    {
        $childQuery = VideoCategory::query()->when($select, fn($q) => $q->select($select));

        if ($parentCat !== null) {
            $childQuery->whereParentCategoryId($parentCat->id);
        } elseif ($parentIds !== null) {
            $childQuery->whereIn('parent_category_id', $parentIds);
        }

        if ($isStatus !== null) {
            $childQuery->whereStatus($isStatus);
        }

        $allCategories = $childQuery->orderBy('sequence_number', 'ASC')->get();
        $allById = $allCategories->keyBy('id');
        $grouped = $allCategories->groupBy('parent_category_id');

        return function ($category, $depth = 0, $parentTrail = []) use (&$buildTree, $grouped, $allById, $parentCat) {
            $categoryArray = $category;

            $currentTrail = [...$parentTrail, $categoryArray['id_name'] ?? ''];

            // Add depth (optional)
            $categoryArray['depth'] = $depth;

            // Add parent info
            if ($parentCat) {
                $categoryArray['parent'] = [
                    'id' => $parentCat->id,
                    'name' => $parentCat->category_name,
                    'cat_link' => $parentCat->cat_link,
                ];
            } else {
                $categoryArray['parent'] = isset($allById[$category->parent_category_id]) ? [
                    'id' => $allById[$category->parent_category_id]->id,
                    'name' => $allById[$category->parent_category_id]->category_name,
                ] : null;
            }

            // Recursively build children
            $children = ($grouped[$category->id] ?? collect())->map(function ($child) use (&$buildTree, $depth, $currentTrail) {
                return $buildTree($child, $depth + 1, $currentTrail);
            })->values();

//            $allChildIds = $children->flatMap(function ($child) {
//                return array_merge(
//                    [$child['id']],
//                    $child['child_cat_ids'] ?? []
//                );
//            })->unique()->values()->all();

//            $categoryArray['child_cat_ids'] = $allChildIds;

//            $categoryArray['child_cat_ids'] = $categoryArray['child_cat_ids'] ? json_decode($categoryArray['child_cat_ids'], true) : null;
            $categoryArray['subcategories'] = $children;

            return $categoryArray;
        };
    }
}

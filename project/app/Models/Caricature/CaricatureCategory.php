<?php

namespace App\Models\Caricature;

use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\QueryManager;
use Cache;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;


/**
 * App\Models\Caricature\CaricatureCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string $cat_link
 * @property array $child_cat_ids
 * @property string $id_name
 * @property string|null $canonical_link
 * @property string $string_id
 * @property int|null $emp_id
 * @property string|null $seo_emp_id
 * @property string $meta_title
 * @property string $primary_keyword
 * @property string|null $meta_desc
 * @property string $tag_line
 * @property string|null $h1_tag
 * @property string|null $h2_tag
 * @property string|null $short_desc
 * @property string|null $long_desc
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $category_name
 * @property string|null $size
 * @property string $category_thumb
 * @property string|null $banner
 * @property string|null $mockup
 * @property mixed|null $top_keywords
 * @property string $fldr_str
 * @property int|null $imp
 * @property int|null $sequence_number
 * @property int|null $total_templates
 * @property int|null $status
 * @property int|null $no_index
 * @property int|null $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $child_updated_at
 * @property CaricatureCategory[] $subcategories
 * @property array $parent
 * @property-read Collection<int, CaricatureCategory> $children
 * @property-read int|null $children_count
 * @property-read int|null $new_search_tag_count
 * @method static Builder|CaricatureCategory newModelQuery()
 * @method static Builder|CaricatureCategory newQuery()
 * @method static Builder|CaricatureCategory query()
 * @method static Builder|CaricatureCategory whereBanner($value)
 * @method static Builder|CaricatureCategory whereCanonicalLink($value)
 * @method static Builder|CaricatureCategory whereCatLink($value)
 * @method static Builder|CaricatureCategory whereCategoryName($value)
 * @method static Builder|CaricatureCategory whereCategoryThumb($value)
 * @method static Builder|CaricatureCategory whereChildCatIds($value)
 * @method static Builder|CaricatureCategory whereChildUpdatedAt($value)
 * @method static Builder|CaricatureCategory whereContents($value)
 * @method static Builder|CaricatureCategory whereCreatedAt($value)
 * @method static Builder|CaricatureCategory whereDeleted($value)
 * @method static Builder|CaricatureCategory whereEmpId($value)
 * @method static Builder|CaricatureCategory whereFaqs($value)
 * @method static Builder|CaricatureCategory whereFldrStr($value)
 * @method static Builder|CaricatureCategory whereH1Tag($value)
 * @method static Builder|CaricatureCategory whereH2Tag($value)
 * @method static Builder|CaricatureCategory whereId($value)
 * @method static Builder|CaricatureCategory whereIdName($value)
 * @method static Builder|CaricatureCategory whereImp($value)
 * @method static Builder|CaricatureCategory whereLongDesc($value)
 * @method static Builder|CaricatureCategory whereMetaDesc($value)
 * @method static Builder|CaricatureCategory whereMetaTitle($value)
 * @method static Builder|CaricatureCategory whereMockup($value)
 * @method static Builder|CaricatureCategory whereNoIndex($value)
 * @method static Builder|CaricatureCategory whereParentCategoryId($value)
 * @method static Builder|CaricatureCategory wherePrimaryKeyword($value)
 * @method static Builder|CaricatureCategory whereSeoEmpId($value)
 * @method static Builder|CaricatureCategory whereSequenceNumber($value)
 * @method static Builder|CaricatureCategory whereShortDesc($value)
 * @method static Builder|CaricatureCategory whereSize($value)
 * @method static Builder|CaricatureCategory whereStatus($value)
 * @method static Builder|CaricatureCategory whereStringId($value)
 * @method static Builder|CaricatureCategory whereTagLine($value)
 * @method static Builder|CaricatureCategory whereTopKeywords($value)
 * @method static Builder|CaricatureCategory whereTotalTemplates($value)
 * @method static Builder|CaricatureCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CaricatureCategory extends Model
{
    public static string $preCacheTag = "cari_";
    public static array $defaultNewCategorySelect = ['id', 'parent_category_id', 'id_name', 'string_id', 'category_name', 'category_thumb', 'banner', 'mockup', 'cat_link', 'child_cat_ids', 'imp'];
    protected $connection = 'crafty_caricature_mysql';
    protected $table = 'categories';

    use HasFactory;

//    public function parent(): BelongsTo
//    {
//        return $this->belongsTo(NewCategory::class, 'parent_category_id');
//    }
//
//    public function children(): HasMany
//    {
//        return $this->hasMany(NewCategory::class, 'parent_category_id');
//    }


    public function getCatLinkAttribute($value): string
    {
        return HelperController::$webPageUrl . 'caricature/' . $value;
    }

    public function getSitemapUrlAttribute(): string
    {
        return "caricature/{$this->getRawOriginal('cat_link')}";
    }

    public function getChildCatIdsAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

    /**
     * @return CaricatureCategory|null Returns an associative array based on `NewCategory` model with tree data, or null if not found.
     */
    public static function findId(
        ?array $select = [],
        ?int   $isStatus = null,
        ?int   $id = null,
        ?int   $parentId = null,
        ?bool  $getChild = true
    ): ?CaricatureCategory
    {
        $cacheKey = 'categories_id_' . md5(json_encode([
                'select' => $select,
                'status' => $isStatus,
                'id' => $id,
                'parentId' => $parentId,
                'getChild' => $getChild,
            ]));

        $callback = function () use ($select, $isStatus, $id, $parentId, $getChild) {

            $select = self::resolveSelect($select);

            $query = CaricatureCategory::query()->when($select, fn($q) => $q->select($select));
            $query->whereId($id);
            if ($parentId !== null) $query->whereParentCategoryId($parentId);
            if ($isStatus !== null) $query->whereStatus($isStatus);
            $query->where('total_templates', '>', 0);
            $category = $query->first();

            if (!$category) return null;
            if (!$getChild) return $category;

            $parentCat = null;
            if ($category->parent_category_id != 0) $parentCat = CaricatureCategory::find($category->parent_category_id);

            $buildTree = self::getChilds(isStatus: $isStatus, parentCat: $parentCat, select: $select);

            return $buildTree($category);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags([CaricatureCategory::$preCacheTag . "category_$id"])->remember(
                $cacheKey,
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }

    /**
     * @return CaricatureCategory|null Returns an associative array based on `NewCategory` model with tree data, or null if not found.
     */
    public static function findIdName(
        ?array  $select = [],
        ?int    $isStatus = null,
        ?string $id = null,
        ?int    $parentId = null,
        ?bool   $getChild = true
    ): ?CaricatureCategory
    {

        $cacheKey = 'categories_id_name_' . md5(json_encode([
                'select' => $select,
                'status' => $isStatus,
                'id' => $id,
                'parentId' => $parentId,
                'getChild' => $getChild,
            ]));

        $callback = function () use ($select, $isStatus, $id, $parentId, $getChild) {
            $select = self::resolveSelect($select);

            $query = CaricatureCategory::query()->when($select, fn($q) => $q->select($select));
            $query->whereIdName($id);
            if ($parentId !== null) $query->whereParentCategoryId($parentId);
            if ($isStatus !== null) $query->whereStatus($isStatus);
            $query->where('total_templates', '>', 0);
            $category = $query->first();

            if (!$category) return null;
            if (!$getChild) return $category;

            $parentCat = null;
            if ($category->parent_category_id != 0) $parentCat = CaricatureCategory::find($category->parent_category_id);

            $buildTree = self::getChilds(isStatus: $isStatus, parentCat: $parentCat, select: $select);

            return $buildTree($category);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags([CaricatureCategory::$preCacheTag . "category_$id"])->remember(
                $cacheKey,
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }

    public static function findCatLink(
        ?array  $select = [],
        ?int    $isStatus = null,
        ?string $id = null,
        ?int    $parentId = null,
        ?bool   $getChild = true
    ): ?CaricatureCategory
    {

        $cacheKey = 'categories_id_name_' . md5(json_encode([
                'select' => $select,
                'status' => $isStatus,
                'catLink' => $id,
                'parentId' => $parentId,
                'getChild' => $getChild,
            ]));

        $callback = function () use ($select, $isStatus, $id, $parentId, $getChild) {
            $select = self::resolveSelect($select);

            $query = CaricatureCategory::query()->when($select, fn($q) => $q->select($select));
            $query->whereCatLink($id);
            if ($isStatus !== null) $query->whereStatus($isStatus);
            $query->where('total_templates', '>', 0);
            $category = $query->first();

            if (!$category) return null;
            if (!$getChild) return $category;

            $parentCat = null;
            if ($category->parent_category_id != 0) $parentCat = CaricatureCategory::find($category->parent_category_id);

            $buildTree = self::getChilds(isStatus: $isStatus, parentCat: $parentCat, select: $select);

            return $buildTree($category);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags([CaricatureCategory::$preCacheTag . "category_$id"])->remember(
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

            $query = CaricatureCategory::query()->when($select, fn($q) => $q->select($select))->where('parent_category_id', 0);
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
            return Cache::tags([CaricatureCategory::$preCacheTag . "categories"])->remember(
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

        $query = CaricatureCategory::query()->when($select, fn($q) => $q->select($select))->where('parent_category_id', 0);

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
        if ($select === null) return self::$defaultNewCategorySelect;
        if (count($select) === 0) return null;
        return $select;
    }

    /**
     * @param int|null $isStatus
     * @param CaricatureCategory|null $parentCat
     * @param int[]|null $parentIds
     * @param array|null $select
     * @return Closure
     */

    private static function getChilds(?int $isStatus = null, ?CaricatureCategory $parentCat = null, ?array $parentIds = null, ?array $select = null): Closure
    {
        $childQuery = CaricatureCategory::query()->when($select, fn($q) => $q->select($select));

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

    private static function getChildsBackup(?int $isStatus = null, ?CaricatureCategory $parentCat = null, ?array $parentIds = null, ?array $select = null): Closure
    {
        $childQuery = CaricatureCategory::query()->when($select, fn($q) => $q->select($select));

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

        $buildTree = function ($category, $depth = 0, $parentTrail = []) use (&$buildTree, $grouped, $allById, $parentCat) {
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

        return $buildTree;
    }

}

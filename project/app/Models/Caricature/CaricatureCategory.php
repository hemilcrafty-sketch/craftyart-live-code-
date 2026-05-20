<?php

namespace App\Models\Caricature;

use App\Http\Controllers\Api\CaricatureController;
use App\Models\Attire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Traits\UpdateLogger;

/**
 * App\Models\CaricatureCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string|null $cat_link
 * @property mixed|null $child_cat_ids
 * @property string $id_name
 * @property string|null $canonical_link
 * @property mixed|null $related_tags
 * @property string $string_id
 * @property int|null $emp_id
 * @property string|null $seo_emp_id
 * @property int|null $app_id
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
 * @property mixed|null $cta
 * @property int|null $imp
 * @property int|null $sequence_number
 * @property int|null $total_templates
 * @property int|null $status
 * @property int|null $no_index
 * @property int|null $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $child_updated_at
 * @property CaricatureCategory $parentCategory
 * @property Attire $attires
 * @property-read int|null $attires_count
 * @method static Builder|CaricatureCategory newModelQuery()
 * @method static Builder|CaricatureCategory newQuery()
 * @method static Builder|CaricatureCategory query()
 * @method static Builder|CaricatureCategory whereAppId($value)
 * @method static Builder|CaricatureCategory whereBanner($value)
 * @method static Builder|CaricatureCategory whereCanonicalLink($value)
 * @method static Builder|CaricatureCategory whereCatLink($value)
 * @method static Builder|CaricatureCategory whereCategoryName($value)
 * @method static Builder|CaricatureCategory whereCategoryThumb($value)
 * @method static Builder|CaricatureCategory whereChildCatIds($value)
 * @method static Builder|CaricatureCategory whereChildUpdatedAt($value)
 * @method static Builder|CaricatureCategory whereContents($value)
 * @method static Builder|CaricatureCategory whereCreatedAt($value)
 * @method static Builder|CaricatureCategory whereCta($value)
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
 * @method static Builder|CaricatureCategory whereRelatedTags($value)
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
    protected $connection = 'crafty_caricature_mysql';
    protected $table = 'categories';
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
    ];

    public function parentCategory()
    {
        return $this->belongsTo(CaricatureCategory::class, 'parent_category_id');
    }

    public function attires()
    {
        return $this->hasMany(Attire::class, 'category_id');
    }

    public static function getAllCategoriesWithSubcategories($isStatus = null)
    {
        if ($isStatus != null) {
            $categories = CaricatureCategory::where('parent_category_id', 0)->where('status', $isStatus)->get();
        } else {
            $categories = CaricatureCategory::where('parent_category_id', 0)->get();
        }
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
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
            return $this->hasMany(CaricatureCategory::class, 'parent_category_id', 'id')->where('status', $isStatus);
        } else {
            return $this->hasMany(CaricatureCategory::class, 'parent_category_id', 'id');
        }
    }

}
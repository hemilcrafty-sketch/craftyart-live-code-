<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\VirtualCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string $id_name
 * @property string|null $string_id
 * @property int|null $emp_id
 * @property int|null $app_id
 * @property string|null $canonical_link
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $h1_tag
 * @property string|null $h2_tag
 * @property string|null $short_desc
 * @property string|null $long_desc
 * @property string|null $tag_line
 * @property string $category_name
 * @property string|null $size
 * @property string $category_thumb
 * @property string|null $mockup
 * @property string|null $banner
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $fldr_str
 * @property string $virtual_query
 * @property string|null $top_keywords
 * @property string|null $cta
 * @property string|null $primary_keyword
 * @property int $imp
 * @property int $sequence_number
 * @property int $no_index
 * @property int $status
 * @property int|null $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VirtualCategory newModelQuery()
 * @method static Builder|VirtualCategory newQuery()
 * @method static Builder|VirtualCategory query()
 * @method static Builder|VirtualCategory whereAppId($value)
 * @method static Builder|VirtualCategory whereBanner($value)
 * @method static Builder|VirtualCategory whereCanonicalLink($value)
 * @method static Builder|VirtualCategory whereCategoryName($value)
 * @method static Builder|VirtualCategory whereCategoryThumb($value)
 * @method static Builder|VirtualCategory whereContents($value)
 * @method static Builder|VirtualCategory whereCreatedAt($value)
 * @method static Builder|VirtualCategory whereCta($value)
 * @method static Builder|VirtualCategory whereDeleted($value)
 * @method static Builder|VirtualCategory whereEmpId($value)
 * @method static Builder|VirtualCategory whereFaqs($value)
 * @method static Builder|VirtualCategory whereFldrStr($value)
 * @method static Builder|VirtualCategory whereH1Tag($value)
 * @method static Builder|VirtualCategory whereH2Tag($value)
 * @method static Builder|VirtualCategory whereId($value)
 * @method static Builder|VirtualCategory whereIdName($value)
 * @method static Builder|VirtualCategory whereSlug($value)
 * @method static Builder|VirtualCategory whereImp($value)
 * @method static Builder|VirtualCategory whereLongDesc($value)
 * @method static Builder|VirtualCategory whereMetaDesc($value)
 * @method static Builder|VirtualCategory whereMetaTitle($value)
 * @method static Builder|VirtualCategory whereMockup($value)
 * @method static Builder|VirtualCategory whereNoIndex($value)
 * @method static Builder|VirtualCategory whereParentCategoryId($value)
 * @method static Builder|VirtualCategory wherePrimaryKeyword($value)
 * @method static Builder|VirtualCategory whereSequenceNumber($value)
 * @method static Builder|VirtualCategory whereShortDesc($value)
 * @method static Builder|VirtualCategory whereSize($value)
 * @method static Builder|VirtualCategory whereStatus($value)
 * @method static Builder|VirtualCategory whereStringId($value)
 * @method static Builder|VirtualCategory whereTagLine($value)
 * @method static Builder|VirtualCategory whereTopKeywords($value)
 * @method static Builder|VirtualCategory whereUpdatedAt($value)
 * @method static Builder|VirtualCategory whereVirtualQuery($value)
 * @mixin \Eloquent
 */
class VirtualCategory extends Model
{
    protected $connection = 'mysql';
    protected $table = 'virtual_categories';

    use HasFactory;

    public function getSitemapUrlAttribute(): string
    {
        return "templates/$this->id_name";
    }
}

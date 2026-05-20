<?php

namespace App\Models\Video;

use App\Http\Controllers\HelperController;
use App\Models\User;
use App\Traits\UpdateLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoVirtualCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string $slug
 * @property string|null $string_id
 * @property int|null $emp_id
 * @property string|null $seo_emp_id
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
 * @property-read User|null $assignedSeo
 * @method static Builder|VideoVirtualCategory newModelQuery()
 * @method static Builder|VideoVirtualCategory newQuery()
 * @method static Builder|VideoVirtualCategory query()
 * @method static Builder|VideoVirtualCategory whereBanner($value)
 * @method static Builder|VideoVirtualCategory whereCanonicalLink($value)
 * @method static Builder|VideoVirtualCategory whereCategoryName($value)
 * @method static Builder|VideoVirtualCategory whereCategoryThumb($value)
 * @method static Builder|VideoVirtualCategory whereContents($value)
 * @method static Builder|VideoVirtualCategory whereCreatedAt($value)
 * @method static Builder|VideoVirtualCategory whereCta($value)
 * @method static Builder|VideoVirtualCategory whereDeleted($value)
 * @method static Builder|VideoVirtualCategory whereEmpId($value)
 * @method static Builder|VideoVirtualCategory whereFaqs($value)
 * @method static Builder|VideoVirtualCategory whereFldrStr($value)
 * @method static Builder|VideoVirtualCategory whereH1Tag($value)
 * @method static Builder|VideoVirtualCategory whereH2Tag($value)
 * @method static Builder|VideoVirtualCategory whereId($value)
 * @method static Builder|VideoVirtualCategory whereImp($value)
 * @method static Builder|VideoVirtualCategory whereLongDesc($value)
 * @method static Builder|VideoVirtualCategory whereMetaDesc($value)
 * @method static Builder|VideoVirtualCategory whereMetaTitle($value)
 * @method static Builder|VideoVirtualCategory whereMockup($value)
 * @method static Builder|VideoVirtualCategory whereNoIndex($value)
 * @method static Builder|VideoVirtualCategory whereParentCategoryId($value)
 * @method static Builder|VideoVirtualCategory wherePrimaryKeyword($value)
 * @method static Builder|VideoVirtualCategory whereSeoEmpId($value)
 * @method static Builder|VideoVirtualCategory whereSequenceNumber($value)
 * @method static Builder|VideoVirtualCategory whereShortDesc($value)
 * @method static Builder|VideoVirtualCategory whereSize($value)
 * @method static Builder|VideoVirtualCategory whereSlug($value)
 * @method static Builder|VideoVirtualCategory whereStatus($value)
 * @method static Builder|VideoVirtualCategory whereStringId($value)
 * @method static Builder|VideoVirtualCategory whereTagLine($value)
 * @method static Builder|VideoVirtualCategory whereTopKeywords($value)
 * @method static Builder|VideoVirtualCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoVirtualCategory extends Model
{
    protected $connection = 'crafty_video_mysql';
    use HasFactory;
    use UpdateLogger;
    protected $table = 'virtual_categories';

    protected $guarded = [];

    public function assignedSeo()
    {
        return $this->belongsTo(User::class, 'seo_emp_id');
    }

    protected static function booted()
    {
        static::created(function (VideoVirtualCategory $videoVirtualCategory) {
            self::generateStringId($videoVirtualCategory->id, $videoVirtualCategory->string_id);
            VideoSlugHistory::updateVideoSlug(id: $videoVirtualCategory->id, slug: $videoVirtualCategory->slug, type: "virtual_page");
        });

        static::updated(function (VideoVirtualCategory $videoVirtualCategory) {
            self::generateStringId($videoVirtualCategory->id, $videoVirtualCategory->string_id);
            VideoSlugHistory::updateVideoSlug(id: $videoVirtualCategory->id, slug: $videoVirtualCategory->slug, type: "virtual_page", update: true);
        });
    }

    private static function generateStringId($catId, $stringID): void
    {
        if ($stringID && $stringID != "")
            return;

        $generateStringID = HelperController::generateStringIds(source: VideoVirtualCategory::class);
        $category = VideoVirtualCategory::whereId($catId)->first();
        $category->string_id = $generateStringID;
        $category->saveQuietly();
    }

}

<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\Video\VideoTemplate
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $seo_emp_id
 * @property int|null $seo_assigner_id
 * @property int $relation_id
 * @property string $string_id
 * @property int $category_id
 * @property int $virtual_category_id
 * @property string|null $video_name
 * @property string $folder_name
 * @property string|null $video_thumb
 * @property string|null $video_url
 * @property string $video_zip_url
 * @property int $width
 * @property int $height
 * @property int $watermark_height
 * @property int $template_type
 * @property int|null $do_front_lottie
 * @property string|null $editable_image
 * @property string|null $editable_text
 * @property array $keyword
 * @property string|null $id_name
 * @property string|null $slug
 * @property string|null $full_slug
 * @property string|null $h2_tag
 * @property string|null $canonical_link
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $description
 * @property string|null $lang_id
 * @property string|null $theme_id
 * @property string|null $style_id
 * @property string|null $orientation
 * @property int|null $template_size
 * @property string|null $religion_id
 * @property string|null $interest_id
 * @property int|null $change_text
 * @property int $change_music
 * @property int $encrypted
 * @property string|null $encryption_key
 * @property int $is_premium
 * @property int $is_freemium
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $color_ids
 * @property int $pages
 * @property int $status
 * @property int $is_deleted
 * @property int $views
 * @property int $daily_views
 * @property int $weekly_views
 * @property int|null $creation
 * @property int|null $daily_creation
 * @property int $weekly_creation
 * @property int $no_index
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read VideoCategory|null $videoCat
 * @property-read VideoVirtualCategory|null $virtualCat
 * @method static Builder|VideoTemplate newModelQuery()
 * @method static Builder|VideoTemplate newQuery()
 * @method static Builder|VideoTemplate query()
 * @method static Builder|VideoTemplate with($value)
 * @method static Builder|VideoTemplate whereCanonicalLink($value)
 * @method static Builder|VideoTemplate whereCategoryId($value)
 * @method static Builder|VideoTemplate whereChangeMusic($value)
 * @method static Builder|VideoTemplate whereChangeText($value)
 * @method static Builder|VideoTemplate whereColorIds($value)
 * @method static Builder|VideoTemplate whereCreatedAt($value)
 * @method static Builder|VideoTemplate whereCreation($value)
 * @method static Builder|VideoTemplate whereDailyCreation($value)
 * @method static Builder|VideoTemplate whereDailyViews($value)
 * @method static Builder|VideoTemplate whereDescription($value)
 * @method static Builder|VideoTemplate whereDoFrontLottie($value)
 * @method static Builder|VideoTemplate whereEditableImage($value)
 * @method static Builder|VideoTemplate whereEditableText($value)
 * @method static Builder|VideoTemplate whereEmpId($value)
 * @method static Builder|VideoTemplate whereEncrypted($value)
 * @method static Builder|VideoTemplate whereEncryptionKey($value)
 * @method static Builder|VideoTemplate whereEndDate($value)
 * @method static Builder|VideoTemplate whereFolderName($value)
 * @method static Builder|VideoTemplate whereH2Tag($value)
 * @method static Builder|VideoTemplate whereHeight($value)
 * @method static Builder|VideoTemplate whereId($value)
 * @method static Builder|VideoTemplate whereIdName($value)
 * @method static Builder|VideoTemplate whereInterestId($value)
 * @method static Builder|VideoTemplate whereIsDeleted($value)
 * @method static Builder|VideoTemplate whereIsFreemium($value)
 * @method static Builder|VideoTemplate whereIsPremium($value)
 * @method static Builder|VideoTemplate whereKeyword($value)
 * @method static Builder|VideoTemplate whereLangId($value)
 * @method static Builder|VideoTemplate whereMetaDescription($value)
 * @method static Builder|VideoTemplate whereMetaTitle($value)
 * @method static Builder|VideoTemplate whereNoIndex($value)
 * @method static Builder|VideoTemplate whereOrientation($value)
 * @method static Builder|VideoTemplate wherePages($value)
 * @method static Builder|VideoTemplate whereRelationId($value)
 * @method static Builder|VideoTemplate whereReligionId($value)
 * @method static Builder|VideoTemplate whereSeoAssignerId($value)
 * @method static Builder|VideoTemplate whereSeoEmpId($value)
 * @method static Builder|VideoTemplate whereSlug($value)
 * @method static Builder|VideoTemplate whereStartDate($value)
 * @method static Builder|VideoTemplate whereStatus($value)
 * @method static Builder|VideoTemplate whereStringId($value)
 * @method static Builder|VideoTemplate whereStyleId($value)
 * @method static Builder|VideoTemplate whereTemplateSize($value)
 * @method static Builder|VideoTemplate whereTemplateType($value)
 * @method static Builder|VideoTemplate whereThemeId($value)
 * @method static Builder|VideoTemplate whereUpdatedAt($value)
 * @method static Builder|VideoTemplate whereVideoName($value)
 * @method static Builder|VideoTemplate whereVideoThumb($value)
 * @method static Builder|VideoTemplate whereVideoUrl($value)
 * @method static Builder|VideoTemplate whereVideoZipUrl($value)
 * @method static Builder|VideoTemplate whereViews($value)
 * @method static Builder|VideoTemplate whereVirtualCategoryId($value)
 * @method static Builder|VideoTemplate whereWatermarkHeight($value)
 * @method static Builder|VideoTemplate whereWeeklyCreation($value)
 * @method static Builder|VideoTemplate whereWeeklyViews($value)
 * @method static Builder|VideoTemplate whereWidth($value)
 * @mixin \Eloquent
 */
class VideoTemplate extends Model
{
    protected $table = 'items';
    protected $connection = 'crafty_video_mysql';
    use HasFactory;

    protected $guarded = [];

    public function videoCat()
    {
        return $this->belongsTo(VideoCategory::class, 'category_id', 'id');
    }

    public function virtualCat()
    {
        return $this->belongsTo(VideoVirtualCategory::class, 'virtual_category_id', 'id');
    }

    public function keywordNames(): array
    {
        return VideoSearchTag::select('name')->whereIn('id', $this->keyword)->pluck('name')->toArray();
    }

    public function getSlugAttribute($value): string
    {
        if (empty($value)) {
            return "/templates/p/$this->string_id";
        }
        return "/$value";
    }

    public function getFullSlugAttribute(): string
    {
        return "https://www.myvideoinvites.com$this->slug";
    }

    public function getKeywordAttribute($value): array
    {
        return match (true) {
            is_array($value) => $value,
            is_string($value) => json_decode($value, true) ?? [],
            default => [],
        };
    }
}

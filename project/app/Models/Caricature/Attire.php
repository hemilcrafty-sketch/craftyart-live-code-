<?php

namespace App\Models\Caricature;

use App\Http\Controllers\Utils\HelperController;
use App\Models\Design;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Attire
 *
 * @property int $id
 * @property mixed $json
 * @property array $template_thumbs
 * @property string|null $preview_url
 * @property string $attire_url
 * @property string $thumbnail_url
 * @property string $coordinate_image
 * @property Carbon $created_at
 * @property string $string_id
 * @property string $id_name
 * @property string $post_name
 * @property string|null $meta_title
 * @property int|null $meta_description
 * @property string|null $h2_tag
 * @property string|null $long_desc
 * @property string|null $contents
 * @property string|null $faqs
 * @property string|null $canonical_link
 * @property int $head_count
 * @property string|null $theme_id
 * @property string|null $style_id
 * @property string|null $religion_id
 * @property string|null $related_tags
 * @property int $pinned
 * @property int $editor_choice
 * @property int $is_premium
 * @property int $is_freemium
 * @property int $status
 * @property int $no_index
 * @property string $fldr_str
 * @property int $category_id
 * @property int $emp_id
 * @property Carbon $updated_at
 * @property int $deleted
 * @property int $views
 * @property int $trending_views
 * @property int $width
 * @property int $height
 * @property string $skin_color
 * @property string $page_link
 * @method static Builder|Attire newModelQuery()
 * @method static Builder|Attire newQuery()
 * @method static Builder|Attire query()
 * @method static Builder|Attire whereAttireUrl($value)
 * @method static Builder|Attire whereCanonicalLink($value)
 * @method static Builder|Attire whereCategoryId($value)
 * @method static Builder|Attire whereContents($value)
 * @method static Builder|Attire whereCoordinateImage($value)
 * @method static Builder|Attire whereCreatedAt($value)
 * @method static Builder|Attire whereLongDesc($value)
 * @method static Builder|Attire whereEditorChoice($value)
 * @method static Builder|Attire whereEmpId($value)
 * @method static Builder|Attire whereFaqs($value)
 * @method static Builder|Attire whereFldrStr($value)
 * @method static Builder|Attire whereH2Tag($value)
 * @method static Builder|Attire whereHeadCount($value)
 * @method static Builder|Attire whereId($value)
 * @method static Builder|Attire whereIdName($value)
 * @method static Builder|Attire whereIsFreemium($value)
 * @method static Builder|Attire whereIsPremium($value)
 * @method static Builder|Attire whereJson($value)
 * @method static Builder|Attire whereMetaDescription($value)
 * @method static Builder|Attire whereMetaTitle($value)
 * @method static Builder|Attire whereNoIndex($value)
 * @method static Builder|Attire wherePinned($value)
 * @method static Builder|Attire wherePostName($value)
 * @method static Builder|Attire wherePreviewUrl($value)
 * @method static Builder|Attire whereRelatedTags($value)
 * @method static Builder|Attire whereReligionId($value)
 * @method static Builder|Attire whereStatus($value)
 * @method static Builder|Attire whereStringId($value)
 * @method static Builder|Attire whereStyleId($value)
 * @method static Builder|Attire whereThemeId($value)
 * @method static Builder|Attire whereThumbnailUrl($value)
 * @method static Builder|Attire whereUpdatedAt($value)
 * @property-read CaricatureCategory|null $category
 * @method static Builder|Attire whereDeleted($value)
 * @method static Builder|Attire whereTrendingViews($value)
 * @method static Builder|Attire whereViews($value)
 * @method static Builder|Attire whereHeight($value)
 * @method static Builder|Attire whereWidth($value)
 * @mixin Eloquent
 */
class Attire extends Model
{
    protected $connection = 'crafty_caricature_mysql';
    protected $table = 'attire';
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(CaricatureCategory::class, 'category_id');
    }

    public function getTemplateThumbsAttribute(): array
    {
        $thumbs = [];
        $datas = Design::select(['post_thumb'])->whereJsonContains('caricature_ids', $this->string_id)->get();
        foreach ($datas as $data) {
            $thumbs[] = HelperController::$mediaUrl . $data->post_thumb;
        }
        return $thumbs;
    }

    public function getPreviewUrlAttribute($value): string
    {
        return HelperController::$mediaUrl . $value;
    }

    public function getThumbnailUrlAttribute($value): string
    {
        return HelperController::$mediaUrl . $value;
    }

    public function getPageLinkAttribute($value): string
    {
        return HelperController::$webPageUrl . "caricature/p/$this->id_name";
    }

    public function getSitemapUrlAttribute(): string
    {
        return "caricature/p/$this->id_name";
    }

}

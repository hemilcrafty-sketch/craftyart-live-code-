<?php

namespace App\Models;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Design
 *
 * @property int $id
 * @property string|null $string_id
 * @property string|null $id_name
 * @property string|null $canonical_link
 * @property string|null $h2_tag
 * @property string|null $creator_id
 * @property string|null $creator_draft_id
 * @property int|null $emp_id
 * @property int $app_id
 * @property int|null $category_id
 * @property int $new_category_id
 * @property string|null $sub_cat_id
 * @property string|null $style_id
 * @property string|null $interest_id
 * @property string|null $lang_id
 * @property array $caricature_ids
 * @property string $post_name
 * @property string|null meta_title
 * @property string|null $additional_thumb
 * @property string $post_thumb
 * @property string|null $thumb_array
 * @property int $default_thumb_pos
 * @property string|null $video_thumb
 * @property string $ratio
 * @property int $width
 * @property int $height
 * @property string|null $designs
 * @property string|null $fab_designs
 * @property int|null $total_pages
 * @property string|null $description
 * @property string|null $meta_description
 * @property string|null $related_tags
 * @property array $new_related_tags
 * @property string|null $special_keywords
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $size
 * @property string|null $template_size
 * @property int|null $animation
 * @property string|null $theme_id
 * @property string|null $color_id
 * @property string|null $religion_id
 * @property int|null $trending_views
 * @property int|null $views
 * @property int $web_views
 * @property int|null $auto_create
 * @property int $is_premium
 * @property int $is_freemium
 * @property int $editor_choice
 * @property string|null $orientation
 * @property string|null $cta
 * @property int $pinned
 * @property int $no_index
 * @property int $status
 * @property int $latest
 * @property int|null $deleted
 * @property int|null $has_bug
 * @property int|null $is_fix
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read NewCategory|null $newParent
 * @property-read Category|null $parent
 * @property string $page_link
 * @method static Builder|Design newModelQuery()
 * @method static Builder|Design newQuery()
 * @method static Builder|Design query()
 * @method static Builder|Design whereAdditionalThumb($value)
 * @method static Builder|Design whereAnimation($value)
 * @method static Builder|Design whereAppId($value)
 * @method static Builder|Design whereAutoCreate($value)
 * @method static Builder|Design whereCanonicalLink($value)
 * @method static Builder|Design whereCategoryId($value)
 * @method static Builder|Design whereColorId($value)
 * @method static Builder|Design whereCreatedAt($value)
 * @method static Builder|Design whereCreatorDraftId($value)
 * @method static Builder|Design whereCreatorId($value)
 * @method static Builder|Design whereCta($value)
 * @method static Builder|Design whereDefaultThumbPos($value)
 * @method static Builder|Design whereDeleted($value)
 * @method static Builder|Design whereDescription($value)
 * @method static Builder|Design whereDesigns($value)
 * @method static Builder|Design whereEditorChoice($value)
 * @method static Builder|Design whereEmpId($value)
 * @method static Builder|Design whereEndDate($value)
 * @method static Builder|Design whereFabDesigns($value)
 * @method static Builder|Design whereH2Tag($value)
 * @method static Builder|Design whereHasBug($value)
 * @method static Builder|Design whereHeight($value)
 * @method static Builder|Design whereId($value)
 * @method static Builder|Design whereIdName($value)
 * @method static Builder|Design whereInterestId($value)
 * @method static Builder|Design whereIsFix($value)
 * @method static Builder|Design whereIsFreemium($value)
 * @method static Builder|Design whereIsPremium($value)
 * @method static Builder|Design whereLangId($value)
 * @method static Builder|Design whereLatest($value)
 * @method static Builder|Design whereMetaDescription($value)
 * @method static Builder|Design whereNewCategoryId($value)
 * @method static Builder|Design whereNewRelatedTags($value)
 * @method static Builder|Design whereNoIndex($value)
 * @method static Builder|Design whereOrientation($value)
 * @method static Builder|Design wherePinned($value)
 * @method static Builder|Design wherePostName($value)
 * @method static Builder|Design wherePostThumb($value)
 * @method static Builder|Design whereRatio($value)
 * @method static Builder|Design whereRelatedTags($value)
 * @method static Builder|Design whereReligionId($value)
 * @method static Builder|Design whereSize($value)
 * @method static Builder|Design whereSpecialKeywords($value)
 * @method static Builder|Design whereStartDate($value)
 * @method static Builder|Design whereStatus($value)
 * @method static Builder|Design whereStringId($value)
 * @method static Builder|Design whereStyleId($value)
 * @method static Builder|Design whereSubCatId($value)
 * @method static Builder|Design whereTemplateSize($value)
 * @method static Builder|Design whereThemeId($value)
 * @method static Builder|Design whereThumbArray($value)
 * @method static Builder|Design whereTotalPages($value)
 * @method static Builder|Design whereTrendingViews($value)
 * @method static Builder|Design whereUpdatedAt($value)
 * @method static Builder|Design whereVideoThumb($value)
 * @method static Builder|Design whereViews($value)
 * @method static Builder|Design whereWebViews($value)
 * @method static Builder|Design whereWidth($value)
 * @mixin \Eloquent
 */
class Design extends Model
{
	protected $connection = 'mysql';
    use HasFactory;

    public function getCaricatureIdsAttribute($value): array
    {
        return match (true) {
            is_array($value) => $value,
            is_string($value) => json_decode($value, true) ?? [],
            default => [],
        };
    }

    public function getNewRelatedTagsAttribute($value): array
    {
        return match (true) {
            is_array($value) => $value,
            is_string($value) => json_decode($value, true) ?? [],
            default => [],
        };
//        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }

    public function getPageLinkAttribute($value): string
    {
        return HelperController::$webPageUrl . "templates/p/$this->id_name";
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function newParent(): BelongsTo
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id');
    }

    public function getSitemapUrlAttribute(): string
    {
        return "templates/p/$this->id_name";
    }

    public static function getTempDatas(Order $order): array
    {
        $isInr = $order->currency === "INR";
        $templateData = json_decode($order->plan_id, true);
        $ids = collect($templateData)->pluck('id')->toArray();
        $designs = Design::whereIn('string_id', $ids)->get()->keyBy('string_id');

        $templates = [];
        $paymentProps = [];

        $rates = RateController::getRates();

        $amount = 0;

        foreach ($templateData as $item) {
            if (isset($designs[$item['id']])) {
                $design = $designs[$item['id']];
                $paymentProps[] = ["id" => $item['id'], "type" => 0];

                $thumbArray = json_decode($design->thumb_array);
                $size = sizeof($thumbArray);

                $pyt = RateController::getTemplateRates($rates, $size, $design,  0);

                $amount += $isInr ? $pyt['inrVal'] : $pyt['usdVal'];

                $templates[] = [
                    "title" => $design->post_name,
                    "image" => HelperController::generatePublicUrl($design->post_thumb),
                    "width" => $design->width,
                    "height" => $design->height,
                    "amount" => $isInr ? $pyt['inrAmount'] : $pyt['usdAmount'],
                    "link" => $design->page_link,
                ];
            }
        }

        $response['type'] = "template";
        $response['data'] = [
            "id" => json_encode($paymentProps),
            "templates" => $templates,
            "amount" => ($isInr ? "₹" : "$") . $amount,
        ];
        $response['link'] = "https://www.craftyartapp.com/payment/$order->crafty_id";
        $response['amount'] = $amount;
        return $response;
    }
}

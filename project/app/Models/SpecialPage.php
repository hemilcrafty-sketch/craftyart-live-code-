<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\SpecialPage
 *
 * @property int $id
 * @property string|null $string_id
 * @property int $emp_id
 * @property int $cat_id
 * @property string $page_slug
 * @property string|null $canonical_link
 * @property string $meta_title
 * @property string $title
 * @property string $meta_desc
 * @property string $description
 * @property string|null $pre_breadcrumb
 * @property string $breadcrumb
 * @property string|null $banner_type
 * @property string|null $banner
 * @property string|null $hero_bg_option
 * @property string $colors
 * @property string|null $hero_background_image
 * @property string|null $body_background_image
 * @property string|null $button
 * @property string|null $button_link
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $page_type
 * @property int|null $button_target
 * @property int|null $button_rel
 * @property string|null $resume_guide_content
 * @property string|null $resume_content
 * @property string|null $fldr_str
 * @property string|null $top_keywords
 * @property string|null $cta
 * @property string|null $primary_keyword
 * @property int $updated_record
 * @property int $no_index
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SpecialPage newModelQuery()
 * @method static Builder|SpecialPage newQuery()
 * @method static Builder|SpecialPage query()
 * @method static Builder|SpecialPage whereBanner($value)
 * @method static Builder|SpecialPage whereBannerType($value)
 * @method static Builder|SpecialPage whereBodyBackgroundImage($value)
 * @method static Builder|SpecialPage whereBreadcrumb($value)
 * @method static Builder|SpecialPage whereButton($value)
 * @method static Builder|SpecialPage whereButtonLink($value)
 * @method static Builder|SpecialPage whereButtonRel($value)
 * @method static Builder|SpecialPage whereButtonTarget($value)
 * @method static Builder|SpecialPage whereCanonicalLink($value)
 * @method static Builder|SpecialPage whereCatId($value)
 * @method static Builder|SpecialPage whereColors($value)
 * @method static Builder|SpecialPage whereContents($value)
 * @method static Builder|SpecialPage whereCreatedAt($value)
 * @method static Builder|SpecialPage whereCta($value)
 * @method static Builder|SpecialPage whereDescription($value)
 * @method static Builder|SpecialPage whereEmpId($value)
 * @method static Builder|SpecialPage whereFaqs($value)
 * @method static Builder|SpecialPage whereFldrStr($value)
 * @method static Builder|SpecialPage whereHeroBackgroundImage($value)
 * @method static Builder|SpecialPage whereHeroBgOption($value)
 * @method static Builder|SpecialPage whereId($value)
 * @method static Builder|SpecialPage whereMetaDesc($value)
 * @method static Builder|SpecialPage whereMetaTitle($value)
 * @method static Builder|SpecialPage whereNoIndex($value)
 * @method static Builder|SpecialPage wherePageSlug($value)
 * @method static Builder|SpecialPage wherePageType($value)
 * @method static Builder|SpecialPage wherePreBreadcrumb($value)
 * @method static Builder|SpecialPage wherePrimaryKeyword($value)
 * @method static Builder|SpecialPage whereResumeContent($value)
 * @method static Builder|SpecialPage whereResumeGuideContent($value)
 * @method static Builder|SpecialPage whereStatus($value)
 * @method static Builder|SpecialPage whereStringId($value)
 * @method static Builder|SpecialPage whereTitle($value)
 * @method static Builder|SpecialPage whereTopKeywords($value)
 * @method static Builder|SpecialPage whereUpdatedAt($value)
 * @method static Builder|SpecialPage whereUpdatedRecord($value)
 * @mixin Eloquent
 */
class SpecialPage extends Model
{
    use HasFactory;

    public const DRAFT = 0;
    public const PUBLISH = 1;

    public static $status = [
        "draft" => self::DRAFT,
        "publish" => self::PUBLISH
    ];

    public static $pageType = ["special","tool"];

    protected $connection = 'special_page_mysql';
    protected $table = 'special_pages';
    protected $fillable = ['page_slug','meta_title','title','meta_desc','description','breadcrumb','image','video','banner','banner_type','colors','button','button_link','contents','faqs','status','page_type'];

    public function getSitemapUrlAttribute(): string
    {
        return "$this->page_slug";
    }
}

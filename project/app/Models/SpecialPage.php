<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class SpecialPage extends Model
{
    use HasFactory;
    use UpdateLogger;

    public const DRAFT = 0;
    public const PUBLISH = 1;

    public static $status = [
        "draft" => self::DRAFT,
        "publish" => self::PUBLISH
    ];

    public static $pageType = ["special", "tool", "kpage"];

    protected $connection = 'special_page_mysql';
    protected $table = 'special_pages';
    protected $fillable = ['string_id', 'cat_id', 'page_slug', 'meta_title', 'title', 'primary_keyword', "new_related_tags", 'meta_desc', 'description', 'pre_breadcrumb', 'breadcrumb', 'image', 'button_target', 'video', 'banner', 'banner_type', 'colors', 'button', 'button_link', 'contents', 'faqs', 'status', 'priority', 'frequency', 'page_type', 'resume_guide_content', 'resume_content', 'hero_bg_option', 'hero_background_image', 'body_background_image', 'fldr_str', 'top_keywords', 'cta', 'canonical_link'];

    public function newCategory()
    {
        return $this->belongsTo(NewCategory::class, 'cat_id');
    }
}
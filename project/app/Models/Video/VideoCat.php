<?php

namespace App\Models\Video;

use App\Http\Controllers\Utils\HelperController;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoCat
 *
 * @property int $id
 * @property string $string_id
 * @property string $id_name
 * @property int|null $parent_category_id
 * @property int $emp_id
 * @property string $category_name
 * @property string $category_thumb
 * @property string $cat_link
 * @property int $sequence_number
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read VideoCat|null $parentCategory
 * @property-read Collection<int, VideoCat> $subcategories
 * @property-read Collection<int, VideoTemplate> $videoTemplates
 * @property Collection<int, VideoTemplate> $templates
 * @property-read int|null $video_templates_count
 * @method static Builder|VideoCat with($value)
 * @method static Builder|VideoCat newModelQuery()
 * @method static Builder|VideoCat newQuery()
 * @method static Builder|VideoCat query()
 * @method static Builder|VideoCat whereCatLink($value)
 * @method static Builder|VideoCat whereCategoryName($value)
 * @method static Builder|VideoCat whereCategoryThumb($value)
 * @method static Builder|VideoCat whereCreatedAt($value)
 * @method static Builder|VideoCat whereEmpId($value)
 * @method static Builder|VideoCat whereId($value)
 * @method static Builder|VideoCat whereStringId($value)
 * @method static Builder|VideoCat whereIdName($value)
 * @method static Builder|VideoCat whereParentCategoryId($value)
 * @method static Builder|VideoCat whereNoIndex($value)
 * @method static Builder|VideoCat whereSequenceNumber($value)
 * @method static Builder|VideoCat whereStatus($value)
 * @method static Builder|VideoCat whereUpdatedAt($value)
 * @mixin Eloquent
 */
class VideoCat extends Model
{
    protected $table = 'main_categories';
    protected $connection = 'crafty_video_mysql';
    use HasFactory;


    public function videoTemplates()
    {
        return $this->hasMany(VideoTemplate::class, 'category_id', 'id');
    }

    public function subcategories()
    {
        return $this->hasMany(VideoCat::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(VideoCat::class, 'parent_category_id', 'id');
    }

    public function getCatLinkAttribute($value): string
    {
//        return "http://localhost:3000/" . 'videos/' . $this->id_name;
        return '/templates/' . $value;
    }

}

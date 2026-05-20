<?php

namespace App\Models;

use App\Http\Controllers\Utils\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Draft
 *
 * @property int $id
 * @property string $string_id
 * @property string|null $template_id
 * @property string $user_id
 * @property string $name
 * @property string $ratio
 * @property int $width
 * @property int $height
 * @property string $designs
 * @property int $is_premium
 * @property int $trashed
 * @property int $deleted
 * @property string $thumbs
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Draft newModelQuery()
 * @method static Builder|Draft newQuery()
 * @method static Builder|Draft query()
 * @method static Builder|Draft whereCreatedAt($value)
 * @method static Builder|Draft whereDeleted($value)
 * @method static Builder|Draft whereDesigns($value)
 * @method static Builder|Draft whereHeight($value)
 * @method static Builder|Draft whereId($value)
 * @method static Builder|Draft whereIsPremium($value)
 * @method static Builder|Draft whereName($value)
 * @method static Builder|Draft whereRatio($value)
 * @method static Builder|Draft whereStringId($value)
 * @method static Builder|Draft whereTemplateId($value)
 * @method static Builder|Draft whereThumbs($value)
 * @method static Builder|Draft whereTrashed($value)
 * @method static Builder|Draft whereUpdatedAt($value)
 * @method static Builder|Draft whereUserId($value)
 * @method static Builder|Draft whereWidth($value)
 * @mixin \Eloquent
 */
class Draft extends Model
{
    protected $table = 'drafts';
    protected $connection = 'mysql';
    use HasFactory;

//    public function getThumbsAttribute($value)
//    {
//        return $value === null ? [] : json_decode($value, true);
//    }
}

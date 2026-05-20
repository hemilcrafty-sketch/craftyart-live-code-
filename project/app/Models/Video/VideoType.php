<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Video\VideoType
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoType whereValue($value)
 * @mixin \Eloquent
 */
class VideoType extends Model
{
	protected $table = 'template_types';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;
}

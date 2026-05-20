<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoType
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoType newModelQuery()
 * @method static Builder|VideoType newQuery()
 * @method static Builder|VideoType query()
 * @method static Builder|VideoType whereCreatedAt($value)
 * @method static Builder|VideoType whereId($value)
 * @method static Builder|VideoType whereType($value)
 * @method static Builder|VideoType whereUpdatedAt($value)
 * @method static Builder|VideoType whereValue($value)
 * @mixin \Eloquent
 */
class VideoType extends Model
{
	protected $table = 'template_types';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;
    use UpdateLogger;
}

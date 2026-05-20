<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Video\VideoCmd
 *
 * @property int $id
 * @property string $video_cmd
 * @property string $video_cmd2
 * @property string $audio_cmd
 * @property string $vertex_shader
 * @property string $alpha_shader
 * @property string $float_data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereAlphaShader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereAudioCmd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereFloatData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereVertexShader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereVideoCmd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCmd whereVideoCmd2($value)
 * @mixin \Eloquent
 */
class VideoCmd extends Model
{
	protected $table = 'cmd_tables';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;
}

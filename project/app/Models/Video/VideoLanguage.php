<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoLanguage
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoLanguage newModelQuery()
 * @method static Builder|VideoLanguage newQuery()
 * @method static Builder|VideoLanguage query()
 * @method static Builder|VideoLanguage whereCreatedAt($value)
 * @method static Builder|VideoLanguage whereEmpId($value)
 * @method static Builder|VideoLanguage whereId($value)
 * @method static Builder|VideoLanguage whereIdName($value)
 * @method static Builder|VideoLanguage whereName($value)
 * @method static Builder|VideoLanguage whereStatus($value)
 * @method static Builder|VideoLanguage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoLanguage extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'language';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id'
  ];
}

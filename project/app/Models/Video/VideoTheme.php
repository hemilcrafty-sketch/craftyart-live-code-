<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoTheme
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property string|null $category_id
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoTheme newModelQuery()
 * @method static Builder|VideoTheme newQuery()
 * @method static Builder|VideoTheme query()
 * @method static Builder|VideoTheme whereCategoryId($value)
 * @method static Builder|VideoTheme whereCreatedAt($value)
 * @method static Builder|VideoTheme whereEmpId($value)
 * @method static Builder|VideoTheme whereId($value)
 * @method static Builder|VideoTheme whereIdName($value)
 * @method static Builder|VideoTheme whereName($value)
 * @method static Builder|VideoTheme whereStatus($value)
 * @method static Builder|VideoTheme whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoTheme extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'themes';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id',
    'category_id'
  ];
}

<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoStyle
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoStyle newModelQuery()
 * @method static Builder|VideoStyle newQuery()
 * @method static Builder|VideoStyle query()
 * @method static Builder|VideoStyle whereCreatedAt($value)
 * @method static Builder|VideoStyle whereEmpId($value)
 * @method static Builder|VideoStyle whereId($value)
 * @method static Builder|VideoStyle whereIdName($value)
 * @method static Builder|VideoStyle whereName($value)
 * @method static Builder|VideoStyle whereStatus($value)
 * @method static Builder|VideoStyle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoStyle extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'styles';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id'
  ];
}

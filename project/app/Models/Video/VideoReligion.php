<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoReligion
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoReligion newModelQuery()
 * @method static Builder|VideoReligion newQuery()
 * @method static Builder|VideoReligion query()
 * @method static Builder|VideoReligion whereCreatedAt($value)
 * @method static Builder|VideoReligion whereEmpId($value)
 * @method static Builder|VideoReligion whereId($value)
 * @method static Builder|VideoReligion whereIdName($value)
 * @method static Builder|VideoReligion whereName($value)
 * @method static Builder|VideoReligion whereStatus($value)
 * @method static Builder|VideoReligion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoReligion extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'religions';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id'
  ];
}

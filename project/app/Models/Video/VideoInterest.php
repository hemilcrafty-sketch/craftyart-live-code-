<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoInterest
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property string|null $category_id
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoInterest newModelQuery()
 * @method static Builder|VideoInterest newQuery()
 * @method static Builder|VideoInterest query()
 * @method static Builder|VideoInterest whereCategoryId($value)
 * @method static Builder|VideoInterest whereCreatedAt($value)
 * @method static Builder|VideoInterest whereEmpId($value)
 * @method static Builder|VideoInterest whereId($value)
 * @method static Builder|VideoInterest whereIdName($value)
 * @method static Builder|VideoInterest whereName($value)
 * @method static Builder|VideoInterest whereStatus($value)
 * @method static Builder|VideoInterest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoInterest extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'interests';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id',
    'category_id'
  ];
}

<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoSearchTag
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property int|null $seo_emp_id
 * @property int|null $emp_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoSearchTag newModelQuery()
 * @method static Builder|VideoSearchTag newQuery()
 * @method static Builder|VideoSearchTag query()
 * @method static Builder|VideoSearchTag whereCreatedAt($value)
 * @method static Builder|VideoSearchTag whereEmpId($value)
 * @method static Builder|VideoSearchTag whereId($value)
 * @method static Builder|VideoSearchTag whereIdName($value)
 * @method static Builder|VideoSearchTag whereName($value)
 * @method static Builder|VideoSearchTag whereSeoEmpId($value)
 * @method static Builder|VideoSearchTag whereStatus($value)
 * @method static Builder|VideoSearchTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoSearchTag extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'search_tags';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id',
    'seo_emp_id'
  ];
}

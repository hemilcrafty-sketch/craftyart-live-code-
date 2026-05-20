<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoSize
 *
 * @property int $id
 * @property string $size_name
 * @property string|null $paper_size
 * @property string $thumb
 * @property string|null $category_id
 * @property string|null $id_name
 * @property int $width_ration
 * @property int $height_ration
 * @property int $width
 * @property int $height
 * @property string|null $unit
 * @property int|null $status
 * @property int|null $emp_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VideoSize newModelQuery()
 * @method static Builder|VideoSize newQuery()
 * @method static Builder|VideoSize query()
 * @method static Builder|VideoSize whereCategoryId($value)
 * @method static Builder|VideoSize whereCreatedAt($value)
 * @method static Builder|VideoSize whereEmpId($value)
 * @method static Builder|VideoSize whereHeight($value)
 * @method static Builder|VideoSize whereHeightRation($value)
 * @method static Builder|VideoSize whereId($value)
 * @method static Builder|VideoSize whereIdName($value)
 * @method static Builder|VideoSize wherePaperSize($value)
 * @method static Builder|VideoSize whereSizeName($value)
 * @method static Builder|VideoSize whereStatus($value)
 * @method static Builder|VideoSize whereThumb($value)
 * @method static Builder|VideoSize whereUnit($value)
 * @method static Builder|VideoSize whereUpdatedAt($value)
 * @method static Builder|VideoSize whereWidth($value)
 * @method static Builder|VideoSize whereWidthRation($value)
 * @mixin \Eloquent
 */
class VideoSize extends Model
{
    use HasFactory;

    protected $connection = 'crafty_video_mysql';

    protected $table = 'sizes';

    protected $fillable = [
        'size_name',
        'thumb',
        'id_name',
        'width_ration',
        'width',
        'category_id',
        'height_ration',
        'height',
        'paper_size',
        'unit',
        'emp_id',
        'status',
    ];
}

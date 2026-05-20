<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class VideoItem extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $fillable = [
        'video_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'compress_vdo',
        'thumbnail',
        'duration',
        'height',
        'width',
        'size',
        'is_premium',
        'status',
    ];


    public function videoCategory()
    {
        return $this->belongsTo(VideoCategory::class, 'video_category_id');
    }


}
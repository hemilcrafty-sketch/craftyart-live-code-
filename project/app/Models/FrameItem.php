<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'frame_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'width',
        'height',
        'is_premium',
        'status',
    ];


    public function frameCategory()
    {
        return $this->belongsTo(FrameCategory::class);
    }
}

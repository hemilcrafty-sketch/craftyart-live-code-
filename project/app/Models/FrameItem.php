<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UpdateLogger;

class FrameItem extends Model
{
    use HasFactory;
    use UpdateLogger;
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


    // public function frameCategory()
    // {
    //     return $this->belongsTo(FrameCategory::class);
    // }

    public function frameCategory(): BelongsTo
    {
        return $this->belongsTo(FrameCategory::class, 'frame_category_id');
    }

}

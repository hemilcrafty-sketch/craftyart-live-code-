<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class StickerItem extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
    protected $fillable = [
        'stk_cat_id',
        'emp_id',
        'sticker_thumb',
        'sticker_image',
        'sticker_type',
        'sticker_name',
        'width',
        'height',
        'is_premium',
        'status',
    ];
}
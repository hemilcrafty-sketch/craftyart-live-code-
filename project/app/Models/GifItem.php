<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class GifItem extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $fillable = [
        'gif_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'width',
        'height',
        'is_premium',
        'status',
    ];


    public function gifCategory()
    {
        return $this->belongsTo(GifCategory::class);
    }
}
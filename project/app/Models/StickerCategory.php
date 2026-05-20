<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class StickerCategory extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;

    protected $fillable = [
        'stk_category_name',
        'stk_category_thumb',
        'sequence_number',
        'status',
        'emp_id',
    ];
}

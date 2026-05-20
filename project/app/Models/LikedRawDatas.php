<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikedRawDatas extends Model
{
    protected $table = 'liked_raw_datas';
    protected $connection = 'mysql';
    use HasFactory;
}

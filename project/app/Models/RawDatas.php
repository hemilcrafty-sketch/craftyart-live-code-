<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawDatas extends Model
{
    protected $table = 'raw_datas';
    protected $connection = 'mysql';
    use HasFactory;
}

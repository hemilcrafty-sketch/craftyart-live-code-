<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpTable extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
}
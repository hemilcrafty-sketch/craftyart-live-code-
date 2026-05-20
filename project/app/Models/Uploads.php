<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    protected $table = 'uploads';
    protected $connection = 'mysql';
    use HasFactory;
}

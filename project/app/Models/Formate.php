<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class Formate extends Model
{
    use HasFactory;
    use UpdateLogger;

    protected $connection = 'mysql';
    protected $fillable = ['name'];
}

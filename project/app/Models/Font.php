<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class Font extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
}

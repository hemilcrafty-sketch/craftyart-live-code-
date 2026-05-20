<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class FontFamily extends Model
{
	protected $table = 'font_families';
	protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
}

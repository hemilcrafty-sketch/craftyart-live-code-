<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class FontList extends Model
{
	protected $table = 'font_list';
	protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
}

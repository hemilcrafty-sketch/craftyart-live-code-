<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FontList extends Model
{
	protected $table = 'font_list';
	protected $connection = 'mysql';
    use HasFactory;
}

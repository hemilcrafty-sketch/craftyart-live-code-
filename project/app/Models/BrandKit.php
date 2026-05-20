<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandKit extends Model
{
	protected $table = 'brand_kit';
	protected $connection = 'brand_kit_mysql';
    use HasFactory;
}

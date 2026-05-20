<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikedProduct extends Model
{
    protected $connection = 'mysql';
	protected $table = 'liked_products';
    use HasFactory;
}

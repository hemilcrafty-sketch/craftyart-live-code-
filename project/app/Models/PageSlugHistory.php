<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSlugHistory extends Model
{
	protected $table = 'page_slug_history';
	protected $connection = 'mysql';
    use HasFactory;
}

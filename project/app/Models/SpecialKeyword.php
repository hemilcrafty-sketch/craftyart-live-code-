<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class SpecialKeyword extends Model
{
	protected $table = 'special_keywords';
	protected $connection = 'mysql';

	use HasFactory;
use UpdateLogger;

	public function newCategory()
	{
		return $this->belongsTo(NewCategory::class, 'cat_id');
	}
}

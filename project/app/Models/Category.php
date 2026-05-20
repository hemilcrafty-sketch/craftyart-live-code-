<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
	protected $connection = 'mysql';
    use HasFactory;

    public function children(): HasMany
    {
        return $this->hasMany(Design::class, 'category_id');
    }
}

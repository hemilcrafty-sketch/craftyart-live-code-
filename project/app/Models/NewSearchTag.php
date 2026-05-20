<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewSearchTag extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'name',
        'id_name',
        'new_category_id',
        'status',
        'emp_id',
    ];
    public function newCategories()
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id', 'id');
    }
}

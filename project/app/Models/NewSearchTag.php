<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UpdateLogger;

class NewSearchTag extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $fillable = [
        'name',
        'id_name',
        'new_category_id',
        'seo_emp_id',
        'status',
        'emp_id',
    ];
    public function newCategories()
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id', 'id');
    }

    public function assignedSeo()
    {
        return $this->belongsTo(User::class, 'seo_emp_id');
    }

}

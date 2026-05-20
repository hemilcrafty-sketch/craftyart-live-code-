<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class SearchTag extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
    public function assignedSeo()
    {
        return $this->belongsTo(User::class, 'seo_emp_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class VirtualCategory extends Model
{
    use HasFactory;
    use UpdateLogger;

    protected $connection = 'mysql';

    protected $casts = [
        'priority' => 'float',
    ];

    public function assignedSeo()
{
    return $this->belongsTo(User::class, 'seo_emp_id');
}


}
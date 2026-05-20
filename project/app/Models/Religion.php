<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = [
        'religion_name',
        'id_name',
        'emp_id',
        'status'
    ];
}

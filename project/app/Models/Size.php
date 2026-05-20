<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['size_name','id_name','width_ration','width','new_category_id','height_ration','height', 'emp_id','status'];

}

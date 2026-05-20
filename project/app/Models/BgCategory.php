<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class BgCategory extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;

    protected $fillable = [
        "emp_id",
        "bg_category_name",
        "bg_category_thumb",
        "sequence_number",
        "status",
        "deleted",
    ];
}

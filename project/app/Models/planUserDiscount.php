<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanUserDiscount extends Model
{
    use HasFactory;
    protected $table = 'plan_user_discount';

    protected $connection = 'mysql';
    protected $fillable = [
        "discount_percentage",
        "x",
    ];
}

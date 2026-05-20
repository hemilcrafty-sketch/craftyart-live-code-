<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Traits\UpdateLogger;

class Size extends Model
{
    use UpdateLogger;
    use HasFactory, Notifiable;
    protected $connection = 'mysql';
    protected $fillable = ['size_name', 'thumb', 'id_name', 'width_ration', 'width', 'new_category_id', 'height_ration', 'height', 'paper_size', 'emp_id', 'status'];

    public static function getAllSizes()
    {
        $sizes = Size::all();
        return $sizes;
    }

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'app_id',
        'category_id',
        'sub_cat_id',
        'style_id',
        'theme_id',
        'interest_id',
        'lang_id',
        'bg_cat_id',
        'bg_id',
        'post_name',
        'post_thumb',
        'back_image_type',
        'back_image',
        'back_color',
        'grad_angle',
        'grad_ratio',
        'ratio',
        'width',
        'height',
        'component_info',
        'text_info',
        'description',
        'keywords',
        'start_date',
        'end_date',
        'size',
        'trending_views',
        'views',
        'is_premium',
        'status',
        'priority',
        'frequency',
        'latest',
        'deleted',
    ];

}
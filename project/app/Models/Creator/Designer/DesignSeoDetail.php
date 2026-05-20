<?php

namespace App\Models\Creator\Designer;

use Illuminate\Database\Eloquent\Model;

class DesignSeoDetail extends Model
{
    protected $connection = 'crafty_creator_mysql';

    protected $fillable = [
        'design_submission_id',
        'post_name',
        'id_name',
        'h2_tag',
        'description',
        'meta_title',
        'meta_description',
        'slug',
        'keywords',
        'og_image',
        'post_thumb',
        'additional_thumb',
        'is_featured',
        'is_trending',
        'priority',
        'primary_category_id',
        'filters',
        'aspect_ratio',
        'width',
        'height',
    ];

    protected $casts = [
        'keywords' => 'array',
        'filters' => 'array',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
    ];

    public function design()
    {
        return $this->belongsTo(DesignSubmission::class, 'design_submission_id');
    }
}

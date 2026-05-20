<?php

namespace App\Models\Creator\Designer;

use Illuminate\Database\Eloquent\Model;

class DesignerGoal extends Model
{
    protected $connection = 'crafty_creator_mysql';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}

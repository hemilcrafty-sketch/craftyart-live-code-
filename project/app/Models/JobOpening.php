<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Published job listing (title, slug, location, employment type, body).
 */
class JobOpening extends Model
{
    protected $connection = 'mysql';

    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'location',
        'type',
        'department',
        'experience',
        'salary_range',
        'description',
        'responsibilities',
        'requirements',
        'perks',
        'tools',
        'interview_steps',
        'is_active',
        'is_actively_hiring',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_actively_hiring' => 'boolean',
        'responsibilities' => 'array',
        'requirements' => 'array',
        'perks' => 'array',
        'tools' => 'array',
        'interview_steps' => 'array',
    ];

  
}

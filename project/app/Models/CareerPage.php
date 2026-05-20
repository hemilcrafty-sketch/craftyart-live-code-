<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CMS rows for the careers page (one row per section_key; JSON in value).
 * One row per section_key; payload lives in JSON `value`.
 */
class CareerPage extends Model
{
    protected $connection = 'mysql';

    use HasFactory;

    protected $fillable = [
        'section_key',
        'value',
        'is_active',
    ];

    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
    ];
}

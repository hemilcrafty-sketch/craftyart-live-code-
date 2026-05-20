<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorVisit extends Model
{
	protected $table = 'editor_visits';
	protected $connection = 'mysql';

    protected $fillable = [
        'uid',
        'pid',
        'draft_id',
        'ip_address',
        'country',
        'fbc',
        'fbp',
    ];

    use HasFactory;
}

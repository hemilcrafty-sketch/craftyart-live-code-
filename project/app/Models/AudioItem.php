<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class AudioItem extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $fillable = [
        'audio_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'duration',
        'size',
        'is_premium',
        'status',
    ];


    public function audioCategory()
    {
        return $this->belongsTo(AudioCategory::class);
    }
}

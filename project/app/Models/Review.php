<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'photo_uri',
        'feedback',
        'rate',
        'is_approve'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}

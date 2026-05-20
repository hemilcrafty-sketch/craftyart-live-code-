<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PReview extends Model
{
    protected $table = 'p_reviews';
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        'user_id',
        'p_type',
        'p_id',
        'name',
        'email',
        'photo_uri',
        'feedback',
        'rate',
        'is_approve',
        'is_deleted',
    ];

    public function getPageTypeNameAttribute()
    {
        $types = [
            0 => 'Product Page',
            1 => 'New Category',
            2 => 'Special Page',
            3 => 'Special Keyword',
            4 => 'Category',
            5 => 'Virtual Category',
        ];

        return $types[$this->p_type] ?? 'Unknown';
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }

}

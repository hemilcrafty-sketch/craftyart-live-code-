<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'device_id',
        'token_id',
        'ip_address',
        'user_agent',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id');
    }

}

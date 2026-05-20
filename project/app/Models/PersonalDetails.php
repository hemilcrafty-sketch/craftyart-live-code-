<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalDetails extends Model
{
    protected $connection = 'mysql'; // crafty_db
    protected $table = 'personal_details';
    
    protected $fillable = [
        'uid',
        'user_name',
        'bio',
        'country',
        'state',
        'city',
        'address',
        'interest',
        'purpose',
        'usage',
        'reference',
        'language',
    ];
    
    /**
     * Get the user that owns the personal details
     */
    public function user()
    {
        return $this->belongsTo(UserData::class, 'uid', 'uid');
    }
}

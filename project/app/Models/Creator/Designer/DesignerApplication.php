<?php

namespace App\Models\Creator\Designer;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DesignerApplication extends Model
{
    protected $connection = 'crafty_creator_mysql';

    protected $fillable = [
        'user_id',
        'app_user_uid',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'experience',
        'experience_level',
        'skills',
        'portfolio_links',
        'uploaded_samples',
        'selected_types',
        'selected_categories',
        'selected_goals',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'portfolio_links' => 'array',
        'uploaded_samples' => 'array',
        'selected_types' => 'array',
        'selected_categories' => 'array',
        'selected_goals' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

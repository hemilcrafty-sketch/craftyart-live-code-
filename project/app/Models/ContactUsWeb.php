<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsWeb extends Model
{
    use HasFactory;

    protected $table = 'contact_us_web';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'ip_address',
        'user_agent',
        'system_info',
        'followup_call',
        'followup_note',
        'followup_label',
        'emp_id'
    ];
}
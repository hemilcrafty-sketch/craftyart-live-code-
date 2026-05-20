<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebFbSelling extends Model
{
	protected $table = 'web_fb_selling';
	protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'product_id',
        'amount',
        'ip_address',
        'country',
        'fbc',
        'fbp',
        'gclid',
        'gcl_au',
        'ga',
        'userAgent',
    ];

    use HasFactory;
}

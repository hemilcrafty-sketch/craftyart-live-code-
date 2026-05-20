<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedIp extends Model
{
	protected $table = 'allowed_ip';
	protected $connection = 'mysql';
    use HasFactory;
}

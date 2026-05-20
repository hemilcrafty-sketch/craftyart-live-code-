<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTPTable extends Model
{
    protected $table = 'otp_tables';
    protected $connection = 'mysql';
    use HasFactory;
}

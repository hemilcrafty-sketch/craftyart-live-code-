<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeTranscation extends Model
{
    protected $table = 'promo_code_transcation';
    protected $connection = 'mysql';
    use HasFactory;
}

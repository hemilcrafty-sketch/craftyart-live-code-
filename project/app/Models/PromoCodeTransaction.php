<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeTransaction extends Model
{
    protected $table = 'promo_code_transaction';
    protected $connection = 'mysql';
    use HasFactory;
}

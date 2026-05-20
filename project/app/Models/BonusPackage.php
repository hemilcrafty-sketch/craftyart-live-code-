<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusPackage extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
	protected $table = 'bonus_package';

    protected $fillable = [
        'string_id',
        'bonus_code',
        'inr_price',
        'usd_price',
        'additional_day',
    ];
}
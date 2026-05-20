<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageSubscription extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['user_id','is_base_price','package_name','desc','validity','actual_price','actual_price_dollar','price','price_dollar','months','has_offer','sequence_number','status'];
}

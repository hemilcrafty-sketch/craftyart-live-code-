<?php

namespace App\Models\CustomOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingTable extends Model
{
	protected $table = 'pricing_table';
	protected $connection = 'custom_order_mysql';
    use HasFactory;
}

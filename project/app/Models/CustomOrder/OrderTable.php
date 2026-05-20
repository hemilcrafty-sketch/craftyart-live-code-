<?php

namespace App\Models\CustomOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTable extends Model
{
	protected $table = 'order_table';
	protected $connection = 'custom_order_mysql';
    use HasFactory;
}

<?php

namespace App\Models\CustomOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeTable extends Model
{
	protected $table = 'size_table';
	protected $connection = 'custom_order_mysql';
    use HasFactory;
}

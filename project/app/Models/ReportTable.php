<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTable extends Model
{
	protected $table = 'reports';
	protected $connection = 'mysql';
    use HasFactory;
}

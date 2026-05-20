<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FbTraces extends Model
{
	protected $table = 'fb_traces';
	protected $connection = 'mysql';
    use HasFactory;
}

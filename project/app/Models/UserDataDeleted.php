<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDataDeleted extends Model
{
	protected $table = 'user_data_deleted';
	protected $connection = 'mysql';
    use HasFactory;
}

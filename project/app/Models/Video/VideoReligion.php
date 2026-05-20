<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoReligion extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'religions';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id'
  ];
}

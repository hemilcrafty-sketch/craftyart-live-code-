<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoStyle extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'styles';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id'
  ];
}

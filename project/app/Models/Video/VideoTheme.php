<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoTheme extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'themes';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id',
    'new_category_id'
  ];
}

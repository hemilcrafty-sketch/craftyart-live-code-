<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoSearchTag extends Model
{
  use HasFactory;

  protected $connection = 'crafty_video_mysql';
  protected $table = 'search_tags';

  protected $fillable = [
    'name',
    'id_name',
    'status',
    'emp_id',
    'seo_emp_id'
  ];
}

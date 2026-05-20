<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoReview extends Model
{
  use HasFactory;

  protected $table = 'reviews';
  protected $connection = 'crafty_video_mysql';

  protected $fillable = [
    'user_id',
    'name',
    'email',
    'photo_uri',
    'feedback',
    'rate',
    'is_approve'
  ];

  public function user()
  {
    return $this->belongsTo(UserData::class, 'user_id', 'uid');
  }
}

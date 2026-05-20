<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoPageReview extends Model
{
  use HasFactory;

  protected $table = 'page_reviews';
  protected $connection = 'crafty_video_mysql';

  protected $fillable = [
    'user_id',
    'p_type',
    'p_id',
    'name',
    'email',
    'photo_uri',
    'suggestion_type',
    'summarised',
    'feedback',
    'rate',
    'is_approve',
    'is_deleted',
  ];

  public function getVideoTypeNameAttribute()
  {
    $types = [
      6 => 'Video Product Page',
      7 => 'Video New Category',
      8 => 'Video Virtual Category',
    ];
    return $types[$this->p_type] ?? 'Unknown';
  }

  public function user()
  {
    return $this->belongsTo(UserData::class, 'user_id', 'uid');
  }

  public function getVideoPage()
  {
    if ($this->p_type == 6) {
      return VideoTemplate::where('string_id', $this->p_id)->first();
    } elseif ($this->p_type == 7) {
      return VideoCategory::where('id_name', $this->p_id)->first();
    } elseif ($this->p_type == 8) {
      return VideoCategory::where('id_name', $this->p_id)->first();
    }
    return null;
  }
}

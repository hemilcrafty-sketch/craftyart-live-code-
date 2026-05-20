<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoPageReview
 *
 * @property int $id
 * @property string|null $user_id
 * @property int $p_type
 * @property string $p_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $photo_uri
 * @property string $feedback
 * @property string|null $suggestion_type
 * @property string|null $summarised
 * @property float $rate
 * @property int|null $is_approve
 * @property int $is_deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $video_type_name
 * @property-read UserData|null $user
 * @method static Builder|VideoPageReview newModelQuery()
 * @method static Builder|VideoPageReview newQuery()
 * @method static Builder|VideoPageReview query()
 * @method static Builder|VideoPageReview whereCreatedAt($value)
 * @method static Builder|VideoPageReview whereEmail($value)
 * @method static Builder|VideoPageReview whereFeedback($value)
 * @method static Builder|VideoPageReview whereId($value)
 * @method static Builder|VideoPageReview whereIsApprove($value)
 * @method static Builder|VideoPageReview whereIsDeleted($value)
 * @method static Builder|VideoPageReview whereName($value)
 * @method static Builder|VideoPageReview wherePId($value)
 * @method static Builder|VideoPageReview wherePType($value)
 * @method static Builder|VideoPageReview wherePhotoUri($value)
 * @method static Builder|VideoPageReview whereRate($value)
 * @method static Builder|VideoPageReview whereSuggestionType($value)
 * @method static Builder|VideoPageReview whereSummarised($value)
 * @method static Builder|VideoPageReview whereUpdatedAt($value)
 * @method static Builder|VideoPageReview whereUserId($value)
 * @mixin \Eloquent
 */
class VideoPageReview extends Model
{
  use HasFactory;

  protected $table = 'p_reviews';
  protected $connection = 'mysql';

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
      6 => 'Video New Category',
      7 => 'Video Virtual Category',
      8 => 'Video Product Page',
    ];
    $key = (int) $this->p_type;

    return $types[$key] ?? 'Unknown';
  }

  public function user()
  {
    return $this->belongsTo(UserData::class, 'user_id', 'uid');
  }
  public function getVideoPage()
  {
    $pid = (string) $this->p_id;
    if ((int) $this->p_type === 6) {
      return VideoCategory::query()
        ->where(function ($q) use ($pid) {
          $q->where('slug', $pid)->orWhere('string_id', $pid);
          if ($pid !== '' && ctype_digit($pid)) {
            $q->orWhere('id', (int) $pid);
          }
        })
        ->first();
    }
    if ((int) $this->p_type === 7) {
      return VideoVirtualCategory::query()
        ->where(function ($q) use ($pid) {
          $q->where('slug', $pid)->orWhere('string_id', $pid);
          if ($pid !== '' && ctype_digit($pid)) {
            $q->orWhere('id', (int) $pid);
          }
        })
        ->first();
    }
    if ((int) $this->p_type === 8) {
      return VideoTemplate::query()
        ->where(function ($q) use ($pid) {
          $q->where('string_id', $pid)->orWhere('slug', $pid);
          if ($pid !== '' && ctype_digit($pid)) {
            $q->orWhere('id', (int) $pid);
          }
        })
        ->first();
    }

    return null;
  }
}

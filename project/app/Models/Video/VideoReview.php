<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoReview
 *
 * @property int $id
 * @property int|null $user_id User ID from main database
 * @property string|null $name
 * @property string|null $email
 * @property string|null $photo_uri
 * @property string|null $feedback
 * @property int $rate Rating 1-5
 * @property int $is_approve 0=Not Approved, 1=Approved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read UserData|null $user
 * @method static Builder|VideoReview newModelQuery()
 * @method static Builder|VideoReview newQuery()
 * @method static Builder|VideoReview query()
 * @method static Builder|VideoReview whereCreatedAt($value)
 * @method static Builder|VideoReview whereEmail($value)
 * @method static Builder|VideoReview whereFeedback($value)
 * @method static Builder|VideoReview whereId($value)
 * @method static Builder|VideoReview whereIsApprove($value)
 * @method static Builder|VideoReview whereName($value)
 * @method static Builder|VideoReview wherePhotoUri($value)
 * @method static Builder|VideoReview whereRate($value)
 * @method static Builder|VideoReview whereUpdatedAt($value)
 * @method static Builder|VideoReview whereUserId($value)
 * @mixin \Eloquent
 */
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

<?php

namespace App\Models\Automation;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Automation\WpFeedbackResponse
 *
 * @property int $id
 * @property int $wp_feedback_request_id
 * @property string $user_id
 * @property int $purchase_id
 * @property int $rating 1-5 stars
 * @property string|null $feedback_text
 * @property string|null $suggestions
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WpFeedbackRequest|null $wpFeedbackRequest
 * @method static Builder|WpFeedbackResponse newModelQuery()
 * @method static Builder|WpFeedbackResponse newQuery()
 * @method static Builder|WpFeedbackResponse query()
 * @method static Builder|WpFeedbackResponse whereCreatedAt($value)
 * @method static Builder|WpFeedbackResponse whereFeedbackText($value)
 * @method static Builder|WpFeedbackResponse whereId($value)
 * @method static Builder|WpFeedbackResponse whereIpAddress($value)
 * @method static Builder|WpFeedbackResponse wherePurchaseId($value)
 * @method static Builder|WpFeedbackResponse whereRating($value)
 * @method static Builder|WpFeedbackResponse whereSubmittedAt($value)
 * @method static Builder|WpFeedbackResponse whereSuggestions($value)
 * @method static Builder|WpFeedbackResponse whereUpdatedAt($value)
 * @method static Builder|WpFeedbackResponse whereUserAgent($value)
 * @method static Builder|WpFeedbackResponse whereUserId($value)
 * @method static Builder|WpFeedbackResponse whereWpFeedbackRequestId($value)
 * @mixin \Eloquent
 */
class WpFeedbackResponse extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'wp_feedback_responses';

    protected $fillable = [
        'wp_feedback_request_id', 'user_id', 'purchase_id', 'rating',
        'feedback_text', 'suggestions', 'ip_address', 'user_agent', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function wpFeedbackRequest(): BelongsTo
    {
        return $this->belongsTo(WpFeedbackRequest::class, 'wp_feedback_request_id');
    }
}

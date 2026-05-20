<?php

namespace App\Models\Automation;

use App\Http\Controllers\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Automation\WpFeedbackRequest
 *
 * @property int $id
 * @property string|null $string_id
 * @property string $user_id
 * @property string|null $contact_no
 * @property int $purchase_id
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $completed_at
 * @property int $days_after_purchase
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WpFeedbackResponse|null $response
 * @method static Builder|WpFeedbackRequest completed()
 * @method static Builder|WpFeedbackRequest newModelQuery()
 * @method static Builder|WpFeedbackRequest newQuery()
 * @method static Builder|WpFeedbackRequest pending()
 * @method static Builder|WpFeedbackRequest query()
 * @method static Builder|WpFeedbackRequest whereCompletedAt($value)
 * @method static Builder|WpFeedbackRequest whereContactNo($value)
 * @method static Builder|WpFeedbackRequest whereCreatedAt($value)
 * @method static Builder|WpFeedbackRequest whereDaysAfterPurchase($value)
 * @method static Builder|WpFeedbackRequest whereExpiresAt($value)
 * @method static Builder|WpFeedbackRequest whereId($value)
 * @method static Builder|WpFeedbackRequest wherePurchaseId($value)
 * @method static Builder|WpFeedbackRequest whereSentAt($value)
 * @method static Builder|WpFeedbackRequest whereStatus($value)
 * @method static Builder|WpFeedbackRequest whereStringId($value)
 * @method static Builder|WpFeedbackRequest whereUpdatedAt($value)
 * @method static Builder|WpFeedbackRequest whereUserId($value)
 * @mixin \Eloquent
 */
class WpFeedbackRequest extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'wp_feedback_requests';

    protected $fillable = [
        'string_id', 'user_id', 'contact_no', 'purchase_id', 'status',
        'sent_at', 'expires_at', 'completed_at', 'days_after_purchase',
        'followup_call', 'followup_note', 'followup_label', 'emp_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->string_id)) {
                $model->string_id = HelperController::generateStringIds(
                    prefix: 'wf',
                    source: self::class
                );
            }
        });
    }

    protected $casts = [
        'sent_at'        => 'datetime',
        'expires_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'followup_call'  => 'boolean',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function response()
    {
        return $this->hasOne(WpFeedbackResponse::class, 'wp_feedback_request_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function userData()
    {
        return $this->belongsTo(\App\Models\UserData::class, 'user_id', 'uid');
    }

    public function unifiedSupport()
    {
        return $this->hasOne(UnifiedSupport::class, 'purchase_history_id', 'purchase_id');
    }

}

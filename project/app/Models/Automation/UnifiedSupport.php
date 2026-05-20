<?php

namespace App\Models\Automation;

use App\Models\PurchaseHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\UnifiedSupport
 *
 * @property int $id
 * @property int $purchase_history_id
 * @property int|null $emp_id
 * @property string $user_id
 * @property int $subscription_followup_call
 * @property string|null $subscription_followup_note
 * @property string|null $subscription_followup_label
 * @property int $expire_followup_call
 * @property string|null $expire_followup_note
 * @property string|null $expire_followup_label
 * @property int $expire_email_sent
 * @property int $expire_wp_sent
 * @property int $wp_feedback_followup_call
 * @property string|null $wp_feedback_followup_note
 * @property string|null $wp_feedback_followup_label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UnifiedSupport extends Model
{
    use HasFactory;

    protected $connection = 'crafty_automation_mysql';
    protected $table = 'unified_support';

    protected $fillable = [
        'purchase_history_id',
        'transaction_log_id',
        'emp_id',
        'user_id',
        'account_creation_email_sent',
        'account_creation_wp_sent',
        'subscription_followup_call',
        'subscription_followup_note',
        'subscription_followup_label',
        'subscription_email_sent',
        'subscription_wp_sent',
        'expire_followup_call',
        'expire_followup_note',
        'expire_followup_label',
        'expire_email_sent',
        'expire_wp_sent',
        'wp_feedback_followup_call',
        'wp_feedback_followup_note',
        'wp_feedback_followup_label',
        'event_date',
        'source_type',
    ];

    protected $casts = [
        'account_creation_email_sent' => 'integer',
        'account_creation_wp_sent' => 'integer',
        'subscription_followup_call' => 'integer',
        'subscription_email_sent' => 'integer',
        'subscription_wp_sent' => 'integer',
        'expire_followup_call' => 'integer',
        'expire_email_sent' => 'integer',
        'expire_wp_sent' => 'integer',
        'wp_feedback_followup_call' => 'integer',
        'event_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the purchase history record
     */
    public function purchaseHistory()
    {
        return $this->belongsTo(PurchaseHistory::class, 'purchase_history_id', 'id');
    }
}

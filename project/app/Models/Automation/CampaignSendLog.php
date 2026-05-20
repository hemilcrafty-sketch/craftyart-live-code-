<?php

namespace App\Models\Automation;

use App\Models\PromoCode;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Enums\AutomationType;

/**
 * App\Models\Automation\CampaignSendLog
 *
 * @property int $id
 * @property string $subject
 * @property string $email_template_id
 * @property string $wp_template_id
 * @property array $user_ids
 * @property int $promo_code
 * @property int $plan_id
 * @property int $select_users_type 1 = All User
 * 2 = Premium User
 * 3 = Custom Selected User
 * @property int $auto_pause_count
 * @property int $total
 * @property int $email_sent
 * @property int $wp_sent
 * @property int $email_failed
 * @property int $wp_failed
 * @property string $status
 * @property int $stopped
 * @property int $sent_since_last_pause
 * @property string|null $last_processed_user_id
 * @property int $total_processed
 * @property string|null $pause_type
 * @property string $type
 * @property int $auto_resume 0 = Next day mail will be not sent Automatically
 * 1 = Next day mail will be sent Automatically
 * @property int $send_type 1 = Custom Campaign Mail Send
 * 2 = Checkout Drop User Instant // not in used
 * 3 = Checkout Drop User Frequency mail
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $sent_type
 * @property-read string $failed_type
 * @property-read array $template_name
 * @property-read Subscription|null $plan
 * @property-read PromoCode|null $promoCode
 * @method static Builder|CampaignSendLog newModelQuery()
 * @method static Builder|CampaignSendLog newQuery()
 * @method static Builder|CampaignSendLog query()
 * @method static Builder|CampaignSendLog whereAutoPauseCount($value)
 * @method static Builder|CampaignSendLog whereAutoResume($value)
 * @method static Builder|CampaignSendLog whereCreatedAt($value)
 * @method static Builder|CampaignSendLog whereEmailFailed($value)
 * @method static Builder|CampaignSendLog whereEmailSent($value)
 * @method static Builder|CampaignSendLog whereEmailTemplateId($value)
 * @method static Builder|CampaignSendLog whereId($value)
 * @method static Builder|CampaignSendLog whereLastProcessedUserId($value)
 * @method static Builder|CampaignSendLog wherePauseType($value)
 * @method static Builder|CampaignSendLog wherePlanId($value)
 * @method static Builder|CampaignSendLog wherePromoCode($value)
 * @method static Builder|CampaignSendLog whereSelectUsersType($value)
 * @method static Builder|CampaignSendLog whereSendType($value)
 * @method static Builder|CampaignSendLog whereSentSinceLastPause($value)
 * @method static Builder|CampaignSendLog whereStatus($value)
 * @method static Builder|CampaignSendLog whereStopped($value)
 * @method static Builder|CampaignSendLog whereSubject($value)
 * @method static Builder|CampaignSendLog whereTotal($value)
 * @method static Builder|CampaignSendLog whereType($value)
 * @method static Builder|CampaignSendLog whereUpdatedAt($value)
 * @method static Builder|CampaignSendLog whereUserIds($value)
 * @method static Builder|CampaignSendLog whereWpFailed($value)
 * @method static Builder|CampaignSendLog whereWpSent($value)
 * @method static Builder|CampaignSendLog whereWpTemplateId($value)
 * @method static Builder|CampaignSendLog whereTotalProcessed($value)
 * @property-read EmailTemplate|null $emailTemplate
 * @property-read WhatsappTemplate|null $whatsappTemplate
 * @mixin \Eloquent
 */
class CampaignSendLog extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'campaign_send_logs';

    protected $fillable = ['subject',
        'email_template_id',
        'wp_template_id',
        'user_ids',
        'plan_id',
        'select_users_type',
        'promo_code',
        'auto_pause_count',
        'total',
        'email_sent',
        'wp_sent',
        'email_failed',
        'wp_failed',
        'status',
        'stopped',
        'sent_since_last_pause',
        'last_processed_user_id',
        'type',
        'pause_type',
        'auto_resume',
        'send_type'];

    /**
     * Relationship with EmailTemplate model
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Relationship with WhatsappTemplate model
     */
    public function whatsappTemplate(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'wp_template_id');
    }

    /**
     * Relationship with Subscription/Plan model
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'plan_id');
    }

    /**
     * Relationship with PromoCode model
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code');
    }

    public function getUserIdsAttribute($user_ids): array
    {
        return $user_ids === null ? [] : json_decode($user_ids, true);
    }

    public function getSentTypeAttribute(): array
    {
        $type = AutomationType::tryFrom(strtolower($this->type)) ?? AutomationType::EMAIL;

        return match($type) {
            AutomationType::EMAIL => ['email' => $this->email_sent],
            AutomationType::WHATSAPP => ['Wp' => $this->wp_sent],
            AutomationType::EMAIL_WHATSAPP => [
                'email' => $this->email_sent,
                'Wp' => $this->wp_sent
            ],
        };
    }

    /**
     * Check if campaign has email template
     */
    public function hasEmailTemplate(): bool
    {
        return !empty($this->email_template_id) && $this->email_template_id != 0;
    }

    /**
     * Check if campaign has WhatsApp template
     */
    public function hasWhatsappTemplate(): bool
    {
        return !empty($this->wp_template_id) && $this->wp_template_id != 0;
    }

    public function getTemplateNameAttribute(): array
    {
        $templateNames = [];

        if ($this->hasEmailTemplate() && $this->emailTemplate) {
            $templateNames[] = "Email: " . ($this->emailTemplate->name ?? 'Email Template');
        }

        if ($this->hasWhatsappTemplate() && $this->whatsappTemplate) {
            $templateNames[] = "WP: " . ($this->whatsappTemplate->campaign_name ?? 'WhatsApp Template');
        }

        return $templateNames;
    }


    /**
     * Get the failed counts as structured data
     */
    public function getFailedTypeAttribute(): array
    {
        $type = AutomationType::tryFrom(strtolower($this->type)) ?? AutomationType::EMAIL;

        return match($type) {
            AutomationType::EMAIL => ['email' => $this->email_failed],
            AutomationType::WHATSAPP => ['Wp' => $this->wp_failed],
            AutomationType::EMAIL_WHATSAPP => [
                'email' => $this->email_failed,
                'Wp' => $this->wp_failed
            ],
        };
    }
}
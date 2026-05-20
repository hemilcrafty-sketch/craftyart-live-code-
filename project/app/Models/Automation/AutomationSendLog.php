<?php

namespace App\Models\Automation;

use App\Enums\ConfigType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AutomationType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\AutomationSendLog
 *
 * @property int $id
 * @property string $campaign_name
 * @property mixed|null $user_ids
 * @property int|null $select_users_type
 * @property int $total
 * @property int $sent
 * @property int $failed
 * @property string $status
 * @property string $type
 * @property int $send_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AutomationSendDetail> $details
 * @property-read int|null $details_count
 * @property-read Collection<int, AutomationSendDetail> $emailDetails
 * @property-read int|null $email_details_count
 * @property-read float $success_rate
 * @property-read Collection<int, AutomationSendDetail> $whatsappDetails
 * @property-read int|null $whatsapp_details_count
 * @method static Builder|AutomationSendLog bySendType(\App\Enums\ConfigType $sendType)
 * @method static Builder|AutomationSendLog email()
 * @method static Builder|AutomationSendLog newModelQuery()
 * @method static Builder|AutomationSendLog newQuery()
 * @method static Builder|AutomationSendLog query()
 * @method static Builder|AutomationSendLog whatsapp()
 * @method static Builder|AutomationSendLog whereCampaignName($value)
 * @method static Builder|AutomationSendLog whereCreatedAt($value)
 * @method static Builder|AutomationSendLog whereFailed($value)
 * @method static Builder|AutomationSendLog whereId($value)
 * @method static Builder|AutomationSendLog whereSelectUsersType($value)
 * @method static Builder|AutomationSendLog whereSendType($value)
 * @method static Builder|AutomationSendLog whereSent($value)
 * @method static Builder|AutomationSendLog whereStatus($value)
 * @method static Builder|AutomationSendLog whereTotal($value)
 * @method static Builder|AutomationSendLog whereType($value)
 * @method static Builder|AutomationSendLog whereUpdatedAt($value)
 * @method static Builder|AutomationSendLog whereUserIds($value)
 * @mixin Eloquent
 */
class AutomationSendLog extends Model
{
    use HasFactory;
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'automation_send_logs';

    protected $fillable = [
        'campaign_name',
        'user_ids',
        'select_users_type',
        'total',
        'sent',
        'failed',
        'status',
        'type',
        'send_type'
    ];

    // Relationships
    public function details(): HasMany
    {
        return $this->hasMany(AutomationSendDetail::class, 'log_id');
    }

    public function emailDetails(): HasMany
    {
        return $this->hasMany(AutomationSendDetail::class, 'log_id')
            ->where('type', AutomationType::EMAIL->value);
    }

    public function whatsappDetails(): HasMany
    {
        return $this->hasMany(AutomationSendDetail::class, 'log_id')
            ->where('type', AutomationType::WHATSAPP->value);
    }

    // Scopes
    public function scopeEmail($query)
    {
        return $query->where('type', AutomationType::EMAIL->value);
    }

    public function scopeWhatsapp($query)
    {
        return $query->where('type', AutomationType::WHATSAPP->value);
    }

    public function scopeBySendType($query, ConfigType $sendType)
    {
        return $query->where('send_type', $sendType->value);
    }

    // Helpers
    public function getSuccessRateAttribute(): float
    {
        if ($this->total === 0) return 0;
        return ($this->sent / $this->total) * 100;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}

<?php
// app/Models/AutomationSendDetail.php

namespace App\Models;

use App\Enums\ConfigType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AutomationType;
use Illuminate\Support\Carbon;

/**
 * App\Models\AutomationSendDetail
 *
 * @property int $id
 * @property int $log_id
 * @property int|null $user_id
 * @property AutomationType $type
 * @property ConfigType $send_type
 * @property string|null $email
 * @property string|null $contact_number
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $channel_name
 * @property-read \App\Models\AutomationSendLog|null $log
 * @property-read \App\Models\User|null $user
 * @method static Builder|AutomationSendDetail bySendType(\App\Enums\ConfigType $sendType)
 * @method static Builder|AutomationSendDetail email()
 * @method static Builder|AutomationSendDetail failed()
 * @method static Builder|AutomationSendDetail newModelQuery()
 * @method static Builder|AutomationSendDetail newQuery()
 * @method static Builder|AutomationSendDetail query()
 * @method static Builder|AutomationSendDetail sent()
 * @method static Builder|AutomationSendDetail whatsapp()
 * @method static Builder|AutomationSendDetail whereContactNumber($value)
 * @method static Builder|AutomationSendDetail whereCreatedAt($value)
 * @method static Builder|AutomationSendDetail whereEmail($value)
 * @method static Builder|AutomationSendDetail whereErrorMessage($value)
 * @method static Builder|AutomationSendDetail whereId($value)
 * @method static Builder|AutomationSendDetail whereLogId($value)
 * @method static Builder|AutomationSendDetail whereSendType($value)
 * @method static Builder|AutomationSendDetail whereStatus($value)
 * @method static Builder|AutomationSendDetail whereType($value)
 * @method static Builder|AutomationSendDetail whereUpdatedAt($value)
 * @method static Builder|AutomationSendDetail whereUserId($value)
 * @mixin \Eloquent
 */
class AutomationSendDetail extends Model
{
    use HasFactory;
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'automation_send_details';

    protected $fillable = [
        'log_id',
        'user_id',
        'type',
        'send_type',
        'email',
        'contact_number',
        'status',
        'error_message'
    ];

    protected $casts = [
        'template_params' => 'array',
        'type' => AutomationType::class,
        'send_type' => ConfigType::class,
    ];

    // Relationships
    public function log()
    {
        return $this->belongsTo(AutomationSendLog::class, 'log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeBySendType($query, ConfigType $sendType)
    {
        return $query->where('send_type', $sendType->value);
    }

    // Helpers
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getChannelNameAttribute(): string
    {
        return $this->type === AutomationType::EMAIL ? 'Email' : 'WhatsApp';
    }
}
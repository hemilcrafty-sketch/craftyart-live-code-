<?php

namespace App\Models\Automation;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Models\User;
use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Automation\CampaignFailedDetail
 *
 * @property int $id
 * @property string $log_id
 * @property int $user_id
 * @property string|null $email
 * @property string|null $contact_no
 * @property string $status
 * @property string $error_message
 * @property AutomationType $type
 * @property ConfigType $send_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $channel_name
 * @property-read CampaignSendLog|null $log
 * @property-read User|null $user
 * @method static Builder|CampaignFailedDetail whereContactNo($value)
 * @method static Builder|CampaignFailedDetail whereCreatedAt($value)
 * @method static Builder|CampaignFailedDetail whereEmail($value)
 * @method static Builder|CampaignFailedDetail whereErrorMessage($value)
 * @method static Builder|CampaignFailedDetail whereId($value)
 * @method static Builder|CampaignFailedDetail whereLogId($value)
 * @method static Builder|CampaignFailedDetail whereSendType($value)
 * @method static Builder|CampaignFailedDetail whereStatus($value)
 * @method static Builder|CampaignFailedDetail whereType($value)
 * @method static Builder|CampaignFailedDetail whereUpdatedAt($value)
 * @method static Builder|CampaignFailedDetail whereUserId($value)
 * @mixin Eloquent
 */
class CampaignFailedDetail extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $fillable = [
        'log_id',
        'user_id',
        'email',
        'contact_no',
        'status',
        'send_type',
        'type',
        'error_message'
    ];
    use HasFactory;


    // Relationships
    public function log(): BelongsTo
    {
        return $this->belongsTo(CampaignSendLog::class, 'log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id');
    }

}

<?php

namespace App\Models\Automation;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\MailSendDetail
 *
 * @property int $id
 * @property string $log_id
 * @property int $user_id
 * @property string $email
 * @property string $status
 * @property string $error_message
 * @property int $send_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|MailSendDetail newModelQuery()
 * @method static Builder|MailSendDetail newQuery()
 * @method static Builder|MailSendDetail query()
 * @method static Builder|MailSendDetail whereCreatedAt($value)
 * @method static Builder|MailSendDetail whereEmail($value)
 * @method static Builder|MailSendDetail whereErrorMessage($value)
 * @method static Builder|MailSendDetail whereId($value)
 * @method static Builder|MailSendDetail whereLogId($value)
 * @method static Builder|MailSendDetail whereSendType($value)
 * @method static Builder|MailSendDetail whereStatus($value)
 * @method static Builder|MailSendDetail whereUpdatedAt($value)
 * @method static Builder|MailSendDetail whereUserId($value)
 * @mixin Eloquent
 */
class MailSendDetail extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $fillable = [
        'log_id',
        'user_id',
        'email',
        'status',
        'send_type',
        'error_message'
    ];
    use HasFactory;
}

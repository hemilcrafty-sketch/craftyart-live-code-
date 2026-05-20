<?php

namespace App\Models\Automation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\MailSendLog
 *
 * @property int $id
 * @property string $subject
 * @property string $email_template_id
 * @property array $user_ids
 * @property int $select_users_type 1 = All User
 * 2 = Premium User
 * 3 = Custom Selected User
 * @property int $auto_pause_count
 * @property int $total
 * @property int $sent
 * @property int $failed
 * @property string $status
 * @property int $stopped
 * @property int $emails_sent_since_last_pause
 * @property string|null $last_processed_user_id
 * @property string|null $pause_type
 * @property int $auto_resume 0 = Next day mail will be not sent Automatically
 * 1 = Next day mail will be sent Automatically
 * @property int $send_type 1 = Custom Campaign Mail Send
 * 2 = Checkout Drop User Instant // not in used
 * 3 = Checkout Drop User Frequency mail
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $promo_code
 * @property int $plan_id
 * @method static Builder|MailSendLog newModelQuery()
 * @method static Builder|MailSendLog newQuery()
 * @method static Builder|MailSendLog query()
 * @method static Builder|MailSendLog whereAutoPauseCount($value)
 * @method static Builder|MailSendLog whereAutoResume($value)
 * @method static Builder|MailSendLog whereCreatedAt($value)
 * @method static Builder|MailSendLog whereEmailTemplateId($value)
 * @method static Builder|MailSendLog whereEmailsSentSinceLastPause($value)
 * @method static Builder|MailSendLog whereFailed($value)
 * @method static Builder|MailSendLog whereId($value)
 * @method static Builder|MailSendLog whereLastProcessedUserId($value)
 * @method static Builder|MailSendLog wherePauseType($value)
 * @method static Builder|MailSendLog whereSelectUsersType($value)
 * @method static Builder|MailSendLog whereSendType($value)
 * @method static Builder|MailSendLog whereSent($value)
 * @method static Builder|MailSendLog whereStatus($value)
 * @method static Builder|MailSendLog whereStopped($value)
 * @method static Builder|MailSendLog whereSubject($value)
 * @method static Builder|MailSendLog whereTotal($value)
 * @method static Builder|MailSendLog whereUpdatedAt($value)
 * @method static Builder|MailSendLog whereUserIds($value)
 * @method static Builder|MailSendLog wherePromoCode($value)
 * @method static Builder|MailSendLog wherePlanId($value)
 * @mixin Eloquent
 */
class MailSendLog extends Model
{
    protected $connection = 'crafty_automation_mysql';
    protected $table = 'mail_send_logs';

    protected $fillable = ['subject', 'email_template_id','user_ids','plan_id','select_users_type','promo_code', 'auto_pause_count' , 'total', 'sent', 'failed', 'status','stopped','emails_sent_since_last_pause','last_processed_user_id','pause_type','auto_resume','send_type'];

    public function getUserIdsAttribute($user_ids): array
    {
        return $user_ids === null ? [] : json_decode($user_ids, true);
    }

}

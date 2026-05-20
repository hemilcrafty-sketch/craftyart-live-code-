<?php

namespace App\Models;

use App\Models\Video\VideoPurchaseHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * App\Models\UserData
 *
 * @property int $id
 * @property string|null $uid
 * @property string $refer_id
 * @property string|null $stripe_cus_id
 * @property string|null $razorpay_cus_id
 * @property string|null $photo_uri
 * @property string|null $device_limit
 * @property string $name
 * @property string|null $country_code
 * @property string|null $number
 * @property string|null $user_name
 * @property string|null $is_username_update
 * @property string|null $email
 * @property string|null $email_preferance
 * @property string|null $bio
 * @property string|null $subscription
 * @property string|null $login_type
 * @property string|null $total_validity
 * @property string|null $validity
 * @property int|null $is_premium
 * @property int|null $special_user
 * @property int $can_update
 * @property int $web_update
 * @property int $cheap_rate
 * @property int|null $status
 * @property int $creator
 * @property int $profile_count
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property int|null $coins
 * @property string|null $device_id
 * @property string|null $fldr_str
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, PurchaseHistory> $templatePurchaseLogs
 * @property-read int|null $template_purchase_logs_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, TransactionLog> $transactionLogs
 * @property-read int|null $transaction_logs_count
 * @property-read Collection<int, VideoPurchaseHistory> $videPurchaseLogs
 * @property-read int|null $vide_purchase_logs_count
 * @method static Builder|UserData newModelQuery()
 * @method static Builder|UserData newQuery()
 * @method static Builder|UserData query()
 * @method static Builder|UserData whereBio($value)
 * @method static Builder|UserData whereCanUpdate($value)
 * @method static Builder|UserData whereCheapRate($value)
 * @method static Builder|UserData whereCoins($value)
 * @method static Builder|UserData whereCountryCode($value)
 * @method static Builder|UserData whereCreatedAt($value)
 * @method static Builder|UserData whereCreator($value)
 * @method static Builder|UserData whereDeviceId($value)
 * @method static Builder|UserData whereDeviceLimit($value)
 * @method static Builder|UserData whereEmail($value)
 * @method static Builder|UserData whereEmailPreferance($value)
 * @method static Builder|UserData whereFldrStr($value)
 * @method static Builder|UserData whereId($value)
 * @method static Builder|UserData whereIsPremium($value)
 * @method static Builder|UserData whereIsUsernameUpdate($value)
 * @method static Builder|UserData whereLoginType($value)
 * @method static Builder|UserData whereName($value)
 * @method static Builder|UserData whereNumber($value)
 * @method static Builder|UserData wherePhotoUri($value)
 * @method static Builder|UserData whereProfileCount($value)
 * @method static Builder|UserData whereRazorpayCusId($value)
 * @method static Builder|UserData whereReferId($value)
 * @method static Builder|UserData whereSpecialUser($value)
 * @method static Builder|UserData whereStatus($value)
 * @method static Builder|UserData whereStripeCusId($value)
 * @method static Builder|UserData whereSubscription($value)
 * @method static Builder|UserData whereTotalValidity($value)
 * @method static Builder|UserData whereUid($value)
 * @method static Builder|UserData whereUpdatedAt($value)
 * @method static Builder|UserData whereUserName($value)
 * @method static Builder|UserData whereUtmMedium($value)
 * @method static Builder|UserData whereUtmSource($value)
 * @method static Builder|UserData whereValidity($value)
 * @method static Builder|UserData whereWebUpdate($value)
 * @mixin \Eloquent
 */
class UserData extends Authenticatable
{
    protected $connection = 'mysql';
    protected $table = 'user_data'; // Specify correct table name (singular)

    use HasApiTokens, HasFactory, Notifiable;


    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class, 'user_id', 'uid');
    }

    public function latestTransactionLog()
    {
        return $this->hasOne(TransactionLog::class, 'user_id', 'uid')->latest();
    }

    public function getReviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'uid');
    }

    public function templatePurchaseLogs()
    {
        return $this->hasMany(PurchaseHistory::class, 'user_id', 'uid');
    }

    public function videPurchaseLogs()
    {
        return $this->hasMany(VideoPurchaseHistory::class, 'user_id', 'uid');
    }

    public function personalDetails()
    {
        return $this->hasOne(PersonalDetails::class, 'uid', 'uid');
    }
}

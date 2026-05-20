<?php

namespace App\Models;

use App\Http\Controllers\Utils\HelperController;
use App\Models\Video\VideoPurchaseHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserData
 *
 * @property int $id
 * @property string $uid
 * @property string|null $user_name
 * @property string|null $bio
 * @property int $is_username_update
 * @property string $refer_id
 * @property string|null $stripe_cus_id
 * @property string|null $razorpay_cus_id
 * @property string|null $photo_uri
 * @property string $name
 * @property string|null $country_code
 * @property string|null $number
 * @property string|null $contact_no
 * @property int $contact_no_verified
 * @property string|null $email
 * @property string|null $password
 * @property string $login_type
 * @property int $total_validity
 * @property int $validity
 * @property int $ai_credit
 * @property int|null $is_premium
 * @property int $business_user
 * @property int|null $special_user
 * @property int $can_update
 * @property int $web_update
 * @property int $cheap_rate
 * @property int $creator
 * @property int $hoc head of creator
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property int|null $coins
 * @property string|null $device_id
 * @property string|null $fldr_str
 * @property string|null $email_preferance
 * @property int $profile_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PurchaseHistory> $templatePurchaseLogs
 * @property-read int|null $template_purchase_logs_count
 * @property-read Collection<int, TransactionLog> $transactionLogs
 * @property-read int|null $transaction_logs_count
 * @property-read Collection<int, VideoPurchaseHistory> $videPurchaseLogs
 * @property-read int|null $vide_purchase_logs_count
 * @method static Builder|UserData newModelQuery()
 * @method static Builder|UserData newQuery()
 * @method static Builder|UserData query()
 * @method static Builder|UserData with($value)
 * @method static Builder|UserData whereBio($value)
 * @method static Builder|UserData whereCanUpdate($value)
 * @method static Builder|UserData whereCheapRate($value)
 * @method static Builder|UserData whereCoins($value)
 * @method static Builder|UserData whereCountryCode($value)
 * @method static Builder|UserData whereCreatedAt($value)
 * @method static Builder|UserData whereCreator($value)
 * @method static Builder|UserData whereDeviceId($value)
 * @method static Builder|UserData whereEmail($value)
 * @method static Builder|UserData whereEmailPreferance($value)
 * @method static Builder|UserData whereFldrStr($value)
 * @method static Builder|UserData whereHoc($value)
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
 * @method static Builder|UserData whereStripeCusId($value)
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
class UserData extends Model
{
	protected $connection = 'mysql';
    use HasFactory;

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
       return  $this->hasMany(Review::class,'user_id','uid');
    }

    public function templatePurchaseLogs()
    {
        return $this->hasMany(PurchaseHistory::class,'user_id','uid');
    }

    public function videPurchaseLogs()
    {
        return $this->hasMany(VideoPurchaseHistory::class,'user_id','uid');
    }

    public static function generateUid(): string
    {
            return HelperController::generateRandomId(length: 20, modelSource: self::class, column: 'uid');
    }
}

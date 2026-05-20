<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserDataDeleted
 *
 * @property int $id
 * @property int $user_int_id
 * @property string $uid
 * @property string $refer_id
 * @property string|null $stripe_cus_id
 * @property string|null $razorpay_cus_id
 * @property string|null $photo_uri
 * @property string $name
 * @property string|null $country_code
 * @property string|null $number
 * @property string|null $email
 * @property string $login_type
 * @property int $total_validity
 * @property int $validity
 * @property int $ai_credit
 * @property int|null $is_premium
 * @property int|null $special_user
 * @property int $can_update
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property int|null $coins
 * @property string|null $device_id
 * @property string|null $fldr_str
 * @property string $creation_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|UserDataDeleted newModelQuery()
 * @method static Builder|UserDataDeleted newQuery()
 * @method static Builder|UserDataDeleted query()
 * @method static Builder|UserDataDeleted whereCanUpdate($value)
 * @method static Builder|UserDataDeleted whereCoins($value)
 * @method static Builder|UserDataDeleted whereCountryCode($value)
 * @method static Builder|UserDataDeleted whereCreatedAt($value)
 * @method static Builder|UserDataDeleted whereCreationDate($value)
 * @method static Builder|UserDataDeleted whereDeviceId($value)
 * @method static Builder|UserDataDeleted whereEmail($value)
 * @method static Builder|UserDataDeleted whereFldrStr($value)
 * @method static Builder|UserDataDeleted whereId($value)
 * @method static Builder|UserDataDeleted whereIsPremium($value)
 * @method static Builder|UserDataDeleted whereLoginType($value)
 * @method static Builder|UserDataDeleted whereName($value)
 * @method static Builder|UserDataDeleted whereNumber($value)
 * @method static Builder|UserDataDeleted wherePhotoUri($value)
 * @method static Builder|UserDataDeleted whereRazorpayCusId($value)
 * @method static Builder|UserDataDeleted whereReferId($value)
 * @method static Builder|UserDataDeleted whereSpecialUser($value)
 * @method static Builder|UserDataDeleted whereStripeCusId($value)
 * @method static Builder|UserDataDeleted whereTotalValidity($value)
 * @method static Builder|UserDataDeleted whereUid($value)
 * @method static Builder|UserDataDeleted whereUpdatedAt($value)
 * @method static Builder|UserDataDeleted whereUserIntId($value)
 * @method static Builder|UserDataDeleted whereUtmMedium($value)
 * @method static Builder|UserDataDeleted whereUtmSource($value)
 * @method static Builder|UserDataDeleted whereValidity($value)
 * @mixin \Eloquent
 */
class UserDataDeleted extends Model
{
	protected $table = 'user_data_deleted';
	protected $connection = 'mysql';
    use HasFactory;
}

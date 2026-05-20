<?php

namespace App\Models;

use App\Http\Controllers\Utils\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserSession
 *
 * @property int $id
 * @property int $user_id
 * @property string $device_id
 * @property int|null $token_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $last_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read UserData|null $user
 * @method static Builder|UserSession newModelQuery()
 * @method static Builder|UserSession newQuery()
 * @method static Builder|UserSession query()
 * @method static Builder|UserSession whereCreatedAt($value)
 * @method static Builder|UserSession whereDeviceId($value)
 * @method static Builder|UserSession whereId($value)
 * @method static Builder|UserSession whereIpAddress($value)
 * @method static Builder|UserSession whereLastActive($value)
 * @method static Builder|UserSession whereTokenId($value)
 * @method static Builder|UserSession whereUpdatedAt($value)
 * @method static Builder|UserSession whereUserAgent($value)
 * @method static Builder|UserSession whereUserId($value)
 * @mixin \Eloquent
 */
class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'device_id',
        'token_id',
        'custom_token',
        'ip_address',
        'user_agent',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id');
    }

    public static function generateDeviceId(): string
    {
        $deviceId = HelperController::generateID(length: 20);
        while (UserSession::whereDeviceId($deviceId)->exists()) {
            $deviceId = HelperController::generateID(length: 20);
        }
        return $deviceId;
    }

}

<?php

namespace App\Models\Vendor;

use App\Http\Controllers\Utils\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\vendor\tenants
 *
 * @property int $id
 * @property string $domain
 * @property string $user_id
 * @property string $type
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Tenants newModelQuery()
 * @method static Builder|Tenants newQuery()
 * @method static Builder|Tenants query()
 * @method static Builder|Tenants whereCreatedAt($value)
 * @method static Builder|Tenants whereDomain($value)
 * @method static Builder|Tenants whereId($value)
 * @method static Builder|Tenants whereStatus($value)
 * @method static Builder|Tenants whereType($value)
 * @method static Builder|Tenants whereUpdatedAt($value)
 * @method static Builder|Tenants whereUserId($value)
 * @mixin \Eloquent
 */
class Tenants extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'tenants';

    protected $guarded = [];

    public static function generateSubDomain(): string
    {
        do {
            $subDomain = HelperController::generateRandomId(postfix: '.craftyartapp.in', stringType: 'lower');
        } while (self::whereDomain($subDomain)->exists());

        return $subDomain;
    }
}
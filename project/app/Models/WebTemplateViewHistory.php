<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\WebTemplateViewHistory
 *
 * @property int $id
 * @property string|null $user_id
 * @property string $product_id
 * @property string|null $ip_address
 * @property string|null $country
 * @property string|null $fbc
 * @property string|null $fbp
 * @property string|null $gclid
 * @property string|null $gcl_au
 * @property string|null $ga
 * @property string|null $userAgent
 * @property string|null type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|WebTemplateViewHistory newModelQuery()
 * @method static Builder|WebTemplateViewHistory newQuery()
 * @method static Builder|WebTemplateViewHistory query()
 * @method static Builder|WebTemplateViewHistory whereCountry($value)
 * @method static Builder|WebTemplateViewHistory whereCreatedAt($value)
 * @method static Builder|WebTemplateViewHistory whereFbc($value)
 * @method static Builder|WebTemplateViewHistory whereFbp($value)
 * @method static Builder|WebTemplateViewHistory whereGa($value)
 * @method static Builder|WebTemplateViewHistory whereGclAu($value)
 * @method static Builder|WebTemplateViewHistory whereGclid($value)
 * @method static Builder|WebTemplateViewHistory whereId($value)
 * @method static Builder|WebTemplateViewHistory whereIpAddress($value)
 * @method static Builder|WebTemplateViewHistory whereProductId($value)
 * @method static Builder|WebTemplateViewHistory whereUpdatedAt($value)
 * @method static Builder|WebTemplateViewHistory whereUserAgent($value)
 * @method static Builder|WebTemplateViewHistory whereUserId($value)
 * @method static Builder|WebTemplateViewHistory whereType($value)
 * @mixin Eloquent
 */
class WebTemplateViewHistory extends Model
{
	protected $table = 'template_view_history';
	protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'product_id',
        'ip_address',
        'country',
        'fbc',
        'fbp',
        'gclid',
        'gcl_au',
        'ga',
        'userAgent',
        'type',
    ];

    use HasFactory;
}

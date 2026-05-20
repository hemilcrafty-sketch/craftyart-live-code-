<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Vendor\VendorTemplate
 *
 * @property int $id
 * @property string $user_id
 * @property string $template_id
 * @property string $price
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|VendorTemplate newModelQuery()
 * @method static Builder|VendorTemplate newQuery()
 * @method static Builder|VendorTemplate query()
 * @method static Builder|VendorTemplate whereCreatedAt($value)
 * @method static Builder|VendorTemplate whereCurrency($value)
 * @method static Builder|VendorTemplate whereId($value)
 * @method static Builder|VendorTemplate wherePrice($value)
 * @method static Builder|VendorTemplate whereTemplateId($value)
 * @method static Builder|VendorTemplate whereUpdatedAt($value)
 * @method static Builder|VendorTemplate whereUserId($value)
 * @mixin \Eloquent
 */
class VendorTemplate extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'vendor_template';

    protected $fillable = [
        'user_id',
        'template_id',
        'price',
        'currency'
    ];
}

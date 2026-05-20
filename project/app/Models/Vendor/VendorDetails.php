<?php

namespace App\Models\Vendor;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Vendor\VendorDetails
 *
 * @property int $id
 * @property string $user_id
 * @property string $description
 * @property string $contact_no
 * @property string $brand_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|VendorDetails newModelQuery()
 * @method static Builder|VendorDetails newQuery()
 * @method static Builder|VendorDetails query()
 * @method static Builder|VendorDetails whereBrandName($value)
 * @method static Builder|VendorDetails whereContactNo($value)
 * @method static Builder|VendorDetails whereCreatedAt($value)
 * @method static Builder|VendorDetails whereDescription($value)
 * @method static Builder|VendorDetails whereId($value)
 * @method static Builder|VendorDetails whereUpdatedAt($value)
 * @method static Builder|VendorDetails whereUserId($value)
 * @mixin Eloquent
 */
class VendorDetails extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'vendor_details';

    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];



}

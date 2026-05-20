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
 * @property string|null $title
 * @property string|null $description
 * @property string|null $contact_no
 * @property string|null $brand_name
 * @property string|null $owner_name
 * @property string|null $email
 * @property string|null $instagram_url
 * @property string|null $facebook_url
 * @property string|null $website_url
 * @property string|null $business_logo
 * @property string|null $owner_profile_photo
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

    protected $appends = ['business_logo_url', 'owner_profile_photo_url'];

    public function getBusinessLogoUrlAttribute(): ?string
    {
        if (!$this->business_logo) return null;
        return str_contains($this->business_logo, 'http') ? $this->business_logo : \App\Http\Controllers\Utils\HelperController::$mediaUrl . $this->business_logo;
    }

    public function getOwnerProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->owner_profile_photo) return null;
        return str_contains($this->owner_profile_photo, 'http') ? $this->owner_profile_photo : \App\Http\Controllers\Utils\HelperController::$mediaUrl . $this->owner_profile_photo;
    }
}

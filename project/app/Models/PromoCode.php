<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $connection = 'mysql';
    public const FILLABLE_FIELDS = [
        "user_id",
        'promo_code',
        'disc',
        'additional_days',
        'type',
        'status',
        'expiry_date',
        'disc_upto_inr',
        'min_cart_inr',
        'disc_upto_usd',
        'min_cart_usd',
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function getUserNamesAttribute()
    {
        $ids = json_decode($this->user_id, true);
        if (empty($ids) || !is_array($ids)) {
            return 'Not Applicable';
        }
        $emails = UserData::whereIn('id', $ids)->pluck('email');
        return $emails->isNotEmpty() ? $emails->implode('<br>') : 'Not Applicable';
    }
}
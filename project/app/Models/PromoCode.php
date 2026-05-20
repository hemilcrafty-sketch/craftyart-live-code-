<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $connection = 'mysql';
    // Add the fields that can be mass-assigned
    protected $fillable = [
        "user_id",
        'code',
        'disc',
        'purchase_type',
        'status',
        'expiry_date',
        'disc_upto_inr',
        'min_cart_inr',
        'disc_upto_usd',
        'min_cart_usd',
    ];
}
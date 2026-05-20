<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Wallet / payout configuration stored in crafty_vendor (vendor + designer withdrawal rules).
 *
 * @property int $id
 * @property string $setting_key
 * @property string|null $setting_name
 * @property string|null $description
 * @property string $min_withdrawal_threshold
 * @property string|null $max_withdrawal_limit
 * @property string $platform_commission_rate
 * @property string $payment_type manual|razorpay
 * @property bool $is_active
 */
class WalletSetting extends Model
{
    protected $connection = 'crafty_vendor_mysql';

    protected $table = 'wallet_settings';

    protected $fillable = [
        'setting_key',
        'setting_name',
        'description',
        'min_withdrawal_threshold',
        'max_withdrawal_limit',
        'platform_commission_rate',
        'referral_commission_rate',
        'payment_type',
        'is_active',
    ];

    protected $casts = [
        'min_withdrawal_threshold' => 'decimal:2',
        'max_withdrawal_limit' => 'decimal:2',
        'platform_commission_rate' => 'decimal:2',
        'referral_commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function getDefault(): Builder|null
    {
        return self::query()
            ->where('setting_key', 'default')
            ->where('is_active', true)
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isManualPayout(): bool
    {
        return $this->payment_type === 'manual';
    }

    public function isRazorpayPayout(): bool
    {
        return $this->payment_type === 'razorpay';
    }
}

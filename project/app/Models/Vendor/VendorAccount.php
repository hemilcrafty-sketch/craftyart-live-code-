<?php

namespace App\Models\Vendor;

use App\Http\Controllers\Utils\HelperController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorAccount extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'vendor_accounts';

    protected $fillable = [
        'string_id',
        'user_id',
        'contact_id',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (VendorAccount $account): void {
            if ($account->string_id === null || $account->string_id === '') {
                $account->string_id = HelperController::generateRandomId(modelSource: self::class, stringType: 'lower');
            }
        });
    }

    /**
     * Revenue ledger rows (crafty_vendor.revenue_history) for this account.
     */
    public function history(): HasMany
    {
        return $this->hasMany(RevenueHistory::class, 'user_id', 'user_id');
    }

    /**
     * Get withdrawals for this account
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(VendorWithdraw::class, 'user_id', 'user_id');
    }
}

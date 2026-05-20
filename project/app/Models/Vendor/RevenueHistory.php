<?php

namespace App\Models\Vendor;

use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\UserData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueHistory extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'revenue_history';

    protected $fillable = [
        'string_id',
        'purchase_id',
        'payout_reference',
        'user_id',
        'vendor_amount',
        'vendor_percentage',
        'purchase_user_id',
        'purchase_amount',
        'currency',
        'type',
        'vendor_type',
        'status',
    ];

    protected $casts = [
        'purchase_id' => 'integer',
        'vendor_amount' => 'integer',
        'vendor_percentage' => 'integer',
        'purchase_amount' => 'integer',
    ];

    /**
     * Revenue purchase row (crafty_revenue_mysql.purchase_history).
     */
    public function purchaseHistory(): BelongsTo
    {
        return $this->belongsTo(MasterPurchaseHistory::class, 'purchase_id');
    }

    /**
     * User who made the purchase (buyer UID in user_data.uid).
     */
    public function purchaseUser(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'purchase_user_id', 'uid');
    }

    public function isAffiliate(): bool
    {
        return $this->vendor_type === 'affiliate';
    }

    public function isFreelancer(): bool
    {
        return $this->vendor_type === 'freelancer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

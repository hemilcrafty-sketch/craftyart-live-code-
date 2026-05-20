<?php

namespace App\Models\Vendor;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorWithdraw extends Model
{
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'vendor_withdraw';

    protected $fillable = [
        'string_id',
        'user_id',
        'bank_details_id',
        'amount',
        'razorpay_payout_id',
        'utr',
        'currency',
        'vendor_type',
        'status',
        'completed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the bank details for this withdrawal
     */
    public function bankDetails(): BelongsTo
    {
        return $this->belongsTo(UserBankDetails::class, 'bank_details_id', 'string_id');
    }

    /**
     * Get the user data for this withdrawal
     */
    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    /**
     * Alias for userData() - used by admin panel
     */
    public function user(): BelongsTo
    {
        return $this->userData();
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if withdrawal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if withdrawal is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if withdrawal failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if withdrawal was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if this is an affiliate withdrawal
     */
    public function isAffiliate(): bool
    {
        return $this->vendor_type === 'affiliate';
    }

    /**
     * Check if this is a freelancer withdrawal
     */
    public function isFreelancer(): bool
    {
        return $this->vendor_type === 'freelancer';
    }
}

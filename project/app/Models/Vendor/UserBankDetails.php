<?php

namespace App\Models\Vendor;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;


/**
 * App\Models\Vendor\UserBankDetails
 *
 * @property int $id
 * @property string $string_id
 * @property string $user_id
 * @property string|null $razorpay_bank_account_id
 * @property int $withdraw_type 0 For Bank Transfer and 1 For Upi
 * @property string|null $bank_name
 * @property string|null $bank_holder_name
 * @property string|null $bank_account_number
 * @property string|null $ifsc_code
 * @property string $status
 * @property string|null $upi
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|UserBankDetails newModelQuery()
 * @method static Builder|UserBankDetails newQuery()
 * @method static Builder|UserBankDetails query()
 * @method static Builder|UserBankDetails whereBankAccountNumber($value)
 * @method static Builder|UserBankDetails whereBankHolderName($value)
 * @method static Builder|UserBankDetails whereBankName($value)
 * @method static Builder|UserBankDetails whereCreatedAt($value)
 * @method static Builder|UserBankDetails whereId($value)
 * @method static Builder|UserBankDetails whereIfscCode($value)
 * @method static Builder|UserBankDetails whereRazorpayBankAccountId($value)
 * @method static Builder|UserBankDetails whereStatus($value)
 * @method static Builder|UserBankDetails whereStringId($value)
 * @method static Builder|UserBankDetails whereUpdatedAt($value)
 * @method static Builder|UserBankDetails whereUpi($value)
 * @method static Builder|UserBankDetails whereUserId($value)
 * @method static Builder|UserBankDetails whereWithdrawType($value)
 * @mixin \Eloquent
 */
class UserBankDetails extends Model
{
    use HasFactory;
    protected $connection = 'crafty_vendor_mysql';
    protected $table = 'user_bank_details';

    protected $fillable = [
        'string_id',
        'user_id',
        'bank_name',
        'bank_holder_name',
        'bank_account_number',
        'ifsc_code',
        'status',
        'upi',
        'razorpay_bank_account_id',
        'withdraw_type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user data for this bank detail
     */
    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }
}


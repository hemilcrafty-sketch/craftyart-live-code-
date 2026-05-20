<?php

namespace App\Models\Revenue;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Revenue\CustomLead
 *
 * @property int $id
 * @property int|null $emp_id Assigned employee ID
 * @property string $cosmofeed_transaction_id _id from cosmofeed API
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $country_code
 * @property string $amount_paid
 * @property string $currency
 * @property string|null $product_title
 * @property array|null $product_quantity
 * @property string $refund_status
 * @property string $payment_status
 * @property Carbon|null $transaction_date
 * @property int $followup_call
 * @property string|null $followup_note
 * @property string|null $followup_label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $employee
 * @method static Builder|CustomLead newModelQuery()
 * @method static Builder|CustomLead newQuery()
 * @method static Builder|CustomLead query()
 * @method static Builder|CustomLead whereAmountPaid($value)
 * @method static Builder|CustomLead whereCosmofeedTransactionId($value)
 * @method static Builder|CustomLead whereCountryCode($value)
 * @method static Builder|CustomLead whereCreatedAt($value)
 * @method static Builder|CustomLead whereCurrency($value)
 * @method static Builder|CustomLead whereEmail($value)
 * @method static Builder|CustomLead whereEmpId($value)
 * @method static Builder|CustomLead whereFollowupCall($value)
 * @method static Builder|CustomLead whereFollowupLabel($value)
 * @method static Builder|CustomLead whereFollowupNote($value)
 * @method static Builder|CustomLead whereId($value)
 * @method static Builder|CustomLead wherePaymentStatus($value)
 * @method static Builder|CustomLead wherePhone($value)
 * @method static Builder|CustomLead whereProductQuantity($value)
 * @method static Builder|CustomLead whereProductTitle($value)
 * @method static Builder|CustomLead whereRefundStatus($value)
 * @method static Builder|CustomLead whereTransactionDate($value)
 * @method static Builder|CustomLead whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomLead extends Model
{
    use HasFactory;

    protected $connection = 'crafty_revenue_mysql';
    protected $table = 'custom_leads';

    protected $fillable = [
        'cosmofeed_transaction_id',
        'email',
        'phone',
        'country_code',
        'amount_paid',
        'currency',
        'product_title',
        'product_quantity',
        'refund_status',
        'payment_status',
        'transaction_date',
        'followup_call',
        'followup_note',
        'followup_label',
        'emp_id',
    ];

    protected $casts = [
        'product_quantity' => 'array',
        'transaction_date' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Get the employee assigned to this lead
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }
}

<?php

namespace App\Models\AI;

use Eloquent;
use App\Models\UserData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Http\Controllers\HelperController;

/**
 * App\Models\Caricature\AICreditTransaction
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $ref_id
 * @property string $type
 * @property string $reason
 * @property string $debited
 * @property string $credited
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AICreditTransaction newModelQuery()
 * @method static Builder|AICreditTransaction newQuery()
 * @method static Builder|AICreditTransaction query()
 * @method static Builder|AICreditTransaction whereUserId($value)
 * @method static Builder|AICreditTransaction whereRefId($value)
 * @method static Builder|AICreditTransaction whereReason($value)
 * @method static Builder|AICreditTransaction whereCreatedAt($value)
 * @method static Builder|AICreditTransaction whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AICreditTransaction extends Model
{
    protected $connection = 'crafty_ai_mysql';
    protected $table = 'credit_transaction';

    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    public static function generateTxnId(): string
    {
        $txnId = HelperController::generateID('txn_');
        while (AICreditTransaction::whereTxnId($txnId)->exists()) {
            $txnId = HelperController::generateID('txn_');
        }
        return $txnId;
    }
}

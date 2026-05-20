<?php

namespace App\Models\AI;

use App\Http\Controllers\Utils\HelperController;
use App\Models\Order;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Caricature\AICreditTransaction
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $ref_id
 * @property string $txn_id
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
 * @method static Builder|AICreditTransaction whereTxnId($value)
 * @method static Builder|AICreditTransaction whereType($value)
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

    public static function generateTxnId(): string
    {
        $txnId = HelperController::generateID('txn_');
        while (AICreditTransaction::whereTxnId($txnId)->exists()) {
            $txnId = HelperController::generateID('txn_');
        }
        return $txnId;
    }
}

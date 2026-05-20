<?php

namespace App\Models\AI;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Models\AiCredit
 *
 * @property int $id
 * @property int $credits
 * @property int $disc
 * @property int $inr_price
 * @property int $usd_price
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|AiCredit newModelQuery()
 * @method static Builder|AiCredit newQuery()
 * @method static Builder|AiCredit query()
 * @method static Builder|AiCredit whereCreatedAt($value)
 * @method static Builder|AiCredit whereCredits($value)
 * @method static Builder|AiCredit whereDisc($value)
 * @method static Builder|AiCredit whereId($value)
 * @method static Builder|AiCredit whereInrPrice($value)
 * @method static Builder|AiCredit whereStatus($value)
 * @method static Builder|AiCredit whereUpdatedAt($value)
 * @method static Builder|AiCredit whereUsdPrice($value)
 * @mixin Eloquent
 */
class AiCredit extends Model
{
    use HasFactory;

    protected $connection = 'crafty_ai_mysql';
    protected $table = 'ai_credits';

    protected $fillable = [
        'credits',
        'disc',
        'inr_price',
        'usd_price',
        'status'
    ];
}

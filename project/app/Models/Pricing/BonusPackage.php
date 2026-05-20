<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\BonusPackage
 *
 * @property int $id
 * @property string $string_id
 * @property string $bonus_code
 * @property string $inr_price
 * @property string $usd_price
 * @property string $additional_day
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|BonusPackage newModelQuery()
 * @method static Builder|BonusPackage newQuery()
 * @method static Builder|BonusPackage query()
 * @method static Builder|BonusPackage whereAdditionalDay($value)
 * @method static Builder|BonusPackage whereBonusCode($value)
 * @method static Builder|BonusPackage whereCreatedAt($value)
 * @method static Builder|BonusPackage whereId($value)
 * @method static Builder|BonusPackage whereInrPrice($value)
 * @method static Builder|BonusPackage whereStringId($value)
 * @method static Builder|BonusPackage whereUpdatedAt($value)
 * @method static Builder|BonusPackage whereUsdPrice($value)
 * @mixin Eloquent
 */
class BonusPackage extends Model
{
    use HasFactory;
    protected $connection = 'crafty_pricing_mysql';
	protected $table = 'bonus_package';

    protected $fillable = [
        'string_id',
        'bonus_code',
        'inr_price',
        'inr_trial_days',
        'inr_trial_price',
        'usd_price',
        'usd_trial_days',
        'usd_trial_price',
        'additional_day',
    ];
}

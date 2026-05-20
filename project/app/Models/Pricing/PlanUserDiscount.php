<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan\PlanUserDiscount
 *
 * @property int $id
 * @property int $discount_percentage
 * @property float $x
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|PlanUserDiscount newModelQuery()
 * @method static Builder|PlanUserDiscount newQuery()
 * @method static Builder|PlanUserDiscount query()
 * @method static Builder|PlanUserDiscount whereCreatedAt($value)
 * @method static Builder|PlanUserDiscount whereDiscountPercentage($value)
 * @method static Builder|PlanUserDiscount whereId($value)
 * @method static Builder|PlanUserDiscount whereUpdatedAt($value)
 * @method static Builder|PlanUserDiscount whereX($value)
 * @mixin Eloquent
 */
class PlanUserDiscount extends Model
{
    use HasFactory;
    protected $table = 'plan_user_discount';

    protected $connection = 'crafty_pricing_mysql';
    protected $fillable = [
        "discount_percentage",
        "factor",
        "x",
    ];
}

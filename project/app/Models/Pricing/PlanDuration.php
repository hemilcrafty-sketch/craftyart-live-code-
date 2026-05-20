<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Plan\PlanDuration
 *
 * @property int $id
 * @property string $name
 * @property string|null $string_id
 * @property string $duration
 * @property int $is_annual
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|PlanDuration newModelQuery()
 * @method static Builder|PlanDuration newQuery()
 * @method static Builder|PlanDuration query()
 * @method static Builder|PlanDuration whereCreatedAt($value)
 * @method static Builder|PlanDuration whereDuration($value)
 * @method static Builder|PlanDuration whereId($value)
 * @method static Builder|PlanDuration whereIsAnnual($value)
 * @method static Builder|PlanDuration whereName($value)
 * @method static Builder|PlanDuration whereStringId($value)
 * @method static Builder|PlanDuration whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PlanDuration extends Model
{
  use HasFactory;

  protected $table = 'plan_duration';
  protected $connection = 'crafty_pricing_mysql';

  protected $fillable = [
    "name",
    "string_id",
    "duration",
    "is_annual",
  ];

}

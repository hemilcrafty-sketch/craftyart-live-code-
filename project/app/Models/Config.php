<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Config
 *
 * @property int $id
 * @property string $name
 * @property array $value
 * @method static Builder|Config newModelQuery()
 * @method static Builder|Config newQuery()
 * @method static Builder|Config query()
 * @method static Builder|Config whereId($value)
 * @method static Builder|Config whereName($value)
 * @method static Builder|Config whereValue($value)
 * @mixin Eloquent
 */

class Config extends Model
{

    protected $table = 'configs';
    protected $connection = 'crafty_automation_mysql';

    protected $fillable = [
        'name',
        'value',
    ];

    public $timestamps = false; // if your table does not have created_at/updated_at


    public function getValueAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }
}
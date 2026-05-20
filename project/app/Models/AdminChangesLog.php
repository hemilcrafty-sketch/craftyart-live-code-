<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Models\AdminChangesLog
 *
 * @property int $id
 * @property int $emp_id
 * @property string $model
 * @property int $model_id
 * @property string $updated_fields
 * @property string $old_values
 * @property string $ip_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|AdminChangesLog newModelQuery()
 * @method static Builder|AdminChangesLog newQuery()
 * @method static Builder|AdminChangesLog query()
 * @method static Builder|AdminChangesLog whereCreatedAt($value)
 * @method static Builder|AdminChangesLog whereEmpId($value)
 * @method static Builder|AdminChangesLog whereId($value)
 * @method static Builder|AdminChangesLog whereIpAddress($value)
 * @method static Builder|AdminChangesLog whereModel($value)
 * @method static Builder|AdminChangesLog whereModelId($value)
 * @method static Builder|AdminChangesLog whereUpdatedFields($value)
 * @method static Builder|AdminChangesLog whereUpdatedAt($value)
 * @method static Builder|AdminChangesLog whereOldValues($value)
 * @mixin \Eloquent
 */
class AdminChangesLog extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'admin_changes_log';

    protected $fillable = [
        'emp_id',
        'model',
        'model_id',
        'old_values',
        'updated_fields',
        'ip_address',
    ];


}
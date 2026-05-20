<?php

namespace App\Models\AI;

use App\Http\Controllers\Utils\HelperController;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Caricature\AIJobModel
 *
 * @property int $id
 * @property string $job_id
 * @property int|null $ref_id
 * @property string $user_id
 * @property string $type
 * @property array $data
 * @property array $ip_data
 * @property string $status
 * @property string|null $error_msg
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AIJobModel newModelQuery()
 * @method static Builder|AIJobModel newQuery()
 * @method static Builder|AIJobModel query()
 * @method static Builder|AIJobModel whereJobId($value)
 * @method static Builder|AIJobModel whereUserId($value)
 * @method static Builder|AIJobModel whereType($value)
 * @method static Builder|AIJobModel whereStatus($value)
 * @method static Builder|AIJobModel whereCreatedAt($value)
 * @method static Builder|AIJobModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AIJobModel extends Model
{
    protected $connection = 'crafty_ai_mysql';
    protected $table = 'ai_job';

    protected $fillable = [
        'job_id',
        'ref_id',
        'user_id',
        'type',
        'data',
        'ip_data',
        'status',
        'error_msg',
    ];

    use HasFactory;

    public static function generateJobId(): string
    {
        $txnId = HelperController::generateID('job_');
        while (AIJobModel::whereJobId($txnId)->exists()) {
            $txnId = HelperController::generateID('job_');
        }
        return $txnId;
    }

    public static function getIpDataAttribute($value): array
    {
       if (is_string($value)) {
           return json_decode($value, true);
       }
       return $value;
    }

    public static function getDataAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }
}

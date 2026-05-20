<?php

namespace App\Models\AI;

use App\Http\Controllers\Utils\HelperController;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Caricature\AICreatedHistory
 *
 * @property int $id
 * @property string $user_id
 * @property string $type
 * @property array $images
 * @property string $credits
 * @property string $user_input
 * @property int $show_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AICreatedHistory newModelQuery()
 * @method static Builder|AICreatedHistory newQuery()
 * @method static Builder|AICreatedHistory query()
 * @method static Builder|AICreatedHistory whereUserId($value)
 * @method static Builder|AICreatedHistory whereType($value)
 * @method static Builder|AICreatedHistory whereCreatedAt($value)
 * @method static Builder|AICreatedHistory whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AICreatedHistory extends Model
{
    protected $connection = 'crafty_ai_mysql';
    protected $table = 'created_history';

    use HasFactory;

    public function getImagesAttribute($value): array
    {
        $datas = $value === null ? [] : json_decode($value, true);
        foreach ($datas as $key => $value) {
            $datas[$key] = HelperController::$mediaUrl . $value;
        }
        return $datas;
    }

    public function getUserInputAttribute($value): ?string
    {
        return $value === null ? null : HelperController::$mediaUrl . $value;
    }

}

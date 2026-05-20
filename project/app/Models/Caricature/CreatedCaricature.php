<?php

namespace App\Models\Caricature;

use App\Http\Controllers\HelperController;
use App\Models\UserData;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Traits\UpdateLogger;

/**
 * App\Models\Caricature\CreatedCaricature
 *
 * @property int $id
 * @property string $user_id
 * @property string $caricature_id
 * @property array $images
 * @property string|null $payment_id
 * @property string|null $user_input
 * @property string|null $cartoon_image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CreatedCaricature newModelQuery()
 * @method static Builder|CreatedCaricature newQuery()
 * @method static Builder|CreatedCaricature query()
 * @method static Builder|CreatedCaricature whereUserId($value)
 * @method static Builder|CreatedCaricature whereCaricatureId($value)
 * @method static Builder|CreatedCaricature wherePaymentId($value)
 * @method static Builder|CreatedCaricature whereCreatedAt($value)
 * @method static Builder|CreatedCaricature whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CreatedCaricature extends Model
{
    protected $connection = 'crafty_caricature_mysql';
    protected $table = 'created_history';

    use HasFactory;
    use UpdateLogger;

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

    public function getCartoonImageAttribute($value): ?string
    {
        return $value === null ? null : HelperController::$mediaUrl . $value;
    }

    public function getPageLinkAttribute(): ?string
    {
        return HelperController::$webPageUrl . "caricature/p/" . $this->caricature_id;
    }

    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    // Relationship to purchase_history
    public function purchase()
    {
        return $this->hasOne(CaricaturePurchaseHistory::class, 'payment_id', 'payment_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;
use Illuminate\Support\Carbon;

/**
 * App\Models\OfferPopUp
 *
 * @property int $id
 * @property int $enable_offer
 * @property int $duration
 * @property int $frequency_duration
 * @property int $force_show_duration
 * @property int $enable_force
 * @property string|null $title
 * @property string|null $festival_name
 * @property string|null $description
 * @property string|null $sub_description
 * @property string|null $promo_code
 * @property string|null $btn_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|OfferPopUp newModelQuery()
 * @method static Builder|OfferPopUp newQuery()
 * @method static Builder|OfferPopUp query()
 * @method static Builder|OfferPopUp whereCreatedAt($value)
 * @method static Builder|OfferPopUp whereDuration($value)
 * @method static Builder|OfferPopUp whereEnableForce($value)
 * @method static Builder|OfferPopUp whereEnableOffer($value)
 * @method static Builder|OfferPopUp whereForceShowDuration($value)
 * @method static Builder|OfferPopUp whereFrequencyDuration($value)
 * @method static Builder|OfferPopUp whereId($value)
 * @method static Builder|OfferPopUp whereUpdatedAt($value)
 * @method static Builder|OfferPopUp whereBtnName($value)
 * @method static Builder|OfferPopUp whereDescription($value)
 * @method static Builder|OfferPopUp whereFestivalName($value)
 * @method static Builder|OfferPopUp wherePromoCode($value)
 * @method static Builder|OfferPopUp whereSubDescription($value)
 * @method static Builder|OfferPopUp whereTitle($value)
 * @mixin \Eloquent
 */
class OfferPopUp extends Model
{
    use HasFactory;
    use UpdateLogger;
    protected $connection = 'mysql';
    protected $table = 'offer_popup';

    protected $fillable = [
        'title',
        'description',
        'sub_description',
        'promo_code',
        'enable_promo_code',
        'btn_name',
        'btn_link',
        'festival_name',
        'enable_offer',
        'duration',
        'frequency_duration',
        'force_show_duration',
        'enable_force',
    ];
}

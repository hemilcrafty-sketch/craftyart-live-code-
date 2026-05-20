<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OfferPopUp newModelQuery()
 * @method static Builder|OfferPopUp newQuery()
 * @method static Builder|OfferPopUp query()
 * @method static Builder|OfferPopUp whereId($value)
 * @method static Builder|OfferPopUp whereEnableOffer($value)
 * @method static Builder|OfferPopUp whereDuration($value)
 * @method static Builder|OfferPopUp whereFrequencyDuration($value)
 * @method static Builder|OfferPopUp whereForceShowDuration($value)
 * @method static Builder|OfferPopUp whereEnableForce($value)
 * @method static Builder|OfferPopUp whereCreatedAt($value)
 * @method static Builder|OfferPopUp whereUpdatedAt($value)
 * @mixin Eloquent
 */

class OfferPopUp extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'offer_popup';

    protected $fillable = [
        'enable_offer',
        'duration',
        'frequency_duration',
        'force_show_duration',
        'enable_force',
    ];
}

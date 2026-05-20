<?php

namespace App\Models\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Pricing\OfferPage
 *
 * @property int $id
 * @property int $offer_package_id
 * @property string $slug
 * @property bool $enable_instructions
 * @property string|null $instructions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read OfferPackage|null $offerPackage
 *
 * @mixin Eloquent
 */
class OfferPage extends Model
{
    use HasFactory;

    protected $connection = 'crafty_pricing_mysql';

    protected $table = 'offer_pages';

    protected $fillable = [
        'offer_package_id',
        'slug',
        'enable_instructions',
        'instructions',
        'is_show_addon',
    ];

    protected $casts = [
        'enable_instructions' => 'boolean',
        'is_show_addon' => 'boolean',
    ];

    /**
     * Relationship: OfferPage belongs to OfferPackage
     */
    public function offerPackage(): BelongsTo
    {
        return $this->belongsTo(OfferPackage::class, 'offer_package_id');
    }

    /**
     * Relationship: OfferPage has many addons
     */
    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'offer_page_addon_mappings', 'offer_page_id', 'addon_id');
    }


    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('offer_page_slugs');
        });

        static::deleted(function () {
            Cache::forget('offer_page_slugs');
        });
    }
}
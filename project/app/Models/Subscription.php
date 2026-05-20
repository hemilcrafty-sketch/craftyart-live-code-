<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Subscription
 *
 * @property int $id
 * @property int $is_base_price
 * @property string $package_name
 * @property string $desc
 * @property int $validity
 * @property float $actual_price
 * @property float|null $actual_price_dollar
 * @property float $price
 * @property float|null $price_dollar
 * @property int $months
 * @property int $has_offer
 * @property int $sequence_number
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereIn($key, $value)
 * @method static Builder|Subscription whereActualPrice($value)
 * @method static Builder|Subscription whereActualPriceDollar($value)
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereDesc($value)
 * @method static Builder|Subscription whereHasOffer($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription whereIsBasePrice($value)
 * @method static Builder|Subscription whereMonths($value)
 * @method static Builder|Subscription wherePackageName($value)
 * @method static Builder|Subscription wherePrice($value)
 * @method static Builder|Subscription wherePriceDollar($value)
 * @method static Builder|Subscription whereSequenceNumber($value)
 * @method static Builder|Subscription whereStatus($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereValidity($value)
 * @mixin Eloquent
 */
class Subscription extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    /**
     * @return array<int, array{
     *     id: int,
     *     package_name: string,
     *     desc: string,
     *     validity: string,
     *     currency: string,
     *     actual_price: string,
     *     offer_price: string,
     *     price: float,
     *     has_offer: int,
     *     offer_msg: ?string,
     *     discount: int
     * }> | null
     */
    public static function getSubs(array|null $ids = null, string $currency = "INR", int|null $status = 1): ?array
    {
        $subs = Subscription::query();
        if ($ids !== null) $subs->whereIn('id', $ids);
        if ($status !== null) $subs->whereStatus($status);
        $subs = $subs->get();

        $isInr = $currency === "INR";

        $column = $isInr ? 'price' : 'price_dollar';
        $actualColumn = $isInr ? 'actual_price' : 'actual_price_dollar';
        $currency_symbol = $isInr ? "₹" : "$";

        $callback = function ($package) use ($column, $actualColumn, $currency, $currency_symbol) {
            $price = round($package->$column, 2);
            $actual_price = round($package->$actualColumn, 2);

            $discount = 0;
            $offer_msg = null;
            $has_offer = 0;

            if ($price < $actual_price) {
                $discount = (int)((($actual_price - $price) / $actual_price) * 100);
                $offer_msg = "Best Value ({$discount}% off)";
                $has_offer = 1;
            }

            return [
                'id' => $package->id,
                'package_name' => $package->package_name,
                'desc' => $package->desc,
                'validity' => $package->validity,
                'currency' => $currency,
                'actual_price' => $currency_symbol . $actual_price,
                'offer_price' => $currency_symbol . $price,
                'price' => $price,
                'has_offer' => $has_offer,
                'offer_msg' => $offer_msg,
                'discount' => $discount
            ];
        };

        $datas = [];
        foreach ($subs as $sub) {
            $datas[] = $callback($sub);
        }

        return empty($datas) ? null : $datas;
    }
}

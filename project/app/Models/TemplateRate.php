<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\TemplateRate
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property int $tmp_base_inr
 * @property int $tmp_page_inr
 * @property int $tmp_max_inr
 * @property float $tmp_base_usd
 * @property float $tmp_page_usd
 * @property float $tmp_max_usd
 * @property int $free_tmp_base_inr
 * @property int $free_tmp_page_inr
 * @property int $free_tmp_max_inr
 * @property float $free_tmp_base_usd
 * @property float $free_tmp_page_usd
 * @property float $free_tmp_max_usd
 * @property int $vid_base_inr
 * @property int $vid_page_inr
 * @property int $vid_max_inr
 * @property float $vid_base_usd
 * @property float $vid_page_usd
 * @property float $vid_max_usd
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|TemplateRate newModelQuery()
 * @method static Builder|TemplateRate newQuery()
 * @method static Builder|TemplateRate query()
 * @method static Builder|TemplateRate whereCreatedAt($value)
 * @method static Builder|TemplateRate whereFreeTmpBaseInr($value)
 * @method static Builder|TemplateRate whereFreeTmpBaseUsd($value)
 * @method static Builder|TemplateRate whereFreeTmpMaxInr($value)
 * @method static Builder|TemplateRate whereFreeTmpMaxUsd($value)
 * @method static Builder|TemplateRate whereFreeTmpPageInr($value)
 * @method static Builder|TemplateRate whereFreeTmpPageUsd($value)
 * @method static Builder|TemplateRate whereId($value)
 * @method static Builder|TemplateRate whereName($value)
 * @method static Builder|TemplateRate whereTmpBaseInr($value)
 * @method static Builder|TemplateRate whereTmpBaseUsd($value)
 * @method static Builder|TemplateRate whereTmpMaxInr($value)
 * @method static Builder|TemplateRate whereTmpMaxUsd($value)
 * @method static Builder|TemplateRate whereTmpPageInr($value)
 * @method static Builder|TemplateRate whereTmpPageUsd($value)
 * @method static Builder|TemplateRate whereUpdatedAt($value)
 * @method static Builder|TemplateRate whereValue($value)
 * @method static Builder|TemplateRate whereVidBaseInr($value)
 * @method static Builder|TemplateRate whereVidBaseUsd($value)
 * @method static Builder|TemplateRate whereVidMaxInr($value)
 * @method static Builder|TemplateRate whereVidMaxUsd($value)
 * @method static Builder|TemplateRate whereVidPageInr($value)
 * @method static Builder|TemplateRate whereVidPageUsd($value)
 * @mixin \Eloquent
 */
class TemplateRate extends Model
{
    protected $table = 'template_rates';
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'value',
    ];

    public static function getRates($name)
    {
        $rate = TemplateRate::where('name', $name)->pluck('value')->first();
        if ($rate) {
            return json_decode($rate);
        } else {
            return self::getDefaultValue();
        }
    }


    private static function checkRateStructure(array $default, array $data): bool
    {
        foreach ($default as $currency => $fields) {
            if (!isset($data[$currency]) || !is_array($data[$currency])) {
                return false;
            }

            foreach ($fields as $key => $value) {
                if (!array_key_exists($key, $data[$currency])) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function getDefaultValue()
    {
        return json_decode(json_encode([
            'inr' => [
                'base_price' => 99,
                'page_price' => 100,
                'max_price' => 399,
                'editor_choice' => 0,
                'animation' => 0,
                'caricature' => 0
            ],
            'usd' => [
                'base_price' => 4.99,
                'page_price' => 2,
                'max_price' => 8.99,
                'editor_choice' => 0,
                'animation' => 0,
                'caricature' => 0,
            ],
        ]));
    }
}

<?php

namespace App\Models;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Models\Caricature\AIPurchaseHistory;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Video\VideoPurchaseHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ExportTable
 *
 * @property int $id
 * @property string|null $uid
 * @property string $original_name
 * @property string $name
 * @property string $path
 * @property string $ids
 * @property int $total
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $email_sent
 * @property int $wp_sent
 * @property int $watermark
 * @property-read Draft|null $draft
 * @property-read UserData|null $userData
 * @property int $amount
 * @property string $currency
 * @property string $crafty_id
 * @method static Builder|ExportTable newModelQuery()
 * @method static Builder|ExportTable newQuery()
 * @method static Builder|ExportTable query()
 * @method static Builder|ExportTable with($value)
 * @method static Builder|ExportTable whereCreatedAt($value)
 * @method static Builder|ExportTable whereId($value)
 * @method static Builder|ExportTable whereName($value)
 * @method static Builder|ExportTable whereOriginalName($value)
 * @method static Builder|ExportTable wherePath($value)
 * @method static Builder|ExportTable whereStatus($value)
 * @method static Builder|ExportTable whereTotal($value)
 * @method static Builder|ExportTable whereUid($value)
 * @method static Builder|ExportTable whereUpdatedAt($value)
 * @method static Builder|ExportTable whereEmailSent($value)
 * @method static Builder|ExportTable whereWatermark($value)
 * @method static Builder|ExportTable whereWpSent($value)
 * @method static Builder|ExportTable whereCurrency($value)
 * @method static Builder|ExportTable whereCraftyId($value)
 * @method static Builder|ExportTable whereAmount($value)
 * @mixin \Eloquent
 */
class ExportTable extends Model
{
    protected $table = 'exports';
    protected $connection = 'mysql';
    use HasFactory;

    public static function generateCraftyId(): string
    {
        $txnId = HelperController::generateID('txn_');
        while (ExportTable::whereCraftyId($txnId)->exists()) {
            $txnId = HelperController::generateID('txn_');
        }
        return $txnId;
    }


    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class, 'path', 'string_id');
    }

    public function userData(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'uid', 'uid');
    }

    public function getContactNo(): ?string
    {
        if (!$this->uid) {
            return null;
        }

        $purData = PurchaseHistory::where('user_id', $this->uid)->first()
            ?: VideoPurchaseHistory::where('user_id', $this->uid)->first()
                ?: CaricaturePurchaseHistory::where('user_id', $this->uid)->first()
                    ?: AIPurchaseHistory::whereUserId($this->uid)->first()
                        ?: TransactionLog::where('user_id', $this->uid)->first();
        // Return contact_no from purchase data or fall back to user's contact_no
        return $purData?->contact_no ?? $this->userData->contact_no;
    }

    public function getAutomationCommonData(): array
    {

        if (!$this->userData) {
            return ['success' => false, 'message' => "User not found for transaction {$this->id}"];
        }

        $commonData['userData'] = [
            'name' => $this->userData->name ?? '',
            'email' => $this->userData->email ?? '',
        ];

        $paymentLink = "https://www.craftyartapp.com/payment/$this->crafty_id";

        $thumbs = json_decode($this->draft->thumbs, true);
        $tempArray[] = [
            "title" => $this->name,
            "image" => $thumbs[0],
            "width" => $this->draft->width,
            "height" => $this->draft->height,
            "amount" => ($this->currency == "INR" ? "₹" : "$") . $this->amount,
//            "link" => $this->draft->design->page_link,
        ];

        $commonData['data'] = [
            'templates' => $tempArray,
            'amount' => ($this->currency == "INR" ? "₹" : "$") . $this->amount,
            "package_name" => $this->name,
        ];
        $commonData['type'] = "template";
        $commonData['planType'] = "template";

        $commonData['link'] = $paymentLink;
        $commonData['waBtnLink'] = str_replace("https://www.craftyartapp.com/", "", $paymentLink);

        return $commonData;
    }

    public function getTempDatas($offerApplied): array|null
    {
        $isInr = $this->currency === "INR";
        $ids = json_decode($this->ids, true);
        $designs = Design::whereIn('string_id', $ids)->get()->keyBy('string_id');

        $amount = 0;
        $templates = [];
        $paymentProps = [];

        $rates = RateController::getRates();

        foreach ($designs as $design) {

            if (!PurchaseHistory::where('user_id', $this->uid)->where('product_id', $design->string_id)->wherePaymentStatus(1)->exists()) {
//                $containPremium = $design->is_premium == 1 || $design->is_freemium == 1;

                $thumbArray = json_decode($design->thumb_array);
                $size = sizeof($thumbArray);

                $pyt = RateController::getTemplateRates($rates, $size, $design, /*!$containPremium &&*/ $offerApplied ? PaymentController::$FREE_TEMPLATE_DISCOUNT : 0);
                $pyt['id'] = $design->string_id;
                $pyt['type'] = 0;
                $paymentProps[] = $pyt;

                $perTemp = $isInr ? $pyt['inrVal'] : $pyt['usdVal'];
                $amount += $perTemp;

                $templates[] = [
                    "title" => $design->post_name,
                    "image" => HelperController::generatePublicUrl($design->post_thumb),
                    "width" => $design->width,
                    "height" => $design->height,
                    "amount" => $perTemp,
                    "link" => $design->page_link,
                ];
            }

        }

        if ($amount == 0) return null;

        $response['type'] = "template";
        $response['data'] = [
            "id" => $paymentProps,
            "templates" => $templates,
            "amount" => ($isInr ? "₹" : "$") . $amount,
        ];

        $response['link'] = "https://www.craftyartapp.com/payment/$this->crafty_id";
        $response['amount'] = $amount;
        return $response;
    }
}

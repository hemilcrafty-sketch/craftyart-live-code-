<?php

namespace App\Models;

use App\Http\Controllers\HelperController;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\AIPurchaseHistory;
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


        // Try to get contact number from purchase histories in order
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
        $currency = $this->currency;

        if (!$this->userData) {
            return ['success' => false, 'message' => "User not found for transaction {$this->id}"];
        }

        $commonData['userData'] = [
            'name' => $this->userData->name ?? '',
            'email' => $this->userData->email ?? '',
        ];

        $paymentLink =  "https://www.craftyartapp.com/payment/$this->crafty_id";

        $tempArray[] = [
            "title" => $this->name,
            "image" => HelperController::generatePublicUrl($this->draft->design->post_thumb),
            "width" => $this->draft->width,
            "height" => $this->draft->height,
            "amount" => ($this->currency == "INR" ? "₹" : "$").$this->amount,
            "link" => $this->draft->design->page_link,
        ];

        $commonData['data'] = [
            'templates' => $tempArray,
            'amount' => ($this->currency == "INR" ? "₹" : "$").$this->amount,
            "package_name" => $this->name,
        ];
        $commonData['type'] = "template";
        $commonData['planType'] = "template";

        $commonData['link'] = $paymentLink;
        $commonData['waBtnLink'] = str_replace("https://www.craftyartapp.com/", "", $paymentLink);

        return $commonData;
    }

}

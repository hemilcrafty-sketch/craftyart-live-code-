<?php

namespace App\Models;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Http\Controllers\HelperController;
use App\Models\Caricature\Attire;
use App\Models\Video\VideoTemplate;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\Pricing\OfferPage;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property string|null $user_id
 * @property string|null $plan_id
 * @property string|null $contact_no
 * @property string $crafty_id
 * @property string $razorpay_order_id
 * @property string|null $razorpay_payment_id
 * @property string|null $stripe_payment_intent_id
 * @property string|null $stripe_txn_id
 * @property string $status
 * @property string|null $amount
 * @property string|null $paid
 * @property string $currency
 * @property int $email_template_count
 * @property int $whatsapp_template_count
 * @property int $followup_call
 * @property string|null $followup_note
 * @property string $type
 * @property mixed|null $raw_notes
 * @property int $is_deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $plan_items
 * @property-read OfferPackage|null $offerPackage
 * @property-read SubPlan|null $subPlan
 * @property-read Subscription|null $subscription
 * @property-read UserData|null $user
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereAmount($value)
 * @method static Builder|Order whereContactNo($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrency($value)
 * @method static Builder|Order whereEmailTemplateCount($value)
 * @method static Builder|Order whereFollowupCall($value)
 * @method static Builder|Order whereFollowupNote($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereIsDeleted($value)
 * @method static Builder|Order wherePaid($value)
 * @method static Builder|Order wherePlanId($value)
 * @method static Builder|Order whereRawNotes($value)
 * @method static Builder|Order whereCraftyId($value)
 * @method static Builder|Order whereRazorpayOrderId($value)
 * @method static Builder|Order whereRazorpayPaymentId($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereStripePaymentIntentId($value)
 * @method static Builder|Order whereStripeTxnId($value)
 * @method static Builder|Order whereType($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @method static Builder|Order whereWhatsappTemplateCount($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'plan_id',
        'contact_no',
        'crafty_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'stripe_payment_intent_id',
        'stripe_txn_id',
        'status',
        'amount',
        'paid',
        'currency',
        'email_template_count',
        'whatsapp_template_count',
        'followup_call',
        'followup_note',
        'type',
        'is_deleted',
        'raw_notes'
    ];

    protected static function booted(): void
    {
        static::retrieved(function ($promo) {
            if (!empty($promo->expiry_date) && now()->gt($promo->expiry_date)) {
                if ($promo->status != 0) {
                    $promo->status = 0;
                    $promo->save();
                }
            }
        });

        // Note: WebSocket broadcasting is handled by OrderObserver
        // Status change events are also handled by OrderObserver
    }

    public static function generateCraftyId(): string
    {
        $txnId = HelperController::generateID('txn_');
        while (Order::whereCraftyId($txnId)->exists()) {
            $txnId = HelperController::generateID('txn_');
        }
        return $txnId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    public function userActive(): BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }

    public function userDeleted(): BelongsTo
    {
        return $this->belongsTo(UserDataDeleted::class, 'user_id', 'uid');
    }

    public function getUserAttribute()
    {
        if ($this->relationLoaded('userActive') && $this->userActive) {
            return $this->userActive;
        }

        if ($this->relationLoaded('userDeleted') && $this->userDeleted) {
            return $this->userDeleted;
        }

        return $this->userActive()->first() ?? $this->userDeleted()->first();
    }

    public function getNewTypeAttribute()
    {
        if ($this->type === 'old_sub') {
            $isExists = TransactionLog::whereUserId($this->user_id)->where('expired_at', '>', now())->exists() ? "Subscribed" : "Unsubscribed";
            return $this->type . " ($isExists)";
        }

        return $this->type;
    }

    public function getFromWhereAttribute()
    {
        static $offerPageSlugs = null;

        // Load once per request from cache
        if ($offerPageSlugs === null) {

            $offerPageSlugs = Cache::remember(
                'offer_page_slugs',
                now()->addHours(1),
                fn() => OfferPage::pluck('slug')->flip()->toArray()
            );
        }

        // First check URL slug
        if (!empty($this->url)) {

            $path = parse_url($this->url, PHP_URL_PATH);

            if (!empty($path)) {

                $slug = trim($path, '/');

                if (!empty($slug) && isset($offerPageSlugs[$slug])) {
                    return $slug;
                }
            }
        }

        // Fallback logic
        $hasFb = !empty($this->fbc);

        $hasGoogle =
            !empty($this->gclid) ||
            !empty($this->wbraid) ||
            !empty($this->gbraid);

        if ($hasFb && $hasGoogle) {
            return 'Meta-Google';
        }

        if ($hasFb) {
            return 'Meta';
        }

        if ($hasGoogle) {
            return 'Google';
        }

        return 'Seo';
    }


    // public function isSubscriptionActive(): bool
    // {
    //     return TransactionLog::whereUserId($this->user_id)->where('expired_at', '>', now())->exists();
    // }

    public function isSubscriptionActive(): bool
    {
        $transaction = $this->user?->latestTransactionLog;

        return $transaction && Carbon::parse($transaction->expired_at)->isFuture();
    }

    /**
     * Relationship for new_sub type orders - SubPlan
     */
    public function subPlan(): BelongsTo
    {
        return $this->belongsTo(SubPlan::class, 'plan_id', 'string_id')
            ->orWhere('id', $this->plan_id);
    }

    /**
     * Relationship for old_sub and offer type orders - Subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'plan_id');
    }

    public function offerPackage(): BelongsTo
    {
        return $this->belongsTo(OfferPackage::class, 'plan_id', 'id');
    }

    /**
     * Accessor for designs for template type orders
     */
    public function getDesigns(): Collection
    {
        if ($this->type !== 'template') {
            return collect();
        }

        $templateData = json_decode($this->plan_id, true);
        if (!is_array($templateData)) {
            return collect();
        }

        $designIds = collect($templateData)->pluck('id')->filter()->toArray();

        return Design::whereIn('string_id', $designIds)->get();
    }

    /**
     * Accessor for video templates for video type orders
     */
    public function getVideoTemplates(): Collection
    {
        if ($this->type !== 'video') {
            return collect();
        }

        $templateData = json_decode($this->plan_id, true);
        if (!is_array($templateData)) {
            return collect();
        }

        $videoIds = collect($templateData)->pluck('id')->filter()->toArray();

        return VideoTemplate::whereIn('string_id', $videoIds)->get();
    }

    public function getCaricatures(): Collection
    {
        if ($this->type !== 'caricature') {
            return collect();
        }

        $templateData = json_decode($this->plan_id, true);
        if (!is_array($templateData)) {
            return collect();
        }

        $videoIds = collect($templateData)->pluck('id')->filter()->toArray();

        return Attire::whereIn('string_id', $videoIds)->get();
    }


    /**
     * Get formatted plan items (common interface for both designs and videos)
     */
    public function getPlanItemsAttribute(): Collection
    {
        return match ($this->type) {
            'template' => $this->getDesigns(),
            'video' => $this->getVideoTemplates(),
            'caricature' => $this->getCaricatures(),
            default => collect([$this->plan_id])
        };
    }

    public function getContactNoAttribute(): ?string
    {
        if (!empty($this->contact_no)) {
            return $this->contact_no;
        }
        return $this->user->contact_no;
    }

    public function getAmountWithSymbolAttribute(): string
    {
        return match (strtoupper($this->currency ?? '')) {
            'INR' => '₹',
            'USD' => '$',
            default => '',
        } . ($this->amount ? $this->amount : '-');
    }

    public function getItemLink($item): string
    {
        return match ($this->type) {
            'caricature' => HelperController::getFrontendPageUrl(5, $item->id_name),
            'template' => HelperController::getFrontendPageUrl(0, $item->id_name),
            default => '#'
        };
    }

    public function getItemDisplayText($item): string
    {
        if ($this->type == 'ai_credit') {
            $templateData = json_decode($this->plan_id, true);
            return $templateData[0]['id'] ?? 'N/A';
        }
        return match ($this->type) {
            'caricature', 'template', 'video' => $item->string_id,
            default => $this->plan_id ?? 'N/A'
        };
    }

    public function shouldShowLink(): bool
    {
        return in_array($this->type, ['template', 'caricature']);
    }

    public function getAutomationCommonData(): array
    {
        $planType = $this->type;
        $currency = $this->currency;

        // Check if user exists
        if (!$this->user) {
            return ['success' => false, 'message' => "User not found for order {$this->id}"];
        }

        $commonData['userData'] = [
            'name' => $this->user->name ?? '',
            'email' => $this->user->email ?? '',
            'password' => "",
        ];

        $paymentLink = "https://www.craftyartapp.com/payment/$this->crafty_id";

        if (in_array($planType, ['template', 'video'])) {
            // Always ensure we have a collection, not null
            $planItems = $this->planItems ?? collect();

            if ($planItems->isEmpty()) {
                return ['success' => false, 'message' => "No items found for {$planType} order {$this->id}"];
            }

            $templateData = json_decode($this->plan_id, true);
            if (!is_array($templateData)) {
                return ['success' => false, 'message' => "Invalid plan data for {$planType} order {$this->id}"];
            }

            Design::getTempDatas($this);

            $newArray = [];
            $paymentProps = [];

            foreach ($templateData as $item) {
                $planItem = $planItems->firstWhere(
                    $planType === 'template' ? 'string_id' : 'id',
                    $item['id']
                );

                if ($planItem) {
                    $paymentProps[] = ["id" => $item['id'], "type" => $planType === 'video' ? 1 : 0];

                    if ($planType === 'template') {
                        $newArray[] = [
                            "title" => $planItem->post_name,
                            "image" => HelperController::generatePublicUrl($planItem->post_thumb),
                            "width" => $planItem->width,
                            "height" => $planItem->height,
                            "amount" => $currency == "INR" ? $item['inrAmount'] : $item['usdAmount'],
                            "link" => $planItem->page_link,
                        ];
                    } else if ($planType === 'video') { // video
                        $newArray[] = [
                            "title" => $planItem->video_name,
                            "image" => HelperController::generatePublicUrl($planItem->video_thumb),
                            "width" => $planItem->width,
                            "height" => $planItem->height,
                            "amount" => $currency == "INR" ? $item['inrAmount'] : $item['usdAmount'],
                            "link" => "",
                        ];
                    } else {
                        $newArray[] = [
                            "title" => $planItem->post_name,
                            "image" => $planItem->thumbnail_url,
                            "width" => $planItem->width,
                            "height" => $planItem->height,
                            "amount" => $currency == "INR" ? $item['inrAmount'] : $item['usdAmount'],
                            "link" => $planItem->page_link,
                        ];
                    }
                }
            }

            if (empty($newArray)) {
                return ['success' => false, 'message' => "No valid items found for order {$this->id}"];
            }

            $commonData['type'] = $planType;
            $commonData['data'] = [
                "templates" => $newArray,
                "amount" => ($currency == "INR" ? "₹" : "$") . $this->amount,
            ];
            $commonData['planType'] = $planType;
            $commonData['paymentProps'] = $paymentProps;

        } elseif ($planType === 'new_sub') {
            $plan = $this->subPlan;
            if (!$plan) {
                return ['success' => false, 'message' => "New SubPlan not found for order {$this->id}"];
            }

            $commonData['type'] = "plan";
            $commonData['data'] = AutomationUtils::formatNewPlanData($plan, $currency);
            $commonData['plan'] = $plan;
            $commonData['planType'] = $planType;

        } elseif ($planType === 'old_sub') {
            $plan = $this->subscription;
            if (!$plan) {
                return ['success' => false, 'message' => "Subscription not found for order {$this->id}"];
            }

            if (in_array($plan->id, PaymentController::$OFFER_IDS)) {
                $paymentLink = "https://www.craftyartapp.com/offer/payment/$this->crafty_id";
            }

            $commonData['type'] = "plan";
            $commonData['data'] = AutomationUtils::formatOldPlanData($plan, $currency);
            $commonData['plan'] = $plan;
            $commonData['planType'] = $planType;
        } elseif ($planType === 'offer') {
            $offerSubPlan = $this->offerPackage->subPlan;
            if (!$offerSubPlan) {
                return ['success' => false, 'message' => "Offer SubPlan not found for order {$this->id}"];
            }
            $commonData['type'] = "plan";
            $commonData['data'] = AutomationUtils::formatNewPlanData($offerSubPlan, $currency);
            $commonData['plan'] = $offerSubPlan;
            $commonData['planType'] = $planType;
        } else {
            return ['success' => false, 'message' => "Invalid plan type provided for order {$this->id}"];
        }

        $commonData['link'] = $paymentLink;
        $commonData['waBtnLink'] = str_replace("https://www.craftyartapp.com/", "", $paymentLink);

        return $commonData;
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\DomainChecker;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PlanLimitHelper;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\OfferPage;
use App\Models\Pricing\Plan;
use App\Models\Pricing\PlanDuration;
use App\Models\Pricing\PlanFeature;
use App\Models\Pricing\PlanUserDiscount;
use App\Models\Pricing\SubPlan;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\UserSubscriptions;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\WebTemplateViewHistory;
use App\Services\PaymentGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Stripe\StripeClient;

class PlanController extends ApiController
{
    public static array $unusedUrls = [
        "https://www.craftyartapp.com/offer-package",
        "https://www.craftyartapp.com/indian-wedding-template-bundle",
        "https://www.craftyartapp.com/editable-gujarati-wedding-invitation-video-template",
        "https://www.craftyartapp.com/wedding-template-bundle",
        "https://www.craftyartapp.com/wedding-video-bundle",
        "https://www.craftyartapp.com/video-bundle",
        "https://www.craftyartapp.com/video-bundle-1.0",
        "https://www.craftyartapp.com/video-bundle-2.0",
        "https://www.craftyartapp.com/video-bundle-3.0",
        "https://www.craftyartapp.com/video-bundle-4.0",
        "https://www.craftyartapp.com/video-bundle-5.0",
        "https://www.craftyartapp.com/video-bundle-6.0",
        "https://www.craftyartapp.com/video-bundle-7.0",
        "https://www.craftyartapp.com/video-bundle-8.0",
        "http://www.craftyartapp.com/offer-package",
        "http://www.craftyartapp.com/indian-wedding-template-bundle",
        "http://www.craftyartapp.com/editable-gujarati-wedding-invitation-video-template",
        "http://www.craftyartapp.com/wedding-template-bundle",
        "http://www.craftyartapp.com/wedding-video-bundle",
        "http://www.craftyartapp.com/video-bundle",
        "http://www.craftyartapp.com/video-bundle-1.0",
        "http://www.craftyartapp.com/video-bundle-2.0",
        "http://www.craftyartapp.com/video-bundle-3.0",
        "http://www.craftyartapp.com/video-bundle-4.0",
        "http://www.craftyartapp.com/video-bundle-5.0",
        "http://www.craftyartapp.com/video-bundle-6.0",
        "http://www.craftyartapp.com/video-bundle-7.0",
        "http://www.craftyartapp.com/video-bundle-8.0",
    ];

    public function getPlanData(Request $request): mixed
    {

//        if ($request->isMethod('get') && $this->isTester()) {
//            $datas = WpFeedbackRequest::whereEmpId(0)->get();
//            foreach ($datas as $data) {
//                $data->emp_id = MasterPurchaseHistory::find($data->purchase_id)->emp_id;
//                $data->save();
//            }
//        }

        if ($request->isMethod('get') && $this->isTester()) {
            $user = collect(['uid' => ""]);
            $multiData = array();
            $transData = MasterPurchaseHistory::whereUserId($user->uid)->orderBy('id', 'DESC')->get();

            if ($transData != null && $transData->count() != 0) {
                foreach ($transData as $transLog) {
                    $subRow = null;
                    $isSub = false;
                    if ($transLog->product_type == "old_sub") {
                        $isSub = true;
                        $subRow = Subscription::find($transLog->product_id);
                    } else if ($transLog->product_type == "new_sub") {
                        $isSub = true;
                        $subRow = SubPlan::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                $query->where('id', $transLog->product_id)
                                    ->orWhere('string_id', $transLog->product_id);
                            })->first();
                    } else if ($transLog->product_type == "offer_sub") {
                        $isSub = true;
                        $subRow = OfferPackage::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                $query->where('id', $transLog->product_id)
                                    ->orWhere('string_id', $transLog->product_id);
                            })->first();
                    }

                    if ($isSub && !$subRow) continue;

                    if ($subRow) {
                        if ($transLog->currency_code == 'Trial') {
                            $amount = "Trial";
                        } else {
                            $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                            $amount = $currency_code . $transLog->paid_amount;
                        }

                        $purchaseDate = $transLog->created_at;
                        $billingDate = Carbon::parse($transLog->expired_at);
                        $days = $purchaseDate->diffInDays($billingDate, false);

                        $package_name = $subRow->package_name;
                        if ($subRow->plan) {
                            $package_name = "$package_name ({$subRow->plan->name})";
                        }
                        $multiData[] = array(
                            'package_name' => $package_name,
                            'subscription_id' => $transLog->subscription_id ?? '--',
                            'transaction_id' => $transLog->transaction_id,
                            'recurred' => $transLog->is_e_mandate == 1 ? 'True' : 'False',
                            'amount' => $amount,
                            'method' => $transLog->payment_method,
                            'purchase_date' => $purchaseDate->format('d/m/Y H:i:s'),
                            'billing_date' => $billingDate->format('d/m/Y H:i:s'),
                            'validity' => $days . " Days",
                            'status' => HelperController::checkSubsStatus($billingDate->isFuture() ? 1 : 0),
                            'color' => HelperController::getSubsColor($billingDate->isFuture() ? 1 : 0),
                            'type' => $transLog->product_type,
                            'payment_status' => $transLog->payment_status,
                        );
                    }

                }
            }

            $datas['subsHistory'] = $multiData;
        }

//        if ($request->isMethod('get') && $this->isTester()) {
//            $datas = MasterPurchaseHistory::whereProductType('offer_sub')->whereProductId('2ms58gxhbe')->get();;
//
//            foreach ($datas as $data) {
//                $expiredAt = Carbon::parse($data->created_at)->addDays(3);
//                $data->validity = 3;
//                $data->is_trial = 1;
//                $data->expired_at = $expiredAt;
//                $data->save();
//
//                $subData = UserSubscriptions::where('gateway_subscription_id', $data->subscription_id)->first();
//                if ($subData) {
//                    $subData->is_trial = 1;
//                    $subData->trial_end = $expiredAt;
//                    $subData->current_end = $expiredAt;
//                    $subData->save();
//                }
//
//                $subData = TransactionLog::whereTransactionId($data->transaction_id)->first();
//                if ($subData) {
//                    $subData->validity = 3;
//                    $subData->is_trial = 1;
//                    $subData->expired_at = $expiredAt;
//                    $subData->save();
//                }
//
//            }
//        }
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        if ($request->has('id')) {
            return $this->successed(msg: 'Plan data loaded successfully.', datas: ["data" => PlanLimitHelper::recommendedPlan($request)]);
        }

        $ipData = HelperController::getIpAndCountry($request);
        $currency = strtoupper($ipData['cur']);

        $planDurations = PlanDuration::select(['id', 'name', 'duration', 'is_annual'])->get();
        $plans = Plan::where('status', 1)->get();

        $planIds = $plans->pluck('string_id')->toArray();
        $subPlans = SubPlan::whereIn('plan_id', $planIds)->where('deleted', 0)->get();

        $featureIds = [];
        foreach ($plans as $plan) {
            foreach ($plan->appearance as $item) {
                if (isset($item['features_id']) && !array_key_exists($item['features_id'], $item)) {
                    $featureIds[] = $item['features_id'];
                }
            }
        }

        $features = PlanFeature::whereIn('id', $featureIds)->get()->keyBy('id');

        $subPlansByPlanId = $subPlans->groupBy('plan_id');

        $plansData = $plans->map(function ($plan) use ($currency, $planDurations, $features, $subPlansByPlanId) {

            $appearance = [];

            foreach ($plan->appearance as $item) {
                $metaAppearance = $item['is_feature_visible'] ?? false;
                if (!$metaAppearance) continue;

                $featureName = null;

                if (isset($item['features_id'])) {
                    $featureName = $features[$item['features_id']]->name ?? null;
                }

                $metaAppearance = $item['meta_appearance'] ?? null;
                $appearance[] = [
                    'meta_appearance' => $metaAppearance ?: $featureName,
                    'meta_value' => $item['meta_value'] ?? null,
                    'sub_name' => $item['sub_name'] ?? null,
                ];
            }

            $appearance = collect($appearance)->sortByDesc('meta_value')->values()->all();

            $subPlanData = [];
            if (isset($subPlansByPlanId[$plan->string_id])) {
                $subPlanData = $subPlansByPlanId[$plan->string_id]->map(function ($subPlan) use ($currency, $planDurations) {
                    $planDetails = $subPlan->plan_details;

                    $duration = $planDurations->where('id', $subPlan->duration_id)->first();
                    $durationValue = $duration->duration ?? 0;
                    $additionalDuration = (int)($planDetails['additional_duration'] ?? 0);
                    $totalDay = $durationValue + $additionalDuration;

                    $isInr = $currency === 'INR';
                    $curSymbol = $isInr ? '₹' : '$';
                    $key_price = $isInr ? 'inr_price' : 'usd_price';
                    $key_offer_price = $isInr ? 'inr_offer_price' : 'usd_offer_price';

                    $price = (float)($planDetails[$key_price] ?? 0);
                    $finalPrice = (float)($planDetails[$key_offer_price] ?? 0);

                    $disc = (($price - $finalPrice) / $price) * 100;
                    $discount = $disc;

                    $finalPrice = (float)number_format($finalPrice, 2, '.', '');

                    return [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'plan_id' => $subPlan->plan_id,
                        'duration_id' => (int)$subPlan->duration_id,

                        'details' => [
                            'additional_duration' => $additionalDuration,
                            'duration' => $durationValue,
                            'total_day' => $totalDay,
                            'discount' => number_format($discount, 2) . '%',
                            'base_price' => $discount > 0 ? $price : "",
                            'final_price' => $finalPrice,
                            'curSymbol' => $curSymbol,
                        ],

//                        'details' => [
//                            'discount' => $discount,
//                            'additional_duration' => $additionalDuration,
//                            'duration' => $durationValue,
//                            'total_day' => $totalDay,
//
//                            'price' => $curSymbol . $price,
//                            'final_price' => $curSymbol . $finalPrice,
//
//                            'amount' => $finalPrice,
//                            'base_price' => $price,
//
//                            'discount_price' => $curSymbol.$finalPrice,
//                            'additional_user_discount' => "0%",
//                            'additional_charge' => null,
//                            'total_discount' => number_format($discount, 2) . '%',
//                            'total_price' => $curSymbol.$finalPrice,
//                        ],
                    ];
                });
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'sub_title' => $plan->sub_title,
                'btn_name' => $plan->btn_name,
                'is_recommended' => $plan->is_recommended == 1,
                'is_free_type' => $plan->is_free_type == 1,
                'string_id' => $plan->string_id,
                'icon' => ContentManager::getStorageLink($plan->icon),
                'description' => $plan->description,
                'appearance' => $appearance,
                'sub_plan' => $subPlanData,
            ];
        });

        $userData = UserData::whereUid($this->uid)->first();

        $response = [
            'duration' => $planDurations,
            'plans' => $plansData,
            'ipData' => $ipData,
            'check_user_offer' => $userData?->email == 'viddhi.crafty@gmail.com'
        ];
        if ($userData) $response['contact_no'] = $userData->contact_no;

        return $this->successed(msg: 'Plan data loaded successfully.', datas: $response);
    }

    public static function formatPlanData($plans, $currency, $durationId = null): array
    {
        if ($durationId) {
            $planDurations = PlanDuration::where('id', $durationId)->select(['id', 'name', 'duration', 'is_annual'])->orderBy('duration')->get();
        } else {
            $planDurations = PlanDuration::select(['id', 'name', 'duration', 'is_annual'])->orderBy('duration')->get();
        }

        $planIds = collect($plans)->pluck('string_id')->toArray();

        $subPlans = SubPlan::whereIn('plan_id', $planIds)
            ->where('deleted', 0)
            ->whereIn('duration_id', $planDurations->pluck('id'))
            ->get()
            ->groupBy('plan_id');

        // Collect feature IDs
        $featureIds = [];
        foreach ($plans as $plan) {
            foreach ($plan->appearance as $item) {
                if (isset($item['features_id']) && !array_key_exists($item['features_id'], $item)) {
                    $featureIds[] = $item['features_id'];
                }
            }
        }

        $features = PlanFeature::whereIn('id', $featureIds)->get()->keyBy('id');

        $plansData = collect($plans)->map(function ($plan) use ($currency, $planDurations, $features, $subPlans) {

            // Appearance
            $appearance = [];

            foreach ($plan->appearance as $item) {
                $metaAppearance = $item['is_feature_visible'] ?? false;
                if (!$metaAppearance) continue;

                $featureName = null;

                if (isset($item['features_id'])) {
                    $featureName = $features[$item['features_id']]->name ?? null;
                }

                $metaAppearance = $item['meta_appearance'] ?? null;
                $appearance[] = [
                    'meta_appearance' => $metaAppearance ?: $featureName,
                    'meta_value' => $item['meta_value'] ?? null,
                    'sub_name' => $item['sub_name'] ?? null,
                ];
            }

            $appearance = collect($appearance)->sortByDesc('meta_value')->values()->all();

            // SubPlans
            $subPlanData = [];
            if (isset($subPlans[$plan->string_id])) {
                $subPlanData = $subPlans[$plan->string_id]->map(function ($subPlan) use ($currency, $planDurations) {

                    $planDetails = $subPlan->plan_details;

                    $duration = $planDurations->where('id', $subPlan->duration_id)->first();
                    $durationValue = $duration->duration ?? 0;

                    $additionalDuration = (int)($planDetails['additional_duration'] ?? 0);
                    $totalDay = $durationValue + $additionalDuration;

                    $isInr = $currency === 'INR';
                    $curSymbol = $isInr ? '₹' : '$';

                    $key_price = $isInr ? 'inr_price' : 'usd_price';
                    $key_offer_price = $isInr ? 'inr_offer_price' : 'usd_offer_price';

                    $price = (float)($planDetails[$key_price] ?? 0);
                    $finalPrice = (float)($planDetails[$key_offer_price] ?? 0);

                    $discount = $price > 0 ? (($price - $finalPrice) / $price) * 100 : 0;
                    if ($discount > 0) {
                        $discount = number_format($discount, 2) . '%';
                    } else {
                        $discount = null;
                    }
                    return [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'plan_id' => $subPlan->plan_id,
                        'duration_id' => (int)$subPlan->duration_id,

                        'details' => [
                            'additional_duration' => $additionalDuration,
                            'duration' => $durationValue,
                            'total_day' => $totalDay,
                            'discount' => $discount,
                            'base_price' => $price,
                            'final_price' => number_format($finalPrice, 2, '.', ''),
                            'curSymbol' => $curSymbol,
                        ],
                    ];
                });
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'sub_title' => $plan->sub_title,
                'btn_name' => $plan->btn_name,
                'is_recommended' => $plan->is_recommended == 1,
                'is_free_type' => $plan->is_free_type == 1,
                'string_id' => $plan->string_id,
                'icon' => ContentManager::getStorageLink($plan->icon),
                'description' => $plan->description,
                'appearance' => $appearance,
                'sub_plan' => $subPlanData,
            ];
        });

        return [
            'plans' => $plansData,
            'duration' => $planDurations
        ];
    }

    public function getOfferPackage(Request $request): array|string
    {

        if ($request->isMethod('get') && $this->isTester()) {
//            $datas = UserSubscriptions::where("is_final", 0)->take(100)->get();
//
//            $razorpayGateway = PaymentGateway::initByGateway('razorpay', null);
//            $stripeGateway = PaymentGateway::initByGateway('stripe', null);
//
//            /** @var Api|null $razorpay */
//            $razorpay = $razorpayGateway?->client;
//
//            /** @var StripeClient|null $stripeClient */
//            $stripe = $stripeGateway?->client;
//
//            $successed = [];
//            $errors = [];
//            foreach ($datas as $data) {
//                try {
//                    $total_count = 0;
//                    if ($data->payment_gateway === 'razorpay') {
//                        $subData = $razorpay->subscription->fetch($data->gateway_subscription_id)->toArray();
//                        $status = $subData['status'];
//                        $total_count = $subData['total_count'];
//                        $sub_plan_id = $subData['plan_id'];
//                    } else {
//                        $subData = $stripe->subscriptions->retrieve($data->gateway_subscription_id);
//                        $status = $subData->status;
//                        $sub_plan_id = $subData['plan']['id'];
//                    }
//
//                    $oldest = MasterPurchaseHistory::whereSubscriptionId($data->gateway_subscription_id)->oldest()->first();
//                    $latest = MasterPurchaseHistory::whereSubscriptionId($data->gateway_subscription_id)->latest()->first();
//
//                    if ($status === 'cancelled') {
//                        $data->cancellation_reason = $latest->cancellation_reason;
//                    }
//
//                    if ($oldest->is_trial == 1) {
//                        $data->is_trial = 1;
//                        $data->trial_start = $oldest->created_at;
//                        $data->trial_end = $oldest->expired_at;
//                    } else {
//                        $data->is_trial = 0;
//                        $data->trial_start = null;
//                        $data->trial_end = null;
//                    }
//
//                    $data->plan_id = $sub_plan_id;
//                    $data->status = $status;
//
//                    $data->current_start = $latest->created_at;
//                    $data->current_end = $latest->expired_at;
//
//                    $data->total_count = $total_count;
//                    $data->paid_count = MasterPurchaseHistory::whereSubscriptionId($data->gateway_subscription_id)->count();
//                    $data->is_final = 1;
//                    $data->save();
//
//                    $successed[] = $data->id;
//                } catch (\Exception $e) {
//                    $errors[] = $e->getMessage();
//                }
//            }
//
//            return ["errors" => $errors, "successed" => $successed];
        }

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $pageUrl = $this->getPageUrl();
        $pageUrl = strtok($pageUrl, '?');
        $path = parse_url($pageUrl, PHP_URL_PATH); // /category/product-name
        $slug = trim($path, '/'); // category/product-name

        $slug = $request->input("slug", $slug);

        $ipData = HelperController::getIpAndCountry($request);
        $currency = strtoupper($ipData['cur']);

        $offerPage = OfferPage::with(['offerPackage'])->whereSlug($slug)->first();
        if (empty($offerPage) || empty($offerPage->offerPackage)) return $this->failed(msg: "Offer page not found");

        $offerId = $offerPage->offerPackage->string_id;
        $upgradeId = "rbtuqv9jmh";


        WebTemplateViewHistory::create([
            'user_id' => $this->uid,
            'product_id' => "$offerId",
            'ip_address' => $ipData['ip'],
            'country' => $ipData['cn'],
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
            'gclid' => $request->cookie('_gclid'),
            'gcl_au' => $request->cookie('_gcl_au'),
            'ga' => $request->cookie('_ga'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'type' => 'subscription',
        ]);

        $offerPackage = OfferPackage::with(['plan', 'duration'])->where('string_id', $offerId)->first();
        $upgradePackage = OfferPackage::with(['plan', 'duration'])->where('string_id', $upgradeId)->first();

        if (is_null($offerPackage) || is_null($upgradePackage)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Not valid"));
        }

        $isInr = $currency === "INR";

        $column = $isInr ? 'inr_offer_price' : 'usd_offer_price';
        $actualColumn = $isInr ? 'inr_price' : 'usd_price';

        $currency_symbol = $isInr ? "₹" : "$";

        $callback = function ($package) use ($column, $actualColumn, $currency, $currency_symbol, $isInr) {
            /** @var OfferPackage $package */

            $planDetails = $package->plan_details;
            $subscriptionIds = $package->subscription_ids;

            $trialDays = ($isInr ? $planDetails['inr_trial_days'] : $planDetails['usd_trial_days']) ?? 0;
            $trialAmount = ($isInr ? $planDetails['inr_trial_price'] : $planDetails['usd_trial_price']) ?? 0;

            $actual_price = round($planDetails[$actualColumn], 2);
            $price = round($planDetails[$column], 2);

            if (!empty($subscriptionIds) && $trialDays > 0 && $trialAmount > 0) {
                $trialAmount = ($isInr ? $planDetails['inr_trial_price'] : $planDetails['usd_trial_price']) ?? 0;
            } else {
                $trialAmount = $price;
            }

            $validity = $package->duration->duration;
            $description = $package->plan->description;
            $packageId = $package->string_id;

            if (empty($subscriptionIds)) $trialDays = 0;

            $discount = 0;
            $offer_msg = null;
            $has_offer = 0;

            if ($price < $actual_price) {
                $discount = (int)((($actual_price - $price) / $actual_price) * 100);
                $offer_msg = "Best Value ({$discount}% off)";
                $has_offer = 1;
            }

            return [
                'id' => $packageId,
                'package_name' => $package->package_name,
                'desc' => $description,
                'validity' => $validity,
                'currency' => $currency,
                'actual_price' => $currency_symbol . $actual_price,
                'offer_price' => $currency_symbol . $trialAmount,
                'price' => $trialAmount,
                'has_offer' => $has_offer,
                'offer_msg' => $offer_msg,
                'discount' => $discount,
                'next_payment' => $currency_symbol . $price,
                'trial_days' => $trialDays,
                'trial_amount' => $currency_symbol . $trialAmount,
                'is_auto_recurring' => !empty($subscriptionIds),
            ];
        };

        $offerPackage = $callback($offerPackage);
        $response['offer'] = $offerPackage;
        $response['recommended'] = PlanLimitHelper::recommendedPlan($request);
        $response['upgrade'] = $callback($upgradePackage);
        $response['ipData'] = $ipData;
        $response['btn_title'] = "Processed to pay";
        $response['money_back'] = false;
        $response['add_on_discount'] = "85%";
        if ($offerPage->enable_instructions && $offerPackage['is_auto_recurring']) {
            $trialDays = $offerPackage['trial_days'];
            $trialAmount = $offerPackage['trial_amount'];

            $next_payment = $response['offer']['next_payment'];
            $validity = $response['offer']['validity'];
            if ($trialDays > 0) {
                $response['autopay_terms'] = "<span>$currency_symbol$trialAmount for $trialDays-day trial, then $next_payment/every $validity days.</span>
                <span>By continuing, you agree to automatic recurring billing. Cancel anytime</span>";
            } else {
                $response['autopay_terms'] = "To begin your subscription, a payment of $next_payment will be charged now. CraftyArt will then charge $next_payment every $validity days.";
            }
        }

        $response['add_ons'] = [];
        if ($offerPage->is_show_addon) {
            $response['add_on_support'] = [
                "key" => "business_support",
                "title" => "सिर्फ " . ($isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD)) . " में पाएं पर्सनल बिज़नेस ट्रेनिंग सपोर्ट",
                "desc" => "क्लाइंट लाने से लेकर पहला डिजाइन बनाने तक – हम आपको सरल भाषा में हाथ पकड़कर पूरा ऑनलाइन बिज़नेस सिखाएंगे।",
                "sub_desc" => [
                    "क्लाइंट पाने का Proven सिस्टम",
                    "पहला डिजाइन स्टेप-बाय-स्टेप",
                    "डेली व्हाट्सएप सपोर्ट",
                    "प्रैक्टिकल कमाई की ट्रेनिंग"
                ],
                "btn_name" => "हाँ, मुझे अभी जॉइन करना है",
                "money_back" => true,
                "old_amount" => $isInr ? ("₹" . self::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_INR)) : ("$" . self::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_USD)),
                "amount" => $isInr ? PaymentController::$BUSINESS_SUPPORT_RATE_INR : PaymentController::$BUSINESS_SUPPORT_RATE_USD,
                "amount_str" => $isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD),
            ];

            $response['add_ons'] = [
                [
                    "title" => "Meta + Google + Whatsapp Marketing Course",
                    "key" => "meta_course",
                    "msg" => "Turn strategy into results with our best marketing masterclass..",
                    "old_amount" => $isInr ? ("₹" . self::findOldRate(PaymentController::$META_COURSE_RATE_INR)) : ("$" . self::findOldRate(PaymentController::$META_COURSE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$META_COURSE_RATE_INR : PaymentController::$META_COURSE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$META_COURSE_RATE_INR) : ("$" . PaymentController::$META_COURSE_RATE_USD),
                ],

                [
                    "title" => "Caricature Maker Tool",
                    "key" => "caricature",
//                "msg" => "Add " . PaymentController::$MAX_ADDON_CARICATURES . " Caricatures",
                    "msg" => "Create Personalize caricatures for your client",
                    "old_amount" => $isInr ? ("₹" . self::findOldRate(PaymentController::$MAX_ADDON_CARICATURE_RATE_INR)) : ("$" . self::findOldRate(PaymentController::$MAX_ADDON_CARICATURE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$MAX_ADDON_CARICATURE_RATE_INR : PaymentController::$MAX_ADDON_CARICATURE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$MAX_ADDON_CARICATURE_RATE_INR) : ("$" . PaymentController::$MAX_ADDON_CARICATURE_RATE_USD),
                ],

                [
                    "title" => "On Demand Service",
                    "key" => "on_demand_service",
                    "msg" => "Create Personalize Design for your client",
                    "old_amount" => $isInr ? ("₹" . self::findOldRate(PaymentController::$ON_DEMAND_SERVICE_RATE_INR)) : ("$" . self::findOldRate(PaymentController::$ON_DEMAND_SERVICE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$ON_DEMAND_SERVICE_RATE_INR : PaymentController::$ON_DEMAND_SERVICE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$ON_DEMAND_SERVICE_RATE_INR) : ("$" . PaymentController::$ON_DEMAND_SERVICE_RATE_USD),
                ]
            ];

            if (!DomainChecker::isMainDomain($request) && !empty(DomainChecker::getDomainName($request))) {
                $response['add_ons'][] = [
                    "title" => "सिर्फ " . ($isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD)) . " में पाएं पर्सनल बिज़नेस ट्रेनिंग सपोर्ट",
                    "key" => "business_support",
                    "msg" => "क्लाइंट लाने से लेकर पहला डिजाइन बनाने तक – हम आपको सरल भाषा में हाथ पकड़कर पूरा ऑनलाइन बिज़नेस सिखाएंगे।",
                    "old_amount" => $isInr ? ("₹" . self::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_INR)) : ("$" . self::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$BUSINESS_SUPPORT_RATE_INR : PaymentController::$BUSINESS_SUPPORT_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD),
                ];
            }
        }

//        $response['check_offer'] = $this->isTester();
        $response['check_offer'] = false;

        return $this->successed(datas: $response);
    }

    public static function findOldRate($newRate): float|int
    {
        return round($newRate * 100 / (100 - 85));
    }

    public function checkOffer(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");
        $pageUrl = $this->getPageUrl();
        if ($this->isTester()) {
            return ['headers' => $request->headers->all(), '$pageUrl' => $pageUrl];
        }
        $email = $request->input("email");
        $contact_no = $request->input("contact_no");
        $version = $request->input("v", 1);
        $showDecoded = $this->isTester() && $request->input("showDecode", false);
        if (empty($email)) return $this->failed(msg: "Invalid param", showDecoded: $showDecoded);

//        $offerTrial = true;
//        $planId = null;
        if ($version == 1) {
            $offerId = 23;
//            $planId = "plan_RnqB0tpHJFriDt";
//            $offerTrial = false;
        } else if ($version == 2) {
            $offerId = 29;
//            $planId = "plan_Rkl1WmV9Bw460h";
        } else if ($version == 3) {
            $offerId = 33;
//            $planId = "plan_Rba04L0g9vPRJL";
        } else if ($version == 4) {
            $offerId = 35;
//            $offerTrial = false;
        } else if ($version == 5) {
            $offerId = 36;
//            $offerTrial = false;
        } else {
            $offerId = 23;
//            $planId = "plan_RnqB0tpHJFriDt";
//            $offerTrial = false;
        }

        $offerPackage = Subscription::find($offerId);
        if (!$offerPackage) return $this->failed(msg: "Invalid param", showDecoded: $showDecoded);

        $planId = $offerPackage->sub_id;
        $trial_days = $offerPackage->trial_days;
        $offerTrial = $trial_days > 0;

        if (!empty($planId)) {
            $razorpayGateway = PaymentGateway::initByGateway('razorpay', null);
            /** @var Api|null $razorpay */
            $razorpay = $razorpayGateway?->client;

            $planData = $razorpay->plan->fetch($planId);
            $amount = $planData->item->amount / 100;
        } else {
            $amount = $offerPackage->price;
        }

        $nextPayment = "₹$amount";

        $user = UserData::where('email', $email)->first();
        $isNew = !$user || !TransactionLog::whereUserId($user->uid)->exists();

        $showTrial = $isNew && $offerTrial;

        $currentAmount = $isNew ? $offerPackage->price : $amount;

        $data = [
            'is_new' => $isNew,
            'price' => "₹$currentAmount",
            'next_payment' => !empty($planId) ? "Next payment will be $nextPayment" : null,
            'autopay_terms' => !empty($planId) ? "Next payment will be $nextPayment" : null,
            'btn_title' => $showTrial ? "Try it for $trial_days days at just" : "Processed to pay",
            'amount' => $currentAmount
        ];

//        $data = [
//            'is_new' => $isNew,
//            'price' => "₹$currentAmount",
//            'next_payment' => null,
//            'btn_title' => "Processed to pay",
//            'amount' => $currentAmount
//        ];

        return $this->successed(datas: $data, showDecoded: $showDecoded);

    }

    public function getAdditionalUserPlan(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $formatPrice = function ($amount, $currencyText = ''): string {
            return $currencyText . number_format((float)$amount, 2, '.', '');
        };

        $planId = $request->input('plan_id');
        $numUsers = (int)$request->input('number_of_user', 1);
        $currency = $request->input('currency', 'INR');

        $currency = strtoupper($currency);

        $planDiscount = PlanUserDiscount::first();

        $discountPercentage = null;
        $additionalDiscountPercentage = null;

        if ($numUsers > 1) {
            $discountPercentage = $planDiscount?->discount_percentage ?? 0;
            $additionalDiscountPercentage = $discountPercentage . '%';
        }

        $subPlans = SubPlan::where('plan_id', $planId)
            ->where('deleted', 0)
            ->get();

        $durations = PlanDuration::whereIn('id', $subPlans->pluck('duration_id')->unique())
            ->get()
            ->keyBy('id');

        $subPlanData = $subPlans->map(function ($subPlan) use ($currency, $numUsers, $discountPercentage, $additionalDiscountPercentage, $durations, $formatPrice) {
            $planDetails = $subPlan->plan_details;

            $duration = $durations[$subPlan->duration_id] ?? null;
            $durationValue = (int)($duration?->duration ?? 0);

            $additionalDuration = (int)($planDetails['additional_duration'] ?? 0);
            $totalDay = $durationValue + $additionalDuration;

            $details = [
                'additional_duration' => $additionalDuration,
                'duration' => $durationValue,
                'total_day' => $totalDay,
            ];

            $isInr = $currency === 'INR';

            $currencyText = $isInr ? '₹' : '$';
            $key_price = $isInr ? 'inr_price' : 'usd_price';
            $key_discount = $isInr ? 'inr_discount' : 'usd_discount';
            $key_offer_price = $currency === 'INR' ? 'inr_offer_price' : 'usd_offer_price';

            $basePrice = (float)($planDetails[$key_price] ?? 0);
            $subPlanDiscount = (float)($planDetails[$key_discount] ?? 0);

            // Step 1: Apply plan discount for 1 user
            $oneUserPrice = $basePrice;
            $discountedPrice = (float)($planDetails[$key_offer_price] ?? 0);

//            $discountAmount = ($oneUserPrice * $subPlanDiscount) / 100;
//            $discountedPrice = $oneUserPrice - $discountAmount;

            // Step 2: Apply additional discount if multiple users
            $additionalCharge = null;
            $totalPrice = $discountedPrice;

            if ($numUsers > 1) {
                $extraUsers = $numUsers - 1;

                $additionalDiscount = $discountPercentage > 0 ? ($discountedPrice * $discountPercentage / 100) : 0;
                $discountedExtraPrice = $discountedPrice - $additionalDiscount;

                $additionalCharge = $discountedExtraPrice * $extraUsers;
                $totalPrice = $discountedPrice + $additionalCharge;
            }

            // Step 3: Total calculations
            $baseTotalPrice = $oneUserPrice * $numUsers;
            $totalDiscountPercent = 100 - (($totalPrice / $baseTotalPrice) * 100);

            // Step 4: Format results
            $details['discount'] = $subPlanDiscount . '%';
            $details['base_price'] = $formatPrice($baseTotalPrice, $currencyText);
            $details['one_user_price'] = $formatPrice($oneUserPrice, $currencyText);
            $details['discount_price'] = $formatPrice($discountedPrice, $currencyText);
            $details['additional_user_discount'] = $numUsers > 1 ? $additionalDiscountPercentage : null;
            $details['additional_charge'] = $numUsers > 1 ? $formatPrice($additionalCharge, $currencyText) : null;
            $details['total_discount'] = number_format($totalDiscountPercent, 2) . '%';
            $details['total_price'] = $formatPrice($totalPrice, $currencyText);
            $details['amount'] = $totalPrice;
            $details['curSymbol'] = $currencyText;

            return [
                'id' => $subPlan->id,
                'string_id' => $subPlan->string_id,
                'plan_id' => $subPlan->plan_id,
                'duration_id' => (int)$subPlan->duration_id,
                'details' => $details,
            ];
        });

        $response = [
            'number_of_user' => $numUsers,
            'sub_plan' => $subPlanData,
        ];

        return $this->successed(datas: $response);
    }

    public function checkLimit(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $planData = SubscriptionController::getActivePlan($this->uid);

        $appearance = [];
        if ($planData) {
            $appearance = $planData->plan_limit;
        } else {
            $appearance[] = [
                'sub_name' => "1 templates / Day",
                'slug' => "access_template",
                'access_mode' => "daily",
                'limit' => 1,
                'meta_value' => 1,
            ];

            $appearance[] = [
                'sub_name' => "Export templates/video",
                'slug' => "download_limit",
                'access_mode' => "daily",
                'limit' => 1,
                'meta_value' => 1,
            ];
        }

        $datas = collect($appearance);
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | 2. Usage Counts (single time query)
        |--------------------------------------------------------------------------
        */

        $templateUsed = Draft::whereUserId($this->uid)
            ->whereDate('created_at', $today)
            ->count();

        $downloadUsed = ExportTable::whereUid($this->uid) // 👈 change if needed
        ->whereDate('created_at', $today)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 3. Apply Runtime Limit Logic
        |--------------------------------------------------------------------------
        */

        $datas = $datas->map(function ($item) use ($templateUsed, $downloadUsed) {

            $used = 0;

            switch ($item['slug']) {

                case 'access_template':
                    $used = $templateUsed;
                    break;

                case 'download_limit':
                    $used = $downloadUsed;
                    break;
            }

            // Agar koi usage mila ho
            if ($used !== null) {

                $item['used'] = $used;

                // Unlimited support (-1)
                if ($item['limit'] == -1) {

                    $item['remaining'] = 'unlimited';
                    $item['excessed'] = false;

                } else {

                    $item['remaining'] = max(0, $item['limit'] - $used);
                    $item['excessed'] = $used >= $item['limit'];
                }

                $item['excessed_msg'] = $item['excessed']
                    ? "Today's limit exceeded"
                    : null;
            }

            return $item;
        });

        return $this->successed(datas: ['datas' => $datas->toArray()]);
    }

}

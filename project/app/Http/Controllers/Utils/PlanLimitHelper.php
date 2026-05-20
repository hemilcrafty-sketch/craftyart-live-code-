<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\Pricing\Plan;
use App\Models\Pricing\PlanDuration;
use App\Models\Pricing\SubPlan;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlanLimitHelper
{

    public static int $DAILY_FREE_EXPORT_LIMIT = 3;

    public static function getPlanLimit(
        Request $request,
        string  $type,
        string  $uid,
        bool    $needToPurchase,
        bool    $goWithWatermark,
                $planData = null
    ): array
    {

        $todayRange = [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
        $used = 0;

        $planData = $planData ?? SubscriptionController::getActivePlan($uid);

        // =========================
        // 🚀 ACTIVE PLAN FLOW
        // =========================
        if ($planData) {

            // 👉 Get plan row
            $subRow = match ($planData->type) {
                0 => Subscription::find($planData->plan_id),
                1 => SubPlan::with('plan')->find($planData->plan_id),
                default => null
            };

            // 👉 Package name
            $packageName = match (true) {
                $subRow instanceof Subscription => $subRow->package_name,
                $subRow instanceof SubPlan => $subRow->plan?->name,
                default => 'Free'
            };

            $planLimit = collect($planData->plan_limit);
            $limitObject = $planLimit->firstWhere('slug', $type);

            // 👉 Unlimited
            if (!$limitObject || $limitObject['limit'] == -1) {
                return self::unlimitedResponse($used);
            }

            // 👉 Access mode
            $isDaily = ($limitObject['access_mode'] ?? 'daily') === 'daily';

            $range = $isDaily ? $todayRange : [$planData->created_at, $planData->expired_at];

            // 👉 Seats multiplier
            $seats = max($planLimit->firstWhere('slug', 'device_limit')['limit'] ?? 1, 1);
            $limit = $limitObject['limit'] * $seats;

            // 👉 Used count
            $used = match ($type) {
                'access_template' => Draft::whereUserId($uid)->whereBetween('created_at', $range)->count(),
                'download_limit' => ExportTable::whereUid($uid)->whereBetween('created_at', $range)->count(),
                default => 0
            };

            $icon = match ($type) {
                'access_template' => 'https://media.craftyartapp.com/icons/template.svg',
                'download_limit' => 'https://media.craftyartapp.com/icons/export.svg',
                default => 'https://media.craftyartapp.com/icons/caricature.svg',
            };

            // 👉 UI content
            $ui = self::buildUI($type, $packageName, $icon, $limit, $isDaily);

            return [
                ...$ui,
                "excessed" => $used >= $limit,
                "period" => $isDaily ? "Daily" : "Plan",
                "limit" => $limit,
                "used" => $used,
                "left" => max(0, $limit - $used),
                "secondaryBtn" => $isDaily ? "Try Again Tomorrow" : null,
                "plan_name" => $packageName,
                "recommended" => self::recommendedPlan($request, $planData)
            ];
        }

        // =========================
        // 🆓 FREE USER FLOW
        // =========================
        if ($type === 'download_limit') {

            $used = ExportTable::where('uid', $uid)
                ->where('watermark', 1)
                ->whereBetween('created_at', $todayRange)
                ->count();

            if ($needToPurchase) {
                $limit = $goWithWatermark ? self::$DAILY_FREE_EXPORT_LIMIT : 0;

                return [
                    ...self::buildUI($type, "Free", 'https://media.craftyartapp.com/icons/export.svg', 1, true),
                    "excessed" => $used >= $limit,
                    "period" => "Daily",
                    "limit" => $limit,
                    "used" => $used,
                    "left" => max(0, $limit - $used),
                    "secondaryBtn" => "Try Again Tomorrow",
                    "plan_name" => "Free",
                    "recommended" => self::recommendedPlan($request)
                ];
            }
        }

        return self::unlimitedResponse($used);
    }

    private static function buildUI(string $type, string $package, string $icon, int $limit, bool $isDaily): array
    {
        $map = [
            'access_template' => [
                'title' => "Template Access Limit Reached",
                'label' => "Templates"
            ],
            'download_limit' => [
                'title' => "Export Limit Reached",
                'label' => "Exports"
            ]
        ];

        $title = $map[$type]['title'] ?? "Limit Reached";
        $label = $map[$type]['label'] ?? "Items";

        $periodText = $isDaily ? "daily " : "";

        return [
            "title" => $title,
            "icon" => $icon,
            "message" => "You have reached your $periodText $label limit for the $package Plan.",
            "msg" => "You have reached your $periodText $label limit for the $package Plan.",
            "description" => "Your current plan allows $limit $label" . ($isDaily ? " per day" : "") . ". Upgrade for more.",
            "limitValue" => "$limit $label"
        ];
    }

    private static function unlimitedResponse(int $used): array
    {
        return [
            "limit" => -1,
            "used" => $used,
            "left" => "unlimited",
            "msg" => "Unlimited",
            "excessed" => false
        ];
    }

    public static function recommendedPlan(Request $request, ?TransactionLog $planData = null): ?array
    {

        $planId = $request->input("id");
        $durationId = $request->input("duration");

        $ipData = HelperController::getIpAndCountry($request);
        $currency = strtoupper($ipData['cur']);
        $isInr = $currency === "INR";

        $recommendedPlan = null;
        if ($planId) {
            $recommendedPlan = Plan::whereStringId($planId)->first();
        } else {
            $planData = $planData ?? SubscriptionController::getActivePlan($request->uid);
            if ($planData) {
//                $planDuration = PlanDuration::whereIsAnnual(1)->first();
//                if ($planDuration) $durationId = $planDuration->id;
                $recommendedPlan = Plan::whereIsRecommended(1)->whereStatus(1)->first();
            } else {
                $key = $isInr ? 'inr_offer_price' : 'usd_offer_price';
                $data = SubPlan::orderByRaw("JSON_EXTRACT(plan_details, '$.$key') ASC")->whereDeleted(0)->first();
                if ($data) {
                    $recommendedPlan = Plan::whereStringId($data->plan_id)->first();
                    $durationId = $data->duration_id;
                }
            }
        }

        if (!$recommendedPlan) {
            return null;
        }

        $data = PlanController::formatPlanData([$recommendedPlan], $currency, $durationId);

        $user_data = UserData::where('uid', $request->uid)->first();

        return [
            'ipData' => $ipData,
            'duration' => $data['duration'],
            'data' => $data['plans'][0],
            "btn_title" => "Proceed to Pay",
            'contact_no' => is_null($user_data) ? null : $user_data->contact_no,
            "add_on_discount" => "85%",
            "currency" => $isInr ? "₹" : "$",
            "add_ons" => [
                [
                    "title" => "Meta + Google + Whatsapp Marketing Course",
                    "key" => "meta_course",
                    "msg" => "Turn strategy into results with our best marketing masterclass..",
                    "old_amount" => $isInr ? ("₹" . PlanController::findOldRate(PaymentController::$META_COURSE_RATE_INR)) : ("$" . PlanController::findOldRate(PaymentController::$META_COURSE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$META_COURSE_RATE_INR : PaymentController::$META_COURSE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$META_COURSE_RATE_INR) : ("$" . PaymentController::$META_COURSE_RATE_USD),
                ],
                [
                    "title" => "Caricature Maker Tool",
                    "key" => "caricature",
//                "msg" => "Add " . PaymentController::$MAX_ADDON_CARICATURES . " Caricatures",
                    "msg" => "Create Personalize caricatures for your client",
                    "old_amount" => $isInr ? ("₹" . PlanController::findOldRate(PaymentController::$MAX_ADDON_CARICATURE_RATE_INR)) : ("$" . PlanController::findOldRate(PaymentController::$MAX_ADDON_CARICATURE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$MAX_ADDON_CARICATURE_RATE_INR : PaymentController::$MAX_ADDON_CARICATURE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$MAX_ADDON_CARICATURE_RATE_INR) : ("$" . PaymentController::$MAX_ADDON_CARICATURE_RATE_USD),
                ],
                [
                    "title" => "On Demand Service",
                    "key" => "on_demand_service",
                    "msg" => "Create Personalize Design for your client",
                    "old_amount" => $isInr ? ("₹" . PlanController::findOldRate(PaymentController::$ON_DEMAND_SERVICE_RATE_INR)) : ("$" . PlanController::findOldRate(PaymentController::$ON_DEMAND_SERVICE_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$ON_DEMAND_SERVICE_RATE_INR : PaymentController::$ON_DEMAND_SERVICE_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$ON_DEMAND_SERVICE_RATE_INR) : ("$" . PaymentController::$ON_DEMAND_SERVICE_RATE_USD),
                ],
                [
                    "title" => "सिर्फ " . ($isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD)) . " में पाएं पर्सनल बिज़नेस ट्रेनिंग सपोर्ट",
                    "key" => "business_support",
                    "msg" => "क्लाइंट लाने से लेकर पहला डिजाइन बनाने तक – हम आपको सरल भाषा में हाथ पकड़कर पूरा ऑनलाइन बिज़नेस सिखाएंगे।",
                    "old_amount" => $isInr ? ("₹" . PlanController::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_INR)) : ("$" . PlanController::findOldRate(PaymentController::$BUSINESS_SUPPORT_RATE_USD)),
                    "amount" => $isInr ? PaymentController::$BUSINESS_SUPPORT_RATE_INR : PaymentController::$BUSINESS_SUPPORT_RATE_USD,
                    "amount_str" => $isInr ? ("₹" . PaymentController::$BUSINESS_SUPPORT_RATE_INR) : ("$" . PaymentController::$BUSINESS_SUPPORT_RATE_USD),
                ]
            ]
        ];
    }
}

<?php

namespace App\Http\Controllers\Payment\Gateways;

use App\Http\Controllers\Payment\PaymentController;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Services\PaymentGateway;
use Razorpay\Api\Api;

class RazorpayGateway
{

    public static function createRazorpayOrder(
        PaymentGateway $paymentGateway,
        UserData       $user_data,
                       $assetDetails,
                       $craftyId,
                       $trialDays,
                       $isMeta,
                       $promoCodeId,
                       $subId,
                       $amount,
                       $validity,
                       $currency,
                       $razorpayCusId,
                       $description,
                       $seats,
                       $userAddOns
    ): array
    {
        $is_subs = false;

        /** @var Api $razorpay */
        $razorpay = $paymentGateway->client;
        $credentials = $paymentGateway->credentials;

        $orderId = null;
        $subscriptionId = null;

        $addons = [];

        if (!empty($subId)) {

            $is_subs = true;

            if ($validity >= 365) $totalCount = 12;
            else if ($validity >= 180) $totalCount = 24;
            else $totalCount = 120;

//            if (in_array($oldSubIds, [12, 26])) $totalCount = 12;
//            elseif ($oldSubIds == 18) $totalCount = 24;
//            else $totalCount = 120;

            $subData = [
                'plan_id' => $subId,
                'customer_notify' => false,
                'quantity' => $seats,
                'total_count' => $totalCount,
                'notes' => ['craftyId' => $craftyId]
            ];

//            $already = TransactionLog::whereUserId($user_data->uid)->whereIn('plan_id', PaymentController::$OFFER_IDS)->exists();

            $courseAddon = [
                'item' => [
                    'name' => 'Upfront Value',
                    'amount' => $amount * 100,
                    'currency' => 'INR'
                ]
            ];

            if ($trialDays > 0) {
                $subData['start_at'] = strtotime('+' . $trialDays . ' days');
                $addons[] = $courseAddon;
            } else if (!$isMeta && $promoCodeId != 0) {
                $subData['start_at'] = strtotime('+' . $validity . ' days');
                $addons[] = $courseAddon;
            }

//            if ($oldSubIds == 26) {
//                $subData['start_at'] = strtotime('+' . $validity . ' days');
//                $addons[] = $courseAddon;
//            } else {
//                if ($trialDays > 0) {
//                    if (!$already) {
//                        $subData['start_at'] = strtotime('+' . $trialDays . ' days');
//                        $addons[] = $courseAddon;
//                    }
//                } else if (!$isMeta) {
//                    if ($promoCodeId != 0) {
//                        $subData['start_at'] = strtotime('+' . $validity . ' days');
//                        $addons[] = $courseAddon;
//                    }
//                } else {
//                    $subData['start_at'] = strtotime('+' . $validity . ' days');
//                    $addons[] = $courseAddon;
//                }
//            }

            foreach ($userAddOns as $userAddOn) {
                $addons[] = [
                    'item' => [
                        'name' => $userAddOn['name'],
                        'amount' => $userAddOn['amount'] * 100,
                        'currency' => $userAddOn['currency'],
                    ]
                ];
            }

            if (!empty($addons)) $subData['addons'] = $addons;

            $subData['customer_id'] = $razorpayCusId;
            $datas = $razorpay->subscription->create($subData);
            $subscriptionId = $datas['id'];
        } else {

            foreach ($userAddOns as $userAddOn) {
                $amount += $userAddOn['amount'];
            }

//            $amount = $amount * $seats;

            $datas = $razorpay->order->create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'notes' => ['craftyId' => $craftyId]
            ]);

            $orderId = $datas['id'];
        }

        $payment_data = [];
        $payment_data['key'] = $credentials['key_id'];
        $payment_data['name'] = "CraftyArt";
        $payment_data['description'] = $description;
        if (!$is_subs) {
            $payment_data['customer_id'] = $razorpayCusId;
            $payment_data['order_id'] = $datas['id'];
        } else {
            $payment_data['recurring'] = 1;
            $payment_data['subscription_id'] = $datas['id'];
        }
        $payment_data['remember_customer'] = True;
        $payment_data['notes'] = ['craftyId' => $craftyId];
        $payment_data['method'] = [ // Add this to restrict to UPI
            'upi' => true,
            'card' => true,
            'netbanking' => true,
            'wallet' => true
        ];
        $payment_data['config'] = [
            'display' => [
                'preferences' => [
                    'show_default_blocks' => true,
                    'payment_options_order' => ['upi', 'card', 'netbanking', 'wallet'],
                    'highlight' => ['upi', 'card'],
                ]
            ]
        ];

        $payment_data['prefill'] = [
            'email' => $user_data->email,
            'contact' => $user_data->contact_no
        ];

//        stripe_payment_intent_id
        return ['id' => $orderId, 'subscription_id' => $subscriptionId, 'key' => 'razorpay_order_id', 'data' => $payment_data];
    }

    public static function cancelSubscription(?PaymentGateway $paymentGateway, $subId): ?string
    {

        if (!$paymentGateway) {
            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
        }

        if (!$paymentGateway) {
            return "Payment gateway not found";
        }

        /** @var Api|null $razorpay */
        $razorpay = $paymentGateway->client;

        try {
            $razorpay->subscription->fetch($subId)->cancel(array("cancel_at_cycle_end" => false));
            return null;
        } catch (\Exception $e) {
            return "Something went wrong";
        }
    }
}

<?php

namespace App\Http\Controllers\Payment\Gateways;

use App\Http\Controllers\Payment\PaymentController;
use App\Services\PaymentGateway;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeGateway
{

    public static function createStripeOrder(
        PaymentGateway $paymentGateway,
                       $craftyId,
                       $paymentMethodId,
                       $subId,
                       $amount,
                       $currency,
                       $stripeCusId,
                       $seats,
                       $userAddOns
    ): array
    {

        /** @var StripeClient $stripeClient */
        $stripeClient = $paymentGateway->client;

        $isSub = $subId && $currency !== "INR";

        if ($isSub) {
            $payData = [
                'customer' => $stripeCusId,
                'items' => [
                    [
                        'price' => $subId,
                        'quantity' => $seats,
                    ],
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'craftyId' => $craftyId
                ],
            ];

            $addons = [];

            foreach ($userAddOns as $userAddOn) {
                $addons[] = [
                    'price_data' => [
                        'currency' => $userAddOn['currency'],
                        'product_data' => [
                            'name' => $userAddOn['name'],
                            'description' => "One-time add-on service"
                        ],
                        'unit_amount' => $userAddOn['amount'] * 100,
                    ],
                    'quantity' => 1,
                ];
            }

            if (!empty($addons)) $payData['add_invoice_items'] = $addons;
        } else {

            foreach ($userAddOns as $userAddOn) {
                $amount += $userAddOn['amount'];
            }

//            $amount = $amount * $seats;

            $payData = [
                'amount' => (int)($amount * 100),
                'currency' => $currency,
                'customer' => $stripeCusId,
                'payment_method' => $paymentMethodId,
                'description' => 'Payment for Craftyart Service',
                'return_url' => 'https://www.craftyartapp.com',
                'confirmation_method' => 'automatic',
                'setup_future_usage' => 'off_session',
                'confirm' => true,
                'metadata' => [
                    'craftyId' => $craftyId
                ],
            ];
        }

        try {
            if ($isSub) $paymentIntent = $stripeClient->subscriptions->create($payData);
            else $paymentIntent = $stripeClient->paymentIntents->create($payData);

            if ($isSub) $paymentIntent->client_secret = $paymentIntent->latest_invoice->payment_intent->client_secret;
        } catch (ApiErrorException $e) {
            return [];
        }

        return ['id' => $paymentIntent->id, 'key' => 'stripe_payment_intent_id', 'data' => $paymentIntent];
    }

    public static function cancelSubscription(?PaymentGateway $paymentGateway, $subId): ?string
    {

        if (!$paymentGateway) {
            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
        }

        if (!$paymentGateway) {
            return "Payment gateway not found";
        }

        /** @var StripeClient|null $stripeClient */
        $stripeClient = $paymentGateway->client;

        try {
            $stripeClient->subscriptions->cancel($subId);
            return null;
        } catch (\Exception $e) {
            return "Something went wrong";
        }

    }
}

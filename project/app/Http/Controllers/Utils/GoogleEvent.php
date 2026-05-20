<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Facades\Http;

class GoogleEvent
{
    public static array $PURCHASE = [
        "id" => "G-FV7CT0VRZM",
        "name" => "purchase_template",
        "ads_id" => "AW-17011651980/sv2SCIDwyqgbEIzr5K8_"
    ];

    public static function trackEvent(GoogleEnum $enum, array $paymentIntent): void
    {
//        try {
//            $payload = [
//                'v' => '2',
//                'tid' => 'G-FV7CT0VRZM',
//                'en' => $enum->value,
//                'ep.value' => $paymentIntent['amount'],
//                'ep.currency' => $paymentIntent['currency'],
//                'dl'  => $paymentIntent['url'] ?? null,
//            ];
//
//            $payload['ep.user_data.email_address'] = hash('sha256', strtolower(trim($paymentIntent['email'])));
//            $payload['email'] = strtolower(trim($paymentIntent['email']));
//
//            if (!empty($paymentIntent['transaction_id'])) {
//                $payload['ep.transaction_id'] = $paymentIntent['transaction_id'];
//            }
//
//            if (!empty($paymentIntent['_gclid'])) {
//                $payload['gclid'] = $paymentIntent['_gclid'];
//            }
//
//            if (!empty($paymentIntent['_wbraid'])) {
//                $payload['wbraid'] = $paymentIntent['_wbraid'];
//            }
//
//            if (!empty($paymentIntent['_gbraid'])) {
//                $payload['gbraid'] = $paymentIntent['_gbraid'];
//            }
//
//            Http::withHeaders([
//                'X-Gtm-Server-Preview' => 'ZW52LTN8eGUyS2k4NVUycFF6VExfQjVsN0xSUXwxOWJhMTVlZjhlMGE4MDFiMWMzYTA=',
//            ])->get('https://crafty-gtm.craftyartapp.com/g/collect', array_filter($payload));
//        } catch (\Exception $e) {
//
//        }
    }

    private static function sendGoogleAdsConversion($eventData, $gclid, $gclAu, $paymentIntent): void
    {
        $apiToken = "1hf838SbXF8a2ePPdGS7mw";

        $data = array_filter([
            'gclid' => $gclid,
            'gcl_au' => $gclAu,
            'conversion_action' => $eventData->ads_id,
            'value' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'order_id' => $paymentIntent->transaction_id,
            'conversion_date_time' => gmdate('Y-m-d\TH:i:s\Z'),
        ]);

        try {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiToken,
            ])->post('https://www.google.com/ads/conversion', $data);
        } catch (\Exception $e) {

        }
    }

    private static function sendGA4PurchaseEvent($eventData, $gclid, $gclAu, $gaClientId, $paymentIntent): void
    {
        $apiSecret = "EfyhWzkbQOuY1jGgcfoFgA";
        $clientId = $gaClientId ?? session()->getId(); // Fallback to session ID if no GA client ID

        $measurementId = $eventData->id;

        $payload = [
            'client_id' => $clientId,
            'events' => [
                [
                    'name' => $eventData->name,
                    'params' => array_filter([
                        'currency' => $paymentIntent->currency,
                        'value' => $paymentIntent->amount,
                        'transaction_id' => $paymentIntent->transaction_id,
                        'items' => [
                            [
                                'item_id' => $paymentIntent->id,
                                'item_name' => $paymentIntent->name,
                                'price' => $paymentIntent->amount,
                                'quantity' => 1,
                            ],
                        ],
                        'gclid' => $gclid,
                        'gcl_au' => $gclAu,
                    ]),
                ],
            ],
        ];

        try {
            Http::post("https://www.google-analytics.com/mp/collect?measurement_id=$measurementId&api_secret=$apiSecret", $payload);
        } catch (\Exception $e) {
        }
    }

}

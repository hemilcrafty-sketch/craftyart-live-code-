<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Facades\Http;

class GoogleEvent2
{
    public static array $PURCHASE = [
        "id" => "G-FV7CT0VRZM",
        "name" => "purchase_template",
        "ads_id" => "AW-17011651980/sv2SCIDwyqgbEIzr5K8_"
    ];

    public static function trackEvent($eventData, array $paymentIntent): void
    {
        $gclid = $paymentIntent['_gclid'] ?? null;
        $gclAu = $paymentIntent['_gcl_au'] ?? null;
        $gaClientId = $paymentIntent['_ga'] ?? null;

        if ($gaClientId) {
            $gaParts = explode('.', $gaClientId);
            if (count($gaParts) >= 4) {
                $gaClientId = $gaParts[2] . '.' . $gaParts[3];
            }
        }

        if ($gclid || $gclAu) {
            GoogleEvent2::sendGoogleAdsConversion(json_decode(json_encode($eventData)), $gclid, $gclAu, json_decode(json_encode($paymentIntent)));
        }

        if ($gclid || $gclAu || $gaClientId) {
            GoogleEvent2::sendGA4PurchaseEvent(json_decode(json_encode($eventData)), $gclid, $gclAu, $gaClientId, json_decode(json_encode($paymentIntent)));
        }
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

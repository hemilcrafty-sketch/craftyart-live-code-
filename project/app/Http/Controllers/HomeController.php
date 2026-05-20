<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\AIPurchaseHistory;
use Carbon\Carbon;

class HomeController
{

    public static function index(): array
    {
        $now = Carbon::now();

        $periods = [
            'today'      => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday'  => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year'  => [Carbon::create($now->year, 1, 1), Carbon::create($now->year, 12, 31)],
            'last_year'  => [Carbon::create($now->year - 1, 1, 1), Carbon::create($now->year - 1, 12, 31)],
            'total'      => [],
        ];

        // Query helper
        $sum = function ($model, $field, $currency, $range, $extra = []) {
            return (float) $model::query()
                ->where($extra['currency_field'] ?? 'currency_code', $currency)
                ->when($range, fn($q) => $q->whereBetween('created_at', $range))
                ->when($extra['whereNotNull'] ?? null, fn($q, $col) => $q->whereNotNull($col))
                ->when($extra['whereIn'] ?? null, fn($q, $arr) => $q->whereIn(array_key_first($arr), $arr[array_key_first($arr)]))
                ->sum($field);
        };

        // Filters
        $withFbc = ['whereNotNull' => 'fbc'];                               // for templates, caricature, caricature_credit
        $offerFilter = ['whereIn' => ['plan_id' => [23, 24, 26, 29, 30]]];  // for subs

        $baseLink = "https://panel.craftyartapp.com/new_template";
        $models = [
            'templates'         => ["link" => "$baseLink/purchases", "model" => PurchaseHistory::class],
            'caricature'        => ["link" => "$baseLink/cari_purchases", "model" => CaricaturePurchaseHistory::class],
            'caricature_credit' => ["link" => "$baseLink/transcation_logs", "model" => AIPurchaseHistory::class],
            'video'             => ["link" => "$baseLink/video_transcation_logs", "model" => VideoPurchaseHistory::class],
            'subs'              => ["link" => "$baseLink/transcation_logs", "model" => TransactionLog::class],
        ];

        $labelTitles = [
            'today'      => "Today's",
            'yesterday'  => "Yesterday's",
            'this_month' => "This Month's",
            'last_month' => "Last Month's",
            'this_year'  => "This Year's",
            'last_year'  => "Last Year's",
            'total'      => "Total",
        ];

        $symbols = ['INR' => 'Rs', 'USD' => '$'];

        // Internal numeric stores
        $raw = [];     // $raw[period][key]['INR'|'USD'] = amount
        $fbc = [];     // $fbc[period][key]['INR'|'USD'] = amount (only for specific keys)
        $offer = [];   // $offer[period]['subs']['INR'|'USD'] = amount

        $revenueData = [];

        foreach ($periods as $label => $range) {

            // Collect raw, fbc, offer for each model & currency
            foreach ($symbols as $currency => $symbol) {
                foreach ($models as $key => $config) {
                    $model = $config['model'];

                    $normal = $sum($model, 'net_amount', $currency, $range);
                    $raw[$label][$key][$currency] = $normal;

                    // FBC breakdown for selected models
                    if (in_array($key, ['templates','caricature','caricature_credit'], true)) {
                        $fbc[$label][$key][$currency] = $sum($model, 'net_amount', $currency, $range, $withFbc);
                    }

                    // Offer breakdown for subs
                    if ($key === 'subs') {
                        $offer[$label]['subs'][$currency] = $sum(TransactionLog::class, 'net_amount', $currency, $range, $offerFilter);
                    }
                }
            }

            // Build ordered rows for this period
            $rows = [];

            foreach ($models as $key => $config) {
                $titleBase = $labelTitles[$label] . ' ' . ucwords(str_replace('_', ' ', $key));

                // INR row piece
                $inrNormal = $raw[$label][$key]['INR'] ?? 0.0;
                $inrText = "Rs {$inrNormal}";

                if (isset($fbc[$label][$key]['INR'])) {
                    $inrText .= " - ({$fbc[$label][$key]['INR']})";
                }
                if ($key === 'subs' && isset($offer[$label]['subs']['INR'])) {
                    $inrText .= " - ({$offer[$label]['subs']['INR']})";
                }

                // USD row piece
                $usdNormal = $raw[$label][$key]['USD'] ?? 0.0;
                $usdText = "$ {$usdNormal}";

                if (isset($fbc[$label][$key]['USD'])) {
                    $usdText .= " - ({$fbc[$label][$key]['USD']})";
                }
                if ($key === 'subs' && isset($offer[$label]['subs']['USD'])) {
                    $usdText .= " - ({$offer[$label]['subs']['USD']})";
                }

                $rows[] = [
                    "title" => $titleBase,
                    "inr"   => $inrText,
                    "usd"   => $usdText,
                    "link"  => $config['link'],
                ];
            }

            // Totals (numeric)
            $inrTotal = array_sum(array_column($raw[$label], 'INR'));
            $usdTotal = array_sum(array_column($raw[$label], 'USD'));
            $final    = $inrTotal + $usdTotal; // NOTE: no FX conversion, just a raw add

            // "Total" row (no link)
            $rows[] = [
                "title" => $labelTitles[$label] . " Total",
                "inr"   => "Rs {$inrTotal}",
                "usd"   => "$ {$usdTotal}",
            ];

            // "Final" row (no link)
            $rows[] = [
                "title" => $labelTitles[$label] . " Final",
                "final" => "Rs {$final}",
            ];

            $revenueData[$label] = $rows;
        }

        return $revenueData;
    }


    public static function index1(): array
    {
        $now = Carbon::now();

        $periods = [
            'today'      => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday'  => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year'  => [Carbon::create($now->year, 1, 1), Carbon::create($now->year, 12, 31)],
            'last_year'  => [Carbon::create($now->year - 1, 1, 1), Carbon::create($now->year - 1, 12, 31)],
            'total'      => [],
        ];

        $sum = function ($model, $field, $currency, $range, $extra = []) {
            return (float) $model::query()
                ->where($extra['currency_field'] ?? 'currency_code', $currency)
                ->when($range, fn($q) => $q->whereBetween('created_at', $range))
                ->when($extra['whereNotNull'] ?? null, fn($q, $col) => $q->whereNotNull($col))
                ->when($extra['whereIn'] ?? null, fn($q, $arr) => $q->whereIn(array_key_first($arr), $arr[array_key_first($arr)]))
                ->sum($field);
        };

        $withFbc = ['whereNotNull' => 'fbc'];
        $offerFilter = ['whereIn' => ['plan_id' => [23, 24, 26, 29, 30]]];

        $baseLink = "https://panel.craftyartapp.com/new_template";
        $models = [
            'templates'         => ["link" => "$baseLink/purchases", "model" => PurchaseHistory::class],
            'caricature'        => ["link" => "$baseLink/cari_purchases", "model" => CaricaturePurchaseHistory::class],
            'caricature_credit' => ["link" => "$baseLink/transcation_logs", "model" => AIPurchaseHistory::class],
            'video'             => ["link" => "$baseLink/video_transcation_logs", "model" => VideoPurchaseHistory::class],
            'subs'              => ["link" => "$baseLink/transcation_logs", "model" => TransactionLog::class],
        ];

        $symbols = ['INR' => 'Rs', 'USD' => '$'];

        $raw = [];        // <-- numeric internal data
        $revenueData = []; // <-- final formatted output

        foreach ($periods as $label => $range) {

            foreach ($symbols as $currency => $symbol) {
                foreach ($models as $key => $modelData) {

                    $model = $modelData['model'];
                    $link  = $modelData['link'];

                    $normal = $sum($model, 'net_amount', $currency, $range);
                    $raw[$label][$key][$currency] = $normal;

                    $fbc = in_array($key, ['templates','caricature','caricature_credit'])
                        ? $sum($model, 'net_amount', $currency, $range, $withFbc)
                        : null;

                    $offer = $key === 'subs'
                        ? $sum(TransactionLog::class, 'net_amount', $currency, $range, $offerFilter)
                        : null;

                    $line = "{$symbol} {$normal}";
                    if ($fbc !== null)   $line .= " - ($fbc)";
                    if ($offer !== null) $line .= " - ($offer)";

                    $revenueData["{$label}_{$key}_" . strtolower($currency)] = [
                        "amount" => $line,
                        "link" => $link
                    ];
                }
            }

            // Calculate totals using raw numeric array
            $inr = array_sum(array_column($raw[$label], 'INR'));
            $usd = array_sum(array_column($raw[$label], 'USD'));

            $revenueData["{$label}_inr"]   =  [
                "amount" => "Rs {$inr}"
            ];
            $revenueData["{$label}_usd"]   = [
                "amount" => "Rs {$usd}"
            ];

            $revenueData["{$label}_final"] = [
                "amount" =>  "Rs " . ($inr + $usd)
            ];
        }

        return $revenueData;
    }

}

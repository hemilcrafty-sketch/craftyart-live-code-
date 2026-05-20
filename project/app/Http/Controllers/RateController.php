<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\TemplateRate;
use Illuminate\Support\Collection;
use stdClass;

class RateController extends Controller
{

    public static function getRates($needVid = false): Collection
    {
        if (!$needVid) {
            $rateTypes = ['premium_template', 'freemium_template', 'remove_watermark', 'caricature'];
        } else {
            $rateTypes = ['premium_template', 'freemium_template', 'remove_watermark', 'caricature', 'mobile_video'];
        }

        return TemplateRate::whereIn('name', $rateTypes)
            ->pluck('value', 'name')
            ->map(function ($value) {
                $decoded = json_decode($value);
                return $decoded ?? [];
            });
    }

    public static function getTemplateRates(Collection $rates, $size, Design|stdClass $item, $extraDisc = 0): array
    {

        $data = match (true) {
            is_array($item->caricature_ids) => $item->caricature_ids,
            is_string($item->caricature_ids) => json_decode($item->caricature_ids, true) ?? [],
            default => [],
        };

        $isPremium = $item->is_premium == 1;
        $isFreemium = $item->is_freemium == 1;
        $hasAnimation = $item->animation == 1;
        $isEditorChoice = $item->editor_choice == 1;
        $totalCaricatures = count($data);

        if ($isPremium) {
            $value = $rates['premium_template'] ?? TemplateRate::getRates("premium_template");
        } else if ($isFreemium) {
            $value = $rates['freemium_template'] ?? TemplateRate::getRates("freemium_template");
        } else {
            $value = $rates['remove_watermark'] ?? TemplateRate::getRates("remove_watermark");
        }

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        if ($hasAnimation) {
            $inrAmount = $inrAmount + $value->inr->animation;
            $usdAmount = $usdAmount + $value->usd->animation;
        }

        if ($isEditorChoice) {
            $inrAmount = $inrAmount + $value->inr->editor_choice;
            $usdAmount = $usdAmount + $value->usd->editor_choice;
        }

        $inrAmount = $inrAmount + ($value->inr->caricature * $totalCaricatures);
        $usdAmount = $usdAmount + ($value->usd->caricature * $totalCaricatures);

        if ($extraDisc > 0) {
            $inrDiscount = round($inrAmount * $extraDisc / 100);
            $usdDiscount = $usdAmount * $extraDisc / 100;

            $inrAmount = $inrAmount - $inrDiscount;
            $usdAmount = $usdAmount - $usdDiscount;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function getVideoRates($rates, $size): array
    {
        $value = $rates['mobile_video'] ?? TemplateRate::getRates("mobile_video");

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

    public static function getCaricatureRates($rates, $size, $hasAnimation, $isEditorChoice): array
    {
        $value = $rates['caricature'] ?? TemplateRate::getRates("caricature");

        if ($size == 0) {
            $size = 1;
        }

        $extraPage = $size - 1;

        $inrAmount = $value->inr->base_price + ($value->inr->page_price * $extraPage);
        $usdAmount = $value->usd->base_price + ($value->usd->page_price * $extraPage);
        $inrAmount = min($inrAmount, $value->inr->max_price);
        $usdAmount = min($usdAmount, $value->usd->max_price);

        if ($hasAnimation) {
            $inrAmount = $inrAmount + $value->inr->animation;
            $usdAmount = $usdAmount + $value->usd->animation;
        }

        if ($isEditorChoice) {
            $inrAmount = $inrAmount + $value->inr->editor_choice;
            $usdAmount = $usdAmount + $value->usd->editor_choice;
        }

        $payment['inrVal'] = $inrAmount;
        $payment['usdVal'] = $usdAmount;
        $payment['inrAmount'] = '₹' . $inrAmount;
        $payment['usdAmount'] = '$' . $usdAmount;

        return $payment;
    }

}

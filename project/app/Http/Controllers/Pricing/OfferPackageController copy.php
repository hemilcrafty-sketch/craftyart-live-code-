<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\Plan;
use App\Models\Pricing\SubPlan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfferPackageController extends AppBaseController
{
    /** Sentinel value for "custom days" in the duration dropdown (must match JS). */
    public const SUB_PLAN_CUSTOM = '__custom__';

    public function index(Request $request): Factory|View|Application
    {
        $OfferPackage = OfferPackage::with(['plan', 'duration'])->orderByDesc('id')->get();
        $plans = Plan::where('is_free_type', 0)->where('status', 1)->get();

        return view('pricing.offer_package.index', compact('OfferPackage', 'plans'));
    }

    public function getDurations(string $plan_id): JsonResponse
    {
        $rows = SubPlan::with('duration')->where('plan_id', $plan_id)->get();

        $data = $rows->map(static function (SubPlan $sub): array {
            $d = $sub->duration;
            $durationRaw = (string) ($d->duration ?? '');
            $durationDays = '';
            if ($durationRaw !== '' && preg_match('/\d+/', $durationRaw, $m)) {
                $durationDays = $m[0];
            }

            return [
                'duration_id'   => $sub->duration_id,
                'duration_name' => $d->name ?? '-',
                'is_annual'     => (int) ($d->is_annual ?? 0),
                'duration_days' => $durationDays,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id'         => 'required',
            'duration_id'     => 'required|string',
            'package_name'    => 'required|string',
            'inr_price'       => 'required|numeric|min:0',
            'inr_offer_price' => 'required|numeric|min:0',
            'usd_price'       => 'required|numeric|min:0',
            'usd_offer_price' => 'required|numeric|min:0',
            'status'          => 'nullable|boolean',
        ]);

        $isCustom = $request->duration_id === self::SUB_PLAN_CUSTOM;
        if ($isCustom) {
            $request->validate(['custom_days' => 'required|integer|min:1']);
        }

        $inrPrice = (float) $request->inr_price;
        $inrOffer = (float) $request->inr_offer_price;
        $usdPrice = (float) $request->usd_price;
        $usdOffer = (float) $request->usd_offer_price;

        $planDetails = [
            'additional_duration' => (int) ($request->additional_duration ?? 0),
            'inr_price'           => $inrPrice,
            'inr_offer_price'     => $inrOffer,
            'inr_trial_days'      => (int) ($request->inr_trial_days ?? 0),
            'inr_trial_price'     => (float) ($request->inr_trial_price ?? 0),
            'inr_discount'        => $inrPrice > 0 ? round((($inrPrice - $inrOffer) / $inrPrice) * 100).'%' : '0%',
            'usd_price'           => $usdPrice,
            'usd_offer_price'     => $usdOffer,
            'usd_trial_days'      => (int) ($request->usd_trial_days ?? 0),
            'usd_trial_price'     => (float) ($request->usd_trial_price ?? 0),
            'usd_discount'        => $usdPrice > 0 ? round((($usdPrice - $usdOffer) / $usdPrice) * 100).'%' : '0%',
        ];

        $subscriptionIds = [];
        if ($request->filled('subscription_ids_json')) {
            $decoded = json_decode((string) $request->subscription_ids_json, true);
            $subscriptionIds = is_array($decoded) ? $decoded : [];
        }

        $data = [
            'plan_id'          => $request->plan_id,
            'duration_id'      => $isCustom ? 'custom' : (string) $request->duration_id,
            'custom_days'      => $isCustom ? (int) $request->custom_days : null,
            'package_name'     => $request->package_name,
            'plan_details'     => $planDetails,
            'subscription_ids' => $subscriptionIds !== [] ? $subscriptionIds : null,
            'status'           => $request->status ?? 0,
        ];

        if ($request->id) {
            $package = OfferPackage::findOrFail($request->id);
            $package->update($data);
            $msg = 'Offer Package updated successfully!';
        } else {
            $data['string_id'] = HelperController::generateStringIds(10, '', OfferPackage::class);
            $package = OfferPackage::create($data);
            $msg = 'Offer Package created successfully!';
        }

        return response()->json([
            'status'  => true,
            'message' => $msg,
            'data'    => $package->load(['plan', 'duration']),
        ]);
    }

    public function edit($id): JsonResponse
    {
        return response()->json(OfferPackage::findOrFail($id));
    }

    public function destroy($id): JsonResponse
    {
        OfferPackage::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Deleted successfully']);
    }
}

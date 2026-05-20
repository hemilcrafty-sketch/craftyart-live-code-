<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\SubPlan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\OfferPackage;
use App\Models\BonusPackage;

class OfferPackageController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $OfferPackage = OfferPackage::with(['plan', 'duration', 'BonusPackage'])->orderByDesc('id')->get();
        $plans = Plan::where('is_free_type', 0)->where('status', 1)->get();
        $BouncePackages = BonusPackage::all();

        return view('offer_package.index', compact('OfferPackage', 'plans', 'BouncePackages'));
    }

    public function getDurations($plan_id)
    {
        $subPlans = SubPlan::with('duration')
            ->where('plan_id', $plan_id)
            ->get();

        $data = $subPlans->map(function ($sub) {
            return [
                'sub_plan_id' => $sub->string_id,
                'duration_id' => $sub->duration_id,
                'duration_name' => $sub->duration->name ?? '-',
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
            'sub_plan_id' => 'required',
            'bounce_code_id' => 'required',
            'status' => 'nullable|boolean',
        ]);

        $status = $request->status ?? 0;

        if ($status == 0) {
            $activeCount = OfferPackage::when($request->id, function ($q) use ($request) {
                $q->where('id', '!=', $request->id);
            })->where('status', 1)->count();

            if ($activeCount == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'At least one Bounce Package must be Active!'
                ]);
            }
        }

        $subPlan = SubPlan::where('string_id', $request->sub_plan_id)->firstOrFail();

        $data = [
            'plan_id' => $request->plan_id,
            'sub_plan_id' => $request->sub_plan_id,
            'duration_id' => $subPlan->duration_id,
            'bounce_code_id' => $request->bounce_code_id,
            'status' => $status,
        ];

        if ($request->id) {
            $package = OfferPackage::findOrFail($request->id);
            $package->update($data);
            $msg = 'Bounce Package updated successfully!';
        } else {
            $data['string_id'] = HelperController::generateStringIds(10, '', OfferPackage::class);
            $package = OfferPackage::create($data);
            $msg = 'Bounce Package created successfully!';
        }

        if ($status == 1) {
            OfferPackage::where('id', '!=', $package->id)->update(['status' => 0]);
        }

        return response()->json([
            'status' => true,
            'message' => $msg,
            'data' => $package->load(['plan', 'duration', 'BonusPackage'])
        ]);
    }

    public function edit($id): JsonResponse
    {
        $package = OfferPackage::findOrFail($id);
        return response()->json($package);
    }

    public function destroy($id): JsonResponse
    {
        $package = OfferPackage::findOrFail($id);
        $package->delete();
        return response()->json(['status' => true, 'message' => 'Deleted successfully']);
    }
}
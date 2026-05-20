<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Pricing\PlanUserDiscount;

class PlanUserDiscountController extends Controller {

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $data = [
            'discount_percentage' => $request->discount_percentage,
            'factor' => $request->factor,
            'x' => 1 - ($request->discount_percentage / 100),
        ];

        if ($request->id) {
            $discount = PlanUserDiscount::findOrFail($request->id);
            $discount->update($data);
        } else {
            $discount = PlanUserDiscount::create($data);
        }

        return response()->json(['success' => true, 'data' => $discount]);
    }

    // Get single record for edit
    public function edit($id): JsonResponse
    {
        $discount = PlanUserDiscount::findOrFail($id);
        return response()->json($discount);
    }

    // Delete
    public function destroy($id): JsonResponse
    {
        PlanUserDiscount::destroy($id);
        return response()->json(['success' => true]);
    }
}

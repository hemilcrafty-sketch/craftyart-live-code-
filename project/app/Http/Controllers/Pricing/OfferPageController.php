<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\OfferPage;
use App\Models\Pricing\Plan;
use App\Models\Pricing\SubPlan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfferPageController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $offerPages = OfferPage::with(['offerPackage'])->orderByDesc('id')->get();
        $offerPackages = OfferPackage::with('duration')->where('status', 1)->get();
        return view('pricing.offer_page.index', compact('offerPages', 'offerPackages'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'offer_package_id'    => 'required|exists:crafty_pricing_mysql.offer_package,id',
            'slug'                => 'required|string|unique:crafty_pricing_mysql.offer_pages,slug,' . ($request->id ?? 'NULL') . ',id',
            'enable_instructions' => 'nullable|boolean',
            'instructions'        => 'required_if:enable_instructions,1|nullable|string',
            'is_show_addon'       => 'nullable|boolean',
        ], [
            'instructions.required_if' => 'Instruction text is required when instructions are enabled.',
            'slug.required' => 'The slug field is required.',
            'slug.unique'   => 'This slug is already in use. Please choose a different one.',
            'offer_package_id.required' => 'Please select an offer package.',
            'offer_package_id.exists'   => 'The selected offer package is invalid.',
        ]);

        $data = [
            'offer_package_id'    => $request->offer_package_id,
            'slug'                => $request->slug,
            'enable_instructions' => $request->boolean('enable_instructions'),
            'instructions'        => $request->instructions,
            'is_show_addon'       => $request->boolean('is_show_addon'),
        ];

        if ($request->id) {
            $offerPage = OfferPage::findOrFail($request->id);
            $offerPage->update($data);
            $msg = 'Offer Page updated successfully!';
        } else {
            $offerPage = OfferPage::create($data);
            $msg = 'Offer Page created successfully!';
        }

        return response()->json([
            'status'  => true,
            'message' => $msg,
            'data'    => $offerPage->load('offerPackage'),
        ]);
    }

    public function edit($id): JsonResponse
    {
        return response()->json(OfferPage::findOrFail($id));
    }

    public function destroy($id): JsonResponse
    {
        // OfferPage::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Deleted successfully']);
    }
}

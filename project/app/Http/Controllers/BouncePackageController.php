<?php

namespace App\Http\Controllers;

use App\Models\BonusPackage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BouncePackageController extends Controller
{
    public function index(): Factory|View|Application
    {
        $BouncePackages = BonusPackage::orderByDesc('id')->get();
        return view('bonus_package.index', compact('BouncePackages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $id = $request->id;
        BonusPackage::updateOrCreate(
            ['id' => $id],
            [
                'string_id' => $id ? $request->string_id : Str::random(8),
                'bonus_code' => $request->bonus_code,
                'inr_price' => $request->inr_price,
                'usd_price' => $request->usd_price,
                'additional_day' => $request->additional_day,
            ]
        );

        return redirect()->route('bonus-package.index')->with('success', 'Bounce Code saved successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        BonusPackage::where("id", $id)->delete();
        return redirect()->route('bonus-package.index')->with('success', 'Bounce Code deleted.');
    }
}

<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor\WalletSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Vendor wallet settings (crafty_vendor.wallet_settings). Panel: /vendor/wallet-settings */
class VendorWalletSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = WalletSetting::orderBy('created_at', 'desc')->get();
        return view('vendor.wallet_settings.index', compact('settings'));
    }

    public function create()
    {
        return view('vendor.wallet_settings.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setting_key' => 'required|string|max:100',
            'setting_name' => 'nullable|string|max:255',
            'min_withdrawal_threshold' => 'required|numeric|min:0',
            'max_withdrawal_limit' => 'nullable|numeric|min:0',
            'freelancer_commission_rate' => 'required|numeric|min:0|max:100',
            'referral_commission_rate' => 'required|numeric|min:0|max:100',
            'payment_type' => 'required|in:manual,razorpay',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        WalletSetting::create([
            'setting_key' => $request->setting_key,
            'setting_name' => $request->setting_name,
            'description' => $request->description,
            'min_withdrawal_threshold' => $request->min_withdrawal_threshold,
            'max_withdrawal_limit' => $request->max_withdrawal_limit,
            'platform_commission_rate' => $request->freelancer_commission_rate, // Use freelancer rate as platform rate for backward compatibility
            'freelancer_commission_rate' => $request->freelancer_commission_rate,
            'referral_commission_rate' => $request->referral_commission_rate ?? 10.00,
            'payment_type' => $request->payment_type,
            'is_active' => true,
        ]);
        return redirect()->route('vendor.wallet_settings')->with('success', 'Wallet setting created successfully.');
    }

    public function edit($id)
    {
        $setting = WalletSetting::findOrFail($id);
        return view('vendor.wallet_settings.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = WalletSetting::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'setting_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'min_withdrawal_threshold' => 'required|numeric|min:0',
            'max_withdrawal_limit' => 'nullable|numeric|min:0',
            'freelancer_commission_rate' => 'required|numeric|min:0|max:100',
            'referral_commission_rate' => 'required|numeric|min:0|max:100',
            'payment_type' => 'required|in:manual,razorpay',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $setting->update([
            'setting_name' => $request->setting_name,
            'description' => $request->description,
            'min_withdrawal_threshold' => $request->min_withdrawal_threshold,
            'max_withdrawal_limit' => $request->max_withdrawal_limit,
            'platform_commission_rate' => $request->freelancer_commission_rate, // Use freelancer rate as platform rate for backward compatibility
            'freelancer_commission_rate' => $request->freelancer_commission_rate,
            'referral_commission_rate' => $request->referral_commission_rate ?? 10.00,
            'payment_type' => $request->payment_type,
        ]);
        return redirect()->route('vendor.wallet_settings')->with('success', 'Wallet setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = WalletSetting::findOrFail($id);
        if ($setting->setting_key === 'default') {
            return redirect()->back()->with('error', 'Cannot delete default setting');
        }
        $setting->delete();
        return redirect()->route('vendor.wallet_settings')->with('success', 'Wallet setting deleted successfully.');
    }

    public function toggleActive($id)
    {
        $setting = WalletSetting::findOrFail($id);
        $setting->is_active = !$setting->is_active;
        $setting->save();
        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}

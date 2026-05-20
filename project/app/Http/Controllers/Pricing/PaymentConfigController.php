<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\Api\CryptoJsAes;
use App\Http\Controllers\AppBaseController;
use App\Models\Pricing\PaymentConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Auth;

class PaymentConfigController extends AppBaseController
{

    protected array $paymentType = ["caricature", "template", "video", "ai_credit", "business_support", "subscription", "offer_sub"];

    public function index(): Factory|View|Application
    {
        $configurations = PaymentConfiguration::all()->keyBy('gateway');
        // 🔐 Decrypt + Mask credentials
        foreach ($configurations as $config) {

            $credentials = $config->credentials;

            foreach ($credentials as $key => $value) {
                try {
                    $decrypted = CryptoJsAes::decrypt($value);

                    $credentials[$key] =
                        str_repeat('*', max(0, strlen($decrypted) - 4))
                        . substr($decrypted, -4);

                } catch (\Exception $e) {
                    $credentials[$key] = $value;
                }
            }

            $config->credentials = $credentials;
        }

        $nationalGateways = $configurations->where('payment_scope', 'NATIONAL');
        $internationalGateways = $configurations->where('payment_scope', 'INTERNATIONAL');

        $nationalPaymentTypes = $nationalGateways
            ->pluck('payment_types')
            ->flatten()->toArray();

        $internationalPaymentTypes = $internationalGateways
            ->pluck('payment_types')
            ->flatten()->toArray();


        $nationalPaymentPendingConfig = array_diff($this->paymentType, $nationalPaymentTypes);
        $internationalPaymentPendingConfig = array_diff($this->paymentType, $internationalPaymentTypes);

        return view('pricing.payment_configuration.index', [
            'nationalGateways' => $nationalGateways,
            'internationalGateways' => $internationalGateways,
            'configurations' => $configurations,
            "paymentType" => $this->paymentType,
            "nationalPaymentPendingConfig" => $nationalPaymentPendingConfig,
            "internationalPaymentPendingConfig" => $internationalPaymentPendingConfig
        ]);
}

    public function addNewGateway(Request $request): JsonResponse
    {
        try {
            if (!(Auth::user()->user_type == 1 && Auth::user()->admin_type == 1)) {
                return response()->json([
                    'success' => false,
                    'message' => "Permission Denied"
                ], 422);
            }
            $request->validate([
                'gateway' => 'required|string|unique:payment_configurations,gateway',
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
                'credential_keys' => 'required|array',
                'credential_values' => 'required|array',
                'payment_types' => 'nullable|array',
                'payment_types.*' => [
                    Rule::in($this->paymentType),
                ],
            ]);

            $paymentTypes = $request->input('payment_types', []);

            // Prepare credentials from key-value pairs
            $credentials = [];
            $keys = $request->credential_keys;
            $values = $request->credential_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty(trim($keys[$i])) && !empty(trim($values[$i]))) {
                    $credentials[trim($keys[$i])] = CryptoJsAes::encrypt(trim($values[$i]));
                }
            }

            if (empty($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one credential field is required'
                ], 422);
            }

            // Check if any selected payment type is already assigned to another gateway in the same scope
            $existingGateways = PaymentConfiguration::where('payment_scope', strtoupper($request->scope))
                ->get();

            foreach ($existingGateways as $existingGateway) {
                // Properly decode payment_types if it's a string
                $existingTypes = $existingGateway->payment_types;
                if (is_string($existingTypes)) {
                    $existingTypes = json_decode($existingTypes, true) ?? [];
                } elseif (!is_array($existingTypes)) {
                    $existingTypes = [];
                }

                $conflictingTypes = array_intersect($paymentTypes, $existingTypes);

                if (!empty($conflictingTypes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment type(s) ' . implode(', ', $conflictingTypes) . ' already assigned to ' . $existingGateway->gateway
                    ], 422);
                }
            }

            // Create new gateway configuration (inactive by default)
            $config = PaymentConfiguration::create([
                'gateway' => Str::slug($request->gateway, '_'),
                'payment_scope' => strtoupper($request->scope),
                'credentials' => $credentials, // Don't json_encode - model will handle it
                'payment_types' => $paymentTypes, // Don't json_encode - model will handle it
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New gateway added successfully',
                'gateway' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateGateway(Request $request, $id): JsonResponse
    {
        try {
            if (!(Auth::user()->user_type == 1 && Auth::user()->admin_type == 1)) {
                return response()->json([
                    'success' => false,
                    'message' => "Permission Denied"
                ], 422);
            }
            $config = PaymentConfiguration::findOrFail($id);

            $request->validate([
                'gateway' => ['required', 'string'],
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
                'credential_keys' => 'required|array',
                'credential_values' => 'required|array',
                'payment_types' => 'nullable|array',
                'payment_types.*' => [Rule::in($this->paymentType)],
            ]);

            $paymentTypes = $request->input('payment_types', []);

            $credentials = [];
            $keys = $request->credential_keys;
            $values = $request->credential_values;

            foreach ($keys as $index => $key) {
                if (!empty(trim($key)) && !empty(trim($values[$index] ?? ''))) {
                    $credentials[trim($key)] = CryptoJsAes::encrypt(trim($values[$index]));
                }
            }

            if (empty($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one credential field is required'
                ], 422);
            }

            $existingGateways = PaymentConfiguration::where('payment_scope', strtoupper($request->scope))
                ->where('id', '!=', $id)
                ->get();

            foreach ($existingGateways as $existingGateway) {

                $existingTypes = $existingGateway->payment_types ?? [];

                if (is_string($existingTypes)) {
                    $existingTypes = json_decode($existingTypes, true) ?? [];
                }

                $conflictingTypes = array_intersect($paymentTypes, $existingTypes);

                if (!empty($conflictingTypes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment type(s) ' .
                            implode(', ', $conflictingTypes) .
                            ' already assigned to ' .
                            $existingGateway->gateway
                    ], 422);
                }
            }

            $config->update([
                'gateway' => Str::slug($request->gateway, '_'),
                'payment_scope' => strtoupper($request->scope),
                'credentials' => $credentials,
                'payment_types' => $paymentTypes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gateway credentials updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getGateway($id): JsonResponse
    {
        try {
            if (!(Auth::user()->user_type == 1 && Auth::user()->admin_type == 1)) {
                return response()->json([
                    'success' => false,
                    'message' => "Permission Denied"
                ], 422);
            }
            $config = PaymentConfiguration::findOrFail($id);

            return response()->json([
                'success' => true,
                'gateway' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

}

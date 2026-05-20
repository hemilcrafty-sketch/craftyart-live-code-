<?php

namespace App\Services;

use App\Models\Pricing\PaymentConfiguration;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhonePe\Env;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Razorpay\Api\Api;
use Stripe\StripeClient;

class PaymentGateway
{

    public string $name;
    public Api|StripeClient|StandardCheckoutClient $client;
    public array $credentials;

    public function __construct(string $name, Api|StripeClient|StandardCheckoutClient $client, array $credentials)
    {
        $this->name = $name;
        $this->client = $client;
        $this->credentials = $credentials;
    }

    public function getAccessToken(): string
    {
        // Check cache first
        $cachedToken = Cache::get('phonepe_access_token');
        if ($cachedToken) return $cachedToken;

        // Generate new token
        return $this->generateNewToken();
    }

    protected function generateNewToken(): string
    {
        if ($this->name !== 'phonepe_pg') return '';

        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        try {
            $response = Http::asForm()->post($url, [
                'client_id' => $this->credentials['client_id'],
                'client_secret' => $this->credentials['client_secret'],
                'client_version' => $this->credentials['client_version'],
                'grant_type' => 'client_credentials',
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                throw new Exception('PhonePe OAuth failed: ' . json_encode($data));
            }

            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;

            Cache::put('phonepe_access_token', $accessToken, ($expiresIn - 300));

            return $accessToken;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getOrderStatus($merchantOrderId): array|string
    {
        if ($this->name !== 'phonepe_pg') return '';

        try {
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/order/$merchantOrderId/status?details=true";
            $response = Http::timeout(60)->withHeaders([
                "Authorization" => "O-Bearer {$this->getAccessToken()}",
                "Accept" => "application/json"
            ])->get($url);

            if (!$response->successful()) {
                return '';
            }

            return $response->json();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function initByGateway(string $gateway, ?string $scope): ?PaymentGateway
    {
        $paymentGateway = PaymentConfiguration::getCredentialsByName(null, $scope, $gateway);
        if ($paymentGateway) {
            return self::buildGateway($paymentGateway->gateway, $paymentGateway->credentials);
        }
        return null;
    }

    public static function initByType(string $type, string $scope): ?PaymentGateway
    {
        $paymentGateway = PaymentConfiguration::getCredentialsByScope($type, $scope);
        if ($paymentGateway) {
            return self::buildGateway($paymentGateway->gateway, $paymentGateway->credentials);
        }
        return null;
    }

    private static function buildGateway(string $gateway, array $credentials): ?PaymentGateway
    {
        try {

            if ($gateway === 'razorpay') {

                $razorpayCredentials = [
                    "key_id" => $credentials['key_id'],
                    "secret_key" => $credentials['secret_key'],
                ];

                $razorpay = new Api($credentials['key_id'], $credentials['secret_key']);

                return new PaymentGateway(name: $gateway, client: $razorpay, credentials: $razorpayCredentials);
            }

            if ($gateway === 'phonepe_pg') {

                $phonePeCredentials = [
                    "client_id" => $credentials['client_id'],
                    "client_secret" => $credentials['client_secret'],
                    "client_version" => $credentials['client_version'],
                    "merchant_user_id" => $credentials['merchant_user_id'],
                    "webhook_username" => $credentials['webhook_username'],
                    "webhook_password" => $credentials['webhook_password'],
                ];
                $phonePePaymentsClient = StandardCheckoutClient::getInstance(
                    $credentials['client_id'],
                    $credentials['client_version'],
                    $credentials['client_secret'],
                    Env::PRODUCTION
                );

                return new PaymentGateway(name: $gateway, client: $phonePePaymentsClient, credentials: $phonePeCredentials);
            }

            if ($gateway === 'stripe') {

                $stripeCredentials = [
                    "publishable_key" => $credentials['publishable_key'],
                    "secret_key" => $credentials['secret_key'],
                ];
                $stripeClient = new StripeClient($credentials['secret_key']);
                return new PaymentGateway(name: $gateway, client: $stripeClient, credentials: $stripeCredentials);
            }

        } catch (Exception $e) {
            return null;
        }

        return null;
    }

}

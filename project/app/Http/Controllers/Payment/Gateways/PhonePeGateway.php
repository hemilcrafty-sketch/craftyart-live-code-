<?php

namespace App\Http\Controllers\Payment\Gateways;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Jobs\AutoPayJob;
use App\Jobs\PreDebitNotificationJob;
use App\Models\Order;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\SubPlan;
use App\Models\Revenue\AutoPayTransaction;
use App\Models\Revenue\UserSubscriptions;
use App\Models\Subscription;
use App\Services\PaymentGateway;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;

class PhonePeGateway extends ApiController
{

    public function checkOrderStatus(Request $request): array|string
    {
        try {
            $merchantOrderId = $request->input('order_id');
            $type = $request->input('type');

            if (empty($merchantOrderId)) {
                return $this->failed(
                    statusCode: 400,
                    msg: 'Invalid merchant order ID',
                );
            }

            $order = Order::whereOrderId($merchantOrderId)->first();
            if (!$order) return $this->failed(msg: 'Order not found');

            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
            if (!$paymentGateway) return $this->failed(msg: 'Phonepe init failed');

            $token = $paymentGateway->getAccessToken();

            if ($order->status !== 'paid') {
                $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/order/$merchantOrderId/status?details=true";

                $response = Http::timeout(60)->withHeaders([
                    "Authorization" => "O-Bearer $token",
                    "Accept" => "application/json"
                ])->get($url);

                if (!$response->successful()) {
                    return $this->failed(
                        statusCode: $response->status(),
                        msg: 'Failed to fetch order status',
                        datas: [
                            'error' => $data['message'] ?? 'Unknown error',
                            'error_code' => $data['code'] ?? null
                        ]
                    );
                }

                $data = $response->json();

                // Parse status from response
                $orderState = $data['state'] ?? 'PENDING';
                $isPending = $orderState === 'PENDING';
                $isPaid = $orderState === 'COMPLETED';
                $isFailed = $orderState === 'FAILED';

                if ($isPending) $order->status = 'processing';
                if ($isPaid) $order->status = 'paid';
                if ($isFailed) $order->status = 'failed';

                $order->save();

                if ($isFailed) return $this->failed(msg: 'Failed', datas: ['status' => $orderState]);
                if ($isPending) return $this->successed(msg: 'Waiting for user approval', datas: ['status' => $orderState]);
            }

            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/$order->subscription_id/status?details=true";

            $response = Http::timeout(60)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Accept" => "application/json"
            ])->get($url);

            if (!$response->successful()) {
                return $this->failed(
                    statusCode: $response->status(),
                    msg: 'Failed to fetch sub status',
                    datas: [
                        'error' => $data['message'] ?? 'Unknown error',
                        'error_code' => $data['code'] ?? null,
                        '$url' => $url
                    ]
                );
            }

            $data = $response->json();

            $subscriptionState = $data['state'] ?? 'ACTIVATION_IN_PROGRESS';
            $isActive = $subscriptionState === 'ACTIVE';
            $isPending = $subscriptionState === 'ACTIVATION_IN_PROGRESS';
            $isFailed = $subscriptionState === 'FAILED';

            $responseData = [
                'status' => $subscriptionState,
                'is_active' => $isActive,
                'is_pending' => $isPending,
                'is_failed' => $isFailed,
                'amount' => isset($data['amount']) ? $data['amount'] / 100 : null,
                'currency' => $data['currency'] ?? 'INR'
            ];

            $message = match ($subscriptionState) {
                'ACTIVE' => 'Subscription is active and ready to use',
                'PENDING', 'ACTIVATION_IN_PROGRESS' => 'Waiting for user approval',
                'FAILED', 'EXPIRED', 'DECLINED', 'REJECTED' => 'Subscription setup failed',
                default => 'Subscription status: ' . $subscriptionState
            };

            return $this->successed(msg: $message, datas: $responseData);

        } catch (Exception $e) {
            return $this->failed(
                statusCode: 500,
                msg: 'Error checking order status',
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    public function preDebitNotification(Request $request): array|string
    {
        $request->validate([
            'merchant_subscription_id' => 'required|string',
            'amount' => 'nullable|numeric|min:1',
            'auto_debit' => 'nullable|boolean'
        ]);

        try {
            $merchantSubscriptionId = $request->input('merchant_subscription_id');
            $amount = $request->input('amount');
            $autoDebit = $request->input('auto_debit', false); // false = user must approve

            $response = self::sendPreDebitNotification($merchantSubscriptionId, $amount, $autoDebit);

            return $this->sendResponse($response);

        } catch (Exception $e) {
            return $this->failed(
                statusCode: 500,
                msg: 'Error sending notification',
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    public static function sendPreDebitNotification($merchantSubscriptionId, $amount, $autoDebit = true): array
    {
        $callback = function (int $statusCode, bool $success, string $msg, array $datas = []) {
            $response['statusCode'] = $statusCode;
            $response['success'] = $success;
            $response['msg'] = $msg;

            foreach ($datas as $key => $value) {
                $response[$key] = $value;
            }

            return $response;
        };

        try {
            $userSubData = UserSubscriptions::whereGatewaySubscriptionId($merchantSubscriptionId)->first();
            if (!$userSubData) return $callback(statusCode: 502, success: false, msg: 'User subscription not found');

            $refOrder = Order::whereSubscriptionId($merchantSubscriptionId)->first();
            if (!$refOrder) return $callback(statusCode: 502, success: false, msg: 'Order not found');

            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
            if (!$paymentGateway) return $callback(statusCode: 502, success: false, msg: 'Phonepe init failed');

            $token = $paymentGateway->getAccessToken();

            // Check if subscription is active
            $statusUrl = "https://api.phonepe.com/apis/pg/subscriptions/v2/$merchantSubscriptionId/status";

            $statusResponse = Http::timeout(60)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Accept" => "application/json"
            ])->get($statusUrl);

            $statusData = $statusResponse->json();

            if (($statusData['state'] ?? '') !== 'ACTIVE') {
                return $callback(
                    statusCode: 502,
                    success: false,
                    msg: 'Subscription is not active',
                    datas: [
                        'error' => 'Cannot send notification for inactive subscription',
                        'current_status' => $statusData['state'] ?? 'UNKNOWN'
                    ]
                );
            }

            // Use subscription amount if not provided
            if (!$amount) {
                $amount = isset($statusData['recurringAmount']) ? $statusData['recurringAmount'] / 100 : null;
                if (!$amount) {
                    return $callback(
                        statusCode: 502,
                        success: false,
                        msg: 'Amount is required',
                        datas: [
                            'error' => 'Please provide amount',
                        ]
                    );
                }
            }

            // Create notification order
            $merchantOrderId = "order_" . uniqid() . time();

            // Calculate expireAt: 48 hours from now (PhonePe max window)
            $expireAt = now()->addHours(48)->timestamp * 1000;

            // Send redemption notification (this notifies user about upcoming debit)
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => $amount * 100,
                "expireAt" => $expireAt,
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_REDEMPTION",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "redemptionRetryStrategy" => "STANDARD",
                    "autoDebit" => $autoDebit // false = user gets notification and must approve
                ]
            ];

            $url = 'https://api.phonepe.com/apis/pg/subscriptions/v2/notify';

            $response = Http::timeout(60)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);

            $data = $response->json();

            if (!$response->successful()) {
                return $callback(
                    statusCode: $response->status(),
                    success: false,
                    msg: 'Failed to send notification',
                    datas: [
                        'error' => $data['message'] ?? 'Unknown error',
                        'error_code' => $data['code'] ?? null
                    ]
                );
            }

            $orderData = [
                'emp_id' => $refOrder->emp_id,
                'user_id' => $refOrder->user_id,
                'plan_id' => $refOrder->plan_id,
                'crafty_id' => Order::generateCraftyId(),
                'contact_no' => $refOrder->contact_no,
                'order_id' => $merchantOrderId,
                'subscription_id' => $merchantSubscriptionId,
                'gateway' => $paymentGateway->name,
                'status' => 'pending',
                'currency' => 'INR',
                'amount' => $amount,
                'paid' => 0,
                'type' => $refOrder->type,
                'has_offer' => 0,
                'raw_notes' => json_encode($refOrder->raw_notes),
                'show_data' => 0,
                'url' => "https://www.craftyartapp.com",
                'fbc' => $refOrder->fbc,
                'fbp' => $refOrder->fbp,
                'gclid' => $refOrder->gclid,
                'wbraid' => $refOrder->wbraid,
                'gbraid' => $refOrder->gbraid,
                'gcl_au' => $refOrder->gcl_au,
                'ga' => $refOrder->ga,
                'userAgent' => $refOrder->userAgent,
                'ip_address' => $refOrder->ip_address,
            ];

            Order::create($orderData);

            AutoPayTransaction::create([
                "user_id" => $userSubData->user_id,
                "order_id" => $merchantOrderId,
                "subscription_id" => $merchantSubscriptionId,
                "amount" => $amount,
                "currency" => "INR",
                "transaction_type" => "manual",
                "status" => "pending",
                "payment_status" => $data['state'] ?? 'NOTIFICATION_IN_PROGRESS',
                "is_autopay" => true,
                "notification_time" => Carbon::now(),
            ]);

            AutoPayJob::dispatch($merchantOrderId, $merchantSubscriptionId, $amount)->delay(now()->addDays());

            $responseData = [
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => $data['orderId'] ?? null,
                'amount' => $amount,
                'status' => $data['state'] ?? 'NOTIFICATION_IN_PROGRESS',
                'auto_debit' => $autoDebit,
                'notification_type' => $autoDebit ? 'AUTO_DEBIT' : 'USER_APPROVAL_REQUIRED',
                'message' => $autoDebit
                    ? 'User will be notified and charged automatically'
                    : 'User will be notified and must approve the payment'
            ];

            return $callback(
                statusCode: 200,
                success: true,
                msg: 'Redemption notification sent successfully',
                datas: $responseData
            );

        } catch (Exception $e) {
            return $callback(
                statusCode: 500,
                success: false,
                msg: 'Error sending notification',
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    public static function triggerManualDebit($merchantOrderId, $merchantSubscriptionId, $amount): array
    {
        $callback = function (int $statusCode, bool $success, string $msg, array $datas = [], bool $notifyAgain = false) use ($merchantOrderId, $merchantSubscriptionId, $amount) {

            if (!$success) {
                Order::whereOrderId($merchantOrderId)->update(['status' => 'failed']);
                AutoPayTransaction::whereOrderId($merchantOrderId)->update(['status' => 'failed', 'payment_status' => 'FAILED']);
                if ($notifyAgain) PreDebitNotificationJob::dispatch($merchantSubscriptionId, $amount, true)->delay(now()->addDays(2));
            }

            $response['statusCode'] = $statusCode;
            $response['success'] = $success;
            $response['msg'] = $msg;

            foreach ($datas as $key => $value) {
                $response[$key] = $value;
            }

            return $response;
        };

        try {

            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
            if (!$paymentGateway) {
                return $callback(statusCode: 502, success: false, msg: 'Phonepe init failed');
            }

            $token = $paymentGateway->getAccessToken();
            // Check if subscription is active
            $statusUrl = "https://api.phonepe.com/apis/pg/subscriptions/v2/$merchantSubscriptionId/status";

            $statusResponse = Http::timeout(30)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Accept" => "application/json"
            ])->get($statusUrl);

            $statusData = $statusResponse->json();

            if (($statusData['state'] ?? '') !== 'ACTIVE') {
                return $callback(
                    statusCode: 502,
                    success: false,
                    msg: 'Subscription is not active',
                    datas: [
                        'error' => 'Cannot trigger debit on inactive subscription',
                        'current_status' => $statusData['state'] ?? 'UNKNOWN'
                    ]
                );
            }

            // Use subscription amount if not provided
            if (!$amount) {
                $amount = isset($statusData['recurringAmount']) ? $statusData['recurringAmount'] / 100 : null;
                if (!$amount) {
                    return $callback(
                        statusCode: 502,
                        success: false,
                        msg: 'Amount is required',
                        datas: ['error' => 'Please provide amount'],
                        notifyAgain: true
                    );
                }
            }

            $payload = ["merchantOrderId" => $merchantOrderId];

            $url = 'https://api.phonepe.com/apis/pg/subscriptions/v2/redeem';

            $response = Http::timeout(30)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);

            $data = $response->json();

            if (!$response->successful() || $data['state'] == 'FAILED') {
                return $callback(
                    statusCode: $response->status(),
                    success: false,
                    msg: 'Failed to trigger debit',
                    datas: [
                        'error' => $data['message'] ?? 'Unknown error',
                        'error_code' => $data['code'] ?? null
                    ],
                    notifyAgain: true
                );
            }

//            $order = Order::whereOrderId($merchantOrderId)->first();
//            if ($order) {
//                $order->status = 'paid';
//                $order->payment_id = $data['transactionId'];
//                $order->paid = $amount;
//                $order->save();
//                PaymentController::removeOrdersDuplicate($order);
//            }
//
//            AutoPayTransaction::whereOrderId($merchantOrderId)->update(['payment_status' => 'PAID', 'status' => 'paid', 'payment_time' => Carbon::now()]);

            $responseData = [
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => $data['orderId'] ?? null,
                'amount' => $amount,
                'status' => $data['state'] ?? 'PENDING'
            ];

            return $callback(
                statusCode: 200,
                success: true,
                msg: 'Manual debit triggered successfully',
                datas: $responseData
            );

        } catch (Exception $e) {
            return $callback(
                statusCode: 502,
                success: false,
                msg: 'Error triggering debit',
                datas: ['error' => $e->getMessage()],
                notifyAgain: true
            );
        }
    }

    public static function createPhonepe(
        PaymentGateway                         $paymentGateway,
                                               $isSubscription,
                                               $craftyId,
                                               $amount,
                                               $seats,
                                               $userAddOns,
        Subscription|OfferPackage|SubPlan|null $planData
    ): string|array|false
    {

        /** @var StandardCheckoutClient $phonePePaymentsClient */
        $phonePePaymentsClient = $paymentGateway->client;

        foreach ($userAddOns as $userAddOn) {
            $amount += $userAddOn['amount'];
        }

//        $amount = $amount * $seats;

        $maxAmount = $amount;

        if ($planData && !($planData instanceof Subscription)) {
            $maxAmount = $planData->plan_details["inr_offer_price"];
        }

        $maxAmount = max($maxAmount, $amount);

        try {
            if ($isSubscription && $maxAmount < 15000) {
                $merchantOrderId = "order_" . uniqid() . time();
                $merchantSubscriptionId = "sub_" . uniqid() . time();

                $expiryTime = now()->addMinutes(10);

                $payload = [
                    "merchantOrderId" => $merchantOrderId,
                    "amount" => $amount * 100,
                    "expireAt" => $expiryTime->timestamp * 1000,
                    "metaInfo" => [
                        "udf1" => $craftyId,
                    ],
                    "paymentFlow" => [
                        "type" => "SUBSCRIPTION_SETUP",
                        "merchantSubscriptionId" => $merchantSubscriptionId,
                        "authWorkflowType" => "TRANSACTION",
                        "amountType" => "VARIABLE",
                        "maxAmount" => $maxAmount * 100,
                        "frequency" => "ON_DEMAND",
                        "productType" => "UPI_MANDATE",
                        "paymentMode" => [
                            "type" => "UPI_INTENT",
                        ]
                    ]
                ];

                $url = 'https://api.phonepe.com/apis/pg/subscriptions/v2/setup';

                $response = Http::withHeaders([
                    "Authorization" => "O-Bearer {$paymentGateway->getAccessToken()}",
                    "Content-Type" => "application/json",
                    "Accept" => "application/json"
                ])->post($url, $payload);

                $data = $response->json();

                $intentUrl = $data['intentUrl'] ?? null;
                if (!$intentUrl) {
                    $data['maxAmount'] = $maxAmount;
                    return json_encode($data);
                }

                $qrExpireAt = null;

                $query = parse_url($intentUrl, PHP_URL_QUERY);
                parse_str($query, $params);

                if (!empty($params['QRexpire'])) {
                    $raw = $params['QRexpire'];
                    $raw = str_replace(' ', '+', $raw);
                    $qrExpireAt = Carbon::parse($raw);
                }

                $androidUpiIntentUrl = "intent://" . str_replace("upi://", "", $intentUrl);
                $iosUpiIntentUrl = "intent://" . str_replace("upi://mandate?", "", $intentUrl);

                return [
                    'id' => $merchantOrderId,
                    'subscription_id' => $merchantSubscriptionId,
                    'data' => [
                        'subscription' => true,
                        'order_id' => $merchantOrderId,
                        'subscription_id' => $merchantSubscriptionId,
                        'state' => $data['state'] ?? 'PENDING',
                        'amount' => $amount,
                        'currency' => 'INR',
                        'qr_expires_at' => $qrExpireAt?->toIso8601String(),
                        'expires_in_minutes' => 10,
                        'qr_code' => [
                            'intent_url' => $intentUrl,
                            'instructions' => [
                                'en' => 'Scan this QR code with any UPI app to set up AutoPay mandate',
                                'hi' => 'AutoPay mandate सेट करने के लिए किसी भी UPI ऐप से इस QR कोड को स्कैन करें',
                                'gu' => 'AutoPay mandate સેટ કરવા માટે કોઈપણ UPI એપ્લિકેશન વડે આ QR કોડ સ્કેન કરો'
                            ],
                            'upi_apps' => [
                                'ios' => [
                                    [
                                        'name' => 'PhonePe',
                                        'icon' => 'https://media.craftyartapp.com/icons/phonepe.png',
                                        'intent_url' => "ppe://mandate?$iosUpiIntentUrl"
                                    ],
                                    [
                                        'name' => 'Paytm',
                                        'icon' => 'https://media.craftyartapp.com/icons/paytm.png',
                                        'intent_url' => "paytmmp://upi/mandate?$iosUpiIntentUrl"
                                    ],
                                    [
                                        'name' => 'GPay',
                                        'icon' => 'https://media.craftyartapp.com/icons/gpay.png',
                                        'intent_url' => "gpay://upi/mandate?$iosUpiIntentUrl"
                                    ],
                                    [
                                        'name' => 'Any',
                                        'icon' => 'https://media.craftyartapp.com/icons/upi.png',
                                        'intent_url' => $intentUrl,
                                    ]
                                ],
                                'android' => [
                                    [
                                        'name' => 'PhonePe',
                                        'icon' => 'https://media.craftyartapp.com/icons/phonepe.png',
                                        'intent_url' => $androidUpiIntentUrl . "#Intent;scheme=upi;package=" . "com.phonepe.app" . ";end;",
                                    ],
                                    [
                                        'name' => 'Paytm',
                                        'icon' => 'https://media.craftyartapp.com/icons/paytm.png',
                                        'intent_url' => $androidUpiIntentUrl . "#Intent;scheme=upi;package=" . "net.one97.paytm" . ";end;",
                                    ],
                                    [
                                        'name' => 'GPay',
                                        'icon' => 'https://media.craftyartapp.com/icons/gpay.png',
                                        'intent_url' => $androidUpiIntentUrl . "#Intent;scheme=upi;package=" . "com.google.android.apps.nbu.paisa.user" . ";end;",
                                    ],
                                    [
                                        'name' => 'Any',
                                        'icon' => 'https://media.craftyartapp.com/icons/upi.png',
                                        'intent_url' => $intentUrl,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            } else {
                $phonePeRequest = StandardCheckoutPayRequestBuilder::builder()
                    ->merchantOrderId($craftyId)
                    ->amount($amount * 100)
                    ->redirectUrl('https://craftyartapp.com')
                    ->message("Phone Pe Payment Integration")
                    ->udf1($craftyId)
                    ->build();
                $response = $phonePePaymentsClient->pay($phonePeRequest);
                $response->transactionId = $craftyId;

                return ['id' => $response->getOrderId(), 'data' => $response];
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function cancelSubscription(?PaymentGateway $paymentGateway, $subId): ?string
    {

        if (!$paymentGateway) {
            $paymentGateway = PaymentGateway::initByGateway("phonepe_pg", null);
        }

        if (!$paymentGateway) {
            return "Payment gateway not found";
        }

        try {
            $token = $paymentGateway->getAccessToken();
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/$subId/cancel";
            $response = Http::timeout(60)->withHeaders([
                "Authorization" => "O-Bearer $token",
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url);

            if ($response->status() == 204) return null;

        } catch (\Exception $e) {
            return "Something went wrong";
        }

        return "Something went wrong";
    }
}

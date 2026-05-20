<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Order;
use App\Models\Revenue\AutoPayTransaction;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\UserSubscriptions;
use App\Models\TransactionLog;
use App\Models\WebFbSelling;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhonePeWebhookController extends ApiController
{
    public function handleWebhook(Request $request): JsonResponse
    {
        $data = null;
        try {
            $rawBody = $request->getContent();

            $data = json_decode($rawBody, true);

            if (!isset($data['event'])) return response()->json(['success' => false]);

            $event = $data['event'];
            $payload = $data['payload'];

            $merchantOrderId = $payload['merchantOrderId'] ?? null;
            $merchantSubscriptionId = $payload['merchantSubscriptionId'] ?? null;
            $orderId = $payload['orderId'] ?? null;
            $amount = $payload['amount'] ?? 0;

            if ($event === 'checkout.order.completed' || $event === 'subscription.redemption.transaction.completed' || $event === 'subscription.redemption.order.completed') {
                $order = Order::where('order_id', $orderId)->orWhere('crafty_id', $merchantOrderId)->orWhere('subscription_id', $merchantSubscriptionId)->first();
                if ($order) {
                    $order->status = 'paid';
                    $order->payment_id = $merchantOrderId;
                    $order->amount = $amount / 100;
                    $order->paid = $amount / 100;
                    $order->save();
                    PaymentController::removeOrdersDuplicate($order);
                }

                if ($event === 'subscription.redemption.transaction.completed' || $event === 'subscription.redemption.order.completed') {
//                    $query = AutoPayTransaction::query();
//
//                    if ($merchantOrderId) {
//                        $query->where('order_id', $merchantOrderId);
//                    }
//
//                    if ($merchantSubscriptionId) {
//                        $query->orWhere('subscription_id', $merchantSubscriptionId);
//                    }
//
//                    $query->update([
//                        'payment_status' => 'PAID',
//                        'status' => 'paid',
//                        'payment_time' => Carbon::now()
//                    ]);

                    if ($merchantOrderId) {
                        AutoPayTransaction::whereOrderId($merchantOrderId)->update(['payment_status' => 'PAID', 'status' => 'paid', 'payment_time' => Carbon::now()]);
                    }
                }

                (new PaymentController($request))->enterTransData(
                    request: $request,
                    transaction_id: $merchantOrderId,
                    method: 'phonepe_pg',
                    currency_code: "INR",
                    isManual: 0);
            }

            if ($event === 'checkout.order.failed') {
                $order = Order::where('order_id', $orderId)->orWhere('crafty_id', $merchantOrderId)->first();
                if ($order) {
                    $order->status = 'failed';
                    $order->save();
                    EmailController::sendPurchaseDropoutEmail($order);
                }
            }

            if ($event === 'subscription.revoked' || $event === 'subscription.cancelled') {
                if (!empty($merchantSubscriptionId)) {
                    TransactionLog::whereSubscriptionId($merchantSubscriptionId)->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
                    MasterPurchaseHistory::whereSubscriptionId($merchantSubscriptionId)->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
                    UserSubscriptions::whereGatewaySubscriptionId($merchantSubscriptionId)->update(['status' => 'cancelled']);
                }
            }

            WebFbSelling::create([
                'user_id' => $event,
                'product_id' => json_encode($data),
                'amount' => $merchantOrderId,
                'ip_address' => "phonePe",
                'country' => $merchantSubscriptionId,
                'fbc' => "d",
                'fbp' => "d",
                'gclid' => "d",
                'gcl_au' => "d",
                'ga' => "d",
                'userAgent' => "d",
            ]);


            return response()->json(['success' => true]);
        } catch (\Exception $e) {

            WebFbSelling::create([
                'user_id' => 'failed',
                'product_id' => json_encode($data),
                'amount' => $e->getMessage(),
                'ip_address' => "phonePe",
                'country' => "d",
                'fbc' => "d",
                'fbp' => "d",
                'gclid' => "d",
                'gcl_au' => "d",
                'ga' => "d",
                'userAgent' => "d",
            ]);

            return response()->json(['error' => $e->getMessage()]);
        }
    }

}

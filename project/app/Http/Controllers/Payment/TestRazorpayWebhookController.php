<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Order;
use App\Models\WebFbSelling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestRazorpayWebhookController extends ApiController
{
    public function handleWebhook(Request $request): JsonResponse
    {
        $webhookSecret = "bbyaNDXugg6SqjfuQo6cgdqi9EmMaTudFhf6hWT9";

        $signature = $request->header('X-Razorpay-Signature') ?? "";
        $payload = $request->getContent() ?? "";

        if (!$this->verifySignature($payload, $signature, $webhookSecret)) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $event = $request->event ?? null;
        $payloadData = $request->payload ?? []; //plan_RbxL9SiaJnw9j5

        WebFbSelling::create([
            'user_id' => $event,
            'product_id' => json_encode($payloadData),
            'amount' => "test",
            'ip_address' => "d",
            'country' => "d",
            'fbc' => "d",
            'fbp' => "d",
            'gclid' => "d",
            'gcl_au' => "d",
            'ga' => "d",
            'userAgent' => "d",
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature($payload, $signature, $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    private function handleOrderPaid($payload): void
    {
        $orderEntity = $payload['order']['entity'];
        $order = Order::where('razorpay_order_id', $orderEntity['id'])->first();
        if ($order) {
            $order->status = 'paid';
            $order->save();
            PaymentController::removeOrdersDuplicate($order);
        }
    }

    private function handlePaymentLinkPaid(Request $request, $payload): void
    {

        WebFbSelling::create([
            'user_id' => '',
            'product_id' => '',
            'amount' => '',
            'ip_address' => '',
            'country' => '',
            'fbc' => '$fbclid',
            'fbp' => '$fbp',
            'gclid' => '$gclid',
            'gcl_au' => '$gclAu',
            'ga' => '$gaClientId',
            'userAgent' => $payload,
        ]);

        $payment = $payload['payment_link']['entity'];

//        $orderEntity = $payload['order']['entity'];
//        $order = Order::where('razorpay_order_id', $orderEntity['id'])->first();
//        if ($order) {
//            $order->status = 'paid';
//            $order->save();
//        }

        PaymentController::enterTransData(
            request: $request,
            transaction_id: $payment['id'],
            method: 'Razorpay',
            currency_code: "INR",
            isManual: 0);
    }

    private function handlePaymentAuthorized($payload): void
    {
        $payment = $payload['payment']['entity'];
        $order = Order::where('razorpay_order_id', $payment['order_id'])->first();
        if ($order && !in_array($order->status, ["paid", "success"], true)) {
            $order->status = 'processing';
            $order->save();
        }
    }

    private function handlePaymentSuccess(Request $request, $payload): void
    {
        $payment = $payload['payment']['entity'];

        $order = Order::where('razorpay_order_id', $payment['order_id'])->first();
        if ($order) {
            if ($order->status !== 'paid') $order->status = 'success';
            $order->razorpay_payment_id = $payment['id'];
            $order->amount = $payment['amount'] / 100;
            $order->paid = $payment['amount'] / 100;
            $order->save();
        }

        PaymentController::enterTransData(
            request: $request,
            transaction_id: $payment['id'],
            method: 'Razorpay',
            currency_code: "INR",
            isManual: 0);
    }

    private function handlePaymentFailed($payload): void
    {
        $payment = $payload['payment']['entity'];
        $order = Order::where('razorpay_order_id', $payment['order_id'])->first();
        if ($order && !in_array($order->status, ["paid", "success"], true)) {
            $order->status = 'failed';
            $order->save();
            EmailController::sendPurchaseDropoutEmail($order);
        }
    }

    private function handleRefundCreated($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refund_initiated';
            $order->save();
        }
    }

    private function handleRefundProcessed($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refunded';
            $order->save();
        }
    }

    private function handleRefundFailed($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refund_failed';
            $order->save();
        }
    }
}

<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Order;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\Sale;
use App\Models\Revenue\UserSubscriptions;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\WebFbSelling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazorpayWebhookController extends ApiController
{
    public function handleWebhook(Request $request): JsonResponse
    {
        $webhookSecret = "yaNDXu6SqjfuQo6cqi9EmMaTudF6hWT9";

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

        switch ($event) {
            case 'order.paid':         // ✅ Order paid
                $this->handleOrderPaid($request, $payloadData);
                break;

            case 'subscription.charged':
                $this->handleSubscriptionCharged($request, $payloadData);
                break;

            case 'subscription.paused':
                $this->handleSubscriptionStatus($payloadData, 'paused');
                break;

            case 'subscription.resumed':
                $this->handleSubscriptionStatus($payloadData, 'active');
                break;

            case 'subscription.halted':
                $this->handleSubscriptionStatus($payloadData, 'halted');
                break;

            case 'subscription.pending':
                $this->handleSubscriptionStatus($payloadData, 'pending');
                break;

            case 'subscription.cancelled':
                $this->handleSubscriptionCancelled($payloadData);
                break;

            case 'payment.authorized': // ⏳ Payment authorized
                $this->handlePaymentAuthorized($payloadData);
                break;

            case 'payment.captured':   // ✅ Payment success
                $this->handlePaymentSuccess($request, $payloadData);
                break;

            case 'payment_link.paid':   // ✅ Payment success
                $this->handlePaymentLinkPaid($request, $payloadData);
                break;

            case 'payment.failed':     // ❌ Payment failed
                $this->handlePaymentFailed($payloadData);
                break;

            case 'refund.created':     // 🟡 Refund initiated
                $this->handleRefundCreated($payloadData);
                break;

            case 'refund.processed':   // 🔄 Refund success
                $this->handleRefundProcessed($payloadData);
                break;

            case 'refund.failed':      // ❌ Refund failed
                $this->handleRefundFailed($payloadData);
                break;

            case 'payment.dispute.lost': // ❌ Refund failed
                $this->handleDisputeLost($payloadData);
                break;
        }

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature($payload, $signature, $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    private function handleOrderPaid(Request $request, $payload): void
    {
        $orderEntity = $payload['order']['entity'];
        $payment = $payload['payment']['entity'];

        $notes = $payment['notes'] ?? [];
        $craftyId = $notes['craftyId'] ?? null;
        $query = Order::where('order_id', $orderEntity['id']);
        if (!empty($craftyId)) $query->orWhere('crafty_id', $craftyId);
        $order = $query->first();

        if ($order) {
            $order->status = 'paid';
            $order->razorpay_payment_id = $payment['id'];
            if (empty($order->order_id)) $order->order_id = $orderEntity['id'];
            $order->payment_id = $payment['id'];
            $order->amount = $payment['amount'] / 100;
            $order->paid = $payment['amount'] / 100;
            $order->save();
            PaymentController::removeOrdersDuplicate($order);
        }


        (new PaymentController($request))->enterTransData(
            request: $request,
            transaction_id: $payment['id'],
            method: 'Razorpay',
            currency_code: "INR",
            isManual: 0);
    }

    private function handleSubscriptionCharged(Request $request, $payload): void
    {
        $subscription = $payload['subscription']['entity'];
        $payment = $payload['payment']['entity'];

        $notes = $subscription['notes'] ?? [];
        $craftyId = $notes['craftyId'] ?? null;

        $refOrder = Order::whereSubscriptionId($subscription['id'])->orWhere('crafty_id', $craftyId)->first();
        if (!$refOrder) return;

        $order = Order::whereOrderId($payment['order_id'])->first();
        if ($order) {
            $order->status = 'paid';
            $order->razorpay_payment_id = $payment['id'];
            $order->payment_id = $payment['id'];
            $order->amount = $payment['amount'] / 100;
            $order->paid = $payment['amount'] / 100;
            $order->save();
            PaymentController::removeOrdersDuplicate($order);
        } else {
            $orderData = [
                'emp_id' => Order::getOrderAssignEmpId($refOrder->user_id),
                'user_id' => $refOrder->user_id,
                'plan_id' => $refOrder->plan_id,
                'crafty_id' => Order::generateCraftyId(),
                'contact_no' => $refOrder->contact_no,
                'order_id' => $payment['order_id'],
                'subscription_id' => $subscription['id'],
                'razorpay_order_id' => $payment['order_id'],
                'razorpay_payment_id' => $payment['id'],
                'payment_id' => $payment['id'],
                'gateway' => 'razorpay',
                'status' => 'paid',
                'currency' => 'INR',
                'amount' => $payment['amount'] / 100,
                'paid' => $payment['amount'] / 100,
                'type' => $refOrder->type,
                'has_offer' => 0,
                'raw_notes' => json_encode($refOrder->raw_notes),
                'show_data' => 1,
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
        }

        (new PaymentController($request))->enterTransData(
            request: $request,
            transaction_id: $payment['id'],
            method: 'Razorpay',
            currency_code: "INR",
            isManual: 0);
    }

    private function handleSubscriptionStatus($payload, $status): void
    {
        $subscription = $payload['subscription']['entity'];
        TransactionLog::whereSubscriptionId($subscription['id'])->update(['subscription_status' => $status]);
        MasterPurchaseHistory::whereSubscriptionId($subscription['id'])->update(['subscription_status' => $status]);
        UserSubscriptions::whereGatewaySubscriptionId($subscription['id'])->update(['status' => $status]);
    }

    private function handleSubscriptionCancelled($payload): void
    {
        $subscription = $payload['subscription']['entity'];
        TransactionLog::whereSubscriptionId($subscription['id'])->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
        MasterPurchaseHistory::whereSubscriptionId($subscription['id'])->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
        UserSubscriptions::whereGatewaySubscriptionId($subscription['id'])->update(['status' => 'cancelled']);
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
//            $order->razorpay_payment_id = $payment['id'];
//            $order->amount = $payment['amount'] / 100;
//            $order->paid = $payment['amount'] / 100;
            $order->save();
        }

//        PaymentController::enterTransData(
//            request: $request,
//            transaction_id: $payment['id'],
//            method: 'Razorpay',
//            currency_code: "INR",
//            isManual: 0);
    }

    private function handlePaymentLinkPaid(Request $request, $payload): void
    {

        $paymentLink = $payload['payment_link']['entity'];
        $payment = $payload['payment']['entity'];

//        $orderEntity = $payload['order']['entity'];
//        $order = Order::where('razorpay_order_id', $orderEntity['id'])->first();
//        if ($order) {
//            $order->status = 'paid';
//            $order->save();
//        }

        $sale = Sale::where('payment_link_id', $paymentLink['id'])->first();
        $sale->status = 'paid';
        $sale->save();

        $userData = UserData::where('email', $sale->email)->first();

        $notes = [
            'craftyId' => $sale->reference_id,
            'user_id' => $userData->uid,
            'plan_id' => $sale->plan_id,
            'amount' => $sale->amount,
            'currency' => "INR",
            'fromWallet' => 0,
            'coins' => 0,
            'from' => "Web",
            'pay_mode' => $sale->subscription_type,
            'code' => 0,
            'ip' => null,
            'seats' => 1,
            'eventData' => json_encode([]),
            'caricatures' => $sale->caricature,
            'meta_course' => false,
            'on_demand_service' => false,
            'sales_person_id' => $sale->sales_person_id,
        ];

        $orderData = [
            'emp_id' => $sale->sales_person_id,
            'user_id' => $userData->uid,
            'plan_id' => $sale->plan_id,
            'crafty_id' => $sale->reference_id,
            'contact_no' => $sale->contact_no,
            'order_id' => $payment['order_id'],
            'razorpay_order_id' => $payment['order_id'],
            'razorpay_payment_id' => $payment['id'],
            'payment_id' => $payment['id'],
            'gateway' => 'razorpay',
            'status' => 'paid',
            'currency' => 'INR',
            'amount' => $sale->amount,
            'paid' => $sale->amount,
            'type' => $sale->subscription_type,
            'has_offer' => 0,
            'raw_notes' => json_encode($notes),
            'show_data' => 1,
            'url' => "https://www.craftyartapp.com",
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
            'gclid' => $request->cookie('_gclid'),
            'wbraid' => $request->cookie('_wbraid'),
            'gbraid' => $request->cookie('_gbraid'),
            'gcl_au' => $request->cookie('_gcl_au'),
            'ga' => $request->cookie('_ga'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'ip_address' => null,
        ];

        Order::create($orderData);

        (new PaymentController($request))->enterTransData(
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
        MasterPurchaseHistory::whereTransactionId($refund['payment_id'])->update(['payment_status' => 'refund_initiated']);
    }

    private function handleRefundProcessed($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refunded';
            $order->save();
        }
        MasterPurchaseHistory::whereTransactionId($refund['payment_id'])->update(['payment_status' => 'refunded']);
    }

    private function handleRefundFailed($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refund_failed';
            $order->save();
        }
        MasterPurchaseHistory::whereTransactionId($refund['payment_id'])->update(['payment_status' => 'refund_failed']);
    }

    private function handleDisputeLost($payload): void
    {
        $refund = $payload['refund']['entity'];
        $order = Order::where('razorpay_payment_id', $refund['payment_id'])->first();
        if ($order) {
            $order->status = 'refunded';
            $order->save();
        }
        TransactionLog::whereTransactionId($refund['payment_id'])->delete();
        MasterPurchaseHistory::whereTransactionId($refund['payment_id'])->update(['payment_status' => 'lost_dispute']);
    }
}

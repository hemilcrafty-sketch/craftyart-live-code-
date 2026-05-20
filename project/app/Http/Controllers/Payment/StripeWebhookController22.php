<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\DomainChecker;
use App\Jobs\ChangeStatusJob;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\UserSubscriptions;
use App\Models\TransactionLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Order;
use Stripe\Charge;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;
use Stripe\SubscriptionSchedule;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends ApiController
{
    public function handleWebhook(Request $request): JsonResponse
    {
        $endpoint_secret = "whsec_KPU037em8HoUIurHhH9Fcg37b4Ycyezh";

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            $paymentIntent = $event->data->object;

            switch ($event->type) {
                case 'payment_intent.created':   // ✅ CREATED
                    $this->handlePaymentCreated($paymentIntent);
                    break;

                case 'invoice.created':
                case 'invoice.payment_succeeded':
                    $this->handleInvoiceCreated($paymentIntent);
                    break;

                case 'payment_intent.payment_failed': // ❌ FAILED
                    $this->handlePaymentFailed($paymentIntent);
                    break;

                case 'payment_intent.processing': // ⏳ PROCESSING
                    $this->handlePaymentProcessing($paymentIntent);
                    break;

                case 'payment_intent.canceled': // ❌ CANCELED
                    $this->handlePaymentCanceled($paymentIntent);
                    break;

                case 'charge.succeeded': // ✅ SUCCESS
                    $this->handlePaymentSuccess($request, $paymentIntent);
                    break;

                case 'charge.refunded': // 🔄 REFUND SUCCESS
                    $this->handleRefundProcessed($paymentIntent);
                    break;

                case 'charge.refund.updated': // ❌ REFUND FAILED
                    $this->handleRefundFailed($paymentIntent);
                    break;

                case 'customer.subscription.paused':
                    $this->handleSubscriptionStatus($paymentIntent, 'paused');
                    break;

                case 'customer.subscription.resumed':
                    $this->handleSubscriptionStatus($paymentIntent, 'active');
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCancelled($paymentIntent);
                    break;

//                case 'subscription.paused':
//                    $this->handleSubscriptionStatus($paymentIntent, 'paused');
//                    break;
//
//                case 'subscription.resumed':
//                    $this->handleSubscriptionStatus($paymentIntent, 'active');
//                    break;
//
//                case 'subscription.halted':
//                    $this->handleSubscriptionStatus($paymentIntent, 'halted');
//                    break;
//
//                case 'subscription.pending':
//                    $this->handleSubscriptionStatus($paymentIntent, 'pending');
//                    break;
            }

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    private function handlePaymentCreated(PaymentIntent $paymentIntent): void
    {
        $metadata = $paymentIntent->metadata ?? [];
        $notes = json_encode($metadata);

        $ip = $notes->ip ?? null;
        if (!DomainChecker::isAllowedIps($ip)) {
            $order = Order::whereStripePaymentIntentId($paymentIntent->id)->first();
            if ($order) {
                ChangeStatusJob::dispatch($order->id)->delay(now()->addMinutes(5));
            }
        }
    }

    private function handleInvoiceCreated(Invoice $invoice): void
    {
        if ($invoice->subscription) {
            $metadata = $invoice->subscription_details->metadata ? $invoice->subscription_details->metadata->toArray() : [];
            $stripe = new StripeClient(PaymentController::$STRIPE_SECRET_KEY);
            $stripe->paymentIntents->update(
                $invoice->payment_intent,
                ['metadata' => $metadata]
            );
        }
    }

    private function handlePaymentFailed(PaymentIntent $paymentIntent): void
    {
        $metaData = $paymentIntent->metadata ? $paymentIntent->metadata->toArray() : [];
        $order = Order::whereStripePaymentIntentId($paymentIntent->id)->orWhere('crafty_id', $metaData['craftyId'])->first();
        if ($order) {
            $order->status = 'failed';
            $phone = null;

            try {
                if (!empty($paymentIntent->last_payment_error) &&
                    !empty($paymentIntent->last_payment_error->payment_method->billing_details->phone)) {
                    $phone = $paymentIntent->last_payment_error->payment_method->billing_details->phone;
                }
            } catch (Exception $e) {
                $phone = null;
            }

            $order->contact_no = $phone;
            $order->save();

            EmailController::sendPurchaseDropoutEmail($order);
        }
    }

    private function handlePaymentProcessing(PaymentIntent $paymentIntent): void
    {
        $metaData = $paymentIntent->metadata ? $paymentIntent->metadata->toArray() : [];
        $order = Order::whereStripePaymentIntentId($paymentIntent->id)->orWhere('crafty_id', $metaData['craftyId'])->first();
        if ($order) {
            $order->status = 'processing';
            $order->save();
        }
    }

    private function handlePaymentCanceled(PaymentIntent $paymentIntent): void
    {
        $metaData = $paymentIntent->metadata ? $paymentIntent->metadata->toArray() : [];
        $order = Order::whereStripePaymentIntentId($paymentIntent->id)->orWhere('crafty_id', $metaData['craftyId'])->first();
        if ($order) {
            $order->status = 'canceled';
            $order->save();
        }
    }

    private function handlePaymentSuccess(Request $request, Charge $charge): void
    {
        $metaData = $charge->metadata ? $charge->metadata->toArray() : [];
        if (empty($metaData)) {
            $stripe = new StripeClient(PaymentController::$STRIPE_SECRET_KEY);
            $pi = $stripe->paymentIntents->retrieve($charge->payment_intent);
            $metaData = $pi->metadata ? $pi->metadata->toArray() : [];
        }
        $order = Order::whereStripePaymentIntentId($charge->payment_intent)->orWhere('crafty_id', $metaData['craftyId'])->first();
        if ($order) {
            $order->status = 'paid';
            $order->stripe_txn_id = $charge->balance_transaction;
            $order->payment_id = $charge->balance_transaction;
            $order->paid = $charge->amount / 100;

            $phone = null;

            try {
                if (!empty($charge->billing_details->phone)) {
                    $phone = $charge->billing_details->phone;
                }
            } catch (Exception $e) {
                $phone = null;
            }

            $order->contact_no = $phone;

            $order->save();
            PaymentController::removeOrdersDuplicate($order);
        }

        (new PaymentController($request))->enterTransData(
            request: $request,
            transaction_id: $charge->balance_transaction,
            method: 'Stripe',
            currency_code: strtoupper($charge->currency),
            isManual: 0);
    }

    private function handleRefundProcessed(Refund $refund): void
    {
        $order = Order::whereStripePaymentIntentId($refund->payment_intent)->orWhere('stripe_txn_id', $refund->balance_transaction)->first();
        if ($order) {
            $order->status = 'refunded';
            $order->save();
        }
        MasterPurchaseHistory::whereTransactionId($refund->balance_transaction)->update(['payment_status' => 'refunded']);
    }

    private function handleRefundFailed(Refund $refund): void
    {
        $order = Order::whereStripePaymentIntentId($refund->payment_intent)->orWhere('stripe_txn_id', $refund->balance_transaction)->first();
        if ($order) {
            $order->status = 'refund_failed';
            $order->save();
        }
        MasterPurchaseHistory::whereTransactionId($refund->balance_transaction)->update(['payment_status' => 'refund_failed']);
    }

    private function handleSubscriptionStatus(StripeSubscription|SubscriptionSchedule $payload, $status): void
    {

        $subscriptionId = match (true) {
            $payload instanceof StripeSubscription => $payload->id,

            $payload instanceof SubscriptionSchedule
            && is_string($payload->subscription) => $payload->subscription,

            $payload instanceof SubscriptionSchedule
            && $payload->subscription instanceof StripeSubscription =>
            $payload->subscription->id,

            default => null,
        };

        if (!$subscriptionId) return;

        TransactionLog::whereSubscriptionId($subscriptionId)->update(['subscription_status' => $status]);
        MasterPurchaseHistory::whereSubscriptionId($subscriptionId)->update(['subscription_status' => $status]);
        UserSubscriptions::whereGatewaySubscriptionId($subscriptionId)->update(['status' => $status]);
    }

    private function handleSubscriptionCancelled(StripeSubscription $payload): void
    {
        TransactionLog::whereSubscriptionId($payload->id)->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
        MasterPurchaseHistory::whereSubscriptionId($payload->id)->update(['subscription_is_active' => 0, 'subscription_status' => 'cancelled']);
        UserSubscriptions::whereGatewaySubscriptionId($payload->id)->update(['status' => 'cancelled']);
    }

}

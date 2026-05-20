<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Caricature\AICreditController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Payment\Gateways\PhonePeGateway;
use App\Http\Controllers\Payment\Gateways\RazorpayGateway;
use App\Http\Controllers\Payment\Gateways\StripeGateway;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\DomainChecker;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\GoogleEnum;
use App\Http\Controllers\Utils\GoogleEvent;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PlanLimitHelper;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Jobs\ChangeStatusJob;
use App\Jobs\PreDebitNotificationJob;
use App\Models\AI\AICreditTransaction;
use App\Models\Caricature\AIPurchaseHistory;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Design;
use App\Models\ExportTable;
use App\Models\Order;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\SubPlan;
use App\Models\PromoCode;
use App\Models\PurchaseHistory;
use App\Models\Revenue\BusinessSupportPurchaseHistory;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\UserSubscriptions;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use App\Services\PaymentGateway;
use App\Services\WhatsAppService;
use Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Razorpay\Api\Api;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends ApiController
{

    public static int $MAX_ADDON_CARICATURES = 10;

    public static float $MAX_ADDON_CARICATURE_RATE_INR = 249;
    public static float $MAX_ADDON_CARICATURE_RATE_USD = 9.99;

    public static float $META_COURSE_RATE_INR = 149;
    public static float $META_COURSE_RATE_USD = 9.99;

    public static float $ON_DEMAND_SERVICE_RATE_INR = 499;
    public static float $ON_DEMAND_SERVICE_RATE_USD = 9.99;

    public static float $BUSINESS_SUPPORT_RATE_INR = 49;
    public static float $BUSINESS_SUPPORT_RATE_USD = 9.99;

    public static array $OFFER_IDS = [23, 24, 26, 29, 30, 33, 34, 35, 36, 37, 16, 38, 39, 40];
    public static int $FREE_TEMPLATE_DISCOUNT = 50;

    function refreshTransaction(Request $request, $id = null): array|string
    {

//        if (!DomainChecker::isPanel($request)) return "error";

        if ($id) {
            return $this->enterTransData(
                request: $request,
                transaction_id: $id,
                method: str_starts_with($id, 'pay_') ? "Razorpay" : "stripe",
                currency_code: "INR",
                isManual: 1
            );
        }

        try {

            $errors = [];
            $data = [];

            $paymentGateway = PaymentGateway::initByGateway('razorpay', null);
            if (!$paymentGateway) {
                $errors[] = "Razorpay init failed";
            } else {
                /** @var Api $razorpay */
                $razorpay = $paymentGateway->client;
                $datas = $razorpay->payment->all(array('count' => '100'));
                $datas = $datas->toArray();
                foreach ($datas['items'] as $value) {

                    if (MasterPurchaseHistory::whereTransactionId($value['id'])->exists()) continue;

                    if ($value['status'] == 'captured') {
//                    if (!$this->isTester($value['notes']['user_id'])) {
                        $result = $this->enterTransData(
                            request: $request,
                            transaction_id: $value['id'],
                            method: 'Razorpay',
                            currency_code: "INR",
                            isManual: 1);
                        $data[] = $result;
//                    }
                    }
                }
            }

            $paymentGateway = PaymentGateway::initByGateway('stripe', null);
            if (!$paymentGateway) {
                $errors[] = "Stripe init failed";
            } else {
                /** @var StripeClient $stripe */
                $stripe = $paymentGateway->client;
                $datas = $stripe->charges->all(['limit' => 100]);
                $datas = $datas->toArray();
                foreach ($datas['data'] as $value) {

                    if (MasterPurchaseHistory::whereTransactionId($value['balance_transaction'])->exists()) continue;

                    if ($value['amount'] == $value['amount_captured'] && $value['amount_refunded'] === 0 && $value['amount'] > 100) {
//                    if (!$this->isTester($value['metadata']['user_id'])) {
                        $result = $this->enterTransData(
                            request: $request,
                            transaction_id: $value['balance_transaction'],
                            method: 'Stripe',
                            currency_code: $value['currency'],
                            isManual: 1);
                        $data[] = $result;
//                    }
                    }
                }
            }

            $paymentGateway = PaymentGateway::initByGateway('phonepe_pg', null);
            if (!$paymentGateway) {
                $errors[] = "PhonePe init failed";
            } else {
                /** @var StandardCheckoutClient $phonepe */
                $phonepe = $paymentGateway->client;
                $datas = Order::whereGateway('phonepe_pg')->where("status", "!=", "failed")->limit(100)->get();
                foreach ($datas as $value) {
                    try {
                        $phonepeData = $phonepe->getOrderStatus($value->crafty_id, true);
                        if ($phonepeData->getState() === 'COMPLETED' && !MasterPurchaseHistory::whereTransactionId($value->crafty_id)->exists()) {

                            $paidAmount = $phonepeData->getAmount() / 100;

                            $value->payment_id = $value->crafty_id;
                            $value->paid = $paidAmount;
                            $value->status = 'paid';
                            $value->save();

                            $result = $this->enterTransData(
                                request: $request,
                                transaction_id: $value->crafty_id,
                                method: 'phonepe_pg',
                                currency_code: "INR",
                                isManual: 1);
                            $data[] = $result;
                        }
                    } catch (Exception $e) {
                        $errors[] = "PhonePe order status check failed for order ID: {$value->crafty_id}, error: {$e->getMessage()}";
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = 'Done';
            $response['data'] = $data;
            $response['errors'] = $errors;

        } catch (Exception $e) {
            $response['success'] = false;
            $response['data'] = $data;
            $response['errors'] = $errors;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    function checkPromoCode(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $ipData = HelperController::getIpAndCountry($request);
        $amount = $request->get('amount');
        $code = $request->get('code');
        $show24Buyers = $request->get('sb', false);

        $data = $this->cpm($amount, $code, $ipData['cur']);
        $currency = $ipData['cur'];
        $curSymbol = $currency === "INR" ? '₹' : '$';
        $data['curSymbol'] = $curSymbol;

        if ($currency === 'INR') $discount = round($amount * self::$FREE_TEMPLATE_DISCOUNT / 100);
        else $discount = $amount * self::$FREE_TEMPLATE_DISCOUNT / 100;

        $discAmount = $amount - $discount;
        $discAmount = number_format((float)$discAmount, 2, '.', '');

        $data['template_discount'] = self::$FREE_TEMPLATE_DISCOUNT;
        $data['template_amount'] = $curSymbol . $discAmount;

        if ($show24Buyers) {
            $models = [
                PurchaseHistory::class,
                VideoPurchaseHistory::class,
                CaricaturePurchaseHistory::class,
                AIPurchaseHistory::class,
                TransactionLog::class,
            ];

            $last24Buyers = 0;
            foreach ($models as $model) {
                /** @var class-string<Model> $model */
                $last24Buyers += $model::where('created_at', '>=', now()->subHours(24))->count();
            }

            $data['last_24_hours_buyers'] = "$last24Buyers sold in last 24 hours";
        }
        return ResponseHandler::sendEncryptedResponse($request, $data);
    }

    function getTempRates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $ids = $request->get('ids');
        $offerApplied = $request->get('offer', false);
        if (is_null($ids)) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Ids are missing"));

        if (str_starts_with($ids, "{")) $ids = CryptoJsAes::decrypt($ids, $this->aesPassword);

        $response = self::getRatesOfTemplates($request, $this->uid, $ids, $offerApplied);

        return ResponseHandler::sendResponse($request, new ResponseInterface($response["statusCode"], $response["success"], $response["msg"], $response["data"] ?? []));
    }

    function getOrder(Request $request): array|string
    {
        $errorMsg = "Looks like this payment link was cancelled or already paid";
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $id = $request->get('id');
        if (is_null($id)) return $this->failed(msg: "Parameters missing!");

        $order = Order::whereCraftyId($id)->whereStatus('failed')->first();
        if (!$order) return $this->getExportTemplate($id, $errorMsg);

        $user_data = UserData::where("uid", $order->user_id)->first();
        if (!$user_data) return $this->failed(msg: "Invalid User");

        $ipData = HelperController::getIpAndCountry($request);

        $description = 'Premium Assets';
        $purchaseData = null;
        $promo = true;
        $id = null;
        $amount = 0;
        $offer_amount = 0;
        $offer_amount_str = null;
        $isInr = $order->currency === "INR";
        $offerMsg = null;
        $autoTrigger = false;

        if ($order->type === 'old_sub') {
            if (in_array($order->plan_id, self::$OFFER_IDS)) $promo = false;
            $purchaseData = Subscription::getSubs(ids: [$order->plan_id], currency: $order->currency, status: null);
            if (!$purchaseData) return $this->failed(msg: "Invalid Payment");
            $purchaseData = $purchaseData[0];
            $description = $purchaseData['package_name'];
            $id = "" . $purchaseData['id'];
            $amount = $purchaseData['price'];
        } else if ($order->type === 'template') {
            $purchaseData = Design::getTempDatas($order);
            $id = $purchaseData['data']['id'];
            $amount = $purchaseData['amount'];
            if ($isInr) $discount = round($amount * self::$FREE_TEMPLATE_DISCOUNT / 100);
            else $discount = $amount * self::$FREE_TEMPLATE_DISCOUNT / 100;
            $offer_amount = $amount - $discount;
            if ($isInr) $offer_amount = number_format((float)$offer_amount);
            else $offer_amount = number_format((float)$offer_amount, 2);
            $offerMsg = self::$FREE_TEMPLATE_DISCOUNT . "% Discount applied";
            $offer_amount_str = ($isInr ? "₹" : "$") . $offer_amount;
            $autoTrigger = $isInr;
        }

        $purchaseData['offer_amount'] = (float)$offer_amount;
        $purchaseData['offer_amount_str'] = $offer_amount_str;

        $returnRes['id'] = $id;
        $returnRes['amount'] = (float)$amount;
        $returnRes['currency'] = $order->currency;
        $returnRes['promo'] = $promo;
        $returnRes['orderData'] = $purchaseData;
        $returnRes['name'] = '';
        $returnRes['offer'] = (bool)$order->has_offer;
        $returnRes['offer_msg'] = $offerMsg;
        $returnRes['has_offer'] = (bool)$offerMsg;
        $returnRes['type'] = $order->gateway;
        $returnRes['auto_trigger'] = $autoTrigger;

//        if (!is_null($order->razorpay_order_id)) {
        if ($order->gateway === 'razorpay') {
            $paymentGateway = PaymentGateway::initByGateway('razorpay', null);
            if (!$paymentGateway) return $this->failed(msg: "Razorpay init failed");

            if (!$promo) {
                $payment_data = [];
                $payment_data['key'] = $paymentGateway->credentials['publishable_key'];
                $payment_data['name'] = "CraftyArt";
                $payment_data['description'] = $description;
                if (str_starts_with($order->razorpay_order_id, "sub_")) {
                    $payment_data['subscription_id'] = $order->razorpay_order_id;
                } else {
                    $payment_data['customer_id'] = $user_data->razorpay_cus_id;
                    $payment_data['order_id'] = $order->razorpay_order_id;
                }
                $payment_data['remember_customer'] = True;
                $payment_data['notes'] = ['craftyId' => $order->crafty_id];
                $payment_data['method'] = [ // Add this to restrict to UPI
                    'upi' => true,
                    'card' => true,
                    'netbanking' => true,
                    'wallet' => true
                ];
                $payment_data['config'] = [
                    'display' => [
                        'preferences' => [
                            'show_default_blocks' => true,
                            'payment_options_order' => ['upi', 'card', 'netbanking', 'wallet'],
                            'highlight' => ['upi', 'card'],
                        ]
                    ]
                ];

                if ($user_data->contact_no) {
                    $payment_data['prefill'] = ['contact' => $user_data->contact_no];
                }

                if (!DomainChecker::isAllowedIps($ipData['ip'])) {
                    ChangeStatusJob::dispatch($order->id)->delay(now()->addMinutes(5));
                }

                $returnRes['paymentData'] = $payment_data;
            }

            $returnRes['type'] = 'razorpay';
            $returnRes['create'] = $promo;

        } else if ($order->gateway === 'stripe') {
            try {
                $paymentGateway = PaymentGateway::initByGateway('stripe', null);
                if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");
                $stripe = $paymentGateway->client;

                $datas = $stripe->customers->allPaymentMethods($user_data->stripe_cus_id, ['type' => 'card']);
                $returnRes['type'] = 'stripe';
                $returnRes['create'] = true;
                $returnRes['pm_data'] = [
                    'email' => $user_data->email,
                    'data' => $datas,
                    'ipData' => ['ip' => '', 'cc' => $isInr ? 'IN' : 'US', 'cn' => '', 'cur' => $order->currency]
                ];
            } catch (ApiErrorException|Exception $e) {
                return $this->successed(msg: $errorMsg);
            }
        }

        return $this->successed(datas: $returnRes);
    }

    function getExportTemplate($id, $errorMsg): array|string
    {
        $export = ExportTable::whereCraftyId($id)->whereWatermark(1)->first();
        if (!$export) return $this->successed(msg: $errorMsg);

        $user_data = UserData::where("uid", $export->uid)->first();
        if (!$user_data) return $this->failed(msg: "Invalid User");

        $isInr = $export->currency === "INR";

        $purchaseData = $export->getTempDatas(false);
        if (!$purchaseData) return $this->successed(msg: $errorMsg);

        $id = $purchaseData['data']['id'];

        $amount = $purchaseData['amount'];
        if ($isInr) $discount = round($amount * self::$FREE_TEMPLATE_DISCOUNT / 100);
        else $discount = $amount * self::$FREE_TEMPLATE_DISCOUNT / 100;
        $offer_amount = $amount - $discount;
        if ($isInr) $offer_amount = number_format((float)$offer_amount);
        else $offer_amount = number_format((float)$offer_amount, 2);
        $offerMsg = self::$FREE_TEMPLATE_DISCOUNT . "% Discount applied";

        $purchaseData['offer_amount'] = (float)$offer_amount;
        $purchaseData['offer_amount_str'] = ($isInr ? "₹" : "$") . $offer_amount;

        $returnRes['id'] = json_encode($id);
        $returnRes['amount'] = (float)$amount;
        $returnRes['currency'] = $export->currency;
        $returnRes['promo'] = true;
        $returnRes['orderData'] = $purchaseData;
        $returnRes['name'] = '';
        $returnRes['offer'] = false;
        $returnRes['offer_msg'] = $offerMsg;
        $returnRes['has_offer'] = (bool)$offerMsg;
        $returnRes['auto_trigger'] = $isInr;

        if ($isInr) {
            $returnRes['type'] = 'razorpay';
            $returnRes['create'] = true;
        } else {
            try {
                $paymentGateway = PaymentGateway::initByGateway('stripe', null);
                if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");
                $stripe = $paymentGateway->client;

                $datas = $stripe->customers->allPaymentMethods($user_data->stripe_cus_id, ['type' => 'card']);
                $returnRes['type'] = 'stripe';
                $returnRes['create'] = true;
                $returnRes['pm_data'] = [
                    'email' => $user_data->email,
                    'data' => $datas,
                    'ipData' => ['ip' => '', 'cc' => 'US', 'cn' => '', 'cur' => $export->currency]
                ];
            } catch (ApiErrorException|Exception $e) {
                return $this->successed(msg: $errorMsg);
            }
        }

        return $this->successed(datas: $returnRes);
    }

    function cancelSubscription(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $reason = $request->get('reason');
        $comment = $request->get('comment');

        if (empty($reason)) return $this->failed(msg: "Invalid Params");

        $reason = json_decode($reason, true);
        if (!is_array($reason)) return $this->failed(msg: "Reason not valid");

        $comment = 'comment => ' . $comment;
        $reason[] = $comment;

        $data = TransactionLog::whereUserId($this->uid)->whereSubscriptionIsActive(1)->first();

        if (!$data) return $this->failed(msg: "Unknown error");

        $paymentGateway = PaymentGateway::initByGateway($data->payment_method, null);
        if (!$paymentGateway) return $this->failed(msg: "Gateway init failed");

        $success = false;
        $errorMsg = null;
        if ($paymentGateway->name === 'razorpay') {
            $errorMsg = RazorpayGateway::cancelSubscription($paymentGateway, $data->subscription_id);
        } else if ($paymentGateway->name === 'stripe') {
            $errorMsg = StripeGateway::cancelSubscription($paymentGateway, $data->subscription_id);
        } else if ($paymentGateway->name === 'phonepe_pg') {
            $errorMsg = PhonePeGateway::cancelSubscription($paymentGateway, $data->subscription_id);
        }

        if (empty($errorMsg)) {
            $data->subscription_is_active = 0;
            $data->cancellation_reason = json_encode($reason);
            $data->subscription_status = 'cancelled';
            $success = $data->save();
        }

        if (!$success) return $this->failed(msg: $errorMsg ?? "Please try after sometime");

        MasterPurchaseHistory::whereSubscriptionId($data->subscription_id)->whereSubscriptionIsActive(1)->update(['subscription_status' => 'cancelled', 'cancellation_reason' => json_encode($reason)]);
        UserSubscriptions::whereGatewaySubscriptionId($data->subscription_id)->update(['status' => 'cancelled', 'cancellation_reason' => json_encode($reason)]);

        return $this->successed(msg: "Your subscription has been canceled successfully");
    }

    function createRazorPayIntent(Request $request): array|string
    {
        return $this->createOrder($request, "razorpay");
    }

    function createStripeIntent(Request $request): array|string
    {
        return $this->createOrder($request, "stripe");
    }

    function listMethods(Request $request): array|string
    {
        if (!$request->has('u') && $this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $this->uid = $request->input("u", $this->uid);
        $ipData = HelperController::getIpAndCountry($request);
        $user_data = UserData::where("uid", $this->uid)->first();

        if (!$user_data || $request->offer === true) {
            return $this::successed(datas: [
                'email' => '',
                'data' => ['data' => []],
                'ipData' => $ipData
            ]);
        }

        $paymentGateway = PaymentGateway::initByGateway('stripe', null);
        if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");

        $stripeCusId = $this::createUserPaymentGatewayRefId($user_data, null, $paymentGateway);

        return $this::successed(datas: [
            'email' => $user_data->email,
            'data' => $paymentGateway->client->customers->allPaymentMethods($stripeCusId, ['type' => 'card']),
            'ipData' => $ipData
        ]);

    }

    function updatePm(Request $request): array|string
    {
        if (!$request->has('u') && $this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $this->uid = $request->input("u", $this->uid);
        $pmId = $request->get('pm');
        $billing_details = $request->get('billing_details');
        $month = $request->get('month');
        $year = $request->get('year');

        $user = UserData::whereUid($this->uid)->first();
        if (empty($pmId) || empty($user) || empty($user->stripe_cus_id) || empty($billing_details) || empty($month) || empty($year)) return $this->failed("Parameters missing!");

        $paymentGateway = PaymentGateway::initByGateway('stripe', null);
        if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");
        $stripeClient = $paymentGateway->client;

        $billingDatas = json_decode($billing_details, true);

        try {
            $customer = $stripeClient->customers->retrieve($user->stripe_cus_id);
            $paymentMethod = $stripeClient->paymentMethods->retrieve($pmId);

            if (empty($paymentMethod->customer) || $paymentMethod->customer !== $customer->id) {
                return $this->failed(msg: "Unable to edit payment method.");
            }

            $subscriptions = $stripeClient->subscriptions->all([
                'customer' => $customer->id,
                'status' => 'all',
                'limit' => 100,
            ]);

            foreach ($subscriptions->data as $subscription) {
                if (in_array($subscription->status, ['active', 'past_due'], true) && $subscription->default_payment_method === $pmId) {
                    return $this->failed(msg: "Payment method is used by an active or past due subscription.");
                }
            }

            $data = $stripeClient->paymentMethods->update($pmId, ['billing_details' => $billingDatas, 'card' => ['exp_month' => $month, 'exp_year' => $year]]);

            return $this->successed(datas: ['data' => $data]);

        } catch (\Exception $e) {
            return $this->failed();
        }
    }

    function detachPm(Request $request): array|string
    {
        if (!$request->has('u') && $this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $this->uid = $request->input("u", $this->uid);
        $pmId = $request->get('pm');
        $user = UserData::whereUid($this->uid)->first();
        if (empty($pmId) || empty($user) || empty($user->stripe_cus_id)) return $this->failed("Parameters missing!");

        $paymentGateway = PaymentGateway::initByGateway('stripe', null);
        if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");
        $stripeClient = $paymentGateway->client;

        try {
            $customer = $stripeClient->customers->retrieve($user->stripe_cus_id);
            $paymentMethod = $stripeClient->paymentMethods->retrieve($pmId);

            if (empty($paymentMethod->customer) || $paymentMethod->customer !== $customer->id) {
                return $this->failed(msg: "Unable to detach payment method.");
            }

            if ($customer->invoice_settings->default_payment_method === $pmId) return $this->failed(msg: "Cannot detach default payment method.");

            $subscriptions = $stripeClient->subscriptions->all([
                'customer' => $customer->id,
                'status' => 'all',
                'limit' => 100,
            ]);

            foreach ($subscriptions->data as $subscription) {
                if (in_array($subscription->status, ['active', 'past_due'], true) && $subscription->default_payment_method === $pmId) {
                    return $this->failed(msg: "Payment method is used by an active or past due subscription.");
                }
            }

            $data = $stripeClient->paymentMethods->detach($pmId, []);

            return $this->successed(datas: ['data' => $data]);
        } catch (\Exception $e) {
            return $this->failed();
        }
    }

    function createOrder(Request $request, $gatewayType___ = null): array|string
    {

        $this->uid = $request->input("u", $this->uid);
        $paymentMethodId = $request->get('id');
        $name = $request->get('name');
        $number = $request->get('number');
        $email = $request->get('email');
        $refId = $request->get('ref');
        $url = $request->get('url');
        $seats = $request->get('seats', 1);
        $offerApplied = $request->get('offer', false);
        $addOns = $request->get('add_ons');

        $allExists = $name && $email && $number;

        if (!$request->has("u")) {
            if (($allExists || $refId) ? $this->isFakeRequest($request) : $this->isFakeRequestAndUser($request)) {
                return $this->failed(msg: "Unauthorized");
            }
        }

        if (empty($addOns)) {
            $addOns = [];
        }

        if (is_string($addOns)) {
            $addOns = json_decode($addOns, true);
        }

        $assetDetails = $request->get('p');
//        $currency = $request->get('currency');
        $from = $request->get('from', 'Web');
        $code = $request->get('code');

        if ($assetDetails == null) {
            return $this->failed(msg: "Parameters missing!");
        }

        if (DomainChecker::isEditor($request)) $from = 'Editor';

        $ipData = HelperController::getIpAndCountry($request);
        $currency = $ipData['cur'];
        $fromAllowedIp = DomainChecker::isAllowedIps($ipData['ip']);

        if (strtoupper($currency) != "INR") $currency = "USD";

        $isInr = $currency === "INR";

//        if ($currency === "USD") $gatewayType = 'stripe';

        $password = HelperController::generateID(length: 6);

        if ($refId) {
            $order = Order::whereCraftyId($refId)->first();
            if (!$order) $order = ExportTable::whereCraftyId($refId)->whereWatermark(1)->first();
            if ($order) $this->uid = $order->user_id ?? $order->uid;
        }

        if ($name && $email && $number) {
            $user_data = UserData::whereEmail($email)->first();
            if (!$user_data) {
                $userController = new UserController($request);
                $result = $userController->createFirebaseUser($request, $name, $email, $number, $password);
                if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
                $user_data = $result['data'];
            }
        } else {
            $user_data = UserData::where("uid", $this->uid)->first();
        }

        if (!$user_data) return $this->failed(msg: "Error");

        $this->uid = $user_data->uid;

        $paymentDetails = $this->getPaymentDetails(request: $request, user_data: $user_data, currency: $currency, assetDetails: $assetDetails, code: $code, offerApplied: $offerApplied, url: $url, seats: $seats);
        if (!$paymentDetails['success']) return $paymentDetails;

        $assetDetails = $paymentDetails['assetDetails'];
        $amount = $paymentDetails['amount'];
        $payMode = $paymentDetails['payMode'];
        $promoCodeId = $paymentDetails['promoCodeId'];
        $description = $paymentDetails['description'];
        $eventData = $paymentDetails['eventData'];
        $validity = $paymentDetails['validity'];
        $subId = $paymentDetails['subId'];
        $isMeta = $paymentDetails['isMeta'];
        $trialDays = $paymentDetails['trial_days'];
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $paymentDetails['paymentGateway'];

        if ($payMode !== 'template') $offerApplied = false;

        /** @var Subscription|OfferPackage|SubPlan|null $planData */
        $planData = $paymentDetails['planData'];

//        $isSubscription = in_array($payMode, ["old_sub", "new_sub", "offer_sub"]);
        $isSubscription = !empty($subId);

        $pageUrl = strtok($url, '?');
//        if (in_array($pageUrl, PlanController::$unusedUrls)) {
//        if (in_array($user_data->email, ["jdmakwana1999@gmail.com", "jignashacrafty@gmail.com", "zalaksoni022@gmail.com"])) {
//            $typee = 'phonepe_pg';
//            $paymentGateway = PaymentGateway::initByGateway($typee, $isInr ? "NATIONAL" : "INTERNATIONAL");
//        } else {
//            if ($request->has("u")) {
//                $paymentGateway = PaymentGateway::initByGateway($isInr ? 'razorpay' : 'stripe', $isInr ? "NATIONAL" : "INTERNATIONAL");
//            } else {
//                $paymentGateway = PaymentGateway::initByType($isSubscription ? "subscription" : $payMode, $isInr ? "NATIONAL" : "INTERNATIONAL");
//            }
//        }

        if ($request->has("u")) {
            $paymentGateway = PaymentGateway::initByGateway($isInr ? 'razorpay' : 'stripe', $isInr ? "NATIONAL" : "INTERNATIONAL");
        }

        if (!$paymentGateway) return $this->failed(msg: "Gateway init failed");

        $gatewayType = $paymentGateway->name;

        $paymentGatewayRefId = $this->createUserPaymentGatewayRefId(user_data: $user_data, number: $number, paymentGateway: $paymentGateway);
        if (!$paymentGatewayRefId) return $this->failed(msg: "User error!");

        $craftyId = Order::generateCraftyId();

        $newAddOns = [];

        foreach ($addOns as $addOn) {
            if ($addOn === 'caricature') {
                $newAddOns[] = [
                    'key' => $addOn,
                    'name' => PaymentController::$MAX_ADDON_CARICATURES . " Caricatures",
                    'amount' => $isInr ? PaymentController::$MAX_ADDON_CARICATURE_RATE_INR : PaymentController::$MAX_ADDON_CARICATURE_RATE_USD,
                    'currency' => $currency,
                    'value' => PaymentController::$MAX_ADDON_CARICATURES,
                ];
            } else if ($addOn === 'meta_course') {
                $newAddOns[] = [
                    'key' => $addOn,
                    'name' => "How To Find Client (Course)",
                    'amount' => $isInr ? PaymentController::$META_COURSE_RATE_INR : PaymentController::$META_COURSE_RATE_USD,
                    'currency' => $currency,
                    'value' => true,
                ];
            } else if ($addOn === 'on_demand_service') {
                $newAddOns[] = [
                    'key' => $addOn,
                    'name' => "On Demand Service",
                    'amount' => $isInr ? PaymentController::$ON_DEMAND_SERVICE_RATE_INR : PaymentController::$ON_DEMAND_SERVICE_RATE_USD,
                    'currency' => $currency,
                    'value' => true,
                ];
            } else if ($addOn === 'business_support') {
                $newAddOns[] = [
                    'key' => $addOn,
                    'name' => "Business Support",
                    'amount' => $isInr ? PaymentController::$BUSINESS_SUPPORT_RATE_INR : PaymentController::$BUSINESS_SUPPORT_RATE_USD,
                    'currency' => $currency,
                    'value' => true,
                ];
            }
        }

        $notes = [
            'craftyId' => $craftyId,
            'user_id' => $user_data->uid,
            'plan_id' => $assetDetails,
            'amount' => $amount,
            'currency' => $currency,
            'fromWallet' => 0,
            'coins' => 0,
            'from' => $from,
            'pay_mode' => $payMode,
            'code' => $promoCodeId,
            'ip' => $ipData['ip'],
            'seats' => $seats,
            'eventData' => json_encode($eventData),
            'add_ons' => $newAddOns,
        ];

        foreach ($newAddOns as $addOn) {
            $notes[$addOn['key']] = $addOn['value'];
        }

        if ($gatewayType === 'razorpay') {
            $datas = RazorpayGateway::createRazorpayOrder(
                paymentGateway: $paymentGateway,
                user_data: $user_data,
                assetDetails: $assetDetails,
                craftyId: $craftyId,
                trialDays: $trialDays,
                isMeta: $isMeta,
                promoCodeId: $promoCodeId,
                subId: $subId,
                amount: $amount,
                validity: $validity,
                currency: $currency,
                razorpayCusId: $paymentGatewayRefId,
                description: $description,
                seats: $seats,
                userAddOns: $newAddOns,
            );
        } else if ($gatewayType === 'stripe') {
            $datas = StripeGateway::createStripeOrder(
                paymentGateway: $paymentGateway,
                craftyId: $craftyId,
                paymentMethodId: $paymentMethodId,
                subId: $subId,
                amount: $amount,
                currency: $currency,
                stripeCusId: $paymentGatewayRefId,
                seats: $seats,
                userAddOns: $newAddOns,
            );
        } else if ($gatewayType === 'phonepe_pg') {
            $datas = PhonePeGateway::createPhonepe(
                paymentGateway: $paymentGateway,
                isSubscription: $isSubscription,
                craftyId: $craftyId,
                amount: $amount,
                seats: $seats,
                userAddOns: $newAddOns,
                planData: $planData,
            );
        } else {
            return $this->failed(datas: ['gateway' => $gatewayType]);
        }

        if (empty($datas) || is_string($datas)) return $this->failed(msg: "Empty Data", datas: ['datas' => $datas, 'gatewayType' => $gatewayType], showDecoded: $this->isTester());

        $orderData = [
            'emp_id' => Order::getOrderAssignEmpId($this->uid),
            'user_id' => $this->uid,
            'plan_id' => $assetDetails,
            'crafty_id' => $craftyId,
            'contact_no' => $user_data->contact_no,
            'order_id' => $datas['id'] ?? null,
            'subscription_id' => $datas['subscription_id'] ?? null,
            'gateway' => $gatewayType,
            'status' => 'pending',
            'currency' => $currency,
            'amount' => $amount,
            'type' => $payMode,
            'has_offer' => $offerApplied ? 1 : 0,
            'raw_notes' => json_encode($notes),
            'show_data' => $fromAllowedIp ? 0 : 1,
            'url' => $url,
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
            'gclid' => $request->cookie('_gclid'),
            'wbraid' => $request->cookie('_wbraid'),
            'gbraid' => $request->cookie('_gbraid'),
            'gcl_au' => $request->cookie('_gcl_au'),
            'ga' => $request->cookie('_ga'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'ip_address' => $ipData['ip'],
        ];

        if (isset($datas['key'])) {
            $orderData[$datas['key']] = $datas['id'];
        }

        $order = Order::create($orderData);

        if ($order && $order->fbc != null) {
            self::fbEvent(
                eventName: FacebookEvent::INITIATE_CHECKOUT,
                request: $request,
                uid: $this->uid,
                assetDetails: $assetDetails,
                name: $user_data->name,
                email: $user_data->email,
                contact: $user_data->contact_no,
                currency: $currency,
                amount: $amount,
                detail: json_decode(json_encode($eventData)),
                isOfferPixel: $isMeta,
                url: $url
            );
        }

        self::googleEvent(
            eventName: GoogleEnum::INITIATE_CHECKOUT,
            assetDetails: $assetDetails,
            currency_code: $currency,
            amount: $amount,
            transaction_id: null,
            detail: json_decode(json_encode($eventData)),
            email: $user_data->email,
            url: $url
        );

        return $this->successed(datas: ['data' => $datas['data'], 'type' => $gatewayType]);
    }

    function webhook(Request $request): array|string
    {

//        $assetDetails = $request->get('plan_id');
        $method = $request->get('method');
        $transaction_id = $request->get('transaction_id');
        $currency_code = $request->get('currency_code');
//        $fromWhere = $request->has('fromWhere') ? $request->get('fromWhere') : "Mobile";
        $isManual = $request->has('isManual') ? $request->get('isManual') : 1;

        if (/*$assetDetails == null ||*/ $method == null || $transaction_id == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        $data = $this->enterTransData($request, $transaction_id, $method, $currency_code, $isManual);

        $success = $data['success'];
        $msg = $data['msg'];
        $is_trial = $data['is_trial'] ?? false;
        $days = $data['days'] ?? 0;

        return ResponseHandler::sendResponse($request, new ResponseInterface($success ? 200 : 401, $success, $msg, ['is_trial' => $is_trial, 'days' => $days]));

    }

    private function getPaymentDetails(Request $request, UserData $user_data, $currency, $assetDetails, $code, $offerApplied, $url, $seats): array
    {
        $validity = 0;
        $subId = null;
        $isMeta = false;
        $trial_days = 0;
        $trialAmount = 0;
        $tempAssetData = json_decode($assetDetails, true);
        $isInr = strtoupper($currency) == "INR";

        /** @var Subscription|OfferPackage|SubPlan|null $planData */
        $planData = null;

        if ($tempAssetData && is_array($tempAssetData)) {
            $assetDatas = $this->getAssetData(RateController::getRates(true), $tempAssetData, $currency, $this->uid, $offerApplied);
            if (!$assetDatas['success']) return $this->failed(msg: $assetDatas['message'], showDecoded: true);

            $payMode = $assetDatas['payMode'];
            if ($payMode === 'ai_credit') $description = 'AI Credits';
            else $description = 'Premium Assets';
            $amount = $assetDatas['message'];
            $assetDetails = json_encode($assetDatas['datas']);

            $paymentGateway = PaymentGateway::initByType($payMode, $isInr ? "NATIONAL" : "INTERNATIONAL");
            if (!$paymentGateway) return $this->failed(msg: 'Gateway Error', showDecoded: true);
        } else {

            if (is_numeric($assetDetails)) {
                if (in_array($assetDetails, self::$OFFER_IDS)) $res = Subscription::find($assetDetails);
                else $res = Subscription::whereId($assetDetails)->whereStatus(1)->first();
                if (!$res) return $this->failed(msg: 'Sub Error', showDecoded: true);
                $description = $res->package_name;
                if ($isInr) $amount = $res->price;
                else $amount = $res->price_dollar;
                $payMode = 'old_sub';
                $validity = $res->validity;
                $subId = $isInr ? $res->sub_id : $res->stripe_sub_id;
                $isMeta = $res->is_meta == 1;
                $trial_days = $res->trial_days;
                $planData = $res;

                $already = TransactionLog::whereUserId($user_data->uid)->whereIn('plan_id', PaymentController::$OFFER_IDS)->exists();
                if ($already) $trial_days = 0;

                $paymentGateway = PaymentGateway::initByType("subscription", $isInr ? "NATIONAL" : "INTERNATIONAL");
                if (!$paymentGateway) return $this->failed(msg: 'Gateway Error', showDecoded: true);
            } else {
                $initType = 'offer_sub';
                $newPayMode = 'offer_sub';
                $newPlan = OfferPackage::with(['plan', 'duration'])->where('string_id', $assetDetails)->first();
                if (!$newPlan) {
                    $initType = 'subscription';
                    $newPayMode = 'new_sub';
                    $newPlan = SubPlan::with(['plan', 'duration'])->where('string_id', $assetDetails)->first();
                }

                if (!$newPlan || !$newPlan->plan || !$newPlan->duration || array_diff(SubPlan::$PLAN_KEYS, array_keys($newPlan->plan_details))) {
                    return $this->failed(msg: 'Sub Error', showDecoded: true);
                } else {

                    $paymentGateway = PaymentGateway::initByType($initType, $isInr ? "NATIONAL" : "INTERNATIONAL");
                    if (!$paymentGateway) return $this->failed(msg: 'Gateway Error', showDecoded: true);

                    $description = $newPlan->plan->name;
                    if ($isInr) $amount = $newPlan->plan_details["inr_offer_price"];
                    else $amount = $newPlan->plan_details["usd_offer_price"];
                    $payMode = $newPayMode;
                    $validity = $newPlan->duration->duration;
                    $planData = $newPlan;

                    $subId = $newPlan->subscription_ids[$paymentGateway->name] ?? null;
                    if ($subId) {
                        if ($isInr) {
                            $trial_days = $newPlan->plan_details['inr_trial_days'] ?? 0;
                            $trialAmount = $newPlan->plan_details["inr_trial_price"] ?? 0;
                        } else {
                            $trial_days = $newPlan->plan_details['usd_trial_days'] ?? 0;
                            $trialAmount = $newPlan->plan_details["usd_trial_price"] ?? 0;
                        }
                    }

                    if ($trialAmount > 0) $amount = $trialAmount;
                }
            }
        }

        if ($amount <= 0) return $this->failed(msg: 'Amount Error', datas: ['data' => $planData, "amount" => $amount], showDecoded: true);

        $amount = $amount * $seats;

        $promoCodeId = 0;
        if ($trial_days < 1) {
            $promoCode = $this->cpm($amount, $code, $currency, true);
            if ($promoCode['success']) {
                $amount = $promoCode['amount'];
                $promoCodeId = $promoCode['id'];
            }
        }

        $cheapRate = $user_data->cheap_rate == 1 || $user_data->cheap_rate == "1";
//        if ($subId !== null) $cheapRate = false;
        if ($cheapRate) $amount = 1;

        $eventData = [
            '_fbclid' => $request->cookie('_fbclid'),
            '_caid' => $request->cookie('_caid'),
            '_gclid' => $request->cookie('_gclid'),
            '_wbraid' => $request->cookie('_wbraid'),
            '_gbraid' => $request->cookie('_gbraid'),
            '_ga' => $request->cookie('_ga'),
            '_gcl_au' => $request->cookie('_gcl_au'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'clientIp' => ApiController::findIp($request) ?? '0.0.0.0',
            'url' => $url
        ];

        return $this->successed(datas: [
            'amount' => $amount,
            'payMode' => $payMode,
            'promoCodeId' => $promoCodeId,
            'description' => $description,
            'eventData' => $eventData,
            'assetDetails' => $assetDetails,
            'validity' => $validity,
            'subId' => $subId,
            'isMeta' => $isMeta,
            'trial_days' => $trial_days,
            'planData' => $planData,
            'paymentGateway' => $paymentGateway,
        ], showDecoded: true);
    }

    private function getAssetData($rates, array $assetDetails, $currency, $uid, $offerApplied): array
    {
        $payMode = null;
        $amount = 0;
        $paymentDatas = [];
        /** @var array $assetDetail */
        foreach ($assetDetails as $assetDetail) {
            if ($assetDetail['type'] == 0 || $assetDetail['type'] == '0') {
                if ($assetDetail['id'] != 'draft') {
                    if (!PurchaseHistory::where('user_id', $uid)->where('product_id', $assetDetail['id'])->wherePaymentStatus(1)->exists()) {
                        $desData = Design::where('string_id', $assetDetail['id'])->where('status', 1)->first();
                        if ($desData) {

//                            $containPremium = $desData->is_premium == 1 || $desData->is_freemium == 1;

                            $thumbArray = json_decode($desData->thumb_array);
                            $size = sizeof($thumbArray);

                            $pyt = RateController::getTemplateRates($rates, $size, $desData, /*!$containPremium && */ $offerApplied ? self::$FREE_TEMPLATE_DISCOUNT : 0);
                            $pyt['id'] = $desData->string_id;
                            $pyt['type'] = 0;
                            $paymentDatas[] = $pyt;
                            $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                            if (is_null($payMode)) $payMode = "template";
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Data Error';
                            return $response;
                        }
                    }
                }
            } else if ($assetDetail['type'] == 4 || $assetDetail['type'] == '4') {
                $desData = VideoTemplate::where('string_id', $assetDetail['id'])->first();
                if ($desData) {
                    $size = $desData->pages;
                    $pyt = RateController::getVideoRates($rates, $size);
                    $pyt['id'] = $desData->string_id;
                    $pyt['type'] = 4;
                    $paymentDatas[] = $pyt;
                    $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                    if (is_null($payMode)) $payMode = "video";
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Data Error';
                    return $response;
                }
            } else if ($assetDetail['type'] == 5 || $assetDetail['type'] == '5') {
                $desData = Attire::where('string_id', $assetDetail['id'])->first();
                if ($desData) {
                    $size = $desData->head_count;
                    $pyt = RateController::getCaricatureRates($rates, $size, false, $desData->editor_choice == 1);
                    $pyt['id'] = $desData->string_id;
                    $pyt['type'] = 5;
                    $paymentDatas[] = $pyt;
                    $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                    if (is_null($payMode)) $payMode = "caricature";
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Data Error';
                    return $response;
                }
            } else if ($assetDetail['type'] == 6 || $assetDetail['type'] == '6') {
                $pyt = AICreditController::getData($assetDetail['id']);
                if (!$pyt) {
                    $response['success'] = false;
                    $response['message'] = 'Invalid type';
                    return $response;
                }
                $pyt['id'] = $assetDetail['id'];
                $pyt['type'] = 6;
                $paymentDatas[] = $pyt;
                $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                if (is_null($payMode)) $payMode = "ai_credit";
            } else if ($assetDetail['type'] == 10 || $assetDetail['type'] == '10') {
                $pyt['inrVal'] = self::$BUSINESS_SUPPORT_RATE_INR;
                $pyt['usdVal'] = self::$BUSINESS_SUPPORT_RATE_USD;
                $pyt['inrAmount'] = '₹' . self::$BUSINESS_SUPPORT_RATE_INR;
                $pyt['usdAmount'] = '$' . self::$BUSINESS_SUPPORT_RATE_USD;

                $pyt['id'] = $assetDetail['id'];
                $pyt['type'] = 10;
                $paymentDatas[] = $pyt;
                $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                if (is_null($payMode)) $payMode = "business_support";
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid type';
                return $response;
            }
        }

        if ($amount <= 0 || is_null($payMode)) {
            $response['success'] = false;
            $response['message'] = 'Invalid amount';
        } else {
            $response['success'] = true;
            $response['message'] = $amount;
            $response['datas'] = $paymentDatas;
            $response['payMode'] = $payMode;
        }
        return $response;
    }

    private function cpm($amount, $code, $currency, $provideID = false): array
    {
        if ($amount == null || $code == null) {
            return $this->failed(msg: "Parameters missing!", showDecoded: true);
        }

        if (!is_numeric($amount)) {
            return $this->failed(msg: "Amount invalid!", showDecoded: true);
        }

        $promoData = PromoCode::where('promo_code', $code)->first();

        if (!$promoData || $promoData->status == 0) {
            return $this->failed(msg: "Code is invalid!", showDecoded: true);
        }

        if ($promoData->expiry_date) {
            $expiryDate = Carbon::createFromFormat('Y-m-d', $promoData->expiry_date);

            if ($expiryDate->isPast()) {
                return $this->failed(msg: "Code is expired!", showDecoded: true);
            }
        }

        $isInr = $currency === "INR";

        $curSymbol = $isInr ? '₹' : '$';
        $minimumPurchase = $isInr ? $promoData->min_cart_inr : $promoData->min_cart_usd;
        $discountUpto = $isInr ? $promoData->disc_upto_inr : $promoData->disc_upto_usd;

        if ($amount < $minimumPurchase) {
            return $this->failed(msg: "Minimum order value is $curSymbol$minimumPurchase", showDecoded: true);
        }

        if ($currency === 'INR') $discount = round($amount * $promoData->disc / 100);
        else $discount = $amount * $promoData->disc / 100;

        if ($discountUpto !== 0) $discount = min($discount, $discountUpto);

        $discAmount = $amount - $discount;
        $discAmount = number_format((float)$discAmount, 2, '.', '');

        $response = [
            'discount' => $promoData->disc,
            'amount' => $discAmount,
            'saving' => $discount,
            'msg' => "You have saved ",
            'curSymbol' => $curSymbol,
            'amountStr' => $curSymbol . $discAmount,
            'additional_days' => $promoData->additional_days,
            'code' => $code
        ];

        if ($provideID) $response['id'] = $promoData->id;

        return $this->successed(msg: "Promo code applied", datas: $response, showDecoded: true);
    }

    private function createUserPaymentGatewayRefId(UserData $user_data, mixed $number, PaymentGateway $paymentGateway): string|null
    {

        $cusIdUpdate = false;

        $gatewayType = $paymentGateway->name;
        $gatewayClient = $paymentGateway->client;

        if ($gatewayType === 'razorpay') {
            $paymentGatewayRefId = $user_data->razorpay_cus_id;
        } else if ($gatewayType === 'stripe') {
            $paymentGatewayRefId = $user_data->stripe_cus_id;
        } else {
            $paymentGatewayRefId = $user_data->email;
        }

        if (empty($paymentGatewayRefId)) {
            $cusIdUpdate = true;

            $pattern = '/^[a-zA-Z ]{3,50}$/';
            $customer_name = $user_data->name;

            if (!preg_match($pattern, $customer_name)) {
                $customer_name = "CraftyArt";
            }

            if ($gatewayType === 'razorpay') {
                if (!$user_data->country_code) {
                    $cusRes = $gatewayClient->customer->create(array('fail_existing' => '0', 'name' => $customer_name, 'email' => $user_data->email));
                } else {
                    $cusRes = $gatewayClient->customer->create(array('fail_existing' => '0', 'name' => $customer_name, 'contact' => $user_data->country_code . $user_data->number));
                }
                $paymentGatewayRefId = $cusRes['id'];

            } else if ($gatewayType === 'stripe') {
                if (!$user_data->country_code) {
                    $cusRes = $gatewayClient->customers->create(array('name' => $user_data->name, 'email' => $user_data->email));
                } else {
                    $cusRes = $gatewayClient->customers->create(array('name' => $user_data->name, 'phone' => $user_data->country_code . $user_data->number));
                }
                $paymentGatewayRefId = $cusRes['id'];
            }

            if (empty($paymentGatewayRefId)) return null;
        }

        $numberUpdate = !is_null($number) && is_null($user_data->contact_no);

        if ($cusIdUpdate || $numberUpdate) {
            $res = UserData::find($user_data->id);
            if ($cusIdUpdate) {
                if ($gatewayType === 'razorpay') {
                    $res->razorpay_cus_id = $paymentGatewayRefId;
                } else if ($gatewayType === 'stripe') {
                    $res->stripe_cus_id = $paymentGatewayRefId;
                }
            }
            if ($numberUpdate) {
                $res->contact_no = $number;
                $user_data->contact_no = $number;
            }
            $res->save();
        }

        return $paymentGatewayRefId;
    }

    private static function googleEvent(GoogleEnum $eventName, $assetDetails, $currency_code, $amount, $transaction_id, $detail, $email, $url = null): void
    {
        $ids = self::getIdsData($assetDetails);
        if ($ids) {
            GoogleEvent::trackEvent(
                $eventName,
                [
                    'currency' => $currency_code,
                    'amount' => $amount,
                    'transaction_id' => $transaction_id,
                    'id' => json_encode($ids),
                    'name' => "Templates",
                    'email' => $email,
                    'url' => $url,
                    '_fbclid' => $detail->_fbclid ?? null,
                    '_caid' => $detail->_caid ?? null,
                    '_gclid' => $detail->_gclid ?? null,
                    '_wbraid' => $detail->_wbraid ?? null,
                    '_gbraid' => $detail->_gbraid ?? null,
                    '_ga' => $detail->_ga ?? null,
                    '_gcl_au' => $detail->_gcl_au ?? null,
                    'userAgent' => $detail->userAgent ?? null,
                    'clientIp' => $detail->clientIp ?? null,
                ]
            );
        }
    }

    private static function fbEvent(FacebookEvent $eventName, Request $request, $uid, $assetDetails, $name, $email, $contact, $currency, $amount, $detail, $isOfferPixel, $url): void
    {
        $ids = self::getIdsData($assetDetails);
        if ($ids) {
            FbPixel::purchaseEvent(
                eventName: $eventName,
                request: $request,
                name: $name,
                email: $email,
                phone: $contact,
                url: $url,
                purchaseData: [
                    'uid' => $uid,
                    'currency' => $currency,
                    'value' => $amount,
                    'id' => $ids,
                    '_fbclid' => $detail->_fbclid ?? null,
                    '_caid' => $detail->_caid ?? null,
                    '_gclid' => $detail->_gclid ?? null,
                    '_ga' => $detail->_ga ?? null,
                    '_gcl_au' => $detail->_gcl_au ?? null,
                    'userAgent' => $detail->userAgent ?? null,
                    'clientIp' => $detail->clientIp ?? null,
                ],
                isOfferPixel: $isOfferPixel
            );
        }
    }

    private static function getIdsData($assetDetails): array|null
    {
        $ids = null;
        if (is_numeric($assetDetails)) {
            return [$assetDetails];
        }

        $tempAssetData = json_decode($assetDetails, true);
        if ($tempAssetData && is_array($tempAssetData)) {
            foreach ($tempAssetData as $data) {
                $ids[] = $data['id'];
            }
            return $ids;
        }

        return [$assetDetails];
    }

    public static function getRatesOfTemplates(Request $request, $uid, $ids, $offerApplied): array
    {
        $assetDetails = json_decode($ids);

        if (!$assetDetails || !is_array($assetDetails)) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, "Datas are invalid"));
        }

        $user_data = UserData::where('uid', $uid)->first();

        $ipData = HelperController::getIpAndCountry($request);
        $currency = strtoupper($ipData['cur']);

        $isSubscribed = SubscriptionController::getActivePlan($uid);
        if (!$isSubscribed) {
            if ($user_data) $isSubscribed = DomainChecker::isValidSpecialUser($request, $user_data) == 1;
        }

        if ($request->isTester) {
//            $isSubscribed = false;
        }

        $amount = 0;
        $offeredAmountForWatermark = 0;
        $paymentDatas = [];
        $containPremium = false;
        $rates = RateController::getRates(true);
        $srcset = [];

        if (!$isSubscribed) {
            foreach ($assetDetails as $assetDetail) {
                if (!PurchaseHistory::where('user_id', $uid)->where('product_id', $assetDetail)->wherePaymentStatus(1)->exists() && $assetDetail != 'draft') {
                    $desData = Design::where('string_id', $assetDetail)->where('status', 1)->first();
                    if ($desData) {
                        if (!$containPremium) {
                            $containPremium = $desData->is_premium == 1 || $desData->is_freemium == 1;
                        }
                        $thumbArray = json_decode($desData->thumb_array);
                        $size = sizeof($thumbArray);
                        $data = RateController::getTemplateRates($rates, $size, $desData);
                        $pyt['id'] = $desData->string_id;
                        $pyt['type'] = 0;
                        $pyt['title'] = $desData->post_name;
                        $pyt['image'] = HelperController::$mediaUrl . $thumbArray[0];
                        $pyt['width'] = $desData->width;
                        $pyt['height'] = $desData->height;
                        $pyt['amount'] = $currency == 'INR' ? $data['inrAmount'] : $data['usdAmount'];
                        $pyt['price'] = $currency == 'INR' ? $data['inrVal'] : $data['usdVal'];
                        $paymentDatas[] = $pyt;
                        $srcset[] = HelperController::$mediaUrl . $thumbArray[0];
                        $amount += $currency == 'INR' ? $data['inrVal'] : $data['usdVal'];
                    } else {
                        return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, "Data Error"));
                    }
                }
            }
        }

        $needToPurchase = sizeof($paymentDatas) > 0;

        $symbol = $currency == 'INR' ? '₹' : '$';

        $todayRange = [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
        if (ExportTable::where('uid', $uid)
            ->where('watermark', 1)
            ->whereBetween('created_at', $todayRange)
            ->count() > PlanLimitHelper::$DAILY_FREE_EXPORT_LIMIT) {
            $goWithWatermark = false;
        } else {
            $goWithWatermark = !$containPremium;
        }

        $showOffer = $offerApplied;

        $package = null;
        if ($needToPurchase) {

            $curSymbol = $currency == 'INR' ? '₹' : '$';

            if ($showOffer) {
                $amount = 0;

                foreach ($paymentDatas as &$paymentData) {
                    $rawAmount = $paymentData['price'];

                    if ($currency === 'INR') $discount = round($rawAmount * self::$FREE_TEMPLATE_DISCOUNT / 100);
                    else $discount = $rawAmount * self::$FREE_TEMPLATE_DISCOUNT / 100;

                    $discAmount = $rawAmount - $discount;
                    $amount += $discAmount;

                    if ($currency === 'INR') $discAmount = number_format((float)$discAmount);
                    else $discAmount = number_format((float)$discAmount, 2);

                    $paymentData['amount'] = $curSymbol . $discAmount;

//                    unset($paymentData['price']);
                }
            }

            if (!$offerApplied && $goWithWatermark) {
                foreach ($paymentDatas as &$paymentData) {
                    $rawAmount = $paymentData['price'];
                    if ($currency === 'INR') $discount = round($rawAmount * self::$FREE_TEMPLATE_DISCOUNT / 100);
                    else $discount = $rawAmount * self::$FREE_TEMPLATE_DISCOUNT / 100;

                    $discAmount = $rawAmount - $discount;
                    $offeredAmountForWatermark += $discAmount;
                }
            }

            $column = strtoupper($currency) == "INR" ? 'price' : 'price_dollar';
            $actualColumn = strtoupper($currency) == "INR" ? 'actual_price' : 'actual_price_dollar';
            $currency_symbol = strtoupper($currency) == "INR" ? "₹" : "$";

            $sub = Subscription::whereStatus(1)->orderBy($column)->first();

            if ($sub) {
                $price = round($sub->$column, 2);
                $actual_price = round($sub->$actualColumn, 2);

                $discount = 0;
                $offer_msg = null;
                $has_offer = 0;

                if ($price < $actual_price) {
                    $discount = (int)((($actual_price - $price) / $actual_price) * 100);
                    $offer_msg = "Best Value ({$discount}% off)";
                    $has_offer = 1;
                }

                $package = [
                    'id' => $sub->id,
                    'package_name' => $sub->package_name,
                    'desc' => $sub->desc,
                    'validity' => $sub->validity,
                    'currency' => $currency,
                    'actual_price' => $currency_symbol . $actual_price,
                    'offer_price' => $currency_symbol . $price,
                    'price' => $price,
                    'has_offer' => $has_offer,
                    'offer_msg' => $offer_msg,
                    'discount' => $discount
                ];
            }
        }

//        $offeredAmountForWatermark = $amount;
//        if (!$offerApplied && $goWithWatermark) {
//            if ($currency === 'INR') $discount = round($offeredAmountForWatermark * self::$FREE_TEMPLATE_DISCOUNT / 100);
//            else $discount = $offeredAmountForWatermark * self::$FREE_TEMPLATE_DISCOUNT / 100;
//            $offeredAmountForWatermark = $offeredAmountForWatermark - $discount;
//        }

        $total = $amount;
        $amount = $symbol . $amount;

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "Loaded",
            [
                "data" => [
                    "total" => $total,
                    "currency" => $currency,
                    "amount" => $amount,
                    'datas' => $paymentDatas,
                    'needToPurchase' => $needToPurchase,
                    'goWithWatermark' => $goWithWatermark,
                    'ipData' => $ipData,
                    'package' => $package,
                    'recommended' => PlanLimitHelper::recommendedPlan($request),
                    'title' => 'Attention',
                    'desc' => 'Please subscribe to download this template or Buy this for ' . $amount . '.',
                    'contact_no' => is_null($user_data) ? null : $user_data->contact_no,
                    'offer' => $showOffer,
                    'numberRequired' => true,
                    'watermark_data' => [
                        "price" => $symbol . $offeredAmountForWatermark . "/-",
                        "disc" => self::$FREE_TEMPLATE_DISCOUNT . "%",
                        "msg" => "Unlock premium, watermark-free export — just at ",
                        "ids" => $paymentDatas,
                        "src" => $srcset
                    ]
                ]
            ]
        ));
    }

    public function enterTransData(Request $request, $transaction_id, $method, $currency_code, $isManual, array|null $metaData = null): array
    {
//        if ($method === 'PhonePe') $method = 'phonepe_pg';

        $successRes = ['success' => true, 'msg' => 'Purchase successfully.'];
        $errorRes = ['success' => false, 'msg' => 'Payment not valid.'];

        $paymentGateway = PaymentGateway::initByGateway(strtolower($method), null);
        if (!$paymentGateway) return $this->failed(msg: "Stripe init failed");

        $metaData = $metaData ?? $this->getMetaData($paymentGateway, $transaction_id);

        if (!$metaData['isSuccessed']) return ['success' => false, 'msg' => 'Payment not valid.', 'metaData' => $metaData];

        $user_data = $metaData['user_data'];
        $transaction_id = $metaData['transaction_id'];

        if (VideoPurchaseHistory::whereTransactionId($transaction_id)->exists()) return $successRes;
        if (PurchaseHistory::whereTransactionId($transaction_id)->exists()) return $successRes;
        if (CaricaturePurchaseHistory::whereTransactionId($transaction_id)->exists()) return $successRes;
        if ($tData = TransactionLog::whereTransactionId($transaction_id)->first()) {
            return ['success' => true, 'msg' => 'Purchase successfully.', 'is_trial' => $tData->is_trial == 1, 'days' => $tData->validity];
        }

        $cheap_rate = $user_data->cheap_rate == 1 || $user_data->cheap_rate == "1";

        $promo_code_id = $metaData['promoCodeId'];
        $totalPaidAmount = $metaData['paidAmount'];
        $totalNetAmount = $metaData['netAmount'];
        $feePercentage = $metaData['feePercentage'];
        $exchangeRate = $metaData['exchange_rate'];
        $fees = $metaData['fees'];
        $details = $metaData['details'];
        $subscriptionId = $metaData['subscriptionId'];
        $sales_person_id = $metaData['sales_person_id'];
        $sales_person_id_for_report = $metaData['sales_person_id_for_report'];

        if ($totalNetAmount < 0 && !$cheap_rate) return $errorRes;

        $eventData = is_array($details->eventData) ? $details->eventData : json_decode($details->eventData);
        $fbcId = $eventData->_fbclid ?? null;
        $gclId = $eventData->_gclid ?? $eventData->_wbraid ?? $eventData->_gbraid ?? null;
        $url = $eventData->url ?? null;

        $name = $metaData['name'];
        $email = $metaData['email'];
        $contact = $metaData['contact'];

        $currency_code = $details->currency ?? $currency_code;
        $fromWhere = $details->from ?? 'Web';

        $caricatures = $details->caricatures ?? $details->caricature ?? 0;
        $seats = $details->seats ?? 1;

        $assetDetails = json_decode($details->plan_id);

        $callFBEvents = false;
        $isOfferPixel = false;

        if ($assetDetails && !is_numeric($assetDetails)) {

            foreach ($assetDetails as $assetDetail) {

                $paidAmount = $assetDetail->inrVal;

                if (strtoupper($currency_code) != "INR") $paidAmount = $assetDetail->usdVal * $exchangeRate;

                $netAmount = $paidAmount - ($paidAmount * $feePercentage / 100);

                $this->savePurchaseData(
                    user_data: $user_data,
                    contact: $contact,
                    assetDetail: $assetDetail,
                    transaction_id: $transaction_id,
                    currency_code: $currency_code,
                    paidAmount: $paidAmount,
                    netAmount: $netAmount,
                    promo_code_id: $promo_code_id,
                    method: $method,
                    fromWhere: $fromWhere,
                    isManual: $isManual,
                    cheap_rate: $cheap_rate,
                    fbcId: $fbcId,
                    gclId: $gclId,
                    sales_person_id_for_report: $sales_person_id_for_report,
                );
            }

        } else {
            $saveData = $this->saveSubsData(
                user_data: $user_data,
                plan_id: $details->plan_id,
                totalPaidAmount: $totalPaidAmount,
                totalNetAmount: $totalNetAmount,
                contact: $contact,
                transaction_id: $transaction_id,
                subscriptionId: $subscriptionId,
                currency_code: $currency_code,
                promo_code_id: $promo_code_id,
                paymentGateway: $paymentGateway,
                fromWhere: $fromWhere,
                isManual: $isManual,
                cheap_rate: $cheap_rate,
                fbcId: $fbcId,
                gclId: $gclId,
                url: $url,
                caricatures: $caricatures,
                seats: $seats,
                sales_person_id: $sales_person_id,
                sales_person_id_for_report: $sales_person_id_for_report,
            );

            if (!$saveData['success']) return $saveData;

            $successRes['is_trial'] = $saveData['is_trial'];
            $successRes['days'] = $saveData['days'];
            $isOfferSub = $saveData['is_offer_sub'];

            if (in_array($details->plan_id, self::$OFFER_IDS) || $isOfferSub) {
                $isOfferPixel = true;
                if (TransactionLog::whereUserId($user_data->uid)->count() == 1) {
                    WhatsAppService::sendTemplateMessageFromCustomCrm(
                        campaignName: "account_registration",
                        userName: $user_data->name,
                        mobile: $user_data->contact_no,
                        templateParams: [$user_data->name, $user_data->email],
                        ctaButtons: [
                            [
                                "type" => "button",
                                "sub_type" => "url",
                                "index" => 0,
                                "parameters" => [
                                    [
                                        "type" => "text",
                                        "text" => "invitation"
                                    ]
                                ],
                            ]
                        ]
                    );
                }
            }

            $isByOffice = $saveData['is_e_mandate'] || $saveData['by_sales_team'];

            if (
                !empty($metaData['order']) && 
                !empty($metaData['order']->fbc) && 
                (in_array($assetDetails, self::$OFFER_IDS) || $isOfferSub) && !$isByOffice
            ) {
                $callFBEvents = true;
            }

//            if ($user_data->uid === 'YTC1UOvR05hSKSkJSXFnb6LUFAi1') {
//                EmailController::sendWhatsappTemplateMessage($user_data);
//            }
        }

        
        if ($callFBEvents) {
            self::fbEvent(
            /*is_numeric($details->plan_id) ? FacebookEvent::SUBSCRIBE :*/ 
                FacebookEvent::PURCHASE,
                $request, 
                $user_data->uid, 
                $details->plan_id, 
                $name, 
                $email, 
                $contact, 
                "INR", 
                // /*$isOfferPixel ? 299 :*/
                $totalPaidAmount, 
                $eventData, 
                $isOfferPixel,
                $url
            );

//            self::fbEvent(FacebookEvent::SELLING, $request, $user_data->uid, $details->plan_id, $name, $email, $contact, "INR", /*$isOfferPixel ? 299 :*/ $totalPaidAmount, $eventData, $isOfferPixel);

            if ($isOfferPixel) {
                self::googleEvent(
                    eventName: GoogleEnum::PURCHASE,
                    assetDetails: $details->plan_id,
                    currency_code: "INR",
                    amount: 299,
                    transaction_id: $transaction_id,
                    detail: $eventData,
                    email: $email,
                    url: $url);
            }

//        self::googleEvent(GoogleEvent::$SELLING, $details->plan_id, "INR", $totalPaidAmount, $transaction_id, $eventData);
        }

        $successRes['taData'] = $metaData;
        return $successRes;
    }

    public function getMetaData(PaymentGateway $paymentGateway, $transaction_id): array
    {
        $isSuccessed = false;
        $promoCodeId = 0;
        $paidAmount = 0;
        $netAmount = 0;
        $feePercentage = 0;
        $details = null;
        $name = $user_data->name ?? null;
        $email = $user_data->email ?? null;
        $contact = null;
        $error = null;
        $uid = null;
        $sales_person_id_for_report = 0;
        $sales_person_id = 0;
        $subscriptionId = null;
        $paymentIntentId = null;
        $exchange_rate = 1;
        $fees = 0;
        $orderData = null;

        try {
            if (strtolower($paymentGateway->name) === 'stripe') {

                /** @var StripeClient $stripeClient */
                $stripeClient = $paymentGateway->client;

                if (str_starts_with($transaction_id, 'pi_')) {
                    $paymentIntent = $stripeClient->paymentIntents->retrieve($transaction_id);
                    if ($paymentIntent && isset($paymentIntent->latest_charge)) {
                        $charge = $stripeClient->charges->retrieve($paymentIntent->latest_charge);
                        $transaction_id = $charge->balance_transaction;
                    }
                }

                $transaction = $stripeClient->balanceTransactions->retrieve($transaction_id);
                if ($transaction) {

                    if (isset($transaction['source'])) {

                        $exchange_rate = $transaction->exchange_rate ?? 1;
                        $fees = ($transaction->fee / 100) ?? 0;

                        $charge = $stripeClient->charges->retrieve($transaction['source']);
                        $isSuccessed = !($charge->amount_refunded > 0);

                        $paidAmount = $transaction['amount'] / 100;
                        $netAmount = $transaction['net'] / 100;

                        if ($paidAmount > 0) {
                            $feePercentage = (($paidAmount - $netAmount) / $paidAmount) * 100;
                        }

                        $metadata = $charge->metadata->toArray();
                        $craftyId = $metadata['craftyId'] ?? null;

                        $query = Order::whereStripeTxnId($transaction_id)->orWhere('payment_id', $transaction_id);
                        if (!empty($craftyId)) $query->orWhere('crafty_id', $craftyId);
                        $orderData = $query->first();

                        if ($orderData) {
                            $subscriptionId = str_starts_with($orderData->stripe_payment_intent_id, "sub_") ? $orderData->stripe_payment_intent_id : null;
                            $notes = $orderData->raw_notes;
                            if (isset($notes['code'])) {
                                $promoCodeId = $notes['code'];
                            }
                            $uid = $notes['user_id'] ?? $orderData->user_id;
                            $details = json_decode(json_encode($notes));
                            $sales_person_id_for_report = $orderData->emp_id;
                        }

                        $paymentMethod = $stripeClient->paymentMethods->retrieve($charge['payment_method']);

                        $paymentIntentId = $charge->payment_intent;
                        $name = $paymentMethod->billing_details->name ?? $name;
                        $email = $paymentMethod->billing_details->email ?? $email;
                        $contact = $paymentMethod->billing_details->phone ?? $contact;
                    }
                }
            } else if (strtolower($paymentGateway->name) === 'razorpay') {

                /** @var Api $stripeClient */
                $razorpay = $paymentGateway->client;

                $transaction = $razorpay->payment->fetch($transaction_id);
                if ($transaction && ($transaction['status'] == 'authorized' || $transaction['status'] == 'captured')) {

                    $isSuccessed = !($transaction['amount_refunded'] > 0);

//                    $isSuccessed = true;
                    $paidAmount = $transaction['amount'] / 100;
                    $netAmount = ($transaction['amount'] - $transaction['fee'] - $transaction['tax']) / 100;

                    $fees = $transaction['fee'] + $transaction['tax'];

                    if ($paidAmount > 0) {
                        $feePercentage = (($paidAmount - $netAmount) / $paidAmount) * 100;
                    }

                    $notes = $payment['notes'] ?? [];
                    $craftyId = $notes['craftyId'] ?? null;

                    $query = Order::whereRazorpayPaymentId($transaction_id)->orWhere('payment_id', $transaction_id);
                    if (!empty($craftyId)) $query->orWhere('crafty_id', $craftyId);
                    $orderData = $query->first();

                    if ($orderData) {
                        $subscriptionId = str_starts_with($orderData->razorpay_order_id, "sub_") ? $orderData->razorpay_order_id : $orderData->subscription_id;
                        $notes = $orderData->raw_notes;
                        if (isset($notes['code'])) {
                            $promoCodeId = $notes['code'];
                        }
                        $uid = $notes['user_id'] ?? null;
                        $sales_person_id = $notes['sales_person_id'] ?? 0;
                        $details = json_decode(json_encode($notes));

                        if (empty($sales_person_id)) $sales_person_id_for_report = $orderData->emp_id;
                    }

                    $email = $transaction['email'] ?? $email;
                    $contact = $transaction['contact'] ?? $contact;
                }

            } else if (strtolower($paymentGateway->name) === 'phonepe_pg') {

                /** @var StandardCheckoutClient $phonePePaymentsClient */
                $phonePePaymentsClient = $paymentGateway->client;

                try {
                    $paymentData = $paymentGateway->getOrderStatus($transaction_id);
                    if (!empty($paymentData) && !is_string($paymentData)) {
                        if ($paymentData['state'] === 'COMPLETED') {
                            $isSuccessed = true;
                            $paidAmount = $paymentData['amount'] / 100;
                            $netAmount = $paidAmount;

                            $query = Order::whereOrderId($transaction_id)->orWhere('crafty_id', $transaction_id);
                            $orderData = $query->first();

                            if ($orderData) {
                                $subscriptionId = $orderData->subscription_id;
                                $notes = $orderData->raw_notes;
                                if (isset($notes['code'])) {
                                    $promoCodeId = $notes['code'];
                                }
                                $uid = $notes['user_id'] ?? null;
                                $sales_person_id = $notes['sales_person_id'] ?? 0;
                                $details = json_decode(json_encode($notes));
                                if (empty($sales_person_id)) $sales_person_id_for_report = $orderData->emp_id;
                            }
                        }
                    } else {
                        $statusCheckResponse = $phonePePaymentsClient->getOrderStatus($transaction_id, true);
                        if ($statusCheckResponse->getState() === 'COMPLETED') {
                            $isSuccessed = true;
                            $paidAmount = $statusCheckResponse->getAmount() / 100;
                            $netAmount = $paidAmount;

                            $query = Order::whereOrderId($transaction_id)->orWhere('crafty_id', $transaction_id);
                            $orderData = $query->first();

                            if ($orderData) {
                                $notes = $orderData->raw_notes;
                                if (isset($notes['code'])) {
                                    $promoCodeId = $notes['code'];
                                }
                                $uid = $notes['user_id'] ?? null;
                                $sales_person_id = $notes['sales_person_id'] ?? 0;
                                $details = json_decode(json_encode($notes));
                                if (empty($sales_person_id)) $sales_person_id_for_report = $orderData->emp_id;
                            }
                        }
                    }
                } catch (PhonePeException $e) {
                    $error = $e->getMessage();
                    $isSuccessed = false;
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $user_data = UserData::where("uid", $uid)->first();
        if ($user_data) {
            $name = $name ?? $user_data->name;
            $email = $email ?? $user_data->email;
            $contact = $contact ?? $user_data->contact_no;
        } else {
            $isSuccessed = false;
        }

        if (empty($sales_person_id)) $sales_person_id = 0;

        return [
            'isSuccessed' => $isSuccessed,
            'promoCodeId' => $promoCodeId,
            'transaction_id' => $transaction_id,
            'paidAmount' => $paidAmount,
            'netAmount' => $netAmount,
            'feePercentage' => $feePercentage,
            'exchange_rate' => $exchange_rate,
            'fees' => $fees,
            'details' => $details,
            'name' => $name,
            'email' => $email,
            'contact' => $contact,
            'error' => $error,
            'user_data' => $user_data,
            'subscriptionId' => $subscriptionId,
            'paymentMethodId' => $paymentIntentId,
            'method' => $paymentGateway->name,
            'sales_person_id' => $sales_person_id,
            'sales_person_id_for_report' => $sales_person_id_for_report,
            'orderData' => $statusCheckResponse ?? null,
            '$transaction' => $transaction ?? null,
            '$charge' => $charge ?? null,
            'order' => $orderData
        ];
    }

    private function savePurchaseData(
        UserData $user_data,
                 $contact,
                 $assetDetail,
                 $transaction_id,
                 $currency_code,
                 $paidAmount,
                 $netAmount,
                 $promo_code_id,
                 $method,
                 $fromWhere,
                 $isManual,
                 $cheap_rate,
                 $fbcId,
                 $gclId,
                 $sales_person_id_for_report): void
    {

        if ($assetDetail->type == 0 || $assetDetail->type == '0') $modelClass = PurchaseHistory::class;
        else if ($assetDetail->type == 4 || $assetDetail->type == '4') $modelClass = VideoPurchaseHistory::class;
        else if ($assetDetail->type == 5 || $assetDetail->type == '5') $modelClass = CaricaturePurchaseHistory::class;
        else if ($assetDetail->type == 6 || $assetDetail->type == '6') $modelClass = AIPurchaseHistory::class;
        else if ($assetDetail->type == 10 || $assetDetail->type == '10') $modelClass = BusinessSupportPurchaseHistory::class;
        else return;

        if ($modelClass::where('transaction_id', $transaction_id)->where('product_id', $assetDetail->id)->exists()) return;

        $payment_id = self::generateID();
        while ($modelClass::where('payment_id', $payment_id)->exists()) {
            $payment_id = self::generateID();
        }

        if (!strcasecmp($currency_code, "INR")) {
            $currency_code = "INR";
            $amount = $assetDetail->inrVal;
        } else {
            $currency_code = "USD";
            $amount = $assetDetail->usdVal;
        }

        $lockKey = "save_data_{$transaction_id}_{$assetDetail->id}";
        $lock = Cache::lock($lockKey, 1); // Lock for 10 seconds

        try {
            if ($lock->get()) {
                if ($modelClass::where('transaction_id', $transaction_id)->where('product_id', $assetDetail->id)->exists()) return;
                $modelClass::firstOrCreate(
                    [
                        'transaction_id' => $transaction_id,
                        'product_id' => $assetDetail->id,
                    ],
                    [
                        'user_id' => $user_data->uid,
                        'contact_no' => $contact,
                        'product_id' => $assetDetail->id,
                        'product_type' => $assetDetail->type,
                        'order_id' => null,
                        'transaction_id' => $transaction_id,
                        'payment_id' => $payment_id,
                        'currency_code' => $currency_code,
                        'amount' => $amount,
                        'paid_amount' => $paidAmount,
                        'net_amount' => $netAmount,
                        'promo_code_id' => $promo_code_id,
                        'payment_method' => $method,
                        'from_where' => $fromWhere,
                        'fbc' => $fbcId,
                        'gclid' => $gclId,
                        'isManual' => $isManual,
                        'payment_status' => 1,
                        'status' => 1,
                    ]
                );

                if ($assetDetail->type == 0 || $assetDetail->type == '0') {
                    if (empty($user_data->contact_no)) $user_data->contact_no = $contact;
                    EmailController::sendInstantTemplatePurchaseMessage($user_data);
                    $design = Design::whereStringId($assetDetail->id)->first();
                    if ($design) {

                        $rates = RateController::getRates();
                        $payment = RateController::getCaricatureRates($rates, 2, false, false);
                        $credits = (count($design->caricature_ids) * $payment['inrVal']);
                        if ($credits) {
                            $resTrans = new AICreditTransaction();
                            $resTrans->user_id = $user_data->uid;
                            $resTrans->ref_id = $payment_id;
                            $resTrans->txn_id = AICreditTransaction::generateTxnId();
                            $resTrans->type = 'template_purchase';
                            $resTrans->reason = "Template Purchased";
                            $resTrans->credited = $credits;
                            $resTrans->save();
                        }

                        $user_data->ai_credit = $user_data->ai_credit + $credits;
                        $user_data->save();
                    }
                }

                if ($assetDetail->type == 6 || $assetDetail->type == '6') {

                    $resTrans = new AICreditTransaction();
                    $resTrans->user_id = $user_data->uid;
                    $resTrans->ref_id = $payment_id;
                    $resTrans->txn_id = AICreditTransaction::generateTxnId();
                    $resTrans->type = 'purchase';
                    $resTrans->reason = "Purchased";
                    $resTrans->credited = (int)$assetDetail->id;
                    $resTrans->save();

                    $user_data->ai_credit = $user_data->ai_credit + (int)$assetDetail->id;
                    $user_data->save();
                }

                MasterPurchaseHistory::firstOrCreate(
                    [
                        'transaction_id' => $transaction_id,
                        'product_id' => $assetDetail->id,
                    ],
                    [
                        'user_id' => $user_data->uid,
                        'emp_id' => $sales_person_id_for_report,
                        'contact_no' => $contact,
                        'product_id' => $assetDetail->id,
                        'product_type' => MasterPurchaseHistory::$types[$assetDetail->type],
                        'transaction_id' => $transaction_id,
                        'payment_id' => $payment_id,
                        'currency_code' => $currency_code,
                        'amount' => $amount,
                        'paid_amount' => $paidAmount,
                        'net_amount' => $netAmount,
                        'promo_code_id' => $promo_code_id,
                        'payment_method' => $method,
                        'from_where' => $fromWhere,
                        'fbc' => $fbcId,
                        'gclid' => $gclId,
                        'isManual' => $isManual,
                        'status' => 1,
                    ]
                );
            }

        } catch (Exception $e) {

        }
    }

    private function saveSubsData(
        UserData       $user_data,
                       $plan_id,
                       $totalPaidAmount,
                       $totalNetAmount,
                       $contact,
                       $transaction_id,
                       $subscriptionId,
                       $currency_code,
                       $promo_code_id,
        PaymentGateway $paymentGateway,
                       $fromWhere,
                       $isManual,
                       $cheap_rate,
                       $fbcId,
                       $gclId,
                       $url,
                       $caricatures,
                       $seats,
                       $sales_person_id,
                       $sales_person_id_for_report
    ): array
    {

        $already = TransactionLog::whereUserId($user_data->uid)->whereIn('plan_id', self::$OFFER_IDS)->exists();
        $is_e_mandate = TransactionLog::whereSubscriptionId($subscriptionId)->whereNotNull('subscription_id')->exists();

        $trial_days = 0;
        $is_trial = false;
        $cancellationReason = json_encode(["Change Plan"]);

        $promoData = PromoCode::find($promo_code_id);

        $isInr = strtoupper($currency_code) == "INR";
        $currency_code = $isInr ? 'INR' : 'USD';
        $plan_limit = [];

        $newPayMode = TransactionLog::$OFFER_PLAN;
        $newPlan = OfferPackage::with(['plan', 'duration'])->whereStringId($plan_id)->first();
        if (!$newPlan) {
            $newPayMode = TransactionLog::$NEW_PLAN;
            $newPlan = SubPlan::with(['plan', 'duration'])->whereStringId($plan_id)->first();
        }
        if ($newPlan && $newPlan->plan && $newPlan->duration && !array_diff(SubPlan::$PLAN_KEYS, array_keys($newPlan->plan_details))) {
            $validity = $newPlan->duration->duration + $newPlan->plan_details['additional_duration'];
            $price = $isInr ? $newPlan->plan_details['inr_offer_price'] : $newPlan->plan_details['usd_offer_price'];
            $type = $newPayMode;

            $plan_limit = $newPlan->plan->plan_details;
            if (!$is_e_mandate && ($newPlan->subscription_ids[$paymentGateway->name] ?? null)) {
                if ($isInr) {
                    $trial_days = $newPlan->plan_details['inr_trial_days'] ?? 0;
                } else {
                    $trial_days = $newPlan->plan_details['usd_trial_days'] ?? 0;
                }
            }
            $is_trial = $trial_days > 0;
        } else {
            $subData = Subscription::find($plan_id);
            if (!$subData) return ["success" => false, "msg" => "plan not found"];
            $validity = $subData->validity;
            $trial_days = $subData->trial_days;
            $price = $isInr ? $subData->price : $subData->price_dollar;
            $type = TransactionLog::$OLD_PLAN;
            if (!$already && $isInr) $is_trial = $trial_days > 0;
        }

        if (!empty($plan_limit) && $is_trial) {
            $plan_limit = array_filter($plan_limit, function ($item) {
                return $item['slug'] !== 'ai_credit';
            });
            $plan_limit = array_values($plan_limit);
        }

        $plan_limit[] = [
            "sub_name" => "Device Limit",
            "slug" => "device_limit",
            "access_mode" => "lifetime",
            "limit" => $seats,
            "meta_value" => 1
        ];

        if ($promoData && empty($subscriptionId)) $validity = $validity + $promoData->additional_days;

        if ($tData = TransactionLog::whereTransactionId($transaction_id)->first()) {
            if ($tData->is_trial == 1) return ['success' => true, 'msg' => 'Purchase successfully.', 'is_trial' => true, 'days' => $tData->validity];
            return ['success' => true, 'msg' => 'Purchase successfully.'];
        }

        $payment_id = self::generateID();
        while (TransactionLog::where('payment_id', $payment_id)->exists()) {
            $payment_id = self::generateID();
        }

        if ($is_trial) $validity = $trial_days;

        $total_count = 0;
        $nextAmount = 0;
        $sub_plan_id = null;
        if (!empty($subscriptionId)) {
            try {
                if (strtolower($paymentGateway->name) === 'razorpay') {
                    /** @var Api $razorpay */
                    $razorpay = $paymentGateway->client;

                    $data = $razorpay->subscription->fetch($subscriptionId)->toArray();
                    $sub_plan_id = $data['plan_id'];
                    $planData = $razorpay->plan->fetch($data['plan_id'])->toArray();
                    $nextAmount = $planData['item']['amount'] / 100;
                    $total_count = $data['total_count'];
                } else if (strtolower($paymentGateway->name) === 'stripe') {
                    /** @var StripeClient $stripeClient */
                    $stripeClient = $paymentGateway->client;

                    $data = $stripeClient->subscriptions->retrieve($subscriptionId);
                    $sub_plan_id = $data['plan']['id'];
                    $nextAmount = $data['plan']['amount'] / 100;
                } else if (strtolower($paymentGateway->name) === 'phonepe_pg') {
                    $nextAmount = $price;
                    $sub_plan_id = $plan_id;
                }
            } catch (Exception $e) {

            }
        }

        $startDate = Carbon::now();
        $endDate = Carbon::parse(Carbon::now())->addDays($validity);

        try {
            TransactionLog::firstOrCreate(
                ['transaction_id' => $transaction_id],
                [
                    'plan_id' => $plan_id,
                    'emp_id' => $sales_person_id_for_report,
                    'by_sales_team' => empty($sales_person_id) ? 0 : 1,
                    'subscription_id' => $subscriptionId,
                    'subscription_is_active' => empty($subscriptionId) ? 0 : 1,
                    'user_id' => $user_data->uid,
                    'contact_no' => $contact,
                    'order_id' => null,
                    'transaction_id' => $transaction_id,
                    'payment_id' => $payment_id,
                    'currency_code' => $currency_code,
                    'price_amount' => $price,
                    'paid_amount' => $totalPaidAmount,
                    'net_amount' => $totalNetAmount,
                    'next_amount' => $nextAmount,
                    'coins' => 0,
                    'discount' => 0,
                    'promo_code_id' => $promo_code_id,
                    'payment_method' => $paymentGateway->name,
                    'from_where' => $fromWhere,
                    'fbc' => $fbcId,
                    'gclid' => $gclId,
                    'isManual' => $isManual,
                    'validity' => $validity,
                    'yearly' => $validity >= 365 ? 1 : 0,
                    'plan_limit' => json_encode($plan_limit),
                    'type' => $type,
                    'is_trial' => $is_trial ? 1 : 0,
                    'is_e_mandate' => $is_e_mandate ? 1 : 0,
                    'url' => $url,
                    'payment_status' => 1,
                    'status' => 1,
                    'expired_at' => $endDate,
                ]
            );

            MasterPurchaseHistory::firstOrCreate(
                ['transaction_id' => $transaction_id],
                [
                    'user_id' => $user_data->uid,
                    'emp_id' => $sales_person_id_for_report,
                    'by_sales_team' => empty($sales_person_id) ? 0 : 1,
                    'contact_no' => $contact,
                    'product_type' => MasterPurchaseHistory::$sub_type[$type],
                    'product_id' => $plan_id,
                    'subscription_id' => $subscriptionId,
                    'subscription_is_active' => empty($subscriptionId) ? 0 : 1,
                    'transaction_id' => $transaction_id,
                    'payment_id' => $payment_id,
                    'currency_code' => $currency_code,
                    'amount' => $price,
                    'paid_amount' => $totalPaidAmount,
                    'net_amount' => $totalNetAmount,
                    'next_amount' => $nextAmount,
                    'promo_code_id' => $promo_code_id,
                    'payment_method' => $paymentGateway->name,
                    'from_where' => $fromWhere,
                    'fbc' => $fbcId,
                    'gclid' => $gclId,
                    'isManual' => $isManual,
                    'validity' => $validity,
                    'yearly' => $validity >= 365 ? 1 : 0,
                    'plan_limit' => json_encode($plan_limit),
                    'is_trial' => $is_trial ? 1 : 0,
                    'is_e_mandate' => $is_e_mandate ? 1 : 0,
                    'url' => $url,
                    'status' => 1,
                    'expired_at' => $endDate,
                ]
            );

            if (!empty($subscriptionId) && $paymentGateway->name == "phonepe_pg") {
                PreDebitNotificationJob::dispatch($subscriptionId, $nextAmount, true)->delay(now()->addDays($validity - 1));
            }

            $models = [
                TransactionLog::class,
                MasterPurchaseHistory::class
            ];

            foreach ($models as $model) {
                /** @var class-string<TransactionLog|MasterPurchaseHistory> $model */
                $latest = $model::where('user_id', $user_data->uid)
                    ->latest('id') // or 'created_at' if you use timestamps
                    ->first();

                $model::where('user_id', $user_data->uid)
                    ->where('id', '!=', optional($latest)->id) // optional handles null case
                    ->update(['status' => '0']);

                $model::where('user_id', $user_data->uid)
                    ->where('id', '!=', optional($latest)->id) // optional handles null case
                    ->whereNotNull('subscription_id')
                    ->where('subscription_id', $subscriptionId) // optional handles null case
                    ->update(['subscription_is_active' => '0', 'cancellation_reason' => json_encode(["recurred"])]);
            }

            $user_data->is_premium = "1";
            $user_data->save();

//            $oldDatas = TransactionLog::where('user_id', $user_data->uid)
//                ->where('subscription_is_active', 1)
//                ->whereNotNull('subscription_id')
//                ->where('subscription_id', "!=", $subscriptionId)
//                ->get();

//            $razorpayGateway = PaymentGateway::initByGateway('razorpay', null);
//            $stripeGateway = PaymentGateway::initByGateway('stripe', null);
//            $phonepeGateway = PaymentGateway::initByGateway('phonepe_pg', null);

//            foreach ($oldDatas as $oldData) {
//                if ($oldData->payment_method == "razorpay") {
////                    $errorMsg = RazorpayGateway::cancelSubscription($razorpayGateway, $oldData->subscription_id);
//                } else if ($oldData->payment_method == "stripe") {
////                    $errorMsg = StripeGateway::cancelSubscription($stripeGateway, $oldData->subscription_id);
//                } else if ($oldData->payment_method == "phonepe_pg") {
////                    $errorMsg = PhonepeGateway::cancelSubscription($phonepeGateway, $oldData->subscription_id);
//                }
//
//                if (empty($errorMsg)) {
//                    $oldData->subscription_is_active = 0;
//                    $oldData->cancellation_reason = $cancellationReason;
//                    $oldData->subscription_status = 'cancelled';
//                    $oldData->save();
//                }
//            }

            MasterPurchaseHistory::where('user_id', $user_data->uid)
                ->where('subscription_is_active', 1)
                ->whereNotNull('subscription_id')
                ->where('subscription_id', "!=", $subscriptionId)
                ->update(['subscription_is_active' => 0, 'cancellation_reason' => $cancellationReason, 'subscription_status' => 'cancelled']);

            $aiCredits = [];
            if ($caricatures = (int)$caricatures) {
                $aiCredits[] = [
                    'value' => $caricatures,
                    'multiplier' => RateController::getCaricatureRates([], 2, false, false)['inrVal']
                ];
            }

            if ($aiCreditObject = collect($plan_limit)->firstWhere('slug', 'ai_credit')) {
                $aiCredits[] = [
                    'value' => $aiCreditObject['limit'] * $seats,
                    'multiplier' => 1
                ];
            }

            $totalCredits = 0;
            foreach ($aiCredits as $aiCredit) {
                $resTrans = new AICreditTransaction();
                $resTrans->user_id = $user_data->uid;
                $resTrans->ref_id = $payment_id;
                $resTrans->txn_id = AICreditTransaction::generateTxnId();
                $resTrans->type = 'subscription';
                $resTrans->reason = "Subscription Plan Purchased";
                $resTrans->credited = $aiCredit['value'] * $aiCredit['multiplier'];
                $resTrans->save();
                $totalCredits += $aiCredit['value'] * $aiCredit['multiplier'];
            }

            if ($totalCredits) $user_data->increment('ai_credit', $totalCredits);

            MasterPurchaseHistory::whereUserId($user_data->uid)->update(['total_purchases' => MasterPurchaseHistory::whereUserId($user_data->uid)->count()]);

            if (!empty($subscriptionId) && !empty($sub_plan_id)) {

                if (!UserSubscriptions::whereGatewaySubscriptionId($subscriptionId)->exists()) {
                    $res = new UserSubscriptions();
                    $res->user_id = $user_data->uid;
                    $res->plan_id = $sub_plan_id;
                    $res->payment_gateway = strtolower($paymentGateway->name);
                    $res->gateway_subscription_id = $subscriptionId;
                    $res->currency = $currency_code;
                    $res->amount = $nextAmount;
                    $res->status = "active";
                    $res->is_trial = $is_trial ? 1 : 0;
                    $res->trial_start = $is_trial ? $startDate : null;
                    $res->trial_end = $is_trial ? $endDate : null;
                    $res->current_start = $startDate;
                    $res->current_end = $endDate;
                    $res->total_count = $total_count;
                    $res->paid_count = MasterPurchaseHistory::whereSubscriptionId($subscriptionId)->count();
                    $res->save();
                } else {
                    UserSubscriptions::whereGatewaySubscriptionId($subscriptionId)->update(['paid_count' => MasterPurchaseHistory::whereSubscriptionId($subscriptionId)->count()]);
                }
            }

        } catch (Exception $e) {
            return ["success" => false, "msg" => $e->getMessage(), "is_e_mandate" => false, "by_sales_team" => false, "is_offer_sub" => false];
        }

        $isOfferSub = MasterPurchaseHistory::$sub_type[$type] == 'offer_sub';
        return ["success" => true, "msg" => "done", "is_e_mandate" => $is_e_mandate, "by_sales_team" => !empty($sales_person_id), 'is_trial' => $is_trial, 'days' => $validity, "is_offer_sub" => $isOfferSub];
    }

    public static function generateID($length = 10): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return 'crafty_' . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    public static function removeOrdersDuplicate(Order $order): void
    {
        Order::whereUserId($order->user_id)->whereIn('status', ['pending', 'failed'])->update(['status' => 'override']);
    }
}

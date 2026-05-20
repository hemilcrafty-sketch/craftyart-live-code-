<?php

namespace App\Http\Controllers;

use App\Models\PaymentMetadata;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\Env;
use PhonePe\payments\v1\models\request\builders\InstrumentBuilder;
use PhonePe\payments\v1\models\request\builders\PgPayRequestBuilder;
use PhonePe\payments\v1\PhonePePaymentClient;
//use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
//use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;

class PhonePayPaymentController extends AppBaseController
{
//    public $clientId = 'TEST-M22EOXLUSO1LA_25042';
//    public $clientSecret = 'YzhhMmRiYmEtYjg0ZS00YTMwLWEyOGQtNmU3YzVhMzc5ZTc1';
//    public $clientVersion = '1';
//    public $saltKey = '96434309-7796-489d-8924-ab56988a6076';
//    public $saltIndex = 1;

    public $clientId = 'PGTESTPAYUAT86';
    public $clientSecret = 'YzhhMmRiYmEtYjg0ZS00YTMwLWEyOGQtNmU3YzVhMzc5ZTc1';
    public $clientVersion = '1';
    public $saltKey = '96434309-7796-489d-8924-ab56988a6076';
    public $saltIndex = 1;
    protected $phonePePaymentsClient;
    public function __construct()
    {
        parent::__construct();
        $this->phonePePaymentsClient = new PhonePePaymentClient(
            $this->clientId,
            $this->saltKey,
            $this->saltIndex,
            Env::UAT,
            true
        );
    }

    /*public function index(): void
    {
        $client = StandardCheckoutClient::getInstance(
            $this->clientId,
            $this->clientVersion,
            $this->saltKey,
            Env::PRODUCTION
        );
        $payRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId("DEMO_1234")
            ->amount(1000)
            ->redirectUrl("https://craftyartapp.com")
            ->message("Please do payment for your order")  //Optional Message
            ->build();

        try {
            $payResponse = $client->pay($payRequest);

            if ($payResponse->getState() === "PENDING") {
                header("Location: " . $payResponse->getRedirectUrl());
                exit();
            } else {
                // Handle the error (e.g., display an error message)
                echo "Payment initiation failed: " . $payResponse->getState();
            }
            dd($payResponse);
        } catch (PhonePeException $e) {
            // Handle exceptions (e.g., log the error)
            echo "Error initiating payment: " . $e->getMessage();
        }
    }*/



    public function index() {


//        $merchantTransactionID = bin2hex(random_bytes(7)) . Carbon::now()->timestamp;
//        $request = PgPayRequestBuilder::builder()
//            ->callbackUrl(route('checkstatus', ['id' => $merchantTransactionID]))
//            ->merchantId($this->clientId)
//            ->merchantUserId("M22EOXLUSO1LA")
//            ->amount(1000)
//            ->merchantTransactionId($merchantTransactionID)
//            ->redirectUrl(route('checkstatus', ['id' => $merchantTransactionID]))
//            ->redirectMode("REDIRECT")
//            ->paymentInstrument(InstrumentBuilder::buildPayPageInstrument())
//            ->build();
//        try {
//            $response = $this->phonePePaymentsClient->pay($request);
//            $pagPageUrl = $response->getInstrumentResponse()->getRedirectInfo()->getUrl();
            return view('email/welcome',);

//        } catch (\Exception $e) {
//            return back()->with('error', 'Payment initiation failed: ' . $e->getMessage());
//        }
    }

    public function paymentWebhook(Request $request): JsonResponse
    {
        PaymentMetadata::create([
            'transaction_id' => "efrgthyj",
            'meta_data' => json_encode($request->all())
        ]);
        // Optional: You can verify the request here using signature, etc.

        // Save the payload or update order status as needed
        return response()->json(['status' => 'received'], 200);
    }

    public function checkstatus($id)
    {

//        dd("Response",$id ,"--");

        $merchantId =$id;
        $checkStatus = $this->phonePePaymentsClient->statusCheck($merchantId);

//        $checkStatus->getResponseCode();
//        $checkStatus->getState();
//        $checkStatus->getTransactionId();
        dd($checkStatus);

//        $transactionId = $request->input('transactionId');
//
//        $verifyString = '/pg/v1/status/' . $merchantId . '/' . $transactionId . $this->saltKey;
//        $verifyHeader = hash('sha256', $verifyString) . '###' . $this->saltIndex;
//
//        $token = $this->getAccessToken();
//
//        $response = Http::withHeaders([
//            'Content-Type' => 'application/json',
//            'accept' => 'application/json',
//            'X-VERIFY' => $verifyHeader,
//            'X-MERCHANT-ID' => $transactionId,
//            'Authorization' => "Bearer $token"
//        ])->get("https://api-preprod.phonepe.com/apis/merchant-simulator/pg/v1/status/$merchantId/$transactionId");
//
//        dd($response->json());
    }

    public function getAccessToken()
    {
        $response = Http::asForm()->post('https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'client_version' => $this->clientVersion,
            'grant_type' => 'client_credentials',
        ]);
        return $response['access_token'] ?? null;
    }

    public function refundProcess($tra_id)
    {
        $originalTransactionId = strrev($tra_id);
        $payload = [
            'merchantId' => 'MERCHANTUAT',
            'merchantUserId' => 'MUID123',
            'merchantTransactionId' => $tra_id,
            'originalTransactionId' => $originalTransactionId,
            'amount' => 5000,
            'callbackUrl' => route('response'),
        ];

        $encode = base64_encode(json_encode($payload));
        $string = $encode . '/pg/v1/refund' . $this->saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $this->saltIndex;

        $token = $this->getAccessToken();



        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $finalXHeader,
            'Authorization' => "Bearer $token"
        ])->post('https://api-preprod.phonepe.com/apis/merchant-simulator/pg/v1/refund', [
                    'request' => $encode,
                ]);

        $verifyString = '/pg/v1/status/MERCHANTUAT/' . $tra_id . $this->saltKey;
        $verifyHeader = hash('sha256', $verifyString) . '###' . $this->saltIndex;

        $status = Http::withHeaders([
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
            'X-VERIFY' => $verifyHeader,
            'X-MERCHANT-ID' => $tra_id,
            'Authorization' => "Bearer $token"
        ])->get("https://api-preprod.phonepe.com/apis/merchant-simulator/pg/v1/status/MERCHANTUAT/$tra_id");

        dd($response->json(), $status->json());
    }
}

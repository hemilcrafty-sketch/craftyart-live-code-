<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Order;
use App\Models\WebFbSelling;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhonePe\Env;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;

class PhonePeController extends ApiController
{
    public StandardCheckoutClient $phonePePaymentsClient;
    public string $clientId = "SU2512031928441979485878";
    public string $clientSecret = "04652cf1-d98d-4f48-8ae8-0ecf60fac76f";
    public int $clientVersion = 1;
    public string $merchantUserId = "M22EOXLUSO1LA";
    public string $callbackUrl = "https://www.craftyartapp.com/";
    public string $webhookUsername = "craftyart";
    public string $webhookPassword = "gdqi9EmMaTudFhf6hWT9";

    public function __construct(Request $request)
    {
        parent::__construct($request);

        try {
            $this->phonePePaymentsClient = StandardCheckoutClient::getInstance(
                $this->clientId,
                $this->clientVersion,
                $this->clientSecret,
                Env::PRODUCTION
            );
        } catch (Exception $e) {

        }
    }

    public function createOrder(Request $request): mixed
    {

        try {
            $amount = 1;
            $merchantOrderId = "PHONEPE_" . Carbon::now()->timestamp;

            $phonePeRequest = StandardCheckoutPayRequestBuilder::builder()
                ->merchantOrderId($merchantOrderId)
                ->amount($amount * 100)
                ->redirectUrl('https://craftyartapp.com')
                ->message("Phone Pe Payment Integration")
                ->build();
            $response = $this->phonePePaymentsClient->pay($phonePeRequest);
            $response->merchantOrderID = $merchantOrderId;

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\DomainChecker;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\AesCipher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Design;
use App\Models\PurchaseHistory;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\Subscription;
use Mail;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Auth;

class PaymentController extends Controller
{
    // Unused secret constants removed to prevent exposure and satisfy repository policies.


    public function isTester($uid): bool
    {
       return 'YTC1UOvR05hSKSkJSXFnb6LUFAi1' == $uid;
    }

    function refreshTanscation(Request $request) {

        $currentuserid = Auth::user()->id;
        $idAdmin = HelperController::isAdminOrManger($currentuserid);

        if (!$idAdmin) {
            $response['success'] = false;
            $response['message'] = 'Error';
            return $response;
        }

        $url = 'https://api.craftyartapp.com/api/payment/refreshTransaction';

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->body();
        } else {
            // Handle error (optional)
            return response()->json([
                'success' => false,
                'msg' => $response->body()
            ], 500);
        }

    }

    public static function enterTransData($uid, $transaction_id, $method, $assetDetailsccc, $currency_code_, $fromWhere__, $isManual): array
    {

        $url = "https://api.craftyartapp.com/api/payment/refreshTransaction/$transaction_id";

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            // Handle error (optional)
            return response()->json([
                'success' => false,
                'msg' => $response->body()
            ], 500);
        }

    }
}

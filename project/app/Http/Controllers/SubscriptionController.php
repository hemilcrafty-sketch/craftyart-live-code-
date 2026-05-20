<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\UserData;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Design;
use Carbon\Carbon;

class SubscriptionController extends ApiController
{

    function getIp(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $ipData = HelperController::getIpAndCountry($request);
        $response['data'] = $ipData;
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));
    }

    function getSubs(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $ipData = HelperController::getIpAndCountry($request);

        $currency = $ipData['cur'];
        $currency = strtoupper($currency);
        $currency_symbol = "₹";

        if (strtoupper($currency) != "INR") {
            $currency = "USD";
            $currency_symbol = "$";
        }

        $subData = Subscription::where("status", '1')->orderBy('sequence_number', 'ASC')->get();

        $discount = 0;

        $top_rows = array();
        foreach ($subData as $item) {
            $offer_msg = null;
            $has_offer = 0;
            if (strtoupper($currency) == "INR") {
                if ($item->actual_price != $item->price) {
                    $disc = (($item->actual_price - $item->price) / $item->actual_price) * 100;
                    $discount = (int)($disc);
                    $offer_msg = "Best Value (" . $discount . "% off)";
                    $has_offer = 1;
                }
            }

            if (strtoupper($currency) == "USD") {
                if ($item->actual_price_dollar != $item->price_dollar) {
                    $disc = (($item->actual_price_dollar - $item->price_dollar) / $item->actual_price_dollar) * 100;
                    $discount = (int)($disc);
                    $offer_msg = "Best Value (" . $discount . "% off)";
                    $has_offer = 1;
                }
            }

            if (strtoupper($currency) == "INR") {
                $price = round($item->price, 2);
                $actual_price = round($item->actual_price, 2);
            } else {
                $price = round($item->price_dollar, 2);
                $actual_price = round($item->actual_price_dollar, 2);
            }

            $top_rows[] = array(
                'id' => $item->id,
                'package_name' => $item->package_name,
                'desc' => $item->desc,
                'validity' => $item->validity,
                'currency' => $currency,
                'actual_price' => $currency_symbol . $actual_price,
                'offer_price' => $currency_symbol . $price,
                'price' => $price,
                'has_offer' => $has_offer,
                'offer_msg' => $offer_msg,
                'discount' => $discount
            );
        }

        $response['datas'] = $top_rows;

        $uData = UserData::whereUid($this->uid)->first();
        if ($uData) $response['contact_no'] = $uData->contact_no;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));

    }

    function getPurchases(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->has('page') ? $request->get('page') : 1;

        $limit = HelperController::getPaginationLimit(size: 10);

        $purHistory = array();

        $purDatas = PurchaseHistory::where("user_id", $this->uid)->where('payment_status', 1)->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $allCategoryIds = $purDatas->getCollection()->pluck('product_id')->unique();
        $designs = Design::whereIn('string_id', $allCategoryIds)->get()->keyBy('string_id');

        foreach ($purDatas->items() as $row) {
            $subRow = $designs->get($row->product_id);
            $currency_code = "$";
            if ($row->currency_code === "INR") {
                $currency_code = "₹";
            }

            $amount = $currency_code . $row->amount;

            $purHistory[] = array(
                'id' => $row->product_id,
                'type' => $row->product_type,
                'name' => $subRow->post_name,
                'image' => HelperController::$mediaUrl . $subRow->post_thumb,
                'width' => $subRow->width,
                'height' => $subRow->height,
                'transaction_id' => $row->payment_id,
                'amount' => $amount,
                'purchase_date' => $row->created_at->format('d/m/Y H:i:s'),
                'status' => HelperController::checkSubsStatus($row->status),
                'color' => HelperController::getSubsColor($row->status),
            );
        }

        $msg = 'Data loaded';
        if (($page == 1 || $page == '1') && sizeof($purHistory) == 0) {
            $msg = 'No History exist.';
        }

        $response['isLastPage'] = $purDatas->currentPage() >= $purDatas->lastPage();
        $response['datas'] = $purHistory;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
    }

    public static function getActivePlan($user_id): TransactionLog|null
    {
//        if ($user_id === 'YTC1UOvR05hSKSkJSXFnb6LUFAi1') return null;

        $transDatas = TransactionLog::where("user_id", $user_id)->where('payment_status', 1)->where("status", "1")->get();
        foreach ($transDatas as $transData) {
            $minLeft = Carbon::now()->diffInMinutes(Carbon::parse($transData->expired_at), false);
            if ($minLeft < 1) {
                $transData->status = 0;
                if ($transData->subscription_id == null)
                    $transData->subscription_status = 'expired';
//                $transData->save();
            } else {
                $transData->status = 1;
            }
            $transData->save();
        }

        $data = TransactionLog::where("user_id", $user_id)->where("status", "1")->latest()->first();
//        if ($data && $data->is_trial == 1 && $data->subscription_status !== 'active') {
//            UserData::where('uid', $user_id)->update(['is_premium' => 0]);
//            return null;
//        }
        if (!$data) UserData::where('uid', $user_id)->update(['is_premium' => 0]);
        else UserData::where('uid', $user_id)->update(['is_premium' => 1]);

        return $data;
    }

    public static function findTimeLeft($expiry_date): string
    {
        $expiry_date = Carbon::parse($expiry_date);
        $nowDate = Carbon::now();

        $daysLeft = $nowDate->diffInDays($expiry_date, false);
        $HoursLeft = $nowDate->diffInHours($expiry_date, false);
        $minLeft = $nowDate->diffInMinutes($expiry_date, false);

        $currentLeft = $daysLeft;
        $typeLeft = " Days Left";
        if ($daysLeft < 1) {
            if ($HoursLeft >= 1) {
                $typeLeft = " Hours Left";
                $currentLeft = $HoursLeft;
            } else {
                $typeLeft = " Minutes Left";
                $currentLeft = $minLeft;
            }
        }

        return $currentLeft . $typeLeft;
    }
}

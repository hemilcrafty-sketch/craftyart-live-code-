<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\AI\AiGenerator;
use App\Http\Controllers\AI\BGRemover;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Models\AI\AiCredit;
use App\Models\Caricature\Attire;
use App\Models\UserData;
use Illuminate\Http\Request;

class AICreditController extends ApiController
{
    function getAiCreditsPlan(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");
        return $this->successed(datas: self::getCredits($request));
    }

    function getRates(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $id = $request->get('id');

        $user_data = UserData::where('uid', $this->uid)->first();

        $ipData = HelperController::getIpAndCountry($request);
        $isInr = $ipData['cur'] === "INR";

        $data = null;
        if (!is_null($id)) {
            $attire = Attire::whereStringId($id)->first();
            if (!$attire) return $this->failed(msg: "Invalid request");

            $pyt = RateController::getCaricatureRates([], $attire->head_count, false, $attire->editor_choice == 1);

            $data['id'] = $attire->string_id;
            $data['currency'] = $ipData['cur'];
            $data['amount'] = $isInr ? $pyt['inrVal'] : $pyt['usdVal'];
            $data['amountStr'] = $isInr ? $pyt['inrAmount'] : $pyt['usdAmount'];
            $data['src'] = $attire->thumbnail_url;
            $data['title'] = $attire->post_name;
            $data['plan'] = [[
                'id' => $attire->string_id,
                'type' => 5,
                'title' => $attire->post_name
            ]];
        }

        return $this->successed(datas: [
            'data' => $data,
            'credits' => self::getCredits($request),
            'currency' => $ipData['cur'],
            'contact_no' => is_null($user_data) ? null : $user_data->contact_no]
        );
    }

    public static function getCredits(Request $request): array
    {

        $pyt = RateController::getCaricatureRates([], 2, false, false);
        $ipData = HelperController::getIpAndCountry($request);
        $isInr = strtoupper($ipData['cur']) === "INR";
        $curSymbol = $isInr ? '₹' : '$';

        $columnName = $isInr ? "inr_price" : "usd_price";

        $datas = [];

        $credits = AiCredit::whereStatus(1)->orderBy('credits')->get();

        foreach ($credits as $creditData) {

            $realAmount = $creditData->$columnName;

            $credit = $creditData->credits;
            $disc = $creditData->disc;

            if ($isInr) $discount = round($realAmount * $disc / 100);
            else $discount = $realAmount * $disc / 100;

            $discAmount = $realAmount - $discount;
            $discAmount = number_format((float)$discAmount, 2);
            $realAmount = number_format((float)$realAmount, 2);

            $datas[] = [
                "credits" => $credit,
                "actual_amount" => $curSymbol . $realAmount,
                "amount" => $curSymbol . $discAmount,
                "discount" => $disc == 0 ? null : $disc . '%',
                "saved" => $curSymbol . $discount,
                "price" => $discount,
                "type" => $creditData->type,
                "plan" => [[
                    'id' => $credit,
                    'type' => 6,
                    'title' => $credit,
                ]],
                'msg' => "You have saved ",
            ];
        }

        return [
            'datas' => $datas,
            'currency' => strtoupper($ipData['cur']),
            'caricature_credit' => $pyt['inrVal'],
            'bg_remover_credit' => BGRemover::$ai_credits,
            'style_credit' => AiGenerator::$ai_credits,
        ];
    }

    public static function getData($credit): array|null
    {

        $creditItem = AiCredit::whereCredits($credit)->first();
        if (!$creditItem) return null;

        $realInrAmount = $creditItem->inr_price;
        $realUsdAmount = $creditItem->usd_price;

        $inrDiscount = round($realInrAmount * $creditItem->disc / 100);
        $usdDiscount = $realUsdAmount * $creditItem->disc / 100;

        $inrDiscAmount = $realInrAmount - $inrDiscount;
        $inrDiscAmount = number_format((float)$inrDiscAmount, 2, '.', '');

        $usdDiscAmount = $realUsdAmount - $usdDiscount;
        $usdDiscAmount = number_format((float)$usdDiscAmount, 2, '.', '');

        $payment['inrVal'] = $inrDiscAmount;
        $payment['usdVal'] = $usdDiscAmount;
        $payment['inrAmount'] = '₹' . $inrDiscAmount;
        $payment['usdAmount'] = '$' . $usdDiscAmount;

        return $payment;
    }
}

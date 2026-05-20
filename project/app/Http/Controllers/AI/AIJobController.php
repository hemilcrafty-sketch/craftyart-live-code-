<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Caricature\AICreditController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Jobs\AICaricatureJob;
use App\Models\AI\AICreatedHistory;
use App\Models\AI\AIJobModel;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\CreatedCaricature;
use App\Models\UserData;
use Illuminate\Http\Request;

class AIJobController extends ApiController
{

    public function generate(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) return $this->failed(msg: "Unauthorized");

        $userImage = $request->file('file');
        $id = $request->input('id');
        if (is_null($userImage) || is_null($id)) return $this->failed(msg: "Parameter missing");

        $attire = Attire::whereStringId($id)->first();
        if (!$attire) return $this->failed(msg: "Invalid request");

        $ipData = HelperController::getIpAndCountry($request);
        $isInr = $ipData['cur'] === "INR";

        $pyt = RateController::getCaricatureRates([], $attire->head_count, false, $attire->editor_choice == 1);
        $ai_credits = $pyt['inrVal'];

        $needPurchase = $user->ai_credit < $ai_credits;

        if ($needPurchase) {
            $cariData = CaricaturePurchaseHistory::whereUserId($this->uid)->whereProductId($id)->whereUsed(0)->first();
            if (!$cariData) {

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

                $credits = AICreditController::getCredits($request);

                return $this->successed(datas: [
                    'needToPurchase' => true,
                    'data' => $data,
                    'credits' => $credits,
                    'currency' => $ipData['cur'],
                    'contact_no' => $user->contact_no
                ]);
            }
        }

        $userImageBase64 = base64_encode(file_get_contents($userImage->getRealPath()));
        $userImageMime = $userImage->getMimeType();

        $userInput = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($userImageMime);
        StorageUtils::put($userInput, base64_decode($userImageBase64));

        $res = new AIJobModel();
        $res->job_id = AIJobModel::generateJobId();
        $res->user_id = $this->uid;
        $res->type = 'bgremover';
        $res->data = [
            'attire_id' => $attire->string_id,
            'user_input' => $userInput,
        ];
        $res->ip_data = $ipData;
        $res->status = 'processing';
        $res->save();

        AICaricatureJob::dispatch($res->id);

        return $this->successed(datas: ['job_id' => $res->job_id]);
    }

    public function jobStatus(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) return $this->failed(msg: "Unauthorized");

        $id = $request->input('id');

        if (is_null($id)) return $this->failed(msg: "Parameter missing");

        $job = AIJobModel::whereJobId($id)->whereUserId($this->uid)->first();
        if (!$job) return $this->failed(msg: "Invalid request");

        if ($job->status === 'failed') return $this->failed(msg: "Invalid request");
        if ($job->status === 'processing') return $this->successed(datas: ['working' => true]);
        if ($job->status === 'purchase') {
            if ($job->type === 'bgremover') {
                $ipData = HelperController::getIpAndCountry($request);
                $isInr = $ipData['cur'] === "INR";

                $attire = Attire::whereStringId($job->data['attire_id'])->first();
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

                $credits = AICreditController::getCredits($request);
                return $this->successed(datas: [
                    'needToPurchase' => true,
                    'data' => $data,
                    'credits' => $credits,
                    'currency' => $ipData['cur'],
                    'contact_no' => $user->contact_no
                ]);
            } else {
                return $this->successed(datas: ['needToPurchase' => true]);
            }
        }

        if ($job->status === 'success') {
            if ($job->type === 'bgremover') $data = CreatedCaricature::find($job->ref_id);
            else $data = AICreatedHistory::find($job->ref_id);

            if (!$data) return $this->failed(msg: "Data not found");

            return $this->successed(datas: ['images' => $data->images, 'credits_left' => $user->ai_credit]);
        }

        return $this->failed();
    }

}

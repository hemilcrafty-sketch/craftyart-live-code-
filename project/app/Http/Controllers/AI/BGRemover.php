<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AI\AICreatedHistory;
use App\Models\AI\AICreditTransaction;
use App\Models\UserData;
use Illuminate\Http\Request;

class BGRemover extends ApiController
{
    public static int $ai_credits = 25;

    public function remove(Request $request): array|string
    {
        $userImage = $request->file('file');
        if (is_null($userImage)) return $this->failed(msg: "Parameter missing");

        $user = UserData::whereUid($this->uid)->first();

        $needPurchase = $user->ai_credit < self::$ai_credits;

        if ($needPurchase) return $this->successed(datas: ['needToPurchase' => true]);

        $userImageBase64 = base64_encode(file_get_contents($userImage->getRealPath()));
        $userImageMime = $userImage->getMimeType();

        $uniqid = uniqid();

        $userInput = "bgremover/$user->fldr_str/$uniqid." . HelperController::getExtensionFromMimeType($userImageMime);
        StorageUtils::put($userInput, base64_decode($userImageBase64));

        $output = "bgremover/$user->fldr_str/generated/$uniqid.png";
        StorageUtils::put($output, base64_decode($userImageBase64));

        $fullUserInput = escapeshellarg(StorageUtils::path($userInput));
        $fullOutput = escapeshellarg(StorageUtils::path($output));

        $outputLines = [];
        $exitCode = 0;

        $cmd = "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin "
            . "HOME=/tmp "
            . "TMPDIR=/tmp "
            . "NUMBA_DISABLE_JIT=1 "
            . "/venv/bin/rembg "
            . "i $fullUserInput $fullOutput";

        exec($cmd . " 2>&1", $outputLines, $exitCode);

        if ($exitCode !== 0) {
            StorageUtils::delete($userInput);
            StorageUtils::delete($output);
            return $this->failed(msg: "Background remover failed");
        }

        $user->decrement('ai_credit', self::$ai_credits);

        $res = new AICreatedHistory();
        $res->user_id = $this->uid;
        $res->type = 'bgremover';
        $res->images = json_encode([$output]);
        $res->credits = self::$ai_credits . " credits used";
        $res->user_input = $userInput;
        $res->show_data = $this->isTester() ? 0 : 1;
        $res->save();

        $resTrans = new AICreditTransaction();
        $resTrans->user_id = $this->uid;
        $resTrans->ref_id = $res->id;
        $resTrans->txn_id = AICreditTransaction::generateTxnId();
        $resTrans->type = 'bgremover';
        $resTrans->reason = "Consumed";
        $resTrans->debited = self::$ai_credits;
        $resTrans->save();

        return $this->successed(datas: ['image' => HelperController::$mediaUrl . $output, 'credits_left' => $user->ai_credit]);
    }
}

<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Utils\ApiController;
use App\Models\AI\AICreatedHistory;
use App\Models\AI\AICreditTransaction;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreditHistoryController extends ApiController
{

    public function history(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $limit = 20;

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) return $this->failed(msg: "Unauthorized");

        $page = $request->get('page', 1);
        $page = filter_var($page, FILTER_VALIDATE_INT);
        if (!$page || $page < 1) $page = 1;

        $transactions = AICreditTransaction::whereUserId($this->uid)->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $rows = [];

        foreach ($transactions->items() as $transaction) {
            $color = '#ff0000';
            $coins = '-' . $transaction->debited;
            $reason = $transaction->reason;
            if ($transaction->credited != 0) {
                $coins = '+' . $transaction->credited;
                $color = '#2EC4B6';
            } else {
                $reason = "Used for " . ucfirst($transaction->type);
            }

            $rows[] = array(
                'reason' => $reason,
                'date' => Carbon::parse($transaction->created_at)->format('d/m/Y'),
                'time' => Carbon::parse($transaction->created_at)->format('H:i:s'),
                'txn_id' => $transaction->txn_id,
                'coins' => $coins,
                'color' => $color
            );
        }

        return $this->successed(datas: [
            'pageNo' => $page,
            'isLastPage' => $transactions->currentPage() === $transactions->lastPage(),
            'ai_credit' => $user->ai_credit,
            'datas' => $rows
        ]);
    }

    public function aiCreatedHistory(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $limit = 20;

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) return $this->failed(msg: "Unauthorized");

        $page = $request->get('page', 1);
        $page = filter_var($page, FILTER_VALIDATE_INT);
        if (!$page || $page < 1) $page = 1;

        $createdHistory = AICreatedHistory::whereUserId($this->uid)->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $rows = [];

        foreach ($createdHistory->items() as $row) {
            /** @var AICreatedHistory $row */
            $images = [];
            foreach ($row->images as $image) {
                $images[] = [
                    "width" => 1000,
                    "height" => 1000,
                    "src" => $image,
                ];
            }

            $rows[] = array(
                'type' => ucfirst($row->type),
                'image' => $images,
            );
        }

        return $this->successed(datas: [
            'pageNo' => $page,
            'isLastPage' => $createdHistory->currentPage() === $createdHistory->lastPage(),
            'ai_credit' => $user->ai_credit,
            'datas' => $rows
        ]);
    }

//    public function history(Request $request): mixed
//    {
//
//        if (!$this->isTester()) return $this->failed(msg: "Unauthorized");
//
//        $userIds = ["g3jtVGAHxgQwrNmdIJf4JqBdEpK2", "48vNvOa7qYPZwRTnS4sqkEdUTGv1", $this->uid];
//
//        $credits = AIPurchaseHistory::whereNotIn('user_id', $userIds)->get();
//        $histories = AICreatedHistory::whereNotIn('user_id', $userIds)->get();
//        $caricatures = CreatedCaricature::whereNotIn('user_id', $userIds)->get();
//
//        $datas = [];
//
//        $datas[] = array(
//            'user_id' => "n1VNBHzj2rSpfxOe9DvVpJXf5xC3",
//            'ref_id' => null,
//            'type' => "bonus",
//            'reason' => "Bonus",
//            'debited' => 0,
//            'credited' => '7470',
//            'created_at' => Carbon::parse("2025-10-26T14:40:48.000000Z"),
//            'updated_at' => Carbon::parse("2025-10-26T14:40:48.000000Z"),
//        );
//
//        $datas[] = array(
//            'user_id' => "XwnET4P2PJeiRilcNtZb5PK0oXW2",
//            'ref_id' => null,
//            'type' => "bonus",
//            'reason' => "Bonus",
//            'debited' => 0,
//            'credited' => '4980',
//            'created_at' => Carbon::parse("2025-11-17T04:30:51.000000Z"),
//            'updated_at' => Carbon::parse("2025-11-17T04:30:51.000000Z"),
//        );
//
//        $datas[] = array(
//            'user_id' => "hklyOy7oaXXnTQJEazjnFZIlk1I3",
//            'ref_id' => null,
//            'type' => "bonus",
//            'reason' => "Bonus",
//            'debited' => 0,
//            'credited' => '249',
//            'created_at' => Carbon::parse("2025-11-18T11:10:51.000000Z"),
//            'updated_at' => Carbon::parse("2025-11-18T11:10:51.000000Z"),
//        );
//
//        $datas[] = array(
//            'user_id' => "tDSZpAfxS2anP2ZOb6mwBtwG0XP2",
//            'ref_id' => null,
//            'type' => "bonus",
//            'reason' => "Bonus",
//            'debited' => 0,
//            'credited' => '249',
//            'created_at' => Carbon::parse("2025-10-26T14:48:48.000000Z"),
//            'updated_at' => Carbon::parse("2025-10-26T14:48:48.000000Z"),
//        );
//
//        foreach ($credits as $credit) {
//            $datas[] = array(
//                'user_id' => $credit->user_id,
//                'ref_id' => $credit->id,
//                'type' => "purchase",
//                'reason' => "Purchased",
//                'debited' => 0,
//                'credited' => $credit->product_id,
//                'created_at' => Carbon::parse($credit->created_at),
//                'updated_at' => Carbon::parse($credit->created_at),
//            );
//        }
//
//        foreach ($histories as $history) {
//            $datas[] = array(
//                'user_id' => $history->user_id,
//                'ref_id' => $history->id,
//                'type' => $history->type,
//                'reason' => "Consumed",
//                'debited' => 80,
//                'credited' => 0,
//                'created_at' => Carbon::parse($history->created_at),
//                'updated_at' => Carbon::parse($history->created_at),
//            );
//        }
//
//        foreach ($caricatures as $caricature) {
//            if (str_starts_with($caricature->payment_id, 'crafty_')) continue;
//            $datas[] = array(
//                'user_id' => $caricature->user_id,
//                'ref_id' => $caricature->id,
//                'type' => 'caricature',
//                'reason' => "Consumed",
//                'debited' => 249,
//                'credited' => 0,
//                'created_at' => Carbon::parse($caricature->created_at),
//                'updated_at' => Carbon::parse($caricature->created_at),
//            );
//        }
//
//        $datas = collect($datas)->sortBy('created_at')->values()->toArray();
//
////        AICreditTransaction::insert($datas);
//
//        return $datas;
//    }

}

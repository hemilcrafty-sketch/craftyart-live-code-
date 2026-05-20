<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\AppBaseController;
use App\Models\AI\AICreditTransaction;
use Illuminate\Http\Request;

class AiCreditTransactionController extends AppBaseController
{

    public function index(Request $request)
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'type', "value" => 'Type'],
            ["id" => 'reason', "value" => 'Reason'],
            ["id" => 'debited', "value" => 'Debited'],
            ["id" => 'credited', "value" => 'Credited'],
        ];

        $query = AICreditTransaction::query()->with(['user']);

        $transactions = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        $total = $query->count();
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $str_count = "Showing 0-0 of 0 entries";
        } else {
            $str_count = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        return view("subscription/credit_transaction",compact('transactions','str_count'));
    }



}
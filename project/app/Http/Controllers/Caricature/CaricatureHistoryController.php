<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\AppBaseController;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\CreatedCaricature;
use App\Models\UserData;
use Illuminate\Http\Request;

class CaricatureHistoryController extends AppBaseController
{
    public function index(Request $request)
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'id'],
            ["id" => 'user_id', "value" => 'user id'],
            ["id" => 'caricature_id', "value" => 'caricature id'],
            ["id" => 'payment_id', "value" => 'payment id']
        ];

        $relationSearchConfig = [
            [
                'relation' => 'user',
                'model' => UserData::class,
                'match_column' => 'uid',
                'foreign_key' => 'user_id',
                'fields' => ['email', 'name']
            ],
            [
                'relation' => 'purchase',
                'model' => CaricaturePurchaseHistory::class,
                'match_column' => 'payment_id',
                'foreign_key' => 'payment_id',
                'fields' => ['contact_no', 'transaction_id']
            ]
        ];

        $allCategories = $this->applyFiltersAndPagination(
            $request,
            CreatedCaricature::with(['user', 'purchase'])->where('show_data', 1),
            $searchableFields,
            $relationSearchConfig
        );

        return view('caricature_history.index', compact('allCategories', 'searchableFields'));
    }

    public function showByPaymentId($payment_id)
    {
        $records = CreatedCaricature::where('payment_id', $payment_id)->orWhere('id', $payment_id)->get();

        if ($records->isEmpty()) {
            return redirect()->route('caricature_history.index')->with('error', 'No records found for this payment ID.');
        }

        return view('caricature_history.show', compact('records', 'payment_id'));
    }

}
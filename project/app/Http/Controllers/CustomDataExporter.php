<?php

namespace App\Http\Controllers;

use App\Exports\CustomDataExport;
use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\UserData;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;

class CustomDataExporter extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    function getDatas(): BinaryFileResponse
    {
        $purchases = PurchaseHistory::whereNotNull('contact_no')->orderBy('id', 'DESC')->get();

        $headings = ['Name', 'Email', 'Number', 'Currency', 'Amount'];
        $data = [];

        foreach ($purchases as $purchase) {
            $user = UserData::where('uid', $purchase->user_id)->first();
            if ($user) {
                $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->amount];
            }
        }

        return Excel::download(new CustomDataExport($data, $headings), 'dynamic-data.xlsx');
    }

    function getSubDatas(): BinaryFileResponse
    {
        // $purchases = TransactionLog::whereNotNull('contact_no')->orderBy('id', 'DESC')->get();

        $purchases = TransactionLog::whereNotNull('contact_no')
                    ->latest() // equivalent to orderBy('created_at', 'desc')
                    ->get()
                    ->unique('user_id')
                    ->values();

        $headings = ['Name', 'Email', 'Number', 'Currency', 'Amount', 'Status', 'Purchase Date', 'Expiry Date'];
        $data = [];

        foreach ($purchases as $purchase) {
            $user = UserData::where('uid', $purchase->user_id)->first();
            if ($user) {
                $status = "Active";
                $minLeft = Carbon::now()->diffInMinutes(Carbon::parse($purchase->expired_at), false);
                if ($minLeft < 1) {
                    $status = "InActive";
                    $purchase->status = 0;
                    $purchase->save();
                } 

                if ($purchase->currency_code === 'Rs') {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->paid_amount, $status, $purchase->created_at, $purchase->expired_at];
                } else {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->price_amount, $status, $purchase->created_at, $purchase->expired_at];
                }
                
            }
        }

        return Excel::download(new CustomDataExport($data, $headings), 'dynamic-data.xlsx');
    }

    function getMetaSubDatas(): BinaryFileResponse
    {
        // $purchases = TransactionLog::whereNotNull('contact_no')->orderBy('id', 'DESC')->get();

        $purchases = TransactionLog::whereNotNull('contact_no')
                    ->whereIn('plan_id', [23, 24, 26, 29, 30])
                    ->latest() // equivalent to orderBy('created_at', 'desc')
                    ->get()
                    ->unique('user_id')
                    ->values();

        $headings = ['Name', 'Email', 'Number', 'Currency', 'Amount', 'Status', 'Purchase Date', 'Expiry Date'];
        $data = [];

        foreach ($purchases as $purchase) {
            $user = UserData::where('uid', $purchase->user_id)->first();
            if ($user) {
                $status = "Active";
                $minLeft = Carbon::now()->diffInMinutes(Carbon::parse($purchase->expired_at), false);
                if ($minLeft < 1) {
                    $status = "InActive";
                    $purchase->status = 0;
                    $purchase->save();
                } 

                if ($purchase->currency_code === 'Rs') {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->paid_amount, $status, $purchase->created_at, $purchase->expired_at];
                } else {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->price_amount, $status, $purchase->created_at, $purchase->expired_at];
                }
                
            }
        }

        return Excel::download(new CustomDataExport($data, $headings), 'dynamic-data.xlsx');
    }

    function getUsers(): BinaryFileResponse
    {
        $userDatas = UserData::whereNotNull('email')->paginate(50000, ['*'], 'page', 1);

        $headings = ['fn', 'email', 'phone'];
        $data = [];

        foreach ($userDatas->items() as $user) {
            $data[] = [$user->name, $user->email, $user->contact_no];
        }

        return Excel::download(new CustomDataExport($data, $headings), 'users-data.xlsx');
    }
}

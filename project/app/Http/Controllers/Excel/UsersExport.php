<?php

namespace App\Http\Controllers\Excel;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\UserData;
use Razorpay\Api\Api;
use Stripe\StripeClient;
use Illuminate\Support\Str;

class UsersExport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            $this->createSheet('Subscription', TransactionLog::class),
            $this->createSheet('Templates', PurchaseHistory::class),
            $this->createSheet('Videos', VideoPurchaseHistory::class),
        ];
    }

    private function createSheet(string $title, string $model)
    {
        return new class($title, $model) implements FromCollection, WithHeadings, WithTitle {
            private $title;
            private $model;
            private $stripe;
            private $razorpay;
            private $razorpay2;

            public function __construct($title, $model)
            {
                $this->title = $title;
                $this->model = $model;
                $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
                $this->razorpay = new Api(env('RAZORPAY_PUBLISHABLE_KEY'), env('RAZORPAY_SECRET_KEY'));
                $this->razorpay2 = new Api(env('RAZORPAY_PUBLISHABLE_KEY_2'), env('RAZORPAY_SECRET_KEY_2'));
            }

            public function collection()
            {
                $transData = [];
                $users = [];
                $datas = $this->model::orderBy('id', 'DESC')->get();
                $userIds = $datas->pluck('user_id')->unique();

                $userDetails = UserData::whereIn('uid', $userIds)->get()->keyBy('uid');

                foreach ($datas as $item) {
                    if (!in_array($item->user_id, $users)) {
                        $users[] = $item->user_id;
                        $userData = $userDetails->get($item->user_id);
                        if ($userData && isset($userData->email) && $item->transaction_id) {
                            try {
                                $number = "";
                                if (Str::startsWith($item->transaction_id, 'txn_')) {
                                    $balanceTransaction = $this->stripe->balanceTransactions->retrieve($item->transaction_id);
                                    if (isset($balanceTransaction->source)) {
                                        $charge = $this->stripe->charges->retrieve($balanceTransaction->source);
                                        if ($charge && isset($charge->billing_details->phone)) {
                                            $number = $charge->billing_details->phone;
                                        }
                                    }
                                } else if (Str::startsWith($item->transaction_id, 'pay_')) {
                                    try {
                                        $payment = $this->razorpay->payment->fetch($item->transaction_id);
                                        $number = $payment['contact'] ?? "";
                                    } catch (\Exception $e) {
                                        $payment = $this->razorpay2->payment->fetch($item->transaction_id);
                                        $number = $payment['contact'] ?? "";
                                    }

                                } else {
                                    $number = $userData->number ?? "";
                                }
                                $transData[] = [
                                    'name' => $userData->name,
                                    'email' => $userData->email,
                                    'number' => $number,
                                ];
                            } catch (\Exception $e) {
                                $transData[] = [
                                    'name' => $userData->name,
                                    'email' => $userData->email,
                                    'number' => $number,
                                ];
                            }
                        }
                    }
                }
                return collect($transData);
            }

            public function headings(): array
            {
                return ['Name', 'Email', 'Number'];
            }

            public function title(): string
            {
                return $this->title;
            }
        };
    }
}

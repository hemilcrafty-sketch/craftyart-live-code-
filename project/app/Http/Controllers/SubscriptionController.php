<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Subscription;
use App\Models\PaymentSetting;
use App\Models\TransactionLog;
use App\Models\RefundedTransactionLog;
use App\Models\ExportTable;
use App\Models\PurchaseHistory;
use App\Models\Design;
use App\Models\UserData;
use App\Models\Revenue\BusinessSupportPurchaseHistory;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\Attire;
use App\Models\Caricature\AIPurchaseHistory;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\AI\AICreditTransaction;
use App\Models\Pricing\PaymentConfiguration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use Razorpay\Api\Api;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Http;
use Exception;

class SubscriptionController extends AppBaseController
{

    public function index()
    {
    }

    public function show_package(Subscription $subscription)
    {
        return view('subscription/show_package')->with('packageArray', Subscription::all());
    }

    public function showPaymentSetting(PaymentSetting $paymentSetting)
    {
        $payment = PaymentSetting::find(1);
        $payment = ($payment == null) ? [] : $payment;
        return view('subscription/payment_setting')->with('payment', $payment);
    }

    public function showTranscation(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;
        $temp_data = TransactionLog::with(['userData', 'subscription']);
        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data->where(function ($q) use ($query) {
                $q->where('plan_id', 'LIKE', "%{$query}%")
                    ->orWhere('user_id', 'LIKE', "%{$query}%")
                    ->orWhere('transaction_id', 'LIKE', "%{$query}%")
                    ->orWhere('subscription_id', 'LIKE', "%{$query}%")
                    ->orWhere('paid_amount', 'LIKE', "%{$query}%")
                    ->orWhere('payment_method', 'LIKE', "%{$query}%")
                    ->orWhere('from_where', 'LIKE', "%{$query}%");
            });
        }
        if ($request->has('type')) {
            $planIds = Subscription::where('is_meta', 1)->pluck('id')->toArray();
            $type = $request->get('type');
            if ($type == 'e_mandate') {
                $temp_data->where('is_e_mandate', 1);
            } else if ($type == 'sales_team') {
                $temp_data->where('is_e_mandate', 0)->whereNull('fbc')->whereNull('gclid')->where('by_sales_team', 1);
            } else if ($type == 'seo') {
                $temp_data->where('is_e_mandate', 0)->whereNull('fbc')->whereNull('gclid')->where('by_sales_team', 0);
            } else if ($type == 'meta') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNull('gclid');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNull('gclid')->whereIn('plan_id', $planIds);
            } else if ($type == 'google') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('gclid')->whereNull('fbc');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('gclid')->whereNull('fbc')->whereIn('plan_id', $planIds);
            } else if ($type == 'meta-google') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNotNull('gclid');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNotNull('gclid')->whereIn('plan_id', $planIds);
            } else if ($type == "google_page"){
                $temp_data->where('is_e_mandate', 0)->whereNotNull('url')->where('url', 'like',  "%/video-bundle%");
            } else if ($type == "meta_page"){
                $temp_data->where('is_e_mandate', 0)->whereNotNull('url')->where('url', 'like',  "%/wedding-video-bundle%");
            }
        }
        $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);
        $temp_data_count = $temp_data->total();
        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }
        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();
        return view('subscription/show_transcation')->with('datas', $data);
    }

    public function showTranscationw(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        $temp_data = TransactionLog::with(['userData', 'subscription']);

        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data->where(function ($q) use ($query) {
                $q->where('plan_id', 'LIKE', "%{$query}%")
                  ->orWhere('user_id', 'LIKE', "%{$query}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$query}%")
                  ->orWhere('subscription_id', 'LIKE', "%{$query}%")
                  ->orWhere('paid_amount', 'LIKE', "%{$query}%")
                  ->orWhere('payment_method', 'LIKE', "%{$query}%")
                  ->orWhere('from_where', 'LIKE', "%{$query}%");
            });
        }

        if ($request->has('type')) {
            $planIds = Subscription::where('is_meta', 1)->pluck('id')->toArray();
            $type = $request->get('type');
            if ($type == 'e_mandate') {
                $temp_data->where('is_e_mandate', 1);
            } else if ($type == 'sales_team') {
                $temp_data->where('is_e_mandate', 0)->whereNull('fbc')->whereNull('gclid')->where('by_sales_team', 1);
            } else if ($type == 'seo') {
                $temp_data->where('is_e_mandate', 0)->whereNull('fbc')->whereNull('gclid')->where('by_sales_team', 0);
            } else if ($type == 'meta') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNull('gclid');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNull('gclid')->whereIn('plan_id', $planIds);
            } else if ($type == 'google') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('gclid')->whereNull('fbc');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('gclid')->whereNull('fbc')->whereIn('plan_id', $planIds);
            } else if ($type == 'meta-google') {
                $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNotNull('gclid');
                // $temp_data->where('is_e_mandate', 0)->whereNotNull('fbc')->whereNotNull('gclid')->whereIn('plan_id', $planIds);
            }
            
        }

        $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);

        $temp_data_count = $temp_data->total();


        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();

        return view('subscription/show_transcation')->with('datas', $data);
    }

    public function showPurchases(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $temp_data_count = PurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = PurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = PurchaseHistory::orderBy('id', 'desc')->count();
            $temp_data = PurchaseHistory::orderBy('id', 'desc')->paginate(10);
        }

        $temp_data->getCollection()->transform(function ($purchase) {
            $res = Design::where('string_id', $purchase->product_id)->first();
            $purchase->thumb = HelperController::$mediaUrl . $res->post_thumb;
            $purchase->page_link = $res->page_link;
            return $purchase;
        });

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        return view('subscription/show_purchases')->with('datas', $data);
    }

    public function addPackage(Request $request)
    {
        $res = new Subscription;
        $res->package_name = $request->input('package_name');
        $res->desc = $request->input('desc');
        $res->validity = $request->input('validity');
        $res->actual_price = $request->input('actual_price');
        $res->price = $request->input('price');
        $res->actual_price_dollar = $request->input('actual_price_dollar');
        $res->price_dollar = $request->input('price_dollar');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updatePackage(Request $request, Subscription $subscription)
    {

        $res = Subscription::find($request->id);
        $res->package_name = $request->input('package_name');
        $res->desc = $request->input('desc');
        $res->validity = $request->input('validity');
        $res->actual_price = $request->input('actual_price');
        $res->price = $request->input('price');
        $res->actual_price_dollar = $request->input('actual_price_dollar');
        $res->price_dollar = $request->input('price_dollar');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deletePackage(Request $request, Subscription $subscription)
    {
        // Subscription::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    public function updatePaymentSetting(Request $request, PaymentSetting $paymentSetting)
    {

        $res = PaymentSetting::find($request->id);
        $res->razorpay_status = $request->input('razorpay_status');
        $res->stripe_status = $request->input('stripe_status');
        $res->paypal_status = $request->input('paypal_status');

        $res->razorpay_ki = $request->input('razorpay_ki');
        $res->razorpay_ck = $request->input('razorpay_ck');

        $res->stripe_sk = $request->input('stripe_sk');
        $res->stripe_pk = $request->input('stripe_pk');
        $res->stripe_ver = $request->input('stripe_ver');

        $res->paypal_ci = $request->input('paypal_ci');
        $res->paypal_sk = $request->input('paypal_sk');

        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function showTemplateTranscation(Request $request)
    {

        $temp_data = [];
        $temp_data_count = 0;
        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data_count = PurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = PurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = PurchaseHistory::with('userData')->orderBy('id', 'desc')->count();
            $temp_data = PurchaseHistory::with('userData')->orderBy('id', 'desc')->paginate(10);
        }

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationTemplateArray'] = $temp_data;

        return view('subscription/show_template_transcation')->with('datas', $data);
    }

    public function showVideoTranscation(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data_count = VideoPurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = VideoPurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = VideoPurchaseHistory::with('userData')->orderBy('id', 'desc')->count();
            $temp_data = VideoPurchaseHistory::with('userData')->orderBy('id', 'desc')->paginate(10);
        }


        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationVideArray'] = $temp_data;

        return view('subscription/show_video_transcation')->with('datas', $data);
    }

    public function showCariPurchases(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $temp_data_count = CaricaturePurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = CaricaturePurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = CaricaturePurchaseHistory::orderBy('id', 'desc')->count();
            $temp_data = CaricaturePurchaseHistory::orderBy('id', 'desc')->paginate(10);
        }

        $temp_data->getCollection()->transform(function ($purchase) {
            $res = Attire::where('string_id', $purchase->product_id)->first();
            $purchase->thumb = $res->thumbnail_url;
            $purchase->page_link = $res->page_link;
            return $purchase;
        });

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['showCaricatureHistory'] = true;
        return view('subscription/show_purchases')->with('datas', $data);
    }

    public function upcomingMandates(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        $status = $request->get('status', 'active');

        $temp_data = TransactionLog::whereNotNull('subscription_id')->where('subscription_status', $status);

        if ($status !== 'cancelled') {
            $temp_data->where('subscription_is_active', 1);
        }

        if ($request->has('expired_at')) {
            $temp_data->whereDate('expired_at', $request->get('expired_at'));
        }

        if ($request->has('query')) {

            $query = $request->input('query');
    
            $temp_data = $temp_data->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('plan_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('subscription_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('paid_amount', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            });
        } 

        $temp_data = $temp_data->orderBy('expired_at', 'asc')->paginate(10);

        $temp_data_count = $temp_data->total();


        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();

        return view('subscription/upcoming_mandates')->with('datas', $data);
    }

    public function showAiCreditPurchases(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        $temp_data = AIPurchaseHistory::query();


        if ($request->has('query')) {

            $query = $request->input('query');
    
            $temp_data = $temp_data->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('product_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%');
            });
        } 
        

        $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);
        $temp_data_count = $temp_data->total();

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        return view('subscription/show_ai_credit_purchases')->with('datas', $data);
    }

    public function showBusinessSupport(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        $temp_data = BusinessSupportPurchaseHistory::query();


        if ($request->has('query')) {

            $query = $request->input('query');
    
            $temp_data = $temp_data->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('product_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%');
            });
        } 
        

        $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);
        $temp_data_count = $temp_data->total();

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        return view('subscription/show_ai_credit_purchases')->with('datas', $data);
    }

    public function addAiCreditBouns(Request $request)
    {
        $currentuser = Auth::user()->user_type;

        if (!in_array($currentuser, [1, UserRole::SALES_MANAGER->id()])) {
            return response()->json([
                'status' => false,
                'success' => "Error",
            ]);
        }

        $user_id = $request->user_id;
        $email = $request->email;
        $credits = $request->input('credits', 0);
        $credits = filter_var($credits, FILTER_VALIDATE_INT);
        if (!$credits || $credits <= 0) $credits = 0;

        if ($credits == 0) {
            return response()->json([
                'status' => false,
                'success' => "Invalid Credits",
            ]);
        }

        if ($email) $user_data = UserData::where("email", $email)->first();
        else $user_data = UserData::where("uid", $user_id)->first();

        if (!$user_data) {
            return response()->json([
                'status' => false,
                'success' => "User not found.",
            ]);
        }

        $resTrans = new AICreditTransaction();
        $resTrans->ref_id = null;
        $resTrans->user_id = $user_data->uid;
        $resTrans->txn_id = AICreditTransaction::generateTxnId();
        $resTrans->type = 'bonus';
        $resTrans->reason = "Bonus";
        $resTrans->credited = $credits;
        $resTrans->save();

        $user_data->ai_credit = $user_data->ai_credit + $credits;
        $user_data->save();
        
        return response()->json([
            'status' => true,
            'success' => "AI Credit has been added successfully.",
        ]);
    }

    public function freeExports(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;
        $temp_data = ExportTable::with('draft')->where('watermark', 1)->whereDate('created_at', Carbon::today());
        $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);
        $temp_data_count = $temp_data->total();

        $total = $temp_data_count;
        $count = $total;
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
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }
        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();
        // return $data;
        return view('subscription/free_exports')->with('datas', $data);
    }

    public function processRefund(Request $request)
    {

        $currentUserId = Auth::user()->id;
        $currentUserType = Auth::user()->user_type;

        if (!in_array($currentUserType, [1, UserRole::SALES_MANAGER->id()])) {
            return response()->json([
                'status' => false,
                'success' => "Error",
            ]);
        }

        $validated = $request->validate([
            'refund_id' => 'required|numeric|string',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|in:duplicate,fraudulent,requested_by_customer,other',
            'refund_note' => 'nullable|string',
            'refund_instantly' => 'boolean'
        ]);

        if ($validated === false) {
            return response()->json([
                'success' => false,
                'msg' => "Error",
            ]);
        }

        $refund_id = $request->get('refund_id');
        $refund_amount = $request->get('refund_amount');
        $refund_reason = $request->get('refund_reason');
        $refund_note = $request->get('refund_note');
        $refund_instantly = $request->get('refund_instantly');

        $masterData = MasterPurchaseHistory::find($refund_id);
        if (!$masterData) {
            return response()->json([
                'success' => true,
                'msg' => 'Transcation Id not found'
            ]);
        }

        $data = TransactionLog::whereTransactionId($masterData->transaction_id)->first();


        if (!$data) {
            return response()->json([
                'success' => true,
                'msg' => 'Transcation not found'
            ]);
        }

        $method = strtolower($data->payment_method);
        if (!in_array($method, ['razorpay', 'stripe', 'phonepe_pg'])) {
            return response()->json([
                'success' => false,
                'msg' => 'Transcation is invalid'
            ]);
        }

        $transaction_id = $data->transaction_id;
        $subId = $data->subscription_id;
        $refund_amount = $refund_amount * 100;

        $msg = "Unknown Error";

        $refunded = false;
        if ($method === 'razorpay') {

            $config = PaymentConfiguration::getCredentialsByName(null, 'NATIONAL', $method);
            $credentials = $config->credentials;
            $razorpay = new Api($credentials['key_id'], $credentials['secret_key']);

            try {
                $razorpay->subscription->fetch($data->subscription_id)->cancel(array("cancel_at_cycle_end" => false));
                $data->subscription_is_active = 0;
                $data->cancellation_reason = json_encode(['refund']);
                $data->subscription_status = 'cancelled';
                $data->save();
            } catch (\Exception $e) {

            }

            try {
                $speedType = $refund_instantly ? 'optimum' : 'normal';
                $refundData = $razorpay->payment->fetch($transaction_id)->refund(array("amount" => $refund_amount, "speed" => $speedType, "notes" => array("reason" => $refund_reason, "comment" => $refund_note, "receipt" => "Receipt No. $data->id")));
                if ($refundData && isset($refundData['id'])) {
                    $refunded = true;
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        } else if ($method === 'phonepe_pg') {

            $config = PaymentConfiguration::getCredentialsByName(null, 'NATIONAL', $method);
            $credentials = $config->credentials;
            
            $token = $this->generateNewToken($credentials);
            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Token is invalid'
                ]);
            }

            try {
                $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/$subId/cancel";
                $response = Http::timeout(60)->withHeaders([
                    "Authorization" => "O-Bearer $token",
                    "Content-Type" => "application/json",
                    "Accept" => "application/json"
                ])->post($url);

                $data->subscription_is_active = 0;
                $data->cancellation_reason = json_encode(['refund']);
                $data->subscription_status = 'cancelled';
            } catch (\Exception $e) {
               
            }

            try {

                $merchantRefundId = "refund_" . uniqid() . time();
                $payload = [
                    "merchantRefundId" => $merchantRefundId,
                    "originalMerchantOrderId" => $transaction_id,
                    "amount" => $refund_amount
                ];


                $url = 'https://api.phonepe.com/apis/pg/payments/v2/refund';

                $response = Http::withHeaders([
                    "Authorization" => "O-Bearer $token",
                    "Content-Type" => "application/json",
                    "Accept" => "application/json"
                ])->post($url, $payload);

                if ($response->status() == 200) {
                    $refunded = true;
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        } else {

            $config = PaymentConfiguration::getCredentialsByName(null, 'INTERNATIONAL', $method);
            $credentials = $config->credentials;
            $stripe = new StripeClient($credentials['secret_key']);

            try {
                $stripe->subscriptions->cancel($data->subscription_id);
                $data->subscription_is_active = 0;
                $data->cancellation_reason = json_encode(['refund']);
                $data->subscription_status = 'cancelled';
                $data->save();
            } catch (\Exception $e) {

            }

            if (str_starts_with($transaction_id, 'pi_')) {
                try {
                    $paymentIntent = $stripe->paymentIntents->retrieve($transaction_id);
                    if ($paymentIntent && isset($paymentIntent->latest_charge)) {
                        $charge = $stripe->charges->retrieve($paymentIntent->latest_charge);
                        $transaction_id = $charge->balance_transaction;
                    }
                    $transaction = $stripe->balanceTransactions->retrieve($transaction_id);
                    if ($transaction) {
                        if (isset($transaction['source'])) {
                            $stripe->refunds->create([
                                'charge' => $transaction['source'],
                                'amount' => $refund_amount,
                                'reason' => $refund_reason,
                                'metadata' => [
                                    'reason' => $refund_reason,
                                    'comment' => $refund_note,
                                    "receipt" => "Receipt No. $data->id",
                                ],
                            ]);
                        }
                        $refunded = true;
                    }
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                }

            }
        }

        if ($refunded) {
            try {
                
                MasterPurchaseHistory::whereTransactionId($transaction_id)->update(['payment_status' => 'refunded', 'refund_by' => $currentUserId]);

                $newData = collect($data->toArray())->except(['id', 'created_at', 'updated_at'])->toArray();
                $refund = new RefundedTransactionLog($newData);

                $refund->ref_id = $data->id;
                $refund->refund_by = $currentUserId;
                $refund->ref_created_at = $data->created_at;
                $refund->ref_updated_at = $data->updated_at;
                $refund->save();

                $data->delete();


                return response()->json([
                    'success' => true,
                    'msg' => "Done",
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'msg' => $msg,
        ]);
    }

    protected function generateNewToken($credentials): string
    {

        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        try {
            $response = Http::asForm()->post($url, [
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
                'client_version' => $credentials['client_version'],
                'grant_type' => 'client_credentials',
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                return null;
            }

            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;

            return $accessToken;

        } catch (Exception $e) {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Utils\RoleManager;
use App\Models\ManageSubscription;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\AI\AICreditTransaction;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class UserManageSubscriptionController extends AppBaseController
{

    public function showManageSubscription(Request $request,$userId=null)
    {
        $temp_data = [];
        $temp_data_count = 0;
        $user = UserData::where('id',$userId)->first();
        $user_id = $user->uid;

        if($request->has('query')) {
            $temp_data_count = TransactionLog::where('user_id',$user_id)
                            ->orWhere('plan_id','LIKE','%'.$request->input('query').'%')
                            ->orWhere('transaction_id','LIKE','%'.$request->input('query').'%')
                            ->orWhere('paid_amount','LIKE','%'.$request->input('query').'%')
                            ->orWhere('payment_method','LIKE','%'.$request->input('query').'%')
                            ->orWhere('from_where','LIKE','%'.$request->input('query').'%')
                            ->orderBy('created_at', 'desc')
                            ->count();

            $temp_data = TransactionLog::where('user_id',$user_id)
                            ->where('plan_id','LIKE','%'.$request->input('query').'%')
                            ->orWhere('transaction_id','LIKE','%'.$request->input('query').'%')
                            ->orWhere('paid_amount','LIKE','%'.$request->input('query').'%')
                            ->orWhere('payment_method','LIKE','%'.$request->input('query').'%')
                            ->orWhere('from_where','LIKE','%'.$request->input('query').'%')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        } else {
            $temp_data_count = TransactionLog::where('user_id',$user_id)->orderBy('created_at', 'desc')->count();
            $temp_data = TransactionLog::where('user_id',$user_id)->orderBy('created_at', 'desc')->paginate(10);
        }

        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if($total < 10) {
            $diff = ($total - 1);
        }

        if($request->has('page')) {
            $count = $request->input('page') * 10;
            if($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
           $count = 10;
            if($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing ".($count - $diff). "-".$count. " of ".$total." entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();


        $datas = $data;
        return view('subscription.manage_subscription',compact('datas','user_id'));

    }

    public function saveManageSubscription(Request $request)
    {
        try {

            $currentuser = Auth::user()->user_type;
            if (!RoleManager::isAdminOrSalesTeamOrManager($currentuser)) {
                return response()->json([
                    'status' => false,
                    'success' => "Error",
                ]);
            }

            $user_id = $request->user_id;
            $email = $request->email;
            $plan_id = $request->plan_id;
            $contact = $request->contact;
            $payment_method = $request->method;
            $currency_code = $request->currency_code;
            $transaction_id = $request->transaction_id;
            $priceAmount = $request->price_amount;
            $paidAmount = $request->paid_amount;
            $from_where = $request->fromWhere;
            $promo_code_id = $request->promo_code_id ?? 0;
            $additional_days = $request->additional_days ?? 0;
            $caricatures = $request->caricatures ?? 0;

            $subscription_type = $request->subscription_type ?? 'old_sub';
            $isNewPricingPlan = $subscription_type === 'new_sub';

            if ($email) $user_data = UserData::where("email", $email)->first();
            else $user_data = UserData::where("uid", $user_id)->first();

            if (!$user_data) {
                return response()->json([
                    'status' => false,
                    'success' => "User not found.",
                ]);
            }

            $user_id = $user_data->uid;


             // Update usage_purpose in personal_details if provided
            if ($request->has('usage_purpose') && !empty($request->usage_purpose)) {
                $personalDetails = \App\Models\PersonalDetails::where('uid', $user_id)->first();
                
                if ($personalDetails) {
                    $personalDetails->usage = $request->usage_purpose;
                    $personalDetails->save();
                } else {
                    // Create new personal details record if doesn't exist
                    \App\Models\PersonalDetails::create([
                        'uid' => $user_id,
                        'usage' => $request->usage_purpose,
                    ]);
                }
            }

            $data = PaymentController::enterTransData($user_id, $transaction_id, $payment_method, $plan_id, $currency_code, $from_where, 1);

            if ($data['success']) {
                return response()->json([
                    'status' => true,
                    'success' => $data['msg']
                ]);
            } else {

                $subData = Subscription::find($plan_id);
                if ($subData) {

                    if (TransactionLog::whereTransactionId($transaction_id)->exists()) {
                        return response()->json([
                            'status' => false,
                            'success' => "Found duplicate Transaction Id",
                        ]);
                    }

                    $expired_at = Carbon::parse(Carbon::now())->addDays($subData->validity + $additional_days);

                    $payment_id = HelperController::generateID('crafty_');
                    while (MasterPurchaseHistory::where('payment_id', $payment_id)->exists()) {
                        $payment_id = HelperController::generateID('crafty_');
                    }

                    $res = new TransactionLog();
                    $res->emp_id = Auth::user()->id;
                    $res->by_sales_team = 1;
                    $res->plan_id = $plan_id;
                    $res->user_id = $user_id;
                    $res->contact_no = $contact;
                    $res->payment_method = $payment_method;
                    $res->from_where = $from_where;
                    $res->payment_id = $payment_id;
                    $res->transaction_id = $transaction_id;
                    $res->payment_id = TransactionLog::generatePaymentId();
                    $res->promo_code_id = $promo_code_id;

                    if (!strcasecmp($currency_code, "INR")) {
                        $res->currency_code = "INR";
                        $res->price_amount = $subData->price;
                    } else {
                        $res->currency_code = "USD";
                        $res->price_amount = $subData->price_dollar;
                    }

                    $res->paid_amount = $paidAmount;
                    $res->net_amount = $paidAmount;
                    $res->coins = 0;
                    $res->isManual = 1;

                    $existedData = TransactionLog::where('transaction_id', $transaction_id)->get();

                    if ($existedData != null && $existedData->count() != 0) {
                        $response['success'] = true;
                        $response['msg'] = 'Purchase successfully.';
                        return $response;
                    }

                    TransactionLog::where('user_id', $user_id)->update(['status' => '0']);

                    $res->validity = $subData->validity + $additional_days;
                    $res->yearly = $subData->validity  >= 365 ? 1 : 0;
                    $res->expired_at = $expired_at;
                    $res->save();

                    $user_data->is_premium = "1";
                    $user_data->save();

                    $res = new MasterPurchaseHistory();
                    $res->product_type = 'old_sub';
                    $res->emp_id = Auth::user()->id;
                    $res->by_sales_team = 1;
                    $res->product_id = $plan_id;
                    $res->user_id = $user_id;
                    $res->contact_no = $contact;
                    $res->payment_id = $payment_id;
                    $res->payment_method = $payment_method;
                    $res->from_where = $from_where;
                    $res->transaction_id = $transaction_id;
                    $res->payment_id = TransactionLog::generatePaymentId();
                    $res->promo_code_id = $promo_code_id;

                    if (!strcasecmp($currency_code, "INR")) {
                        $res->currency_code = "INR";
                        $res->amount = $subData->price;
                    } else {
                        $res->currency_code = "USD";
                        $res->amount = $subData->price_dollar;
                    }

                    $res->paid_amount = $paidAmount;
                    $res->net_amount = $paidAmount;
                    $res->isManual = 1;

                    $res->validity = $subData->validity + $additional_days;
                    $res->yearly = $subData->validity  >= 365 ? 1 : 0;
                    $res->expired_at = $expired_at;
                    $res->save();

                    if ($caricatures > 0) {
                        $payment = HelperController::getCaricatureRates(2, false, false);
                        $credits = ($caricatures * $payment['inrVal']);
                        if ($credits) {
                            $resTrans = new AICreditTransaction();
                            $resTrans->user_id = $user_data->uid;
                            $resTrans->ref_id = $payment_id;
                            $resTrans->txn_id = AICreditTransaction::generateTxnId();
                            $resTrans->type = 'subscription';
                            $resTrans->reason = "Subscription Plan Purchased";
                            $resTrans->credited = $credits;
                            $resTrans->save();
                        }
                        $user_data->ai_credit = $user_data->ai_credit + $credits;
                        $user_data->save();
                    }

                    return response()->json([
                        'status' => true,
                        'success' => "User Subscription has been added successfully.",
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'success' => "Incorrect Data.",
                    ]);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updateManageSubscription(Request $request)
    {
        // try {
        //     $priceAmount = $request->price_amount;
        //     $paidAmount = $request->paid_amount;
        //     if($priceAmount != $paidAmount) {
        //         $disc = (($priceAmount - $paidAmount) / $priceAmount) * 100;
        //         $discount = (int)($disc);
        //     }
        //     $inputs = [
        //         "user_id" => $request->user_id,
        //         "plan_id" => $request->plan_id,
        //         "method" => $request->method,
        //         "transaction_id" => $request->transaction_id,
        //         "currency_code" => (isset($request->currency_code) && $request->currency_code == "INR") ? "RS" : "$",
        //         "discount" => $discount,
        //         "price_amount" => $request->price_amount,
        //         "paid_amount" => $request->paid_amount,
        //         "fromWallet" => $request->fromWallet,
        //         "fromWhere" => $request->fromWhere,
        //         "coins" => $request->coins,
        //     ];

        //     TransactionLog::where('id',$request->package_id)->update($inputs);
        //     return response()->json([
        //         'status' => true,
        //         'success' => "User Subscription has been updated successfully.",
        //     ]);

        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
        return response()->json([
            'status' => false,
            'success' => "Error",
        ]);
    }

    public function deleteManageSubscription( $sub_package_id = null )
    {
        // try {
        //     ManageSubscription::where('id',$sub_package_id)->delete();
        //     return response()->json([
        //         'status' => true,
        //         'success' => "User Subscription has been deleted successfully.",
        //     ]);

        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
        return response()->json([
            'status' => false,
            'success' => "Error",
        ]);
    }
}

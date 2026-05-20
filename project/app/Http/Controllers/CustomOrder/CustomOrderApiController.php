<?php
namespace App\Http\Controllers\CustomOrder;

use App\Http\Controllers\AesCipher;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CustomOrder\OrderTable;
use App\Models\CustomOrder\SizeTable;
use App\Models\CustomOrder\PricingTable;
use App\Models\Category;
use App\Models\UserData;
use App\Models\CoinTransaction;
use App\Models\PromoCode;
use App\Models\PromoCodeTranscation;
use Mail;
use Carbon\Carbon;
use Validator;


class CustomOrderApiController extends Controller
{

    function getOrderSizes(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $datas = Category::where("status", '1')->orderBy('sequence_number', 'ASC')->get();
            foreach ($datas as $data) {
                $datas[] = array(
                    'id' => $data->id,
                    'name' => $data->category_name,
                    'size' => $data->size
                );
            }

            $datas[] = array(
                    'id' => -1,
                    'name' => 'Other',
                    'size' => 'Other'
                );
            $response['success'] = 1;
            $response['datas'] = $datas;
            return $response;
        }
    }

    function getBasePrices(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $currency = $request->has('currency') ? $request->get('currency') : "INR";
            $currency = strtoupper($currency);
            $currency_symbol = "₹";

            if(strtoupper($currency) != "INR") {
                $currency = "USD";
                $currency_symbol = "$";
            }

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $reqRes = HelperController::checkRequestFields($request, array('currency'));
            if ($reqRes['success'] == 0) {
                return $reqRes;
            }

            $datas = PricingTable::all();
            $rows = array();
            foreach ($datas as $item) {
                $has_offer = false;
                $offer_msg = null;
                if(strtoupper($currency) == "INR") {
                    if($item->rate_inr != $item->offer_inr) {
                        $disc = (($item->rate_inr - $item->offer_inr) / $item->rate_inr) * 100;
                        $discount = (int)($disc);
                        $offer_msg = "Best Value (" . $discount . "% off)";
                        $has_offer = true;
                    }
                }

                if(strtoupper($currency) == "USD") {
                    if($item->rate_usd != $item->offer_usd) {
                        $disc = (($item->rate_usd - $item->offer_usd) / $item->rate_usd) * 100;
                        $discount = (int)($disc);
                        $offer_msg = "Best Value (" . $discount . "% off)";
                        $has_offer = true;
                    }
                }

                if(strtoupper($currency) == "INR") {
                    $price = round($item->offer_inr, 2);
                    $actual_price = round($item->rate_inr, 2);
                } else {
                    $price = round($item->offer_usd, 2);
                    $actual_price = round($item->rate_usd, 2);
                }

                $rows[] = array(
                    'type' => $item->type,
                    'name' => $item->name,
                    'currency' => $currency,
                    'actual_price' => $currency_symbol.$actual_price,
                    'offer_price' => $currency_symbol.$price,
                    'price' => $price,
                    'has_offer' => $has_offer,
                    'offer_msg' => $offer_msg,
                );
            }

            $response['success'] = 1;
            $response['currency'] = $currency;
            $response['currency_symbol'] = $currency_symbol;
            $response['datas'] = $rows;
            return $response;
        }
    }

    function customOrderWebhookTranscation(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $plan_id = $request->get('plan_id');
            $user_id = $request->get('user_id');
            $method = $request->get('method');
            $transaction_id = $request->get('transaction_id');
            $paid_amount = $request->get('paid_amount');
            $fromWallet = $request->has('fromWallet') ? $request->get('fromWallet') : 0;

            if ($plan_id == null || $user_id == null || $method == null || $transaction_id == null) {
                $response['type'] = 'error';
                $response['message'] = 'Paramaters Missing!';
                return $response;
            }

            $existedData = OrderTable::where('transaction_id', $transaction_id)->get();

            if ($existedData != null && $existedData->count() != 0) {
                $response['type'] = 'success';
                $response['message'] = 'Purchase successfully.';
                return $response; 
            }

            if (OrderTable::where('order_id', $plan_id)->exists()) {
                $data = OrderTable::where('order_id', $plan_id)->first();

                $coins = $data->amount - $paid_amount;
                if(strtoupper($data->currency) == "USD") {
                    $coins = $coins / 0.0125;
                    $coins = intval($coins);
                }

                $data->payment_method = $method;
                $data->transaction_id = $transaction_id;
                $data->paid_amount = $paid_amount;
                $data->coins = $coins;
                $data->discount =  $data->amount - $paid_amount;
                $data->payment_status = 1;
                $data->status = 1;
                $data->save();

                if ($fromWallet == 1) {

                	$coinTransaction = new CoinTransaction();
	                $coinTransaction->user_id = $user_id;
	                $coinTransaction->reason = "Paid for custom order.";
	                $coinTransaction->debited = $coins;
	                $coinTransaction->save();

	                $uData = UserData::where('uid', $user_id)->first();
	                $uData->coins = $uData->coins - $coins;
	                $uData->save();
	            }
            }
        }
    }

    public static function customOrderTranscation(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {

            $key = $request->get('key');
            $user_id = $request->get('user_id');
            $plan_id = $request->get('plan_id');
            $currency_code = $request->get('currency_code');
          
            if ($key == null) {
                $response['success'] = 0;
                $response['message'] = 'Paramaters Missing!';
                return $response;
            }

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $reqRes = HelperController::checkRequestFields($request, array('user_id', 'plan_id', 'currency_code'));
            if ($reqRes['success'] == 0) {
                return $reqRes;
            }

            if (OrderTable::where('order_id', $plan_id)->exists()) {
            	$data = OrderTable::where('order_id', $plan_id)->first();

                $coins = $data->amount;
                if(strtoupper($data->currency) == "USD") {
                    $coins = $coins / 0.0125;
                    $coins = intval($coins);
                }

                $data->payment_method = $method;
                $data->transaction_id = $this->generateTransID();
                $data->paid_amount = 0;
                $data->coins = $coins;
                $data->discount =  $data->amount;
                $data->payment_status = 1;
                $data->status = 1;
                $data->save();

	            if ($fromWallet == 1) {

	            	$coinTransaction = new CoinTransaction();
		            $coinTransaction->user_id = $user_id;
		            $coinTransaction->reason = "Paid for custom order.";
		            $coinTransaction->debited = $usedCoins;
		            $coinTransaction->save();

		            $uData = UserData::where('uid', $user_id)->first();
		            $uData->coins = $uData->coins - $usedCoins;
		            $uData->save();
		        }

		        $response['success'] = 1;
	            $response['message'] = 'Purchase successfully.';
	            return $response;
            } else {
            	$response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }           
    
        }
    }

    function customOrderRefund(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $paymentId = $request->get('paymentId');

            $reqRes = HelperController::checkRequestFields($request, array('paymentId'));
            if ($reqRes['success'] == 0) {
                return $reqRes;
            }

            $existedData = OrderTable::where('transaction_id', $paymentId)->first();

            if ($existedData != null) {
                $existedData->status = 5;
                $existedData->save();
                $response['type'] = 'success';
                $response['message'] = 'Purchase successfully.';
                return $response; 
            }
        }
    }

    function createOrder(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $uid = $request->get('user_id');
            $name = $request->get('name');
            $email = $request->get('email');
            $country_code = $request->get('country_code');
            $number = $request->get('number');
            $pages = $request->get('pages');
            $ref_temp_link = $request->get('ref_temp_link');
            $ref_temp_name = $request->get('ref_temp_name');
            $size = $request->get('size');
            $ref_images = $request->file('ref_images');
            $notes = $request->get('notes');
            $watermark = $request->get('watermark');
            $psd = $request->get('psd');
            $express = $request->get('express');
            $currency = $request->get('currency');
            $amount = $request->get('amount');
            $promo_code = $request->get('promo_code');
            $device_id = $request->get('device_id');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $idData = OrderTable::query()->latest()->first();
            $id = 0;
            if($idData == null) {
                $id = 0;
            } else {
                $id = $idData->id;
            }
            $id++;

            $orderId = $this->generateOrderID($id);

            if($amount == 0) {
                $exists = PromoCode::where('promo_code', $promo_code)->first();
                if ($exists != null) {
                    if ($exists->disc != 100) {
                        $response['success'] = 0;
                        $response['message'] = 'Something went wrong';
                        return $response;
                    }
                } else {
                    $response['success'] = 0;
                    $response['message'] = 'Something went wrong';
                    return $response;
                }
            }

            $res = new OrderTable();
            $res->order_id = $orderId;
            $res->user_id = $uid;
            $res->name = $name;
            $res->email = $email;
            $res->country_code = $country_code;
            $res->number = $number;
            $res->total_pages = $pages;
            $res->ref_temp_link = $ref_temp_link;
            $res->ref_temp_name = $ref_temp_name;
            $res->size = $size;
            $res->notes = $notes;
            $res->watermark = $watermark;
            $res->psd = $psd;
            $res->express = $express;
            $res->currency = $currency;
            $res->amount = $amount;
            $res->promo_code = $promo_code;
            if($amount == 0) {
                $res->payment_status = 1;
                $res->status = 1;
            }

            if($ref_images != null) {
                $validator = Validator::make($request->all(), [
                    'ref_images.*' => ['image', 'mimes:jpeg,png,jpg', 'max:111111'],
                ]);

                if ($validator->fails()) {
                    $response['success'] = 0;
                    $response['message'] = 'Only PNG or JPG allowed.';
                    return $response;
                }

                $st_count = count($ref_images);

                if ($st_count > 5) {
                    $response['success'] = 0;
                    $response['message'] = 'Max 5 images allowed.';
                    return $response;
                }

                $ref_images_data = array();
                for ($i = 0; $i < $st_count; $i++) {
                    $bytes = random_bytes(20);
                   
                    $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $ref_images[$i]->getClientOriginalExtension();
                    $ref_images[$i]->storeAs('uploadedFiles/customOrder/'.$orderId, $new_name, 'public');
                    $st_data = array();
                    $ref_images_data[] = 'uploadedFiles/customOrder/'.$orderId.'/'. $new_name;
 
                }
                $res->ref_images = json_encode($ref_images_data);
            } 

            $res->save();

            if ($promo_code != null || $promo_code != '') {
                $pct = new PromoCodeTranscation();
                $pct->user_id = $uid;
                $pct->promo_code = $promo_code;
                $pct->device_id = $device_id;
                $pct->save();
            }

            $response['success'] = 1;
            $response['message'] = "Order placed successfully";
            if($amount == 0) {
                $response['paymentAct'] = 0;
            } else {
                $response['paymentAct'] = 1;
            }
            $response['order_id'] = $orderId;
            return $response;

        }
    }

    function updateOrder(Request $request) {

        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $id = $request->get('id');
            $uid = $request->get('user_id');
            $name = $request->get('name');
            $email = $request->get('email');
            $country_code = $request->get('country_code');
            $number = $request->get('number');
            $pages = $request->get('pages');
            $ref_temp_link = $request->get('ref_temp_link');
            $ref_temp_name = $request->get('ref_temp_name');
            $size = $request->get('size');
            $ref_images = $request->file('ref_images');
            $notes = $request->get('notes');
            $watermark = $request->get('watermark');
            $psd = $request->get('psd');
            $express = $request->get('express');
            $currency = $request->get('currency');
            $amount = $request->get('amount');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $res = OrderTable::find($id);

            if($res == null) {
                $response['success'] = 0;
                $response['message'] = 'Order not found';
                return $response;
            }

            if($uid != $res->user_id) {
                $response['success'] = 0;
                $response['message'] = 'Data mismatched.';
                return $response;
            }

            $res->user_id = $uid;
            $res->name = $name;
            $res->email = $email;
            $res->country_code = $country_code;
            $res->number = $number;
            $res->total_pages = $pages;
            $res->ref_temp_link = $ref_temp_link;
            $res->ref_temp_name = $ref_temp_name;
            $res->size = $size;
            $res->notes = $notes;
            $res->watermark = $watermark;
            $res->psd = $psd;
            $res->express = $express;
            $res->currency = $currency;
            $res->amount = $amount;

            if($ref_images != null) {
                $validator = Validator::make($request->all(), [
                    'ref_images.*' => ['image', 'mimes:jpeg,png,jpg', 'max:111111'],
                ]);

                if ($validator->fails()) {
                    $response['success'] = 0;
                    $response['message'] = 'Only PNG or JPG allowed.';
                    return $response;
                }

                $count = 0;
                $ref_images_data = array();
                $datass = json_decode($res->ref_images);
                foreach ($datass as $image) {
                    $ref_images_data[] = $image;
                    $count++;
                } 

                $st_count = count($ref_images);

                $total_count = $st_count + $count;

                if ($total_count > 5) {
                    $response['success'] = 0;
                    $response['message'] = 'Max 5 images allowed.';
                    return $response;
                }
                
                for ($i = 0; $i < $st_count; $i++) {
                    $bytes = random_bytes(20);
                   
                    $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $ref_images[$i]->getClientOriginalExtension();
                    $ref_images[$i]->storeAs('uploadedFiles/customOrder/'.$res->order_id, $new_name, 'public');
                    $ref_images_data[] = 'uploadedFiles/customOrder/'.$res->order_id.'/'. $new_name;
 
                }
                $res->ref_images = json_encode($ref_images_data);
            } 

            $res->save();

            $response['success'] = 1;
            $response['message'] = "Order updated successfully";
            $response['order_id'] = $res->order_id;
            $response['startPayment'] = OrderTable::find($id)->status == 0;
            return $response;

        }
    }

    function deleteImage(Request $request) {

        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $id = $request->get('id');
            $uid = $request->get('user_id');
            $path = $request->get('path');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $res = OrderTable::find($id);
            if($res == null) {
                $response['success'] = 0;
                $response['message'] = 'Order not found';
                return $response;
            }

            if($uid != $res->user_id) {
                $response['success'] = 0;
                $response['message'] = 'Action is invalid.';
                return $response;
            }

            $ref_images_data = array();
            $datass = json_decode($res->ref_images);
            foreach ($datass as $image) {
                if($image != $path) {
                    $ref_images_data[] = $image;
                }
            } 

            try {
                unlink(storage_path("app/public/".$path));
                $res->ref_images = json_encode($ref_images_data);
                $res->save();
                $response['success'] = 1;
                $response['message'] = "Delete Successfully.";
            } catch (\Exception $e) {
                $response['success'] = 0;
                $response['message'] = "Unknown error occured.";
            }
            
            return $response;
        }
    }

    function cancelOrder(Request $request) {

        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $id = $request->get('id');
            $order_id = $request->get('order_id');
            $uid = $request->get('user_id');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $res = OrderTable::find($id);
            if($res == null) {
                $response['success'] = 0;
                $response['message'] = 'Order not found';
                return $response;
            }

            if($uid != $res->user_id || $order_id != $res->order_id) {
                $response['success'] = 0;
                $response['message'] = 'Action is invalid.';
                return $response;
            }

            $toDate = \Carbon\Carbon::parse($res->created_at)->addHours(1);

            if (Carbon::now() < $toDate) {
                $msg = "";
                if ($res->payment_status == 0) {
                    $res->status = 3;
                    $msg = "Order successfully canceled.";
                } else {
                    if ($res->payment_method == 'Crafty Coins') {
                        $res->status = 5;
                        $msg = "Order successfully canceled.";
                    } else {
                        $apiString = "";
                        if ($res->payment_method == 'Razorpay') {
                            $apiString = 'https://craftyverse.in/payment/razorpay/refund';
                        } else {
                            $apiString = 'https://craftyverse.in/payment/stripe/refund';
                        }

                        $apiResponse = Http::asForm()->post($apiString, [
                            'paymentId' => $res->transaction_id,
                            'amount' => $res->amount * 100,
                            'pay_mode' => 'customOrder',
                        ]);

                        if($apiResponse->successful()) {
                            $bodyData = json_decode($apiResponse->body());
                            if($bodyData->success == 1) {
                                $msg = "Order cancel request has been accepted we will refund you within 2-3 working days.";
                                $res->status = 4;
                            } else {
                                $msg = $bodyData->msg;
                            }
                        } else {
                            $msg = "Something went wrong please try again later.";
                        } 
                    }
                }
                $res->save();
                $response['success'] = 1;
                $response['message'] = $msg;
            } else {
                if ($res->payment_status == 0) {
                    $res->status = 3;
                    $res->save();
                    $response['success'] = 1;
                    $response['message'] = "Order successfully canceled.";
                } else {
                    $response['success'] = 0;
                    $response['message'] = "Time not valid.";
                }
            }
            
            return $response;
        }
    }

    function orderTimeValidate(Request $request) {

        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $order_id = $request->get('order_id');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $data = OrderTable::where("order_id", $order_id)->first();
            if ($data == null) {
                $response['success'] = 0;
                $response['message'] = "Order does not exists.";
            } else {
                $toDate = \Carbon\Carbon::parse($data->created_at)->addHours(1);

                if (Carbon::now() < $toDate) {
                    $response['success'] = 1;
                    $response['message'] = "Success";
                } else {
                    $response['success'] = 0;
                    $response['message'] = "Fail";
                }
            }
            
            return $response;
        }
    }

    function listOrder(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $uid = $request->get('user_id');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
            } else {
                $datas = OrderTable::where('user_id', $uid)->get()->sortByDesc("id");
                $rows = array();

                foreach ($datas as $data) {
                    $toDate = \Carbon\Carbon::parse($data->created_at)->addHours(1);
                    $edit_visibility = 0;
                    if (Carbon::now() < $toDate) {
                        $edit_visibility = 0;
                    } else {
                        $edit_visibility = 8;
                    }
                    $rows[] = array(
                        'url' => env('APP_URL', '') . "/",
                        'id' => $data->id,
                        'order_id' => $data->order_id,
                        'name' => $data->name,
                        'email' => $data->email,
                        'country_code' => $data->country_code,
                        'number' => $data->number,
                        'pages' => $data->total_pages,
                        'ref_temp_link' => $data->ref_temp_link,
                        'ref_temp_name' => $data->ref_temp_name,
                        'ref_images' => json_decode($data->ref_images),
                        'size' => $data->size,
                        'notes' => $data->notes,
                        'size' => $data->size,
                        'watermark' => $data->watermark,
                        'psd' => $data->psd,
                        'express' => $data->express,
                        'currency' => $data->currency,
                        'price' => $data->amount,
                        'amount' => $this->getCurrencySymbol($data->currency).' '. $data->amount,
                        'payment_pending' => $data->payment_status == 0,
                        'order_completed' => $data->order_completed == 2,
                        'status' => $data->status, 
                        'status_title' => $this->geStatusTitle($data->status), 
                        'status_msg' => $this->geStatusMsg($data->status),
                        'status_color' => $this->geStatusColor($data->status),
                        'edit_visibility' => $edit_visibility,
                        'order_date' => $data->created_at->format('d/m/Y H:i:s'), 
                    );
                }

                $response['success'] = 1;
                $response['message'] = 'Done';
                $response['datas'] = $rows;
            }
            
            return $response;
        }
    }

    public static function geStatusTitle($status)
    {
        $msg = "";

        if($status == 0) {
            $msg = "Payment Pending";
        } else if($status == 1) {
            $msg = "Work In Progress";
        } else if($status == 2) {
            $msg = "Completed";
        } else if($status == 3) {
            $msg = "Canceled";
        } else if($status == 4) {
            $msg = "Refund Accepted";
        } else if($status == 5) {
            $msg = "Refunded";
        }
        
        return $msg;
    }

    public static function geStatusMsg($status)
    {
        $msg = "";

        if($status == 0) {
            $msg = "Payment Pending --------- ";
        } else if($status == 1) {
            $msg = "Work In Progress  --------- ";
        } else if($status == 2) {
            $msg = "Completed  --------- ";
        } else if($status == 3) {
            $msg = "Canceled  --------- ";
        } else if($status == 4) {
            $msg = "Refund Accepted  --------- ";
        } else if($status == 5) {
            $msg = "Refunded  --------- ";
        }
        
        return $msg;
    }

    public static function geStatusMsg2($status)
    {
        $msg = "";

        if($status == 0) {
            $msg = "Payment Pending";
        } else if($status == 1) {
            $msg = "Pending Orders";
        } else if($status == 2) {
            $msg = "Completed";
        } else if($status == 3) {
            $msg = "Canceled";
        } else if($status == 4) {
            $msg = "Refund Accepted";
        } else if($status == 5) {
            $msg = "Refunded ";
        }
        
        return $msg;
    }

    public static function geStatusColor($status)
    {
        $color = "#ffffff";

        if($status == 0) {
            $color = "#FF0000";
        } else if($status == 1) {
            $color = "#FF9F1C";
        } else if($status == 2) {
            $color = "#31d2c3";
        } else if($status == 3) {
            $color = "#F8E11B";
        } else if($status == 4) {
            $color = "#FF9F1C";
        } else if($status == 5) {
            $color = "#FF0000";
        }
        
        return $color;
    }

    public static function generateOrderID($id, $length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $order_id = 'order_'.substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (OrderTable::where('order_id', $order_id)->exists());
        return $order_id;
    }

    public static function generateTransID($length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
			$order_id = 'crafty_'.substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
		} while (OrderTable::where('transaction_id', $order_id)->exists());
        return $order_id;
    }

    public static function getCurrencySymbol($currency)
    {
        if(strtoupper($currency) != "INR") {
            $currency_symbol = "$";
        } else {
            $currency_symbol = "₹";
        }
        return $currency_symbol;
    }


    
}

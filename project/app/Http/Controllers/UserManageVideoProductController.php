<?php

namespace App\Http\Controllers;

use App\Models\UserData;
use App\Http\Controllers\Api\PaymentController;
use App\Models\Video\VideoPurchaseHistory;
use App\Models\Video\VideoTemplate;
use Exception;
use Illuminate\Http\Request;

class UserManageVideoProductController extends AppBaseController
{
    public function manageVideoProductShow($userId)
    {
        $users = UserData::where('id',$userId)->first();
        $user_id = $users->uid;
        $resultData = VideoPurchaseHistory::where('user_id',$user_id)->get();
        return view('subscription.user_video_product',compact('resultData','userId','user_id'));
    }

    public function saveVideoProduct(Request $request)
    {   
        try {

            $user_id = $request->user_id;
            $product_id = $request->product_id;
            $currency_code = $request->currency_code;
            $transaction_id = $request->transaction_id;
            $amount = $request->amount;
            $payment_method = $request->payment_method;
            $from_where = $request->from_where;
            $status = $request->status;
            $product_type = $request->product_type;

            $user_data = UserData::where("uid", $user_id)->first();
            if (!$user_data) {
                return response()->json([
                    'status' => false,
                    'success' => "User not found.",
                ]);
            }

            $design = VideoTemplate::where('string_id', $request->product_id)->first();

            if (!$design) {
                return response()->json([
                    'status' => false,
                    'success' => "video not found, it might be template",
                ]);
            }

            $size = $desData->pages;
            $pyt = HelperController::getVideoRates($size);

            $pyt['id'] = $desData->string_id;
            $pyt['type'] = 4;

            $paymentDatas[] = $pyt;

            $assetDetails = json_encode($paymentDatas);

            $data = PaymentController::enterTransData($user_id, $transaction_id, $payment_method, $assetDetails, $currency_code, $from_where, 1);

            if ($data['success']) {
                return response()->json([
                    'status' => true,
                    'success' => "Purchase Video Product has been added successfully.",
                ]);
            } else {
                if (!VideoPurchaseHistory::where('user_id', $request->user_id)->where('product_id', $request->product_id)->where('product_type', $request->product_type)->exists()) {
                    $payment_id = $this->generateID();
                    while (VideoPurchaseHistory::where('payment_id', $payment_id)->exists()) {
                        $payment_id = $this->generateID();
                    }

                    $res = new VideoPurchaseHistory;
                    $res->payment_id = $payment_id;
                    $res->product_id = $product_id;
                    $res->currency_code = $currency_code;
                    $res->transaction_id = $transaction_id;
                    $res->amount = $amount;
                    $res->paid_amount = $amount;
                    $res->net_amount = $amount;
                    $res->payment_method = $payment_method;
                    $res->from_where = $from_where;
                    $res->status = $status;
                    $res->user_id = $user_id;
                    $res->product_type = $product_type;
                    $res->isManual = 1;
                    $res->save();

                    return response()->json([
                        'status' => true,
                        'success' => "Purchase Video Product has been added successfully.",
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'success' => "Already purchased.",
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

    public function updateVideoProduct(Request $request)
    {
        // try {
        //     $inputs = [
        //         "product_id" => $request->product_id,
        //         "currency_code" => $request->currency_code,
        //         "transaction_id" => $request->transaction_id,
        //         "amount" => $request->amount,
        //         "payment_method" => $request->payment_method,
        //         "from_where" => $request->from_where,
        //         "status" => $request->status,
        //         "user_id" => $request->user_id,
        //         "status" => $request->status,
        //     ];

        //     VideoPurchaseHistory::where('id',$request->id)->update($inputs);
        //     return response()->json([
        //         'status' => true,
        //         'success' => "Purchase Video Product has been updated successfully.",
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

    public function deleteVideoProduct( $id = null )
    {
        // try {
        //     VideoPurchaseHistory::where('id',$id)->delete();
        //     return response()->json([
        //         'status' => true,
        //         'success' => "Purchase Video Product has been deleted successfully.",
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

    public static function generateID($length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return 'crafty_'.substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}

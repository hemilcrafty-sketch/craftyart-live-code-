<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Payment\Gateways\PhonePeGateway;
use App\Http\Controllers\Payment\Gateways\RazorpayGateway;
use App\Http\Controllers\Payment\Gateways\StripeGateway;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\DomainChecker;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Utils\ValidEmail;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorDomainController;
use App\Models\BrandKit;
use App\Models\CoinTransaction;
use App\Models\Design;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\OTPTable;
use App\Models\Pricing\OfferPackage;
use App\Models\PurchaseHistory;
use App\Models\Pricing\SubPlan;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\Revenue\UserSubscriptions;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserActivity;
use App\Models\UserData;
use App\Models\UserDataDeleted;
use App\Models\UserEmailChangeLog;
use App\Models\UserSession;
use App\Services\PaymentGateway;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Razorpay\Api\Api;
use Stripe\StripeClient;

class UserController extends ApiController
{

    protected ?Auth $auth = null;

    public function __construct(Request $request, Auth|null $auth = null)
    {
        parent::__construct($request);
        if ($auth == null) {
            $serviceAccountPath = base_path("private-files/firebase-service-account.json");
            if (file_exists($serviceAccountPath)) {
                $factory = (new Factory)->withServiceAccount($serviceAccountPath);
                $this->auth = $factory->createAuth();
            }
        } else {
            $this->auth = $auth;
        }
    }

    function getUser(Request $request, $minimalResponse = false): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $user_data = UserData::where("uid", $this->uid)->first();
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", $this->getUserRes(request: $request, userData: $user_data, minimalResponse: $minimalResponse)));
    }

    function createUser(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $fetch = $request->get('fetch', false);
        $check = $request->get('check', false);
        $get = $request->get('get', false);

//        if ($fetch) return $this->getUser($request, true);
        if ($check) return $this->userExist($request);
        if ($get) return $this->getUser($request);

        $photo_uri = $request->get('photo_uri');
        $name = $request->get('name');
        $email = $request->get('email');
        $login_type = $request->get('type');
        $device_id = $request->get('device_id', "");
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        $user_data = UserData::where("email", $email)->first();

        if (!$user_data) {
            $isExists = $this->checkFirebaseUid($email);
            if (!$isExists['registered']) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Something went wrong"));
            }

            $userInfo = $isExists['user'];
            $result = $this->addUser($request, $userInfo['uid'], $photo_uri, $name, $email, null, $login_type, $device_id, $utm_medium, $utm_source);
            if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
            $user_data = $result['data'];
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", $this->getUserRes(request: $request, userData: $user_data, isNewUser: true)));
    }

    function updateUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

//        if ($this->isTester()) {
//            return $this->generateUsernamesForAll();
//        }

        $photo_uri = $request->file('photo_uri');
        $name = $request->get('name');
        $contactNo = $request->get('contact_no');
        $updateDp = $request->get('update_dp');

        $userData = UserData::where("uid", $this->uid)->first();

        if ($request->has('bio')) {
            if (strlen($request->bio) > 100) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Bio must be at most 100 characters.'));
            }
            $userData->bio = $request->bio;
        }

        if (isset($request->user_name) && $request->user_name !== $userData->user_name) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $request->user_name)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Username can only contain letters, numbers, and underscores.'));
            }
            $existingUser = UserData::where('user_name', $request->user_name)->first();
            if ($existingUser) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username already taken by another user.'));
            }
            if ($userData->is_username_update == 1) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username can only be updated once.'));
            }
            $userData->user_name = $request->user_name;
            $userData->is_username_update = 1;
        }

        if ($photo_uri == null) {
            if ($updateDp == 1) {
                $userData->photo_uri = null;
            }
        } else {
            $new_name = $this->uid . '-' . HelperController::generateID('') . '.png';
            StorageUtils::delete($userData->photo_uri);
            StorageUtils::storeAs($photo_uri, 'uploadedFiles/user_dp', $new_name);
            $new_photo_uri = 'uploadedFiles/user_dp/' . $new_name;
            $userData->photo_uri = $new_photo_uri;
        }

        $userData->name = $name;
//        if (!empty($contactNo)) $userData->contact_no = $contactNo;
        $userData->save();

        $userData = UserData::where("uid", $this->uid)->first();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "User updated successfully.", $this->getNewUserRes($request, $userData)));
    }

    // function deleteUser(Request $request): array|string
    // {
    //     if ($this->isFakeRequestAndUser($request)) {
    //         return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
    //     }

    //     $otp = $request->get('otp');
    //     $idToken = $request->get('idToken');

    //     if ($otp == null || $idToken == null) {
    //         return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
    //     }

    //     $user_data = UserData::where('uid', $this->uid)->first();

    //     $data = OTPTable::where('mail', $user_data->email)->where('type', 'delete_acc')->get()->last();

    //     if (!$data || $data->status == "0" || $data->otp != $otp) {
    //         return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid OTP"));
    //     }

    //     $res = OTPTable::find($data->id);
    //     $res->status = "0";
    //     $res->save();

    //     try {

    //         $client = new Client();

    //         $fbaseRes = $client->post('https://identitytoolkit.googleapis.com/v1/accounts:delete?key=AIzaSyCQP7F26DBVJvXWNgwS3lerBUCGcbH2z4U', [
    //             'headers' => [
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'idToken' => $idToken,
    //             ],
    //         ]);

    //         $statusCode = $fbaseRes->getStatusCode();
    //         if ($statusCode != 200) {
    //             $response['success'] = false;
    //             $response['message'] = 'Bad request';
    //             return $response;
    //         }

    //         $data = json_decode($fbaseRes->getBody(), true);

    //         if (isset($data['error'])) {
    //             return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Bad Request"));
    //         }

    //         if (UserSubscriptions::whereUserId($user_data->uid)->where('status', '!=', 'cancelled')->exists()) {
    //             $oldDatas = UserSubscriptions::whereUserId($user_data->uid)->where('status', '!=', 'cancelled')->get();

    //             $razorpayGateway = PaymentGateway::initByGateway('razorpay', null);
    //             $stripeGateway = PaymentGateway::initByGateway('stripe', null);
    //             $phonepeGateway = PaymentGateway::initByGateway('phonepe_pg', null);

    //             /** @var Api|null $razorpay */
    //             $razorpay = $razorpayGateway?->client;

    //             /** @var StripeClient|null $stripeClient */
    //             $stripeClient = $stripeGateway?->client;

    //             $cancellationReason = json_encode(["Delete Account"]);

    //             foreach ($oldDatas as $oldData) {
    //                 if ($oldData->payment_gateway == "razorpay") {
    //                     $errorMsg = RazorpayGateway::cancelSubscription($razorpayGateway, $oldData->subscription_id);
    //                 } else if ($oldData->payment_gateway == "stripe") {
    //                     $errorMsg = StripeGateway::cancelSubscription($stripeGateway, $oldData->subscription_id);
    //                 } else if ($oldData->payment_gateway == "phonepe_pg") {
    //                     $errorMsg = PhonepeGateway::cancelSubscription($phonepeGateway, $oldData->subscription_id);
    //                 }

    //                 if (empty($errorMsg)) {
    //                     $oldData->cancellation_reason = $cancellationReason;
    //                     $oldData->status = 'cancelled';
    //                     $oldData->save();
    //                 }
    //             }
    //         }


    //         $res = new UserDataDeleted();
    //         $res->user_int_id = $user_data->id;
    //         $res->uid = $user_data->uid;
    //         $res->refer_id = $user_data->refer_id;
    //         $res->stripe_cus_id = $user_data->stripe_cus_id;
    //         $res->razorpay_cus_id = $user_data->razorpay_cus_id;
    //         $res->photo_uri = $user_data->photo_uri;
    //         $res->name = $user_data->name;
    //         $res->country_code = $user_data->country_code;
    //         $res->number = $user_data->number;
    //         $res->email = $user_data->email;
    //         $res->login_type = $user_data->login_type;
    //         $res->total_validity = $user_data->total_validity;
    //         $res->validity = $user_data->validity;
    //         $res->ai_credit = $user_data->ai_credit;
    //         $res->is_premium = $user_data->is_premium;
    //         $res->special_user = $user_data->special_user;
    //         $res->can_update = $user_data->can_update;
    //         $res->utm_source = $user_data->utm_source;
    //         $res->utm_medium = $user_data->utm_medium;
    //         $res->coins = $user_data->coins;
    //         $res->device_id = $user_data->device_id;
    //         $res->fldr_str = $user_data->fldr_str;
    //         $res->creation_date = $user_data->created_at;
    //         $res->save();

    //         UserData::where('uid', $this->uid)->delete();

    //         return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Your account has been successfully deleted."));

    //     } catch (RequestException $e) {
    //         $message = $e->getMessage();

    //         if ($e->hasResponse()) {
    //             $fbaseRes = $e->getResponse();
    //             $statusCode = $fbaseRes->getStatusCode();
    //             if ($statusCode === 400) {
    //                 $errorData = json_decode($fbaseRes->getBody()->getContents(), true);
    //                 $message = $errorData['error']['message'];
    //             }
    //         }

    //         return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $message));
    //     }

    // }
    function deleteUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $otp = $request->get('otp');
        //        $idToken = $request->get('idToken');

        if ($otp == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
        }

        $user_data = UserData::where('uid', $this->uid)->first();

        $data = OTPTable::where('mail', $user_data->email)->where('type', 'delete_acc')->get()->last();

        if (!$data || $data->status == "0" || $data->otp != $otp) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid OTP"));
        }

        $res = OTPTable::find($data->id);
        $res->status = "0";
        $res->save();

        try {

            //            $client = new Client();
//
//            $fbaseRes = $client->post('https://identitytoolkit.googleapis.com/v1/accounts:delete?key=AIzaSyCQP7F26DBVJvXWNgwS3lerBUCGcbH2z4U', [
//                'headers' => [
//                    'Content-Type' => 'application/json',
//                ],
//                'json' => [
//                    'idToken' => $idToken,
//                ],
//            ]);
//
//            $statusCode = $fbaseRes->getStatusCode();
//            if ($statusCode != 200) {
//                $response['success'] = false;
//                $response['message'] = 'Bad request';
//                return $response;
//            }
//
//            $data = json_decode($fbaseRes->getBody(), true);
//
//            if (isset($data['error'])) {
//                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Bad Request"));
//            }

            if (UserSubscriptions::whereUserId($user_data->uid)->where('status', '!=', 'cancelled')->exists()) {
                $oldDatas = UserSubscriptions::whereUserId($user_data->uid)->where('status', '!=', 'cancelled')->get();

                $razorpayGateway = PaymentGateway::initByGateway('razorpay', null);
                $stripeGateway = PaymentGateway::initByGateway('stripe', null);
                $phonepeGateway = PaymentGateway::initByGateway('phonepe_pg', null);

                $cancellationReason = json_encode(["Delete Account"]);

                foreach ($oldDatas as $oldData) {
                    if ($oldData->payment_gateway == "razorpay") {
                        $errorMsg = RazorpayGateway::cancelSubscription($razorpayGateway, $oldData->subscription_id);
                    } else if ($oldData->payment_gateway == "stripe") {
                        $errorMsg = StripeGateway::cancelSubscription($stripeGateway, $oldData->subscription_id);
                    } else if ($oldData->payment_gateway == "phonepe_pg") {
                        $errorMsg = PhonepeGateway::cancelSubscription($phonepeGateway, $oldData->subscription_id);
                    }

                    if (empty($errorMsg)) {
                        $oldData->cancellation_reason = $cancellationReason;
                        $oldData->status = 'cancelled';
                        $oldData->save();
                    }
                }
            }


            $res = new UserDataDeleted();
            $res->user_int_id = $user_data->id;
            $res->uid = $user_data->uid;
            $res->refer_id = $user_data->refer_id;
            $res->stripe_cus_id = $user_data->stripe_cus_id;
            $res->razorpay_cus_id = $user_data->razorpay_cus_id;
            $res->photo_uri = $user_data->photo_uri;
            $res->name = $user_data->name;
            $res->country_code = $user_data->country_code;
            $res->number = $user_data->number;
            $res->email = $user_data->email;
            $res->login_type = $user_data->login_type;
            $res->total_validity = $user_data->total_validity;
            $res->validity = $user_data->validity;
            $res->ai_credit = $user_data->ai_credit;
            $res->is_premium = $user_data->is_premium;
            $res->special_user = $user_data->special_user;
            $res->can_update = $user_data->can_update;
            $res->utm_source = $user_data->utm_source;
            $res->utm_medium = $user_data->utm_medium;
            $res->coins = $user_data->coins;
            $res->device_id = $user_data->device_id;
            $res->fldr_str = $user_data->fldr_str;
            $res->creation_date = $user_data->created_at;
            $res->save();

            UserData::where('uid', $this->uid)->delete();

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Your account has been successfully deleted."));

        } catch (RequestException $e) {
            $message = $e->getMessage();

            if ($e->hasResponse()) {
                $fbaseRes = $e->getResponse();
                $statusCode = $fbaseRes->getStatusCode();
                if ($statusCode === 400) {
                    $errorData = json_decode($fbaseRes->getBody()->getContents(), true);
                    $message = $errorData['error']['message'];
                }
            }

            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $message));
        }

    }

    private function userExist(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $email = $request->get('email');

        $user_data = UserData::where("uid", $email)->first();
        if ($user_data == null) {
            $user_data = UserData::where("email", $email)->first();
        }

        if ($user_data) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", $this->getUserRes($request, $user_data)));
        }

        $isExists = $this->checkFirebaseUid($email);
        if (!$isExists['registered']) {
            $isExists = $this->checkFirebaseUid($email);
        }

        if (!$isExists['registered']) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not exist"));
        }

        $userInfo = $isExists['user'];
        $result = $this->addUser($request, $userInfo['uid'], $userInfo['photoUrl'], $userInfo['name'], $userInfo['email'], null, "email", null, "craftyart", "craftyart");

        if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
        $user_data = $result['data'];

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", $this->getUserRes(request: $request, userData: $user_data, isNewUser: true)));
    }

    public function addUser(Request $request, $uid, $photo_uri, $name, $email, $number, $login_type, $device_id, $utm_medium, $utm_source, $password = null): array
    {
        $isValid = ValidEmail::passes($email);
        if (!is_null($isValid)) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, $isValid));
        }

        $res = new UserData();
        $res->uid = $uid;
        $res->refer_id = $this->generateReferID();
        $res->photo_uri = $photo_uri;
        $res->name = $name;
        $res->email = $email;
        $res->password = $password;
        $res->contact_no = $number;
        $res->login_type = $login_type;
        $res->is_premium = "0";
        $res->utm_medium = $utm_medium;
        $res->utm_source = $utm_source;
        $res->device_id = $device_id;

        $referralCode = $request->header('Referral-Code') ?? $request->get('referral_code') ?? $request->get('refer_id');
        \Log::info("Signup Referral Check (Main UserController):", [
            'header_ref' => $request->header('Referral-Code'),
            'body_ref' => $request->get('referral_code'),
            'utm_source' => $utm_source,
            'final_ref_code' => $referralCode
        ]);

        if ($referralCode) {
            $refer_user = UserData::where("refer_id", $referralCode)->first();
            \Log::info("Referral User Found:", ['user' => $refer_user ? $refer_user->id : 'not found']);
            if ($refer_user) {
                $res->referral_code = $referralCode;
                $res->referral_user_id = $refer_user->id;
                
                $refer_user->coins = $refer_user->coins + 10;
                $refer_user->save();

                $coinTransaction = new CoinTransaction();
                $coinTransaction->user_id = $refer_user->uid;
                $coinTransaction->refered_user = $uid;
                $coinTransaction->reason = $name . " has just logged in through your referral link.";
                $coinTransaction->credited = 10;
                $coinTransaction->save();
            }
        } elseif ($utm_source) {
             $refer_user = UserData::where("refer_id", $utm_source)->first();
             if ($refer_user != null) {
                $res->referral_code = $utm_source;
                $res->referral_user_id = $refer_user->id;
                
                $refer_user->coins = $refer_user->coins + 10;
                $refer_user->save();

                $coinTransaction = new CoinTransaction();
                $coinTransaction->user_id = $refer_user->uid;
                $coinTransaction->refered_user = $uid;
                $coinTransaction->reason = $name . " has just logged in through your referral link.";
                $coinTransaction->credited = 10;
                $coinTransaction->save();
            }
        }
        $res->save();

        $user_data = UserData::where("uid", $uid)->first();
        if ($user_data) $user_data->business_user = 0;

        FbPixel::trackEvent(FacebookEvent::COMPLETE_REGISTRATION, $request, $name, $email);

        $isNull = is_null($user_data);
        $statusCode = $isNull ? 404 : 200;
        $success = !$isNull;
        $msg = $isNull ? "Something went wrong." : "valid";
        return ResponseHandler::sendRealResponse(new ResponseInterface($statusCode, $success, $msg, ['data' => $user_data]));
    }

    public function getUserRes(Request $request, UserData $userData, $isNewUser = false, $minimalResponse = false): array
    {
        if (!$userData->user_name || empty($userData->user_name)) {
            $userName = self::generateUserName();
            $userData->user_name = $userName;
            UserData::where('id', $userData->id)->update(['user_name' => $userName]);
        }

        $this->addBrandKit($userData);

        //      business_user: number
        $user['uid'] = $userData->uid;
        $user['name'] = $userData->name;
        $user['email'] = $userData->email;
        $user['number'] = $userData->number;
        $user['contact_no'] = $userData->contact_no;
        $user['country_code'] = $userData->country_code;
        $user['user_name'] = $userData->user_name;
        $user['is_username_update'] = $userData->is_username_update == 1;
        $user['bio'] = $userData->bio;
        $user['creator'] = $userData->creator == 1;
        $user['hoc'] = $userData->hoc;
        $user['photo_uri'] = $userData->photo_uri;
        $user['ai_credit'] = $userData->ai_credit;
        $user['profile_view'] = $this->formatCount($userData->profile_count);

        //        $user['cancel_sub'] = false;
        if (str_contains($userData->photo_uri, 'uploadedFiles/')) {
            $user['photo_uri'] = HelperController::$mediaUrl . $userData->photo_uri;
        }

        $videoLeft = $this->getUsersVideoLimit($userData->uid);
        $user['total_video_limit'] = $videoLeft['limit'];
        $user['video_left'] = $videoLeft['left'];

        $subData = $this->getUserSubHistory($request, $userData);

        $subExists = TransactionLog::whereUserId($userData->uid)->whereSubscriptionIsActive(1)->whereNotNull('subscription_id')->exists();
        //        $subExists = false;
        $user['cancel_sub'] = $subExists;
        if ($subData["current"]) {
            $amount = $subData["current"]["amount"];
            $billing_date = $subData["current"]["billing_date"];
            if ($subExists) $user['expiry_msg'] = "Your next billing date is $billing_date";
            else $user['expiry_msg'] = "Your current plan $amount is end on $billing_date";
        }

        if ($subData["current"]) {
            $user['is_premium'] = 1;
        } else {
            $user['is_premium'] = DomainChecker::isValidSpecialUser($request, $userData);
        }

        $response['user'] = $user;
        $response['isNewUser'] = $isNewUser;
        if (!$minimalResponse) {
            $response['currentPlan'] = $subData["current"];
            $response['subsHistory'] = $subData["history"];
            $response['purHistory'] = $this->getUserPurchaseHistory($userData);
        }

        $response['ipData'] = HelperController::getIpAndCountry($request);

        return $response;
    }

    public function getNewUserRes(Request $request, UserData $userData, $isSessionCheck = false, $isNewUser = false, $minimalResponse = false): array
    {
        if (!$userData->user_name || empty($userData->user_name)) {
            $userName = self::generateUserName();
            $userData->user_name = $userName;
            UserData::where('id', $userData->id)->update(['user_name' => $userName]);
        }

        $this->addBrandKit($userData);
        $freelancerSummary = VendorController::affiliateSummary($userData->uid, 'freelancer');
        $RevenueHistorySummary = VendorController::affiliateSummary($userData->uid, 'affiliate');
            
        $user['uid'] = $userData->uid;
        $user['name'] = $userData->name;
        $user['email'] = $userData->email;
        $user['number'] = $userData->number;
        $user['contact_no'] = $userData->contact_no;
        $user['country_code'] = $userData->country_code;
        $user['user_name'] = $userData->user_name;
        $user['is_username_update'] = $userData->is_username_update == 1;
        $user['bio'] = $userData->bio;
        $user['creator'] = $userData->creator == 1;
        $user['hoc'] = $userData->hoc;
        $user['is_vendor'] = empty(VendorDomainController::isValidVendor($userData->uid));
        $user['photo_uri'] = $userData->photo_uri;
        $user['ai_credit'] = $userData->ai_credit;
        $user['refer_id'] = $userData->refer_id;
        $user['freelancer_earnings'] = $freelancerSummary['earnings'];
        $user['freelancer_withdraw'] = $freelancerSummary['withdraw'];
        $user['available_freelancer_amount'] = $freelancerSummary['available'];
        $user['referral_earnings'] = $RevenueHistorySummary['earnings'];
        $user['referral_withdraw'] = $RevenueHistorySummary['withdraw'];
        $user['available_referral_amount'] = $RevenueHistorySummary['available'];
        // $user['available_referral_amount'] = VendorController::getReferralCoins($userData->uid);
        if (str_contains($userData->photo_uri, 'uploadedFiles/')) {
            $user['photo_uri'] = HelperController::$mediaUrl . $userData->photo_uri;
        }

        $videoLeft = $this->getUsersVideoLimit($userData->uid);
        $user['total_video_limit'] = $videoLeft['limit'];
        $user['video_left'] = $videoLeft['left'];

        $user['total_spent'] = MasterPurchaseHistory::whereUserId($userData->uid)->wherePaymentStatus('paid')->sum('paid_amount');

        $subData = $this->getUserSubHistory($request, $userData);

        $subExists = UserSubscriptions::whereUserId($userData->uid)->latest()->first();
//        $subExists = false;
        $user['cancel_sub'] = !($subExists?->status == "cancelled");
        if ($subData["current"]) {
            $amount = $subData["current"]["amount"];
            $billing_date = $subData["current"]["billing_date"];
            if ($subExists) $user['expiry_msg'] = "Your next billing date is $billing_date";
            else $user['expiry_msg'] = "Your current plan $amount is end on $billing_date";
        }

        if ($subData["current"]) {
            $user['is_premium'] = 1;
        } else {
            $user['is_premium'] = DomainChecker::isValidSpecialUser($request, $userData);
        }

//        $user['is_internal_user'] = $userData->special_user == 1;
        $user['is_internal_user'] = $userData->email == 'viddhi.crafty@gmail.com';

//        if ($isSessionCheck) {
        $response['user_details'] = [
            'device_limit' => $subData['device_limit'] ?? ((int)($userData->device_limit ?? 1)),
            'active_sessions' => UserSession::whereUserId($userData->uid)->get(),
        ];
//        }

        $response['user'] = $user;
        $response['isNewUser'] = $isNewUser;
        if (!$minimalResponse) {
            $response['currentPlan'] = $subData["current"];
            $response['subsHistory'] = $subData["history"];
            $response['purHistory'] = $this->getUserPurchaseHistory($userData);
        }   

        $response['ipData'] = HelperController::getIpAndCountry($request);

        return $response;
    }

    public static function getUsersVideoLimit($uid): array
    {
        return [
            "limit" => 50,
            "left" => ExportTable::where('uid', $uid)->where('path', 'LIKE', '%.mp4')->where('created_at', '>=', now()->subDay())->count()
        ];
    }

    private function getUserSubHistory(Request $request, UserData $user_data): array|null
    {

        $singleData = null;
        $transLog = SubscriptionController::getActivePlan($user_data->uid);
        if ($transLog != null) {
            $subRow = null;
            if ($transLog->type == 0) $subRow = Subscription::find($transLog->plan_id);
            else if ($transLog->type == 1) {
                $subRow = SubPlan::with(['plan'])
                    ->where(function ($query) use ($transLog) {
                        $query->where('id', $transLog->plan_id)
                            ->orWhere('string_id', $transLog->plan_id);
                    })->first();
            } else if ($transLog->type == 2) {
                $subRow = OfferPackage::with(['plan'])
                    ->where(function ($query) use ($transLog) {
                        $query->where('id', $transLog->plan_id)
                            ->orWhere('string_id', $transLog->plan_id);
                    })->first();
            }

            if ($subRow) {
                $purchaseDate = $transLog->created_at;
                $billingDate = Carbon::parse($transLog->expired_at);
                $days = $purchaseDate->diffInDays($billingDate, false);

                $singleData['package_name'] = $transLog->type == 0 ? $subRow->package_name : $subRow->plan->name;
                $singleData['transaction_id'] = $transLog->transaction_id;
                if ($transLog->currency_code == 'Trial') {
                    $singleData['amount'] = "Trial";
                } else {
                    $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                    $singleData['amount'] = $currency_code . $transLog->paid_amount;
                }
                $singleData['method'] = $transLog->payment_method;
                $singleData['purchase_date'] = $purchaseDate->format('d/m/Y H:i:s');
                $singleData['billing_date'] = Carbon::parse($billingDate)->format('d/m/Y H:i:s');
                $singleData['validity'] = SubscriptionController::findTimeLeft($transLog->expired_at);
                $singleData['plan_validity'] = self::formatDays($days);
                $singleData['status'] = HelperController::checkSubsStatus($transLog->status);
                $singleData['color'] = HelperController::getSubsColor($transLog->status);
                $user_data->is_premium = 1;
                $user_data->business_user = $transLog->plan_id == 20 ? 1 : 0;

                $planLimit = collect($transLog->plan_limit);
                $deviceLimitData = $planLimit->where('slug', 'device_limit')->first();
                $seats = $deviceLimitData['limit'] ?? 1;

                $singleData['plan_limit'] = $planLimit->toArray();
                $singleData['device_limit'] = $seats;
            }
        } else {
            $user_data->is_premium = DomainChecker::isValidSpecialUser($request, $user_data);
            $user_data->business_user = 0;
        }

        $multiData = array();
        $transData = TransactionLog::where("user_id", $user_data->uid)->orderBy('id', 'DESC')->get();
        if ($transData != null && $transData->count() != 0) {
            foreach ($transData as $transLog) {
                $subRow = null;
                if ($transLog->type == 0) $subRow = Subscription::find($transLog->plan_id);
                else if ($transLog->type == 1) {
                    $subRow = SubPlan::with(['plan'])
                        ->where(function ($query) use ($transLog) {
                            $query->where('id', $transLog->plan_id)
                                ->orWhere('string_id', $transLog->plan_id);
                        })->first();
                } else if ($transLog->type == 2) {
                    $subRow = OfferPackage::with(['plan'])
                        ->where(function ($query) use ($transLog) {
                            $query->where('id', $transLog->plan_id)
                                ->orWhere('string_id', $transLog->plan_id);
                        })->first();
                }

                if (!$subRow) continue;
                if ($transLog->type == 1 && !$subRow->plan) continue;

                if ($transLog->currency_code == 'Trial') {
                    $amount = "Trial";
                } else {
                    $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                    $amount = $currency_code . $transLog->paid_amount;
                }

                $purchaseDate = $transLog->created_at;
                $billingDate = Carbon::parse($transLog->expired_at);
                $days = $purchaseDate->diffInDays($billingDate, false);

                $planLimit = collect($transLog->plan_limit);
                $deviceLimitData = $planLimit->where('slug', 'device_limit')->first();
                $seats = $deviceLimitData['limit'] ?? 1;

                $multiData[] = array(
                    'package_name' => $transLog->type == 0 ? $subRow->package_name : $subRow->plan->name,
                    'transaction_id' => $transLog->transaction_id,
                    'amount' => $amount,
                    'method' => $transLog->payment_method,
                    'purchase_date' => $purchaseDate->format('d/m/Y H:i:s'),
                    'billing_date' => $billingDate->format('d/m/Y H:i:s'),
                    'validity' => $days . " Days",
                    'plan_validity' => self::formatDays($days),
                    'plan_limit' => $planLimit->toArray(),
                    'device_limit' => $seats,
                    'status' => HelperController::checkSubsStatus($transLog->status),
                    'color' => HelperController::getSubsColor($transLog->status)
                );
            }
        }

        return ["current" => $singleData, "history" => $multiData];
    }

    private function getUserPurchaseHistory(UserData $user_data): array|null
    {
        $purchaseDatas = PurchaseHistory::where('user_id', $user_data->uid)->where('payment_status', 1)->where('status', 1)->get();

        $purchase_rows = [];

        if ($purchaseDatas != null && $purchaseDatas->count() != 0) {
            foreach ($purchaseDatas as $purchaseData) {
                $purchase_rows[] = array(
                    'id' => $purchaseData->product_id,
                    'type' => $purchaseData->product_type,
                );
            }
        }

        return $purchase_rows;
    }

    private function addBrandKit(UserData $user_data): void
    {
        $brand_res = Brandkit::where('user_id', $user_data->uid)->first();
        if ($brand_res == null) {
            $brand_res = new Brandkit();
            $brand_res->user_id = $user_data->uid;
            $brand_res->name = $user_data->name;
            $brand_res->email = $user_data->email;
            if ($user_data->number != null) {
                $brand_res->primary_number = $user_data->country_code . " " . $user_data->number;
            }
            $brand_res->save();
        }
    }

    public function changeEmailSubscribe(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $user = UserData::where('uid', $this->uid)->first();

        $type = $user->email_preferance;
        if (is_string($type)) {
            $type = json_decode($type, true);
        }
        if (!is_array($type)) {
            $type = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
        }
        foreach ($type as $key => $val) {
            $type[$key] = $val == 0 ? 1 : 0;
        }
        $user->email_preferance = json_encode($type);
        $user->save();
        $responseType = $type['offer'];
        $statusText = $responseType === 1 ? 'You have subscribed' : 'You have unsubscribed';
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $statusText, [
            'type' => $responseType,
            'subscription' => $type,
        ]));
    }

    public function getSubscribeStatus(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $user = UserData::where('uid', $this->uid)->first();

        $subscription = $user->email_preferance;
        if (empty($subscription)) {
            $subscription = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
            $user->email_preferance = json_encode($subscription);
            $user->save();
        } else {
            $subscription = json_decode($subscription, true);
            if (!is_array($subscription)) {
                $subscription = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
                $user->email_preferance = json_encode($subscription);
                $user->save();
            }
        }
        $type = $subscription['offer'];
        $boolSubscription = array_map(fn($val) => (bool)$val, $subscription);
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'success', [
            'type' => $type,
            'subscription' => $boolSubscription,
        ]));
    }

    private function formatCount($count): string
    {
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        }
        if ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        return (string)$count;
    }

    public function getPortfolio(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $limit = HelperController::getPaginationLimit(size: 50);
        $page = $request->input('page', 1);
        $uidInput = $request->user_name;

        $isJdMakwana = $uidInput === 'jignesh-makwana';

        if ($isJdMakwana) $uidInput = "vishrutivaghani";

        $userData = UserData::where('user_name', $uidInput)->where('creator', 1)->first();
        if (!$userData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
        if ($page == 1 && $this->uid !== $userData->uid) {
            $userData->increment('profile_count', 1);
            $userData->save();
        }
        $formatedCount = $this->formatCount($userData->profile_count);
        $query = Design::where('creator_id', $userData->uid);
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('h2_tag', 'like', '%' . $searchTerm . '%')
                    ->orWhere('id_name', 'like', '%' . $searchTerm . '%');
            });
        }
        $filter = $request->input('filter');
        if ($filter && isset($filter['id'], $filter['type'])) {
            $filterId = $filter['id'];
            $filterType = $filter['type'];
            if ($filterType === 'child') {
                $query->where('new_category_id', $filterId);
            } elseif ($filterType === 'All' || $filterType === 'all') {
                $category = NewCategory::find($filterId);
                if ($category) {
                    if ($category->parent_category_id != 0) {
                        $query->where('new_category_id', $filterId);
                    } else {
                        $childIds = NewCategory::where('parent_category_id', $filterId)->pluck('id')->toArray();
                        $query->whereIn('new_category_id', $childIds);
                    }
                }
            } elseif ($filterType === 'tag') {
                $tagId = (int)$filterId;
                $parentId = (int)($filter['parent_id'] ?? 0);
                $query->where('new_category_id', $parentId)
                    ->whereJsonContains('new_related_tags', $tagId);
            }
        }
        $query->orderBy('id', 'DESC');
        $transformedCategories = [];
        if ($page == 1) {
            $allTemplates = (clone $query)->get();
            $totalCount = $allTemplates->count();
            $allNewCategoryIds = $allTemplates->pluck('new_category_id')->unique()->values();
            $allNewCategories = NewCategory::whereIn('id', $allNewCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get()
                ->keyBy('id');
            $tagsByCategory = $allTemplates->groupBy('new_category_id')->map(function ($designs) {
                return $designs->pluck('new_related_tags')
                    ->filter()
                    ->flatten()
                    ->unique()->values();
            });
            $allTagIds = $tagsByCategory->flatten()->unique()->values();
            $searchTags = NewSearchTag::whereIn('id', $allTagIds)->get()->keyBy('id');
            $categoryTagMap = $tagsByCategory->map(function ($tagIds) use ($searchTags) {
                return $tagIds->map(function ($tagId) use ($searchTags) {
                    $tag = $searchTags->get($tagId);
                    return $tag ? [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'parent_category_id' => $tag->category_id ?? null,
                    ] : null;
                })->filter()->values();
            });
            $parentCategoryIds = $allNewCategories->pluck('parent_category_id')->filter()->unique();
            $parentCategories = NewCategory::whereIn('id', $parentCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get();
            $transformedCategories = $parentCategories->map(function ($parent) use ($allNewCategories, $categoryTagMap) {
                $children = $allNewCategories->filter(function ($cat) use ($parent) {
                    return $cat->parent_category_id == $parent->id;
                })->values();
                $subCategories = collect();
                foreach ($children as $child) {
                    $tags = collect();
                    $tags->push([
                        'id' => $child->id,
                        'name' => 'All',
                        'type' => 'all',
                        'display_name' => $child->category_name,
                    ]);
                    if ($categoryTagMap->has($child->id)) {
                        foreach ($categoryTagMap[$child->id] as $tag) {
                            $tags->push([
                                'id' => $tag['id'],
                                'name' => $tag['name'],
                                'display_name' => $tag['name'],
                                'parent_id' => $child->id,
                                'type' => 'tag',
                            ]);
                        }
                    }
                    $subCategories->push([
                        'id' => $child->id,
                        'name' => $child->category_name,
                        'display_name' => $child->category_name,
                        'parent_id' => $child->parent_category_id,
                        'type' => 'child',
                        'tags' => $tags->toArray()
                    ]);
                }
                return [
                    'id' => $parent->id,
                    'name' => $parent->category_name,
                    'display_name' => $parent->category_name,
                    'parent_id' => $parent->parent_category_id,
                    'type' => 'parent',
                    'sub_categories' => collect([
                        [
                            'id' => $parent->id,
                            'name' => 'All',
                            'type' => 'all',
                            'display_name' => $parent->category_name,
                        ]
                    ])->merge($subCategories)->toArray()
                ];
            })->values();
            $allCategoriesOption = collect([
                [
                    'id' => 0,
                    'name' => 'All Categories',
                    'display_name' => 'Category',
                    'parent_id' => 0,
                    'type' => 'parent',
                    'default' => true,
                ]
            ]);
            $transformedCategories = $allCategoriesOption->merge($transformedCategories)->values();
        }
        $templates = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $templates->currentPage() >= $templates->lastPage();
        $allCategoryIds = $templates->getCollection()->pluck('new_category_id')->unique();
        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $rates = RateController::getRates();

        $item_rows = collect($templates->items())->map(function ($item) use ($userData, $categories, $rates) {
            $catRow = $categories[$item->new_category_id] ?? null;
            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
            if ($catRow != null) {
                $catLink = $catRow->cat_link;
            }
            return HelperController::getItemData(
                uid: $this->uid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array, true) ?? [],
                catLink: $catLink,
                rates: $rates
            );
        })->filter()->values();

        if ($isJdMakwana) {
            $userData = UserData::whereUid($this->testingUid)->first();
        }

        $user['name'] = $userData->name;
        $user['user_name'] = $userData->user_name;
        $user['unique_name'] = '@' . $userData->user_name;
        $user['bio'] = $userData->bio;
        $user['photo_uri'] = $userData->photo_uri;
        if (str_contains($userData->photo_uri, 'uploadedFiles/')) {
            $user['photo_uri'] = HelperController::$mediaUrl . $userData->photo_uri;
        }

        $responseData = [
            'success' => true,
            'message' => 'Templates and categories loaded successfully.',
            'isLastPage' => $isLastPage,
            'page' => $page,
            'profile_view' => $formatedCount,
            'total_templates' => $totalCount ?? 0,
            'user' => $user,
            'datas' => $item_rows,
            'categories' => $transformedCategories,
        ];
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loading Success!', $responseData));
    }

    public function generateUsernamesForAll(): array|string
    {
        UserData::select(['id', 'user_name'])->where('creator', 1)->whereNull('user_name')->orderBy('id')->chunk(1000, function ($rows) {
            foreach ($rows as $row) {
                UserData::where('id', $row->id)
                    ->update(['user_name' => self::generateUserName()]);
            }
        });
        return "done";
    }

    public static function generateUserName($prefix = 'user', $length = 8): string
    {
        $pool = '0123456789';
        do {
            $username = $prefix . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
            $exists = UserData::where('user_name', $username)->exists();
        } while ($exists);
        return $username;
    }

    public function checkFirebaseUid($uidORmail): array
    {
        try {
            $user = $this->auth->getUserByEmail($uidORmail);

            $userId = $user->uid;
            $emailId = $user->email;

            if ($userId && $emailId) {
                return [
                    'registered' => true,
                    'user' => [
                        'name' => $user->displayName ?? "CraftyArt",
                        'email' => $emailId,
                        'photoUrl' => $user->photoUrl,
                        'uid' => $userId,
                    ]
                ];
            }
            return ['registered' => false];
        } catch (\Exception|AuthException|FirebaseException $e) {
            return ['registered' => false];
        }
    }

    public function createFirebaseUser(Request $request, $name, $email, $number, $password, $device_id = null, $utm_medium = null, $utm_source = null, $sendMail = true): array
    {
        try {
            ValidEmail::passes($email);

            $userData = UserData::whereEmail($email)->first();
            if (!$userData) {
                $uid = UserData::generateUid();
                $userInfo = [
                    'name' => $name,
                    'email' => $email,
                    'photoUrl' => null,
                    'uid' => $uid,
                ];

                $result = $this->addUser(
                    request: $request,
                    uid: $userInfo['uid'],
                    photo_uri: $userInfo['photoUrl'],
                    name: $userInfo['name'],
                    email: $userInfo['email'],
                    number: $number,
                    login_type: "email",
                    device_id: $device_id ?? null,
                    utm_medium: $utm_medium ?? "offer",
                    utm_source: $utm_source ?? "offer",
                    password: Hash::make($password));

                if (!$result['success']) return $result;
                $userData = $result['data'];
            }

            if ($sendMail) EmailController::sendUserCreation($userData, $password);

            return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "done", ['data' => $userData]));


        } catch (\Exception $e) {
            \Log::error("Signup Error:", ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return (new ResponseInterface(404, false, "Something went wrong"))->toArray();
    }


//    public function createFirebaseUser(Request $request, $name, $email, $number, $password, $device_id = null, $utm_medium = null, $utm_source = null, $sendMail = true): array
//    {
//        try {
//            $userInfo = null;
//
//            ValidEmail::passes($email);
//
//            $data = $this->checkFirebaseUid($email);
//            if ($data['registered']) {
//                $userInfo = $data['user'];
//            } else {
//                $user = $this->auth->createUser([
//                    'displayName' => $name,
//                    'email' => $email,
//                    'password' => $password
//                ]);
//
//                $userId = $user->uid;
//                $emailId = $user->email;
//
//                if ($userId && $emailId) {
//
//                    $this->auth->updateUser($userId, [
//                        'emailVerified' => true
//                    ]);
//
//                    $userInfo = [
//                        'name' => $user->displayName ?? "CraftyArt",
//                        'email' => $emailId,
//                        'photoUrl' => $user->photoUrl,
//                        'uid' => $userId,
//                    ];
//                }
//            }
//            if ($userInfo) {
//                $result = $this->addUser(
//                    request: $request,
//                    uid: $userInfo['uid'],
//                    photo_uri: $userInfo['photoUrl'],
//                    name: $userInfo['name'],
//                    email: $userInfo['email'],
//                    number: $number,
//                    login_type: "email",
//                    device_id: $device_id ?? null,
//                    utm_medium: $utm_medium ?? "offer",
//                    utm_source: $utm_source ?? "offer",
//                    password: Hash::make($password));
//
//                if (!$result['success']) return $result;
//                $userData = $result['data'];
//                if ($sendMail) EmailController::sendUserCreation($userData, $password);
//                return $result;
//            }
//
//
//        } catch (\Exception|AuthException|FirebaseException $e) {
//
//        }
//
//        return (new ResponseInterface(404, false, "Something went wrong"))->toArray();
//    }

    public static function generateReferID($id = "", $length = 6): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $refer_id = $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (UserData::where('refer_id', $refer_id)->exists());
        return $refer_id;
    }

    public function user_detail(UserData $userData, $id)
    {

        $user = UserData::where('uid', $id)->first();
        $datas['user'] = [
            'name' => $user->name,
            'email' => $user->email,
            'contact_no' => $user->contact_no,
            'profile_pic' => $user->photo_uri
        ];

        $multiData = array();
        $transData = TransactionLog::whereUserId($user->uid)->orderBy('id', 'DESC')->get();
        if ($transData != null && $transData->count() != 0) {
            foreach ($transData as $transLog) {
                $subRow = null;
                if ($transLog->type == 0) $subRow = Subscription::find($transLog->plan_id);
                if ($transLog->type == 1) $subRow = SubPlan::with(['plan'])->whereId($transLog->plan_id)->first();

                if (!$subRow) continue;
                if ($transLog->type == 1 && !$subRow->plan) continue;

                if ($transLog->currency_code == 'Trial') {
                    $amount = "Trial";
                } else {
                    $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                    $amount = $currency_code . $transLog->paid_amount;
                }

                $purchaseDate = $transLog->created_at;
                $billingDate = Carbon::parse($transLog->expired_at);
                $days = $purchaseDate->diffInDays($billingDate, false);
                $multiData[] = array(
                    'package_name' => $transLog->type == 0 ? $subRow->package_name : $subRow->plan->name,
                    'transaction_id' => $transLog->transaction_id,
                    'amount' => $amount,
                    'method' => $transLog->payment_method,
                    'purchase_date' => $purchaseDate->format('d/m/Y H:i:s'),
                    'billing_date' => $billingDate->format('d/m/Y H:i:s'),
                    'validity' => $days . " Days",
                    'status' => HelperController::checkSubsStatus($transLog->status),
                    'color' => HelperController::getSubsColor($transLog->status)
                );
            }
        }

        $datas['subsHistory'] = $multiData;

        $drafts = [];
        $draftDatas = Draft::with(['template'])->whereUserId($user->uid)->get();
        foreach ($draftDatas as $draft) {
            if ($draft->template) {
                $thumb = HelperController::$mediaUrl . $draft->template->post_thumb;
            } else {
                $thumbs = json_decode($draft->thumbs, true);
                $thumb = empty($thumbs) ? null : $thumbs[0];
            }
            $drafts[] = [
                "id" => $draft->string_id,
                "thumb" => $thumb,
                "created_at" => $draft->created_at->format('d/m/Y H:i:s'),
            ];
        }

        $exports = [];
        $exportDatas = ExportTable::with(['draft'])->whereUid($user->uid)->get();
        foreach ($exportDatas as $export) {
            if ($export->draft) {
                if ($export->draft->template) $thumb = HelperController::$mediaUrl . $export->draft->template->post_thumb;
                else {
                    $thumbs = json_decode($export->draft->thumbs, true);
                    $thumb = empty($thumbs) ? null : $thumbs[0];
                }
            } else {
                $thumb = null;
            }
            $exports[] = [
                "id" => $export->id,
                "draft_id" => $export->path,
                "thumb" => $thumb,
                "created_at" => $export->created_at->format('d/m/Y H:i:s'),
            ];
        }

        $datas['drafts'] = $drafts;
        $datas['export'] = $exports;

//        return view('users/user_detail')->with('userData', $datas);
        return $datas;
    }


    private static function formatDays($days): string
    {
        if ($days < 0) {
            return "Invalid";
        }

        $daysInYear = 365;
        $daysInMonth = 30;

        if ($days < $daysInMonth) {
            return $days . ' day' . ($days != 1 ? 's' : '');
        }

        if ($days < $daysInYear) {
            $months = $days / $daysInMonth;
            return number_format($months, 1) . ' month' . ($months >= 2 ? 's' : '');
        }

        $years = $days / $daysInYear;
        return number_format($years, 1) . ' year' . ($years >= 2 ? 's' : '');
    }

    function changeEmail(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $otp = $request->get('otp');
        $new_email = $request->get('new_email');

        if ($otp == null || $new_email == null) {
            return $this->failed(msg: "Invalid params");
        }

        if (UserData::whereEmail($new_email)->exists()) {
            return $this->failed(msg: "This email is already registered with another account");
        }

        $user_data = UserData::where('uid', $this->uid)->first();
        if (!$user_data) return $this->failed(msg: "User not found");

        $old_email = $user_data->email;

        $data = OTPTable::where('otp', $otp)
            ->where('type', 'change_email')
            ->where('status', '1')
            ->where('mail', $new_email)
            ->latest()
            ->first();

        if (!$data) {
            return $this->failed(msg: "Invalid OTP");
        }

        $user_data->email = $new_email;
        $user_data->save();

        $log = new UserEmailChangeLog();
        $log->uid = $this->uid;
        $log->old_email = $old_email;
        $log->new_email = $new_email;
        $log->save();

        $data->status = "0";
        $data->save();

        return $this->successed(msg: "Email updated successfully");
    }
    function changePassword(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $old_password = $request->get('old_password');
        $new_password = $request->get('new_password');

        if ($old_password == null || $new_password == null) {
            return $this->failed(msg: "Invalid params");
        }

        $user_data = UserData::where('uid', $this->uid)->first();
        if (!$user_data) return $this->failed(msg: "User not found");

        if (!Hash::check($old_password, $user_data->password)) {
            return $this->failed(msg: "The old password you entered is incorrect");
        }

        $user_data->password = Hash::make($new_password);
        $user_data->save();

        return $this->successed(msg: "Password updated successfully");
    }
}

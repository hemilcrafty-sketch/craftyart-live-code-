<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Models\OTPTable;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;

class AuthController extends ApiController
{
    protected Auth $auth;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $serviceAccountPath = "/private-files/firebase-service-account.json";
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->auth = $factory->createAuth();
    }

    function getUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");
        $user_data = UserData::whereUid($this->uid)->first();
        $userController = new UserController($request, $this->auth);
        return $this->successed(datas: $userController->getUserRes(request: $request, userData: $user_data));
    }

    function login(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $password = $request->get('password');

        if (is_null($email) || is_null($password)) return $this->failed(msg: "Invalid request");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(msg: "Email is not registered");

        if (!Hash::check($password, $user_data->password)) return $this->failed(msg: "Incorrect Password");

        $userController = new UserController($request, $this->auth);

        return $this->successed(datas: $userController->getUserRes(request: $request, userData: $user_data, minimalResponse: true));
    }

    function signup(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $otp = $request->get('otp');
        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');
        $contactNo = $request->get('contact');
        $device_id = $request->get('device_id', "");
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (is_null($otp) || is_null($name) || is_null($email) || is_null($password)) return $this->failed(msg: "Invalid request");
        if (UserData::whereEmail($email)->exists()) return $this->failed(msg: "Email is already registered");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $data = OTPTable::whereMail($email)->whereType('account_create')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

//        $hashPassword = Hash::make($password);
//        $uid = AuthController::generateUid();

        $userController = new UserController($request, $this->auth);
        $result = $userController->createFirebaseUser($request, $name, $email, $contactNo, $password, $device_id, $utm_medium, $utm_source, false);

        if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
        $user_data = $result['data'];

        return $this->successed(datas: $userController->getUserRes(request: $request, userData: $user_data, minimalResponse: true));
    }

    function resetPassword(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $otp = $request->get('otp');
        $password = $request->get('password');

        if (is_null($email) || is_null($otp) || is_null($password)) return $this->failed(msg: "Invalid request");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(msg: "Invalid request");

        $data = OTPTable::whereMail($email)->whereType('forgot_pass')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

        try {
//            $this->auth->changeUserPassword($user_data->uid, $password);
            $password = Hash::make($password);
            $success = UserData::where('email', $email)->update(["password" => $password]);
            if (!$success) return $this->failed();
            return $this->successed(msg: "Password has been changed successfully");
        } catch (\Exception $e) {
            return $this->failed();
        }

    }

    public static function generateUid($id = "", $length = 30): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $uid = $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (UserData::whereUid($uid)->exists());
        return $uid;
    }

}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\UserController;
use App\Events\ForceLogout;
use App\Events\SessionUpdated;
use App\Helpers\JwtHelper;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Draft;
use App\Models\OTPTable;
use App\Models\UserData;
use App\Models\UserSession;
use Exception;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;

class NewAuthController extends ApiController
{

    protected Auth $auth;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $serviceAccountPath = "/private-files/firebase-service-account.json";
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->auth = $factory->createAuth();
    }

    function getUserV2(Request $request): array|string
    {
        if ($request->header('api_key') !== 'craftyart_invites_7f3a9c2d91b84e5fb7a2c6d1e8f93a4b') {
            return $this->failed(msg: "Unauthorized", datas: ['api_key' => $request->header('api-key'), 'token' => $request->header('token')], showDecoded: true);
        }

        $token = $request->header('token');
        $draft_id = $request->input('draft_id');

        try {
            $jwtPayload = JwtHelper::decode($token);
            $this->uid = $jwtPayload->uid;
        } catch (Exception $e) {
            $this->uid = null;
        }

        $user_data = UserData::whereUid($this->uid)->first();
        if (!$user_data) return $this->failed(msg: "User not found", showDecoded: true);

        $plan = SubscriptionController::getActivePlan($this->uid);

        $planLimit = collect($plan?->plan_limit ?? []);
        $editablePdfObj = $planLimit->where('slug', "editable_pdf")->first();

        $deviceLimitData = $planLimit->where('slug', 'device_limit')->first();
        $seats = max($deviceLimitData['limit'] ?? 1, 1);

        $isDailyLimit = true;
        if ($editablePdfObj && $editablePdfObj["access_mode"] !== "daily") {
            $isDailyLimit = false;
        }

        $userRes['uid'] = $user_data->uid;
        $userRes['name'] = $user_data->name;
        $userRes['email'] = $user_data->email;
        $userRes['is_premium'] = !empty($plan);

        if ($draft_id) {
            if (in_array($user_data->email, ['karankotar11@gmail.com', 'vaghanivishruti7@gmail.com'])) {
                $draft_data = Draft::whereStringId($draft_id)->where('trashed', 0)->where('deleted', 0)->first();
            } else {
                $draft_data = Draft::whereUserId($user_data->uid)->whereStringId($draft_id)->where('trashed', 0)->where('deleted', 0)->first();
            }

            if ($draft_data) {
                $userRes['draft'] = json_decode(StorageUtils::get($draft_data->designs));
            } else {
                $userRes['draft'] = null;
            }
        }

        if ($editablePdfObj && $plan) {
            $userRes['editable_pdf'] = [
                'is_enabled' => (bool)$editablePdfObj,
                'is_daily_limit' => $isDailyLimit,
                'limit' => ($editablePdfObj['limit'] ?? 0) * $seats,
                'time_range' => [
                    $plan->created_at,
                    $plan->expired_at,
                ]
            ];
        } else {
            $userRes['editable_pdf'] = [
                'is_enabled' => false,
                'is_daily_limit' => true,
                'limit' => 0,
                'time_range' => []
            ];
        }


        if (str_contains($user_data->photo_uri, 'uploadedFiles/')) {
            $userRes['photo_uri'] = HelperController::$mediaUrl . $user_data->photo_uri;
        } else {
            $userRes['photo_uri'] = $user_data->photo_uri;
        }

        return $this->successed(datas: ['user' => $userRes], showDecoded: true);
    }

    function getUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        if (empty($this->deviceId)) return $this->failed(msg: "Unauthorized");

        $user_data = UserData::whereUid($this->uid)->first();
        $userController = new UserController($request);

        $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
        if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

        return $this->successed(datas: $userController->getNewUserRes(request: $request, userData: $user_data));
    }

    private function checkAndUpdateSession($userData, string $deviceId, string $IP, string $userAgent): array
    {
//        $activeCount = UserSession::whereUserId($userData->uid)->count();

//        $transactionLog = TransactionLog::whereUserId($userData->uid)
//            ->whereStatus(1)
//            ->where('expired_at', '>', now())
//            ->latest()
//            ->first();

//        $deviceLimit = 2;

//        if ($transactionLog) {
//            $deviceLimitData = collect($transactionLog->plan_limit)->where('slug', 'device_limit')->first();
//            $deviceLimit = $deviceLimitData ? ($deviceLimitData['limit'] ?? $deviceLimit) : $deviceLimit;
//        }

        $session = UserSession::whereUserId($userData->uid)
            ->whereDeviceId($deviceId)
            ->first();

        if (!$session) {
//            if ($activeCount >= $deviceLimit) {
//                return [
//                    'success' => false,
//                    "msg" => "Device limit reached. Please logout from another device first.",
//                    "data" => [
//                        'current_sessions' => UserSession::whereUserId($userData->uid)->get(),
//                        'device_limit' => $deviceLimit
//                    ]];
//            }

            $session = new UserSession();
            $session->user_id = $userData->uid;
            $session->device_id = $deviceId;
            $session->ip_address = $IP;
            $session->user_agent = $userAgent;
            $session->last_active = now();
            $session->save();
        } else {
            $session->update([
                'ip_address' => $IP,
                'user_agent' => $userAgent,
                'last_active' => now(),
            ]);
        }
        return ['success' => true, 'data' => $session];
    }

    function login(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $password = $request->get('password');

        if (empty($email) || empty($password)) return $this->failed(statusCode: 400, msg: "Invalid request");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(statusCode: 400, msg: "Email is not registered");

        if (!Hash::check($password, $user_data->password)) {
//            try {
//                $result = $this->auth->signInWithEmailAndPassword($email, $password);
//                if (empty($result->firebaseUserId())) return $this->failed(statusCode: 400, msg: "Incorrect Password");
//                else {
//                    $user_data->password = Hash::make($password);
//                    $user_data->save();
//                }
//            } catch (\Exception $e) {
            return $this->failed(statusCode: 400, msg: "Incorrect Password");
//            }
        }

        $this->deviceId = UserSession::generateDeviceId();

        $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
        if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

        $userController = new UserController($request);
        $data = $userController->getNewUserRes(request: $request, userData: $user_data, minimalResponse: true);

        $jwtPayload = [
            'uid' => $user_data->uid,
            'email' => $user_data->email,
            'device_id' => $this->deviceId,
            'session_id' => $sessionResponse['data']['id'],
        ];

        $data['session'] = [
            "nsdf" => JwtHelper::generate($jwtPayload),
            "dvi" => $this->deviceId,
        ];

        $data['token'] = JwtHelper::generate($jwtPayload);

        return $this->successed(datas: $data);
    }

    function signup(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $otp = $request->get('otp');
        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');
        $contactNo = $request->get('contact');
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (empty($otp) || empty($name) || empty($email) || empty($password)) return $this->failed(msg: "Invalid request");
        if (UserData::whereEmail($email)->exists()) return $this->failed(msg: "Email is already registered");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $data = OTPTable::whereMail($email)->whereType('account_create')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

        $this->deviceId = UserSession::generateDeviceId();

        $userController = new UserController($request);
        $result = $userController->createFirebaseUser($request, $name, $email, $contactNo, $password, $this->deviceId, $utm_medium, $utm_source);

        if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);

        $user_data = $result['data'];

        $data = $userController->getNewUserRes(request: $request, userData: $user_data, minimalResponse: true);

        $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
        if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

        $jwtPayload = [
            'uid' => $user_data->uid,
            'email' => $user_data->email,
            'device_id' => $this->deviceId,
            'session_id' => $sessionResponse['data']['id'],
        ];

        $data['session'] = [
            "nsdf" => JwtHelper::generate($jwtPayload),
            "dvi" => $this->deviceId,
        ];

        $data['token'] = JwtHelper::generate($jwtPayload);

        return $this->successed(datas: $data);
    }

    function handleGoogleSignIn(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(statusCode: 400, msg: "Unauthorized");

        $token = $request->get('token');

        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        try {

            $isApp = filter_var($request->get('isapp', false), FILTER_VALIDATE_BOOLEAN);
            $clientId = $isApp
                ? "291310090237-ld024tk1re3jvd25j8bu58bgt35es9f5.apps.googleusercontent.com"
                : "795839389518-6es2ihud9pd5qit1g4j1b4smka3k27a1.apps.googleusercontent.com";

            $client = new Google_Client([
                'client_id' => $clientId
            ]);

            $payload = $client->verifyIdToken($token);
            if (!$payload) return $this->failed(statusCode: 400, msg: "Invalid token");

            $name = $payload['name'];
            $email = $payload['email'];
            $photo_uri = $payload['picture'] ?? null;
        } catch (\Exception $e) {
            return $this->failed(statusCode: 400, msg: "Invalid token");
        }

        $user_data = UserData::whereEmail($email)->first();
        $userController = new UserController($request);

        $this->deviceId = UserSession::generateDeviceId();

        if (!$user_data) {
            $userController = new UserController($request, $this->auth);

            $uid = UserData::generateUid();
            $result = $userController->addUser($request, $uid, $photo_uri, $name, $email, null, "Google", $this->deviceId, $utm_medium, $utm_source);

            if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
            $user_data = $result['data'];
        }

        $data = $userController->getNewUserRes(request: $request, userData: $user_data);

        $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
        if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

        $jwtPayload = [
            'uid' => $user_data->uid,
            'email' => $user_data->email,
            'device_id' => $this->deviceId,
            'session_id' => $sessionResponse['data']['id'],
        ];

        $data['session'] = [
            "nsdf" => JwtHelper::generate($jwtPayload),
            "dvi" => $this->deviceId,
        ];

        $data['token'] = JwtHelper::generate($jwtPayload);

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", datas: $data));
    }

//    function handleGoogleSignIn(Request $request): array|string
//    {
//        if ($this->isFakeRequest($request)) return $this->failed(statusCode: 400, msg: "Unauthorized");
//
//        $token = $request->get('token');
//
//        $utm_medium = $request->get('utm_medium', "craftyart");
//        $utm_source = $request->get('utm_source', "craftyart");
//
//        try {
//            $data = $this->auth->verifyIdToken($token);
//            $name = $data->claims()->get('name');
//            $email = $data->claims()->get('email');
//            $photo_uri = $data->claims()->get('picture');
//        } catch (\Exception $e) {
//            return $this->failed(statusCode: 400, msg: "Invalid token");
//        }
//        $this->auth->verifyIdToken($token)->claims()->get('email');
//
//        $user_data = UserData::whereEmail($email)->first();
//        $userController = new UserController($request);
//
//        $this->deviceId = UserSession::generateDeviceId();
//
//        if (!$user_data) {
//            $isExists = $userController->checkFirebaseUid($email);
//            if (!$isExists['registered']) return $this->failed();
//
//            $userController = new UserController($request, $this->auth);
//
//            $userInfo = $isExists['user'];
//            $result = $userController->addUser($request, $userInfo['uid'], $photo_uri, $name, $email, null, "Google", $this->deviceId, $utm_medium, $utm_source);
//
//            if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
//            $user_data = $result['data'];
//        }
//
//        $data = $userController->getNewUserRes(request: $request, userData: $user_data);
//
//        $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
//        if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);
//
//        $jwtPayload = [
//            'uid' => $user_data->uid,
//            'email' => $user_data->email,
//            'device_id' => $this->deviceId,
//            'session_id' => $sessionResponse['data']['id'],
//        ];
//
//        $data['session'] = [
//            "nsdf" => JwtHelper::generate($jwtPayload),
//            "dvi" => $this->deviceId,
//        ];
//
//        $data['token'] = JwtHelper::generate($jwtPayload);
//
//        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", datas: $data));
//    }

    function resetPassword(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $otp = $request->get('otp');
        $password = $request->get('password');

        if (empty($email) || empty($otp) || empty($password)) return $this->failed(msg: "Invalid request");
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

            $sessions = UserSession::whereUserId($user_data->uid)->get();
            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user_data->uid, 'session_removed', $session->device_id, [], $user_data->email));
                broadcast(new ForceLogout($user_data->uid, $session->device_id));
                $session->delete();
            }

            return $this->successed(msg: "Password has been changed successfully");
        } catch (Exception|AuthException|FirebaseException $e) {
            return $this->failed();
        }
    }

    function logout(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $deviceId = $request->get('device_id');
        $logoutAll = $request->get('logout_all', false);

        if (!$logoutAll && !$deviceId) {
            return $this->failed(msg: "Device ID required for single logout");
        }

        $user = UserData::whereEmail($email)->first();

        if (!$user) return $this->failed(msg: "User not found");

        if ($logoutAll) {
            $sessions = UserSession::whereUserId($user->uid)->get();

            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $session->device_id, [], $user->email));
                broadcast(new ForceLogout($user->uid, $session->device_id));
                $session->delete();
            }
            return $this->successed("All devices logged out successfully");
        } else {
            $session = UserSession::where('user_id', $user->uid)
                ->where('device_id', $deviceId)
                ->first();

            if ($session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $deviceId, [], $user->email));
                try {
                    broadcast(new ForceLogout($user->uid, $deviceId));
                } catch (Exception $e) {
                    Log::error("Failed to broadcast ForceLogout: " . $e->getMessage());
                }

                $session->delete();
            }

            return $this->successed("Logged out successfully", datas: ['data' => $session], showDecoded: true);
        }
    }

}

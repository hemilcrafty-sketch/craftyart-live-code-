<?php

namespace App\Http\Controllers\Utils;

use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController2 extends Controller
{
    public array|null|string $authKey;
    public array|null|string $uid;
    public string $testingUid = "YTC1UOvR05hSKSkJSXFnb6LUFAi1";
    public string $aesPassword = 'E@7r1K7!6v#KZx^m';
    public string|null $clientIp = null;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->authKey = CryptoJsAes::decrypt($request->cookie('_aky'));
        $this->uid = CryptoJsAes::decrypt($request->cookie('_sdf'));
        if (is_null($this->authKey)) {
            $this->authKey = CryptoJsAes::decrypt($request->header('werty'));
        }
        if (is_null($this->uid)) {
            $this->uid = CryptoJsAes::decrypt($request->header('rtyhrj'));
        }

        $this->clientIp = $request->header('Client-Ip');
        $request->clientIp = $this->clientIp;

        if ((is_null($this->authKey) || is_array($this->authKey)) && DomainChecker::isFromTrustedDomain($request)) {
            $key_table = DB::table('key_table')->first();
            $this->authKey = $key_table->android_key;
        }

        $request->uid = $this->uid;
        $request->isTester = $this->isTester();
        $this->request = $request;
    }

    public function successed(string $msg = "Loaded!!", array $datas = [], bool $noIndex = false, bool $showDecoded = false): array|string
    {
        return ResponseHandler::sendResponse(request: $this->request, response: new ResponseInterface(200, true, $msg, $datas), noIndex: $noIndex, showDecoded: $showDecoded);
    }

    public function failed(int $statusCode = 401, string $msg = "Something went wrong", array $datas = [], bool $noIndex = false, bool $showDecoded = false): array|string
    {
        return ResponseHandler::sendResponse(request: $this->request, response: new ResponseInterface($statusCode, false, $msg, $datas), noIndex: $noIndex, showDecoded: $showDecoded);
    }

    public function isTester($uid = null): bool
    {
        return $this->testingUid == ($uid ?? $this->uid);
    }

    public function isFakeRequest(Request $request): bool
    {
        if ($request->isMethod('get')) return true;
        if (!DomainChecker::isValidDomain($request)) return true;
        if ($this->checkAuthKey()) return true;
        return false;
    }

    public function isFakeRequestAndUser(Request $request): bool
    {
        if ($request->isMethod('get')) return true;
        if (!DomainChecker::isValidDomain($request)) return true;
        if ($this->checkAuthKeyAndUid()) return true;
        return false;
    }

    public function isFakeRequestAndCreator(Request $request): bool
    {
        if ($request->isMethod('get')) return true;
        if (!DomainChecker::isValidDomain($request)) return true;
        if ($this->checkAuthKeyAndCreator()) return true;
        return false;
    }

    public function isFromBgRemoverDomain(Request $request): bool
    {
        if (DomainChecker::isValidDomain($request)) return true;
        return false;
    }

    private function checkAuthKey(): bool
    {
        if (is_null($this->authKey) || is_array($this->authKey)) return true;
        return false;
//        $key_table = DB::table('key_table')->first();
//        return $this->authKey != $key_table->android_key;
    }

    private function checkAuthKeyAndUid(): bool
    {
        if (is_null($this->authKey) || is_array($this->authKey) || is_null($this->uid) || is_array($this->uid)) return true;


//        $key_table = DB::table('key_table')->first();
        $userData = UserData::where("uid", $this->uid)->exists();

//        if ($this->authKey != $key_table->android_key) {
//            return true;
//        }

        if (!$userData) return true;
        return false;
    }

    private function checkAuthKeyAndCreator(): bool
    {
        if (is_null($this->authKey) || is_array($this->authKey) || is_null($this->uid) || is_array($this->uid)) return true;

//        $key_table = DB::table('key_table')->first();

        $userData = UserData::where('uid', $this->uid)
            ->where(function ($query) {
                $query->where('creator', 1)->orWhere('hoc', 1);
            })->first();

//        if ($this->authKey != $key_table->android_key) {
//            return true;
//        }

        if (!$userData) return true;
        return false;
    }

    public static function findIp(Request $request): string
    {


        $clientIP = $request->header('Client-Ip');

        $defaultUserIp = '89.116.134.215';
//        $userIp = $request->clientIp ?? $request->ip();
        $userIp = !is_null($clientIP) ? ($clientIP) : $request->ip();
        if (!$userIp || $userIp == $defaultUserIp) {
            $userIp = $request->header('X-Forwarded-For', $defaultUserIp);
        }
        return $userIp ?? $defaultUserIp;
    }
}

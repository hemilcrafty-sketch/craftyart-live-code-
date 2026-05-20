<?php

namespace App\Http\Controllers\Utils;

use App\Models\AllowedIp;
use Illuminate\Http\Request;

class DomainChecker extends Controller
{

    const authorizedUserDomain = ['www.craftyartapp.com', 'editor.craftyartapp.com', 'payment.craftyartapp.com'];
    const allowedDomains = ['www.craftyartapp.com', 'beta.craftyartapp.com',
        'editor.craftyartapp.com', /*'betaeditor.craftyartapp.com',*/
        'updater.craftyartapp.com', 'designer.craftyartapp.com', 'payment.craftyartapp.com', 'bgremover.craftyartapp.com'];

    public static function isFromAuthorizedUserDomain(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return in_array($requestDomain, self::authorizedUserDomain);
    }

    public static function isFromTrustedDomain(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        if (!in_array($requestDomain, self::allowedDomains)) {
            return self::checkIp($request);
        }
        return true;
    }

    public static function isValidDomain(Request $request): bool
    {
        $allowedDomains = array_merge([''], self::allowedDomains);
        $requestDomain = self::getDomainName($request);
        if (!in_array($requestDomain, $allowedDomains)) {
            return self::checkIp($request);
        }
        return true;
    }

    public static function isMainDomain(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return $requestDomain == "www.craftyartapp.com";
    }

    public static function isPanel(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return $requestDomain == "panel.craftyartapp.com";
    }

    public static function isEditor(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return $requestDomain == "editor.craftyartapp.com";
    }

    public static function isBetaEditor(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return $requestDomain == "betaeditor.craftyartapp.com";
    }

    public static function isBgRemoverDomain(Request $request): bool
    {
        $requestDomain = self::getDomainName($request);
        return $requestDomain == "bgremover.craftyartapp.com";
    }

    public static function getDomainName(Request $request): false|array|int|string|null
    {
        $origin = $request->header('Origin');
        $requestDomain = $origin ? parse_url($origin, PHP_URL_HOST) : null;

        // If 'Origin' header is not present, check 'Referer' header
        if (empty($requestDomain)) {
            $referer = $request->header('Referer');
            $requestDomain = parse_url($referer, PHP_URL_HOST);
        }

        return $requestDomain;
    }

    private static function checkIp(Request $request): bool
    {
        return true;
//        $ip = ApiController::findIp($request);
//
//        if (!$ip) {
//            return false;
//        }
//
//        return AllowedIp::where(function ($query) use ($ip) {
//            $query->where('main_ip', $ip)
//                ->orWhere('additional', 'like', '%' . $ip . '%');
//        })->exists();
    }

    public static function isValidSpecialUser(Request $request, $user_data): int
    {
        $ip = ApiController::findIp($request);

        if (!$ip || !$user_data || $user_data->special_user == 0) {
            return 0;
        }

        return 1;

//        $isExists = AllowedIp::where(function ($query) use ($ip) {
//            $query->where('main_ip', $ip)
//                ->orWhere('additional', 'like', '%' . $ip . '%');
//        })->exists();

//        return $isExists ? 1 : 0;
    }

    public static function isAllowedIps ($ip): bool
    {
        if (!$ip) {
            return false;
        }

        return AllowedIp::where(function ($query) use ($ip) {
            $query->where('main_ip', $ip)
                ->orWhere('additional', 'like', '%' . $ip . '%');
        })->exists();
    }
}

<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Http\Request;

class ResponseHandler extends Controller
{

    private const showDecoded = false;

    public static function sendResponse(Request $request, ResponseInterface $response, bool $noIndex = false, bool $showDecoded = false): array|string
    {
        if ($request->has("showDecoded") || $showDecoded || (self::showDecoded && $request->isTester)) {
            return $response->toArray($noIndex);
        }
        return json_encode(CryptoJsAes::encrypt(json_encode($response->toArray($noIndex))));
    }

    public static function sendRealResponse(ResponseInterface|array $response, $noIndex = false): array
    {
        if (is_array($response)) return $response;
        return $response->toArray($noIndex);
    }

    public static function sendEncryptedResponse(Request $request, $array, $noIndex = false): array|string
    {
        if ($noIndex) {
            $array['noIndex'] = true;
        }

        if ($request->has("showDecoded") || (self::showDecoded && $request->isTester)) {
            return $array;
        }
        return json_encode(CryptoJsAes::encrypt(json_encode($array)));
    }

    public static function sendRawResponse(Request $request, array $response, $noIndex = false, bool $showDecoded = false): array|string
    {
        if ($noIndex) {
            $response['noIndex'] = true;
        }

        if ($request->has("showDecoded") || $showDecoded || (self::showDecoded && $request->isTester)) {
            return $response;
        }
        return json_encode(CryptoJsAes::encrypt(json_encode($response)));
    }
}

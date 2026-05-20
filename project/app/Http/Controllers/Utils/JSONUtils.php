<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\HelperController;

class JSONUtils
{
    public static function replaceMediaUrl($data, $uid)
    {

        // if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        // }

        return self::replace($data);
    }

    private static function replace($data)
    {
        return is_array($data)
            ? array_map(fn($item) => JSONUtils::replace($item), $data)
            : (is_string($data) ? str_replace(HelperController::$oldMediaUrl, HelperController::$mediaUrl, $data) : $data);
    }
}

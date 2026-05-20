<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\CategoryTemplatesApiController;
use Cache;

class CacheHelper
{
    public static function getSubCategories($catId)
    {
        $callback = function () use ($catId) {
            return CategoryTemplatesApiController::getSubCategories($catId);
        };

        if (HelperController::$cacheEnabled) {
            return Cache::tags(["category_$catId"])->remember(
                "category_$catId",
                HelperController::$cacheTimeOut,
                $callback
            );
        }

        return $callback();
    }
}

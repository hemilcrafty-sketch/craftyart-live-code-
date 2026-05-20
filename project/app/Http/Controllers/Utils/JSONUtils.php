<?php

namespace App\Http\Controllers\Utils;

class JSONUtils
{

    public static function applyPageStringId($data, $templateId, $forceTid = true)
    {
        $data = json_decode(json_encode($data), true);

        if (isset($data['pages']) && is_array($data['pages'])) {

            $data['pages'] = array_filter($data['pages'], function ($page) {
                return !empty($page['objects']) &&
                    isset($page['objects'][0]['type']) &&
                    $page['objects'][0]['type'] === "Background";
            });

            $data['pages'] = array_values($data['pages']);


            foreach ($data['pages'] as &$page) {
                if (!is_array($page)) {
                    continue;
                }
                if (!isset($page['templateId']) || trim($page['templateId']) === '' || trim($page['templateId']) === 'draft') {
                    $page['templateId'] = $templateId;
                }
            }
        }
        return self::replace($data, $templateId, $forceTid);
    }

//    public static function replaceMediaUrl($data, $templateId, $forceTid)
//    {
//
////        if (is_object($data)) {
//        $data = json_decode(json_encode($data), true);
////        }
//
//        return self::replace($data);
//    }

    private static function replace2($data, $templateId, $forceTid = true)
    {
        return is_array($data)
            ? array_map(fn($item) => JSONUtils::replace($item, $templateId, $forceTid), $data)
            : (is_string($data) ? str_replace(HelperController::$oldMediaUrl, HelperController::$mediaUrl, $data) : $data);
    }

    private static function replace($data, $templateId, $forceTid = true)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$item) {
                if (is_array($item) || is_object($item)) {
                    $item = self::replace($item, $templateId, $forceTid);
                } elseif ($forceTid && $key === 'tid') {
                    $item = $templateId;
                } elseif (is_string($item)) {
                    $item = str_replace(HelperController::$oldMediaUrl, HelperController::$mediaUrl, $item);
                }
            }
            return $data;
        } elseif (is_object($data)) {
            foreach ($data as $key => &$item) {
                if (is_array($item) || is_object($item)) {
                    $item = self::replace($item, $templateId, $forceTid);
                } elseif ($forceTid && $key === 'tid') {
                    $item = $templateId;
                } elseif (is_string($item)) {
                    $item = str_replace(HelperController::$oldMediaUrl, HelperController::$mediaUrl, $item);
                }
            }
            return $data;
        } elseif (is_string($data)) {
            return str_replace(HelperController::$oldMediaUrl, HelperController::$mediaUrl, $data);
        }

        return $data;
    }

}

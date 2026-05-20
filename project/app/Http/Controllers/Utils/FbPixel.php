<?php

namespace App\Http\Controllers\Utils;

use App\Jobs\FbPixelJob;
use App\Models\FbTraces;
use DB;
use Illuminate\Http\Request;
use FacebookAds\Api;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\Util;
use FacebookAds\Object\ServerSide\ActionSource;

class FbPixel
{

    private static Api|null $apiInstance = null;
    private static string $offerPixelId = '1331905735018339';
    private static string $accessToken = 'EAAxpeR8ZAouIBO39ZBxUcZCTl7uZCSXJF5hGqnJpWYLSJUw9agvi0ZB06qRQHpcuFDpL9o8xPghM0YlkfKKx8IX6MZCtYzmf3gUFwVg5fK2tthxuVQIMXV9XXKlHCWTZB4JZAseWjQVv2nXEP22iwpdFjZColH8gYmV3C5Lq3lTKcLQwmVAKKwZAcm4iuMXJp6ogP5xQZDZD';
    private static string $graphicBundlePixelId = '1890577194965709';
    private static string $graphicBundleAccessToken = 'EAANo421BgJsBRS0Ao7i6yMt1RzRTQu3w7ZB5qLHhnbZAAv7ZCbRnap7Fz4SWcZB6Ap80VJG9fvINqqAsRkmUOcTqZA4SJcC1MW0t4ZBRtTk7oA96gr7z2Av0v71UlRQ3TvqR8pluMDBKYa3KOK34YvtlD4hXlNbVHpnzUvNZBvNGF2ardTRmZB4ZBFKh25ts4lAZDZD';
    // Initialize API only once
    private static function initApi(string $accessToken): void
    {
        self::$apiInstance = Api::init(null, null, $accessToken);
    }

    public static function purchaseEvent(FacebookEvent $eventName, Request $request, ?string $name = null, ?string $email = null, ?string $phone = null, ?string $url = null, array|null $purchaseData = null, $isOfferPixel = false): void
    {
        FbPixelJob::dispatch(
            $eventName,
            $request->ip(),
            $request->header('User-Agent', 'Unknown'),
            $request->cookie('_fbclid'),
            $request->cookie('_caid'),
            $name,
            $email,
            $phone,
            $url,
            $purchaseData,
            $isOfferPixel
        );
    }

    public static function purchaseJobEvent(
        FacebookEvent $eventName,
        ?string $clientIp,
        ?string $userAgent,
        ?string $fbclid,
        ?string $fbp,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $url = null,
        ?array $purchaseData = null,
        bool $isOfferPixel = false
    ): void /*: EventResponse*/ {
        try {

            $isGraphicsBundle = $url != null ? DomainChecker::isSpecialPage($url, 'graphics-bundle') : false;

            $pixelId = $isGraphicsBundle
                ? self::$graphicBundlePixelId
                : self::$offerPixelId;

            $accessToken = $isGraphicsBundle
                ? self::$graphicBundleAccessToken
                : self::$accessToken;

            self::initApi($accessToken);
            if ($purchaseData) {
                $userAgent = $purchaseData['userAgent'] ?? $userAgent ?? null;
                $clientIp = $purchaseData['clientIp'] ?? $clientIp;
                $fbclid = $purchaseData['_fbclid'] ?? $fbclid ?? null;
                $fbp = $purchaseData['_caid'] ?? $fbp ?? null;
            }

            if ($fbclid) {
                $fbclid = "fb.1." . time() . "." . $fbclid;
            }

            if (str_starts_with($fbp, "cid.1.")) {
                $fbp = str_replace("cid.1.", "fb.1.", $fbp);
            }

            $userData = new UserData();

            if ($name) {
                $name = strtolower(trim($name));
                $hashed = Util::hash($name);
                $userData->setFirstName($hashed);
            }

            if ($email) {
                $email = strtolower(trim($email));
                $hashed = Util::hash($email);
                $userData->setEmail($hashed);
            }

            if ($phone) {
                $phone = strtolower(trim($phone));
                $hashed = Util::hash($phone);
                $userData->setPhone($hashed);
            }

            $data = HelperController::getCountryByIp($clientIp);
            if ($data && isset($data['cc'])) {
                $hashed = Util::hash($data['cc']);
                $userData->setCountryCode($hashed);
            }

            if ($clientIp) $userData->setClientIpAddress($clientIp);
            if ($userAgent) $userData->setClientUserAgent($userAgent);

            if ($fbclid && $fbclid != "undefined") $userData->setFbc($fbclid);
            if ($fbp && $fbp != "undefined") $userData->setFbp($fbp);

            // Create the event
            $event = (new Event())
                ->setEventName($eventName->value)
                ->setEventTime(time())
                ->setUserData($userData)
                ->setActionSource(ActionSource::WEBSITE)
                ->setEventId(uniqid());

            if ($url) {
                $event->setEventSourceUrl($url);
            }

            if ($purchaseData) {
                $customData = (new CustomData())
                    ->setCurrency($purchaseData['currency'])
                    ->setValue($purchaseData['value'])
                    ->setContentType('product')
                    ->setContentIds($purchaseData['id']);

                $event->setCustomData($customData);
            }

            // Send event to Facebook
//            $eventRequest = new EventRequest($isOfferPixel ? self::$offerPixelId : self::$craftyPixelId);
            $eventRequest = new EventRequest($pixelId);
            $eventRequest->setEvents([$event]);
//            $eventRequest->setTestEventCode('TEST93287');

            $res = $eventRequest->execute();

//            DB::table('fb_traces')->insert(['msg' => json_encode($res->getMessages())]);
            $fbTrace = new FbTraces();
            $fbTrace->event_name = $event->getEventName();
            $fbTrace->msg = json_encode([
                'events_received' => $res->getEventsReceived(),
                'messages' => $res->getMessages(),
                'fbtrace_id' => $res->getFbtraceId(),
                'data' => [
                    "event_name" => $event->getEventName(),
                    "event_time" => $event->getEventTime(),
                    "action_source" => $event->getActionSource(),
                    "user_data" => $event->getUserData()->normalize(),
                    "custom_data" => $event->getCustomData()->normalize()
                ]
            ]);
            $fbTrace->fbclid = $event->getUserData()->getFbc();
            $fbTrace->fbpid = $event->getUserData()->getFbp();
            $fbTrace->email = $email;
            $fbTrace->page_url = $url;
            $fbTrace->product_ids = json_encode($purchaseData['id']);
            $fbTrace->currency = $purchaseData['currency'];
            $fbTrace->amount = $purchaseData['value'];
            $fbTrace->is_meta = empty($fbclid) ? 0 : 1;
            $fbTrace->save();

//            DB::table('fb_traces')->insert([
//                'msg' => json_encode([
//                    'events_received' => $res->getEventsReceived(),
//                    'messages' => $res->getMessages(),
//                    'fbtrace_id' => $res->getFbtraceId(),
//                    'data' => [
//                        "event_name" => $event->getEventName(),
//                        "event_time" => $event->getEventTime(),
//                        "action_source" => $event->getActionSource(),
//                        "user_data" => $event->getUserData()->normalize(),
//                        "custom_data" => $event->getCustomData()->normalize()
//                    ]
//                ])
//            ]);
        } catch (\Exception $e) {

            $fbTrace = new FbTraces();
            $fbTrace->event_name = $eventName->value;
            $fbTrace->msg = $e->getMessage();
            $fbTrace->traces = json_encode($e->getTrace());
            $fbTrace->fbclid = $fbclid;
            $fbTrace->fbpid = $fbp;
            $fbTrace->email = $email;
            $fbTrace->page_url = $url;
            $fbTrace->product_ids = json_encode($purchaseData['id']);
            $fbTrace->currency = $purchaseData['currency'];
            $fbTrace->amount = $purchaseData['value'];
            $fbTrace->is_error = 1;
            $fbTrace->save();

//            DB::table('fb_traces')->insert(['msg' => $e->getMessage(), 'traces' => json_encode($e->getTrace())]);
        }
    }

    public static function trackEvent(FacebookEvent $eventName, Request $request, ?string $name = null, ?string $email = null, ?string $phone = null, ?string $url = null, array|null $purchaseData = null) /*: EventResponse*/
    {
//        self::initApi();
//
//        $clientIp = ApiController::findIp($request) ?? '0.0.0.0';
//        $userAgent = $request->header('User-Agent', 'Unknown');
//        $fbclid = $request->cookie('_fbclid');
//        $fbp = $request->cookie('_caid');
//
//        if ($purchaseData) {
//            $userAgent = $purchaseData['userAgent'] ?? $userAgent ?? null;
//            $clientIp = $purchaseData['clientIp'] ?? $clientIp;
//            $fbclid = $purchaseData['_fbclid'] ?? $fbclid ?? null;
//            $fbp = $purchaseData['_caid'] ?? $fbp ?? null;
//
//            $gclid = $paymentIntent['_gclid'] ?? null;
//            $gclAu = $paymentIntent['_gcl_au'] ?? null;
//            $gaClientId = $paymentIntent['_ga'] ?? null;
//
//            $ipData = HelperController::getIpAndCountry($request, $clientIp);
//            $userIp = $ipData['ip'];
//
//            if ($eventName == FacebookEvent::SELLING) {
//                WebFbSelling::create([
//                    'user_id' => $purchaseData['uid'],
//                    'product_id' => json_encode($purchaseData['id']),
//                    'amount' => $purchaseData['value'],
//                    'ip_address' => $userIp,
//                    'country' => $ipData['cn'],
//                    'fbc' => $fbclid,
//                    'fbp' => $fbp,
//                    'gclid' => $gclid,
//                    'gcl_au' => $gclAu,
//                    'ga' => $gaClientId,
//                    'userAgent' => $userAgent,
//                ]);
//            }
//        }
//
//        if (str_starts_with($fbp, "cid.1.")) {
//            $fbp = str_replace("cid.1.", "fb.1.", $fbp);
//        }
//
//        $userData = new UserData();
//
//        if ($name) {
//            $hashed = Util::hash($name);
//            $userData->setFirstName($hashed);
//        }
//
//        if ($email) {
//            $hashed = Util::hash($email);
//            $userData->setEmail($hashed);
//        }
//
//        if ($phone) {
//            $hashed = Util::hash($phone);
//            $userData->setPhone($hashed);
//        }
//
//        $data = HelperController::getIpAndCountry($request);
//        if ($data && isset($data['cc'])) {
//            $hashed = Util::hash($data['cc']);
//            $userData->setCountryCode($hashed);
//        }
//
//        if ($clientIp) $userData->setClientIpAddress($clientIp);
//        if ($userAgent) $userData->setClientUserAgent($userAgent);
//
//        if ($fbclid && $fbclid != "undefined") $userData->setFbc("fb.1." . time() . $fbclid);
//        if ($fbp && $fbp != "undefined") $userData->setFbp($fbp);
//
//        // Create the event
//        $event = (new Event())
//            ->setEventName($eventName->value)
//            ->setEventTime(time())
//            ->setUserData($userData)
//            ->setActionSource(ActionSource::WEBSITE)
//            ->setEventId(uniqid());
//
//        if ($url) {
//            $event->setEventSourceUrl($url);
//        }
//
//        if ($purchaseData) {
//            $customData = (new CustomData())
//                ->setCurrency($purchaseData['currency'])
//                ->setValue($purchaseData['value'])
//                ->setContentType('product')
//                ->setContentIds($purchaseData['id']);
//
//            $event->setCustomData($customData);
//        }
//
//        // Send event to Facebook
//        $eventRequest = new EventRequest(self::$pixelId);
//        $eventRequest->setEvents([$event]);
//
//        return $eventRequest->execute();
    }
}
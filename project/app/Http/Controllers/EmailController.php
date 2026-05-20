<?php

namespace App\Http\Controllers;

use App\Enums\ConfigType;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Automation\Config;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\Design;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PurchaseHistory;
use App\Models\Subscription;
use App\Models\UserData;
use App\Services\WhatsAppService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use View;

class EmailController extends ApiController
{
    public static function sendUserCreation(UserData $userData, $password): array
    {

        try {

            $config = Config::whereName('account_create_automation')->first();
            if ($config && $config->value && !empty($config->value)) {

                $configValue = $config->value;
                if ($configValue['email']['enable'] ?? false) {
                    $emailConfig = $configValue['email']['config'] ?? [];

                    $emailTemplateId = $emailConfig['template'] ?? null;

                    if (!$emailTemplateId) {
                        return ['success' => false, 'message' => "Email template not defined in config"];
                    }

                    $emailTemplate = EmailTemplate::find($emailTemplateId);

                    if (!$emailTemplate) {
                        return ['success' => false, 'message' => "Email Template not found"];
                    }

                    $emailData = [
                        'userData' => [
                            'name' => $userData->name,
                            'email' => $userData->email,
                            'password' => $password,
                        ],
                    ];

                    $name = str_replace('.', '/', $emailTemplate->email_template);
                    $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";

                    if (!file_exists($viewPath)) {
                        return ['success' => false, 'message' => "Email Template not found"];
                    }

                    $htmlBody = View::file($viewPath, [
                        'data' => $emailData
                    ])->render();

                    $subject = $emailConfig['subject'] ?? '';

                    Mail::mailer('otp')->send([], [], function ($message) use ($userData, $subject, $htmlBody) {
                        $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                            ->to($userData->email)
                            ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                            ->subject($subject)
                            ->setBody($htmlBody, 'text/html');

                        $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                    });

                    if (count(Mail::failures()) > 0) {
                        return ResponseHandler::sendRealResponse(new ResponseInterface(
                            500,
                            false,
                            'Email sending failed.',
                            ['failures' => Mail::failures()]
                        ));
                    }

                    return ResponseHandler::sendRealResponse(new ResponseInterface(
                        200,
                        true,
                        'Email sent successfully'
                    ));
                }
            }

            return ['success' => false, 'message' => "Email Template not found"];

        } catch (\Throwable $e) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(
                500,
                false,
                'Failed to send email.',
                ['error' => $e->getMessage()]
            ));
        }
    }

    public static function sendPurchaseDropoutEmail(Order $order): void
    {
        if (!in_array($order->type, ['old_sub', 'template'], true)) return;

        $user = UserData::where('uid', $order->user_id)->first();
        if (!$user) return;

        $planId = $order->plan_id;
        $currency = $order->currency;

        $config = Config::whereName('checkout_drop_automation')->first();
        if ($config && $config->value && !empty($config->value)) {
            $configValue = collect($config->value)->firstWhere('day', 0);
            if (!$configValue) return;

            if (($configValue['email']['enable'] ?? false) || ($configValue['wp']['enable'] ?? false)) {

                $applyPromo = true;
                if ($order->type === 'old_sub') {
                    $sub = Subscription::getSubs(ids: [$planId], currency: $currency, status: null);
                    if (!$sub) return;
                    $planData = $sub[0];
                    $response['type'] = "plan";
                    $response['data'] = $planData;

                    if (in_array($planId, PaymentController::$OFFER_IDS)) {
                        $orderType = "offer";
                        $applyPromo = false;
                        $response['link'] = "https://www.craftyartapp.com/offer/payment/$order->crafty_id";
                    } else {
                        $orderType = "subscription";
                        $response['link'] = "https://www.craftyartapp.com/payment/$order->crafty_id";
                    }

                } else if ($order->type === 'template') {
                    $orderType = "templates";
                    $response = Design::getTempDatas($order);
                } else {
                    return;
                }

                $response['userData'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                if ($configValue['email']['enable'] ?? false) {
                    $isSent = EmailController::sendMail($order, $user, $configValue, $orderType, $applyPromo, $response);
                    if ($isSent) $order->increment('email_template_count');
                }

                if ($configValue['wp']['enable'] ?? false) {
                    $user->contact_no = $order->contact_no;
                    $isSent = EmailController::sendWa($order, $user, $configValue, $orderType, $applyPromo, $response);
                    if ($isSent) $order->increment('whatsapp_template_count');

                }
            }
        }
    }

    public static function sendInstantTemplatePurchaseMessage(UserData $userData): void
    {
        $config = Config::whereName(ConfigType::INSTANT_TEMPLATE_PURCHASE->name)->first();
        if ($config && $config->value && !empty($config->value)) {
            $configValue = $config->value;
            if (($configValue['email']['enable'] ?? false) || ($configValue['wp']['enable'] ?? false)) {

                $response['userData'] = [
                    'name' => $userData->name,
                    'email' => $userData->email,
                ];

                $promoId = $configValue['promo'] ?? 0;
                $promoData = PromoCode::whereId($promoId)->first();
                if (!$promoData) return;

                $response['promo'] = [
                    "code" => $promoData->promo_code,
                    "disc" => "$promoData->disc%",
                    "expiry_date" => $promoData->expiry_date ? Carbon::parse($promoData->expiry_date)->format('j F Y') : null,
                    "discount_price" => "0"
                ];

                $response['link'] = "https://www.craftyartapp.com/templates/invitation";

                if ($configValue['email']['enable'] ?? false) {
                    EmailController::sendEmailTemplateMessage($userData->email, $configValue['email']['config']['subject'], $configValue['email']['config']['template'], $response);
                }
                if ($configValue['wp']['enable'] ?? false) {

                    $wpConfig = $configValue['wp']['config'] ?? [];
                    if (!$wpConfig || !$wpConfig['template'])
                        return;
                    $waTemplate = WhatsappTemplate::whereId($wpConfig['template'])->first();
                    if (!$waTemplate)
                        return;

                    $waParams = self::resolveWhatsappTemplateParams(
                        keys: $waTemplate->template_params,
                        response: $response,
                        orderType: ""
                    );

                    $ctaButtons[] = [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => 0,
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                            ]
                        ],
                    ];
                    EmailController::sendWhatsappTemplateMessage($userData, $waParams, $ctaButtons, $waTemplate->campaign_name, $waTemplate->url ?? "");
                }
            }
        }
    }

    public static function sendEmailTemplateMessage($email, $subject, $emailTemplateId, $response): bool
    {
        try {

            $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
            if (!$emailTemplate) return false;

            $name = str_replace('.', '/', $emailTemplate->email_template);
            $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
            if (!file_exists($viewPath))
                return false;

            $htmlBody = View::file($viewPath, [
                'data' => $response
            ])->render();

            Mail::mailer('otp')->send([], [], function ($message) use ($email, $subject, $htmlBody) {
                $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                    ->to($email)
                    ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($htmlBody, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function sendMail(Order $order, UserData $userData, $configValue, $orderType, $applyPromo, $response): bool
    {
        $emailConfig = $configValue['email']['config'] ?? [];
        $templateConfig = $emailConfig[$orderType] ?? null;

        $emailTemplateId = $templateConfig['template'] ?? null;

        if (!$emailTemplateId) return false;
        $emailTemplate = EmailTemplate::find($emailTemplateId);
        if (!$emailTemplate) return false;

        $name = str_replace('.', '/', $emailTemplate->email_template);
        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";

        if (!file_exists($viewPath)) return false;

        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = 0;
                if ($orderType == 'templates') {
                    if (!empty($response['amount'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['amount']);
                    }
                } else {
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }
                }

                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $currency = $order->currency;
                    $isInr = $currency == "INR";
                    $discountedValue = $isInr ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                    $currencySymbol = $isInr ? "₹" : "$";
                    $discountPrice = $currencySymbol . $discountedValue;
                }

                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%",
                    'expiry_date' => $expiry_date,
                    'discount_price' => $discountPrice
                ];
                $response['promo'] = $promoObject;
            }
        }

        try {
            $htmlBody = View::file($viewPath, [
                'data' => $response
            ])->render();
            $subject = $templateConfig['subject'] ?? '';
            Mail::mailer('otp')->send([], [], function ($message) use ($userData, $subject, $htmlBody) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                    ->to($userData->email)
                    ->replyTo(env("MAIL_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($htmlBody, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    private static function sendWa(Order $order, UserData $user, $configValue, $orderType, $applyPromo, $response): bool
    {
        $wpConfig = $configValue['wp']['config'] ?? [];

        $promoCode = "";
        $disc = "";

        $templateConfig = $wpConfig[$orderType] ?? null;

        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                $rawPrice = 0;
                if (in_array($order->type, ['template', 'video'])) {
                    if (!empty($response['amount'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['amount']);
                    }
                } else {
                    if (!empty($response['data']['price'])) {
                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $response['data']['price']);
                    }
                }

                $discountPrice = 0;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $currency = $order->currency;
                    $isInr = $currency == "INR";
                    $discountedValue = $isInr ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                    $currencySymbol = $isInr ? "₹" : "$";
                    $discountPrice = $currencySymbol . $discountedValue;
                }

                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%",
                    'expiry_date' => $expiry_date,
                    'discount_price' => $discountPrice
                ];
                $response['promo'] = $promoObject;
            }
        }

        if (!$templateConfig || !$templateConfig['template']) return false;
        $waTemplate = WhatsappTemplate::find($templateConfig['template']);
        if (!$waTemplate) return false;

        $response['userData'] = [
            "name" => $user->name,
            "email" => $user->email
        ];

        $waParams = self::resolveWhatsappTemplateParams(
            keys: $waTemplate->template_params,
            response: $response,
            orderType: $order->type
        );

        $ctaButtons[] = [
            "type" => "button",
            "sub_type" => "url",
            "index" => 0,
            "parameters" => [
                [
                    "type" => "text",
                    "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                ]
            ],
        ];

        $result = EmailController::sendWhatsappTemplateMessage($user, $waParams, $ctaButtons, $waTemplate->campaign_name);

        if ($result['success'] == "true" || $result['success']) {
            return true;
        }
        return false;
    }

    private static function sendWa2(UserData $user, $configValue, $orderType, $applyPromo, $response): bool
    {
        $wpConfig = $configValue['wp']['config'] ?? [];

        $promoCode = "";
        $disc = "";

        $templateConfig = $wpConfig[$orderType] ?? null;

        if (($templateConfig['promo_code'] ?? false) && $applyPromo) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = PromoCode::find($promoCodeId);
            if ($promo) {
                $promoCode = $promo->promo_code;
                $disc = "$promo->disc%";
                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => "$promo->disc%"
                ];
                $response['promo'] = $promoObject;
            }
        }

        if (!$templateConfig || !$templateConfig['template']) return false;
        $waTemplate = WhatsappTemplate::find($templateConfig['template']);
        if (!$waTemplate) return false;

        if ($orderType === 'offer') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price']];
        } elseif ($orderType === 'subscription') {
            $waParams = [$user->name, $response['data']['offer_price'], $response['data']['actual_price'], $promoCode, $disc];
        } else {
            $waParams = [$user->name, $response['data']['amount'], $promoCode, $disc];
        }

        $ctaButtons[] = [
            "type" => "button",
            "sub_type" => "url",
            "index" => 0,
            "parameters" => [
                [
                    "type" => "text",
                    "text" => str_replace("https://www.craftyartapp.com/", "", $response['link'])
                ]
            ],
        ];

        $result = EmailController::sendWhatsappTemplateMessage($user, $waParams, $ctaButtons, $waTemplate->campaign_name);

        if ($result['success'] == "true" || $result['success']) {
            return true;
        }
        return false;
    }

    private static function resolveWhatsappTemplateParams(array $keys, array $response, $orderType): array
    {
        $resolved = [];
        foreach ($keys as $key) {
            if (str_contains($key, ".")) {
                [$group, $field] = explode('.', $key);
            } else {
                $group = $key;
                $field = null;
            }

            $resolved[] = match ($group) {
                'UserData' => $response['userData'][$field] ?? '',
                'PlanData' => (in_array($orderType, ['template', 'video']) ? (
                ($field === "actual_price" || $field === "offer_price")
                    ? ($response['data']['amount'] ?? '')
                    : ($field === "package_name"
                    ? ($response['data']['templates'][0]['title'] ?? '')
                    : ($response['data'][$field] ?? '')))
                    : ($response['data'][$field] ?? '')
                ),
                'PromoData' => $response['promo'][$field] ?? '',
                'link' => $response['link'] ?? ''
            };
        }
        return $resolved;
    }

//    private static function resolveWhatsappTemplateParams(array $keys, array $response, $orderType): array
//    {
//        $resolved = [];
//        foreach ($keys as $key) {
//            [$group, $field] = explode('.', $key);
//            $resolved[] = match ($group) {
//                'UserData' => $response['userData'][$field] ?? '',
//                'PlanData' => in_array($orderType, ['template', 'video']) ? $response['amount'] : $response['data'][$field] ?? '',
//                'PromoData' => $response['promo'][$field] ?? ''
//            };
//        }
//        return $resolved;
//    }

//    public static function sendWhatsappTemplateMessage(UserData $userData, array $params, array $ctaBtns, $campName): array
//    {
//        return WhatsAppService::sendTemplateMessage(
//            campaignName: $campName,
//            userName: $userData->name,
//            mobile: $userData->contact_no,
//            templateParams: $params,
//            ctaButtons: $ctaBtns
//        );
//    }

    public static function sendWhatsappTemplateMessage(UserData $userData, array $params, array $ctaBtns, $campName, string $mediaUrl = ""): array
    {
        return WhatsAppService::sendTemplateMessageFromCustomCrm(
            campaignName: $campName,
            userName: $userData->name,
            mobile: $userData->contact_no,
            templateParams: $params,
            ctaButtons: $ctaBtns, media: !empty($mediaUrl), mediaUrl: $mediaUrl,
            messageType: 'leads'
        );
    }
}


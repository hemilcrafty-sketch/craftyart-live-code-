<?php

namespace App\Http\Controllers\Utils;

use App\Enums\ConfigType;
use App\Http\Controllers\EmailController;
use App\Models\Automation\EmailTemplate;
use App\Models\PromoCode;
use App\Models\Automation\WhatsappTemplate;
use App\Services\WhatsAppService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AutomationUtils
{
    public static function formatNewPlanData($subPlan, string $currency = 'INR'): array
    {
        $isInr = $currency === "INR";
        $column = $isInr ? 'inr_offer_price' : 'usd_offer_price';
        $actualColumn = $isInr ? 'inr_price' : 'usd_price';
        $currencySymbol = $isInr ? "₹" : "$";

        $price = round($subPlan->plan_details[$column], 2);
        $actualPrice = round($subPlan->plan_details[$actualColumn], 2);

        $discount = 0;
        $offerMsg = null;
        $hasOffer = 0;

        if ($price < $actualPrice) {
            $discount = (int)((($actualPrice - $price) / $actualPrice) * 100);
            $offerMsg = "Best Value ($discount% off)";
            $hasOffer = 1;
        }

        return [
            'id' => $subPlan->id,
            'package_name' => "",
            'desc' => $subPlan->plan->description,
            'validity' => $subPlan->duration->duration + $subPlan->plan_details['additional_duration'],
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price' => $currencySymbol . $price,
            'price' => $price,
            'has_offer' => $hasOffer,
            'offer_msg' => $offerMsg,
            'discount' => $discount,
        ];
    }

    public static function formatOldPlanData($plan, string $currency = 'INR'): array
    {
        $isInr = $currency === "INR";
        $column = $isInr ? 'price' : 'price_dollar';
        $actualColumn = $isInr ? 'actual_price' : 'actual_price_dollar';
        $currencySymbol = $isInr ? "₹" : "$";

        $price = round($plan->$column, 2);
        $actualPrice = round($plan->$actualColumn, 2);

        $discount = 0;
        $offerMsg = null;
        $hasOffer = 0;

        if ($price < $actualPrice) {
            $discount = (int)((($actualPrice - $price) / $actualPrice) * 100);
            $offerMsg = "Best Value ($discount% off)";
            $hasOffer = 1;
        }

        return [
            'id' => $plan->id,
            'package_name' => $plan->package_name,
            'desc' => $plan->desc,
            'validity' => $plan->validity,
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price' => $currencySymbol . $price,
            'price' => $currencySymbol . $price,
            'has_offer' => $hasOffer,
            'offer_msg' => $offerMsg,
            'discount' => $discount,
        ];
    }

    public static function generateWhatsappParams($user, $commonData, $templateConfig, $promoCodes, $paramsKey, $type): array
    {
        $response = [
            "UserData" => [
                "name" => $user->name,
                "email" => $user->email
            ]
        ];

        if (!empty($commonData['data'])) {
            $response["PlanData"] = $commonData['data'];
        }

        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            $promoCode = "";
            $promoDiscount = "";
            $expiryDate = "";

            $rawPrice = 0;
            if (in_array($commonData['planType'], ['template', 'video'])) {
                if (!empty($commonData['data']['amount'])) {
                    $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['amount']);
                }
            } else {
                if (!empty($commonData['data']['price'])) {
                    $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['price']);
                }
            }
            $discountPrice = 0;
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoCode = $promo->promo_code;
                $promoDiscount = (string)$promo->disc;
                $expiryDate = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;
                if ($rawPrice > 0) {
                    $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                    $currency = $response['data']['currency'] ?? 'INR';
                    $isInr = $currency == "INR";
                    $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
                    $currencySymbol = $isInr ? "₹" : "$";
                    $discountPrice = $currencySymbol . $discountedValue;
                }
            }
            $response["PromoData"] = [
                "code" => $promoCode,
                "disc" => "$promoDiscount%",
                "expiry_date" => $expiryDate,
                "discount_price" => (string)$discountPrice
            ];
        }
        $finalParams = [];
        foreach ($paramsKey as $key) {
            if (str_contains($key, '.')) {
                [$group, $field] = explode('.', $key);
                if ($group == "PlanData" && in_array($commonData['planType'], ['template', 'video'])) {
                    if ($field == "actual_price" || $field == "offer_price")
                        $finalParams[] = $commonData['data']['amount'] ?? "";
                    else if ($field == 'package_name')
                        $finalParams[] = $commonData['data']['templates'][0]['title'] ?? "";
                    else
                        $finalParams[] = $response[$group][$field] ?? "";
                } else {
                    $finalParams[] = $response[$group][$field] ?? "";
                }
            } else {
                $finalParams[] = $commonData[$key] ?? "";
            }
        }
        return $finalParams;
    }

//    public static function generateWhatsappParams($user, $commonData, $templateConfig, $promoCodes, $paramsKey, $type): array
//    {
//        $response = [
//            "UserData" => [
//                "name" => $user->name,
//                "email" => $user->email
//            ]
//        ];
//
//        if ($type == ConfigType::CHECKOUT_DROP_AUTOMATION->value) {
//            if (!empty($commonData['data'])) {
//                $response["PlanData"] = $commonData['data'];
//            }
//        }
//
//
//        if ($templateConfig['promo_code'] ?? false) {
//            $promoCodeId = $templateConfig['promo_code'];
//            $promoCode = "";
//            $promoDiscount = "";
//            $expiryDate = "";
//
//            $rawPrice = 0;
//            if ($type == ConfigType::CHECKOUT_DROP_AUTOMATION->value) {
//                if (in_array($commonData['planType'], ['template', 'video'])) {
//                    if (!empty($commonData['data']['amount'])) {
//                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['amount']);
//                    }
//                } else {
//                    if (!empty($commonData['data']['price'])) {
//                        $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['price']);
//                    }
//                }
//            }
//
//            $discountPrice = 0;
//
//
//            if (isset($promoCodes[$promoCodeId])) {
//                $promo = $promoCodes[$promoCodeId];
//                $promoCode = $promo->promo_code;
//                $promoDiscount = (string)$promo->disc;
//                $expiryDate = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;
//                if ($type == ConfigType::CHECKOUT_DROP_AUTOMATION->value) {
//                    if ($rawPrice > 0) {
//                        $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
//                        $currency = $response['data']['currency'] ?? 'INR';
//                        $isInr = $currency == "INR";
//                        $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float)$discountedValue, 2, '.', '');
//                        $currencySymbol = $isInr ? "₹" : "$";
//                        $discountPrice = $currencySymbol . $discountedValue;
//                    }
//                }
//
//            }
//            $response["PromoData"] = [
//                "code" => $promoCode,
//                "disc" => $promoDiscount,
//                "expiry_date" => $expiryDate,
//                "discount_price" => (string)$discountPrice
//            ];
//        }
//
//        $finalParams = [];
//
//        foreach ($paramsKey as $key) {
//            [$group, $field] = explode('.', $key);
//            if ($group == "PlanData" && in_array($commonData['planType'], ['template', 'video'])) {
//                $finalParams[] = $commonData['data']['amount'] ?? "";
//            } else {
//                $finalParams[] = $response[$group][$field] ?? "";
//            }
//        }
//
//        return $finalParams;
//    }

    /**
     * Common method to prepare WhatsApp parameters
     */

    public static function prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes): array
    {
        $name = $user->name;
        $promoCode = "";
        $promoDiscount = "";

        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value) {
            return [
                $name,
                $user->email
            ];
        }

        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoCode = $promo->promo_code;
                $promoDiscount = (string)$promo->disc;
            }

        }

        if ($type == ConfigType::RECENT_EXPIRE_AUTOMATION->value) {
            return [$name, $promoCode, $promoDiscount];
        }

        if (in_array($commonData['planType'], ['template', 'video'])) {
            $firstTemplate = $commonData['data']['templates'][0] ?? null;
            if (!$firstTemplate) {
                return ['success' => false, 'message' => "No design found in plan"];
            }

            return [
                $name,
                $commonData['data']['amount'],
                $promoCode,
                $promoDiscount
            ];

        } elseif ($commonData['planType'] === 'new_sub') {
            return [
                $name,
                $commonData['data']['offer_price'],
                $commonData['data']['actual_price'],
                $promoCode,
                $promoDiscount
            ];

        } elseif (in_array($commonData['planType'], ['old_sub', 'offer'])) {
            if ($commonData['planType'] === "offer") {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                ];
            } else {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                    $promoCode,
                    $promoDiscount
                ];
            }
        }

        return ['success' => false, 'message' => "Invalid plan type for WhatsApp parameters"];
    }

    public static function prepareWhatsAppParams222($user, $commonData, $templateConfig, $type, $promoCodes): array
    {
        $name = $user->name;
        $promoCode = "";
        $promoDiscount = "";

        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value) {
            return [
                $name,
                $user->email
            ];
        }

        if ($type == ConfigType::RECENT_EXPIRE_AUTOMATION) {
            $promoCodeId = $templateConfig['promo_code'];
            $promo = $promoCodes[$promoCodeId];
            $promoCode = $promo->promo_code;
            $promoDiscount = $promo->disc . "%";
        }

        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            $promoCode = "";
            $promoDiscount = "";
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoCode = $promo->promo_code;
                $promoDiscount = (string)$promo->disc;
            }
            return [$name, $promoCode, $promoDiscount];
        }

        if (in_array($commonData['planType'], ['template', 'video'])) {
            $firstTemplate = $commonData['data']['templates'][0] ?? null;
            if (!$firstTemplate) {
                return ['success' => false, 'message' => "No design found in plan"];
            }

            return [
                $name,
                $commonData['data']['amount'],
                $promoCode,
                $promoDiscount
            ];

        } elseif ($commonData['planType'] === 'new_sub') {
            return [
                $name,
                $commonData['data']['offer_price'],
                $commonData['data']['actual_price'],
                $promoCode,
                $promoDiscount
            ];

        } elseif (in_array($commonData['planType'], ['old_sub', 'offer'])) {
            if ($commonData['planType'] === "offer") {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                ];
            } else {
                return [
                    $name,
                    $commonData['data']['offer_price'],
                    $commonData['data']['actual_price'],
                    $promoCode,
                    $promoDiscount
                ];
            }
        }

        return ['success' => false, 'message' => "Invalid plan type for WhatsApp parameters"];
    }

    /**
     * Common method to prepare WhatsApp button
     */
    public static function prepareWhatsAppButton($commonData, $type): array
    {
        if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value || $type == ConfigType::RECENT_EXPIRE_AUTOMATION) {
            return [];
        }

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $commonData['waBtnLink']
                    ]
                ],
            ]
        ];
    }

    /**
     * Common method to send email from config
     */
    public static function sendEmailFromConfig($emailConfig, $user, $commonData, $emailTemplates, $type, $promoCodes): array
    {
        // $emailTemplateId = $emailConfig['template'] ?? null;
        $templateConfig = null;
        if (in_array($type, [ConfigType::ACCOUNT_CREATE_AUTOMATION->value, ConfigType::RECENT_EXPIRE_AUTOMATION->value, ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value])) {
            $templateConfig = $emailConfig;
        } else if ($commonData['planType'] === 'offer') {
            $templateConfig = $emailConfig['offer'] ?? null;
        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
            $templateConfig = $emailConfig['subscription'] ?? null;
        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
            $templateConfig = $emailConfig['templates'] ?? null;
        }
        if (!$templateConfig || !$templateConfig['template']) {
            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
        }
        // if (!$emailTemplateId) {
        //     return ['success' => false, 'message' => "Email template not defined in config"];
        // }
        // Use pre-fetched template
        $emailTemplate = $emailTemplates[$templateConfig['template']] ?? null;
        if ($emailTemplate === null) {
            return ['success' => false, 'message' => "Email Template not found"];
        }
        // Add promo object to email data if available
        $promoObject = null;
        if ($templateConfig['promo_code'] ?? false) {
            $promoCodeId = $templateConfig['promo_code'];
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                if ($promo) {
                    $expiry_date = $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('j F Y') : null;

                    $rawPrice = 0;
                    if (in_array($commonData['planType'], ['template', 'video', 'caricature'])) {
                        if (!empty($commonData['data']['amount'])) {
                            $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['amount']);
                        }
                    } else {
                        if (!empty($commonData['data']['price'])) {
                            $rawPrice = (float)preg_replace('/[^0-9.]/', '', $commonData['data']['price']);
                        }
                    }

                    $discountPrice = 0;
                    if ($rawPrice > 0) {
                        $discountedValue = $rawPrice - ($rawPrice * $promo->disc / 100);
                        $currency = $commonData['data']['currency'] ?? 'INR';
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
                }
            }
        }
        $emailData = [
            'userData' => $commonData['userData'],
            'type' => $commonData['type'],
            'data' => $commonData['data'],
            'link' => $commonData['link'],
            'promo' => $promoObject
        ];
        $name = str_replace('.', '/', $emailTemplate->email_template);
        $viewPath = "/var/www/craftyartapp_com/admin_panels/templates2/project/resources/views/$name.blade.php";
        if (!file_exists($viewPath))
            return ['success' => false, 'message' => "Email Template not found"];
        $htmlBody = View::file($viewPath, [
            'data' => $emailData
        ])->render();
        $result = AutomationUtils::sendEmail($user->email, $templateConfig['subject'] ?? '', $htmlBody);
        if (str_contains($result, "successfully")) {
            return ['success' => true, 'message' => 'Email Sent Successfully'];
        }
        return ['success' => false, 'message' => $result];
    }

    /**
     * Common method to send WhatsApp from config
     */
    public static function sendWhatsAppFromConfig($wpConfig, $user, $commonData, $contactNumber, $type, $whatsappTemplates, $promoCodes): array
    {
        if (!$contactNumber) {
            return ['success' => false, 'message' => "Contact number not found"];
        }

        // Determine which template config to use based on plan type
        $templateConfig = null;
        if (in_array($type, [ConfigType::ACCOUNT_CREATE_AUTOMATION->value, ConfigType::RECENT_EXPIRE_AUTOMATION->value, ConfigType::EXPORT_WITH_WATERMARK_AUTOMATION->value])) {
            $templateConfig = $wpConfig;
        } else if ($commonData['planType'] === 'offer') {
            $templateConfig = $wpConfig['offer'] ?? null;
        } elseif (in_array($commonData['planType'], ['new_sub', 'old_sub'])) {
            $templateConfig = $wpConfig['subscription'] ?? null;
        } elseif (in_array($commonData['planType'], ['template', 'video'])) {
            $templateConfig = $wpConfig['templates'] ?? null;
        }

        if (!$templateConfig || !$templateConfig['template']) {
            return ['success' => false, 'message' => "WhatsApp template not defined for plan type: {$commonData['planType']}"];
        }

        // Use pre-fetched template
        $whatsappTemplate = $whatsappTemplates[$templateConfig['template']] ?? null;
        if (!$whatsappTemplate) {
            return ['success' => false, 'message' => "WhatsApp Template not found"];
        }

//        $templateParams = self::prepareWhatsAppParams(user: $user, commonData: $commonData, templateConfig: $templateConfig, type: $type, promoCodes: $promoCodes);
        $templateParams = self::generateWhatsappParams(user: $user, commonData: $commonData, templateConfig: $templateConfig, promoCodes: $promoCodes, paramsKey: $whatsappTemplate->template_params, type: $type);
        $count = (int)$whatsappTemplate->template_params_count;

        if ($count != count($templateParams)) {
            return ['success' => false, 'message' => "WhatsApp Template parameter count mismatch"];
        }

        // Prepare dynamic button with payment link
        $dynamicButton = self::prepareWhatsAppButton(commonData: $commonData, type: $type);

//        $result = EmailController::sendWhatsappTemplateMessage($user, $templateParams, $dynamicButton, $whatsappTemplate->campaign_name);
        $result = EmailController::sendWhatsappTemplateMessage($user, $templateParams, $dynamicButton, $whatsappTemplate->campaign_name, $whatsappTemplate->url ?? "");

        return AutomationUtils::handleWhatsAppResponse($result);
    }

    public static function handleWhatsAppResponse($result): array
    {
        if (is_array($result)) {
            $status = $result['success'] ?? false;
            $message = $result['message'] ?? 'Something went wrong';
        } else {
            $status = $result->success ?? false;
            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
        }

        return [
            'success' => $status,
            'message' => $message,
        ];
    }

    /**
     * Common method to handle automation for job
     */
    public static function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData, $type): array
    {
        $results = [];

        // Handle Email Automation (if enabled)
        if ($frequencyConfig['email']['enable'] ?? false) {
            $emailConfig = $frequencyConfig['email']['config'] ?? [];
            $results['email'] = self::sendEmailFromConfig(
                emailConfig: $emailConfig,
                user: $user,
                commonData: $commonData,
                emailTemplates: $allTemplateData['emailTemplates'],
                type: $type,
                promoCodes: $allTemplateData['promoCodes']
            );
        }

        // Handle WhatsApp Automation (if enabled)
        if ($frequencyConfig['wp']['enable'] ?? false) {
            $wpConfig = $frequencyConfig['wp']['config'] ?? [];
            $results['whatsapp'] = self::sendWhatsAppFromConfig(
                wpConfig: $wpConfig,
                user: $user,
                commonData: $commonData,
                contactNumber: $contactNumber,
                type: $type,
                whatsappTemplates: $allTemplateData['whatsappTemplates'],
                promoCodes: $allTemplateData['promoCodes']
            );
        }

        return $results;
    }

    /**
     * Common method to pre-fetch templates and promo codes
     */
    public static function preFetchAllTemplatesAndPromoCodes(array $configData, string $configType): array
    {
        $allTemplateIds = [
            'email' => [],
            'whatsapp' => []
        ];
        $allPromoCodeIds = [];
        // Normalize structure for offer purchase type (since it's a single object, not an array)
        $configs = ($configType == ConfigType::ACCOUNT_CREATE_AUTOMATION->value)
            ? [$configData]
            : $configData;
        foreach ($configs as $frequency) {

            if (!empty($frequency['email']['enable']) && !empty($frequency['email']['config'])) {
                $emailConfig = $frequency['email']['config'];
                if (
                    $configType == ConfigType::ACCOUNT_CREATE_AUTOMATION->value ||
                    $configType == ConfigType::RECENT_EXPIRE_AUTOMATION->value
                ) {
                    // Single WhatsApp template & promo code
                    if (!empty($emailConfig['template'])) {
                        $allTemplateIds['email'][] = $emailConfig['template'];
                    }
                    if (!empty($emailConfig['promo_code'])) {
                        $allPromoCodeIds[] = $emailConfig['promo_code'];
                    }
                } else {
                    // Multiple types (offer, subscription, templates)
                    foreach (['offer', 'subscription', 'templates'] as $type) {
                        if (!empty($emailConfig[$type]['template'])) {
                            $allTemplateIds['email'][] = $emailConfig[$type]['template'];
                        }
                        if (!empty($emailConfig[$type]['promo_code'])) {
                            $allPromoCodeIds[] = $emailConfig[$type]['promo_code'];
                        }
                    }
                }
            }
            if (!empty($frequency['wp']['enable']) && !empty($frequency['wp']['config'])) {
                $wpConfig = $frequency['wp']['config'];
                // Different logic depending on config type
                if (
                    $configType == ConfigType::ACCOUNT_CREATE_AUTOMATION->value ||
                    $configType == ConfigType::RECENT_EXPIRE_AUTOMATION->value
                ) {
                    // Single WhatsApp template & promo code
                    if (!empty($wpConfig['template'])) {
                        $allTemplateIds['whatsapp'][] = $wpConfig['template'];
                    }
                    if (!empty($wpConfig['promo_code'])) {
                        $allPromoCodeIds[] = $wpConfig['promo_code'];
                    }
                } else {
                    // Multiple types (offer, subscription, templates)
                    foreach (['offer', 'subscription', 'templates'] as $type) {
                        if (!empty($wpConfig[$type]['template'])) {
                            $allTemplateIds['whatsapp'][] = $wpConfig[$type]['template'];
                        }
                        if (!empty($wpConfig[$type]['promo_code'])) {
                            $allPromoCodeIds[] = $wpConfig[$type]['promo_code'];
                        }
                    }
                }
            }
        }
        // --- REMOVE DUPLICATES ---
        $allTemplateIds['email'] = array_unique($allTemplateIds['email']);
        $allTemplateIds['whatsapp'] = array_unique($allTemplateIds['whatsapp']);
        $allPromoCodeIds = array_unique($allPromoCodeIds);
        // --- FETCH TEMPLATES & PROMO CODES ---
        $emailTemplates = EmailTemplate::whereIn('id', $allTemplateIds['email'])->get()->keyBy('id');
        $whatsappTemplates = WhatsappTemplate::whereIn('id', $allTemplateIds['whatsapp'])->get()->keyBy('id');
        $promoCodes = PromoCode::whereIn('id', $allPromoCodeIds)->get()->keyBy('id');
        return [
            'emailTemplates' => $emailTemplates,
            'whatsappTemplates' => $whatsappTemplates,
            'promoCodes' => $promoCodes
        ];
    }

    public static function sendEmail($to, $subject, $body): string
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $body) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                    ->to($to)
                    ->replyTo(env("MAIL_FROM_ADDRESS"), 'Reply Support')
                    ->subject($subject)
                    ->setBody($body, 'text/html');

                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
            return "Email sent successfully";
        } catch (\Throwable $e) {
            return "Failed: " . $e->getMessage();
        }
    }
}

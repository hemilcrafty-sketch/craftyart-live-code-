<?php

namespace App\Http\Controllers\Utils;



use App\Enums\ConfigType;
use App\Models\Automation\EmailTemplate;
use App\Models\PromoCode;
use App\Models\Automation\WhatsappTemplate;
use Illuminate\Support\Facades\View;

class AutomationUtils2
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
            $offerMsg = "Best Value ({$discount}% off)";
            $hasOffer = 1;
        }

        $data = [
            'id'           => $subPlan->id,
            'package_name' => "",
            'desc'         => $subPlan->plan->description,
            'validity'     => $subPlan->duration->duration + $subPlan->plan_details['additional_duration'],
            'currency'     => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price'  => $currencySymbol . $price,
            'price'        => $price,
            'has_offer'    => $hasOffer,
            'offer_msg'    => $offerMsg,
            'discount'     => $discount,
        ];

        return $data;
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
            $offerMsg = "Best Value ({$discount}% off)";
            $hasOffer = 1;
        }

        $data = [
            'id'           => $plan->id,
            'package_name' => $plan->package_name,
            'desc'         => $plan->desc,
            'validity'     => $plan->validity,
            'currency'     => $currency,
            'currency_symbol' => $currencySymbol,
            'actual_price' => $currencySymbol . $actualPrice,
            'offer_price'  => $currencySymbol . $price,
            'price'        => $currencySymbol .$price,
            'has_offer'    => $hasOffer,
            'offer_msg'    => $offerMsg,
            'discount'     => $discount,
        ];

        return $data;
    }

    /**
     * Common method to prepare WhatsApp parameters
     */
    public static function prepareWhatsAppParams($user, $commonData, $templateConfig, $type): array
    {
        $name = $user->name;
        $promoCode = "";
        $promoDiscount = "";

        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value || $type == ConfigType::RECENT_EXPIRE_AUTOMATION){
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
                $promoDiscount = $promo->disc . "%";
            }
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
                    $commonData['paymentLink']
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
        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value || $type == ConfigType::RECENT_EXPIRE_AUTOMATION){
            return [];
        }

        $buttonText = $commonData['paymentLink'];

        if (in_array($commonData['planType'], ['template', 'video'])) {
            $buttonText = "/redirect/" . $commonData['paymentLink'];
        } elseif ($commonData['planType'] === 'new_sub') {
            $buttonText = "/redirect/" . $commonData['paymentLink'];
        } elseif ($commonData['planType'] === 'old_sub') {
            if (str_contains($commonData['paymentLink'], 'offer-package')) {
                $buttonText = $commonData['paymentLink'];
            } else {
                $buttonText = "/payment/redirect=" . $commonData['paymentLink'];
            }
        }

        return [
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $buttonText
                    ]
                ],
            ]
        ];
    }

    /**
     * Common method to handle WhatsApp response
     */
    public static function handleWhatsAppResponse($result, $templateParams = []): array
    {
        if (is_array($result)) {
            $status = $result['status'] ?? false;
            $message = $result['message'] ?? 'Something went wrong';
        } else {
            $status = $result->status ?? false;
            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
        }

        return [
            'success' => $status,
            'message' => $message,
        ];
    }

    /**
     * Common method to send email from config
     */
    public static function sendEmailFromConfig($emailConfig, $user, $commonData, $emailTemplates, $promoCodes, $type): array
    {
        $emailTemplateId = $emailConfig['template'] ?? null;
        if (!$emailTemplateId) {
            return ['success' => false, 'message' => "Email template not defined in config"];
        }

        // Use pre-fetched template
        $emailTemplate = $emailTemplates[$emailTemplateId] ?? null;
        if (!$emailTemplate) {
            return ['success' => false, 'message' => "Email Template not found"];
        }

        // Add promo object to email data if available
        $promoObject = null;
        if ($emailConfig['promo_code'] ?? false) {
            $promoCodeId = $emailConfig['promo_code'];
            if (isset($promoCodes[$promoCodeId])) {
                $promo = $promoCodes[$promoCodeId];
                $promoObject = [
                    'code' => $promo->promo_code,
                    'disc' => $promo->disc
                ];
            }
        }

        $emailData = [
            'userData' => $commonData['userData'],
            'type' => $commonData['type'],
            'data' => $commonData['data'],
            'link' => $commonData['link'],
            'promo' => $promoObject
        ];

        $htmlBody = View::make($emailTemplate->email_template, [
            'data' => $emailData
        ])->render();

        $result = EmailTemplateController::sendEmail($user->email, $emailConfig['subject'] ?? '', $htmlBody);

        if (str_contains($result, "successfully")) {
            return ['success' => true, 'message' => 'Email Sent Successfully'];
        }

        return ['success' => false, 'message' => $result];
    }

    /**
     * Common method to send WhatsApp from config
     */
    public static function sendWhatsAppFromConfig($wpConfig, $user, $commonData, $contactNumber, $type, $promoCodes, $whatsappTemplates): array
    {
        if (!$contactNumber) {
            return ['success' => false, 'message' => "Contact number not found"];
        }

        // Determine which template config to use based on plan type
        $templateConfig = null;
        if($type == ConfigType::OFFER_PURCHASE_AUTOMATION->value) {
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

        $templateParams = self::prepareWhatsAppParams($user, $commonData, $templateConfig, $type, $promoCodes);

        $count = (int)$whatsappTemplate->template_params_count;

        if ($count != count($templateParams)) {
            return ['success' => false, 'message' => "WhatsApp Template parameter count mismatch"];
        }

        // Prepare dynamic button with payment link
        $dynamicButton = self::prepareWhatsAppButton($commonData, $type);

        // Send WhatsApp message (commented out for now)

        //        $result = WhatsAppService::sendTemplateMessage(
//            $whatsappTemplate->campaign_name,
//            $user->name,
//            $contactNumber,
//            $templateParams,
//            $dynamicButton
//        );
//
//        return $this->handleWhatsAppResponse($result, $templateParams);

        return [
            "success"=> true,
            "message"=>"Generated",
            "campaign_name"=> $whatsappTemplate->campaign_name,
            "name"=> $user->name,
            "contact_number" => $contactNumber,
            "template_params" => $templateParams,
            "dynamic_button" => $dynamicButton
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
                $emailConfig,
                $user,
                $commonData,
                $allTemplateData['emailTemplates'],
                $allTemplateData['promoCodes'],
                $type
            );
        }

        // Handle WhatsApp Automation (if enabled)
        if ($frequencyConfig['wp']['enable'] ?? false) {
            $wpConfig = $frequencyConfig['wp']['config'] ?? [];
            $results['whatsapp'] = self::sendWhatsAppFromConfig(
                $wpConfig,
                $user,
                $commonData,
                $contactNumber,
                $type,
                $allTemplateData['promoCodes'],
                $allTemplateData['whatsappTemplates']
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

        // Handle different config structures
        if ($configType == ConfigType::OFFER_PURCHASE_AUTOMATION->value) {

            // Single configuration object for offer purchase
            $frequency = $configData;
            // Email templates and promo codes
            if (isset($frequency['email']['enable']) && $frequency['email']['enable']) {
                $emailTemplateId = $frequency['email']['config']['template'] ?? null;
                if ($emailTemplateId) {
                    $allTemplateIds['email'][] = $emailTemplateId;
                }

                // Collect email promo code IDs
                $emailPromoCodeId = $frequency['email']['config']['promo_code'] ?? null;
                if ($emailPromoCodeId) {
                    $allPromoCodeIds[] = $emailPromoCodeId;
                }
            }

            // WhatsApp templates
            if (isset($frequency['wp']['enable']) && $frequency['wp']['enable']) {
                $wpConfig = $frequency['wp']['config'] ?? [];

                // For offer purchase, template is directly in wp.config
                $templateId = $wpConfig['template'] ?? null;

                if ($templateId) {
                    $allTemplateIds['whatsapp'][] = $templateId;
                }

                // Also collect promo code IDs if any
                $promoCodeId = $wpConfig['promo_code'] ?? null;
                if ($promoCodeId) {
                    $allPromoCodeIds[] = $promoCodeId;
                }
            }

        } else if ($configType == ConfigType::RECENT_EXPIRE_AUTOMATION->value){
            foreach ($configData as $frequency) {
                // Email templates and promo codes
                if (isset($frequency['email']['enable']) && $frequency['email']['enable']) {
                    $emailTemplateId = $frequency['email']['config']['template'] ?? null;
                    if ($emailTemplateId) {
                        $allTemplateIds['email'][] = $emailTemplateId;
                    }

                    // Collect email promo code IDs
                    $emailPromoCodeId = $frequency['email']['config']['promo_code'] ?? null;
                    if ($emailPromoCodeId) {
                        $allPromoCodeIds[] = $emailPromoCodeId;
                    }
                }

                // WhatsApp templates
                if (isset($frequency['wp']['enable']) && $frequency['wp']['enable']) {
                    $wpConfig = $frequency['wp']['config'] ?? [];

                    // For offer purchase, template is directly in wp.config
                    $templateId = $wpConfig['template'] ?? null;

                    if ($templateId) {
                        $allTemplateIds['whatsapp'][] = $templateId;
                    }

                    // Also collect promo code IDs if any
                    $promoCodeId = $wpConfig['promo_code'] ?? null;
                    if ($promoCodeId) {
                        $allPromoCodeIds[] = $promoCodeId;
                    }

                }
            }
        } else {
            foreach ($configData as $frequency) {
                // Email templates and promo codes
                if (isset($frequency['email']['enable']) && $frequency['email']['enable']) {
                    $emailTemplateId = $frequency['email']['config']['template'] ?? null;
                    if ($emailTemplateId) {
                        $allTemplateIds['email'][] = $emailTemplateId;
                    }

                    // Collect email promo code IDs
                    $emailPromoCodeId = $frequency['email']['config']['promo_code'] ?? null;
                    if ($emailPromoCodeId) {
                        $allPromoCodeIds[] = $emailPromoCodeId;
                    }
                }

                // WhatsApp templates
                if (isset($frequency['wp']['enable']) && $frequency['wp']['enable']) {
                    $wpConfig = $frequency['wp']['config'] ?? [];

                    // Check all template types for WhatsApp
                    $templateTypes = ['offer', 'subscription', 'templates'];
                    foreach ($templateTypes as $type) {
                        $templateId = $wpConfig[$type]['template'] ?? null;
                        if ($templateId) {
                            $allTemplateIds['whatsapp'][] = $templateId;
                        }

                        // Also collect promo code IDs
                        $promoCodeId = $wpConfig[$type]['promo_code'] ?? null;
                        if ($promoCodeId) {
                            $allPromoCodeIds[] = $promoCodeId;
                        }
                    }
                }
            }
        }

        // Remove duplicates
        $allTemplateIds['email'] = array_unique($allTemplateIds['email']);
        $allTemplateIds['whatsapp'] = array_unique($allTemplateIds['whatsapp']);
        $allPromoCodeIds = array_unique($allPromoCodeIds);

        // Fetch all data in single queries
        $emailTemplates = EmailTemplate::whereIn('id', $allTemplateIds['email'])->get()->keyBy('id');
        $whatsappTemplates = WhatsappTemplate::whereIn('id', $allTemplateIds['whatsapp'])->get()->keyBy('id');
        $promoCodes = PromoCode::whereIn('id', $allPromoCodeIds)->get()->keyBy('id');
        return [
            'emailTemplates' => $emailTemplates,
            'whatsappTemplates' => $whatsappTemplates,
            'promoCodes' => $promoCodes
        ];
    }
}

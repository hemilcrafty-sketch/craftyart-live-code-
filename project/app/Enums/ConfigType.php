<?php

namespace App\Enums;

enum ConfigType: int
{
    case CAMPAIGN = 1;
    case EMAIL_OFFER_PURCHASE_AUTOMATION = 2;
    case EMAIL_CHECKOUT_DROP_AUTOMATION = 3;
    case WHATSAPP_CHECKOUT_DROP_AUTOMATION = 4;
    case WHATSAPP_OFFER_PURCHASE_AUTOMATION = 5;
    case ACCOUNT_CREATE_AUTOMATION = 6;
    case CHECKOUT_DROP_AUTOMATION = 7;
    case RECENT_EXPIRE_AUTOMATION = 8;
    case EXPORT_WITH_WATERMARK_AUTOMATION = 9;
    case INSTANT_TEMPLATE_PURCHASE = 10;
    // 👇 add helper to return label
    public function label(): string
    {
        return match($this) {
            self::CAMPAIGN => 'campaign',
            self::EMAIL_OFFER_PURCHASE_AUTOMATION => 'email_offer_purchase_automation',
            self::EMAIL_CHECKOUT_DROP_AUTOMATION => 'email_checkout_drop_automation',
            self::WHATSAPP_CHECKOUT_DROP_AUTOMATION => 'whatsapp_checkout_drop_automation',
            self::WHATSAPP_OFFER_PURCHASE_AUTOMATION => 'whatsapp_offer_purchase_automation',
            self::CHECKOUT_DROP_AUTOMATION => 'checkout_drop_automation',
            self::RECENT_EXPIRE_AUTOMATION => 'recent_expire_automation',
            self::ACCOUNT_CREATE_AUTOMATION => 'account_create_automation',
            self::EXPORT_WITH_WATERMARK_AUTOMATION => 'download_with_export_automation',
            self::INSTANT_TEMPLATE_PURCHASE => 'instant_template_purchase',
        };
    }
}
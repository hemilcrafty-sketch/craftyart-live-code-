<?php

namespace App\Enums;

enum MailSendType: int
{
    case CAMPAIGN = 1;
    case EMAIL_OFFER_PURCHASE_AUTOMATION = 2;
    case EMAIL_CHECKOUT_DROP_AUTOMATION = 3;

    // 👇 add helper to return label
    public function label(): string
    {
        return match($this) {
            self::CAMPAIGN => 'campaign',
            self::EMAIL_OFFER_PURCHASE_AUTOMATION => 'email_offer_purchase_automation',
            self::EMAIL_CHECKOUT_DROP_AUTOMATION => 'email_checkout_drop_automation',
        };
    }
}
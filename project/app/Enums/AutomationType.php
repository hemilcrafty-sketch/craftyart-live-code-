<?php

namespace App\Enums;

enum AutomationType: string
{
    case EMAIL = 'email';
    case WHATSAPP = 'whatsapp';
    case EMAIL_WHATSAPP = 'email & whatsapp';
}
<?php

namespace App\Enums;

enum PlanFeatureSlug:string
{
    case ACCESS_TEMPLATE = 'access_template';
    case DOWNLOAD_LIMIT = 'download_limit';
    case ACCESS_CARICATURE = 'access_caricature';
    case AI_CREDIT = 'ai_credit';

    public function label(): string
    {
        return match($this) {
            self::ACCESS_TEMPLATE => 'Access Template',
            self::DOWNLOAD_LIMIT => 'Download Limit',
            self::ACCESS_CARICATURE => 'Access Caricature',
            self::AI_CREDIT => 'Ai Credit',
        };
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
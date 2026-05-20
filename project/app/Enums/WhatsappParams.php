<?php

namespace App\Enums;

enum WhatsappParams
{
    public static function list(): array
    {
        return [
            'UserData' => [
                'name',
                'email',
            ],
            'PlanData' => [
                'actual_price',
                'offer_price',
                'description',
                'validity',
                'discount',
                'package_name'
            ],
            'PromoData' => [
                'code',
                'disc',
                'expiry_date',
                'discount_price',
            ],
            "link" => 'link'
        ];
    }
}
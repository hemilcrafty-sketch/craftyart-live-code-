<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{

    public static function sendTemplateMessage(
        string $campaignName,
        string $userName,
        string $mobile,
        array $templateParams,
        array $ctaButtons = [],
        bool $media = false,
        string $mediaUrl = ""
    ): array {
        $url = "https://backend.aisensy.com/campaign/t1/api/v2";
        $payload = [
            "apiKey" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY4ZGExNGIyNTJkYWI4MTM0MDQ5YTNmMSIsIm5hbWUiOiJJbmZpYXBwIFNvbHV0aW9uIiwiYXBwTmFtZSI6IkFpU2Vuc3kiLCJjbGllbnRJZCI6IjY4ZGExNGIyNTJkYWI4MTM0MDQ5YTNlYyIsImFjdGl2ZVBsYW4iOiJGUkVFX0ZPUkVWRVIiLCJpYXQiOjE3NTkxMjI2MTB9.ueNuD0LTasaGyuasoYVRAp-ZLA5kcofIU7FDLc-fiJ0",
            "campaignName" => $campaignName,
            "destination" => $mobile,
            "userName" => $userName,
            "templateParams" => $templateParams,
        ];
        if ($media && !empty($mediaUrl)) {
            $payload["media"] = [
                "url" => $mediaUrl,
                "filename" => "file"
            ];
        }

        if (!empty($ctaButtons)) {
            $payload["buttons"] = $ctaButtons;
        }

        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->post($url, $payload);

        return $response->json();
    }

    public static function sendTemplateMessageFromCustomCrm(
        string $campaignName,
        string $userName,
        string $mobile,
        array $templateParams,
        array $ctaButtons = [],
        bool $media = false,
        string $mediaUrl = "",
        string $messageType = 'support'
    ): array {
        $url = "https://crm.craftyart.tech/api/v1/send_templet";

        $allowedTypes = ['support', 'leads'];

        if (!in_array($messageType, $allowedTypes)) {
            return [
                "status" => false,
                "message" => "Invalid messageType. Allowed values: support, leads"
            ];
        }

        // ✅ Token mapping
        $tokens = [
            'support' => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOiIwNEtoSGoxOVJYSnhRZlhxYlR6bnI0RzV1U2tJR3g0UiIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzczMjI1MzU3fQ.H2YmcLRxMkIObbxf2z-6Cjeyz82KPksNST8gyjNvSeo",
            'leads' => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOiJDSXRZTjRES3g4OVZrbGNtUEhJVjIzRVZOWEtiS0FuMCIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzc1Mjc1NjY4fQ.3N2xhdblOn8IjuhAJmTO-tR57jjH6xQnZpf2wy4Gy14"
        ];

        $token = $tokens[$messageType];

        $payload = [
            "sendTo" => $mobile,
            "templetName" => $campaignName,
            "exampleArr" => $templateParams,
            "token" => $token,
            "enableLog" => true
        ];
        if ($media && !empty($mediaUrl)) {
            $payload["mediaUri"] = $mediaUrl;
        }

        if (!empty($ctaButtons)) {
            $payload["buttons"] = $ctaButtons;
        }

        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->post($url, $payload);

        return $response->json();
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_KEY', '');
    }

    /**
     * Generate content (image, text, etc.) from Gemini model.
     *
     * @param string $model
     * @param array $payload
     * @param array $config
     * @return array|string
     */
    public function generateContent(string $model, array $payload, array $config = [], bool $retry = false): array|string
    {
        $apiKey = $retry ? env('FALLBACK_GEMINI_KEY', '') : $this->apiKey;
        $endpoint = "{$this->baseUrl}{$model}:generateContent?key=$apiKey";

        // Merge config if provided
        if (!empty($config)) {
            $payload['generationConfig'] = $config;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        if (!$retry) return $this->generateContent($model, $payload, $config, true);

        return [
            'error' => $response->json(),
            'status' => $response->status(),
        ];
    }
}

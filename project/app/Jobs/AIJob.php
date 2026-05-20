<?php

namespace App\Jobs;

use App\Events\ImageGenerationStatusUpdated;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Services\GeminiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 300; // 5 minutes

    public function __construct(public string $uid)
    {
    }

    public function handle(): void
    {
        // --- Broadcast Start Event ---
        try {
            Log::info('Attempting to broadcast [started] event...');
            broadcast(new ImageGenerationStatusUpdated(
                status: 'started',
                message: 'Image generation has started...',
                imageUrl: null,
                userId: $this->uid
            ));
            Log::info('[started] event broadcasted successfully.');
        } catch (\Exception $e) {
            Log::error('BROADCAST FAILED for [started] event.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Add trace for more details
            ]);
        }

        // --- Generate Image ---
        $gemini = new GeminiService();
        $firstCartoonImage = $this->generateCartoonImage($gemini);

        // --- Broadcast Result Event ---
        if (is_null($firstCartoonImage)) {
            try {
                Log::info('Attempting to broadcast [failed] event...');
                broadcast(new ImageGenerationStatusUpdated(
                    status: 'failed',
                    message: 'Model timed out or failed',
                    imageUrl: null,
                    userId: $this->uid
                ));
                Log::info('[failed] event broadcasted successfully.');
            } catch (\Exception $e) {
                Log::error('BROADCAST FAILED for [failed] event.', ['error' => $e->getMessage()]);
            }
            return;
        }

        $finalOutput = "ai/demo/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($firstCartoonImage['mimeType']);
        StorageUtils::put($finalOutput, base64_decode($firstCartoonImage['base64']));

        try {
            Log::info('Attempting to broadcast [success] event...');
            broadcast(new ImageGenerationStatusUpdated(
                status: 'success',
                message: 'Image ready',
                imageUrl: json_encode([HelperController::$mediaUrl . $finalOutput]),
                userId: $this->uid
            ));
            Log::info('[success] event broadcasted successfully.');
        } catch (\Exception $e) {
            Log::error('BROADCAST FAILED for [success] event.', ['error' => $e->getMessage()]);
        }
    }

    private function generateCartoonImage(GeminiService $gemini): array|null
    {
        try {
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => "Generate a cute dog photo"
                        ]
                    ]
                ]],
            ];

            $config = [
                'responseModalities' => ['IMAGE'],
//                'temperature' => 0.8,
            ];
            $response = $gemini->generateContent('gemini-2.5-flash-image', $payload, $config);

            if (isset($response['error'])) return null;

            $generatedImages = [];
            $candidates = $response['candidates'] ?? [];
            foreach ($candidates as $candidate) {
                $contentParts = $candidate['content']['parts'] ?? [];
                foreach ($contentParts as $part) {
                    if (isset($part['inlineData']['data'])) {
                        $base64 = $part['inlineData']['data'];
                        $mimeType = $part['inlineData']['mimeType'] ?? 'image/png';
                        $generatedImages[] = [
                            'base64' => $base64,
                            'mimeType' => $mimeType,
                        ];
                    }
                }
            }

            return $generatedImages[0];
        } catch (Exception $e) {
            Log::error('Gemini API failed.', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

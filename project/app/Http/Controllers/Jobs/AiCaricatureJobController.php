<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AI\AICreditTransaction;
use App\Models\AI\AIJobModel;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\CreatedCaricature;
use App\Models\UserData;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Storage;
use Exception;

class AiCaricatureJobController
{

    public string $cartoonPrompt = "Transform the original image into a clean, detailed digital illustration in a modern cartoon or vector art style. The result should look professionally hand-drawn and digitally rendered, with smooth lines, clear outlines, and flat vibrant colors.
        Use {{skinColor}} or all skin tones to keep them uniform.
        Emphasize sharp detailing on facial features, clothing, and hair. Keep the skin smooth and matte, but add a soft, natural glow on the cheeks.
        Ensure even, soft lighting with no harsh shadows.
        The final image should have a high-resolution, crisp, and polished look suitable for editorial or commercial use.";

    /** @noinspection SqlNoDataSourceInspection */
    public string $caricaturePrompt = "Replace the placeholder face areas in the second image with the exact facial features and structure of the people in the first image, maintaining their natural identity, expression, and proportions.
        The final faces should match the same person’s look — including their face shape, eyes, nose, lips, smile, eyebrows, and hairline — while converting them into a clean, modern cartoon/vector art style consistent with the reference illustration in the first image.
        Preserve the original body posture, outfits, jewelry, and background of the second image exactly as they are — do not blur, crop, or modify the background.
        Align the faces precisely with the neck and shoulders to maintain natural proportions.
        Use smooth outlines, soft shading, and even lighting with no harsh shadows.
        Match the skin tone exactly to {{skinColor}} so the faces blend seamlessly with the body illustration.
        Ensure sharp and clean detailing of facial features, polished edges at the neckline, and a high-resolution, unified finish.
        The final result should look like a single hand-drawn digital illustration, not a photo edit.
        The final image must have a solid pure color (#FFFFFFF) background.";

    public string $thirdPrompt = "You are an expert digital artist specializing in creating caricatures.
        Your task is to perfectly combine two images:

        1.  **Image 1:** This image contains the cartoon-style heads of a couple.
        2.  **Image 2:** This image contains the desired attire on bodies.
        3.  **SCALE & ALIGN:** This is the most critical step. You MUST resize the heads to be anatomically proportional to the bodies. A normal human head is approximately the same width as the person's shoulders. Align the heads perfectly onto the necks. You can adjust the head angle so final output looks great.
        4.  **BLEND:** Create a seamless, invisible transition between the cartoon heads and the necks on the bodies.

        Follow these instructions with extreme precision:
        - Expertly and cleanly extract the heads from Image 1.
        - Seamlessly place these exact heads onto the corresponding bodies in Image 2.
        - Ensure the head-to-body ratio is natural and proportional for a caricature.
        - Blend the necks perfectly with the attire for a flawless look.
        - Do not change attire pose.
        - Produce a high-resolution, crisp, and polished final image. Do not add any text or watermarks.
        - Remove background and make it transparent in final output.";

    public function instantSend($job_id): void
    {
        $job = AIJobModel::find($job_id);
        if (!$job) return;

        $cariData = null;

        try {

            $data = $job->data;
            $string_id = $data['attire_id'];
            $user_input = $data['user_input'];

            if (!StorageUtils::exists($user_input)) {
                $job->update(['error_msg' => 'User Image not found', 'status' => 'failed']);
                return;
            }

            $user = UserData::whereUid($job->user_id)->first();
            if (!$user) {
                StorageUtils::delete($user_input);
                $job->update(['error_msg' => 'User not found', 'status' => 'failed']);
                return;
            }

            $user_id = $user->uid;
            $isTester = $user_id == "YTC1UOvR05hSKSkJSXFnb6LUFAi1";

            $attire = Attire::whereStringId($string_id)->first();
            if (!$attire) {
                StorageUtils::delete($user_input);
                $job->update(['error_msg' => 'Attire not found', 'status' => 'failed']);
                return;
            }

            $pyt = RateController::getCaricatureRates([], $attire->head_count, false, $attire->editor_choice == 1);
            $ai_credits = $pyt['inrVal'];

            $needPurchase = $user->ai_credit < $ai_credits;

            if ($needPurchase) {
                $cariData = CaricaturePurchaseHistory::whereUserId($user_id)->whereProductId($string_id)->whereUsed(0)->first();
                if (!$cariData) {
                    StorageUtils::delete($user_input);
                    $job->update(['status' => 'purchase']);
                    return;
                }
            }

            $userImage = StorageUtils::get($user_input);
            $attireImage = StorageUtils::get($attire->coordinate_image);

            $userImageBase64 = base64_encode($userImage);
            $userImageMime = Storage::mimeType($user_input);

            $attireImageBase64 = base64_encode($attireImage);
            $attireImageMime = Storage::mimeType($attire->coordinate_image);

            $gemini = new GeminiService();

            $prompt = str_replace("{{skinColor}}", $attire->skin_color, $this->cartoonPrompt);

            $firstCartoonImage = $this->generateCartoonImage($gemini, $userImageBase64, $userImageMime, $prompt);
            if (is_string($firstCartoonImage)) {
                StorageUtils::delete($user_input);
                $job->update(['error_msg' => $firstCartoonImage, 'status' => 'failed']);
                return;
            }

            $cartoonImage = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($firstCartoonImage['mimeType']);
            StorageUtils::put($cartoonImage, base64_decode($firstCartoonImage['base64']));


            $allPrompts = [
                "Complete prompt from the second image for context use first image do not change second image background, match the skin tones",
                str_replace("{{skinColor}}", $attire->skin_color, $this->caricaturePrompt),
                $this->thirdPrompt
            ];

            $caricatures = [];
            $errors = [];

            foreach ($allPrompts as $cariPrompt) {
                $firstCaricature = $this->generateCaricature($gemini, $firstCartoonImage['base64'], $firstCartoonImage['mimeType'], $attireImageBase64, $attireImageMime, $cariPrompt);
                if (is_array($firstCaricature)) {
                    $caricatures[] = $firstCaricature;
                } else {
                    $errors[] = $firstCaricature;
                }
            }

            if (empty($caricatures)) {
                StorageUtils::delete($user_input);
                StorageUtils::delete($cartoonImage);
                $job->update(['error_msg' => json_encode($errors), 'status' => 'failed']);
                return;
            }

            $images = [];

            foreach ($caricatures as $caricature) {
                $first = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($caricature['mimeType']);
                StorageUtils::put($first, base64_decode($caricature['base64']));
                $images[] = $first;
            }

            if (empty($images)) {
                StorageUtils::delete($user_input);
                StorageUtils::delete($cartoonImage);
                $job->update(['error_msg' => 'Caricature Generation failed', 'status' => 'failed']);
                return;
            }

            if (!$isTester && $cariData) {
                $cariData->used = 1;
                $cariData->save();
            }

            if ($cariData == null) {
                $user->decrement('ai_credit', $ai_credits);
            }

            $caricatureRes = new CreatedCaricature();
            $caricatureRes->user_id = $user_id;
            $caricatureRes->caricature_id = $attire->string_id;
            $caricatureRes->images = json_encode($images);
            $caricatureRes->payment_id = $cariData == null ? "$ai_credits credits used" : $cariData->payment_id;
            $caricatureRes->user_input = $user_input;
            $caricatureRes->cartoon_image = $cartoonImage;
            $caricatureRes->show_data = $isTester ? 0 : 1;
            $caricatureRes->save();

            if ($cariData == null) {
                $resTrans = new AICreditTransaction();
                $resTrans->user_id = $user_id;
                $resTrans->ref_id = $caricatureRes->id;
                $resTrans->txn_id = AICreditTransaction::generateTxnId();
                $resTrans->type = 'caricature';
                $resTrans->reason = "Consumed";
                $resTrans->debited = $ai_credits;
                $resTrans->save();
            }

            $job->update(['ref_id' => $caricatureRes->id, 'status' => 'success']);

        } catch (\Exception $e) {
            $job->update(['error_msg' => $e->getMessage(), 'status' => 'failed']);;
        }
    }

    private function generateCartoonImage(GeminiService $gemini, $usrImageBase64, $mime, $prompt): array|string
    {
        try {
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $mime,
                                'data' => $usrImageBase64,
                            ]
                        ],
                        [
                            'text' => $prompt
                        ]
                    ]
                ]],
            ];

            $config = [
                'responseModalities' => ['IMAGE'],
//                'temperature' => 0.8,
            ];
            $response = $gemini->generateContent('gemini-2.5-flash-image', $payload, $config);

            if (isset($response['error'])) return json_encode($response['error']);

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
            return $e->getMessage();
        }
    }

    private function generateCaricature(GeminiService $gemini, $usrImageBase64, $userImageMime, $attireBase64, $attireMime, $prompt): array|string
    {
        try {
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => "This is the COUPLE PHOTO containing both faces:"
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $userImageMime,
                                'data' => $usrImageBase64,
                            ]
                        ],
                        [
                            'text' => "This is the ATTIRE IMAGE, which is the unchangeable base for the final artwork:"
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $attireMime,
                                'data' => $attireBase64,
                            ]
                        ],
                        [
                            'text' => $prompt
                        ]
                    ]
                ]],
            ];

            $config = [
                'responseModalities' => ['IMAGE'],
//                'temperature' => 0.7,
            ];
            $response = $gemini->generateContent('gemini-2.5-flash-image', $payload, $config);

            if (isset($response['error'])) return json_encode($response['error']);

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
            return $e->getMessage();
        }
    }
}

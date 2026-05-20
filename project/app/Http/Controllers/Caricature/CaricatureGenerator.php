<?php

namespace App\Http\Controllers\Caricature;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Jobs\AICaricatureJob;
use App\Models\AI\AICreditTransaction;
use App\Models\AI\AIJobModel;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\CreatedCaricature;
use App\Models\UserData;
use App\Services\GeminiService;
use Exception;
use Illuminate\Http\Request;
use Storage;

class CaricatureGenerator extends ApiController
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

    public function generate(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::whereUid($this->uid)->first();
        if (!$user) return $this->failed(msg: "Unauthorized");

        $userImage = $request->file('file');
        $id = $request->input('id');
        if (is_null($userImage) || is_null($id)) return $this->failed(msg: "Parameter missing");

        $attire = Attire::whereStringId($id)->first();
        if (!$attire) return $this->failed(msg: "Invalid request");

        $ipData = HelperController::getIpAndCountry($request);
        $isInr = $ipData['cur'] === "INR";

        $cariData = null;

        $pyt = RateController::getCaricatureRates([], $attire->head_count, false, $attire->editor_choice == 1);
        $ai_credits = $pyt['inrVal'];

        $needPurchase = $user->ai_credit < $ai_credits;

        if ($needPurchase) {
            $cariData = CaricaturePurchaseHistory::whereUserId($this->uid)->whereProductId($id)->whereUsed(0)->first();
            if (!$cariData) {

                $data['id'] = $attire->string_id;
                $data['currency'] = $ipData['cur'];
                $data['amount'] = $isInr ? $pyt['inrVal'] : $pyt['usdVal'];
                $data['amountStr'] = $isInr ? $pyt['inrAmount'] : $pyt['usdAmount'];
                $data['src'] = $attire->thumbnail_url;
                $data['title'] = $attire->post_name;
                $data['plan'] = [[
                    'id' => $attire->string_id,
                    'type' => 5,
                    'title' => $attire->post_name
                ]];

                $credits = AICreditController::getCredits($request);

                return $this->successed(datas: [
                    'needToPurchase' => true,
                    'data' => $data,
                    'credits' => $credits,
                    'currency' => $ipData['cur'],
                    'contact_no' => $user->contact_no
                ]);
            }
        }

        $userImageBase64 = base64_encode(file_get_contents($userImage->getRealPath()));
        $userImageMime = $userImage->getMimeType();

        if ($request->input('v', 1) == 2) {
            $userInput = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($userImageMime);
            StorageUtils::put($userInput, base64_decode($userImageBase64));

            $res = new AIJobModel();
            $res->job_id = AIJobModel::generateJobId();
            $res->user_id = $this->uid;
            $res->type = 'bgremover';
            $res->data = json_encode([
                'attire_id' => $attire->string_id,
                'user_input' => $userInput,
            ]);
            $res->ip_data = json_encode($ipData);
            $res->status = 'processing';
            $res->save();

            AICaricatureJob::dispatch($res->id);
            return $this->successed(datas: ['job_id' => $res->job_id]);
        }

        $attireImage = StorageUtils::get($attire->coordinate_image);

        $attireImageBase64 = base64_encode($attireImage);
        $attireImageMime = Storage::mimeType($attire->coordinate_image);

        $gemini = new GeminiService();

        $prompt = str_replace("{{skinColor}}", $attire->skin_color, $this->cartoonPrompt);

        $firstCartoonImage = $this->generateCartoonImage($gemini, $userImageBase64, $userImageMime, $prompt);
        if (is_null($firstCartoonImage)) $this->failed();

        $allPrompts = [
            "Complete prompt from the second image for context use first image do not change second image background, match the skin tones",
            str_replace("{{skinColor}}", $attire->skin_color, $this->caricaturePrompt),
        ];

        if ($this->isTester()) {
            $allPrompts[] = $this->thirdPrompt;
        }

        $caricatures = [];

        foreach ($allPrompts as $cariPrompt) {
            $firstCaricature = $this->generateCaricature($gemini, $firstCartoonImage['base64'], $firstCartoonImage['mimeType'], $attireImageBase64, $attireImageMime, $cariPrompt);
            if (is_null($firstCaricature)) $this->failed();
            $caricatures[] = $firstCaricature;
        }

        $userInput = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($userImageMime);
        StorageUtils::put($userInput, base64_decode($userImageBase64));
        $cartoonImage = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($firstCartoonImage['mimeType']);
        StorageUtils::put($cartoonImage, base64_decode($firstCartoonImage['base64']));

        $images = [];
        $returnImages = [];

        foreach ($caricatures as $caricature) {
            $first = "caricature/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($caricature['mimeType']);
            StorageUtils::put($first, base64_decode($caricature['base64']));
            $images[] = $first;
            $returnImages[] = HelperController::$mediaUrl . $first;
        }

        if (!$this->isTester() && $cariData) {
            $cariData->used = 1;
            $cariData->save();
        }

        if ($cariData == null) $user->decrement('ai_credit', $ai_credits);

        $res = new CreatedCaricature();
        $res->user_id = $this->uid;
        $res->caricature_id = $attire->string_id;
        $res->images = json_encode($images);
        $res->payment_id = $cariData == null ? "$ai_credits credits used" : $cariData->payment_id;
        $res->user_input = $userInput;
        $res->cartoon_image = $cartoonImage;
        $res->show_data = $this->isTester() ? 0 : 1;
        $res->save();

        if ($cariData == null) {
            $resTrans = new AICreditTransaction();
            $resTrans->user_id = $this->uid;
            $resTrans->ref_id = $res->id;
            $resTrans->txn_id = AICreditTransaction::generateTxnId();
            $resTrans->type = 'caricature';
            $resTrans->reason = "Consumed";
            $resTrans->debited = $ai_credits;
            $resTrans->save();
        }


        return $this->successed(datas: ['images' => $returnImages, 'credits_left' => $user->ai_credit]);
    }

    private function generateCartoonImage(GeminiService $gemini, $usrImageBase64, $mime, $prompt): array|null
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
            return null;
        }
    }

    private function generateCaricature(GeminiService $gemini, $usrImageBase64, $userImageMime, $attireBase64, $attireMime, $prompt): array|null
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
            return null;
        }
    }

    function getCaricatures(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->has('page') ? $request->get('page') : 1;
        $version = $request->get('v', 1);

        $limit = HelperController::getPaginationLimit(size: 10);

        $purHistory = array();

        $purDatas = CreatedCaricature::whereUserId($this->uid)->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $collections = $purDatas->getCollection();

        $allCaricatureIds = $collections->pluck('caricature_id')->unique();
        $allPaymentIds = $collections->pluck('payment_id')->unique();

        $attires = Attire::whereIn('string_id', $allCaricatureIds)->get()->keyBy('string_id');
        $payments = CaricaturePurchaseHistory::whereIn('payment_id', $allPaymentIds)->get()->keyBy('payment_id');

        foreach ($purDatas->items() as $row) {

            $attire = $attires->get($row->string_id);
            $payment = $payments->get($row->payment_id);

            $amount = "0";
            if ($payment) {
                $currency_code = "$";
                if ($payment->currency_code === "INR") $currency_code = "₹";
                $amount = $currency_code . $payment->amount;
            }

            $images = [];
            foreach ($row->images as $image) {
                $images[] = [
                    "width" => 1080,
                    "height" => 1920,
                    "src" => $image,
                ];
            }

            $purHistory[] = array(
                'id' => $row->caricature_id,
                'type' => $row->product_type,
                'name' => $attire?->post_name,
                'image' => $version == 1 ? $row->images : $images,
                'transaction_id' => $row->payment_id,
                'amount' => $amount,
                'purchase_date' => $row->created_at->format('d/m/Y H:i:s'),
            );
        }

        $msg = 'Data loaded';
        if (($page == 1 || $page == '1') && sizeof($purHistory) == 0) {
            $msg = 'No History exist.';
        }

        $response['isLastPage'] = $purDatas->currentPage() >= $purDatas->lastPage();
        $response['datas'] = $purHistory;

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, $response));
    }
}

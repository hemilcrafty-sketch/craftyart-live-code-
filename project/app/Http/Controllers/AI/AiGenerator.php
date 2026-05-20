<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\AI\AICreatedHistory;
use App\Models\AI\AICreditTransaction;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricaturePurchaseHistory;
use App\Models\Caricature\CreatedCaricature;
use App\Models\UserData;
use App\Services\GeminiService;
use Exception;
use Illuminate\Http\Request;

class AiGenerator extends ApiController
{
    public static int $ai_credits = 80;

    public string $cartoonPrompt = "Transform the original image into a clean, detailed digital illustration in a modern cartoon or vector art style. The result should look professionally hand-drawn and digitally rendered, with smooth lines, clear outlines, and flat vibrant colors.
        Emphasize sharp detailing on facial features, clothing, and hair. Keep the skin smooth and matte, but add a soft, natural glow on the cheeks.
        Ensure even, soft lighting with no harsh shadows.
        The final image should have a high-resolution, crisp, and polished look suitable for editorial or commercial use.";

    public string $ghibliPrompt = "Convert the uploaded photo into a Studio Ghibli style illustration.

Keep the person’s identity exactly the same.
Do not change the facial structure, eyes, nose, or mouth.
The person must still look exactly like themselves.

Apply the classic Studio Ghibli art style:
• Soft hand-painted watercolor texture
• Smooth clean outlines
• Pastel and natural color palette
• Warm gentle lighting
• Soft shadows, no harsh contrast
• Slightly expressive eyes but NOT oversize

Keep the same pose, clothing, and scene composition.
Do not add new objects or characters.

Make the background feel peaceful and dreamy:
soft sky, subtle nature tones, warm atmosphere.

Final result should feel cozy, magical, and cinematic,
like a frame from a Studio Ghibli movie.
";

    public string $arcanePrompt = "Redraw this photo in Arcane style";

    public function generate(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::whereUid($this->uid)->first();

        $userImage = $request->file('file');
        $type = $request->input('type');
        $prompt = $request->input('prompt');
        if (is_null($userImage) || (is_null($type) && is_null($prompt))) return $this->failed(msg: "Parameter missing");

        if (!empty($prompt)) $type = "generate";
        else if ($type === "ghibli") $prompt = $this->ghibliPrompt;
        else if ($type === "arcane") $prompt = $this->arcanePrompt;
        else if ($type === "cartoon") $prompt = $this->cartoonPrompt;
        else if ($type === "modal") $prompt = $this->cartoonPrompt;
        else return $this->failed(msg: "Invalid param");

        $needPurchase = $user->ai_credit < self::$ai_credits;
        if ($needPurchase) return $this->successed(datas: ['needToPurchase' => true]);

        $gemini = new GeminiService();

        $userImageBase64 = base64_encode(file_get_contents($userImage->getRealPath()));
        $userImageMime = $userImage->getMimeType();

        $firstCartoonImage = $this->generateCartoonImage($gemini, $userImageBase64, $userImageMime, $prompt);
        if (is_null($firstCartoonImage)) $this->failed();

        $userInput = "ai/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($userImageMime);
        StorageUtils::put($userInput, base64_decode($userImageBase64));

        $finalOutput = "ai/generated/$user->fldr_str/" . uniqid() . '.' . HelperController::getExtensionFromMimeType($firstCartoonImage['mimeType']);
        StorageUtils::put($finalOutput, base64_decode($firstCartoonImage['base64']));

        $user->decrement('ai_credit', self::$ai_credits);

        $res = new AICreatedHistory();
        $res->user_id = $this->uid;
        $res->type = $type;
        $res->prompt = $prompt;
        $res->images = json_encode([$finalOutput]);
        $res->credits = self::$ai_credits . " credits used";
        $res->user_input = $userInput;
        $res->show_data = $this->isTester() ? 0 : 1;
        $res->save();

        $resTrans = new AICreditTransaction();
        $resTrans->user_id = $this->uid;
        $resTrans->ref_id = $res->id;
        $resTrans->txn_id = AICreditTransaction::generateTxnId();
        $resTrans->type = $type;
        $resTrans->reason = "Consumed";
        $resTrans->debited = self::$ai_credits;
        $resTrans->save();

        return $this->successed(datas: ['images' => [HelperController::$mediaUrl . $finalOutput], 'credits_left' => $user->ai_credit]);
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

    function getCaricatures(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $page = $request->has('page') ? $request->get('page') : 1;

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

            $purHistory[] = array(
                'id' => $row->caricature_id,
                'type' => $row->product_type,
                'name' => $attire?->post_name,
                'image' => $row->images,
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

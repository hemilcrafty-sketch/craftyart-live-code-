<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Utils\ApiController;
use App\Models\Automation\Config;
use App\Models\Automation\WhatsappTemplate;
use App\Models\Automation\WpFeedbackRequest;
use App\Models\Automation\WpFeedbackResponse;
use App\Models\Revenue\MasterPurchaseHistory;
use App\Models\UserData;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WpFeedbackApiController extends ApiController
{

    /**
     * Scheduler job — called via $schedule->call() in Kernel.
     * Reads config from wp_feedback_automation to determine days and template.
     */
    public function startProcess(): void
    {
        $configRecord = Config::where('name', 'wp_feedback_automation')->first();
        $config = $configRecord ? $configRecord->value : [];

        $daysAfter = (int)($config['days_after_purchase'] ?? 2);
        $wpEnable = (bool)($config['wp']['enable'] ?? false);
        $templateId = $config['wp']['config']['template'] ?? null;

        if (!$wpEnable || !$templateId) {
            return;
        }
        // Resolve campaign_name string from WhatsappTemplate id
        $tplRecord = WhatsappTemplate::find($templateId);
        if (!$tplRecord) {
            return;
        }
        $campaign = $tplRecord->campaign_name;

        $targetDate = now()->subDays($daysAfter)->toDateString();
        $purchases = MasterPurchaseHistory::whereIn('product_type', ['old_sub', 'new_sub'])
            ->where('expired_at', ">", now())
            ->where('is_e_mandate', 0)
            ->whereDate('created_at', $targetDate)
            ->get();

        /** @var MasterPurchaseHistory $purchase */
        foreach ($purchases as $purchase) {

            if (WpFeedbackRequest::where('purchase_id', $purchase->id)->exists()) {
                continue;
            }

            $user = UserData::where('uid', $purchase->user_id)->first();
            if (!$user || empty($purchase->contact_no)) {
                continue;
            }

            $phone = preg_replace('/\D/', '', $purchase->contact_no);

            $newRequest = WpFeedbackRequest::create([
                'emp_id' => $purchase->emp_id,
                'user_id' => $purchase->user_id,
                'contact_no' => $phone,
                'purchase_id' => $purchase->id,
                'status' => 'pending',
                'expires_at' => now()->addDays(30),
                'days_after_purchase' => $daysAfter,
            ]);

            $feedbackUrl = 'feedback/' . $newRequest->string_id;

            try {
                $ctaButtons = [
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => 0,
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => "invitation"
                            ]
                        ],
                    ]
                ];

                $result = WhatsAppService::sendTemplateMessageFromCustomCrm(
                    campaignName: $campaign,
                    userName: $user->name ?? 'User',
                    mobile: $phone,
                    templateParams: [$user->name ?? 'User', $feedbackUrl],
                    ctaButtons: $ctaButtons
                );
                if ($result['success'] == "true" || $result['success']) {
                    $newRequest->update(['sent_at' => now()]);
                } else {
                    $newRequest->delete();
                }
            } catch (\Exception $e) {
                $newRequest->delete();
            }
        }
    }


    /**
     * GET /api/wp-feedback/{string_id}
     *
     * Check feedback status by string_id.
     * - Invalid string_id  → success: false, message: "Invalid feedback url"
     * - Already submitted  → success: true,  message: "Feedback already Submitted"
     * - Not yet submitted  → success: true,  message: "Feedback not submitted", unique_token: "..."
     */
    public function show(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: $validator->errors());
        }

        $stringId = $request->slug;
        $feedbackRequest = WpFeedbackRequest::where('string_id', $stringId)->first();

        if (!$feedbackRequest) {
            return $this->failed(msg: 'Invalid feedback url');
        }

        if ($feedbackRequest->status === 'completed') {
            return $this->successed(msg: 'Feedback already Submitted', datas: ['is_submitted' => true]);
        }

        // Mark expired if past expiry
        if ($feedbackRequest->isExpired()) {
            $feedbackRequest->update(['status' => 'expired']);
            return $this->failed(msg: 'Invalid feedback url');
        }

        return $this->successed(msg: 'Feedback not submitted', datas: ['is_submitted' => false]);
    }

    /**
     * POST /api/wp-feedback/submit
     *
     * Submit feedback using string_id. All fields required.
     * Body: { string_id, rating, feedback_text, suggestions }
     */
    public function submit(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'feedback_text' => 'required|string|max:1000',
            'suggestions' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: $validator->errors());
        }

        $feedbackRequest = WpFeedbackRequest::where('string_id', $request->slug)->first();

        if (!$feedbackRequest || !$feedbackRequest->isValid()) {
            return $this->failed(msg: 'Invalid feedback url');
        }

        $connection = $feedbackRequest->getConnection();
        $connection->beginTransaction();
        try {
            WpFeedbackResponse::create([
                'wp_feedback_request_id' => $feedbackRequest->id,
                'user_id' => $feedbackRequest->user_id,
                'purchase_id' => $feedbackRequest->purchase_id,
                'rating' => $request->rating,
                'feedback_text' => $request->feedback_text,
                'suggestions' => $request->suggestions,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'submitted_at' => now(),
            ]);

            $feedbackRequest->markCompleted();

            $connection->commit();

            return $this->successed(msg: 'Thank you for your feedback!');
        } catch (\Exception $e) {
            $connection->rollBack();
            return $this->failed(msg: 'An error occurred. Please try again.');
        }
    }
}

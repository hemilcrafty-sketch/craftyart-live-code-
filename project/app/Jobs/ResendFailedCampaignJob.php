<?php

namespace App\Jobs;

use App\Enums\AutomationType;
use App\Http\Controllers\Automation\EmailTemplateController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\CampaignFailedDetail;
use App\Models\Automation\CampaignSendLog;
use App\Models\UserData;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Services\WhatsAppService;

class ResendFailedCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $log_id;
    protected string $resend_type; // 'all', 'email', 'whatsapp'

    public function __construct(int $log_id, string $resend_type = 'all')
    {
        $this->log_id = $log_id;
        $this->resend_type = $resend_type;
    }

    public function handle(): void
    {
        $log = CampaignSendLog::with(['emailTemplate', 'whatsappTemplate', 'plan', 'promoCode'])
            ->find($this->log_id);

        if (!$log) {
            Log::error("Log ID {$this->log_id} not found.");
            return;
        }

        $query = CampaignFailedDetail::where('log_id', $this->log_id)
            ->where('status', 'failed');

        // Filter based on resend type
        if ($this->resend_type === 'email') {
            $query->where('type', AutomationType::EMAIL);
        } elseif ($this->resend_type === 'whatsapp') {
            $query->where('type', AutomationType::WHATSAPP);
        }

        $failedLogs = $query->get();

        $emailSuccessCount = 0;
        $whatsappSuccessCount = 0;

        foreach ($failedLogs as $failed) {
            $user = UserData::with('latestTransactionLog')->where('id', $failed->user_id)->first();
            if (!$user) {
                continue;
            }

            // Process based on failure type and resend type
            switch ($failed->type) {
                case AutomationType::EMAIL->value:
                    if ($this->resend_type === 'all' || $this->resend_type === 'email') {
                        $result = $this->resendEmail($user, $log);
                        if ($result['status']) {
                            $emailSuccessCount++;
                            $failed->delete();
                        }
                    }
                    break;

                case AutomationType::WHATSAPP->value:
                    if ($this->resend_type === 'all' || $this->resend_type === 'whatsapp') {
                        $result = $this->resendWhatsApp($user, $log);
                        if ($result['status']) {
                            $whatsappSuccessCount++;
                            $failed->delete();
                        } else {
                            $failed->update(['error_message' => $result['message']]);
                        }
                    }
                    break;
            }
        }

        // Update log counts
        if ($emailSuccessCount > 0) {
            $log->decrement('email_failed', $emailSuccessCount);
            $log->increment('email_sent', $emailSuccessCount);
        }

        if ($whatsappSuccessCount > 0) {
            $log->decrement('wp_failed', $whatsappSuccessCount);
            $log->increment('wp_sent', $whatsappSuccessCount);
        }

        Log::info("Resend job completed ({$this->resend_type}): {$emailSuccessCount} emails, {$whatsappSuccessCount} WhatsApp messages resent.");
    }

    private function resendEmail(UserData $user, CampaignSendLog $log): array
    {
        if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => false, "message" => "Email is not valid"];
        }

        // Check unsubscribe preference
        if (!$this->isUserSubscribed($user)) {
            return ["status" => false, "message" => "User Unsubscribe"];
        }

        try {
            $emailTemplate = $log->emailTemplate;
            if (!$emailTemplate) {
                Log::error("Email template not found for log #{$log->id}");
                return ["status" => false, "message" => "Email template not found"];
            }

            $response = $this->buildUserResponse($user, $log);
            $htmlBody = View::make($emailTemplate->email_template, [
                'data' => $response,
            ])->render();

            $result = EmailTemplateController::sendEmail($user->email, $log->subject, $htmlBody);

            if (str_contains($result, "successfully")) {
                Log::info("Resent email successfully to {$user->email}");
                return ["status" => true, "message" => "Resent email successfully"];
            } else {
                Log::error("Failed to resend email to {$user->email}: {$result}");
                return ["status" => false, "message" => $result];
            }

        } catch (\Throwable $e) {
            Log::error("Error resending email to {$user->email}: {$e->getMessage()}");
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    private function resendWhatsApp(UserData $user, CampaignSendLog $log): array
    {
        $contactNo = $user->latestTransactionLog && !empty($user->latestTransactionLog->contact_no) ? $user->latestTransactionLog->contact_no : (!empty($user->contact_no)
            ? $user->contact_no : "");

        if (empty($contactNo)) {
            return ["status" => false, "message" => "Contact Number Not Found"];
        }

        try {
            $whatsappTemplate = $log->whatsappTemplate;
            if (!$whatsappTemplate) {
                Log::error("WhatsApp template not found for log #{$log->id}");
                return ["status" => false, "message" => "WhatsApp template not found"];
            }

            $response = $this->buildUserResponse($user, $log);
            $templateParams = $this->resolveWhatsappTemplateParams(
                keys: $whatsappTemplate->template_params ?? [],
                response: $response,
            );

            if ($whatsappTemplate->template_params_count !== count($templateParams)) {
                Log::error("Template Parameter Count Mismatch #{$log->id}");
                return ["status" => false, "message" => "Template Parameter Count Mismatch"];
            }

            // Uncomment for actual WhatsApp sending
            $wpResponse = WhatsAppService::sendTemplateMessageFromCustomCrm(
                campaignName: $whatsappTemplate->campaign_name,
                userName: $user->name,
                mobile: $contactNo,
                templateParams: $templateParams,
                media: $whatsappTemplate->media_url == 1,
                mediaUrl: $whatsappTemplate->url ?? ""
            );
            $result = $this->handleWhatsAppResponse($wpResponse);

            //            Log::info("Resend WhatsApp - Campaign: {$whatsappTemplate->campaign_name}, User: {$user->name}, Mobile: {$contactNo}");
//
//            // Simulate success for demo
//            $result = ["success" => true, "message" => "Generated"];

            if ($result['success']) {
                Log::info("Resent WhatsApp successfully to {$contactNo}");
                return ["status" => true, "message" => "WhatsApp template not found"];
            } else {
                return ["status" => false, "message" => $result['message'] ?? 'Unknown error'];
            }

        } catch (Exception $e) {
            Log::error("Error resending WhatsApp to {$user->contact_no}: {$e->getMessage()}");
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Build unified response data for both email and WhatsApp
     */
    private function buildUserResponse(UserData $user, CampaignSendLog $log): array
    {
        $response = [
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];

        // Add subscription data
        if ($log->plan) {
            $transaction = $user->transactionLogs()->latest('id')->first();
            $response['type'] = "plan";
            $response['plan'] = $log->plan;
            $response['planType'] = 'old_sub';

            $currency = $transaction->currency_code ?? null;
            $response['data'] = AutomationUtils::formatOldPlanData(
                plan: $log->plan,
                currency: $currency
            );
        }

        // Add promo code data
        if ($log->promoCode) {
            $rawPrice = 0;
            if (!empty($response['data']['price'])) {
                $rawPrice = (float) preg_replace('/[^0-9.]/', '', $response['data']['price']);
            }

            $discountPrice = null;
            if ($rawPrice > 0) {
                $discountedValue = $rawPrice - ($rawPrice * $log->promoCode->disc / 100);
                $currency = $response['data']['currency'] ?? 'INR';
                $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float) $discountedValue, 2, '.', '');
                $currencySymbol = $response['data']['currency_symbol'] ?? '';
                $discountPrice = $currencySymbol . $discountedValue;
            }

            $response['promo'] = [
                'code' => $log->promoCode->promo_code,
                'disc' => (string) $log->promoCode->disc,
                'expiry_date' => $log->promoCode->expiry_date ? Carbon::parse($log->promoCode->expiry_date)->format('j F Y') : null,
                'discount_price' => (string) $discountPrice,
            ];
        }

        return $response;
    }

    /**
     * Check if user is subscribed to emails
     */
    private function isUserSubscribed(UserData $user): bool
    {
        $subRaw = $user->email_preferance;
        if (empty($subRaw)) {
            return true;
        }

        $sub = json_decode($subRaw, true);
        return !(is_array($sub) && isset($sub['offer']) && (int) $sub['offer'] === 0);
    }

    private function resolveWhatsappTemplateParams(array $keys, array $response): array
    {
        $resolved = [];
        foreach ($keys as $key) {
            if (!str_contains($key, '.')) {
                $resolved[] = match ($key) {
                    'link' => "https://www.craftyartapp.com/plans",
                    default => ''
                };
                continue;
            }

            [$group, $field] = explode('.', $key);
            $resolved[] = match ($group) {
                'UserData' => $response['userData'][$field] ?? '',
                'PlanData' => $response['data'][$field] ?? '',
                'PromoData' => $response['promo'][$field] ?? '',
                default => ''
            };
        }
        return $resolved;
    }

    private function handleWhatsAppResponse($result): array
    {
        if (is_array($result)) {
            $status = $result['success'] ?? false;
            $message = $result['message'] ?? 'Something went wrong';
        } else {
            $status = $result->success ?? false;
            $message = $status ? 'Message Sent Successfully' : ($result->message ?? 'Something went wrong');
        }
        return [
            'success' => $status,
            'message' => $message,
        ];
    }
}
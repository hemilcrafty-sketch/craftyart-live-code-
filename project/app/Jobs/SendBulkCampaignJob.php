<?php
namespace App\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\Automation\EmailTemplateController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\CampaignFailedDetail;
use App\Models\Automation\CampaignSendLog;
use App\Models\PromoCode;
use App\Models\TransactionLog;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Services\WhatsAppService;

class SendBulkCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 5999;

    protected int $logId;

    public function __construct(int $logId)
    {
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = CampaignSendLog::with(['emailTemplate', 'whatsappTemplate', 'plan', 'promoCode'])
            ->whereId($this->logId)
            ->first();

        if (!$log || $log->stopped) {
            return;
        }
        if (in_array($log->status, ['paused', 'completed', 'failed', 'error'])) {
            return;
        }
        $log->update(['status' => 'processing']);

        try {
            // 🔹 Pre-fetch common resources using eager loaded relationships
            $commonData = $this->prefetchCommonData($log);

            // If any required template is missing, stop execution
            if ($log->hasEmailTemplate() && !$commonData['emailTemplate']) {
                $log->update(['status' => 'error', 'error_message' => 'Email Template Not Found']);
                return;
            }
            if ($log->hasWhatsappTemplate() && !$commonData['whatsappTemplate']) {
                $log->update(['status' => 'error', 'error_message' => 'Whatsapp Template Not Found']);
                return;
            }

            $baseQuery = UserData::select('id', 'email', 'uid', 'name', 'email_preferance', 'contact_no')->with('latestTransactionLog');

            // Apply last processed user filter for all types
            if (!empty($log->last_processed_user_id)) {
                $baseQuery->where('id', '>', $log->last_processed_user_id);
            }

            // 🔹 TYPE 2 → Premium Users
            if ($log->select_users_type == '2') {
                $baseQuery->where('is_Premium', 1);
            }
            // 🔹 TYPE 3 → Custom Users
            elseif ($log->select_users_type == '3' && !empty($log->user_ids)) {
                $baseQuery->whereIn('uid', $log->user_ids);
            } elseif ($log->select_users_type == '4') {
                $this->processExpiredUsers($log, $baseQuery, $commonData);
                return;
            }
            // 🔹 TYPE 5 → Active Users
            elseif ($log->select_users_type == '5') {
                $this->processActiveUsers($log, $baseQuery, $commonData);
                return;
            }

            // 🔹 TYPE 6 → Failed Orders Users
            elseif ($log->select_users_type == '6') {
                $this->processFailedOrderUsers($log, $baseQuery, $commonData);
                return;
            }

            // 🔹 Default chunking for type 2 and 3
            if (empty($log->total) || $log->total == 0) {
                $count = (clone $baseQuery)->count();
                $log->update(['total' => $count]);
            }

            $baseQuery->chunk(500, function ($users) use ($log, $commonData) {
                self::processUsersChunk(users: $users, log: $log, commonData: $commonData);
            });

            self::finalizeLogStatus($log);

        } catch (\Exception $e) {
            Log::error("Error in bulk job for log #{$log->id}: " . $e->getMessage());
            $log->update(['status' => 'error', 'error_message' => $e->getMessage()]);
        }
    }

    /**
     * Process expired users (Type 4)
     */
    private function processExpiredUsers(CampaignSendLog $log, $baseQuery, array $commonData): void
    {
        $expiredQuery = TransactionLog::query()
            ->joinSub(
                TransactionLog::selectRaw('user_id, MAX(id) as latest_id')
                    ->groupBy('user_id'),
                'latest',
                fn($join) => $join->on('latest.latest_id', '=', 'transaction_logs.id')
            )
            ->where('transaction_logs.expired_at', '<', now())
            ->select([
                'transaction_logs.user_id',
                'transaction_logs.id as id',
                'transaction_logs.expired_at',
            ])
            ->orderBy('transaction_logs.id');

        if (empty($log->total) || $log->total == 0) {
            $count = (clone $expiredQuery)
                ->distinct('transaction_logs.user_id')
                ->count('transaction_logs.user_id');
            $log->update(['total' => $count]);
        }

        $expiredQuery->chunk(100, function ($logs) use ($baseQuery, $log, $commonData) {
            $expiredUserIds = $logs->pluck('user_id')->toArray();
            $query = (clone $baseQuery)->whereIn('uid', $expiredUserIds);

            if (!empty($log->last_processed_user_id)) {
                $query->where('id', '>', $log->last_processed_user_id);
            }

            $query->chunk(100, function ($users) use ($log, $commonData) {
                self::processUsersChunk(users: $users, log: $log, commonData: $commonData);
            });
        });
    }
    /**
     * Process active users (Type 5)
     */
    private function processActiveUsers(CampaignSendLog $log, $baseQuery, array $commonData): void
    {
        $activeQuery = TransactionLog::where('expired_at', '>=', now())
            ->where('yearly', 0);

        // ✅ Clone query for total count (unique users)
        if (empty($log->total) || $log->total == 0) {
            $count = (clone $activeQuery)
                ->distinct('user_id')
                ->count('user_id');

            $log->update(['total' => $count]);
        }

        // ✅ Process chunks
        $activeQuery->chunk(1000, function ($transactionLogs) use ($log, $baseQuery, $commonData) {
            $userIds = $transactionLogs->pluck('user_id')->unique()->toArray();

            $query = (clone $baseQuery)->whereIn('uid', $userIds);

            if (!empty($log->last_processed_user_id)) {
                $query->where('id', '>', $log->last_processed_user_id);
            }

            $query->chunk(500, function ($users) use ($log, $commonData) {
                self::processUsersChunk(users: $users, log: $log, commonData: $commonData);
            });
        });

        self::finalizeLogStatus($log);
    }

    /**
     * Process failed order users (Type 6)
     */
    private function processFailedOrderUsers(CampaignSendLog $log, $baseQuery, array $commonData): void
    {
        // Get unique user IDs from failed orders
        $userIdsQuery = \App\Models\Order::query()
            ->where('status', 'failed')
            ->whereIn('type', ['old_sub', 'new_sub', 'offer'])
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('latestTransactionLog', function ($subQuery) {
                    $subQuery->where('expired_at', '>=', now());
                });
            })
            ->distinct()
            ->pluck('user_id');

        // Count unique users in UserData table
        if (empty($log->total) || $log->total == 0) {
            $count = (clone $baseQuery)
                ->whereIn('uid', $userIdsQuery)
                ->count();
            $log->update(['total' => $count]);
        }

        // Process users in chunks
        $query = (clone $baseQuery)->whereIn('uid', $userIdsQuery);

        if (!empty($log->last_processed_user_id)) {
            $query->where('id', '>', $log->last_processed_user_id);
        }

        $query->chunk(500, function ($users) use ($log, $commonData) {
            self::processUsersChunk(users: $users, log: $log, commonData: $commonData);
        });

        self::finalizeLogStatus($log);
    }

    /**
     * Pre-fetch all common data using eager loaded relationships
     */
    private function prefetchCommonData(CampaignSendLog $log): array
    {
        return [
            'emailTemplate' => $log->emailTemplate,
            'whatsappTemplate' => $log->whatsappTemplate,
            'whatsappTemplateKeys' => $log->whatsappTemplate?->template_params ?? [],
            'subscription' => $log->plan,
            'promoCode' => $log->promoCode,
        ];
    }

    /**
     * Process a batch of users and send emails
     */
    private function processUsersChunk(Collection $users, CampaignSendLog $log, array $commonData)
    {
        foreach ($users as $user) {
            $log->refresh();

            // Manual pause or stop
            if ($log->status === 'paused' || $log->stopped) {
                return false;
            }

            if ($log->hasEmailTemplate()) {
                self::sendEmailToUser(user: $user, log: $log, commonData: $commonData);
            }

            if ($log->hasWhatsappTemplate()) {
                self::sendWpToUser(user: $user, log: $log, commonData: $commonData);
            }

            $log->increment('total_processed');
            $log->last_processed_user_id = $user->id;
            $log->save();

            $log->refresh();

            // Auto pause based on total attempts
            $totalSinceLastPause = ($log->total_processed) - ($log->sent_since_last_pause ?? 0);
            if ($log->auto_pause_count != -1 && $totalSinceLastPause >= $log->auto_pause_count) {
                $log->update([
                    'status' => 'paused',
                    'pause_type' => 'auto',
                    'sent_since_last_pause' => $log->total_processed,
                ]);
                return false;
            }
        }
    }

    private function sendWpToUser(UserData $user, CampaignSendLog $log, array $commonData): void
    {

        $contactNo = $user->latestTransactionLog && !empty($user->latestTransactionLog->contact_no) ? $user->latestTransactionLog->contact_no : (!empty($user->contact_no)
            ? $user->contact_no : "");

        if (empty($contactNo)) {
            self::recordFailed(log: $log, user: $user, message: 'Contact Number Not Found.', type: AutomationType::WHATSAPP);
            return;
        }

        try {
            $whatsappTemplate = $commonData['whatsappTemplate'];
            $response = $this->buildUserResponse($user, $commonData);

            $templateParams = $this->resolveWhatsappTemplateParams(
                keys: $commonData['whatsappTemplateKeys'],
                response: $response,
            );

            if ((int) $whatsappTemplate->template_params_count !== count($templateParams)) {
                self::recordFailed(log: $log, user: $user, message: 'Template Parameter Count Mismatch', type: AutomationType::WHATSAPP);
                return;
            }

            $wpResponse = WhatsAppService::sendTemplateMessageFromCustomCrm(
                campaignName: $whatsappTemplate->campaign_name,
                userName: $user->name,
                mobile: $contactNo,
                templateParams: $templateParams,
                media: $whatsappTemplate->media_url == 1,
                mediaUrl: $whatsappTemplate->url ?? ""
            );
            $result = self::handleWhatsAppResponse($wpResponse);

            Log::info("Campaign Name " . $whatsappTemplate->campaign_name . " Name " . $user->name . " mobile " . $contactNo . " TemplateParams " . json_encode($templateParams) . "mediaUrl " . $whatsappTemplate->url);


            if ($result['success']) {
                $log->increment('wp_sent');
            } else {
                self::recordFailed(log: $log, user: $user, message: $result, type: AutomationType::WHATSAPP);
            }
        } catch (\Exception $e) {
            Log::error("Exception sending Whatsapp to {$user->uid}: " . $e->getMessage());
            self::recordFailed(log: $log, user: $user, message: $e->getMessage(), type: AutomationType::WHATSAPP);
        }
    }

    private function sendEmailToUser(UserData $user, CampaignSendLog $log, array $commonData): void
    {
        $email = $user->email;
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::recordFailed(log: $log, user: $user, message: 'Invalid or missing email address', type: AutomationType::EMAIL);
            return;
        }

        // Check unsubscribe
        if (!$this->isUserSubscribed($user)) {
            self::recordFailed(log: $log, user: $user, message: 'Unsubscribe user', type: AutomationType::EMAIL);
            return;
        }

        try {
            $emailTemplate = $commonData['emailTemplate'];
            $response = $this->buildUserResponse($user, $commonData);

            $htmlBody = View::make($emailTemplate->email_template, [
                'data' => $response,
            ])->render();

            $result = EmailTemplateController::sendEmail($email, $log->subject, $htmlBody);
            Log::info("Send Email Result " . json_encode($result));

            if (str_contains($result, "successfully")) {
                $log->increment('email_sent');
            } else {
                self::recordFailed(log: $log, user: $user, message: $result, type: AutomationType::EMAIL);
            }
        } catch (\Exception $e) {
            Log::error("Exception sending email to {$user->uid}: " . $e->getMessage());
            self::recordFailed(log: $log, user: $user, message: $e->getMessage(), type: AutomationType::EMAIL);
        }
    }

    /**
     * Build unified response data for both email and WhatsApp
     */
    private function buildUserResponse(UserData $user, array $commonData): array
    {
        $response = [
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];

        // Add subscription data
        if ($commonData['subscription']) {
            $transaction = $user->transactionLogs()->latest('id')->first();
            $response['type'] = "plan";
            $response['plan'] = $commonData['subscription'];
            $response['planType'] = 'old_sub';

            // Fix: Provide a default currency if null
            $currency = $transaction->currency_code ?? 'INR'; // Default to 'INR' if null
            $response['data'] = AutomationUtils::formatOldPlanData(
                plan: $commonData['subscription'],
                currency: $currency
            );
        }

        // Add promo code data
        if ($commonData['promoCode']) {
            $response['promo'] = $this->calculatePromoData($response, $commonData['promoCode']);
        }

        return $response;
    }

    /**
     * Calculate promo code discount data
     */
    private function calculatePromoData(array $response, PromoCode $promoCode): array
    {
        $rawPrice = 0;
        if (!empty($response['data']['price'])) {
            $rawPrice = (float) preg_replace('/[^0-9.]/', '', $response['data']['price']);
        }

        $discountPrice = null;
        if ($rawPrice > 0) {
            $discountedValue = $rawPrice - ($rawPrice * $promoCode->disc / 100);
            $currency = $response['data']['currency'] ?? 'INR';
            $discountedValue = $currency == 'INR' ? round($discountedValue) : number_format((float) $discountedValue, 2, '.', '');
            $currencySymbol = $response['data']['currency_symbol'] ?? '';
            $discountPrice = $currencySymbol . $discountedValue;
        }

        return [
            'code' => $promoCode->promo_code,
            'disc' => (string) $promoCode->disc,
            'expiry_date' => $promoCode->expiry_date ? Carbon::parse($promoCode->expiry_date)->format('j F Y') : null,
            'discount_price' => (string) $discountPrice,
        ];
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

    public static function handleWhatsAppResponse($result, $templateParams = []): array
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

    /**
     * Record failed email
     */
    private function recordFailed(CampaignSendLog $log, UserData $user, $message, $type): void
    {
        $campaignSendDetails = new CampaignFailedDetail();

        if ($type == AutomationType::EMAIL) {
            $campaignSendDetails->email = $user->email;
            $campaignSendDetails->type = AutomationType::EMAIL;
            $log->increment('email_failed');
        } elseif ($type == AutomationType::WHATSAPP) {
            $campaignSendDetails->contact_no = $user->contact_no;
            $campaignSendDetails->type = AutomationType::WHATSAPP;
            $log->increment('wp_failed');
        }
        $campaignSendDetails->log_id = $log->id;
        $campaignSendDetails->user_id = $user->id;
        $campaignSendDetails->send_type = ConfigType::CAMPAIGN;
        $campaignSendDetails->status = 'failed';
        $campaignSendDetails->error_message = $message;
        $campaignSendDetails->save();
    }

    /**
     * Finalize job status
     */
    private function finalizeLogStatus(CampaignSendLog $log): void
    {
        $log->refresh();
        if ($log->status !== 'paused' && !$log->stopped) {
            $log->update(['status' => 'completed']);
        }
    }
}
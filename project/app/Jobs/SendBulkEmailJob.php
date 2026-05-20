<?php
namespace App\Jobs;

use App\Enums\ConfigType;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\MailSendDetail;
use App\Models\Automation\MailSendLog;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class SendBulkEmailJob implements ShouldQueue
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
        $log = MailSendLog::find($this->logId);

        if (!$log || $log->stopped) {
            Log::info("BulkEmailJob: Log not found or stopped.");
            return;
        }

        if (in_array($log->status, ['paused', 'completed', 'failed'])) {
            Log::info("BulkEmailJob: Job {$log->id} is {$log->status}, skipping...");
            return;
        }

        $log->update(['status' => 'processing']);

        $baseQuery = UserData::select('id', 'email', 'uid', 'name', 'email_preferance')
            ->whereNotNull('email');

        // Apply last processed user filter for all types
        if (!empty($log->last_processed_user_id)) {
            $baseQuery->where('id', '>', $log->last_processed_user_id);
        }

        // 🔹 TYPE 2 → Premium Users
        if ($log->select_users_type == '2') {
            $baseQuery->where('is_Premium', 1);
        } // 🔹 TYPE 3 → Custom Users
        elseif ($log->select_users_type == '3' && !empty($log->user_ids)) {
            $baseQuery->whereIn('uid', $log->user_ids);
        }
        elseif ($log->select_users_type == '4') {
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
                Log::info("Updated total user count for log {$log->id} (Type 4): {$count}");
            }

            $expiredQuery->chunk(100, function ($logs) use ($baseQuery, $log) {
                $expiredUserIds = $logs->pluck('user_id')->toArray();
                $query = (clone $baseQuery)->whereIn('uid', $expiredUserIds);

                if (!empty($log->last_processed_user_id)) {
                    $query->where('id', '>', $log->last_processed_user_id);
                }

                $query->chunk(100, function ($users) use ($log) {
                    $this->processUsersChunk($users, $log);
                });
            });

            $this->finalizeLogStatus($log);
            return;
        }
        // 🔹 TYPE 5 → Active Users
        elseif ($log->select_users_type == '5') {
            $activeQuery = TransactionLog::where('expired_at', '>=', now())
                ->where('yearly', 0);

            // ✅ Clone query for total count (unique users)
            if (empty($log->total) || $log->total == 0) {
                $count = (clone $activeQuery)
                    ->distinct('user_id')
                    ->count('user_id');

                $log->update(['total' => $count]);
                Log::info("Updated total active users for log {$log->id} (Type 5): {$count}");
            }

            // ✅ Process chunks
            $activeQuery->chunk(1000, function ($transactionLogs) use ($log, $baseQuery) {
                $userIds = $transactionLogs->pluck('user_id')->unique()->toArray();

                $query = (clone $baseQuery)->whereIn('uid', $userIds);

                if (!empty($log->last_processed_user_id)) {
                    $query->where('id', '>', $log->last_processed_user_id);
                }

                $query->chunk(500, function ($users) use ($log) {
                    $this->processUsersChunk($users, $log);
                });
            });

            $this->finalizeLogStatus($log);
            return;
        }

        // 🔹 Default chunking for type 2 and 3
        try {
            if (empty($log->total) || $log->total == 0) {
                $count = (clone $baseQuery)->count();
                $log->update(['total' => $count]);
                Log::info("Updated total user count for log {$log->id}: {$count}");
            }

            $baseQuery->chunk(500, function ($users) use ($log) {
                $this->processUsersChunk($users, $log);
            });

            $this->finalizeLogStatus($log);
        } catch (\Exception $e) {
            Log::error("Error in bulk job for log #{$log->id}: " . $e->getMessage());
            $log->update(['status' => 'failed']);
        }
    }

    /**
     * Process a batch of users and send emails
     */
    private function processUsersChunk($users, $log)
    {
        foreach ($users as $user) {
            $log->refresh();

            // Manual pause or stop
            if ($log->status === 'paused') {
                Log::info("BulkEmailJob: Manually paused at user {$user->id}");
                return false;
            }

            if ($log->stopped) {
                Log::info("BulkEmailJob: Stopped at user {$user->id}");
                return false;
            }

            $email = $user->email;
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->recordFailed($log, $user, $email, 'Invalid or missing email address');
                continue;
            }

            // Check unsubscribe
            $subRaw = $user->email_preferance;
            if (!empty($subRaw)) {
                $sub = json_decode($subRaw, true);
                if (is_array($sub) && isset($sub['offer']) && (int)$sub['offer'] === 0) {
                    $this->recordFailed($log, $user, $email, 'Unsubscribe user');
                    continue;
                }
            }

            try {
                $emailTemplate = EmailTemplate::find($log->email_template_id);
                $response['userData'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                if(!empty($log->plan_id) && $log->plan_id != 0){
                    $plan = Subscription::find($log->plan_id);
                    $transaction = $user->transactionLogs()->latest('id')->first();
                    if($plan && $transaction){
                        $response['type'] = "plan";
                        $response['data'] = AutomationUtils::formatOldPlanData(plan: $plan, currency: $transaction->currency_code);
                        $response['plan'] = $plan;
                        $response['planType'] = 'old_sub';
                    }
                }

                if (!empty($log->promo_code) && $log->promo_code != 0) {
                    $PromoCode = PromoCode::find($log->promo_code);
                    if ($PromoCode) {

                        // Extract the numeric value from price (remove ₹ or $)
                        $rawPrice = 0;
                        if (!empty($response['data']['price'])) {
                            // Remove any non-numeric characters like ₹ or $
                            $rawPrice = preg_replace('/[^0-9.]/', '', $response['data']['price']);
                            $rawPrice = (float)$rawPrice;
                        }

                        // Calculate discount amount
                        $discountPrice = null;
                        if ($rawPrice > 0) {
                            $discountedValue = $rawPrice - ($rawPrice * $PromoCode->disc / 100);
                            $discountedValue = $response['data']['currency'] == 'INR' ? round($discountedValue) :number_format((float)$discountedValue, 2, '.', '');
                            $currencySymbol = $response['data']['currency_symbol'];
                            $discountPrice = $currencySymbol . $discountedValue;
                        }

                        // Set promo response
                        $response['promo'] = [
                            'code' => $PromoCode->promo_code,
                            'disc' => $PromoCode->disc,
                            'expiry_date' => $PromoCode->expiry_date == null ? null : Carbon::parse($PromoCode->expiry_date)->format('j F Y'),
                            'discount_price' => $discountPrice,
                        ];
                    }
                }

                $htmlBody = View::make($emailTemplate->email_template, [
                    'data' => $response,
                ])->render();

                $result = EmailTemplateController::sendEmail($email, $log->subject, $htmlBody, 'text');

                if (str_contains($result, "successfully")) {
                    $log->increment('sent');
                } else {
                    $this->recordFailed($log, $user, $email, $result);
                }
            } catch (\Exception $e) {
                Log::error("Exception sending email to {$email}: " . $e->getMessage());
                $this->recordFailed($log, $user, $email, $e->getMessage());
            }

            $log->last_processed_user_id = $user->id;
            $log->save();

            // Auto pause based on total attempts
            $totalSinceLastPause = ($log->sent + $log->failed) - ($log->emails_sent_since_last_pause ?? 0);
            if ($totalSinceLastPause >= $log->auto_pause_count) {
                $log->update([
                    'status' => 'paused',
                    'pause_type' => 'auto',
                    'emails_sent_since_last_pause' => $log->sent + $log->failed,
                ]);
                Log::info("BulkEmailJob: Auto-paused after {$totalSinceLastPause} total attempts.");
                return false;
            }
        }
    }

    /**
     * Record failed email
     */
    private function recordFailed($log, $user, $email, $message)
    {
        $log->increment('failed');
        MailSendDetail::create([
            'log_id' => $log->id,
            'user_id' => $user->id,
            'send_type' => ConfigType::CAMPAIGN,
            'email' => $email ?? 'invalid',
            'status' => 'failed',
            'error_message' => $message,
        ]);
    }

    /**
     * Finalize job status
     */
    private function finalizeLogStatus($log)
    {
        $log->refresh();
        if ($log->status !== 'paused' && !$log->stopped) {
            $log->update(['status' => 'completed']);
        }
    }
}
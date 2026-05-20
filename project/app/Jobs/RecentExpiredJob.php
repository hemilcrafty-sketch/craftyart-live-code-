<?php

namespace App\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\Config;
use App\Models\Automation\AutomationSendDetail;
use App\Models\Automation\AutomationSendLog;
use App\Models\TransactionLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecentExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("🚀 Recent Expire Drop Job Started");

        // Fetch configuration
        $config = Config::where('name', ConfigType::RECENT_EXPIRE_AUTOMATION->label())->first();
        if (!$config) {
            Log::warning("❌ Recent Expire: Config not found!");
            return;
        }

        $configData = $config->value;
        if (!is_array($configData) || empty($configData)) {
            Log::warning("❌ Recent Expire: Invalid config data!");
            return;
        }

        Log::info("📊 Processing " . count($configData) . " frequency configurations");

        $now = Carbon::now();

        $allTemplateData = AutomationUtils::preFetchAllTemplatesAndPromoCodes($configData, ConfigType::RECENT_EXPIRE_AUTOMATION->value);

        Log::info("🎯 Starting to process frequencies");
        $activeUserIds = TransactionLog::where('expired_at', '>', now())
            ->where('status', 1)
            ->pluck('user_id')
            ->toArray();
        foreach ($configData as $frequencyIndex => $frequency) {
            $days = (int)($frequency['day'] ?? 0);

            if ($days === 0) {
                Log::info("⏭️ Skipping day 0 configuration");
                continue;
            }

            Log::info("🔁 Processing frequency #{$frequencyIndex} for day: {$days}");

            $this->processFrequency($frequency, $days, $now, $allTemplateData,$activeUserIds);
        }

        Log::info("🎉 Checkout Drop Job Finished Successfully");
    }

    private function processFrequency($frequency, int $days, Carbon $now, array $allTemplateData,$activeUserIds): void
    {
        $targetDate = $now->copy()->subDays($days)->toDateString();
        Log::info("📅 Target date for {$days} days ago: {$targetDate}");

        // Skip processing if both channels are disabled
        $emailEnabled = $frequency['email']['enable'] ?? false;
        $whatsappEnabled = $frequency['wp']['enable'] ?? false;

        if (!$emailEnabled && !$whatsappEnabled) {
            Log::info("⏭️ Both email and WhatsApp disabled for day {$days}, skipping processing");
            return;
        }

        // Create automation logs only for enabled channels
        $emailLog = null;
        $whatsappLog = null;
        $campaignName = "Recent Expire - Day {$days} - {$targetDate}";

        if ($emailEnabled) {
            $emailLog = $this->createAutomationLog($campaignName, [], AutomationType::EMAIL);
            Log::info("📧 Email Automation Log created: #{$emailLog->id}");
        }

        if ($whatsappEnabled) {
            $whatsappLog = $this->createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
            Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
        }

        // Process users in chunks without loading all data at once
        $this->processUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData,$activeUserIds);

        // Mark logs as completed only if they exist
        if ($emailLog) {
            $emailLog->update(['status' => 'completed']);
            Log::info("✅ Email Log #{$emailLog->id} completed - Sent: {$emailLog->sent}, Failed: {$emailLog->failed}");
        }

        if ($whatsappLog) {
            $whatsappLog->update(['status' => 'completed']);
            Log::info("✅ WhatsApp Log #{$whatsappLog->id} completed - Sent: {$whatsappLog->sent}, Failed: {$whatsappLog->failed}");
        }
    }

    private function processUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData,$activeUserIds): void
    {
        $chunkSize = 100;
        $totalProcessed = 0;
        $chunkIndex = 0;

        Log::info("👥 Starting chunked user processing for day {$days}");

        // Use chunking with eager loading - relationships will handle the data
        TransactionLog::with(['userData', 'subscription', 'subPlan', 'offer'])
            ->whereDate('expired_at', $targetDate)
            ->whereNotIn('user_id', $activeUserIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('transaction_logs')
                    ->groupBy('user_id');
            })
            ->chunkById($chunkSize, function ($chunks) use (
                $frequency,
                $days,
                $emailLog,
                $whatsappLog,
                $allTemplateData,
                &$chunkIndex,
                &$totalProcessed
            ) {
                Log::info("🔄 Processing chunk #{$chunkIndex} with " . $chunks->count() . " users for day {$days}");

                foreach ($chunks as $transaction) {
                    $this->processSingleUser(
                        $transaction,
                        $frequency,
                        $emailLog,
                        $whatsappLog,
                        $allTemplateData
                    );

                    $totalProcessed++;
                    sleep(2); // Rate limiting
                }
                $chunkIndex++;
                Log::info("📊 Chunk #{$chunkIndex} completed - Total processed: {$totalProcessed}");
            });

        Log::info("✅ Finished processing all chunks for day {$days} - Total users: {$totalProcessed}");
    }

    private function processSingleUser(
        TransactionLog $transaction,
                       $frequency,
                       $emailLog,
                       $whatsappLog,
                       $allTemplateData
    ): void {
        $user = $transaction->userData;
        if (!$user) {
            Log::warning("⚠️ Skipping Order #{$transaction->id} due to missing user relation");
            $this->logFailedCommunication($emailLog, $whatsappLog, null, null, null, "Missing user for Order {$transaction->id}");
            return;
        }

        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}");

        try {
            $commonData = $transaction->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $transaction->contact_no, $commonData['message']);
                return;
            }

            $contactNumber = $transaction->contact_no;

            $results = $this->handleAutomationForJob($frequency, $user, $commonData, $contactNumber, $allTemplateData);

            $this->processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);

        } catch (\Exception $e) {
            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
            $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $transaction->contact_no, $e->getMessage());
        }
    }

    private function handleAutomationForJob($frequencyConfig, $user, $commonData, $contactNumber, $allTemplateData): array
    {
        return AutomationUtils::handleAutomationForJob(
            $frequencyConfig,
            $user,
            $commonData,
            $contactNumber,
            $allTemplateData,
            ConfigType::RECENT_EXPIRE_AUTOMATION->value
        );
    }

    private function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
    {
        foreach ($results as $channel => $result) {
            $log = $channel === 'email' ? $emailLog : $whatsappLog;

            if (!$log) continue;

            if ($result['success']) {
                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
                $this->logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'sent', $user->email, $contactNumber);
            } else {
                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
                $this->logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'failed', $user->email, $contactNumber, $result['message']);
            }
        }
    }

    /**
     * Create automation log entry
     */
    private function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
    {
        $userIdsJson = json_encode($userIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Log::info("📝 Creating log for {$type->value} with user_ids: {$userIdsJson}");

        return AutomationSendLog::create([
            'campaign_name' => $campaignName,
            'user_ids' => $userIdsJson,
            'select_users_type' => 3,
            'total' => count($userIds),
            'sent' => 0,
            'failed' => 0,
            'status' => 'processing',
            'type' => $type->value,
            'send_type' => ConfigType::RECENT_EXPIRE_AUTOMATION->value
        ]);
    }

    private function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $contactNumber, $error): void
    {
        if ($emailLog) {
            $this->logCommunicationResult($emailLog, $userId, AutomationType::EMAIL, 'failed', $email, null, $error);
        }

        if ($whatsappLog) {
            $this->logCommunicationResult($whatsappLog, $userId, AutomationType::WHATSAPP, 'failed', null, $contactNumber, $error);
        }
    }

    private function logCommunicationResult($log, $userId, AutomationType $type, string $status, $email = null, $contactNumber = null, $error = null): void
    {
        // Update the main log counters
        if ($status === 'sent') {
            $log->increment('sent');
        } else {
            $log->increment('failed');
        }

        $log->increment('total');

        // Create detail log only for failures
        if ($status === 'failed') {
            AutomationSendDetail::create([
                'log_id' => $log->id,
                'user_id' => $userId,
                'type' => $type->value,
                'send_type' => ConfigType::RECENT_EXPIRE_AUTOMATION->value,
                'email' => $email,
                'contact_number' => $contactNumber,
                'status' => 'failed',
                'error_message' => $error,
            ]);
        }
    }

}

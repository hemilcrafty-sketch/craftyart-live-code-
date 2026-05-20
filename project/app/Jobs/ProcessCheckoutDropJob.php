<?php

namespace App\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\Config;
use App\Models\Automation\AutomationSendDetail;
use App\Models\Automation\AutomationSendLog;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCheckoutDropJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("🚀 Checkout Drop Job Started");

        // Fetch configuration
        $config = Config::where('name', ConfigType::CHECKOUT_DROP_AUTOMATION->label())->first();
        if (!$config) {
            Log::warning("❌ CheckoutDropJob: Config not found!");
            return;
        }

        $configData = $config->value;
        if (!is_array($configData) || empty($configData)) {
            Log::warning("❌ CheckoutDropJob: Invalid config data!");
            return;
        }

        Log::info("📊 Processing " . count($configData) . " frequency configurations");

        $now = Carbon::now();

        $allTemplateData = AutomationUtils::preFetchAllTemplatesAndPromoCodes($configData, ConfigType::CHECKOUT_DROP_AUTOMATION->value);

        Log::info("🎯 Starting to process frequencies");

        foreach ($configData as $frequencyIndex => $frequency) {
            $days = (int)($frequency['day'] ?? 0);

            if ($days === 0) {
                Log::info("⏭️ Skipping day 0 configuration");
                continue;
            }

            Log::info("🔁 Processing frequency #{$frequencyIndex} for day: {$days}");

            $this->processFrequency($frequency, $days, $now, $allTemplateData);
        }

        Log::info("🎉 Checkout Drop Job Finished Successfully");
    }

    private function processFrequency($frequency, int $days, Carbon $now, array $allTemplateData): void
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
        $campaignName = "Checkout Drop - Day {$days} - {$targetDate}";

        if ($emailEnabled) {
            $emailLog = $this->createAutomationLog($campaignName, [], AutomationType::EMAIL);
            Log::info("📧 Email Automation Log created: #{$emailLog->id}");
        }

        if ($whatsappEnabled) {
            $whatsappLog = $this->createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
            Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
        }

        // Process users in chunks without loading all data at once
        $this->processUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData);

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

    private function processUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData): void
    {
        $chunkSize = 100;
        $totalProcessed = 0;
        $chunkIndex = 0;

        Log::info("👥 Starting chunked user processing for day {$days}");

        // Use chunking with eager loading - relationships will handle the data
        Order::with([
            'user.transactionLogs' => function ($q) {
                $q->latest('id')->limit(1);
            }
        ])
            ->whereDate('created_at', $targetDate)
            ->where('status', 'failed')
            ->whereHas('user')
            ->where(function ($q) {
                $q->whereDoesntHave('user.transactionLogs')
                    ->orWhereHas('user.transactionLogs', function ($q2) {
                        $q2->where('expired_at', '<', now());
                    });
            })
            ->chunkById($chunkSize, function ($chunk) use (
                $frequency,
                $days,
                $emailLog,
                $whatsappLog,
                $allTemplateData,
                &$chunkIndex,
                &$totalProcessed
            ) {
                Log::info("🔄 Processing chunk #{$chunkIndex} with " . $chunk->count() . " users for day {$days}");

                foreach ($chunk as $order) {
                    $this->processSingleUser(
                        $order,
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
            'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value
        ]);
    }

    private function processSingleUser(
        Order $order,
              $frequency,
              $emailLog,
              $whatsappLog,
              $allTemplateData
    ): void
    {
        $user = $order->user;
        if (!$user) {
            Log::warning("⚠️ Skipping Order #{$order->id} due to missing user relation");
            $this->logFailedCommunication($emailLog, $whatsappLog, null, null, null, "Missing user for Order {$order->id}");
            return;
        }

        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}, PlanType: {$order->type}");

        try {
            $commonData = $order->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_no, $commonData['message']);
                return;
            }

            $contactNumber = $order->contact_no;

            $results = AutomationUtils::handleAutomationForJob(
                $frequency,
                $user,
                $commonData,
                $contactNumber,
                $allTemplateData,
                ConfigType::CHECKOUT_DROP_AUTOMATION->value
            );

            $this->processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);

        } catch (\Exception $e) {
            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
            $this->logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_no, $e->getMessage());
        }
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
     * Common method to log failed communication for both email and WhatsApp
     * (Keep this for other failure scenarios outside processCommunicationResults)
     */
    private function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $contactNumber, $error): void
    {
        if ($emailLog) {
            $this->logCommunicationResult($emailLog, $userId, AutomationType::EMAIL, 'failed', $email, null, $error);
        }

        if ($whatsappLog) {
            $this->logCommunicationResult($whatsappLog, $userId, AutomationType::WHATSAPP, 'failed', null, $contactNumber, $error);
        }
    }

    /**
     * Common method to log communication results for both email and WhatsApp
     */
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
                'send_type' => ConfigType::CHECKOUT_DROP_AUTOMATION->value,
                'email' => $email,
                'contact_number' => $contactNumber,
                'status' => 'failed',
                'error_message' => $error,
            ]);
        }
    }
}

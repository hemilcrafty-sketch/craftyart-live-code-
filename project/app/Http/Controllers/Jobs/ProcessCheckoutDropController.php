<?php

namespace App\Http\Controllers\Jobs;

use App\Enums\AutomationType;
use App\Enums\ConfigType;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Automation\Config;
use App\Models\Automation\AutomationSendDetail;
use App\Models\Automation\AutomationSendLog;
use App\Models\Order;
use App\Models\TransactionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessCheckoutDropController extends ApiController
{
    public static function instantDropOut(): void
    {
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $orders = Order::where('status', 'pending')->where('created_at', '<', $fiveMinutesAgo)->get();

        foreach ($orders as $order) {
            $isExists = TransactionLog::whereUserId($order->user_id)->where('expired_at', '>', now())->exists();
            $order->status = $isExists ? 'override' : 'failed';
            $order->save();
            if (!$isExists) EmailController::sendPurchaseDropoutEmail($order);
        }

    }

    public static function startProcess(): void
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

            ProcessCheckoutDropController::processFrequency($frequency, $days, $now, $allTemplateData);
        }

        Log::info("🎉 Checkout Drop Job Finished Successfully");
    }

    private static function processFrequency($frequency, int $days, Carbon $now, array $allTemplateData): void
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
            $emailLog = ProcessCheckoutDropController::createAutomationLog($campaignName, [], AutomationType::EMAIL);
            Log::info("📧 Email Automation Log created: #{$emailLog->id}");
        }

        if ($whatsappEnabled) {
            $whatsappLog = ProcessCheckoutDropController::createAutomationLog($campaignName, [], AutomationType::WHATSAPP);
            Log::info("💬 WhatsApp Automation Log created: #{$whatsappLog->id}");
        }

        // Process users in chunks without loading all data at once
        ProcessCheckoutDropController::processUsersInChunks($frequency, $days, $targetDate, $emailLog, $whatsappLog, $allTemplateData);

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

    private static function processUsersInChunks($frequency, int $days, string $targetDate, $emailLog, $whatsappLog, array $allTemplateData): void
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
                    ProcessCheckoutDropController::processSingleUser(
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
    private static function createAutomationLog(string $campaignName, array $userIds, AutomationType $type): AutomationSendLog
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

    private static function processSingleUser(
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
            ProcessCheckoutDropController::logFailedCommunication($emailLog, $whatsappLog, null, null, null, "Missing user for Order {$order->id}");
            return;
        }

        Log::info("👤 Processing User #{$user->id}, Email: {$user->email}, PlanType: {$order->type}");

        try {
            $commonData = $order->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                ProcessCheckoutDropController::logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_no, $commonData['message']);
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
            ProcessCheckoutDropController::processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber);

        } catch (\Exception $e) {
            Log::error("❌ Error processing User #{$user->id}: " . $e->getMessage());
            ProcessCheckoutDropController::logFailedCommunication($emailLog, $whatsappLog, $user->id, $user->email, $order->contact_no, $e->getMessage());
        }
    }

    private static function processCommunicationResults($results, $emailLog, $whatsappLog, $user, $contactNumber): void
    {
        foreach ($results as $channel => $result) {
            $log = $channel === 'email' ? $emailLog : $whatsappLog;

            if (!$log) continue;


            $isSuccess = $result['success'];
            if ($isSuccess) {
                Log::info("✅ {$channel} sent successfully to User #{$user->id}");
                ProcessCheckoutDropController::logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'sent', $user->email, $contactNumber);
            } else {
                Log::error("❌ Failed to send {$channel} to User #{$user->id}: {$result['message']}");
                ProcessCheckoutDropController::logCommunicationResult($log, $user->id, $channel === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP, 'failed', $user->email, $contactNumber, $result['message']);
            }
        }
    }

    /**
     * Common method to log failed communication for both email and WhatsApp
     * (Keep this for other failure scenarios outside processCommunicationResults)
     */
    private static function logFailedCommunication($emailLog, $whatsappLog, $userId, $email, $contactNumber, $error): void
    {
        if ($emailLog) {
            ProcessCheckoutDropController::logCommunicationResult($emailLog, $userId, AutomationType::EMAIL, 'failed', $email, null, $error);
        }

        if ($whatsappLog) {
            ProcessCheckoutDropController::logCommunicationResult($whatsappLog, $userId, AutomationType::WHATSAPP, 'failed', null, $contactNumber, $error);
        }
    }

    /**
     * Common method to log communication results for both email and WhatsApp
     */
    private static function logCommunicationResult($log, $userId, AutomationType $type, string $status, $email = null, $contactNumber = null, $error = null): void
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

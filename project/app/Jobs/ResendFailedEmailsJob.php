<?php

namespace App\Jobs;

use App\Helpers\MailHelper;
use App\Http\Controllers\EmailTemplateController;
use App\Models\EmailTemplate;
use App\Models\MailSendDetail;
use App\Models\MailSendLog;
use App\Models\UserData;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResendFailedEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $log_id;

    public function __construct(int $log_id)
    {
        $this->log_id = $log_id;
    }

    public function handle(): void
    {
        $log = MailSendLog::find($this->log_id);
        if (!$log) {
            Log::error("Log ID {$this->log_id} not found or template missing.");
            return;
        }

        $failedLogs = MailSendDetail::where('log_id', $this->log_id)->get();
        $successCount = 0;

        foreach ($failedLogs as $failed) {
            $user = UserData::find($failed->user_id);
            if (!$user || empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $emailTemplate = EmailTemplate::find($log->email_template_id);

            try {

                $response['userData'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                $htmlBody = View::make($emailTemplate->email_templates, [
                    'data' => $response,
                ])->render();

                // 🔥 Use MailHelper instead of API
                $result = EmailTemplateController::sendEmail(
                    $user->email,
                    $log->subject,
                    $htmlBody,
                    'text'
                );

                if (str_contains($result, "successfully")) {
                    $failed->delete();
                    $successCount++;
                }

            } catch (\Throwable $e) {
                Log::error("Error resending email to {$user->email}: {$e->getMessage()}");
            }
        }

        $log->increment('sent', $successCount);
        $log->decrement('failed', $successCount);
    }
}

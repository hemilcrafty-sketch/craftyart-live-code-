<?php

namespace App\Http\Controllers\Automation;

use App\Enums\AutomationType;
use App\Http\Controllers\AppBaseController;
use App\Jobs\ResendFailedCampaignJob;
use App\Jobs\SendBulkCampaignJob;
use App\Models\Automation\CampaignFailedDetail;
use App\Models\Automation\CampaignSendLog;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\PromoCode;
use App\Models\Subscription;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CampaignController extends AppBaseController
{
    public function index(Request $request): Application|Factory|View
    {

        $emailTemplates = EmailTemplate::whereStatus(1)->get();
        $wpTemplates = WhatsappTemplate::whereStatus(1)->get();
        $promoCodes = PromoCode::whereStatus( 1)->get();
        $getPlans = Subscription::all();
        return view('campaign.index', compact('emailTemplates','wpTemplates', 'promoCodes', 'getPlans'));
    }

    public function startCampaign(Request $request): JsonResponse
    {
        try {
            $validationRules = [
                'campaign_type' => 'required|array',
                'campaign_type.*' => 'in:email,whatsapp',
                'select_users_type' => 'required|integer|in:1,2,3,4,5,6',
                'auto_pause_count' => 'required|integer|min:1',
                'user_id' => 'sometimes|array',
                'promo_code' => 'sometimes|integer',
                'plan_id' => 'sometimes|integer',
            ];

            // Add conditional validation based on campaign types
            $campaignTypes = $request->input('campaign_type', []);

            if (empty($campaignTypes)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Please select at least one campaign type.',
                ], 422);
            }

            if (in_array('email', $campaignTypes)) {
                $validationRules['email_template_id'] = 'required|integer';
                $validationRules['subject'] = 'required|string|max:255';
                $validationRules['template_type'] = 'required|in:1';
            }
            if (in_array('whatsapp', $campaignTypes)) {
                $validationRules['wp_template_id'] = 'required|integer';
            }
            $request->validate($validationRules);

            $select_users_type = $request->input('select_users_type');
            $user_ids = $request->input('user_id', []);
            $auto_pause_count = $request->input('auto_pause_count');
            $promo_code = $request->input('promo_code', 0);
            $plan_id = $request->input('plan_id', 0);


            // Custom validation for user selection
            if ($select_users_type == 3 && empty($user_ids)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Please select at least one user for custom audience.',
                ], 422);
            }

            // Email campaign specific validations
            if (in_array('email', $campaignTypes)) {
                $email_template_id = $request->input('email_template_id');
                $subject = $request->input('subject');

                $emailTemplate = EmailTemplate::find($email_template_id);
                if (!$emailTemplate || empty($emailTemplate->email_template)) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Selected email template not found or invalid.',
                    ], 422);
                }
            }

            // WhatsApp campaign specific validations
            if (in_array('whatsapp', $campaignTypes)) {
                $wp_template_id = $request->input('wp_template_id');

                $wpTemplate = WhatsappTemplate::find($wp_template_id);
                if (!$wpTemplate) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Selected WhatsApp template not found.',
                    ], 422);
                }

                // Template parameter validation for WhatsApp
                $templateParams = $wpTemplate->template_params;
                $hasPromoData = collect($templateParams)->contains(fn($param) => str_starts_with($param, 'PromoData.'));
                $hasPlanData = collect($templateParams)->contains(fn($param) => str_starts_with($param, 'PlanData.'));

                if ($hasPromoData && !$promo_code) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Promo code is required for the selected WhatsApp template.',
                    ], 422);
                }

                if ($hasPlanData && !$plan_id) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Plan selection is required for the selected WhatsApp template.',
                    ], 422);
                }
            }

            $total_users = 0;
            if ($select_users_type == 3) {
                $total_users = count($user_ids);
            }

            // Create campaign log
            $campaign = new CampaignSendLog();
            $campaign->subject = $request->input('subject', '');
            $campaign->email_template_id = in_array('email', $campaignTypes) ? $request->input('email_template_id') : 0;
            $campaign->wp_template_id = in_array('whatsapp', $campaignTypes) ? $request->input('wp_template_id') : 0;
            $campaign->select_users_type = $select_users_type;
            $campaign->promo_code = $promo_code;
            $campaign->user_ids = !empty($user_ids) ? json_encode($user_ids) : "[]";
            $campaign->status = "pending";
            $campaign->plan_id = $plan_id;
            $campaign->total = $total_users;
            $campaign->auto_pause_count = $auto_pause_count;
            $campaign->sent_since_last_pause = 0;
            $campaign->stopped = false;
            $campaign->last_processed_user_id = null;

            // Set type based on campaign selection
            if (count($campaignTypes) === 2) {
                $campaign->type = AutomationType::EMAIL_WHATSAPP;
            } else {
                $campaign->type = $campaignTypes[0] === 'email' ? AutomationType::EMAIL : AutomationType::WHATSAPP;
            }

            $campaign->email_sent = 0;
            $campaign->wp_sent = 0;
            $campaign->email_failed = 0;
            $campaign->wp_failed = 0;
            $campaign->total_processed = 0;
            $campaign->pause_type = null;
            $campaign->auto_resume = 0;
            $campaign->send_type = 1; // Custom Campaign

            $campaign->save();


            SendBulkCampaignJob::dispatch($campaign->id);


            // Generate success message based on campaign types
            $message = 'Campaign started successfully. ';
            if (in_array('email', $campaignTypes) && in_array('whatsapp', $campaignTypes)) {
                $message .= 'Emails and WhatsApp messages will be processed in the background.';
            } elseif (in_array('email', $campaignTypes)) {
                $message .= 'Emails will be processed in the background.';
            } else {
                $message .= 'WhatsApp messages will be processed in the background.';
            }

            return response()->json([
                'status' => true,
                'msg' => $message,
                'campaign_id' => $campaign->id
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'msg' => 'Failed to start campaign: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function report(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Log ID'],
            ["id" => 'subject', "value" => 'Subject'],
            ["id" => 'type', "value" => 'Type'],
            ["id" => 'total', "value" => 'Total'],
            ["id" => 'email_sent', "value" => 'Email Sent'],
            ["id" => 'email_failed', "value" => 'Email Failed'],
            ["id" => 'wp_sent', "value" => 'WhatsApp Sent'],
            ["id" => 'wp_failed', "value" => 'WhatsApp Failed'],
            ["id" => 'status', "value" => 'Status'],
            ["id" => 'created_at', "value" => 'Created Date'],
            ["id" => 'updated_at', "value" => 'Updated Date'],
        ];

        $query = CampaignSendLog::query();

        $logs = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        return view('campaign.report', compact('logs', 'searchableFields'));
    }

    public function triggerResendCampaignJob(Request $request, int $log_id): JsonResponse
    {
        $log = CampaignSendLog::whereId($log_id)->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
        }

        // Check if there are any failed messages to resend
        $hasFailedEmails = $log->email_failed > 0;
        $hasFailedWhatsApp = $log->wp_failed > 0;

        if (!$hasFailedEmails && !$hasFailedWhatsApp) {
            return response()->json(['success' => false, 'message' => 'No failed messages found to resend.'], 400);
        }

        ResendFailedCampaignJob::dispatch($log_id, 'all');

        $message = 'Resend job started for ';
        $parts = [];

        if ($hasFailedEmails) {
            $parts[] = $log->email_failed . ' failed email(s)';
        }

        if ($hasFailedWhatsApp) {
            $parts[] = $log->wp_failed . ' failed WhatsApp message(s)';
        }

        $message .= implode(' and ', $parts) . ' in the background.';

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function triggerResendEmailJob(Request $request, int $log_id): JsonResponse
    {
        $log = CampaignSendLog::whereId($log_id)->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
        }

        if ($log->email_failed <= 0) {
            return response()->json(['success' => false, 'message' => 'No failed emails found to resend.'], 400);
        }

        ResendFailedCampaignJob::dispatch($log_id, 'email');

        return response()->json([
            'success' => true,
            'message' => "Resend job started for {$log->email_failed} failed email(s) in the background."
        ]);
    }

    public function triggerResendWhatsAppJob(Request $request, int $log_id): JsonResponse
    {
        $log = CampaignSendLog::whereId($log_id)->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
        }

        if ($log->wp_failed <= 0) {
            return response()->json(['success' => false, 'message' => 'No failed WhatsApp messages found to resend.'], 400);
        }

        ResendFailedCampaignJob::dispatch($log_id, 'whatsapp');

        return response()->json([
            'success' => true,
            'message' => "Resend job started for {$log->wp_failed} failed WhatsApp message(s) in the background."
        ]);
    }

    /**
     * Unified failed logs method
     */
    public function failedLogs(Request $request, int $log_id): Factory|View|Application
    {
        $log = CampaignSendLog::findOrFail($log_id);

        $failedLogs = CampaignFailedDetail::where('log_id', $log_id)
            ->where('status', 'failed')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('campaign.failed_logs', compact('failedLogs', 'log_id', 'log'));
    }

    public function stop($id): RedirectResponse
    {
        $log = CampaignSendLog::findOrFail($id);
        $log->update(['status' => 'stopped', 'stopped' => true]);
        return redirect()->back()->with('success', 'Job stopped permanently.');
    }

    public function pause($id): RedirectResponse
    {
        $log = CampaignSendLog::findOrFail($id);
        $log->update([
            'status' => 'paused',
            'pause_type' => 'manual'
        ]);
        return redirect()->back()->with('success', 'Job manually paused.');
    }

    public function toggleAutoResume($id): JsonResponse
    {
        $log = CampaignSendLog::findOrFail($id);

        $log->update(['auto_resume' => !$log->auto_resume]);

        return response()->json([
            'status' => true,
            'auto_resume' => $log->auto_resume,
            'message' => 'Status Updated Successfully.'
        ]);
    }

    public function resume($id): RedirectResponse
    {
        $log = CampaignSendLog::findOrFail($id);

        if ($log->stopped) {
            return redirect()->back()->with('error', 'Cannot resume stopped job.');
        }

        if ($log->pause_type === 'auto') {
            return redirect()->back()->with('error', 'This job was auto-paused after reaching the daily limit. It will resume automatically tomorrow.');
        }

        $log->update([
            'status' => 'pending',
            'pause_type' => null
        ]);

        SendBulkCampaignJob::dispatch($log->id);

        return redirect()->back()->with('success', 'Job resumed.');
    }

}
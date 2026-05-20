<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\AutomationUtils;
use App\Jobs\ResendFailedEmailsJob;
use App\Jobs\SendBulkEmailJob;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\MailSendDetail;
use App\Models\Automation\MailSendLog;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View as Views;
use Illuminate\Support\Str;

class EmailTemplateController extends AppBaseController
{
    public function index(Request $request)
    {

        $emailTemplates = EmailTemplate::whereStatus(1)->get();
        $promoCodes = PromoCode::where('status', 1)->get();
        $getPlans = Subscription::all();
        return view('email_template.index', compact('emailTemplates', 'promoCodes','getPlans'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required',
            'template_type' => 'required',
            'email_template_id' => 'required',
            'select_users_type' => 'required|in:1,2,3,4,5',
            'auto_pause_count' => 'required|integer|min:1',
        ]);

        $auto_pause_count = $request->input('auto_pause_count');
        $subject = $request->input('subject');
        $select_users_type = $request->input('select_users_type');
        $user_ids = $request->input('user_id', []);
        $email_template_id = $request->input('email_template_id');
        $getPlan = $request->input('plan_id');
        $promo_code = $request->input('promo_code', 0) ?? 0;

        $emailTemplate = EmailTemplate::find($email_template_id);

        if (!$emailTemplate || empty($emailTemplate->email_template)) {
            return response()->json([
                'status' => false,
                'msg' => "Selected old template not found or invalid.",
            ]);
        }

        $log = MailSendLog::create([
            'subject' => $subject,
            'email_template_id' => $email_template_id,
            'select_users_type' => $select_users_type,
            'promo_code' => $promo_code,
            'user_ids' => json_encode($user_ids),
            'status' => 'pending',
            'sent' => 0,
            'failed' => 0,
            'last_processed_user_id' => null,
            'auto_pause_count' => $auto_pause_count,
            'emails_sent_since_last_pause' => 0,
            'stopped' => false,
            'plan_id' => $getPlan,
            'total' => count($user_ids) ?: 0,
        ]);

        SendBulkEmailJob::dispatch($log->id);

        return response()->json([
            'status' => true,
            'msg' => 'Emails will be processed in the background.',
        ]);
    }

    public function stop($id): RedirectResponse
    {
        $log = MailSendLog::findOrFail($id);
        $log->update(['status' => 'stopped', 'stopped' => true]);
        return redirect()->back()->with('success', 'Job stopped permanently.');
    }

    public function pause($id): RedirectResponse
    {
        $log = MailSendLog::findOrFail($id);
        $log->update([
            'status' => 'paused',
            'pause_type' => 'manual'
        ]);
        return redirect()->back()->with('success', 'Job manually paused.');
    }

    public function resume($id): RedirectResponse
    {
        $log = MailSendLog::findOrFail($id);

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

        SendBulkEmailJob::dispatch($log->id);

        return redirect()->back()->with('success', 'Job resumed.');
    }

    public function toggleAutoResume($id): JsonResponse
    {
        $log = MailSendLog::findOrFail($id);

        $log->update(['auto_resume' => !$log->auto_resume]);

        return response()->json([
            'status' => true,
            'auto_resume' => $log->auto_resume,
            'message' => 'Status Updated Successfully.'
        ]);
    }

    public function storeTemplate(Request $request, $id = null): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email_template' => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($request->email_template, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
            if ($dom->getElementsByTagName('script')->length > 0) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Script tags are not allowed in email templates.'
                ]);
            }

            if ($id) {
                $template = EmailTemplate::findOrFail($id);
                $filePath = resource_path('views/' . str_replace('.', '/', $template->email_template) . '.blade.php');
                file_put_contents($filePath, $request->email_template);
                $template->update([
                    'name' => $request->name,
                    'status' => $request->status
                ]);
            } else {
                $timestamp = time();
                $fileName = 'email_view_' . $timestamp . '.blade.php';
                $filePath = resource_path('views/email_view/' . $fileName);
                file_put_contents($filePath, $request->email_template);

                $template = EmailTemplate::create([
                    'name' => $request->name,
                    'email_template' => 'email_view.email_view_' . $timestamp,
                    'status' => $request->status
                ]);
            }

            return response()->json([
                'status' => true,
                'msg' => 'Template saved successfully!',
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function editTemplate($id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $filePath = resource_path('views/' . str_replace('.', '/', $template->email_template) . '.blade.php');
        $templateContent = file_exists($filePath) ? file_get_contents($filePath) : '';

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'status' => $template->status,
                'email_template_content' => $templateContent
            ]
        ]);
    }

    public function deleteTemplate($id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        // Delete blade file
        $filePath = resource_path('views/' . str_replace('.', '/', $template->email_template) . '.blade.php');
        if (file_exists($filePath))
            unlink($filePath);

        $template->delete();

        return response()->json(['status' => true, 'msg' => 'Template deleted successfully']);
    }


    public function createEmailTemplate(): Factory|View|Application
    {
        $templateDatas = EmailTemplate::all();
        return view('email_template.template_create', compact('templateDatas'));
    }



    public function getEmailTmp(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $users = UserData::where(function ($q) use ($query) {
            $q->where('email', 'like', "%{$query}%")
                ->orWhere('id', 'like', "%{$query}%");
        })->limit(100)->get(['id', 'uid', 'email']);

        return response()->json(
            $users->map(fn($u) => ['id' => $u->uid, 'text' => "{$u->id} - {$u->email}"])
        );
    }

    public function report(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Log ID'],
            ["id" => 'subject', "value" => 'Subject'],
            ["id" => 'total', "value" => 'Total'],
            ["id" => 'sent', "value" => 'Sent'],
            ["id" => 'failed', "value" => 'Failed'],
            ["id" => 'created_at', "value" => 'Created Date'],
            ["id" => 'updated_at', "value" => 'Updated Date'],
        ];

        $logs = $this->applyFiltersAndPagination(
            $request,
            MailSendLog::query()->where("send_type",1),
            $searchableFields
        );

        return view('email_template.report', compact('logs', 'searchableFields'));
    }

    public function viewTemplate($view): Factory|View|Application
    {
        if (!Views::exists($view)) {
            abort(404, 'Email template not found');
        }

        $name = Auth::user()->name;
        $email = Auth::user()->email;

        return view($view, compact('name', 'email'));
    }

    public function failedLogs($log_id): Factory|View|Application
    {
        $failedLogs = MailSendDetail::where('log_id', $log_id)
            ->whereNotNull('error_message')
            ->get();

        return view('email_template.failed_logs', compact('failedLogs', 'log_id'));
    }

    public function triggerResendJob(Request $request, int $log_id): JsonResponse
    {
        $log = MailSendLog::find($log_id);

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
        }

        ResendFailedEmailsJob::dispatch($log_id);

        return response()->json(['success' => true, 'message' => 'Resend job started in the background.']);
    }

    public function resendSingleFailed(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:mail_send_details,id',
        ]);

        $logDetail = MailSendDetail::find($request->id);
        $user = UserData::find($logDetail->user_id);
        $log = MailSendLog::find($logDetail->log_id);

        if (!$user || !$log || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid user or log.']);
        }

        try {
            if (!Views::exists($log->email_template)) {
                return response()->json(['success' => false, 'message' => 'Email template not found.']);
            }

            $htmlBody = Views::make($log->email_template, [
                'name' => $user->name,
                'email' => $user->email,
            ])->render();

            $result = $this->sendEmail(
                $user->email,
                $log->subject,
                $htmlBody,
                'text'
            );

            if (str_contains($result, "successfully")) {
                $logDetail->delete();
                $log->increment('sent', 1);
                $log->decrement('failed', 1);

                return response()->json(['success' => true, 'message' => 'Email resent successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Failed to resend email.']);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Internal error: ' . $e->getMessage()]);
        }
    }

    public static function sendEmail($to, $subject, $body, $mailType = 'text'): string
    {
        try {
            if ($mailType === 'view') {
                $fileName = Str::slug($subject) . '.blade.php';
                $path = resource_path('views/email_view/' . $fileName);
                file_put_contents($path, $body);

                $viewName = 'email_view.' . Str::slug($subject);

                Mail::send($viewName, [], function ($message) use ($to, $subject) {
                    $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                        ->to($to)
                        ->replyTo(env("MAIL_FROM_ADDRESS"), 'Reply Support')
                        ->subject($subject);

                    $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                });
            } else {
                Mail::send([], [], function ($message) use ($to, $subject, $body) {
                    $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                        ->to($to)
                        ->replyTo(env("MAIL_FROM_ADDRESS"), 'Reply Support')
                        ->subject($subject)
                        ->setBody($body, 'text/html');

                    $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                });
            }

            return "Email sent successfully";
        } catch (\Throwable $e) {
            return "Failed: " . $e->getMessage();
        }
    }

    public function preview($id)
    {
        $log = EmailTemplate::findOrFail($id);

        if (empty($log->email_template)) {
            return response("Template not found!", 404);
        }

        $response['userData'] = [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            "password" => "123456"
        ];
        $plan = Subscription::whereStatus(1)->first();
        $response['type'] = "plan";
        $response['data'] = AutomationUtils::formatOldPlanData(plan: $plan);
        $response['plan'] = $plan;
        $response['planType'] = 'old_sub';
        $PromoCode = PromoCode::whereStatus(1)->first();
        if ($PromoCode) {
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
        return view($log->email_template, [
            'data' => $response,
        ]);
    }
}
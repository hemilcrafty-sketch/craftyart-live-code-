<?php

namespace App\Http\Controllers\Automation;

use App\Enums\WhatsappParams;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Automation\WhatsappTemplate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $templates = WhatsappTemplate::orderBy('id', 'desc')->paginate(15);
        return view('whatsapp_template.index', compact('templates'));
    }

    public function storeTemplate(Request $req): JsonResponse
    {
        if (!$req->filled('campaign_name')) {
            return response()->json(['success' => false, 'message' => 'Campaign name required'], 400);
        }
        $mediaUrl = "";
        if ($req->boolean('media_url')){
            if ($error = ContentManager::validateBase64Images([['img' => $req->input('url'), 'name' => 'Thumb', 'required' => true]])) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $mediaUrl = ContentManager::saveImageToPath(
                $req->input('url'),
                'campaigns/whatsapp' . StorageUtils::getNewName()
            );
        }


        $template = WhatsappTemplate::create([
            'campaign_name' => $req->input('campaign_name'),
            'template_params_count' => $req->input('template_params_count'),
            'template_params' => json_encode($req->input('template_params')),   // array
            'media_url' => $req->boolean('media_url'),
            'url' => $mediaUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template created',
            'template' => $template,
        ]);
    }

    public function edit($id): JsonResponse
    {
        $template = WhatsappTemplate::find($id);
        return $template
            ? response()->json(['success' => true, 'template' => $template, 'params_enum' => WhatsappParams::list()])
            : response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    public function update(Request $req, $id): JsonResponse
    {
        $template = WhatsappTemplate::whereId($id)->first();
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        if (!$req->filled('campaign_name')) {
            return response()->json(['success' => false, 'message' => 'Campaign name required'], 400);
        }

//        $template->update([
//            'campaign_name' => $req->input('campaign_name'),
//            'template_params_count' => $req->input('template_params_count'),
//            'media_url' => $req->boolean('media_url'),
//            'url' => $req->input('url'),
//        ]);

        $mediaUrl = "";
        if ($req->boolean('media_url')){
            if ($error = ContentManager::validateBase64Images([['img' => $req->input('url'), 'name' => 'Thumb', 'required' => true]])) {
                return response()->json(['status' => false, 'error' => $error]);
            }

            $mediaUrl = ContentManager::saveImageToPath(
                $req->input('url'),
                'campaigns/whatsapp/' . StorageUtils::getNewName()
            );


            if ($template->url){
                if(StorageUtils::exists($template->url)){
                    StorageUtils::delete($template->url);
                }
            }
        }

        $template->update([
            'campaign_name' => $req->input('campaign_name'),
            'template_params_count' => $req->input('template_params_count'),
            'template_params' => json_encode($req->input('template_params')),
            'media_url' => $req->boolean('media_url'),
            'url' => $mediaUrl,
        ]);

        return response()->json(['success' => true, 'message' => 'Updated', 'template' => $template]);
    }

    public function destroy($id): JsonResponse
    {
        $template = WhatsappTemplate::find($id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $template->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }

    /*public function startCampaign(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'wp_template_id' => 'required|integer',
                'select_users_type' => 'required|in:1,2,3,4,5',
                'auto_pause_count' => 'required|integer|min:1',
                'user_id' => 'sometimes|array',
            ]);

            $wp_template_id = $request->input('wp_template_id');
            $select_users_type = $request->input('select_users_type');
            $user_ids = $request->input('user_id', []);

            // Get template
            $wpTemplate = WhatsappTemplate::find($wp_template_id);
            if (!$wpTemplate) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Selected template not found.',
                ], 422);
            }

            // Custom validation
            if ($select_users_type == 3 && empty($user_ids)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Please select at least one user for custom audience.',
                ], 422);
            }

            // Template parameter validation
            $templateParams = $wpTemplate->template_params;
            $hasPromoData = collect($templateParams)->contains(fn($param) => strpos($param, 'PromoData.') === 0);
            $hasPlanData = collect($templateParams)->contains(fn($param) => strpos($param, 'PlanData.') === 0);

            if ($hasPromoData && !$request->input('promo_code')) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Promo code is required for the selected template.',
                ], 422);
            }

            if ($hasPlanData && !$request->input('plan_id')) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Plan selection is required for the selected template.',
                ], 422);
            }

            // Rest of your store logic...
            $campaign = new CampaignSendLog();
            $campaign->subject = "";
            $campaign->email_template_id = 0;
            $campaign->wp_template_id = $wp_template_id;
            $campaign->select_users_type = $select_users_type;
            $campaign->promo_code = $request->input('promo_code');
            $campaign->user_ids = !empty($user_ids) ? json_encode($user_ids) : "[]";
            $campaign->status = "pending";
            $campaign->plan_id = $request->input('plan_id') ?? 0;
            $campaign->total = $select_users_type == 3 ? count($user_ids) : 0;
            $campaign->type = AutomationType::WHATSAPP;
            $campaign->auto_pause_count = $request->input('auto_pause_count');
            $campaign->save();

            SendBulkCampaignJob::dispatch($campaign->id);

            return response()->json([
                'status' => true,
                'msg' => 'WhatsApp messages will be processed in the background.',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    public function report(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Log ID'],
            ["id" => 'subject', "value" => 'Subject'],
            ["id" => 'total', "value" => 'Total'],
            ["id" => 'wp_sent', "value" => 'Sent'],
            ["id" => 'wp_failed', "value" => 'Failed'],
            ["id" => 'created_at', "value" => 'Created Date'],
            ["id" => 'updated_at', "value" => 'Updated Date'],
        ];

        $logs = $this->applyFiltersAndPagination(
            $request,
            CampaignSendLog::query()->whereType(AutomationType::WHATSAPP)->orWhere('type',AutomationType::EMAIL_WHATSAPP),
            $searchableFields
        );

        return view('whatsapp_template.report', compact('logs', 'searchableFields'));
    }

    public function templateIndex(Request $request): Factory|View|Application
    {

        $wpTemplates = WhatsappTemplate::whereStatus(1)->get();
        $promoCodes = PromoCode::whereStatus(1)->get();
        $getPlans = Subscription::all();
        return view('whatsapp_template.index', compact('wpTemplates', 'promoCodes', 'getPlans'));
    }

    public function failedLogs($log_id): Factory|View|Application
    {
        $failedLogs = CampaignSendDetail::whereLogId($log_id)
            ->whereNotNull('error_message')
            ->get();

        return view('whatsapp_template.failed_logs', compact('failedLogs', 'log_id'));
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
    }*/
}

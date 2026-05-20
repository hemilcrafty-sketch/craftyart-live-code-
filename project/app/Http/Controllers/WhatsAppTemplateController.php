<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WhatsappTemplate;

class WhatsAppTemplateController extends Controller
{
    public function index(Request $request)
    {
//        $apiConfig = Config::where('name', 'whatsapp_api')->first();
        $templates = WhatsappTemplate::orderBy('id', 'desc')->paginate(15);

        return view('whatsapp_config.index', compact('templates'));
    }

    public function store(Request $req): JsonResponse
    {
        if (!$req->filled('campaign_name')) {
            return response()->json(['success' => false, 'message' => 'Campaign name required'], 400);
        }

        $template = WhatsappTemplate::create([
            'campaign_name' => $req->input('campaign_name'),
            'template_params_count' => $req->input('template_params_count'),
            'media_url' => $req->boolean('media_url'),
            'url' => $req->input('url'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template created',
            'template' => $template,
        ]);
    }

    public function edit($id)
    {
        $template = WhatsappTemplate::find($id);
        return $template
            ? response()->json(['success' => true, 'template' => $template])
            : response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    public function update(Request $req, $id)
    {
        $template = WhatsappTemplate::find($id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        if (!$req->filled('campaign_name')) {
            return response()->json(['success' => false, 'message' => 'Campaign name required'], 400);
        }

        $template->update([
            'campaign_name' => $req->input('campaign_name'),
            'template_params_count' => $req->input('template_params_count'),
            'media_url' => $req->boolean('media_url'),
            'url' => $req->input('url'),
        ]);

        return response()->json(['success' => true, 'message' => 'Updated', 'template' => $template]);
    }

    public function destroy($id)
    {
        $template = WhatsappTemplate::find($id);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $template->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}

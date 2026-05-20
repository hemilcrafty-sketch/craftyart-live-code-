<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\AppBaseController;
use App\Models\Automation\EmailTemplate;
use App\Models\Automation\WhatsappTemplate;
use App\Models\Config;
use App\Models\PromoCode;
use App\Models\Subscription;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AutomationConfigController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $configs = Config::all()
            ->pluck('value', 'name')
            ->toArray();
        $emailTemplates = EmailTemplate::whereStatus(1)->get();
        $whatsappTemplates = WhatsappTemplate::whereStatus(1)->get();
        $promoCodes = PromoCode::where('status',1)->get();
        $plans = Subscription::whereStatus(1)->get();
        return view('automation_config.index', compact('configs', 'emailTemplates', 'whatsappTemplates' ,'promoCodes','plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $name = $request->input('name');
        $data = $request->input('data', []);

        array_walk_recursive($data, function (&$value) {
            if (is_numeric($value)) {
                $value = (int) $value;
            }
        });

        Config::updateOrCreate(
            ['name' => $name],
            ['value' => json_encode($data)]
        );

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $name)) . ' saved successfully!');
    }
}
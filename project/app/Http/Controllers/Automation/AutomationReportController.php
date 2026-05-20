<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\AppBaseController;
use App\Models\AutomationSendDetail;
use App\Models\AutomationSendLog;
use Illuminate\Http\Request;

class AutomationReportController extends AppBaseController
{
    function index(Request $request)
    {
        $automationReports = $this->applyFiltersAndPagination($request, AutomationSendLog::query(), $searchableFields = []);

        return view("automation_report.index", compact('automationReports'));
    }

    public function failedLogs($log_id)
    {
        $failedLogs = AutomationSendDetail::where('log_id', $log_id)
            ->whereNotNull('error_message')
            ->get();

        return view('automation_report.failed_logs', compact('failedLogs', 'log_id'));
    }

}
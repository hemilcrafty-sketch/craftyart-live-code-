<?php

namespace App\Http\Controllers;

use App\Models\AdminChangesLog;
use Illuminate\Http\Request;


class PanelHistroyController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = AdminChangesLog::query();

        $adminChangesLogs = $this->applyFiltersAndPagination($request, $query, $searchableFields = []);

        $adminChangesLogs->getCollection()->transform(function ($log) {
            $log->decoded_old_values = $this->formatJsonData($log->old_values);
            $log->decoded_updated_fields = $this->formatJsonData($log->updated_fields);
            return $log;
        });

        return view('panel_histroy.index', compact('adminChangesLogs', 'searchableFields'));
    }


    private static function formatJsonData($data)
    {
        if (empty($data))
            return [];

        $decoded = json_decode($data, true);
        if (!is_array($decoded))
            return [];

        $formatted = [];
        foreach ($decoded as $key => $value) {
            if (is_string($value)) {
                $inner = json_decode($value, true);
                if (is_array($inner)) {
                    $formatted[$key] = json_encode($inner, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    continue;
                }
            }

            if (is_array($value)) {
                $formatted[$key] = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }
}
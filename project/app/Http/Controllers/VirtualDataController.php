<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\QueryManager;
use App\Http\Controllers\Utils\RateController;
use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VirtualDataController extends ApiController
{
    // -----------------------------
    // Get Options
    // -----------------------------
    public function getOptions(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string',
            'idColumn' => 'required|string',
            'nameColumn' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: "Invalid Params");
        }

        $table = $request->input('table');
        $idColumn = $request->input('idColumn');
        $nameColumn = $request->input('nameColumn');

        $data = DB::table($table)
            ->select($idColumn, $nameColumn)
            ->get();

        return $this->successed(datas: ["data" => $data->values()->all()]);
    }

    // -----------------------------
    // Get Dependent Value
    // -----------------------------
    public function getDependentValue(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string',
            'dependentColumn' => 'required|string',
            'dependentColumnId' => 'required|string',
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: "Invalid Params");
        }

        $table = $request->input('table');
        $dependentColumn = $request->input('dependentColumn');
        $dependentColumnId = $request->input('dependentColumnId');
        $id = $request->input('id');

        $value = DB::table($table)
            ->where($dependentColumnId, $id)
            ->value($dependentColumn);

        return $this->successed(datas: ["value" => $value ?? ""]);
    }

    // -----------------------------
    // Get Unique Options
    // -----------------------------
    public function getUniqueOptions(Request $request): array|string
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string',
            'column' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: "Invalid Params");
        }

        $table = $request->input('table');
        $column = $request->input('column');

        $results = DB::table($table)
            ->select($column)
            ->distinct()
            ->get();

        $options = [];

        foreach ($results as $result) {

            $cleaned = str_replace(['[', ']', '"'], '', $result->$column);
            $tags = explode(',', $cleaned);

            foreach ($tags as $tag) {

                $trimmedTag = trim($tag);

                if ($trimmedTag !== '') {
                    $options[] = [
                        'value' => $trimmedTag,
                        'text' => $trimmedTag
                    ];
                }
            }
        }

        return $this->successed(datas: ['data' => $options]);
    }

    public function getVirtualData(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $vQuery = $request->input('query');
        $page = $request->input('page', 1);

        if (empty($vQuery)) return $this->failed(msg: "Parameters Missing");

        $query = Design::query();
        $query->whereStatus(1);
        $limit = QueryManager::applyConditionToQuery($query, explode(' && ', $vQuery), 50);

        $itemData = $query->paginate($limit, ['*'], 'page', $page);

        $rates = RateController::getRates();

        $item_rows = [];
        foreach ($itemData->items() as $item) {
            $item_rows[] = HelperController::getItemData($this->uid, null, $item, json_decode($item->thumb_array), catLink: HelperController::$webPageUrl . "templates/p/$item->id_name", rates: $rates);
        }

        $responsePayload = [
            "templateCount" => HelperController::getTemplateCount($itemData->total(), ""),
            "datas" => $item_rows,
            "pagination" => PaginationController::getPagination($itemData),
        ];

        return $this->successed(datas: ['data' => $responsePayload]);
    }
}

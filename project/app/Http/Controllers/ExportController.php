<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Jobs\ExportDesignCampaignController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\PlanLimitHelper;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Draft;
use App\Models\ExportTable;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExportController extends ApiController
{

    function export(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $oldIds = $request->get('ids');
        if (is_null($oldIds)) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Ids are missing"));

        $draftRes = [];
        if ($request->get('s', 0) == 1) {
            $draftController = new DraftController($request);
            $draftRes = $draftController->saveData($request, true);
            if (!$draftRes['success']) return ResponseHandler::sendEncryptedResponse($request, $draftRes);
        }

        $ids = CryptoJsAes::decrypt($oldIds, $this->aesPassword);
        $response = PaymentController::getRatesOfTemplates($request, $this->uid, $ids, false);

        if (!$response['success']) return ResponseHandler::sendEncryptedResponse($request, $response);

        $returnVal = ResponseHandler::sendResponse($request, new ResponseInterface($response["statusCode"], $response["success"], $response["msg"], array_merge($response["data"] ?? [], $draftRes)));
        if ($response['data']['needToPurchase']) return $returnVal;

        $addRes = $this->addNew($request, $oldIds, true, false);

        if (!$addRes['success']) {
            return ResponseHandler::sendEncryptedResponse($request, $response);
        }

        return ResponseHandler::sendEncryptedResponse($request, $addRes);
    }

    function add(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");
        if ($request->has('ids')) return $this->export($request);
        return $this->addNew($request, appliedOffer: false);
    }

//    function add(Request $request): array|string
//    {
//        if ($request->has('ids')) return $this->export($request);
//        if ($request->has('pages')) return $this->addNew($request);
//
//        if ($this->isFakeRequest($request)) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
//
//        $fileName = $request->get('name');
//        $original_name = $request->get('original_name');
//        $fileOutput = $request->get('path');
//        $watermark = $request->get('watermark', 0);
//
//        if (is_null($fileName) || is_null($original_name) || is_null($fileOutput)) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing"));
//        }
//
//        $res = new ExportTable();
//        $res->uid = $this->uid;
//        $res->name = $fileName;
//        $res->original_name = $original_name;
//        $res->path = $fileOutput;
//        $res->watermark = $watermark;
//        $res->save();
//
//        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "done",
//            [
//                'data' => $res->id
//            ]
//        ));
//    }

    function addNew(Request $request, $ids = null, bool $showDecoded = false, $appliedOffer = true): array|string
    {
        $keyArrays = ["type", "sm", "qu", "tf", "cf", "pages"];

        foreach ($keyArrays as $key) {
            if (!request()->has($key)) return $this->failed(msg: "Parameters missing", showDecoded: $showDecoded);
        }

        $name = $request->input('n') ?? $request->input('name') ?? "Untitled";
        $idOrDesign = $request->input('i') ?? $request->input('d');
        $type = filter_var($request->input('type'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $sizeMultiplier = filter_var($request->input('sm'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $quality = filter_var($request->input('qu'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $transparentFile = filter_var($request->input('tf'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $compressFile = filter_var($request->input('cf'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $pages = filter_var($request->input('pages'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $watermark = $request->get('watermark', 0);

        $ids = $request->get('dIds', $ids);
        if (is_null($ids)) return $this->failed(msg: "Ids are missing");
        $ids = CryptoJsAes::decrypt($ids, $this->aesPassword);

        $response = PaymentController::getRatesOfTemplates($request, $this->uid, $ids, $appliedOffer);
        if (!$response['success']) {
            if ($showDecoded) return ResponseHandler::sendRealResponse($response);
            else return ResponseHandler::sendEncryptedResponse($request, $response);
        }

        $currency = $response['data']['currency'];
        $amount = $response['data']['total'];
        $needToPurchase = $response['data']['needToPurchase'];
        $goWithWatermark = $response['data']['goWithWatermark'];

        if (!is_numeric($sizeMultiplier) || !is_numeric($pages) || !is_bool($transparentFile) || !is_bool($compressFile) || is_null($idOrDesign)) {
            return $this->failed(msg: "Params are invalid", showDecoded: $showDecoded);
        }

        if (!is_numeric($type) || $type < 1 || $type > 4) {
            return $this->failed(msg: "Type is invalid", showDecoded: $showDecoded);
        }

        if (!is_numeric($quality) || $quality < 0 || $quality > 100) {
            return $this->failed(msg: "Quality is invalid", showDecoded: $showDecoded);
        }

        if ($this->uid == $this->testingUid) {
//            return $this->successed(msg: "Done", showDecoded: $showDecoded);
        }

        if (str_starts_with($idOrDesign, "{")) $idOrDesign = CryptoJsAes::decrypt($idOrDesign, $this->aesPassword);
        $draft_data = Draft::where("string_id", $idOrDesign)->where('user_id', $this->uid)->where('trashed', 0)->where('deleted', 0)->first();

        if (!$draft_data) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Invalid data"), showDecoded: $showDecoded);
        }

        $planData = SubscriptionController::getActivePlan($this->uid);
        $exportLimit = PlanLimitHelper::getPlanLimit(
            request: $request,
            type: 'download_limit',
            uid: $this->uid,
            needToPurchase: $needToPurchase,
            goWithWatermark: $goWithWatermark,
            planData: $planData
        );
        if ($exportLimit['excessed']) {
            return $this->successed(msg: $exportLimit['msg'], datas: ['limit_exceeded' => $exportLimit], showDecoded: $showDecoded);
        }

        $videoLeft = UserController::getUsersVideoLimit($this->uid);

        if ($videoLeft['left'] > $videoLeft['limit']) {
            return $this->failed(msg: "Video limit exceeded", showDecoded: $showDecoded);
        }

        $res = new ExportTable();
        $res->uid = $this->uid;
        $res->crafty_id = ExportTable::generateCraftyId();
        $res->name = $name;
        $res->original_name = $name;
        $res->path = $idOrDesign;
        $res->watermark = $watermark;
        $res->ids = $ids;
        $res->currency = $currency;
        $res->amount = $amount;
        $res->save();

        if ($watermark == 1) ExportDesignCampaignController::instantSend($res->id);

        $response = [];

//        if (!$planData) {
        $response['recommended'] = $watermark == 1 ? null : PlanLimitHelper::recommendedPlan($request);
//        }

        return $this->successed(msg: "Done", datas: $response, showDecoded: $showDecoded);
    }

    function update(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $id = $request->get('id');

        $res = ExportTable::find($id);
        if (!$res) return $this->failed(msg: "Parameters missing");

        $res->total = $res->total + 1;

        $res->save();

        return $this->successed(datas: ['data' => $res->id]);
    }

    function get(Request $request): array|string
    {
        $name = $request->get('name');

        $res = ExportTable::where('name', $name)->first();

        if (!$res) return $this->failed(statusCode: 404, msg: "File is not exist", showDecoded: true);

        $creationTime = strtotime($res->created_at);
        $currentTime = time();
        $timeDifference = $currentTime - $creationTime;

        if ($timeDifference > 24 * 3600) {
            return $this->failed(statusCode: 400, msg: "Error: File link has expired", showDecoded: true);
        }

        $res->total = $res->total + 1;

        $res->save();

        return $this->successed(datas: ['path' => $res->path], showDecoded: true);
    }


    private function getFileExt(int $type, int $length = 0): string
    {
        if ($type === 1) {
            return $length > 1 ? 'zip' : 'jpg';
        } elseif ($type === 2) {
            return $length > 1 ? 'zip' : 'png';
        } elseif ($type === 3) {
            return 'pdf';
        } else {
            return 'mp4';
        }
    }

    public function getFreeExportLimit2($uid, $needToPurchase, $goWithWatermark): array
    {
        $planData = SubscriptionController::getActivePlan($uid);
        if ($planData) {
            $appearance = $planData->plan_limit;
            $exportObject = collect($appearance)->where('slug', 'download_limit')->first();

            $today = Carbon::today();
            $used = ExportTable::whereUid($uid)->whereDate('created_at', $today)->count();

            if ($exportObject && $exportObject['limit'] !== -1) {

                $isLifeTime = $exportObject["access_mode"] === "lifetime";
                $msg = "You have exceeded today's export limit";

                if ($isLifeTime) {
                    $used = ExportTable::whereUid($uid)->whereBetween('created_at', [$planData->created_at, $planData->expired_at])->count();
                    $msg = "You have exceeded export limit";
                }

                return [
                    "limit" => $exportObject['limit'],
                    "used" => $used,
                    "left" => max(0, $exportObject['limit'] - $used),
                    "msg" => $msg,
                    "excessed" => $used >= $exportObject['limit']
                ];

            } else {
                return [
                    "limit" => -1,
                    "used" => $used,
                    "left" => "unlimited",
                    "msg" => "You have exceeded today's free template limit",
                    "excessed" => false
                ];
            }
        } else if ($needToPurchase && $goWithWatermark) {

            $used = ExportTable::where('uid', $uid)->where('watermark', 1)->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count();
            return [
                "limit" => 1,
                "used" => $used,
                "left" => max(0, $used - 1),
                "msg" => "You have exceeded today's free template limit",
                "excessed" => $used >= 1
            ];

        } else {
            $used = ExportTable::where('uid', $uid)->where('watermark', 1)->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count();
            return [
                "limit" => -1,
                "used" => $used,
                "left" => "unlimited",
                "msg" => "You have exceeded today's free template limit",
                "excessed" => false
            ];
        }
    }

    public function getFreeExportLimit($uid, $needToPurchase, $goWithWatermark): array
    {
        $planData = SubscriptionController::getActivePlan($uid);

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        if ($planData) {
            $planLimit = collect($planData->plan_limit);
            $exportObject = $planLimit->where('slug', 'download_limit')->first();

            $used = ExportTable::whereUid($uid)
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count();

            if (!$exportObject || $exportObject['limit'] == -1) {
                return [
                    "limit" => -1,
                    "used" => $used,
                    "left" => "unlimited",
                    "msg" => "Unlimited exports",
                    "excessed" => false
                ];
            }

            $deviceLimitData = $planLimit->where('slug', 'device_limit')->first();
            $seats = $deviceLimitData['limit'] ?? 1;

            $msg = "You have exceeded today's export limit";
            if ($exportObject["access_mode"] === "lifetime") {
                $used = ExportTable::whereUid($uid)->whereBetween('created_at', [$planData->created_at, $planData->expired_at])->count();
                $msg = "You have exceeded export limit";
            }

            $limit = $exportObject['limit'] * $seats;

            return [
                "limit" => $limit,
                "used" => $used,
                "left" => max(0, $limit - $used),
                "msg" => $msg,
                "excessed" => $used >= $limit
            ];

        }

        $used = ExportTable::where('uid', $uid)
            ->where('watermark', 1)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        if ($needToPurchase) {
            $limit = 0;
            if ($goWithWatermark) $limit = 1;
            return [
                "limit" => $limit,
                "used" => $used,
                "left" => max(0, $limit - $used),
                "msg" => "You have exceeded today's free template limit",
                "excessed" => $used >= $limit
            ];
        }

        return [
            "limit" => -1,
            "used" => $used,
            "left" => "unlimited",
            "msg" => "Unlimited free exports with watermark",
            "excessed" => false
        ];
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Jobs\ExportDesignCampaignController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
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

        return $returnVal;
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

        if ($watermark == 1) {
            $freeLeft = $this->getFreeExportLimit($this->uid);
            if ($freeLeft['left'] >= $freeLeft['limit']) {
                return $this->successed(msg: "You have exceeded today's free template limit", datas: ['limit_exceeded' => true], showDecoded: $showDecoded);
            }
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

        return $this->successed(msg: "Done", showDecoded: $showDecoded);
    }

    function update(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get('id');

        $res = ExportTable::find($id);
        if (!$res) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing"));
        }

        $res->total = $res->total + 1;

        $res->save();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "done",
            [
                'data' => $res->id
            ]
        ));
    }

    function get(Request $request): array|string
    {
        $name = $request->get('name');

        $res = ExportTable::where('name', $name)->first();

        if (!$res) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, "File is not exist"));
        }

        $creationTime = strtotime($res->created_at);
        $currentTime = time();
        $timeDifference = $currentTime - $creationTime;

        if ($timeDifference > 24 * 3600) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(400, false, "Error: File link has expired"));
        }

        $res->total = $res->total + 1;

        $res->save();

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, "",
            [
                'path' => $res->path
            ]
        ));
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

    public function getFreeExportLimit($uid): array
    {
        return [
            "limit" => 1,
            "left" => ExportTable::where('uid', $uid)->where('watermark', 1)->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->count()
        ];
    }

}

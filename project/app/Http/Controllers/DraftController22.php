<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\DomainChecker;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\EditorVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Models\Design;
use App\Models\Draft;
use App\Models\UserData;
use App\Models\PurchaseHistory;
use Carbon\Carbon;

class DraftController extends ApiController
{
    private string $url = 'https://assets.craftyart.in/';
    private array $defaultFrame__ = [
        'name' => 'Untitled design',
        'frame' => [
            'width' => 1080,
            'height' => 1080,
        ],
        'pages' => [
            [
                'pageId' => "Str::random(20)",
                'name' => "Page 1",
                'objects' => [
                    [
                        'id' => "Str::random(20)",
                        'angle' => 0,
                        'stroke' => null,
                        'strokeWidth' => 0,
                        'left' => 0,
                        'top' => 0,
                        'width' => 1080,
                        'height' => 1080,
                        'opacity' => 1,
                        'originX' => "left",
                        'originY' => "top",
                        'scaleX' => 1,
                        'scaleY' => 1,
                        'type' => "Background",
                        'flipX' => false,
                        'flipY' => false,
                        'skewX' => 0,
                        'skewY' => 0,
                        'visible' => true,
                        'fill' => "#ffffff",
                        'src' => "",
                        'metadata' => [],
                    ]
                ]
            ]
        ]
    ];

    function getPosterDetail(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $template_id = $request->get('i');
        if ($template_id == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Parameters missing"));
        }

        $defaultFrame = $this->defaultFrame__;
        $uid = $this->uid;

        $user_data = UserData::where("uid", $uid)->first();

        SubscriptionController::getActivePlan($uid);

        if ($request->has('fetch') && $user_data) {
            if ($uid == $this->testingUid) {
                $draft_data = Draft::where("string_id", $template_id)->first();
            } else {
                $draft_data = Draft::where("string_id", $template_id)->where('user_id', $uid)->where('trashed', 0)->where('deleted', 0)->first();
            }

            if ($draft_data) {

                $jsonData = json_decode($draft_data->designs);
                if ($jsonData) {
                    $frame = $jsonData;
                } else {
                    $frame = json_decode(StorageUtils::get($draft_data->designs));
                }

                while (is_string($frame)) {
                    $frame = json_decode($frame);
                }

                return ResponseHandler::sendResponse($request, new ResponseInterface(
                    200,
                    true,
                    "Loaded",
                    [
                        'data' => JSONUtils::applyPageStringId($frame, $draft_data->template_id),
                        'thumb' => null
                    ]
                ));
            } else {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Bad Request"));
            }
        }
        try {

            if (Str::endsWith($template_id, '%3D')) {
                $template_id = str_replace("%3D", "", $template_id);
            }

            $json = json_decode(base64_decode($template_id));
            $w = isset($json->w) ? (int)$json->w : null;
            $h = isset($json->h) ? (int)$json->h : null;

            $minSize = 40;
            $maxSize = 4000;

            if ($w && $h) {
                if ($user_data) {

                    if (
                        $h < $minSize ||
                        $h > $maxSize ||
                        $w < $minSize ||
                        $w > $maxSize
                    ) {
                        return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "width and height must be between " . $minSize . " and " . $maxSize . "."));
                    }

                    $string_id = HelperController::generateID('', 30);
                    while (Draft::where('string_id', $string_id)->exists()) {
                        $string_id = HelperController::generateID('', 30);
                    }

                    $defaultFrame['frame']['width'] = $w;
                    $defaultFrame['frame']['height'] = $h;
                    $defaultFrame['pages'][0]['pageId'] = HelperController::generateID('', 20);
                    $defaultFrame['pages'][0]['objects'][0]['id'] = HelperController::generateID('', 20);
                    $defaultFrame['pages'][0]['objects'][0]['width'] = $w;
                    $defaultFrame['pages'][0]['objects'][0]['height'] = $h;

                    $bytes = random_bytes(20);
                    $new_name = bin2hex($bytes) . Carbon::now()->timestamp;
                    $filePath = 'uploadedFiles/drafts/' . $new_name . '.json';
                    StorageUtils::put($filePath, json_encode($defaultFrame));

                    $image = Image::canvas(500, ($h / $w) * 500, "#ffffff");
                    $bytes = random_bytes(20);
                    $new_name = 'thumbs/' . bin2hex($bytes) . Carbon::now()->timestamp . '.jpg';
                    Storage::disk('cloudflare_r2')->put($new_name, $image->encode('jpg'));

                    $imageArray = [];
                    $imageArray[] = $this->url . $new_name;


                    $tempRatio = $w / $h;
                    $tempRatio = round($tempRatio, 2);

                    $res = new Draft();
                    $res->string_id = $string_id;
                    $res->user_id = $uid;
                    $res->name = $defaultFrame['name'];
                    $res->ratio = $tempRatio;
                    $res->width = $w;
                    $res->height = $h;
                    $res->thumbs = json_encode($imageArray);
                    $res->designs = $filePath;
                    $res->is_premium = 0;
                    $res->save();

                    DraftController::doEditorVisit($request, $this->uid, $template_id, $string_id);

                    return ResponseHandler::sendResponse($request, new ResponseInterface(
                        200,
                        true,
                        "Loaded",
                        [
                            'data' => JSONUtils::applyPageStringId($defaultFrame, 'draft'),
                            'ul' => $string_id,
                            'is_purchased' => 0,
                            'thumb' => DesignerDraftController::getDesignThumb($res->thumbs)
                        ]
                    ));
                } else {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Not valid"));
                }
            } else {
                return $this->getDraft($request);
            }

        } catch (\Exception $e) {
            return $this->getDraft($request);
        }
    }

    private function getDraft(Request $request): array|string
    {
//        $isBetaEditor = DomainChecker::isBetaEditor($request);
        $isBetaEditor = true;

        $uid = $this->uid;
        $template_id = $request->get('i');

        $string_id = $template_id;

        $user_data = UserData::where("uid", $uid)->first();

        $draft_data = FabricJsController::getPosterDetail($request, true);

        $fromDraft = false;
        $is_purchased = 0;

        $isSubActive = SubscriptionController::getActivePlan($uid);

        if (!$draft_data) {
            if ($uid == $this->testingUid || ($user_data && $user_data->hoc === 1) || $isSubActive) {
                $draft_data = Draft::where("string_id", $template_id)->first();
            } else {
                $draft_data = Draft::where("string_id", $template_id)->where('user_id', $uid)->where('trashed', 0)->where('deleted', 0)->first();
            }
            if (!$draft_data) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Incorrect Data!"));
            }


            $jsonData = json_decode(StorageUtils::get($draft_data->designs));
            if (is_string($jsonData)) {
                $jsonData = json_decode($jsonData);
            }
            if (!DesignerDraftController::checkJsonIsValid($jsonData)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, 'Invalid data 1'));
            }

            $templateStringId = $draft_data->template_id;
            $is_purchased = $draft_data->is_premium;
            $thumbs = $draft_data->thumbs;
            $fromDraft = true;
        } else {

            $fieldName = is_numeric($template_id) ? "id" : "id_name";
            $itemData = Design::where($fieldName, $template_id)->first();

            if ($itemData == null && !is_numeric($template_id)) {
                $itemData = Design::where('string_id', $template_id)->first();
                if ($itemData == null && !is_numeric($template_id)) {
                    $parts = explode("-", $template_id);
                    $firstWord = $parts[0];
                    $itemData = Design::where('string_id', $firstWord)->first();
                }
            }

            if (!$itemData) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Incorrect Data!"));
            }

            if ($user_data) {

                $isExists = PurchaseHistory::where('user_id', $user_data->uid)->where('product_id', $itemData->string_id)->where('product_type', 0)->exists();

                $special_user = DomainChecker::isValidSpecialUser($request, $user_data);

                $isPremiumUser = $user_data->is_premium === 1 || $special_user === 1 || $isExists || ($itemData->is_premium === 0 && $itemData->is_freemium === 1);

                if (!$isPremiumUser) {
                    if (!$isBetaEditor) {
                        return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Incorrect Data!"));
                    }
                }

                $itemData->is_premium = ($user_data->is_premium === 1 || $special_user === 1 || $isExists) ? 1 : 0;

                $string_id = HelperController::generateID('', 30);
                while (Draft::where('string_id', $string_id)->exists()) {
                    $string_id = HelperController::generateID('', 30);
                }

                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp;

                $filePath = 'uploadedFiles/drafts/' . $new_name . '.json';
                StorageUtils::put($filePath, json_encode($draft_data));

                $thumbObj = json_decode($itemData->thumb_array);

                $fldr_str = $user_data->fldr_str;
                if ($fldr_str == null) {
                    $fldr_str = HelperController::generateID('', 10);
                    while (UserData::where('fldr_str', $fldr_str)->exists()) {
                        $fldr_str = HelperController::generateID('', 10);
                    }
                    UserData::where('id', $user_data->id)->update(array('fldr_str' => $fldr_str));
                }

                $thumbArray = [];
                foreach ($thumbObj as $thumb) {
                    $bytes = random_bytes(20);
                    $dir = 'temp/u/' . $fldr_str . '/';
                    $new_name = $dir . bin2hex($bytes) . Carbon::now()->timestamp . '.jpg';
                    Storage::disk('cloudflare_r2')->put($new_name, StorageUtils::get($thumb));
                    $thumbArray[] = $this->url . $new_name;
                }

                $res = new Draft();
                $res->string_id = $string_id;
                $res->user_id = $uid;
                $res->name = $itemData->post_name;
                $res->ratio = $itemData->ratio;
                $res->width = $itemData->width;
                $res->height = $itemData->height;
                $res->thumbs = json_encode($thumbArray);
                $res->designs = $filePath;
                $res->is_premium = $itemData->is_premium;
                $res->template_id = $itemData->string_id;
                $res->save();

                $res = Design::find($itemData->id);
                $res->web_views = $res->web_views + 1;
                $res->trending_views = $res->trending_views + 1;
                $res->save();

                $is_purchased = $itemData->is_premium;

            } else {
                if (!$isBetaEditor) {
                    if ($itemData->is_premium === 1) {
                        $draft_data = null;
                    }
                }
            }

            $templateStringId = $itemData->string_id;
            $thumbs = $itemData->thumb_array;

            DraftController::doEditorVisit($request, $this->uid, $itemData->id, $string_id);

        }

        if ($draft_data) {

            if (!$fromDraft) {
                $frame = $draft_data;
            } else {
                $jsonData = json_decode($draft_data->designs);
                if ($jsonData) {
                    $frame = $jsonData;
                } else {
                    $frame = json_decode(StorageUtils::get($draft_data->designs));
                }
            }

            while (is_string($frame)) {
                $frame = json_decode($frame);
            }

            return ResponseHandler::sendResponse($request, new ResponseInterface(
                200,
                true,
                "Loaded",
                [
                    'data' => JSONUtils::applyPageStringId($frame, $templateStringId, !$fromDraft),
                    'ul' => $string_id,
                    'is_purchased' => $is_purchased,
                    'thumb' => DesignerDraftController::getDesignThumb($thumbs)
                ]
            ));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Incorrect Data!"));
        }
    }

    function saveData(Request $request, bool $showDecoded = false): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"), showDecoded: $showDecoded);
        }

        $uid = $this->uid;
        $post_name = $request->get('n');
        $draft_id = $request->get('i');
        $draft_save_data = $request->get('d');
        $draft_thumbs = $request->file('t');

        if ($draft_id == null || $draft_save_data == null || $draft_thumbs == null || $post_name == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(502, false, "Parameters Missing!", ['type' => 0]), showDecoded: $showDecoded);
        }

        $draft_id = CryptoJsAes::decrypt($draft_id, $this->aesPassword);
        $draft_save_data = CryptoJsAes::decrypt($draft_save_data, $this->aesPassword);

        $user_data = UserData::where("uid", $uid)->first();

        if ($this->isTester() || ($user_data->hoc === 1)) {
            if (!Draft::where("string_id", $draft_id)->where('user_id', $uid)->exists()) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(502, false, "Error"), showDecoded: $showDecoded);
            }
        }

        $draft_thumbs = is_array($draft_thumbs) ? $draft_thumbs : [$draft_thumbs];

        $draft_data = Draft::where("string_id", $draft_id)->where('user_id', $uid)->first();

        $fldr_str = $user_data->fldr_str;

        if ($fldr_str == null) {
            $fldr_str = HelperController::generateID('');
            while (UserData::where('fldr_str', $fldr_str)->exists()) {
                $fldr_str = HelperController::generateID('');
            }
            UserData::where('id', $user_data->id)->update(array('fldr_str' => $fldr_str));
        }

        if (!$draft_data) {

            $fieldName = is_numeric($draft_id) ? "id" : "id_name";
            $itemData = Design::where($fieldName, $draft_id)->first();

            if ($itemData == null && !is_numeric($draft_id)) {
                $itemData = Design::where('string_id', $draft_id)->first();
                if ($itemData == null) {
                    $parts = explode("-", $draft_id);
                    $firstWord = $parts[0];
                    $itemData = Design::where('string_id', $firstWord)->first();
                }
            }

            if ($itemData) {

                $string_id = HelperController::generateID('', 30);
                while (Draft::where('string_id', $string_id)->exists()) {
                    $string_id = HelperController::generateID('', 30);
                }

                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp;

                $filePath = 'uploadedFiles/drafts/' . $new_name . '.json';
                StorageUtils::put($filePath, $draft_save_data);

                $thumbArray = [];

                foreach ($draft_thumbs as $file) {
                    $bytes = random_bytes(20);
                    $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
                    $dir = 'temp/u/' . $fldr_str . '/';
                    $file->storeAs($dir, $new_name, 'cloudflare_r2');
                    $thumbArray[] = $this->url . $dir . $new_name;
                }

                $itemData->is_premium = 1;

                $res = new Draft();
                $res->string_id = $string_id;
                $res->user_id = $uid;
                $res->name = $itemData->post_name;
                $res->ratio = $itemData->ratio;
                $res->width = $itemData->width;
                $res->height = $itemData->height;
                $res->thumbs = json_encode($thumbArray);
                $res->designs = $filePath;
                $res->is_premium = $itemData->is_premium;
                $res->template_id = $itemData->string_id;
                $res->save();
                return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", ['type' => 0, 'ul' => $string_id, 'is_purchased' => $itemData->is_premium]), showDecoded: $showDecoded);
            } else {
                return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Incorrect Data!", ['type' => 1]), showDecoded: $showDecoded);
            }
        } else {
            if ($draft_data->trashed === 1 || $draft_data->deleted === 1) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Incorrect Data!", ['type' => 1]), showDecoded: $showDecoded);
            } else {

                $thumbArray = [];

                foreach ($draft_thumbs as $file) {
                    $bytes = random_bytes(20);
                    $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
                    $dir = 'temp/u/' . $fldr_str . '/';
                    $file->storeAs($dir, $new_name, 'cloudflare_r2');
                    $thumbArray[] = $this->url . $dir . $new_name;
                }

                $jsonData = json_decode($draft_save_data);

                $oldPath = $draft_data->designs;
                $jsonFilePath = 'uploadedFiles/drafts/' . StorageUtils::getNewName() . '.json';
                StorageUtils::put($jsonFilePath, $draft_save_data);

                $success = Draft::where('string_id', $draft_id)->where('user_id', $uid)->update(array('name' => $jsonData->name, 'thumbs' => json_encode($thumbArray), 'designs' => $jsonFilePath));

                if ($success) StorageUtils::delete($oldPath);

                return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", ['type' => 0, 'ul' => $draft_data->string_id, 'is_purchased' => $draft_data->is_premium]), showDecoded: $showDecoded);
            }
        }

    }

    function getDrafts(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $uid = $this->uid;
        $type = $request->get('type');
        $page = $request->has('page') ? $request->get('page') : 1;

        if ($type === null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Parameters Missing!"));
        }

        $limit = HelperController::getPaginationLimit(size: 30);

        $draftQuery = Draft::query();

//        if ($uid != $this->testingUid) {
        $draftQuery->where('user_id', $uid)->where("trashed", $type)->where("deleted", 0);
//        } else {
//            $draftQuery->whereDate('created_at', '2024-10-19');
//        }


        $draftData = $draftQuery->orderBy('created_at', 'DESC')->paginate($limit, ['*'], 'page', $page);

        $draft_rows = [];

        foreach ($draftData->items() as $draft) {
            $draft_rows[] = array(
                'id' => $draft->string_id,
                'name' => $draft->name,
                'width' => $draft->width,
                'height' => $draft->height,
                'thumbs' => json_decode($draft->thumbs)
            );
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", ['datas' => $draft_rows, 'isLastPage' => $page >= $draftData->lastPage()]));
    }

    function modifiedDraft(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $uid = $this->uid;
        $draft_id = $request->get('id');
        $type = $request->get('type');

        if ($draft_id === null || $type === null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Parameters Missing!"));
        }

        if ($this->uid == $this->testingUid) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }

        if ($type == 0 || $type == '0') {
            $updateData = ['trashed' => 1];
            $condition = ['deleted' => 0];
        } elseif ($type == 1 || $type == '1') {
            $updateData = ['trashed' => 0];
            $condition = ['deleted' => 0];
        } elseif ($type == 2 || $type == '2') {
            $updateData = ['deleted' => 1];
            $condition = ['trashed' => 1];
        } else {
            $response['message'] = 'Invalid type';
            return $response;
        }

        $draft_ids = is_array($draft_id) ? $draft_id : [$draft_id];

        $success = Draft::whereIn('string_id', $draft_ids)->where('user_id', $uid)->where($condition)->update($updateData);

        if (!$success) {
            $msg = 'Invalid request';
        } else {
            $msg = 'Done';
            if ($type == 0 || $type == '0') {
                $msg = 'Moved to trash';
            } elseif ($type == 1 || $type == '1') {
                $msg = 'Restore successfully';
            } elseif ($type == 2 || $type == '2') {
                $msg = 'Deleted successfully';
            }
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface($success ? 200 : 401, (bool)$success, $msg));
    }

    function renameDraft(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $draft_id = $request->id;
        $name = $request->name;

        if (is_null($draft_id) || is_null($name)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        if ($this->uid == $this->testingUid) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }

        $draft_data = Draft::where('string_id', $draft_id)->where('user_id', $this->uid)->first();

        if (!$draft_data) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid data 2"));
        }

        $updateData = ['name' => $name];
        $success = Draft::where('string_id', $draft_id)->where('user_id', $this->uid)->update($updateData);

        if ($success) {
            $frame = json_decode(StorageUtils::get($draft_data->designs));
            $frame->name = $name;

            $jsonFilePath = 'uploadedFiles/drafts/' . StorageUtils::getNewName() . '.json';
            StorageUtils::put($jsonFilePath, json_encode($frame));
            StorageUtils::delete($draft_data->designs);

            Draft::where('string_id', $draft_id)->where('user_id', $this->uid)->update(['designs' => $jsonFilePath]);

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Renamed successfully"));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }
    }

    function copyDraft(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $draft_id = $request->id;

        if (is_null($draft_id)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        if ($this->isTester()) {
            $draftData = Draft::where('string_id', $draft_id)->first();
        } else {
            $draftData = Draft::where('string_id', $draft_id)->where('user_id', $this->uid)->first();
        }

        if (!$draftData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid data 3"));
        }

        $singleDataRow = SubscriptionController::getActivePlan($this->uid);

        if (!$singleDataRow) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(456, false, "Subscription required"));
        }

        $string_id = HelperController::generateID('', 30);
        while (Draft::where('string_id', $string_id)->exists()) {
            $string_id = HelperController::generateID('', 30);
        }

        $res = new Draft();
        $res->string_id = $string_id;
        $res->user_id = $this->isTester() ? $this->uid : $draftData->user_id;
        $res->name = $draftData->name;
        $res->ratio = $draftData->ratio;
        $res->width = $draftData->width;
        $res->height = $draftData->height;
        $res->thumbs = $draftData->thumbs;

        $filePath = $draftData->designs;
        $frame = json_decode(StorageUtils::get($filePath));
        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp;
        $newFilePath = 'uploadedFiles/drafts/' . $new_name . '.json';
        StorageUtils::put($newFilePath, json_encode($frame));

        $res->designs = $newFilePath;
        $res->is_premium = $draftData->is_premium;
        $res->save();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Copied successfully", ['id' => $string_id]));

    }

    public static function doEditorVisit(Request $request, $uid, $pid, $did): void
    {
        $ipData = HelperController::getIpAndCountry($request);
        $userIp = $ipData['ip'];
        EditorVisit::create([
            'uid' => $uid,
            'pid' => $pid,
            'draft_id' => $did,
            'ip_address' => $userIp == '89.116.134.215' ? null : $userIp,
            'country' => $ipData['cn'],
            'fbc' => $request->cookie('_fbclid'),
            'fbp' => $request->cookie('_caid'),
        ]);
    }
}


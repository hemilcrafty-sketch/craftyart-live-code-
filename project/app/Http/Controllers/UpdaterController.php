<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Design;
use App\Models\UserData;

use Carbon\Carbon;

class UpdaterController extends ApiController
{
    private array $allowedExt = ['jpeg', 'jpg', 'webp'];

    private string $url = 'https://assets.craftyart.in/';

    function get(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $user_data = UserData::where("uid", $this->uid)->first();

        if (!$user_data || $user_data->web_update != 1) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Not approved"));
        }

        $id = $request->get('i');

        if ($id) {
            $deData = FabricJsController::getPosterDetail($request, true, true, true);
            if ($deData) {
                $deData['data'] = JSONUtils::applyPageStringId($deData['data'], $deData['string_id']);
                return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "done",
                    [
                        'data' => $deData['data'],
                        'ul' => $deData['id'] . '',
                    ]
                ));
            } else {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Not found"));
            }
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Not found"));
        }
    }

    function update(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $data = $request->get('data');
        $id = $request->get('id');
        $draft_thumbs = $request->file('t');
        $video = $request->file('file');

        $user_data = UserData::where("uid", $this->uid)->first();

        if (!$user_data || $user_data->web_update != 1) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Not approved"));
        }

        foreach ($draft_thumbs as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($fileExtension), $this->allowedExt)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, 'Invalid file'));
            }
        }

        $data = CryptoJsAes::decrypt($data, $this->aesPassword);
        if (!$data || !$id) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Template not found"));
        }

        $designData = json_decode($data);
        if (!$designData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Template not found"));
        }

        $res = Design::find($id);

        if (!$res) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Template not found"));
        }

        if ($video && $video->getClientOriginalExtension() !== 'mp4') {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Video file is missing"));
        }

        if (!$draft_thumbs && $res->creator_id !== null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Thumbs missing"));
        }

        $response = [];

        foreach ($designData->pages as $pages) {

            foreach ($pages->objects as $layer) {
                if (isset($layer->clipSvg) && $layer->clipSvg && Str::startsWith($layer->clipSvg, '<svg') && Str::endsWith($layer->clipSvg, '</svg>')) {

                    $new_name = 'e/s/' . StorageUtils::getNewName() . '.svg';
                    Storage::disk('cloudflare_r2')->put($new_name, $layer->clipSvg);

                    $layer->clipSvg = $this->url . $new_name;
                }
            }
        }

        $videoFile = null;
        if ($video) {
            $new_name = StorageUtils::getNewName() . '.' . $video->getClientOriginalExtension();
            StorageUtils::storeAs($video, 'uploadedFiles/v', $new_name);
            $videoFile = 'uploadedFiles/v/' . $new_name;
        }

        StorageUtils::delete($res->video_thumb);

        if ($draft_thumbs && $res->creator_id !== null) {
            $oldThumbArray = json_decode($res->thumb_array);
            $thumbArray = [];
            $thumbIndex = 0;
            foreach ($draft_thumbs as $file) {
                $thumbPath = $oldThumbArray[$thumbIndex] ?? 'uploadedFiles/thumb_file/' . StorageUtils::getNewName() . '.' . $file->getClientOriginalExtension();
                StorageUtils::delete($thumbPath);
                StorageUtils::storeAs($file, dirname($thumbPath), basename($thumbPath));
                $thumbArray[] = $thumbPath;
                $thumbIndex++;
            }
            $res->post_thumb = $thumbArray[0];
            $res->thumb_array = json_encode($thumbArray);
        }

        $oldPath = $res->fab_designs;

        $new_name = StorageUtils::getNewName() . '.json';
        $filePath = 'uploadedFiles/fab_designs/' . $new_name;

        if ($res->fab_designs == null || !StorageUtils::exists($res->fab_designs)) {
            $response['restart'] = true;
        }

        $res->fab_designs = $filePath;
        StorageUtils::put($filePath, json_encode($designData));

        $res->video_thumb = $videoFile;
        $res->animation = $videoFile != null ? 1 : 0;
        $res->is_fix = 1;
        $res->save();

        StorageUtils::delete($oldPath);

//        if ($this->isTester()) {
//            $datas = Design::where('post_thumb', 'like', '%uploadedFiles/thumb_file/uploadedFiles%')->get();
//
//            foreach ($datas as $data) {
//
//                $oldThumbArray = json_decode($data->thumb_array);
//                $thumbArray = [];
//                foreach ($oldThumbArray as $oldThumb) {
//                    $name = basename($oldThumb);
//                    $newPath = 'uploadedFiles/thumb_file/' . $name;
//                    StorageUtils::put($oldThumb, $newPath);
//                    $thumbArray[] = $newPath;
//                }
//
//                Design::where('id', $data->id)->update(array('post_thumb' => $thumbArray[0], 'thumb_array' => json_encode($thumbArray)));
//            }
//        }

        DB::table('web_update_history')->insert([
            'user_id' => $this->uid,
            'template_id' => $id,
            'animated' => $videoFile != null ? 1 : 0
        ]);

        $response['fileLink'] = null;
        if ($videoFile != null) {
            $response['fileLink'] = HelperController::$mediaUrl . $videoFile;
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Updated successfully', $response));
    }
}

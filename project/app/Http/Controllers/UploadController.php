<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use App\Http\Controllers\Utils\AudioVideoManager;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Uploads;
use App\Models\UserData;

use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\File;

class UploadController extends ApiController
{

    private string $url = 'https://assets.craftyart.in/';

    public static int $aspectWidth = 150;
    public static int $aspectHeight = 150;
    private int $totalNormalStorageLimit = 104857600;
    private int $totalPremiumStorageLimit = 1073741824;

    const ASSET_TYPES = [
        0 => 'image',
        1 => 'gif',
        2 => 'svg',
        3 => 'video',
        4 => 'audio'
    ];

    function uploadDatas(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $assetType = 0;
        $files = $request->file('files');
        if ($files == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        $user_data = UserData::where("uid", $this->uid)->first();

//        $allowedExt = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'mp4', 'mov', 'avi', 'mp3', 'wav'];

        $allowedExt = ['jpeg', 'jpg', 'png', 'mp3', 'wav'];
        if ($this->isTester() || $user_data->web_update == 1) {
            $allowedExt =  ['jpeg', 'jpg', 'png', 'gif', 'mp3', 'wav'];
        }

        $storageSize = $this->totalNormalStorageLimit; // 100 MB
        $errorStorageMSg = "The file cannot be uploaded because the user's storage limit has already exceeded 100 MB";

//        $singleDataRow = SubscriptionController::getActivePlan($this->uid);
//        if ($singleDataRow || $user_data->creator == 1) {
//            $storageSize = $this->totalPremiumStorageLimit; // 1GB
//            $errorStorageMSg = "The file cannot be uploaded because the user's storage limit has already exceeded 1 GB";
//        }

        $totalUserExistingSize = Uploads::where("user_id", $this->uid)->where("deleted", 0)->sum('asset_size');
        if ($totalUserExistingSize > $storageSize) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $errorStorageMSg, ['maxSize' => $totalUserExistingSize]));
        }

        $uploadedFileSize = 0;
        foreach ($files as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($fileExtension), $allowedExt)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, 'Invalid file'));
            }
            $fileSize = $file->getSize();
            $uploadedFileSize = $uploadedFileSize + $fileSize;
            if ($uploadedFileSize + $totalUserExistingSize > $storageSize) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $errorStorageMSg, ['maxSize' => $totalUserExistingSize]));
            }
        }
        foreach ($files as $file) {

            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();


            if (in_array(strtolower($fileExtension), ['jpeg', 'jpg', 'png'])) {
                $assetType = 0;
            }
            if ($fileExtension == "gif") {
                $assetType = 1;
            } elseif ($fileExtension == "svg") {
                $assetType = 2;
            } elseif (in_array(strtolower($fileExtension), ['mp4', 'mov', 'avi'])) {
                $assetType = 3;
            } elseif (in_array(strtolower($fileExtension), ['mp3', 'wav'])) {
                $assetType = 4;
            }
            $fldr_str = $user_data->fldr_str;
            if ($fldr_str == null) {
                $fldr_str = HelperController::generateID('');
                while (UserData::where('fldr_str', $fldr_str)->exists()) {
                    $fldr_str = HelperController::generateID('');
                }
                UserData::where('id', $user_data->id)->update(['fldr_str' => $fldr_str]);
            }
            $string_id = HelperController::generateID('', 30);
            while (Uploads::where('string_id', $string_id)->exists()) {
                $string_id = HelperController::generateID('', 30);
            }

            $dir = 'u/' . $fldr_str . '/';
            $dirThumb = 'u/' . $fldr_str . '/';
            $dirCmpVdo = 'u/' . $fldr_str . '/';

            if ($assetType == 0) {
                $dir .= 'ri/';
                $dirThumb .= 'ti/';
            } elseif ($assetType == 1) {
                $dir .= 'rgif/';
                $dirThumb .= 'tgif/';
            } elseif ($assetType == 2) {
                $dir .= 'rsvg/';
                $dirThumb .= 'tsvg/';
            } elseif ($assetType == 3) {
                $dir .= 'rvd/';
                $dirThumb .= 'tvd/';
                $dirCmpVdo .= 'cvd/';
            } elseif ($assetType == 4) {
                $dir .= 'au/';
            }

            $thumb_name_without_ext = bin2hex(random_bytes(20)) . Carbon::now()->timestamp;
            $thumb_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            $new_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            Storage::disk('cloudflare_r2')->putFileAs($dir, $file, $new_name);
            $width = 0;
            $height = 0;
            $ratio = null;
            $thumbnail = null;

            // Create thumbnail for image formats except SVG
            if ($assetType == 0) {
                $image = Image::make($file);
                $width = $image->width();
                $height = $image->height();
                $ratio = round($width / $height, 2);
                $image->resize($this->aspectWidth, $this->aspectHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $thumbnailPath = $dirThumb . 'thumb_' . $thumb_name;
                Storage::disk('cloudflare_r2')->put($thumbnailPath, $image->encode());
                $thumbnail = $this->url . $dirThumb . 'thumb_' . $thumb_name;
            }

            // Handle Gig thumbnail creation
            if ($assetType == 1 || $assetType == 2) {
                $imagick = new \Imagick();
                if ($assetType == 1) {
                    $imagick->readImage($file->getPathname());
                    $imagick = $imagick->coalesceImages();
                    $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                } else {
                    $svgContent = file_get_contents($file->getRealPath());
                    $imagick->readImageBlob($svgContent);
                }
                $imagick->setImageFormat('png');
                $thumbnailPath = 'thumb_' . $thumb_name_without_ext . '.png';
                $localThumbnailPath = storage_path('app/temp/' . $thumbnailPath);

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();

                $ratio = round($width / $height, 2);

                if (!File::exists(storage_path('app/temp'))) {
                    File::makeDirectory(storage_path('app/temp'), 0755, true);
                }

                if (!$imagick->writeImage($localThumbnailPath)) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, 'Failed to write thumbnail PNG'));
                }

                Storage::disk('cloudflare_r2')->put($dirThumb . $thumbnailPath, file_get_contents($localThumbnailPath));
                $thumbnail = $this->url . $dirThumb . $thumbnailPath;
                File::delete($localThumbnailPath);
                $imagick->clear();
                $imagick->destroy();
            }

            //svg
            // if ($assetType == 2) {
            //     $thumbnail = $this->url . $dir . $thumb_name;
            //     $imagick = new \Imagick();
            //     $imagick->readImage($file->getPathname());
            //     $width = $imagick->getImageWidth();
            //     $height = $imagick->getImageHeight();
            // }

            $comporessVdoPath = "";
            $durationString = "";

            //video
            if ($assetType == 3) {
                try {
                     $meta = AudioVideoManager::getVideoMeta($file->getRealPath(),true,$dirCmpVdo . $thumb_name, true,$this->url . $dirThumb . 'thumb_' . $thumb_name_without_ext . '.jpg');
                    $durationString = $meta['duration'];
                    $thumbnail = $meta['thumbnail'];
                    $comporessVdoPath = $meta['compress_vdo'];

                } catch (\Exception $e) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $e->getMessage()));
                }
            }

            // Audio file duration handling
            if ($assetType == 4) {
                $filePath = $file->getRealPath();
                $durationString = AudioVideoManager::getDuration($filePath);
            }

            $res = new Uploads();
            $res->string_id = $string_id;
            $res->user_id = $this->uid;
            $res->name = $file->getClientOriginalName();
            $res->ratio = $ratio;
            $res->height = $height;
            $res->width = $width;
            $res->image = $this->url . $dir . $new_name;
            $res->thumbnail = $thumbnail;
            $res->asset_size = $fileSize;
            $res->asset_type = $assetType;
            $res->compress_vdo = $comporessVdoPath;
            $res->duration = $durationString;
            $res->save();
        }// end files
        return $this->gettingUploads($request, $this->uid, 0, 1, 1);
    }

    function getUploads(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $type = $request->get('type', 0);
        $assetType = $request->get('at', 1);
        $page = $request->has('page') ? $request->get('page') : 1;
        $keyword = $request->has('kw') ? $request->get('kw') : null;

        return $this->gettingUploads($request, $this->uid, $type, $page, $assetType, $keyword);
    }

    public function gettingUploads(Request $request, $uid, $type, $page, $assetType = null, $keyword = null): array|string
    {
        $limit = 10;

        $response = [];

        if ($page == 1) {
            $titles = [];
            foreach (self::ASSET_TYPES as $key => $assetName) {
                $data = $this->getUploadData($uid, $type, $page, $limit, $key, $keyword);
                $response['datas'][$assetName] = [
                    'isLastPage' => $data['isLastPage'],
                    'data' => $data['upload_rows']
                ];
                if (!$data['isError']) {
                    $title['assetType'] = $key;
                    $title['title'] = $data['title'];
                    $title['id'] = $assetName;
                    $titles[] = $title;
                }
            }
            $response['titles'] = $titles;
        } else {
            if (!array_key_exists($assetType, self::ASSET_TYPES)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "The asset type is not available"));
            }
            $response['titles'] = [];
            $data = $this->getUploadData($uid, $type, $page, $limit, $assetType, $keyword);

            $response['datas'] = [
                'isLastPage' => $data['isLastPage'],
                'data' => $data['upload_rows']
            ];
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));
    }

    private function getUploadData($uid, $type, $page, $limit, $assetType, $keyword): array
    {
        $query = Uploads::where("user_id", $uid)
            ->where("trashed", $type)
            ->where("asset_type", $assetType)
            ->where("deleted", 0)
            ->orderBy('created_at', 'DESC');

        if ($keyword) {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        $totalCount = $query->count();
        $uploadData = $query->paginate($limit, ['*'], 'page', $page);
        $uploadRows = $this->getUploadRows($uploadData, $assetType);

        $title = '';
        switch ($assetType) {
            case 0:
                $title = 'Image';
                break;
            case 1:
                $title = 'Gif';
                break;
            case 2:
                $title= 'Svg';
                break;
            case 3:
                $title = 'Video';
                break;
            case 4:
                $title = 'Audio';
                break;
        }

        $isLastPage = $uploadData->currentPage() >= $uploadData->lastPage();

        if ($page == 1 && $totalCount == 0) {
            return ["isLastPage" => $isLastPage, "upload_rows" => $uploadRows, "title" => $title, "isError" => true];
        }

        return ["isLastPage" => $isLastPage, "upload_rows" => $uploadRows, "title" => $title, "isError" => false];
    }

    private function getUploadRows($uploadData, $assetType): array
    {
        $uploadRows = [];

        foreach ($uploadData->items() as $draft) {
            $row = [
                'id' => $draft->string_id,
                'name' => $draft->name,
                'source_file' => $draft->image,
                'thumbnail' => $draft->thumbnail,
                'width' => $draft->width,
                'height' => $draft->height,
                'assetType' => $assetType
            ];

            switch ($assetType) {
                case 0:
                    $row['title'] = 'Image';
                    $row['layerType'] = 'StaticImage';
                    break;
                case 1:
                    $row['title'] = 'Gif';
                    $row['layerType'] = 'StaticGif';
                    break;
                case 2:
                    $row['title'] = 'Svg';
                    $row['layerType'] = 'StaticVector';
                    break;
                case 3:
                    $row['title'] = 'Video';
                    $row['layerType'] = 'StaticVideo';
                    $row['compress_video'] = HelperController::$mediaUrl . $draft->compress_vdo;
                    $row['duration'] = $this->getTime($draft->duration);
                    break;
                case 4:
                    $row['title'] = 'Audio';
                    $row['layerType'] = 'StaticAudio';
                    $row['duration'] = $this->getTime($draft->duration);
                    break;
            }

            $uploadRows[] = array_filter($row, function ($value) {
                return !is_null($value);
            });
        }

        return $uploadRows;
    }

    function modifiedUpload(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $draft_id = $request->id;
        $type = $request->type;

        if (is_null($draft_id) || is_null($type)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        // Determine update data based on type
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

        $success = Uploads::whereIn('string_id', $draft_ids)->where('user_id', $this->uid)->where($condition)->update($updateData);

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

    function renameUpload(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $draft_id = $request->id;
        $name = $request->name;

        if (is_null($draft_id) || is_null($name)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        $updateData = ['name' => $name];

        $success = Uploads::where('string_id', $draft_id)->where('user_id', $this->uid)->update($updateData);

        if (!$success) {
            $msg = 'Invalid request';
        } else {
            $msg = 'Renamed successfully';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface($success ? 200 : 401, (bool)$success, $msg));

    }

    private function getTime($durationInMs) {

        // $totalHours = floor($durationInMs / (3600000)); // 1 hour = 3600000 milliseconds
        // $remainingMsAfterHours = $durationInMs % 3600000;

        // $totalMinutes = floor($remainingMsAfterHours / 60000);
        // $remainingMsAfterMinutes = $remainingMsAfterHours % 60000;

        // $totalSeconds = floor($remainingMsAfterMinutes / 1000);

        // // Build the output conditionally
        // $output = "";

        // if ($totalHours > 0) {
        //     $output .= "$totalHours hour" . ($totalHours > 1 ? "s" : ""); // plural if more than 1 hour
        // }
        // if ($totalMinutes > 0) {
        //     if ($output !== "") $output .= " "; // Add space if there are hours already
        //     $output .= "$totalMinutes minute" . ($totalMinutes > 1 ? "s" : ""); // plural if more than 1 minute
        // }
        // if ($totalSeconds > 0 && $output === "") {
        //     $output .= "$totalSeconds second" . ($totalSeconds > 1 ? "s" : ""); // plural if more than 1 second, only show seconds if no hours/minutes
        // }

        // // Output the result
        return null;

    }
}

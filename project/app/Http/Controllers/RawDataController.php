<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\AudioVideoManager;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\LikedRawDatas;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\RawDatas;
use App\Models\UserData;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\File;

class RawDataController extends ApiController
{

    private int $aspectWidth = 150;
    private int $aspectHeight = 150;
    private int $totalNormalStorageLimit = 1073741824;
    private int $totalPremiumStorageLimit = 1073741824;
    private int $fileSize = 1048576;
    private int $videoFileSize = 10485760;
    private string $fileSizeMsg = "The file size can not more than 1mb";
    private string $videoFileSizeMsg = "The file size can not more than 10mb";

    const ASSET_TYPES = [
        0 => 'image',
        1 => 'gif',
        2 => 'svg',
        5 => 'frame',
        3 => 'video',
        4 => 'audio'
    ];

    function uploadDatas(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc) {
            return $this->failed(msg: "Head of creator can't upload datas");
        }

        $assetType = 0;
        $files = $request->file('files');
        $types = $request->get('types');
        if ($files == null || $types == null) {
            return $this->failed(msg: "Parameters Missing!");
        }

        $user_data = UserData::where("uid", $this->uid)->first();

        // $allowedExt = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'mp4', 'mov', 'avi', 'mp3', 'wav'];
        $allowedExt = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'mp3', 'mp4', 'webm'];

        $storageSize = $this->totalNormalStorageLimit; // 100 MB
        $errorStorageMSg = "The file cannot be uploaded because the user's storage limit has already exceeded 1 GB";

        $singleDataRow = SubscriptionController::getActivePlan($this->uid);
        if ($singleDataRow) {
            $storageSize = $this->totalPremiumStorageLimit; // 1GB
            $errorStorageMSg = "The file cannot be uploaded because the user's storage limit has already exceeded 1 GB";
        }

        $totalUserExistingSize = RawDatas::where("user_id", $this->uid)->where("deleted", 0)->sum('asset_size');
        if ($totalUserExistingSize > $storageSize) {
            return $this->failed(msg: $errorStorageMSg, datas: ['maxSize' => $totalUserExistingSize]);
        }

        $uploadedFileSize = 0;
        $md5Hashes = [];
        foreach ($files as $file) {
            $md5Hash = md5($file->get());
            if (RawDatas::where('md5', $md5Hash)->exists()) {
                return $this->failed(msg: "File already exists");
            }

            $md5Hashes[] = $md5Hash;

            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($fileExtension), $allowedExt)) {
                return $this->failed(msg: "Invalid file");
            }

            $fileSize = $file->getSize();

            if (in_array(strtolower($fileExtension), ['mp4', 'mov', 'avi'])) {
                if ($fileSize > $this->videoFileSize) {
                    return $this->failed(msg: $this->videoFileSizeMsg, datas: ['maxSize' => $this->videoFileSize]);
                }
            } else {
                if ($fileSize > $this->fileSize) {
                    return $this->failed(msg: $this->fileSizeMsg, datas: ['maxSize' => $this->fileSize]);
                }
            }

            $uploadedFileSize = $uploadedFileSize + $fileSize;
            if ($uploadedFileSize + $totalUserExistingSize > $storageSize) {
                return $this->failed(msg: $errorStorageMSg, datas: ['maxSize' => $totalUserExistingSize]);
            }
        }

        $count = 0;
        foreach ($files as $file) {

            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();


            if (in_array(strtolower($fileExtension), ['jpeg', 'jpg', 'png'])) {
                $assetType = 0;
            }
            if ($fileExtension == "gif") {
                $assetType = 1;
            } elseif ($fileExtension == "svg") {
                if ($types[$count] == "frame") {
                    $assetType = 5;
                } else {
                    $assetType = 2;
                }
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
            while (RawDatas::where('string_id', $string_id)->exists()) {
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
            } elseif ($assetType == 5) {
                $dir .= 'fr/';
                $dirThumb .= 'fr/';
            }

            StorageUtils::makeDirectory($dir);
            StorageUtils::makeDirectory($dirThumb);
            StorageUtils::makeDirectory($dirCmpVdo);

            $thumb_name_without_ext = bin2hex(random_bytes(20)) . Carbon::now()->timestamp;
            $thumb_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            $new_name = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
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
                StorageUtils::put($thumbnailPath, $image->encode());
                $thumbnail = $dirThumb . 'thumb_' . $thumb_name;
            }

            // Handle Gig thumbnail creation
            if ($assetType == 1 || $assetType == 2 || $assetType == 5) {
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

                if (!File::exists(storage_path('app/temp'))) {
                    File::makeDirectory(storage_path('app/temp'), 0755, true);
                }

                if (!$imagick->writeImage($localThumbnailPath)) {
                    return $this->failed(msg: "Failed to write thumbnail PNG");
                }

                StorageUtils::put($dirThumb . $thumbnailPath, file_get_contents($localThumbnailPath));
                $thumbnail = $dirThumb . $thumbnailPath;
                File::delete($localThumbnailPath);
                $imagick->clear();
                $imagick->destroy();
            }

            $comporessVdoPath = "";
            $durationString = "";


            //video
            if ($assetType == 3) {
                try {
                    $ffmpeg = FFMpeg::create([
                        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                        'ffprobe.binaries' => '/usr/bin/ffprobe',
                        'timeout' => 3600,
                        'ffmpeg.threads' => 12,
                    ]);
                    $video = $ffmpeg->open($file->getPathname());
                    $ffprobe = FFProbe::create([
                        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                        'ffprobe.binaries' => '/usr/bin/ffprobe',
                        'timeout' => 3600,
                        'ffmpeg.threads' => 12,
                    ]);
                    $durationInSeconds = $ffprobe->format($file->getPathname())->get('duration');
                    $durationInSeconds = (float)$durationInSeconds;
                    $durationInSeconds = (int)$durationInSeconds;
                    $currentTimestamp = time();
                    $durationString = $currentTimestamp + $durationInSeconds;

                    $frame = $video->frame(TimeCode::fromSeconds(1));
                    $localThumbnailPath = storage_path('app/temp/thumb_' . $thumb_name_without_ext . '.jpg');
                    $frame->save($localThumbnailPath);

                    // Resize the thumbnail to 150x150 while maintaining the aspect ratio
                    $image = Image::make($localThumbnailPath);
                    $image->resize($this->aspectWidth, $this->aspectHeight, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $width = $image->width();
                    $height = $image->height();
                    $ratio = round($width / $height, 2);
                    $image->save($localThumbnailPath);

                    // Upload the thumbnail to cloudflare_r2
                    $thumbnailPath = $dirThumb . 'thumb_' . $thumb_name_without_ext . '.jpg';
                    StorageUtils::put($thumbnailPath, file_get_contents($localThumbnailPath));
                    $thumbnail = $dirThumb . 'thumb_' . $thumb_name_without_ext . '.jpg';

                    // Delete the local temporary file
                    File::delete($localThumbnailPath);

                    // Compress and save the video locally
                    $compressedVideoPath = storage_path('app/temp/' . $thumb_name);
                    $video->filters()->resize(new Dimension($this->aspectWidth, $this->aspectHeight))->synchronize();
                    $video->save(new X264(), $compressedVideoPath);

                    // Upload the compressed video to cloudflare_r2
                    StorageUtils::put($dirCmpVdo . $thumb_name, file_get_contents($compressedVideoPath));

                    // Delete the local temporary file
                    File::delete($compressedVideoPath);
                    $comporessVdoPath = $dirCmpVdo . $thumb_name;

                } catch (\Exception $e) {
                    return $this->failed(msg: $e->getMessage());
                }
            }

            // Audio file duration handling
            if ($assetType == 4) {
                $filePath = $file->getRealPath();
                $durationString = AudioVideoManager::getDuration($filePath);
//                $filePath = $file->getPathname();
//                $output = shell_exec("ffmpeg -i " . escapeshellarg($filePath) . " 2>&1");
//                if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', $output, $time)) {
//                    $hours = (int)$time[2];
//                    $minutes = (int)$time[3];
//                    $seconds = (int)$time[4];
//                    $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
//                    $currentTimestamp = time();
//                    $durationString = $currentTimestamp + $totalSeconds;
//                } else {
//                    return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, 'Failed to retrieve MP3 duration'));
//                }
            }

            StorageUtils::putFileAs($dir, $file, $new_name);

            $res = new RawDatas();
            $res->string_id = $string_id;
            $res->user_id = $this->uid;
            $res->name = $file->getClientOriginalName();
            $res->ratio = $ratio;
            $res->height = $height;
            $res->width = $width;
            $res->image = $dir . $new_name;
            $res->thumbnail = $thumbnail;
            $res->asset_size = $fileSize;
            $res->asset_type = $assetType;
            $res->compress_vdo = $comporessVdoPath;
            $res->duration = $durationString;
            $res->md5 = $md5Hashes[$count];
            $res->save();

            $count++;
        }// end files
        return $this->gettingUploads($request, 0, 1, 0, 1);


    }

    function getUploads(Request $request): array|string
    {

        if ($this->isFakeRequestAndCreator($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

//        if ($this->isTester()) {
//            $disk = Storage::disk('gcs');
//            $datas = RawDatas::where('asset_type', 0)->whereNull('md5')->take(5)->get();
//            foreach ($datas as $data) {
//                if (!$data->md5) {
//                    try {
//                        $stream = $disk->readStream($data->image);
//
//                        if ($stream) {
//                            // Compute MD5 Hash
//                            $md5Hash = md5(stream_get_contents($stream, -1, 0));
//                            fclose($stream);
//                            $data->md5 = $md5Hash;
//                            $data->update(['md5' => $md5Hash]);
//                        }
//                    } catch (\Exception $e) {
//
//                    }
//                }
//            }
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
//        }

        $type = $request->get('type', 0);
        $assetType = $request->get('at', 1);
        $page = $request->has('page') ? $request->get('page') : 1;
        $fvrt = $request->has('fvrt') ? $request->get('fvrt') : 0;
        $keyword = $request->has('kw') ? $request->get('kw') : null;

        return $this->gettingUploads($request, $type, $page, $fvrt, $assetType, $keyword);

    }

    public function gettingUploads(Request $request, $type, $page, $fvrt, $assetType = null, $keyword = null): array|string
    {
        $userData = UserData::where('uid', $this->uid)->first();
        if (!$userData) return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid"));
        $limit = HelperController::getPaginationLimit(size: 50);

        $response = [];

        if ($page == 1 && !$keyword) {
            $titles = [];
            foreach (self::ASSET_TYPES as $key => $assetName) {
                $data = $this->getUploadData($userData, $type, $page, $limit, $key, $keyword, $fvrt);
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
            $data = $this->getUploadData($userData, $type, $page, $limit, $assetType, $keyword, $fvrt);

            $response['datas'] = [
                'isLastPage' => $data['isLastPage'],
                'data' => $data['upload_rows']
            ];
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $response));
    }

    private function getUploadData($userData, $type, $page, $limit, $assetType, $keyword, $fvrt): array
    {
        if ($fvrt) {
            $likedDatas = LikedRawDatas::where("user_id", $userData->uid)
                ->where("asset_type", $assetType)
                ->orderBy('id', 'DESC')
                ->paginate($limit, ['*'], 'page', $page);

            $ids = $likedDatas->pluck('product_id')->unique();

            $query = RawDatas::whereIn('string_id', $ids)
                ->where("trashed", $type)
                ->where("asset_type", $assetType)
                ->where("deleted", 0)
                ->orderBy('id', 'DESC');

        } else {
            if ($this->isTester() || $userData->hoc == 1) {
                $query = RawDatas::where("asset_type", $assetType)
                    ->where("deleted", 0)
                    ->orderBy('id', 'DESC');
            } else {
                $query = RawDatas::where("trashed", $type)
                    ->where("asset_type", $assetType)
                    ->where("deleted", 0)
                    ->orderBy('id', 'DESC');
            }
        }

        if ($keyword) {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        $totalCount = $query->count();
        $uploadData = $query->paginate($limit, ['*'], 'page', $page);

        $userIds = $uploadData->pluck('user_id')->unique();
        $string_ids = $uploadData->pluck('string_id')->unique();

        $likedProducts = LikedRawDatas::where('user_id', $this->uid)->whereIn('product_id', $string_ids)->get()->keyBy('product_id');
        $users = UserData::whereIn('uid', $userIds)->get()->keyBy('uid');

        $uploadRows = $this->getUploadRows($uploadData, $assetType);

        foreach ($uploadRows as $key => $data) {
            $user_ = $users->get($data['user_id']);
            $liked = $likedProducts->get($data['id']);
            $uploadRows[$key]['liked'] = (bool)$liked;
            $uploadRows[$key]['user_email'] = $user_ ? $user_->email : null;
            $uploadRows[$key]['user_name'] = $user_ ? $user_->name : null;
            unset($uploadRows[$key]['user_id']);
        }

        $title = '';
        switch ($assetType) {
            case 0:
                $title = 'Image';
                break;
            case 1:
                $title = 'Gif';
                break;
            case 2:
                $title = 'Svg';
                break;
            case 3:
                $title = 'Video';
                break;
            case 4:
                $title = 'Audio';
                break;
            case 5:
                $title = 'Frame';
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
            $name = $draft->name;
            if ($this->isTester()) {
                if ($draft->deleted == 1) $name = "(D) $name";
                else if ($draft->trashed == 1) $name = "(T) $name";
            }
            $row = [
                'user_id' => $draft->user_id,
                'id' => $draft->string_id,
                'name' => $name,
//                'source_file' => $draft->image,
//                'thumbnail' => $draft->thumbnail,
                'width' => $draft->width,
                'height' => $draft->height,
                'assetType' => $assetType
            ];

            if ($draft->image) {
                $row['source_file'] = HelperController::$mediaUrl . $draft->image;
            }

            if ($draft->thumbnail) {
                $row['thumbnail'] = HelperController::$mediaUrl . $draft->thumbnail;
            }

//            $row = [
//                'id' => $draft->string_id,
//                'name' => $draft->name,
//                'source_file' => $draft->image,
//                'thumbnail' => $draft->thumbnail,
//                'width' => $draft->width,
//                'height' => $draft->height,
//                'assetType' => $assetType
//            ];

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
                case 5:
                    $row['title'] = 'Frame';
                    $row['layerType'] = 'StaticFrame';
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

        if ($this->isFakeRequestAndCreator($request)) {
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

        $success = RawDatas::whereIn('string_id', $draft_ids)->where('user_id', $this->uid)->where($condition)->update($updateData);

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

        if ($this->isFakeRequestAndCreator($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $draft_id = $request->id;
        $name = $request->name;

        if (is_null($draft_id) || is_null($name)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters Missing!"));
        }

        $updateData = ['name' => $name];

        $success = RawDatas::where('string_id', $draft_id)->where('user_id', $this->uid)->update($updateData);

        if (!$success) {
            $msg = 'Invalid request';
        } else {
            $msg = 'Renamed successfully';
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface($success ? 200 : 401, (bool)$success, $msg));

    }

    function favourite(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get("id");

        $rawData = RawDatas::where("string_id", $id)->where("trashed", 0)->where("deleted", 0)->first();

        if (!$rawData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }

        $isExists = LikedRawDatas::where("user_id", $this->uid)->where("product_id", $id)->exists();

        if ($isExists) {
            $msg = "Unstarred";
            $isSuccess = LikedRawDatas::where("user_id", $this->uid)->where("product_id", $id)->delete();
            $liked = false;
        } else {
            $msg = "Starred";
            $isSuccess = LikedRawDatas::insert(['user_id' => $this->uid, 'product_id' => $id, 'asset_type' => $rawData->asset_type]);
            $liked = true;
        }

        if ($isSuccess) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $msg, ['liked' => $liked]));
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid request"));
        }
    }

    private function getTime($durationInMs)
    {

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

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\PaginationController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Design;
use App\Models\DesignerDraft;
use App\Models\Draft;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\UserData;
use Illuminate\Http\Request;
use App\Models\BgCategory;
use App\Models\BgItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Facades\Agent;

class ErrorReportingController extends ApiController
{

    function errorReporting(Request $request): array|string
    {

        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized", showDecoded: true);

        $draftId = $request->get('draft_id');
        $msg = $request->get('msg');
        $traces = $request->get('traces');

        if (is_null($draftId) || is_null($msg)) return $this->failed(msg: "Invalid request", showDecoded: true);

        $draftId = str_replace("/", "", $draftId);
        $draftId = strtok($draftId, '?');

        $msg = CryptoJsAes::decrypt($msg, $this->aesPassword);
        $traces = CryptoJsAes::decrypt($traces, $this->aesPassword);

        $type = "draft";
        $draft = Draft::whereStringId($draftId)->first();
        if (!$draft) {
            $type = "designer_draft";
            $draft = DesignerDraft::whereStringId($draftId)->first();
        }

        if (!$draft) {
            return $this->failed(msg: "Invalid draft id", showDecoded: true);
        }

        $browser = Agent::browser();
        $browserVersion = Agent::version($browser);
        $platform = Agent::platform();
        $platformVersion = Agent::version($platform);
        $device = Agent::device();

        if ($platform === 'Windows' && $platformVersion === '10.0') {
            $platformVersion = '10/11';
        }

        if ($device === 'WebKit' || $device === 'GenericDevice' || !$device) {
            $userAgent = $request->header('User-Agent');
            if (preg_match('/\(([^)]+)\)/', $userAgent, $matches)) {
                $parts = explode(';', $matches[1]);
                if (count($parts) > 2) {
                    $device = trim($parts[count($parts) - 1]);
                    if (str_contains($device, 'Build/')) {
                        $device = explode('Build/', $device)[0];
                    }
                }
            }
        }

        $systemInfo = "Browser: $browser $browserVersion, Platform: $platform $platformVersion, Device: $device";

        DB::table('draft_render_error')->insert([
            'user_id' => $this->uid,
            'draft_user_id' => $draft->user_id,
            'draft_id' => $draftId,
            'type' => $type,
            'msg' => $msg,
            'traces' => $traces,
            'system_info' => $systemInfo
        ]);

        return $this->successed(msg: "Error reported successfully", showDecoded: true);
    }

}

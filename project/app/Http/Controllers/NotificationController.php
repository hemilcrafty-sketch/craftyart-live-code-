<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\AllowedIp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends AppBaseController
{

    public function index()
    {

    }
   
    public function showNotificationSetting(Notification $notification)
    {
        $data['notification'] =  Notification::find(1);
        $data['allowed_ip'] =  AllowedIp::find(1);
        return view('notification/notification_setting')->with('data', $data);
    }

    public function updateNotificationSetting(Request $request, Notification $notification)
    {

        $res = Notification::find($request->id);
        $res->key = $request->input('key');
        $res->app_id = $request->input('app_id');
    
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function updateIpSetting(Request $request, Notification $notification)
    {

        $res = AllowedIp::find(1);
        $res->main_ip = $request->input('main_ip');
        $res->additional = $request->input('additional');
    
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }
}

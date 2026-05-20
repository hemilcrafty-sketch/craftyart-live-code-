<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Models\OTPTable;
use App\Models\UserData;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class MobileVerificationController extends ApiController
{

    function sendVerificationOTP(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $contact_no = $request->get('contact_no');
        $type = $request->get('type', "mobile_verification");

        if ($contact_no == null || $type == null) return $this->failed(msg: "Parameters missing!");

        $otp = sprintf("%06d", mt_rand(1, 999999));

        $user_data = UserData::where('uid', $this->uid)->first();
        $userName = $user_data?->name ?? "Crafty Art";

        $result = WhatsAppService::sendTemplateMessageFromCustomCrm(
            campaignName: "otp_verification",
            userName: $userName,
            mobile: $contact_no,
            templateParams: [$otp],
            ctaButtons: [
                [
                    "type" => "button",
                    "sub_type" => "url",
                    "index" => 0,
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $otp
                        ]
                    ],
                ]
            ]
        );

        if ($result['success'] == "true" || $result['success']) {

            OTPTable::whereMail($contact_no)->whereType("mobile_verification")->update(["status" => 0]);

            $res = new OTPTable();
            $res->mail = $contact_no;
            $res->otp = $otp;
            $res->msg = $otp;
            $res->type = $type;
            $res->status = "1";
            $res->save();

            return $this->successed(msg: "OTP has been successfully sent to your whatsapp number", datas: ["otp_length" => strlen($otp)]);
        }

        return $this->failed();
    }

    function verifyOTP(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");

        $contact_no = $request->get('contact_no');
        $otp = $request->get('otp');

        if ($contact_no == null || $otp == null) return $this->failed(msg: "Parameters missing!");

        $data = OTPTable::whereMail($contact_no)->whereType("mobile_verification")->get()->last();
        if ($data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid OTP");

        $user_data = UserData::where('uid', $this->uid)->first();
        $user_data->contact_no = $contact_no;
        $user_data->contact_no_verified = 1;
        $user_data->save();

        $res = OTPTable::find($data->id);
        $res->status = "0";
        $res->save();

        return $this->successed(msg: "Mobile number verified successfully!");
    }
}

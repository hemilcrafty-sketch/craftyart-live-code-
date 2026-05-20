<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\OTPTable;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationController extends ApiController
{

    function sendVerificationOTP(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $email = $request->get('email');
        $type = $request->get('type'); // 0 = account creation,

//        if ($type == null) {
//            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
//        }

        if (!in_array($type, [0, 1, 'account_create', 'forgot_pass', 'delete_acc'], true)) {
            return $this->failed(msg: "Invalid request");
        }

        if ($type == 0 || $type == "0") $type = 'account_create';
        if ($type == 1 || $type == "1") $type = 'delete_acc';

        if ($type === 'account_create') {
            if (UserData::whereEmail($email)->exists()) return $this->failed(msg: "Email is already registered");
            $mail = $email;
        } else {
            if ($email) $user_data = UserData::where('email', $email)->first();
            else $user_data = UserData::where('uid', $this->uid)->first();
            if (!$user_data) return $this->failed(msg: "Invalid request");
            $mail = $user_data->email;
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));

        $htmlStr = "Dear user,<br/><b>" . $otp . "</b> is your one time password (OTP). Please do not share the OTP with others.<br/>Regards,<br/>Team Crafty Art";
        $msg = "Dear user,\n" . $otp . " is your one time password (OTP). Please do not share the OTP with others.\nRegards,\nTeam Crafty Art";

        Mail::mailer('otp')->send([], [], function ($message) use ($mail, $htmlStr) {
            $message->from(env("MAIL_OTP_FROM_ADDRESS"), env("MAIL_OTP_FROM_NAME"))
                ->to($mail)
                ->replyTo(env("MAIL_OTP_FROM_ADDRESS"), 'Reply Support')
                ->subject("Verify OTP")
                ->setBody($htmlStr, 'text/html');

            $message->getHeaders()->addTextHeader('Precedence', 'bulk');
        });

        OTPTable::whereMail($mail)->whereType($type)->update(["status" => 0]);

        $res = new OTPTable();
        $res->mail = $mail;
        $res->otp = $otp;
        $res->msg = $msg;
        $res->type = $type;
        $res->status = "1";
        $res->save();

        return $this->successed(msg: "OTP has been successfully sent to your mail", datas: ["length" => strlen($otp), "otp_length" => strlen($otp)]);
    }

    function verifyOTP(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $otp = $request->get('otp');

        if ($otp == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
        }

        $user_data = UserData::where('uid', $this->uid)->first();
        $mail = $user_data->email;

        $data = OTPTable::where('mail', $mail)->get()->last();

        if ($data->status == "0" || $data->otp != $otp) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid OTP"));
        }

        $res = OTPTable::find($data->id);
        $res->status = "0";
        $res->save();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Welcome to Crafty Art"));
    }
}

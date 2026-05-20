<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\OfferRegistrations;
use Illuminate\Http\Request;

class OfferRegistrationController extends ApiController
{

    function add(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $name = $request->get('name');
        $email = $request->get('email');
        $mobile_no = $request->get('number');
        $insta_id = $request->get('insta_id');
        $video_link = $request->get('video_link');

        if ($name == null || $email == null || $mobile_no == null || $insta_id == null || $video_link == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Parameters missing"));
        }

        if (OfferRegistrations::where('email', $email)->exists()) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "User already registered"));
        }

        $res = new OfferRegistrations();
        $res->name = $name;
        $res->email = $email;
        $res->mobile_no = $mobile_no;
        $res->insta_id = $insta_id;
        $res->video_link = $video_link;
        $res->save();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "done"));

    }


    function show(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $res = OfferRegistrations::select('name', 'email', 'mobile_no', 'insta_id', 'video_link')->get();
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'done', ["datas" => $res]));
    }
}


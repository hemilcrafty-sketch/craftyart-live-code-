<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\OfferPopUp;
use App\Models\OfferRegistrations;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OfferPopUpController extends ApiController
{

    public function getOfferPopUp(Request $request)
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");


        $offer = OfferPopUp::first();
        if (!$offer) return $this->failed(msg:"No Offer Pop Up configured");


        $promoCodes = PromoCode::where('id', $offer->promo_code)->first();

        $response = [
            'enable_offer' => (bool) $offer->enable_offer,
            'duration' => $offer->duration,
            'frequency_duration' => $offer->frequency_duration,
            'enable_force' => (bool) $offer->enable_force,
            'force_show_duration' => $offer->force_show_duration,
            'title' => $offer->title,
            'festival_name' => $offer->festival_name,
            'description' => $offer->description,
            'sub_description' => $offer->sub_description,
            'btn_name' => $offer->btn_name,
            'btn_link' => $offer->btn_link,
            'enable_promo_code' => (bool) $offer->enable_promo_code,
            'promo_code' => $promoCodes->promo_code,
            'expiry_date' => Carbon::parse($promoCodes->expiry_date)->format('F Y'),
            'disc' => $promoCodes->disc,
        ];

        return $this->successed(datas: $response);
    }

    public function getOfferPopUp2(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $offer = OfferPopUp::first();
        if (!$offer) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, 'No Offer Pop Up configured.', [])
            );
        }
        $response = [
            'id' => $offer->id,
            'enable_offer' => (bool)$offer->enable_offer,
            'duration' => $offer->duration,
            'frequency_duration' => $offer->frequency_duration,
            'enable_force' => (bool)$offer->enable_force,
            'force_show_duration' => $offer->force_show_duration,
        ];
        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Offer Pop-Up detail loaded successfully.', $response)
        );
    }
}


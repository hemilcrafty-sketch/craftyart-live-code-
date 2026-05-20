<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class StockImagesController extends ApiController
{
    public function getImages(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $url = $request->get('url');
        $query = $request->get('query');

        if (empty($url)) $url = "https://api.pexels.com/v1/curated/?per_page=80";

        if (!empty($query)) $url = "https://api.pexels.com/v1/search?query=$query&per_page=80";

        try {
            $client = new Client();

            $fbaseRes = $client->get($url, [
                'headers' => [
                    'Authorization' => 'fRSBZFV8gwRCtnqV9rzZ8gkyHDeeSnP2N0Fjbk4DjPKxaxbxvoDVTCou',
                ]
            ]);

            $statusCode = $fbaseRes->getStatusCode();
            if ($statusCode != 200) return $this->failed(msg: "Bad request");


            return $this->successed(datas: [
                'data' => json_decode($fbaseRes->getBody(), true),
            ]);

        } catch (Exception|GuzzleException $e) {
            return $this->failed(msg: $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class WebCorsMiddleware extends CorsMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $response = parent::handle($request, $next);

        $response->header('Access-Control-Allow-Origin', Config::get('app.url'));

        return $response;
    }
}

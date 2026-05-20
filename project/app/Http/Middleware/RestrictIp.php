<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AllowedIp;

class RestrictIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

        return $next($request);

        // if ($request->user()->user_type == 1) {
        //     return $next($request);   
        // }

        // $defaultIp = $request->ip();
        // $ips = $request->getClientIps();

        // $ipv4 = [];
        // $ipv6 = [];

        // foreach ($ips as $ip) {
        //     if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        //         $ipv4[] = $ip;
        //     }
        //     if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        //         $ipv6[] = $ip;
        //     }
        // }

        // if (!in_array($defaultIp, $ipv4) && filter_var($defaultIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        //     $ipv4[] = $defaultIp;
        // }

        // if (!in_array($defaultIp, $ipv6) && filter_var($defaultIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        //     $ipv6[] = $defaultIp;
        // }

        // $allIps = array_unique(array_merge($ipv4, $ipv6));

        // $validIp = false;

        // foreach ($allIps as $ip) {
        //     if (
        //         AllowedIp::where('main_ip', $ip)
        //             ->orWhere('additional', 'like', '%'.$ip.'%')
        //             ->exists()
        //     ) {
        //         $validIp = true;
        //         break;
        //     }
        // }

        // if (!$validIp) {
        //     abort(403, 'Unauthorized User.');
        // }

        // return $next($request);
    }
}

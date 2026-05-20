<?php

namespace App\Http\Middleware;

use Closure;

class isAdminOrSeoManger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->user_type == 1 || $request->user()->user_type == 2 || $request->user()->user_type == 4) {
            return $next($request);
        }
        return abort(404);
    }
}
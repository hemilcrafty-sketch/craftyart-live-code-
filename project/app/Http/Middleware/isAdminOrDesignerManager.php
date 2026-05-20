<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;

class IsAdminOrdesignerManager
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
        if ($request->user()->user_type == 1 || $request->user()->user_type == 7) {
            return $next($request);
        }
        return abort(404);
    }
}
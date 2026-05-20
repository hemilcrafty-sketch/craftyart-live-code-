<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;

class IsSalesAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        if ($request->user()->user_type == UserRole::ADMIN->id() || $request->user()->user_type == UserRole::MANAGER->id() || $request->user()->user_type == UserRole::SALES_MANAGER->id() || $request->user()->user_type == UserRole::SALES->id()) {
            return $next($request);
        }
        return abort(404);
    }
}
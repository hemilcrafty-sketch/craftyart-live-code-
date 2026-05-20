<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class IsAdminOrHr
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ($request->user()->user_type == UserRole::ADMIN->id() || $request->user()->user_type == UserRole::HR->id())) {
            return $next($request);
        }
        return abort(404);
    }
}

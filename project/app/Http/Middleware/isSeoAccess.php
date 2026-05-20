<?php

namespace App\Http\Middleware;

use Closure;

class IsSeoAccess
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
    // Ensure user is authenticated before accessing user_type
    $user = $request->user();

    if ($user && in_array($user->user_type, [1, 2, 4, 6,5,7,8])) {
      return $next($request);
    }

    return redirect()->route('dashboard');
  }

}
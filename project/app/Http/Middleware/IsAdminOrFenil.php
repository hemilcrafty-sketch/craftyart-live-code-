<?php
 
namespace App\Http\Middleware;
 
use Closure;
 
class IsAdminOrFenil
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
        try {
            if ($request->user()->user_type == 1 || $request->user()->id == 40) {
                return $next($request);   
            }
            return abort(404);
        } catch (\Exception $e) {
            return abort(404);       
        }
    }
}
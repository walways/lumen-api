<?php

namespace App\Http\Middleware\External;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Exceptions\BusinessException;

class Authenticate extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'external')
    {
        if($request->header('Access-Token') != env('Access_Token')){
            throw new BusinessException("ERROR_NOT_HAS_PERMSSION");
        }
        return $next($request);
    }
}

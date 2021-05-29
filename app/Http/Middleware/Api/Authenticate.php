<?php

namespace App\Http\Middleware\Api;

use Closure;
use App\Librarys\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use \Illuminate\Support\Facades\Auth;

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
    public function handle($request, Closure $next, $guard = "api")
    {
        Log::info('header', [$request->header()]);
        app('auth')->setProvider(app('auth')->createUserProvider('fc_account'));

        try {
            $this->checkForToken($request);
            if (($token = $request->header('authorization')) || ($token = $request->input('token'))) {
                $this->auth->setToken(substr($token, 6));   //去除头部的bearer
            }

            if ($this->auth->authenticate()) {
                $user_info = Auth::user();
                $activeUser = $user_info->toArray();
                $activeUser['network_id'] = $request->get('network_id');
                // 设置登录用户信息
                app('activeUser')->setUser($activeUser);
                return $next($request);
            }

            return Response::error('ERR_TOKEN_INVALID_JUMP_LOGIN');
        } catch (TokenInvalidException $e) {
            return Response::error('ERR_TOKEN_INVALID_JUMP_LOGIN');//无效token会返回未知错误抛出来,对此处理
        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                // 刷新用户的 token
                $token = \Illuminate\Support\Facades\Auth::guard($guard)->refresh();
                // 使用一次性登录以保证此次请求的成功
                Auth::guard('users')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }
        }
        $response = $next($request);
        return $this->setAuthenticationHeader($response, $token);
    }
}

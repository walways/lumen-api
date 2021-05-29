<?php

namespace App\Exceptions;

use App\Librarys\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use OSS\Core\OssException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        BusinessException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return Response::error('METHOD_NOT_ALLOWED');
        }

        //参数验证异常
        if ($exception instanceof ValidationException) {
            $errorList = $exception->validator->getMessageBag()->getMessages();
            return Response::error('INVALID_ARGUMENT', [array_keys($errorList)], head(head($errorList)));
        }

        if ($exception instanceof BusinessException) {
            return Response::error($exception->getMessage());
        }

        if ($exception instanceof TokenExpiredException) {
            return Response::error('ERR_TOKEN_INVALID_JUMP_LOGIN');
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return Response::error('ERR_TOKEN_INVALID_JUMP_LOGIN');
        }

        if ($exception instanceof TokenInvalidException) {
            return Response::error('TOKEN_INVALID');
        }

        if ($exception instanceof CustomException) {
            return Response::response(intval($exception->getCode()), $exception->getMessage(), []);
        }
        return parent::render($request, $exception);
    }
}

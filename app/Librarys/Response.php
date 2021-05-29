<?php

namespace App\Librarys;

use stdClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response as ApiResponse;

class Response
{
    const STATUS_SUCCESS = 0; //响应成功 code

    protected static $cookie;

    protected static $header;

    public static $jsonForceObj = false;//是否强制返回object型json

    /**
     * 设置cookie
     *
     * @param $cookie
     */
    public static function withCookie($cookie)
    {
        self::$cookie = $cookie;
    }

    /**
     * 设置header
     *
     * @param $header
     */
    public static function withHeader($header)
    {
        self::$header = $header;
    }

    /**
     * 响应
     *
     * @param       $code
     * @param       $msg
     * @param array $data
     *
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public static function response($code, $msg, $data = [], $status = 200)
    {
        $msgpack = [
            'code' => $code,
            'msg'  => $msg,
        ];

        $msgpack['trace-id'] = app('gc-api-trace')->getTrace();

        if (!($data instanceof stdClass) && empty($data)) {
            $msgpack['data'] = new stdClass();
        } else {
            $msgpack['data'] = $data;
        }

        $response = ApiResponse::json($msgpack, $status, [], static::$jsonForceObj ? (JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE) : JSON_UNESCAPED_UNICODE);
        if (!is_null(self::$cookie)) {
            $response = $response->withCookie(self::$cookie);
        }

        if (!empty(self::$header)) {
            foreach (self::$header as $key => $value) {
                $response = $response->header($key, $value);
            }
        }

        return $response;
    }

    /**
     * 成功响应
     *
     * @param null   $data
     * @param string $msg
     *
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $msg = 'success')
    {
        return static::response(self::STATUS_SUCCESS, $msg, $data);
    }

    /**
     * 失败响应
     *
     * @param       $errno
     * @param array $data
     *
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public static function error($errno, $data = [], $msg = '', $status = 200)
    {
        if (!$msg) {
            [$errno, $msg] = self::parseMsg($errno);
        }

        $code = Config::get("error.{$errno}" . ".code", '110000');
        if (empty($data)) {
            $data = new stdClass();
        }

        return self::response($code, $msg, $data, $status);
    }

    private static function parseMsg($errno)
    {
        if (!Str::contains($errno, '|')) {
            return [$errno, Config::get("error.{$errno}" . ".msg", '系统未知错误')];
        }

        $errArr = explode('|', $errno);

        $errno = Arr::get($errArr, 0);

        $errMsg = Config::get("error.{$errno}" . ".msg", '系统未知错误');

        $dynamicMsg = json_decode(Arr::get($errArr, 1), true);

        if (!is_array($dynamicMsg) || empty($dynamicMsg)) {
            return [$errno, $errMsg];
        }

        return [$errno, strtr($errMsg, $dynamicMsg)];
    }
}

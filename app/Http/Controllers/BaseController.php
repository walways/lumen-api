<?php
/**
 * Created by PhpStorm.
 * User: Geikiy
 * Date: 19/01/2021
 * Time: 14:08
 */

namespace App\Http\Controllers;

use App\Librarys\Response;

class BaseController extends Controller
{
    /**
     * 返回接口错误信息
     *
     * @param       $errno
     * @param array $data
     *
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public static function error($errno, $data = [], $msg = '', $status = 200)
    {
        return Response::error($errno, $data, $msg, $status);
    }

    /**
     * 返回接口正确信息
     *
     * @param $data
     *
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public static function success($data = [], $msg = 'success')
    {
        return Response::success($data, $msg);
    }
}

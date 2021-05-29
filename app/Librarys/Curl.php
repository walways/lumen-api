<?php

namespace App\Librarys;

use Illuminate\Support\Facades\Log;

class Curl
{

    //缓存数据
    static $curlCacheData = [];

    /**
     * HTTP 请求
     * @param  string $url     请求地址
     * @param  array  $params  请求参数
     * @param  string $method  请求方式
     * @param  array  $header  头信息
     * @param  int    $timeout 超时设置
     *
     * @return bool|mixed
     */
    public static function httpCurl($url, $params = [], $method = 'GET', $header = [], $timeout = 25)
    {
        $key         = json_encode(func_get_args(), true);
        $phpSapiName = function_exists("php_sapi_name") ? php_sapi_name() : "";

        if (isset(self::$curlCacheData[$key]) && stripos($phpSapiName, "cli") === false) {
            return self::$curlCacheData[$key];
        }

        $result = self::httpClient($url, $params, $method, $header, $timeout);
        if (isset($result['errNo']) && $result['errNo'] == 0 && isset($result['content']) && $result['content']) {
            if (stripos($phpSapiName, "cli") === false) {
                self::$curlCacheData[$key] = $result['content'];
            }
            return $result['content'];
        }
        return false;
    }


    public static function httpClient($url, $params = [], $method = 'GET', $header = [], $timeout = 25, $withCookie = true)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if($withCookie){
            $cookieStr = '';
            foreach ($_COOKIE as $name => $value){
                $cookieStr .= "{$name}={$value};";
            }
            curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
        }
        if ($timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        $method = strtoupper($method);
        if ($method == 'GET') {
            if (!empty($params)) {
                $queryString = http_build_query($params);
                $url         .= "?$queryString";
            }
        } elseif ($method == 'POST') {
            if(is_array($params)){
                $postFields = http_build_query($params);
            }else{
                //简直json类型
                $postFields = $params;
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        $data  = curl_exec($ch);
        $errNo = curl_errno($ch);

        $result = [
            'header'   => array(),
            'content'  => '',
            'errNo'    => 0,
            'errorMsg' => ''
        ];
        if ($errNo) {
            $result['errNo']    = $errNo;
            $result['errorMsg'] = curl_error($ch);

            //记录日志
            Log::error('USER_EXT_CURL_ERROR', [$url, $header, func_get_args(), $result]);
            return $result;
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        $result['content'] = $data;
        //记录日志
        Log::debug('=======CURL_LOG', [$url, $header, func_get_args(), $result]);

        $result['header'] = $info;
        return $result;
    }
}

<?php

namespace App\Http\Bll;

use App\Librarys\HttpRequest;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Redis;
use App\Constants\CacheKey\SmsCacheKey;

class BaseBll
{
    private static $httpClient;
    private static $httpDomain;

    public static function getTime()
    {
        return time();
    }

    //分转换为元
    public static function fenToYuan($money)
    {
        return empty($money) ? '0.00' : (string)bcdiv($money, 100, 2);
    }

    // 四舍五入分转元
    public static function fenToYuanByRound($money)
    {
        return empty($money) ? '0.00' : sprintf(
            "%.2f",
            round(bcdiv($money, 100, 6), 2)
        );
    }

    /**
     * 处理递归
     *
     * @param $array
     *
     * @return array
     */
    public static function generateTree($array)
    {
        $items = [];
        foreach ($array as $value) {
            $value instanceof \StdClass && $value = (array)$value;
            $items[$value['id']] = $value;
        }

        $tree = [];
        foreach ($items as $key => $value) {
            if (isset($items[$value['pid']]) && !empty($items[$value['pid']])) {
                $items[$value['pid']]['children'][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }

        return $tree;
    }

    public static function modelListReturn(
        $page = 1,
        $pageSize = 10,
        $total = 0,
        $data = []
    ) {
        return [
            'pageNo'     => (int)$page,
            'pageSize'   => (int)$pageSize,
            'totalCount' => (int)$total,
            'totalPage'  => (int)ceil($total / $pageSize),
            'list'       => $data,
        ];
    }

    /**
     * 验证码校验
     *
     * @param $type    类型
     * @param $mobile  手机号
     * @param $captcha 验证码
     * @throws BusinessException
     */
    public static function checkMobileCaptcha($type, $mobile, $captcha)
    {
        if (!in_array(env('APP_ENV'), ['prod', 'pre']) && $captcha == '111111') {
            return true;
        }

        $cache_key = SmsCacheKey::getCaptchaKey($type, $mobile);
        $cacheCode = Redis::get($cache_key);
        if (empty($cacheCode)) {
            throw new BusinessException('COMMON_ACCOUNT_CAPTCHA_EMPTY');
        }

        if ($captcha != $cacheCode) {
            throw new BusinessException('COMMON_ACCOUNT_CAPTCHA_ERROR');
        }
        
        return true;
    }

    /**
     * http client请求类
     */
    public static function httpClient($doomain = 'go')
    {
        if(self::$httpClient !== NULL && self::$httpDomain == $doomain){
            return self::$httpClient;
        }

        $httpClient = new HttpRequest();
        switch($doomain){
            case 'go':
                $httpClient->setHost(env('GO_SERVICE_HTTP_URL', ''));
                break;
            case 'qywx':
                $httpClient->setHost('https://qyapi.weixin.qq.com/cgi-bin');
                break;
            default:
                $httpClient->setHost('');
        }

        self::$httpDomain = $doomain;
        self::$httpClient = $httpClient;
        return $httpClient;
    }
}

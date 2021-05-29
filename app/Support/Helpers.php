<?php
//namespace App\Support;

use App\Librarys\ConfigDB\ConfigDB;
use Jenssegers\Agent\Facades\Agent;

/**
 * Generate a signature.
 *
 * @param array $attributes
 * @param string $key
 * @param string $encryptMethod
 *
 * @return string
 */
if (!function_exists('generate_sign')) {

    function generate_sign(array $attributes, $key, $encryptMethod = 'md5')
    {
        ksort($attributes);

        $attributes['key'] = $key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }
}

/**
 *
 * @return string
 */

if (!function_exists('get_client_type')) {
    function get_client_type()
    {
        // 微信访问
        if (strpos(Agent::getUserAgent(), 'MicroMessenger') !== false) {
            return 'wechat';
        }

        if (Agent::isiOS()) {
            return 'ios';
        }

        if (Agent::isAndroidOS()) {
            return 'andriod';
        }

        return 'web';
    }
}

if (!function_exists('config_db')) {

    /**
     * @param null $key
     * @param null $default
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    function config_db($key = null, $default = null)
    {
        if (is_null($key)) {
            return new ConfigDB();
        }

        if (is_array($key)) {
            return (new ConfigDB())->set($key);
        }

        return (new ConfigDB())->get($key, $default);
    }
}

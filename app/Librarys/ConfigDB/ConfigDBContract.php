<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/7/24
 * Time: 19:25
 */


namespace App\Librarys\ConfigDB;

/**
 * Interface ConfigDBContract
 * @package Lxk\Base\Config
 */
interface ConfigDBContract
{
    public function has($key);

    public function get($key, $default = null);

    public function set($key, $value);

    public function forget($key);

    public function flush();
}
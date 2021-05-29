<?php
/**
 * 类说明
 * Created by PhpStorm.
 * User: qpf
 * Date: 2021/5/26
 * Time: 2:07 下午
 */

Route::group(['prefix' => ''], function (\Laravel\Lumen\Routing\Router $router) {
    $router->post('login', ['uses' => 'UserController@login']);

    $router->post('info', ['uses' => 'UserController@info', 'middleware' => ['api.auth']]);
    //退出登录
    $router->post('logout', ['uses' => 'UserController@logout', 'middleware' => ['api.auth']]);
});

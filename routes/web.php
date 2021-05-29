<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// 供其它第三方调用的接口
$router->group(['prefix' => '', 'namespace' => 'External', 'middleware' => []], function () {
    include_once __DIR__ . '/External/api.php';
});

// 内部业务调用的接口
$router->group(['prefix' => '', 'namespace' => 'Internal', 'middleware' => []], function () {
    include_once __DIR__ . '/Internal/api.php';
});
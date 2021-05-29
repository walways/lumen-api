<?php
/**
 * Created by PhpStorm.
 * User: Geikiy
 * Date: 19/01/2021
 * Time: 13:57
 */
Route::group(['prefix' => 'v1', 'namespace' => 'V1'], function () {
    //用户模块
    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        include_once __DIR__ . '/V1/User/user.php';
    });

});

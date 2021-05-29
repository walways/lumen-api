<?php
/**
 * 类说明
 * Created by PhpStorm.
 * User: qpf
 * Date: 2021/5/26
 * Time: 11:48 上午
 */

namespace App\Http\Bll\User;

use App\Http\Bll\BaseBll;
use App\Models\User;
use App\Exceptions\BusinessException;


class UserBll extends BaseBll
{
    /**
     * 用户登录
     * @param $params
     *
     * @return array
     * @throws BusinessException
     */
    public static function userLogin($params)
    {
        $email = $params['username'];
        $password = sha1($params['password']);
        $user = User::where('email',$email)->first();
        if (!$user) {
            throw new BusinessException('ACCOUNT_NOT_EXIST');
        }
        if ($password != $user->password) {
            throw new BusinessException('LOGIN_PASSWORD_ERROR');
        }
        $token= auth('api')->login($user);
        $data = [];

        $data['token'] = 'bearer' . $token;
        $data['token_type'] = 'Bearer';
        $data['expires_in'] = \Auth::guard('api')->factory()->getTTL() * 60;

        return $data;
    }
}
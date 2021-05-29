<?php

namespace App\Http\Controllers\Internal\V1\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\Bll\User\UserBll;

class UserController extends BaseController
{

    /**
     * 用户登录
     *
     * @param Request $request
     * Route POST /v1/user/login
     *
     * @return \App\Librarys\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|string|min:1|max:50',
            'password' => 'required|string',
        ];

        $message = [
            'username.max'      => trans('common.USERNAME_MAX'),
            'password.required' => trans('validation.required',
                ['attribute' => trans('common.PASSWORD')]),
        ];

        $params    = $request->all();
        $validator = \Validator::make($params, $rules, $message);
        if ($validator->fails()) {
            return self::error('INVALID_ARGUMENT', $validator->errors(),
                $validator->errors()->first());
        }
        $data                 = UserBll::userLogin($params);
        return self::success($data, trans('common.SUCCESS'));
    }

    /**
     * 登录用户信息
     *
     * @param Request $request
     * Route POST /v1/user/info
     *
     * @return \App\Librarys\Response|\Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $ActiveUser = app('activeUser');
        return self::success($ActiveUser, trans('common.SUCCESS'));
    }

    /**
     * 退出登录
     *
     * @param Request $request
     * Route POST /v1/user/logout
     *
     * @return \App\Librarys\Response|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth('api')->logout();
        return $this->success([]);
    }


}


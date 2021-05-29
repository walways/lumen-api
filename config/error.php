<?php

return [

    /**********************  100000 ~ 199999 区间的错误代码为系统保留。请勿随意增加。  ********************************/

    'COMMON_UNKNOWN_ERROR' => [
        'code' => 100000,
        'msg' => trans('common.COMMON_UNKNOWN_ERROR'),
    ],
    'INVALID_ARGUMENT' => [
        'code' => 100001,
        'msg' => trans('common.INVALID_ARGUMENT'),
    ],
    'COMMON_SERVICE_FAILED' => [
        'code' => 100003,
        'msg' => trans('common.COMMON_SERVICE_FAILED'),
    ],
    'COMMON_PARAM_NOT_LEGAL' => [
        'code' => 100006,
        'msg' => trans('common.COMMON_PARAM_NOT_LEGAL'),
    ],
    'METHOD_NOT_ALLOWED' => [
        'code' => 100007,
        'msg' => trans('common.METHOD_NOT_ALLOWED'),
    ],
    'NOT_FOUND' => [
        'code' => 100008,
        'msg' => trans('common.NOT_FOUND'),
    ],
    'DATA_NOT_FOUND' => [
        'code' => 100009,
        'msg' => trans('common.DATA_NOT_FOUND'),
    ],
    'REQUEST_TOO_OFTEN' => [
        'code' => 100010,
        'msg' => trans('common.REQUEST_TOO_OFTEN'),
    ],
    'ORIGIN_NOT_ALLOW' => [
        'code' => 100011,
        'msg' => trans('common.ORIGIN_NOT_ALLOW'),
    ],
    'OPERATION_FAILED_AGAIN_LATER' => [
        'code' => 100012,
        'msg' => trans('common.OPERATION_FAILED_AGAIN_LATER'),
    ],
    'COMMON_PARAM_NOT_ERROR' => [
        'code' => 100012,
        'msg' => trans('common.COMMON_PARAM_NOT_ERROR'),
    ],
    'COMMON_REQUEST_ERROR' => [
        'code' => 100013,
        'msg' => trans('common.COMMON_REQUEST_ERROR'),
    ],
    'COMMON_REPEAT_DATA' => [
        'code' => 100014,
        'msg' => trans('common.COMMON_REPEAT_DATA'),
    ],
    'ERR_TOKEN_INVALID_JUMP_LOGIN' => [
        'code' => 1000015,
        'msg' => trans('common.ERR_TOKEN_INVALID_JUMP_LOGIN'),
    ],
    'ACCOUNT_NOT_EXIST' => [
        'code' => 1000016,
        'msg' => trans('common.ACCOUNT_NOT_EXIST'),
    ],
    'LOGIN_PASSWORD_ERROR' => [
        'code' => 1000017,
        'msg' => trans('common.LOGIN_PASSWORD_ERROR'),
    ],
];
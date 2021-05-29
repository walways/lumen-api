<?php
namespace App\Librarys;


class ActiveUser
{
    public function __construct()
    {

    }

    /**
     * 设置登录用户
     */
    public function setUser($user)
    {
        if (empty($user)) {
            return false;
        }

        if(is_array($user)){
            $user = (object) $user;
        }

        foreach ($user as $k => $v) {
            $this->$k = $v;
        }
    }


}